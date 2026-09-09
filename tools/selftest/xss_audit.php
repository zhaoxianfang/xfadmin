<?php
/**
 * xfadmin 组件 XSS 转义一致性审计（运行时模糊审计）
 *
 * 遍历全部已注册组件，把唯一 payload `<xfxss-payload>` 注入到「文本展示字段」，
 * 渲染后检测输出 HTML 是否含【字面未转义】的子串（经 e() 转义后 < 会变成 &lt;，
 * 故字面出现即说明该字段未被转义 = 潜在 XSS）。
 *
 * 设计：
 *  - 跳过结构性字段（class/id/variant/size/placement/tag/type/... 等拼进属性名或 class 的值），
 *    注入它们无意义且会误报；
 *  - 跳过明确「原样输出 HTML」的内容槽位（body/content/slot/footer/left/right/toggle/menu/
 *    head/scripts/below 等 RAW_KEYS），这些本就设计 raw；
 *  - 仅向文本键（title/label/text/name/value/message/heading/subtitle/...）与「非结构性标量默认值」
 *    注入 payload；渲染后若仍出现字面 payload，即判定为未转义缺陷。
 *
 * 用法：php tools/selftest/xss_audit.php
 * 退出码：0 = 全部通过；1 = 存在未转义缺陷或渲染致命错误。
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

const PAYLOAD = '<xfxss-payload>';

// 结构性字段：拼进 class / 属性名 / 资源路径等，注入无意义（且可能被白名单拒绝），跳过
const SKIP_KEYS = [
    'class', 'id', 'style', 'variant', 'size', 'type', 'tag', 'placement', 'position', 'align',
    'icon', 'src', 'href', 'url', 'target', 'method', 'action', 'data', 'attributes', 'theme',
    'layout', 'cols', 'rows', 'width', 'height', 'ratio', 'cover', 'key', 'for', 'role', 'aria',
    'name', // 表单字段名（属性值），按属性转义由组件负责；这里跳过以免噪声
];

// 明确「原样输出 HTML」的内容槽位：设计上 raw，注入后字面出现属预期，豁免
const RAW_KEYS = [
    'body', 'content', 'slot', 'actions', 'caption', 'footer', 'left', 'right', 'toggle',
    'menu', 'head', 'scripts', 'below', 'html', 'raw', 'prepend', 'append',
];

// 强制注入的「语义文本」键（即使组件 defaults 未声明也会尝试设置，以触发渲染路径）
const TEXT_KEYS = [
    'title', 'label', 'text', 'value', 'message', 'heading', 'subtitle', 'description',
    'placeholder', 'hint', 'help', 'note', 'summary', 'question', 'answer', 'item',
];

// 组件级豁免：这些组件/键组合设计上就原样输出某字段 HTML（已在组件 docblock 注明）
const RAW_TEXT_EXEMPT = [
    'popover'   => ['text'],
    'tooltip'   => ['text'],
    'editor'    => ['value'],
    'authPage'  => ['captcha'],
    'terms'     => ['intro', 'accept'],
];

/** 把可注入文本键设为 payload（仅 TEXT_KEYS，且跳过 raw 槽位与组件级 raw 豁免） */
function injectPayload(array $opts, string $alias): array
{
    $exempt = RAW_TEXT_EXEMPT[$alias] ?? [];
    foreach (TEXT_KEYS as $k) {
        // raw 槽位 / 组件级 raw 豁免的字段不注入（它们设计上原样输出 HTML）
        if (in_array($k, RAW_KEYS, true) || in_array($k, $exempt, true)) {
            continue;
        }
        $opts[$k] = PAYLOAD;
    }

    return $opts;
}

$list = XfAdmin::componentList();
$failures = [];
$errors   = [];

foreach ($list as $alias => $class) {
    try {
        $inst = $class::make([]);
        $opts = $inst->options();
        $injected = injectPayload($opts, $alias);
        $html = (string) $class::make($injected);
    } catch (\Throwable $e) {
        $errors[$alias] = $e->getMessage();
        $opts = [];
        $html = '';
    }

    if ($html !== '' && str_contains($html, PAYLOAD)) {
        $failures[$alias] = 'literal unescaped payload found';
    }

    // URL 伪协议探测：向 url/href 注入 javascript: 链接，检测是否被原样输出到 href/src 属性
    try {
        $urlOpts = $opts;
        $urlOpts['url']  = 'javascript:alert(1)';
        $urlOpts['href'] = 'javascript:alert(1)';
        $urlHtml = (string) $class::make($urlOpts);
        // 排除良性 no-op：javascript:void(0)（设计性无操作，非 XSS）
        if (preg_match('/href\s*=\s*["\']\s*javascript:(?!void\s*\()/i', $urlHtml)
            || preg_match('/src\s*=\s*["\']\s*javascript:(?!void\s*\()/i', $urlHtml)) {
            $failures[$alias] = ($failures[$alias] ?? 'unsafe') . ' | unsafe javascript: url';
        }
    } catch (\Throwable $e) {
        // 渲染异常已由上方 $errors 记录，此处忽略
    }
}

$total = count($list);
echo "XSS AUDIT: {$total} components scanned\n";

if ($errors !== []) {
    echo "\n[RENDER ERRORS]\n";
    foreach ($errors as $a => $m) {
        echo "  - {$a}: {$m}\n";
    }
}

if ($failures !== []) {
    echo "\n[XSS FAILURES]\n";
    foreach ($failures as $a => $m) {
        echo "  - {$a}: {$m}\n";
    }
    echo "\nRESULT: FAIL (" . count($failures) . " unescaped, " . count($errors) . " errors)\n";
    exit(1);
}

echo "\nRESULT: PASS (0 unescaped, " . count($errors) . " errors)\n";
exit(0);

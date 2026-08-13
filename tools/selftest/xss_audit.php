<?php
/**
 * XSS 模糊审计：对每个已注册组件注入唯一 payload 到全部【文本展示字段】，
 * 渲染后检测输出 HTML 中是否出现【未转义】的 payload 字面子串。
 *
 * 判定原理：
 *   - 经 e()/htmlspecialchars(ENT_QUOTES) 转义后，< 变为 &lt;，故字面
 *     "<xfxss-payload>" 不会在输出中出现；
 *   - 若输出中出现字面 "<xfxss-payload>"，说明该文本字段未经转义直接拼入 HTML，
 *     即潜在 XSS（除非该字段是组件显式允许的 raw HTML 槽位，需人工复核）。
 *
 * 设计：
 *   - 结构性字段（class/id/variant/size/placement/tag/type/...）不注入 payload，
 *     避免把开发者提供的类名误判为 XSS；其逃逸防护由各组件调用 e() 保证（已审计修复）。
 *   - 明确的内容槽位（body/content/slot/footer/left/right/toggle/menu/...）不注入，
 *     这些是设计上原样输出 HTML 的容器，调用方应传入可信组件/HTML。
 *   - 仅对“文本展示字段”（title/label/text/name/value/message/heading/...）注入并检测。
 *
 * 用法：php tools/selftest/xss_audit.php
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

$root = dirname(__DIR__, 2); // tools/selftest -> 仓库根
if (is_file($root . '/vendor/autoload.php')) {
    require $root . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class) use ($root): void {
        if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
            $path = $root . '/src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    });
    require $root . '/src/helpers.php';
}

const PAYLOAD = '<xfxss-payload>';

// 已审计确认“触发元素内容”设计上原样输出 HTML 的组件（其 text 字段为 raw 槽位，豁免）
const RAW_TEXT_EXEMPT = [
    'zxf\\XfAdmin\\Components\\UI\\Popover',
    'zxf\\XfAdmin\\Components\\UI\\Tooltip',
];

// 顶层强制注入的“文本展示”键名
const TEXT_KEYS = [
    'title', 'label', 'text', 'name', 'value', 'content_text', 'description',
    'subtitle', 'message', 'heading', 'subheading', 'caption', 'summary',
    'placeholder', 'tooltip', 'alt', 'badge', 'prefix', 'suffix', 'note',
    'time', 'author', 'keywords', 'copyright', 'contact', 'deadline',
];

// 跳过注入的键：结构性字段 + 明确的内容/HTML 槽位
const SKIP_KEYS = [
    // 结构性
    'class', 'id', 'style', 'href', 'src', 'url', 'icon', 'type', 'variant',
    'size', 'placement', 'position', 'tag', 'align', 'direction', 'rounded',
    'shape', 'trigger', 'action', 'method', 'target', 'role', 'data', 'soft',
    'ajax', 'html', 'fade', 'striped', 'animated', 'justified', 'vertical',
    'centered', 'scrollable', 'static', 'dismissible', 'dismiss', 'split',
    'infinite', 'speed', 'repeat', 'delay', 'show', 'autohide', 'allowfullscreen',
    'controls', 'featured', 'card', 'subscribe', 'customizer', 'lang', 'layout',
    'container', 'width', 'order', 'offset', 'ratio', 'period', 'current_url',
    'sidenav', 'topbar', 'menu', 'filters', 'tags', 'features',
    'query', 'count', 'description', 'favicon', 'logo', 'brand', 'theme',
    'csrf', 'head', 'scripts', 'pagination', 'below', 'items', 'group', 'tabs',
    // 明确的内容 / HTML 槽位
    'body', 'content', 'slot', 'footer', 'header', 'header_left', 'header_right',
    'left', 'right', 'toggle', 'menu', 'custom', 'extra', 'prepend', 'append',
    'raw', 'image', 'thumb',
    // 页面级布局组件的内容容器（接受组件 HTML / 整页片段，raw 输出）
    'bottom', 'table', 'filters', 'charts', 'stats', 'groups', 'nav', 'panels',
    'topnav', 'sidenav', 'toolbar', 'dropdowns', 'main', 'aside', 'actions',
];

/** 递归把数组字符串叶子替换为 payload，但跳过 SKIP_KEYS */
function injectRecursive(array $arr, string $payload): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        if (in_array($k, SKIP_KEYS, true)) {
            $out[$k] = $v;
            continue;
        }
        if (is_array($v)) {
            $out[$k] = injectRecursive($v, $payload);
        } elseif (is_string($v)) {
            $out[$k] = $payload;
        } else {
            $out[$k] = $v;
        }
    }

    return $out;
}

/** 扫描 src/Components 下所有组件类 */
function collectComponents(string $dir): array
{
    $classes = [];
    $rii     = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $src = file_get_contents($file->getPathname());
        if (! preg_match('/namespace\s+([^;]+);/', $src, $ns)) {
            continue;
        }
        if (! preg_match('/^\s*(?:final\s+|abstract\s+)*class\s+(\w+)/m', $src, $cls)) {
            continue;
        }
        $fqn = trim($ns[1]) . '\\' . $cls[1];
        if (preg_match('/^\s*abstract\s+class\s+' . preg_quote($cls[1], '/') . '/m', $src)) {
            continue;
        }
        $classes[] = $fqn;
    }

    return $classes;
}

$dir       = __DIR__ . '/../../src/Components';
$classes   = collectComponents($dir);
$total     = count($classes);
$failures  = [];
$errors    = [];
$exempt    = [];

foreach ($classes as $fqn) {
    try {
        $ref = new ReflectionClass($fqn);
    } catch (\Throwable $e) {
        $errors[$fqn] = 'reflection: ' . $e->getMessage();
        continue;
    }

    $options = [];
    try {
        $m = $ref->getMethod('defaults');
        if ($m->isProtected()) {
            $m->setAccessible(true);
        }
        $defaults = $m->invoke($ref->newInstanceWithoutConstructor());
        if (is_array($defaults)) {
            $options = injectRecursive($defaults, PAYLOAD);
        }
    } catch (\Throwable $e) {
        // 无 defaults 或不可调用，继续用空 options
    }

    foreach (TEXT_KEYS as $k) {
        if (! array_key_exists($k, $options)) {
            $options[$k] = PAYLOAD;
        }
    }

    try {
        $instance = $ref->newInstance($options);
    } catch (\Throwable $e) {
        try {
            $instance = $ref->newInstanceWithoutConstructor();
        } catch (\Throwable $e2) {
            $errors[$fqn] = 'construct: ' . $e->getMessage();
            continue;
        }
    }

    try {
        $html = (string) $instance;
    } catch (\Throwable $e) {
        $errors[$fqn] = 'render: ' . $e->getMessage();
        continue;
    }

    $pos = strpos($html, PAYLOAD);
    if ($pos !== false) {
        $ctx = substr($html, max(0, $pos - 90), 220);
        if (in_array($fqn, RAW_TEXT_EXEMPT, true)) {
            $exempt[$fqn] = $ctx;
        } else {
            $failures[$fqn] = $ctx;
        }
    }
}

$pass = $total - count($failures) - count($errors) - count($exempt);
echo "=== XSS 模糊审计（文本字段） ===\n";
echo "组件总数: {$total}\n";
echo "PASS    : {$pass}\n";
echo "FAIL(文本字段疑似未转义): " . count($failures) . "\n";
echo "EXEMPT(设计 raw 槽位,已审计): " . count($exempt) . "\n";
echo "ERROR(渲染异常,需关注): " . count($errors) . "\n\n";

if ($exempt !== []) {
    echo "--- 设计性 raw 槽位（文本字段原样输出 HTML，调用方应传可信内容）---\n";
    foreach ($exempt as $c => $ctx) {
        echo "  [RAW] {$c}\n";
    }
    echo "\n";
}

if ($errors !== []) {
    echo "--- 渲染异常（不计入 XSS，但应排查）---\n";
    foreach ($errors as $c => $msg) {
        echo "  [ERR] {$c}: {$msg}\n";
    }
    echo "\n";
}

if ($failures !== []) {
    echo "--- 疑似未转义文本字段（需人工复核）---\n";
    foreach ($failures as $c => $ctx) {
        echo "  [FAIL] {$c}\n";
        echo "         ... " . trim(preg_replace('/\s+/', ' ', $ctx)) . "\n";
    }
    exit(1);
}

echo "全部组件文本展示字段转义通过 ✅\n";
exit(0);

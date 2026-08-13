<?php
// 生成按分类的组件文档（docs/categories/*.md）与模板文档（docs/templates.md）。
// 数据来源于 XfAdmin 实时注册表 + 组件 docblock / assets，保证与代码一致。
declare(strict_types=1);
$base = '/Users/aha/www/xfadmin/src';
spl_autoload_register(function (string $class) use ($base): void {
    if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
        $path = $base . '/' . str_replace('\\', '/', substr($class, 12)) . '.php';
        if (is_file($path)) require $path;
    }
});
require $base . '/helpers.php';
use zxf\XfAdmin\XfAdmin;

// 分类中文名
$catNames = [
    'UI'       => '基础 UI 组件',
    'Data'     => '数据 / 业务组件',
    'Navigation'=> '导航组件',
    'Layout'   => '布局 / 页面模板',
    'Charts'   => '图表组件',
    'Misc'     => '杂项 / 工具组件',
    'Widget'   => '小部件',
];

function extractArr(string $s, int $openPos): string {
    $depth = 0; $len = strlen($s);
    for ($i = $openPos; $i < $len; $i++) {
        $c = $s[$i];
        if ($c === '[') { $depth++; if ($depth === 1) continue; }
        elseif ($c === ']') { $depth--; if ($depth === 0) return substr($s, $openPos, $i - $openPos + 1); }
    }
    return substr($s, $openPos);
}
function meta(string $class): array {
    $f = '/Users/aha/www/xfadmin/src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
    $src = is_file($f) ? file_get_contents($f) : '';
    $out = ['desc' => '', 'assets' => [], 'ns' => ''];
    if (preg_match('#^namespace\s+([^;]+);#m', $src, $m)) {
        $parts = explode('\\', trim($m[1]));
        $out['ns'] = end($parts);
    }
    if (preg_match('#/\*\*(.*?)\*/\\s*(?:final\\s+)?(?:abstract\\s+)?class\\s+\\w+#s', $src, $m)) {
        $doc = trim($m[1]);
        $lines = array_filter(array_map(fn($l) => preg_replace('/^\s*\*\s?/', '', $l), explode("\n", $doc)));
        $desc = [];
        foreach ($lines as $l) {
            if (str_starts_with($l, '@') || str_starts_with($l, '```') || trim($l) === '' || str_starts_with($l, 'XfAdmin::') || str_starts_with($l, 'echo XfAdmin')) break;
            $desc[] = $l;
        }
        $out['desc'] = $desc[0] ?? '';
        if (mb_strlen($out['desc']) > 80) $out['desc'] = mb_substr($out['desc'], 0, 77) . '…';
    }
    if (preg_match('/protected\\s+function\\s+assets\\s*\\(\\)[^{]*\\{/s', $src, $am, PREG_OFFSET_CAPTURE)) {
        $body = extractArr($src, $am[0][1] + strpos(substr($src, $am[0][1]), '['));
        if (preg_match_all("/'([a-zA-Z0-9_-]+)'/", $body, $asm)) {
            $out['assets'] = array_values(array_unique($asm[1]));
        }
    }
    return $out;
}

$list = XfAdmin::componentList();
$byCat = [];
foreach ($list as $alias => $class) {
    $mt = meta($class);
    $cat = $mt['ns'] ?: 'Misc';
    $byCat[$cat][$alias] = ['class' => $class, 'desc' => $mt['desc'], 'assets' => $mt['assets']];
}
ksort($byCat);
@mkdir('/Users/aha/www/xfadmin/docs/categories', 0777, true);

foreach ($byCat as $cat => $items) {
    ksort($items);
    $title = $catNames[$cat] ?? $cat . ' 组件';
    $b = [];
    $b[] = "# {$title}（" . count($items) . " 个）\n";
    $b[] = "> 本文件由 `tools/gen_category_docs.php` 自动生成，数据来自组件注册表与 docblock。完整选项 / 默认值 / 链式方法 / 示例见 [组件详细参考](../components-reference.md)。\n";
    $b[] = "| 组件别名 | 说明 | 依赖资源 | 详细文档 |";
    $b[] = "|----------|------|----------|----------|";
    foreach ($items as $alias => $it) {
        $desc = $it['desc'] ?: '—';
        $assets = $it['assets'] ? '`' . implode('`,`', $it['assets']) . '`' : '—';
        $b[] = "| `{$alias}` | {$desc} | {$assets} | [查看](../components-reference.md#" . strtolower($alias) . ") |";
    }
    $b[] = "";
    file_put_contents("/Users/aha/www/xfadmin/docs/categories/" . strtolower($cat) . ".md", implode("\n", $b));
}
echo "CATEGORY DOCS: " . count($byCat) . " files\n";

// 模板文档（Layout 组里输出完整文档的“页面模板”）
$tplAliases = ['page', 'authPage', 'lockScreen', 'errorPage', 'comingSoon', 'maintenance', 'emptyState', 'landing', 'profilePage'];
$b = [];
$b[] = "# 页面模板（Layout / Templates）\n";
$b[] = "> 页面模板会输出**完整的 HTML 文档**（含 `<!DOCTYPE>`、`<head>`、主题样式与脚本），通常用于独立路由渲染，而非嵌套在其它页面内。完整选项见 [组件详细参考](../components-reference.md)。\n";
$b[] = "";
foreach ($tplAliases as $alias) {
    if (!isset($list[$alias])) continue;
    $mt = meta($list[$alias]);
    $b[] = "## `{$alias}`\n";
    $b[] = $mt['desc'] ? $mt['desc'] . "\n" : "（无描述）\n";
    $b[] = "```php\nXfAdmin::{$alias}([ /* 见组件详细参考 */ ]);\n```\n";
    $b[] = "> 详细选项 / 示例：[组件详细参考#" . strtolower($alias) . "](../components-reference.md#" . strtolower($alias) . ")\n";
}
file_put_contents('/Users/aha/www/xfadmin/docs/templates.md', implode("\n", $b));
echo "TEMPLATES DOC written\n";

<?php
/** 文档覆盖审计：确认每个注册别名都在 03-组件参考 中有条目、示例与参数表 */
require __DIR__ . '/../vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

$list = XfAdmin::componentList();

// 按 "### `别名`" 切分所有章节（避免大正则回溯）
$sections = [];
$cur = null;
foreach (glob(__DIR__ . '/../docs/03-组件参考/*.md') as $f) {
    foreach (file($f, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^### `([^`]+)`$/', $line, $m)) {
            $cur = $m[1];
            $sections[$cur] = '';
            continue;
        }
        if ($cur !== null) {
            $sections[$cur] .= $line . "\n";
        }
    }
}

$missingEntry = $missingParam = $missingExample = [];
foreach (array_keys($list) as $alias) {
    if (! isset($sections[$alias])) {
        $missingEntry[] = $alias;
        continue;
    }
    $sec = $sections[$alias];
    $isAliasNote = str_contains($sec, '的别名，指向同一个组件类');
    if (! $isAliasNote && ! str_contains($sec, '**配置参数**')) {
        $missingParam[] = $alias;
    }
    if (! str_contains($sec, '```php')) {
        $missingExample[] = $alias;
    }
}

$out = '';
$out .= '注册别名总数: ' . count($list) . "\n";
$out .= '文档章节总数: ' . count($sections) . "\n";
$out .= '无文档条目: ' . count($missingEntry) . ($missingEntry ? ' → ' . implode(', ', $missingEntry) : '') . "\n";
$out .= '无参数表: ' . count($missingParam) . ($missingParam ? ' → ' . implode(', ', $missingParam) : '') . "\n";
$out .= '无示例: ' . count($missingExample) . ($missingExample ? ' → ' . implode(', ', $missingExample) : '') . "\n";
$out .= '唯一组件类: ' . count(array_unique(array_values($list))) . "\n";

echo $out;

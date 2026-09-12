<?php

/**
 * 文档内部链接检查器
 *
 * 扫描 docs/ 下所有 markdown 的相对链接，报告失效链接。
 *
 * 用法：php tools/check_docs_links.php
 * 退出码：0 = 全部有效，1 = 存在失效链接
 */

declare(strict_types=1);

$root = dirname(__DIR__) . '/docs';

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$total = 0;
$bad   = [];

foreach ($rii as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'md') {
        continue;
    }
    $content = (string) file_get_contents($file->getPathname());

    // 匹配 ](path) 或 ](path#anchor)
    if (! preg_match_all('/\]\(([^)#\s]+)(#[^)\s]*)?\)/', $content, $m)) {
        continue;
    }
    foreach ($m[1] as $link) {
        if (preg_match('#^(https?:)?//#', $link) || str_starts_with($link, 'mailto:')) {
            continue;      // 外链跳过
        }
        $total++;
        $clean  = rtrim(urldecode($link), '.;,');
        $target = realpath(dirname($file->getPathname()) . '/' . $clean);
        if ($target === false || ! file_exists($target)) {
            $bad[] = str_replace($root . '/', '', $file->getPathname()) . '  →  ' . $link;
        }
    }
}

echo "检查内部链接：{$total} 个\n";
if ($bad !== []) {
    echo '失效链接：' . count($bad) . " 个\n";
    foreach (array_unique($bad) as $b) {
        echo '  ✘ ' . $b . "\n";
    }
    exit(1);
}
echo "全部有效 ✔\n";
exit(0);

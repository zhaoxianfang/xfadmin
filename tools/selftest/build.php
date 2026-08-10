<?php

declare(strict_types=1);

// 渲染全部组件到 .build/ 目录，供 Playwright 运行时自测（固化版，路径可移植）。
// 用法：php tools/selftest/build.php

$root = dirname(__DIR__, 2); // tools/selftest -> 仓库根
$assetsPrefix = '/zxf/xfadmin';

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

use zxf\XfAdmin\XfAdmin;

XfAdmin::config(array_replace(require $root . '/config/xfadmin.php', [
    'assets_url' => $assetsPrefix,
    'version'    => '1.0.0',
]));

$build = __DIR__ . '/.build';
@mkdir($build, 0777, true);

// 布局级组件：本身返回完整页面，单独成页且不并入 all.html
$layoutAliases = [
    'page', 'sidenav', 'topbar', 'pagetitle', 'footer',
    'customizer', 'authpage', 'errorpage', 'comingsoon', 'maintenance',
    'lockscreen', 'landing', 'emptystate',
];

$list = XfAdmin::componentList();
$docIndex = [];
$allSections = [];

foreach ($list as $alias => $class) {
    // 每次组件渲染前重置资源收集器，避免跨组件单例污染导致脚本/样式重复输出
    \zxf\XfAdmin\Assets\Assets::reset();

    try {
        $html = (string) XfAdmin::component($alias, []);
    } catch (\Throwable $e) {
        $html = '<div class="alert alert-danger">RENDER ERROR: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</div>';
    }

    if (in_array(strtolower($alias), $layoutAliases, true)) {
        $page = $html; // 完整页面
    } else {
        $section = '<div class="xf-test-section card mb-3">'
            . '<div class="card-header">'
            . '<code>' . htmlspecialchars($alias, ENT_QUOTES) . '</code> &middot; <span class="text-muted">' . htmlspecialchars($class, ENT_QUOTES) . '</span>'
            . '</div><div class="card-body">' . $html . '</div></div>';
        $page = (string) XfAdmin::page([
            'title'   => 'Component: ' . $alias,
            'content' => $section,
        ]);
        $allSections[] = $section;
    }

    file_put_contents($build . '/doc_' . $alias . '.html', $page);
    $docIndex[$alias] = '/doc/' . $alias;
}

$allPage = (string) XfAdmin::page([
    'title'   => 'All Components Overview',
    'content' => implode("\n", $allSections),
]);

file_put_contents($build . '/all.html', $allPage);
file_put_contents($build . '/index.html', $allPage);
file_put_contents($build . '/doc_index.json', json_encode($docIndex, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo 'Built ' . count($list) . " components into $build\n";

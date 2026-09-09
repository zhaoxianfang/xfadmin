<?php
declare(strict_types=1);

/**
 * 渲染全部已注册组件到 tools/selftest/.build/：
 *   - 每个组件独立页 doc_<alias>.html（含该组件声明的前端资源）
 *   - 总览页 all.html（组件清单 + 链接）
 *   - 索引 doc_index.json
 *
 * 运行：php tools/selftest/build.php
 */

$root = dirname(__DIR__, 2);

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

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\XfAdmin;

XfAdmin::config(array_replace(require $root . '/config/xfadmin.php', [
    'assets_url' => '/zxf/xfadmin',
    'version'    => '1.0.0',
]));

$components = XfAdmin::componentList();

$buildDir = __DIR__ . '/.build';
@mkdir($buildDir, 0777, true);

function pageShell(string $title, string $head, string $body, string $scripts): string
{
    return "<!doctype html>\n<html lang=\"zh-CN\">\n<head>\n<meta charset=\"utf-8\">\n"
        . "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n"
        . "<title>" . htmlspecialchars($title, ENT_QUOTES) . "</title>\n"
        . $head . "\n</head>\n<body style=\"overflow-x:hidden\">\n<div class=\"container-fluid py-4\" style=\"padding-left:1.5rem;padding-right:1.5rem\">\n" . $body . "\n</div>\n"
        . $scripts . "\n</body>\n</html>\n";
}

$built  = [];
$errors = [];
$links  = '';

foreach ($components as $alias => $class) {
    Assets::reset();
    XfAdmin::config(array_replace(require $root . '/config/xfadmin.php', [
        'assets_url' => '/zxf/xfadmin',
        'version'    => '1.0.0',
    ]));
    try {
        $html    = (string) XfAdmin::component($alias, []);
        $head    = Assets::instance()->head();
        $scripts = Assets::instance()->scripts();
    } catch (Throwable $e) {
        $errors[$alias] = $e->getMessage();
        $html    = '<div class="alert alert-danger">render error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</div>';
        $head    = '';
        $scripts = '';
    }
    if (trim($html) === '') {
        $html = '<!-- empty output -->';
    }

    // 布局级组件（page/sidenav/topbar…）本身已返回完整页面，原样写入，不做二次包裹
    if (preg_match('/<html\b|<!doctype/i', $html)) {
        file_put_contents($buildDir . '/doc_' . $alias . '.html', $html);
    } else {
        $doc = pageShell(
            $alias,
            $head,
            '<h4 class="mb-3 text-capitalize">' . htmlspecialchars($alias, ENT_QUOTES) . '</h4>' . $html,
            $scripts
        );
        file_put_contents($buildDir . '/doc_' . $alias . '.html', $doc);
    }

    $built[] = $alias;
    $links  .= '<a class="btn btn-sm btn-outline-secondary m-1" href="/doc/' . rawurlencode($alias) . '">'
        . htmlspecialchars($alias, ENT_QUOTES) . '</a>';
}

$all = pageShell(
    'All Components',
    Assets::instance()->head(),
    '<h1 class="mb-4">XfAdmin 组件总览（' . count($built) . '）</h1><div>' . $links . '</div>',
    Assets::instance()->scripts()
);
file_put_contents($buildDir . '/all.html', $all);

file_put_contents($buildDir . '/doc_index.json', json_encode(
    ['count' => count($built), 'aliases' => $built, 'errors' => $errors],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
));

echo 'Built ' . count($built) . ' components, render errors=' . count($errors) . "\n";
if ($errors) {
    echo "render errors:\n";
    foreach ($errors as $a => $m) {
        echo "  - {$a}: {$m}\n";
    }
}
exit($errors ? 1 : 0);

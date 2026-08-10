<?php

declare(strict_types=1);

// 演示资源自托管：把 /zxf/xfadmin/* 直接映射到 resources/assets，
// 无需执行发布命令即可在浏览器中正常加载 CSS/JS（修复全部 404）。
$__xfAssetPrefix = '/zxf/xfadmin';
$__xfUri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (str_starts_with($__xfUri, $__xfAssetPrefix . '/')) {
    $__xfRel  = ltrim(substr($__xfUri, strlen($__xfAssetPrefix) + 1), '/');
    $__xfFile = __DIR__ . '/../resources/assets/' . $__xfRel;
    // 扩展名白名单（未知扩展一律 404）+ realpath 包含性校验（防越界读取）
    $__xfMime = match (strtolower(pathinfo($__xfFile, PATHINFO_EXTENSION))) {
        'css'  => 'text/css; charset=utf-8',
        'js', 'mjs' => 'text/javascript; charset=utf-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'json', 'map' => 'application/json',
        'pdf'  => 'application/pdf',
        default => null,
    };
    $__xfBase = realpath(__DIR__ . '/../resources/assets');
    $__xfReal = realpath($__xfFile);
    if ($__xfMime !== null
        && ! str_contains($__xfRel, '..')
        && $__xfBase !== false && $__xfReal !== false
        && str_starts_with($__xfReal, $__xfBase . DIRECTORY_SEPARATOR)
        && is_file($__xfReal)
    ) {
        header('Content-Type: ' . $__xfMime);
        header('Cache-Control: public, max-age=3600');
        readfile($__xfReal);
        exit;
    }
    http_response_code(404);
    exit;
}

// 无需 composer install 也可运行的演示自动加载
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        if (str_starts_with($class, 'zxf\XfAdmin\\')) {
            $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    });
    require __DIR__ . '/../src/helpers.php';
}

use zxf\XfAdmin\XfAdmin;

XfAdmin::config(array_replace(require __DIR__ . '/../config/xfadmin.php', [
    'assets_url' => $__xfAssetPrefix,
    'version'    => '1.0.0',
]));

$menu = [
    ['title' => '导航'],
    ['text' => '仪表盘', 'icon' => 'ti ti-layout-dashboard', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
    ['text' => '组件展示', 'icon' => 'ti ti-components', 'url' => '/widgets'],
    ['text' => '业务应用', 'icon' => 'ti ti-apps', 'url' => '/apps'],
    ['text' => '顶部导航', 'icon' => 'ti ti-layout-navbar', 'url' => '/topnav'],
    ['text' => '落地页', 'icon' => 'ti ti-world', 'url' => '/landing'],
    ['text' => '表格与数据', 'icon' => 'ti ti-table', 'url' => '/tables'],
    ['text' => '表单', 'icon' => 'ti ti-forms', 'url' => '/forms'],
    ['text' => '图表', 'icon' => 'ti ti-chart-bar', 'url' => '/charts'],
    ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
        ['text' => '用户管理', 'url' => '/widgets'],
        ['text' => '角色权限', 'url' => '/widgets'],
        ['text' => '更多', 'children' => [
            ['text' => '深层菜单', 'url' => '/widgets'],
            ['text' => '更深一层', 'url' => '/widgets'],
        ]],
    ]],
    ['text' => '登录页', 'icon' => 'ti ti-lock', 'url' => '/login'],
    ['text' => '404', 'icon' => 'ti ti-alert-triangle', 'url' => '/404'],
];

$route = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$route = $route === '' ? 'home' : $route;

$user = ['name' => '张三', 'role' => '超级管理员', 'items' => [
    ['text' => '个人资料', 'icon' => 'ti ti-user', 'url' => '#'],
    ['text' => '设置', 'icon' => 'ti ti-settings', 'url' => '#'],
    ['divider' => true],
    ['text' => '退出登录', 'icon' => 'ti ti-logout', 'url' => '/login'],
]];

match ($route) {
    'home'    => require __DIR__ . '/pages/home.php',
    'apps'    => require __DIR__ . '/pages/apps.php',
    'landing' => require __DIR__ . '/pages/landing.php',
    'topnav'  => require __DIR__ . '/pages/topnav.php',
    'widgets' => require __DIR__ . '/pages/widgets.php',
    'tables'  => require __DIR__ . '/pages/tables.php',
    'forms'   => require __DIR__ . '/pages/forms.php',
    'charts'  => require __DIR__ . '/pages/charts.php',
    'login'   => require __DIR__ . '/pages/login.php',
    '404'     => require __DIR__ . '/pages/error404.php',
    default   => require __DIR__ . '/pages/error404.php',
};

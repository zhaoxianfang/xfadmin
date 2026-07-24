<?php

declare(strict_types=1);

// 演示资源自托管：把 /zxf/xfadmin/* 直接映射到 resources/assets，
// 无需执行发布命令即可在浏览器中正常加载 CSS/JS（修复全部 404）。
$__xfAssetPrefix = '/zxf/xfadmin';
$__xfUri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (str_starts_with($__xfUri, $__xfAssetPrefix . '/')) {
    $__xfRel  = ltrim(substr($__xfUri, strlen($__xfAssetPrefix) + 1), '/');
    $__xfFile = __DIR__ . '/../resources/assets/' . $__xfRel;
    if (is_file($__xfFile) && ! str_contains($__xfRel, '..')) {
        $__xfMime = match (pathinfo($__xfFile, PATHINFO_EXTENSION)) {
            'css'  => 'text/css; charset=utf-8',
            'js'   => 'text/javascript; charset=utf-8',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'ico'  => 'image/x-icon',
            'woff', 'woff2' => 'font/woff2',
            'ttf'  => 'font/ttf',
            'json' => 'application/json',
            'map'  => 'application/json',
            default => 'application/octet-stream',
        };
        header('Content-Type: ' . $__xfMime);
        header('Cache-Control: public, max-age=3600');
        readfile($__xfFile);
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
    'widgets' => require __DIR__ . '/pages/widgets.php',
    'tables'  => require __DIR__ . '/pages/tables.php',
    'forms'   => require __DIR__ . '/pages/forms.php',
    'charts'  => require __DIR__ . '/pages/charts.php',
    'login'   => require __DIR__ . '/pages/login.php',
    '404'     => require __DIR__ . '/pages/error404.php',
    default   => require __DIR__ . '/pages/error404.php',
};

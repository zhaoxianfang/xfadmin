<?php
/**
 * 自测路由：
 *   /zxf/xfadmin/*          => resources/assets（无需发布资源）
 *   /doc/<alias>            => .build/doc_<alias>.html（单组件独立页）
 *   / 或 /all 或 /index.html => .build/all.html（总览页）
 *
 * 运行：php -S 127.0.0.1:8901 tools/selftest/router.php
 */

$root        = dirname(__DIR__, 2);
$uri         = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$assetPrefix = '/zxf/xfadmin';
$assetRoot   = $root . '/resources/assets';

if (str_starts_with($uri, $assetPrefix . '/')) {
    $rel  = ltrim(substr($uri, strlen($assetPrefix) + 1), '/');
    $file = $assetRoot . '/' . $rel;
    $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
        'css'          => 'text/css; charset=utf-8',
        'js', 'mjs'    => 'text/javascript; charset=utf-8',
        'svg'          => 'image/svg+xml',
        'png'          => 'image/png',
        'jpg', 'jpeg'  => 'image/jpeg',
        'gif'          => 'image/gif',
        'ico'          => 'image/x-icon',
        'webp'         => 'image/webp',
        'woff'         => 'font/woff',
        'woff2'        => 'font/woff2',
        'ttf'          => 'font/ttf',
        'json', 'map'  => 'application/json',
        'pdf'          => 'application/pdf',
        default        => null,
    };
    $realBase = realpath($assetRoot);
    $realFile = realpath($file);
    if ($mime !== null
        && ! str_contains($rel, '..')
        && $realBase !== false && $realFile !== false
        && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)
        && is_file($realFile)
    ) {
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        readfile($realFile);
        exit;
    }
    http_response_code(404);
    exit;
}

if (str_starts_with($uri, '/doc/')) {
    $alias = basename($uri);
    $file  = __DIR__ . '/.build/doc_' . basename($alias) . '.html';
    if (is_file($file)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($file);
        exit;
    }
    http_response_code(404);
    echo 'component not built: ' . htmlspecialchars($alias, ENT_QUOTES);
    exit;
}

if ($uri === '/' || $uri === '/all' || $uri === '/index.html') {
    $file = __DIR__ . '/.build/all.html';
    if (is_file($file)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($file);
        exit;
    }
}

http_response_code(404);
echo 'not found';

<?php

declare(strict_types=1);

// 自测路由器：
//   /zxf/xfadmin/*  -> resources/assets （无需发布即可加载 CSS/JS）
//   /              -> .build/index.html （全部组件总览）
//   /doc/<alias>   -> .build/doc_<alias>.html （单组件页）
//   /doc_index.json-> .build/doc_index.json
// 用法：php -S 127.0.0.1:8901 tools/selftest/router.php

$root   = dirname(__DIR__, 2);
$assets = $root . '/resources/assets';
$build  = __DIR__ . '/.build';
$prefix = '/zxf/xfadmin';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if (str_starts_with($uri, $prefix . '/')) {
    $rel  = ltrim(substr($uri, strlen($prefix) + 1), '/');
    $file = $assets . '/' . $rel;
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
    $realBase = realpath($assets);
    $realFile = realpath($file);
    if ($mime !== null && ! str_contains($rel, '..') && $realBase !== false && $realFile !== false
        && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR) && is_file($realFile)) {
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        readfile($realFile);
        exit;
    }
    http_response_code(404);
    exit;
}

if ($uri === '/' || $uri === '') {
    $f = $build . '/index.html';
    if (is_file($f)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($f);
        exit;
    }
    http_response_code(404);
    echo 'not built, run: php tools/selftest/build.php';
    exit;
}

if (preg_match('#^/doc/([A-Za-z0-9_]+)$#', $uri, $m)) {
    $f = $build . '/doc_' . $m[1] . '.html';
    if (is_file($f)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($f);
        exit;
    }
    http_response_code(404);
    echo 'no doc: ' . $m[1];
    exit;
}

if ($uri === '/doc_index.json') {
    $f = $build . '/doc_index.json';
    if (is_file($f)) {
        header('Content-Type: application/json');
        readfile($f);
        exit;
    }
    http_response_code(404);
    echo '{}';
    exit;
}

http_response_code(404);
echo '404';

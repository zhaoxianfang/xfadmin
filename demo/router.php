<?php

/**
 * PHP 内置服务器路由器：把 /vendor/xfadmin/* 指向 resources/assets，
 * 其余请求交给 demo/index.php 处理。
 *
 * 运行：php -S 127.0.0.1:8900 demo/router.php
 */

$uri    = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$assets = __DIR__ . '/../resources/assets';

if (str_starts_with($uri, '/vendor/xfadmin/')) {
    $rel  = ltrim(substr($uri, strlen('/vendor/xfadmin/')), '/');
    $file = $assets . '/' . $rel;
    if (is_file($file)) {
        $mime = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'css'  => 'text/css',
            'js'   => 'text/javascript',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'ico'  => 'image/x-icon',
            'woff', 'woff2' => 'font/woff2',
            'ttf'  => 'font/ttf',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

require __DIR__ . '/index.php';

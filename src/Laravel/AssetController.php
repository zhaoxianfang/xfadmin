<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Laravel;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * 资源自动托管控制器（无需发布静态资源即可使用）
 *
 * 当宿主项目未执行 `php artisan vendor:publish --tag=xfadmin-assets` 时，
 * 把 `assets_url` 前缀（默认 /zxf/xfadmin）下的请求直接映射到本包
 * `resources/assets` 目录并流式返回，实现「开箱即用」，行为与 demo/index.php 一致。
 *
 * 设计要点（参照 zxf/trace 的 AssetController 实现）：
 * - 采用「控制器方法」而非「闭包路由」提供资源，确保 `php artisan route:cache` /
 *   `php artisan optimize` 能安全序列化路由（闭包路由会隐式绑定服务提供者持有的
 *   整个应用容器，序列化时递归展开导致内存耗尽）。
 * - 仅允许静态资源扩展名（白名单），杜绝 .php 等被当作资源输出。
 * - 路径穿越防护：realpath 后必须位于资源根目录内。
 * - ETag + 304 Not Modified：命中客户端缓存时直接返回 304，零流量。
 * - Cache-Control / Expires 1 年，配合 ?v= 版本号做缓存刷新。
 */
class AssetController extends Controller
{
    /**
     * 允许的静态资源 MIME 映射（白名单）
     */
    private const MIME_TYPES = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'text/javascript; charset=utf-8',
        'mjs'   => 'text/javascript; charset=utf-8',
        'map'   => 'application/json',
        'json'  => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'avif'  => 'image/avif',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'otf'   => 'font/otf',
    ];

    /**
     * 路由动作：流式返回包内 resources/assets 下的静态资源
     *
     * @param  string  $path  相对资源根目录的路径（如 js/xfadmin.js）
     */
    public function serve(string $path): SymfonyResponse
    {
        $base = __DIR__ . '/../../resources/assets';
        $realBase = realpath($base);

        if ($realBase === false) {
            abort(404);
        }

        // 扩展名白名单：非静态资源（如 .php）一律拒绝
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! isset(self::MIME_TYPES[$extension])) {
            abort(404);
        }

        // 路径穿越防护：文件必须真实存在、可读，且位于资源根目录内
        $realFile = realpath($base . '/' . ltrim($path, '/'));
        if ($realFile === false
            || ! str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)
            || ! is_file($realFile)
            || ! is_readable($realFile)
        ) {
            abort(404);
        }

        $contentType = self::MIME_TYPES[$extension];
        $etag = md5($realFile . ':' . filemtime($realFile));

        $headers = [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires'       => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
            'ETag'          => $etag,
        ];

        // 客户端已缓存且未变更 -> 304 Not Modified（零流量）
        if (request()->header('If-None-Match') === $etag) {
            return new Response('', 304, $headers);
        }

        return response()->file($realFile, $headers);
    }

    /** 供 ServiceProvider 注册路由时使用的前缀（去掉首斜杠） */
    public static function prefix(): string
    {
        return ltrim((string) \Illuminate\Support\Facades\Config::get('xfadmin.assets_url', '/zxf/xfadmin'), '/');
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use zxf\XfAdmin\XfAdmin;

/**
 * Laravel 11+ 服务提供者
 *
 * 发布资源：
 *   php artisan vendor:publish --tag=xfadmin-assets
 *   php artisan vendor:publish --tag=xfadmin-config
 *
 * Blade 指令：
 *   @xfHead                      —— <head> 中输出 CSS
 *   @xfScripts                   —— </body> 前输出 JS
 *   @xf('card', ['title'=>'x'])  —— 渲染任意组件
 */
class XfAdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/xfadmin.php', 'xfadmin');

        $this->app->singleton('xfadmin', function ($app) {
            XfAdmin::config($app['config']->get('xfadmin', []));

            return new XfAdmin();
        });
    }

    public function boot(): void
    {
        // 应用配置（未解析 singleton 时也生效）
        XfAdmin::config($this->app['config']->get('xfadmin', []));

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../resources/assets' => public_path('zxf/xfadmin'),
            ], 'xfadmin-assets');

            $this->publishes([
                __DIR__ . '/../../config/xfadmin.php' => config_path('xfadmin.php'),
            ], 'xfadmin-config');
        }

        // 资源自动托管：未发布（或未完全发布）资源时，把 `assets_url` 前缀下的请求直接映射到
        // 包内 resources/assets 流式返回，开箱即用（与 demo/index.php 行为一致）。
        // 发布到 public 后由 Web 服务器直接服务，本路由不会命中；缺失文件自动回退。
        // 仅在本地前缀时注册（远程 CDN 前缀无需、也不能自托管）。
        // 路由层 + 控制器层双重限制扩展名白名单，杜绝 .php 等被当作资源输出。
        $prefix = AssetController::prefix();
        if ($prefix !== '' && ! preg_match('#^https?://#', $prefix)) {
            \Illuminate\Support\Facades\Route::get($prefix . '/{path}', [AssetController::class, 'serve'])
                ->where('path', '(?i).*\.(css|js|mjs|map|json|svg|png|jpe?g|gif|ico|webp|avif|woff2?|ttf|eot|otf)$')
                ->name('xfadmin.assets');
        }

        Blade::directive('xfHead', fn () => "<?php echo \\zxf\XfAdmin\\XfAdmin::head(); ?>");
        Blade::directive('xfScripts', fn () => "<?php echo \\zxf\XfAdmin\\XfAdmin::scripts(); ?>");
        Blade::directive('xf', fn ($expression) => "<?php echo \\zxf\XfAdmin\\XfAdmin::component({$expression}); ?>");
    }
}

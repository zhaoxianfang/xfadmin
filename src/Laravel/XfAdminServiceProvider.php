<?php

declare(strict_types=1);

namespace XfAdmin\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use XfAdmin\XfAdmin;

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
                __DIR__ . '/../../resources/assets' => public_path('vendor/xfadmin'),
            ], 'xfadmin-assets');

            $this->publishes([
                __DIR__ . '/../../config/xfadmin.php' => config_path('xfadmin.php'),
            ], 'xfadmin-config');
        }

        Blade::directive('xfHead', fn () => "<?php echo \\XfAdmin\\XfAdmin::head(); ?>");
        Blade::directive('xfScripts', fn () => "<?php echo \\XfAdmin\\XfAdmin::scripts(); ?>");
        Blade::directive('xf', fn ($expression) => "<?php echo \\XfAdmin\\XfAdmin::component({$expression}); ?>");
    }
}

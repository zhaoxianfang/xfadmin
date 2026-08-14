<?php

declare(strict_types=1);

namespace zxf\XfAdmin\ThinkPHP;

use think\Service as BaseService;
use zxf\XfAdmin\XfAdmin;

/**
 * ThinkPHP 8+ 服务
 *
 * composer require zxf/xfadmin 后自动注册（extra.think.services）。
 * 发布静态资源：php think xfadmin:publish
 */
class Service extends BaseService
{
    /**
     * register（public实例方法）
     *
     * @return void result
     */
    public function register(): void
    {
        $this->app->bind('xfadmin', XfAdmin::class);

        // 注册全局助手函数 xfadmin()，与 Laravel 门面 XfAdmin:: 平行
        if (! function_exists('xfadmin')) {
            /**
             * XfAdmin 全局助手
             *
             * xfadmin('card', ['title' => '标题'])  => Component
             * xfadmin()                             => XfAdmin::class（用于静态调用）
             */
            function xfadmin(?string $component = null, array $options = []): Component|string
            {
                if ($component === null) {
                    return XfAdmin::class;
                }
                return XfAdmin::component($component, $options);
            }
        }
    }

    /**
     * boot（public实例方法）
     *
     * @return void result
     */
    public function boot(): void
    {
        // 读取 config/xfadmin.php（ThinkPHP 会将 extra.think.config 复制到应用 config）
        $config = [];
        if (function_exists('config')) {
            $config = (array) config('xfadmin');
        }
        if ($config === []) {
            $config = require __DIR__ . '/../../config/xfadmin.php';
        }
        XfAdmin::config($config);

        $this->commands([
            PublishCommand::class,
        ]);
    }
}

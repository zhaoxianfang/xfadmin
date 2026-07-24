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
    public function register(): void
    {
        $this->app->bind('xfadmin', XfAdmin::class);
    }

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

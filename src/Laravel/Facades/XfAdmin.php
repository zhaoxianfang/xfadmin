<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \zxf\XfAdmin\XfAdmin
 */
class XfAdmin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'xfadmin';
    }

    public static function __callStatic($method, $args)
    {
        // 全部转发到静态工厂（组件均为无状态工厂方法）
        return \zxf\XfAdmin\XfAdmin::$method(...$args);
    }
}

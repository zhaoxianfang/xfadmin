<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \zxf\XfAdmin\XfAdmin
 */
class XfAdmin extends Facade
{
    /**
     * get Facade Accessor（protected静态方法）
     *
     * @return string result
     */
    protected static function getFacadeAccessor(): string
    {
        return 'xfadmin';
    }

    /**
     * call Static（public静态方法）
     *
     * @param mixed $method method
     * @param mixed $args args
     *
     * @return mixed 渲染结果 / 组件实例或配置
     */
    public static function __callStatic($method, $args)
    {
        // 全部转发到静态工厂（组件均为无状态工厂方法）
        return \zxf\XfAdmin\XfAdmin::$method(...$args);
    }
}

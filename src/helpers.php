<?php

declare(strict_types=1);

use XfAdmin\Components\Component;
use XfAdmin\XfAdmin;

if (! function_exists('xf_admin')) {
    /**
     * 创建 XfAdmin 组件
     *
     * xf_admin('card', ['title' => '标题'])   => Component
     * xf_admin()                              => XfAdmin::class（用于静态调用）
     */
    function xf_admin(?string $component = null, array $options = []): Component|string
    {
        if ($component === null) {
            return XfAdmin::class;
        }

        return XfAdmin::component($component, $options);
    }
}

if (! function_exists('xf_asset')) {
    /** XfAdmin 资源 URL */
    function xf_asset(string $path): string
    {
        return XfAdmin::asset($path);
    }
}

if (! function_exists('xf_head')) {
    /** <head> 内输出 CSS/主题配置 */
    function xf_head(): string
    {
        return XfAdmin::head();
    }
}

if (! function_exists('xf_scripts')) {
    /** </body> 前输出 JS */
    function xf_scripts(): string
    {
        return XfAdmin::scripts();
    }
}

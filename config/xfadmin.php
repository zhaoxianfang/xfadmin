<?php

/**
 * XfAdmin 配置
 */
return [
    // 静态资源基础 URL（发布后指向 public 下的目录）
    // Laravel: php artisan vendor:publish --tag=xfadmin-assets  => public/zxf/xfadmin
    // ThinkPHP: php think xfadmin:publish                       => public/zxf/xfadmin
    // 也可指向远程 CDN，例如 https://cdn.example.com/xfadmin
    'assets_url' => '/zxf/xfadmin',

    // 资源版本号（附加 ?v= 用于浏览器缓存刷新）
    'version' => '1.0.0',

    // 主题默认外观（渲染到 <html> 的 data-* 属性，运行时可被浏览器端 config.js 持久化覆盖）
    //
    // 注意：以下默认值与 INSPINIA v4.1.0 模板 assets/js/config.js 中的 defaultConfig
    // 保持一致（skin=modern、menu.color=gradient），确保开箱即用的观感与模板完全相同。
    // 修改前请同步核对模板 config.js，避免出现“与模板不一致”的外观偏差。
    'theme' => [
        'skin'            => 'modern',    // classic | material | modern | saas | flat
        'mode'            => 'light',     // light | dark | system
        'layout'          => 'vertical',  // vertical | horizontal | dual
        'layout_position' => 'fixed',     // fixed | scrollable
        'layout_width'    => 'fluid',     // fluid | boxed
        'topbar_color'    => 'light',     // light | dark | gray | gradient
        'menu_color'      => 'gradient',  // light | dark | gray | gradient | image
        'sidenav_size'    => 'default',   // default | compact | condensed | on-hover | on-hover-active | offcanvas | full | fullscreen
        'sidenav_user'    => true,        // 侧边栏是否显示用户卡片
    ],

    // 品牌信息
    'brand' => [
        'name'        => 'XfAdmin',
        'logo'        => null,        // 大 logo（浅色背景）为 null 时使用包内默认
        'logo_dark'   => null,        // 大 logo（深色背景）
        'logo_sm'     => null,        // 小 logo
        'favicon'     => null,
        'home_url'    => '/',
    ],

    // 页脚
    'footer' => [
        'text'  => null,              // null 时自动生成版权文案
        'right' => null,
    ],
];

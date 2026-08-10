<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

// 落地页组件返回完整页面（Stringable），与 XfAdmin::page() 用法一致，可直接 echo / return。
echo XfAdmin::landing([
    'brand' => 'XfAdmin',
    'nav'   => [
        ['text' => '功能', 'url' => '#features'],
        ['text' => '价格', 'url' => '#pricing'],
        ['text' => '文档', 'url' => '/'],
    ],
    'hero' => [
        'title'    => '企业级后台框架',
        'subtitle' => '基于 Bootstrap + INSPINIA 风格，156 个即用组件，开箱即用的管理后台。',
        'primary'  => '立即体验',
        'secondary'=> '查看文档',
        'image'    => 'gallery/1.jpg',
    ],
    'stats' => [
        ['value' => '156+', 'label' => '可用组件'],
        ['value' => '100%', 'label' => '服务端渲染'],
        ['value' => '0', 'label' => '构建依赖'],
    ],
    'features' => [
        ['icon' => 'ti ti-bolt', 'title' => '极速', 'text' => '纯 PHP 组件，无需编译，即写即渲染。'],
        ['icon' => 'ti ti-layout', 'title' => '布局丰富', 'text' => '侧边栏、顶栏、栅格、卡片、表格一应俱全。'],
        ['icon' => 'ti ti-palette', 'title' => '主题可定制', 'text' => '明暗主题与配色面板一键切换。'],
    ],
    'pricing' => [
        ['title' => '开源版', 'price' => '免费', 'features' => ['全部组件', '社区支持'], 'button' => '开始使用'],
        ['title' => '专业版', 'price' => '$99', 'features' => ['优先支持', '定制开发'], 'highlight' => true, 'button' => '选择专业版'],
    ],
    'testimonials' => [
        ['name' => '张三', 'role' => 'CTO', 'avatar' => 'users/user-1.jpg', 'text' => '这套后台极大提升了运营效率。'],
    ],
    'footer' => [
        'text'  => '© 2026 XfAdmin',
        'links' => [['text' => '关于', 'url' => '#'], ['text' => '联系', 'url' => '#']],
    ],
]);

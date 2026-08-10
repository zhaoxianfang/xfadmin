<?php

declare(strict_types=1);

use zxf\XfAdmin\Support\DemoMenu;
use zxf\XfAdmin\XfAdmin;

/**
 * TopNav 顶部水平导航演示
 *
 * 来源：INSPINIA v4.0 layouts-horizontal.html 的 header.app-topbar + header.topnav
 * 两个 DOM 合并封装；水平菜单位于品牌 Logo 右侧，右端为语言 / 邮件 / 通知 /
 * 主题 / 用户头像工具区。
 */

$tools = DemoMenu::topbarTools('#!');

echo XfAdmin::page([
    'title'       => '顶部导航 TopNav - XfAdmin Demo',
    'layout'      => 'horizontal',
    'current_url' => '/topnav',
    'menu'        => DemoMenu::topNavMenu('#!'),
    'topnav'      => [
        'search'        => true,
        'languages'     => $tools['languages'],
        'messages'      => $tools['messages'],
        'notifications' => $tools['notifications'],
        'user'          => $tools['user'],
    ],
    'page_title'  => [
        'title'      => '顶部导航 TopNav',
        'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '顶部导航']],
    ],
    'content'     => [
        XfAdmin::alert([
            'variant'     => 'info',
            'icon'        => 'ti ti-info-circle',
            'text'        => '本页布局为 layout=horizontal：侧边栏被移除，水平菜单内联在顶栏品牌右侧。'
                . '桌面端悬停展开多级子菜单（支持 5 级），窄屏折叠为手风琴面板。',
            'dismissible' => true,
        ]),

        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '组件构成',
                'body'  => '<ul class="mb-0 ps-3">'
                    . '<li>品牌 Logo（亮/暗双版本，含小屏 logo-sm）</li>'
                    . '<li>水平菜单：无限级下拉 + Mega 大面板 + 徽标 / 分组标题 / 分隔线</li>'
                    . '<li>全局搜索框（≥xl 显示）</li>'
                    . '<li>右侧工具区：语言切换、邮件、通知、主题定制、全屏、明暗模式、用户卡片</li>'
                    . '<li>窄屏折叠按钮 <code>.topnav-toggle-button</code></li>'
                    . '</ul>',
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '交互要点',
                'body'  => '<ul class="mb-0 ps-3">'
                    . '<li>桌面端（≥992px）：CSS <code>:hover</code> 级联展开，子面板逐级向右浮出</li>'
                    . '<li>边界避让：右侧空间不足时子面板自动向左翻转</li>'
                    . '<li>移动端（&lt;992px）：菜单收进折叠面板，点击父项手风琴展开，同级互斥</li>'
                    . '<li>视口切换时自动清理展开态，避免样式残留</li>'
                    . '</ul>',
            ])],
        ]]),

        XfAdmin::card([
            'title' => '使用方式',
            'body'  => XfAdmin::codeBlock([
                'language' => 'php',
                'code'     => <<<'PHP'
// 方式一：整页水平布局（推荐）—— Page 自动渲染 TopNav，不再输出侧边栏
echo XfAdmin::page([
    'layout'      => 'horizontal',
    'menu'        => $menu,          // 水平菜单数据
    'current_url' => '/topnav',
    'topnav'      => [
        'search'        => true,
        'languages'     => [...],
        'messages'      => ['count' => 4, 'items' => [...]],
        'notifications' => ['count' => 3, 'items' => [...]],
        'user'          => ['name' => '张三', 'avatar' => '...', 'items' => [...]],
    ],
    'content'     => $content,
]);

// 方式二：单独渲染组件
echo XfAdmin::topNav([
    'menu' => [
        ['text' => '仪表盘', 'icon' => 'ti ti-dashboard', 'url' => '/'],
        ['text' => '应用', 'children' => [
            ['text' => '日历', 'url' => '/calendar'],
            ['text' => '邮箱', 'children' => [           // 无限级嵌套
                ['text' => '收件箱', 'url' => '/inbox'],
            ]],
        ]],
        ['text' => '电商', 'mega' => [                    // Mega 大面板
            'cols'    => 3,
            'title'   => '电商中心',
            'columns' => [
                ['title' => '商品', 'items' => [['text' => '商品列表', 'url' => '#']]],
            ],
        ]],
    ],
]);
PHP,
            ]),
        ]),

        XfAdmin::card([
            'title' => '演示内容区',
            'body'  => XfAdmin::row(['gutter' => 3, 'cols' => [
                ['width' => ['md' => 3], 'content' => XfAdmin::widget([
                    'title' => '总访问量', 'value' => '128,430', 'icon' => 'ti ti-eye', 'variant' => 'primary',
                ])],
                ['width' => ['md' => 3], 'content' => XfAdmin::widget([
                    'title' => '订单数', 'value' => '3,254', 'icon' => 'ti ti-shopping-cart', 'variant' => 'success',
                ])],
                ['width' => ['md' => 3], 'content' => XfAdmin::widget([
                    'title' => '新增用户', 'value' => '892', 'icon' => 'ti ti-user-plus', 'variant' => 'info',
                ])],
                ['width' => ['md' => 3], 'content' => XfAdmin::widget([
                    'title' => '营收', 'value' => '¥ 86,420', 'icon' => 'ti ti-currency-yuan', 'variant' => 'warning',
                ])],
            ]]),
        ]),
    ],
]);

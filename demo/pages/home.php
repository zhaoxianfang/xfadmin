<?php

declare(strict_types=1);

use XfAdmin\XfAdmin;

echo XfAdmin::page([
    'title'       => '仪表盘 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/',
    'sidenav'     => ['user' => $user + ['avatar' => xf_asset('images/users/user-2.jpg')]],
    'topbar'      => [
        'user'          => $user + ['avatar' => xf_asset('images/users/user-2.jpg')],
        'notifications' => [
            'count' => 3,
            'items' => [
                ['title' => '系统', 'text' => '您有 3 笔新订单', 'icon' => 'ti ti-shopping-cart', 'variant' => 'primary', 'time' => '2 分钟前'],
                ['title' => '安全', 'text' => '异地登录提醒', 'icon' => 'ti ti-shield-lock', 'variant' => 'danger', 'time' => '1 小时前'],
            ],
            'all_url' => '#',
        ],
    ],
    'page_title' => ['title' => '仪表盘', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '仪表盘']]],
    'content'    => [
        // 统计卡片
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['md' => 6, 'xl' => 3], 'content' => XfAdmin::statCard(['title' => '总用户', 'counter' => 12480, 'icon' => 'ti ti-users', 'variant' => 'primary', 'trend' => ['text' => '+12.5%', 'direction' => 'up', 'label' => '较上周']])],
            ['width' => ['md' => 6, 'xl' => 3], 'content' => XfAdmin::statCard(['title' => '订单量', 'counter' => 3843, 'icon' => 'ti ti-shopping-cart', 'variant' => 'success', 'trend' => ['text' => '+3.2%', 'direction' => 'up', 'label' => '较昨日']])],
            ['width' => ['md' => 6, 'xl' => 3], 'content' => XfAdmin::statCard(['title' => '销售额', 'counter' => 89540, 'prefix' => '￥', 'icon' => 'ti ti-currency-yen', 'variant' => 'warning', 'trend' => ['text' => '-1.8%', 'direction' => 'down', 'label' => '较上月']])],
            ['width' => ['md' => 6, 'xl' => 3], 'content' => XfAdmin::statCard(['title' => '退款单', 'counter' => 96, 'icon' => 'ti ti-receipt-refund', 'variant' => 'danger', 'trend' => ['text' => '+0.4%', 'direction' => 'up', 'label' => '较上周']])],
        ]]),
        // 图表 + 时间线
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 8], 'content' => XfAdmin::card([
                'title' => '销售趋势',
                'tools' => ['collapse', 'refresh', 'close'],
                'body'  => XfAdmin::apexChart([
                    'type'   => 'area',
                    'height' => 320,
                    'series' => [
                        ['name' => '销售额', 'data' => [31, 40, 28, 51, 42, 82, 56]],
                        ['name' => '订单量', 'data' => [11, 32, 45, 32, 34, 52, 41]],
                    ],
                    'labels'  => ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                    'options' => ['stroke' => ['curve' => 'smooth'], 'dataLabels' => ['enabled' => false]],
                ]),
            ])],
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => '最近动态',
                'body'  => XfAdmin::timeline(['items' => [
                    ['time' => '09:30', 'title' => '新订单 #1024', 'text' => '客户王五下单 3 件商品', 'icon' => 'ti ti-shopping-cart', 'variant' => 'primary'],
                    ['time' => '10:12', 'title' => '订单已付款', 'text' => '支付宝到账 ￥299.00', 'icon' => 'ti ti-credit-card', 'variant' => 'success'],
                    ['time' => '11:40', 'title' => '库存告警', 'text' => 'SKU-88 库存低于 10 件', 'icon' => 'ti ti-alert-triangle', 'variant' => 'warning'],
                ]]),
            ])],
        ]]),
        // 环形图 + 列表 + 日历
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => '流量来源',
                'body'  => XfAdmin::apexChart(['type' => 'donut', 'height' => 280, 'series' => [44, 55, 13, 33], 'labels' => ['直接访问', '搜索引擎', '社交媒体', '外链']]),
            ])],
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title'   => '待办事项',
                'padding' => false,
                'body'    => XfAdmin::listGroup(['flush' => true, 'items' => [
                    ['text' => '审核新用户注册', 'badge' => ['text' => '12', 'class' => 'bg-primary']],
                    ['text' => '处理退款申请', 'badge' => ['text' => '3', 'class' => 'bg-danger']],
                    ['text' => '更新商品价格', 'badge' => ['text' => '8', 'class' => 'bg-warning']],
                    ['text' => '回复客户留言', 'badge' => ['text' => '25', 'class' => 'bg-info']],
                ]]),
            ])],
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => 'ECharts 示例',
                'body'  => XfAdmin::echart(['height' => 280, 'options' => [
                    'tooltip' => ['trigger' => 'axis'],
                    'xAxis'   => ['type' => 'category', 'data' => ['一月', '二月', '三月', '四月', '五月']],
                    'yAxis'   => ['type' => 'value'],
                    'series'  => [['type' => 'bar', 'data' => [120, 200, 150, 80, 170], 'itemStyle' => ['color' => '#3e60d5']]],
                ]]),
            ])],
        ]]),
    ],
]);

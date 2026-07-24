<?php

declare(strict_types=1);

use XfAdmin\XfAdmin;

echo XfAdmin::page([
    'title'       => '图表 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/charts',
    'topbar'      => ['user' => $user],
    'page_title'  => ['title' => '图表组件', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '图表']]],
    'content'     => [
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['lg' => 6], 'content' => XfAdmin::card(['title' => 'Apex 折线图', 'body' => XfAdmin::apexChart([
                'type' => 'line', 'height' => 300,
                'series' => [['name' => 'PV', 'data' => [10, 41, 35, 51, 49, 62, 69]]],
                'labels' => ['一', '二', '三', '四', '五', '六', '日'],
                'options' => ['stroke' => ['curve' => 'smooth', 'width' => 3]],
            ])])],
            ['width' => ['lg' => 6], 'content' => XfAdmin::card(['title' => 'Apex 柱状图', 'body' => XfAdmin::apexChart([
                'type' => 'bar', 'height' => 300,
                'series' => [['name' => '销量', 'data' => [44, 55, 57, 56, 61]], ['name' => '退货', 'data' => [13, 23, 20, 8, 13]]],
                'labels' => ['一月', '二月', '三月', '四月', '五月'],
            ])])],
            ['width' => ['lg' => 4], 'content' => XfAdmin::card(['title' => '径向图', 'body' => XfAdmin::apexChart([
                'type' => 'radialBar', 'height' => 300, 'series' => [76], 'labels' => ['完成率'],
            ])])],
            ['width' => ['lg' => 4], 'content' => XfAdmin::card(['title' => 'ECharts 饼图', 'body' => XfAdmin::echart(['height' => 300, 'options' => [
                'tooltip' => ['trigger' => 'item'],
                'series'  => [['type' => 'pie', 'radius' => ['40%', '70%'], 'data' => [
                    ['value' => 1048, 'name' => '搜索'], ['value' => 735, 'name' => '直接'], ['value' => 580, 'name' => '邮件'],
                ]]],
            ]])])],
            ['width' => ['lg' => 4], 'content' => XfAdmin::card(['title' => '世界地图（jsVectorMap）', 'body' => XfAdmin::vectorMap([
                'height' => 300,
                'markers' => [['name' => '北京', 'coords' => [39.9, 116.4]], ['name' => '纽约', 'coords' => [40.7, -74.0]]],
            ])])],
            ['width' => 12, 'content' => XfAdmin::card(['title' => '日历（FullCalendar）', 'body' => XfAdmin::calendar([
                'events' => [
                    ['title' => '产品评审会', 'start' => date('Y-m-d'), 'className' => 'bg-primary'],
                    ['title' => '版本发布', 'start' => date('Y-m-d', strtotime('+3 days')), 'className' => 'bg-success'],
                ],
            ])])],
        ]]),
    ],
]);

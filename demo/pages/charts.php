<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

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
            // ---------- 新增：组织架构树（apextree 插件，离线可用） ----------
            ['width' => ['lg' => 7], 'content' => XfAdmin::card(['title' => '组织架构树（ApexTree）', 'body' => XfAdmin::apexTree([
                'height' => 360, 'direction' => 'top',
                'data'   => [
                    'id' => '1', 'name' => '王一', 'role' => 'CEO', 'avatar' => 'users/user-1.jpg', 'color' => '#3e60d5',
                    'children' => [
                        ['id' => '2', 'name' => '李二', 'role' => '技术 VP', 'avatar' => 'users/user-2.jpg', 'color' => '#47ad77', 'children' => [
                            ['id' => '4', 'name' => '张四', 'role' => '前端组长', 'avatar' => 'users/user-4.jpg'],
                            ['id' => '5', 'name' => '陈五', 'role' => '后端组长', 'avatar' => 'users/user-5.jpg'],
                        ]],
                        ['id' => '3', 'name' => '赵三', 'role' => '市场 VP', 'avatar' => 'users/user-3.jpg', 'color' => '#fa5c7c'],
                    ],
                ],
            ])])],
            // ---------- 新增：桑基图（apexsankey 插件，离线可用） ----------
            ['width' => 12, 'content' => XfAdmin::card(['title' => '桑基图（ApexSankey）', 'body' => XfAdmin::apexSankey([
                'height' => 380,
                'nodes'  => [
                    ['id' => 'search', 'title' => '搜索引擎'],
                    ['id' => 'social', 'title' => '社交媒体'],
                    ['id' => 'direct', 'title' => '直接访问'],
                    ['id' => 'visit',  'title' => '访问量'],
                    ['id' => 'signup', 'title' => '注册'],
                    ['id' => 'lost',   'title' => '流失'],
                    ['id' => 'paid',   'title' => '付费转化'],
                ],
                'edges'  => [
                    ['source' => 'search', 'target' => 'visit',  'value' => 60],
                    ['source' => 'social', 'target' => 'visit',  'value' => 25],
                    ['source' => 'direct', 'target' => 'visit',  'value' => 15],
                    ['source' => 'visit',  'target' => 'signup', 'value' => 40],
                    ['source' => 'visit',  'target' => 'lost',   'value' => 60, 'color' => '#dddddd'],
                    ['source' => 'signup', 'target' => 'paid',   'value' => 12, 'color' => '#ffbc0b'],
                    ['source' => 'signup', 'target' => 'lost',   'value' => 28, 'color' => '#dddddd'],
                ],
            ])])],
            // ---------- 新增：谷歌地图（iframe 免 Key 嵌入，需外网） ----------
            ['width' => ['lg' => 5], 'content' => XfAdmin::card(['title' => '谷歌地图（GoogleMap）', 'body' => XfAdmin::googleMap([
                'height' => 360, 'place' => '北京市朝阳区', 'zoom' => 12,
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

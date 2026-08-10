<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

echo XfAdmin::page([
    'title'       => '组件展示 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/widgets',
    'sidenav'     => ['user' => $user + ['avatar' => xf_asset('images/users/user-2.jpg')]],
    'topbar'      => ['user' => $user + ['avatar' => xf_asset('images/users/user-2.jpg')]],
    'page_title'  => ['title' => '组件展示', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '组件展示']]],
    'content'     => [
        // 提示 / 徽标 / 按钮
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '提示框 Alert',
                'body'  => XfAdmin::alert(['variant' => 'primary', 'text' => '这是一条主要提示', 'dismissible' => true])
                    . XfAdmin::alert(['variant' => 'success', 'icon' => 'ti ti-circle-check', 'text' => '操作成功'])
                    . XfAdmin::alert(['variant' => 'warning', 'icon' => 'ti ti-alert-triangle', 'text' => '磁盘空间不足'])
                    . XfAdmin::alert(['variant' => 'danger', 'icon' => 'ti ti-alert-circle', 'text' => '发生错误，请重试']),
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '徽标与按钮',
                'body'  => '<div class="mb-3">'
                    . XfAdmin::badge(['text' => '主要', 'variant' => 'primary']) . ' '
                    . XfAdmin::badge(['text' => '成功', 'variant' => 'success']) . ' '
                    . XfAdmin::badge(['text' => '警告', 'variant' => 'warning', 'pill' => true]) . ' '
                    . XfAdmin::badge(['text' => '危险', 'variant' => 'danger', 'soft' => true])
                    . '</div><div class="d-flex flex-wrap gap-2">'
                    . XfAdmin::button(['text' => '主要按钮', 'variant' => 'primary'])
                    . XfAdmin::button(['text' => '成功按钮', 'variant' => 'success'])
                    . XfAdmin::button(['text' => '描边按钮', 'variant' => 'outline-secondary'])
                    . XfAdmin::button(['text' => '带图标', 'variant' => 'info', 'icon' => 'ti ti-download'])
                    . '</div>',
            ])],
        ]]),
        // 进度条 / 分页 / 评分
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => '进度条 Progress',
                'body'  => XfAdmin::progress(['value' => 25, 'variant' => 'primary', 'class' => 'mb-2'])
                    . XfAdmin::progress(['value' => 50, 'variant' => 'success', 'striped' => true, 'class' => 'mb-2'])
                    . XfAdmin::progress(['value' => 75, 'variant' => 'warning', 'striped' => true, 'animated' => true]),
            ])],
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => '分页 Pagination',
                'body'  => XfAdmin::pagination(['total' => 50, 'per_page' => 10, 'current' => 3]),
            ])],
            ['width' => ['xl' => 4], 'content' => XfAdmin::card([
                'title' => '评分 Rating',
                'body'  => XfAdmin::rating(['value' => 3.5, 'readonly' => true]),
            ])],
        ]]),
        // 标签页 / 手风琴
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '标签页 Tabs',
                'body'  => XfAdmin::tabs(['items' => [
                    ['title' => '首页', 'content' => '首页内容：欢迎使用 XfAdmin。', 'active' => true],
                    ['title' => '资料', 'content' => '资料内容：这里是个人资料。'],
                    ['title' => '设置', 'content' => '设置内容：这里是系统设置。'],
                ]]),
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '手风琴 Accordion',
                'body'  => XfAdmin::accordion(['items' => [
                    ['title' => '什么是 XfAdmin？', 'content' => '一个开箱即用的 PHP 后台 UI 组件库。', 'open' => true],
                    ['title' => '支持哪些框架？', 'content' => 'Laravel 11+ 与 ThinkPHP 8+，也可独立使用。'],
                    ['title' => '需要前端构建吗？', 'content' => '不需要，纯 PHP + 静态资源，离线可用。'],
                ]]),
            ])],
        ]]),
        // 时间线 / 步骤条
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '时间线 Timeline',
                'body'  => XfAdmin::timeline(['items' => [
                    ['time' => '09:00', 'title' => '创建订单', 'text' => '订单 #1024 已创建', 'icon' => 'ti ti-file-plus', 'variant' => 'primary'],
                    ['time' => '10:30', 'title' => '完成支付', 'text' => '支付宝到账 ￥299.00', 'icon' => 'ti ti-credit-card', 'variant' => 'success'],
                    ['time' => '14:00', 'title' => '已发货', 'text' => '顺丰快递 SF123456789', 'icon' => 'ti ti-truck', 'variant' => 'info'],
                ]]),
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '步骤条 Stepper',
                'body'  => XfAdmin::stepper(['current' => 2, 'items' => [
                    ['title' => '填写信息'],
                    ['title' => '确认订单'],
                    ['title' => '完成支付'],
                    ['title' => '交易完成'],
                ]]),
            ])],
        ]        ]),
        // 统计卡片 StatCard
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 3], 'content' => XfAdmin::statCard([
                'title' => '总营收', 'value' => '¥128,430', 'icon' => 'ti ti-currency-yuan', 'variant' => 'primary',
                'trend' => ['text' => '+12.5%', 'direction' => 'up', 'label' => '较上周'],
            ])],
            ['width' => ['xl' => 3], 'content' => XfAdmin::statCard([
                'title' => '活跃用户', 'value' => '8,642', 'icon' => 'ti ti-users', 'variant' => 'success',
                'trend' => ['text' => '+5.2%', 'direction' => 'up', 'label' => '较上周'],
            ])],
            ['width' => ['xl' => 3], 'content' => XfAdmin::statCard([
                'title' => '订单数', 'value' => '1,205', 'icon' => 'ti ti-shopping-cart', 'variant' => 'info',
                'trend' => ['text' => '-2.1%', 'direction' => 'down', 'label' => '较上周'],
            ])],
            ['width' => ['xl' => 3], 'content' => XfAdmin::statCard([
                'title' => '转化率', 'value' => '3.8%', 'icon' => 'ti ti-chart-arcs', 'variant' => 'warning',
                'trend' => ['text' => '+0.4%', 'direction' => 'up', 'label' => '较上周'],
            ])],
        ]]),
        // 仪表盘小部件 Widget / 团队成员 TeamMember
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '仪表盘小部件 Widget',
                'body'  => XfAdmin::widget([
                    'style' => 'icon', 'title' => '本月营收', 'value' => '¥52,000', 'icon' => 'ti ti-currency-yen', 'variant' => 'primary',
                    'trend' => ['value' => '8.2%', 'up' => true, 'text' => '较上月'],
                ]) . '<hr class="my-3">' . XfAdmin::widget([
                    'style' => 'progress', 'title' => '目标完成度', 'value' => '72%', 'icon' => 'ti ti-target', 'variant' => 'success',
                    'progress' => 72, 'footer' => '已达成 72 / 100 个里程碑',
                ]),
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '团队成员 TeamMember',
                'body'  => XfAdmin::teamMember([
                    'members' => [
                        ['avatar' => 'users/avatar-1.jpg', 'name' => '张三', 'role' => '产品经理', 'bio' => '负责产品规划与迭代', 'social' => ['ti ti-twitter' => '#', 'ti ti-facebook' => '#']],
                        ['avatar' => 'users/avatar-2.jpg', 'name' => '李四', 'role' => '前端工程师', 'bio' => '专注交互与性能优化'],
                        ['avatar' => 'users/avatar-3.jpg', 'name' => '王五', 'role' => '后端工程师', 'bio' => '架构与 API 设计'],
                        ['avatar' => 'users/avatar-4.jpg', 'name' => '赵六', 'role' => '设计师', 'bio' => '视觉与品牌'],
                    ],
                ]),
            ])],
        ]]),
        // 用户证言 Testimonial / 动态流 ActivityFeed
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '用户证言 Testimonial',
                'body'  => XfAdmin::testimonial([
                    'cols' => 1,
                    'items' => [
                        ['avatar' => 'users/avatar-2.jpg', 'name' => '李四', 'role' => 'CTO', 'text' => '这套后台极大提升了我们的运营效率。', 'rating' => 5],
                        ['avatar' => 'users/avatar-3.jpg', 'name' => '王五', 'role' => '运营总监', 'text' => '组件齐全，开箱即用，节省了大量开发时间。', 'rating' => 4],
                    ],
                ]),
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '动态流 ActivityFeed',
                'body'  => XfAdmin::activityFeed([
                    'items' => [
                        ['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'action' => '评论了任务', 'target' => '首页改版', 'href' => '#', 'text' => '看起来不错', 'time' => '2 小时前'],
                        ['icon' => 'ti ti-check', 'variant' => 'success', 'user' => '系统', 'action' => '完成部署', 'time' => '昨天'],
                    ],
                ]),
            ])],
        ]]),
        // 标签 Chip / 投票 VoteList
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '标签 Chip',
                'body'  => '<div class="d-flex flex-wrap gap-2">'
                    . XfAdmin::chip(['label' => '张三', 'avatar' => 'users/avatar-1.jpg'])
                    . XfAdmin::chip(['label' => '设计', 'variant' => 'info', 'icon' => 'ti ti-palette'])
                    . XfAdmin::chip(['label' => '可关闭', 'variant' => 'success', 'dismissible' => true])
                    . XfAdmin::chip(['label' => '链接', 'variant' => 'light', 'href' => '#'])
                    . '</div>',
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '投票 VoteList',
                'body'  => XfAdmin::voteList([
                    'items' => [
                        ['title' => '是否支持暗色模式？', 'desc' => '希望后台支持深色主题', 'votes' => 128,
                         'author' => ['avatar' => 'users/user-7.jpg', 'name' => '陈晓'], 'date' => '3 小时前', 'tag' => '功能', 'comments' => 12],
                    ],
                ]),
            ])],
        ]]),
        // ---------- 新增：指标卡 MetricCard（metrics.html，数值滚动 + 迷你图） ----------
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 3, 'md' => 6], 'content' => XfAdmin::metricCard([
                'title' => '总收入', 'value' => 368425, 'prefix' => '¥', 'trend' => 12.5,
                'chart' => 'donut', 'data' => [45, 30, 25], 'labels' => ['线上', '门店', '分销'],
                'icon' => 'ti ti-coin',
            ])],
            ['width' => ['xl' => 3, 'md' => 6], 'content' => XfAdmin::metricCard([
                'title' => '新增订单', 'value' => 1893, 'trend' => -3.2, 'color' => '#fa5c7c',
                'chart' => 'bar', 'data' => [12, 18, 9, 22, 16, 25, 19],
            ])],
            ['width' => ['xl' => 3, 'md' => 6], 'content' => XfAdmin::metricCard([
                'title' => '活跃用户', 'value' => 45280, 'trend' => 8.1, 'color' => '#47ad77',
                'chart' => 'area', 'data' => [30, 42, 38, 55, 48, 66, 72],
            ])],
            ['width' => ['xl' => 3, 'md' => 6], 'content' => XfAdmin::metricCard([
                'title' => '转化率', 'value' => 3.86, 'suffix' => '%', 'decimals' => 2, 'trend' => 1.4,
                'chart' => null, 'icon' => 'ti ti-percentage',
            ])],
        ]]),
        // ---------- 新增：多语言 i18n（data-lang 键 + XFAdmin.i18n 切换） ----------
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => 12, 'content' => XfAdmin::card([
                'title' => '多语言 i18n（misc-i18.html）',
                'body'  => '<p class="text-muted">元素加 <code>data-lang="翻译键"</code>，调用 <code>XFAdmin.i18n.set(语言码)</code> 即整页切换（翻译文件位于 data/translations/*.json，自动记忆到 localStorage）：</p>'
                    . '<div class="d-flex flex-wrap gap-3 align-items-center mb-3 fs-15">'
                    . '<span class="badge bg-primary-subtle text-primary" data-lang="dashboards">Dashboards</span>'
                    . '<span class="badge bg-success-subtle text-success" data-lang="menu-title">Menu</span>'
                    . '<span class="badge bg-info-subtle text-info" data-lang="apps">Apps</span>'
                    . '</div>'
                    . '<div class="btn-group">'
                    . '<button class="btn btn-sm btn-outline-primary" onclick="XFAdmin.i18n.set(\'en\')">English</button>'
                    . '<button class="btn btn-sm btn-outline-primary" onclick="XFAdmin.i18n.set(\'de\')">Deutsch</button>'
                    . '<button class="btn btn-sm btn-outline-primary" onclick="XFAdmin.i18n.set(\'es\')">Español</button>'
                    . '<button class="btn btn-sm btn-outline-secondary" onclick="XFAdmin.i18n.set(\'\')">恢复默认</button>'
                    . '</div>',
            ])],
        ]]),
        // ---------- 新增：动画 Animate（animate.css）与条款 Terms ----------
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '动画 Animate（悬浮 / 点击 / 滚动触发）',
                'body'  => '<div class="d-flex flex-wrap gap-3">'
                    . XfAdmin::animate(['animation' => 'pulse', 'trigger' => 'hover', 'tag' => 'span',
                        'content' => XfAdmin::button(['text' => '悬浮 pulse', 'variant' => 'primary'])])
                    . XfAdmin::animate(['animation' => 'tada', 'trigger' => 'click', 'tag' => 'span',
                        'content' => XfAdmin::button(['text' => '点击 tada', 'variant' => 'success'])])
                    . XfAdmin::animate(['animation' => 'fadeInUp', 'trigger' => 'scroll', 'tag' => 'span',
                        'content' => XfAdmin::badge(['text' => '滚动入场 fadeInUp', 'variant' => 'info'])])
                    . '</div>',
            ])],
            ['width' => ['xl' => 6], 'content' => XfAdmin::card([
                'title' => '条款 Terms（侧栏目录 + 分节正文）',
                'body'  => XfAdmin::terms([
                    'title' => '服务条款', 'updated_at' => '2026-07-01', 'toc' => false,
                    'intro' => '<p>欢迎使用 XfAdmin，请仔细阅读以下条款。</p>',
                    'sections' => [
                        ['id' => 'usage', 'title' => '1. 使用规范', 'content' => '<p>您应遵守相关法律法规，不得利用本服务从事违法活动。</p>'],
                        ['id' => 'privacy', 'title' => '2. 隐私保护', 'content' => '<p>我们仅在必要范围内收集与处理您的个人信息。</p>'],
                    ],
                    'accept' => ['label' => '我已阅读并同意'],
                ]),
            ])],
        ]]),
    ],
]);

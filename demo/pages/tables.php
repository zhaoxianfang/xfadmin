<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

// 演示数据（本地模式，无需服务端接口即可体验全部交互）
$data = [
    ['name' => '王伟',   'position' => '系统架构师',   'office' => '北京', 'age' => 41, 'start_date' => '2015-09-12', 'salary' => 9800],
    ['name' => '李娜',   'position' => '前端工程师',   'office' => '上海', 'age' => 29, 'start_date' => '2018-03-08', 'salary' => 7200],
    ['name' => '张强',   'position' => '后端工程师',   'office' => '广州', 'age' => 34, 'start_date' => '2016-11-23', 'salary' => 8100],
    ['name' => '刘洋',   'position' => '产品经理',     'office' => '深圳', 'age' => 38, 'start_date' => '2014-07-01', 'salary' => 10500],
    ['name' => '陈静',   'position' => '数据分析师',   'office' => '杭州', 'age' => 27, 'start_date' => '2020-01-15', 'salary' => 6800],
    ['name' => '杨光',   'position' => 'UI设计师',     'office' => '北京', 'age' => 31, 'start_date' => '2017-05-19', 'salary' => 7500],
    ['name' => '赵敏',   'position' => '前端工程师',   'office' => '深圳', 'age' => 26, 'start_date' => '2021-08-30', 'salary' => 6900],
    ['name' => '黄磊',   'position' => '后端工程师',   'office' => '上海', 'age' => 36, 'start_date' => '2015-02-11', 'salary' => 8700],
    ['name' => '周婷',   'position' => '系统架构师',   'office' => '广州', 'age' => 43, 'start_date' => '2013-10-05', 'salary' => 10200],
    ['name' => '吴昊',   'position' => '数据分析师',   'office' => '北京', 'age' => 30, 'start_date' => '2019-04-22', 'salary' => 7100],
    ['name' => '徐丽',   'position' => '产品经理',     'office' => '杭州', 'age' => 33, 'start_date' => '2016-09-17', 'salary' => 9300],
    ['name' => '孙鹏',   'position' => 'UI设计师',     'office' => '深圳', 'age' => 28, 'start_date' => '2020-12-03', 'salary' => 6400],
    ['name' => '马涛',   'position' => '后端工程师',   'office' => '广州', 'age' => 39, 'start_date' => '2014-06-14', 'salary' => 8900],
    ['name' => '朱琳',   'position' => '前端工程师',   'office' => '杭州', 'age' => 25, 'start_date' => '2022-02-28', 'salary' => 6600],
    ['name' => '胡军',   'position' => '系统架构师',   'office' => '上海', 'age' => 45, 'start_date' => '2012-03-19', 'salary' => 11000],
    ['name' => '郭燕',   'position' => '数据分析师',   'office' => '北京', 'age' => 32, 'start_date' => '2018-07-09', 'salary' => 7300],
    ['name' => '林峰',   'position' => '产品经理',     'office' => '广州', 'age' => 37, 'start_date' => '2015-11-11', 'salary' => 9900],
    ['name' => '何雪',   'position' => 'UI设计师',     'office' => '上海', 'age' => 24, 'start_date' => '2023-05-06', 'salary' => 6200],
    ['name' => '高翔',   'position' => '后端工程师',   'office' => '杭州', 'age' => 35, 'start_date' => '2017-01-25', 'salary' => 8400],
    ['name' => '罗浩',   'position' => '前端工程师',   'office' => '北京', 'age' => 27, 'start_date' => '2021-10-13', 'salary' => 7000],
];
// 为批量操作演示补充自增 id（批量提交 {ids} 占位符取自行数据 id 字段）
foreach ($data as $i => &$row) {
    $row['id'] = $i + 1;
}
unset($row);

$page = XfAdmin::page([
    'title'      => '表格与数据',
    'menu'       => $menu,
    'user'       => $user,
    'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '表格与数据']],
    'content'    => function () use ($data) {
        $html = '';

        // ============ 卡片 1：基础数据表（行分组 / 排序 / 多选 / 固定表头） ============
        $html .= '<div class="row"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">基础数据表</h4><p class="card-subtitle text-muted mb-0">行分组、默认排序、多选、固定表头与全局搜索</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'           => 'dt-demo-basic',
            'columns'      => [
                ['data' => 'name',       'title' => '姓名', 'xfBadges' => ['王伟' => 'danger', '李娜' => 'success']],
                ['data' => 'position',   'title' => '职位'],
                ['data' => 'office',     'title' => '城市'],
                ['data' => 'age',        'title' => '年龄', 'class' => 'text-end'],
                ['data' => 'start_date', 'title' => '入职日期'],
                ['data' => 'salary',     'title' => '月薪', 'class' => 'text-end', 'render' => 'money'],
            ],
            'data'        => $data,
            'row_group'   => 'office',
            'select'      => 'multi',
            'order'       => [[0, 'asc']],
            'fixed_header'=> true,
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 2：列筛选 columnFilters 示范 ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">列筛选（columnFilters）</h4><p class="card-subtitle text-muted mb-0">表头下方每列独立输入框，输入即按该列实时筛选；配合列显隐按钮使用</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'            => 'dt-demo-colsearch',
            'columns'       => [
                ['data' => 'name',       'title' => '姓名'],
                ['data' => 'position',   'title' => '职位'],
                ['data' => 'office',     'title' => '城市'],
                ['data' => 'age',        'title' => '年龄', 'class' => 'text-end'],
                ['data' => 'start_date', 'title' => '入职日期'],
                ['data' => 'salary',     'title' => '月薪', 'class' => 'text-end', 'render' => 'money'],
            ],
            'data'         => $data,
            'column_filters'=> true,
            'buttons'      => ['refresh', 'colvis'],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 3：完整过滤栏 filter_bar + 导出 / 密度切换 ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">完整过滤栏（filter_bar） + 工具栏</h4><p class="card-subtitle text-muted mb-0">顶部过滤栏按列联动搜索；工具栏含复制 / 导出 / 列显隐 / 全屏 / 密度切换</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'            => 'dt-demo-filterbar',
            'columns'       => [
                ['data' => 'name',       'title' => '姓名'],
                ['data' => 'position',   'title' => '职位'],
                ['data' => 'office',     'title' => '城市'],
                ['data' => 'age',        'title' => '年龄', 'class' => 'text-end'],
                ['data' => 'start_date', 'title' => '入职日期'],
                ['data' => 'salary',     'title' => '月薪', 'class' => 'text-end', 'render' => 'money'],
            ],
            'data'         => $data,
            'buttons'      => ['refresh', 'copy', 'csv', 'excel', 'print', 'colvis', 'fullscreen', 'density'],
            'filter_bar'   => [
                ['type' => 'search', 'name' => 'name',    'label' => '姓名',   'placeholder' => '按姓名搜索', 'width' => 'col-md-3'],
                ['type' => 'select', 'name' => 'position', 'label' => '职位',   'placeholder' => '全部职位', 'width' => 'col-md-3', 'options' => [
                    '系统架构师' => '系统架构师', '前端工程师' => '前端工程师', '后端工程师' => '后端工程师',
                    '产品经理'   => '产品经理',   '数据分析师' => '数据分析师', 'UI设计师'   => 'UI设计师',
                ]],
                ['type' => 'select', 'name' => 'office',   'label' => '城市',   'placeholder' => '全部城市', 'width' => 'col-md-3', 'options' => [
                    '北京' => '北京', '上海' => '上海', '广州' => '广州', '深圳' => '深圳', '杭州' => '杭州',
                ]],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 4：全渲染器矩阵（固定列 + 富单元格 + 操作闭环） ============
        $avatar = fn (int $i) => '/zxf/xfadmin/images/users/user-' . $i . '.jpg';
        $imgs   = fn (int $a, int $b, int $c) => implode(',', array_map(
            fn (int $n) => '/zxf/xfadmin/images/products/' . $n . '.png',
            [$a, $b, $c]
        ));
        $staff = [];
        $names = ['王伟', '李娜', '张强', '刘洋', '陈静', '杨光', '赵敏', '黄磊', '周婷', '吴昊'];
        $levels = ['P5', 'P6', 'P7', 'P8'];
        $states = ['active', 'busy', 'offline'];
        foreach ($names as $i => $n) {
            $id = $i + 1;
            $staff[] = [
                'id'       => $id,
                'name'     => $n,
                'email'    => 'user' . $id . '@example.com',
                'account'  => 'XF' . str_pad((string) (10000 + $id * 137), 6, '0', STR_PAD_LEFT),
                'avatar'   => $avatar(($i % 10) + 1),
                'ip'       => '10.20.' . ($id * 3) . '.' . ($id * 11 % 255),
                'enabled'  => $id % 3 !== 0 ? 1 : 0,
                'notify'   => $id % 2,
                'quota'    => 40 + $id * 5,
                'nickname' => '昵称' . $id,
                'level'    => $states[$id % 3],
                'grade'    => $levels[$id % 4],
                'growth'   => round((($id * 37) % 23) - 8.5, 1),
                'visits'   => array_map(fn ($k) => ($id * 7 + $k * 13) % 40 + 5, range(0, 9)),
                'photos'   => $imgs(($id % 10) ?: 10, ($id % 9) + 1, ($id % 8) + 1),
                'remark'   => '该成员近 30 天活跃度较高，参与了 ' . ($id + 2) . ' 个项目的迭代交付，代码评审通过率 9' . ($id % 10) . '%。',
                'bio'      => '拥有 ' . (3 + $id % 8) . ' 年研发经验，擅长高并发服务与数据中台建设，曾主导多个核心系统的架构演进。',
                'tags'     => ['后端', 'Go', 'K8s', '中台'][($id % 4)] . ',骨干,' . ($id % 2 ? '导师' : '新锐'),
                'events'   => [
                    ['time' => '07-2' . ($id % 9), 'title' => '完成季度目标评审', 'color' => 'success'],
                    ['time' => '07-1' . ($id % 9), 'title' => '提交晋升答辩材料', 'color' => 'primary'],
                    ['time' => '06-3' . ($id % 2), 'title' => '主导线上故障复盘', 'color' => 'warning'],
                    ['time' => '06-1' . ($id % 9), 'title' => '入选人才梯队计划', 'color' => 'info'],
                ],
                'perf'     => 60 + ($id * 9) % 40,
            ];
        }

        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">全渲染器矩阵（固定列 + 富单元格 + 操作闭环）</h4><p class="card-subtitle text-muted mb-0">输入框 / 复制输入框 / 悬浮提示 / 气泡提示 / 按钮切换 / 开关 / IP / 截断 / 头像 / 图片列表 / 进度条 / 星级 / 趋势 / 迷你图 / 时间线 / 状态点 / 标签 / 按钮组 / 下拉操作组；左 2 列 + 右 1 列固定，表头吸顶</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'            => 'dt-demo-renderers',
            'data'          => $staff,
            'scroll_x'      => true,
            'fixed_header'  => true,
            'fixed_columns' => ['left' => 2, 'right' => 1],
            'page_length'   => 10,
            'columns'       => [
                ['data' => 'id',      'title' => 'ID', 'width' => '52px'],
                ['data' => 'name',    'title' => '成员', 'render' => ['type' => 'user', 'avatar' => 'avatar', 'sub' => 'email'], 'width' => '180px'],
                ['data' => 'account', 'title' => '工号（复制输入框）', 'render' => 'copy', 'width' => '170px'],
                ['data' => 'nickname', 'title' => '昵称（可编辑输入框）', 'render' => ['type' => 'input', 'url' => '/api/demo/staff/{id}', 'field' => 'nickname'], 'width' => '140px'],
                ['data' => 'bio',     'title' => '简介（悬浮提示）', 'render' => ['type' => 'tooltip', 'length' => 12], 'width' => '160px'],
                ['data' => 'name',    'title' => '备注（气泡提示）', 'render' => ['type' => 'popover', 'label' => '查看备注', 'title' => '成员备注', 'content' => '{remark}', 'trigger' => 'focus'], 'width' => '120px'],
                ['data' => 'enabled', 'title' => '账号（按钮切换）', 'render' => ['type' => 'toggle', 'url' => '/api/demo/staff/{id}/toggle', 'field' => 'enabled', 'on_label' => '已启用', 'off_label' => '已停用'], 'width' => '120px'],
                ['data' => 'notify',  'title' => '通知（开关）', 'render' => ['type' => 'switch', 'url' => '/api/demo/staff/{id}/notify', 'field' => 'notify'], 'width' => '90px'],
                ['data' => 'ip',      'title' => '最近登录 IP', 'render' => 'ip', 'width' => '140px'],
                ['data' => 'level',   'title' => '在线状态', 'render' => ['type' => 'status', 'map' => [
                    'active' => ['label' => '在线', 'color' => 'success'],
                    'busy'   => ['label' => '忙碌', 'color' => 'warning'],
                    'offline' => ['label' => '离线', 'color' => 'secondary'],
                ]], 'width' => '90px'],
                ['data' => 'quota',   'title' => '目标完成度', 'render' => 'progress', 'width' => '150px'],
                ['data' => 'growth',  'title' => '环比', 'render' => ['type' => 'trend', 'suffix' => '%'], 'width' => '96px'],
                ['data' => 'visits',  'title' => '近10日活跃', 'render' => ['type' => 'sparkline'], 'width' => '110px'],
                ['data' => 'photos',  'title' => '作品（图片列表）', 'render' => ['type' => 'images', 'max' => 3], 'width' => '120px'],
                ['data' => 'tags',    'title' => '标签', 'render' => 'tags', 'width' => '170px'],
                ['data' => 'events',  'title' => '动态（时间线）', 'render' => ['type' => 'timeline', 'max' => 2, 'title' => '成员动态时间线'], 'width' => '220px'],
                ['data' => 'grade',   'title' => '职级（下拉操作组）', 'render' => ['type' => 'dropdown', 'label' => '调整', 'icon' => 'ti ti-adjustments', 'items' => [
                    ['label' => '晋升一级', 'icon' => 'ti ti-arrow-up', 'ajax' => '/api/demo/staff/{id}/promote', 'confirm' => '确认晋升该成员？', 'reload' => false],
                    ['label' => '降级一级', 'icon' => 'ti ti-arrow-down', 'ajax' => '/api/demo/staff/{id}/demote', 'confirm' => '确认降级该成员？', 'reload' => false],
                    ['type' => 'divider'],
                    ['label' => '冻结账号', 'icon' => 'ti ti-lock', 'ajax' => '/api/demo/staff/{id}/freeze', 'confirm' => '确认冻结？'],
                ]], 'width' => '110px'],
                ['key' => '', 'title' => '操作', 'width' => '210px', 'class' => 'text-nowrap', 'actions' => [
                    ['label' => '详情', 'icon' => 'ti ti-eye', 'class' => 'btn-soft-info', 'action' => 'view', 'view' => [
                        'title'   => '成员档案 - {name}',
                        'size'    => 'xl',
                        'layout'  => 'tabs',
                        'ajax'    => '/api/demo/staff/{id}',
                        'header'  => ['avatar' => 'avatar', 'title' => '{name}', 'sub' => '{email} · 工号 {account}', 'badge' => ['field' => 'level', 'map' => [
                            'active' => ['label' => '在线', 'color' => 'success'],
                            'busy'   => ['label' => '忙碌', 'color' => 'warning'],
                            'offline' => ['label' => '离线', 'color' => 'secondary'],
                        ]]],
                        'sections' => [
                            ['title' => '基础信息', 'icon' => 'ti ti-id', 'type' => 'kv', 'cols' => 2,
                             'fields' => ['id', 'name', 'email', 'account', 'ip', 'grade', 'quota', 'growth', 'last_login', 'login_count']],
                            ['title' => '参与项目', 'icon' => 'ti ti-briefcase', 'type' => 'table', 'field' => 'projects',
                             'columns' => ['name' => '项目', 'role' => '角色', 'status' => '状态']],
                            ['title' => '安全日志', 'icon' => 'ti ti-shield-lock', 'type' => 'timeline', 'field' => 'security_log'],
                            ['title' => '能力画像', 'icon' => 'ti ti-chart-radar', 'type' => 'progress', 'fields' => [
                                ['field' => 'quota', 'label' => '目标完成度'],
                                ['field' => 'perf', 'label' => '绩效得分', 'color' => 'info'],
                            ]],
                        ],
                        'labels' => ['id' => 'ID', 'last_login' => '最近登录', 'login_count' => '累计登录', 'grade' => '职级'],
                    ]],
                    ['label' => '编辑', 'icon' => 'ti ti-edit', 'class' => 'btn-soft-primary', 'action' => 'edit',
                     'page' => '/api/demo/staff/{id}/edit', 'title' => '编辑成员 - {name}', 'size' => 'lg', 'reload' => false, 'maximizable' => true],
                    ['label' => '删除', 'icon' => 'ti ti-trash', 'class' => 'btn-soft-danger', 'action' => 'delete',
                     'ajax' => '/api/demo/staff/{id}', 'confirm' => '确定删除该成员？删除后不可恢复。', 'confirm_popover' => true, 'reload' => false],
                ]],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 5：同一份数据，多种个性化详情布局 ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">个性化详情布局（同一行数据，四种完全不同的详情弹窗）</h4><p class="card-subtitle text-muted mb-0">档案式 profile / 标签页 tabs / 分区 sections（含统计卡+时间线）/ 自定义模板 template —— 每个业务功能都可拥有专属的详情排版</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'      => 'dt-demo-viewlayouts',
            'data'    => $staff,
            'paging'  => false,
            'info'    => false,
            'searching' => false,
            'columns' => [
                ['data' => 'name',   'title' => '成员', 'render' => ['type' => 'user', 'avatar' => 'avatar', 'sub' => 'email']],
                ['data' => 'grade',  'title' => '职级', 'badges' => ['P5' => 'secondary', 'P6' => 'info', 'P7' => 'primary', 'P8' => 'danger']],
                ['data' => 'quota',  'title' => '完成度', 'render' => 'progress'],
                ['key' => '', 'title' => '四种详情布局', 'width' => '380px', 'class' => 'text-nowrap', 'actions' => [
                    ['label' => '档案式', 'icon' => 'ti ti-user-circle', 'class' => 'btn-soft-primary', 'action' => 'view', 'view' => [
                        'title'  => '成员档案',
                        'layout' => 'profile',
                        'header' => ['avatar' => 'avatar', 'title' => '{name}', 'sub' => '{bio}'],
                        'fields' => ['email', 'account', 'ip', 'grade', 'quota', 'growth'],
                        'labels' => ['grade' => '职级'],
                    ]],
                    ['label' => '标签页', 'icon' => 'ti ti-layout-navbar', 'class' => 'btn-soft-info', 'action' => 'view', 'view' => [
                        'title'    => '{name} · 多标签详情',
                        'layout'   => 'tabs',
                        'sections' => [
                            ['title' => '概览', 'icon' => 'ti ti-dashboard', 'type' => 'stats', 'fields' => [
                                ['field' => 'quota', 'label' => '完成度', 'suffix' => '%', 'color' => 'primary', 'icon' => 'ti ti-target'],
                                ['field' => 'perf', 'label' => '绩效', 'color' => 'success', 'icon' => 'ti ti-chart-bar'],
                                ['field' => 'id', 'label' => '排名', 'color' => 'info', 'icon' => 'ti ti-trophy'],
                            ]],
                            ['title' => '资料', 'icon' => 'ti ti-id', 'type' => 'kv', 'fields' => ['name', 'email', 'account', 'ip', 'grade']],
                            ['title' => '动态', 'icon' => 'ti ti-history', 'type' => 'timeline', 'field' => 'events'],
                        ],
                    ]],
                    ['label' => '分区式', 'icon' => 'ti ti-layout-list', 'class' => 'btn-soft-success', 'action' => 'view', 'view' => [
                        'title'    => '{name} · 分区详情',
                        'sections' => [
                            ['title' => '关键指标', 'icon' => 'ti ti-gauge', 'type' => 'stats', 'fields' => [
                                ['field' => 'quota', 'label' => '目标完成', 'suffix' => '%', 'color' => 'primary'],
                                ['field' => 'perf', 'label' => '绩效得分', 'color' => 'success'],
                            ]],
                            ['title' => '技能标签', 'icon' => 'ti ti-tags', 'type' => 'tags', 'field' => 'tags'],
                            ['title' => '作品集', 'icon' => 'ti ti-photo', 'type' => 'images', 'field' => 'photos'],
                            ['title' => '近期动态', 'icon' => 'ti ti-history', 'type' => 'timeline', 'field' => 'events'],
                        ],
                    ]],
                    ['label' => '模板式', 'icon' => 'ti ti-code', 'class' => 'btn-soft-warning', 'action' => 'view', 'view' => [
                        'title'    => '自定义模板详情',
                        'layout'   => 'template',
                        'template' => '<div class="text-center py-3"><h4 class="mb-1">{name}</h4><p class="text-muted mb-3">{bio}</p>'
                            . '<div class="d-inline-flex gap-4"><div><div class="fs-3 fw-bold text-primary">{quota}%</div><div class="text-muted fs-13">目标完成</div></div>'
                            . '<div><div class="fs-3 fw-bold text-success">{perf}</div><div class="text-muted fs-13">绩效得分</div></div>'
                            . '<div><div class="fs-3 fw-bold text-info">{grade}</div><div class="text-muted fs-13">当前职级</div></div></div>'
                            . '<div class="alert alert-info mt-3 mb-0 fs-13 text-start"><i class="ti ti-bulb me-1"></i>template 布局支持任意 HTML 排版，{field} 占位符自动转义，可为每个业务功能定制完全不同的详情页。</div></div>',
                    ]],
                ]],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 6：弹窗加载服务端页面（编辑页 / 新建页） ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">弹窗加载服务端页面（编辑页 / 新建页）</h4><p class="card-subtitle text-muted mb-0">编辑按钮配置 page 地址后，弹窗直接加载服务端渲染的编辑页面（提取 [data-xf-page-content] 片段，表单 AJAX 接管提交、422 校验回填）；create 选项渲染「新增」按钮，同样以弹窗加载新建页面</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'      => 'dt-demo-pagedialog',
            'data'    => array_slice($staff, 0, 5),
            'paging'  => false,
            'info'    => false,
            'searching' => false,
            'create'  => [
                'page'  => '/api/demo/staff/create',
                'label' => '新增成员',
                'title' => '新增成员',
            ],
            'columns' => [
                ['data' => 'id',    'title' => 'ID', 'width' => '60px'],
                ['data' => 'name',  'title' => '成员', 'render' => ['type' => 'user', 'avatar' => 'avatar', 'sub' => 'email']],
                ['data' => 'grade', 'title' => '职级', 'badges' => ['P5' => 'secondary', 'P6' => 'info', 'P7' => 'primary', 'P8' => 'danger']],
                ['data' => 'quota', 'title' => '完成度', 'render' => 'progress'],
                ['key' => '', 'title' => '操作', 'width' => '120px', 'class' => 'text-nowrap', 'actions' => [
                    ['label' => '编辑', 'icon' => 'ti ti-edit', 'class' => 'btn-soft-primary', 'action' => 'edit',
                     'page' => '/api/demo/staff/{id}/edit', 'title' => '编辑成员 #{id}', 'size' => 'lg', 'reload' => false],
                ]],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 7：交互增强（批量操作 / 行明细展开 / 状态持久化 / 一键导出） ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">交互增强（批量操作 / 行明细展开 / 状态持久化 / 一键导出）</h4><p class="card-subtitle text-muted mb-0">勾选行后顶部出现批量操作栏（{ids} 占位自动替换）；点击行首箭头展开该行完整字段明细；排序 / 分页 / 列显隐状态自动持久化到 localStorage；export 一键开启导出按钮</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'          => 'dt-demo-enhanced',
            'data'        => $data,
            'page_length' => 10,
            'state_save'  => true,
            'export'      => ['copy', 'excel', 'csv'],
            'bulk'        => [
                'actions' => [
                    ['label' => '批量启用', 'icon' => 'ti ti-check', 'class' => 'btn-soft-success',
                     'url' => '/api/demo/staff/batch-enable?ids={ids}', 'confirm' => '确认启用选中成员？', 'reload' => false],
                    ['label' => '批量停用', 'icon' => 'ti ti-ban', 'class' => 'btn-soft-warning',
                     'url' => '/api/demo/staff/batch-disable?ids={ids}', 'confirm' => '确认停用选中成员？', 'reload' => false],
                    ['label' => '批量删除', 'icon' => 'ti ti-trash', 'class' => 'btn-soft-danger',
                     'url' => '/api/demo/staff/batch-delete?ids={ids}', 'confirm' => '确认删除选中成员？删除后不可恢复！', 'reload' => false],
                ],
            ],
            'row_detail'  => ['columns' => ['name', 'position', 'office', 'age', 'start_date', 'salary']],
            'columns'     => [
                ['data' => 'name',       'title' => '姓名'],
                ['data' => 'position',   'title' => '职位'],
                ['data' => 'office',     'title' => '城市'],
                ['data' => 'age',        'title' => '年龄', 'class' => 'text-end'],
                ['data' => 'salary',     'title' => '月薪', 'class' => 'text-end', 'render' => 'money'],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 8：开发者自定义（js: 渲染器 / page 弹窗单元格 / 自定义过滤控件） ============
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">开发者自定义（js: 渲染器 / page 弹窗单元格 / 自定义过滤控件）</h4><p class="card-subtitle text-muted mb-0">render=js:函数名 调用前端全局函数自定义单元格；render=page 点击单元格弹窗加载服务端页面（关闭可选刷新）；filter_bar 支持 html 完全自定义过滤控件</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'      => 'dt-demo-custom',
            'data'    => array_slice($staff, 0, 6),
            'paging'  => false,
            'info'    => false,
            'filter_bar' => [
                ['type' => 'search', 'name' => 'name', 'label' => '姓名', 'placeholder' => '搜索姓名', 'width' => 'col-md-3'],
                // 完全自定义控件：任意 HTML，带 class="xf-filter" data-filter="参数名" 即自动参与过滤
                ['html' => '<label class="form-label">职级（自定义控件）</label>'
                    . '<div class="btn-group w-100" role="group">'
                    . '<input type="radio" class="btn-check xf-filter" data-filter="grade" name="grade" id="fg-all" value="" checked><label class="btn btn-outline-secondary btn-sm" for="fg-all">全部</label>'
                    . '<input type="radio" class="btn-check xf-filter" data-filter="grade" name="grade" id="fg-p6" value="P6"><label class="btn btn-outline-secondary btn-sm" for="fg-p6">P6</label>'
                    . '<input type="radio" class="btn-check xf-filter" data-filter="grade" name="grade" id="fg-p7" value="P7"><label class="btn btn-outline-secondary btn-sm" for="fg-p7">P7</label>'
                    . '</div>', 'width' => 'col-md-4'],
            ],
            'columns' => [
                ['data' => 'id',    'title' => 'ID', 'width' => '60px'],
                // page 渲染器：点击成员姓名弹窗加载服务端编辑页，关闭弹窗不刷新表格（reload=false）
                ['data' => 'name',  'title' => '成员（点击弹窗）', 'render' => [
                    'type' => 'page', 'url' => '/api/demo/staff/{id}/edit', 'title' => '成员详情 #{id}', 'size' => 'lg', 'reload' => false,
                ]],
                // js: 渲染器：调用页面上定义的全局函数 demoSalaryCell 自定义渲染
                ['data' => 'quota', 'title' => '完成度（js: 自定义渲染）', 'render' => 'js:demoQuotaCell'],
                ['data' => 'grade', 'title' => '职级', 'badges' => ['P5' => 'secondary', 'P6' => 'info', 'P7' => 'primary', 'P8' => 'danger']],
            ],
        ]);
        // 开发者自定义渲染函数示例：签名 fn(data, row, cfg, meta) => HTML
        $html .= '<script>function demoQuotaCell(d, row) {'
            . "var color = d >= 80 ? 'success' : (d >= 50 ? 'warning' : 'danger');"
            . "return '<span class=\"badge bg-' + color + '-subtle text-' + color + ' fs-12\">' + d + '%</span>'"
            . " + (d >= 80 ? ' <i class=\"ti ti-trophy text-warning\"></i>' : '');"
            . '}</script>';
        $html .= '</div></div></div></div>';

        // ============ 卡片 9：文件与协作（file 单元格 / 多人头像组 / 下载·打印·分享 操作） ============
        $pdf = XfAdmin::asset('images/files/sample.pdf');
        $u = function ($n) { return XfAdmin::img('users/user-' . $n . '.jpg'); };
        $docs = [
            ['id' => 1, 'name' => '产品需求文档', 'file' => ['url' => $pdf, 'name' => 'PRD-2026Q3.pdf', 'size' => 245760], 'team' => [
                ['url' => $u(1), 'name' => '王伟'], ['url' => $u(2), 'name' => '李娜'],
                ['url' => $u(3), 'name' => '张强'], ['url' => $u(4), 'name' => '刘洋'], ['url' => '', 'name' => '陈静']]],
            ['id' => 2, 'name' => '接口设计稿', 'file' => ['url' => $pdf, 'name' => 'API-Design.docx', 'size' => 88064], 'team' => [
                ['url' => $u(5), 'name' => '杨光'], ['url' => $u(6), 'name' => '赵敏']]],
            ['id' => 3, 'name' => '上线checklist', 'file' => $pdf, 'team' => [
                ['url' => $u(7), 'name' => '周婷'], ['url' => $u(8), 'name' => '吴昊'],
                ['url' => $u(9), 'name' => '徐丽'], ['url' => $u(10), 'name' => '孙鹏']]],
        ];
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">文件与协作（file 单元格 / 多人头像组 / 下载·打印·分享 操作）</h4><p class="card-subtitle text-muted mb-0">file 渲染器展示图标+文件名+人类可读大小+下载按钮；avatarGroup 渲染多人头像（超出折叠为 +N）；操作列支持 download（下载附件）、print（打印预览弹窗）、share（复制分享链接并提示）</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'      => 'dt-demo-files',
            'data'    => $docs,
            'columns' => [
                ['data' => 'id',   'title' => 'ID', 'width' => '56px'],
                ['data' => 'name', 'title' => '文档', 'render' => 'truncate'],
                ['data' => 'file', 'title' => '附件', 'render' => ['type' => 'file'], 'width' => '280px'],
                ['data' => 'team', 'title' => '协作成员', 'render' => ['type' => 'avatarGroup', 'max' => 3], 'width' => '160px'],
                ['key' => '', 'title' => '操作', 'width' => '240px', 'class' => 'text-nowrap', 'actions' => [
                    ['label' => '下载', 'icon' => 'ti ti-download', 'class' => 'btn-soft-primary', 'action' => 'download', 'ajax' => '{file.url}'],
                    ['label' => '打印', 'icon' => 'ti ti-printer', 'class' => 'btn-soft-secondary', 'action' => 'print', 'ajax' => '{file.url}', 'title' => '打印预览'],
                    ['label' => '分享', 'icon' => 'ti ti-share', 'class' => 'btn-soft-info', 'action' => 'share', 'ajax' => '/demo/share/{id}', 'title' => '分享链接已复制'],
                ]],
            ],
        ]);
        $html .= '</div></div></div></div>';

        // ============ 卡片 10：二维码单元格（qr 渲染器：URL / 文本 / 中文 均可） ============
        $qrData = [];
        $welcome = ['欢迎加入团队 🎉', '扫码访问个人主页', '会议入场凭证 · 2026', '资料下载通道'];
        foreach (array_slice($staff, 0, 6) as $i => $s) {
            $qrData[] = [
                'id'      => $s['id'],
                'name'    => $s['name'],
                'account' => $s['account'],
                'home'    => 'https://example.com/u/' . $s['account'],
                'welcome' => $welcome[$i % count($welcome)],
                'avatar'  => $u($s['id']),
            ];
        }
        $html .= '<div class="row mt-3"><div class="col-12"><div class="card"><div class="card-header"><div><h4 class="card-title mb-1">二维码单元格（qr 渲染器：URL / 文本 / 中文 均可）</h4><p class="card-subtitle text-muted mb-0">qr 渲染器基于 qrcode-generator：内容可为 URL、纯文本或含中文的非 ASCII 内容（自动 UTF-8 编码）；支持配置 size / ec（纠错级别 L·M·Q·H）/ color / bg；点击单元格放大查看，链接内容额外提供「打开链接」按钮</p></div></div><div class="card-body">';
        $html .= XfAdmin::datatable([
            'id'      => 'dt-demo-qr',
            'data'    => $qrData,
            'paging'  => false,
            'info'    => false,
            'searching' => false,
            'columns' => [
                ['data' => 'id',   'title' => 'ID', 'width' => '56px'],
                ['data' => 'name', 'title' => '成员', 'render' => ['type' => 'user', 'avatar' => 'avatar', 'sub' => 'account']],
                ['data' => 'home',    'title' => '主页二维码', 'render' => ['type' => 'qr', 'text' => '{home}', 'size' => 96, 'ec' => 'M'], 'width' => '130px'],
                ['data' => 'welcome', 'title' => '欢迎语二维码（中文）', 'render' => ['type' => 'qr', 'text' => '{welcome}', 'size' => 96, 'ec' => 'M'], 'width' => '150px'],
                ['data' => 'account', 'title' => '工号二维码', 'render' => ['type' => 'qr', 'text' => '{account}', 'size' => 72, 'ec' => 'H'], 'width' => '110px'],
            ],
        ]);
        $html .= '</div></div></div></div>';

        return $html;
    },
]);

echo $page;

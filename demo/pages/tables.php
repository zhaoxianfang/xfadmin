<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

$rows = [];
$names = ['张三', '李四', '王五', '赵六', '钱七', '孙八', '周九', '吴十'];
$roles = ['管理员', '编辑', '访客'];
$statuses = ['启用', '禁用'];
for ($i = 1; $i <= 57; $i++) {
    $rows[] = [
        'id'     => $i,
        'name'   => $names[$i % 8] . $i,
        'email'  => 'user' . $i . '@example.com',
        'role'   => $roles[$i % 3],
        'status' => $statuses[$i % 5 === 0 ? 1 : 0],
        'created_at' => date('Y-m-d', strtotime("-{$i} days")),
    ];
}

echo XfAdmin::page([
    'title'       => '表格 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/tables',
    'topbar'      => ['user' => $user],
    'page_title'  => ['title' => '表格与数据', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '表格']]],
    'content'     => [
        XfAdmin::card([
            'title'    => '全功能 DataTable（搜索/排序/分页/多选/导出/列筛选/徽章/模板列）',
            'subtitle' => '渲染两个表格验证资源只加载一次',
            'body'     => XfAdmin::dataTable([
                'columns' => [
                    'id'     => ['label' => 'ID', 'width' => '60px'],
                    'name'   => '姓名',
                    'email'  => '邮箱',
                    'role'   => '角色',
                    'status' => ['label' => '状态', 'badges' => ['启用' => 'success', '禁用' => 'danger']],
                    'created_at' => '注册时间',
                    'op'     => ['label' => '操作', 'sortable' => false,
                        'template' => '<a href="/users/{id}/edit" class="btn btn-sm btn-soft-primary me-1">编辑</a><a href="javascript:;" class="btn btn-sm btn-soft-danger">删除</a>'],
                ],
                'data'           => $rows,
                'buttons'        => ['copy', 'csv', 'excel', 'print'],
                'select'         => 'multi',
                'fixed_header'   => true,
                'column_filters' => true,
                'order'          => [[0, 'desc']],
                'hover'          => true,
                'centered'       => true,
            ]),
        ]),
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['lg' => 6], 'content' => XfAdmin::card([
                'title' => '静态表格（条纹+悬停+自定义单元格）',
                'body'  => XfAdmin::table([
                    'columns' => [
                        'name' => '姓名',
                        'role' => '角色',
                        'status' => ['label' => '状态', 'raw' => true,
                            'format' => fn ($r) => '<span class="badge bg-' . ($r['status'] === '启用' ? 'success' : 'danger') . '-subtle text-' . ($r['status'] === '启用' ? 'success' : 'danger') . '">' . $r['status'] . '</span>'],
                    ],
                    'data'    => array_slice($rows, 0, 5),
                    'striped' => true,
                    'hover'   => true,
                ]),
            ])],
            ['width' => ['lg' => 6], 'content' => XfAdmin::card([
                'title' => '第二个 DataTable（AJAX 说明 + 分页组件）',
                'body'  => XfAdmin::dataTable([
                    'columns' => ['id' => 'ID', 'name' => '姓名', 'email' => '邮箱'],
                    'data'    => array_slice($rows, 0, 15),
                    'page_length' => 5,
                ]) . '<hr>' . XfAdmin::pagination(['total' => 200, 'per_page' => 10, 'current' => 5, 'url' => '?page={page}']),
            ])],
        ]]),
    ],
]);

<?php

declare(strict_types=1);

// 排序诊断页：渲染一个带排序的 DataTable
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

$rows = [];
for ($i = 1; $i <= 25; $i++) {
    $rows[] = [
        'id'     => $i,
        'name'   => 'User ' . str_pad((string) ((($i * 7) % 25) + 1), 2, '0', STR_PAD_LEFT),
        'age'    => 20 + (($i * 13) % 40),
        'city'   => ['北京', '上海', '广州', '深圳', '杭州'][$i % 5],
        'salary' => 3000 + (($i * 977) % 20000),
    ];
}

$table = XfAdmin::dataTable([
    'id'      => 'dt-sort',
    'columns' => [
        ['data' => 'id', 'title' => 'ID'],
        ['data' => 'name', 'title' => '姓名'],
        ['data' => 'age', 'title' => '年龄'],
        ['data' => 'city', 'title' => '城市'],
        ['data' => 'salary', 'title' => '薪资'],
    ],
    'data'    => $rows,
    'order'   => [[0, 'asc']],
]);

echo XfAdmin::page([
    'title'   => '排序诊断',
    'content' => (string) $table,
]);

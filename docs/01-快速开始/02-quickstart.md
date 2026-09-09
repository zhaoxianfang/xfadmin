# 五分钟上手

本文用最小示例串起 XfAdmin 的五个高频场景：整页 → 卡片 → 数据表格 → 表单 → 图表。

## 1. 第一个页面

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

// 引导时合并一次全局配置（Laravel/ThinkPHP 由服务提供者自动完成，可省略）
XfAdmin::config(require __DIR__ . '/vendor/zxf/xfadmin/config/xfadmin.php');

echo XfAdmin::page([
    'title'   => '我的后台',
    'content' => XfAdmin::card([
        'title' => '欢迎',
        'body'  => 'Hello XfAdmin',
    ]),
]);
```

`XfAdmin::page()` 会输出**完整 HTML 文档**（`<!DOCTYPE html>` → `</html>`），
内部已包含 `head()`（CSS + 主题脚本）与 `scripts()`（JS + 组件初始化），无需手工调用。

## 2. 加上侧边栏与顶栏

```php
$menu = [
    ['title' => '主导航'],                                   // 分组标题（无 url）
    ['text' => '仪表盘', 'icon' => 'ti ti-dashboard', 'url' => '/admin', 'active' => true],
    ['text' => '用户管理', 'icon' => 'ti ti-users', 'url' => '/admin/users', 'badge' => 'New'],
    ['text' => '系统设置', 'icon' => 'ti ti-settings', 'children' => [
        ['text' => '基础配置', 'url' => '/admin/setting'],
        ['text' => '菜单管理', 'url' => '/admin/menu'],
    ]],
];

echo XfAdmin::page([
    'title'      => '我的后台',
    'menu'       => $menu,                       // 自动下发给 Sidenav
    'topbar'     => [
        'search'   => true,
        'user'     => [
            'name'   => '张三',
            'avatar' => 'users/user-2.jpg',
            'items'  => [
                ['text' => '个人资料', 'url' => '/profile', 'icon' => 'ti ti-user'],
                ['divider' => true],
                ['text' => '退出登录', 'url' => '/logout', 'icon' => 'ti ti-logout'],
            ],
        ],
    ],
    'page_title' => [
        'title'      => '仪表盘',
        'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '仪表盘']],
    ],
    'content'    => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
]);
```

## 3. 栅格 + 指标卡

```php
$content = XfAdmin::row([
    'cols' => [
        ['width' => 3, 'content' => XfAdmin::statCard([
            'title' => '今日订单', 'value' => '1,286',
            'icon' => 'ti ti-shopping-cart', 'variant' => 'primary',
            'trend' => 12.5,                       // 正数为上升
        ])],
        ['width' => 3, 'content' => XfAdmin::statCard([
            'title' => '销售额', 'value' => '￥86,420', 'icon' => 'ti ti-coin', 'variant' => 'success',
        ])],
        ['width' => 3, 'content' => XfAdmin::statCard([
            'title' => '新增用户', 'value' => 328, 'icon' => 'ti ti-user-plus', 'variant' => 'info',
        ])],
        ['width' => 3, 'content' => XfAdmin::statCard([
            'title' => '退款率', 'value' => '1.2%', 'icon' => 'ti ti-alert-triangle', 'variant' => 'danger',
        ])],
    ],
]);
```

## 4. 第一个数据表格（本地数据）

```php
$table = XfAdmin::dataTable([
    'id'      => 'user-table',
    'columns' => [
        ['key' => 'id', 'label' => 'ID', 'width' => '60px'],
        ['key' => 'name', 'label' => '姓名'],
        ['key' => 'role', 'label' => '角色', 'badges' => ['admin' => 'danger', 'user' => 'primary']],
        ['key' => 'created_at', 'label' => '注册时间', 'render' => 'datetime'],
        ['key' => '', 'label' => '操作', 'actions' => [
            ['label' => '编辑', 'icon' => 'ti ti-pencil', 'action' => 'edit', 'ajax' => '/admin/users/{id}'],
            ['label' => '删除', 'icon' => 'ti ti-trash', 'action' => 'ajax',
             'ajax' => '/admin/users/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？'],
        ]],
    ],
    'data' => [
        ['id' => 1, 'name' => '张三', 'role' => 'admin', 'created_at' => '2026-01-01 10:00:00'],
        ['id' => 2, 'name' => '李四', 'role' => 'user',  'created_at' => '2026-02-11 09:30:00'],
    ],
]);
```

## 5. 服务端分页表格（对接后端）

前端：

```php
$table = XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',            // POST 模式自动带 X-CSRF-TOKEN，规避 URL 过长
    'columns'     => [
        ['key' => 'id', 'label' => 'ID'],
        ['key' => 'name', 'label' => '姓名'],
        ['key' => 'email', 'label' => '邮箱'],
        ['key' => 'status', 'label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],
    ],
    'filter_bar'  => [
        ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
        ['name' => 'status', 'label' => '状态', 'type' => 'select',
         'options' => ['1' => '正常', '0' => '禁用']],
    ],
]);
```

后端（Laravel）：

```php
use zxf\XfAdmin\XfAdmin;

public function users(Request $request)
{
    return response()->json(XfAdmin::dataResponse(
        \App\Models\User::query(),                  // 查询构造器：条件自动下推
        $request->all(),
        [
            'searchable' => ['name', 'email'],      // 全局搜索命中列
            'filters'    => [
                'keyword'   => ['name', 'email'],   // 参数名 => 多字段模糊
                'status'    => ['field' => 'status', 'op' => '='],
                'date_from' => ['field' => 'created_at', 'op' => 'date_from'],
                'date_to'   => ['field' => 'created_at', 'op' => 'date_to'],
            ],
        ]
    ));
}
```

`XfAdmin::dataResponse()` 会完成搜索、过滤、排序、分页，返回
`{draw, recordsTotal, recordsFiltered, data}`，与前端组件直接对接，
**无需自己解析 DataTables 参数**。

## 6. 第一个表单

```php
$form = XfAdmin::form([
    'action' => '/admin/users',
    'method' => 'POST',
    'ajax'   => true,                    // 输出 data-xf-remote，由前端托管提交
    'csrf'   => true,                    // 注入 _token 隐藏域
    'fields' => [
        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true]),
        XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true]),
        XfAdmin::select(['name' => 'role', 'label' => '角色', 'enhance' => 'choices',
                         'options' => ['admin' => '管理员', 'user' => '普通用户']]),
        XfAdmin::check(['name' => 'status', 'type' => 'switch', 'label' => '启用', 'checked' => true]),
    ],
    'buttons' => [
        XfAdmin::button(['label' => '保存', 'type' => 'submit', 'variant' => 'primary']),
        XfAdmin::button(['label' => '取消', 'variant' => 'light']),
    ],
]);
```

后端成功响应（AJAX 模式约定）：

```php
return response()->json([
    'ok'      => true,                 // 顶层 ok
    'message' => '保存成功',
    'url'     => '/admin/users',       // 存在则前端 3s 后跳转
]);
```

校验失败返回 422，前端自动把 `errors` 回填到对应字段下方：

```php
return response()->json(['message' => '校验失败', 'errors' => [
    'email' => ['邮箱已被占用'],
]], 422);
```

## 7. 第一个图表

```php
$chart = XfAdmin::apexChart([
    'id'     => 'sales-chart',
    'type'   => 'line',
    'height' => 320,
    'series' => [
        ['name' => '销售额', 'data' => [120, 200, 150, 280, 220, 310]],
    ],
    'categories' => ['1月', '2月', '3月', '4月', '5月', '6月'],
]);
```

## 8. 组合成页

```php
echo XfAdmin::page([
    'title'   => '仪表盘',
    'menu'    => $menu,
    'content' =>
        $content                                        // 指标卡行
        . XfAdmin::row([
            'cols' => [
                ['width' => 8, 'content' => XfAdmin::card(['title' => '销售趋势', 'body' => $chart])],
                ['width' => 4, 'content' => XfAdmin::card(['title' => '快速操作', 'body' => $quick])],
            ],
        ])
        . XfAdmin::card(['title' => '用户列表', 'body' => $table, 'padding' => false]),
]);
```

> `row`/`col` 支持嵌套；`card` 的 `padding => false` 会在 `card-body` 上加 `p-0`，适合放表格。

## 9. 运行内置演示

```bash
php -S 127.0.0.1:8900 demo/router.php
```

| 路径 | 演示内容 |
|---|---|
| `/` | 仪表盘（指标卡 + 图表 + 表格） |
| `/widgets` | 55 个 UI 基础组件 |
| `/apps` | 业务应用组件 |
| `/tables` | 表格与数据（含服务端模式） |
| `/forms` | 全部表单控件 |
| `/charts` | Apex / ECharts / 地图 |
| `/auth` | 9 种认证页 × 3 种布局 |
| `/topnav` | 水平顶部导航 |
| `/landing` | 落地页 |
| `/404` | 错误页 |

## 10. 下一步

- [03-核心概念](03-concepts.md) —— 理解组件、配置合并与资源机制
- [04-全局配置](04-configuration.md) —— 主题、品牌、资源基址
- [03-数据表格全指南](../04-进阶指南/03-datatable.md) —— 表格的全部能力

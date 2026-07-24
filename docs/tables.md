# 表格

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 静态表格 `table`

```php
XfAdmin::table([
    'columns'    => ['ID', '姓名', '邮箱', '状态'],
    'data'       => [
        [1, '张三', 'a@x.com', XfAdmin::badge(['text' => '正常', 'variant' => 'success'])],
        [2, '李四', 'b@x.com', XfAdmin::badge(['text' => '停用', 'variant' => 'danger'])],
    ],
    'striped'    => true,
    'bordered'   => false,
    'hover'      => true,
    'responsive' => true,
    'size'       => 'sm',        // sm | null
    'class'      => 'align-middle',
]);
```

也可用关联数组 + 列定义（支持渲染回调）：

```php
XfAdmin::table([
    'columns' => [
        'id'   => ['label' => 'ID', 'width' => '60px'],
        'name' => ['label' => '姓名'],
        'ops'  => ['label' => '操作', 'render' => fn ($row) =>
            XfAdmin::button(['label' => '编辑', 'size' => 'sm', 'href' => '/edit/' . $row['id']])],
    ],
    'data' => $users,   // 每行为关联数组
]);
```

---

## 全功能表格 `dataTable`

基于 DataTables，内置排序、搜索、分页、列显隐、响应式、导出。**自动加载并去重** DataTables 相关资源（同一页多个 dataTable 也只加载一次核心库）。

```php
XfAdmin::dataTable([
    'columns' => ['ID', '姓名', '邮箱', '注册时间'],
    'data'    => $rows,             // 二维数组或关联数组
    'searching'  => true,
    'paging'     => true,
    'page_length'=> 25,
    'ordering'   => true,
    'order'      => [[0, 'desc']],  // 默认排序：第0列倒序
    'responsive' => true,
    'select'     => true,           // 复选
    'buttons'    => ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],  // 导出/列控制
    'language'   => 'zh',           // 内置中文语言包
    'options'    => [               // 透传任意 DataTables 原生配置
        'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, '全部']],
    ],
]);
```

### 服务端处理（Ajax）

```php
XfAdmin::dataTable([
    'columns'    => ['ID', '姓名', '邮箱'],
    'server_side'=> true,
    'ajax'       => '/api/users/datatable',   // 返回 DataTables 规范 JSON
    'columns_def'=> [
        ['data' => 'id'],
        ['data' => 'name'],
        ['data' => 'email'],
    ],
]);
```

### 复杂筛选

在表头/工具栏加入自定义筛选控件，配合 `data-xf="datatable"` 暴露的实例进行联动：

```php
XfAdmin::dataTable([
    'id'      => 'user-table',
    'columns' => ['ID', '姓名', '部门'],
    'data'    => $rows,
    'toolbar' => XfAdmin::row(['cols' => [
        ['width' => ['md' => 4], 'content' => XfAdmin::select([
            'id' => 'flt-dept', 'label' => '部门', 'options' => ['技术', '市场'],
        ])],
        ['width' => ['md' => 4], 'content' => XfAdmin::dateRangePicker(['id' => 'flt-date', 'label' => '日期'])],
    ]]),
]);
```

```js
// 前端联动：dataTable 实例挂在元素的 _xf 上
const el = document.getElementById('user-table');
document.getElementById('flt-dept').addEventListener('change', function () {
    el._xf.datatable.column(2).search(this.value).draw();
});
```

### 导出按钮所需资源

`buttons` 含 `pdf` 时会自动附加 `pdfmake`；含 `excel` 会附加 `jszip`，均随包内置、离线可用、去重加载。

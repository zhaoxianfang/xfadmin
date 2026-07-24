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

> 静态表格兼容 DataTables 的列定义别名：`data`→`key`、`title`→`label`、`orderable`→`sortable`、`xfBadges`→`badges`、`xfTemplate`→`template`，方便从 `dataTable` 迁移。

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

---

## 服务端处理（Ajax）+ DataSet

每个 `dataTable` 的服务端分页、搜索、排序、过滤**全部由包内 `DataSet` 完成**，你只需把「数据 + 请求参数」交给它，无需手写任何 DataTables 协议代码。前端默认 `errMode: 'none'`，请求失败以 toast 提示而非原生 `alert`，彻底消除 `DataTables warning: table id=xxxx - Ajax error` 弹窗。

### 后端：返回 DataTables 协议

```php
use zxf\XfAdmin\XfAdmin;

// 数组数据
public function users(Request $request)
{
    $rows = User::query()->get()->toArray();
    return XfAdmin::dataResponse($rows, $request->all(), [
        'searchable' => ['name', 'email'],                 // 全局搜索字段
        'filters'    => [                                 // 自定义过滤（请求参数名 => 字段）
            'status',                                     // 同名字段精确匹配
            'keyword' => ['name', 'email'],               // 多字段模糊匹配
            'date_from' => fn ($row, $v) => $row['created_at'] >= $v,  // 闭包自定义
        ],
        'transform'  => fn ($row) => $row + ['extra' => '...'],  // 行输出转换
    ]);
}

// 或直接使用 Laravel 查询构造器（包内鸭子类型识别，自动 count/forPage/get，不强制依赖 Laravel）
public function usersBuilder(Request $request)
{
    return XfAdmin::dataResponse(User::query(), $request->all(), [
        'searchable' => ['name', 'email'],
        'filters'    => ['status', 'keyword' => ['name', 'email']],
    ]);
}
```

`XfAdmin::dataResponse($rows, $params, $options)` 等价于 `zxf\XfAdmin\Support\DataSet::response(...)`，返回标准协议数组：

```json
{ "draw": 1, "recordsTotal": 53, "recordsFiltered": 11, "data": [ ... ] }
```

`DataSet` 对请求做了全面容错：`search[value]` 可能为 `null`（Laravel `ConvertEmptyStringsToNull` 中间件会把空串转为 `null`）、`columns`/`order` 可能为数组或标量混杂、参数缺失等均不崩；`length` 超过上限（1000）会被强制收敛，避免超大分页拖垮服务。

### 前端：开启服务端模式

```php
XfAdmin::dataTable([
    'id'         => 'user-table',
    'server_side'=> true,
    'ajax'       => '/api/users/datatable',     // 返回上述协议 JSON
    'columns'    => [
        'id'    => ['label' => 'ID'],
        'name'  => ['label' => '姓名', 'searchable' => true],
        'email' => ['label' => '邮箱', 'searchable' => true],
    ],
    'order'      => [[0, 'desc']],
    'language'   => 'zh',
    'buttons'    => ['refresh', 'colvis', 'fullscreen'],  // refresh/fullscreen 为包内扩展按钮
]);
```

- `server_side => true` + `ajax` 时自动注入 `serverSide: true`、`processing: true` 与中文语言包。
- 内置扩展按钮 `refresh`（重载当前表格）、`fullscreen`（表格全屏）由前端 `xfButton` 接管，无需后端配合。

---

## 富单元格渲染器

`dataTable` 列支持「超级丰富」的单元格形态，只需在列定义里写 `render` 或快捷键即可，前端 `XFAdmin.cellRenderers` 自动渲染。**所有用户输入均经 `escapeHtml` 转义，杜绝 XSS。**

### 列定义速览

```php
XfAdmin::dataTable([
    'columns' => [
        'id'      => ['label' => 'ID'],

        // 输入框（失焦/回车 AJAX 保存）
        'nickname'=> ['label' => '昵称', 'render' => 'input', 'xfCellInput' => ['url' => '/api/cell/nickname']],

        // 带复制按钮的输入框（只读展示 + 一键复制）
        'token'   => ['label' => 'Token', 'render' => 'copy', 'xfCopy' => ['text' => true]],

        // 复制按钮（直接复制整格文本）
        'code'    => ['label' => '邀请码', 'render' => 'copy'],

        // IP 地址展示
        'ip'      => ['label' => '登录IP', 'render' => 'ip'],

        // 状态开关（点击 AJAX 切换）
        'status'  => ['label' => '状态', 'render' => 'switch', 'xfSwitch' => ['url' => '/api/switch/status']],

        // 标签组（数组字段渲染为多色标签）
        'tags'    => ['label' => '角色', 'render' => 'tags'],

        // 颜色色块
        'color'   => ['label' => '主题色', 'render' => 'color'],

        // 进度条
        'progress'=> ['label' => '完成度', 'render' => 'progress'],

        // 评分（星级）
        'rating'  => ['label' => '评分', 'render' => 'rating'],

        // 金额（千分位 + 货币符号）
        'balance' => ['label' => '余额', 'render' => 'money', 'xfMoney' => ['symbol' => '¥']],

        // 布尔值（是/否 徽标）
        'active'  => ['label' => '启用', 'render' => 'bool'],

        // 链接
        'homepage'=> ['label' => '主页', 'render' => 'link', 'xfLink' => ['target' => '_blank']],

        // 头像
        'avatar'  => ['label' => '头像', 'render' => 'avatar'],

        // 时间（自动友好格式化）
        'created_at' => ['label' => '注册时间', 'render' => 'datetime'],

        // 长文本截断 + tooltip
        'remark'  => ['label' => '备注', 'render' => 'truncate'],

        // 代码块
        'payload' => ['label' => 'Payload', 'render' => 'code'],

        // 图标
        'os'      => ['label' => '系统', 'render' => 'icon', 'xfIcon' => ['map' => ['ios' => 'ti ti-brand-apple', 'android' => 'ti ti-brand-android']]],

        // 自定义渲染函数（前端不可用时回退后端渲染）
        'custom'  => ['label' => '自定义', 'render' => fn ($row) => "<b>{$row['id']}</b>"],

        // 操作栏（见下节）
        'op'      => ['label' => '操作', 'actions' => [ ... ]],
    ],
    'data' => $rows,
]);
```

### 渲染器总表

| 渲染器 | 快捷键 | 说明 |
|--------|--------|------|
| `text` | — | 纯文本（`escapeHtml` 转义） |
| `input` | `render => 'input'` + `xfCellInput` | 可编辑输入框，失焦/回车触发 `xf:cell-input` 事件，由前端自动 POST `xfCellInput.url` |
| `copy` | `render => 'copy'` | 复制按钮 + 文本；`xfCopy => ['text' => true]` 为「只读框 + 复制按钮」样式 |
| `ip` | `render => 'ip'` | IP 等宽字体展示 |
| `switch` | `render => 'switch'` + `xfSwitch` | 开关，点击触发 `xf:switch` 事件，自动 POST `xfSwitch.url`，失败回滚 |
| `tags` | `render => 'tags'` | 数组/逗号串 → 多色 `badge` |
| `color` | `render => 'color'` | 色块 + 色值 |
| `progress` | `render => 'progress'` | 0–100 进度条，可带 `xfProgress` 配色 |
| `rating` | `render => 'rating'` | 5 星评分（只读） |
| `money` | `render => 'money'` | 千分位金额，`xfMoney => ['symbol' => '¥']` |
| `bool` | `render => 'bool'` | true/false → 是/否徽标 |
| `link` | `render => 'link'` | 超链接，`xfLink => ['target' => '_blank']` |
| `image` | `render => 'image'` | 缩略图，`xfImage => ['width' => 40]` |
| `avatar` | `render => 'avatar'` | 圆形头像（图片或首字母） |
| `datetime` | `render => 'datetime'` | 友好时间格式 |
| `truncate` | `render => 'truncate'` | 截断 + `title` 完整提示 |
| `code` | `render => 'code'` | 等宽代码块 |
| `icon` | `render => 'icon'` | 值 → 图标，`xfIcon => ['map' => [...]]` |
| `view` | `render => 'view'` | 整行 JSON 弹窗（只读） |
| `actions` | `actions => [...]` | 行操作栏（见下节） |

> 前端渲染器全部挂在 `XFAdmin.cellRenderers` 上，可通过 `XFAdmin.cellRenderers.myType = (cell, row, col) => '...'` 自定义扩展（见[扩展组件](extending.md)）。

### 操作栏（actions 列）

`actions` 接收按钮数组，支持下拉、图标、确认、AJAX、自定义事件等所有交互：

```php
'op' => ['label' => '操作', 'actions' => [
    ['text' => '查看', 'icon' => 'ti ti-eye', 'act' => 'view'],
    ['text' => '编辑', 'icon' => 'ti ti-edit', 'act' => 'link', 'url' => '/edit/{id}'],
    ['text' => '复制', 'icon' => 'ti ti-copy', 'act' => 'copy-row', 'confirm' => '复制本条？'],
    [
        'text' => '更多', 'icon' => 'ti ti-dots', 'dropdown' => true, 'items' => [
            ['text' => '禁用', 'icon' => 'ti ti-lock', 'act' => 'ajax', 'url' => '/ban/{id}', 'confirm' => '确认禁用？'],
            ['text' => '删除', 'icon' => 'ti ti-trash', 'act' => 'ajax', 'url' => '/del/{id}', 'confirm' => '确认删除？', 'class' => 'text-danger'],
        ],
    ],
]],
```

按钮 `act` 语义：

| act | 行为 |
|-----|------|
| `view` | 弹窗展示整行数据（只读） |
| `link` | 跳转 `url`（支持 `{id}` 占位替换为当前行 id） |
| `copy-row` | 复制整行 JSON 到剪贴板（可选 `confirm`） |
| `ajax` | 发送 `fetch` 到 `url`，可选 `confirm`，成功后 toast 并自动重载表格 |
| `custom` | 仅派发 `xf:action` 事件，交由你的代码处理（见[扩展组件](extending.md)） |
| `dialog` / `form` | 弹窗（可内嵌表单），配合自定义事件完成复杂操作 |

---

## 过滤工具栏 `filter_bar`

`filter_bar` 是一等公民：配置后自动在表格上方渲染筛选控件（select / text / date / radio），变更后由前端自动拼接为查询参数并重载表格，无需任何手动接线。

```php
XfAdmin::dataTable([
    'id'      => 'log-table',
    'columns' => ['id' => 'ID', 'level' => '级别', 'message' => '内容', 'created_at' => '时间'],
    'data'    => [],
    'server_side' => true,
    'ajax'    => '/api/logs/datatable',
    'filter_bar' => [
        ['name' => 'level',  'label' => '级别', 'options' => ['error' => '错误', 'info' => '信息']],
        ['name' => 'keyword','label' => '关键词', 'type' => 'text', 'placeholder' => '消息内容'],
        ['name' => 'date_from', 'label' => '开始', 'type' => 'date'],
        ['name' => 'date_to',   'label' => '结束', 'type' => 'date'],
        ['name' => 'src', 'label' => '来源', 'type' => 'radio', 'options' => ['web' => 'Web', 'api' => 'API']],
    ],
]);
```

- 前端把各控件值合并进 `ajax.url` 的查询串后 `table.ajax.url(url).load()`，后端 `DataSet` 通过 `filters` 选项消费这些参数。
- 渲染一个「重置」按钮，点击清空全部筛选。
- 控件 `type` 支持：`select`（默认，需 `options`）、`text`、`date`、`radio`（按钮组单选）。

---

## 复杂筛选（前端联动）

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

---

## 导出按钮所需资源

`buttons` 含 `pdf` 时会自动附加 `pdfmake`；含 `excel` 会附加 `jszip`，均随包内置、离线可用、去重加载。

`buttons` 支持：`copy`、`csv`、`excel`、`pdf`、`print`、`colvis`、`refresh`、`fullscreen`。其中 `refresh`/`fullscreen` 为包内扩展按钮（`xfButton`），由前端接管为「重载表格 / 表格全屏」，不依赖后端导出库。

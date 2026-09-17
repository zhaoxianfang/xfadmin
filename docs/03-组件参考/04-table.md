# 表格组件

> 静态表格、DataTables 数据表格（服务端/客户端）、自定义表格与列表工具条。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`table`](#table) — 静态表格（基础 HTML 表格封装）
- [`dataTable`](#datatable) — 数据表格（DataTables：服务端/客户端、导出、固定列等）
- [`tablesCustom`](#tablescustom) — 自定义表格（可编辑/带控件的复杂表）
- [`dataTableToolbar`](#datatabletoolbar) — 列表工具条 搜索 + 列筛选 + 每页条数 + 批量操作按钮 + 视图切换， 复刻文件管理器 / 订单等页面的列表工具栏

## 本章导读

| 组件 | 适用场景 |
|---|---|
| `table` | 静态表格，服务端渲染真实 `<tbody>`（SEO/打印友好） |
| `dataTable` | 数据表格：服务端/客户端、排序、搜索、分页、导出、行操作（**功能最全**） |
| `tablesCustom` | 简单自定义表格（带控件单元格 + tfoot），无排序分页 |
| `dataTableToolbar` | 独立工具条（搜索/筛选/每页条数/视图切换），可搭配任意表格 |

### 完整能力见

- [03-数据表格全指南](../04-进阶指南/03-datatable.md) —— 列定义、52 种渲染器、行操作、过滤栏、批量、导出、服务端对接、陷阱清单
- [05-服务端数据协议](../02-核心架构/05-data-protocol.md) —— `XfAdmin::dataResponse()` 的请求/响应与 `filters` 语法

### 组合范式

```php
echo XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',
    'columns'     => [
        ['key' => 'id', 'label' => 'ID'],
        ['key' => 'name', 'label' => '姓名'],
        ['key' => 'status', 'label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],
        ['key' => '', 'label' => '操作', 'actions' => [...]],
    ],
    'filter_bar'  => [['name' => 'keyword', 'label' => '关键词', 'type' => 'text']],
]);
```

### 约定与陷阱

- `row_detail` 与 `responsive` **互斥**（都占首列）；
- 启用 `bulk`/`row_detail` 后，`order` 的列索引会被辅助列顶偏；
- `data` 为空的列（操作列/明细列）必须 `defaultContent`（组件已自动补 `""`）；
- 导出按钮用 `print` 而非 `printHtml5`；
- 只有 `buttons`/`export` 中的 `pdf` 会自动加载 pdfmake（写在 `options.buttons` 里检测不到）；
- `row_group` 对服务端模式无效；`row_url` 当前实现无效。


---

### `table`

静态表格（基础 HTML 表格封装）。

> **类**：`zxf\XfAdmin\Components\Table\Table`
> **文件**：`src/Components/Table/Table.php`（149 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::table([
    'columns' => [
        'name' => '姓名',
        'age'  => ['label' => '年龄', 'class' => 'text-center'],
        'op'   => ['label' => '操作', 'format' => fn ($row) => '<a href="/edit/' . $row['id'] . '">编辑</a>', 'raw' => true],
    ],
    'data'    => [['id'=>1,'name'=>'张三','age'=>20], ...],
    'striped' => true, 'hover' => true, 'bordered' => false, 'sm' => false,
    'variant' => null,          // dark|light|primary...  => table-*
    'responsive' => true,
    'caption' => null,
    'empty'   => '暂无数据',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::table([
    'columns' => [],
    'data' => [],
    'striped' => false,
    'striped_cols' => false,
    'hover' => false,
    'bordered' => false,
    'borderless' => false,
    'sm' => false,
    'align_middle' => false,
    'centered' => false,
    'nowrap' => false,
    'variant' => null,
    'head_variant' => null,    // light|dark => table-*
    'responsive' => true,
    'caption' => null,
    'empty' => '暂无数据',
    'row_attrs' => null,    // fn($row): array
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `columns[]` 元素键：`key`、`data`、`title`、`label`、`orderable`、`sortable`、`xfBadges`、`badges`、`xfTemplate`、`template`；补充：`键名 => 标题` 或 `[['key'=>..,'label'=>..,'class'=>..,'format'=>闭包,'raw'=>bool]]`
- `data[]`：行数据数组（数组或对象均可）

> **渲染骨架**：主要 class `table-responsive`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `striped` | bool | `false` | 是否斑马纹；源码用法：`'table-striped' => $this->get('striped'),` |
| `striped_cols` | bool | `false` | 源码用法：`'table-striped-columns' => $this->get('striped_cols'),` |
| `hover` | bool | `false` | 是否悬停高亮；源码用法：`'table-hover' => $this->get('hover'),` |
| `bordered` | bool | `false` | 是否显示边框；源码用法：`'table-bordered' => $this->get('bordered'),` |
| `borderless` | bool | `false` | 是否无边框；源码用法：`'table-borderless' => $this->get('borderless'),` |
| `sm` | bool | `false` | 是否紧凑尺寸（table-sm / 小型）；源码用法：`'table-sm' => $this->get('sm'),` |
| `align_middle` | bool | `false` | 单元格垂直居中；源码用法：`'align-middle' => $this->get('align_middle'),` |
| `centered` | bool | `false` | 是否垂直居中；源码用法：`'table-centered' => $this->get('centered'),` |
| `nowrap` | bool | `false` | 源码用法：`'table-nowrap' => $this->get('nowrap'),` |
| `variant` | mixed | `null` | 源码用法：`], $this->get('variant') ? 'table-' . $this->enum($this->get('variant'), ['light', 'd…`；可选值：`primary` / `secondary` / `success` / `danger` / `warning` / `info` / `light` / `dark` / `link` |
| `head_variant` | mixed | `null` | 枚举白名单 `'light', 'dark', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'striped', 'striped-dark'` |
| `responsive` | bool | `true` | 是否响应式（横向滚动 / 响应式表格）；开关：非空 / 真值时启用对应区块 |
| `caption` | mixed | `null` | 表格 caption 文本；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `empty` | string | `'暂无数据'` | 空数据提示文案；**文本槽位**：输出前自动 HTML 转义 |
| `row_attrs` | mixed | `null` | fn($row): array |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `dataTable`

数据表格（DataTables：服务端/客户端、导出、固定列等）。

> **类**：`zxf\XfAdmin\Components\Table\DataTable`
> **文件**：`src/Components/Table/DataTable.php`（813 行）
> **依赖插件**：`datatables`、`datatables-pdf`、`select2`、`qrcode`

**用法示例**

```php
// ① 客户端模式（本地数据）
echo XfAdmin::dataTable([
    'id'      => 'user-table',
    'columns' => [
        ['key' => 'id',    'label' => 'ID', 'width' => '70px'],
        ['key' => 'name',  'label' => '姓名'],
        ['key' => 'status','label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],
        ['key' => 'created_at', 'label' => '注册时间', 'render' => ['type' => 'datetime', 'ago' => true]],
        ['key' => '', 'label' => '操作', 'actions' => [
            ['label' => '编辑', 'icon' => 'ti ti-pencil', 'action' => 'edit', 'ajax' => '/admin/users/{id}'],
            ['label' => '删除', 'icon' => 'ti ti-trash', 'action' => 'ajax', 'class' => 'btn-soft-danger',
             'ajax' => '/admin/users/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？'],
        ]],
    ],
    'data'    => $rows,
]);

// ② 服务端模式（配合 XfAdmin::dataResponse()）
echo XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',
    'columns'     => [ /* 同上 */ ],
    'filter_bar'  => [
        ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
        ['name' => 'status',  'label' => '状态',   'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],
        ['name' => 'date',    'label' => '注册时间', 'type' => 'daterange'],
    ],
    'create' => ['page' => '/admin/users/create', 'label' => '新增用户'],
    'bulk'   => ['actions' => [
        ['label' => '批量删除', 'url' => '/admin/users/batch-delete', 'method' => 'DELETE', 'confirm' => '确认？'],
    ]],
    'export' => ['copy', 'excel', 'csv'],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::dataTable([
    'columns' => [],    // 列定义数组
    'data' => [],    // 数据数组（行数据 / 图表数据）
    'striped' => true,
    'striped_cols' => false,
    'hover' => false,    // 是否悬停高亮
    'bordered' => false,    // 是否显示边框
    'borderless' => false,    // 是否无边框
    'sm' => false,    // 是否紧凑尺寸（table-sm / 小型）
    'align_middle' => true,
    'centered' => false,    // 是否垂直居中
    'nowrap' => false,
    'variant' => null,    // 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束）
    'head_variant' => null,    // 保留 table-light/dark 语义；模板风格表头见 head_class
    'responsive' => false,    // 默认关闭响应式折叠（避免列被收进 child 子行），依赖 scrollX 水平滚动处理溢出列；row_detail 启用时仍互斥
    'caption' => null,    // 表格 caption 文本
    'empty' => '暂无数据',    // 空数据提示文案
    'row_attrs' => null,    // 行属性回调 `fn($row): array`，返回 `<tr>` 属性
    'head_class' => 'thead-sm text-uppercase fs-xxs',
    'ajax' => null,
    'server_side' => false,
    'method' => 'GET',    // 服务端数据请求方式（POST 可规避 URL 长度限制/WAF）
    'searching' => false,    // 默认关闭内置搜索，改用自定义搜索表单
    'ordering' => true,
    'paging' => true,
    'info' => true,
    'processing' => true,    // 默认显示加载处理提示
    'page_length' => 10,
    // length_menu：标准每页条数菜单
    'length_menu' => [
        10,
        15,
        20,
        25,
        50,
    ],
    'show_custom_search' => true,    // 启用自定义搜索表单
    'row_detail' => null,    // 行详情展开 callback / 列名（null=不启用）
    'show_header_btn' => null,    // 表头按钮区域 ['create'=>'/url','refresh'=>true,'search'=>true]
    'created_row' => null,    // createdRow 回调（JS 函数名/全局函数引用）
    'draw_callback' => null,    // drawCallback 全局函数名
    'buttons' => [],
    'select' => false,
    'fixed_header' => false,
    'column_filters' => false,
    'order' => [],    // [[0, 'asc']]
    'language' => null,    // DataTables 语言包配置数组（缺省内置中文）
    'row_id' => null,    // 行 id 字段
    'row_url' => null,    // 整行点击跳转 URL（支持 {id} 占位符，如 '/admin/app/{module}/{page}/detail/{id}'）
    'auto_width' => null,
    'scroll_x' => true,    // 默认启用横向滚动，溢出列通过滚动条展示（scroll_x=false 可关闭）
    'fixed_columns' => null,    // 固定列：true=左1列；['left'=>2,'right'=>1]（自动开启横向滚动）
    'filter_bar' => [],    // 过滤工具栏（服务端模式自动拼接查询参数并重载）
    'filter_auto' => false,    // 过滤工具栏是否即改即查（默认点击“搜索”按钮才发起请求）
    'create' => null,    // 「新增」弹窗按钮：'/xx/create' 或 ['page'=>..,'label'=>..,'title'=>..,'size'=>..,'frame'=>..,'icon'=>..,'class'=>..]
    'density' => null,    // 'compact' | 'comfortable'（行间距密度；默认舒适）
    'options' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `columns[]` 元素键：`actions`、`render`；补充：`key`/`data`（字段名）、`label`/`title`（表头）、`width`、`minWidth`、`class`、`sortable`、`searchable`、`visible`、`render`、`badges`、`template`、`actions`
- `data[]`：本地行数据（每行字段与列 `key` 对应）
- `buttons[]`：`copy`/`csv`/`excel`/`pdf`/`print`/`colvis`/`refresh`/`fullscreen`/`density`，或原生按钮配置数组
- `order[]`：`[[列索引, 'asc'|'desc']]`（启用 bulk/row_detail 后索引会被辅助列顶偏）
- `filter_bar[]` 元素键：`multiple`、`type`；补充：`name`、`label`、`type`、`options`、`width`、`placeholder`、`min`/`max`/`step`、`html`
- `options[]` 元素键：`order`、`ajax`、`serverSide`、`data`、`fixedHeader`、`responsive`、`scrollX`、`autoWidth`、`scrollCollapse`、`scrollY`、`deferRender`、`rowId`、`select`、`buttons`、`layout`、`language`、`stateSave`；补充：透传 DataTables 原生配置（递归合并，优先级最高）

> **渲染骨架**：主要 class `table-responsive`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据）；源码用法：`$rowCount = is_array($this->get('data')) ? count((array) $this->get('data')) : 0;` |
| `striped` | bool | `true` | 是否斑马纹 |
| `striped_cols` | bool | `false` |  |
| `hover` | bool | `false` | 是否悬停高亮 |
| `bordered` | bool | `false` | 是否显示边框 |
| `borderless` | bool | `false` | 是否无边框 |
| `sm` | bool | `false` | 是否紧凑尺寸（table-sm / 小型） |
| `align_middle` | bool | `true` | 单元格垂直居中 |
| `centered` | bool | `false` | 是否垂直居中 |
| `nowrap` | bool | `false` |  |
| `variant` | mixed | `null` | 可选值：`primary` / `secondary` / `success` / `danger` / `warning` / `info` / `light` / `dark` / `link` |
| `head_variant` | mixed | `null` | 枚举白名单 `'light', 'dark', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'striped', 'striped-dark'` |
| `responsive` | bool | `false` | 默认关闭响应式折叠（避免列被收进 child 子行），依赖 scrollX 水平滚动处理溢出列；row_detail 启用时仍互斥；开关：非空 / 真值时启用对应区块 |
| `caption` | mixed | `null` | 表格 caption 文本 |
| `empty` | string | `'暂无数据'` | 空数据提示文案 |
| `row_attrs` | mixed | `null` | 行属性回调 `fn($row): array`，返回 `<tr>` 属性 |
| `head_class` | string | `'thead-sm text-uppercase fs-xxs'` | 追加到 thead 的 class |
| `ajax` | mixed | `null` | AJAX 地址或 DataTables 原生 ajax 配置；开关：非空 / 真值时启用对应区块 |
| `server_side` | bool | `false` | 服务端分页模式；开关：非空 / 真值时启用对应区块 |
| `method` | string | `'GET'` | 服务端数据请求方式（POST 可规避 URL 长度限制/WAF） |
| `searching` | bool | `false` | 默认关闭内置搜索，改用自定义搜索表单 |
| `ordering` | bool | `true` | 是否允许排序；源码用法：`'ordering' => (bool) $this->get('ordering'),` |
| `paging` | bool | `true` | 是否分页；源码用法：`'paging' => (bool) $this->get('paging'),` |
| `info` | bool | `true` | 是否显示分页信息；源码用法：`'info' => (bool) $this->get('info'),` |
| `processing` | bool | `true` | 默认显示加载处理提示 |
| `page_length` | int | `10` | 每页条数；源码用法：`'pageLength' => (int) $this->get('page_length'),` |
| `length_menu` | array | `[10, 15, 20, 25, 50]` | 标准每页条数菜单 |
| `show_custom_search` | bool | `true` | 启用自定义搜索表单 |
| `row_detail` | mixed | `null` | 行详情展开 callback / 列名（null=不启用）；开关：非空 / 真值时启用对应区块 |
| `show_header_btn` | mixed | `null` | 表头按钮区域 ['create'=>'/url','refresh'=>true,'search'=>true] |
| `created_row` | mixed | `null` | createdRow 回调（JS 函数名/全局函数引用） |
| `draw_callback` | mixed | `null` | drawCallback 全局函数名 |
| `buttons` | array | `[]` | 按钮定义数组 |
| `select` | bool | `false` | 行选择：`true` 或 `['style'=>'multi']`；开关：非空 / 真值时启用对应区块 |
| `fixed_header` | bool | `false` | 表头固定；开关：非空 / 真值时启用对应区块 |
| `column_filters` | bool | `false` | 表头追加列筛选输入行；源码用法：`'columnFilters' => (bool) $this->get('column_filters'),` |
| `order` | array | `[]` | [[0, 'asc']]；开关：非空 / 真值时启用对应区块 |
| `language` | mixed | `null` | DataTables 语言包配置数组（缺省内置中文）；开关：非空 / 真值时启用对应区块 |
| `row_id` | mixed | `null` | 行 id 字段；开关：非空 / 真值时启用对应区块 |
| `row_url` | mixed | `null` | 整行点击跳转 URL（支持 {id} 占位符，如 '/admin/app/{module}/{page}/detail/{id}'）；开关：非空 / 真值时启用对应区块 |
| `auto_width` | mixed | `null` | 自动列宽（false 会影响 scrollX 判定）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `scroll_x` | bool | `true` | 默认启用横向滚动，溢出列通过滚动条展示（scroll_x=false 可关闭）；开关：非空 / 真值时启用对应区块 |
| `fixed_columns` | mixed | `null` | 固定列：true=左1列；['left'=>2,'right'=>1]（自动开启横向滚动） |
| `filter_bar` | array | `[]` | 过滤工具栏（服务端模式自动拼接查询参数并重载） |
| `filter_auto` | bool | `false` | 过滤工具栏是否即改即查（默认点击“搜索”按钮才发起请求） |
| `create` | mixed | `null` | 「新增」弹窗按钮：'/xx/create' 或 ['page'=>..,'label'=>..,'title'=>..,'size'=>..,'frame'=>..,'icon'=>..,'class'=>..] |
| `density` | mixed | `null` | 'compact' \| 'comfortable'（行间距密度；默认舒适） |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `tablesCustom`

自定义表格（可编辑/带控件的复杂表）。

> **类**：`zxf\XfAdmin\Components\Table\TablesCustom`
> **文件**：`src/Components/Table/TablesCustom.php`（105 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::tablesCustom([
    'columns' => ['#', 'Name', 'Email', 'Role', 'Status'],
    'rows' => [
        ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'role' => 'Admin', 'status' => 'active'],
        ...
    ],
    'headerBg' => 'primary',
    'striped' => true,
    'hover' => true,
    'bordered' => true,
    'compact' => false,
    'footable' => [],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::tablesCustom([
    'columns' => [],
    'rows' => [],
    'headerBg' => '',
    'striped' => true,
    'hover' => true,
    'bordered' => true,
    'compact' => false,
    'footable' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `columns[]` 元素键：`label`；补充：字符串（直接作表头）或 `['label'=>..,'class'=>..]`
- `rows[]`：按**位置顺序**输出单元格：字符串（转义）或 `['class'=>..,'html'=>..]`（原样）
- `footable[]`：表尾行，规则同 `rows`

> **渲染骨架**：主要 class `card` `card-body` `p-0` `table-responsive`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `rows` | array | `[]` | 行数据数组 |
| `headerBg` | string | `''` | 表头背景语义色（如 `primary`） |
| `striped` | bool | `true` | 是否斑马纹 |
| `hover` | bool | `true` | 是否悬停高亮 |
| `bordered` | bool | `true` | 是否显示边框 |
| `compact` | bool | `false` | 紧凑模式 |
| `footable` | array | `[]` | 表尾行数据 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `dataTableToolbar`

列表工具条 搜索 + 列筛选 + 每页条数 + 批量操作按钮 + 视图切换， 复刻文件管理器 / 订单等页面的列表工具栏。 'table'    => 'dt-orders',        // 关联的表格 id；留空则自动查找同容器中最近的表格 'search'   => true, 'searchPlaceholder' => '搜索...', 'filters'  => [ ['label'=>'全部', 'value'=>''], ['label'=>'待处理','value'=>'pending'] ], 'filterField' => 'status',        // 筛选绑定的列（列索引或 DataTables 列名） 'pageSize' => [10, 20, 50], 'actions'  => [ ['label'=>'导出','variant'=>'outline-secondary','icon'=>'ti ti-download'] ], 'views'    => [ ['label'=>'网格','icon'=>'ti ti-layout-grid','active'=>true], ['label'=>'列表','icon'=>'ti ti-list'] ], ]) 交互由 xfadmin.js 的 dt-search / dt-filter / dt-pagesize / dt-views 四个 widget 托管： 搜索与筛选为输入防抖后重绘、每页条数调用 page.len()、视图切换同步 active 并派发 xf.dtview.change 事件（detail: {view, button, api}）供业务切换渲染形态。。

> **类**：`zxf\XfAdmin\Components\Table\DataTableToolbar`
> **文件**：`src/Components/Table/DataTableToolbar.php`（89 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::dataTableToolbar([
    'table'    => 'dt-orders',        // 关联的表格 id；留空则自动查找同容器中最近的表格
    'search'   => true,
    'searchPlaceholder' => '搜索...',
    'filters'  => [ ['label'=>'全部', 'value'=>''], ['label'=>'待处理','value'=>'pending'] ],
    'filterField' => 'status',        // 筛选绑定的列（列索引或 DataTables 列名）
    'pageSize' => [10, 20, 50],
    'actions'  => [ ['label'=>'导出','variant'=>'outline-secondary','icon'=>'ti ti-download'] ],
    'views'    => [ ['label'=>'网格','icon'=>'ti ti-layout-grid','active'=>true], ['label'=>'列表','icon'=>'ti ti-list'] ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::dataTableToolbar([
    'table' => null,    // 关联表格 id（不含 #）；留空则自动就近查找
    'search' => true,
    'searchPlaceholder' => '搜索...',
    'filters' => [],
    'filterField' => null,    // 筛选列：列索引（0 起）或 DataTables 列名
    // pageSize
    'pageSize' => [
        10,
        20,
        50,
    ],
    'actions' => [],
    'views' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `filters[]` 元素键：`value`、`label`
- `actions[]` 元素键：`variant`（默认 `outline-secondary`）、`icon`、`label`
- `views[]` 元素键：`active`、`name`、`label`、`icon`

> **渲染骨架**：主要 class `xf-dt-toolbar` `d-flex` `flex-wrap` `gap-2` `align-items-center` `mb-3` `flex-grow-1` `app-search`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `table` | mixed | `null` | 关联表格 id（不含 #）；留空则自动就近查找 |
| `search` | bool | `true` | 是否启用搜索；源码用法：`$search = $this->get('search');` |
| `searchPlaceholder` | string | `'搜索...'` | 源码用法：`$ph = $this->get('searchPlaceholder');` |
| `filters` | array | `[]` | 过滤条件定义 |
| `filterField` | mixed | `null` | 筛选列：列索引（0 起）或 DataTables 列名 |
| `pageSize` | array | `[10, 20, 50]` | 数组结构（见「全参数示例」） |
| `actions` | array | `[]` | 操作区内容（按钮组 / 行操作定义） |
| `views` | array | `[]` | 浏览量数据 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。


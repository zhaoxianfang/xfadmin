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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `striped` | bool | `false` | 是否斑马纹 |
| `striped_cols` | bool | `false` |  |
| `hover` | bool | `false` | 是否悬停高亮 |
| `bordered` | bool | `false` | 是否显示边框 |
| `borderless` | bool | `false` | 是否无边框 |
| `sm` | bool | `false` | 是否紧凑尺寸（table-sm / 小型） |
| `align_middle` | bool | `false` |  |
| `centered` | bool | `false` | 是否垂直居中 |
| `nowrap` | bool | `false` |  |
| `variant` | mixed | `null` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `head_variant` | mixed | `null` | light\|dark => table-* |
| `responsive` | bool | `true` | 是否响应式（横向滚动 / 响应式表格） |
| `caption` | mixed | `null` | 表格 caption 文本 |
| `empty` | string | `'暂无数据'` | 空数据提示文案 |
| `row_attrs` | mixed | `null` | fn($row): array |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `dataTable`

数据表格（DataTables：服务端/客户端、导出、固定列等）。

> **类**：`zxf\XfAdmin\Components\Table\DataTable`
> **文件**：`src/Components/Table/DataTable.php`（813 行）
> **依赖插件**：`datatables`、`datatables-pdf`、`select2`、`qrcode`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `striped` | bool | `true` | 是否斑马纹 |
| `striped_cols` | bool | `false` |  |
| `hover` | bool | `false` | 是否悬停高亮 |
| `bordered` | bool | `false` | 是否显示边框 |
| `borderless` | bool | `false` | 是否无边框 |
| `sm` | bool | `false` | 是否紧凑尺寸（table-sm / 小型） |
| `align_middle` | bool | `true` |  |
| `centered` | bool | `false` | 是否垂直居中 |
| `nowrap` | bool | `false` |  |
| `variant` | mixed | `null` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `head_variant` | mixed | `null` | 保留 table-light/dark 语义；模板风格表头见 head_class |
| `responsive` | bool | `false` | 默认关闭响应式折叠（避免列被收进 child 子行），依赖 scrollX 水平滚动处理溢出列；row_detail 启用时仍互斥 |
| `caption` | mixed | `null` | 表格 caption 文本 |
| `empty` | string | `'暂无数据'` | 空数据提示文案 |
| `row_attrs` | mixed | `null` |  |
| `head_class` | string | `'thead-sm text-uppercase fs-xxs'` |  |
| `ajax` | mixed | `null` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `server_side` | bool | `false` | 服务端分页模式 |
| `method` | string | `'GET'` | 服务端数据请求方式（POST 可规避 URL 长度限制/WAF） |
| `searching` | bool | `false` | 默认关闭内置搜索，改用自定义搜索表单 |
| `ordering` | bool | `true` | 是否允许排序 |
| `paging` | bool | `true` | 是否分页 |
| `info` | bool | `true` |  |
| `processing` | bool | `true` | 默认显示加载处理提示 |
| `page_length` | int | `10` | 每页条数 |
| `length_menu` | array | `[10, 15, 20, 25, 50]` | 标准每页条数菜单 |
| `show_custom_search` | bool | `true` | 启用自定义搜索表单 |
| `row_detail` | mixed | `null` | 行详情展开 callback / 列名（null=不启用） |
| `show_header_btn` | mixed | `null` | 表头按钮区域 ['create'=>'/url','refresh'=>true,'search'=>true] |
| `created_row` | mixed | `null` | createdRow 回调（JS 函数名/全局函数引用） |
| `draw_callback` | mixed | `null` | drawCallback 全局函数名 |
| `buttons` | array | `[]` | 按钮定义数组 |
| `select` | bool | `false` | 行选择：`true` 或 `['style'=>'multi']` |
| `fixed_header` | bool | `false` | 表头固定 |
| `column_filters` | bool | `false` |  |
| `order` | array | `[]` | [[0, 'asc']] |
| `language` | mixed | `null` | DataTables 语言包配置数组（缺省内置中文） |
| `row_id` | mixed | `null` | 行 id 字段 |
| `row_url` | mixed | `null` | 整行点击跳转 URL（支持 {id} 占位符，如 '/admin/app/{module}/{page}/detail/{id}'） |
| `auto_width` | mixed | `null` |  |
| `scroll_x` | bool | `true` | 默认启用横向滚动，溢出列通过滚动条展示（scroll_x=false 可关闭） |
| `fixed_columns` | mixed | `null` | 固定列：true=左1列；['left'=>2,'right'=>1]（自动开启横向滚动） |
| `filter_bar` | array | `[]` | 过滤工具栏（服务端模式自动拼接查询参数并重载） |
| `filter_auto` | bool | `false` | 过滤工具栏是否即改即查（默认点击“搜索”按钮才发起请求） |
| `create` | mixed | `null` | 「新增」弹窗按钮：'/xx/create' 或 ['page'=>..,'label'=>..,'title'=>..,'size'=>..,'frame'=>..,'icon'=>..,'class'=>..] |
| `density` | mixed | `null` | 'compact' \| 'comfortable'（行间距密度；默认舒适） |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `rows` | array | `[]` | 行数据数组 |
| `headerBg` | string | `''` |  |
| `striped` | bool | `true` | 是否斑马纹 |
| `hover` | bool | `true` | 是否悬停高亮 |
| `bordered` | bool | `true` | 是否显示边框 |
| `compact` | bool | `false` |  |
| `footable` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `table` | mixed | `null` | 关联表格 id（不含 #）；留空则自动就近查找 |
| `search` | bool | `true` | 是否启用搜索 |
| `searchPlaceholder` | string | `'搜索...'` |  |
| `filters` | array | `[]` | 过滤条件定义 |
| `filterField` | mixed | `null` | 筛选列：列索引（0 起）或 DataTables 列名 |
| `pageSize` | array | `[10, 20, 50]` | 数组结构（见组件用法示例） |
| `actions` | array | `[]` | 操作区内容（按钮组 / 行操作定义） |
| `views` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


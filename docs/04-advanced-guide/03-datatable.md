# 数据表格全指南

`XfAdmin::dataTable()` 是包内功能最丰富的组件：服务端/客户端双模式、
52 种单元格渲染器、行操作、过滤栏、批量操作、导出、固定列、行明细、行分组、
列筛选、状态保存等。

> 源码：`src/Components/Table/DataTable.php`（825 行）
> 前端：`resources/assets/js/xfadmin.js` 中 `XFAdmin.register('datatable', …)`

## 1. 快速上手

### 1.1 客户端模式（本地数据）

```php
echo XfAdmin::dataTable([
    'id'      => 'user-table',
    'columns' => [
        ['key' => 'id', 'label' => 'ID', 'width' => '60px'],
        ['key' => 'name', 'label' => '姓名'],
        ['key' => 'status', 'label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],
    ],
    'data'    => $rows,
]);
```

### 1.2 服务端模式

```php
echo XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',
    'columns'     => [...],
]);
```

后端见 [05-服务端数据协议](../02-core-architecture/05-data-protocol.md)。

## 2. 参数总表

### 2.1 外观（继承自 `Table`）

| 参数 | 类型 | 默认（DataTable） | 说明 |
|---|---|---|---|
| `striped` | bool | `true` | 斑马纹 |
| `align_middle` | bool | `true` | 垂直居中 |
| `responsive` | bool | `false` | 与 `row_detail` 互斥 |
| `hover` | bool | `false` | 悬停高亮 |
| `bordered` | bool | `false` | 边框 |
| `borderless` | bool | `false` | 无边框 |
| `sm` | bool | `false` | 紧凑 |
| `striped_cols` | bool | `false` | 列斑马纹 |
| `centered` | bool | `false` | 单元格居中 |
| `nowrap` | bool | `false` | 不换行 |
| `variant` | string\|null | `null` | `table-*`（白名单，非法回退 light） |
| `head_variant` | string\|null | `null` | thead 的 `table-*` |
| `head_class` | string | `'thead-sm text-uppercase fs-xxs'` | 追加到 thead |
| `columns` | array | `[]` | 列定义（见 §3） |
| `data` | array | `[]` | 本地行数据 |

### 2.2 行为

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `ajax` | string\|array\|null | `null` | 服务端地址或原生 ajax 配置 |
| `server_side` | bool | `false` | 服务端模式（启用 xfc/xfo/xfs 压缩协议） |
| `method` | string | `'GET'` | 仅 `server_side=true` 且 ajax 为字符串时生效；`POST` 自动带 CSRF |
| `searching` | bool | `false` | 内置搜索框（推荐用 `filter_bar` 代替） |
| `ordering` | bool | `true` | 排序 |
| `paging` | bool | `true` | 分页 |
| `info` | bool | `true` | 分页信息 |
| `processing` | bool | `true` | 加载遮罩 |
| `page_length` | int | `10` | 每页条数 |
| `length_menu` | array | `[10,15,20,25,50]` | 每页条数选项 |
| `order` | array | `[]` | 默认排序 `[[0,'asc']]`；为空则不输出 |
| `language` | array\|null | `null` | 与内置中文包合并 |
| `row_id` | string\|null | `null` | 行 DOM id 字段 |
| `auto_width` | bool\|null | `null` | 见 §6 滚动规则 |
| `scroll_x` | bool | `true` | 见 §6 |
| `scroll_y` | bool\|string | `null` | `false` 不输出；否则默认 `'100%'` |
| `fixed_header` | bool | `false` | 表头固定（FixedHeader 扩展） |
| `fixed_columns` | bool\|array\|null | `null` | `true`→左 1 列；`['left'=>2,'right'=>1]` 前 2 后 1；`['indexes'=>[0,2,5]]` / `['indexes'=>[0=>'left',5=>'right']]` 固定任意列（含中间列）；`['fields'=>['id','status']]` 按字段名固定；`false` 关闭全部固定列。详见 §2.4 |
| `auto_fix_actions` | bool | `true` | 自动把操作列精确固定到最右侧（始终可见）；显式 `fixed_columns` 声明优先。详见 §2.4.1 |
| `defer_render` | bool\|null | `null` | `null` 且本地行数 ≥100 自动开启 |
| `select` | bool\|string\|array | `false` | `true`→`{style:'multi'}` |
| `state_save` | bool\|null | `null` | 保存分页/排序/搜索状态 |
| `density` | string\|null | `null` | `'compact'` → `xf-dt-compact` |
| `options` | array | `[]` | **最高优先级**，透传任意 DataTables 原生配置 |

### 2.3 增强功能

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `buttons` | array | `[]` | 按钮组（见 §7） |
| `export` | bool\|array | `null` | `true` → copy/excel/csv/pdf/print；数组指定子集 |
| `filter_bar` | array | `[]` | 过滤工具条（见 §8） |
| `filter_auto` | bool | `false` | 条件变更即查（默认点「搜索」） |
| `column_filters` | bool | `false` | thead 追加筛选输入行（300ms 防抖） |
| `create` | string\|array\|null | `null` | 「新增」按钮（见 §9） |
| `bulk` | bool\|array\|null | `null` | 批量操作（见 §10） |
| `row_detail` | bool\|array\|null | `null` | 行明细展开（见 §11） |
| `row_group` | string\|array\|null | `null` | 行分组（见 §12） |
| `dataset` | string | `''` | 数据集标识（批量/领域 op 提交给后端） |
| `show_custom_search` | bool\|array | `true` | 可折叠自定义搜索面板（见 §8.5）：`true` 按可搜索列自动生成；配合 `search_fields` 自定义字段 |
| `search_fields` | array | `[]` | 自定义搜索字段 `[['data'=>'name','label'=>'名称','type'=>'text\|select\|daterange','options'=>[]]]`（见 §8.5） |
| `created_row` | string\|null | `null` | 全局 JS 函数名 |
| `draw_callback` | string\|null | `null` | 全局 JS 函数名 |

⚠️ **`show_header_btn` 是死选项**（声明但 `html()` 未使用）。
✅ `row_url` **已生效**：整行点击跳转，URL 支持 `{字段}` 占位（如 `/admin/user/{id}`），行内链接/按钮点击不拦截（见 §2.2）。

### 2.4 固定 / 冻结列 `fixed_columns`（支持首列、尾列及任意中间列）

基于 CSS `sticky` 实现（无需 FixedColumns 扩展），开启后自动启用横向滚动。三种写法可组合，最终合并去重：

```php
// ① 连续规则（兼容历史）：前 2 列左固定、后 1 列右固定
'fixed_columns' => ['left' => 2, 'right' => 1],

// ② 精确列索引：可固定任意中间列；数组值为 left，键值对可指定方向
'fixed_columns' => ['indexes' => [0, 2, 5]],                    // 索引 0/2/5 全部左固定
'fixed_columns' => ['indexes' => [0 => 'left', 2 => 'left', 5 => 'right']], // 逐列指定方向

// ③ 按数据字段名（按 columns 顺序自动解析为索引，最易维护）
'fixed_columns' => ['fields' => ['id', 'name', 'status']],
```

- 索引从 0 开始，包含辅助列（行明细列 / 批量选择列会占用最左索引）；
- 前端按「左固定列从左向右、右固定列从右向左」累计各自宽度作为 sticky 偏移，表头固定列层级更高；
- 与 `fixed_header`（表头固定）可同时开启，实现「表头 + 关键列」双向冻结；

#### 2.4.1 操作列自动右固定 `auto_fix_actions`（默认 true）

**铁律**：所有含操作列的表格，操作列统一固定到最右侧、始终处于可视区域，业务侧无需逐表手写 `fixed_columns`。

- 探测（`detectActionColumnIndexes()`）命中任一形态即识别为操作列：
  1. `key` 为 `actions` / `action` / `operate` / `operation`；
  2. `label` 为 `操作` / `actions` / `operation`（中英文标题）；
  3. 存在 `actions` 数组配置（行操作按钮声明）；
  4. `render` / `xfRender` 类型为 `actions` / `dropdown` / `buttons`。
- **精确定位索引**：基于辅助列插入后的最终列定义定位，操作列不在末位也能正确冻结，不再假定「最后一列是操作列」。
- **连续冻结组**：若操作列不在末位，从最左操作列起至末列整体纳入右侧冻结组——以免其右侧仍滚动的普通列被高 z-index 的冻结列遮挡。显式声明的方向仍优先。
- **优先级**：显式 `fixed_columns` 声明的方向（`left`/`right`/`indexes`/`fields`）高于自动探测，绝不覆盖用户已声明的列；未声明的操作列仍自动右固定。
- **关闭方式**：`fixed_columns=false` 关闭全部固定列；`auto_fix_actions=false` 仅关闭自动右固定；`scroll_x=false` 时不自动固定。
- 前端 `fixedColumns.indexes` 经 JSON 序列化可能是对象或数组，`Object.keys()` 两种形态都支持。

## 3. 列定义 `columns`

### 3.1 列子键全表

| 子键 | 映射到 / 行为 | 说明 |
|---|---|---|
| `key` | `data` | 数据字段名（空串 → `null`） |
| `data` | `data` | DataTables 原生风格别名 |
| `label` | `<th>` 文本 | 表头文案 |
| `title` | → `label` | 别名 |
| `sortable` | `orderable` | 排序开关 |
| `orderable` | → `sortable` | 别名 |
| `searchable` | `searchable` | 列搜索 |
| `visible` | `visible` | 显隐 |
| `width` | `width` + `<th style>` | 列宽 |
| `minWidth` | `minWidth` + `<th style>` | 注意**驼峰** |
| `class` | `className` + `<th class>` | 前端靠 `thead th.xf-dt-select-col` 定位全选框 |
| ~~`style`~~ | — | **已移除**（v2.2.15）：任意 CSS 声明串是「分号注入」向量（可追加 `position:fixed` 等声明），列级样式请用 `width` / `minWidth` / `class` |
| `template` | `xfTemplate` | `{field}` 占位模板，自动 `orderable=false` |
| `badges` | `xfBadges` | `值 => 颜色名`，渲染 `<span class="badge bg-x-subtle text-x">` |
| `render` | `xfRender` | 渲染器（见 §4） |
| `actions` | `xfRender = {type:'actions', items:[…]}` | 行操作（见 §5） |
| `show_detail` | `showDetail` | dt-control 子行（见 §11） |

> `cell_class`、`format`、`raw` **仅父类 `Table` 使用**，DataTable 不渲染 `<td>`。

⚠️ `data` 为空的列（操作列 / 明细列 / 选择列）会自动补 `defaultContent: ''`，
否则 DataTables 每行抛 "Requested unknown parameter"。

## 4. 单元格渲染器

### 4.1 渲染器快捷键

列上直接写键名即启用（`RENDER_SHORTCUTS`）：

`'switch' | 'copy' | 'tags' | 'color' | 'progress' | 'link' | 'image' | 'enum' | 'user' | 'toggle' | 'tooltip' | 'popover' | 'status' | 'trend' | 'sparkline' | 'timeline' | 'dropdown' | 'page' | 'badge' | 'statusPill' | 'priority' | 'rate' | 'duration' | 'currency' | 'json' | 'copyBtn' | 'linkBtn' | 'miniBar' | 'progressBar' | 'sparkbar' | 'heatmap' | 'ranking' | 'progressSteps' | 'gradient' | 'tagInput' | 'avatarStack' | 'rich'`

转换规则：

| 写法 | 结果 |
|---|---|
| `'switch' => '/toggle/{id}'` | `['type' => 'switch', 'url' => '/toggle/{id}']` |
| `'badge' => ['variant' => 'success', 'pill' => true]` | `['type' => 'badge', 'variant' => 'success', 'pill' => true]` |
| `'copy' => true` | `['type' => 'copy']` |
| `'switch' => false` | 忽略 |

### 4.2 渲染器全表

| type | 说明 | 常用 cfg |
|---|---|---|
| `text` | 纯文本（转义）—— 所有渲染器的兜底 | — |
| `badge` | 徽章 | `variant` `dot` `pill` `icon` `soft` |
| `statusPill` | 带状态点的胶囊 | `variant` `map` |
| `priority` | 高/中/低 | `low/medium/high` 映射 |
| `rate` | 星级（支持半星） | `max` |
| `duration` | 秒 → `1天2时3分4秒` | — |
| `currency` | 货币 | `symbol` `decimals` `color` |
| `json` | 「查看」按钮 + 弹窗格式化 JSON | — |
| `copyBtn` / `copy` | 复制按钮 / 只读输入+复制 | — |
| `linkBtn` | 按钮式链接 | `url` `text` `variant` `target` `confirm` |
| `miniBar` | 表内迷你条 | `max` `variant` `showVal` |
| `progressBar` | 带阈值色映射的进度条 | `thresholds` `showVal` `suffix` |
| `sparkbar` | 内联 SVG 柱状火花线 | `variant` `width` `height` |
| `heatmap` | 热力单格 | `max` `palette` |
| `ranking` | 排名徽章（前三特殊色） | — |
| `progressSteps` | 横向步骤点 | `steps` |
| `gradient` | 渐变文字色数值 | `from` `to` |
| `tagInput` | 只读标签组 | `variant` |
| `input` | 单元格输入框 `.xf-cell-input` | `size` `url` `placeholder` `field` |
| `ip` | 等宽 + 点击复制 | — |
| `switch` | 开关 | `url` `field` `on` `off` |
| `tags` | 标签组 | `variant` / `variants`（轮换色） |
| `color` | 色块 + `<code>` | — |
| `image` | 图片 | `height` `rounded` `circle` |
| `avatar` | 头像 + 名称 | `name_field` |
| `progress` | 进度条（自动配色） | `variant` `striped` `max` |
| `bool` | √ / × | — |
| `link` | 链接 | `href` `text` `target` |
| `code` | `<code>` + 点击复制 | — |
| `datetime` | 日期时间 | `ago`（相对时间） |
| `money` | 金额（千分位 + 负数红） | `prefix` `decimals` |
| `truncate` | 截断 + title | `length` |
| `rating` | 星级 | `max` |
| `icon` | 图标 | `map` |
| `percent` | 百分比 | `decimals` `bar` `variant` |
| `enum` | 枚举映射 | `map` |
| `email` / `phone` / `url` | `mailto:` / `tel:` / 外链 | `length` |
| `user` | 头像 + 姓名 + 副标题 | `avatar` `sub` `url` |
| `images` | 多图缩略 + `+N` | `max` `size` |
| `number` | 千分位数字 | `decimals` `prefix` `suffix` |
| `filesize` | 字节 → B/KB/MB/GB/TB | — |
| `qr` | 二维码（自动加载 qrcode 资源） | `size` `ec` `color` `bg` `download` |
| `file` | 文件图标 + 名称 + 大小 + 下载 | — |
| `avatarGroup` | 多人头像堆叠 + `+N` | `max` |
| `select` | 可编辑下拉 | `options` `url` `field` |
| `tooltip` | Bootstrap Tooltip | `text` `field` `placement` `length` |
| `popover` | Bootstrap Popover | `title` `content` `placement` `html` |
| `toggle` | 按钮切换（派发 `xf:toggle`） | `on/off` `on_label/off_label` |
| `status` | 状态点 + 文案 | `map` |
| `trend` | 涨跌（绿升红降） | `suffix` `decimals` `invert` |
| `sparkline` | 内联 SVG 迷你趋势图 | `type` `width` `height` `color` |
| `timeline` | 单元格时间线 | `max` `title` |
| `dropdown` | 单按钮下拉 | `label` `icon` `items` |
| `actions` / `buttons` | 行操作栏（见 §5） | `items` |
| `rich` | 复合信息单元格 | `title` `sub` `meta` `icon` `avatar` `status` |
| `page` | 弹窗页链接（复用 `data-xf-page-dialog`） | `url` `title` `size` `frame` |

✅ 自 v1.0.3 起，PHP 快捷渲染器白名单（`DataTable::RENDER_SHORTCUTS`）已与前端
`XFAdmin.cellRenderers` **双向对齐**——上表所有渲染器均可直接用快捷写法
（如 `'money' => true` 等价 `'render' => 'money'`），无需记忆两套清单：
- `enum` 前端有实现（基于 `cfg.map`），`avatarStack` 已补齐（与 `avatarGroup` 等价）；
- `ip` / `qr` / `money` / `filesize` 等已加入快捷白名单；
- 仅 `actions`/`buttons`（由 `actions` 键处理）与 `text`（兜底渲染器）不作为快捷写法。

交互型渲染器（`actions`/`buttons`/`dropdown`/`input`/`select`/`switch`/`toggle`/
`sparkline`/`timeline`）在列未显式指定时自动关闭排序与搜索。

### 4.3 使用示例

```php
'columns' => [
    ['key' => 'name', 'label' => '姓名', 'render' => ['type' => 'user', 'sub' => 'email']],
    ['key' => 'amount', 'label' => '金额', 'render' => ['type' => 'money', 'prefix' => '￥']],
    ['key' => 'progress', 'label' => '进度', 'render' => 'progress'],
    ['key' => 'created_at', 'label' => '创建时间', 'render' => ['type' => 'datetime', 'ago' => true]],
    ['key' => 'tags', 'label' => '标签', 'render' => ['type' => 'tags', 'variants' => ['primary','info','success']]],
    ['key' => 'avatar', 'label' => '头像', 'render' => ['type' => 'image', 'height' => 32, 'circle' => true]],
    ['key' => 'code', 'label' => '二维码', 'render' => ['type' => 'qr', 'size' => 64]],
    ['key' => 'name', 'label' => '客户', 'template' => '{name} <small class="text-muted">{company}</small>'],
    ['key' => 'status', 'label' => '状态', 'badges' => ['active' => 'success', 'banned' => 'danger']],
];
```

### 4.4 自定义渲染器

```php
// 方式一：js: 全局函数（支持点号路径）
['key' => 'amount', 'render' => 'js:App.render.money']

// 方式二：前端注册
// XFAdmin.registerCellRenderer('myRenderer', function (d, row, cfg) { return '...'; });
['key' => 'x', 'render' => ['type' => 'myRenderer', 'foo' => 'bar']]
```

### 4.5 单元格事件

```php
['key' => 'name', 'render' => ['type' => 'text', 'event' => 'onNameClick']]
// 或多事件
['key' => 'x', 'render' => ['type' => 'text', 'event' => ['click' => 'onX', 'dblclick' => 'onXDbl']]]
```

```js
XFAdmin.onCell('onNameClick', function (ctx) {
    // ctx: {event, el, row, value, field, originalEvent}
});
```

## 5. 行操作 `actions`

```php
[
    'key' => '', 'label' => '操作',
    'actions' => [
        ['label' => '编辑', 'icon' => 'ti ti-pencil', 'action' => 'edit',
         'ajax' => '/admin/users/{id}', 'method' => 'PUT', 'fields' => [...]],
        ['label' => '详情', 'action' => 'view', 'view' => [...], 'viewTitle' => '用户详情'],
        ['label' => '日志', 'action' => 'modal', 'url' => '/admin/users/{id}/logs', 'size' => 'lg'],
        ['label' => '删除', 'icon' => 'ti ti-trash', 'action' => 'ajax', 'class' => 'btn-soft-danger',
         'ajax' => '/admin/users/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？', 'reload' => true],
        ['label' => '审核', 'op' => 'audit', 'dataset' => 'users',
         'prompt' => '请输入审核意见', 'arg' => 'comment'],
        ['label' => '更多', 'dropdown' => [
            ['label' => '重置密码', 'ajax' => '/admin/users/{id}/reset', 'method' => 'POST'],
            ['type' => 'divider'],
            ['label' => '禁用', 'ajax' => '/admin/users/{id}/disable'],
        ]],
    ],
]
```

### 5.1 item 键

| 键 | 说明 |
|---|---|
| `label` / `icon` / `class` / `title` | 文案与样式（默认 class `btn-soft-primary`） |
| `action` | `edit` / `view` / `modal` / `ajax`（默认）/ `delete` / `copy-row` / `download` / `print` / `share` / 自定义 |
| `url` | 存在则渲染为 `<a href>`（支持 `{field}` 占位 + `target`），**优先级高于 ajax** |
| `ajax` / `method` | `data-xf-url` / `data-xf-method`（URL 经 `XFAdmin.tpl` 渲染） |
| `confirm` / `confirm_popover` | 确认文案 / 气泡确认 |
| `reload` | 成功后刷新表格 |
| `event` | 指定时**不执行内置动作**，改为派发自定义事件 |
| `edit` 专属 | `fields`（弹窗字段）、`editTitle`、`page`（整页编辑）、`frame`、`size`、`maximizable`、method 默认 `PUT` |
| `view` 专属 | `view` 对象 / `viewTitle` → `data-xf-view`（详情引擎） |
| `modal` 专属 | `url` + `title` + `size` + `maximizable` → `data-xf-page-dialog` |
| `op` 专属 | `op`（领域动作）、`dataset`、`prompt`（输入提示）、`arg`（字段名，默认 `comment`） |
| `dropdown` | 子项数组，子项支持 `type => 'divider'` |

`dropdown` 列类型 ≈ `actions` + 单按钮（label 默认「操作」，icon `ti ti-settings`）。

### 5.2 详情弹窗 `view`（`viewRow` 引擎）

```php
['label' => '详情', 'action' => 'view', 'viewTitle' => '客户详情 - {name}', 'view' => [
    'size'    => 'lg',
    'layout'  => 'sections',
    'labels'  => ['created_at' => '创建时间'],
    'exclude' => ['_internal'],
    'header'  => ['avatar' => 'avatar', 'title' => '{name}', 'sub' => '{email}',
                  'badge' => ['field' => 'status', 'map' => ['1' => ['color' => 'success', 'label' => '正常']]]],
    'sections' => [
        ['title' => '基础信息', 'icon' => 'ti ti-user', 'type' => 'kv', 'fields' => ['name','email'], 'cols' => 2],
        ['title' => '时间线', 'type' => 'timeline'],
        ['title' => '统计', 'type' => 'stats'],
    ],
    'renderers' => ['amount' => ['type' => 'money', 'prefix' => '￥']],
    'ajax'      => '/admin/api/users/{id}/extra',     // 打开前拉取并合并
]]
```

分区 `type`：`kv | table | timeline | stats | tags | progress | images | html | template`。
`layout` 可选 `kv | profile | tabs | sections | template`（省略时自动推断）。

## 6. 滚动 / 固定 / 布局规则

```php
$disableScrollX = ($scroll_x === false) || ($auto_width === false);

if (! $disableScrollX) {
    scrollX = true; autoWidth = true; scrollCollapse = true;
    scrollY = $scroll_y ?: '100%';
} elseif ($scroll_x === true || 列数 > 10) {
    scrollX = true; scrollCollapse = true; scrollY = 同上;
}
if ($auto_width !== null) autoWidth = (bool) $auto_width;      // 最后覆盖，优先级最高
if ($fixed_columns !== false) {
    if ($fixed_columns === true) $fixed_columns = ['left' => 1];
    $fixedColumns = normalizeFixedColumns($fixed_columns ?: ['left'=>0,'right'=>0,'indexes'=>[]], $columns);
    if ($auto_fix_actions !== false && $scroll_x !== false) {
        $actionIndexes = detectActionColumnIndexes($columns);
        if ($actionIndexes !== []) {
            $groupStart = min($actionIndexes);                  // 最左操作列
            for ($i = $groupStart; $i < count($columns); $i++) { // 连续冻结到末列，避免遮挡
                $fixedColumns['indexes'][$i] ??= 'right';       // 显式声明优先，绝不覆盖
            }
        }
    }
    $hasFixed = left || right || indexes 非空;
    if ($hasFixed) scrollX = true;   // 下发 fixedColumns / 强制 scrollX / 不套 table-responsive 三处共用此判定
}
```

| 规则 | 说明 |
|---|---|
| `scrollX + scrollY` 联合 | DataTables 2 生成协调的 `.dt-scroll-head/.dt-scroll-body` 双表，列宽一致；仅 scrollX 会严重错位 |
| `.table-responsive` 包裹 | **仅在**（`scroll_x === false` 或 `auto_width === false`）且无 `fixed_columns` 时输出，避免双滚动条 |
| 自动右固定操作列 | 探测到操作列（key/label/actions 数组/render 四形态）时按**精确索引**固定到右侧；显式 `fixed_columns` 声明优先；`fixed_columns=false` 或 `auto_fix_actions=false` 或 `scroll_x=false` 时不自动固定 |
| 列宽同步 | 前端双 RAF `columns.adjust()` + `draw.dt` +50ms + ResizeObserver(200ms 防抖) + `shown.bs.tab` + window resize(300ms) |

⚠️ `scroll_x => false` **不一定**关掉 scrollX（列数 > 10 时会强制开启）。
⚠️ `auto_width => false` 会连带影响 scrollX 判定。

## 7. 按钮 `buttons` 与导出 `export`

| 字符串 | 映射 |
|---|---|
| `copy` | `copyHtml5` |
| `csv` | `csvHtml5` |
| `excel` | `excelHtml5`（jszip 已在基础资源） |
| `pdf` | `pdfHtml5`（**触发 `datatables-pdf` 资源加载**） |
| `print` | `{extend:'print'}`（⚠️ 不存在 `printHtml5`） |
| `colvis` | 列显隐（前端自注册兜底实现） |
| `refresh` | `xfButton:'refresh'` |
| `fullscreen` | `xfButton:'fullscreen'` |
| `density` | `xfButton:'density'`（切换紧凑模式） |
| 其它 | `{extend:'collection', text:值}` |

```php
'export'  => ['copy', 'excel', 'csv'],        // 不含 pdf 时不加载 pdfmake
'buttons' => ['refresh', 'colvis', 'density'],
```

⚠️ 只要 `buttons !== []`，PHP 同时输出
`layout: {topStart: ['buttons'], topEnd: ['search']}`（DataTables 2 的 `layout`，取代旧 `dom`）。

## 8. 过滤工具条 `filter_bar`

```php
'filter_bar' => [
    ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],
    ['name' => 'date', 'label' => '注册时间', 'type' => 'daterange'],
    ['name' => 'score', 'label' => '分数', 'type' => 'range', 'min' => 0, 'max' => 100],
    ['name' => 'dept', 'label' => '部门', 'type' => 'tree', 'options' => [...]],
    ['name' => 'tag', 'label' => '标签', 'type' => 'select2', 'multiple' => true, 'options' => [...]],
    ['name' => 'x', 'label' => '自定义', 'html' => '<input class="form-control" name="x">'],
],
'filter_auto' => false,      // true = 即改即查
```

### 8.1 控件类型与产出参数

| `type` | 产出参数名 | 说明 |
|---|---|---|
| `select`（默认） | `name` | 首项「全部」 |
| `select2` | `name` | 可搜索下拉 |
| `select` + `multiple:true` | `name`（逗号连接） | 后端配 `op => in` |
| `text` / `search` | `name` | 文本框 |
| `number` | `name` | 带 min/max/step |
| `range` | `name_min` / `name_max` | 数值区间 |
| `date` / `datetime` / `time` / `month` / `week` | `name` | 原生控件 |
| `daterange` / `datetimerange` / `timerange` | `name_from` / `name_to` | 双输入组 |
| `year` | `name` | 年份下拉（默认近 10 年，`from` 可调） |
| `color` | `name` | 保持默认值视为未过滤 |
| `checkbox` / `switch` | `name` | `value` 默认 `'1'` |
| `radio` | `name` | 按钮组单选（含「全部」） |
| `checkboxes` | `name`（逗号连接） | 复选组 |
| `slider` / `range-slider` | `name`（`"lo,hi"`） | 后端配 `op => between` |
| `tree` | `name`（逗号连接） | 树形复选，`options` 为 `[['value','label','children']]` |
| `autocomplete` | `name` | datalist 候选或 `url` 远程 |
| `custom` | `name` | JS 读 `data-xf-custom-value` 或派发 `xf:filter-custom` |

通用键：`name`、`label`、`type`、`width`（默认 `col-6 col-md-3 col-xl-2`）、
`placeholder`、`options`、`min`/`max`/`step`、`html`、`control`。

### 8.2 行为

- **服务端模式**：`table.ajax.url(baseUrl + '?' + qs).load()`；
- **本地模式**：`columns().search('')` → 按 filter 名映射到列索引 → `column(ci).search(v)` → `draw()`；
- `filter_auto=true`：change 即查；text/number 400ms 防抖、slider 300ms、autocomplete 350ms；
- 回车 = 搜索；重置按钮清空后自动 reload。

⚠️ 前端控件名必须与后端 `filters` 键配对（对照表见
[05-服务端数据协议 §11](../02-core-architecture/05-data-protocol.md#11-完整示例)）。

### 8.5 自定义搜索面板 `show_custom_search` / `search_fields`

与常显的 `filter_bar` 不同，`show_custom_search` 渲染一个**默认折叠**的搜索面板（点击「搜索」按钮展开），适合字段多、平时隐藏搜索区的列表页：

```php
XfAdmin::dataTable([
    'columns' => [
        ['key' => 'name', 'label' => '名称'],
        ['key' => 'status', 'label' => '状态', 'options' => ['1' => '启用', '0' => '禁用']],
    ],
    'show_custom_search' => true,   // true=按 searchable 业务列自动生成；false=关闭
    'search_fields' => [            // 可选：自定义字段，留空则自动生成
        ['data' => 'name', 'label' => '名称', 'type' => 'text', 'placeholder' => '模糊匹配'],
        ['data' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
        ['data' => 'created_at', 'label' => '创建时间', 'type' => 'daterange'],
    ],
]);
```

| `search_fields[*]` 键 | 说明 |
|---|---|
| `data`（或 `name`） | 查询参数名 / 数据字段名（必填） |
| `label` | 字段标签 |
| `type` | `text`(默认) / `select` / `date` / `datetime-local` / `time` / `month` / `week` / `daterange` / `datetimerange` |
| `options` | `select` 选项 `['值'=>'文案']` |
| `placeholder` | 文本框占位 |
| `width` | 栅格宽度（默认 `col-12 col-md-6 col-xl-4`） |
| `html` | 完全自定义控件片段（原样注入） |

- 服务端模式：采集所有 `[data-xf-search]` 值 → 追加到 ajax URL 同名参数重载；区间控件产出 `_from` / `_to`；
- 本地模式：按字段名定位列索引做列搜索；
- 与 `filter_bar` 相互独立、可同时使用（参数合并到请求）；自动生成的字段中，`select` 仅当列定义自带 `options` 时出现。

## 9. 「新增」按钮 `create`

```php
'create' => [
    'page'     => '/admin/users/create',      // 弹窗加载（data-xf-page-dialog）
    'url'      => '/admin/users/create',      // 整页跳转（<a href>，可与 page 并存）
    'label'    => '新增',  'urlLabel' => '新增用户',
    'title'    => '新增用户',
    'size'     => 'lg',
    'frame'    => false,                      // iframe 模式
    'icon'     => 'ti ti-plus', 'urlIcon' => 'ti ti-file-plus',
    'class'    => 'btn-primary', 'urlClass' => 'btn-outline-primary',
    'target'   => '_blank',                   // 仅 url 链接有效
],
```

## 10. 批量操作 `bulk`

```php
'bulk' => [
    'checkbox' => true,
    'actions'  => [
        ['label' => '启用', 'icon' => 'ti ti-check', 'class' => 'btn-soft-success',
         'url' => '/admin/users/batch-enable', 'method' => 'POST',
         'confirm' => '确认启用？', 'action' => 'enable', 'reload' => true],
        ['label' => '删除', 'url' => '/admin/users/batch-delete?ids={ids}', 'method' => 'DELETE'],
    ],
],
'dataset' => 'users',
```

| action 键 | 默认 | 说明 |
|---|---|---|
| `label` | `'操作'` | |
| `icon` / `class` | `''` / `btn-outline-secondary` | |
| `url` | `''` | 支持 `{ids}` 占位 → 逗号分隔选中 id |
| `method` | `POST` | |
| `confirm` | `''` | 非空则 `window.confirm` |
| `action` | `''` | 批量动作名，作为 `payload.action` |
| `reload` | `true` | `false` → 成功后仅本地 `draw(false)` |

前端行为：注入 `.xf-dt-select-col` 行复选框 + `thead .xf-dt-select-all` 全选（含 indeterminate）；
提交 payload `{action?, dataset?, ids:[]}`；成功后 toast + 重载 + 清空勾选。

## 11. 行明细 `row_detail` 与 `show_detail`

### 11.1 `row_detail`（首列插入明细列）

```php
'row_detail' => true,                       // 展开全部字段
'row_detail' => ['columns' => ['name', 'email', 'phone']],   // 限定字段
```

⚠️ **与 `responsive` 互斥**：启用时强制关闭 `responsive`（二者都占用首列），
且 `tableClass()` 不输出 `dt-responsive`。

### 11.2 `show_detail`（dt-control 子行）

```php
['key' => 'name', 'label' => '姓名', 'show_detail' => true],              // 展示全部字段
['key' => 'name', 'show_detail' => 'description'],                        // 只展示该字段
['key' => 'name', 'show_detail' => fn ($row, $i, $rowApi, $table) => '…'], // 自定义（JS 侧为函数）
```

`html()` 会在该业务列**前面**插入独立的 `class="dt-control"` 辅助列。

## 12. 行分组 `row_group`

```php
'row_group' => 'dept',                                          // 字段名
'row_group' => ['data' => 'dept', 'empty' => '（未分组）'],
```

⚠️ 仅**本地数据**生效（`dt.serverSide !== true`）—— 服务端返回已分页，
分组只能在当前页插入。实现为 `drawCallback` 插入 `<tr class="xf-dt-group-row">`。

## 13. 其它增强

| 功能 | 配置 | 说明 |
|---|---|---|
| 列筛选 | `column_filters => true` | thead 追加输入行，300ms 防抖 `column(idx).search(v).draw()` |
| 状态保存 | `state_save => true` | `stateSave`（⚠️ 赋值顺序敏感，见 §15） |
| 行选择 | `select => true` / `['style' => 'os']` | 需 select 扩展（已含在 datatables 资源） |
| 紧凑模式 | `density => 'compact'` 或 `buttons` 含 `density` | 切换 `xf-dt-compact` |
| 自定义搜索 | `show_custom_search => true` | 绑定 `.custom-datatable-search` / `.xf-dt-search-form` |
| 行回调 | `created_row => 'globalFn'` | `window[名称]` |
| 绘制回调 | `draw_callback => 'globalFn'` | 与 rowGroup 的 drawCallback 组合执行 |

## 14. 输出结构与前后端契约

```html
<!-- 1) 批量操作栏（bulk 非空时） -->
<div class="xf-dt-bulk d-none card card-body py-2 px-3 mb-2 d-flex flex-wrap align-items-center gap-2"
     data-dt="user-table">
  <span class="xf-dt-bulk-count">已选 <b>0</b> 项</span>
  <div class="btn-group btn-group-sm">
    <button data-xf-bulk-action data-url="…" data-method="POST" data-confirm="…" data-action="…" data-reload="1">…</button>
  </div>
</div>

<!-- 2) 新增按钮区 -->
<div class="xf-dt-create mb-2 d-flex gap-2 flex-wrap">…</div>

<!-- 3) 过滤栏 -->
<form class="row g-2 mb-3 xf-filter-bar" data-xf-filter-for="user-table" onsubmit="return false;">…</form>

<!-- 4) 表格 -->
<table id="user-table" class="table …" data-xf="datatable" data-xf-dataset="users"
       data-xf-config='{"dt":{…},"bulk":{…},"filterBar":true,…}'>
  <thead class="thead-sm text-uppercase fs-xxs"><tr>…</tr></thead>
  <tbody></tbody>   <!-- 恒为空，避免初始化前布局抖动 -->
</table>
```

`data-xf-config` 结构（键名即前后端契约）：

```jsonc
{
  "dt": { "columns": [...], "serverSide": true, "ajax": "…", "pageLength": 10, … },
  "columnFilters": false, "processing": true, "showCustomSearch": true,
  "createdRow": null, "drawCallback": null,
  "filterBar": true, "filterAuto": false,
  "fixedColumns": {"left":0,"right":0,"indexes":{"5":"right"}},
  "bulk": {"checkbox":true,"actions":[...]},
  "rowDetail": {"columns":null},
  "rowGroup": {"data":"dept","empty":"…"}
}
```

## 15. 陷阱清单（重要）

1. **`state_save` 赋值顺序敏感**：必须在 `$config` 拷入 `$xfConfig` 之前设置，否则丢失。
2. **`row_detail` 与 `responsive` 互斥**（二者都占首列）。
3. **`auto_width => false` 连带影响 scrollX 判定**。
4. **`scroll_x => false` 在列数 > 10 时仍会强制 scrollX**。
5. **自动右固定操作列**：探测到操作列即按**精确索引**固定到右侧（非简单 `right:1` 取末列）；显式 `fixed_columns` 声明优先，`fixed_columns=false`/`auto_fix_actions=false`/`scroll_x=false` 可关闭。
6. **`order` 用列索引会被辅助列顶偏**：启用 bulk/row_detail 后索引 0/1 是辅助列。
7. **`data` 为空的列必须有 `defaultContent`**（组件已自动补 `''`）。
8. **`print` 不能写成 `printHtml5`**。
9. **`export` 含 pdf 需 `datatables-pdf`**：`assets()` 自动追加，但手写在 `options.buttons` 里检测不到。
10. **`show_header_btn` 是死选项**；`caption`/`empty`/`row_attrs`/`cell_class`/`format`/`raw` 只在父类 `Table` 生效。
11. **`defer_render` 阈值 100 行**（本地数据）。
12. **初始化失败不重试**：`register('datatable')` 内部 `return null` 不抛错；`window.DataTable` 缺失时会永久静默空白。
13. **`show_detail` / `bindRowDetail` / `bindBulk` / 部分事件依赖 jQuery**，无 jQuery 时静默降级。
14. **批量按钮在表格外部**，jQuery 需用 `$(this)`；`data-reload` 用 `attr()` 读取（避免 `.data()` 把 `"0"` 转数字）。
15. **`row_url` 当前实现无效**。
16. **`row_group` 对服务端模式无效**。
17. **`xf:filter-custom` 事件 `bubbles:false` 且派发到 `document`**，必须在 document 上直接监听。
18. **表格内按钮的双重处理**：`initRecordDetailOps` 遇 `btn.closest('table')` 会主动 return，
    避免行委托与全局委托重复触发（prompt 弹两次 / op 重复提交）。

## 16. 相关组件

| 组件 | 用途 |
|---|---|
| `XfAdmin::table()` | 静态表格（服务端渲染真实 `<tbody>`） |
| `XfAdmin::tablesCustom()` | 简单自定义表格 + tfoot |
| `XfAdmin::dataTableToolbar()` | 独立工具条（搜索/筛选/每页条数/视图切换），可搭配任意表格 |
| `XfAdmin::kanban()` | 看板（DataTable 无内置看板模式） |
| `XfAdmin::importExport()` | 导入导出（DataTable 只有导出按钮） |

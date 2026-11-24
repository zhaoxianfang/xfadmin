# DataTable 专项（固定列 / 自定义搜索 / 渲染器 / 服务端协议）

> 事实来源：`src/Components/Table/DataTable.php` + `resources/assets/js/xfadmin.js` 的 `datatable` widget。
> 这是本包最复杂、最常被业务方使用的组件，改动务必同步 PHP 白名单与前端渲染器。

---

## 1. 全部配置键（来自 `defaults()`，逐字）

### 1.1 行为与数据
| 键 | 默认 | 说明 |
| --- | --- | --- |
| `id` | 自动生成 | 表格 DOM id |
| `columns` | [] | 列定义（见 §2） |
| `data` | [] | 本地数据（非 server_side 时用） |
| `ajax` | null | 服务端数据端点 |
| `server_side` | false | 服务端模式 |
| `method` | GET | 数据请求方式；POST 可规避 URL 长度限制/WAF |
| `row_id` | null | 行 id 字段 |
| `row_url` | null | 整行点击跳转 URL，支持 `{字段}` 占位（如 `/x/detail/{id}`） |
| `order` | [] | 初始排序，如 `[[0,'asc']]` |
| `language` | null | DataTables 语言包配置数组（缺省内置中文） |
| `options` | [] | 原生 DataTables 配置透传 |

### 1.2 外观与交互
| 键 | 默认 | 说明 |
| --- | --- | --- |
| `striped` | true | 斑马纹 |
| `align_middle` | true | 垂直居中 |
| `head_variant` | null | table-light/dark 语义 |
| `head_class` | `thead-sm text-uppercase fs-xxs` | 表头 class |
| `searching` | false | 内置搜索（默认关，改用自定义搜索表单） |
| `ordering` / `paging` / `info` / `processing` | true | DataTables 开关 |
| `page_length` | 10 | 每页条数 |
| `length_menu` | [10,15,20,25,50] | 每页菜单 |
| `density` | null | `compact`\|`comfortable` |
| `auto_width` | null | DataTables autoWidth |
| `scroll_x` | true | 横向滚动（溢出列滚动展示） |
| `responsive` | false | 响应式折叠（默认关，避免列被收进 child 子行；与 row_detail 互斥） |
| `fixed_header` | false | 固定表头 |

### 1.3 固定列（`fixed_columns`）
| 值 | 效果 |
| --- | --- |
| `true` | 冻结首列（左 1） |
| `['left'=>2,'right'=>1]` | 冻结左 2 列 + 右 1 列 |
| `['indexes'=>[2=>'left',3=>'right']]` | 按**列索引**冻结中间任意列到左/右 |
| `['fields'=>['status'=>'left','actions'=>'right']]` | 按**字段名**冻结（PHP 侧归一化为 indexes） |

- PHP `normalizeFixedColumns()` 把 `fields`/`indexes` 归一化为
  `['left'=>int, 'right'=>int, 'indexes'=>[列索引=>'left'|'right']]`。
- 前端 sticky 偏移按左右两侧列宽分别累计，支持中间任意列冻结。
- 启用固定列会自动开启横向滚动。

#### 操作列自动右固定（`auto_fix_actions`，默认 true）
- **铁律**：任何使用 `dataTable` 且含操作列的表格，操作列都必须固定到最右侧、始终可见；禁止业务侧逐个表格手写 `fixed_columns` 来实现。
- 探测由 `detectActionColumnIndexes()` 完成，命中任一形态即识别为操作列：
  1. `key` 为 `actions` / `action` / `operate` / `operation`；
  2. `label` 为 `操作` / `actions` / `operation`（中英文标题）；
  3. 存在 `actions` 数组配置（行操作按钮声明）；
  4. `render` / `xfRender` 类型为 `actions` / `dropdown` / `buttons`。
- **精确定位索引**：基于「含 bulk/row_detail/dt-control 辅助列插入后的最终列定义」定位，操作列不在末位也能正确冻结；不再假定「最后一列是操作列」。
- **连续冻结组**：若操作列不在末位，从最左操作列起至末列**整体**纳入右侧冻结组——CSS sticky right 从右边缘向内累计偏移，若只冻结中间操作列，其右侧滚动列会从冻结列下方穿过（z-index 更高）被遮挡。显式声明的方向仍优先、不被覆盖。
- **优先级**：显式 `fixed_columns` 声明的 `left`/`right`/`indexes`/`fields` 方向高于自动探测，绝不覆盖用户已声明的列方向；未被显式声明的操作列仍会自动右固定。
- **关闭方式**：`fixed_columns=false` 关闭全部固定列（含自动）；`auto_fix_actions=false` 仅关闭自动右固定、保留显式声明；`scroll_x=false` 时不自动固定（无横向滚动时固定无意义）。
- `fixedColumns.indexes` 经 JSON 序列化可能是对象 `{"3":"right"}` 或数组 `["left","right"]`（PHP key 连续时），前端用 `Object.keys()` 解析，两种形态都支持。
- 对应测试：`tests/regression.php` 的「操作列自动右固定」7 组用例（纯 key / label 中英文 / bulk 辅助列 / 中间列 / render 型 / 显式优先 / 开关关闭）。

### 1.4 自定义搜索（`show_custom_search` / `search_fields`）
| 键 | 默认 | 说明 |
| --- | --- | --- |
| `show_custom_search` | true | true=按 searchable 业务列自动生成搜索面板；数组=自定义字段；false=不渲染 |
| `search_fields` | [] | 自定义字段：`[['data'=>'字段','label'=>'标题','type'=>'text\|select\|date\|daterange','options'=>[],'placeholder'=>'']]`；留空按 searchable 列生成 |

- PHP `renderCustomSearchForm()` 渲染真实可折叠 `.custom-datatable-search` 面板（非旧版空占位）。
- 前端 `dt-search` widget 负责采集、提交、重置、回车查询；服务端模式重载表格参数，本地模式做列搜索。
- 对应测试：回归测试「自定义搜索：渲染面板/切换按钮/按列生成字段/false 不渲染」。

### 1.5 过滤栏 / 按钮 / 行操作
| 键 | 默认 | 说明 |
| --- | --- | --- |
| `filter_bar` | [] | 过滤工具栏（服务端模式自动拼接查询参数并重载） |
| `filter_auto` | false | 即改即查（默认点「搜索」才请求） |
| `filter_all` | false | 依据 columns 自动生成「全部列」筛选控件（按类型推断） |
| `column_filters` | false | 列筛选 |
| `buttons` | [] | DataTables buttons |
| `select` | false | 行选择 |
| `show_header_btn` | null | 表头按钮区 `['create'=>url,'refresh'=>true,'search'=>true]` |
| `created_row` / `draw_callback` | null | JS 回调名（全局函数引用） |
| `create` | null | 新增弹窗按钮：URL 字符串或 `[page,label,title,size,frame,icon,class]` |
| `row_detail` | null | 行详情展开 callback/列名 |

---

## 2. 列定义 `columns[]`

| 键 | 说明 |
| --- | --- |
| `key` / `data` | 字段名 |
| `label` | 表头标题 |
| `width` | 列宽 |
| `sortable` / `searchable` | 排序/搜索开关 |
| `render` | 快捷渲染器键（字符串或数组，见 §3） |
| `format` | **服务端**格式化闭包（由 `dataResponse`/`DataTableService` 应用；组件侧不持有） |
| `actions`[] | 行操作按钮：`action`(modal/page/delete…) `label` `icon` `url`(支持 `{id}`) `title` `size` `confirm` `hideFlags` `class` |

> **关键**：DataTable 组件 `columnRender()` 只接受 `render` 为「前端渲染器字符串/数组」；
> 若把 PHP 闭包传给 `render` 会触发 `Closure could not be converted to string` 致命错误。
> 服务端格式化用 `format`，前端渲染用 `render`，两者不可混。

---

## 3. 渲染器白名单（`RENDER_SHORTCUTS`，当前 60+）

必须与前端 `XFAdmin.cellRenderers` 对齐。分组：

- **交互/状态**：`switch` `toggle` `copy` `copyBtn` `status` `statusPill` `priority` `badge` `dropdown` `bool`
- **文本/富文本**：`tags` `tagInput` `truncate` `code` `json` `rich` `tooltip` `popover`
- **数值/进度/图表**：`progress` `progressBar` `progressSteps` `rate` `percent` `money` `number` `currency` `miniBar` `sparkbar` `sparkline` `heatmap` `ranking` `gradient` `duration` `trend` `rating`
- **媒体/头像**：`color` `image` `images` `avatar` `avatarGroup` `avatarStack` `user` `qr`
- **链接/跳转**：`link` `linkBtn` `page`（单元格变可点击链接，弹窗加载后端页面，URL 支持 `{字段}`） `url` `email` `phone`
- **时间/枚举**：`datetime` `enum` `timeline` `ip`
- **可编辑/富媒体**：`input` `select` `icon` `file` `filesize`

**维护纪律**：
- 新增白名单项时**必须**同时在 `xfadmin.js` 的 `XFAdmin.cellRenderers` 提供实现（如 `avatarStack` = `avatarGroup` 别名），
  否则会静默回退 text。
- 白名单测试在 `tests/regression.php`（avatarStack/money/select 快捷写法）。
- 渲染器逐项用法见 `docs/04-advanced-guide/13-cell-renderers.md`。

---

## 4. 服务端协议

前端 `dataTable(['server_side'=>true,'ajax'=>$url,'method'=>'POST'])` 须配合服务端返回：

```json
{ "draw": 1, "recordsTotal": 100, "recordsFiltered": 25, "data": [ ... ] }
```

- 包内：`XfAdmin::dataResponse($rows, request()->all(), ['searchable'=>['name'], 'filters'=>['status']])`
  → `DataSet::response()`。
- 业务项目：可用自己的 `DataTableService`（如 yoc 的 `DataTableService::make()`）。
- `POST` 模式注意 CSRF：前端 `XFAdmin.request` 自动带 `_token`（由 `setCsrfResolver` 接线）。

---

## 5. 前端桥接

- 组件输出 `data-xf="datatable"` + `data-xf-config`（HEX 转义 JSON），由 `datatable` widget 初始化。
- 自定义搜索由 `dt-search` widget 处理；过滤栏由 `dt-filter`；每页 `dt-pagesize`；视图 `dt-views`。
- 表格重绘派发 `xf-datatable-drawn` 事件（如 `rowActions` widget 监听它重新绑定按钮）。
- 重载表格：`XFAdmin.reloadTable('#table-id')`。

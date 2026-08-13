# DataTable 高级用法（渲染器 / 单元格事件 / 自定义搜索）

> xfadmin 的 `XfAdmin::dataTable()` 基于 DataTables 2.x，全部交互由 `resources/assets/js/xfadmin.js` 自动初始化。
> 本文档覆盖「富单元格渲染器」「单元格事件系统」「自定义搜索 / 过滤组件类型」「行内编辑 / 弹窗」等高级能力。

## 一、富单元格渲染器（cellRenderers）

通过列的 `render` 配置指定渲染器 `type`，前端在 `XFAdmin.cellRenderers` 注册表中找到对应实现并渲染。
所有渲染器第二参数均为 `cfg`（配置对象），支持前端自定义样式。

```php
XfAdmin::dataTable([
    'data' => $rows,
    'columns' => [
        ['name' => 'status',  'title' => '状态',   'render' => ['type' => 'enum',   'options' => ['active'=>'启用','inactive'=>'停用'], 'colors'=>['active'=>'success','inactive'=>'secondary']]],
        ['name' => 'score',   'title' => '评分',   'render' => ['type' => 'progress', 'variant' => 'primary', 'showVal' => true]],
        ['name' => 'owner',   'title' => '负责人', 'render' => ['type' => 'user', 'avatar' => 'avatar', 'name' => 'owner_name', 'sub' => 'dept']],
        ['name' => 'amount',  'title' => '金额',   'render' => ['type' => 'currency', 'symbol' => '¥', 'decimals' => 2]],
        ['name' => 'ops',     'title' => '操作',   'render' => ['type' => 'actions', 'items' => [['label'=>'查看','icon'=>'ti ti-eye','url'=>'/x/{id}'],['label'=>'删除','icon'=>'ti ti-trash','url'=>'/x/{id}','method'=>'delete','confirm'=>'确认删除？']]],
    ],
]);
```

### 渲染器速查表

| type | 说明 | 常用 cfg |
|------|------|----------|
| `text` | 纯文本（默认，HTML 转义） | — |
| `enum` | 枚举徽章（带色彩映射） | `options`, `colors`, `icons`, `map`, `dot` |
| `status` | 状态点徽章（语义化） | `variant`, `map`, `icon` |
| `badge` | 自定义徽章 | `variant`, `dot`, `pill`, `icon`, `soft`, 或传对象 `{text,variant,icon}` |
| `statusPill` | 带左侧状态点的胶囊 | `variant`, `map`, `icon`, `text` |
| `priority` | 优先级（高/中/低 图标） | 自动按 `high/medium/low` 或数值 1-10 判定 |
| `rate` | 评分（支持半星） | `max` |
| `progress` | 进度条 | `variant`, `showVal`, `suffix` |
| `progressBar` | 带阈值色映射的进度条 | `thresholds:[{max,variant}]`, `variant`, `showVal` |
| `miniBar` | 迷你条（表内单值） | `max`, `variant`, `showVal` |
| `progressSteps` | 横向步骤条 | `steps:[...]`, `variant` |
| `currency` | 货币 | `symbol`, `decimals`, `color` |
| `duration` | 时长（秒→人类可读） | — |
| `switch` | 开关（可点击切换，PATCH 提交） | `url`, `field`, `on`, `off` |
| `toggle` | 开关别名 | 同 `switch` |
| `user` | 用户（头像+姓名+副标题） | `avatar`, `name`, `sub` |
| `avatarStack` | 头像组（多用户） | `users` 或逗号分隔 `avatar`/`name` |
| `rich` | 复合信息（图标+标题+副标题+状态徽章） | `icon`,`title`,`sub`,`meta`,`url`,`color`,`status`,`statusMap`,`showMeta`,`showStatus` |
| `tags` | 标签组 | `variant` |
| `tagInput` | 标签输入展示（只读） | `variant` |
| `color` | 色块 | — |
| `image` | 图片（object-fit cover） | `width`, `height` |
| `link` | 链接 | `url`, `text`, `target` |
| `linkBtn` | 按钮式链接 | `url`, `text`, `variant`, `icon`, `confirm`, `target` |
| `copy` | 点击复制文本 | — |
| `copyBtn` | 独立复制按钮 | — |
| `json` | JSON 折叠查看 | — |
| `qr` | 二维码 | `size` |
| `trend` | 趋势（涨跌箭头+百分比） | `suffix` |
| `sparkline` | 迷你折线 | — |
| `sparkbar` | 火花线（SVG） | `variant`, `width`, `height` |
| `heatmap` | 热力格（按强度深浅） | `max`, `palette` |
| `ranking` | 排名徽章（前三 trophy） | — |
| `gradient` | 渐变文字数值 | `from`, `to` |
| `datetime` | 日期时间 | `format` |
| `phone` | 电话（tel: 链接） | — |
| `email` | 邮箱（mailto:） | — |
| `ip` | IP 地址 | — |
| `url` | URL（可点击） | `target` |
| `rating` | 评分（只读展示） | `max` |
| `filesize` | 文件大小（字节→可读） | — |
| `truncate` | 截断 + 悬浮全文 | `len` |
| `tooltip` | 悬浮提示 | `content` |
| `popover` | 气泡卡片 | `content` |
| `dropdown` | 下拉菜单（避免被裁，见 fixedColumns） | `items` |
| `actions` | 操作按钮组（含确认/弹窗/下载） | `items:[{label,icon,url,method,confirm,modal,frame,page}]` |
| `page` | 点击弹窗加载后端页面 | `url`, `title`, `size`, `frame`, `reload` |
| `input` | 单元格输入框（change 即 PATCH 提交） | `url`, `size`, `placeholder` |
| `code` | 代码片段 | — |

> 渲染器一律经过 HTML 转义，杜绝 XSS。自定义渲染器可用官方插件 API 注册（推荐）：

```js
// 方式一（推荐，带覆盖警告）：XFAdmin.registerCellRenderer(name, fn)
XFAdmin.registerCellRenderer('riskLevel', function (d, row, cfg) {
    var map = { high: 'danger', mid: 'warning', low: 'success' };
    return '<span class="badge badge-soft-' + (map[d] || 'secondary') + '">' + d + '</span>';
});
// 方式二（直接挂载，等价但无警告）：XFAdmin.cellRenderers.xxx = fn
// 方式三（全局函数路径，无需注册）：type => 'js:window.MyApp.renderRisk'
```

注册后即可在 PHP 端直接使用：

```php
'render' => ['type' => 'riskLevel', 'cfg' => [...]]
```

> 注意：PHP 端 `DataTable::TYPES` 是已知类型白名单（用于配置校验/文档）。第三方插件渲染器若需通过白名单校验，
> 可在列配置里改用 `['type' => 'js:window.MyApp.renderRisk']` 全局函数路径形式，或暂时放宽校验（见 `DataTable::isKnownType()`）。

## 二、单元格事件系统

任意渲染器可通过 `render['event']` 为单元格绑定点击/双击/悬浮事件，点击时前端 `dispatch` 自定义事件，
开发者用 `XFAdmin.onCell(name, fn)` 订阅，实现「点击用户打开资料」「双击订单查看详情」等交互。

```php
'render' => ['type' => 'user', 'event' => ['click' => 'openUser', 'dblclick' => 'openUserDetail']]
```

```js
XFAdmin.onCell('openUser', function (detail) {
    // detail = { event:'click', el, row, value, field, originalEvent }
    XFAdmin.dialog({ url: '/admin/users/' + detail.row.id + '/view', title: detail.row.name });
});
```

支持的 `event` 形式：
- 字符串：`'event' => 'openUser'` → 等同 `{click:'openUser'}`
- 对象：`'event' => ['click' => 'a', 'dblclick' => 'b', 'hover' => 'c', 'hoverout' => 'd']`

事件类型：`click` / `dblclick` / `hover`（鼠标进入）/ `hoverout`（鼠标离开）。

## 三、自定义搜索 / 过滤组件类型

`filterBar` 配置项的 `type` 支持以下类型，前端自动渲染并即改即查（auto 模式）：

| type | 前端渲染 | cfg |
|------|----------|-----|
| `text` | 文本输入 | `placeholder` |
| `number` | 数字输入 | — |
| `select` | 下拉单选 | `options` |
| `select2` | 可搜索下拉（多选） | `options`, `multiple` |
| `multiple` | 多选下拉 | `options` |
| `date` / `daterange` | 日期 / 日期范围 | — |
| `color` | 颜色选择 | — |
| `slider` / `range-slider` | 双滑块范围 | `min`, `max`, `step`, `suffix` |
| `tree` | 树形多选（层级 options） | `options:[{value,label,children}]` |
| `autocomplete` | 自动完成（datalist 或远程） | `options` 或 `url`（远程搜索） |
| `checkbox` | 复选框组 | `options` |
| `radio` | 单选按钮组 | `options` |
| `custom` | 完全自定义 HTML | `control`（含 `class="xf-filter" data-filter="字段"` 的片段） |

### 示例

```php
'filter_bar' => [
    ['name' => 'status',  'type' => 'select',  'options' => ['active'=>'启用','inactive'=>'停用'], 'placeholder' => '全部状态'],
    ['name' => 'score',   'type' => 'slider',  'min' => 0, 'max' => 100, 'step' => 5, 'suffix' => '分'],
    ['name' => 'dept',    'type' => 'tree',    'options' => [['value'=>'dev','label'=>'研发','children'=>[['value'=>'fe','label'=>'前端'],['value'=>'be','label'=>'后端']]],['value'=>'ops','label'=>'运维']]],
    ['name' => 'region',  'type' => 'autocomplete', 'options' => ['华东'=>'华东','华北'=>'华北']],
    ['name' => 'tags',    'type' => 'custom',  'control' => '<div class="xf-filter" data-filter="tags"><input class="form-control form-control-sm" data-xf-custom-value=""></div>'],
],
```

- `slider`：双滑块，值形如 `lo-hi`（`*` 表示不限），如 `20-80`。
- `tree`：勾选叶子逗号连接，值如 `fe,be`。
- `autocomplete`：`url` 存在时前端可监听 `xf:filter-custom` 事件做远程搜索。
- `custom`：宿主监听 `xf:filter-custom` 事件并通过 `detail.getValue(v)` 回传过滤值。

## 四、行内编辑 / 弹窗 / 批量 / 领域操作

- **开关列**：`render['type'=>'switch', 'url'=>'/x/{id}', 'field'=>'status']` → 点击即 PATCH。
- **单元格输入**：`render['type'=>'input', 'url'=>'...', 'size'=>'sm']` → change 即提交。
- **操作列**：`render['type'=>'actions', 'items'=>[...]`，支持 `confirm` / `modal` / `page` / `method`。
- **批量操作**：`bulk` 配置渲染批量工具条；操作列勾选后调用 `data-xf-bulk-action`。
- **行明细**：`row_detail => true` 展开当前行全字段键值；与 `responsive` 互斥。
- **看板拖拽**：`kanban` 视图下拖拽卡片跨列即状态流转（PATCH `data-xf-update-url`）。

### 领域管理动作（业务闭环）

DataTable 支持「领域操作按钮」——按业务语义（工单派发/认领/转派/回复/评价、审批、入库/出库、任务完成等）
注入到行操作列 / 批量栏 / 详情操作面板，统一走 `POST /admin/api/enterprise/op` 与
`POST /admin/api/enterprise/bulk` 两个演示接收端点，后端真实更新数据 + 写入时间线（刷新不丢）。

**行操作列领域按钮**（`actions` 数组元素）额外支持：

| 键 | 类型 | 说明 |
|----|------|------|
| `op` | string | 领域动作名（`assign` / `claim` / `transfer` / `reply` / `rate` / `invite` / `inbound` / `outbound` / `inventory` / `complete` / `onboard` / `regularize` / `offboard` / `ship` / `refund` / `publish` / `run` / `pause` / `approve` / `reject` / `enable` / `disable`） |
| `dataset` | string | 目标数据集 id（如 `ent_ticket_list`），提交时随 `_op`/`id` 一并发送 |
| `prompt` | string | 需要用户输入的文本（回复/评价/转派/数量等），弹出输入框 |
| `arg` | string | 输入值对应的请求字段名（默认 `comment`；派发=`assignee`、转派=`to`、数量=`qty`、协办=`member`） |

前端提交契约：`{ _op, dataset, id, [arg]: value }`，由 `XFAdmin.request` 发送；后端
`DataController::op` 分发给 `EnterpriseData::domainOp()`（含负责人/数量字段探测 + 状态流转 + 时间线留痕）。

**批量按钮**（`bulk.actions` 数组元素）在原有 `label/icon/class/url/method/confirm/reload` 基础上新增：

| 键 | 类型 | 说明 |
|----|------|------|
| `action` | string | 批量动作名，渲染为 `data-action`，提交时放入 `{ action, dataset, ids }` |

后端 `DataController::bulk` 按 `action` 分发：领域动作逐行 `domainOp`，状态 key 批量置位，`delete` 批量删除，
`approve/reject/enable/disable` 映射到对应字段。

**看板卡片快捷操作**：看板卡片底部可渲染 `[data-xf-op]` 领域按钮（`<span role="button">`），
点击不触发卡片详情弹窗（`bindXfPageLinks` 已跳过），提交逻辑与行操作按钮一致。

**表格 dataset 标识**：DataTable 渲染时若 config 提供 `dataset` 键，`<table>` 会输出
`data-xf-dataset` 属性，供前端批量提交携带。`XFAdmin.prompt(opts)` 为新增的通用输入对话框
（优先 SweetAlert2，回退原生 `prompt`）。

## 五、固定操作列（fixedColumns）

当列数较多时，操作列会被横向滚动遮挡。DataTable 自动检测操作列（`actions` / `dropdown` / `buttons` 类型）
并启用 `fixed_columns => ['right' => 1]`，使操作列冻结在右侧（`.xf-dt-sticky`）。
下拉菜单的 Popper 定位已由 `initDtDropdownFix` 修正（fixed 定位 + 边界翻转），不再飞出视口。

## 六、前端扩展点

| 钩子 | 说明 |
|------|------|
| `XFAdmin.cellRenderers.xxx` | 注册自定义单元格渲染器 |
| `XFAdmin.onCell(name, fn)` | 订阅单元格事件 |
| `XFAdmin.onCommand(name, fn)` | 订阅命令面板 action |
| `XFAdmin.onUpload(id, fn)` | 订阅拖拽上传成功回调 |
| `XFAdmin.request(url, opts)` | 统一的 fetch 封装（返回 `{ok,status,data}`） |
| `XFAdmin.dialog(opts)` | 弹窗（支持 `url` 远程加载 / `body` 内联 / `frame` iframe） |
| `XFAdmin.toast(opts)` | 轻提示（`{body,variant}`） |
| `XFAdmin.prompt(opts)` | 输入对话框（`{text,title,placeholder,confirmText}`，优先 SweetAlert2，回退原生 prompt；返回 Promise） |

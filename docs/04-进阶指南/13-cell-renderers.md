# 单元格渲染器完全手册

`DataTable` 的列可通过 `render` 指定**渲染器**，把原始数据渲染成徽章、进度、开关、头像、二维码等富内容。
包内内置 **56 个渲染器**，本文逐个给出列配置示例与配置项。

> 源码：`resources/assets/js/xfadmin.js` 的 `XFAdmin.cellRenderers`
> 相关：[03-数据表格全指南 §4](03-datatable.md#4-单元格渲染器)

---

## 1. 三种写法

```php
// ① 字符串简写（无 cfg）
['key' => 'amount', 'label' => '金额', 'render' => 'money']

// ② 数组（type + cfg）
['key' => 'amount', 'label' => '金额', 'render' => ['type' => 'money', 'prefix' => '￥', 'decimals' => 2]]

// ③ 列上直接写渲染器名作为键（快捷键）
['key' => 'status', 'label' => '状态', 'badge' => ['variant' => 'success']]
['key' => 'avatar', 'label' => '头像', 'image' => ['height' => 32, 'circle' => true]]
['key' => 'id', 'label' => '操作', 'actions' => [...]]
```

**解析优先级**：内置/已注册渲染器 → `js:App.render.x` 全局函数 → `text` 兜底。

**自动行为**：
- 交互型渲染器（`actions`/`buttons`/`dropdown`/`input`/`select`/`switch`/`toggle`/`sparkline`/`timeline`）
  在列未显式指定时自动关闭排序与搜索；
- `data` 为空的列自动补 `defaultContent: ''`；
- `qr` 渲染器自动加载 `qrcode` 资源。

---

## 2. 渲染器速查表（56 个）

| 渲染器 | 用途 | 主要 cfg |
|---|---|---|
| `text` | 纯文本（转义）——所有渲染器兜底 | — |
| `badge` | 徽章 | `variant` `icon` `dot` `pill` `soft` |
| `statusPill` | 带状态点胶囊 | `variant` `map` `text` `icon` `soft` |
| `status` | 状态点 + 文案 | `map` |
| `priority` | 高/中/低 | — |
| `icon` | 图标 | `map` |
| `bool` | √ / × | — |
| `code` | `<code>` + 点击复制 | — |
| `color` | 色块 + 色值（点击复制） | — |
| `tags` | 标签组 | `variant` `variants` |
| `tagInput` | 只读标签组 | `variant` |
| `number` | 千分位数字 | `decimals` `prefix` `suffix` |
| `money` | 金额（负数红色） | `decimals` `prefix` |
| `currency` | 货币 | `symbol` `decimals` `color` |
| `percent` | 百分比（可带条） | `decimals` `bar` `variant` |
| `progress` | 进度条（自动配色） | `max` `variant` `striped` |
| `progressBar` | 阈值配色进度条 | `variant` `thresholds` `showVal` `suffix` |
| `miniBar` | 表内迷你条 | `max` `variant` `showVal` |
| `progressSteps` | 横向步骤点 | `steps` `variant` |
| `gradient` | 渐变文字数值 | `from` `to` |
| `heatmap` | 热力单格 | `max` `palette` |
| `ranking` | 排名徽章 | — |
| `trend` | 涨跌趋势 | `invert` `decimals` `suffix` |
| `sparkline` | SVG 迷你趋势图 | `type` `width` `height` `color` |
| `sparkbar` | SVG 迷你柱状 | `width` `height` `variant` |
| `duration` | 秒 → 时长文本 | — |
| `filesize` | 字节 → B/KB/MB/GB | — |
| `datetime` | 日期时间（可相对） | `ago` |
| `avatar` | 头像 + 名称 | `name_field` |
| `avatarGroup` | 头像堆叠 +N | `max` |
| `user` | 头像 + 姓名 + 副标题 | `avatar` `sub` `url` |
| `image` | 图片 | `height` `rounded` `circle` |
| `images` | 多图 +N | `max` `size` |
| `file` | 文件（图标+名+大小+下载） | `download` |
| `qr` | 二维码（点击放大/下载） | `size` `ec` `color` `bg` |
| `switch` | 开关（可提交） | `on` `off` `url` `field` |
| `toggle` | 按钮切换（可提交） | `on` `off` `on_label` `off_label` `url` `field` |
| `input` | 单元格输入框 | `size` `placeholder` `url` `field` |
| `select` | 单元格下拉 | `options` `url` `field` |
| `copy` | 只读输入 + 复制 | — |
| `copyBtn` | 复制按钮 | — |
| `ip` | IP（等宽 + 点击复制） | — |
| `link` | 链接 | `href` `text` `target` |
| `linkBtn` | 按钮式链接 | `url` `text` `variant` `icon` `target` `confirm` |
| `email` | `mailto:` 链接 | — |
| `phone` | `tel:` 链接 | — |
| `url` | 外链（新窗口） | `length` |
| `json` | 「查看」弹窗展示 JSON | — |
| `tooltip` | Bootstrap Tooltip | `text` `field` `placement` `length` `icon` |
| `popover` | Bootstrap Popover | `title` `content` `field` `placement` `trigger` `html` `icon` |
| `dropdown` | 单按钮下拉 | `label` `icon` `class` `items` |
| `actions` / `buttons` | 行操作栏 | `items` |
| `timeline` | 单元格时间线 | `max` `title` |
| `rate` / `rating` | 星级评分 | `max` |
| `truncate` | 截断长文本 + title | `length` |
| `rich` | 复合信息单元格 | `title` `sub` `meta` `icon` `avatar` `status` |
| `page` | 弹窗页链接 | `url` `title` `size` `frame` |

---

## 3. 分类详解：文本与标识

### `text`

```php
['key' => 'name', 'label' => '名称', 'render' => 'text']
```

值经 HTML 转义，是所有未知渲染器的兜底。

### `badge`

```php
['key' => 'status', 'label' => '状态',
 'render' => ['type' => 'badge', 'variant' => 'success', 'dot' => true, 'pill' => true, 'soft' => true]]

// 值本身就是对象时
// d = ['text' => '通过', 'variant' => 'success', 'icon' => 'ti ti-check']
```

`variant`：`primary|secondary|success|danger|warning|info|light|dark`。

### `statusPill`

```php
['key' => 'state', 'label' => '状态',
 'render' => ['type' => 'statusPill', 'map' => ['1' => 'success', '0' => 'secondary', '*' => 'light']]]
```

### `status`

```php
['key' => 'state', 'label' => '状态',
 'render' => ['type' => 'status', 'map' => ['active' => ['label' => '正常', 'color' => 'success']]]]
```

### `priority`

```php
['key' => 'level', 'label' => '优先级', 'render' => 'priority']
// 值：low|medium|high 或数值（≥7 高，≥4 中）
```

### `icon`

```php
['key' => 'type', 'label' => '类型',
 'render' => ['type' => 'icon', 'map' => ['image' => 'ti ti-photo text-info', 'video' => 'ti ti-video text-danger']]]
```

### `bool` / `code` / `color`

```php
['key' => 'enabled', 'render' => 'bool']        // √ / ×，识别 1/true/yes/on
['key' => 'sn',      'render' => 'code']        // <code> + 点击复制
['key' => 'theme',   'render' => 'color']       // 色块 + #hex，点击复制
```

### `tags` / `tagInput`

```php
['key' => 'tags', 'render' => ['type' => 'tags', 'variants' => ['primary', 'info', 'success']]]
['key' => 'labels', 'render' => ['type' => 'tagInput', 'variant' => 'primary']]
```

分隔符支持 `,` `，` `;` `；`。

---

## 4. 分类详解：数值与进度

```php
['key' => 'qty',     'render' => ['type' => 'number', 'decimals' => 0, 'suffix' => ' 件']]
['key' => 'amount',  'render' => ['type' => 'money', 'prefix' => '￥', 'decimals' => 2]]
['key' => 'budget',  'render' => ['type' => 'currency', 'symbol' => '$', 'decimals' => 2, 'color' => 'success']]
['key' => 'rate',    'render' => ['type' => 'percent', 'decimals' => 1, 'bar' => true]]
['key' => 'progress','render' => ['type' => 'progress', 'max' => 100, 'striped' => true]]
['key' => 'score',   'render' => ['type' => 'progressBar',
                                  'thresholds' => [['max' => 30, 'variant' => 'danger'], ['max' => 70, 'variant' => 'warning']],
                                  'showVal' => true, 'suffix' => '%']]
['key' => 'sales',   'render' => ['type' => 'miniBar', 'max' => 1000, 'showVal' => true]]
['key' => 'step',    'render' => ['type' => 'progressSteps', 'steps' => ['下单', '付款', '发货', '完成']]]
['key' => 'heat',    'render' => ['type' => 'heatmap', 'max' => 100, 'palette' => ['#e8f0fe', '#3e60d5']]]
['key' => 'rank',    'render' => 'ranking']
['key' => 'growth',  'render' => ['type' => 'trend', 'suffix' => '%', 'decimals' => 1, 'invert' => false]]
['key' => 'vals',    'render' => ['type' => 'sparkline', 'type' => 'line', 'width' => 90, 'height' => 28]]
['key' => 'bars',    'render' => ['type' => 'sparkbar', 'width' => 90, 'height' => 28, 'variant' => 'primary']]
['key' => 'seconds', 'render' => 'duration']            // 3661 → 1时1分1秒
['key' => 'size',    'render' => 'filesize']            // 1048576 → 1 MB
['key' => 'num',     'render' => ['type' => 'gradient', 'from' => '#f59e0b', 'to' => '#3e60d5']]
```

---

## 5. 分类详解：日期时间

```php
['key' => 'created_at', 'render' => 'datetime']                       // 2026-09-09 12:00:00
['key' => 'created_at', 'render' => ['type' => 'datetime', 'ago' => true]]   // 3 分钟前
```

---

## 6. 分类详解：人员与媒体

```php
['key' => 'avatar',  'render' => ['type' => 'avatar', 'name_field' => 'name']]
['key' => 'members', 'render' => ['type' => 'avatarGroup', 'max' => 3]]     // 元素：url 或 {url,name}
['key' => 'owner',   'render' => ['type' => 'user', 'avatar' => 'avatar_url', 'sub' => 'email', 'url' => '/u/{id}']]
['key' => 'cover',   'render' => ['type' => 'image', 'height' => 32, 'circle' => true]]
['key' => 'photos',  'render' => ['type' => 'images', 'max' => 3, 'size' => 28]]
['key' => 'attach',  'render' => ['type' => 'file', 'download' => true]]   // d：url 或 {url,name,size,icon}
['key' => 'code',    'render' => ['type' => 'qr', 'size' => 64, 'ec' => 'M']]  // 自动加载 qrcode
```

> `qr` 渲染器点击可放大弹窗，支持下载与打开链接。

---

## 7. 分类详解：交互控件

### `switch`（可提交）

```php
['key' => 'status', 'render' => ['type' => 'switch', 'url' => '/admin/users/{id}',
                                 'field' => 'status', 'on' => 1, 'off' => 0]]
```

```js
document.addEventListener('xf:switch', e => console.log(e.detail.checked, e.detail.row));
```

### `toggle`

```php
['key' => 'state', 'render' => ['type' => 'toggle', 'on' => 1, 'off' => 0,
                                'on_label' => '启用', 'off_label' => '禁用',
                                'url' => '/admin/users/{id}', 'field' => 'state']]
```

### `input` / `select`（行内编辑）

```php
['key' => 'sort', 'render' => ['type' => 'input', 'size' => 'sm', 'placeholder' => '排序',
                               'url' => '/admin/users/{id}', 'field' => 'sort']]

['key' => 'role', 'render' => ['type' => 'select', 'field' => 'role', 'url' => '/admin/users/{id}',
                               'options' => ['admin' => '管理员', 'user' => '普通用户']]]
```

### 链接与复制

```php
['key' => 'title',   'render' => ['type' => 'link', 'href' => '/post/{id}', 'text' => '{title}', 'target' => '_blank']]
['key' => 'id',      'render' => ['type' => 'linkBtn', 'url' => '/admin/order/{id}', 'text' => '查看',
                                  'variant' => 'soft-primary', 'icon' => 'ti ti-eye']]
['key' => 'email',   'render' => 'email']
['key' => 'mobile',  'render' => 'phone']
['key' => 'site',    'render' => ['type' => 'url', 'length' => 32]]
['key' => 'token',   'render' => 'copy']
['key' => 'token',   'render' => 'copyBtn']
['key' => 'ip',      'render' => 'ip']
['key' => 'payload', 'render' => 'json']          // 「查看」按钮 + 弹窗格式化 JSON
```

### `tooltip` / `popover`

```php
['key' => 'remark', 'render' => ['type' => 'tooltip', 'field' => 'remark', 'length' => 20,
                                 'placement' => 'top', 'icon' => 'ti ti-info-circle']]

['key' => 'name',   'render' => ['type' => 'popover', 'title' => '详情', 'content' => '{description}',
                                 'trigger' => 'hover', 'html' => true, 'placement' => 'right']]
```

> 每次 `draw.dt` 后会重建 Tooltip / Popover 实例。

### `dropdown` / `actions`

```php
['key' => '', 'label' => '操作', 'render' => ['type' => 'dropdown', 'label' => '更多', 'icon' => 'ti ti-dots',
    'items' => [
        ['label' => '编辑', 'ajax' => '/admin/u/{id}', 'method' => 'PUT'],
        ['type' => 'divider'],
        ['label' => '删除', 'ajax' => '/admin/u/{id}', 'method' => 'DELETE', 'confirm' => '确认？'],
    ]]]

['key' => '', 'label' => '操作', 'actions' => [ /* 同 items，见下文 */ ]]
```

---

## 8. `actions` 行操作项完整字段

| 键 | 说明 |
|---|---|
| `label` / `icon` / `class` / `title` | 文案与样式（默认 `btn-soft-primary`） |
| `action` | `edit` / `view` / `modal` / `ajax` / `delete` / `copy-row` / `download` / `print` / `share` / 自定义 |
| `url` | 存在则渲染为 `<a href>`（支持 `{field}` 占位），**优先于 ajax** |
| `ajax` / `method` | 请求地址与方法（默认 POST；`delete` 默认 DELETE） |
| `confirm` / `confirm_popover` | 确认文案 / 气泡确认 |
| `reload` | 成功后刷新表格 |
| `event` | 指定时不执行内置动作，改派发自定义事件 |
| `op` / `dataset` / `prompt` / `arg` | 领域动作：提交 `{_op, dataset, id, [arg]:value}` |
| `fields` / `editTitle` / `size` / `frame` | `edit` 弹窗字段与标题 |
| `view` / `viewTitle` | `view` 详情配置 |
| `dropdown` | 子项数组（支持 `type => 'divider'`） |

---

## 9. 其它常用渲染器

```php
['key' => 'score',    'render' => ['type' => 'rate', 'max' => 5]]           // 支持半星
['key' => 'score',    'render' => ['type' => 'rating', 'max' => 5]]         // Tabler 星标
['key' => 'summary',  'render' => ['type' => 'truncate', 'length' => 30]]
['key' => 'logs',     'render' => ['type' => 'timeline', 'max' => 2, 'title' => '流转记录']]
['key' => 'customer', 'render' => ['type' => 'rich', 'title' => '{name}', 'sub' => '{company}',
                                   'avatar' => '{avatar}', 'status' => '{status}']]
['key' => 'id',       'render' => ['type' => 'page', 'url' => '/admin/u/{id}/edit',
                                   'title' => '编辑用户', 'size' => 'lg']]
```

---

## 10. 自定义渲染器

### 10.1 前端注册

```js
XFAdmin.registerCellRenderer('myMoney', function (d, row, cfg) {
    const n = Number(d || 0).toLocaleString('zh-CN', { minimumFractionDigits: cfg.decimals ?? 2 });
    return '<span class="text-success fw-semibold">' + (cfg.prefix || '') + n + '</span>';
});
```

```php
['key' => 'amount', 'render' => ['type' => 'myMoney', 'prefix' => '￥', 'decimals' => 2]]
```

### 10.2 全局函数（`js:` 前缀，支持点号路径）

```php
['key' => 'amount', 'render' => 'js:App.render.money']
```

```js
window.App = window.App || {};
App.render = { money(d, row, cfg) { return '￥' + Number(d).toFixed(2); } };
```

> 覆盖内置渲染器会 `console.warn` 提示。

---

## 11. 单元格事件

```php
// 单事件
['key' => 'name', 'render' => ['type' => 'text', 'event' => 'onNameClick']]

// 多事件
['key' => 'x', 'render' => ['type' => 'text', 'event' => ['click' => 'onX', 'dblclick' => 'onXDbl']]]
```

```js
XFAdmin.onCell('onNameClick', function (ctx) {
    // ctx: {event, el, row, value, field, originalEvent}
    console.log(ctx.row.id, ctx.value);
});
```

配置了 `event` 的单元格会被 `<span class="xf-cell-event" data-xf-cell-event="…">` 包裹。

---

## 12. 陷阱清单

1. `enum`、`avatarStack` 在**快捷键白名单**中，但前端无同名渲染器 → 静默回退 `text`；
2. `ip`、`qr`、`money`、`filesize` 在 `cellRenderers` 中存在，但**不在快捷键白名单** →
   只能通过 `'render' => 'ip'`（字符串形式）使用；
3. `qr` 会自动注入 `qrcode` 资源（每个单元格生成二维码，大数据量慎用）；
4. 交互型渲染器生成的控件会为每个单元格创建实例，列数多时影响渲染性能；
5. `renders` 中 `{field}` 占位由 `XFAdmin.tpl` 插值，值会 HTML 转义（属性场景用 `tplRaw` 自行处理）；
6. 服务端模式下渲染器在前端执行，因此 `row` 必须包含所需字段（后端 `select` 不能漏列）；
7. Tooltip / Popover 在每次 `draw.dt` 后重建，自定义 DOM 需在此后初始化；
8. `actions` 按钮若同时写了 `data-xf-op` 与 `data-xf-act` 可能重复触发，保留其一。

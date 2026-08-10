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
| `tooltip` | `render => ['type' => 'tooltip', 'text' => '注册于 {created_at}', 'length' => 12]` | 悬浮提示（Bootstrap Tooltip），`text` 支持 `{field}` 占位，`length` 截断展示 |
| `popover` | `render => ['type' => 'popover', 'title' => '备注', 'content' => '{remark}']` | 气泡提示，点击/聚焦弹出，`trigger`/`placement`/`html` 可配 |
| `toggle` | `render => ['type' => 'toggle', 'url' => '/api/x/{id}/toggle', 'on_label' => '已启用', 'off_label' => '已停用']` | 按钮式状态切换：点击 POST 提交后翻转按钮文案/配色，失败保持原状态，派发 `xf:toggle` |
| `status` | `render => ['type' => 'status', 'map' => ['active' => ['label' => '在线', 'color' => 'success']]]` | 彩色状态点 + 文案 |
| `trend` | `render => ['type' => 'trend', 'suffix' => '%']` | 涨跌趋势：正值绿升/负值红降 + 箭头图标，`invert` 反转语义 |
| `sparkline` | `render => ['type' => 'sparkline', 'type' => 'line'\|'bar']` | 迷你趋势图（内联 SVG，零依赖），值为数字数组或逗号串 |
| `filesize` | `render => 'filesize'` | 字节数 → 人类可读大小（B/KB/MB/GB/TB） |
| `file` | `render => ['type' => 'file']` | 文件单元格：图标（按扩展名）+ 文件名（截断悬浮）+ 人类可读大小 + 下载按钮；数据可为字符串 url 或 `{url, name, size, icon, download}`，`download:false` 关闭下载按钮，`{field}` 占位 |
| `avatarGroup` | `render => ['type' => 'avatarGroup', 'max' => 3]` | 多人头像组：元素为字符串 url 或 `{url, name}`，最多显示 `max`（默认 3）个，超出折叠为 `+N`；无图时显示姓名首字母 |
| `qr` | `render => ['type' => 'qr']` | 二维码单元格（依赖 qrcode-generator）：内容可为 URL / 纯文本 / 含中文的非 ASCII 内容（自动 UTF-8 编码）；`text` 支持 `{字段}` 占位，可选 `size`(像素) / `ec`(纠错 L·M·Q·H) / `color` / `bg`；点击放大查看，链接内容额外「打开链接」按钮 |

```php
XfAdmin::datatable([
    'id' => 'dt-files',
    'data' => [
        ['id' => 1, 'name' => '需求文档', 'file' => ['url' => '/files/prd.pdf', 'name' => 'PRD.pdf', 'size' => 245760],
         'team' => [['url' => 'users/1.jpg', 'name' => '王伟'], ['url' => '', 'name' => '李娜'], ['url' => 'users/3.jpg', 'name' => '张强']]],
    ],
    'columns' => [
        ['data' => 'name', 'title' => '文档', 'render' => 'truncate'],
        ['data' => 'file', 'title' => '附件', 'render' => ['type' => 'file'], 'width' => '280px'],
        ['data' => 'team', 'title' => '协作成员', 'render' => ['type' => 'avatarGroup', 'max' => 3], 'width' => '160px'],
        ['key' => '', 'title' => '操作', 'actions' => [
            ['label' => '下载', 'icon' => 'ti ti-download', 'action' => 'download', 'ajax' => '{file.url}'],
            ['label' => '打印', 'icon' => 'ti ti-printer', 'action' => 'print', 'ajax' => '{file.url}', 'title' => '打印预览'],
            ['label' => '分享', 'icon' => 'ti ti-share', 'action' => 'share', 'ajax' => '/share/{id}'],
        ]],
    ],
]);
```

- `file` 的 `size` 可用 `render` 配置里的 `download:false` 关闭下载按钮；`icon` 可手动指定图标类覆盖扩展名自动匹配。
- `avatarGroup` 的 `url` 走组件 `img()` 解析（外链/ data URI 原样返回），`name` 用于悬浮 `title` 与无图首字母。
- `qr` 渲染器由 DataTable 自动按需加载 `qrcode` 库（无需手动声明）；`text` 省略时默认使用单元格数据（如 `'data' => 'url'` 整列即二维码）。完整示例见 `demo/pages/tables.php`「二维码单元格」卡片。

| `timeline` | `render => ['type' => 'timeline', 'max' => 2]` | 单元格时间线，值为 `[{time,title,text,color}]`；超过 `max` 条出现「查看全部」弹窗 |
| `dropdown` | `render => ['type' => 'dropdown', 'label' => '操作', 'items' => [...]]` | 单按钮点击展开下拉操作组（子项同 actions） |
| `buttons` | 同 `actions` | 按钮组别名 |
| `actions` | `actions => [...]` | 行操作栏（见下节） |

> 前端渲染器全部挂在 `XFAdmin.cellRenderers` 上，可通过 `XFAdmin.cellRenderers.myType = (cell, row, col) => '...'` 自定义扩展（见[扩展组件](extending.md)）。
> `tooltip` / `popover` 渲染器在每次 `draw.dt` 后自动初始化 Bootstrap 实例，无需手工处理。

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
| `view` | 详情弹窗；无配置时自动键值对展示（复用列头中文名与列渲染器），配 `view` 后支持个性化布局（见下节） |
| `edit` | 模态表单编辑闭环：`fields` 定义控件 → 保存经 `ajax`（默认 PUT）提交 → toast + 自动刷新表格 |
| `delete` | 删除闭环：确认框 → `ajax`（默认 DELETE）→ toast + 刷新；本地数据源省略 `ajax` 时直接移除行 |
| `link` | 跳转 `url`（支持 `{id}` 占位替换为当前行 id） |
| `copy-row` | 复制整行 JSON 到剪贴板（可选 `confirm`） |
| `ajax` | 发送 `fetch` 到 `url`，可选 `confirm`，成功后 toast 并自动重载表格 |
| `custom` | 仅派发 `xf:action` 事件，交由你的代码处理（见[扩展组件](extending.md)） |
| `dialog` / `form` | 弹窗（可内嵌表单），配合自定义事件完成复杂操作 |
| `download` | 下载 `ajax`（即 url，支持 `{字段}` 占位）；同源强制触发下载，外链新标签页打开 |
| `print` | 打印：配 `ajax`（url）则弹窗 iframe 加载该页面并触发打印对话框；否则以弹窗展示当前行键值对预览后调用浏览器打印 |
| `share` | 复制分享链接（`ajax` 即链接，支持 `{字段}` 占位）并 toast 提示；若浏览器支持 `navigator.share` 则唤起原生分享面板（可用 `noshare` 禁用） |

### 个性化详情弹窗（`action: view` + `view` 配置）

不同业务功能的详情应有不同的排版。`view` 配置驱动多布局详情引擎：

```php
['label' => '详情', 'action' => 'view', 'view' => [
    'title'   => '成员档案 - {name}',          // {field} 占位
    'size'    => 'xl',                         // sm | lg | xl
    'layout'  => 'tabs',                       // kv | profile | tabs | sections | template
    'ajax'    => '/api/staff/{id}',            // 打开时拉取详情接口并合并展示（列表轻、详情全）
    'header'  => [                             // 档案头（profile 布局默认展示，其他布局可显式开启）
        'avatar' => 'avatar', 'title' => '{name}', 'sub' => '{email}',
        'badge'  => ['field' => 'status', 'map' => ['active' => ['label' => '在线', 'color' => 'success']]],
    ],
    'sections' => [                            // 分区（tabs 布局下每区一个标签页）
        ['title' => '基础信息', 'type' => 'kv',       'cols' => 2, 'fields' => ['id', 'name', 'email']],
        ['title' => '参与项目', 'type' => 'table',    'field' => 'projects', 'columns' => ['name' => '项目', 'role' => '角色']],
        ['title' => '安全日志', 'type' => 'timeline', 'field' => 'security_log'],
        ['title' => '关键指标', 'type' => 'stats',    'fields' => [['field' => 'quota', 'label' => '完成度', 'suffix' => '%']]],
        ['title' => '能力画像', 'type' => 'progress', 'fields' => [['field' => 'perf', 'label' => '绩效']]],
        ['title' => '技能标签', 'type' => 'tags',     'field' => 'tags'],
        ['title' => '作品集',   'type' => 'images',   'field' => 'photos'],
        ['title' => '自由排版', 'type' => 'html',     'template' => '<div>{bio}</div>'],
    ],
    'labels'  => ['id' => 'ID'],               // 补充/覆盖列头中文名
    'exclude' => ['password'],                 // 排除字段
    // layout=template 时：'template' => '<div class="text-center">{name} ...</div>'
]],
```

- 布局：`kv` 键值对（默认）、`profile` 档案头 + 双列 kv、`tabs` 分区标签页、`sections` 分区堆叠、`template` 任意 HTML 模板（占位值自动转义）。
- 分区类型：`kv` / `table`（子表格）/ `timeline` / `stats`（统计卡）/ `tags` / `progress` / `images` / `html`。
- 零配置时自动复用表格列头中文名与列渲染器，详情观感与表格一致。
- 完整可运行示例见 `demo/pages/tables.php`「全渲染器矩阵」与「个性化详情布局」两张卡片（配套 Mock API `/api/demo/*` 演示编辑/删除/切换全闭环）。

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
- 控件 `type` 支持：`select`（默认，需 `options`）、`select2`（可搜索）、`text`/`search`、`number`、`range`（数值区间）、
  `date`/`datetime`/`time`/`month`/`week`/`year`、`daterange`/`datetimerange`/`timerange`（区间，自动拆 `_from`/`_to`）、
  `radio`（按钮组单选）、`checkbox`（单勾选）、`checkboxes`（复选组）、`color`。

### 自定义过滤控件（开发者完全自定义）

filter_bar 项传 `html` 键即可注入任意 HTML 控件，控件内部凡带 `class="xf-filter"` 与 `data-filter="参数名"` 的
表单元素都会被自动采集进查询参数，与内置控件混用无缝：

```php
'filter_bar' => [
    ['type' => 'search', 'name' => 'name', 'label' => '姓名'],
    // 完全自定义：Bootstrap 按钮组单选
    ['html' => '<label class="form-label">职级</label>'
        . '<div class="btn-group w-100">'
        . '<input type="radio" class="btn-check xf-filter" data-filter="grade" name="g" id="g-all" value="" checked>'
        . '<label class="btn btn-outline-secondary btn-sm" for="g-all">全部</label>'
        . '<input type="radio" class="btn-check xf-filter" data-filter="grade" name="g" id="g-p7" value="P7">'
        . '<label class="btn btn-outline-secondary btn-sm" for="g-p7">P7</label>'
        . '</div>', 'width' => 'col-md-4'],
],
```

| 参数 | 类型 | 说明 |
|------|------|------|
| `html` | string | 原样注入的控件 HTML（自行保证转义安全） |
| `width` | string | 栅格宽度类，默认 `col-6 col-md-3 col-xl-2` |

也可以把整个过滤表单放在表格之外：任意 `<form data-xf-filter-for="表格id">` 中带 `name` 或
`data-filter` 的控件都会参与过滤（提交即查询）。

---

## 弹窗页单元格 `page` 渲染器

单元格文本渲染为可点击链接，点击后弹窗加载**服务端渲染的页面**（如编辑页/详情页），
关闭弹窗时可选择是否自动刷新表格：

```php
'columns' => [
    ['data' => 'name', 'title' => '成员', 'render' => [
        'type'   => 'page',
        'url'    => '/admin/users/{id}/edit',   // {字段} 占位取当前行数据
        'title'  => '编辑成员 #{id}',            // 弹窗标题，同样支持占位
        'size'   => 'lg',                       // sm | lg | xl | fullscreen
        'frame'  => false,                      // true=iframe 整页嵌入；false=提取 [data-xf-page-content] 片段
        'reload' => true,                       // 关闭弹窗后是否刷新表格（默认 true）
        'text'   => null,                       // 覆盖显示文本（默认单元格值），支持占位
        'icon'   => null,                       // 前置图标类
    ]],
],
```

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `url` | string | — | 弹窗加载的后端页面地址，`{字段}` 占位自动替换（URL 编码） |
| `title` | string | 单元格值 | 弹窗标题，支持 `{字段}` 占位 |
| `size` | string | `lg` | 弹窗尺寸 |
| `frame` | bool | `false` | `true` 用 iframe 嵌入整页（子页可用 `XFAdmin.dialogBridge` 与父页交互） |
| `reload` | bool | `true` | 关闭弹窗后是否 `ajax.reload` 刷新本表 |
| `text` / `icon` | string | — | 自定义链接文本 / 前置图标 |

---

## 开发者自定义单元格渲染 `js:` 前缀

两种方式任选：

**方式一：`js:` 前缀调用全局函数**（免注册，支持点号路径）：

```php
['data' => 'quota', 'title' => '完成度', 'render' => 'js:demoQuotaCell'],
// 或 'render' => 'js:App.render.quota'
```

```js
// 签名与内置渲染器一致：fn(data, row, cfg, meta) => HTML 字符串
function demoQuotaCell(d, row) {
    var color = d >= 80 ? 'success' : 'danger';
    return '<span class="badge bg-' + color + '-subtle text-' + color + '">' + d + '%</span>';
}
```

**方式二：注册可复用渲染器**（多表共享，PHP 端 `'render' => ['type' => 'myType', ...任意配置]`）：

```js
XFAdmin.cellRenderers.myType = function (data, row, cfg, meta) {
    return '<b>' + XFAdmin.escapeHtml(String(data)) + '</b>';
};
```

自定义函数抛异常时自动回退为纯文本渲染，不会破坏表格。

---

## 弹窗与父页面交互 `XFAdmin.dialogBridge`

弹窗（`page` 单元格 / 行操作 `edit` / `create` 新建按钮）加载的子页面可通过桥接 API 与父页面交互；
`frame`（iframe）与片段模式下代码完全一致，非弹窗环境自动降级为本地行为：

```js
XFAdmin.dialogBridge.inDialog;          // 是否运行于弹窗 iframe 内
XFAdmin.dialogBridge.close();           // 关闭弹窗
XFAdmin.dialogBridge.closeAndReload();  // 关闭弹窗并刷新父页表格
XFAdmin.dialogBridge.markReload();      // 仅标记「关闭时刷新」（不立即关闭）
XFAdmin.dialogBridge.toast('已保存', 'success'); // 在父页面弹出提示
```

父页面侧的挂点：

```js
// 弹窗关闭事件（detail.reloaded 表示是否已触发表格刷新）
document.addEventListener('xf:dialog-closed', function (e) {
    console.log(e.detail.url, e.detail.reloaded);
});

// 编程式打开弹窗页
XFAdmin.pageDialog({
    url: '/admin/users/5/edit',
    title: '编辑成员',
    size: 'lg',
    frame: false,                        // true=iframe
    tableEl: document.getElementById('dt-users'),  // 关闭后刷新该表
    onClose: function (reloaded) {},     // 关闭回调
});
```

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

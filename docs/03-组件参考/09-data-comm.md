# 业务组件（四）沟通 · 邮件

> 聊天应用与会话面板、邮件应用、邮件撰写、邮件列表与 Outlook 风格客户端。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`chatApp`](#chatapp) — 聊天应用整页（会话+消息）
- [`chatBox`](#chatbox) — 聊天框（消息气泡容器）
- [`chatConversationPanel`](#chatconversationpanel) — 聊天会话面板（单会话消息区）
- [`chatMessageBubble`](#chatmessagebubble) — 聊天气泡（单条消息）
- [`emailApp`](#emailapp) — 邮件应用整页（列表+阅读+撰写）
- [`emailCompose`](#emailcompose) — 邮件撰写（收件人/主题/正文/附件）
- [`mailList`](#maillist) — 邮件列表（收件箱条目）
- [`outlook`](#outlook) — Outlook 风格邮件客户端

## 本章导读

沟通与邮件类组件。聊天与邮件应用为整页级布局，会话面板/气泡为可复用片段。

### 组合范式

```php
echo XfAdmin::chatApp(['contacts' => \$contacts, 'messages' => \$messages]);
echo XfAdmin::emailApp(['folders' => [...], 'mails' => [...]]);
echo XfAdmin::chatConversationPanel(['messages' => \$messages, 'action' => '/chat/send']);
```

### 约定与陷阱

- 聊天/邮件的前端交互由 `chat-scroll`、`chat-form`、`email` 等 widget 提供；
- 发送消息会派发 `xf.chat.send` 事件，可监听后走 AJAX；
- `outlook` 为 Outlook 风格三栏布局，适合整页使用。


---

### `chatApp`

聊天应用整页（会话+消息）。

> **类**：`zxf\XfAdmin\Components\Data\ChatApp`
> **文件**：`src/Components/Data/ChatApp.php`（94 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::chatApp([
    'conversations' => [
        ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'last' => '好的，明天见',
         'time' => '10:02', 'unread' => 2, 'active' => true, 'online' => true, 'url' => '#'],
    ],
    'peer'     => ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'online' => true, 'status' => '在线'],
    'messages' => [
        ['from' => 'other', 'text' => '你好！', 'time' => '09:58'],
        ['from' => 'me',    'text' => '在的，请讲', 'time' => '09:59'],
    ],
    'placeholder' => '输入消息…',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::chatApp([
    'conversations' => [],
    'peer' => [],
    'messages' => [],
    'search' => '搜索联系人…',
    'placeholder' => '输入消息…',
    'height' => '60vh',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `conversations[]` 元素键：`online`、`unread`、`url`（默认 `#`）、`active`、`avatar`、`name`、`last`、`time`
- `peer[]` 元素键：`avatar`、`online`、`name`、`status`
- `messages[]` 元素键：`from`、`avatar`、`text`、`time`

> **渲染骨架**：主要 class `badge` `bg-danger` `rounded-pill` `ms-1` `avatar` `avatar-sm` `position-relative` `me-2`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `conversations` | array | `[]` | 会话列表（聊天） |
| `peer` | array | `[]` | 对方（会话对象）信息 |
| `messages` | array | `[]` | 消息列表（聊天/通知/消息中心条目） |
| `search` | string | `'搜索联系人…'` | 是否启用搜索；**文本槽位**：输出前自动 HTML 转义 |
| `placeholder` | string | `'输入消息…'` | 占位提示文案；**文本槽位**：输出前自动 HTML 转义 |
| `height` | string | `'60vh'` | 高度（CSS 长度，受安全白名单约束） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `chatBox`

聊天框（消息气泡容器）。

> **类**：`zxf\XfAdmin\Components\Data\ChatBox`
> **文件**：`src/Components/Data/ChatBox.php`（87 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::chatBox([
    'title'    => '张三',
    'status'   => '在线',
    'avatar'   => 'users/avatar-2.jpg',
    'height'   => '460px',
    'messages' => [
        ['from' => 'them', 'text' => '你好', 'time' => '10:00', 'avatar' => 'users/avatar-2.jpg'],
        ['from' => 'me',   'text' => '在的', 'time' => '10:01'],
        // text 默认转义（防 XSS）；富文本消息请使用 'html' 字段（调用方自行保证安全）
        ['from' => 'them', 'html' => '<b>加粗</b>', 'time' => '10:02'],
    ],
    'input'    => true,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::chatBox([
    'title' => '',
    'status' => null,
    'avatar' => null,
    'height' => '460px',
    'messages' => [],
    'input' => true,
    'header' => true,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `messages[]` 元素键：`from`（默认 `them`）、`avatar`、`html`、`text`、`time`

> **渲染骨架**：主要 class `card-header` `d-flex` `align-items-center` `gap-2` `avatar` `avatar-md` `flex-shrink-0` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `status` | mixed | `null` | 状态值 / 状态映射；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `avatar` | mixed | `null` | 头像地址（自动解析为包内图片 URL）；开关：非空 / 真值时启用对应区块 |
| `height` | string | `'460px'` | 高度（CSS 长度，受安全白名单约束）；CSS 长度：仅接受 `数字+单位`（px/%/rem/em/vh/vw/pt/ch/fr） |
| `messages` | array | `[]` | 消息列表（聊天/通知/消息中心条目） |
| `input` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `header` | bool | `true` | 头部内容（原样输出）；开关：非空 / 真值时启用对应区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `chatConversationPanel`

聊天会话面板（单会话消息区）。

> **类**：`zxf\XfAdmin\Components\Data\ChatConversationPanel`
> **文件**：`src/Components/Data/ChatConversationPanel.php`（66 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::chatConversationPanel([
    'contacts' => [
        ['id'=>1, 'name'=>'张三', 'avatar'=>'users/user-1.jpg', 'last'=>'在吗？', 'time'=>'10:20', 'unread'=>2, 'online'=>true],
    ],
    'messages' => [ ChatMessageBubble 实例 或 ['side'=>..., 'text'=>...] ],
    'title'    => '在线客服',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::chatConversationPanel([
    'contacts' => [],
    'messages' => [],
    'title' => '会话',
    'me' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `contacts[]` 元素键：`online`、`unread`、`avatar`、`name`、`time`、`last`
- `messages[]` 元素键：`render`

> **渲染骨架**：主要 class `list-group` `list-group-flush` `xf-chat-contacts` `border-end` `badge` `bg-danger` `rounded-pill` `float-end`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `contacts` | array | `[]` | 联系人列表 |
| `messages` | array | `[]` | 消息列表（聊天/通知/消息中心条目） |
| `title` | string | `'会话'` | 标题文本（部分组件为弹窗/tooltip 标题）；源码用法：`$title = $this->get('title');` |
| `me` | mixed | `null` | 当前用户（聊天气泡定位自己） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `chatMessageBubble`

聊天气泡（单条消息）。

> **类**：`zxf\XfAdmin\Components\Data\ChatMessageBubble`
> **文件**：`src/Components/Data/ChatMessageBubble.php`（58 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::chatMessageBubble([
    'side'     => 'in',   // in（对方/左） | out（自己/右）
    'avatar'   => 'users/user-1.jpg',
    'text'     => '您好，请问订单进度？',
    'time'     => '10:24',
    'read'     => true,
    'attach'   => null,   // 可选：['url'=>..., 'name'=>...]
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::chatMessageBubble([
    'side' => 'in',
    'avatar' => null,
    'text' => '',
    'time' => '',
    'read' => false,
    'attach' => null,
]);
```

</details>

> **渲染骨架**：主要 class `flex-shrink-0` `col-8`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `side` | string | `'in'` | 源码用法：`$out = $this->get('side') === 'out';` |
| `avatar` | mixed | `null` | 头像地址（自动解析为包内图片 URL）；源码用法：`$avatar = $this->get('avatar');` |
| `text` | string | `''` | 正文/按钮文案（纯文本语义，输出时转义）；源码用法：`$text = $this->get('text');` |
| `time` | string | `''` | 时间文本；源码用法：`$time = $this->get('time');` |
| `read` | bool | `false` | 源码用法：`$read = $this->get('read');` |
| `attach` | mixed | `null` | 源码用法：`$attach = $this->get('attach');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `emailApp`

邮件应用整页（列表+阅读+撰写）。

> **类**：`zxf\XfAdmin\Components\Data\EmailApp`
> **文件**：`src/Components/Data/EmailApp.php`（109 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::emailApp([
    'folders' => [['icon'=>'ti ti-inbox','name'=>'收件箱','count'=>12,'active'=>true], ...],
    'messages' => [
        ['id'=>1,'from'=>'张三','avatar'=>'users/user-1.jpg','from_email'=>'z@x.com',
         'subject'=>'周末计划','preview'=>'…','time'=>'10:30','unread'=>true,'starred'=>false,'attachments'=>1],
    ],
    'selected' => ['from'=>'李四','avatar'=>'...','from_email'=>'l@x.com','subject'=>'Re:…','time'=>'昨天','body'=>'…','attachments'=>[['name'=>'a.pdf','size'=>'1MB']]],
    'view' => 'split',          // split | preview（仅列表，点击外部打开）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::emailApp([
    'folders' => [],    // 文件夹列表（文件管理器）
    'messages' => [],    // 消息列表（聊天/通知/消息中心条目）
    'selected' => [],    // 是否选中 / 选中值
    'view' => 'split',    // 详情视图配置（viewRow 引擎，见数据表格文档）
    'composeText' => '写邮件',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `folders[]` 元素键：`active`、`count`、`icon`（默认 `ti ti-folder`）、`name`
- `messages[]` 元素键：`unread`、`starred`、`avatar`、`attachments`、`from`、`subject`、`preview`、`time`
- `selected[]` 元素键：`avatar`、`from`、`subject`、`time`、`from_email`、`body`、`attachments`

> **渲染骨架**：主要 class `card` `row` `g-0` `email-app` `col-xl-2` `col-lg-3` `border-end` `p-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `folders` | array | `[]` | 文件夹列表（文件管理器） |
| `messages` | array | `[]` | 消息列表（聊天/通知/消息中心条目） |
| `selected` | array | `[]` | 是否选中 / 选中值 |
| `view` | string | `'split'` | 详情视图配置（viewRow 引擎，见数据表格文档）；源码用法：`$view = $this->get('view');` |
| `composeText` | string | `'写邮件'` | 源码用法：`$composeText = $this->get('composeText');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `emailCompose`

邮件撰写（收件人/主题/正文/附件）。

> **类**：`zxf\XfAdmin\Components\Data\EmailCompose`
> **文件**：`src/Components/Data/EmailCompose.php`（68 行）
> **依赖插件**：`quill`

**用法示例**

```php
XfAdmin::emailCompose([
    'to'       => '', 'subject' => '', 'body' => '',
    'action'   => '/mail/send',
    'editor'   => 'quill',   // quill | textarea
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::emailCompose([
    'to' => '',
    'subject' => '',
    'body' => '',
    'action' => '#',
    'editor' => 'quill',
]);
```

</details>

> **渲染骨架**：主要 class `card-body` `mb-3` `row` `col-sm-10` `xf-quill` `card-footer` `text-end` `d-flex`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `to` | string | `''` | 结束值 / 目标地址；**文本槽位**：输出前自动 HTML 转义 |
| `subject` | string | `''` | **文本槽位**：输出前自动 HTML 转义 |
| `body` | string | `''` | 主体内容（可为 HTML 字符串、组件实例或数组，原样输出）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；**文本槽位**：输出前自动 HTML 转义 |
| `action` | string | `'#'` | 表单提交地址 / 动作类型；源码用法：`$html = '<form' . $this->attrs(['class' => 'xf-mail-compose card', 'id' => $id, 'meth…` |
| `editor` | string | `'quill'` | 源码用法：`return $this->get('editor') === 'quill' ? ['quill'] : [];` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `mailList`

邮件列表（收件箱条目）。

> **类**：`zxf\XfAdmin\Components\Data\MailList`
> **文件**：`src/Components/Data/MailList.php`（52 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::mailList([
    'title' => '收件箱',
    'action' => ['text'=>'查看全部','url'=>'#'],
    'items' => [
        ['from'=>'张三','avatar'=>'users/user-1.jpg','subject'=>'周末计划','preview'=>'…','time'=>'10:30',
         'unread'=>true,'starred'=>false,'attachments'=>1],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::mailList([
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'action' => [],    // 表单提交地址 / 动作类型
    'items' => [],    // 条目数组（结构见各组件说明）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `action[]` 元素键：`url`（默认 `#`）、`text`（默认 `查看全部`）
- `items[]` 元素键：`unread`、`starred`、`avatar`、`badge`、`badgeVariant`（默认 `primary`）、`attachments`、`from`、`subject`、`preview`、`time`

> **渲染骨架**：主要 class `card` `card-header` `bg-light` `bg-opacity-25` `card-body` `p-0` `table-responsive` `table`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `action` | array | `[]` | 表单提交地址 / 动作类型 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `outlook`

Outlook 风格邮件客户端。

> **类**：`zxf\XfAdmin\Components\Data\Outlook`
> **文件**：`src/Components/Data/Outlook.php`（88 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::outlook([
    'folders' => [['icon'=>'ti ti-inbox','name'=>'收件箱','count'=>12,'active'=>true], ...],
    'messages' => [
        ['id'=>1,'from'=>'张三','avatar'=>'users/user-1.jpg','from_email'=>'z@x.com',
         'subject'=>'周末计划','preview'=>'…','time'=>'10:30','unread'=>true,'starred'=>false,'attachments'=>1],
    ],
    'selected' => ['from'=>'李四','from_email'=>'l@x.com','subject'=>'Re:…','time'=>'昨天','body'=>'…','attachments'=>[['name'=>'a.pdf','size'=>'1MB']]],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::outlook([
    'folders' => [],    // 文件夹列表（文件管理器）
    'messages' => [],    // 消息列表（聊天/通知/消息中心条目）
    'selected' => [],    // 是否选中 / 选中值
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `folders[]` 元素键：`active`、`count`、`icon`（默认 `ti ti-folder`）、`name`
- `messages[]` 元素键：`unread`、`starred`、`avatar`、`attachments`、`from`、`time`、`subject`、`preview`
- `selected[]` 元素键：`avatar`、`from`、`subject`、`time`、`from_email`、`body`、`attachments`

> **渲染骨架**：主要 class `card` `row` `g-0` `outlook-app` `col-md-3` `col-lg-2` `border-end` `p-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `folders` | array | `[]` | 文件夹列表（文件管理器） |
| `messages` | array | `[]` | 消息列表（聊天/通知/消息中心条目） |
| `selected` | array | `[]` | 是否选中 / 选中值 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。


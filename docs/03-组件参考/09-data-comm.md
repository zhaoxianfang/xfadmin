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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `conversations` | array | `[]` |  |
| `peer` | array | `[]` |  |
| `messages` | array | `[]` |  |
| `search` | string | `'搜索联系人…'` | 是否启用搜索 |
| `placeholder` | string | `'输入消息…'` | 占位提示文案 |
| `height` | string | `'60vh'` | 高度（CSS 长度，受安全白名单约束） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `status` | mixed | `null` | 状态值 / 状态映射 |
| `avatar` | mixed | `null` | 头像地址（自动解析为包内图片 URL） |
| `height` | string | `'460px'` | 高度（CSS 长度，受安全白名单约束） |
| `messages` | array | `[]` |  |
| `input` | bool | `true` |  |
| `header` | bool | `true` | 头部内容（原样输出） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `contacts` | array | `[]` |  |
| `messages` | array | `[]` |  |
| `title` | string | `'会话'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `me` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `side` | string | `'in'` |  |
| `avatar` | mixed | `null` | 头像地址（自动解析为包内图片 URL） |
| `text` | string | `''` | 正文/按钮文案（纯文本语义，输出时转义） |
| `time` | string | `''` | 时间文本 |
| `read` | bool | `false` |  |
| `attach` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `folders` | array | `[]` |  |
| `messages` | array | `[]` |  |
| `selected` | array | `[]` |  |
| `view` | string | `'split'` |  |
| `composeText` | string | `'写邮件'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `to` | string | `''` |  |
| `subject` | string | `''` |  |
| `body` | string | `''` | 主体内容（可为 HTML 字符串、组件实例或数组，原样输出） |
| `action` | string | `'#'` | 表单提交地址 / 动作类型 |
| `editor` | string | `'quill'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `action` | array | `[]` | 表单提交地址 / 动作类型 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `folders` | array | `[]` |  |
| `messages` | array | `[]` |  |
| `selected` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


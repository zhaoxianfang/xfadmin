# 认证与错误页

`AuthPage` 是认证类页面的统一入口，覆盖 9 种语义 × 3 种布局。

> 源码：`src/Components/Layout/AuthPage.php`（1569 行）

## 1. 类型与布局

| 常量 | 值 |
|---|---|
| `TYPES` | `sign-in`、`sign-up`、`reset-pass`、`new-pass`、`lock-screen`、`login-pin`、`two-factor`、`delete-account`、`success-mail` |
| `LAYOUTS` | `base`（默认）、`card`、`split`（`basic` 是 `base` 的历史别名） |

语义别名（自动注入 `type`）：

| 别名 | type |
|---|---|
| `XfAdmin::signIn()` | `sign-in` |
| `XfAdmin::signUp()` | `sign-up` |
| `XfAdmin::resetPass()` | `reset-pass` |
| `XfAdmin::newPass()` | `new-pass` |
| `XfAdmin::twoFactor()` | `two-factor` |
| `XfAdmin::loginPin()` | `login-pin` |
| `XfAdmin::deleteAccount()` | `delete-account` |
| `XfAdmin::successMail()` | `success-mail` |
| `XfAdmin::lockScreen()` | ⚠️ 被 `LockScreen` 组件覆盖，语义注入无效 |

```php
// 等价写法
XfAdmin::signIn([...]);
XfAdmin::authPage(['type' => 'sign-in', ...]);
```

默认标题/副标题：

| type | heading | subheading |
|---|---|---|
| sign-in | 欢迎回来 | 请输入账号信息登录 |
| sign-up | 创建账号 | 填写信息以注册新账号 |
| reset-pass | 找回密码 | 输入邮箱以接收重置链接 |
| new-pass | 设置新密码 | 请输入新的登录密码 |
| lock-screen | 屏幕已锁定 | 请输入密码以继续 |
| login-pin | PIN 登录 | 请输入您的 PIN 码 |
| two-factor | 两步验证 | 请输入您的身份验证代码 |
| delete-account | 注销账户 | 此操作不可恢复，请输入密码确认 |
| success-mail | 邮件已发送 | 请查收邮件以继续 |

## 2. 参数总表

### 2.1 布局与外观

| 参数 | 默认 | 说明 |
|---|---|---|
| `layout` | `'base'` | `base` / `card` / `split` |
| `class` | `''` | 附加到根容器 class |
| `id` | `''` | 根容器 id（同时作为 `<form id>`） |
| `bodyClass` | `''` | 追加到 `<form class="auth-form text-start …">` |

### 2.2 品牌与文案

| 参数 | 默认 | 说明 |
|---|---|---|
| `brand` | `['name'=>'XfAdmin','url'=>'/','logo'=>null]` | logo 经 `img()` + `e()` |
| `title` | `''` | `<title>`；空时回退 `heading` → `'XfAdmin'` |
| `heading` | `''` | 主标题；空时按 type 取默认 |
| `subheading` | `''` | 副标题；回退历史字段 `subtitle` |
| `copyright` | `''` | 底部版权 |
| `status` | `''` | 顶部绿色 alert（空时读 `session('status')`） |
| `message` | `''` | 说明文本（new-pass 渲染为 `alert-info`） |

### 2.3 表单

| 参数 | 默认 | 说明 |
|---|---|---|
| `action` | `''` | 提交地址 |
| `method` | `'POST'` | ⚠️ 实际**恒输出 `method="POST"`**（REST 用 `_method` 隐藏域） |
| `ajax` | `true` | 为真时 `<form data-xf-remote>` |
| `fields` | `[]` | **必须关联数组**，key 即字段名 |
| `buttons` | `[]` | 非空时优先于 `submit` |
| `submit` | `'提交'` | 字符串或数组（`text`/`label`/`class`/`variant`/`icon`） |
| `content` | `''` | `raw`，**接管整张卡片主体**（其余表单自动跳过） |
| `below` | `''` | 表单下方补充内容（`raw`）；为空时才渲染默认 links |
| `beforeForm` / `afterForm` | `''` | `<form>` 前后（`raw`） |
| `prepend` / `append` | `''` | 卡片标题后 / 表单后（`raw`） |
| `links` | `[]` | 替代默认链接：`[['text'=>..,'href'=>..,'divider'=>bool]]` |

### 2.4 导航链接

| 参数 | 默认 | 说明 |
|---|---|---|
| `loginRedirect` | `'/login'` | 「已有账号？去登录」 |
| `registerRedirect` | `'/register'` | 「还没有账号？去注册」 |
| `footerLinks` | `[]` | 页脚链接 `[['url'=>..,'text'=>..]]` |
| `showBackToTop` | `false` | 回到顶部按钮 |

### 2.5 社交登录

```php
'socialButtons' => [
    ['icon' => 'ti ti-brand-google', 'url' => '/auth/google', 'label' => 'Google'],
    ['icon' => 'ti ti-brand-github', 'url' => '/auth/github', 'label' => 'GitHub'],
],
```

渲染「或使用社交账号」分隔线 + `btn-outline-secondary` 图标按钮。

### 2.6 split / card 侧栏

| 参数 | 默认 | 说明 |
|---|---|---|
| `sideImage` | `'auth.jpg'` | 包内图片名 / `http(s)` 或 `//` 外链 / `data:` URI / `/` 开头本地路径；`''`/`false`/`null` 关闭 → 回退纯色渐变 |
| `sideImageAlt` | `''` | 无障碍文本（仅 split） |
| `sideImageSize` | `'cover'` | `background-size` |
| `sideImagePosition` | `'center'` | `background-position` |
| `sideOverlay` | `true` | `true`→渐变遮罩；`false`→纯色 |
| `sideTitle` | `'企业级后台管理平台'` | |
| `sideText` | 官方长文案 | |
| `sideList` | 3 条要点 | `[['icon'=>..,'text'=>..]]` |
| `sideVariant` | `'primary'` | 生成 `auth-split-media--{v}` / `auth-card-media--{v}` |

### 2.7 用户（锁屏 / 新密码）

| 参数 | 默认 | 说明 |
|---|---|---|
| `user` | `[]` | `name` / `avatar` / `email` |
| `name` / `avatar` / `email` | — | 顶层兼容键，补齐 `user` 同名字段 |

### 2.8 验证码

| 参数 | 默认 | 说明 |
|---|---|---|
| `captcha` | `false` | `false`/`null`/`''` → 不渲染；**字符串** → `raw` 原样输出（包在 `div.mb-3.auth-field`）；**`true`** → 占位 `div.xf-captcha[data-xf=captcha]` + 隐藏 `xf_captcha_token` |

```php
// 推荐：传入验证码组件或第三方视图（字符串形态）
'captcha' => (string) XfAdmin::captcha(['mode' => 'image', 'src' => '/captcha.png']),
```

## 3. 各语义页字段构成

| type | 字段 | 特有开关 |
|---|---|---|
| `sign-in` | `fields['username']` 优先，否则 `fields['email']`（默认 name `username`）；`password`；captcha | 后台登录用账号 |
| `sign-up` | `name` / `email` / `password` / `password_confirmation`；captcha | 协议用 `append` 插槽 |
| `reset-pass` | `email`（autofocus）；captcha | |
| `new-pass` | 邮箱展示（disabled）；[可选 6 位验证码]；新密码 + 强度条；确认密码；[协议]；captcha | `newPassShowEmail`(true)、`newPassShowCode`(false)、`newPassShowAgree`(true) |
| `lock-screen` | 用户徽标 + `password`（autofocus） | 有 `avatar` 出图片，否则首字 |
| `login-pin` | `pinCodeGroup(n, 'pin[]')`；captcha | 位数取 `fields['pin']['group']`，回退 `get('pinGroup', 6)` |
| `two-factor` | `pinCodeGroup(6)`（固定 6 位，`name="code[]"`） | 无 captcha |
| `delete-account` | `password`（autofocus） | |
| `success-mail` | **无表单**：成功图标 + alert + 默认 links | |

## 4. 单字段配置（`fields['x']`）

| 键 | 默认 | 说明 |
|---|---|---|
| `name` | `''` | 字段名（同时作 id 来源：`auth-{sanitized name}`） |
| `type` | `'text'` | 原生 type |
| `label` | `''` | 非空时输出 `<label class="form-label">`，`required` 加 `*` |
| `placeholder` | `''` | |
| `required` / `autofocus` / `disabled` | false | |
| `value` | — | 默认值；`old($name)` 非空时优先回填 |
| `icon` | 见 `FIELD_ICONS` | email→`ti ti-mail`；password→`ti ti-lock`；name→`ti ti-user`；pin→`ti ti-key`；code→`ti ti-shield-lock` |
| `help` | — | 无错误时输出 `div.form-text` |
| `before` / `after` | — | 该字段前/后 `raw` 插槽 |
| `id` | — | 覆盖自动 id |

输入框统一 class：`form-control py-2 px-3 bg-light bg-opacity-40 border-light[ is-invalid]`。

## 5. 三种布局的 DOM

| layout | 结构 |
|---|---|
| `base` | `div.auth-box.overflow-hidden.align-items-center.d-flex > .container > .row.justify-content-center > .col-xxl-4.col-md-6.col-sm-8`：brand（居中）→ Welcome 区 → 表单 → 版权 |
| `card` | `.auth-box > .container-xxl > .row > .col-xl-10 > .card.rounded-4.overflow-hidden > .row.g-0`：左 `col-lg-6.card-body`（brand + 表单）+ 右 `col-lg-6.d-none.d-lg-block.card-side-img.auth-card-media` |
| `split` | `.auth-box.p-0.w-100 > .row.w-100.g-0`：左 `div.col`（整幅背景图 + overlay 文案）；右 `div.col-md-auto > .card.auth-box-form`（brand + 表单 + 版权） |

外层 `<body class="auth-page auth-{type} auth-layout-{layout} {class}">`。

## 6. 完整示例

```php
echo XfAdmin::signIn([
    'layout'     => 'split',
    'action'     => '/admin/login',
    'ajax'       => true,
    'heading'    => '欢迎回来',
    'subheading' => '请输入账号信息登录',
    'captcha'    => (string) XfAdmin::captcha(['mode' => 'image', 'src' => '/captcha.png']),
    'fields'     => [
        'username' => ['label' => '账号', 'placeholder' => '请输入账号', 'required' => true, 'autofocus' => true],
        'password' => ['label' => '密码', 'type' => 'password', 'required' => true],
    ],
    'submit'     => ['text' => '登录', 'icon' => 'ti ti-login', 'variant' => 'primary'],
    'socialButtons' => [
        ['icon' => 'ti ti-brand-google', 'url' => '/auth/google', 'label' => 'Google'],
    ],
    'registerRedirect' => '/admin/register',
    'footerLinks'      => [['url' => '/help', 'text' => '帮助中心']],
    'sideTitle' => '企业级后台管理平台',
    'sideList'  => [
        ['icon' => 'ti ti-bolt', 'text' => '226 个组件，开箱即用'],
        ['icon' => 'ti ti-shield', 'text' => '安全优先，全程转义'],
    ],
]);
```

## 7. 错误页 `errorPage`

继承 `AuthPage`，内容写入 `content` 后走 `parent::html()`。

| 参数 | 默认 | 说明 |
|---|---|---|
| `code` | `404` | 状态码（也用于插画文件名） |
| `image` | `null` | 默认 `images/svg/{code}.svg`；文件不存在则降级为大号状态码（避免破图） |
| `heading` | `'Page Not Found'` | |
| `message` | `''` | |
| `home_url` | `'/'` | `safeUrl()` |
| `home_text` | `'返回首页'` | |

```php
echo XfAdmin::errorPage(['code' => 500, 'heading' => '服务器错误', 'message' => '请稍后重试']);
```

未传 `title` 时自动设为 `"{code} - {heading}"`。
支持代码：`400` `401` `403` `404` `408` `500`（以及 maintenance）。

## 8. 其它状态页

### 8.1 即将上线 `comingSoon`

```php
echo XfAdmin::comingSoon([
    'heading'   => '即将上线',
    'message'   => '我们正在努力打造精彩内容，敬请期待。',
    'deadline'  => '2026-12-31 23:59:59',     // 渲染倒计时
    'image'     => 'coming-soon.png',
    'subscribe' => true,                       // 邮箱订阅表单
]);
```

### 8.2 维护中 `maintenance`

```php
echo XfAdmin::maintenance([
    'heading' => '网站维护中',
    'message' => '我们正在进行例行维护，请稍后再访问。',
    'contact' => 'admin@example.com',          // 渲染 mailto 链接
]);
```

### 8.3 空状态 `emptyState`（Layout 版，片段）

```php
echo XfAdmin::emptyState([
    'icon'   => 'ti ti-inbox',
    'title'  => '暂无数据',
    'text'   => '换个筛选条件试试',         // 纯文本（转义）
    'action' => XfAdmin::button(['label' => '刷新']),   // raw
]);
```

> 另有一个 UI 版 `XfAdmin::empty()`（`Components\UI\EmptyState`），为片段组件。

### 8.4 锁屏 `lockScreen`（独立整页组件）

```php
echo XfAdmin::lockScreen([
    'user'      => ['name' => '张三', 'avatar' => 'users/user-2.jpg'],
    'action'    => '/admin/unlock',
    'heading'   => '屏幕已锁定',
    'below'     => '<a href="/logout">切换账号</a>',
    'copyright' => '© 2026 我的公司',
]);
```

⚠️ 与 `XfAdmin::authPage(['type' => 'lock-screen'])` 是**不同组件**，参数不通用。

## 9. CSRF 与错误显示

- `formOpen()` = `{beforeForm}` + `<form …>` + `csrf_field()` + 错误 alert + 状态 alert；
- 字段级错误 → `div.invalid-feedback.d-block`；非字段级 → 顶部 `div.alert.alert-danger`；
- 依赖 Laravel `ViewErrorBag`（用 `is_callable([$errors,'has'])` 探测），
  非 Laravel 环境自动降级为空；
- `<head>` 的 `csrf-token` meta **始终输出**（无 token 时为空串）。

## 10. 陷阱清单

1. `AuthPage::$fields` 必须是**关联数组**（key = 字段名）；
2. `method` 参数被读取但 `<form>` **恒输出 `method="POST"`**；REST 请用 `_method`；
3. `AuthPage::document()` **不**调用 `Assets::resetCollected()`；
4. `XfAdmin::lockScreen()` 得到的是独立 `LockScreen` 组件；
5. `captcha => true` 只输出占位（需自己实现 `captcha` widget 或传字符串）；
6. `newPassShowCode` 默认 `false`（不显示验证码分格）；
7. 密码强度条有两套实现（AuthPage 内 0–5 分 vs `passwordStrength` 组件 0–4 分）。

# 表单全指南

## 1. 表单容器 `XfAdmin::form()`

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `action` | string | `''` | 提交地址；空则前端回退 `window.location.href` |
| `method` | string | `'POST'` | `GET/POST` 直出；其余（PUT/PATCH/DELETE）→ `method="POST"` + `_method` 隐藏域 |
| `enctype` | string\|null | `null` | `'multipart/form-data'` 会被识别为含文件 |
| `validation` | bool | `false` | 加 `needs-validation` + `novalidate`（Bootstrap 校验样式） |
| `ajax` | bool | `false` | 输出 `data-xf-remote`，由前端托管提交 |
| `remote` | bool | `false` | `ajax` 的兼容别名 |
| `redirect` | string | `''` | 输出 `data-xf-redirect`，后端无 `url` 时的兜底跳转 |
| `reset` | bool | `false` | 成功后 `form.reset()` |
| `reload` | bool | `true` | 无 url/redirect 时是否刷新页面 |
| `inline` | bool | `false` | 旧写法，等价 `layout='inline'` |
| `layout` | string\|null | `null` | `vertical`（默认）\| `horizontal` \| `inline` |
| `label_width` | int | `180` | 仅 `horizontal` 生效，输出 `style="--xf-label-width:180px"` |
| `fields` | array | `[]` | 字段数组（组件实例 / HTML 字符串 / 数组） |
| `content` | mixed\|null | `null` | 追加在 fields 之后 |
| `buttons` | mixed\|null | `null` | 非空时包在 `<div class="d-flex gap-2">` |
| `csrf` | bool\|array | `[]` | 见下方 ⚠️ |

```php
echo XfAdmin::form([
    'action' => '/admin/users',
    'method' => 'POST',
    'ajax'   => true,
    'csrf'   => true,                     // ⚠️ 必须显式传，默认 [] 不注入
    'fields' => [
        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true]),
        XfAdmin::select(['name' => 'role', 'label' => '角色', 'options' => [...]]),
    ],
    'buttons' => XfAdmin::button(['label' => '保存', 'type' => 'submit']),
]);
```

⚠️ **CSRF 默认值是 `[]` 而非 `true`** —— 默认情况下**不会**输出 `_token` 隐藏域。
需要令牌时显式传 `'csrf' => true`（取 `XfAdmin::csrfToken()`）或 `['name' => 'value', …]`。

⚠️ **Form 不会自动渲染字段** —— 字段必须显式放进 `fields`。

## 2. 公共字段（FieldWrapper）

使用 `FieldWrapper` 的组件：Input、Textarea、Select、Slider、DateRangePicker、
DatePicker、Editor、Upload、ColorPicker、Tags、MaskedInput、PasswordStrength。

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `name` | string\|null | `null` | 字段名（空则不输出 `name`） |
| `id` | string\|null | `null` | 留空自动生成（`xf-input-1` 等） |
| `label` | string\|null | `null` | `null` 时**不渲染** `<label>` |
| `help` | string\|null | `null` | `.form-text`（强制转义） |
| `required` | bool | `false` | `required` + label 红色星号 |
| `disabled` | bool | `false` | |
| `readonly` | bool | `false` | Select/Upload 未消费 |
| `value` | mixed | `null` | 当前值 |
| `placeholder` | string\|null | `null` | |
| `wrapper` | string\|false\|null | `'mb-3'` | 外层 div class；`false`/`null` 不包裹 |
| `feedback` | array\|null | `null` | `['valid' => '…', 'invalid' => '…']` |

渲染顺序：`label` → 控件 → `valid-feedback` → `invalid-feedback` → `help` → 包裹 `wrapper`。

⚠️ **没有 `error` 键**（错误走 `feedback['invalid']`）；
⚠️ **没有 `class` 键**（用 `->addClass()` / `->attr('class', …)`）；
⚠️ 子类用 `fieldDefaults() + [...]`（数组 `+`），**同名键以 `fieldDefaults` 为准**；
⚠️ 栅格包裹不在 FieldWrapper 内，需外层 `XfAdmin::row/col` 或手写。

## 3. 各字段组件

### 3.1 input

```php
XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true]);
XfAdmin::input(['name' => 'phone', 'label' => '电话', 'mask' => '999-9999-9999']);
XfAdmin::input(['name' => 'tags', 'label' => '标签', 'tags' => true, 'value' => 'php,laravel']);
XfAdmin::input(['name' => 'price', 'prepend' => '￥', 'append' => '.00']);
```

| 参数 | 默认 | 说明 |
|---|---|---|
| `type` | `'text'` | 任意原生 type |
| `size` | `null` | `sm`/`lg`（白名单，非法回退 `lg`） |
| `mask` | `null` | inputmask 表达式 → `data-xf="inputmask"` |
| `tags` | `false` | Tagify 标签输入 → `data-xf="tagify"` |
| `prepend` / `append` | `null` | 输入组前后缀（**原样输出**） |
| `min` `max` `step` `pattern` `autocomplete` | `null` | 原生属性 |

⚠️ `mask` 优先于 `tags`（二者互斥）。

### 3.2 textarea

```php
XfAdmin::textarea(['name' => 'remark', 'label' => '备注', 'rows' => 5, 'maxlength' => 500]);
```

### 3.3 select

```php
XfAdmin::select([
    'name' => 'role', 'label' => '角色',
    'options' => ['admin' => '管理员', 'user' => '普通用户'],   // 键值式
    'value' => 'admin',
    'enhance' => 'choices',                                    // null | choices | select2
]);

// 数组式 options
'options' => [
    ['value' => 'bj', 'label' => '北京'],
    ['value' => 'sh', 'label' => '上海', 'disabled' => true],
],

// 分组（groups 非空时忽略 options）
'groups' => ['直辖市' => ['bj' => '北京'], '华东' => ['sh' => '上海']],
```

| 参数 | 默认 | 说明 |
|---|---|---|
| `options` / `groups` | `[]` | 选项 / 分组 |
| `multiple` | `false` | 多选时 `name` 自动加 `[]` |
| `size` | `null` | `sm`/`lg` |
| `enhance` | `null` | `choices` / `select2` |
| `enhance_options` | `[]` | 透传插件配置 |

⚠️ 选中判定为**严格字符串比较**（`0` 与 `''` 不等价）；
⚠️ `placeholder` **仅在单选**时生效。

### 3.4 check（复选 / 单选 / 开关）

```php
// 单控件
XfAdmin::check(['name' => 'agree', 'type' => 'switch', 'label' => '我已阅读协议', 'checked' => true]);

// 组模式
XfAdmin::check([
    'name' => 'hobby', 'label' => '爱好', 'inline' => true,
    'options' => ['code' => '编程', 'music' => '音乐'], 'value' => ['code'],
]);
```

| 参数 | 默认 | 说明 |
|---|---|---|
| `type` | `'checkbox'` | `checkbox` / `radio` / `switch` |
| `options` | `[]` | 空 → 单控件模式 |
| `checked` | `false` | 仅单控件模式 |
| `inline` / `reverse` | `false` | 行内 / label 在左 |
| `wrapper` | `'mb-3'` | ⚠️ 用**真值判定**（空串也不包裹） |

### 3.5 slider（noUiSlider）

```php
XfAdmin::slider(['name' => 'score', 'label' => '评分', 'min' => 0, 'max' => 100,
                 'value' => [20, 80], 'tooltips' => true]);
```

- 生成 `data-xf-config`：`{start, range, step, tooltips, connect, input}`；
- 只要有 `name` 就输出隐藏 input，多值以英文逗号连接；
- 前端 `update` 事件把 `values` 写回隐藏 input。

### 3.6 dateRange / datePicker

```php
XfAdmin::dateRange(['name' => 'range', 'label' => '日期范围', 'ranges' => true, 'timepicker' => true]);
XfAdmin::datePicker(['name' => 'date', 'label' => '日期', 'format' => 'YYYY-MM-DD', 'min' => '2026-01-01']);
```

- `ranges => true` 输出 `xfRanges: true`，前端展开：
  今天 / 昨天 / 最近 7 天 / 最近 30 天 / 本月 / 上月；
- 前端内置中文本地化（周一为首日、确定/取消/自定义范围等）。

### 3.7 editor（富文本）

```php
XfAdmin::editor(['name' => 'content', 'label' => '正文', 'driver' => 'quill', 'height' => 320]);
```

| | quill（默认） | summernote |
|---|---|---|
| DOM | `<div>` + 初始 HTML **原样输出** | `<textarea>` + 内容 `e()` 转义 |
| 提交 | 额外隐藏 input（`text-change` 同步 `.ql-editor`） | textarea 自带 name |
| 依赖 | 无 jQuery | jQuery |

⚠️ quill 的初始内容是 `raw()` 输出 —— **必须保证可信**。

### 3.8 upload

```php
XfAdmin::upload(['name' => 'file', 'label' => '附件', 'driver' => 'dropzone',
                 'url' => '/upload', 'max_size' => 10, 'accept' => '.jpg,.png']);
```

| driver | DOM | 说明 |
|---|---|---|
| `native`（默认） | `<input type="file" class="form-control">` | 无 url / 无大小校验 / 无 JS |
| `dropzone` | `div.dropzone[data-xf=dropzone]` + `.dz-message` | `paramName` 默认取 `name ?? 'file'` |
| `filepond` | `input[data-xf=filepond]` | 自动注册 4 个官方插件 |

⚠️ `max_size` 单位是 **MB**。

### 3.9 其它

| 组件 | 要点 |
|---|---|
| `colorPicker` | Pickr；`theme`：`classic`/`monolith`/`nano`；**点击 save 才写回隐藏 input** |
| `tags` | Tagify；`whitelist`、`max`（→ maxTags）；⚠️ 未继承 FieldWrapper（无 required/disabled/wrapper） |
| `maskedInput` | `mask` 表达式；⚠️ `alias` 前端**未消费** |
| `wizard` | 多步表单，见 §5 |
| `passwordStrength` | 0–4 分评分，见 §6 |
| `captcha` | 三种模式，见 §7 |
| `twoFactorInput` | 4–8 格 OTP，自动跳格/退格/粘贴，提交为单个隐藏字段 |
| `quantityStepper` | 数量步进器，`min`/`max`/`step`/`size` |

## 4. AJAX 提交（`data-xf-remote`）

```php
XfAdmin::form(['action' => '/admin/users', 'ajax' => true, 'csrf' => true, 'fields' => [...]]);
```

前端流程（`bindRemoteForms` → `handleRemoteForm` → `handleFormResponse`）：

1. 拦截 `submit`，清理旧的 `.is-invalid` / `.invalid-feedback.xf-field-error`；
2. `_method` 隐藏域优先决定 method；提交按钮置灰为「保存中…spinner」；
3. `XFAdmin.request(action, {method, data: new FormData(form)})`；
4. 成功优先级：
   `data.url` → **3s** 后跳转 → `data-xf-redirect` **1.2s** 后跳转
   → `data-xf-reset="1"` 重置 → 否则（`reload` 为真）**1.2s** 后 `location.reload()`；
   全程 toast 成功提示；
5. 失败：422 按 `[name="…"]` 定位字段回填错误；419「页面已过期（CSRF）」；500「服务器异常」。

### 后端响应约定

```php
// 成功
return response()->json([
    'ok'      => true,                 // 必须位于顶层
    'message' => '保存成功',
    'url'     => '/admin/users',       // 可选，存在则跳转
]);

// 校验失败（Laravel 自动返回 422）
return response()->json(['message' => '校验失败', 'errors' => [
    'email' => ['邮箱已被占用'],
]], 422);
```

⚠️ `ok` / `url` / `message` 必须在**顶层**（前端读 `res.data.url`，而 `res.data` 是整个响应体）。

## 5. 向导 `wizard`

```php
echo XfAdmin::wizard([
    'action' => '/admin/onboarding', 'method' => 'post', 'remote' => true,
    'variant' => 'primary', 'vertical' => false, 'progress' => true,
    'labels'  => ['prev' => '上一步', 'next' => '下一步', 'finish' => '提交'],
    'steps'   => [
        ['title' => '基本信息', 'icon' => 'ti ti-user', 'content' => $step1],
        ['title' => '联系方式', 'icon' => 'ti ti-mail', 'content' => $step2],
        ['title' => '确认',     'icon' => 'ti ti-check', 'content' => $step3],
    ],
]);
```

前端行为：

- 「下一步」先校验当前面板内控件的 `checkValidity()`，不通过则 `reportValidity()` 并阻止；
- 末步按钮文案切换为 `finish`，点击派发 `xf.wizard.finish` 后 `form.requestSubmit()`；
- 导航项只允许跳到 `idx <= current`；
- 每次切换派发 `xf.wizard.change`（`detail:{step}`）；
- 实例暴露 `{goTo, next, prev}`。

## 6. 密码强度 `passwordStrength`

```php
echo XfAdmin::passwordStrength(['name' => 'password', 'minScore' => 3, 'showRules' => true]);
```

评分规则（0–4）：长度≥8、含小写、含大写、含数字、含特殊符号 五项计数 `cnt`，
基础分 `cnt-1`；长度≥12 或（两位数字/两个特殊符号）+1；上限 4，空值 0。
文案：`非常弱/弱/中等/强/非常强`；进度 0/20/40/70/100%。

输入时派发 `xf.pw.score`（`detail:{score, ok}`）。

⚠️ 与 AuthPage 用的 `initPasswordStrength()`（`.password-input` + `.password-bar`，0–5 分）
是**两套实现**，分数口径不同。

## 7. 验证码 `captcha`

```php
echo XfAdmin::captcha(['mode' => 'image', 'src' => '/captcha.png', 'refreshable' => true]);
echo XfAdmin::captcha(['mode' => 'math', 'question' => '7 + 8 = ?']);
echo XfAdmin::captcha(['mode' => 'slide']);
```

| mode | 说明 |
|---|---|
| `image` | 图片 + 刷新按钮（`data-xf-captcha-refresh`）；点击刷新加 `?t=时间戳`（**要求服务端支持**） |
| `math` | 随机算式展示；**答案在前端随机，需后端自行校验**（建议存 session） |
| `slide` | 滑块占位；**无真实轨迹校验**，需接入第三方或自定义 |

⚠️ 默认 `src` 为内置随机 4 位数字 data-URI SVG（仅占位），**生产必须覆盖**；
⚠️ `label` 传 `null` 会渲染**空 `<label>`**（含星号），要隐藏请传 `''`。

## 8. 表单布局

```php
// 水平表单（CSS Grid 两栏）
XfAdmin::form(['layout' => 'horizontal', 'label_width' => 160, 'fields' => [...]]);

// 行内
XfAdmin::form(['layout' => 'inline', 'fields' => [...]]);

// 栅格排列（推荐与 row/col 配合）
XfAdmin::row([
    'cols' => [
        ['width' => 6, 'content' => XfAdmin::input(['name' => 'first_name', 'label' => '名'])],
        ['width' => 6, 'content' => XfAdmin::input(['name' => 'last_name', 'label' => '姓'])],
    ],
]);
```

## 9. 校验

### 9.1 客户端（Bootstrap 原生）

```php
XfAdmin::form(['validation' => true, 'fields' => [
    XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱',
                    'required' => true, 'feedback' => ['invalid' => '请输入有效邮箱']]),
]]);
```

容器加 `needs-validation` + `novalidate`，由 Bootstrap/app.js 处理样式。

### 9.2 服务端 + AJAX 回填

Laravel 校验失败自动返回 422 + `errors`，前端按字段名回填。

## 10. 陷阱清单

1. `Form` 的 `csrf` 默认 `[]` → **默认不注入 `_token`**；
2. FieldWrapper **没有 `error` 与 `class` 键**；
3. 子类 `defaults()` 用 `+` 合并 → 同名键被公共默认值覆盖；
4. `array_replace_recursive` 递归合并 → 嵌套数组是合并不是替换；
5. `Select` 的 `groups` 非空会忽略 `options`；`placeholder` 仅单选生效；
6. `Input` 的 `mask` 优先于 `tags`；
7. `MaskedInput` 的 `alias` 前端未消费；
8. Editor quill 模式 value 为 `raw()`，summernote 为 `e()`；
9. `Tags`/`MaskedInput`/`Check`/`Captcha`/`Wizard`/`TwoFactorInput`/`QuantityStepper`/`PasswordStrength`
   未（完全）继承 FieldWrapper，参数表与公共字段不一致；
10. `Check` 的 `wrapper` 用真值判定（空串也不包裹）；
11. 演示型组件（`formOtherPlugin` / `formValidation`）引用的
    `fmMask` / `fmMaxlength` / `formValidation` 三个 widget **在 `xfadmin.js` 中无实现**；
12. `XFAdmin.request` 只接受 `opts.data`（对象→JSON / FormData→multipart），不接受 `opts.body`。

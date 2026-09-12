# 表单组件

> 表单容器与全部输入控件（输入/选择/复选/滑块/日期/编辑器/上传/颜色/标签/掩码/验证码/向导等）。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`form`](#form) — 表单容器（统一字段包装与提交处理）
- [`input`](#input) — 文本输入框（含前后缀/帮助/校验态）
- [`textarea`](#textarea) — 多行文本域
- [`select`](#select) — 下拉选择框（原生/select2/tom-select）
- [`check`](#check) — 多选/单选框（check/radio 统一封装）
- [`slider`](#slider) — 滑块/范围选择器（ion-rangeSlider/noUiSlider）
- [`dateRange`](#daterange) — 日期范围选择器（daterangepicker）
- `dateRangePicker` — 等价于 `dateRange`（同一组件类的别名）
- [`datePicker`](#datepicker) — 单日期/日期时间选择器（singleDatePicker 模式）
- [`editor`](#editor) — 富文本编辑器（TinyMCE/Quill 等）
- [`upload`](#upload) — 文件上传（native/dropzone/filepond 驱动）
- [`colorPicker`](#colorpicker) — 颜色选择器（pickr）
- [`tags`](#tags) — 标签输入（Tagify 多标签）
- [`maskedInput`](#maskedinput) — 输入掩码（电话/日期格式约束）
- [`wizard`](#wizard) — 向导（多步表单分步导航）
- [`passwordStrength`](#passwordstrength) — 密码强度计（实时弱/中/强提示）
- [`captcha`](#captcha) — 验证码（image/math/slide 三种模式）
- [`twoFactorInput`](#twofactorinput) — 两步验证 / OTP 验证码输入框 6 格独立输入，自动跳格、退格回退、粘贴自动填充， 复刻 inspinia auth-two-factor.html 的验证码交互
- [`quantityStepper`](#quantitystepper) — 数量步进器 − 数字 + 按钮组，含 min / max / step，复刻电商购物车数量控件
- [`formElements`](#formelements) — 表单元素集合（所有输入控件演示）
- [`formLayout`](#formlayout) — 表单布局（水平/垂直/网格排布）
- [`formOtherPlugin`](#formotherplugin) — 其它表单插件（掩码/校验等组合）
- [`formValidation`](#formvalidation) — 表单校验（实时反馈示例）

## 本章导读

表单组件分为**容器**（`form`）与**字段**（`input`/`select`/`check`/…）两类。
大多数字段共享 `FieldWrapper` 提供的公共字段（见下方"公共字段"）。

### 公共字段（FieldWrapper）

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `name` | string\|null | `null` | 字段名（空则不输出 `name`） |
| `id` | string\|null | `null` | 留空自动生成 |
| `label` | string\|null | `null` | `null` 时不渲染 `<label>` |
| `help` | string\|null | `null` | `.form-text`（强制转义） |
| `required` | bool | `false` | 加 `required` + 红色星号 |
| `disabled` | bool | `false` | |
| `readonly` | bool | `false` | Select/Upload 未消费 |
| `value` | mixed | `null` | 当前值 |
| `placeholder` | string\|null | `null` | |
| `wrapper` | string\|false\|null | `'mb-3'` | 外层 div class；`false`/`null` 不包裹 |
| `feedback` | array\|null | `null` | `['valid'=>…, 'invalid'=>…]` |

⚠️ **没有 `error` 键**（错误走 `feedback['invalid']`）；**没有 `class` 键**（用 `->addClass()`）。

### 组合范式

```php
echo XfAdmin::form([
    'action' => '/admin/users', 'method' => 'POST',
    'ajax'   => true,        // data-xf-remote，前端托管提交
    'csrf'   => true,        // ⚠️ 必须显式传，默认 [] 不注入
    'fields' => [
        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true]),
        XfAdmin::select(['name' => 'role', 'label' => '角色', 'enhance' => 'choices', 'options' => [...]]),
    ],
    'buttons' => XfAdmin::button(['label' => '保存', 'type' => 'submit']),
]);
```

### 约定与陷阱

- `Form` **不会自动渲染字段**，字段必须放进 `fields`；
- `Form.csrf` 默认 `[]` → 默认不注入 `_token`；
- `<form>` 恒输出 `method="POST"`，REST 请用 `_method` 隐藏域；
- 子类 `defaults()` 用 `+` 合并 → 与 `fieldDefaults()` 同名键会被公共默认值覆盖；
- `Select` 的 `groups` 非空会忽略 `options`；`placeholder` 仅单选生效；
- `Input` 的 `mask` 优先于 `tags`；`MaskedInput` 的 `alias` 前端未消费；
- `Tags`/`MaskedInput`/`Check`/`Captcha` 等未完全继承 FieldWrapper，参数表以各组件为准。


---

### `form`

表单容器（统一字段包装与提交处理）。

> **类**：`zxf\XfAdmin\Components\Form\Form`
> **文件**：`src/Components/Form/Form.php`（110 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::form([
    'action'  => '/users',
    'method'  => 'POST',
    'validation' => true,      // Bootstrap 客户端校验（needs-validation）
    'ajax'    => true,         // xfadmin.js 接管提交，触发 xf.form.success/error 事件
    'layout'  => 'vertical',   // 布局：vertical 纵向（默认）| horizontal 标签左置 | inline 行内
    'label_width' => 180,      // horizontal 布局的标签列宽（px）
    'fields'  => [ Input、Select、Check ... 组件或 HTML 的数组 ],
    'buttons' => '<button class="btn btn-primary" type="submit">提交</button>',
    'csrf'    => true,   // true（默认，自动注入 _token）/ false（不注入）/ [name=>value]（自定义隐藏域）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::form([
    'action' => '',
    'method' => 'POST',
    'enctype' => null,
    'validation' => false,
    'ajax' => false,    // true：标记 data-xf-remote，由 JS 拦截为 AJAX 提交（含文件 FormData / CSRF / 统一响应）
    'remote' => false,    // 兼容别名（= ajax），保留旧调用方式
    'redirect' => '',    // AJAX 成功且后端未返回 url 时前端兜底跳转地址（整页）
    'reset' => false,    // AJAX 成功后是否重置表单（默认 false；与 reload 二选一）
    'reload' => true,    // AJAX 成功且无 url/redirect 时是否刷新当前页（false=仅 toast）
    'inline' => false,    // 兼容旧写法（等价 layout=inline）
    'layout' => null,    // vertical | horizontal | inline（form-layouts.html）
    'label_width' => 180,    // horizontal 布局标签列宽（px）
    'fields' => [],
    'content' => null,
    'buttons' => null,
    'csrf' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `fields[]`：组件实例 / HTML 字符串 / 数组的混合列表（逐个原样输出）

> **渲染骨架**：主要 class `d-flex` `gap-2`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `action` | string | `''` | 表单提交地址 / 动作类型；源码用法：`'action' => $this->get('action'),` |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `enctype` | mixed | `null` | 源码用法：`'enctype' => $this->get('enctype'),` |
| `validation` | bool | `false` | 源码用法：`'needs-validation' => $this->get('validation'),` |
| `ajax` | bool | `false` | true：标记 data-xf-remote，由 JS 拦截为 AJAX 提交（含文件 FormData / CSRF / 统一响应）；开关：非空 / 真值时启用对应区块 |
| `remote` | bool | `false` | 兼容别名（= ajax），保留旧调用方式 |
| `redirect` | string | `''` | AJAX 成功且后端未返回 url 时前端兜底跳转地址（整页） |
| `reset` | bool | `false` | AJAX 成功后是否重置表单（默认 false；与 reload 二选一）；开关：非空 / 真值时启用对应区块 |
| `reload` | bool | `true` | AJAX 成功且无 url/redirect 时是否刷新当前页（false=仅 toast） |
| `inline` | bool | `false` | 兼容旧写法（等价 layout=inline） |
| `layout` | mixed | `null` | vertical \| horizontal \| inline（form-layouts.html） |
| `label_width` | int | `180` | horizontal 布局标签列宽（px） |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `content` | mixed | `null` | 内容区（可为 HTML 字符串、组件实例或数组）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `buttons` | mixed | `null` | 按钮定义数组；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `csrf` | array | `[]` | 是否注入 CSRF 隐藏域（`true` 注入；`[]`/`false` 不注入；数组为自定义隐藏域） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `input`

文本输入框（含前后缀/帮助/校验态）。

> **类**：`zxf\XfAdmin\Components\Form\Input`
> **文件**：`src/Components/Form/Input.php`（92 行）
> **依赖插件**：`inputmask`、`tagify`

**用法示例**

```php
XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true]);
XfAdmin::input(['name' => 'phone', 'label' => '电话', 'mask' => '999-9999-9999']);
XfAdmin::input(['name' => 'tags', 'label' => '标签', 'tags' => true, 'value' => 'php,laravel']);
XfAdmin::input(['name' => 'price', 'prepend' => '￥', 'append' => '.00']);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::input([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'type' => 'text',
    'size' => null,    // sm | lg
    'mask' => null,    // inputmask 表达式
    'tags' => false,    // tagify 标签输入
    'prepend' => null,
    'append' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'pattern' => null,
    'autocomplete' => null,
]);
```

</details>

> **渲染骨架**：主要 class `input-group` `input-group-text` `form-control`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读；源码用法：`'readonly' => (bool) $this->get('readonly'),` |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；输出到 `` 属性 |
| `placeholder` | mixed | `null` | 占位提示文案；源码用法：`'placeholder' => $this->get('placeholder'),` |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `type` | string | `'text'` | 类型（各组件语义不同，详见该组件说明）；输出到 `` 属性 |
| `size` | mixed | `null` | 可选值：`lg` |
| `mask` | mixed | `null` | inputmask 表达式；开关：非空 / 真值时启用对应区块 |
| `tags` | bool | `false` | tagify 标签输入；开关：非空 / 真值时启用对应区块 |
| `prepend` | mixed | `null` | 前缀内容（原样输出，如输入组文本/图标）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `append` | mixed | `null` | 后缀内容（原样输出，常用于协议说明）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；为 `null` 时不渲染该区块 |
| `min` | mixed | `null` | 最小值；源码用法：`'min' => $this->get('min'),` |
| `max` | mixed | `null` | 最大值；源码用法：`'max' => $this->get('max'),` |
| `step` | mixed | `null` | 步长；源码用法：`'step' => $this->get('step'),` |
| `pattern` | mixed | `null` | 源码用法：`'pattern' => $this->get('pattern'),` |
| `autocomplete` | mixed | `null` | 源码用法：`'autocomplete' => $this->get('autocomplete'),` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `textarea`

多行文本域。

> **类**：`zxf\XfAdmin\Components\Form\Textarea`
> **文件**：`src/Components/Form/Textarea.php`（41 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::textarea(['name' => 'remark', 'label' => '备注', 'rows' => 4]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::textarea([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'rows' => 3,
    'maxlength' => null,
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-textarea');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读；源码用法：`'readonly' => (bool) $this->get('readonly'),` |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；**文本槽位**：输出前自动 HTML 转义 |
| `placeholder` | mixed | `null` | 占位提示文案；源码用法：`'placeholder' => $this->get('placeholder'),` |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `rows` | int | `3` | 行数据数组；源码用法：`'rows' => $this->get('rows'),` |
| `maxlength` | mixed | `null` | 源码用法：`'maxlength' => $this->get('maxlength'),` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `select`

下拉选择框（原生/select2/tom-select）。

> **类**：`zxf\XfAdmin\Components\Form\Select`
> **文件**：`src/Components/Form/Select.php`（113 行）
> **依赖插件**：`choices`、`select2`

**用法示例**

```php
XfAdmin::select([
    'name'    => 'city',
    'label'   => '城市',
    'options' => ['bj' => '北京', 'sh' => '上海'],                 // 或 [['value'=>,'label'=>,'disabled'=>]]
    'groups'  => ['直辖市' => ['bj' => '北京'], ...],              // 分组
    'value'   => 'bj',            // 多选传数组
    'multiple'=> false,
    'enhance' => 'choices',       // null | choices | select2
    'enhance_options' => [],      // 透传给增强插件
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::select([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'options' => [],
    'groups' => [],
    'multiple' => false,
    'size' => null,
    'enhance' => null,
    'enhance_options' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：`值 => 文案` 或 `[['value'=>..,'label'=>..,'disabled'=>..]]`
- `groups[]`：`组名 => [值 => 文案]`；非空时忽略 `options`
- `enhance_options[]` 元素键：`placeholder`；补充：透传 choices / select2 原生配置

> **渲染骨架**：主要 class `form-select`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-select');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；源码用法：`$selected = (array) ($this->get('value') ?? []);` |
| `placeholder` | mixed | `null` | 占位提示文案；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `groups` | array | `[]` | 分组数据（下拉分组 / 权限分组 / 设置分组） |
| `multiple` | bool | `false` | 是否多选；源码用法：`'name' => $this->get('name') . ($this->get('multiple') && $this->get('name') && ! str…` |
| `size` | mixed | `null` | 可选值：`lg` |
| `enhance` | mixed | `null` | 增强插件：choices \| select2；开关：非空 / 真值时启用对应区块 |
| `enhance_options` | array | `[]` | 透传给增强插件（choices/select2）的原生配置 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `check`

多选/单选框（check/radio 统一封装）。

> **类**：`zxf\XfAdmin\Components\Form\Check`
> **文件**：`src/Components/Form/Check.php`（87 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::check(['type' => 'switch', 'name' => 'enabled', 'label' => '启用', 'checked' => true]);
XfAdmin::check([
    'type'    => 'radio',
    'name'    => 'gender',
    'inline'  => true,
    'value'   => 'f',
    'options' => ['m' => '男', 'f' => '女'],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::check([
    'type' => 'checkbox',    // checkbox | radio | switch
    'name' => null,
    'label' => null,
    'options' => [],    // 一组：value => label
    'value' => null,    // 选中值（组模式）；单个模式用 checked
    'checked' => false,
    'inline' => false,
    'reverse' => false,
    'disabled' => false,
    'required' => false,
    'wrapper' => 'mb-3',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：`值 => 文案` 键值数组；为空时进入「单控件模式」

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `type` | string | `'checkbox'` | checkbox \| radio \| switch |
| `name` | mixed | `null` | 表单字段名 / 语义名称；源码用法：`'name' => $this->get('name'),` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案）；**文本槽位**：输出前自动 HTML 转义；为 `null` 时不渲染该区块 |
| `options` | array | `[]` | 一组：value => label |
| `value` | mixed | `null` | 选中值（组模式）；单个模式用 checked |
| `checked` | bool | `false` | 源码用法：`(bool) $this->get('checked'),` |
| `inline` | bool | `false` | 是否行内排列；源码用法：`'form-check-inline' => $this->get('inline'),` |
| `reverse` | bool | `false` | 源码用法：`'form-check-reverse' => $this->get('reverse'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹）；源码用法：`$wrapper = $this->get('wrapper');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `slider`

滑块/范围选择器（ion-rangeSlider/noUiSlider）。

> **类**：`zxf\XfAdmin\Components\Form\Slider`
> **文件**：`src/Components/Form/Slider.php`（64 行）
> **依赖插件**：`nouislider`

**用法示例**

```php
XfAdmin::slider(['name' => 'price', 'label' => '价格区间', 'min' => 0, 'max' => 1000, 'value' => [100, 500], 'tooltips' => true]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::slider([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'tooltips' => false,
    'connect' => null,
    'options' => [],    // 透传 noUiSlider 配置
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传 noUiSlider 原生配置（递归合并，优先级最高）

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-slider');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；源码用法：`$value = $this->get('value') ?? $this->get('min');` |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `min` | int | `0` | 最小值；源码用法：`$value = $this->get('value') ?? $this->get('min');` |
| `max` | int | `100` | 最大值；源码用法：`'range' => ['min' => (float) $this->get('min'), 'max' => (float) $this->get('max')],` |
| `step` | int | `1` | 步长；源码用法：`'step' => (float) $this->get('step'),` |
| `tooltips` | bool | `false` | 滑块是否显示数值气泡；源码用法：`'tooltips' => $this->get('tooltips'),` |
| `connect` | mixed | `null` | noUiSlider connect 配置（数组自动为 true）；源码用法：`'connect' => $this->get('connect') ?? (is_array($value) ? true : 'lower'),` |
| `options` | array | `[]` | 透传 noUiSlider 配置 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `dateRange`

日期范围选择器（daterangepicker）。

> **类**：`zxf\XfAdmin\Components\Form\DateRangePicker` 等 `dateRange` / `dateRangePicker`
> **文件**：`src/Components/Form/DateRangePicker.php`（63 行）
> **依赖插件**：`daterangepicker`

**用法示例**

```php
XfAdmin::dateRange(['name' => 'date', 'label' => '日期', 'single' => true]);
XfAdmin::dateRange(['name' => 'range', 'label' => '时段', 'format' => 'YYYY-MM-DD', 'ranges' => true]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::dateRange([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'single' => false,
    'timepicker' => false,
    'format' => 'YYYY-MM-DD',
    'ranges' => false,    // 快捷区间（今天/最近7天/本月...）
    'options' => [],    // 透传 daterangepicker 配置
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传 daterangepicker 原生配置（递归合并，优先级最高）

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-daterange');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读；源码用法：`'readonly' => (bool) $this->get('readonly'),` |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；输出到 `` 属性 |
| `placeholder` | mixed | `null` | 占位提示文案；源码用法：`'placeholder' => $this->get('placeholder'),` |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `single` | bool | `false` | 单日期模式（singleDatePicker）；源码用法：`'singleDatePicker' => (bool) $this->get('single'),` |
| `timepicker` | bool | `false` | 是否带时间选择；源码用法：`'timePicker' => (bool) $this->get('timepicker'),` |
| `format` | string | `'YYYY-MM-DD'` | 格式化闭包或格式字符串；源码用法：`'locale' => ['format' => $this->get('format')],` |
| `ranges` | bool | `false` | 快捷区间（今天/最近7天/本月...） |
| `options` | array | `[]` | 透传 daterangepicker 配置 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

### `dateRangePicker`

`dateRangePicker` 是 `dateRange` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Form\DateRangePicker`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::dateRangePicker([              // 等价 XfAdmin::dateRange()
    'name'     => 'range',
    'label'    => '下单时间',
    'ranges'   => true,                      // 今天/昨天/最近7天/最近30天/本月/上月
    'timepicker' => false,
    'format'   => 'YYYY-MM-DD',
]);
```

---

### `datePicker`

单日期/日期时间选择器（singleDatePicker 模式）。

> **类**：`zxf\XfAdmin\Components\Form\DatePicker`
> **文件**：`src/Components/Form/DatePicker.php`（68 行）
> **依赖插件**：`daterangepicker`

**用法示例**

```php
XfAdmin::datePicker(['name' => 'birthday', 'label' => '生日']);
XfAdmin::datePicker(['name' => 'meet_at', 'label' => '会议时间', 'timepicker' => true, 'format' => 'YYYY-MM-DD HH:mm']);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::datePicker([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'timepicker' => false,
    'format' => 'YYYY-MM-DD',
    'min' => null,
    'max' => null,
    'options' => [],
    'prepend' => '',
    'append' => '',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传 daterangepicker 原生配置（singleDatePicker 恒为 true）

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-datepicker');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读；源码用法：`'readonly' => (bool) $this->get('readonly'),` |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；输出到 `` 属性 |
| `placeholder` | mixed | `null` | 占位提示文案；源码用法：`'placeholder' => $this->get('placeholder'),` |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `timepicker` | bool | `false` | 是否带时间选择；源码用法：`'timePicker' => (bool) $this->get('timepicker'),` |
| `format` | string | `'YYYY-MM-DD'` | 格式化闭包或格式字符串；源码用法：`'locale' => ['format' => $this->get('format')],` |
| `min` | mixed | `null` | 最小值；源码用法：`'minDate' => $this->get('min'),` |
| `max` | mixed | `null` | 最大值；源码用法：`'maxDate' => $this->get('max'),` |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `prepend` | string | `''` | 前缀内容（原样输出，如输入组文本/图标） |
| `append` | string | `''` | 后缀内容（原样输出，常用于协议说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `editor`

富文本编辑器（TinyMCE/Quill 等）。

> **类**：`zxf\XfAdmin\Components\Form\Editor`
> **文件**：`src/Components/Form/Editor.php`（69 行）
> **依赖插件**：`quill`、`summernote`

**用法示例**

```php
XfAdmin::editor(['name' => 'content', 'label' => '正文', 'driver' => 'quill', 'theme' => 'snow', 'height' => 300, 'value' => '<p>初始内容</p>']);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::editor([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'driver' => 'quill',    // quill | summernote
    'theme' => 'snow',    // quill: snow | bubble
    'height' => 260,
    'options' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传编辑器原生配置（quill 可传 `modules`，summernote 直接传选项）

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-editor');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；**文本槽位**：输出前自动 HTML 转义 |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `driver` | string | `'quill'` | quill \| summernote |
| `theme` | string | `'snow'` | quill: snow \| bubble |
| `height` | int | `260` | 高度（CSS 长度，受安全白名单约束）；源码用法：`'height' => (int) $this->get('height'),` |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `upload`

文件上传（native/dropzone/filepond 驱动）。

> **类**：`zxf\XfAdmin\Components\Form\Upload`
> **文件**：`src/Components/Form/Upload.php`（94 行）
> **依赖插件**：`dropzone`、`filepond`

**用法示例**

```php
XfAdmin::upload(['name' => 'file', 'label' => '附件']);
XfAdmin::upload(['driver' => 'dropzone', 'url' => '/upload', 'label' => '拖拽上传']);
XfAdmin::upload(['driver' => 'filepond', 'name' => 'avatar', 'multiple' => true]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::upload([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'driver' => 'native',    // native | dropzone | filepond
    'url' => null,    // 上传地址
    'multiple' => false,
    'accept' => null,
    'max_size' => null,    // MB
    'text' => '点击或拖拽文件到此处上传',
    'options' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传 dropzone / filepond 原生配置

> **渲染骨架**：主要 class `dz-message` `needsclick`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-upload');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`'required' => (bool) $this->get('required'),` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`'disabled' => (bool) $this->get('disabled'),` |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `driver` | string | `'native'` | native \| dropzone \| filepond |
| `url` | mixed | `null` | 上传地址 |
| `multiple` | bool | `false` | 是否多选；源码用法：`'allowMultiple' => (bool) $this->get('multiple'),` |
| `accept` | mixed | `null` | 源码用法：`'acceptedFiles' => $this->get('accept'),` |
| `max_size` | mixed | `null` | MB |
| `text` | string | `'点击或拖拽文件到此处上传'` | 正文/按钮文案（纯文本语义，输出时转义）；**文本槽位**：输出前自动 HTML 转义 |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `colorPicker`

颜色选择器（pickr）。

> **类**：`zxf\XfAdmin\Components\Form\ColorPicker`
> **文件**：`src/Components/Form/ColorPicker.php`（49 行）
> **依赖插件**：`pickr`

**用法示例**

```php
XfAdmin::colorPicker(['name' => 'color', 'label' => '主题色', 'value' => '#3e60d5', 'theme' => 'classic']);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::colorPicker([
    'name' => null,    // 表单字段名 / 语义名称
    'id' => null,    // 根元素 id（留空自动生成唯一 id）
    'label' => null,    // 标签文案（表单字段标签 / 按钮文案）
    'help' => null,    // 帮助说明文本（转义输出，渲染为 .form-text）
    'required' => false,    // 是否必填（渲染 required 属性 + 红色星号）
    'disabled' => false,    // 是否禁用
    'readonly' => false,    // 是否只读
    'value' => null,    // 当前值（表单控件值 / 展示数值）
    'placeholder' => null,    // 占位提示文案
    'wrapper' => 'mb-3',    // 外层包裹容器 class（`false`/`null` 时不包裹）
    'feedback' => null,    // 校验反馈文案 `['valid'=>..,'invalid'=>..]`
    'theme' => 'classic',    // classic | monolith | nano
    'options' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `options[]`：透传 Pickr 原生配置

> **渲染骨架**：主要 class `d-flex` `align-items-center` `gap-2`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称；**文本槽位**：输出前自动 HTML 转义 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-color');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值）；**文本槽位**：输出前自动 HTML 转义 |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `feedback` | mixed | `null` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `theme` | string | `'classic'` | classic \| monolith \| nano |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `tags`

标签输入（Tagify 多标签）。

> **类**：`zxf\XfAdmin\Components\Form\Tags`
> **文件**：`src/Components/Form/Tags.php`（67 行）
> **依赖插件**：`tagify`

**用法示例**

```php
XfAdmin::tags([
    'name'      => 'tags',
    'label'     => '标签',
    'value'     => ['php', 'laravel'],
    'whitelist' => ['php','laravel','vue','react'],
    'max'       => 5,
    'placeholder' => '输入后回车',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::tags([
    'name' => '',
    'label' => null,
    'value' => [],
    'whitelist' => [],
    'max' => null,
    'placeholder' => '',
    'help' => null,
    'col' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `whitelist[]`：候选词字符串数组（Tagify 建议列表）

> **渲染骨架**：主要 class `form-control`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `''` | 表单字段名 / 语义名称；源码用法：`$attrs['name'] = $this->get('name');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `value` | array | `[]` | 当前值（表单控件值 / 展示数值）；源码用法：`$value = $this->get('value');` |
| `whitelist` | array | `[]` | 标签输入候选词数组；源码用法：`'whitelist' => $this->get('whitelist') ?: null,` |
| `max` | mixed | `null` | 最大值；源码用法：`'maxTags' => $this->get('max'),` |
| `placeholder` | string | `''` | 占位提示文案；开关：非空 / 真值时启用对应区块 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `col` | mixed | `null` | 列宽（栅格列数 1-12） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `maskedInput`

输入掩码（电话/日期格式约束）。

> **类**：`zxf\XfAdmin\Components\Form\MaskedInput`
> **文件**：`src/Components/Form/MaskedInput.php`（63 行）
> **依赖插件**：`inputmask`

**用法示例**

```php
XfAdmin::maskedInput([
    'name'  => 'phone',
    'label' => '手机号',
    'mask'  => '999-9999-9999',   // 或 alias: 'email' / 'currency' / 'datetime'
    'value' => '',
    'placeholder' => '___-____-____',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::maskedInput([
    'name' => '',
    'label' => null,
    'mask' => null,
    'alias' => null,
    'value' => '',
    'placeholder' => null,
    'help' => null,
    'col' => null,
]);
```

</details>

> **渲染骨架**：主要 class `form-control`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `''` | 表单字段名 / 语义名称；源码用法：`$attrs['name'] = $this->get('name');` |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `mask` | mixed | `null` | 输入掩码表达式，如 `999-9999-9999`；源码用法：`'mask' => $this->get('mask'),` |
| `alias` | mixed | `null` | 掩码别名（部分驱动支持）；源码用法：`'alias' => $this->get('alias'),` |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值）；源码用法：`$attrs['value'] = $this->get('value');` |
| `placeholder` | mixed | `null` | 占位提示文案；开关：非空 / 真值时启用对应区块 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `col` | mixed | `null` | 列宽（栅格列数 1-12） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `wizard`

向导（多步表单分步导航）。

> **类**：`zxf\XfAdmin\Components\Form\Wizard`
> **文件**：`src/Components/Form/Wizard.php`（94 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::wizard([
    'steps' => [
        ['title' => '账户', 'icon' => 'ti ti-user', 'content' => '第一步内容 HTML'],
        ['title' => '资料', 'icon' => 'ti ti-file', 'content' => '第二步内容'],
        ['title' => '完成', 'icon' => 'ti ti-check', 'content' => '完成'],
    ],
    'variant'  => 'primary',
    'vertical' => false,
    'progress' => true,          // 顶部进度条
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::wizard([
    'steps' => [],
    'variant' => 'primary',
    'vertical' => false,
    'progress' => true,
    // labels
    'labels' => [
        'prev' => '上一步',
        'next' => '下一步',
        'finish' => '提交',
    ],
    'action' => '',    // 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit）
    'method' => 'post',    // 提交方法
    'remote' => false,    // 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `steps[]` 元素键：`icon`、`title`；补充：`title`（导航标题）、`icon`、`content`（面板内容，原样输出）
- `labels[]` 元素键：`prev`（默认 `上一步`）、`finish`（默认 `提交`）、`next`（默认 `下一步`）

> **渲染骨架**：主要 class `progress` `mb-3` `col-md-9` `d-flex` `justify-content-between` `mt-3` `xf-wizard`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `steps` | array | `[]` | 步骤数组（向导 / 步骤条） |
| `variant` | string | `'primary'` | 可选值：`primary` |
| `vertical` | bool | `false` | 是否纵向排列；源码用法：`$vertical = (bool) $this->get('vertical');` |
| `progress` | bool | `true` | 进度百分比（0-100）；开关：非空 / 真值时启用对应区块 |
| `labels` | array | `['prev'=>'上一步', 'next'=>'下一步', 'finish'=>'提交']` | 标签或文案数组（组件语义不同：按钮文案 / 单位文案 / 图表标签）；源码用法：`$labels = (array) $this->get('labels');` |
| `action` | string | `''` | 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit） |
| `method` | string | `'post'` | 提交方法；**文本槽位**：输出前自动 HTML 转义 |
| `remote` | bool | `false` | 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `passwordStrength`

密码强度计（实时弱/中/强提示）。

> **类**：`zxf\XfAdmin\Components\Form\PasswordStrength`
> **文件**：`src/Components/Form/PasswordStrength.php`（107 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::passwordStrength([
    'name'     => 'password',
    'label'    => '密码',
    'value'    => '',
    'showRules'=> true,   // 显示规则清单（长度/小写/大写/数字/符号）
    'minScore' => 3,      // 0-4，弱密码时禁用提交（配合表单）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::passwordStrength([
    'name' => 'password',
    'id' => null,
    'label' => '密码',
    'value' => '',
    'showRules' => true,
    'minScore' => 0,
    'hint' => '',
]);
```

</details>

> **渲染骨架**：主要 class `form-text` `mt-2` `progress` `progress-bar` `small` `mt-1` `list-unstyled` `text-muted`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `'password'` | 表单字段名 / 语义名称；输出到 `` 属性 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | string | `'密码'` | 标签文案（表单字段标签 / 按钮文案） |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值）；输出到 `` 属性 |
| `showRules` | bool | `true` | 是否展示密码规则清单；开关：非空 / 真值时启用对应区块 |
| `minScore` | int | `0` | 最低分数要求（低于则禁用提交按钮）；源码用法：`'data-min' => (int) $this->get('minScore'),` |
| `hint` | string | `''` | 输入框下方提示文本；源码用法：`if ($hint = $this->get('hint')) {` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `captcha`

验证码（image/math/slide 三种模式）。

> **类**：`zxf\XfAdmin\Components\Form\Captcha`
> **文件**：`src/Components/Form/Captcha.php`（96 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::captcha([
    'mode' => 'image',
    'src'  => '/captcha?t=' . time(),   // 点击刷新
    'label'=> '验证码',
    'name' => 'captcha',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::captcha([
    'mode' => 'image',    // image | math | slide
    'label' => '验证码',
    'name' => 'captcha',
    'id' => null,
    'src' => null,    // image 模式图片地址（默认走 XfAdmin 通用 /captcha 约定）
    'question' => null,    // math 模式题目，默认随机生成
    'placeholder' => '请输入计算结果',
    'refreshable' => true,    // image 模式点击刷新
    'help' => null,
    'required' => true,
]);
```

</details>

> **渲染骨架**：主要 class `mb-3` `text-danger` `input-group` `mt-2` `form-text` `text-decoration-underline` `alert` `alert-light`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `mode` | string | `'image'` | 枚举白名单 `'image', 'math', 'slide'` |
| `label` | string | `'验证码'` | 标签文案（表单字段标签 / 按钮文案）；源码用法：`$label = $this->get('label');` |
| `name` | string | `'captcha'` | 表单字段名 / 语义名称；源码用法：`$name = $this->get('name');` |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id）；源码用法：`$id = $this->get('id') ?? ('captcha_' . $this->uid('cap'));` |
| `src` | mixed | `null` | image 模式图片地址（默认走 XfAdmin 通用 /captcha 约定） |
| `question` | mixed | `null` | math 模式题目，默认随机生成 |
| `placeholder` | string | `'请输入计算结果'` | 占位提示文案；**文本槽位**：输出前自动 HTML 转义 |
| `refreshable` | bool | `true` | image 模式点击刷新；开关：非空 / 真值时启用对应区块 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `required` | bool | `true` | 是否必填（渲染 required 属性 + 红色星号）；源码用法：`$req = ! empty($this->get('required'));` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `twoFactorInput`

两步验证 / OTP 验证码输入框 6 格独立输入，自动跳格、退格回退、粘贴自动填充， 复刻 inspinia auth-two-factor.html 的验证码交互。 'length'    => 6, 'name'      => 'code', 'value'     => '', 'mask'      => 'name@example.com',  // 展示邮箱掩码提示 'autofocus' => true, ])。

> **类**：`zxf\XfAdmin\Components\Form\TwoFactorInput`
> **文件**：`src/Components/Form/TwoFactorInput.php`（54 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::twoFactorInput([
    'length'    => 6,
    'name'      => 'code',
    'value'     => '',
    'mask'      => 'name@example.com',  // 展示邮箱掩码提示
    'autofocus' => true,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::twoFactorInput([
    'length' => 6,
    'name' => 'code',
    'value' => '',
    'mask' => null,
    'autofocus' => true,
    'disabled' => false,
]);
```

</details>

> **渲染骨架**：主要 class `xf-2fa` `d-flex` `gap-2` `justify-content-center` `fw-semibold`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `length` | int | `6` | 长度 / 位数（如 OTP 格数 4-8）；源码用法：`$length = max(4, min(8, (int) $this->get('length')));` |
| `name` | string | `'code'` | 表单字段名 / 语义名称；源码用法：`$name = $this->get('name');` |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值）；源码用法：`$value = (string) $this->get('value');` |
| `mask` | mixed | `null` | 输入掩码表达式，如 `999-9999-9999`；源码用法：`$mask = $this->get('mask');` |
| `autofocus` | bool | `true` | 源码用法：`$autofocus = $this->get('autofocus') ? ' data-xf-autofocus' : '';` |
| `disabled` | bool | `false` | 是否禁用；源码用法：`$disabled = $this->get('disabled') ? ' disabled' : '';` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `quantityStepper`

数量步进器 − 数字 + 按钮组，含 min / max / step，复刻电商购物车数量控件。 'name'  => 'qty', 'value' => 1, 'min'   => 1, 'max'   => 99, 'step'  => 1, 'size'  => 'md',   // sm | md | lg ])。

> **类**：`zxf\XfAdmin\Components\Form\QuantityStepper`
> **文件**：`src/Components/Form/QuantityStepper.php`（46 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::quantityStepper([
    'name'  => 'qty',
    'value' => 1,
    'min'   => 1,
    'max'   => 99,
    'step'  => 1,
    'size'  => 'md',   // sm | md | lg
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::quantityStepper([
    'name' => 'qty',
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'step' => 1,
    'size' => 'md',
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `'qty'` | 表单字段名 / 语义名称；源码用法：`$name = $this->get('name');` |
| `value` | int | `1` | 当前值（表单控件值 / 展示数值）；源码用法：`$value = (int) $this->get('value');` |
| `min` | int | `1` | 最小值；源码用法：`$min = (int) $this->get('min');` |
| `max` | int | `99` | 最大值；源码用法：`$max = (int) $this->get('max');` |
| `step` | int | `1` | 步长；源码用法：`$step = (int) $this->get('step');` |
| `size` | string | `'md'` | 枚举白名单 `'sm', 'md', 'lg'` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `formElements`

表单元素集合（所有输入控件演示）。

> **类**：`zxf\XfAdmin\Components\Form\FormElements`
> **文件**：`src/Components/Form/FormElements.php`（148 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::formElements([
    'sections' => [
        ['title' => '基础输入', 'items' => [...], 'cols' => 2],
        ['title' => '选择器', 'items' => [...], 'cols' => 3],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::formElements([
    'sections' => null,
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `sections` | mixed | `null` | 分区数组；源码用法：`$sections = $this->get('sections');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `formLayout`

表单布局（水平/垂直/网格排布）。

> **类**：`zxf\XfAdmin\Components\Form\FormLayout`
> **文件**：`src/Components/Form/FormLayout.php`（175 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::formLayout([
    'layout' => 'horizontal', // vertical|horizontal|inline|floating
    'columns' => 2,
    'fields' => [['label' => '用户名', 'type' => 'text', 'name' => 'username', 'placeholder' => '请输入']],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::formLayout([
    'layout' => 'vertical',
    'columns' => 2,
    'fields' => null,
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'vertical'` | 布局模式（各组件不同，如 vertical/horizontal）；源码用法：`? $this->renderCustomForm((string) $this->get('layout'), (int) $this->get('columns'),…` |
| `columns` | int | `2` | 列定义数组；源码用法：`? $this->renderCustomForm((string) $this->get('layout'), (int) $this->get('columns'),…` |
| `fields` | mixed | `null` | 字段定义数组（表单字段 / 详情字段）；源码用法：`$fields = $this->get('fields');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `formOtherPlugin`

其它表单插件（掩码/校验等组合）。

> **类**：`zxf\XfAdmin\Components\Form\FormOtherPlugin`
> **文件**：`src/Components/Form/FormOtherPlugin.php`（73 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::formOtherPlugin([
    'plugins' => ['mask', 'autosize', 'maxlength', 'touchspin'],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::formOtherPlugin([
    // plugins
    'plugins' => [
        'mask',
        'autosize',
        'maxlength',
        'touchspin',
    ],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `plugins[]`：启用哪些插件段：`mask`、`autosize`、`maxlength`、`touchspin`

> **渲染骨架**：主要 class `text-center` `text-muted` `py-4` `card` `mb-3` `card-header` `card-body` `row`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `plugins` | array | `['mask', 'autosize', 'maxlength', 'touchspin']` | 启用的插件列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `formValidation`

表单校验（实时反馈示例）。

> **类**：`zxf\XfAdmin\Components\Form\FormValidation`
> **文件**：`src/Components/Form/FormValidation.php`（108 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::formValidation([
    'showBuiltin' => true, // 是否展示 HTML5 原生验证
    'formId' => 'myForm',
    'fields' => [
        ['label' => '用户名', 'name' => 'username', 'rules' => 'required|min:2', 'type' => 'text'],
        ['label' => '邮箱', 'name' => 'email', 'rules' => 'required|email', 'type' => 'email'],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::formValidation([
    'showBuiltin' => true,
    'formId' => 'xf_form_val',
    'fields' => null,
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `showBuiltin` | bool | `true` | 是否显示内置区块 |
| `formId` | string | `'xf_form_val'` | 源码用法：`? $this->renderCustomForm((string) $this->get('formId'), (array) $fields)` |
| `fields` | mixed | `null` | 字段定义数组（表单字段 / 详情字段）；源码用法：`$fields = $this->get('fields');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。


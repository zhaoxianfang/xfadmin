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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `enctype` | mixed | `null` |  |
| `validation` | bool | `false` |  |
| `ajax` | bool | `false` | true：标记 data-xf-remote，由 JS 拦截为 AJAX 提交（含文件 FormData / CSRF / 统一响应） |
| `remote` | bool | `false` | 兼容别名（= ajax），保留旧调用方式 |
| `redirect` | string | `''` | AJAX 成功且后端未返回 url 时前端兜底跳转地址（整页） |
| `reset` | bool | `false` | AJAX 成功后是否重置表单（默认 false；与 reload 二选一） |
| `reload` | bool | `true` | AJAX 成功且无 url/redirect 时是否刷新当前页（false=仅 toast） |
| `inline` | bool | `false` | 兼容旧写法（等价 layout=inline） |
| `layout` | mixed | `null` | vertical \| horizontal \| inline（form-layouts.html） |
| `label_width` | int | `180` | horizontal 布局标签列宽（px） |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `content` | mixed | `null` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `buttons` | mixed | `null` | 按钮定义数组 |
| `csrf` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `type` | string | `'text'` | 类型（各组件语义不同，详见该组件说明） |
| `size` | mixed | `null` | sm \| lg |
| `mask` | mixed | `null` | inputmask 表达式 |
| `tags` | bool | `false` | tagify 标签输入 |
| `prepend` | mixed | `null` |  |
| `append` | mixed | `null` |  |
| `min` | mixed | `null` | 最小值 |
| `max` | mixed | `null` | 最大值 |
| `step` | mixed | `null` | 步长 |
| `pattern` | mixed | `null` |  |
| `autocomplete` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `rows` | int | `3` | 行数据数组 |
| `maxlength` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `groups` | array | `[]` |  |
| `multiple` | bool | `false` | 是否多选 |
| `size` | mixed | `null` | 尺寸：sm \| lg（部分组件支持 md/xl） |
| `enhance` | mixed | `null` | 增强插件：choices \| select2 |
| `enhance_options` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `type` | string | `'checkbox'` | checkbox \| radio \| switch |
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `options` | array | `[]` | 一组：value => label |
| `value` | mixed | `null` | 选中值（组模式）；单个模式用 checked |
| `checked` | bool | `false` |  |
| `inline` | bool | `false` | 是否行内排列 |
| `reverse` | bool | `false` |  |
| `disabled` | bool | `false` | 是否禁用 |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `wrapper` | string | `'mb-3'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `min` | int | `0` | 最小值 |
| `max` | int | `100` | 最大值 |
| `step` | int | `1` | 步长 |
| `tooltips` | bool | `false` |  |
| `connect` | mixed | `null` |  |
| `options` | array | `[]` | 透传 noUiSlider 配置 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `single` | bool | `false` |  |
| `timepicker` | bool | `false` |  |
| `format` | string | `'YYYY-MM-DD'` | 格式化闭包或格式字符串 |
| `ranges` | bool | `false` | 快捷区间（今天/最近7天/本月...） |
| `options` | array | `[]` | 透传 daterangepicker 配置 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

### `dateRangePicker`

`dateRangePicker` 是 `dateRange` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Form\DateRangePicker`，参数与用法完全一致。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `timepicker` | bool | `false` |  |
| `format` | string | `'YYYY-MM-DD'` | 格式化闭包或格式字符串 |
| `min` | mixed | `null` | 最小值 |
| `max` | mixed | `null` | 最大值 |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `prepend` | string | `''` |  |
| `append` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `driver` | string | `'quill'` | quill \| summernote |
| `theme` | string | `'snow'` | quill: snow \| bubble |
| `height` | int | `260` | 高度（CSS 长度，受安全白名单约束） |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `driver` | string | `'native'` | native \| dropzone \| filepond |
| `url` | mixed | `null` | 上传地址 |
| `multiple` | bool | `false` | 是否多选 |
| `accept` | mixed | `null` |  |
| `max_size` | mixed | `null` | MB |
| `text` | string | `'点击或拖拽文件到此处上传'` | 正文/按钮文案（纯文本语义，输出时转义） |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | mixed | `null` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `false` | 是否必填（渲染 required 属性 + 红色星号） |
| `disabled` | bool | `false` | 是否禁用 |
| `readonly` | bool | `false` | 是否只读 |
| `value` | mixed | `null` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `wrapper` | string | `'mb-3'` |  |
| `feedback` | mixed | `null` |  |
| `theme` | string | `'classic'` | classic \| monolith \| nano |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `''` | 表单字段名 / 语义名称 |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `value` | array | `[]` | 当前值（表单控件值 / 展示数值） |
| `whitelist` | array | `[]` |  |
| `max` | mixed | `null` | 最大值 |
| `placeholder` | string | `''` | 占位提示文案 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `col` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `''` | 表单字段名 / 语义名称 |
| `label` | mixed | `null` | 标签文案（表单字段标签 / 按钮文案） |
| `mask` | mixed | `null` |  |
| `alias` | mixed | `null` |  |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值） |
| `placeholder` | mixed | `null` | 占位提示文案 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `col` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `steps` | array | `[]` | 步骤数组（向导 / 步骤条） |
| `variant` | string | `'primary'` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `vertical` | bool | `false` | 是否纵向排列 |
| `progress` | bool | `true` | 进度百分比（0-100） |
| `labels` | array | `['prev'=>'上一步', 'next'=>'下一步', 'finish'=>'提交']` | 数组结构（见组件用法示例） |
| `action` | string | `''` | 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit） |
| `method` | string | `'post'` | 提交方法 |
| `remote` | bool | `false` | 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `'password'` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `label` | string | `'密码'` | 标签文案（表单字段标签 / 按钮文案） |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值） |
| `showRules` | bool | `true` |  |
| `minScore` | int | `0` |  |
| `hint` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `mode` | string | `'image'` | image \| math \| slide |
| `label` | string | `'验证码'` | 标签文案（表单字段标签 / 按钮文案） |
| `name` | string | `'captcha'` | 表单字段名 / 语义名称 |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |
| `src` | mixed | `null` | image 模式图片地址（默认走 XfAdmin 通用 /captcha 约定） |
| `question` | mixed | `null` | math 模式题目，默认随机生成 |
| `placeholder` | string | `'请输入计算结果'` | 占位提示文案 |
| `refreshable` | bool | `true` | image 模式点击刷新 |
| `help` | mixed | `null` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `required` | bool | `true` | 是否必填（渲染 required 属性 + 红色星号） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `length` | int | `6` |  |
| `name` | string | `'code'` | 表单字段名 / 语义名称 |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值） |
| `mask` | mixed | `null` |  |
| `autofocus` | bool | `true` |  |
| `disabled` | bool | `false` | 是否禁用 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `'qty'` | 表单字段名 / 语义名称 |
| `value` | int | `1` | 当前值（表单控件值 / 展示数值） |
| `min` | int | `1` | 最小值 |
| `max` | int | `99` | 最大值 |
| `step` | int | `1` | 步长 |
| `size` | string | `'md'` | 尺寸：sm \| lg（部分组件支持 md/xl） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `sections` | mixed | `null` | 分区数组 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'vertical'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `columns` | int | `2` | 列定义数组 |
| `fields` | mixed | `null` | 字段定义数组（表单字段 / 详情字段） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `plugins` | array | `['mask', 'autosize', 'maxlength', 'touchspin']` | 数组结构（见组件用法示例） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `showBuiltin` | bool | `true` |  |
| `formId` | string | `'xf_form_val'` |  |
| `fields` | mixed | `null` | 字段定义数组（表单字段 / 详情字段） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


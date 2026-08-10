# 表单

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 表单容器 `form`

```php
XfAdmin::form([
    'action' => '/users',
    'method' => 'POST',
    'layout' => 'vertical',        // vertical 纵向(默认) | horizontal 标签左置 | inline 行内
    'label_width' => 180,          // horizontal 布局的标签列宽（px，CSS Grid 实现，窄屏自动回退纵向）
    'fields' => [
        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true]),
        XfAdmin::input(['name' => 'email', 'label' => '邮箱', 'type' => 'email']),
        XfAdmin::select(['name' => 'role', 'label' => '角色', 'options' => ['管理员', '编辑']]),
    ],
    'buttons' => [
        XfAdmin::button(['label' => '提交', 'type' => 'submit', 'variant' => 'primary']),
        XfAdmin::button(['label' => '重置', 'type' => 'reset', 'variant' => 'light']),
    ],
    'validate' => true,            // 启用 Bootstrap 客户端校验
    'csrf'     => true,            // Laravel 下自动插入 @csrf token
]);
```

## 字段组件

所有字段共用：`name` `label` `value` `help`（帮助文本）`error`（错误信息）`required` `disabled` `readonly` `col`（栅格宽度）`placeholder`。

### input

```php
XfAdmin::input([
    'name' => 'price', 'label' => '价格', 'type' => 'number',
    'prepend' => '¥', 'append' => '.00',       // 输入组前后缀
    'value' => 100,
]);
```

`type` 支持 `text/email/password/number/url/tel/date/time/color/file/hidden` 等。

### textarea / select / check

```php
XfAdmin::textarea(['name' => 'desc', 'label' => '描述', 'rows' => 4]);

XfAdmin::select([
    'name' => 'city', 'label' => '城市',
    'options' => ['bj' => '北京', 'sh' => '上海'],   // 关联或列表
    'value'   => 'sh',
    'multiple'=> false,
    'searchable' => true,          // 启用 Choices.js 搜索
    'placeholder' => '请选择',
]);

XfAdmin::check([
    'name' => 'agree', 'label' => '同意条款',
    'type' => 'checkbox',          // checkbox | radio | switch
    'value'=> 1, 'checked' => true,
]);
// 单选组
XfAdmin::check([
    'name' => 'gender', 'type' => 'radio', 'inline' => true,
    'options' => ['m' => '男', 'f' => '女'],
]);
```

### 增强字段

```php
// 日期区间（Flatpickr）
XfAdmin::dateRangePicker(['name' => 'range', 'label' => '日期范围']);

// 滑块（noUiSlider）
XfAdmin::slider(['name' => 'price', 'label' => '价格区间', 'min' => 0, 'max' => 1000, 'start' => [100, 800]]);

// 颜色选择
XfAdmin::colorPicker(['name' => 'color', 'label' => '主题色', 'value' => '#3e60d5']);

// 富文本（Quill）
XfAdmin::editor(['name' => 'content', 'label' => '正文', 'value' => '<p>...</p>', 'height' => '300px']);

// 文件上传（Dropzone）
XfAdmin::upload(['name' => 'files', 'label' => '附件', 'multiple' => true, 'url' => '/upload', 'max_files' => 5]);

// 标签输入（Tagify）
XfAdmin::tags(['name' => 'tags', 'label' => '标签', 'value' => ['php', 'laravel'], 'whitelist' => ['php', 'vue', 'react'], 'max' => 5]);

// 输入掩码（Inputmask）
XfAdmin::maskedInput(['name' => 'phone', 'label' => '手机', 'mask' => '999-9999-9999']);
XfAdmin::maskedInput(['name' => 'money', 'label' => '金额', 'alias' => 'currency']);
```

---

## 分步向导 `wizard`

纯原生 JS，无第三方依赖。逐步校验当前步骤内的表单元素后才允许前进。

```php
XfAdmin::wizard([
    'variant' => 'primary',
    'vertical'=> false,
    'steps'   => [
        ['title' => '账户信息', 'icon' => 'ti ti-user', 'content' =>
            XfAdmin::input(['name' => 'user', 'label' => '用户名', 'required' => true])],
        ['title' => '个人资料', 'icon' => 'ti ti-file', 'content' =>
            XfAdmin::textarea(['name' => 'bio', 'label' => '简介'])],
        ['title' => '完成', 'icon' => 'ti ti-check', 'content' => '<p>确认并提交</p>'],
    ],
    'labels'  => ['prev' => '上一步', 'next' => '下一步', 'finish' => '提交'],
]);
```

监听事件：

```js
document.querySelector('.xf-wizard').addEventListener('xf.wizard.finish', () => {
    console.log('向导完成');
});
```

---

## 表单校验错误回填

结合后端校验，把错误传给字段：

```php
XfAdmin::input([
    'name'  => 'email',
    'label' => '邮箱',
    'value' => old('email'),
    'error' => $errors->first('email'),   // 有值则渲染 is-invalid + 错误提示
]);
```

### 密码强度计 `passwordStrength`

实时计算强度（长度/小写/大写/数字/符号），进度条 + 规则清单，可联动禁用提交按钮。

```php
echo XfAdmin::passwordStrength([
    'name'      => 'password',
    'label'     => '密码',
    'showRules' => true,    // 显示规则清单
    'minScore'  => 3,        // 强度 < 3 时禁用所在表单的提交按钮
]);

// 监听强度变化
// XFAdmin.onReady(function(){
//   document.querySelector('[data-xf="pw-strength"]')
//     .addEventListener('xf.pw.score', e => console.log(e.detail.score, e.detail.ok));
// });
```
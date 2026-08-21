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

---

## AJAX 表单提交（含文件上传 / CSRF / 响应规范）

XfAdmin 表单组件内置 **Ajax 提交模式**：开启后，前端会拦截表单默认提交、改用 `fetch` 发起 Ajax 请求，并在请求头中自动附加 CSRF Token，支持普通字段与文件字段的混合提交（自动转 `FormData` 二进制）。

### 1. 模板 head 的 CSRF Meta（所有模板页面已自动注入）

`Page` 与 `AuthPage` 组件在 `<head>` 中自动输出（Laravel 环境）：

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

纯 PHP 环境若未用 Blade，可手动输出等价标签：

```php
echo '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
```

> 所有模板页面（后台、登录、设置等）都必须包含该 meta，否则 Ajax 请求会被 CSRF 中间件拦截（返回 419）。

### 2. 开启 Ajax 提交的两种方式

**方式 A：使用 `XfAdmin::form()` 组件（推荐）**

```php
echo XfAdmin::form([
    'action' => '/admin/record/albums',
    'method' => 'POST',
    'ajax'   => true,            // ← 关键：开启 Ajax 提交（自动加 data-xf-remote + multipart/form-data）
    'fields' => [
        XfAdmin::input(['name' => 'title', 'label' => '标题', 'required' => true]),
        XfAdmin::select(['name' => 'status', 'label' => '状态',
            'options' => ['已发布' => '已发布', '草稿' => '草稿']]),
        XfAdmin::upload(['name' => 'cover', 'label' => '封面', 'accept' => 'image/*']), // 文件字段
    ],
    'buttons' => [ XfAdmin::button(['label' => '保存', 'type' => 'submit', 'variant' => 'primary']) ],
]);
```

`ajax => true` 时组件会自动：
- 给 `<form>` 加 `data-xf-remote` 属性（前端据此接管提交）
- 检测到文件字段或显式 `file => true` 时自动加 `enctype="multipart/form-data"`

**Ajax 与默认提交共存**：`ajax` 不传（默认 `false`）时，表单走浏览器原生提交（`action` + `method`），不做任何 JS 拦截；同时组件会把 `method` 差异用隐藏字段 `_method` 表达（Laravel 惯例，支持 PUT/DELETE/GET/PATCH）。即**同一套 `XfAdmin::form()` 既能整页提交也能 Ajax 提交**，由 `ajax` 参数决定：

```php
// 整页提交（默认）：浏览器原生跳转/刷新
XfAdmin::form(['action' => '/save', 'method' => 'POST', 'fields' => [...]]);

// Ajax 提交：JS 拦截，统一处理响应
XfAdmin::form(['action' => '/save', 'method' => 'POST', 'ajax' => true, 'fields' => [...]]);
```

**Ajax 成功行为参数**（仅 `ajax => true` 生效，三选一优先级：`data.url` > `redirect` > `reload` > `reset`）：

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `reload` | bool | `true` | 成功且无 `url`/`redirect` 时刷新当前页（1.2 秒后） |
| `redirect` | string | `''` | 前端兜底跳转地址（后端未返回 `data.url` 时生效，1.2 秒后整页跳转） |
| `reset` | bool | `false` | 成功后重置表单（不刷新/跳转，适合「连续录入」场景） |

```php
XfAdmin::form([
    'action'  => '/admin/record/albums',
    'method'  => 'POST',
    'ajax'    => true,
    'reload'  => false,           // 关闭默认刷新
    'redirect' => '/admin/albums', // 成功跳转回列表
    'fields'  => [...],
]);
```

**方式 B：手写表单加 `data-xf-remote` 属性**

```html
<form method="POST" action="/admin/record/albums" data-xf-remote enctype="multipart/form-data">
    @csrf
    <input type="text" name="title">
    <input type="file" name="cover">
    <button type="submit">保存</button>
</form>
```

### 3. 前端 Ajax 提交机制（xfadmin.js）

开启 Ajax 后，由 `XFAdmin.bindRemoteForms()` 全局委托处理：

```js
// 拦截 data-xf-remote 表单的 submit 默认行为
const form = e.target.closest('form[data-xf-remote]');
e.preventDefault();
const action = form.getAttribute('action');
const method = (form.getAttribute('method') || 'POST').toUpperCase();

// 收集所有类型字段（含文件）-> FormData
const fd = new FormData(form);
// 文件字段自动成为二进制项，普通字段为字符串项，二者一起提交

// 发起请求（XFAdmin.request 内部自动附加 CSRF 头）
XFAdmin.request(action, { method, data: fd }).then(res => { ... });
```

**CSRF 请求头（自动附加，无需手动）：**

```js
XFAdmin.request = function (url, opts) {
    opts = opts || {};
    opts.headers = opts.headers || {};
    opts.headers['X-CSRF-TOKEN'] = XFAdmin.csrf() || '';   // 读取 <meta name="csrf-token">
    opts.headers['Accept'] = 'application/json';
    return fetch(url, { ... }).then(r => r.json().then(data => ({
        ok: r.ok,
        status: r.status,
        data,
    })));
};

// 读取 csrf-token 的辅助函数
XFAdmin.csrf = function () {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};
```

### 4. 复杂表单（含文件）的请求构造

`new FormData(form)` 已自动处理所有字段类型：

| 字段类型 | 提交形式 |
|---------|---------|
| `input[type=text/email/number/...]` | 字符串 |
| `textarea` | 字符串 |
| `select` / `select[multiple]` | 单值 / 多值 |
| `input[type=checkbox/radio]/switch` | `on`/`off` 值（已按配置转换） |
| `input[type=file]` | **二进制 `Blob`** |
| `input[type=file][multiple]` | 多个二进制项 |
| `input[type=hidden]` | 字符串（含 `_method` 伪方法、`id`） |

无需手动拼接；文件与普通字段在同一 `FormData` 中一并提交，Content-Type 由浏览器自动设为 `multipart/form-data; boundary=...`。

### 5. 后端响应规范（约定字段）

后端应返回 **JSON**，字段规范如下（与 `XFAdmin.handleFormResponse` 对齐）：

```jsonc
{
    "code": 200,            // 业务码：200 成功；其它为失败（422 校验失败 / 419 CSRF / 500 异常）
    "message": "创建成功 #12",   // 提示文案（成功或失败都建议返回），缺省时前端用状态文案兜底
    "data": {
        "id": 12,
        "url": "/admin/record/albums/12"   // 可选：存在时前端提示后等待 3 秒再跳转
    }
}
```

**Laravel 后端示例：**

```php
public function store(Request $request)
{
    $id = Album::create($request->validated())->id;

    if ($request->ajax()) {
        // Ajax 提交：返回 code + data.url，前端 handleFormResponse 提示后 3 秒跳转
        return response()->json([
            'code'    => 200,
            'message' => '创建成功 #' . $id,
            'data'    => ['id' => $id, 'url' => '/admin/record/albums/' . $id],
        ]);
    }
    // 非 Ajax：整页跳转（兼容）
    return redirect('/admin/record/albums/' . $id)->with('success', '创建成功 #' . $id);
}
```

### 6. 响应拦截与提示规范（前端统一处理）

`XFAdmin.handleFormResponse(res, cfg)` 统一处理所有表单响应：

```js
XFAdmin.handleFormResponse = function (res, cfg) {
    cfg = cfg || {};
    var data = res.data || {};
    var msg = data.message || data.msg || '';
    if (res.ok) {
        XFAdmin.toast({ body: msg || '保存成功', variant: 'success' });
        if (data.url) {
            // 含跳转地址：提示后等待 3 秒再跳转（给查看提示留时间）
            setTimeout(function () { window.location.href = data.url; }, 3000);
        } else if (!cfg.noReload) {
            setTimeout(function () { window.location.reload(); }, 1200);
        }
        return true;
    }
    // 失败分支：按状态码给出规范提示
    var errMsg = res.status === 500 ? '请求异常(500)：' + (msg || '服务器内部错误')
              : res.status === 422 ? (msg || '表单校验失败')
              : res.status === 419 ? (msg || '页面已过期，请刷新后重试（CSRF 校验失败）')
              : res.status === 0   ? (msg || '网络错误，请稍后重试')
              : (msg || '请求失败(' + (res.status || '未知') + ')');
    XFAdmin.toast({ body: errMsg, variant: 'danger' });
    return false;
};
```

**提示 / 跳转规则一览：**

| 场景 | 行为 |
|------|------|
| 成功 + `data.url` 存在 | `toast` 成功提示 → 等待 **3 秒** → `window.location.href = data.url` |
| 成功 + 无 `url` | `toast` 成功提示 → 等待 1.2 秒 → `window.location.reload()` |
| 校验失败 422 | `toast` 危险提示（`data.message` 或「表单校验失败」），并**回填**字段 `is-invalid` + 错误文案 |
| CSRF 失效 419 | `toast` 提示「页面已过期，请刷新后重试」 |
| 服务器异常 500 | `toast` 提示「请求异常(500)：<message>」 |
| 网络错误 0 | `toast` 提示「网络错误，请稍后重试」 |

### 7. 完整示例：相册管理（Gallery 组件详情/编辑页）

相册类管理功能非常适合用 **Gallery 画廊组件**做详情/编辑页主体。详情页用 `XfAdmin::gallery()` 渲染照片墙，编辑/新建页用 Ajax 表单提交（含封面文件）：

```php
// 详情页：主体用 Gallery 组件
$items = AdminData::galleryItems('albums', $row);   // 由封面 + 演示图构造
$body  = (string) XfAdmin::gallery([
    'items'    => $items,
    'title'    => '照片墙',
    'columns'  => 3,
    'mode'     => 'thumb',
    'lightbox' => true,
]);

// 编辑页：Ajax 表单（含封面文件上传）
$form = '<form method="POST" action="' . e($action) . '" data-xf-form data-xf-remote enctype="multipart/form-data">'
      . csrf_field()
      . '<input type="hidden" name="_method" value="PUT">'
      . (string) XfAdmin::input(['name' => 'title', 'label' => '相册标题', 'value' => $row['title']])
      . (string) XfAdmin::upload(['name' => 'cover', 'label' => '封面', 'accept' => 'image/*'])
      . (string) XfAdmin::button(['label' => '保存', 'type' => 'submit', 'variant' => 'primary'])
      . '</form>';
```

> 参见 wsf 演示：`/admin/albums`（相册管理列表）→ 详情页照片墙（Gallery 组件）→ 编辑页 Ajax 提交（含文件）。

```
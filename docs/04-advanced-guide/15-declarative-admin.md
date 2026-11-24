# 15. 后台声明式开发（零内联 JS/HTML）

> 版本：v1.0.3+　适用场景：基于 `zxf/xfadmin` 的后台管理系统（如本项目 `Modules/Admin`）。
>
> 本文给出一套**强制 MVC 边界**的后台开发范式：控制器/服务层只传配置、不写大段 JS/HTML，
> 由 xfadmin 组件 + 内置 JS widget 声明式地渲染页面、接管交互。读完本文，你应能：
>
> 1. 用一行组件调用生成标准列表页 / 表单弹窗 / 详情片段；
> 2. 用 `rowActions` 取代列表里重复的「确认→POST→toast→刷新表格」脚本；
> 3. 用 `adminBehaviors` + `adminThemeHead` 取代布局服务里内联的退出/缓存/主题脚本；
> 4. 把剩余的页面级 HTML/JS 安全下沉到 Blade 视图片段。

---

## 1. 铁律：MVC 边界

| 位置 | 允许 | 禁止 |
|---|---|---|
| 控制器 / 服务（非视图 PHP） | 传配置、调组件、渲染视图、返回响应 | heredoc/nowdoc 的 `<script>`、手拼 `<div class="card">`、大段 HTML 字符串 |
| 组件（PHP Class） | 生成结构化 HTML、`data-xf-config` 配置节点 | 业务逻辑、数据库访问 |
| 静态 JS（xfadmin.js / 模块 js） | 通过 `XFAdmin.register()` 实现交互 widget | 硬编码业务 URL（应由配置注入） |
| Blade 视图片段 | 页面结构标记、该片段私有的少量初始化脚本 | 与后端强耦合的数据查询 |

> 关键区分：**交互逻辑归 JS widget，不归控制器**。控制器只负责声明「我要什么行为 + 相关 URL/文案」，
> 具体点击、请求、toast、刷新由 `xfadmin.js` 内的 widget 完成。

---

## 2. 三个声明式组件

### 2.1 `XfAdmin::rowActions()` —— 行操作统一接管

**痛点**：几乎每个列表页都有「删除 / 切换状态」按钮，控制器里反复写
`confirm → fetch(POST + CSRF) → toast → table.ajax.reload()`，既重复又难维护。

**方案**：按钮仍由 `dataTable` 的 `actions` 列渲染（携带 `data-url` / `data-confirm` /
`data-label`），再渲染一个 `rowActions` 声明节点统一接管点击。

```php
use zxf\XfAdmin\XfAdmin;

// 控制器：只声明，不写 JS
$rowActions = (string) XfAdmin::rowActions([
    'selector'       => '.btn-run-task, .btn-delete-task', // 接管的按钮选择器
    'table'          => '#spider-task-table',             // 成功后重载的表格
    'delete_class'   => 'btn-delete-task',                // 视为“删除”的按钮 class
    'confirm_delete' => '确认删除该任务？删除后不可恢复。',
    'status_tpl'     => '确认将状态改为「%s」？',           // 状态按钮确认模板（%s 占位）
    'ok_text'        => '操作成功',
    'fail_text'      => '操作失败',
]);

// 把 $rowActions 作为 extra 或卡片正文的一部分输出即可
```

**全部参数**

| 参数 | 默认 | 说明 |
|---|---|---|
| `selector` | `[data-xf-row-action]` | 需接管的按钮 CSS 选择器 |
| `table` | `null` | 成功后重载的表格选择器（按钮也可用 `data-table` 覆盖） |
| `confirm_delete` | `null` | 删除确认文案；按钮 `data-confirm` 优先 |
| `delete_class` | `null` | 判定为删除按钮的 class（也可 `data-action="delete"`） |
| `status_tpl` | `null` | 状态确认文案模板，含一个 `%s`，取 `data-label` |
| `ok_text` / `fail_text` | `操作成功` / `操作失败` | toast 文案兜底 |
| `token` | `null` | CSRF token；留空自动读取页面 `input[name=_token]` |

**按钮侧约定**（dataTable 的 `actions` 列生成）：

- `data-url`：请求地址（必填，缺失则该按钮不响应）；
- `data-confirm`：自定义确认文案（覆盖组件级文案）；
- `data-label`：状态按钮的目标状态名（供 `status_tpl` 的 `%s` 使用）；
- `data-action="delete"` 或带 `delete_class`：按删除处理；
- `data-param-xxx="值"`：作为额外 POST 参数 `xxx` 提交；
- `data-table`：单个按钮覆盖重载的表格选择器。

**行为细节**：发起 `POST`（`X-Requested-With: XMLHttpRequest`），成功后按 `code===0/200/undefined`
判定，toast 提示并 `ajax.reload(null,false)` 刷新表格，同时派发 `xf:row-action-success` 事件
（可在此基础上扩展埋点/联动）。分页重绘后通过 `xf-datatable-drawn` 事件自动重新绑定，无需手动处理。

> 迁移实例：`ArticleAdminBaseController::listActionScript()`、`SpiderTaskController`、
> `SocketAdminController` 均已从内联脚本改为本组件。

### 2.2 `XfAdmin::adminBehaviors()` —— 后台通用行为套件

**痛点**：后台布局里的「退出登录（POST+CSRF）、刷新缓存、图标选择器、主题/语言持久化」
是多处重复的内联脚本（本项目 `AdminLayout` 曾有约 324 行）。

**方案**：布局服务只渲染一个声明节点，交互由 `xfadmin.js` 的 `adminBehaviors` widget 提供。

```php
// Modules/Admin/Services/AdminLayout.php
public function behaviors(): Component
{
    return XfAdmin::adminBehaviors([
        'logout' => [
            'selector' => '.btn-logout',
            'url'      => route('admin.logout'),
            'token'    => csrf_token(),
            // 'method' => 'POST',
        ],
        'refresh' => [
            'selector' => '.btn-refresh-cache',
            'url'      => route('admin.cache.clear'),
            'token'    => csrf_token(),
        ],
        'iconPicker' => [
            'trigger'         => '.btn-icon-picker',
            'url'             => route('admin.icons'),
            'inputTarget'     => '#icon',
            'previewSelector' => '#icon-preview',
            'batch'           => 60,
        ],
        'theme' => true, // 或 ['storageKey'=>'xf_admin_theme','langKey'=>'xf_admin_lang']
    ]);
}
```

**参数结构**

| 顶层键 | 结构 | 说明 |
|---|---|---|
| `logout` | `{selector,url,token,tokenName?,method?}` | 退出登录，动态构造 POST 表单提交 |
| `refresh` | `{selector,url,token,tokenName?,toastSelector?}` | 刷新缓存，AJAX POST + toast |
| `iconPicker` | `{trigger,url,inputTarget,previewSelector?,batch?}` | 图标选择浮层，回填输入框/预览 |
| `theme` | `true` 或 `{storageKey,langKey}` | 主题与语言 localStorage 持久化监听 |

> 组件输出的 `data-xf-config` 采用 `JSON_HEX_*` 转义，内联 JSON 中的 `</script>`/引号不会破坏属性或造成 XSS。

### 2.3 `XfAdmin::adminThemeHead()` —— 主题提前应用（防闪烁）

放在 `<head>` 最前面，同步读取 localStorage 的 `data-bs-theme` / `lang` 并写入
`<html>`，避免首屏白闪。这是**组件自身职责**的内联脚本（不是控制器内联），属于合规用法。

```php
return XfAdmin::adminThemeHead([
    'storageKey' => 'xf_admin_theme',
    'langKey'    => 'xf_admin_lang',
]);
```

> 键名会经 `preg_replace('/[^A-Za-z0-9_]/','',...)` 净化，杜绝注入。

---

## 3. Admin 模块基类声明式助手（本项目）

> 以下助手位于 `Modules/Admin/Http/Controllers/Admin/AdminBaseController`，
> 是 xfadmin 组件在业务基类的二次封装。所有继承该基类的模块控制器都能直接调用。

### 3.1 `listPage()` —— 一行生成标准列表页

```php
protected function listPage(
    string $title,
    string $ajaxUrl,
    array $columns,
    string $active = '',
    array $options = []
): Component
```

默认统一：`server_side POST + CSRF`、固定表头、固定首列、可折叠自定义搜索面板、`p-0` 卡片正文。

```php
return $this->listPage('管理员管理', '/admin/data/admins', $columns, 'admins', [
    'table_id'    => 'admin-table',
    'card_subtitle' => '管理员登录体系基于 admins 表与 admin 守卫。',
    'filter_bar'  => [['name'=>'status','label'=>'状态','type'=>'select','options'=>AdminStatus::labels()]],
    'create'      => ['page'=>'/admin/system/admins/create','label'=>'新增管理员','size'=>'lg'],
    'fixed_columns' => ['left' => 2],       // 冻结前 2 列
    'show_custom_search' => true,
    'plugins'     => [],                     // 弹窗依赖的前端插件
]);
```

`$options` 常用键：`table_id`、`row_id`、`method`、`fixed_header`、`fixed_columns`、
`show_custom_search`、`filter_bar`、`filter_all`、`filter_auto`、`column_filters`、
`search_fields`、`create`、`bulk`、`row_detail`、`row_url`、`select`、`language`、
`order`、`card_title`、`card_subtitle`、`breadcrumb`、`plugins`、`extra`（表格前附加 HTML）。

### 3.2 `formModal()` —— 标准 AJAX 表单片段

```php
protected function formModal(string $action, array $fields, array $options = []): string
```

统一 `ajax + csrf + horizontal 布局 + 保存/取消按钮`，`fields` 为 XfAdmin 表单组件或字符串。

### 3.3 `detailView()` —— 键值详情片段

```php
protected function detailView(array $items, array $options = []): string
```

基于 `descriptionList`，替代控制器手拼详情 `<table>`。

### 3.4 `badge()` / `truncate()`

```php
$this->badge('正常', 'success');   // <span class="badge bg-success">正常</span>（内部已转义）
$this->truncate($longText, 40);    // 超长省略（title 属性保留全文）
```

---

## 4. 页面级 HTML/JS 的安全归宿：Blade 视图片段

当一段标记确实属于某个页面（如验证码挂载、第三方登录按钮、帮助说明），
把它做成**无布局 Blade 片段**，控制器只 `view(...)->render()`。片段内可含该片段私有的少量脚本。

```php
// 控制器
'captcha'   => view('admin::admin.auth.captcha')->render(),
'afterForm' => view('admin::admin.auth.social-buttons')->render()
            . view('admin::admin.auth.captcha-refresh')->render(),
'below'     => view('admin::admin.auth.remember-me')->render(),
```

视图片段约定：

- 放在模块 `Resources/views/admin/<domain>/` 下，文件名以 `_` 或语义名标识；
- 通过命名空间引用：`admin::admin.auth.captcha`；
- 需要注入的配置用 `@json($options)` 输出为 JS 对象，杜绝字符串拼接注入；
- 样式值用 `{{ e($value) }}` 转义（对照 `vendor/zxf/captcha/resources/views/captcha.blade.php`）。

> 迁移实例：`AuthController` 的 `captcha` / `captcha-refresh` / `social-buttons` / `remember-me`
> 四个片段；`SpiderTaskController` 的 `cron_help`；Socket 模块的 `dashboard/stats/detail` 等。

---

## 5. 迁移检查清单

把一个控制器从「内联 JS/HTML」迁到声明式，按此顺序核对：

1. **列表页**：是否可用 `listPage()` 一行生成？剩余 toolbar/card 用 `XfAdmin::toolbar/card/button`。
2. **行操作**：是否有重复的 confirm+POST+toast+reload 脚本？→ `rowActions`。
3. **布局通用行为**：是否有退出/缓存/图标/主题脚本？→ `adminBehaviors` + `adminThemeHead`。
4. **详情/表单**：是否手拼 `<table>`/表单样板？→ `detailView()` / `formModal()`。
5. **单元格**：是否手写 `<span class="badge">`？→ `dataTable` 的 `render` 快捷渲染器（见 [13-cell-renderers](13-cell-renderers.md)）。
6. **剩余页面级标记**：下沉为无布局 Blade 片段，控制器 `view()->render()`。
7. **验证**：`php -l` 通过；控制器内 `grep '<script\|<<<\|<div class'` 无命中；
   xfadmin `smoke/regression/audit` 全绿；vendor 与源码包字节一致。

---

## 6. 相关文档

- [03-数据表格](03-datatable.md) —— fixed_columns / show_custom_search / 渲染器
- [13-单元格渲染器](13-cell-renderers.md) —— 渲染器白名单
- [14-链式构建器](14-builder-chain.md) —— PHP 链式拼 HTML
- [01-页面布局](01-page-layout.md) —— 后台整页骨架

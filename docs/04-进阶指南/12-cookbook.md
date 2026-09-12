# 常见任务手册（Cookbook）

按任务组织，每个任务给**完整可运行代码 + 关键要点**。适合复制后改改即用。

- 基础概念见 [03-核心概念](../01-快速开始/03-concepts.md)
- 组件速查见 [INDEX.md](../INDEX.md)
- 出错先看 [04-问题排查](../05-运维/04-troubleshooting.md)

---

## 1. 服务端分页列表（含后端）

**前端**

```php
echo XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',                 // POST 自动带 CSRF，避免 URL 过长
    'page_length' => 20,
    'columns'     => [
        ['key' => 'id', 'label' => 'ID', 'width' => '70px'],
        ['key' => 'name', 'label' => '姓名'],
        ['key' => 'email', 'label' => '邮箱'],
        ['key' => 'status', 'label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],
        ['key' => 'created_at', 'label' => '注册时间', 'render' => ['type' => 'datetime', 'ago' => true]],
    ],
]);
```

**后端（Laravel）**

```php
use zxf\XfAdmin\XfAdmin;

public function users(Request $request)
{
    return response()->json(XfAdmin::dataResponse(
        User::query(),
        $request->all(),
        ['searchable' => ['name', 'email']]
    ));
}
```

**要点**：`dataResponse()` 完成搜索、排序、分页，返回
`{draw, recordsTotal, recordsFiltered, data}`，无需自己解析 DataTables 参数。

---

## 2. 过滤栏与后端 filters 配对

```php
// 前端控件
'filter_bar' => [
    ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
    ['name' => 'status',  'label' => '状态',   'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],
    ['name' => 'date',    'label' => '注册时间', 'type' => 'daterange'],
    ['name' => 'score',   'label' => '分数',   'type' => 'range', 'min' => 0, 'max' => 100],
],
```

```php
// 后端：键名必须与前端 name 对应（daterange 会拆成 *_from / *_to）
'filters' => [
    'keyword'   => ['name', 'email'],                          // 多字段模糊
    'status'    => ['field' => 'status', 'op' => '='],
    'date_from' => ['field' => 'created_at', 'op' => 'date_from'],
    'date_to'   => ['field' => 'created_at', 'op' => 'date_to'],
    'score_min' => ['field' => 'score', 'op' => '>='],
    'score_max' => ['field' => 'score', 'op' => '<='],
],
```

**要点**：区间控件产出 `*_from`/`*_to`；滑块产出 `"lo,hi"`（配 `op => between`）；
多选产出逗号串（配 `op => in`）。完整对照见
[05-服务端数据协议 §11](../02-核心架构/05-data-protocol.md#11-完整示例)。

---

## 3. 行内编辑（开关 / 下拉 / 输入框）

```php
[
    'key' => 'status', 'label' => '启用',
    'render' => ['type' => 'switch', 'url' => '/admin/users/{id}/toggle', 'field' => 'status', 'on' => 1, 'off' => 0],
],
[
    'key' => 'role', 'label' => '角色',
    'render' => ['type' => 'select', 'field' => 'role', 'url' => '/admin/users/{id}',
                 'options' => ['admin' => '管理员', 'user' => '普通用户']],
],
[
    'key' => 'sort', 'label' => '排序',
    'render' => ['type' => 'input', 'field' => 'sort', 'url' => '/admin/users/{id}'],
],
```

**前端监听**

```js
document.addEventListener('xf:switch', e => console.log(e.detail.checked, e.detail.row));
document.addEventListener('xf:cell-input', e => console.log(e.detail.value, e.detail.field));
```

**后端**：接收 `PUT /admin/users/{id}`，字段名为 `data-xf-field`（即 `field` 配置值）。

---

## 4. 行操作（编辑 / 删除 / 自定义 / 下拉）

```php
[
    'key' => '', 'label' => '操作', 'width' => '160px',
    'actions' => [
        ['label' => '编辑', 'icon' => 'ti ti-pencil', 'action' => 'edit',
         'ajax' => '/admin/users/{id}', 'method' => 'PUT',
         'fields' => [
             ['name' => 'name', 'label' => '姓名', 'type' => 'text', 'required' => true],
             ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],
         ]],
        ['label' => '详情', 'action' => 'view', 'viewTitle' => '用户详情',
         'view' => ['size' => 'lg', 'layout' => 'kv', 'fields' => ['name', 'email', 'created_at']]],
        ['label' => '日志', 'action' => 'modal', 'url' => '/admin/users/{id}/logs', 'size' => 'lg'],
        ['label' => '删除', 'icon' => 'ti ti-trash', 'class' => 'btn-soft-danger', 'action' => 'ajax',
         'ajax' => '/admin/users/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？'],
        ['label' => '更多', 'icon' => 'ti ti-dots', 'dropdown' => [
            ['label' => '重置密码', 'ajax' => '/admin/users/{id}/reset', 'method' => 'POST', 'confirm' => '确认重置？'],
            ['type' => 'divider'],
            ['label' => '禁用', 'ajax' => '/admin/users/{id}/disable', 'method' => 'POST'],
        ]],
    ],
]
```

**要点**：`{id}` 等占位在渲染时由 `XFAdmin.tpl` 插值；`url` 优先于 `ajax`（渲染成 `<a>`）。

---

## 5. 批量操作

```php
'bulk' => [
    'checkbox' => true,
    'actions'  => [
        ['label' => '启用', 'url' => '/admin/users/batch-enable', 'method' => 'POST',
         'action' => 'enable', 'confirm' => '确认启用选中项？'],
        ['label' => '删除', 'url' => '/admin/users/batch-delete', 'method' => 'DELETE',
         'confirm' => '确认删除选中项？'],
    ],
],
'dataset' => 'users',
```

提交体：`{action, dataset, ids:[]}`；URL 中 `{ids}` 会替换为逗号串。

---

## 6. 导入导出

```php
echo XfAdmin::importExport([
    'title'      => '用户数据',
    'formats'    => ['csv', 'xlsx'],            // ⚠️ 使用 export_url 简写时必须显式传
    'export_url' => '/admin/users/export',
    'import_url' => '/admin/users/import',
]);
```

后端导出返回文件下载；导入接收 `multipart/form-data` 的 `file` 字段。

> 表格自身的导出按钮用 `'export' => ['copy','excel','csv','pdf']`（纯前端导出当前数据）。

---

## 7. 详情弹窗（多布局）

```php
['label' => '详情', 'action' => 'view', 'view' => [
    'size'    => 'lg',
    'layout'  => 'sections',
    'header'  => ['avatar' => 'avatar', 'title' => '{name}', 'sub' => '{email}',
                  'badge' => ['field' => 'status', 'map' => ['1' => ['color' => 'success', 'label' => '正常']]]],
    'sections' => [
        ['title' => '基础', 'icon' => 'ti ti-user', 'type' => 'kv', 'fields' => ['name', 'email', 'phone'], 'cols' => 2],
        ['title' => '统计', 'type' => 'stats'],
        ['title' => '时间线', 'type' => 'timeline'],
    ],
    'renderers' => ['amount' => ['type' => 'money', 'prefix' => '￥']],
    'ajax'      => '/admin/api/users/{id}/extra',    // 打开前拉取合并
]]
```

分区 `type`：`kv | table | timeline | stats | tags | progress | images | html | template`。

---

## 8. AJAX 表单保存 + 错误回填

```php
// 前端
echo XfAdmin::form([
    'action' => '/admin/users', 'method' => 'POST',
    'ajax'   => true, 'csrf' => true,
    'fields' => [ /* … */ ],
]);
```

```php
// 后端成功
return response()->json(['ok' => true, 'message' => '保存成功', 'url' => '/admin/users']);

// 后端校验失败（Laravel 自动 422）
return response()->json(['message' => '校验失败', 'errors' => ['email' => ['邮箱已被占用']]], 422);
```

**要点**：`ok`/`url`/`message` 必须在**顶层**；无 `url` 时可用
`data-xf-redirect` 或 `reload => true` 刷新。

---

## 9. 文件上传

```php
echo XfAdmin::upload([
    'name'    => 'attachment',
    'label'   => '附件',
    'driver'  => 'dropzone',          // native | dropzone | filepond
    'url'     => '/admin/upload',
    'multiple'=> true,
    'accept'  => '.jpg,.png,.pdf',
    'max_size'=> 10,                  // MB
]);
```

上传由插件直传；成功后把返回地址写入自己的隐藏字段。

---

## 10. 多步向导

```php
echo XfAdmin::wizard([
    'action' => '/admin/onboarding', 'method' => 'post', 'remote' => true,
    'steps'  => [
        ['title' => '基本信息', 'icon' => 'ti ti-user', 'content' => $step1],
        ['title' => '联系方式', 'icon' => 'ti ti-mail', 'content' => $step2],
        ['title' => '确认',     'icon' => 'ti ti-check', 'content' => $step3],
    ],
]);
```

```js
document.addEventListener('xf.wizard.change', e => console.log('当前步骤', e.detail.step));
document.addEventListener('xf.wizard.finish', e => console.log('提交'));
```

**要点**：「下一步」会先校验当前面板内控件的 `checkValidity()`。

---

## 11. 自定义单元格渲染器

```php
// PHP：声明 type
['key' => 'amount', 'label' => '金额', 'render' => ['type' => 'myMoney', 'prefix' => '￥']]
```

```js
// JS：注册
XFAdmin.registerCellRenderer('myMoney', function (d, row, cfg) {
    const n = Number(d || 0).toLocaleString('zh-CN', { minimumFractionDigits: 2 });
    return '<span class="text-success fw-semibold">' + (cfg.prefix || '') + n + '</span>';
});
```

或用全局函数（无需注册）：

```php
['key' => 'amount', 'render' => 'js:App.render.money']
```

---

## 12. 自定义确认 / 提示

```js
XFAdmin.confirm('确认删除？', () => {
    XFAdmin.request('/admin/users/1', { method: 'DELETE' })
        .then(r => { if (r.ok) XFAdmin.reloadTable('#user-table'); });
});

XFAdmin.toast({ body: '操作成功', variant: 'success' });

XFAdmin.prompt('请输入驳回原因', reason => {
    XFAdmin.request('/admin/orders/1/reject', { method: 'POST', data: { reason } });
});
```

---

## 13. 弹窗中加载远程页面

```php
// 触发按钮
echo XfAdmin::button([
    'label' => '新增用户',
    'icon'  => 'ti ti-plus',
])->attr('data-xf-page-dialog', '/admin/users/create')
  ->attr('data-xf-size', 'lg')
  ->attr('data-xf-title', '新增用户')
  ->attr('data-xf-table', '#user-table');
```

服务端响应中用 `[data-xf-page-content]` 标记要注入的片段：

```html
<div data-xf-page-content>
    <!-- 表单 -->
</div>
```

未标记时回退 `main` / `.content-page` / `.card` / `.container-fluid`。

---

## 14. 主题切换与持久化

```php
// 服务端输出初始主题
XfAdmin::page(['theme' => ['mode' => 'dark', 'menu_color' => 'dark'], …]);
```

```js
// 运行时切换（由 config.js / app.js 处理，写入 sessionStorage）
document.documentElement.setAttribute('data-bs-theme', 'dark');
```

定制面板（`#theme-settings-offcanvas`）的 radio `name` 与 `<html data-*>` 属性一一对应，
持久化键为 `sessionStorage['__INSPINIA_CONFIG__']`。

---

## 15. 权限菜单

组件不处理权限，请在生成菜单数组时过滤：

```php
function buildMenu(array $menu, array $perms): array
{
    $out = [];
    foreach ($menu as $item) {
        if (! empty($item['children'])) {
            $item['children'] = buildMenu($item['children'], $perms);
            if ($item['children'] === [] && empty($item['url'])) {
                continue;
            }
        } elseif (! empty($item['permission']) && ! in_array($item['permission'], $perms, true)) {
            continue;
        }
        $out[] = $item;
    }
    return $out;
}

XfAdmin::page(['menu' => buildMenu($menu, $userPermissions), …]);
```

---

## 16. 图表随主题重绘

图表组件已内置：`app.js` 用 `MutationObserver` 监听 `<html data-skin>` 与
`data-bs-theme`，自动 `rerenderAll()`。自定义图表可复用：

```js
XFAdmin.register('apexchart', function (el, cfg) {
    // 未指定 theme 时跟随 data-bs-theme
    const theme = cfg.theme || document.documentElement.getAttribute('data-bs-theme') || 'light';
    const chart = new ApexCharts(el, Object.assign({ theme: { mode: theme } }, cfg));
    chart.render();
    return chart;
});
```

---

## 17. 打印（发票 / 单据）

```php
echo XfAdmin::invoiceView(['invoice' => $invoice, 'items' => $items]);
echo XfAdmin::invoicePrintButton();   // 输出 [data-xf="print"] 按钮
```

```html
<!-- 指定打印区域 -->
<div data-xf="print" data-xf-config='{"target":"#invoice-area"}'>打印</div>
```

**要点**：打印时给 `body` 加 `.xf-printing`，仅打印 `.xf-invoice-print-area`，
避免用户直接 Ctrl+P 时样式错乱。

---

## 18. 国际化（前端文案）

```js
XFAdmin.i18n.set('en');                  // 切换（localStorage['xfadmin.lang']）
XFAdmin.i18n.set('');                    // 恢复原文
document.addEventListener('xf:lang-changed', e => console.log(e.detail.lang));
```

```html
<span data-lang="user.name">用户名</span>
<button data-lang-code="en">English</button>
```

翻译文件默认位于 `../data/translations/{code}.json`（相对 `xfadmin.js`）。

---

## 19. 扩展自定义组件

```php
class TeamCard extends \zxf\XfAdmin\Components\Component
{
    protected function defaults(): array
    {
        return ['name' => null, 'role' => null, 'avatar' => null];
    }

    protected function html(): string
    {
        return '<div' . $this->attrs(['class' => 'team-card']) . '>'
            . '<img src="' . $this->e($this->img($this->get('avatar'))) . '" width="48" alt="">'
            . '<h6>' . $this->e($this->get('name')) . '</h6>'
            . '<small>' . $this->e($this->get('role')) . '</small>'
            . '</div>';
    }
}

XfAdmin::extend('teamCard', TeamCard::class);
echo XfAdmin::teamCard(['name' => '张三', 'role' => '前端']);
```

详见 [07-扩展机制](../02-核心架构/07-extending.md)。

---

## 20. AJAX 替换内容后重新初始化

```js
fetch('/admin/partial').then(r => r.text()).then(html => {
    XFAdmin.destroyWithin(container);   // 清理旧实例（防定时器/监听泄漏）
    container.innerHTML = html;
    XFAdmin.scan(container);            // 初始化新的 [data-xf]
});
```

---

## 21. 常用一行式操作

```js
XFAdmin.reloadTable('#user-table');                 // 刷新表格
XFAdmin.table('#user-table').ajax.reload();          // 取原生实例
XFAdmin.pageDialog('/admin/users/1/edit', { size: 'lg' });
XFAdmin.copyText('要复制的文本', '已复制');
XFAdmin.disposeModal('my-modal');                    // 清理弹窗残留
XFAdmin.load(['/css/x.css'], ['/js/x.js']).then(() => XFAdmin.scan());
```

---

## 22. 高频坑速查

| 现象 | 原因 | 处理 |
|---|---|---|
| 页面无样式 | 未输出 `head()`/资源 404 | 见 [问题排查 §1](../05-运维/04-troubleshooting.md#1-页面无样式--无交互) |
| 表格空白 | `window.DataTable` 未加载 | 检查资源 |
| 过滤无效 | 前端 name 与后端 filters 不对应 | 见 [§2](#2-过滤栏与后端-filters-配对) |
| AJAX 不跳转 | 后端未返回顶层 `url` | 见 [§8](#8-ajax-表单保存--错误回填) |
| 无 `_token` | `Form.csrf` 默认 `[]` | 传 `'csrf' => true` |
| 批量无 ids | 行数据缺 `id` 字段 | 用 `row_id` 或保证数据含 `id` |
| 弹窗内容整页 | 服务端未标记 `[data-xf-page-content]` | 见 [§13](#13-弹窗中加载远程页面) |
| 参数没生效 | 键名拼错 / 被 `+` 合并覆盖 | 打印 `->options()` 核对 |

完整清单见 [04-问题排查](../05-运维/04-troubleshooting.md)。

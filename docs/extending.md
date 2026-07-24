# 自定义与扩展

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 一、编写自定义组件

继承 `zxf\XfAdmin\Components\Component`，实现 `defaults()` 与 `html()`：

```php
namespace App\Admin\Components;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

class PriceTag extends Component
{
    protected function defaults(): array
    {
        return [
            'amount'   => 0,
            'currency' => '¥',
            'variant'  => 'primary',
        ];
    }

    // 声明依赖资源（可选）
    protected function assets(): array
    {
        return [];   // 如 ['apexcharts']
    }

    protected function html(): string
    {
        return '<span' . $this->attrs(['class' => Html::cls('badge bg-' . $this->e($this->get('variant')))]) . '>'
            . $this->e($this->get('currency')) . $this->e(number_format((float) $this->get('amount'), 2))
            . '</span>';
    }
}
```

### 组件基类可用方法

| 方法 | 说明 |
|------|------|
| `$this->get($key, $default)` | 读取选项 |
| `$this->set($key, $value)` | 设置选项 |
| `$this->e($value)` | HTML 转义输出 |
| `$this->raw($value)` | 原样输出（可为组件对象，自动渲染） |
| `$this->attrs(array $attrs)` | 生成属性字符串（合并用户传入的 `attributes`/`class`/`id`） |
| `$this->resolveId($prefix)` | 取用户 id 或生成唯一 id（并写入 attributes） |
| `$this->uid($prefix)` | 仅生成唯一 id，不写入 attributes |
| `Html::cls(...)` | 合并 class（支持条件数组 `['x' => true]`） |
| `Html::attrs(array)` | 纯属性字符串（不合并组件 attributes） |

### 注册到门面

```php
XfAdmin::extend('priceTag', \App\Admin\Components\PriceTag::class);

echo XfAdmin::priceTag(['amount' => 99.5]);
```

---

## 二、加载自定义资源

在自定义组件的 `assets()` 中返回内置句柄；额外的自有文件可在 `html()` 里直接登记到资源管理器（去重）：

```php
protected function html(): string
{
    XfAdmin::assets()
        ->plugin('jquery')                                 // 复用内置句柄
        ->css('/zxf/xfadmin/plugins/my-lib/my.css')     // 追加自有 CSS
        ->js('/zxf/xfadmin/plugins/my-lib/my.js')       // 追加自有 JS
        ->inlineJs('MyLib.init("#' . $this->resolveId('ml') . '")', 'mylib-' . $this->get('id'));

    return '<div id="' . $this->e($this->get('id')) . '"></div>';
}
```

> 同一 URL / 同一 inline key 多次登记只输出一次，天然去重。若需新增“内置句柄”，可向 `Assets::PLUGINS` 常量补充定义（PR 或本地覆盖）。

---

## 三、覆盖内置组件

`extend()` 使用相同名字即可覆盖：

```php
XfAdmin::extend('card', \App\Admin\Components\MyCard::class);
```

建议 `MyCard extends \zxf\XfAdmin\Components\UI\Card` 复用原逻辑。

---

## 四、全局配置

```php
// config/xfadmin.php
return [
    'assets_url' => '/zxf/xfadmin',   // 资源基础路径（可改为 CDN）
    'version'    => '1.0.0',             // 资源缓存版本号（?v=）
    'theme'      => [                     // 默认外观（运行时可被 config.js 持久化覆盖）
        'skin'   => 'classic',
        'mode'   => 'light',
        'layout' => 'vertical',
    ],
    'brand'      => [
        'name'      => '我的后台',
        'logo'      => 'logo.png',
        'logo_dark' => 'logo-dark.png',
    ],
    'defaults'   => [                     // 各组件默认值覆盖
        'dataTable' => ['page_length' => 20, 'language' => 'zh'],
        'card'      => ['class' => 'shadow-sm'],
    ],
];
```

`defaults` 中每个组件的默认值会与该组件 `defaults()` 合并，全局生效。

---

## 五、事件与前端 API

`xfadmin.js` 暴露全局对象 `window.XFAdmin`：

```js
// 注册自定义 data-xf 行为
XFAdmin.register('my-widget', function (el, config) {
    // el: DOM 元素；config: data-xf-config 解析出的对象
    // 返回的对象会挂到 el._xf.myWidget
});

// 手动初始化某个范围（Ajax 加载新内容后调用）
XFAdmin.init(document.querySelector('#new-content'));

// 便捷 API
XFAdmin.toast({ title: '成功', message: '已保存', variant: 'success' });
```

> Ajax 局部刷新后务必调用 `XFAdmin.init(container)`，让新插入的组件重新绑定。

---

## 六、表格交互事件与扩展点

包内的 `dataTable` 富单元格（输入框、开关、操作栏）通过**事件委托**统一处理：所有交互会在 DOM 上派发标准 `CustomEvent`，你只需监听即可接管任意行为，无需修改包源码。

### 单元格输入 `xf:cell-input`

`render: 'input'` 的输入框失焦/回车时触发。前端自动 `POST xfCellInput.url`，`body` 为 `{ id, field, value, value_type }`；后端返回非 2xx 时自动回滚为原值。

```html
<!-- 自动生成的标记（无需手写） -->
<input class="xf-cell-input" data-field="nickname" data-xf-event="cellInput" ...>
```

```js
document.addEventListener('xf:cell-input', function (e) {
    const { el, field, value, valueType, rowId } = e.detail;
    console.log('单元格保存', rowId, field, value);
    // 自定义保存逻辑；返回 false 可阻止默认 POST
});
```

### 状态开关 `xf:switch`

`render: 'switch'` 的开关切换时触发。前端自动 `POST xfSwitch.url`，`body` 为 `{ id, field, value }`；请求失败自动回滚开关状态。

```js
document.addEventListener('xf:switch', function (e) {
    const { el, field, value, rowId } = e.detail;
    // value 为新的开关值（'on' / 'off'）
});
```

### 行操作 `xf:action`

操作栏按钮（或任意带 `data-xf-act` 的元素）点击时触发。`act` 取值决定默认行为：

| act | 默认行为 | 是否派发事件 |
|-----|----------|--------------|
| `view` | 弹窗展示整行 | 否 |
| `link` | 跳转 `url`（`{id}` 占位） | 否 |
| `copy-row` | 复制整行 JSON | 否 |
| `ajax` | `fetch` 到 `url`，可选 `confirm`，成功后 toast + 重载表格 | 否 |
| `custom` | 仅派发 `xf:action` | **是** |

```html
<button data-xf-act="custom" data-xf-event="approve" data-xf-confirm="确认通过？">通过</button>
```

```js
// 监听默认（custom）行为
document.addEventListener('xf:action', function (e) {
    const { el, act, rowId, row } = e.detail;
    // 自定义逻辑（如打开自己的弹窗 / 调用 XFAdmin.request）
});

// 监听自定义事件名（data-xf-event 指定的名称优先）
document.addEventListener('approve', function (e) {
    const { el, rowId, row } = e.detail;
});
```

- `data-xf-confirm="提示语"`：点击先弹确认框，取消则不触发。
- 自定义 `data-xf-event` 会让该按钮以指定事件名派发，**同时**仍会触发 `xf:action`（除非你 `e.stopPropagation()` 或在监听里 `return false`）。

### 复制 `xf:copy`

任意带 `data-xf-copy`（复制元素文本）或 `data-xf-copy-value="xxx"`（复制指定值）的元素点击即复制，并 toast 提示。

```html
<span data-xf-copy class="xf-copy">点击复制这段文本</span>
<button data-xf-copy-value="sk-xxxx">复制 Token</button>
```

### 前端请求 `XFAdmin.request`

统一封装 `fetch`，**自动携带 CSRF**（读取 `<meta name="csrf-token">`），返回 JSON，失败自动 toast：

```js
const res = await XFAdmin.request('/api/cell/nickname', {
    method: 'POST',
    body: { id: 12, field: 'nickname', value: '新昵称' },
});
// res.ok / res.data
```

---

## 七、单元格渲染器扩展

所有前端单元格渲染器挂在 `XFAdmin.cellRenderers` 上，键名即列定义里的 `render` 值。

```js
// 注册自定义渲染器
XFAdmin.cellRenderers.scoreBar = function (cell, row, col) {
    const v = Number(row[col.key] ?? 0);
    return '<div class="progress"><div class="progress-bar" style="width:' + (v * 10) + '%"></div></div>';
};

// 列定义即可使用 'scoreBar'
// 'score' => ['label' => '评分', 'render' => 'scoreBar']
```

- `cell`：当前单元格 `<td>` DOM；`row`：整行数据；`col`：列配置对象（含 `key`、`xf*` 等）。
- 返回值直接作为单元格 HTML（已做上下文转义的用户数据请使用 `XFAdmin.escapeHtml`）。

你也可以覆盖内置渲染器（如 `input`、`switch`、`tags`），实现项目特有的交互样式。

---

## 八、前端 API 速查

`window.XFAdmin` 主要成员：

| 成员 | 说明 |
|------|------|
| `XFAdmin.init(el?)` | 初始化（或重新初始化）范围内组件；Ajax 局部刷新后调用 |
| `XFAdmin.register(name, fn)` | 注册自定义 `data-xf` 行为 |
| `XFAdmin.toast(opts)` | 顶部 toast 提示 |
| `XFAdmin.escapeHtml(str)` | HTML 转义（用户输入必过） |
| `XFAdmin.tpl(tpl, row)` | 模板插值，`'{name}'` → `row.name`，支持 `a.b.c` |
| `XFAdmin.csrf()` | 读取 CSRF token |
| `XFAdmin.request(url, opts)` | fetch 封装（自动 CSRF + toast + JSON） |
| `XFAdmin.copyText(text)` | 复制文本到剪贴板 |
| `XFAdmin.confirm(opts)` | 确认弹窗（返回 Promise） |
| `XFAdmin.dialog(opts)` | 通用弹窗（可嵌 HTML/表单） |
| `XFAdmin.viewRow(row)` | 整行数据只读弹窗 |
| `XFAdmin.cellRenderers` | 单元格渲染器注册表（可扩展/覆盖） |
| `XFAdmin.dtLanguage` | DataTables 中文语言包对象 |
| `XFAdmin.reloadTable(id, params?)` | 按条件重载指定表格 |

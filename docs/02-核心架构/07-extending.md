# 扩展机制

XfAdmin 提供四个层级的扩展点：PHP 组件、单元格渲染器、前端 widget、命令与事件。

## 1. 注册自定义 PHP 组件

### 1.1 编写组件类

必须继承 `zxf\XfAdmin\Components\Component`，实现 `defaults()` 与 `html()`：

```php
<?php

declare(strict_types=1);

namespace App\XfAdmin;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 团队成员卡
 *
 * XfAdmin::teamCard(['name' => '张三', 'role' => '前端', 'avatar' => 'users/1.jpg'])
 */
class TeamCard extends Component
{
    protected function defaults(): array
    {
        return [
            'name'    => null,
            'role'    => null,
            'avatar'  => null,
            'variant' => 'primary',
            'body'    => '',
        ];
    }

    protected function assets(): array
    {
        return [];                     // 需要时可返回 ['select2', 'jquery']
    }

    protected function html(): string
    {
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');

        $html  = '<div' . $this->attrs([
            'class' => Html::cls('card', 'team-card', 'border-' . $variant),
        ]) . '>';
        $html .= '<div class="card-body d-flex align-items-center gap-3">';
        $html .= '<img src="' . $this->e($this->img($this->get('avatar'))) . '" class="rounded-circle" width="48" height="48" alt="">';
        $html .= '<div>';
        $html .= '<h6 class="mb-0">' . $this->e($this->get('name')) . '</h6>';
        $html .= '<small class="text-muted">' . $this->e($this->get('role')) . '</small>';
        $html .= '</div></div>';
        $html .= $this->raw($this->get('body'));
        $html .= '</div>';

        return $html;
    }
}
```

### 1.2 注册

```php
use zxf\XfAdmin\XfAdmin;

XfAdmin::extend('teamCard', \App\XfAdmin\TeamCard::class);

// 之后即可使用
echo XfAdmin::teamCard(['name' => '张三', 'role' => '前端工程师']);
```

注册时机（Laravel）：服务提供者的 `boot()` 或 `AppServiceProvider`。

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    XfAdmin::extend('teamCard', \App\XfAdmin\TeamCard::class);
}
```

⚠️ `extend()` 校验：类必须 `is_subclass_of(Component::class)`，否则抛
`InvalidArgumentException`。

### 1.3 覆盖内置组件

传入已存在的别名即可：

```php
XfAdmin::extend('card', \App\XfAdmin\MyCard::class);
// 之后 XfAdmin::card() 全部走你的实现
```

推荐做法：继承内置类后只覆写需要改的部分：

```php
class MyCard extends \zxf\XfAdmin\Components\UI\Card
{
    protected function defaults(): array
    {
        return parent::defaults() + ['shadow' => true];
    }

    protected function html(): string
    {
        if ($this->get('shadow')) $this->addClass('shadow-sm');
        return parent::html();
    }
}
```

## 2. 带 JS 行为的组件

使用 `initAttrs()` 输出声明式初始化属性：

```php
protected function html(): string
{
    $id    = $this->resolveId('xf-team');
    $attrs = $this->initAttrs('teamCard', [
        'name' => $this->get('name'),
        'mode' => $this->get('mode'),
    ]);

    return '<div' . $this->attrs(['id' => $id, 'class' => 'team-card'] + $attrs) . '></div>';
}
```

前端注册同名 widget：

```js
XFAdmin.register('teamCard', function (el, cfg) {
    el.textContent = cfg.name || '';
    // 返回带 destroy 的实例（可选，用于 XFAdmin.destroy / destroyWithin）
    return {
        destroy() { /* 清理定时器、事件监听 */ }
    };
});
```

然后 `XFAdmin.scan()` 会自动初始化所有 `data-xf="teamCard"` 元素。

⚠️ 若组件需要**条件加载资源**，可在 `assets()` 中返回插件名：

```php
protected function assets(): array
{
    return $this->get('mode') === 'fancy' ? ['sortablejs'] : [];
}
```

若需加载**包外资源**，用：

```php
XfAdmin::assets()->css('css/my.css')->js('js/my.js');
```

## 3. 自定义表格单元格渲染器

### 3.1 后端注册（PHP 侧只声明 type 名）

```php
['key' => 'amount', 'label' => '金额', 'render' => ['type' => 'myMoney', 'prefix' => '￥']]
```

### 3.2 前端注册

```js
XFAdmin.registerCellRenderer('myMoney', function (d, row, cfg) {
    const prefix = cfg.prefix || '';
    const num = Number(d || 0).toLocaleString('zh-CN');
    return `<span class="text-success fw-semibold">${prefix}${num}</span>`;
});
```

⚠️ 覆盖内置渲染器会 `console.warn` 提示。

### 3.3 使用全局函数（`js:` 前缀）

```php
// PHP
['key' => 'amount', 'label' => '金额', 'render' => 'js:App.render.money']
```

```js
window.App = window.App || {};
App.render = {
    money(d, row, cfg) { return '￥' + Number(d).toFixed(2); }
};
```

支持点号路径（`App.render.money`）。

### 3.4 单元格事件

```php
['key' => 'name', 'label' => '姓名', 'render' => ['type' => 'text', 'event' => 'onNameClick']]
// 或多个事件
['key' => 'x', 'render' => ['type' => 'text', 'event' => ['click' => 'onX', 'dblclick' => 'onXDbl']]]
```

```js
XFAdmin.onCell('onNameClick', function (ctx) {
    // ctx: {event, el, row, value, field, originalEvent}
    console.log(ctx.row);
});
```

## 4. 自定义前端 widget

```js
XFAdmin.register('myWidget', function (el, cfg) {
    const timer = setInterval(() => tick(el), 1000);

    // 返回实例；销毁时优先调用 destroy()
    return {
        destroy() { clearInterval(timer); }
    };
}, function (el, inst) {
    // 可选：独立的 destroyFn（第三参）
    if (inst && inst.destroy) inst.destroy();
});
```

使用：

```html
<div data-xf="myWidget" data-xf-config='{"a":1}'></div>
```

动态内容：

```js
XFAdmin.destroyWithin(oldNode);          // 清理旧节点（防内存泄漏）
container.innerHTML = newHtml;
XFAdmin.scan(container);                 // 初始化新节点
```

## 5. 扩展命令面板

```js
XFAdmin.initCommandPalette({ id: 'my-cmd', hotkey: 'meta+k' });

XFAdmin.onCommand('createUser', function (item, ev) {
    XFAdmin.createPage('/admin/users/create');
});
```

```html
<div class="xf-cmd-item" data-action="createUser">新建用户</div>
<div class="xf-cmd-item" data-url="/admin/orders">订单管理</div>
```

## 6. 扩展上传回调

```js
XFAdmin.initDropzone({ id: 'my-upload', initial: [] });

XFAdmin.onUpload('my-upload', function (res, files) {
    console.log('上传完成', res);
});
```

## 7. 扩展资源（自定义插件）

临时追加：

```php
XfAdmin::assets()
    ->css('css/my.css')
    ->js('js/my.js')
    ->inlineJs('MyApp.init()', 'my-app-init');
```

永久注册：在 `Assets::PLUGINS` 中追加条目，并把文件放入 `resources/assets/plugins/<name>/`。
改完后运行自检：

```bash
php tools/selftest/asset_check.php
```

## 8. 监听内置事件

```js
// 行操作（任意 data-xf-act）
document.addEventListener('xf:action', e => {
    console.log(e.detail.action, e.detail.row, e.detail.el);
});

// 弹窗关闭
document.addEventListener('xf:dialog-closed', e => {
    if (e.detail.saved) XFAdmin.reloadTable(e.detail.tableId);
});

// 自定义过滤控件取值
document.addEventListener('xf:filter-custom', e => {
    e.detail.getValue(myWidget.value);     // 必须调用 getValue 回传
});

// 单元格开关 / 输入
document.addEventListener('xf:switch', e => console.log(e.detail.checked));
document.addEventListener('xf:cell-input', e => console.log(e.detail.value));

// 看板拖拽
document.addEventListener('xf.kanban.move', e => console.log(e.detail.from, e.detail.to));

// 向导
document.addEventListener('xf.wizard.finish', e => console.log('提交', e.detail.step));
```

完整事件表见 [06-前端 JS API](../04-进阶指南/06-javascript-api.md#4-事件全表)。

## 9. 扩展检查清单

| 扩展类型 | 后端 | 前端 |
|---|---|---|
| 新组件 | `XfAdmin::extend()` + 继承 `Component` | 需要交互时 `XFAdmin.register()` |
| 覆盖组件 | `XfAdmin::extend(已有别名)` | — |
| 单元格渲染器 | 列 `render => ['type' => 'x']` | `XFAdmin.registerCellRenderer()` |
| 单元格事件 | 列 `render => ['event' => 'name']` | `XFAdmin.onCell()` |
| 命令 | — | `XFAdmin.onCommand()` |
| 上传 | — | `XFAdmin.onUpload()` |
| 资源 | `Assets::PLUGINS` 或 `assets()->css/js()` | `XFAdmin.load()` 动态加载 |

## 10. 完整示例：一个带 CRUD 交互的自定义组件

```php
// app/XfAdmin/StatusPill.php
class StatusPill extends Component
{
    protected function defaults(): array
    {
        return ['value' => null, 'map' => [], 'default' => 'secondary'];
    }

    protected function html(): string
    {
        $map   = (array) $this->get('map');
        $value = (string) $this->get('value');
        $color = $this->enum($map[$value] ?? $this->get('default'),
            [...self::ENUM_VARIANT, 'secondary'], 'secondary');

        return '<span' . $this->attrs(['class' => "badge bg-{$color}-subtle text-{$color}"]) . '>'
            . $this->e($value) . '</span>';
    }
}
```

```php
XfAdmin::extend('statusPill', \App\XfAdmin\StatusPill::class);

echo XfAdmin::statusPill([
    'value' => 'active',
    'map'   => ['active' => 'success', 'banned' => 'danger'],
]);
```

下一步：[01-页面与布局](../04-进阶指南/01-page-layout.md)

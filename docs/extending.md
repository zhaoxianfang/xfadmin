# 自定义与扩展

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 一、编写自定义组件

继承 `XfAdmin\Components\Component`，实现 `defaults()` 与 `html()`：

```php
namespace App\Admin\Components;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

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
        ->css('/vendor/xfadmin/plugins/my-lib/my.css')     // 追加自有 CSS
        ->js('/vendor/xfadmin/plugins/my-lib/my.js')       // 追加自有 JS
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

建议 `MyCard extends \XfAdmin\Components\UI\Card` 复用原逻辑。

---

## 四、全局配置

```php
// config/xfadmin.php
return [
    'assets_url' => '/vendor/xfadmin',   // 资源基础路径（可改为 CDN）
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

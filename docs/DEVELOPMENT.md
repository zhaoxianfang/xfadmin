# 组件开发指南

> 文档导航：[README](../README.md) · [组件总览](components.md) · [扩展组件](extending.md) · [安全规范](security.md) · [资源机制](assets.md) · [测试](TESTING.md)

本文是 xfadmin 组件开发者的完整手册：从写一个组件、声明资源、绑定前端事件，到本地自测、接入文档生成与发布到 wsf 演示。

---

## 一、组件生命周期

每个组件都是一个继承自 `zxf\XfAdmin\Components\Component` 的 PHP 类。渲染流程：

```
XfAdmin::card($options)
  └─> new Card($options)            // 构造函数合并 defaults()
        └─> setConfig()             // 注入全局 config (brand/theme/...)
        └─> prepare()               // 预处理钩子（子类可覆盖）
        └─> html()                  // 子类实现，返回 HTML 字符串
        └─> __toString()            // 输出最终 HTML + 登记资源
```

组件对象 `(string) $c` 或 `echo $c` 时触发渲染；在另一个组件的 `body` / `content` 中塞入组件对象会被自动 `(string)` 展开。

---

## 二、Component 基类可用方法

在子类的 `html()`（或 `prepare()`）中可调用：

| 方法 | 说明 |
|------|------|
| `$this->get($key, $default)` | 读取选项（默认值为 `defaults()` 中的值，再回退 `$default`） |
| `$this->set($key, $value)` | 写入/覆盖选项（常用于 `prepare()` 中派生值） |
| `$this->has($key)` | 判断选项是否存在且非 null |
| `$this->e($value)` | HTML 转义输出（**所有动态文本必走此路**，防 XSS） |
| `$this->raw($value)` | 原样输出；若值为组件对象则自动 `(string)` 渲染 |
| `$this->attrs(array $attrs)` | 生成属性串，自动合并 `id` / `class` / `attributes` / 组件内部 class |
| `$this->resolveId($prefix)` | 取用户传入 `id` 或生成唯一 id（写入 attributes，供 JS 定位） |
| `$this->img($path)` | 组件上下文图片解析：外链/data URI 原样返回，其余按 `asset('images/...')` |
| `$this->uid` | 当前组件唯一 id（已 resolve） |
| `$this->config($key, $default)` | 读取全局 `config/xfadmin.php` 中的值 |

### 资源声明

```php
protected function assets(): array
{
    return ['apexcharts', 'datatables', 'tagify']; // 已注册在 Assets::PLUGINS 的 key
}
```

页面只会为**实际用到**的组件加载这些资源，且同页去重（见 [资源机制](assets.md)）。

### 前端交互绑定

约定：组件在 HTML 上挂 `data-xf-*` 属性，由 `resources/assets/js/xfadmin.js` 中的统一委托处理。

```html
<button data-xf-confirm="确定删除？" data-xf-ajax="/api/del" data-xf-method="POST">删除</button>
```

新增交互请在 `xfadmin.js` 的对应 `bind*()` 模块中以 **document 委托** 实现，避免逐实例绑定（组件可任意嵌套/重复）。

---

## 三、标准组件骨架

```php
<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

class BadgeNew extends Component
{
    protected function defaults(): array
    {
        return [
            'text'    => '',
            'variant' => 'primary',  // 枚举字段必须 e()
            'pill'    => false,
        ];
    }

    protected function prepare(): void
    {
        // 派生值示例：pill 转 class
        $this->set('class_pill', $this->get('pill') ? 'rounded-pill' : '');
    }

    protected function assets(): array
    {
        return []; // 无第三方依赖
    }

    protected function html(): string
    {
        $variant = $this->e($this->get('variant')); // 枚举字段转义防注入
        $text    = $this->e($this->get('text'));     // 文本字段转义防 XSS
        $cls     = Html::cls(['badge', 'bg-' . $variant, $this->get('class_pill')]);

        return '<span' . $this->attrs(['class' => $cls]) . '>' . $text . '</span>';
    }
}
```

---

## 四、注册组件

在 `src/XfAdmin.php` 的对应分类数组中加入一行：

```php
'UI' => [
    // ...
    'badgeNew' => Components\UI\BadgeNew::class,
],
```

> **PHP 保留字陷阱**：`switch` / `empty` / `list` 等是 PHP 保留字，**绝不能作为类名**。如确需这些别名，用别的类名（如 `Toggle` / `EmptyState`）并在注册表中用 `'switch' => ...` 保留别名（见 `src/XfAdmin.php` 注释）。

子类可覆盖父类的 `aliases()` 提供多别名：

```php
protected static function aliases(): array
{
    return ['toggle' => self::class, 'switch' => self::class];
}
```

---

## 五、转义与安全（强制）

详见 [安全规范](security.md)。要点：

1. **文本展示字段**（`title` / `label` / `text` / `name` / `value` / `message` / …）一律 `$this->e()`。
2. **结构性枚举字段**（`variant` / `type` / `size` / `style` / `placement` / `tag` / `align` / …）拼 class 或进属性值时也一律 `$this->e()`，防 `"` 逃逸注入。
3. **内联 JSON** 走 `Html::scriptJson()`（自动转义 `<` / `>` / `&` / 单双引号 / 换行，杜绝 `</script>` 注入）。
4. **内容容器**（`body` / `content` / `slot` / `footer` / `left` / `right` 等）可 `raw()`，但调用方需保证可信。

---

## 六、本地自测

组件写完后，在包内做三道护栏（详见 [TESTING.md](TESTING.md)）：

```bash
php tools/selftest/build.php          # 渲染全部组件到 .build/，含你的新组件
php tools/selftest/xss_audit.php       # 模糊注入审计，确认无未转义 XSS
php tools/selftest/asset_check.php     # 校验 assets() 引用的 key 均已注册且文件存在
```

Playwright 端到端（需本地 node + playwright，外部请求会被拦截，离线可跑）：

```bash
cd tools/selftest && bash run.sh
```

---

## 七、接入文档生成

`tools/gen_category_docs.php` 与 `tools/gen_docs.php` 会扫描 `XfAdmin.php` 注册表自动生成文档，**无需手写**。你的组件只要：

- 类有 PHPDoc（`@description` 或类注释首行会被提取为描述）
- `defaults()` 返回带类型与说明的数组（注释 `// 说明` 会被提取）

重新生成：

```bash
php tools/gen_category_docs.php
php tools/gen_docs.php
php tools/gen_category_docs.php >/dev/null && php tools/gen_docs.php >/dev/null && echo OK
```

---

## 八、同步到 wsf 演示

改完 `src/` 与 `resources/` 后，**必须**同步到 wsf 的 composer 包才对演示生效：

```bash
rsync -a --delete src/ /Users/aha/www/wsf/vendor/zxf/xfadmin/src/
rsync -a --delete resources/ /Users/aha/www/wsf/vendor/zxf/xfadmin/resources/
```

注意：wsf 的 `public/` 下**不发布** xfadmin 资源——资源由 `XfAdminServiceProvider` 的 `AssetController` 直接从 vendor 包内 `resources/assets` 托管。改完资源后同样需要上面的 `rsync`。

---

## 九、常见问题

**Q：组件里想用 Bootstrap Icons / Tabler 图标？**
统一使用 Tabler 字体图标 `<i class="ti ti-xxx"></i>`（Topbar 已全量切换为 `ti`，不再用 `data-lucide`）。

**Q：组件需要 JS 初始化，但页面是异步加载的？**
`xfadmin.js` 的 `bind*()` 用 document 委托 + `MutationObserver`（如适用），新插入的 DOM 无需手动重绑。

**Q：图片路径怎么写？**
优先用 `$this->img($path)`（组件上下文）。纯静态上下文用 `XfAdmin::img($path)`。两者行为一致：外链/data URI 原样返回，其余按 `images/` 解析。

**Q：如何避免和 INSPINIA 框架样式冲突？**
新样式写在 `resources/assets/css/xfadmin.css`（**最后加载**，可覆盖框架）。新增类前先确认 `app.min.css` 是否已定义同名类——已定义的一律**不要重写**（尤其 position/width/margin/z-index/background/padding/top/right），只补充框架未定义的属性（见 [风格对齐](STYLE_ALIGNMENT.md)）。

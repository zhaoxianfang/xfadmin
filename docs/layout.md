# 布局与页面

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 整页组件 `page`

`XfAdmin::page()` 生成从 `<!DOCTYPE html>` 到 `</html>` 的完整页面，自动包含 `<head>` 资源、侧栏/顶栏、内容区、页脚、主题定制面板与页尾脚本。

```php
echo XfAdmin::page([
    'title'      => '仪表盘',            // <title> 与页面标题
    'layout'     => 'vertical',          // 布局模式，见下
    'lang'       => 'zh-CN',
    'theme'      => 'light',             // light | dark
    'menu'       => $menu,               // 菜单数组
    'user'       => ['name' => '张三', 'role' => '管理员', 'avatar' => 'users/avatar-1.jpg'],
    'notifications' => ['items' => [...]],
    'page_title' => ['title' => '仪表盘', 'breadcrumb' => [...]],  // 传 false 关闭
    'customizer' => true,                // 是否显示右侧定制面板
    'content'    => $bodyHtml,           // 主体内容
    'footer'     => ['copyright' => '© 2026 公司'],
]);
```

### 布局模式 `layout`

| 值 | 说明 | 对应模板 |
|----|------|----------|
| `vertical` | 默认左侧栏垂直布局 | index.html |
| `horizontal` | 顶部水平导航，无侧栏 | layouts-horizontal.html |
| `boxed` | 居中定宽容器 | layouts-boxed.html |
| `compact` | 紧凑侧栏（图标+悬浮展开） | layouts-compact.html |
| `scrollable` | 固定头部、内容滚动 | layouts-scrollable.html |

> `horizontal` 布局自动渲染 `topnav` 而非 `sidenav`；其它布局渲染 `sidenav`。

### 主题属性

`page` 会在 `<html>` 上输出模板所需的 `data-bs-theme`、`data-menu-color`、`data-topbar-color`、`data-layout` 等属性，与 INSPINIA 客制化面板完全兼容。可通过 `html_attrs` 覆盖：

```php
XfAdmin::page([
    'html_attrs' => ['data-sidenav-size' => 'condensed', 'data-menu-color' => 'dark'],
]);
```

---

## 菜单数据结构

`menu` / `sidenav.menu` / `topnav.menu` 共用同一结构，支持**无限层级**：

```php
$menu = [
    ['title' => '主导航'],                              // 分组标题
    [
        'text'  => '仪表盘',
        'icon'  => 'ti ti-dashboard',
        'url'   => '/',
        'active'=> true,                                // 高亮当前项
        'badge' => ['text' => '5', 'class' => 'bg-success'],
    ],
    [
        'text' => '电商',
        'icon' => 'ti ti-shopping-cart',
        'children' => [                                 // 子菜单（可继续嵌套 children）
            ['text' => '商品', 'url' => '/products'],
            ['text' => '订单', 'url' => '/orders', 'children' => [
                ['text' => '待付款', 'url' => '/orders/unpaid'],
            ]],
        ],
    ],
];
```

自动高亮：传 `current_url`，组件会匹配 `url` 并自动展开父级、标记 active。

```php
XfAdmin::menu(['items' => $menu, 'current_url' => request()->path()]);
```

---

## 手动组合布局

若不用 `page`，可自由拼装：

```php
echo '<!DOCTYPE html><html><head>';
echo XfAdmin::assets()->head();                 // 资源自动注入
echo '</head><body>';
echo XfAdmin::sidenav(['menu' => $menu]);
echo '<div class="content-page"><div class="content"><div class="container-fluid">';
echo XfAdmin::pageTitle(['title' => '页面']);
echo $yourContent;
echo '</div></div>';
echo XfAdmin::footer();
echo '</div>';
echo XfAdmin::assets()->scripts();              // 脚本自动注入（已去重）
echo '</body></html>';
```

---

## 认证页 / 错误页 / 特殊页

```php
// 登录页
echo XfAdmin::authPage([
    'title'   => '登录',
    'content' => XfAdmin::form([
        'fields' => [
            XfAdmin::input(['name' => 'email', 'label' => '邮箱', 'type' => 'email']),
            XfAdmin::input(['name' => 'password', 'label' => '密码', 'type' => 'password']),
            XfAdmin::button(['label' => '登录', 'type' => 'submit', 'variant' => 'primary', 'class' => 'w-100']),
        ],
    ]),
]);

// 404
echo XfAdmin::errorPage(['code' => '404', 'title' => '页面不存在', 'home_url' => '/']);

// 即将上线（倒计时）
echo XfAdmin::comingSoon(['heading' => '即将上线', 'deadline' => '2026-12-31 00:00:00']);

// 维护中
echo XfAdmin::maintenance(['contact' => 'support@example.com']);
```

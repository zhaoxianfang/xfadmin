# 主题与外观定制

> 文档导航：[README](../README.md) · [布局](layout.md) · [风格对齐](STYLE_ALIGNMENT.md) · [资源机制](assets.md)

xfadmin 的外观通过三层叠加定制，从最稳定到最灵活：

1. **后端配置**（`config/xfadmin.php` 的 `brand` / `theme`）—— 决定初始外观
2. **前端持久化**（`resources/assets/js/config.js` + `localStorage`）—— 用户切换主题/布局后浏览器端记忆
3. **自定义 CSS**（`xfadmin.css` 末尾覆盖，或项目自己的样式表）—— 项目级品牌微调

---

## 一、后端配置（初始外观）

`config/xfadmin.php` 关键键：

```php
'brand' => [
    'name'        => 'XfAdmin',   // 顶栏/登录页品牌名
    'logo'        => 'images/logo.svg',
    'logo_light'  => 'images/logo-light.svg',
    'favicon'     => 'images/favicon.ico',
],

'theme' => [
    'default'      => 'light',       // light | dark
    'default_size' => 'fluid',       // fluid | boxed
    'menu_color'   => 'dark',        // dark | light（侧边栏配色）
    'layout'       => 'vertical',    // vertical | horizontal（侧边栏 | 顶部导航）
],
```

这些值会被注入到页面的 `<head>`，`config.js` 初始化时读取它们作为初值，再被 `localStorage` 中的用户偏好覆盖。

---

## 二、前端持久化（用户偏好）

`resources/assets/js/config.js` 负责：

- 在首屏同步读取 `localStorage`（key 形如 `xf-theme-*`）
- 提供切换接口（明/暗主题、布局方向、侧边栏配色、盒装/流式、语言）
- 切换时即时改写 `<body>` / `<html>` 的 `data-*` 属性并写回 `localStorage`

切换控件已内置在 `customizer` 组件与 `topbar` / `topNav` 的主题按钮（`#light-dark-mode`）中，无需额外接线。

`topbar` 的 `#light-dark-mode`、侧边栏抽屉 `.sidenav-toggle-button`、全屏 `[data-toggle=fullscreen]`、子菜单 `.side-nav-item` 均由 `xfadmin.js` 的 chrome IIFE 在首屏处理，**无外部依赖**。

---

## 三、明暗主题切换原理

明暗并非两套 CSS，而是通过根节点属性驱动 CSS 变量：

```css
:root { --xf-bg: #fff; --xf-text: #222; }
[data-bs-theme="dark"] { --xf-bg: #1e1e2d; --xf-text: #e4e4ef; }
```

`config.js` 切换时设置 `<html data-bs-theme="dark">`，所有引用变量的组件自然换肤。新增组件请尽量使用 `var(--xf-*)` 而非硬编码颜色，以自动支持明暗。

---

## 四、项目级品牌微调

项目如需微调（不打算改包内文件），在**自己页面**加载顺序最后引入一段 CSS 覆盖即可。注意**不要覆盖** INSPINIA 框架已定义的类（如 `.card` / `.dropdown-menu` / `.modal-content`），只补框架未定义的 `xf-*` 类或项目自有类。

### 何时改包内 `xfadmin.css`？

当你要让**所有使用该包的页面**共享同一微调（例如统一某个业务组件的新样式），应改 `resources/assets/css/xfadmin.css`。改后记得同步到 wsf（见 [组件开发指南](DEVELOPMENT.md#八同步到-wsf-演示)）。

---

## 五、自定义配色方案

若需脱离 Bootstrap 默认调色板，推荐做法：

1. 在 `xfadmin.css` 顶部或 `:root` 覆盖 Bootstrap 的 `--bs-primary` 等变量：
   ```css
   :root { --bs-primary: #6f42c1; --bs-primary-rgb: 111,66,193; }
   ```
2. 所有组件使用 `bg-primary` / `text-primary` / `btn-primary` 等标准类即可整体换色，无需逐个组件改。

> ⚠️ 改 `--bs-*` 变量会影响全站；请在测试环境验证对比度与可访问性。

---

## 六、布局方向：侧边栏 vs 顶部导航

`theme.layout`：

- `vertical` → 使用 `sidenav` + `topbar`（侧边栏 + 顶栏）
- `horizontal` → 使用 `topNav`（顶部导航，支持无限层级子菜单与 Mega Menu）

切换时 `config.js` 改写 `<body>` 的 `data-layout` 类；两种布局的 CSS 与 JS 均已自包含（见 `Menu.php` 的 top 模式与 `xfadmin.js` 的 `placeMenu` 边界避让）。

---

## 七、常见定制场景

**场景 A：改品牌名与 Logo**
改 `config/xfadmin.php` 的 `brand.name` / `brand.logo`，或在调用 `XfAdmin::page()` 时传 `brand` 覆盖。

**场景 B：默认进入暗色模式**
`theme.default = 'dark'`。

**场景 C：默认顶部导航布局**
`theme.layout = 'horizontal'`。

**场景 D：锁定用户不可改主题**
不渲染 `customizer` 组件，且 `config.js` 初始化时传 `lockTheme: true`（在 `page()` 的 `theme` 选项中关闭 customizer 即可）。

**场景 E：项目自定义字体/圆角**
在 `xfadmin.css` 覆盖 `--xf-font-family` / `--xf-radius`（如已定义），或在 `config.js` 注入 CSS 变量。

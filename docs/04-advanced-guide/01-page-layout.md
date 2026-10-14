# 页面与布局

`XfAdmin::page()` 是**所有后台页面的入口**，输出完整 HTML 文档。

> 源码：`src/Components/Layout/Page.php`

## 1. 完整参数

| 参数 | 类型 | 默认 | 说明 |
|---|---|---|---|
| `lang` | string | `'zh-CN'` | `<html lang>` |
| `title` | string | `''` | `<title>`（转义） |
| `description` | ?string | `null` | `<meta name="description">` |
| `keywords` | ?string | `null` | `<meta name="keywords">` |
| `author` | ?string | `null` | `<meta name="author">` |
| `favicon` | ?string | `null` | 回退 `setting('brand.favicon')` → `images/favicon.ico` |
| `layout` | string | `'vertical'` | `'horizontal'` / `'topnav'` 触发水平布局 |
| `theme` | array | `[]` | 与全局 `theme` 合并后输出 `<html data-*>` |
| `menu` | array | `[]` | 菜单数据，自动下发给 Sidenav / TopNav |
| `current_url` | ?string | `null` | 自动高亮当前菜单项 |
| `sidenav` | array\|false | `[]` | Sidenav 配置；`false` 关闭 |
| `topbar` | array\|false | `[]` | Topbar 配置；`false` 关闭 |
| `topnav` | ?array | `null` | TopNav 配置；非 `null`/非 `false` 即启用水平布局 |
| `page_title` | array\|string\|null | `null` | 数组 → `PageTitle` 组件；字符串 → 转义输出 |
| `content` | mixed | `''` | 内容区（`raw()` 输出） |
| `container` | string | `'container-fluid'` | 内容区容器 class（传 `''` 去掉） |
| `footer` | array\|false | `[]` | `false` 关闭页脚 |
| `customizer` | bool | `true` | 是否渲染右侧主题定制面板 |
| `preloader` | bool | `false` | 输出 `#preloader > #status > .spinner` |
| `head` | ?string | `null` | `</head>` 前附加内容（`raw`） |
| `scripts` | ?string | `null` | `</body>` 前附加内容（`raw`） |
| `body_class` | ?string | `null` | `<body class>` |
| `csrf` | ?string | `null` | 显式 CSRF 令牌；`null` 时若存在 `csrf_token()` 自动注入 |

⚠️ **不存在的参数**（调用方常误传）：`dir`、`meta`、`brand`、`head_extra`、`styles`。
品牌与页脚默认值来自**全局配置** `XfAdmin::setting('brand.*')` / `setting('footer.*')`。

## 2. 渲染顺序

```
1) 组装 $body（此过程中所有组件完成资源注册）
   <div class="wrapper">
     TopNav                      ← 水平布局
     或 Sidenav + Topbar         ← 垂直布局
     <div class="content-page">
       <div class="{container}">
         {page_title}
         {content}
       </div>
       {footer}
     </div>
   </div>
   {customizer}

2) 组装文档
   <!DOCTYPE html>
   <html {theme attrs} lang="...">
   <head>
     <meta charset="utf-8">
     <title>{title}</title>
     <meta name="viewport">
     [description / keywords / author]
     [<meta name="csrf-token">]
     <link rel="shortcut icon">
     {Assets::head()}
     {head}
   </head>
   <body [class]>
     [#preloader]
     {body}
     {Assets::scripts()}
     {scripts}
   </body>
   </html>

3) Assets::resetCollected()
```

⚠️ **必须先渲染 body 再输出 head** —— 组件在渲染时注册 CSS/JS。

## 3. 主题属性输出

```php
$themeDefaults = [
    'skin' => 'modern', 'mode' => 'light', 'layout_position' => 'fixed',
    'layout_width' => 'fluid', 'topbar_color' => 'light',
    'menu_color' => 'gradient', 'sidenav_size' => 'default',
];
$theme = array_replace($themeDefaults, XfAdmin::setting('theme', []), $this->get('theme', []));
```

| theme 键 | 输出属性 | 备注 |
|---|---|---|
| — | `lang="{lang}"` | 固定 |
| `skin` | `data-skin` | classic/material/modern/saas/flat |
| `mode` | `data-bs-theme` | light/dark/system |
| `layout_position` | `data-layout-position` | fixed/scrollable |
| `layout_width` | `data-layout-width` | **等于 `fluid` 时省略** |
| `topbar_color` | `data-topbar-color` | light/dark/gray/gradient |
| `menu_color` | `data-menu-color` | light/dark/gray/gradient/image |
| `sidenav_size` | `data-sidenav-size` | default/compact/condensed/on-hover/on-hover-active/offcanvas/full/fullscreen |
| `sidenav_user` | `data-sidenav-user="true"` | 仅真值输出 |
| 水平布局 | `data-layout="topnav"` | 并 unset `data-sidenav-size`、`data-sidenav-user` |

⚠️ 这些属性**必须显式输出**：模板 `config.js` 把「属性缺失」视为
`skin=modern` / `menu.color=gradient`，缺了会与前端脚本回落不一致。

## 4. 水平布局（三种触发方式）

```php
$isHorizontal = in_array($layout, ['horizontal', 'topnav'], true)
    || ($this->get('topnav') !== null && $this->get('topnav') !== false);
```

```php
// 方式一
XfAdmin::page(['layout' => 'horizontal', 'menu' => $menu, ...]);

// 方式二
XfAdmin::page(['layout' => 'topnav', 'menu' => $menu, ...]);

// 方式三（推荐，可传 TopNav 自己的配置）
XfAdmin::page([
    'topnav' => ['menu' => $menu, 'search' => true, 'languages' => [...]],
    ...
]);
```

水平布局下**不渲染 Sidenav 与 Topbar**（TopNav 已内含品牌/搜索/用户等模块）。

## 5. PageTitle（页面标题区）

```php
'page_title' => [
    'title'      => '用户管理',
    'breadcrumb' => [
        ['text' => '首页', 'url' => '/'],
        ['text' => '系统'],                      // 字符串元素自动包成 ['text'=>…]
        ['text' => '用户管理', 'active' => true],
    ],
],
// 或用 actions 替代面包屑
'page_title' => [
    'title'   => '用户管理',
    'actions' => XfAdmin::button(['label' => '新增', 'icon' => 'ti ti-plus']),
],
```

- 面包屑**最后一项自动 active**；
- `active` 或无 `url` 时输出 `<li class="breadcrumb-item active">`，否则输出 `<a>`（`safeUrl`）；
- 容器：`div.page-title-head.d-flex.align-items-center`。

## 6. Footer

```php
'footer' => [
    'text'  => '© 2026 我的公司',        // 纯文本语义，e() 转义
    'right' => '<a href="/help">帮助</a>', // HTML 语义，raw() 输出
],
```

默认值：`text` → `setting('footer.text')` → `© {年} {brand.name}`。

## 7. Customizer（主题定制面板）

```php
'customizer' => false,   // 关闭
```

面板结构：`<div class="offcanvas offcanvas-end" id="theme-settings-offcanvas">`，
内含 6 组 radio，其 `name` 与模板 `app.js` **强耦合，不得改名**：

| 分组 | `name` | 可选值 |
|---|---|---|
| 皮肤 | `data-skin` | classic / material / modern / saas / flat / minimal |
| 明暗 | `data-bs-theme` | light / dark / system |
| 顶栏色 | `data-topbar-color` | light / dark / gray / gradient |
| 菜单色 | `data-menu-color` | light / dark / gray / gradient / image |
| 侧栏尺寸 | `data-sidenav-size` | default / compact / condensed / on-hover / on-hover-active / offcanvas |
| 布局位置 | `data-layout-position` | fixed / scrollable |
| 侧栏用户 | `sidebar-user` | checkbox `#sidebaruser-check` |
| 重置 | 按钮 `#reset-layout` | |

配置持久化在 `sessionStorage['__INSPINIA_CONFIG__']`（由 `config.js` / `app.js` 管理）。

## 8. Landing（落地页）

内部委托 `XfAdmin::page()`，固定关闭 sidenav/topbar/footer/container/customizer。

```php
echo XfAdmin::landing([
    'brand'   => 'XfAdmin',
    'nav'     => [['text' => '特性', 'url' => '#features'], ['text' => '价格', 'url' => '#pricing']],
    'hero'    => ['title' => '企业级后台解决方案', 'subtitle' => '226 个组件', 'image' => 'hero.png'],
    'stats'   => [['value' => '226+', 'label' => '可用组件']],
    'features'=> [['icon' => 'ti ti-bolt', 'title' => '零构建', 'text' => '无需 Node']],
    'pricing' => [['title' => '专业版', 'price' => '￥199', 'features' => ['全部组件'], 'highlight' => true]],
    'testimonials' => [['name' => '张三', 'role' => 'CTO', 'text' => '很好用']],
    'footer'  => ['text' => '© 2026 XfAdmin', 'links' => [['url' => '#', 'text' => '隐私']]],
]);
```

固定顺序：`header` → `hero` → `stats` → `features` → `pricing` → `testimonials` → `cta` → `footer`。

## 9. 同请求渲染多个完整页面

`Page` 在输出后调用 `Assets::resetCollected()`，因此可以在一个请求里渲染多个页面
（邮件模板、PDF、测试场景）而互不污染：

```php
$p1 = (string) XfAdmin::page([...]);
$p2 = (string) XfAdmin::page([...]);   // 资源状态已重置，不会遗漏依赖
```

⚠️ `AuthPage::document()` **不**调用 `resetCollected()`（同请求多个认证页会累积资源，
但由于按路径/key 去重，不会重复引用）。

## 10. 自定义布局骨架

若不用 `Page`，可自行组装（注意 head 必须在内容渲染后输出）：

```php
$content = (string) XfAdmin::card(['title' => 'x', 'body' => 'y']);   // 先渲染

echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">'
   . XfAdmin::head() . '</head><body class="my-body">'
   . $content
   . XfAdmin::scripts() . '</body></html>';
```

## 11. 常见布局问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 页面无样式 | 未输出 `head()`，或资源 404 | 检查 `assets_url` |
| 组件有 HTML 但没交互 | 未输出 `scripts()` | 在 `</body>` 前输出 |
| 主题不生效 | `<html>` 缺 `data-*` 属性 | 检查 `theme` 配置是否被覆盖为非法值 |
| 顶栏右移 235px | 框架 `app.min.css` 给 `.app-topbar` 设置了 `margin-left: var(--ins-sidenav-width)` | `xfadmin.css` 已重置 `margin:0`；若自定义布局需自行覆盖 |
| 侧边栏不显示 | `sidenav => false` 或水平布局 | 检查 `layout`/`topnav` |
| 内容区宽度异常 | `container` 传了空串（Landing 模式） | 按需设置 `container-fluid` |

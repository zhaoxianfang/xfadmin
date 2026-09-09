# 全局配置

全局配置通过 `XfAdmin::config()` 合并，影响**所有组件**的默认行为。
Laravel / ThinkPHP 由服务提供者在引导阶段自动合并 `config/xfadmin.php`。

## 1. 配置文件全貌

`config/xfadmin.php`：

```php
<?php

return [
    // 静态资源基础 URL（发布后指向 public 下的目录）
    'assets_url' => '/zxf/xfadmin',

    // 资源版本号（附加 ?v= 用于浏览器缓存刷新）
    'version' => '2.1.0',

    // 主题默认外观
    'theme' => [
        'skin'            => 'modern',    // classic | material | modern | saas | flat
        'mode'            => 'light',     // light | dark | system
        'layout'          => 'vertical',  // vertical | horizontal | dual
        'layout_position' => 'fixed',     // fixed | scrollable
        'layout_width'    => 'fluid',     // fluid | boxed
        'topbar_color'    => 'light',     // light | dark | gray | gradient
        'menu_color'      => 'gradient',  // light | dark | gray | gradient | image
        'sidenav_size'    => 'default',   // default | compact | condensed | on-hover
                                          // | on-hover-active | offcanvas | full | fullscreen
        'sidenav_user'    => true,        // 侧边栏是否显示用户卡片
    ],

    // 品牌信息
    'brand' => [
        'name'        => 'XfAdmin',
        'logo'        => null,        // 大 logo（浅色背景），null 时用包内默认
        'logo_dark'   => null,        // 大 logo（深色背景）
        'logo_sm'     => null,        // 小 logo
        'favicon'     => null,
        'home_url'    => '/',
    ],

    // 页脚
    'footer' => [
        'text'  => null,              // null 时自动生成版权文案
        'right' => null,
    ],
];
```

## 2. 配置项详解

### 2.1 `assets_url`（string，默认 `/zxf/xfadmin`）

静态资源基址。三种形态：

| 形态 | 示例 | 效果 |
|---|---|---|
| 根相对路径 | `/zxf/xfadmin` | Laravel 自动注册自托管路由；ThinkPHP/原生需发布到 `public/zxf/xfadmin` |
| 完整 URL | `https://cdn.example.com/xfadmin` | 由 CDN 提供；**不再注册自托管路由** |
| 子路径 | `/static/vendor/xfadmin` | 需把资源发布到对应目录 |

⚠️ 修改后必须保证该 URL 可访问，否则整站无样式无 JS。
`Assets::url()` 对已含基址的路径具备**幂等性**，不会拼接两遍。

### 2.2 `version`（string，默认 `2.1.0`）

资源版本号，追加到所有资源 URL 末尾 `?v={version}`。

- 升级包版本后**改这个值**即可强制浏览器刷新缓存；
- 传空字符串则不追加版本参数。

### 2.3 `theme`（array）

控制 `<html>` 上的 `data-*` 属性，进而驱动 CSS 与主题脚本。

| 键 | 可选值 | 输出属性 | 说明 |
|---|---|---|---|
| `skin` | `classic` `material` `modern` `saas` `flat` | `data-skin` | 皮肤预设，影响圆角、阴影、配色密度 |
| `mode` | `light` `dark` `system` | `data-bs-theme` | 明暗模式，`system` 跟随系统 |
| `layout` | `vertical` `horizontal` `dual` | `data-layout` | 布局方向（页面级也可用 `layout` 参数覆盖） |
| `layout_position` | `fixed` `scrollable` | `data-layout-position` | 顶栏/侧栏是否固定 |
| `layout_width` | `fluid` `boxed` | `data-layout-width` | **等于 `fluid` 时不输出该属性** |
| `topbar_color` | `light` `dark` `gray` `gradient` | `data-topbar-color` | 顶栏配色 |
| `menu_color` | `light` `dark` `gray` `gradient` `image` | `data-menu-color` | 侧边栏配色 |
| `sidenav_size` | `default` `compact` `condensed` `on-hover` `on-hover-active` `offcanvas` `full` `fullscreen` | `data-sidenav-size` | 侧边栏尺寸模式 |
| `sidenav_user` | `bool` | `data-sidenav-user="true"` | 仅真值输出 |

⚠️ **必须与模板 `config.js` 保持一致**：模板把"属性缺失"视为 `skin=modern`、
`menu.color=gradient`，因此服务端**必须显式输出**这些属性，
否则服务端 CSS 变量与前端脚本回落不一致，出现"外观与模板不同"。

合并顺序（后者覆盖前者）：

```
内置默认（skin=modern, mode=light, layout_position=fixed, layout_width=fluid,
         topbar_color=light, menu_color=gradient, sidenav_size=default）
  ← XfAdmin::setting('theme', [])      全局配置
  ← $this->get('theme', [])            页面级参数（XfAdmin::page(['theme' => [...]])）
```

### 2.4 `brand`（array）

| 键 | 默认 | 消费方 |
|---|---|---|
| `name` | `XfAdmin` | Sidenav/Topbar/TopNav logo 文字、Footer 版权、Page `<title>` 兜底 |
| `logo` | `images/logo.png` | 侧边栏/顶栏浅色 logo |
| `logo_dark` | `images/logo-black.png` | 深色模式 logo |
| `logo_sm` | `images/logo-sm.png` | 折叠态小 logo |
| `favicon` | `images/favicon.ico` | `<link rel="shortcut icon">` |
| `home_url` | `/` | logo 链接地址 |

### 2.5 `footer`（array）

| 键 | 默认 | 说明 |
|---|---|---|
| `text` | `null` → `© {年} {brand.name}` | 左侧版权（**纯文本语义，转义输出**） |
| `right` | `null` | 右侧内容（**HTML 语义，原样输出**） |

### 2.6 `csrf_token`（string，可选）

未注册 CSRF 解析器时的兜底令牌：

```php
XfAdmin::config(['csrf_token' => $token]);
```

推荐方式（Laravel 服务提供者已自动处理，其它框架可手工注册）：

```php
XfAdmin::setCsrfResolver(fn () => csrf_token());
```

## 3. 运行时读写

```php
// 合并配置（递归）
XfAdmin::config(['theme' => ['mode' => 'dark']]);

// 读取完整配置
$all = XfAdmin::config();

// 点号读取单个值
XfAdmin::setting('brand.name');            // 'XfAdmin'
XfAdmin::setting('theme.skin');            // 'modern'
XfAdmin::setting('theme.nonexist', 'x');   // 'x'（默认值）

// 更多静态 API
XfAdmin::version();            // '2.1.0'
XfAdmin::componentList();      // 全部组件别名 => 类名
XfAdmin::has('dataTable');     // true
XfAdmin::asset('images/logo.png');
XfAdmin::img('users/user-1.jpg');   // 等价于 asset('images/users/user-1.jpg')
XfAdmin::head();               // <head> 资源
XfAdmin::scripts();            // </body> 前脚本
```

## 4. Laravel 中的配置覆盖

```bash
php artisan vendor:publish --tag=xfadmin-config
```

编辑 `config/xfadmin.php`；也可用 `.env` + 配置读取：

```php
// config/xfadmin.php
'assets_url' => env('XFADMIN_ASSETS_URL', '/zxf/xfadmin'),
'version'    => env('XFADMIN_VERSION', '2.1.0'),
```

⚠️ 修改配置后执行 `php artisan config:clear`（配置缓存会让修改不生效）。

## 5. 页面级覆盖

任何全局配置都能在页面级覆盖：

```php
echo XfAdmin::page([
    'theme'  => ['mode' => 'dark', 'menu_color' => 'dark'],   // 覆盖全局主题
    'brand'  => ['name' => '我的后台', 'logo' => 'my-logo.png'],
    'footer' => ['text' => '© 2026 我的公司'],
    // …
]);
```

## 6. 配置自检

```php
// 确认配置已生效
echo XfAdmin::setting('assets_url');                // /zxf/xfadmin
echo XfAdmin::asset('css/app.min.css');             // /zxf/xfadmin/css/app.min.css?v=2.1.0
print_r(XfAdmin::setting('theme'));
```

下一步：[01-架构总览](../02-核心架构/01-architecture.md)

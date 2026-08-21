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

> 所有布局均渲染 `sidenav`（侧边栏）；可通过 `topbar` 选项叠加顶部栏。

### 顶栏 / 顶部导航工具区配置

`topbar`（垂直布局顶栏，组件 `Topbar`）与 `topnav`（水平布局顶栏，组件 `TopNav`）共用同一组工具项配置：

```php
XfAdmin::page([
    'layout' => 'horizontal',                 // 或 'default'（此时用 topbar 而非 topnav）
    'topnav' => [                             // horizontal 布局
        'search'    => true,
        'apps'      => [                       // 应用启动器（圆形九宫格，输出 #apps-dropdown-rounded）
            'title'   => '我的应用',
            'variant' => 'rounded',            // rounded=圆形（默认，对齐 INSPINIA #apps-dropdown-rounded）；grid=方角
            'all_url' => '/apps', 'all_text' => '查看全部应用',
            'add_url' => '/apps/new', 'add_text' => '添加应用',
            'items'   => [
                ['text' => '邮箱', 'icon' => 'ti ti-mail', 'variant' => 'primary', 'url' => '/mail'],
                ['text' => '日历', 'icon' => 'ti ti-calendar', 'variant' => 'info', 'url' => '/cal'],
                // ... 9 宫格，每格 icon + text + variant(语义色) + url
            ],
        ],
        'languages'     => [...],
        'messages'      => [...],
        'notifications' => [...],
        'user'          => [...],
    ],
    // 垂直布局用 topbar 同名键：
    'topbar' => [ 'apps' => [...], 'user' => [...], ... ],
]);
```

- **apps 圆形启动器**：`variant='rounded'` 时容器输出 `id="apps-dropdown-rounded"`，图标为 `rounded-circle` 圆形九宫格，底部含「查看全部 / 添加应用」操作，对齐后台模板 INSPINIA v4 的 App Launcher 标记，便于样式挂钩与 JS 定位（`xfadmin.js` 的 `initAppsDropdown` 会在打开时做视口边界翻转）。
- `variant='grid'` 则为方角九宫格变体（无 `#apps-dropdown-rounded` id）。
- 语义色 `variant` 支持 `primary/info/success/warning/danger/light/dark/purple/teal/orange/cyan`，对应 `bg-*-subtle` + `text-*` 类（已内置）。

### 主题属性

`page` 会在 `<html>` 上输出模板所需的 `data-bs-theme`、`data-menu-color`、`data-topbar-color`、`data-layout` 等属性，与 INSPINIA 客制化面板完全兼容。可通过 `html_attrs` 覆盖：

```php
XfAdmin::page([
    'html_attrs' => ['data-sidenav-size' => 'condensed', 'data-menu-color' => 'dark'],
]);
```

---

## 菜单数据结构

`menu` / `sidenav.menu` 共用同一结构，支持**无限层级**：

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

认证页组件 `authPage` 把 INSPINIA 后台模板中 `auth-*` / `auth-card-*` / `auth-split-*` 全部页面封装为
**3 套布局 × 7 种核心语义类型**（外加 3 种兼容类型），严格对齐模板的 DOM 结构与排版。

### 三套布局 `layout`

| 值 | 说明 | 对应模板 |
|----|------|----------|
| `base`（`basic` 别名） | 单卡片居中、无侧栏图 | auth-sign-in.html 等 |
| `card` | 左右分栏：左表单卡片 + 右侧宣传图 | auth-card-sign-in.html 等 |
| `split` | 左整幅背景图 + 右自动宽表单卡片 | auth-split-sign-in.html 等 |

### 语义类型 `type`

| 类型 | 说明 | 默认标题 |
|------|------|----------|
| `sign-in` | 登录 | 欢迎回来 |
| `sign-up` | 注册 | 创建账号 |
| `reset-pass` | 忘记密码（提交邮箱） | 找回密码 |
| `new-pass` | 设置新密码 / 重置密码（含 6 位验证码分格 + 密码强度条 + 协议勾选） | 设置新密码 |
| `lock-screen` | 锁屏解锁（用户头像 + 密码） | 屏幕已锁定 |
| `login-pin` | PIN 登录 | PIN 登录 |
| `two-factor` | 两步验证（6 位验证码分格） | 两步验证 |
| `delete-account` | 注销账户确认（兼容） | 注销账户 |
| `success-mail` | 邮件发送成功提示（兼容） | 邮件已发送 |

> 也提供快捷方法：`XfAdmin::signIn()` / `signUp()` / `resetPass()` / `newPass()` / `lockScreen()` / `loginPin()` /
> `twoFactor()` / `deleteAccount()` / `successMail()`，均自动注入 `type`。

### 基础用法

```php
// 登录页（split 版，最常用）
echo XfAdmin::authPage([
    'layout'     => 'split',
    'type'       => 'sign-in',
    'title'      => '登录 - 控制台',
    'heading'    => '欢迎回来',
    'subheading' => '请输入管理员账号登录后台',
    'action'     => '/login',
    'fields'     => [
        'email'    => ['label' => '邮箱', 'placeholder' => '请输入邮箱', 'required' => true, 'autofocus' => true],
        'password' => ['label' => '密码', 'placeholder' => '请输入密码', 'required' => true],
    ],
    'submit'     => '登 录',
    'captcha'    => true,                       // bool=渲染占位；string=原样输出（如 SVG 验证码组件）
    'socialButtons' => [                        // 社交登录
        ['icon' => 'ti ti-brand-google', 'url' => '/oauth/google', 'label' => 'Google'],
    ],
    'sideTitle'  => '企业级后台模板组件化方案',
    'sideText'   => '开箱即用 200+ 组件',
    'sideList'   => [['icon' => 'ti ti-check', 'text' => '纯原生 JS，离线可用']],
    'copyright'  => '© 2026 控制台',
]);
```

### 自定义表单 / 模板插槽（在任意位置插入开发者内容）

所有插槽均原样输出（raw），可放入任意组件 HTML 或自定义标签：

| 插槽 | 位置 |
|------|------|
| `beforeForm` / `afterForm` | `<form>` 标签之前 / `</form>` 之后 |
| `prepend` / `append` | 卡片标题之后 / 卡片底部链接之前 |
| `fields[*]['before']` / `fields[*]['after']` | 单个字段之前 / 之后 |
| `content` | 直接接管整张卡片主体（其余表单自动跳过） |
| `below` | 表单下方补充内容（或类型默认导航链接） |

```php
echo XfAdmin::authPage([
    'layout'  => 'split',
    'type'    => 'sign-up',
    'beforeForm' => XfAdmin::alert(['variant' => 'warning', 'content' => '注册前提示']),
    'append'     => XfAdmin::alert(['variant' => 'light', 'content' => '同意《用户协议》']),
    'fields'  => [
        'email' => ['label' => '邮箱', 'after' => '<div class="form-text">不会公开</div>'],
    ],
]);
```

### 锁屏 / PIN / 新密码 专用配置

```php
// 锁屏（user 头像 + 名称 + 邮箱徽标）
echo XfAdmin::authPage(['layout' => 'split', 'type' => 'lock-screen',
    'user' => ['name' => '管理员', 'avatar' => 'users/avatar-1.jpg', 'email' => 'admin@example.com']]);

// 新密码（email 展示 + 6 位验证码分格 + 密码强度条 + 协议）
echo XfAdmin::authPage(['layout' => 'split', 'type' => 'new-pass',
    'email' => 'admin@example.com', 'message' => '密码至少 8 位']);

// 关闭新密码的额外块：newPassShowEmail / newPassShowCode / newPassShowAgree 设为 false
```

### 完整配置项速查

| 键 | 说明 |
|----|------|
| `layout` | base / card / split |
| `type` | 上表 9 种语义类型 |
| `brand` | `['name','url','logo']` 品牌区 |
| `heading` / `subheading` | 主标题 / 副标题（`subtitle` 兼容） |
| `title` | `<title>` |
| `action` / `method` | 表单地址 / 方法（POST 默认） |
| `fields` | 关联数组，key 即字段名（email/password/name/pin/code…） |
| `submit` / `buttons` | 默认提交按钮文案 / 自定义按钮数组 |
| `captcha` | bool=占位；string=原样输出 |
| `socialButtons` | 社交登录数组 |
| `links` | `[['text','href']]` 导航链接（覆盖类型默认） |
| `loginRedirect` / `registerRedirect` | 顶部/底部默认导航地址 |
| `footerLinks` | 底部固定链接列表 |
| `sideImage` / `sideImageAlt` / `sideImageSize` / `sideImagePosition` / `sideOverlay` | 侧栏背景图设置（card/split 共用；`sideImage` 默认包内 `auth.jpg` 与后台模板一致，传 `''`/`false` 关闭回退纯色；`sideOverlay=false` 关闭暗角遮罩） |
| `sideTitle` / `sideText` / `sideList` / `sideVariant` | 侧栏文案与强调色（card/split 共用；`sideVariant` 在无背景图时作为纯色渐变） |
| `user` | 锁屏用户 `['name','avatar','email']` |
| `copyright` | 底部版权 |
| `beforeForm`/`afterForm`/`prepend`/`append`/`content`/`below` | 自定义插槽 |

// 404
echo XfAdmin::errorPage(['code' => '404', 'title' => '页面不存在', 'home_url' => '/']);

// 即将上线（倒计时）
echo XfAdmin::comingSoon(['heading' => '即将上线', 'deadline' => '2026-12-31 00:00:00']);

// 维护中
echo XfAdmin::maintenance(['contact' => 'support@example.com']);
```

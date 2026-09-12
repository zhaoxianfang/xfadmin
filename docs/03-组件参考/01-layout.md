# 布局 · 导航 · 栅格

> 整页骨架、认证页、错误页、侧边栏、顶栏、水平导航、页脚、定制面板、面包屑与栅格系统。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`page`](#page) — 整页布局容器：组装 head/body/scripts，所有后台页面入口
- [`sidenav`](#sidenav) — 左侧导航菜单（支持多级子菜单与 Mega Menu）
- [`topbar`](#topbar) — 顶部导航栏（搜索/通知/用户菜单等）
- [`topNav`](#topnav) — 水平顶部导航（无限级子菜单 + Mega 面板）
- `topnav` — 等价于 `topNav`（同一组件类的别名）
- [`pageTitle`](#pagetitle) — 页面标题区（标题 + 面包屑 + 操作按钮）
- [`footer`](#footer) — 页面底部版权/链接区
- [`customizer`](#customizer) — 右侧主题定制面板（明暗/配色/布局切换）
- [`authPage`](#authpage) — 认证页统一入口（type+layout 控制 9 种语义×3 种布局）
- `signIn` — 等价于 `authPage`（同一组件类的别名）
- `signUp` — 等价于 `authPage`（同一组件类的别名）
- `resetPass` — 等价于 `authPage`（同一组件类的别名）
- `newPass` — 等价于 `authPage`（同一组件类的别名）
- `twoFactor` — 等价于 `authPage`（同一组件类的别名）
- [`lockScreen`](#lockscreen) — 锁屏页（auth lock-screen，密码解锁）
- `deleteAccount` — 等价于 `authPage`（同一组件类的别名）
- `successMail` — 等价于 `authPage`（同一组件类的别名）
- `loginPin` — 等价于 `authPage`（同一组件类的别名）
- [`errorPage`](#errorpage) — 错误页（400/401/403/404/408/500 + maintenance）
- [`comingSoon`](#comingsoon) — 即将上线页（倒计时 + 订阅）
- [`maintenance`](#maintenance) — 系统维护页（503 风格）
- [`emptyState`](#emptystate) — 空状态页（无数据占位插画+文案）
- [`landing`](#landing) — 落地页/营销首页（英雄区+特性+CTA）
- [`accountSettingsPanel`](#accountsettingspanel) — 账户设置面板（头像/密码/通知等卡片组）
- [`menu`](#menu) — 菜单数据组件（导航菜单 DSL，供 sidenav/topnav 使用）
- [`row`](#row) — 栅格行容器（Bootstrap row 封装）
- [`col`](#col) — 栅格列（Bootstrap col 封装，支持响应式断点）

## 本章导读

布局组件负责输出**完整 HTML 文档**或页面骨架，是后台页面的入口。

### 组合范式

```php
echo XfAdmin::page([
    'title'      => '用户管理',
    'menu'       => $menu,              // 自动下发给 Sidenav / TopNav
    'current_url'=> request()->path(),   // 自动高亮当前菜单
    'topbar'     => ['search' => true, 'user' => [...]],
    'page_title' => ['title' => '用户管理', 'breadcrumb' => [...]],
    'content'    => $content,           // 由 card / row / col / dataTable 等拼装
]);
```

### 约定与陷阱

- `Page` **先渲染 body 再输出 head**（组件在渲染时注册 CSS/JS）；
- 水平布局三种触发：`layout => horizontal` / `layout => topnav` / `topnav => [...]`；
- 主题属性（`data-skin`、`data-bs-theme`、`data-menu-color` 等）必须显式输出，
  模板 `config.js` 把「属性缺失」视为 `modern` / `gradient`；
- `Page` **没有** `brand` / `dir` / `meta` / `head_extra` / `styles` 参数，
  品牌与页脚走全局配置 `XfAdmin::setting(...)`；
- `XfAdmin::lockScreen()` 得到的是独立 `LockScreen` 组件，
  要用 AuthPage 的锁屏语义请写 `XfAdmin::authPage(['type' => 'lock-screen'])`。


---

### `page`

整页布局容器：组装 head/body/scripts，所有后台页面入口。

> **类**：`zxf\XfAdmin\Components\Layout\Page`
> **文件**：`src/Components/Layout/Page.php`（199 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::page([
    'title'   => '仪表盘',
    'layout'  => 'vertical',            // vertical | dual
    'theme'   => ['mode' => 'light', 'menu_color' => 'dark', 'sidenav_size' => 'default', ...],
    'menu'    => [ ...Menu items... ],  // 侧栏菜单
    'sidenav' => [...] | false,
    'topbar'  => [...] | false,
    'page_title' => ['title' => '仪表盘', 'breadcrumb' => [...]],
    'content' => $components,           // 字符串 / 组件 / 数组（可混排任意组件）
    'footer'  => [...] | false,
    'customizer' => true,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::page([
    'lang' => 'zh-CN',
    'title' => '',
    'description' => null,
    'keywords' => null,
    'author' => null,
    'favicon' => null,
    'layout' => 'vertical',
    'theme' => [],
    'menu' => [],
    'current_url' => null,
    'sidenav' => [],
    'topbar' => [],
    'topnav' => null,    // 水平布局顶部导航（layout=horizontal 时启用）
    'page_title' => null,
    'content' => '',
    'container' => 'container-fluid',
    'footer' => [],
    'customizer' => true,
    'preloader' => false,    // 页面加载动画（true = 启用）
    'head' => null,    // <head> 附加内容
    'scripts' => null,    // </body> 前附加内容
    'body_class' => null,
    'csrf' => null,    // CSRF Token（Laravel 下自动注入 csrf_token()）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `sidenav[]` 元素键：`menu`、`current_url`

> **渲染骨架**：主要 class `wrapper` `content-page` `spinner` `double-bounce1` `double-bounce2`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `lang` | string | `'zh-CN'` | 源码用法：`$htmlAttrs = ['lang' => $this->get('lang')];` |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `description` | mixed | `null` | 描述文本；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `keywords` | mixed | `null` | **文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `author` | mixed | `null` | 作者信息；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `favicon` | mixed | `null` | 源码用法：`$favicon = $this->get('favicon') ?? XfAdmin::setting('brand.favicon') ?? $assets->url…` |
| `layout` | string | `'vertical'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `theme` | array | `[]` | 主题（light / dark / 图表主题名） |
| `menu` | array | `[]` | 菜单数据数组 |
| `current_url` | mixed | `null` | 当前 URL（用于菜单自动高亮）；源码用法：`$topnavOpts['current_url'] ??= $this->get('current_url');` |
| `sidenav` | array | `[]` | 开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `topbar` | array | `[]` | 开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `topnav` | mixed | `null` | 水平布局顶部导航（layout=horizontal 时启用）；为 `null` 时不渲染该区块 |
| `page_title` | mixed | `null` | 页面标题区（数组则渲染 PageTitle 组件）；源码用法：`$pageTitle = $this->get('page_title');` |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `container` | string | `'container-fluid'` | 内容区容器 class（默认 container-fluid）；**文本槽位**：输出前自动 HTML 转义 |
| `footer` | array | `[]` | 底部内容（原样输出）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `customizer` | bool | `true` | 是否渲染主题定制面板；开关：非空 / 真值时启用对应区块 |
| `preloader` | bool | `false` | 页面加载动画（true = 启用）；开关：非空 / 真值时启用对应区块 |
| `head` | mixed | `null` | <head> 附加内容；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `scripts` | mixed | `null` | </body> 前附加内容；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `body_class` | mixed | `null` | **文本槽位**：输出前自动 HTML 转义 |
| `csrf` | mixed | `null` | CSRF Token（Laravel 下自动注入 csrf_token()） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `sidenav`

左侧导航菜单（支持多级子菜单与 Mega Menu）。

> **类**：`zxf\XfAdmin\Components\Layout\Sidenav`
> **文件**：`src/Components/Layout/Sidenav.php`（125 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::sidenav([
    'brand' => ['name' => 'XfAdmin', 'logo' => '/logo.png', 'logo_sm' => '/logo-sm.png', 'url' => '/'],
    'user'  => ['name' => '张三', 'role' => '管理员', 'avatar' => '/a.jpg', 'items' => [['text'=>'退出','url'=>'/logout','icon'=>'ti ti-logout-2']]],
    'menu'  => [ ...同 Menu items... ] | Menu 实例 | 原始 HTML,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::sidenav([
    'brand' => [],
    'user' => false,
    'menu' => [],
    'current_url' => null,
    'append' => null,    // 菜单下方附加内容
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `brand[]` 元素键：`url`、`home_url`（默认 `/`）、`name`（默认 `XfAdmin`）、`logo`、`logo_dark`、`logo_sm`
- `menu[]` 元素键：`render`

> **渲染骨架**：主要 class `scrollbar`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | array | `[]` | 品牌信息（name/logo/url） |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等）；源码用法：`$user = $this->get('user');` |
| `menu` | array | `[]` | 菜单数据数组；源码用法：`$menu = $this->get('menu');` |
| `current_url` | mixed | `null` | 当前 URL（用于菜单自动高亮）；源码用法：`'current_url' => $this->get('current_url'),` |
| `append` | mixed | `null` | 菜单下方附加内容；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `topbar`

顶部导航栏（搜索/通知/用户菜单等）。

> **类**：`zxf\XfAdmin\Components\Layout\Topbar`
> **文件**：`src/Components/Layout/Topbar.php`（363 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::topbar([
    'brand'         => true,            // 顶栏品牌 logo
    'search'        => true,            // 搜索框
    'left'          => '自定义HTML',     // 左侧附加插槽
    'theme_toggle'  => true,            // 明暗切换按钮
    'fullscreen'    => true,            // 全屏按钮
    'customizer'    => true,            // 主题定制按钮
    'languages'     => [['flag'=>..,'name'=>'中文','code'=>'zh'], ...],
    'notifications' => ['count' => 3, 'items' => [['title'=>..,'text'=>..,'time'=>..,'avatar'=>..,'icon'=>..,'url'=>..]], 'all_url' => '#'],
    'messages'      => ['count' => 7, 'title'=>'消息', 'items' => [['from'=>..,'avatar'=>..,'text'=>..,'time'=>..,'url'=>..,'unread'=>true]], 'all_url'=>'#'],
    'apps'          => ['items' => [['icon'=>'ti ti-mail','text'=>'邮件','url'=>'#','variant'=>'primary'], ...]],
    'search_modal'  => true,              // 点击搜索图标弹出全屏搜索模态（需 search=true）
    'user'          => ['name'=>'张三','role'=>'管理员','avatar'=>'/a.jpg','items'=>[['text'=>'退出','icon'=>'ti ti-logout-2','url'=>'/logout']]],
    'right'         => '自定义HTML',     // 右侧附加插槽
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::topbar([
    'brand' => true,
    'search' => true,
    'search_placeholder' => 'Search...',
    'search_modal' => false,    // 点击搜索图标弹出全屏模态（替代内联搜索框）
    'left' => null,
    'theme_toggle' => true,
    'fullscreen' => true,
    'customizer' => true,
    'languages' => [],
    'notifications' => false,
    'messages' => false,
    'apps' => false,
    'user' => false,
    'right' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `languages[]` 元素键：`active`

> **渲染骨架**：主要 class `container-fluid` `topbar-menu` `d-flex` `align-items-center` `gap-2` `topbar-item` `d-none` `d-xl-flex`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | bool | `true` | 品牌信息（name/logo/url）；源码用法：`if (! $this->get('brand')) {` |
| `search` | bool | `true` | 是否启用搜索；开关：非空 / 真值时启用对应区块 |
| `search_placeholder` | string | `'Search...'` | **文本槽位**：输出前自动 HTML 转义 |
| `search_modal` | bool | `false` | 点击搜索图标弹出全屏模态（替代内联搜索框）；开关：非空 / 真值时启用对应区块 |
| `left` | mixed | `null` | **内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `theme_toggle` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `fullscreen` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `customizer` | bool | `true` | 是否渲染主题定制面板；开关：非空 / 真值时启用对应区块 |
| `languages` | array | `[]` |  |
| `notifications` | bool | `false` | 源码用法：`$conf = $this->get('notifications');` |
| `messages` | bool | `false` | 消息列表（聊天/通知/消息中心条目）；源码用法：`$conf = $this->get('messages');` |
| `apps` | bool | `false` | 应用列表（应用启动器）；源码用法：`$conf = $this->get('apps');` |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等）；源码用法：`$user = $this->get('user');` |
| `right` | mixed | `null` | **内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `topNav`

水平顶部导航（无限级子菜单 + Mega 面板）。

> **类**：`zxf\XfAdmin\Components\Layout\TopNav` 等 `topNav` / `topnav`
> **文件**：`src/Components/Layout/TopNav.php`（616 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::topNav([
    'brand'         => true,
    'menu'          => [ ... ],          // 见 renderMenu()，支持无限级 children 与 mega
    'current_url'   => '/admin/x',
    'search'        => true,
    'mega'          => ['text'=>'快捷入口','title'=>'...','columns'=>[...]],
    'languages'     => [['flag'=>..,'name'=>'简体中文','code'=>'cn','active'=>true]],
    'messages'      => ['count'=>7,'items'=>[...]],
    'notifications' => ['count'=>3,'items'=>[...]],
    'theme_toggle'  => true,
    'fullscreen'    => true,
    'customizer'    => true,
    'user'          => ['name'=>'张三','avatar'=>..,'items'=>[...]],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::topNav([
    'brand' => true,
    'sidenav_toggle' => false,    // 纯水平布局默认不显示侧栏切换按钮
    'menu' => [],
    'current_url' => null,
    'search' => false,
    'search_placeholder' => 'Search for something...',
    'mega' => false,
    'left' => null,
    'languages' => [],
    'messages' => false,
    'notifications' => false,
    'theme_toggle' => true,
    'fullscreen' => true,
    'customizer' => true,
    'user' => false,
    'apps' => false,    // 应用启动器（圆形九宫格 #apps-dropdown-rounded）
    'right' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `menu[]` 元素键：`children`、`mega`、`icon`、`text`、`url`、`divider`、`title`、`disabled`、`badge`、`active`
- `languages[]` 元素键：`active`

> **渲染骨架**：主要 class `container-fluid` `topbar-menu` `d-flex` `align-items-center` `gap-2` `app-search` `topnav-search` `d-none`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | bool | `true` | 品牌信息（name/logo/url）；源码用法：`if (! $this->get('brand')) {` |
| `sidenav_toggle` | bool | `false` | 纯水平布局默认不显示侧栏切换按钮；开关：非空 / 真值时启用对应区块 |
| `menu` | array | `[]` | 菜单数据数组 |
| `current_url` | mixed | `null` | 当前 URL（用于菜单自动高亮） |
| `search` | bool | `false` | 是否启用搜索；开关：非空 / 真值时启用对应区块 |
| `search_placeholder` | string | `'Search for something...'` | **文本槽位**：输出前自动 HTML 转义 |
| `mega` | bool | `false` | 源码用法：`$mega = $this->get('mega');` |
| `left` | mixed | `null` | **内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `languages` | array | `[]` |  |
| `messages` | bool | `false` | 消息列表（聊天/通知/消息中心条目）；源码用法：`$conf = $this->get('messages');` |
| `notifications` | bool | `false` | 源码用法：`$conf = $this->get('notifications');` |
| `theme_toggle` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `fullscreen` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `customizer` | bool | `true` | 是否渲染主题定制面板；开关：非空 / 真值时启用对应区块 |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等）；源码用法：`$user = $this->get('user');` |
| `apps` | bool | `false` | 应用启动器（圆形九宫格 #apps-dropdown-rounded） |
| `right` | mixed | `null` | **内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

### `topnav`

`topnav` 是 `topNav` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\TopNav`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::page([
    'layout' => 'horizontal',                 // 或 'topnav'
    'topnav' => ['menu' => $menu, 'search' => true],
]);
// 单独使用等价写法：
echo XfAdmin::topnav(['menu' => $menu, 'current_url' => '/admin']);
```

---

### `pageTitle`

页面标题区（标题 + 面包屑 + 操作按钮）。

> **类**：`zxf\XfAdmin\Components\Layout\PageTitle`
> **文件**：`src/Components/Layout/PageTitle.php`（51 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::pageTitle([
    'title'      => '用户管理',
    'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '系统'], ['text' => '用户管理', 'active' => true]],
    'actions'    => '右侧自定义HTML（可选，替代面包屑）',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::pageTitle([
    'title' => '',
    'breadcrumb' => [],
    'actions' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `breadcrumb[]` 元素键：`active`、`url`、`text`

> **渲染骨架**：主要 class `flex-grow-1` `text-end`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `breadcrumb` | array | `[]` |  |
| `actions` | mixed | `null` | 操作区内容（按钮组 / 行操作定义）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `footer`

页面底部版权/链接区。

> **类**：`zxf\XfAdmin\Components\Layout\Footer`
> **文件**：`src/Components/Layout/Footer.php`（39 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::footer(['text' => '© 2026 XX公司', 'right' => '<a href="#">帮助</a>']);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::footer([
    'text' => null,
    'right' => null,
]);
```

</details>

> **渲染骨架**：主要 class `container-fluid` `row` `col-md-6` `text-center` `text-md-start` `text-md-end` `d-none` `d-md-block`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义）；源码用法：`$text = $this->get('text') ?? XfAdmin::setting('footer.text');` |
| `right` | mixed | `null` | 源码用法：`$right = $this->get('right') ?? XfAdmin::setting('footer.right');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `customizer`

右侧主题定制面板（明暗/配色/布局切换）。

> **类**：`zxf\XfAdmin\Components\Layout\Customizer`
> **文件**：`src/Components/Layout/Customizer.php`（107 行）
> **依赖插件**：无

**用法示例**

```php
// 主题定制面板：默认由 Page 自动渲染，也可单独使用
echo XfAdmin::customizer([
    'title'    => '界面定制',
    'subtitle' => '快速配置布局、皮肤与偏好',
]);

// 关闭定制面板：XfAdmin::page(['customizer' => false, …])
// 面板内的 radio name 与 <html data-*> 属性一一对应：
//   data-skin / data-bs-theme / data-topbar-color / data-menu-color / data-sidenav-size / data-layout-position
//   配置持久化在 sessionStorage['__INSPINIA_CONFIG__']（由 config.js 管理）
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::customizer([
    'title' => 'Admin Customizer',
    'subtitle' => '快速配置后台界面的布局、皮肤与偏好',
]);
```

</details>

> **渲染骨架**：主要 class `row` `g-3` `form-check` `card-radio` `p-3` `border-bottom` `offcanvas` `offcanvas-end`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'Admin Customizer'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `subtitle` | string | `'快速配置后台界面的布局、皮肤与偏好'` | 副标题文本；**文本槽位**：输出前自动 HTML 转义 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `authPage`

认证页统一入口（type+layout 控制 9 种语义×3 种布局）。

> **类**：`zxf\XfAdmin\Components\Layout\AuthPage` 等 `authPage` / `signIn` / `signUp` / `resetPass` / `newPass` / `twoFactor` / `deleteAccount` / `successMail` / `loginPin`
> **文件**：`src/Components/Layout/AuthPage.php`（1524 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::authPage(['type' => 'sign-in', 'layout' => 'split', ...]);
XfAdmin::signIn();
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::authPage([
    'layout' => 'base',    // base | card | split（basic 视为 base）
    'class' => '',    // 附加到根容器 class
    'id' => '',    // 根容器 id
    'bodyClass' => '',    // 卡片 / 主体区域附加 class
    'theme' => 'light',    // light | dark（split/card 侧栏风格）
    // brand
    'brand' => [
        'name' => 'XfAdmin',
        'url' => '/',
        'logo' => null,
    ],
    'title' => '',    // <title>
    'heading' => '',    // 主标题
    'subheading' => '',    // 副标题（历史字段名 subtitle 兼容）
    'copyright' => '',    // 底部版权
    'status' => '',    // 顶部状态提示（绿，通常由控制器 with('status') 注入）
    'message' => '',    // 说明性文本（如 new-pass 的提示语）
    'action' => '',    // 表单提交地址（核心字段；formAttrs.action 兼容）
    'method' => 'POST',
    'ajax' => true,    // 表单以 AJAX 方式提交（data-xf-remote，组件内置支持）
    'fields' => [],    // 关联数组：['email' => [...], 'password' => [...]]
    'buttons' => [],    // 按钮数组（label/variant/type/...）
    'submit' => '提交',    // 默认提交按钮文案（未传 buttons 时使用）
    'content' => '',    // 自定义主体内容（raw，覆盖默认表单）
    'below' => '',    // 表单下方补充内容（raw，如提示文案 / 链接）
    'beforeForm' => '',    // 插入到 <form> 之前
    'afterForm' => '',    // 插入到 </form> 之后
    'prepend' => '',    // 插入到卡片标题之后（表单之前）
    'append' => '',    // 插入到卡片底部（links 之前）
    'links' => [],    // 顶部 / 底部导航链接（[['text'=>..., 'href'=>...], ...]）
    'loginRedirect' => '/login',    // “已有账号？去登录” 链接
    'registerRedirect' => '/register',    // “还没有账号？去注册” 链接
    'backLink' => null,    // 返回链接（['url'=>..., 'text'=>...] 或 null 隐藏）
    'footerLinks' => [],    // 底部链接列表（[['url'=>..., 'text'=>...], ...]）
    'socialButtons' => [],    // [['icon'=>..., 'url'=>..., 'label'=>...], ...]
    'sideImage' => 'auth.jpg',
    'sideImageAlt' => '',    // 背景图 alt
    'sideImageSize' => 'cover',    // 背景尺寸（CSS background-size：cover/contain/100% 100%...）
    'sideImagePosition' => 'center',    // 背景定位（CSS background-position：center/top left...）
    'sideOverlay' => true,    // 是否叠加底部渐变遮罩（保证侧栏白字可读；false 时无暗角）
    'sideTitle' => '企业级后台管理平台',    // 侧栏标题（默认官方文案，可按需覆盖）
    'sideText' => 'XfAdmin 提供组件化、标准化的一站式企业后�…',    // 侧栏文案
    // sideList
    'sideList' => [
        [/* … */],
        [/* … */],
        [/* … */],
    ],
    'sideVariant' => 'primary',    // 侧栏强调色（primary/info/success/...，无背景图时作为纯色渐变）
    'user' => [],    // ['name'=>..., 'avatar'=>..., 'email'=>...]
    'showBackToTop' => false,
    'captcha' => false,    // bool=渲染占位；string=原样输出（如 web component HTML）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `brand[]` 元素键：`name`（默认 `XfAdmin`）、`url`（默认 `/`）、`logo`
- `fields[]` 元素键：`name`
- `buttons[]` 元素键：`type`（默认 `submit`）、`variant`（默认 `primary`）、`name`、`label`（默认 `提交`）
- `links[]` 元素键：`url`（默认 `#`）、`text`
- `footerLinks[]` 元素键：`url`（默认 `#`）、`text`
- `socialButtons[]` 元素键：`url`（默认 `#`）、`text`
- `sideList[]` 元素键：`url`（默认 `#`）、`text`
- `user[]` 元素键：`name`（默认 `?`）、`avatar`、`email`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 可选值：`base` |
| `class` | string | `''` | 附加到根容器 class |
| `id` | string | `''` | 根容器 id |
| `bodyClass` | string | `''` | 卡片 / 主体区域附加 class |
| `theme` | string | `'light'` | light \| dark（split/card 侧栏风格） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | <title> |
| `heading` | string | `''` | 主标题 |
| `subheading` | string | `''` | 副标题（历史字段名 subtitle 兼容） |
| `copyright` | string | `''` | 底部版权 |
| `status` | string | `''` | 顶部状态提示（绿，通常由控制器 with('status') 注入） |
| `message` | string | `''` | 说明性文本（如 new-pass 的提示语） |
| `action` | string | `''` | 表单提交地址（核心字段；formAttrs.action 兼容） |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE）；源码用法：`if (($this->get('method') === 'POST') && isset($formAttrs['method'])) {` |
| `ajax` | bool | `true` | 表单以 AJAX 方式提交（data-xf-remote，组件内置支持） |
| `fields` | array | `[]` | 关联数组：['email' => [...], 'password' => [...]] |
| `buttons` | array | `[]` | 按钮数组（label/variant/type/...） |
| `submit` | string | `'提交'` | 默认提交按钮文案（未传 buttons 时使用） |
| `content` | string | `''` | 自定义主体内容（raw，覆盖默认表单） |
| `below` | string | `''` | 表单下方补充内容（raw，如提示文案 / 链接） |
| `beforeForm` | string | `''` | 插入到 <form> 之前 |
| `afterForm` | string | `''` | 插入到 </form> 之后 |
| `prepend` | string | `''` | 插入到卡片标题之后（表单之前） |
| `append` | string | `''` | 插入到卡片底部（links 之前） |
| `links` | array | `[]` | 顶部 / 底部导航链接（[['text'=>..., 'href'=>...], ...]） |
| `loginRedirect` | string | `'/login'` | “已有账号？去登录” 链接 |
| `registerRedirect` | string | `'/register'` | “还没有账号？去注册” 链接 |
| `backLink` | mixed | `null` | 返回链接（['url'=>..., 'text'=>...] 或 null 隐藏） |
| `footerLinks` | array | `[]` | 底部链接列表（[['url'=>..., 'text'=>...], ...]） |
| `socialButtons` | array | `[]` | [['icon'=>..., 'url'=>..., 'label'=>...], ...] |
| `sideImage` | string | `'auth.jpg'` | 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径）；源码用法：`$img = $this->get('sideImage');` |
| `sideImageAlt` | string | `''` | 背景图 alt |
| `sideImageSize` | string | `'cover'` | 背景尺寸（CSS background-size：cover/contain/100% 100%...） |
| `sideImagePosition` | string | `'center'` | 背景定位（CSS background-position：center/top left...） |
| `sideOverlay` | bool | `true` | 是否叠加底部渐变遮罩（保证侧栏白字可读；false 时无暗角） |
| `sideTitle` | string | `'企业级后台管理平台'` | 侧栏标题（默认官方文案，可按需覆盖） |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` | 侧栏文案 |
| `sideList` | array | `[{…}, {…}, {…}]` | 侧栏要点列表 `['icon'=>..,'text'=>..]`；源码用法：`$list = $this->get('sideList');` |
| `sideVariant` | string | `'primary'` | 侧栏强调色（primary/info/success/...，无背景图时作为纯色渐变） |
| `user` | array | `[]` | ['name'=>..., 'avatar'=>..., 'email'=>...] |
| `showBackToTop` | bool | `false` | 是否显示「回到顶部」按钮；源码用法：`. ($this->get('showBackToTop') ? '<a href="#" class="back-to-top"><i class="ti ti-che…` |
| `captcha` | bool | `false` | bool=渲染占位；string=原样输出（如 web component HTML） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

### `signIn`

`signIn` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::signIn([                       // 等价 authPage(['type' => 'sign-in'])
    'layout'  => 'split',                    // base | card | split
    'action'  => '/admin/login',
    'ajax'    => true,
    'heading' => '欢迎回来',
    'fields'  => [
        'username' => ['label' => '账号', 'required' => true, 'autofocus' => true],
        'password' => ['label' => '密码', 'type' => 'password', 'required' => true],
    ],
    'submit'  => ['text' => '登录', 'icon' => 'ti ti-login'],
    'captcha' => (string) XfAdmin::captcha(['mode' => 'image', 'src' => '/captcha.png']),
]);
```

### `signUp`

`signUp` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::signUp([                       // 等价 authPage(['type' => 'sign-up'])
    'layout' => 'card',
    'action' => '/admin/register',
    'ajax'   => true,
    'fields' => [
        'name'     => ['label' => '姓名', 'required' => true],
        'email'    => ['label' => '邮箱', 'type' => 'email', 'required' => true],
        'password' => ['label' => '密码', 'type' => 'password', 'required' => true],
        'password_confirmation' => ['label' => '确认密码', 'type' => 'password', 'required' => true],
    ],
    'append' => '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="agree" id="agree">'
             . '<label class="form-check-label" for="agree">我已阅读并同意服务条款</label></div>',
    'loginRedirect' => '/admin/login',
]);
```

### `resetPass`

`resetPass` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::resetPass([                    // 等价 authPage(['type' => 'reset-pass'])
    'layout' => 'base',
    'action' => '/admin/password/email',
    'ajax'   => true,
    'fields' => ['email' => ['label' => '注册邮箱', 'type' => 'email', 'required' => true, 'autofocus' => true]],
    'submit' => ['text' => '发送重置链接'],
]);
```

### `newPass`

`newPass` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::newPass([                      // 等价 authPage(['type' => 'new-pass'])
    'layout' => 'base',
    'action' => '/admin/password/new',
    'ajax'   => true,
    'email'  => 'user@example.com',           // 展示（disabled）
    'newPassShowCode' => false,               // 是否显示 6 位验证码分格输入
    'newPassShowAgree' => true,
    'fields' => [
        'password'              => ['label' => '新密码', 'type' => 'password', 'required' => true],
        'password_confirmation' => ['label' => '确认新密码', 'type' => 'password', 'required' => true],
    ],
]);
```

### `twoFactor`

`twoFactor` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::twoFactor([                    // 等价 authPage(['type' => 'two-factor'])
    'layout' => 'base',
    'action' => '/admin/2fa',
    'ajax'   => true,
    'mask'   => 'u***@example.com',           // 提示验证码发送目标（可省略）
    'submit' => ['text' => '验证'],
]);
// 组件内部渲染 6 个分格输入（name=\"code[]\"），由前端自动拼接为单个值提交
```

---

### `lockScreen`

锁屏页（auth lock-screen，密码解锁）。

> **类**：`zxf\XfAdmin\Components\Layout\LockScreen`
> **文件**：`src/Components/Layout/LockScreen.php`（103 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::lockScreen([
    'user'    => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg'],
    'action'  => '/unlock',
    'heading' => '已锁定',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::lockScreen([
    'lang' => 'zh-CN',
    'title' => null,    // <title>，默认取 heading
    'theme' => [],
    // user
    'user' => [
        'name' => 'User',
        'avatar' => '',
    ],
    'action' => '#',
    'heading' => '屏幕已锁定',
    'text' => '请输入密码以继续',
    'brand' => 'XfAdmin',
    'below' => null,    // 卡片下方补充内容（如「切换账号」链接）
    'copyright' => null,
    'favicon' => null,
    'head' => null,
    'scripts' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `theme[]` 元素键：`skin`、`mode`
- `user[]` 元素键：`name`（默认 `User`）、`avatar`

> **渲染骨架**：主要 class `auth-box` `overflow-hidden` `align-items-center` `d-flex` `container` `row` `justify-content-center` `col-xxl-4`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `lang` | string | `'zh-CN'` | 源码用法：`$htmlAttrs = ['lang' => $this->get('lang')];` |
| `title` | mixed | `null` | <title>，默认取 heading |
| `theme` | array | `[]` | 主题（light / dark / 图表主题名） |
| `user` | array | `['name'=>'User', 'avatar'=>'']` | 用户信息（name/avatar/email/role 等）；源码用法：`$user = (array) $this->get('user');` |
| `action` | string | `'#'` | 表单提交地址 / 动作类型；**文本槽位**：输出前自动 HTML 转义 |
| `heading` | string | `'屏幕已锁定'` | **文本槽位**：输出前自动 HTML 转义 |
| `text` | string | `'请输入密码以继续'` | 正文/按钮文案（纯文本语义，输出时转义）；**文本槽位**：输出前自动 HTML 转义 |
| `brand` | string | `'XfAdmin'` | 品牌信息（name/logo/url）；**文本槽位**：输出前自动 HTML 转义 |
| `below` | mixed | `null` | 卡片下方补充内容（如「切换账号」链接）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `copyright` | mixed | `null` | 版权文案；源码用法：`$copyright = $this->get('copyright') ?? ('© ' . date('Y') . ' ' . XfAdmin::setting('b…` |
| `favicon` | mixed | `null` | 源码用法：`$favicon = $this->get('favicon') ?? XfAdmin::setting('brand.favicon') ?? $assets->url…` |
| `head` | mixed | `null` | </head> 前附加内容（原样输出）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |
| `scripts` | mixed | `null` | </body> 前附加内容（原样输出）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

### `deleteAccount`

`deleteAccount` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::deleteAccount([                // 等价 authPage(['type' => 'delete-account'])
    'layout' => 'base',
    'action' => '/admin/account/delete',
    'ajax'   => true,
    'message' => '注销后数据不可恢复，请谨慎操作。',
    'fields' => ['password' => ['label' => '请输入密码确认', 'type' => 'password', 'required' => true, 'autofocus' => true]],
    'submit' => ['text' => '确认注销', 'variant' => 'danger'],
]);
```

### `successMail`

`successMail` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::successMail([                  // 等价 authPage(['type' => 'success-mail'])
    'layout' => 'base',
    'status' => '重置链接已发送，请查收邮箱。',
    'loginRedirect' => '/admin/login',
]);
// 该语义页无表单，仅展示成功图标 + 提示 + 返回链接
```

### `loginPin`

`loginPin` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

**用法示例**（该别名的典型写法）

```php
echo XfAdmin::loginPin([                     // 等价 authPage(['type' => 'login-pin'])
    'layout'   => 'base',
    'action'   => '/admin/pin-login',
    'ajax'     => true,
    'pinGroup' => 6,                         // PIN 位数，也可写 fields['pin']['group']
    'submit'   => ['text' => '登录'],
]);
```

---

### `errorPage`

错误页（400/401/403/404/408/500 + maintenance）。

> **类**：`zxf\XfAdmin\Components\Layout\ErrorPage`
> **文件**：`src/Components/Layout/ErrorPage.php`（56 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::errorPage([
    'code'    => 404,
    'heading' => '页面不存在',
    'message' => '您访问的页面不存在或已被移动。',
    'home_url'=> '/',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::errorPage([
    'layout' => 'base',    // 布局模式（各组件不同，如 vertical/horizontal）
    'class' => '',    // 附加到根元素的自定义 class
    'id' => '',    // 根元素 id（留空自动生成唯一 id）
    'bodyClass' => '',    // 追加到 <form> 的 class
    'theme' => 'light',    // 主题（light / dark / 图表主题名）
    // brand：品牌信息（name/logo/url）
    'brand' => [
        'name' => 'XfAdmin',
        'url' => '/',
        'logo' => null,
    ],
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'heading' => 'Page Not Found',
    'subheading' => '',    // 副标题（回退历史字段 subtitle）
    'copyright' => '',    // 版权文案
    'status' => '',    // 状态值 / 状态映射
    'message' => '',
    'action' => '',    // 表单提交地址 / 动作类型
    'method' => 'POST',    // HTTP 方法（GET/POST/PUT/DELETE）
    'ajax' => true,    // AJAX 地址或 DataTables 原生 ajax 配置
    'fields' => [],    // 字段定义数组（表单字段 / 详情字段）
    'buttons' => [],    // 按钮定义数组
    'submit' => '提交',    // 提交按钮配置（字符串或数组：text/class/variant/icon）
    'content' => '',    // 内容区（可为 HTML 字符串、组件实例或数组）
    'below' => '',    // 表单下方补充内容（原样输出）
    'beforeForm' => '',    // 插入到 <form> 之前的内容（原样输出）
    'afterForm' => '',    // 插入到 </form> 之后的内容（原样输出）
    'prepend' => '',    // 前缀内容（原样输出，如输入组文本/图标）
    'append' => '',    // 后缀内容（原样输出，常用于协议说明）
    'links' => [],    // 链接数组（导航 / 底部链接）
    'loginRedirect' => '/login',    // 「去登录」链接地址
    'registerRedirect' => '/register',    // 「去注册」链接地址
    'backLink' => null,    // 返回链接配置（保留兼容）
    'footerLinks' => [],    // 页脚链接 `[['url'=>..,'text'=>..]]`
    'socialButtons' => [],    // 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]`
    'sideImage' => 'auth.jpg',    // 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径）
    'sideImageAlt' => '',    // 侧栏背景图无障碍文本
    'sideImageSize' => 'cover',    // 侧栏背景 background-size
    'sideImagePosition' => 'center',    // 侧栏背景 background-position
    'sideOverlay' => true,    // 侧栏是否显示渐变遮罩
    'sideTitle' => '企业级后台管理平台',    // 侧栏主标题
    'sideText' => 'XfAdmin 提供组件化、标准化的一站式企业后�…',    // 侧栏说明文本
    // sideList：侧栏要点列表 `['icon'=>..,'text'=>..]`
    'sideList' => [
        [/* … */],
        [/* … */],
        [/* … */],
    ],
    'sideVariant' => 'primary',    // 侧栏语义变体（primary/info/success/…）
    'user' => [],    // 用户信息（name/avatar/email/role 等）
    'showBackToTop' => false,    // 是否显示「回到顶部」按钮
    'captcha' => false,    // 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位
    'code' => 404,
    'image' => null,    // 默认取包内 images/svg/{code}.svg
    'home_url' => '/',
    'home_text' => '返回首页',
    'card' => false,
]);
```

</details>

> **渲染骨架**：主要 class `xf-error-code` `display-1` `fw-bold` `text-primary` `p-2` `text-center` `btn` `btn-primary`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` | 追加到 <form> 的 class |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；开关：非空 / 真值时启用对应区块 |
| `heading` | string | `'Page Not Found'` | **文本槽位**：输出前自动 HTML 转义 |
| `subheading` | string | `''` | 副标题（回退历史字段 subtitle） |
| `copyright` | string | `''` | 版权文案 |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `''` | **文本槽位**：输出前自动 HTML 转义 |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` | 提交按钮配置（字符串或数组：text/class/variant/icon） |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` | 表单下方补充内容（原样输出） |
| `beforeForm` | string | `''` | 插入到 <form> 之前的内容（原样输出） |
| `afterForm` | string | `''` | 插入到 </form> 之后的内容（原样输出） |
| `prepend` | string | `''` | 前缀内容（原样输出，如输入组文本/图标） |
| `append` | string | `''` | 后缀内容（原样输出，常用于协议说明） |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` | 「去登录」链接地址 |
| `registerRedirect` | string | `'/register'` | 「去注册」链接地址 |
| `backLink` | mixed | `null` | 返回链接配置（保留兼容） |
| `footerLinks` | array | `[]` | 页脚链接 `[['url'=>..,'text'=>..]]` |
| `socialButtons` | array | `[]` | 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]` |
| `sideImage` | string | `'auth.jpg'` | 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径） |
| `sideImageAlt` | string | `''` | 侧栏背景图无障碍文本 |
| `sideImageSize` | string | `'cover'` | 侧栏背景 background-size |
| `sideImagePosition` | string | `'center'` | 侧栏背景 background-position |
| `sideOverlay` | bool | `true` | 侧栏是否显示渐变遮罩 |
| `sideTitle` | string | `'企业级后台管理平台'` | 侧栏主标题 |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` | 侧栏说明文本 |
| `sideList` | array | `[{…}, {…}, {…}]` | 侧栏要点列表 `['icon'=>..,'text'=>..]` |
| `sideVariant` | string | `'primary'` | 侧栏语义变体（primary/info/success/…） |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` | 是否显示「回到顶部」按钮 |
| `captcha` | bool | `false` | 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位 |
| `code` | int | `404` | 源码用法：`$code = (string) $this->get('code');` |
| `image` | mixed | `null` | 默认取包内 images/svg/{code}.svg |
| `home_url` | string | `'/'` | URL：经协议白名单校验（拦截 `javascript:` 等） |
| `home_text` | string | `'返回首页'` | **文本槽位**：输出前自动 HTML 转义 |
| `card` | bool | `false` | 是否以卡片容器呈现（部分组件为遗留键） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `comingSoon`

即将上线页（倒计时 + 订阅）。

> **类**：`zxf\XfAdmin\Components\Layout\ComingSoon`
> **文件**：`src/Components/Layout/ComingSoon.php`（59 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::comingSoon([
    'heading'  => '即将上线',
    'message'  => '我们正在努力，敬请期待！',
    'deadline' => '2026-12-31 00:00:00',
    'image'    => null,
    'subscribe'=> true,          // 显示订阅表单
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::comingSoon([
    'layout' => 'base',    // 布局模式（各组件不同，如 vertical/horizontal）
    'class' => '',    // 附加到根元素的自定义 class
    'id' => '',    // 根元素 id（留空自动生成唯一 id）
    'bodyClass' => '',    // 追加到 <form> 的 class
    'theme' => 'light',    // 主题（light / dark / 图表主题名）
    // brand：品牌信息（name/logo/url）
    'brand' => [
        'name' => 'XfAdmin',
        'url' => '/',
        'logo' => null,
    ],
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'heading' => '即将上线',
    'subheading' => '',    // 副标题（回退历史字段 subtitle）
    'copyright' => '',    // 版权文案
    'status' => '',    // 状态值 / 状态映射
    'message' => '我们正在努力打造精彩内容，敬请期待。',
    'action' => '',    // 表单提交地址 / 动作类型
    'method' => 'POST',    // HTTP 方法（GET/POST/PUT/DELETE）
    'ajax' => true,    // AJAX 地址或 DataTables 原生 ajax 配置
    'fields' => [],    // 字段定义数组（表单字段 / 详情字段）
    'buttons' => [],    // 按钮定义数组
    'submit' => '提交',    // 提交按钮配置（字符串或数组：text/class/variant/icon）
    'content' => '',    // 内容区（可为 HTML 字符串、组件实例或数组）
    'below' => '',    // 表单下方补充内容（原样输出）
    'beforeForm' => '',    // 插入到 <form> 之前的内容（原样输出）
    'afterForm' => '',    // 插入到 </form> 之后的内容（原样输出）
    'prepend' => '',    // 前缀内容（原样输出，如输入组文本/图标）
    'append' => '',    // 后缀内容（原样输出，常用于协议说明）
    'links' => [],    // 链接数组（导航 / 底部链接）
    'loginRedirect' => '/login',    // 「去登录」链接地址
    'registerRedirect' => '/register',    // 「去注册」链接地址
    'backLink' => null,    // 返回链接配置（保留兼容）
    'footerLinks' => [],    // 页脚链接 `[['url'=>..,'text'=>..]]`
    'socialButtons' => [],    // 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]`
    'sideImage' => 'auth.jpg',    // 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径）
    'sideImageAlt' => '',    // 侧栏背景图无障碍文本
    'sideImageSize' => 'cover',    // 侧栏背景 background-size
    'sideImagePosition' => 'center',    // 侧栏背景 background-position
    'sideOverlay' => true,    // 侧栏是否显示渐变遮罩
    'sideTitle' => '企业级后台管理平台',    // 侧栏主标题
    'sideText' => 'XfAdmin 提供组件化、标准化的一站式企业后�…',    // 侧栏说明文本
    // sideList：侧栏要点列表 `['icon'=>..,'text'=>..]`
    'sideList' => [
        [/* … */],
        [/* … */],
        [/* … */],
    ],
    'sideVariant' => 'primary',    // 侧栏语义变体（primary/info/success/…）
    'user' => [],    // 用户信息（name/avatar/email/role 等）
    'showBackToTop' => false,    // 是否显示「回到顶部」按钮
    'captcha' => false,    // 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位
    'deadline' => null,
    'image' => null,
    'subscribe' => true,
    'card' => false,
]);
```

</details>

> **渲染骨架**：主要 class `text-center` `d-flex` `justify-content-center` `gap-3` `my-4` `card` `mb-0` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` | 追加到 <form> 的 class |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；开关：非空 / 真值时启用对应区块 |
| `heading` | string | `'即将上线'` | **文本槽位**：输出前自动 HTML 转义 |
| `subheading` | string | `''` | 副标题（回退历史字段 subtitle） |
| `copyright` | string | `''` | 版权文案 |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `'我们正在努力打造精彩内容，敬请期待。'` | **文本槽位**：输出前自动 HTML 转义 |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` | 提交按钮配置（字符串或数组：text/class/variant/icon） |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` | 表单下方补充内容（原样输出） |
| `beforeForm` | string | `''` | 插入到 <form> 之前的内容（原样输出） |
| `afterForm` | string | `''` | 插入到 </form> 之后的内容（原样输出） |
| `prepend` | string | `''` | 前缀内容（原样输出，如输入组文本/图标） |
| `append` | string | `''` | 后缀内容（原样输出，常用于协议说明） |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` | 「去登录」链接地址 |
| `registerRedirect` | string | `'/register'` | 「去注册」链接地址 |
| `backLink` | mixed | `null` | 返回链接配置（保留兼容） |
| `footerLinks` | array | `[]` | 页脚链接 `[['url'=>..,'text'=>..]]` |
| `socialButtons` | array | `[]` | 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]` |
| `sideImage` | string | `'auth.jpg'` | 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径） |
| `sideImageAlt` | string | `''` | 侧栏背景图无障碍文本 |
| `sideImageSize` | string | `'cover'` | 侧栏背景 background-size |
| `sideImagePosition` | string | `'center'` | 侧栏背景 background-position |
| `sideOverlay` | bool | `true` | 侧栏是否显示渐变遮罩 |
| `sideTitle` | string | `'企业级后台管理平台'` | 侧栏主标题 |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` | 侧栏说明文本 |
| `sideList` | array | `[{…}, {…}, {…}]` | 侧栏要点列表 `['icon'=>..,'text'=>..]` |
| `sideVariant` | string | `'primary'` | 侧栏语义变体（primary/info/success/…） |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` | 是否显示「回到顶部」按钮 |
| `captcha` | bool | `false` | 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位 |
| `deadline` | mixed | `null` | 截止时间（倒计时目标，任意可被 strtotime 解析的字符串）；开关：非空 / 真值时启用对应区块 |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径）；开关：非空 / 真值时启用对应区块 |
| `subscribe` | bool | `true` | 是否显示订阅表单；开关：非空 / 真值时启用对应区块 |
| `card` | bool | `false` | 是否以卡片容器呈现（部分组件为遗留键） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `maintenance`

系统维护页（503 风格）。

> **类**：`zxf\XfAdmin\Components\Layout\Maintenance`
> **文件**：`src/Components/Layout/Maintenance.php`（45 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::maintenance([
    'heading' => '网站维护中',
    'message' => '我们正在进行系统升级，稍后回来。',
    'image'   => null,
    'contact' => 'support@example.com',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::maintenance([
    'layout' => 'base',    // 布局模式（各组件不同，如 vertical/horizontal）
    'class' => '',    // 附加到根元素的自定义 class
    'id' => '',    // 根元素 id（留空自动生成唯一 id）
    'bodyClass' => '',    // 追加到 <form> 的 class
    'theme' => 'light',    // 主题（light / dark / 图表主题名）
    // brand：品牌信息（name/logo/url）
    'brand' => [
        'name' => 'XfAdmin',
        'url' => '/',
        'logo' => null,
    ],
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'heading' => '网站维护中',
    'subheading' => '',    // 副标题（回退历史字段 subtitle）
    'copyright' => '',    // 版权文案
    'status' => '',    // 状态值 / 状态映射
    'message' => '我们正在进行例行维护，请稍后再访问。',
    'action' => '',    // 表单提交地址 / 动作类型
    'method' => 'POST',    // HTTP 方法（GET/POST/PUT/DELETE）
    'ajax' => true,    // AJAX 地址或 DataTables 原生 ajax 配置
    'fields' => [],    // 字段定义数组（表单字段 / 详情字段）
    'buttons' => [],    // 按钮定义数组
    'submit' => '提交',    // 提交按钮配置（字符串或数组：text/class/variant/icon）
    'content' => '',    // 内容区（可为 HTML 字符串、组件实例或数组）
    'below' => '',    // 表单下方补充内容（原样输出）
    'beforeForm' => '',    // 插入到 <form> 之前的内容（原样输出）
    'afterForm' => '',    // 插入到 </form> 之后的内容（原样输出）
    'prepend' => '',    // 前缀内容（原样输出，如输入组文本/图标）
    'append' => '',    // 后缀内容（原样输出，常用于协议说明）
    'links' => [],    // 链接数组（导航 / 底部链接）
    'loginRedirect' => '/login',    // 「去登录」链接地址
    'registerRedirect' => '/register',    // 「去注册」链接地址
    'backLink' => null,    // 返回链接配置（保留兼容）
    'footerLinks' => [],    // 页脚链接 `[['url'=>..,'text'=>..]]`
    'socialButtons' => [],    // 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]`
    'sideImage' => 'auth.jpg',    // 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径）
    'sideImageAlt' => '',    // 侧栏背景图无障碍文本
    'sideImageSize' => 'cover',    // 侧栏背景 background-size
    'sideImagePosition' => 'center',    // 侧栏背景 background-position
    'sideOverlay' => true,    // 侧栏是否显示渐变遮罩
    'sideTitle' => '企业级后台管理平台',    // 侧栏主标题
    'sideText' => 'XfAdmin 提供组件化、标准化的一站式企业后�…',    // 侧栏说明文本
    // sideList：侧栏要点列表 `['icon'=>..,'text'=>..]`
    'sideList' => [
        [/* … */],
        [/* … */],
        [/* … */],
    ],
    'sideVariant' => 'primary',    // 侧栏语义变体（primary/info/success/…）
    'user' => [],    // 用户信息（name/avatar/email/role 等）
    'showBackToTop' => false,    // 是否显示「回到顶部」按钮
    'captcha' => false,    // 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位
    'image' => null,
    'contact' => null,
    'card' => false,
]);
```

</details>

> **渲染骨架**：主要 class `text-center`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` | 追加到 <form> 的 class |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；开关：非空 / 真值时启用对应区块 |
| `heading` | string | `'网站维护中'` | **文本槽位**：输出前自动 HTML 转义 |
| `subheading` | string | `''` | 副标题（回退历史字段 subtitle） |
| `copyright` | string | `''` | 版权文案 |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `'我们正在进行例行维护，请稍后再访问。'` | **文本槽位**：输出前自动 HTML 转义 |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` | 提交按钮配置（字符串或数组：text/class/variant/icon） |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` | 表单下方补充内容（原样输出） |
| `beforeForm` | string | `''` | 插入到 <form> 之前的内容（原样输出） |
| `afterForm` | string | `''` | 插入到 </form> 之后的内容（原样输出） |
| `prepend` | string | `''` | 前缀内容（原样输出，如输入组文本/图标） |
| `append` | string | `''` | 后缀内容（原样输出，常用于协议说明） |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` | 「去登录」链接地址 |
| `registerRedirect` | string | `'/register'` | 「去注册」链接地址 |
| `backLink` | mixed | `null` | 返回链接配置（保留兼容） |
| `footerLinks` | array | `[]` | 页脚链接 `[['url'=>..,'text'=>..]]` |
| `socialButtons` | array | `[]` | 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]` |
| `sideImage` | string | `'auth.jpg'` | 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径） |
| `sideImageAlt` | string | `''` | 侧栏背景图无障碍文本 |
| `sideImageSize` | string | `'cover'` | 侧栏背景 background-size |
| `sideImagePosition` | string | `'center'` | 侧栏背景 background-position |
| `sideOverlay` | bool | `true` | 侧栏是否显示渐变遮罩 |
| `sideTitle` | string | `'企业级后台管理平台'` | 侧栏主标题 |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` | 侧栏说明文本 |
| `sideList` | array | `[{…}, {…}, {…}]` | 侧栏要点列表 `['icon'=>..,'text'=>..]` |
| `sideVariant` | string | `'primary'` | 侧栏语义变体（primary/info/success/…） |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` | 是否显示「回到顶部」按钮 |
| `captcha` | bool | `false` | 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位 |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径）；开关：非空 / 真值时启用对应区块 |
| `contact` | mixed | `null` | 联系信息（渲染为 mailto 链接）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `card` | bool | `false` | 是否以卡片容器呈现（部分组件为遗留键） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `emptyState`

空状态页（无数据占位插画+文案）。

> **类**：`zxf\XfAdmin\Components\Layout\EmptyState`
> **文件**：`src/Components/Layout/EmptyState.php`（42 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::emptyState([
    'icon'   => 'ti ti-inbox',
    'image'  => null,               // 或用图片替代图标
    'title'  => '暂无数据',
    'text'   => '当前还没有任何记录',
    'action' => '<a href="#" class="btn btn-primary">新建</a>',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::emptyState([
    'icon' => 'ti ti-inbox',
    'image' => null,
    'title' => '暂无数据',
    'text' => null,
    'action' => null,
]);
```

</details>

> **渲染骨架**：主要 class `mt-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `icon` | string | `'ti ti-inbox'` | Tabler 图标 class，如 `ti ti-user`；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径）；开关：非空 / 真值时启用对应区块 |
| `title` | string | `'暂无数据'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `action` | mixed | `null` | 表单提交地址 / 动作类型；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `landing`

落地页/营销首页（英雄区+特性+CTA）。

> **类**：`zxf\XfAdmin\Components\Layout\Landing`
> **文件**：`src/Components/Layout/Landing.php`（211 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::landing([
    'brand'   => 'XfAdmin',
    'nav'     => [['text'=>'功能','url'=>'#features'],['text'=>'价格','url'=>'#pricing']],
    'hero'    => ['title'=>'…','subtitle'=>'…','primary'=>'立即体验','secondary'=>'查看文档','image'=>'gallery/1.jpg'],
    'stats'   => [['value'=>'100+','label'=>'组件'}, ...],
    'features'=> [['icon'=>'ti ti-bolt','title'=>'…','text'=>'…']],
    'pricing' => [['title'=>'专业版','price'=>'$99','features'=>[...],'highlight'=>true,'button'=>'选择']],
    'testimonials' => [['name'=>'张三','role'=>'CTO','avatar'=>'users/user-1.jpg','text'=>'…']],
    'footer'  => ['text'=>'© 2026 …','links'=>[['text'=>'关于','url'=>'#']]],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::landing([
    'brand' => 'XfAdmin',
    'nav' => [],
    'hero' => [],
    'stats' => [],
    'features' => [],
    'pricing' => [],
    'testimonials' => [],
    'footer' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `nav[]` 元素键：`url`（默认 `#`）、`text`
- `hero[]` 元素键：`image`、`title`、`subtitle`、`primary`（默认 `立即体验`）、`secondary`（默认 `了解更多`）
- `stats[]` 元素键：`value`、`label`
- `features[]` 元素键：`icon`（默认 `ti ti-bolt`）、`title`、`text`
- `pricing[]` 元素键：`highlight`、`title`、`price`、`features`、`button`（默认 `选择`）
- `testimonials[]` 元素键：`avatar`、`text`、`name`、`role`
- `footer[]` 元素键：`icon`（默认 `ti ti-bolt`）、`title`、`text`

> **渲染骨架**：主要 class `landing-header` `navbar-brand` `fw-bold` `fs-4` `text-primary` `d-none` `d-md-flex` `gap-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | string | `'XfAdmin'` | 品牌信息（name/logo/url）；源码用法：`$brand = $this->get('brand');` |
| `nav` | array | `[]` | 导航项数组 |
| `hero` | array | `[]` | 输出到 `` 属性 |
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `features` | array | `[]` | 特性 / 功能列表 |
| `pricing` | array | `[]` | 价格方案配置 |
| `testimonials` | array | `[]` | 用户证言列表 |
| `footer` | array | `[]` | 底部内容（原样输出） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `accountSettingsPanel`

账户设置面板（头像/密码/通知等卡片组）。

> **类**：`zxf\XfAdmin\Components\Layout\AccountSettingsPanel`
> **文件**：`src/Components/Layout/AccountSettingsPanel.php`（64 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::accountSettingsPanel([
    'title' => '账户设置',
    'tabs'  => [
        ['id'=>'profile','label'=>'个人资料','icon'=>'ti ti-user','content'=>'...'],
        ['id'=>'security','label'=>'安全','icon'=>'ti ti-lock','content'=>'...'],
    ],
    'active' => 'profile',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::accountSettingsPanel([
    'title' => '账户设置',
    'tabs' => [],
    'active' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `tabs[]` 元素键：`id`、`icon`（默认 `ti ti-point`）、`label`

> **渲染骨架**：主要 class `list-group` `list-group-flush` `xf-asp-nav` `tab-content` `xf-asp-content` `card` `border-0` `shadow-sm`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'账户设置'` | 标题文本（部分组件为弹窗/tooltip 标题）；源码用法：`$title = $this->get('title');` |
| `tabs` | array | `[]` | 选项卡数组 |
| `active` | mixed | `null` | 是否激活 / 默认选中项；源码用法：`$active = $this->get('active') ?? ($tabs[0]['id'] ?? '');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `menu`

菜单数据组件（导航菜单 DSL，供 sidenav/topnav 使用）。

> **类**：`zxf\XfAdmin\Components\Navigation\Menu`
> **文件**：`src/Components/Navigation/Menu.php`（148 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::menu([
    'mode'  => 'side',
    'current_url' => '/users',      // 可选：自动高亮
    'items' => [
        ['title' => '导航'],                                    // 分组标题
        ['text' => '仪表盘', 'icon' => 'ti ti-layout-dashboard', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
        ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
            ['text' => '用户管理', 'url' => '/users'],
            ['text' => '更多', 'children' => [ ... 无限层级 ... ]],
        ]],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::menu([
    'mode' => 'side',    // side
    'items' => [],
    'current_url' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`title`、`text`、`disabled`、`children`、`icon`、`id`、`url`（默认 `#!`）、`target`、`active`、`badge`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `mode` | string | `'side'` | side |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `current_url` | mixed | `null` | 当前 URL（用于菜单自动高亮）；源码用法：`$current = $this->get('current_url');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `row`

栅格行容器（Bootstrap row 封装）。

> **类**：`zxf\XfAdmin\Components\Grid\Row`
> **文件**：`src/Components/Grid/Row.php`（55 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::row([
    'gutter' => 3,                        // g-3；也可 ['x' => 2, 'y' => 3]
    'cols'   => [
        ['width' => 6, 'content' => $cardA],                 // col-6
        ['width' => ['md' => 6, 'xl' => 4], 'content' => $cardB],
        $cardC,                                              // 自动 col
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::row([
    'gutter' => null,
    'align' => null,    // start|center|end  => align-items-*
    'justify' => null,    // start|center|end|between|around => justify-content-*
    'cols' => [],
    'content' => null,    // 直接传内容（可与 cols 混用）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `cols[]` 元素键：`render`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `gutter` | mixed | `null` | 栅格间距（数字或 `['x'=>2,'y'=>3]`）；源码用法：`$gutter = $this->get('gutter');` |
| `align` | mixed | `null` | start\|center\|end  => align-items-*；开关：非空 / 真值时启用对应区块 |
| `justify` | mixed | `null` | start\|center\|end\|between\|around => justify-content-*；开关：非空 / 真值时启用对应区块 |
| `cols` | array | `[]` | 列数（栅格 / 分区列数） |
| `content` | mixed | `null` | 直接传内容（可与 cols 混用）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `col`

栅格列（Bootstrap col 封装，支持响应式断点）。

> **类**：`zxf\XfAdmin\Components\Grid\Col`
> **文件**：`src/Components/Grid/Col.php`（45 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::col(['width' => 6, 'content' => ...]);
XfAdmin::col(['width' => ['md' => 6, 'xl' => 4], 'offset' => ['md' => 3], 'content' => ...]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::col([
    'width' => null,    // int | 'auto' | [breakpoint => width]
    'offset' => null,
    'order' => null,
    'content' => '',
]);
```

</details>

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `width` | mixed | `null` | int \| 'auto' \| [breakpoint => width] |
| `offset` | mixed | `null` | 栅格偏移；源码用法：`foreach ((array) ($this->get('offset') ?? []) as $bp => $o) {` |
| `order` | mixed | `null` | 排序规则，如 `[[0, 'asc']]`；源码用法：`foreach ((array) ($this->get('order') ?? []) as $bp => $o) {` |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。


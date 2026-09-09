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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `lang` | string | `'zh-CN'` |  |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `description` | mixed | `null` | 描述文本 |
| `keywords` | mixed | `null` |  |
| `author` | mixed | `null` | 作者信息 |
| `favicon` | mixed | `null` |  |
| `layout` | string | `'vertical'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `theme` | array | `[]` | 主题（light / dark / 图表主题名） |
| `menu` | array | `[]` | 菜单数据数组 |
| `current_url` | mixed | `null` |  |
| `sidenav` | array | `[]` |  |
| `topbar` | array | `[]` |  |
| `topnav` | mixed | `null` | 水平布局顶部导航（layout=horizontal 时启用） |
| `page_title` | mixed | `null` |  |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `container` | string | `'container-fluid'` |  |
| `footer` | array | `[]` | 底部内容（原样输出） |
| `customizer` | bool | `true` |  |
| `preloader` | bool | `false` | 页面加载动画（true = 启用） |
| `head` | mixed | `null` | <head> 附加内容 |
| `scripts` | mixed | `null` | </body> 前附加内容 |
| `body_class` | mixed | `null` |  |
| `csrf` | mixed | `null` | CSRF Token（Laravel 下自动注入 csrf_token()） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | array | `[]` | 品牌信息（name/logo/url） |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等） |
| `menu` | array | `[]` | 菜单数据数组 |
| `current_url` | mixed | `null` |  |
| `append` | mixed | `null` | 菜单下方附加内容 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | bool | `true` | 品牌信息（name/logo/url） |
| `search` | bool | `true` | 是否启用搜索 |
| `search_placeholder` | string | `'Search...'` |  |
| `search_modal` | bool | `false` | 点击搜索图标弹出全屏模态（替代内联搜索框） |
| `left` | mixed | `null` |  |
| `theme_toggle` | bool | `true` |  |
| `fullscreen` | bool | `true` |  |
| `customizer` | bool | `true` |  |
| `languages` | array | `[]` |  |
| `notifications` | bool | `false` |  |
| `messages` | bool | `false` |  |
| `apps` | bool | `false` |  |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等） |
| `right` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | bool | `true` | 品牌信息（name/logo/url） |
| `sidenav_toggle` | bool | `false` | 纯水平布局默认不显示侧栏切换按钮 |
| `menu` | array | `[]` | 菜单数据数组 |
| `current_url` | mixed | `null` |  |
| `search` | bool | `false` | 是否启用搜索 |
| `search_placeholder` | string | `'Search for something...'` |  |
| `mega` | bool | `false` |  |
| `left` | mixed | `null` |  |
| `languages` | array | `[]` |  |
| `messages` | bool | `false` |  |
| `notifications` | bool | `false` |  |
| `theme_toggle` | bool | `true` |  |
| `fullscreen` | bool | `true` |  |
| `customizer` | bool | `true` |  |
| `user` | bool | `false` | 用户信息（name/avatar/email/role 等） |
| `apps` | bool | `false` | 应用启动器（圆形九宫格 #apps-dropdown-rounded） |
| `right` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

### `topnav`

`topnav` 是 `topNav` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\TopNav`，参数与用法完全一致。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `breadcrumb` | array | `[]` |  |
| `actions` | mixed | `null` | 操作区内容（按钮组 / 行操作定义） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义） |
| `right` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `customizer`

右侧主题定制面板（明暗/配色/布局切换）。

> **类**：`zxf\XfAdmin\Components\Layout\Customizer`
> **文件**：`src/Components/Layout/Customizer.php`（107 行）
> **依赖插件**：无

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'Admin Customizer'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `subtitle` | string | `'快速配置后台界面的布局、皮肤与偏好'` | 副标题文本 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | base \| card \| split（basic 视为 base） |
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
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
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
| `sideImage` | string | `'auth.jpg'` |  |
| `sideImageAlt` | string | `''` | 背景图 alt |
| `sideImageSize` | string | `'cover'` | 背景尺寸（CSS background-size：cover/contain/100% 100%...） |
| `sideImagePosition` | string | `'center'` | 背景定位（CSS background-position：center/top left...） |
| `sideOverlay` | bool | `true` | 是否叠加底部渐变遮罩（保证侧栏白字可读；false 时无暗角） |
| `sideTitle` | string | `'企业级后台管理平台'` | 侧栏标题（默认官方文案，可按需覆盖） |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` | 侧栏文案 |
| `sideList` | array | `[{…}, {…}, {…}]` | 数组结构（见组件用法示例） |
| `sideVariant` | string | `'primary'` | 侧栏强调色（primary/info/success/...，无背景图时作为纯色渐变） |
| `user` | array | `[]` | ['name'=>..., 'avatar'=>..., 'email'=>...] |
| `showBackToTop` | bool | `false` |  |
| `captcha` | bool | `false` | bool=渲染占位；string=原样输出（如 web component HTML） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

### `signIn`

`signIn` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `signUp`

`signUp` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `resetPass`

`resetPass` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `newPass`

`newPass` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `twoFactor`

`twoFactor` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `lang` | string | `'zh-CN'` |  |
| `title` | mixed | `null` | <title>，默认取 heading |
| `theme` | array | `[]` | 主题（light / dark / 图表主题名） |
| `user` | array | `['name'=>'User', 'avatar'=>'']` | 用户信息（name/avatar/email/role 等） |
| `action` | string | `'#'` | 表单提交地址 / 动作类型 |
| `heading` | string | `'屏幕已锁定'` |  |
| `text` | string | `'请输入密码以继续'` | 正文/按钮文案（纯文本语义，输出时转义） |
| `brand` | string | `'XfAdmin'` | 品牌信息（name/logo/url） |
| `below` | mixed | `null` | 卡片下方补充内容（如「切换账号」链接） |
| `copyright` | mixed | `null` |  |
| `favicon` | mixed | `null` |  |
| `head` | mixed | `null` |  |
| `scripts` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

### `deleteAccount`

`deleteAccount` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `successMail`

`successMail` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

### `loginPin`

`loginPin` 是 `authPage` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Layout\AuthPage`，参数与用法完全一致。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` |  |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `heading` | string | `'Page Not Found'` |  |
| `subheading` | string | `''` |  |
| `copyright` | string | `''` |  |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `''` |  |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` |  |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` |  |
| `beforeForm` | string | `''` |  |
| `afterForm` | string | `''` |  |
| `prepend` | string | `''` |  |
| `append` | string | `''` |  |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` |  |
| `registerRedirect` | string | `'/register'` |  |
| `backLink` | mixed | `null` |  |
| `footerLinks` | array | `[]` |  |
| `socialButtons` | array | `[]` |  |
| `sideImage` | string | `'auth.jpg'` |  |
| `sideImageAlt` | string | `''` |  |
| `sideImageSize` | string | `'cover'` |  |
| `sideImagePosition` | string | `'center'` |  |
| `sideOverlay` | bool | `true` |  |
| `sideTitle` | string | `'企业级后台管理平台'` |  |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` |  |
| `sideList` | array | `[{…}, {…}, {…}]` | 数组结构（见组件用法示例） |
| `sideVariant` | string | `'primary'` |  |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` |  |
| `captcha` | bool | `false` |  |
| `code` | int | `404` |  |
| `image` | mixed | `null` | 默认取包内 images/svg/{code}.svg |
| `home_url` | string | `'/'` |  |
| `home_text` | string | `'返回首页'` |  |
| `card` | bool | `false` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` |  |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `heading` | string | `'即将上线'` |  |
| `subheading` | string | `''` |  |
| `copyright` | string | `''` |  |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `'我们正在努力打造精彩内容，敬请期待。'` |  |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` |  |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` |  |
| `beforeForm` | string | `''` |  |
| `afterForm` | string | `''` |  |
| `prepend` | string | `''` |  |
| `append` | string | `''` |  |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` |  |
| `registerRedirect` | string | `'/register'` |  |
| `backLink` | mixed | `null` |  |
| `footerLinks` | array | `[]` |  |
| `socialButtons` | array | `[]` |  |
| `sideImage` | string | `'auth.jpg'` |  |
| `sideImageAlt` | string | `''` |  |
| `sideImageSize` | string | `'cover'` |  |
| `sideImagePosition` | string | `'center'` |  |
| `sideOverlay` | bool | `true` |  |
| `sideTitle` | string | `'企业级后台管理平台'` |  |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` |  |
| `sideList` | array | `[{…}, {…}, {…}]` | 数组结构（见组件用法示例） |
| `sideVariant` | string | `'primary'` |  |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` |  |
| `captcha` | bool | `false` |  |
| `deadline` | mixed | `null` |  |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径） |
| `subscribe` | bool | `true` |  |
| `card` | bool | `false` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `layout` | string | `'base'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | string | `''` | 根元素 id（留空自动生成唯一 id） |
| `bodyClass` | string | `''` |  |
| `theme` | string | `'light'` | 主题（light / dark / 图表主题名） |
| `brand` | array | `['name'=>'XfAdmin', 'url'=>'/', 'logo'=>{…}]` | 品牌信息（name/logo/url） |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `heading` | string | `'网站维护中'` |  |
| `subheading` | string | `''` |  |
| `copyright` | string | `''` |  |
| `status` | string | `''` | 状态值 / 状态映射 |
| `message` | string | `'我们正在进行例行维护，请稍后再访问。'` |  |
| `action` | string | `''` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `ajax` | bool | `true` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `fields` | array | `[]` | 字段定义数组（表单字段 / 详情字段） |
| `buttons` | array | `[]` | 按钮定义数组 |
| `submit` | string | `'提交'` |  |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `below` | string | `''` |  |
| `beforeForm` | string | `''` |  |
| `afterForm` | string | `''` |  |
| `prepend` | string | `''` |  |
| `append` | string | `''` |  |
| `links` | array | `[]` | 链接数组（导航 / 底部链接） |
| `loginRedirect` | string | `'/login'` |  |
| `registerRedirect` | string | `'/register'` |  |
| `backLink` | mixed | `null` |  |
| `footerLinks` | array | `[]` |  |
| `socialButtons` | array | `[]` |  |
| `sideImage` | string | `'auth.jpg'` |  |
| `sideImageAlt` | string | `''` |  |
| `sideImageSize` | string | `'cover'` |  |
| `sideImagePosition` | string | `'center'` |  |
| `sideOverlay` | bool | `true` |  |
| `sideTitle` | string | `'企业级后台管理平台'` |  |
| `sideText` | string | `'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。'` |  |
| `sideList` | array | `[{…}, {…}, {…}]` | 数组结构（见组件用法示例） |
| `sideVariant` | string | `'primary'` |  |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `showBackToTop` | bool | `false` |  |
| `captcha` | bool | `false` |  |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径） |
| `contact` | mixed | `null` |  |
| `card` | bool | `false` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `icon` | string | `'ti ti-inbox'` | Tabler 图标 class，如 `ti ti-user` |
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径） |
| `title` | string | `'暂无数据'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义） |
| `action` | mixed | `null` | 表单提交地址 / 动作类型 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `brand` | string | `'XfAdmin'` | 品牌信息（name/logo/url） |
| `nav` | array | `[]` |  |
| `hero` | array | `[]` |  |
| `stats` | array | `[]` |  |
| `features` | array | `[]` |  |
| `pricing` | array | `[]` |  |
| `testimonials` | array | `[]` |  |
| `footer` | array | `[]` | 底部内容（原样输出） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'账户设置'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `tabs` | array | `[]` | 选项卡数组 |
| `active` | mixed | `null` | 是否激活 / 默认选中项 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `mode` | string | `'side'` | side |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `current_url` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `gutter` | mixed | `null` | 栅格间距（数字或 `['x'=>2,'y'=>3]`） |
| `align` | mixed | `null` | start\|center\|end  => align-items-* |
| `justify` | mixed | `null` | start\|center\|end\|between\|around => justify-content-* |
| `cols` | array | `[]` | 列数（栅格 / 分区列数） |
| `content` | mixed | `null` | 直接传内容（可与 cols 混用） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

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

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `width` | mixed | `null` | int \| 'auto' \| [breakpoint => width] |
| `offset` | mixed | `null` | 栅格偏移 |
| `order` | mixed | `null` | 排序规则，如 `[[0, 'asc']]` |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


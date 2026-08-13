# 组件详细参考（自动生成 · 全量 210 个组件 / 213 个别名）

> 本文档由 `tools/gen_docs.php` 扫描全部已注册组件自动生成，列出每个组件的别名、分类、类、描述、依赖资源、全部 `defaults()` 选项（含类型 / 默认值 / 行内说明）、链式方法与实际调用示例。
> 调用统一形式：`XfAdmin::<alias>(array $options)`。所有组件均支持通用键 `id` / `class` / `attributes`。
> 资源前缀统一为 `zxf/xfadmin`，无需发布即可在 `demo/` 中直接加载。返回 → [组件总览](components.md)

## 分类索引

- **布局 / 页面**（15）
  - [`page`](#page)
  - [`sidenav`](#sidenav)
  - [`topbar`](#topbar)
  - [`topNav`](#topnav)
  - [`pageTitle`](#pagetitle)
  - [`footer`](#footer)
  - [`customizer`](#customizer)
  - [`authPage`](#authpage)
  - [`errorPage`](#errorpage)
  - [`comingSoon`](#comingsoon)
  - [`maintenance`](#maintenance)
  - [`emptyState`](#emptystate)
  - [`lockScreen`](#lockscreen)
  - [`landing`](#landing)
  - [`accountSettingsPanel`](#accountsettingspanel)
- **导航**（1）
  - [`menu`](#menu)
- **栅格**（2）
  - [`row`](#row)
  - [`col`](#col)
- **UI 基础**（53）
  - [`card`](#card)
  - [`statCard`](#statcard)
  - [`alert`](#alert)
  - [`badge`](#badge)
  - [`button`](#button)
  - [`dropdown`](#dropdown)
  - [`modal`](#modal)
  - [`offcanvas`](#offcanvas)
  - [`tabs`](#tabs)
  - [`accordion`](#accordion)
  - [`progress`](#progress)
  - [`spinner`](#spinner)
  - [`pagination`](#pagination)
  - [`listGroup`](#listgroup)
  - [`avatar`](#avatar)
  - [`icon`](#icon)
  - [`toast`](#toast)
  - [`timeline`](#timeline)
  - [`carousel`](#carousel)
  - [`breadcrumb`](#breadcrumb)
  - [`tooltip`](#tooltip)
  - [`popover`](#popover)
  - [`placeholder`](#placeholder)
  - [`collapse`](#collapse)
  - [`scrollspy`](#scrollspy)
  - [`ratio`](#ratio)
  - [`rating`](#rating)
  - [`ribbon`](#ribbon)
  - [`chip`](#chip)
  - [`stepper`](#stepper)
  - [`descriptionList`](#descriptionlist)
  - [`loadingButton`](#loadingbutton)
  - [`avatarGroup`](#avatargroup)
  - [`backToTop`](#backtotop)
  - [`callout`](#callout)
  - [`countdown`](#countdown)
  - [`countUp`](#countup)
  - [`divider`](#divider)
  - [`kbd`](#kbd)
  - [`media`](#media)
  - [`skeleton`](#skeleton)
  - [`switch`](#switch)
  - [`codeBlock`](#codeblock)
  - [`empty`](#empty)
  - [`toolbar`](#toolbar)
  - [`searchBox`](#searchbox)
  - [`colorPalette`](#colorpalette)
  - [`iconSet`](#iconset)
  - [`videoEmbed`](#videoembed)
  - [`commandPalette`](#commandpalette)
  - [`notificationCenter`](#notificationcenter)
  - [`dropzoneUpload`](#dropzoneupload)
  - [`invoicePrintButton`](#invoiceprintbutton)
- **表单**（20）
  - [`form`](#form)
  - [`input`](#input)
  - [`textarea`](#textarea)
  - [`select`](#select)
  - [`check`](#check)
  - [`slider`](#slider)
  - [`dateRange`](#daterange)
  - [`editor`](#editor)
  - [`upload`](#upload)
  - [`colorPicker`](#colorpicker)
  - [`tags`](#tags)
  - [`maskedInput`](#maskedinput)
  - [`wizard`](#wizard)
  - [`passwordStrength`](#passwordstrength)
  - [`formElements`](#formelements)
  - [`formLayout`](#formlayout)
  - [`formOtherPlugin`](#formotherplugin)
  - [`formValidation`](#formvalidation)
  - [`twoFactorInput`](#twofactorinput)
  - [`quantityStepper`](#quantitystepper)
- **图表 / 地图**（7）
  - [`apexChart`](#apexchart)
  - [`apexTree`](#apextree)
  - [`apexSankey`](#apexsankey)
  - [`echart`](#echart)
  - [`vectorMap`](#vectormap)
  - [`leafletMap`](#leafletmap)
  - [`googleMap`](#googlemap)
- **表格**（4）
  - [`table`](#table)
  - [`dataTable`](#datatable)
  - [`tablesCustom`](#tablescustom)
  - [`dataTableToolbar`](#datatabletoolbar)
- **数据 / 业务**（91）
  - [`pricingCard`](#pricingcard)
  - [`faq`](#faq)
  - [`profileHeader`](#profileheader)
  - [`productCard`](#productcard)
  - [`kanban`](#kanban)
  - [`chatBox`](#chatbox)
  - [`invoiceTable`](#invoicetable)
  - [`mailList`](#maillist)
  - [`fileManager`](#filemanager)
  - [`widget`](#widget)
  - [`activityFeed`](#activityfeed)
  - [`gallery`](#gallery)
  - [`blogList`](#bloglist)
  - [`invoiceList`](#invoicelist)
  - [`searchResults`](#searchresults)
  - [`permissionMatrix`](#permissionmatrix)
  - [`apiKeys`](#apikeys)
  - [`commentThread`](#commentthread)
  - [`emailCompose`](#emailcompose)
  - [`customers`](#customers)
  - [`orders`](#orders)
  - [`taskList`](#tasklist)
  - [`deals`](#deals)
  - [`orderDetails`](#orderdetails)
  - [`productDetails`](#productdetails)
  - [`projects`](#projects)
  - [`projectDetails`](#projectdetails)
  - [`outlook`](#outlook)
  - [`forumThread`](#forumthread)
  - [`blogArticle`](#blogarticle)
  - [`roles`](#roles)
  - [`invoiceCreate`](#invoicecreate)
  - [`teamMember`](#teammember)
  - [`testimonial`](#testimonial)
  - [`todoList`](#todolist)
  - [`issueTracker`](#issuetracker)
  - [`voteList`](#votelist)
  - [`metricCard`](#metriccard)
  - [`terms`](#terms)
  - [`contactCard`](#contactcard)
  - [`companyCard`](#companycard)
  - [`clients`](#clients)
  - [`sellers`](#sellers)
  - [`reviewList`](#reviewlist)
  - [`projectTeamBoard`](#projectteamboard)
  - [`emailApp`](#emailapp)
  - [`chatApp`](#chatapp)
  - [`profilePage`](#profilepage)
  - [`invoiceDetail`](#invoicedetail)
  - [`companies`](#companies)
  - [`productCategories`](#productcategories)
  - [`productAdd`](#productadd)
  - [`sellerDetails`](#sellerdetails)
  - [`article`](#article)
  - [`projectActivity`](#projectactivity)
  - [`shoppingCart`](#shoppingcart)
  - [`checkout`](#checkout)
  - [`marketplace`](#marketplace)
  - [`accountSettings`](#accountsettings)
  - [`sitemap`](#sitemap)
  - [`privacyPolicy`](#privacypolicy)
  - [`appManage`](#appmanage)
  - [`warehouse`](#warehouse)
  - [`refunds`](#refunds)
  - [`sales`](#sales)
  - [`purchasedOrders`](#purchasedorders)
  - [`attributes`](#attributes)
  - [`ecommerceSettings`](#ecommercesettings)
  - [`productsGrid`](#productsgrid)
  - [`productViews`](#productviews)
  - [`analyticsDashboard`](#analyticsdashboard)
  - [`ecommerceDashboard`](#ecommercedashboard)
  - [`widgetsDashboard`](#widgetsdashboard)
  - [`moduleNav`](#modulenav)
  - [`moduleGrid`](#modulegrid)
  - [`dashboardGrid`](#dashboardgrid)
  - [`settingsCenter`](#settingscenter)
  - [`reportPage`](#reportpage)
  - [`statMiniSparkline`](#statminisparkline)
  - [`cartSummary`](#cartsummary)
  - [`chatMessageBubble`](#chatmessagebubble)
  - [`chatConversationPanel`](#chatconversationpanel)
  - [`orderTrackingTimeline`](#ordertrackingtimeline)
  - [`featureComparisonTable`](#featurecomparisontable)
  - [`filterSidebar`](#filtersidebar)
  - [`searchResultsRich`](#searchresultsrich)
  - [`socialFeed`](#socialfeed)
  - [`faqAccordion`](#faqaccordion)
  - [`contactList`](#contactlist)
  - [`userProfile`](#userprofile)
  - [`invoiceView`](#invoiceview)
- **杂项**（17）
  - [`calendar`](#calendar)
  - [`treeView`](#treeview)
  - [`nestable`](#nestable)
  - [`lightbox`](#lightbox)
  - [`tour`](#tour)
  - [`clipboard`](#clipboard)
  - [`sweetAlert`](#sweetalert)
  - [`raw`](#raw)
  - [`tinycon`](#tinycon)
  - [`idleTimer`](#idletimer)
  - [`animate`](#animate)
  - [`pdfViewer`](#pdfviewer)
  - [`textDiff`](#textdiff)
  - [`pinBoard`](#pinboard)
  - [`masonry`](#masonry)
  - [`videoPlayer`](#videoplayer)
  - [`i18n`](#i18n)

## page

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Page`

整页骨架（完整 HTML 文档） 一行代码渲染完整后台页面，自动组装：主题属性 + 侧边栏 + 顶栏 + 页面标题 + 内容 + 页脚 + 主题定制面板 + 全部按需资源（去重加载）。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `lang` | `'zh-CN'` | — |
| `title` | `''` | — |
| `description` | `null` | — |
| `keywords` | `null` | — |
| `author` | `null` | — |
| `favicon` | `null` | — |
| `layout` | `'vertical'` | — |
| `theme` | `[]` | — |
| `menu` | `[]` | — |
| `current_url` | `null` | — |
| `sidenav` | `[]` | — |
| `topbar` | `[]` | — |
| `topnav` | `null` | 水平布局顶部导航（layout=horizontal 时启用） |
| `page_title` | `null` | — |
| `content` | `''` | — |
| `container` | `'container-fluid'` | — |
| `footer` | `[]` | — |
| `customizer` | `true` | — |
| `preloader` | `false` | 页面加载动画（true = 启用） |
| `head` | `null` | <head> 附加内容 |
| `scripts` | `null` | </body> 前附加内容 |
| `body_class` | `null` | — |
| `csrf` | `null` | CSRF Token（Laravel 下自动注入 csrf_token()） |

### 示例

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

    // … 其余选项见上表 / 默认值（源码）
]);
```

## sidenav

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Sidenav`

侧边栏（Logo + 可选用户卡片 + 无限极菜单）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `brand` | `[]` | — |
| `user` | `false` | — |
| `menu` | `[]` | — |
| `current_url` | `null` | — |
| `append` | `null` | 菜单下方附加内容 |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'brand'       => [],
            'user'        => false,
            'menu'        => [],
            'current_url' => null,
            'append'      => null, // 菜单下方附加内容
        ]
```
</details>

### 示例

```php
echo XfAdmin::sidenav([

    'brand' => [],

    'user' => false,

    'menu' => [],

    'current_url' => null,

    'append' => null,

]);
```

## topbar

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Topbar`

顶部导航栏

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `brand` | `true` | — |
| `search` | `true` | — |
| `search_placeholder` | `'Search...'` | — |
| `search_modal` | `false` | 点击搜索图标弹出全屏模态（替代内联搜索框） |
| `left` | `null` | — |
| `theme_toggle` | `true` | — |
| `fullscreen` | `true` | — |
| `customizer` | `true` | — |
| `languages` | `[]` | — |
| `notifications` | `false` | — |
| `messages` | `false` | — |
| `apps` | `false` | — |
| `user` | `false` | — |
| `right` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'brand'         => true,
            'search'        => true,
            'search_placeholder' => 'Search...',
            'search_modal'  => false,           // 点击搜索图标弹出全屏模态（替代内联搜索框）
            'left'          => null,
            'theme_toggle'  => true,
            'fullscreen'    => true,
            'customizer'    => true,
            'languages'     => [],
            'notifications' => false,
            'messages'      => false,
            'apps'          => false,
            'user'          => false,
            'right'         => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::topbar([

    'brand' => true,

    'search' => true,

    'search_placeholder' => 'Search...',

    'search_modal' => false,

    'left' => null,

    'theme_toggle' => true,

    'fullscreen' => true,

    'customizer' => true,

    'languages' => [],

    'notifications' => false,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## topNav
_别名：_ `topnav`

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\TopNav`

顶部水平导航（TopNav） 来源：INSPINIA v4.0 layouts-horizontal.html 的 header.app-topbar 与 header.topnav 两个 DOM 的拆分 / 重构 / 合并 —— 原模板中「横向菜单」独立成第二个 header.topnav， 本组件将其合并进 header.app-topbar：菜单渲染在品牌 Logo 之后、右侧工具区（语言 / 消息 / 通知 / 主题 / 用户头像）之前，即「系统头像右侧」的水平菜单条。 布局要点：  - 单一 <header class="app-topbar"> 容器，内部 topbar-menu 使用 flex 三段式；  - 菜单沿用模板 .topnav .navbar-nav 的 class 契约，直接复用 app.min.css 中    已实现的 hover 级联下拉（含无限级嵌套 .dropdown > .dropdown-menu）；  - 小屏（<lg）由 .topnav-toggle-button 触发 Bootstrap collapse 折叠展开。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `brand` | `true` | — |
| `sidenav_toggle` | `false` | 纯水平布局默认不显示侧栏切换按钮 |
| `menu` | `[]` | — |
| `current_url` | `null` | — |
| `search` | `false` | — |
| `search_placeholder` | `'Search for something...'` | — |
| `mega` | `false` | — |
| `left` | `null` | — |
| `languages` | `[]` | — |
| `messages` | `false` | — |
| `notifications` | `false` | — |
| `theme_toggle` | `true` | — |
| `fullscreen` | `true` | — |
| `customizer` | `true` | — |
| `user` | `false` | — |
| `apps` | `false` | 应用启动器（圆形九宫格 #apps-dropdown-rounded） |
| `right` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'brand'              => true,
            'sidenav_toggle'     => false,  // 纯水平布局默认不显示侧栏切换按钮
            'menu'               => [],
            'current_url'        => null,
            'search'             => false,
            'search_placeholder' => 'Search for something...',
            'mega'               => false,
            'left'               => null,
            'languages'          => [],
            'messages'           => false,
            'notifications'      => false,
            'theme_toggle'       => true,
            'fullscreen'         => true,
            'customizer'         => true,
            'user'               => false,
            'apps'               => false,   // 应用启动器（圆形九宫格 #apps-dropdown-rounded）
            'right'              => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::topNav([

    'brand' => true,

    'sidenav_toggle' => false,

    'menu' => [],

    'current_url' => null,

    'search' => false,

    'search_placeholder' => 'Search for something...',

    'mega' => false,

    'left' => null,

    'languages' => [],

    'messages' => false,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## pageTitle

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\PageTitle`

页面标题 + 面包屑

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `breadcrumb` | `[]` | — |
| `actions` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'breadcrumb' => [],
            'actions'    => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::pageTitle([

    'title' => '',

    'breadcrumb' => [],

    'actions' => null,

]);
```

## footer

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Footer`

页脚

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'text'  => null,
            'right' => null,
        ]
```

### 示例

```php
echo XfAdmin::footer([

    'text' => null,

    'right' => null,

]);
```

## customizer

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Customizer`

主题定制面板（offcanvas）——皮肤 / 明暗 / 顶栏色 / 菜单色 / 侧栏尺寸 / 布局位置 与模板 app.js 联动（元素 name 与 id 必须保持模板约定）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'title'    => 'Admin Customizer',
            'subtitle' => '快速配置后台界面的布局、皮肤与偏好',
        ]
```

### 示例

```php
echo XfAdmin::customizer([

    'title' => 'Admin Customizer',

    'subtitle' => '快速配置后台界面的布局、皮肤与偏好',

]);
```

## authPage

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\AuthPage`

认证页骨架（登录 / 注册 / 找回密码 / 锁屏等）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `lang` | `'zh-CN'` | — |
| `title` | `''` | — |
| `theme` | `[]` | — |
| `brand` | `[]` | — |
| `layout` | `'card'` | card | split | basic |
| `heading` | `null` | — |
| `subheading` | `null` | — |
| `content` | `''` | — |
| `card` | `true` | 内容是否包裹在卡片中（layout=split 时固定为卡片） |
| `below` | `null` | — |
| `copyright` | `null` | — |
| `width` | `'col-xxl-4 col-md-6 col-sm-8'` | — |
| `favicon` | `null` | — |
| `head` | `null` | — |
| `scripts` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'lang'       => 'zh-CN',
            'title'      => '',
            'theme'      => [],
            'brand'      => [],
            'layout'     => 'card',    // card | split | basic
            'heading'    => null,
            'subheading' => null,
            'content'    => '',
            'card'       => true,     // 内容是否包裹在卡片中（layout=split 时固定为卡片）
            'below'      => null,
            'copyright'  => null,
            'width'      => 'col-xxl-4 col-md-6 col-sm-8',
            'favicon'    => null,
            'head'       => null,
            'scripts'    => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::authPage([

    'lang' => 'zh-CN',

    'title' => '',

    'theme' => [],

    'brand' => [],

    'layout' => 'card',

    'heading' => null,

    'subheading' => null,

    'content' => '',

    'card' => true,

    'below' => null,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## errorPage

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\ErrorPage`

错误页（404 / 500 / 503 …）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::errorPage([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## comingSoon

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\ComingSoon`

即将上线页（pages-coming-soon.html）—— 含倒计时

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::comingSoon([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## maintenance

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Maintenance`

维护中页（maintenance.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::maintenance([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## emptyState

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\EmptyState`

空状态占位（pages-empty.html / pages-search-results.html 无结果）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `icon` | `'ti ti-inbox'` | — |
| `image` | `null` | — |
| `title` | `'暂无数据'` | — |
| `text` | `null` | — |
| `action` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'icon'   => 'ti ti-inbox',
            'image'  => null,
            'title'  => '暂无数据',
            'text'   => null,
            'action' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::emptyState([

    'icon' => 'ti ti-inbox',

    'image' => null,

    'title' => '暂无数据',

    'text' => null,

    'action' => null,

]);
```

## lockScreen

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\LockScreen`

锁屏页（auth-lock-screen.html）—— 全屏锁定，输入密码解锁

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `lang` | `'zh-CN'` | — |
| `title` | `null` | <title>，默认取 heading |
| `theme` | `[]` | — |
| `user` | `['name' => 'User', 'avatar' => '']` | — |
| `action` | `…` | ', |
| `heading` | `'屏幕已锁定'` | — |
| `text` | `'请输入密码以继续'` | — |
| `brand` | `'XfAdmin'` | — |
| `below` | `null` | 卡片下方补充内容（如「切换账号」链接） |
| `copyright` | `null` | — |
| `favicon` | `null` | — |
| `head` | `null` | — |
| `scripts` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'lang'    => 'zh-CN',
            'title'   => null,      // <title>，默认取 heading
            'theme'   => [],
            'user'    => ['name' => 'User', 'avatar' => ''],
            'action'  => '#',
            'heading' => '屏幕已锁定',
            'text'    => '请输入密码以继续',
            'brand'   => 'XfAdmin',
            'below'   => null,      // 卡片下方补充内容（如「切换账号」链接）
            'copyright' => null,
            'favicon' => null,
            'head'    => null,
            'scripts' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::lockScreen([

    'lang' => 'zh-CN',

    'title' => null,

    'theme' => [],

    'user' => ['name' => 'User', 'avatar' => ''],

    'action' => …, // ',
    'heading' => '屏幕已锁定',

    'text' => '请输入密码以继续',

    'brand' => 'XfAdmin',

    'below' => null,

    'copyright' => null,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## landing

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\Landing`

营销落地页（landing.html）—— 返回完整独立页面（Page） return XfAdmin::landing([     'brand'   => 'XfAdmin',     'nav'     => [['text'=>'功能','url'=>'#features'],['text'=>'价格','url'=>'#pricing']],     'hero'    => ['title'=>'…','subtitle'=>'…','primary'=>'立即体验','secondary'=>'查看文档','image'=>'gallery/1.jpg'],     'stats'   => [['value'=>'100+','label'=>'组件'}, ...],     'features'=> [['icon'=>'ti ti-bolt','title'=>'…','text'=>'…']],     'pricing' => [['title'=>'专业版','price'=>'$99','features'=>[...],'highlight'=>true,'button'=>'选择']],     'testimonials' => [['name'=>'张三','role'=>'CTO','avatar'=>'users/user-1.jpg','text'=>'…']],     'footer'  => ['text'=>'© 2026 …','links'=>[['text'=>'关于','url'=>'#']]], ])

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `brand` | `'XfAdmin'` | — |
| `nav` | `[]` | — |
| `hero` | `[]` | — |
| `stats` | `[]` | — |
| `features` | `[]` | — |
| `pricing` | `[]` | — |
| `testimonials` | `[]` | — |
| `footer` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'brand'        => 'XfAdmin',
            'nav'          => [],
            'hero'         => [],
            'stats'        => [],
            'features'     => [],
            'pricing'      => [],
            'testimonials' => [],
            'footer'       => [],
        ]
```
</details>

### 示例

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

## menu

> 分类：**导航** · 类：`zxf\XfAdmin\Components\Navigation\Menu`

无限极菜单导航（侧边栏模式）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `mode` | `'side'` | side |
| `items` | `[]` | — |
| `current_url` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'mode'        => 'side',   // side
            'items'       => [],
            'current_url' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::menu([

    'mode' => 'side',

    'items' => [],

    'current_url' => null,

]);
```

## row

> 分类：**栅格** · 类：`zxf\XfAdmin\Components\Grid\Row`

栅格行

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `gutter` | `null` | — |
| `align` | `null` | start|center|end  => align-items-* |
| `justify` | `null` | start|center|end|between|around => justify-content-* |
| `cols` | `[]` | — |
| `content` | `null` | 直接传内容（可与 cols 混用） |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'gutter'  => null,
            'align'   => null,   // start|center|end  => align-items-*
            'justify' => null,   // start|center|end|between|around => justify-content-*
            'cols'    => [],
            'content' => null,   // 直接传内容（可与 cols 混用）
        ]
```
</details>

### 示例

```php
echo XfAdmin::row([

    'gutter' => null,

    'align' => null,

    'justify' => null,

    'cols' => [],

    'content' => null,

]);
```

## col

> 分类：**栅格** · 类：`zxf\XfAdmin\Components\Grid\Col`

栅格列

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `width` | `null` | int | 'auto' | [breakpoint => width] |
| `offset` | `null` | — |
| `order` | `null` | — |
| `content` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'width'   => null,  // int | 'auto' | [breakpoint => width]
            'offset'  => null,
            'order'   => null,
            'content' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::col([

    'width' => null,

    'offset' => null,

    'order' => null,

    'content' => '',

]);
```

## card

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Card`

卡片

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `null` | — |
| `subtitle` | `null` | — |
| `tools` | `[]` | — |
| `actions` | `null` | — |
| `body` | `''` | — |
| `footer` | `null` | — |
| `padding` | `true` | — |
| `class` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'    => null,
            'subtitle' => null,
            'tools'    => [],
            'actions'  => null,
            'body'     => '',
            'footer'   => null,
            'padding'  => true,
            'class'    => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::card([

    'title' => null,

    'subtitle' => null,

    'tools' => [],

    'actions' => null,

    'body' => '',

    'footer' => null,

    'padding' => true,

    'class' => null,

]);
```

## statCard

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\StatCard`

数据统计卡片（仪表盘小部件）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `value` | `''` | — |
| `icon` | `null` | — |
| `variant` | `'primary'` | — |
| `trend` | `null` | — |
| `url` | `null` | — |
| `counter` | `null` | — |
| `prefix` | `''` | — |
| `suffix` | `''` | — |
| `width` | `null` | 响应式列宽：null=不包裹；数组=['sm'=>6,'xl'=>3] 生成 col-sm-6 col-xl-3；整数=col-md-N col-xl-N |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'   => '',
            'value'   => '',
            'icon'    => null,
            'variant' => 'primary',
            'trend'   => null,
            'url'     => null,
            'counter' => null,
            'prefix'  => '',
            'suffix'  => '',
            'width'   => null,   // 响应式列宽：null=不包裹；数组=['sm'=>6,'xl'=>3] 生成 col-sm-6 col-xl-3；整数=col-md-N col-xl-N
        ]
```
</details>

### 示例

```php
echo XfAdmin::statCard([

    'title' => '',

    'value' => '',

    'icon' => null,

    'variant' => 'primary',

    'trend' => null,

    'url' => null,

    'counter' => null,

    'prefix' => '',

    'suffix' => '',

    'width' => null,

]);
```

## table

> 分类：**表格** · 类：`zxf\XfAdmin\Components\Table\Table`

静态表格（多种渲染风格）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `columns` | `[]` | — |
| `data` | `[]` | — |
| `striped` | `false` | — |
| `striped_cols` | `false` | — |
| `hover` | `false` | — |
| `bordered` | `false` | — |
| `borderless` | `false` | — |
| `sm` | `false` | — |
| `align_middle` | `false` | — |
| `centered` | `false` | — |
| `nowrap` | `false` | — |
| `variant` | `null` | — |
| `head_variant` | `null` | light|dark => table-* |
| `responsive` | `true` | — |
| `caption` | `null` | — |
| `empty` | `'暂无数据'` | — |
| `row_attrs` | `null` | fn($row): array |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'columns'      => [],
            'data'         => [],
            'striped'      => false,
            'striped_cols' => false,
            'hover'        => false,
            'bordered'     => false,
            'borderless'   => false,
            'sm'           => false,
            'align_middle' => false,
            'centered'     => false,
            'nowrap'       => false,
            'variant'      => null,
            'head_variant' => null,   // light|dark => table-*
            'responsive'   => true,
            'caption'      => null,
            'empty'        => '暂无数据',
            'row_attrs'    => null,   // fn($row): array
        ]
```
</details>

### 示例

```php
echo XfAdmin::table([

    'columns' => [],

    'data' => [],

    'striped' => false,

    'striped_cols' => false,

    'hover' => false,

    'bordered' => false,

    'borderless' => false,

    'sm' => false,

    'align_middle' => false,

    'centered' => false,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## dataTable

> 分类：**表格** · 类：`zxf\XfAdmin\Components\Table\DataTable`

全功能数据表格（基于 DataTables，前端由 xfadmin.js 自动初始化） 支持：搜索、排序、分页、多选、导出（Excel/CSV/打印/PDF）、固定表头、 响应式折叠、列筛选、AJAX / 服务端模式、超丰富单元格渲染器、行操作栏等

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::dataTable([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## tablesCustom

> 分类：**表格** · 类：`zxf\XfAdmin\Components\Table\TablesCustom`

自定义表格（tables-custom.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `columns` | `[]` | — |
| `rows` | `[]` | — |
| `headerBg` | `''` | — |
| `striped` | `true` | — |
| `hover` | `true` | — |
| `bordered` | `true` | — |
| `compact` | `false` | — |
| `footable` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'columns' => [],
            'rows' => [],
            'headerBg' => '',
            'striped' => true,
            'hover' => true,
            'bordered' => true,
            'compact' => false,
            'footable' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::tablesCustom([

    'columns' => [],

    'rows' => [],

    'headerBg' => '',

    'striped' => true,

    'hover' => true,

    'bordered' => true,

    'compact' => false,

    'footable' => [],

]);
```

## form

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Form`

表单容器（支持浏览器原生校验样式 / AJAX 提交 / 行内布局）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `action` | `''` | — |
| `method` | `'POST'` | — |
| `enctype` | `null` | — |
| `validation` | `false` | — |
| `ajax` | `false` | — |
| `remote` | `false` | true：表单带 data-xf-remote，由前端全局托管 AJAX 提交 + 接收处理（与登录页一致） |
| `inline` | `false` | 兼容旧写法（等价 layout=inline） |
| `layout` | `null` | vertical | horizontal | inline（form-layouts.html） |
| `label_width` | `180` | horizontal 布局标签列宽（px） |
| `fields` | `[]` | — |
| `content` | `null` | — |
| `buttons` | `null` | — |
| `csrf` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'action'      => '',
            'method'      => 'POST',
            'enctype'     => null,
            'validation'  => false,
            'ajax'        => false,
            'remote'      => false,    // true：表单带 data-xf-remote，由前端全局托管 AJAX 提交 + 接收处理（与登录页一致）
            'inline'      => false,     // 兼容旧写法（等价 layout=inline）
            'layout'      => null,      // vertical | horizontal | inline（form-layouts.html）
            'label_width' => 180,       // horizontal 布局标签列宽（px）
            'fields'      => [],
            'content'     => null,
            'buttons'     => null,
            'csrf'        => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::form([

    'action' => '',

    'method' => 'POST',

    'enctype' => null,

    'validation' => false,

    'ajax' => false,

    'remote' => false,

    'inline' => false,

    'layout' => null,

    'label_width' => 180,

    'fields' => [],

    // … 其余选项见上表 / 默认值（源码）
]);
```

## input

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Input`

输入框（text/email/password/number/... + 输入掩码 + 标签输入 + 前后缀组）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::input([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## textarea

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Textarea`

多行文本框

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::textarea([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## select

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Select`

下拉选择（原生 / Choices.js 增强 / Select2 增强，支持分组、多选、搜索、远程）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::select([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## check

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Check`

复选框 / 单选框 / 开关（单个或一组）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `type` | `'checkbox'` | checkbox | radio | switch |
| `name` | `null` | — |
| `label` | `null` | — |
| `options` | `[]` | 一组：value => label |
| `value` | `null` | 选中值（组模式）；单个模式用 checked |
| `checked` | `false` | — |
| `inline` | `false` | — |
| `reverse` | `false` | — |
| `disabled` | `false` | — |
| `required` | `false` | — |
| `wrapper` | `'mb-3'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'type'     => 'checkbox',  // checkbox | radio | switch
            'name'     => null,
            'label'    => null,
            'options'  => [],          // 一组：value => label
            'value'    => null,        // 选中值（组模式）；单个模式用 checked
            'checked'  => false,
            'inline'   => false,
            'reverse'  => false,
            'disabled' => false,
            'required' => false,
            'wrapper'  => 'mb-3',
        ]
```
</details>

### 示例

```php
echo XfAdmin::check([

    'type' => 'checkbox',

    'name' => null,

    'label' => null,

    'options' => [],

    'value' => null,

    'checked' => false,

    'inline' => false,

    'reverse' => false,

    'disabled' => false,

    'required' => false,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## slider

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Slider`

范围滑块（noUiSlider）

**依赖资源**：`nouislider`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::slider([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## dateRange
_别名：_ `dateRangePicker`

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\DateRangePicker`

日期 / 日期范围 / 日期时间选择器（Date Range Picker）

**依赖资源**：`daterangepicker`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::dateRange([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## editor

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Editor`

富文本编辑器（Quill / Summernote）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::editor([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## upload

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Upload`

文件上传（原生 / Dropzone 拖拽 / FilePond）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::upload([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## colorPicker

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\ColorPicker`

颜色选择器（Pickr）

**依赖资源**：`pickr`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php

```

### 示例

```php
echo XfAdmin::colorPicker([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## tags

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Tags`

标签输入（Tagify，form-other-plugins.html）

**依赖资源**：`tagify`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `''` | — |
| `label` | `null` | — |
| `value` | `[]` | — |
| `whitelist` | `[]` | — |
| `max` | `null` | — |
| `placeholder` | `''` | — |
| `help` | `null` | — |
| `col` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'        => '',
            'label'       => null,
            'value'       => [],
            'whitelist'   => [],
            'max'         => null,
            'placeholder' => '',
            'help'        => null,
            'col'         => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::tags([

    'name' => '',

    'label' => null,

    'value' => [],

    'whitelist' => [],

    'max' => null,

    'placeholder' => '',

    'help' => null,

    'col' => null,

]);
```

## maskedInput

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\MaskedInput`

输入掩码（Inputmask，form-pickers.html / form-other-plugins.html）

**依赖资源**：`inputmask`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `''` | — |
| `label` | `null` | — |
| `mask` | `null` | — |
| `alias` | `null` | — |
| `value` | `''` | — |
| `placeholder` | `null` | — |
| `help` | `null` | — |
| `col` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'        => '',
            'label'       => null,
            'mask'        => null,
            'alias'       => null,
            'value'       => '',
            'placeholder' => null,
            'help'        => null,
            'col'         => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::maskedInput([

    'name' => '',

    'label' => null,

    'mask' => null,

    'alias' => null,

    'value' => '',

    'placeholder' => null,

    'help' => null,

    'col' => null,

]);
```

## wizard

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\Wizard`

分步向导（form-wizard.html）—— 纯原生 JS 驱动

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `steps` | `[]` | — |
| `variant` | `'primary'` | — |
| `vertical` | `false` | — |
| `progress` | `true` | — |
| `labels` | `['prev' => '上一步', 'next' => '下一步', 'finish' => '提交']` | — |
| `action` | `''` | 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit） |
| `method` | `'post'` | 提交方法 |
| `remote` | `false` | 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管） |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'steps'    => [],
            'variant'  => 'primary',
            'vertical' => false,
            'progress' => true,
            'labels'   => ['prev' => '上一步', 'next' => '下一步', 'finish' => '提交'],
            'action'   => '',          // 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit）
            'method'   => 'post',      // 提交方法
            'remote'   => false,       // 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管）
        ]
```
</details>

### 示例

```php
echo XfAdmin::wizard([

    'steps' => [],

    'variant' => 'primary',

    'vertical' => false,

    'progress' => true,

    'labels' => ['prev' => '上一步', 'next' => '下一步', 'finish' => '提交'],

    'action' => '',

    'method' => 'post',

    'remote' => false,

]);
```

## passwordStrength

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\PasswordStrength`

密码强度计（misc-pass-meter）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `'password'` | — |
| `id` | `null` | — |
| `label` | `'密码'` | — |
| `value` | `''` | — |
| `showRules` | `true` | — |
| `minScore` | `…` | — |
| `hint` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'      => 'password',
            'id'        => null,
            'label'     => '密码',
            'value'     => '',
            'showRules' => true,
            'minScore'  => 0,
            'hint'      => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::passwordStrength([

    'name' => 'password',

    'id' => null,

    'label' => '密码',

    'value' => '',

    'showRules' => true,

    'minScore' => 0,

    'hint' => '',

]);
```

## formElements

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\FormElements`

表单元素展示（form-elements.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'sections' => null,
        ]
```

### 示例

```php
echo XfAdmin::formElements([

    'sections' => null,

]);
```

## formLayout

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\FormLayout`

表单布局变体（form-layout.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `layout` | `'vertical'` | — |
| `columns` | `2` | — |
| `fields` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'layout' => 'vertical',
            'columns' => 2,
            'fields' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::formLayout([

    'layout' => 'vertical',

    'columns' => 2,

    'fields' => null,

]);
```

## formOtherPlugin

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\FormOtherPlugin`

其他表单插件展示（form-other-plugin.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'plugins' => ['mask', 'autosize', 'maxlength', 'touchspin'],
        ]
```

### 示例

```php
echo XfAdmin::formOtherPlugin([

    'plugins' => ['mask', 'autosize', 'maxlength', 'touchspin'],

]);
```

## formValidation

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\FormValidation`

表单验证展示（form-validation.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `showBuiltin` | `true` | — |
| `formId` | `'xf_form_val'` | — |
| `fields` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'showBuiltin' => true,
            'formId' => 'xf_form_val',
            'fields' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::formValidation([

    'showBuiltin' => true,

    'formId' => 'xf_form_val',

    'fields' => null,

]);
```

## apexChart

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\ApexChart`

ApexCharts 图表（折线/面积/柱状/条形/饼图/环形/雷达/热力图/K线/迷你走势 sparkline 等全部类型）

**依赖资源**：`apexcharts`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `type` | `'line'` | — |
| `height` | `350` | — |
| `width` | `null` | — |
| `series` | `[]` | — |
| `labels` | `null` | — |
| `colors` | `null` | — |
| `sparkline` | `false` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'type'      => 'line',
            'height'    => 350,
            'width'     => null,
            'series'    => [],
            'labels'    => null,
            'colors'    => null,
            'sparkline' => false,
            'options'   => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::apexChart([

    'type' => 'line',

    'height' => 350,

    'width' => null,

    'series' => [],

    'labels' => null,

    'colors' => null,

    'sparkline' => false,

    'options' => [],

]);
```

## apexTree

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\ApexTree`

组织架构树图（charts-apextree.html，基于 apextree 插件，离线可用） 以树状节点图展示组织架构 / 层级关系，支持四个展开方向、节点头像与自定义配色， 内置展开/收起与缩放交互。

**依赖资源**：`apextree`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `height` | `500` | — |
| `direction` | `'top'` | — |
| `data` | `[]` | — |
| `node_width` | `150` | — |
| `node_height` | `60` | — |
| `collapsible` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'height'      => 500,
            'direction'   => 'top',
            'data'        => [],
            'node_width'  => 150,
            'node_height' => 60,
            'collapsible' => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::apexTree([

    'height' => 500,

    'direction' => 'top',

    'data' => [],

    'node_width' => 150,

    'node_height' => 60,

    'collapsible' => true,

]);
```

## apexSankey

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\ApexSankey`

桑基图（charts-apexsankey.html，基于 apexsankey 插件 + svg.js，离线可用） 用于展示流量 / 能量 / 转化漏斗等「来源 → 去向」的流向分布， 节点与连线宽度按 value 自动分配，内置缩放工具栏与连线悬浮高亮。

**依赖资源**：`apexsankey`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `height` | `400` | — |
| `nodes` | `[]` | — |
| `edges` | `[]` | — |
| `node_width` | `20` | — |
| `toolbar` | `true` | — |
| `order` | `null` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'height'     => 400,
            'nodes'      => [],
            'edges'      => [],
            'node_width' => 20,
            'toolbar'    => true,
            'order'      => null,
            'options'    => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::apexSankey([

    'height' => 400,

    'nodes' => [],

    'edges' => [],

    'node_width' => 20,

    'toolbar' => true,

    'order' => null,

    'options' => [],

]);
```

## echart

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\EChart`

Apache ECharts 图表（支持全部 ECharts 图表类型与配置）

**依赖资源**：`echarts`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `height` | `350` | — |
| `theme` | `null` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'height'  => 350,
            'theme'   => null,
            'options' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::echart([

    'height' => 350,

    'theme' => null,

    'options' => [],

]);
```

## vectorMap

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\VectorMap`

矢量地图（jsVectorMap）

**依赖资源**：`jsvectormap-world`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `map` | `'world'` | — |
| `height` | `360` | — |
| `markers` | `[]` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'map'     => 'world',
            'height'  => 360,
            'markers' => [],
            'options' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::vectorMap([

    'map' => 'world',

    'height' => 360,

    'markers' => [],

    'options' => [],

]);
```

## leafletMap

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\LeafletMap`

Leaflet 交互地图（maps-leaflet.html） 说明：Leaflet 的 JS/CSS 已本地内置（离线可用），但地图底图瓦片(tiles)通常来自在线 瓦片服务（如 OpenStreetMap）。设置 `tiles=null` 可完全离线渲染（仅显示标记/图形，无底图）。

**依赖资源**：`leaflet`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `height` | `400` | — |
| `center` | `[39.9042, 116.4074]` | — |
| `zoom` | `11` | — |
| `tiles` | `…` | {s}.tile.openstreetmap.org/{z}/{x}/{y}.png', |
| `markers` | `[]` | — |
| `circles` | `[]` | — |
| `polygons` | `[]` | — |
| `lines` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'height'  => 400,
            'center'  => [39.9042, 116.4074],
            'zoom'    => 11,
            'tiles'   => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'markers' => [],
            'circles' => [],
            'polygons'=> [],
            'lines'   => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::leafletMap([

    'height' => 400,

    'center' => [39.9042, 116.4074],

    'zoom' => 11,

    'tiles' => …, // {s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'markers' => [],

    'circles' => [],

    'polygons' => [],

    'lines' => [],

]);
```

## googleMap

> 分类：**图表 / 地图** · 类：`zxf\XfAdmin\Components\Chart\GoogleMap`

谷歌地图（maps-google.html） 采用免费 iframe 嵌入方式（无需 API Key）。支持按地点名称搜索或经纬度定位， 可切换普通 / 卫星视图。注意：底图数据来自 Google 在线服务，离线环境不可用 （离线场景请改用 XfAdmin::leafletMap 并设置 tiles=null）。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `height` | `400` | — |
| `place` | `null` | — |
| `center` | `[39.9042, 116.4074]` | — |
| `zoom` | `12` | — |
| `maptype` | `'roadmap'` | — |
| `language` | `'zh-CN'` | — |
| `rounded` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'height'   => 400,
            'place'    => null,
            'center'   => [39.9042, 116.4074],
            'zoom'     => 12,
            'maptype'  => 'roadmap',
            'language' => 'zh-CN',
            'rounded'  => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::googleMap([

    'height' => 400,

    'place' => null,

    'center' => [39.9042, 116.4074],

    'zoom' => 12,

    'maptype' => 'roadmap',

    'language' => 'zh-CN',

    'rounded' => true,

]);
```

## alert

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Alert`

警告提示框

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `variant` | `'primary'` | — |
| `text` | `''` | — |
| `heading` | `null` | — |
| `icon` | `null` | — |
| `dismissible` | `false` | — |
| `soft` | `false` | bg-*-subtle 柔和风格 |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'variant'     => 'primary',
            'text'        => '',
            'heading'     => null,
            'icon'        => null,
            'dismissible' => false,
            'soft'        => false,   // bg-*-subtle 柔和风格
        ]
```
</details>

### 示例

```php
echo XfAdmin::alert([

    'variant' => 'primary',

    'text' => '',

    'heading' => null,

    'icon' => null,

    'dismissible' => false,

    'soft' => false,

]);
```

## badge

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Badge`

徽章

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `variant` | `'primary'` | — |
| `pill` | `false` | — |
| `soft` | `false` | bg-*-subtle |
| `outline` | `false` | — |
| `icon` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'    => '',
            'variant' => 'primary',
            'pill'    => false,
            'soft'    => false,     // bg-*-subtle
            'outline' => false,
            'icon'    => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::badge([

    'text' => '',

    'variant' => 'primary',

    'pill' => false,

    'soft' => false,

    'outline' => false,

    'icon' => null,

]);
```

## button

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Button`

按钮（支持 soft/outline/ghost 风格、图标、加载态 Ladda、链接按钮、模态/抽屉触发）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `variant` | `'primary'` | — |
| `type` | `'button'` | — |
| `href` | `null` | — |
| `size` | `null` | sm | lg |
| `soft` | `false` | — |
| `outline` | `false` | — |
| `ghost` | `false` | — |
| `rounded` | `false` | rounded-pill |
| `icon` | `null` | — |
| `icon_only` | `false` | — |
| `disabled` | `false` | — |
| `ladda` | `false` | — |
| `toggle` | `null` | modal | offcanvas | collapse | dropdown |
| `target` | `null` | — |
| `onclick` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'     => '',
            'variant'  => 'primary',
            'type'     => 'button',
            'href'     => null,
            'size'     => null,       // sm | lg
            'soft'     => false,
            'outline'  => false,
            'ghost'    => false,
            'rounded'  => false,      // rounded-pill
            'icon'     => null,
            'icon_only' => false,
            'disabled' => false,
            'ladda'    => false,
            'toggle'   => null,       // modal | offcanvas | collapse | dropdown
            'target'   => null,
            'onclick'  => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::button([

    'text' => '',

    'variant' => 'primary',

    'type' => 'button',

    'href' => null,

    'size' => null,

    'soft' => false,

    'outline' => false,

    'ghost' => false,

    'rounded' => false,

    'icon' => null,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## dropdown

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Dropdown`

下拉菜单

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `variant` | `'light'` | — |
| `size` | `null` | — |
| `items` | `[]` | — |
| `direction` | `'down'` | — |
| `align` | `null` | — |
| `split` | `false` | — |
| `toggle` | `null` | 自定义触发器 HTML（完全接管） |
| `menu` | `null` | 自定义菜单内容 HTML |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'      => '',
            'variant'   => 'light',
            'size'      => null,
            'items'     => [],
            'direction' => 'down',
            'align'     => null,
            'split'     => false,
            'toggle'    => null,   // 自定义触发器 HTML（完全接管）
            'menu'      => null,   // 自定义菜单内容 HTML
        ]
```
</details>

### 示例

```php
echo XfAdmin::dropdown([

    'text' => '',

    'variant' => 'light',

    'size' => null,

    'items' => [],

    'direction' => 'down',

    'align' => null,

    'split' => false,

    'toggle' => null,

    'menu' => null,

]);
```

## modal

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Modal`

模态框

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `null` | — |
| `body` | `''` | — |
| `footer` | `null` | — |
| `size` | `null` | — |
| `centered` | `false` | — |
| `scrollable` | `false` | — |
| `fade` | `true` | — |
| `static` | `false` | — |
| `close` | `true` | — |
| `trigger` | `null` | — |
| `trigger_variant` | `'primary'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => null,
            'body'       => '',
            'footer'     => null,
            'size'       => null,
            'centered'   => false,
            'scrollable' => false,
            'fade'       => true,
            'static'     => false,
            'close'      => true,
            'trigger'    => null,
            'trigger_variant' => 'primary',
        ]
```
</details>

### 示例

```php
echo XfAdmin::modal([

    'title' => null,

    'body' => '',

    'footer' => null,

    'size' => null,

    'centered' => false,

    'scrollable' => false,

    'fade' => true,

    'static' => false,

    'close' => true,

    'trigger' => null,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## offcanvas

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Offcanvas`

抽屉（Offcanvas）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `null` | — |
| `body` | `''` | — |
| `placement` | `'end'` | — |
| `backdrop` | `true` | — |
| `scroll` | `false` | — |
| `trigger` | `null` | — |
| `trigger_variant` | `'primary'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'     => null,
            'body'      => '',
            'placement' => 'end',
            'backdrop'  => true,
            'scroll'    => false,
            'trigger'   => null,
            'trigger_variant' => 'primary',
        ]
```
</details>

### 示例

```php
echo XfAdmin::offcanvas([

    'title' => null,

    'body' => '',

    'placement' => 'end',

    'backdrop' => true,

    'scroll' => false,

    'trigger' => null,

    'trigger_variant' => 'primary',

]);
```

## tabs

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Tabs`

选项卡（tabs / pills / 垂直 / 图标 / 淡入动画）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `style` | `'tabs'` | — |
| `items` | `[]` | — |
| `justified` | `false` | — |
| `vertical` | `false` | — |
| `fade` | `true` | — |
| `footer` | `null` | — |
| `form` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'style'     => 'tabs',
            'items'     => [],
            'justified' => false,
            'vertical'  => false,
            'fade'      => true,
            'footer'    => null,
            'form'      => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::tabs([

    'style' => 'tabs',

    'items' => [],

    'justified' => false,

    'vertical' => false,

    'fade' => true,

    'footer' => null,

    'form' => null,

]);
```

## accordion

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Accordion`

手风琴 / 折叠面板

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `flush` | `false` | — |
| `always_open` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'       => [],
            'flush'       => false,
            'always_open' => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::accordion([

    'items' => [],

    'flush' => false,

    'always_open' => false,

]);
```

## progress

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Progress`

进度条（支持多段叠加）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `value` | `…` | — |
| `variant` | `'primary'` | — |
| `height` | `null` | px |
| `striped` | `false` | — |
| `animated` | `false` | — |
| `label` | `null` | — |
| `bars` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'value'    => 0,
            'variant'  => 'primary',
            'height'   => null,     // px
            'striped'  => false,
            'animated' => false,
            'label'    => null,
            'bars'     => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::progress([

    'value' => 0,

    'variant' => 'primary',

    'height' => null,

    'striped' => false,

    'animated' => false,

    'label' => null,

    'bars' => [],

]);
```

## spinner

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Spinner`

加载指示器（Bootstrap spinner / SpinKit 高级动画）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `type` | `'border'` | border | grow |
| `variant` | `'primary'` | — |
| `size` | `null` | sm |
| `spinkit` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'type'    => 'border',   // border | grow
            'variant' => 'primary',
            'size'    => null,       // sm
            'spinkit' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::spinner([

    'type' => 'border',

    'variant' => 'primary',

    'size' => null,

    'spinkit' => null,

]);
```

## pagination

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Pagination`

分页

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `total` | `…` | — |
| `per_page` | `15` | — |
| `current` | `1` | — |
| `url` | `'?page={page}'` | — |
| `window` | `2` | — |
| `size` | `null` | — |
| `align` | `'center'` | — |
| `rounded` | `false` | — |
| `arrows` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'total'    => 0,
            'per_page' => 15,
            'current'  => 1,
            'url'      => '?page={page}',
            'window'   => 2,
            'size'     => null,
            'align'    => 'center',
            'rounded'  => false,
            'arrows'   => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::pagination([

    'total' => 0,

    'per_page' => 15,

    'current' => 1,

    'url' => '?page={page}',

    'window' => 2,

    'size' => null,

    'align' => 'center',

    'rounded' => false,

    'arrows' => true,

]);
```

## listGroup

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\ListGroup`

列表组

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `flush` | `false` | — |
| `numbered` | `false` | — |
| `horizontal` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'      => [],
            'flush'      => false,
            'numbered'   => false,
            'horizontal' => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::listGroup([

    'items' => [],

    'flush' => false,

    'numbered' => false,

    'horizontal' => false,

]);
```

## avatar

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Avatar`

头像组件（图片 / 文字缩写 / 图标 / 分组堆叠） ------------------------------------------------------------------ 设计契约（严格对齐后台模板 INSPINIA v4.1.0 的 .avatar 规范）：   - 头像恒以 <span class="avatar avatar-{size}"> 包裹，尺寸类挂在「包裹元素」而非 <img> 上；   - 图片头像：<span class="avatar avatar-sm"><img class="img-fluid rounded-circle"></span>   - 文字/图标头像：<span class="avatar avatar-sm"><span class="avatar-title {bg} rounded-circle fw-bold">XX</span></span>   - 分组堆叠：<div class="avatar-group">…<span class="avatar avatar-sm">…</span>…</div> 调用示例：   XfAdmin::avatar(['src' => '/a.jpg', 'size' => 'md', 'rounded' => 'circle'])   XfAdmin::avatar(['text' => 'ZS', 'variant' => 'primary', 'size' => 'lg'])   XfAdmin::avatar(['group' => [['src' => '/a.jpg'], ['text' => '+5', 'variant' => 'info']]])

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `src` | `null` | — |
| `text` | `null` | — |
| `icon` | `null` | — |
| `alt` | `''` | — |
| `size` | `'md'` | xs | sm | md | lg | xl | xxl |
| `rounded` | `'circle'` | circle | 0 | 1 | 2 | 3 |
| `variant` | `'primary'` | — |
| `soft` | `true` | — |
| `group` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'src'     => null,
            'text'    => null,
            'icon'    => null,
            'alt'     => '',
            'size'    => 'md',       // xs | sm | md | lg | xl | xxl
            'rounded' => 'circle',   // circle | 0 | 1 | 2 | 3
            'variant' => 'primary',
            'soft'    => true,
            'group'   => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::avatar([

    'src' => null,

    'text' => null,

    'icon' => null,

    'alt' => '',

    'size' => 'md',

    'rounded' => 'circle',

    'variant' => 'primary',

    'soft' => true,

    'group' => [],

]);
```

## icon

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Icon`

图标（Tabler webfont / Lucide SVG，模板内置两套图标库）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `''` | — |
| `lib` | `'tabler'` | tabler | lucide |
| `size` | `null` | fs-* 类 |
| `color` | `null` | text-* 类 |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'  => '',
            'lib'   => 'tabler',   // tabler | lucide
            'size'  => null,       // fs-* 类
            'color' => null,       // text-* 类
        ]
```
</details>

### 示例

```php
echo XfAdmin::icon([

    'name' => '',

    'lib' => 'tabler',

    'size' => null,

    'color' => null,

]);
```

## toast

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Toast`

轻提示（Toast）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `null` | — |
| `body` | `''` | — |
| `time` | `null` | — |
| `variant` | `null` | — |
| `autohide` | `true` | — |
| `delay` | `5000` | — |
| `show` | `true` | — |
| `placement` | `null` | 如 'top-0 end-0'，非空时包裹固定容器 |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'     => null,
            'body'      => '',
            'time'      => null,
            'variant'   => null,
            'autohide'  => true,
            'delay'     => 5000,
            'show'      => true,
            'placement' => null,   // 如 'top-0 end-0'，非空时包裹固定容器
        ]
```
</details>

### 示例

```php
echo XfAdmin::toast([

    'title' => null,

    'body' => '',

    'time' => null,

    'variant' => null,

    'autohide' => true,

    'delay' => 5000,

    'show' => true,

    'placement' => null,

]);
```

## timeline

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Timeline`

时间线

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'items' => [],
        ]
```

### 示例

```php
echo XfAdmin::timeline([

    'items' => [],

]);
```

## carousel

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Carousel`

轮播

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `indicators` | `false` | — |
| `controls` | `true` | — |
| `fade` | `false` | — |
| `interval` | `5000` | — |
| `dark` | `false` | — |
| `ride` | `'carousel'` | — |
| `height` | `''` | 轮播图高度，留空则由 .xf-carousel img 兜底（响应式） |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'      => [],
            'indicators' => false,
            'controls'   => true,
            'fade'       => false,
            'interval'   => 5000,
            'dark'       => false,
            'ride'       => 'carousel',
            'height'     => '',          // 轮播图高度，留空则由 .xf-carousel img 兜底（响应式）
        ]
```
</details>

### 示例

```php
echo XfAdmin::carousel([

    'items' => [],

    'indicators' => false,

    'controls' => true,

    'fade' => false,

    'interval' => 5000,

    'dark' => false,

    'ride' => 'carousel',

    'height' => '',

]);
```

## breadcrumb

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Breadcrumb`

面包屑（独立使用；页面标题栏内置面包屑见 PageTitle）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'items'   => [],
            'divider' => null,   // 自定义分隔符字符
        ]
```

### 示例

```php
echo XfAdmin::breadcrumb([

    'items' => [],

    'divider' => null,

]);
```

## tooltip

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Tooltip`

文字提示（Bootstrap Tooltip）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `title` | `''` | — |
| `placement` | `'top'` | — |
| `tag` | `'button'` | — |
| `class` | `'btn btn-secondary'` | — |
| `html` | `false` | — |
| `trigger` | `null` | — |
| `custom_class` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'      => '',
            'title'     => '',
            'placement' => 'top',
            'tag'       => 'button',
            'class'     => 'btn btn-secondary',
            'html'      => false,
            'trigger'   => null,
            'custom_class' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::tooltip([

    'text' => '',

    'title' => '',

    'placement' => 'top',

    'tag' => 'button',

    'class' => 'btn btn-secondary',

    'html' => false,

    'trigger' => null,

    'custom_class' => null,

]);
```

## popover

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Popover`

弹出框（Bootstrap Popover）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `title` | `null` | — |
| `content` | `''` | — |
| `placement` | `'top'` | — |
| `trigger` | `'click'` | — |
| `tag` | `'button'` | — |
| `class` | `'btn btn-secondary'` | — |
| `html` | `false` | — |
| `dismiss` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'      => '',
            'title'     => null,
            'content'   => '',
            'placement' => 'top',
            'trigger'   => 'click',
            'tag'       => 'button',
            'class'     => 'btn btn-secondary',
            'html'      => false,
            'dismiss'   => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::popover([

    'text' => '',

    'title' => null,

    'content' => '',

    'placement' => 'top',

    'trigger' => 'click',

    'tag' => 'button',

    'class' => 'btn btn-secondary',

    'html' => false,

    'dismiss' => false,

]);
```

## placeholder

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Placeholder`

骨架占位（Bootstrap Placeholder）—— 加载态占位内容

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `lines` | `[12, 8, 10, 6]` | — |
| `animation` | `'glow'` | glow|wave|null |
| `variant` | `null` | — |
| `size` | `null` | xs|sm|lg |
| `body` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'lines'     => [12, 8, 10, 6],
            'animation' => 'glow',   // glow|wave|null
            'variant'   => null,
            'size'      => null,     // xs|sm|lg
            'body'      => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::placeholder([

    'lines' => [12, 8, 10, 6],

    'animation' => 'glow',

    'variant' => null,

    'size' => null,

    'body' => null,

]);
```

## collapse

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Collapse`

折叠（Bootstrap Collapse）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `trigger` | `'展开/收起'` | — |
| `body` | `''` | — |
| `open` | `false` | — |
| `trigger_tag` | `'button'` | — |
| `trigger_class` | `'btn btn-primary'` | — |
| `horizontal` | `false` | — |
| `card` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'trigger'       => '展开/收起',
            'body'          => '',
            'open'          => false,
            'trigger_tag'   => 'button',
            'trigger_class' => 'btn btn-primary',
            'horizontal'    => false,
            'card'          => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::collapse([

    'trigger' => '展开/收起',

    'body' => '',

    'open' => false,

    'trigger_tag' => 'button',

    'trigger_class' => 'btn btn-primary',

    'horizontal' => false,

    'card' => true,

]);
```

## scrollspy

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Scrollspy`

滚动监听（Bootstrap Scrollspy）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `height` | `'250px'` | — |
| `nav_width` | `3` | — |
| `offset` | `…` | — |
| `smooth` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'     => [],
            'height'    => '250px',
            'nav_width' => 3,
            'offset'    => 0,
            'smooth'    => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::scrollspy([

    'items' => [],

    'height' => '250px',

    'nav_width' => 3,

    'offset' => 0,

    'smooth' => true,

]);
```

## ratio

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Ratio`

响应式媒体容器（视频 / iframe / 图片，Bootstrap Ratio）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `ratio` | `'16x9'` | — |
| `src` | `null` | — |
| `type` | `'iframe'` | — |
| `body` | `null` | — |
| `allowfullscreen` | `true` | — |
| `controls` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'ratio'  => '16x9',
            'src'    => null,
            'type'   => 'iframe',
            'body'   => null,
            'allowfullscreen' => true,
            'controls' => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::ratio([

    'ratio' => '16x9',

    'src' => null,

    'type' => 'iframe',

    'body' => null,

    'allowfullscreen' => true,

    'controls' => true,

]);
```

## rating

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Rating`

星级评分（纯展示，无外部依赖）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `value` | `…` | — |
| `max` | `5` | — |
| `variant` | `'warning'` | — |
| `size` | `null` | — |
| `show_value` | `false` | — |
| `count` | `null` | — |
| `icon_full` | `'ti ti-star-filled'` | — |
| `icon_half` | `'ti ti-star-half-filled'` | — |
| `icon_empty` | `'ti ti-star'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'value'      => 0,
            'max'        => 5,
            'variant'    => 'warning',
            'size'       => null,
            'show_value' => false,
            'count'      => null,
            'icon_full'  => 'ti ti-star-filled',
            'icon_half'  => 'ti ti-star-half-filled',
            'icon_empty' => 'ti ti-star',
        ]
```
</details>

### 示例

```php
echo XfAdmin::rating([

    'value' => 0,

    'max' => 5,

    'variant' => 'warning',

    'size' => null,

    'show_value' => false,

    'count' => null,

    'icon_full' => 'ti ti-star-filled',

    'icon_half' => 'ti ti-star-half-filled',

    'icon_empty' => 'ti ti-star',

]);
```

## ribbon

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Ribbon`

缎带角标（用于卡片角落标记）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `variant` | `'primary'` | — |
| `position` | `'left'` | — |
| `body` | `''` | — |
| `shape` | `null` | null|round |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'     => '',
            'variant'  => 'primary',
            'position' => 'left',
            'body'     => '',
            'shape'    => null,   // null|round
        ]
```
</details>

### 示例

```php
echo XfAdmin::ribbon([

    'text' => '',

    'variant' => 'primary',

    'position' => 'left',

    'body' => '',

    'shape' => null,

]);
```

## chip

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Chip`

标签/胶囊（Chip / Tag）—— 可带头像、图标、关闭按钮

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `label` | `''` | — |
| `avatar` | `null` | — |
| `icon` | `null` | — |
| `variant` | `'light'` | — |
| `dismissible` | `false` | — |
| `href` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'label'       => '',
            'avatar'      => null,
            'icon'        => null,
            'variant'     => 'light',
            'dismissible' => false,
            'href'        => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::chip([

    'label' => '',

    'avatar' => null,

    'icon' => null,

    'variant' => 'light',

    'dismissible' => false,

    'href' => null,

]);
```

## stepper

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Stepper`

步骤条（只读进度展示，如订单进度 ecommerce-order-details.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `steps` | `[]` | — |
| `vertical` | `false` | — |
| `variant` | `'primary'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'steps'    => [],
            'vertical' => false,
            'variant'  => 'primary',
        ]
```
</details>

### 示例

```php
echo XfAdmin::stepper([

    'steps' => [],

    'vertical' => false,

    'variant' => 'primary',

]);
```

## descriptionList

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\DescriptionList`

描述列表（键值对，详情页常用：invoice / product / order 详情）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `horizontal` | `true` | — |
| `label_width` | `4` | — |
| `striped` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'       => [],
            'horizontal'  => true,
            'label_width' => 4,
            'striped'     => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::descriptionList([

    'items' => [],

    'horizontal' => true,

    'label_width' => 4,

    'striped' => false,

]);
```

## loadingButton

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\LoadingButton`

加载/忙碌按钮（misc-loading-buttons）—— 点击后显示 spinner，避免重复提交

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `'提交'` | — |
| `variant` | `'primary'` | — |
| `driver` | `'spinner'` | — |
| `type` | `'button'` | — |
| `size` | `''` | — |
| `name` | `null` | — |
| `value` | `null` | — |
| `icon` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'    => '提交',
            'variant' => 'primary',
            'driver'  => 'spinner',
            'type'    => 'button',
            'size'    => '',
            'name'    => null,
            'value'   => null,
            'icon'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::loadingButton([

    'text' => '提交',

    'variant' => 'primary',

    'driver' => 'spinner',

    'type' => 'button',

    'size' => '',

    'name' => null,

    'value' => null,

    'icon' => '',

]);
```

## avatarGroup

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\AvatarGroup`

头像组（重叠堆叠），用于展示多人

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `users` | `[]` | — |
| `max` | `5` | — |
| `size` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'users' => [],
            'max'   => 5,
            'size'  => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::avatarGroup([

    'users' => [],

    'max' => 5,

    'size' => '',

]);
```

## backToTop

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\BackToTop`

返回顶部浮动按钮，滚动超过阈值后出现

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'offset' => 300,
            'icon'   => 'ti ti-arrow-up',
        ]
```

### 示例

```php
echo XfAdmin::backToTop([

    'offset' => 300,

    'icon' => 'ti ti-arrow-up',

]);
```

## callout

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Callout`

强调提示框（Callout），比 Alert 更轻量、常用于说明性文字

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `body` | `''` | — |
| `variant` | `'info'` | — |
| `icon` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'   => '',
            'body'    => '',
            'variant' => 'info',
            'icon'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::callout([

    'title' => '',

    'body' => '',

    'variant' => 'info',

    'icon' => '',

]);
```

## countdown

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Countdown`

倒计时（Countdown），到达目标时间前逐秒刷新

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `target` | `''` | — |
| `title` | `''` | — |
| `labels` | `['天', '时', '分', '秒']` | — |
| `expired` | `'已结束'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'target'  => '',
            'title'   => '',
            'labels'  => ['天', '时', '分', '秒'],
            'expired' => '已结束',
        ]
```
</details>

### 示例

```php
echo XfAdmin::countdown([

    'target' => '',

    'title' => '',

    'labels' => ['天', '时', '分', '秒'],

    'expired' => '已结束',

]);
```

## countUp

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\CountUp`

数字滚动动画（CountUp），进入视口时从 0 递增到目标值

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `value` | `…` | — |
| `prefix` | `''` | — |
| `suffix` | `''` | — |
| `decimals` | `…` | — |
| `duration` | `1500` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'value'    => 0,
            'prefix'   => '',
            'suffix'   => '',
            'decimals' => 0,
            'duration' => 1500,
        ]
```
</details>

### 示例

```php
echo XfAdmin::countUp([

    'value' => 0,

    'prefix' => '',

    'suffix' => '',

    'decimals' => 0,

    'duration' => 1500,

]);
```

## divider

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Divider`

分割线（带可选文字 / 图标），用于区块之间的视觉分隔

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `''` | — |
| `icon` | `''` | — |
| `variant` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'    => '',
            'icon'    => '',
            'variant' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::divider([

    'text' => '',

    'icon' => '',

    'variant' => '',

]);
```

## kbd

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Kbd`

键盘按键（Kbd），用于展示快捷键

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'keys' => [],
            'text' => '',
        ]
```

### 示例

```php
echo XfAdmin::kbd([

    'keys' => [],

    'text' => '',

]);
```

## media

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Media`

媒体对象（Media Object）：图片 / 头像 + 标题 + 文本，左右布局

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `image` | `''` | — |
| `avatar` | `false` | — |
| `title` | `''` | — |
| `text` | `''` | — |
| `meta` | `''` | — |
| `href` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'image'  => '',
            'avatar' => false,
            'title'  => '',
            'text'   => '',
            'meta'   => '',
            'href'   => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::media([

    'image' => '',

    'avatar' => false,

    'title' => '',

    'text' => '',

    'meta' => '',

    'href' => '',

]);
```

## skeleton

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Skeleton`

骨架屏占位（加载态），用于内容尚未就绪时的占位提示

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `lines` | `3` | — |
| `type` | `'text'` | — |
| `width` | `'100%'` | — |
| `height` | `16` | — |
| `circle` | `48` | — |
| `animated` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'lines'    => 3,
            'type'     => 'text',
            'width'    => '100%',
            'height'   => 16,
            'circle'   => 48,
            'animated' => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::skeleton([

    'lines' => 3,

    'type' => 'text',

    'width' => '100%',

    'height' => 16,

    'circle' => 48,

    'animated' => true,

]);
```

## switch

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Toggle`

开关控件（基于 Bootstrap form-switch，支持颜色变体/尺寸/标签） 注意：类名用 Toggle（switch 是 PHP 保留字）；对外仍通过别名 switch 调用：   XfAdmin::switch([     'name'     => 'notify',          // 表单字段名     'checked'  => true,              // 是否默认开启     'disabled' => false,     'label'    => '开启通知',         // 右侧文字标签（可空）     'value'    => '1',     'variant'  => 'primary',         // primary/success/danger/warning/info（通过 accent-color 着色）     'size'     => '',                // '' | 'sm' | 'lg'   ])

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `''` | — |
| `checked` | `false` | — |
| `disabled` | `false` | — |
| `label` | `''` | — |
| `value` | `'1'` | — |
| `variant` | `'primary'` | — |
| `size` | `''` | — |
| `id` | `null` | — |
| `class` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'     => '',
            'checked'  => false,
            'disabled' => false,
            'label'    => '',
            'value'    => '1',
            'variant'  => 'primary',
            'size'     => '',
            'id'       => null,
            'class'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::switch([

    'name' => '',

    'checked' => false,

    'disabled' => false,

    'label' => '',

    'value' => '1',

    'variant' => 'primary',

    'size' => '',

    'id' => null,

    'class' => '',

]);
```

## codeBlock

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\CodeBlock`

代码块（转义输出用户代码，防止 XSS；可选复制按钮与标题栏）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `code` | `''` | — |
| `language` | `''` | — |
| `title` | `''` | — |
| `copyable` | `true` | — |
| `theme` | `'dark'` | — |
| `id` | `null` | — |
| `class` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'code'     => '',
            'language' => '',
            'title'    => '',
            'copyable' => true,
            'theme'    => 'dark',
            'id'       => null,
            'class'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::codeBlock([

    'code' => '',

    'language' => '',

    'title' => '',

    'copyable' => true,

    'theme' => 'dark',

    'id' => null,

    'class' => '',

]);
```

## empty

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\EmptyState`

空状态（无数据占位卡片，支持图标/标题/描述/操作区） 注意：类名用 EmptyState（empty 是 PHP 保留字）；对外仍通过别名 empty 调用：   XfAdmin::empty([     'icon'  => 'ti ti-package',                 // Tabler 图标     'title' => '暂无数据',     'text'  => '还没有任何记录',     'action'=> XfAdmin::button([...]),          // 操作区（原样输出 HTML）     'size'  => '',                              // '' | 'sm' | 'lg'   ])

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `icon` | `'ti ti-package'` | — |
| `title` | `'暂无数据'` | — |
| `text` | `''` | — |
| `action` | `''` | — |
| `size` | `''` | — |
| `id` | `null` | — |
| `class` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'icon'  => 'ti ti-package',
            'title' => '暂无数据',
            'text'  => '',
            'action' => '',
            'size'  => '',
            'id'    => null,
            'class' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::empty([

    'icon' => 'ti ti-package',

    'title' => '暂无数据',

    'text' => '',

    'action' => '',

    'size' => '',

    'id' => null,

    'class' => '',

]);
```

## toolbar

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\Toolbar`

工具栏（页面操作区容器：左右分栏、自动换行、间距统一）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `left` | `''` | — |
| `right` | `''` | — |
| `class` | `''` | — |
| `id` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'left'   => '',
            'right'  => '',
            'class'  => '',
            'id'     => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::toolbar([

    'left' => '',

    'right' => '',

    'class' => '',

    'id' => null,

]);
```

## searchBox

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\SearchBox`

搜索框（带图标与可选尺寸的输入框，常用于 Toolbar 左区）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `'q'` | — |
| `value` | `''` | — |
| `placeholder` | `'搜索...'` | — |
| `size` | `''` | — |
| `class` | `''` | — |
| `id` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'        => 'q',
            'value'       => '',
            'placeholder' => '搜索...',
            'size'        => '',
            'class'       => '',
            'id'          => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::searchBox([

    'name' => 'q',

    'value' => '',

    'placeholder' => '搜索...',

    'size' => '',

    'class' => '',

    'id' => null,

]);
```

## colorPalette

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\ColorPalette`

配色方案展示（ui-colors.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'groups' => [],
        ]
```

### 示例

```php
echo XfAdmin::colorPalette([

    'groups' => [],

]);
```

## iconSet

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\IconSet`

图标集合展示（icons-flags.html / icons-lucide.html / icons-tabler.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `set` | `'tabler'` | — |
| `icons` | `[]` | — |
| `columns` | `6` | — |
| `searchable` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'set' => 'tabler',
            'icons' => [],
            'columns' => 6,
            'searchable' => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::iconSet([

    'set' => 'tabler',

    'icons' => [],

    'columns' => 6,

    'searchable' => true,

]);
```

## videoEmbed

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\VideoEmbed`

视频嵌入（ui-videos.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `src` | `''` | — |
| `provider` | `'youtube'` | — |
| `title` | `''` | — |
| `description` | `''` | — |
| `ratio` | `'16x9'` | — |
| `autoplay` | `false` | — |
| `allow` | `'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'src' => '',
            'provider' => 'youtube',
            'title' => '',
            'description' => '',
            'ratio' => '16x9',
            'autoplay' => false,
            'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
        ]
```
</details>

### 示例

```php
echo XfAdmin::videoEmbed([

    'src' => '',

    'provider' => 'youtube',

    'title' => '',

    'description' => '',

    'ratio' => '16x9',

    'autoplay' => false,

    'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',

]);
```

## commandPalette

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\CommandPalette`

命令面板（Command Palette）—— 对标 INSPINIA 缺失的全局快捷键命令中心

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `id` | `'xf-cmd-palette'` | — |
| `title` | `'快捷命令'` | — |
| `placeholder` | `'输入命令或搜索…'` | — |
| `hotkey` | `'meta+k'` | — |
| `commands` | `[]` | — |
| `empty` | `'未找到匹配的命令'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'id'        => 'xf-cmd-palette',
            'title'     => '快捷命令',
            'placeholder' => '输入命令或搜索…',
            'hotkey'    => 'meta+k',
            'commands'  => [],
            'empty'     => '未找到匹配的命令',
        ]
```
</details>

### 示例

```php
echo XfAdmin::commandPalette([

    'id' => 'xf-cmd-palette',

    'title' => '快捷命令',

    'placeholder' => '输入命令或搜索…',

    'hotkey' => 'meta+k',

    'commands' => [],

    'empty' => '未找到匹配的命令',

]);
```

## notificationCenter

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\NotificationCenter`

通知中心（Notification Center）—— 对标 INSPINIA 缺失的右侧通知抽屉

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `id` | `'xf-notify-center'` | — |
| `title` | `'通知中心'` | — |
| `badge` | `…` | — |
| `empty` | `'暂无新通知'` | — |
| `items` | `[]` | — |
| `footer` | `['all' => null, 'clear' => true]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'id'     => 'xf-notify-center',
            'title'  => '通知中心',
            'badge'  => 0,
            'empty'  => '暂无新通知',
            'items'  => [],
            'footer' => ['all' => null, 'clear' => true],
        ]
```
</details>

### 示例

```php
echo XfAdmin::notificationCenter([

    'id' => 'xf-notify-center',

    'title' => '通知中心',

    'badge' => 0,

    'empty' => '暂无新通知',

    'items' => [],

    'footer' => ['all' => null, 'clear' => true],

]);
```

## dropzoneUpload

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\DropzoneUpload`

拖拽上传区（Dropzone Upload）—— 对标 INSPINIA 缺失的拖拽上传组件

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `id` | `'xf-dropzone'` | — |
| `url` | `''` | — |
| `multiple` | `true` | — |
| `accept` | `'*'` | — |
| `maxSize` | `10` | MB |
| `hint` | `'将文件拖到此处，或点击浏览'` | — |
| `value` | `[]` | — |
| `name` | `'file'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'id'       => 'xf-dropzone',
            'url'      => '',
            'multiple' => true,
            'accept'   => '*',
            'maxSize'  => 10,         // MB
            'hint'     => '将文件拖到此处，或点击浏览',
            'value'    => [],
            'name'     => 'file',
        ]
```
</details>

### 示例

```php
echo XfAdmin::dropzoneUpload([

    'id' => 'xf-dropzone',

    'url' => '',

    'multiple' => true,

    'accept' => '*',

    'maxSize' => 10,

    'hint' => '将文件拖到此处，或点击浏览',

    'value' => [],

    'name' => 'file',

]);
```

## pricingCard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\PricingCard`

定价卡片（pages-pricing.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `''` | — |
| `price` | `''` | — |
| `period` | `'/ 月'` | — |
| `desc` | `null` | — |
| `features` | `[]` | — |
| `featured` | `false` | — |
| `badge` | `null` | — |
| `icon` | `null` | — |
| `button` | `…` | ', 'variant' => 'primary'], |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'     => '',
            'price'    => '',
            'period'   => '/ 月',
            'desc'     => null,
            'features' => [],
            'featured' => false,
            'badge'    => null,
            'icon'     => null,
            'button'   => ['label' => '选择方案', 'href' => '#', 'variant' => 'primary'],
        ]
```
</details>

### 示例

```php
echo XfAdmin::pricingCard([

    'name' => '',

    'price' => '',

    'period' => '/ 月',

    'desc' => null,

    'features' => [],

    'featured' => false,

    'badge' => null,

    'icon' => null,

    'button' => …, // ', 'variant' => 'primary'],
]);
```

## faq

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Faq`

常见问题（pages-faq.html）—— 手风琴样式

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `flush` | `false` | — |
| `bordered` | `true` | — |
| `open` | `…` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'flush'    => false,
            'bordered' => true,
            'open'     => 0,
        ]
```
</details>

### 示例

```php
echo XfAdmin::faq([

    'items' => [],

    'flush' => false,

    'bordered' => true,

    'open' => 0,

]);
```

## profileHeader

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProfileHeader`

个人资料头部（pages-profile.html / ecommerce-seller-details.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `cover` | `null` | — |
| `avatar` | `'users/avatar-1.jpg'` | — |
| `name` | `''` | — |
| `role` | `null` | — |
| `location` | `null` | — |
| `stats` | `[]` | — |
| `actions` | `null` | — |
| `tabs` | `[]` | — |
| `verified` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'cover'    => null,
            'avatar'   => 'users/avatar-1.jpg',
            'name'     => '',
            'role'     => null,
            'location' => null,
            'stats'    => [],
            'actions'  => null,
            'tabs'     => [],
            'verified' => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::profileHeader([

    'cover' => null,

    'avatar' => 'users/avatar-1.jpg',

    'name' => '',

    'role' => null,

    'location' => null,

    'stats' => [],

    'actions' => null,

    'tabs' => [],

    'verified' => false,

]);
```

## productCard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductCard`

商品卡片（ecommerce-products-grid.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `image` | `null` | — |
| `title` | `''` | — |
| `category` | `null` | — |
| `price` | `null` | — |
| `old_price` | `null` | — |
| `rating` | `null` | — |
| `rating_count` | `null` | — |
| `badge` | `null` | — |
| `href` | `…` | ', |
| `actions` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'image'        => null,
            'title'        => '',
            'category'     => null,
            'price'        => null,
            'old_price'    => null,
            'rating'       => null,
            'rating_count' => null,
            'badge'        => null,
            'href'         => '#',
            'actions'      => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::productCard([

    'image' => null,

    'title' => '',

    'category' => null,

    'price' => null,

    'old_price' => null,

    'rating' => null,

    'rating_count' => null,

    'badge' => null,

    'href' => …, // ',
    'actions' => null,

]);
```

## kanban

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Kanban`

看板（kanban）组件 —— 对齐 INSPINIA project-kanban.html 结构（与模板完全一致）：   .kanban-app > .card(.card-header 搜索/新增 + .card-body.p-0 > .kanban-content)     > .kanban-board(列) > .kanban-item(列头) + .kanban-board-group(data-simplebar, data-column)       > ul[data-kanban-list][data-plugins=sortable] > li.kanban-item > .card.shadow.border-light

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `columns` | `[]` | — |
| `search` | `true` | — |
| `addText` | `'新建卡片'` | — |
| `class` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'columns'  => [],
            'search'   => true,
            'addText'  => '新建卡片',
            'class'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::kanban([

    'columns' => [],

    'search' => true,

    'addText' => '新建卡片',

    'class' => '',

]);
```

## chatBox

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ChatBox`

聊天窗口（chat.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `status` | `null` | — |
| `avatar` | `null` | — |
| `height` | `'460px'` | — |
| `messages` | `[]` | — |
| `input` | `true` | — |
| `header` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'    => '',
            'status'   => null,
            'avatar'   => null,
            'height'   => '460px',
            'messages' => [],
            'input'    => true,
            'header'   => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::chatBox([

    'title' => '',

    'status' => null,

    'avatar' => null,

    'height' => '460px',

    'messages' => [],

    'input' => true,

    'header' => true,

]);
```

## invoiceTable

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\InvoiceTable`

发票明细表（invoice-details.html）—— 含合计区

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `currency` | `'¥'` | — |
| `summary` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'currency' => '¥',
            'summary'  => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::invoiceTable([

    'items' => [],

    'currency' => '¥',

    'summary' => [],

]);
```

## mailList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\MailList`

邮件列表（紧凑卡片版，复用于仪表盘等） 与 EmailApp 中间列表保持一致的表格式邮件行（对齐 INSPINIA email.html）： 勾选 / 星标 / 头像(avatar-xs) / 发件人 / 主题+预览 / 时间 / 附件。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['title' => '', 'action' => [], 'items' => []]
```

### 示例

```php
echo XfAdmin::mailList([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## fileManager

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\FileManager`

文件管理器网格（file-manager.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'files' => [],
            'cols'  => ['md' => 3, 'sm' => 6],
        ]
```

### 示例

```php
echo XfAdmin::fileManager([

    'files' => [],

    'cols' => ['md' => 3, 'sm' => 6],

]);
```

## widget

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Widget`

仪表盘小部件（widgets.html / index.html）—— 多种预设样式

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `style` | `'icon'` | — |
| `title` | `''` | — |
| `value` | `''` | — |
| `icon` | `null` | — |
| `variant` | `'primary'` | — |
| `trend` | `null` | — |
| `progress` | `null` | — |
| `footer` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'style'    => 'icon',
            'title'    => '',
            'value'    => '',
            'icon'     => null,
            'variant'  => 'primary',
            'trend'    => null,
            'progress' => null,
            'footer'   => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::widget([

    'style' => 'icon',

    'title' => '',

    'value' => '',

    'icon' => null,

    'variant' => 'primary',

    'trend' => null,

    'progress' => null,

    'footer' => null,

]);
```

## activityFeed

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ActivityFeed`

动态/活动流（project-activity.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'items' => [],
        ]
```

### 示例

```php
echo XfAdmin::activityFeed([

    'items' => [],

]);
```

## gallery

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Gallery`

图片画廊 / 作品集 —— 严格对齐 INSPINIA v4.1.0 `misc-gallery.html` 的结构与观感 INSPINIA 规范结构（本组件的输出蓝本）：   <div class="card">     <div class="card-header">                          ← 搜索框（app-search）+ 分类筛选（filter-buttons，btn-ghost-primary）     <div class="card-body">       <div class="row row-cols-... g-2">               ← 响应式等列栅格         <div class="col" data-category="...">          ← 每张图一个 col           <div class="card border-0 mb-0">             <div class="badge text-bg-dark badge-label ...">分类</div>   ← 左上角分类角标             <a class="image-popup"><img class="card-img rounded-2"></a> ← 灯箱 + 圆角图

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `masonry` | `true` | — |
| `lightbox` | `true` | — |
| `search` | `true` | — |
| `cols` | `4` | — |
| `ratio` | `'4x3'` | — |
| `gap` | `''` | 兼容旧参数；留空时用 INSPINIA 的 g-2 间距 |
| `filter` | `[]` | ['all' => '全部', 'design' => '设计', ...] |
| `card` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'masonry'  => true,
            'lightbox' => true,
            'search'   => true,
            'cols'     => 4,
            'ratio'    => '4x3',
            'gap'      => '',       // 兼容旧参数；留空时用 INSPINIA 的 g-2 间距
            'filter'   => [],       // ['all' => '全部', 'design' => '设计', ...]
            'card'     => true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::gallery([

    'items' => [],

    'masonry' => true,

    'lightbox' => true,

    'search' => true,

    'cols' => 4,

    'ratio' => '4x3',

    'gap' => '',

    'filter' => [],

    'card' => true,

]);
```

## blogList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\BlogList`

博客文章列表（blog.html / blog-details.html）—— 卡片网格或列表视图

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `layout` | `'grid'` | — |
| `cols` | `3` | — |
| `gap` | `'24px'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'  => [],
            'layout' => 'grid',
            'cols'   => 3,
            'gap'    => '24px',
        ]
```
</details>

### 示例

```php
echo XfAdmin::blogList([

    'items' => [],

    'layout' => 'grid',

    'cols' => 3,

    'gap' => '24px',

]);
```

## invoiceList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\InvoiceList`

发票列表（invoice.html）—— 表格形式呈现多张发票，含状态、金额、操作

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `currency` | `'¥'` | — |
| `title` | `'发票'` | — |
| `summary` | `[]` | 顶部统计卡片：['label'=>,'value'=>,'variant'=>] |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'currency' => '¥',
            'title'    => '发票',
            'summary'  => [],       // 顶部统计卡片：['label'=>,'value'=>,'variant'=>]
        ]
```
</details>

### 示例

```php
echo XfAdmin::invoiceList([

    'items' => [],

    'currency' => '¥',

    'title' => '发票',

    'summary' => [],

]);
```

## searchResults

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\SearchResults`

搜索结果列表（pages-search-results.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `query` | `''` | — |
| `count` | `…` | — |
| `items` | `[]` | — |
| `filters` | `[]` | — |
| `pagination` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'query'   => '',
            'count'   => 0,
            'items'   => [],
            'filters' => [],
            'pagination' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::searchResults([

    'query' => '',

    'count' => 0,

    'items' => [],

    'filters' => [],

    'pagination' => '',

]);
```

## permissionMatrix

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\PermissionMatrix`

权限矩阵（roles.html / permissions.html）—— 角色 × 权限 的勾选网格

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `roles` | `[]` | — |
| `groups` | `[]` | — |
| `values` | `[]` | — |
| `readOnly` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'roles'    => [],
            'groups'   => [],
            'values'   => [],
            'readOnly' => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::permissionMatrix([

    'roles' => [],

    'groups' => [],

    'values' => [],

    'readOnly' => false,

]);
```

## apiKeys

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ApiKeys`

API 密钥管理（api-keys.html）—— 列表展示、复制、显示/隐藏、重新生成

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['items' => [], 'reveal' => true, 'regenerate' => true]
```

### 示例

```php
echo XfAdmin::apiKeys([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## commentThread

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\CommentThread`

评论/讨论线程（article.html / forum-post.html）—— 支持嵌套回复

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['items' => [], 'form' => true, 'maxDepth' => 4]
```

### 示例

```php
echo XfAdmin::commentThread([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## emailCompose

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\EmailCompose`

邮件撰写（email-compose.html）—— 收件人/主题 + 富文本正文

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `to` | `''` | — |
| `subject` | `''` | — |
| `body` | `''` | — |
| `action` | `…` | ', |
| `editor` | `'quill'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'to'      => '',
            'subject' => '',
            'body'    => '',
            'action'  => '#',
            'editor'  => 'quill',
        ]
```
</details>

### 示例

```php
echo XfAdmin::emailCompose([

    'to' => '',

    'subject' => '',

    'body' => '',

    'action' => …, // ',
    'editor' => 'quill',

]);
```

## customers

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Customers`

客户 / 会员管理（ecommerce-customers.html / crm-clients.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `view` | `'grid'` | — |
| `searchable` | `true` | — |
| `items` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'view'       => 'grid',
            'searchable' => true,
            'items'      => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::customers([

    'title' => '',

    'view' => 'grid',

    'searchable' => true,

    'items' => [],

]);
```

## orders

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Orders`

订单列表 —— 严格对齐 INSPINIA v4.1.0 `ecommerce-orders.html` 的结构与观感 模板规范结构（本组件的输出蓝本）：   <div class="card">     <div class="card-header">      ← 搜索框 + 状态/支付筛选下拉 + 每页条数 + 批量删除     <div class="table-responsive">       <table class="table table-custom table-select table-hover align-middle mb-0">         <thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">         <tbody>：勾选框 / #订单号(link-reset) / 日期+时间 / 客户(.avatar avatar-sm)                 / 金额 / 付款状态(ti-point-filled) / 订单状态(badge-soft) / 支付方式 / 圆形图标操作钮     <div class="card-footer border-0"> ← 统计信息 + 分页 前端交互（搜索/筛选/分页/全选/批量删除）由 xfadmin.js 的 `xftable` 模块驱动 （对标模板的 custom-table.js data-table-* 体系）。服务端大数据量请改用 DataTable 组件。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `orders` | `[]` | — |
| `searchable` | `true` | — |
| `filterable` | `true` | — |
| `selectable` | `true` | — |
| `page_size` | `10` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'orders'     => [],
            'searchable' => true,
            'filterable' => true,
            'selectable' => true,
            'page_size'  => 10,
        ]
```
</details>

### 示例

```php
echo XfAdmin::orders([

    'title' => '',

    'orders' => [],

    'searchable' => true,

    'filterable' => true,

    'selectable' => true,

    'page_size' => 10,

]);
```

## taskList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\TaskList`

任务清单面板（Tasks）—— 严格对齐 INSPINIA v4 `tasks.html` 的看板式任务列表结构。 输出蓝本：   <div class="card xf-tasklist">     <div class="card-header"> 标题 + 过滤（全部/进行中/已完成）+ 新建按钮     <div class="list-group xf-task-group">       每个任务：         <label class="list-group-item xf-task-item">  ← 含自定义勾选框 + 标题 + 元信息（优先级徽标/指派人/截止日） 前端交互（勾选完成、过滤）由 xfadmin.js 的 initTaskList 驱动。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `tasks` | `[]` | — |
| `filterable` | `true` | — |
| `addable` | `true` | — |
| `add_url` | `'javascript:void(0);'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'tasks'      => [],
            'filterable' => true,
            'addable'    => true,
            'add_url'    => 'javascript:void(0);',
        ]
```
</details>

### 示例

```php
echo XfAdmin::taskList([

    'title' => '',

    'tasks' => [],

    'filterable' => true,

    'addable' => true,

    'add_url' => 'javascript:void(0);',

]);
```

## deals

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Deals`

销售漏斗 / 商机管线（Deals / Pipeline）—— 对齐 INSPINIA v4 `deals.html` 的阶段看板结构。 输出蓝本：   <div class="xf-deals">     <div class="row">       每个阶段一列：         <div class="col-xl-3 xf-deal-stage">           <div class="card"> 标题 + 合计金额 + 数量             <div class="xf-deal-card"> 每个商机卡片：标题/客户/金额/负责人/概率 阶段通过 stages 配置定义（key=阶段标识，name=显示名，color=语义色，total 自动汇总）。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `stages` | `…` | — |
| `lead` | `['name' => '线索', 'color' => 'info']` | — |
| `contact` | `['name' => '接洽', 'color' => 'primary']` | — |
| `proposal` | `['name' => '方案', 'color' => 'warning']` | — |
| `won` | `['name' => '成交', 'color' => 'success']` | — |
| `deals` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'stages' => [
                'lead'     => ['name' => '线索', 'color' => 'info'],
                'contact'  => ['name' => '接洽', 'color' => 'primary'],
                'proposal' => ['name' => '方案', 'color' => 'warning'],
                'won'      => ['name' => '成交', 'color' => 'success'],
            ],
            'deals'  => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::deals([

    'stages' => …, // 见说明
    'lead' => ['name' => '线索', 'color' => 'info'],

    'contact' => ['name' => '接洽', 'color' => 'primary'],

    'proposal' => ['name' => '方案', 'color' => 'warning'],

    'won' => ['name' => '成交', 'color' => 'success'],

    'deals' => [],

]);
```

## orderDetails

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\OrderDetails`

订单详情（ecommerce-order-details.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['order' => []]
```

### 示例

```php
echo XfAdmin::orderDetails([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## productDetails

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductDetails`

商品详情（ecommerce-product-details.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['product' => []]
```

### 示例

```php
echo XfAdmin::productDetails([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## projects

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Projects`

项目列表（projects.html / projects-list.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['title' => '', 'projects' => [], 'view' => 'grid']
```

### 示例

```php
echo XfAdmin::projects([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## projectDetails

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProjectDetails`

项目详情（project-details.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['project' => []]
```

### 示例

```php
echo XfAdmin::projectDetails([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## outlook

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Outlook`

三栏邮件应用（outlook.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['folders' => [], 'messages' => [], 'selected' => []]
```

### 示例

```php
echo XfAdmin::outlook([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## forumThread

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ForumThread`

论坛主题（forum-view.html / forum-post.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['thread' => [], 'posts' => []]
```

### 示例

```php
echo XfAdmin::forumThread([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## blogArticle

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\BlogArticle`

博客文章详情（blog/article.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['article' => []]
```

### 示例

```php
echo XfAdmin::blogArticle([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## roles

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Roles`

角色管理（permissions.html / roles 页面）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['title' => '', 'roles' => [], 'permissions' => []]
```

### 示例

```php
echo XfAdmin::roles([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## invoiceCreate

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\InvoiceCreate`

发票创建 / 编辑（invoice-create.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['invoice' => []]
```

### 示例

```php
echo XfAdmin::invoiceCreate([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## teamMember

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\TeamMember`

团队成员卡片网格（apps-team.html / INSPINIA team 区块）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'members' => [],
            'cols'    => 4,
        ]
```

### 示例

```php
echo XfAdmin::teamMember([

    'members' => [],

    'cols' => 4,

]);
```

## testimonial

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Testimonial`

用户证言 / 评价卡片（landing / testimonials 区块）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `cols` | `3` | — |
| `carousel` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'cols'     => 3,
            'carousel' => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::testimonial([

    'items' => [],

    'cols' => 3,

    'carousel' => false,

]);
```

## todoList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\TodoList`

待办清单（dashboard widget / apps-tasks.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `items` | `[]` | — |
| `addable` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'    => '',
            'items'    => [],
            'addable'  => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::todoList([

    'title' => '',

    'items' => [],

    'addable' => false,

]);
```

## issueTracker

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\IssueTracker`

问题跟踪列表（INSPINIA issue-tracker.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `searchable` | `true` | — |
| `add_text` | `''` | — |
| `issues` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'searchable' => true,
            'add_text'   => '',
            'issues'     => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::issueTracker([

    'title' => '',

    'searchable' => true,

    'add_text' => '',

    'issues' => [],

]);
```

## voteList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\VoteList`

投票列表（INSPINIA vote-list.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'title' => '',
            'items' => [],
        ]
```

### 示例

```php
echo XfAdmin::voteList([

    'title' => '',

    'items' => [],

]);
```

## metricCard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\MetricCard`

指标卡片（metrics.html） 「数值 + 变化趋势 + 迷你图表」三合一的经营指标卡，常用于仪表盘顶部的核心指标区。 数值支持滚动计数动画（count-up），迷你图表基于 ECharts 渲染（donut/pie/bar/area/line）。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `''` | — |
| `value` | `…` | — |
| `prefix` | `''` | — |
| `suffix` | `''` | — |
| `decimals` | `…` | — |
| `trend` | `null` | — |
| `trend_text` | `'较上周'` | — |
| `chart` | `'donut'` | — |
| `data` | `[]` | — |
| `labels` | `[]` | — |
| `color` | `…` | 3e60d5', |
| `icon` | `null` | — |
| `footer` | `null` | — |
| `url` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '',
            'value'      => 0,
            'prefix'     => '',
            'suffix'     => '',
            'decimals'   => 0,
            'trend'      => null,
            'trend_text' => '较上周',
            'chart'      => 'donut',
            'data'       => [],
            'labels'     => [],
            'color'      => '#3e60d5',
            'icon'       => null,
            'footer'     => null,
            'url'        => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::metricCard([

    'title' => '',

    'value' => 0,

    'prefix' => '',

    'suffix' => '',

    'decimals' => 0,

    'trend' => null,

    'trend_text' => '较上周',

    'chart' => 'donut',

    'data' => [],

    'labels' => [],

    // … 其余选项见上表 / 默认值（源码）
]);
```

## terms

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Terms`

条款 / 协议页（pages-terms-conditions.html） 「侧栏目录 + 分节正文」的法律条款版式，目录随滚动高亮（Bootstrap Scrollspy）， 适用于服务条款、隐私政策、用户协议等长文档页面。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'服务条款'` | — |
| `updated_at` | `null` | — |
| `intro` | `null` | — |
| `sections` | `[]` | — |
| `toc` | `true` | — |
| `accept` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '服务条款',
            'updated_at' => null,
            'intro'      => null,
            'sections'   => [],
            'toc'        => true,
            'accept'     => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::terms([

    'title' => '服务条款',

    'updated_at' => null,

    'intro' => null,

    'sections' => [],

    'toc' => true,

    'accept' => null,

]);
```

## contactCard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ContactCard`

联系人卡片网格（INSPINIA contacts.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'contacts' => [],
            'cols'     => 3,
        ]
```

### 示例

```php
echo XfAdmin::contactCard([

    'contacts' => [],

    'cols' => 3,

]);
```

## companyCard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\CompanyCard`

公司卡片列表（INSPINIA companies.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'companies' => [],
            'cols'      => 2,
        ]
```

### 示例

```php
echo XfAdmin::companyCard([

    'companies' => [],

    'cols' => 2,

]);
```

## clients

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Clients`

CRM 客户列表（INSPINIA clients.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'客户列表'` | — |
| `searchable` | `true` | — |
| `type_filter` | `[]` | — |
| `add_text` | `'新增客户'` | — |
| `clients` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'       => '客户列表',
            'searchable'  => true,
            'type_filter' => [],
            'add_text'    => '新增客户',
            'clients'     => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::clients([

    'title' => '客户列表',

    'searchable' => true,

    'type_filter' => [],

    'add_text' => '新增客户',

    'clients' => [],

]);
```

## sellers

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Sellers`

电商卖家列表（INSPINIA ecommerce-sellers.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'卖家管理'` | — |
| `searchable` | `true` | — |
| `add_text` | `'新增卖家'` | — |
| `sellers` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '卖家管理',
            'searchable' => true,
            'add_text'   => '新增卖家',
            'sellers'    => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::sellers([

    'title' => '卖家管理',

    'searchable' => true,

    'add_text' => '新增卖家',

    'sellers' => [],

]);
```

## reviewList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ReviewList`

商品评价列表（INSPINIA ecommerce-reviews.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'商品评价'` | — |
| `summary` | `[]` | — |
| `reviews` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'   => '商品评价',
            'summary' => [],
            'reviews' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::reviewList([

    'title' => '商品评价',

    'summary' => [],

    'reviews' => [],

]);
```

## projectTeamBoard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProjectTeamBoard`

项目团队看板（INSPINIA project-team-board.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'项目团队'` | — |
| `cols` | `3` | — |
| `teams` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title' => '项目团队',
            'cols'  => 3,
            'teams' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::projectTeamBoard([

    'title' => '项目团队',

    'cols' => 3,

    'teams' => [],

]);
```

## emailApp

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\EmailApp`

三栏邮件应用（email.html 风格） 对齐 INSPINIA email.html：左侧文件夹（list-custom）+ 中间邮件列表（table table-hover table-select， 含 勾选/星标/头像/发件人/主题+预览/时间/附件）+ 右侧阅读窗格。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['folders' => [], 'messages' => [], 'selected' => [], 'view' => 'split', 'composeText' => '写邮件']
```

### 示例

```php
echo XfAdmin::emailApp([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## chatApp

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ChatApp`

聊天应用（会话列表 + 聊天窗口）—— INSPINIA apps-chat.html 整页抽取

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `conversations` | `[]` | — |
| `peer` | `[]` | — |
| `messages` | `[]` | — |
| `search` | `'搜索联系人…'` | — |
| `placeholder` | `'输入消息…'` | — |
| `height` | `'60vh'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'conversations' => [],
            'peer'          => [],
            'messages'      => [],
            'search'        => '搜索联系人…',
            'placeholder'   => '输入消息…',
            'height'        => '60vh',
        ]
```
</details>

### 示例

```php
echo XfAdmin::chatApp([

    'conversations' => [],

    'peer' => [],

    'messages' => [],

    'search' => '搜索联系人…',

    'placeholder' => '输入消息…',

    'height' => '60vh',

]);
```

## profilePage

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProfilePage`

个人主页（封面 + 头像 + 统计 + 操作 + 标签页）—— INSPINIA pages-profile.html 整页抽取

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `cover` | `''` | — |
| `avatar` | `''` | — |
| `name` | `''` | — |
| `verified` | `false` | — |
| `role` | `''` | — |
| `meta` | `[]` | — |
| `stats` | `[]` | — |
| `actions` | `[]` | — |
| `tabs` | `[]` | — |
| `content` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'cover'    => '',
            'avatar'   => '',
            'name'     => '',
            'verified' => false,
            'role'     => '',
            'meta'     => [],
            'stats'    => [],
            'actions'  => [],
            'tabs'     => [],
            'content'  => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::profilePage([

    'cover' => '',

    'avatar' => '',

    'name' => '',

    'verified' => false,

    'role' => '',

    'meta' => [],

    'stats' => [],

    'actions' => [],

    'tabs' => [],

    'content' => null,

]);
```

## invoiceDetail

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\InvoiceDetail`

发票详情页（抬头 / 双方信息 / 明细 / 汇总 / 操作）—— INSPINIA invoice.html 整页抽取

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `logo` | `''` | — |
| `title` | `''` | — |
| `number` | `''` | — |
| `status` | `[]` | — |
| `meta` | `[]` | — |
| `from` | `[]` | — |
| `to` | `[]` | — |
| `items` | `[]` | — |
| `currency` | `'¥'` | — |
| `summary` | `[]` | — |
| `notes` | `''` | — |
| `actions` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'logo'     => '',
            'title'    => '',
            'number'   => '',
            'status'   => [],
            'meta'     => [],
            'from'     => [],
            'to'       => [],
            'items'    => [],
            'currency' => '¥',
            'summary'  => [],
            'notes'    => '',
            'actions'  => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::invoiceDetail([

    'logo' => '',

    'title' => '',

    'number' => '',

    'status' => [],

    'meta' => [],

    'from' => [],

    'to' => [],

    'items' => [],

    'currency' => '¥',

    'summary' => [],

    // … 其余选项见上表 / 默认值（源码）
]);
```

## companies

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Companies`

公司列表卡片网格（对齐 INSPINIA companies.html） 每张卡片包含：Logo、公司名、官网、关注按钮、位置/行业徽标、简介、 员工数 / 营收 / 星级评分。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'cols'      => 3,
            'companies' => [],
        ]
```

### 示例

```php
echo XfAdmin::companies([

    'cols' => 3,

    'companies' => [],

]);
```

## productCategories

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductCategories`

商品分类管理表（对齐 INSPINIA ecommerce-categories.html） 带工具栏（搜索 / 状态筛选 / 新增按钮）的分类管理列表： 缩略图 + 名称、Slug、商品数、订单数、销售额、更新时间、状态徽标与行内操作。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'商品分类'` | — |
| `searchable` | `true` | — |
| `add_text` | `'新增分类'` | — |
| `categories` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'      => '商品分类',
            'searchable' => true,
            'add_text'   => '新增分类',
            'categories' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::productCategories([

    'title' => '商品分类',

    'searchable' => true,

    'add_text' => '新增分类',

    'categories' => [],

]);
```

## productAdd

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductAdd`

新增 / 编辑商品表单页（对齐 INSPINIA ecommerce-add-product.html） 左侧主表单：基本信息（名称 / SKU / 库存 / 描述）、商品图上传占位、 价格（原价 / 折扣类型 / 折扣值）、分类（品牌 / 分类 / 子分类）； 右侧发布栏：状态 / 可见性 / 标签 / 操作按钮。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `action` | `…` | ', |
| `method` | `'POST'` | — |
| `brands` | `[]` | — |
| `categories` | `[]` | — |
| `sub_categories` | `[]` | — |
| `statuses` | `['草稿', '上架', '下架']` | — |
| `tags` | `[]` | — |
| `values` | `[]` | — |
| `submit_text` | `'保存商品'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'action'         => '#',
            'method'         => 'POST',
            'brands'         => [],
            'categories'     => [],
            'sub_categories' => [],
            'statuses'       => ['草稿', '上架', '下架'],
            'tags'           => [],
            'values'         => [],
            'submit_text'    => '保存商品',
        ]
```
</details>

### 示例

```php
echo XfAdmin::productAdd([

    'action' => …, // ',
    'method' => 'POST',

    'brands' => [],

    'categories' => [],

    'sub_categories' => [],

    'statuses' => ['草稿', '上架', '下架'],

    'tags' => [],

    'values' => [],

    'submit_text' => '保存商品',

]);
```

## sellerDetails

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\SellerDetails`

卖家 / 店铺详情页（对齐 INSPINIA ecommerce-seller-details.html） 上部店铺资料卡（Logo、店名、认证标、评分、联系方式、操作按钮） + 统计卡行（销售额 / 订单 / 商品 / 好评率） + 在售商品表。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `seller` | `[]` | — |
| `stats` | `[]` | — |
| `products` | `[]` | — |
| `actions` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'seller'   => [],
            'stats'    => [],
            'products' => [],
            'actions'  => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::sellerDetails([

    'seller' => [],

    'stats' => [],

    'products' => [],

    'actions' => [],

]);
```

## article

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Article`

文章阅读页（对应 INSPINIA article.html） 渲染一篇完整文章的阅读视图：标题与元信息（分类 / 日期 / 阅读时长）、 封面大图、作者信息块（头像 + 简介）、正文段落、引用块、标签与「相关文章」网格。 适合博客、资讯、帮助文档等详情页。 配置项（article）：  - title    文章标题  - category 分类标签（可选）  - date     发布日期（可选）  - read_time 阅读时长（可选）  - author   ['name'=>作者名, 'avatar'=>头像相对路径, 'bio'=>简介]（可选）  - cover    封面图相对路径（可选，固定 360px 高并裁切）  - body     正文段落数组（支持内联 HTML）  - quote    引用语（可选）  - tags     标签数组（可选）  - related  相关文章数组（可选）：[['title','excerpt','image','date']]

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['article' => []]
```

### 示例

```php
echo XfAdmin::article([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## projectActivity

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProjectActivity`

项目动态时间线（对应 INSPINIA project-activity.html） 以纵向时间线展示某个项目的最近动态：每条记录含操作者头像、动作标题、 说明文字与发生时间，按时间倒序排列。适合项目详情页的「动态 / 进展」区块。 与通用 Timeline 组件的区别：本组件聚焦「谁 + 做了什么」，并内嵌操作者头像。 配置项：  - title 区块标题（默认「项目动态」）  - items 动态数组，每条：      user  操作者名称      avatar 操作者头像相对路径      title 动作标题      desc  说明文字（可选，支持内联 HTML）      time  发生时间（如「10 分钟前」，可选）      color 主题色（默认 primary，可选 success/info/warning/danger 等）      icon  图标类名（可选）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['title' => '项目动态', 'items' => []]
```

### 示例

```php
echo XfAdmin::projectActivity([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## shoppingCart

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ShoppingCart`

购物车列表（apps-ecommerce-cart.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `subtotal` | `…` | — |
| `shipping` | `…` | — |
| `tax` | `…` | — |
| `discount` | `…` | — |
| `total` | `…` | — |
| `currency` | `'¥'` | — |
| `emptyMessage` | `'购物车为空'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items' => [],
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'currency' => '¥',
            'emptyMessage' => '购物车为空',
        ]
```
</details>

### 示例

```php
echo XfAdmin::shoppingCart([

    'items' => [],

    'subtotal' => 0,

    'shipping' => 0,

    'tax' => 0,

    'discount' => 0,

    'total' => 0,

    'currency' => '¥',

    'emptyMessage' => '购物车为空',

]);
```

## checkout

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Checkout`

电商结算页（apps-ecommerce-checkout.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `steps` | `[]` | — |
| `orderSummary` | `…` | — |
| `subtotal` | `…` | — |
| `shipping` | `…` | — |
| `tax` | `…` | — |
| `total` | `…` | — |
| `currency` | `'¥'` | — |
| `currentStep` | `…` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'steps' => [],
            'orderSummary' => [
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0,
            ],
            'currency' => '¥',
            'currentStep' => 0,
        ]
```
</details>

### 示例

```php
echo XfAdmin::checkout([

    'steps' => [],

    'orderSummary' => …, // 见说明
    'subtotal' => 0,

    'shipping' => 0,

    'tax' => 0,

    'total' => 0,

    'currency' => '¥',

    'currentStep' => 0,

]);
```

## marketplace

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Marketplace`

商城市场首页（apps-ecommerce-marketplace.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `categories` | `[]` | — |
| `filters` | `[]` | — |
| `products` | `[]` | — |
| `currency` | `'$'` | — |
| `columns` | `[4, 3, 2, 1]` | — |
| `subtitle` | `'Find Your Perfect Style'` | — |
| `subtitle_desc` | `'👕 Discover styles tailored for everyone'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'categories' => [],
            'filters' => [],
            'products' => [],
            'currency' => '$',
            'columns' => [4, 3, 2, 1],
            'subtitle' => 'Find Your Perfect Style',
            'subtitle_desc' => '👕 Discover styles tailored for everyone',
        ]
```
</details>

### 示例

```php
echo XfAdmin::marketplace([

    'categories' => [],

    'filters' => [],

    'products' => [],

    'currency' => '$',

    'columns' => [4, 3, 2, 1],

    'subtitle' => 'Find Your Perfect Style',

    'subtitle_desc' => '👕 Discover styles tailored for everyone',

]);
```

## accountSettings

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\AccountSettings`

账号设置页面（pages-account-settings.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `tabs` | `[]` | — |
| `user` | `[]` | — |
| `activeTab` | `…` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'tabs' => [],
            'user' => [],
            'activeTab' => 0,
        ]
```
</details>

### 示例

```php
echo XfAdmin::accountSettings([

    'tabs' => [],

    'user' => [],

    'activeTab' => 0,

]);
```

## sitemap

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Sitemap`

站点地图（pages-sitemap.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'columns' => [],
            'colClass' => 'col-md-4',
        ]
```

### 示例

```php
echo XfAdmin::sitemap([

    'columns' => [],

    'colClass' => 'col-md-4',

]);
```

## privacyPolicy

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\PrivacyPolicy`

隐私政策页面（pages-privacy-policy.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'Privacy Policy'` | — |
| `effectiveDate` | `''` | — |
| `intro` | `''` | — |
| `sections` | `[]` | — |
| `contactEmail` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title' => 'Privacy Policy',
            'effectiveDate' => '',
            'intro' => '',
            'sections' => [],
            'contactEmail' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::privacyPolicy([

    'title' => 'Privacy Policy',

    'effectiveDate' => '',

    'intro' => '',

    'sections' => [],

    'contactEmail' => '',

]);
```

## appManage

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\AppManage`

应用管理中心（apps-manage.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `stats` | `[]` | — |
| `apps` | `[]` | — |
| `maxApps` | `10` | — |
| `search` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'stats' => [],
            'apps' => [],
            'maxApps' => 10,
            'search' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::appManage([

    'stats' => [],

    'apps' => [],

    'maxApps' => 10,

    'search' => '',

]);
```

## warehouse

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Warehouse`

仓库管理（apps-ecommerce-warehouse.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `warehouses` | `[]` | — |
| `totalCapacity` | `'0'` | — |
| `totalInventory` | `'0'` | — |
| `title` | `'仓库管理'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'warehouses' => [],
            'totalCapacity' => '0',
            'totalInventory' => '0',
            'title' => '仓库管理',
        ]
```
</details>

### 示例

```php
echo XfAdmin::warehouse([

    'warehouses' => [],

    'totalCapacity' => '0',

    'totalInventory' => '0',

    'title' => '仓库管理',

]);
```

## refunds

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Refunds`

退款管理（apps-ecommerce-refunds.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `refunds` | `[]` | — |
| `currency` | `'¥'` | — |
| `title` | `'退款管理'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'refunds' => [],
            'currency' => '¥',
            'title' => '退款管理',
        ]
```
</details>

### 示例

```php
echo XfAdmin::refunds([

    'refunds' => [],

    'currency' => '¥',

    'title' => '退款管理',

]);
```

## sales

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Sales`

销售仪表板（apps-ecommerce-sales.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `stats` | `[]` | — |
| `recentOrders` | `[]` | — |
| `topProducts` | `[]` | — |
| `currency` | `'¥'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'stats' => [],
            'recentOrders' => [],
            'topProducts' => [],
            'currency' => '¥',
        ]
```
</details>

### 示例

```php
echo XfAdmin::sales([

    'stats' => [],

    'recentOrders' => [],

    'topProducts' => [],

    'currency' => '¥',

]);
```

## purchasedOrders

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\PurchasedOrders`

已采购订单（apps-ecommerce-purchased-orders.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `orders` | `[]` | — |
| `currency` | `'¥'` | — |
| `title` | `'采购订单'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'orders' => [],
            'currency' => '¥',
            'title' => '采购订单',
        ]
```
</details>

### 示例

```php
echo XfAdmin::purchasedOrders([

    'orders' => [],

    'currency' => '¥',

    'title' => '采购订单',

]);
```

## attributes

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\Attributes`

产品属性管理（apps-ecommerce-attributes.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'attributes' => [],
            'title' => '产品属性',
        ]
```

### 示例

```php
echo XfAdmin::attributes([

    'attributes' => [],

    'title' => '产品属性',

]);
```

## ecommerceSettings

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\EcommerceSettings`

电商设置（apps-ecommerce-settings.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'store' => [],
            'sections' => [],
        ]
```

### 示例

```php
echo XfAdmin::ecommerceSettings([

    'store' => [],

    'sections' => [],

]);
```

## productsGrid

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductsGrid`

商品网格视图（apps-ecommerce-products-grid.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `products` | `[]` | — |
| `currency` | `'¥'` | — |
| `columns` | `[4, 3, 2, 1]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'products' => [],
            'currency' => '¥',
            'columns' => [4, 3, 2, 1],
        ]
```
</details>

### 示例

```php
echo XfAdmin::productsGrid([

    'products' => [],

    'currency' => '¥',

    'columns' => [4, 3, 2, 1],

]);
```

## productViews

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ProductViews`

商品浏览统计（apps-ecommerce-product-views.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `products` | `[]` | — |
| `totalViews` | `'0'` | — |
| `totalUnique` | `'0'` | — |
| `title` | `'商品浏览量'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'products' => [],
            'totalViews' => '0',
            'totalUnique' => '0',
            'title' => '商品浏览量',
        ]
```
</details>

### 示例

```php
echo XfAdmin::productViews([

    'products' => [],

    'totalViews' => '0',

    'totalUnique' => '0',

    'title' => '商品浏览量',

]);
```

## analyticsDashboard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\AnalyticsDashboard`

分析仪表盘（dashboards-analytics.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'stats' => [],
            'recentActivity' => [],
        ]
```

### 示例

```php
echo XfAdmin::analyticsDashboard([

    'stats' => [],

    'recentActivity' => [],

]);
```

## ecommerceDashboard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\EcommerceDashboard`

电商仪表盘（dashboard-ecommerce.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `stats` | `[]` | — |
| `recentOrders` | `[]` | — |
| `topProducts` | `[]` | — |
| `chart` | `true` | — |
| `chartData` | `null` | 图表自定义数据数组；为 null 时生成占位演示数据 |
| `currency` | `'¥'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'stats' => [],
            'recentOrders' => [],
            'topProducts' => [],
            'chart' => true,
            'chartData' => null,  // 图表自定义数据数组；为 null 时生成占位演示数据
            'currency' => '¥',
        ]
```
</details>

### 示例

```php
echo XfAdmin::ecommerceDashboard([

    'stats' => [],

    'recentOrders' => [],

    'topProducts' => [],

    'chart' => true,

    'chartData' => null,

    'currency' => '¥',

]);
```

## widgetsDashboard

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\WidgetsDashboard`

小部件仪表盘（widgets.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'widgets' => ['stats', 'charts', 'messages', 'activity', 'tasks'],
            'currency' => '¥',
        ]
```

### 示例

```php
echo XfAdmin::widgetsDashboard([

    'widgets' => ['stats', 'charts', 'messages', 'activity', 'tasks'],

    'currency' => '¥',

]);
```

## moduleNav

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ModuleNav`

模块子导航 / 分段标签导航 用于后台「企业应用」等模块的页面内切换导航，把原先散落在各控制器里 重复拼接的 <ul class="nav nav-pills"> 循环提炼为可复用组件。 支持两种形态：   1) 扁平列表：items = [['label'=>.., 'url'=>.., 'active'=>true, 'icon'=>..], ...]   2) 分组（带分区标题）：sections = [['title'=>.., 'items'=>[...]], ...]

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `items` | `[]` | — |
| `sections` | `[]` | — |
| `type` | `'pills'` | pills | tabs | underline |
| `align` | `'start'` | start | center | end |
| `class` | `'xf-module-subnav'` | — |
| `id` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'items'    => [],
            'sections' => [],
            'type'     => 'pills',   // pills | tabs | underline
            'align'    => 'start',   // start | center | end
            'class'    => 'xf-module-subnav',
            'id'       => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::moduleNav([

    'items' => [],

    'sections' => [],

    'type' => 'pills',

    'align' => 'start',

    'class' => 'xf-module-subnav',

    'id' => null,

]);
```

## moduleGrid

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ModuleGrid`

模块网格 / 应用中心卡片墙 用于后台「企业应用中心」等场景：按分区展示一组模块入口卡片， 把原先散落在各控制器里重复拼接的「分区标题 + 卡片行」提炼为可复用组件。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `sections` | `[]` | — |
| `columns` | `4` | 桌面列数 |
| `title` | `''` | — |
| `subtitle` | `''` | — |
| `class` | `''` | — |
| `id` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'sections' => [],
            'columns'  => 4,     // 桌面列数
            'title'    => '',
            'subtitle' => '',
            'class'    => '',
            'id'       => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::moduleGrid([

    'sections' => [],

    'columns' => 4,

    'title' => '',

    'subtitle' => '',

    'class' => '',

    'id' => null,

]);
```

## dashboardGrid

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\DashboardGrid`

通用仪表盘网格（dashboards-* 系列页面抽象） 将「统计卡片行 + 图表区（左右两列）+ 底部标签页内容」封装为单组件， 适用于所有仪表盘类页面（运营 / 分析 / 电商 / 项目 …）。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `stats` | `[]` | — |
| `charts` | `[]` | — |
| `bottom` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'stats'  => [],
            'charts' => [],
            'bottom' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::dashboardGrid([

    'stats' => [],

    'charts' => [],

    'bottom' => '',

]);
```

## settingsCenter

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\SettingsCenter`

设置中心（settings.html 抽象） 左侧分组导航 + 右侧表单面板，典型的后台「设置」页面。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'title'  => '设置',
            'groups' => [],
        ]
```

### 示例

```php
echo XfAdmin::settingsCenter([

    'title' => '设置',

    'groups' => [],

]);
```

## reportPage

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ReportPage`

报表页（报表类页面抽象） 顶部筛选栏 + 图表区（可多列）+ 底部数据表，典型的后台「数据分析 / 报表」页面。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'报表'` | — |
| `filters` | `''` | — |
| `charts` | `[]` | — |
| `table` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'   => '报表',
            'filters' => '',
            'charts'  => [],
            'table'   => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::reportPage([

    'title' => '报表',

    'filters' => '',

    'charts' => [],

    'table' => '',

]);
```

## calendar

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Calendar`

日历（FullCalendar，对齐 INSPINIA calendar.html） - 包在 .card 内；提供 externalEvents 时渲染左侧「可拖拽事件」栏（两栏布局）。 - 配置向 FullCalendar 透传；默认补齐 INSPINIA 同款选项（bootstrap 主题 / 视图切换 / 可编辑 / 可拖入）。

**依赖资源**：`fullcalendar`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `events` | `[]` | — |
| `editable` | `false` | — |
| `locale` | `'zh-cn'` | — |
| `externalEvents` | `[]` | — |
| `addText` | `'新建事件'` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'events'        => [],
            'editable'      => false,
            'locale'        => 'zh-cn',
            'externalEvents' => [],
            'addText'       => '新建事件',
            'options'       => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::calendar([

    'events' => [],

    'editable' => false,

    'locale' => 'zh-cn',

    'externalEvents' => [],

    'addText' => '新建事件',

    'options' => [],

]);
```

## treeView

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\TreeView`

树形视图（jsTree，支持复选框、拖拽、无限层级）

**依赖资源**：`jstree`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `data` | `[]` | — |
| `checkbox` | `false` | — |
| `dnd` | `false` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'data'     => [],
            'checkbox' => false,
            'dnd'      => false,
            'options'  => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::treeView([

    'data' => [],

    'checkbox' => false,

    'dnd' => false,

    'options' => [],

]);
```

## nestable

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Nestable`

可嵌套拖拽排序列表（对齐 INSPINIA misc-nestable.html 的 .nested-sortable） 结构：list-group.nested-sortable（可多层嵌套 list-group.nested-sortable）， 由 xfadmin.js 的 nestable 模块基于 SortableJS（group:'nested'）初始化。

**依赖资源**：`sortablejs`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['items' => [], 'handle' => false, 'input' => null, 'options' => []]
```

### 示例

```php
echo XfAdmin::nestable([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## lightbox

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Lightbox`

图片画廊 / 灯箱（GLightbox，可选 Masonry 瀑布流布局）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `images` | `[]` | — |
| `columns` | `3` | — |
| `masonry` | `false` | — |
| `gallery` | `'xf-gallery'` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'images'  => [],
            'columns' => 3,
            'masonry' => false,
            'gallery' => 'xf-gallery',
            'options' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::lightbox([

    'images' => [],

    'columns' => 3,

    'masonry' => false,

    'gallery' => 'xf-gallery',

    'options' => [],

]);
```

## tour

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Tour`

新手引导（TourGuide JS）

**依赖资源**：`tourguide`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `steps` | `[]` | — |
| `auto` | `false` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'steps'   => [],
            'auto'    => false,
            'options' => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::tour([

    'steps' => [],

    'auto' => false,

    'options' => [],

]);
```

## clipboard
_别名：_ `clipboardButton`

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\ClipboardButton`

复制按钮（clipboard.js）

**依赖资源**：`clipboard`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `null` | — |
| `target` | `null` | — |
| `label` | `'复制'` | — |
| `variant` | `'light'` | — |
| `success` | `'已复制！'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'    => null,
            'target'  => null,
            'label'   => '复制',
            'variant' => 'light',
            'success' => '已复制！',
        ]
```
</details>

### 示例

```php
echo XfAdmin::clipboard([

    'text' => null,

    'target' => null,

    'label' => '复制',

    'variant' => 'light',

    'success' => '已复制！',

]);
```

## sweetAlert

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\SweetAlert`

SweetAlert2 弹窗（确认框 / 成功提示等） // 按钮触发确认框（confirm_url 确认后跳转 / confirm_js 执行自定义 JS）

**依赖资源**：`sweetalert2`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `trigger` | `null` | — |
| `trigger_variant` | `'primary'` | — |
| `title` | `''` | — |
| `text` | `null` | — |
| `icon` | `null` | success | error | warning | info | question |
| `confirm_text` | `'确定'` | — |
| `cancel_text` | `null` | 非空则显示取消按钮 |
| `confirm_url` | `null` | — |
| `confirm_js` | `null` | — |
| `auto` | `false` | — |
| `options` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'trigger'         => null,
            'trigger_variant' => 'primary',
            'title'           => '',
            'text'            => null,
            'icon'            => null,     // success | error | warning | info | question
            'confirm_text'    => '确定',
            'cancel_text'     => null,     // 非空则显示取消按钮
            'confirm_url'     => null,
            'confirm_js'      => null,
            'auto'            => false,
            'options'         => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::sweetAlert([

    'trigger' => null,

    'trigger_variant' => 'primary',

    'title' => '',

    'text' => null,

    'icon' => null,

    'confirm_text' => '确定',

    'cancel_text' => null,

    'confirm_url' => null,

    'confirm_js' => null,

    'auto' => false,

    // … 其余选项见上表 / 默认值（源码）
]);
```

## raw

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Raw`

原样输出（用于把任意 HTML 混入组件树，同时可声明所需插件资源）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `html` | `''` | — |
| `plugins` | `[]` | — |
| `js` | `null` | 追加内联 JS（自动去重可传 js_key） |
| `js_key` | `null` | — |
| `css` | `null` | — |
| `css_key` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'html'    => '',
            'plugins' => [],
            'js'      => null,   // 追加内联 JS（自动去重可传 js_key）
            'js_key'  => null,
            'css'     => null,
            'css_key' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::raw([

    'html' => '',

    'plugins' => [],

    'js' => null,

    'js_key' => null,

    'css' => null,

    'css_key' => null,

]);
```

## tinycon

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Tinycon`

浏览器标签角标通知（misc-live-favicon）—— 在 favicon 上显示未读数量

**依赖资源**：`tinycon`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['count' => 0, 'color' => '#e63757', 'background' => '#3e60d5']
```

### 示例

```php
echo XfAdmin::tinycon([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## idleTimer

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\IdleTimer`

空闲计时器（misc-idle-timer）—— 用户无操作超时后触发回调（如弹出登录框/提示）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `timeout` | `60` | — |
| `warn` | `…` | — |
| `warnText` | `'您已闲置，即将自动锁定'` | — |
| `onIdleUrl` | `''` | — |
| `onIdle` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'timeout'   => 60,
            'warn'      => 0,
            'warnText'  => '您已闲置，即将自动锁定',
            'onIdleUrl' => '',
            'onIdle'    => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::idleTimer([

    'timeout' => 60,

    'warn' => 0,

    'warnText' => '您已闲置，即将自动锁定',

    'onIdleUrl' => '',

    'onIdle' => '',

]);
```

## animate

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Animate`

CSS 动画包装器（misc-animation.html，基于 animate.css，离线可用） 给任意内容套上入场 / 强调动画，支持四种触发方式：   load   页面加载即播放（默认）   hover  鼠标悬浮播放   click  点击播放   scroll 元素滚动进入视口时播放（IntersectionObserver，逐个触发，适合长页面）

**依赖资源**：`animate`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `animation` | `'bounce'` | — |
| `trigger` | `'load'` | — |
| `infinite` | `false` | — |
| `delay` | `null` | — |
| `speed` | `null` | — |
| `repeat` | `null` | — |
| `content` | `''` | — |
| `tag` | `'div'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'animation' => 'bounce',
            'trigger'   => 'load',
            'infinite'  => false,
            'delay'     => null,
            'speed'     => null,
            'repeat'    => null,
            'content'   => '',
            'tag'       => 'div',
        ]
```
</details>

### 示例

```php
echo XfAdmin::animate([

    'animation' => 'bounce',

    'trigger' => 'load',

    'infinite' => false,

    'delay' => null,

    'speed' => null,

    'repeat' => null,

    'content' => '',

    'tag' => 'div',

]);
```

## pdfViewer

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\PdfViewer`

PDF 查看器（misc-pdf-viewer）—— 使用本地 pdf.js 渲染，完全离线

**依赖资源**：`pdfjs`

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `url` | `''` | — |
| `height` | `600` | — |
| `toolbar` | `true` | — |
| `download` | `true` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'url'     => '',
            'height'  => 600,
            'toolbar' => true,
            'download'=> true,
        ]
```
</details>

### 示例

```php
echo XfAdmin::pdfViewer([

    'url' => '',

    'height' => 600,

    'toolbar' => true,

    'download' => true,

]);
```

## textDiff

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\TextDiff`

文本对比（misc-text-diff）—— 基于本地 jsdiff 渲染行内/并排差异

**依赖资源**：`diff`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['old' => '', 'new' => '', 'mode' => 'inline']
```

### 示例

```php
echo XfAdmin::textDiff([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## pinBoard

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\PinBoard`

便利贴看板（pin-board.html）

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['notes' => [], 'addable' => true]
```

### 示例

```php
echo XfAdmin::pinBoard([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## masonry

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\Masonry`

瀑布流布局容器（对齐 INSPINIA misc-masonry.html） 结构：.row + .col-xl-N.col-md-6.masonry-cell，由 xfadmin.js 的 masonry 模块基于 Masonry.js 初始化。 若 Masonry.js 未加载，自动降级为 Bootstrap 网格（不破版）。

**依赖资源**：`masonry`

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
['columns' => 3, 'gap' => 4, 'items' => []]
```

### 示例

```php
echo XfAdmin::masonry([

    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位

]);
```

## videoPlayer

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\VideoPlayer`

视频播放器（plugins-video-player.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `src` | `''` | — |
| `poster` | `''` | — |
| `type` | `'video/mp4'` | — |
| `width` | `'100%'` | — |
| `autoplay` | `false` | — |
| `controls` | `true` | — |
| `loop` | `false` | — |
| `muted` | `false` | — |
| `title` | `''` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'src' => '',
            'poster' => '',
            'type' => 'video/mp4',
            'width' => '100%',
            'autoplay' => false,
            'controls' => true,
            'loop' => false,
            'muted' => false,
            'title' => '',
        ]
```
</details>

### 示例

```php
echo XfAdmin::videoPlayer([

    'src' => '',

    'poster' => '',

    'type' => 'video/mp4',

    'width' => '100%',

    'autoplay' => false,

    'controls' => true,

    'loop' => false,

    'muted' => false,

    'title' => '',

]);
```

## i18n

> 分类：**杂项** · 类：`zxf\XfAdmin\Components\Misc\I18n`

国际化 i18n 展示（plugins-i18.html）

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `currentLocale` | `'zh-CN'` | — |
| `locales` | `[]` | — |
| `demoKeys` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'currentLocale' => 'zh-CN',
            'locales' => [],
            'demoKeys' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::i18n([

    'currentLocale' => 'zh-CN',

    'locales' => [],

    'demoKeys' => null,

]);
```

## twoFactorInput

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\TwoFactorInput`

两步验证 / OTP 验证码输入框 6 格独立输入，自动跳格、退格回退、粘贴自动填充， 复刻 inspinia auth-two-factor.html 的验证码交互。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `length` | `6` | — |
| `name` | `'code'` | — |
| `value` | `''` | — |
| `mask` | `null` | — |
| `autofocus` | `true` | — |
| `disabled` | `false` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'length'    => 6,
            'name'      => 'code',
            'value'     => '',
            'mask'      => null,
            'autofocus' => true,
            'disabled'  => false,
        ]
```
</details>

### 示例

```php
echo XfAdmin::twoFactorInput([

    'length' => 6,

    'name' => 'code',

    'value' => '',

    'mask' => null,

    'autofocus' => true,

    'disabled' => false,

]);
```

## quantityStepper

> 分类：**表单** · 类：`zxf\XfAdmin\Components\Form\QuantityStepper`

数量步进器 − 数字 + 按钮组，含 min / max / step，复刻电商购物车数量控件。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `name` | `'qty'` | — |
| `value` | `1` | — |
| `min` | `1` | — |
| `max` | `99` | — |
| `step` | `1` | — |
| `size` | `'md'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'name'  => 'qty',
            'value' => 1,
            'min'   => 1,
            'max'   => 99,
            'step'  => 1,
            'size'  => 'md',
        ]
```
</details>

### 示例

```php
echo XfAdmin::quantityStepper([

    'name' => 'qty',

    'value' => 1,

    'min' => 1,

    'max' => 99,

    'step' => 1,

    'size' => 'md',

]);
```

## statMiniSparkline

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\StatMiniSparkline`

迷你 sparkline 统计卡 大数字 + 趋势 sparkline（内联 SVG）+ 同比涨跌幅小标， 复刻 inspinia dashboard-analytics 的迷你统计卡。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `label` | `''` | — |
| `value` | `''` | — |
| `delta` | `null` | — |
| `series` | `[]` | — |
| `variant` | `'primary'` | — |
| `icon` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'label'   => '',
            'value'   => '',
            'delta'   => null,
            'series'  => [],
            'variant' => 'primary',
            'icon'    => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::statMiniSparkline([

    'label' => '',

    'value' => '',

    'delta' => null,

    'series' => [],

    'variant' => 'primary',

    'icon' => null,

]);
```

## cartSummary

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\CartSummary`

购物车结算摘要卡 小计 / 运费 / 优惠 / 总计 + 优惠码输入，复刻电商购物车结算侧栏。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `subtotal` | `…` | — |
| `shipping` | `…` | — |
| `discount` | `…` | — |
| `currency` | `'￥'` | — |
| `promo` | `true` | — |
| `button` | `['text' => '去结算', 'variant' => 'primary']` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'subtotal' => 0,
            'shipping' => 0,
            'discount' => 0,
            'currency' => '￥',
            'promo'    => true,
            'button'   => ['text' => '去结算', 'variant' => 'primary'],
        ]
```
</details>

### 示例

```php
echo XfAdmin::cartSummary([

    'subtotal' => 0,

    'shipping' => 0,

    'discount' => 0,

    'currency' => '￥',

    'promo' => true,

    'button' => ['text' => '去结算', 'variant' => 'primary'],

]);
```

## chatMessageBubble

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ChatMessageBubble`

聊天气泡 左/右对齐、已读状态、时间、头像、附件预览， 复刻 inspinia apps-chat.html 的会话气泡。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `side` | `'in'` | — |
| `avatar` | `null` | — |
| `text` | `''` | — |
| `time` | `''` | — |
| `read` | `false` | — |
| `attach` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'side'   => 'in',
            'avatar' => null,
            'text'   => '',
            'time'   => '',
            'read'   => false,
            'attach' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::chatMessageBubble([

    'side' => 'in',

    'avatar' => null,

    'text' => '',

    'time' => '',

    'read' => false,

    'attach' => null,

]);
```

## chatConversationPanel

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ChatConversationPanel`

会话面板框架 左侧联系人列表 + 右侧消息区 + 底部输入工具栏， 复刻 inspinia apps-chat.html 的会话面板。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `contacts` | `[]` | — |
| `messages` | `[]` | — |
| `title` | `'会话'` | — |
| `me` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'contacts' => [],
            'messages' => [],
            'title'    => '会话',
            'me'       => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::chatConversationPanel([

    'contacts' => [],

    'messages' => [],

    'title' => '会话',

    'me' => null,

]);
```

## dataTableToolbar

> 分类：**表格** · 类：`zxf\XfAdmin\Components\Table\DataTableToolbar`

列表工具条 搜索 + 列筛选 + 每页条数 + 批量操作按钮 + 视图切换， 复刻文件管理器 / 订单等页面的列表工具栏。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `search` | `true` | — |
| `searchPlaceholder` | `'搜索...'` | — |
| `filters` | `[]` | — |
| `pageSize` | `[10, 20, 50]` | — |
| `actions` | `[]` | — |
| `views` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'search'           => true,
            'searchPlaceholder' => '搜索...',
            'filters'          => [],
            'pageSize'         => [10, 20, 50],
            'actions'          => [],
            'views'            => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::dataTableToolbar([

    'search' => true,

    'searchPlaceholder' => '搜索...',

    'filters' => [],

    'pageSize' => [10, 20, 50],

    'actions' => [],

    'views' => [],

]);
```

## orderTrackingTimeline

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\OrderTrackingTimeline`

订单物流进度时间线 节点（已下单/已发货/运输中/已签收）+ 当前节点高亮， 复刻 inspinia apps-ecommerce-order-details.html 的物流进度。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'steps' => [],
        ]
```

### 示例

```php
echo XfAdmin::orderTrackingTimeline([

    'steps' => [],

]);
```

## featureComparisonTable

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\FeatureComparisonTable`

套餐功能对比矩阵表 行 × 列（计划）交叉 ✓ / ✗ / 文本，复刻价格页的"功能对比表"。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `plans` | `[]` | — |
| `featured` | `null` | — |
| `rows` | `[]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'plans'    => [],
            'featured' => null,
            'rows'     => [],
        ]
```
</details>

### 示例

```php
echo XfAdmin::featureComparisonTable([

    'plans' => [],

    'featured' => null,

    'rows' => [],

]);
```

## filterSidebar

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\FilterSidebar`

电商筛选侧栏 分类树 / 价格区间 / 属性多选 / 品牌，含可折叠分组， 复刻 inspinia apps-ecommerce-products.html 的筛选侧栏。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'groups' => [],
            'button' => ['text' => '应用筛选', 'variant' => 'primary'],
        ]
```

### 示例

```php
echo XfAdmin::filterSidebar([

    'groups' => [],

    'button' => ['text' => '应用筛选', 'variant' => 'primary'],

]);
```

## accountSettingsPanel

> 分类：**布局 / 页面** · 类：`zxf\XfAdmin\Components\Layout\AccountSettingsPanel`

账户设置双栏面板 左侧 sidebar 导航（tab）+ 右侧内容区，点击切换， 复刻 inspinia pages-account-settings.html 的设置布局。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `title` | `'账户设置'` | — |
| `tabs` | `[]` | — |
| `active` | `null` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'title'  => '账户设置',
            'tabs'   => [],
            'active' => null,
        ]
```
</details>

### 示例

```php
echo XfAdmin::accountSettingsPanel([

    'title' => '账户设置',

    'tabs' => [],

    'active' => null,

]);
```

## searchResultsRich

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\SearchResultsRich`

全局搜索结果页 分类结果分组 + 关键词高亮 + 分页/空态，复刻 inspinia pages-search-results.html。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'query'  => '',
            'groups' => [],
        ]
```

### 示例

```php
echo XfAdmin::searchResultsRich([

    'query' => '',

    'groups' => [],

]);
```

## invoicePrintButton

> 分类：**UI 基础** · 类：`zxf\XfAdmin\Components\UI\InvoicePrintButton`

发票打印 / 下载按钮 触发 window.print()（配合打印样式仅打印发票区域），复刻 inspinia apps-invoice 的打印按钮。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `text` | `'打印 / 下载 PDF'` | — |
| `variant` | `'primary'` | — |
| `target` | `null` | — |
| `icon` | `'ti ti-printer'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'text'   => '打印 / 下载 PDF',
            'variant' => 'primary',
            'target' => null,
            'icon'   => 'ti ti-printer',
        ]
```
</details>

### 示例

```php
echo XfAdmin::invoicePrintButton([

    'text' => '打印 / 下载 PDF',

    'variant' => 'primary',

    'target' => null,

    'icon' => 'ti ti-printer',

]);
```

## socialFeed

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\SocialFeed`

社交动态流（apps-social-feed.html） 头像 + 用户名 + 时间 + 文本内容 + 配图 + 点赞/评论/转发 操作条。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'posts' => [],
            'title' => '团队动态',
        ]
```

### 示例

```php
echo XfAdmin::socialFeed([

    'posts' => [],

    'title' => '团队动态',

]);
```

## faqAccordion

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\FaqAccordion`

FAQ 折叠列表（pages-faq.html） 基于 Bootstrap 原生 accordion 的问答列表。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'items' => [],
            'title' => '常见问题',
        ]
```

### 示例

```php
echo XfAdmin::faqAccordion([

    'items' => [],

    'title' => '常见问题',

]);
```

## contactList

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\ContactList`

联系人列表（apps-users-contacts.html） 头像 + 名称 + 角色/职位 + 状态点 + 操作按钮。

**依赖资源**：无

_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_

### 默认值（源码）

```php
[
            'contacts' => [],
            'title'    => '团队成员',
        ]
```

### 示例

```php
echo XfAdmin::contactList([

    'contacts' => [],

    'title' => '团队成员',

]);
```

## userProfile

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\UserProfile`

用户资料页（pages-profile.html） 封面 + 头像 + 基本信息 + 统计 + 操作按钮。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `avatar` | `''` | — |
| `cover` | `''` | — |
| `name` | `'匿名用户'` | — |
| `title` | `''` | — |
| `bio` | `''` | — |
| `stats` | `[]` | — |
| `actions` | `['message' => true, 'follow' => true]` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'avatar'  => '',
            'cover'   => '',
            'name'    => '匿名用户',
            'title'   => '',
            'bio'     => '',
            'stats'   => [],
            'actions' => ['message' => true, 'follow' => true],
        ]
```
</details>

### 示例

```php
echo XfAdmin::userProfile([

    'avatar' => '',

    'cover' => '',

    'name' => '匿名用户',

    'title' => '',

    'bio' => '',

    'stats' => [],

    'actions' => ['message' => true, 'follow' => true],

]);
```

## invoiceView

> 分类：**数据 / 业务** · 类：`zxf\XfAdmin\Components\Data\InvoiceView`

发票详情页（apps-invoice-details.html） 发票头（买家/卖家/发票号/日期）+ 明细表 + 合计。

**依赖资源**：无

### 选项（defaults）

| 键 | 默认值 | 说明 |
|----|--------|------|
| `invoice_no` | `''` | — |
| `issued_at` | `''` | — |
| `due_at` | `''` | — |
| `from` | `[]` | — |
| `to` | `[]` | — |
| `items` | `[]` | — |
| `tax_rate` | `…` | — |
| `currency` | `'¥'` | — |

<details><summary>查看 defaults() 源码字面量</summary>

```php
[
            'invoice_no' => '',
            'issued_at'  => '',
            'due_at'     => '',
            'from'       => [],
            'to'         => [],
            'items'      => [],
            'tax_rate'   => 0.06,
            'currency'   => '¥',
        ]
```
</details>

### 示例

```php
echo XfAdmin::invoiceView([

    'invoice_no' => '',

    'issued_at' => '',

    'due_at' => '',

    'from' => [],

    'to' => [],

    'items' => [],

    'tax_rate' => …, // 见说明
    'currency' => '¥',

]);
```

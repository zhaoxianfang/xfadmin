# 组件总览

> 本文件由 `php tools/gen_component_docs.php` 自动生成。
> 组件别名共 **228** 个（对应 **216** 个组件类，部分类别名重复指向同一类）。

## 分类统计

| 分类文档 | 组件数 |
|---|---|
| [布局 · 导航 · 栅格](01-layout.md) | 29 |
| [UI 基础组件](02-ui.md) | 55 |
| [表单组件](03-form.md) | 23 |
| [表格组件](04-table.md) | 4 |
| [图表与地图](05-chart.md) | 7 |
| [业务组件（一）电商 · 商品 · 订单 · 发票](06-data-ecommerce.md) | 31 |
| [业务组件（二）内容 · 社区 · 文件](07-data-content.md) | 17 |
| [业务组件（三）用户 · 组织 · 项目协作](08-data-people.md) | 23 |
| [业务组件（四）沟通 · 邮件](09-data-comm.md) | 8 |
| [业务组件（五）仪表盘 · 模块 · 通用](10-data-dashboard.md) | 13 |
| [杂项与交互增强](11-misc.md) | 18 |

## 命名约定

- **别名大小写不敏感**：`XfAdmin::datatable()` 与 `XfAdmin::dataTable()` 等价；
- **同类别名**：`topNav`/`topnav`、`dateRange`/`dateRangePicker`、`clipboard`/`clipboardButton` 指向同一类；
- **PHP 保留字规避**：`switch` → 类名 `Toggle`（别名 `switch`）；`empty` → 类名 `EmptyState`（别名 `empty`）；
- ⚠️ `lockScreen` 注册表中被 `LockScreen::class` 覆盖，得到的是独立整页组件而非 `AuthPage`；
  如需 AuthPage 的锁屏语义请用 `XfAdmin::authPage(['type' => 'lock-screen'])`。

---

## [布局 · 导航 · 栅格](01-layout.md)

整页骨架、认证页、错误页、侧边栏、顶栏、水平导航、页脚、定制面板、面包屑与栅格系统。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`page`](01-layout.md#page) | 整页布局容器：组装 head/body/scripts，所有后台页面入口 | `Layout\Page` | — |
| [`sidenav`](01-layout.md#sidenav) | 左侧导航菜单 | `Layout\Sidenav` | — |
| [`topbar`](01-layout.md#topbar) | 顶部导航栏 | `Layout\Topbar` | — |
| [`topNav`](01-layout.md#topnav) | 水平顶部导航 | `Layout\TopNav` | — |
| [`pageTitle`](01-layout.md#pagetitle) | 页面标题区 | `Layout\PageTitle` | — |
| [`footer`](01-layout.md#footer) | 页面底部版权/链接区 | `Layout\Footer` | — |
| [`customizer`](01-layout.md#customizer) | 右侧主题定制面板 | `Layout\Customizer` | — |
| [`authPage`](01-layout.md#authpage) | 认证页统一入口 | `Layout\AuthPage` | — |
| [`lockScreen`](01-layout.md#lockscreen) | 锁屏页 | `Layout\LockScreen` | — |
| [`errorPage`](01-layout.md#errorpage) | 错误页 | `Layout\ErrorPage` | — |
| [`comingSoon`](01-layout.md#comingsoon) | 即将上线页 | `Layout\ComingSoon` | — |
| [`maintenance`](01-layout.md#maintenance) | 系统维护页 | `Layout\Maintenance` | — |
| [`emptyState`](01-layout.md#emptystate) | 空状态页 | `Layout\EmptyState` | — |
| [`landing`](01-layout.md#landing) | 落地页/营销首页 | `Layout\Landing` | — |
| [`accountSettingsPanel`](01-layout.md#accountsettingspanel) | 账户设置面板 | `Layout\AccountSettingsPanel` | — |
| [`menu`](01-layout.md#menu) | 菜单数据组件 | `Navigation\Menu` | — |
| [`row`](01-layout.md#row) | 栅格行容器 | `Grid\Row` | — |
| [`col`](01-layout.md#col) | 栅格列 | `Grid\Col` | — |
| [`diyLayoutPage`](01-layout.md#diylayoutpage) | DIY 可视化布局器 | `Layout\DiyLayoutPage` | `diy` |

---

## [UI 基础组件](02-ui.md)

卡片、按钮、徽章、模态框、选项卡、进度、头像、时间线、轮播等通用界面元素，共 55 个。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`card`](02-ui.md#card) | 卡片容器 | `UI\Card` | — |
| [`statCard`](02-ui.md#statcard) | 指标卡 | `UI\StatCard` | — |
| [`alert`](02-ui.md#alert) | 警告提示条 | `UI\Alert` | — |
| [`badge`](02-ui.md#badge) | 徽章标签 | `UI\Badge` | — |
| [`button`](02-ui.md#button) | 按钮 | `UI\Button` | `ladda` |
| [`dropdown`](02-ui.md#dropdown) | 下拉菜单 | `UI\Dropdown` | — |
| [`modal`](02-ui.md#modal) | 模态框 | `UI\Modal` | — |
| [`offcanvas`](02-ui.md#offcanvas) | 抽屉面板 | `UI\Offcanvas` | — |
| [`tabs`](02-ui.md#tabs) | 选项卡 | `UI\Tabs` | — |
| [`accordion`](02-ui.md#accordion) | 手风琴折叠面板 | `UI\Accordion` | — |
| [`progress`](02-ui.md#progress) | 进度条 | `UI\Progress` | — |
| [`spinner`](02-ui.md#spinner) | 加载旋转指示器 | `UI\Spinner` | `spinkit` |
| [`pagination`](02-ui.md#pagination) | 分页器 | `UI\Pagination` | — |
| [`listGroup`](02-ui.md#listgroup) | 列表组 | `UI\ListGroup` | — |
| [`avatar`](02-ui.md#avatar) | 头像 | `UI\Avatar` | — |
| [`avatarGroup`](02-ui.md#avatargroup) | 头像组 | `UI\AvatarGroup` | — |
| [`icon`](02-ui.md#icon) | 图标 | `UI\Icon` | `lucide` |
| [`iconSet`](02-ui.md#iconset) | 图标集展示 | `UI\IconSet` | — |
| [`toast`](02-ui.md#toast) | 轻提示 | `UI\Toast` | — |
| [`timeline`](02-ui.md#timeline) | 时间线 | `UI\Timeline` | — |
| [`carousel`](02-ui.md#carousel) | 轮播图 | `UI\Carousel` | — |
| [`breadcrumb`](02-ui.md#breadcrumb) | 面包屑导航 | `UI\Breadcrumb` | — |
| [`tooltip`](02-ui.md#tooltip) | 文字提示气泡 | `UI\Tooltip` | — |
| [`popover`](02-ui.md#popover) | 弹出框 | `UI\Popover` | — |
| [`placeholder`](02-ui.md#placeholder) | 占位符块 | `UI\Placeholder` | — |
| [`collapse`](02-ui.md#collapse) | 折叠面板 | `UI\Collapse` | — |
| [`scrollspy`](02-ui.md#scrollspy) | 滚动监听 | `UI\Scrollspy` | — |
| [`ratio`](02-ui.md#ratio) | 固定宽高比容器 | `UI\Ratio` | — |
| [`rating`](02-ui.md#rating) | 评分 | `UI\Rating` | — |
| [`ribbon`](02-ui.md#ribbon) | 缎带角标 | `UI\Ribbon` | — |
| [`chip`](02-ui.md#chip) | 筹码标签 | `UI\Chip` | — |
| [`stepper`](02-ui.md#stepper) | 步骤条 | `UI\Stepper` | — |
| [`descriptionList`](02-ui.md#descriptionlist) | 描述列表 | `UI\DescriptionList` | — |
| [`loadingButton`](02-ui.md#loadingbutton) | 加载按钮 | `UI\LoadingButton` | `ladda` |
| [`divider`](02-ui.md#divider) | 分割线 | `UI\Divider` | — |
| [`kbd`](02-ui.md#kbd) | 键盘按键标记 | `UI\Kbd` | — |
| [`media`](02-ui.md#media) | 媒体对象 | `UI\Media` | — |
| [`skeleton`](02-ui.md#skeleton) | 骨架屏占位 | `UI\Skeleton` | — |
| [`switch`](02-ui.md#switch) | 开关切换 | `UI\Toggle` | — |
| [`codeBlock`](02-ui.md#codeblock) | 代码块 | `UI\CodeBlock` | — |
| [`empty`](02-ui.md#empty) | 空状态组件 | `UI\EmptyState` | — |
| [`toolbar`](02-ui.md#toolbar) | 工具栏容器 | `UI\Toolbar` | — |
| [`searchBox`](02-ui.md#searchbox) | 搜索框 | `UI\SearchBox` | — |
| [`colorPalette`](02-ui.md#colorpalette) | 配色方案展示 | `UI\ColorPalette` | — |
| [`typography`](02-ui.md#typography) | 排版展示 | `UI\Typography` | — |
| [`utilities`](02-ui.md#utilities) | Bootstrap 工具类展示 | `UI\Utilities` | — |
| [`videoEmbed`](02-ui.md#videoembed) | 视频嵌入 | `UI\VideoEmbed` | — |
| [`commandPalette`](02-ui.md#commandpalette) | 命令面板 | `UI\CommandPalette` | — |
| [`notificationCenter`](02-ui.md#notificationcenter) | 通知中心 | `UI\NotificationCenter` | — |
| [`dropzoneUpload`](02-ui.md#dropzoneupload) | 拖拽上传区 | `UI\DropzoneUpload` | — |
| [`backToTop`](02-ui.md#backtotop) | 回到顶部悬浮按钮 | `UI\BackToTop` | — |
| [`callout`](02-ui.md#callout) | 强调提示块 | `UI\Callout` | — |
| [`countdown`](02-ui.md#countdown) | 倒计时 | `UI\Countdown` | — |
| [`countUp`](02-ui.md#countup) | 数字滚动递增动画 | `UI\CountUp` | — |
| [`invoicePrintButton`](02-ui.md#invoiceprintbutton) | 发票打印 / 下载按钮 触发 window.print() | `UI\InvoicePrintButton` | — |

---

## [表单组件](03-form.md)

表单容器与全部输入控件（输入/选择/复选/滑块/日期/编辑器/上传/颜色/标签/掩码/验证码/向导等）。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`form`](03-form.md#form) | 表单容器 | `Form\Form` | — |
| [`input`](03-form.md#input) | 文本输入框 | `Form\Input` | `inputmask` `tagify` |
| [`textarea`](03-form.md#textarea) | 多行文本域 | `Form\Textarea` | — |
| [`select`](03-form.md#select) | 下拉选择框 | `Form\Select` | `choices` `select2` |
| [`check`](03-form.md#check) | 多选/单选框 | `Form\Check` | — |
| [`slider`](03-form.md#slider) | 滑块/范围选择器 | `Form\Slider` | `nouislider` |
| [`dateRange`](03-form.md#daterange) | 日期范围选择器 | `Form\DateRangePicker` | `daterangepicker` |
| [`datePicker`](03-form.md#datepicker) | 单日期/日期时间选择器 | `Form\DatePicker` | `daterangepicker` |
| [`editor`](03-form.md#editor) | 富文本编辑器 | `Form\Editor` | `quill` `summernote` |
| [`upload`](03-form.md#upload) | 文件上传 | `Form\Upload` | `dropzone` `filepond` |
| [`colorPicker`](03-form.md#colorpicker) | 颜色选择器 | `Form\ColorPicker` | `pickr` |
| [`tags`](03-form.md#tags) | 标签输入 | `Form\Tags` | `tagify` |
| [`maskedInput`](03-form.md#maskedinput) | 输入掩码 | `Form\MaskedInput` | `inputmask` |
| [`wizard`](03-form.md#wizard) | 向导 | `Form\Wizard` | — |
| [`passwordStrength`](03-form.md#passwordstrength) | 密码强度计 | `Form\PasswordStrength` | — |
| [`captcha`](03-form.md#captcha) | 验证码 | `Form\Captcha` | — |
| [`twoFactorInput`](03-form.md#twofactorinput) | 两步验证 / OTP 验证码输入框 6 格独立输入，自动跳格、退格回退、粘贴自动填充， 复刻 inspinia auth-two-factor.html 的验证码交互 | `Form\TwoFactorInput` | — |
| [`quantityStepper`](03-form.md#quantitystepper) | 数量步进器 − 数字 + 按钮组，含 min / max / step，复刻电商购物车数量控件 | `Form\QuantityStepper` | — |
| [`formElements`](03-form.md#formelements) | 表单元素集合 | `Form\FormElements` | — |
| [`formLayout`](03-form.md#formlayout) | 表单布局 | `Form\FormLayout` | — |
| [`formOtherPlugin`](03-form.md#formotherplugin) | 其它表单插件 | `Form\FormOtherPlugin` | — |
| [`formValidation`](03-form.md#formvalidation) | 表单校验 | `Form\FormValidation` | — |

---

## [表格组件](04-table.md)

静态表格、DataTables 数据表格（服务端/客户端）、自定义表格与列表工具条。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`table`](04-table.md#table) | 静态表格 | `Table\Table` | — |
| [`dataTable`](04-table.md#datatable) | 数据表格 | `Table\DataTable` | `datatables` `datatables-pdf` `select2` `qrcode` |
| [`tablesCustom`](04-table.md#tablescustom) | 自定义表格 | `Table\TablesCustom` | — |
| [`dataTableToolbar`](04-table.md#datatabletoolbar) | 列表工具条 搜索 + 列筛选 + 每页条数 + 批量操作按钮 + 视图切换， 复刻文件管理器 / 订单等页面的列表工具栏 | `Table\DataTableToolbar` | — |

---

## [图表与地图](05-chart.md)

ApexCharts 通用图表 / 树图 / 桑基图、ECharts、矢量地图、Leaflet 与 Google 地图。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`apexChart`](05-chart.md#apexchart) | ApexCharts 图表 | `Chart\ApexChart` | `apexcharts` |
| [`apexTree`](05-chart.md#apextree) | ApexCharts 树形图 | `Chart\ApexTree` | `apextree` |
| [`apexSankey`](05-chart.md#apexsankey) | ApexCharts 桑基图 | `Chart\ApexSankey` | `apexsankey` |
| [`echart`](05-chart.md#echart) | ECharts 图表 | `Chart\EChart` | `echarts` |
| [`vectorMap`](05-chart.md#vectormap) | 矢量地图 | `Chart\VectorMap` | `jsvectormap-world` |
| [`leafletMap`](05-chart.md#leafletmap) | Leaflet 地图 | `Chart\LeafletMap` | `leaflet` |
| [`googleMap`](05-chart.md#googlemap) | Google 地图 | `Chart\GoogleMap` | — |

---

## [业务组件（一）电商 · 商品 · 订单 · 发票](06-data-ecommerce.md)

商品、分类、购物车、结算、订单、退款、库存、销售、发票与电商仪表盘。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`productCard`](06-data-ecommerce.md#productcard) | 商品卡 | `Data\ProductCard` | — |
| [`productsGrid`](06-data-ecommerce.md#productsgrid) | 商品网格 | `Data\ProductsGrid` | — |
| [`productCategories`](06-data-ecommerce.md#productcategories) | 商品分类管理 | `Data\ProductCategories` | — |
| [`productAdd`](06-data-ecommerce.md#productadd) | 商品添加表单 | `Data\ProductAdd` | — |
| [`productDetails`](06-data-ecommerce.md#productdetails) | 商品详情 | `Data\ProductDetails` | — |
| [`productViews`](06-data-ecommerce.md#productviews) | 商品浏览统计 | `Data\ProductViews` | — |
| [`shoppingCart`](06-data-ecommerce.md#shoppingcart) | 购物车 | `Data\ShoppingCart` | — |
| [`cartSummary`](06-data-ecommerce.md#cartsummary) | 购物车摘要 | `Data\CartSummary` | — |
| [`checkout`](06-data-ecommerce.md#checkout) | 结算页 | `Data\Checkout` | — |
| [`orders`](06-data-ecommerce.md#orders) | 订单列表 | `Data\Orders` | — |
| [`orderDetails`](06-data-ecommerce.md#orderdetails) | 订单详情 | `Data\OrderDetails` | — |
| [`orderTrackingTimeline`](06-data-ecommerce.md#ordertrackingtimeline) | 订单追踪时间线 | `Data\OrderTrackingTimeline` | — |
| [`purchasedOrders`](06-data-ecommerce.md#purchasedorders) | 已购订单 | `Data\PurchasedOrders` | — |
| [`refunds`](06-data-ecommerce.md#refunds) | 退款管理 | `Data\Refunds` | — |
| [`sales`](06-data-ecommerce.md#sales) | 销售数据 | `Data\Sales` | — |
| [`customers`](06-data-ecommerce.md#customers) | 客户列表 | `Data\Customers` | — |
| [`sellers`](06-data-ecommerce.md#sellers) | 卖家列表 | `Data\Sellers` | — |
| [`sellerDetails`](06-data-ecommerce.md#sellerdetails) | 卖家详情 | `Data\SellerDetails` | — |
| [`reviewList`](06-data-ecommerce.md#reviewlist) | 评价列表 | `Data\ReviewList` | — |
| [`attributes`](06-data-ecommerce.md#attributes) | 商品属性管理 | `Data\Attributes` | — |
| [`ecommerceSettings`](06-data-ecommerce.md#ecommercesettings) | 电商设置 | `Data\EcommerceSettings` | — |
| [`ecommerceDashboard`](06-data-ecommerce.md#ecommercedashboard) | 电商仪表盘 | `Data\EcommerceDashboard` | — |
| [`marketplace`](06-data-ecommerce.md#marketplace) | 应用市场 | `Data\Marketplace` | — |
| [`warehouse`](06-data-ecommerce.md#warehouse) | 仓库管理 | `Data\Warehouse` | — |
| [`invoiceCreate`](06-data-ecommerce.md#invoicecreate) | 发票创建表单 | `Data\InvoiceCreate` | — |
| [`invoiceDetail`](06-data-ecommerce.md#invoicedetail) | 发票详情整页 | `Data\InvoiceDetail` | — |
| [`invoiceList`](06-data-ecommerce.md#invoicelist) | 发票列表 | `Data\InvoiceList` | — |
| [`invoiceTable`](06-data-ecommerce.md#invoicetable) | 发票明细表格 | `Data\InvoiceTable` | — |
| [`invoiceView`](06-data-ecommerce.md#invoiceview) | 发票查看 | `Data\InvoiceView` | — |
| [`filterSidebar`](06-data-ecommerce.md#filtersidebar) | 筛选侧栏 | `Data\FilterSidebar` | — |
| [`featureComparisonTable`](06-data-ecommerce.md#featurecomparisontable) | 功能对比表 | `Data\FeatureComparisonTable` | — |

---

## [业务组件（二）内容 · 社区 · 文件](07-data-content.md)

文章、博客、评论、论坛、FAQ、画廊、文件管理、搜索结果、条款与隐私政策。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`article`](07-data-content.md#article) | 文章详情 | `Data\Article` | — |
| [`blogArticle`](07-data-content.md#blogarticle) | 博客文章详情 | `Data\BlogArticle` | — |
| [`blogList`](07-data-content.md#bloglist) | 博客列表 | `Data\BlogList` | — |
| [`commentThread`](07-data-content.md#commentthread) | 评论线程 | `Data\CommentThread` | — |
| [`faq`](07-data-content.md#faq) | 常见问题 | `Data\Faq` | — |
| [`faqAccordion`](07-data-content.md#faqaccordion) | FAQ 手风琴 | `Data\FaqAccordion` | — |
| [`forumThread`](07-data-content.md#forumthread) | 论坛帖子 | `Data\ForumThread` | — |
| [`gallery`](07-data-content.md#gallery) | 图片画廊 | `Data\Gallery` | `masonry` `glightbox` |
| [`terms`](07-data-content.md#terms) | 服务条款页 | `Data\Terms` | — |
| [`privacyPolicy`](07-data-content.md#privacypolicy) | 隐私政策页 | `Data\PrivacyPolicy` | — |
| [`sitemap`](07-data-content.md#sitemap) | 站点地图 | `Data\Sitemap` | — |
| [`socialFeed`](07-data-content.md#socialfeed) | 社交动态流 | `Data\SocialFeed` | — |
| [`fileManager`](07-data-content.md#filemanager) | 文件管理器 | `Data\FileManager` | — |
| [`searchResults`](07-data-content.md#searchresults) | 搜索结果列表 | `Data\SearchResults` | — |
| [`searchResultsRich`](07-data-content.md#searchresultsrich) | 富搜索结果 | `Data\SearchResultsRich` | — |
| [`pricingCard`](07-data-content.md#pricingcard) | 价格方案卡 | `Data\PricingCard` | — |
| [`testimonial`](07-data-content.md#testimonial) | 用户证言 | `Data\Testimonial` | — |

---

## [业务组件（三）用户 · 组织 · 项目协作](08-data-people.md)

客户/公司/联系人、角色权限、团队成员、项目与任务、看板、商机、用户档案与账户设置。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`clients`](08-data-people.md#clients) | 客户/客户端列表 | `Data\Clients` | — |
| [`companies`](08-data-people.md#companies) | 公司列表 | `Data\Companies` | — |
| [`companyCard`](08-data-people.md#companycard) | 公司卡 | `Data\CompanyCard` | — |
| [`contactCard`](08-data-people.md#contactcard) | 联系人卡 | `Data\ContactCard` | — |
| [`contactList`](08-data-people.md#contactlist) | 联系人列表 | `Data\ContactList` | — |
| [`roles`](08-data-people.md#roles) | 角色管理 | `Data\Roles` | — |
| [`permissionMatrix`](08-data-people.md#permissionmatrix) | 权限矩阵 | `Data\PermissionMatrix` | — |
| [`teamMember`](08-data-people.md#teammember) | 团队成员卡 | `Data\TeamMember` | — |
| [`projects`](08-data-people.md#projects) | 项目列表 | `Data\Projects` | — |
| [`projectDetails`](08-data-people.md#projectdetails) | 项目详情 | `Data\ProjectDetails` | — |
| [`projectActivity`](08-data-people.md#projectactivity) | 项目活动流 | `Data\ProjectActivity` | — |
| [`projectTeamBoard`](08-data-people.md#projectteamboard) | 项目团队看板 | `Data\ProjectTeamBoard` | — |
| [`taskList`](08-data-people.md#tasklist) | 任务清单 | `Data\TaskList` | — |
| [`todoList`](08-data-people.md#todolist) | 待办列表 | `Data\TodoList` | — |
| [`issueTracker`](08-data-people.md#issuetracker) | 问题追踪 | `Data\IssueTracker` | — |
| [`kanban`](08-data-people.md#kanban) | 看板 | `Data\Kanban` | `sortablejs` `simplebar` |
| [`deals`](08-data-people.md#deals) | 交易/商机列表 | `Data\Deals` | — |
| [`userProfile`](08-data-people.md#userprofile) | 用户资料卡 | `Data\UserProfile` | — |
| [`profileHeader`](08-data-people.md#profileheader) | 个人资料头部 | `Data\ProfileHeader` | — |
| [`profilePage`](08-data-people.md#profilepage) | 个人资料页 | `Data\ProfilePage` | — |
| [`accountSettings`](08-data-people.md#accountsettings) | 账户设置整页 | `Data\AccountSettings` | — |
| [`voteList`](08-data-people.md#votelist) | 投票列表 | `Data\VoteList` | — |
| [`activityFeed`](08-data-people.md#activityfeed) | 活动动态流 | `Data\ActivityFeed` | — |

---

## [业务组件（四）沟通 · 邮件](09-data-comm.md)

聊天应用与会话面板、邮件应用、邮件撰写、邮件列表与 Outlook 风格客户端。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`chatApp`](09-data-comm.md#chatapp) | 聊天应用整页 | `Data\ChatApp` | — |
| [`chatBox`](09-data-comm.md#chatbox) | 聊天框 | `Data\ChatBox` | — |
| [`chatConversationPanel`](09-data-comm.md#chatconversationpanel) | 聊天会话面板 | `Data\ChatConversationPanel` | — |
| [`chatMessageBubble`](09-data-comm.md#chatmessagebubble) | 聊天气泡 | `Data\ChatMessageBubble` | — |
| [`emailApp`](09-data-comm.md#emailapp) | 邮件应用整页 | `Data\EmailApp` | — |
| [`emailCompose`](09-data-comm.md#emailcompose) | 邮件撰写 | `Data\EmailCompose` | `quill` |
| [`mailList`](09-data-comm.md#maillist) | 邮件列表 | `Data\MailList` | — |
| [`outlook`](09-data-comm.md#outlook) | Outlook 风格邮件客户端 | `Data\Outlook` | — |

---

## [业务组件（五）仪表盘 · 模块 · 通用](10-data-dashboard.md)

仪表盘网格、小部件、指标卡、分析报表、设置中心、模块导航/网格、API 密钥、应用管理与导入导出。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`widget`](10-data-dashboard.md#widget) | 通用小部件卡 | `Data\Widget` | — |
| [`metricCard`](10-data-dashboard.md#metriccard) | 迷你指标卡 | `Data\MetricCard` | `echarts` |
| [`statMiniSparkline`](10-data-dashboard.md#statminisparkline) | 迷你指标+火花线 | `Data\StatMiniSparkline` | — |
| [`widgetsDashboard`](10-data-dashboard.md#widgetsdashboard) | 小部件仪表盘 | `Data\WidgetsDashboard` | — |
| [`analyticsDashboard`](10-data-dashboard.md#analyticsdashboard) | 数据分析仪表盘 | `Data\AnalyticsDashboard` | — |
| [`dashboardGrid`](10-data-dashboard.md#dashboardgrid) | 仪表盘网格 | `Data\DashboardGrid` | — |
| [`settingsCenter`](10-data-dashboard.md#settingscenter) | 设置中心 | `Data\SettingsCenter` | — |
| [`reportPage`](10-data-dashboard.md#reportpage) | 报表页 | `Data\ReportPage` | — |
| [`moduleNav`](10-data-dashboard.md#modulenav) | 模块导航 | `Data\ModuleNav` | — |
| [`moduleGrid`](10-data-dashboard.md#modulegrid) | 模块网格 | `Data\ModuleGrid` | — |
| [`apiKeys`](10-data-dashboard.md#apikeys) | API 密钥管理 | `Data\ApiKeys` | — |
| [`appManage`](10-data-dashboard.md#appmanage) | 应用管理列表 | `Data\AppManage` | — |
| [`importExport`](10-data-dashboard.md#importexport) | 批量导入/导出工具条 | `Data\ImportExport` | — |

---

## [杂项与交互增强](11-misc.md)

日历、树形、拖拽排序、灯箱、引导漫游、剪贴板、SweetAlert、PDF 预览、文本对比、瀑布流、动画等。

| 别名 | 说明 | 组件类 | 依赖插件 |
|---|---|---|---|
| [`calendar`](11-misc.md#calendar) | 日历 | `Misc\Calendar` | `fullcalendar` |
| [`treeView`](11-misc.md#treeview) | 树形视图 | `Misc\TreeView` | `jstree` |
| [`nestable`](11-misc.md#nestable) | 可拖拽排序列表 | `Misc\Nestable` | `sortablejs` |
| [`lightbox`](11-misc.md#lightbox) | 灯箱 | `Misc\Lightbox` | `glightbox` `masonry` |
| [`tour`](11-misc.md#tour) | 新手指引漫游 | `Misc\Tour` | `tourguide` |
| [`clipboard`](11-misc.md#clipboard) | 剪贴板复制按钮 | `Misc\ClipboardButton` | `clipboard` |
| [`sweetAlert`](11-misc.md#sweetalert) | SweetAlert2 弹窗 | `Misc\SweetAlert` | `sweetalert2` |
| [`raw`](11-misc.md#raw) | 原生 HTML 透传 | `Misc\Raw` | — |
| [`tinycon`](11-misc.md#tinycon) | 动态 favicon 角标 | `Misc\Tinycon` | `tinycon` |
| [`idleTimer`](11-misc.md#idletimer) | 空闲计时器 | `Misc\IdleTimer` | — |
| [`animate`](11-misc.md#animate) | 入场动画 | `Misc\Animate` | `animate` |
| [`pdfViewer`](11-misc.md#pdfviewer) | PDF 预览 | `Misc\PdfViewer` | `pdfjs` |
| [`textDiff`](11-misc.md#textdiff) | 文本差异对比 | `Misc\TextDiff` | `diff` |
| [`pinBoard`](11-misc.md#pinboard) | 钉板 | `Misc\PinBoard` | — |
| [`masonry`](11-misc.md#masonry) | 瀑布流布局 | `Misc\Masonry` | `masonry` |
| [`videoPlayer`](11-misc.md#videoplayer) | 视频播放器 | `Misc\VideoPlayer` | — |
| [`i18n`](11-misc.md#i18n) | 国际化 | `Misc\I18n` | — |


<?php

declare(strict_types=1);

namespace zxf\XfAdmin;

use Closure;
use InvalidArgumentException;
use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;

/**
 * XfAdmin 组件工厂 / 全局配置统一入口（门面）
 *
 * 本类是扩展包唯一的对外静态门面，职责包括：
 *   1. 组件工厂 —— 通过魔术静态调用按别名创建组件实例（见下方 @method 注解，
 *      分为 布局 Layout / 导航 Navigation / 栅格 Grid / UI 基础 / 表单 Form /
 *      图表 Chart / 数据业务 Data / 杂项 Misc 八大类）；
 *   2. 全局配置 —— config() 合并主题 / 品牌 / 资源基址等全局配置，setting() 点号读取；
 *   3. 资源管理 —— head() / scripts() / asset() 输出 CSS、JS 与资源 URL；
 *   4. 扩展机制 —— extend() 注册或覆盖自定义组件；
 *   5. 服务端数据 —— dataResponse() 生成 DataTables 服务端分页协议响应。
 *
 * 基本用法：
 *   XfAdmin::config(require 'config/xfadmin.php');           // 引导时合并全局配置
 *   echo XfAdmin::card(['title' => '标题', 'body' => '内容']); // 渲染任意组件
 *   echo XfAdmin::dataTable(['columns' => [...], 'data' => [...]]);
 *   echo XfAdmin::head();     // <head> 内输出（自定义布局时）
 *   echo XfAdmin::scripts();  // </body> 前输出（自定义布局时）
 *   return XfAdmin::page([...]); // 或直接用整页组件（已含 head/scripts）
 *
 * @method static Components\Layout\Page       page(array $options = [])  // 整页布局容器：组装 head/body/scripts，所有后台页面入口
 * @method static Components\Layout\Sidenav    sidenav(array $options = [])  // 左侧导航菜单（支持多级子菜单与 Mega Menu）
 * @method static Components\Layout\Topbar     topbar(array $options = [])  // 顶部导航栏（搜索/通知/用户菜单等）
 * @method static Components\Layout\TopNav     topNav(array $options = [])  // 水平顶部导航（无限级子菜单 + Mega 面板）
 * @method static Components\Layout\PageTitle  pageTitle(array $options = [])  // 页面标题区（标题 + 面包屑 + 操作按钮）
 * @method static Components\Layout\Footer     footer(array $options = [])  // 页面底部版权/链接区
 * @method static Components\Layout\Customizer customizer(array $options = [])  // 右侧主题定制面板（明暗/配色/布局切换）
 * @method static Components\Layout\AuthPage   authPage(array $options = [])  // 认证页统一入口（type+layout 控制 9 种语义×3 种布局）
 * @method static Components\Layout\AuthPage   signIn(array $options = [])  // 登录页（auth sign-in，支持 card/basic/split 布局）
 * @method static Components\Layout\AuthPage   signUp(array $options = [])  // 注册页（auth sign-up，含密码强度与协议插槽）
 * @method static Components\Layout\AuthPage   resetPass(array $options = [])  // 找回密码页（auth reset-pass，邮箱输入）
 * @method static Components\Layout\AuthPage   newPass(array $options = [])  // 设置新密码页（auth new-pass，新密码+确认）
 * @method static Components\Layout\AuthPage   twoFactor(array $options = [])  // 两步验证页（auth two-factor，验证码输入）
 * @method static Components\Layout\AuthPage   lockScreen(array $options = [])  // 锁屏页（auth lock-screen，密码解锁）
 * @method static Components\Layout\AuthPage   deleteAccount(array $options = [])  // 注销账户确认页（auth delete-account）
 * @method static Components\Layout\AuthPage   successMail(array $options = [])  // 邮件发送成功页（auth success-mail）
 * @method static Components\Layout\AuthPage   loginPin(array $options = [])  // PIN 码登录页（auth login-pin）
 * @method static Components\Layout\ErrorPage  errorPage(array $options = [])  // 错误页（400/401/403/404/408/500 + maintenance）
 * @method static Components\Navigation\Menu   menu(array $options = [])  // 菜单数据组件（导航菜单 DSL，供 sidenav/topnav 使用）
 * @method static Components\Grid\Row          row(array $options = [])  // 栅格行容器（Bootstrap row 封装）
 * @method static Components\Grid\Col          col(array $options = [])  // 栅格列（Bootstrap col 封装，支持响应式断点）
 * @method static Components\UI\Card           card(array $options = [])  // 卡片容器（header/body/footer + 工具按钮 data-action）
 * @method static Components\UI\StatCard       statCard(array $options = [])  // 指标卡（大数字 + 趋势 + 图标，对标 metrics/widgets）
 * @method static Components\Table\Table       table(array $options = [])  // 静态表格（基础 HTML 表格封装）
 * @method static Components\Table\DataTable   dataTable(array $options = [])  // 数据表格（DataTables：服务端/客户端、导出、固定列等）
 * @method static Components\Form\Form         form(array $options = [])  // 表单容器（统一字段包装与提交处理）
 * @method static Components\Form\Input        input(array $options = [])  // 文本输入框（含前后缀/帮助/校验态）
 * @method static Components\Form\Textarea     textarea(array $options = [])  // 多行文本域
 * @method static Components\Form\Select       select(array $options = [])  // 下拉选择框（原生/select2/tom-select）
 * @method static Components\Form\Check        check(array $options = [])  // 多选/单选框（check/radio 统一封装）
 * @method static Components\Form\Slider       slider(array $options = [])  // 滑块/范围选择器（ion-rangeSlider/noUiSlider）
 * @method static Components\Form\DateRangePicker dateRange(array $options = [])  // 日期范围选择器（daterangepicker）
 * @method static Components\Form\DateRangePicker dateRangePicker(array $options = [])  // 日期范围选择器（daterangepicker，别名）
 * @method static Components\Form\DatePicker      datePicker(array $options = [])  // 单日期/日期时间选择器（singleDatePicker 模式）
 * @method static Components\Form\Editor       editor(array $options = [])  // 富文本编辑器（TinyMCE/Quill 等）
 * @method static Components\Form\Upload       upload(array $options = [])  // 文件上传（native/dropzone/filepond 驱动）
 * @method static Components\Form\ColorPicker  colorPicker(array $options = [])  // 颜色选择器（pickr）
 * @method static Components\Form\PasswordStrength passwordStrength(array $options = [])  // 密码强度计（实时弱/中/强提示）
 * @method static Components\Form\Captcha      captcha(array $options = [])  // 验证码（image/math/slide 三种模式）
 * @method static Components\Chart\ApexChart   apexChart(array $options = [])  // ApexCharts 图表（折线/柱/饼/雷达等）
 * @method static Components\Chart\ApexTree    apexTree(array $options = [])  // ApexCharts 树形图
 * @method static Components\Chart\ApexSankey  apexSankey(array $options = [])  // ApexCharts 桑基图（流量关系）
 * @method static Components\Chart\EChart      echart(array $options = [])  // ECharts 图表（通用可视化）
 * @method static Components\Chart\VectorMap   vectorMap(array $options = [])  // 矢量地图（jsVectorMap）
 * @method static Components\Chart\GoogleMap   googleMap(array $options = [])  // Google 地图
 * @method static Components\Chart\LeafletMap   leafletMap(array $options = [])  // Leaflet 地图（支持离线瓦片）
 * @method static Components\UI\Alert          alert(array $options = [])  // 警告提示条（variant/可关闭/dismiss）
 * @method static Components\UI\Badge          badge(array $options = [])  // 徽章标签（状态/数量标记）
 * @method static Components\UI\Button         button(array $options = [])  // 按钮（variant/size/loading/icon）
 * @method static Components\UI\Dropdown       dropdown(array $options = [])  // 下拉菜单（Bootstrap dropdown 封装）
 * @method static Components\UI\Modal          modal(array $options = [])  // 模态框（居中/尺寸/ajax 加载）
 * @method static Components\UI\Offcanvas      offcanvas(array $options = [])  // 抽屉面板（侧滑浮层）
 * @method static Components\UI\Tabs           tabs(array $options = [])  // 选项卡（支持上下/左右布局）
 * @method static Components\UI\Accordion      accordion(array $options = [])  // 手风琴折叠面板
 * @method static Components\UI\Progress       progress(array $options = [])  // 进度条（variant/条纹/高度）
 * @method static Components\UI\Spinner        spinner(array $options = [])  // 加载旋转指示器（border/grow 变体）
 * @method static Components\UI\Pagination     pagination(array $options = [])  // 分页器（页码/上一页/下一页）
 * @method static Components\UI\ListGroup      listGroup(array $options = [])  // 列表组（卡片式条目列表）
 * @method static Components\UI\Avatar         avatar(array $options = [])  // 头像（图片/文字/图标/状态点）
 * @method static Components\UI\AvatarGroup    avatarGroup(array $options = [])  // 头像组（重叠堆叠展示多人）
 * @method static Components\UI\Callout        callout(array $options = [])  // 强调提示块（左侧色条，variant 语义）
 * @method static Components\UI\Divider        divider(array $options = [])  // 分割线（文本/图标居中分割）
 * @method static Components\UI\Kbd            kbd(array $options = [])  // 键盘按键标记（<kbd> 样式）
 * @method static Components\UI\Media          media(array $options = [])  // 媒体对象（左图右文列表项）
 * @method static Components\UI\Skeleton       skeleton(array $options = [])  // 骨架屏占位（加载占位动画）
 * @method static Components\UI\Icon           icon(array $options = [])  // 图标（Tabler 字体图标封装）
 * @method static Components\UI\Toast          toast(array $options = [])  // 轻提示（右上角自动消失吐司）
 * @method static Components\UI\Timeline       timeline(array $options = [])  // 时间线（纵向/横向事件流）
 * @method static Components\UI\Carousel       carousel(array $options = [])  // 轮播图（多图滑动，object-fit 封面）
 * @method static Components\UI\Breadcrumb     breadcrumb(array $options = [])  // 面包屑导航
 * @method static Components\Misc\Calendar     calendar(array $options = [])  // 日历（FullCalendar 事件视图）
 * @method static Components\Misc\TreeView     treeView(array $options = [])  // 树形视图（可展开节点）
 * @method static Components\Misc\Nestable     nestable(array $options = [])  // 可拖拽排序列表（嵌套 sortable）
 * @method static Components\Misc\Lightbox     lightbox(array $options = [])  // 灯箱（点击图片放大预览）
 * @method static Components\Misc\Tour         tour(array $options = [])  // 新手指引漫游（分步高亮引导）
 * @method static Components\Misc\ClipboardButton clipboard(array $options = [])  // 剪贴板复制按钮（clipboard.js）
 * @method static Components\Misc\ClipboardButton clipboardButton(array $options = [])  // 剪贴板复制按钮（别名）
 * @method static Components\Misc\SweetAlert   sweetAlert(array $options = [])  // SweetAlert2 弹窗（confirm/toast 等）
 * @method static Components\Misc\Raw          raw(array $options = [])  // 原生 HTML 透传（不包裹任何结构的原样输出组件）
 * @method static Components\Misc\Tinycon      tinycon(array $options = [])  // 动态 favicon 角标（未读消息数）
 * @method static Components\Misc\IdleTimer    idleTimer(array $options = [])  // 空闲计时器（用户无操作超时处理）
 * @method static Components\Misc\Animate      animate(array $options = [])  // 入场动画（滚动触发元素动画）
 * @method static Components\Misc\PdfViewer    pdfViewer(array $options = [])  // PDF 预览（pdf.js 内嵌查看）
 * @method static Components\Misc\TextDiff     textDiff(array $options = [])  // 文本差异对比（merge-diff 高亮）
 * @method static Components\Layout\ComingSoon  comingSoon(array $options = [])  // 即将上线页（倒计时 + 订阅）
 * @method static Components\Layout\Maintenance maintenance(array $options = [])  // 系统维护页（503 风格）
 * @method static Components\Layout\EmptyState  emptyState(array $options = [])  // 空状态页（无数据占位插画+文案）
 * //method static Components\Layout\LockScreen  lockScreen(array $options = [])  // 锁屏页（auth lock-screen，密码解锁）
 * @method static Components\Layout\AccountSettingsPanel accountSettingsPanel(array $options = [])  // 账户设置面板（头像/密码/通知等卡片组）
 * @method static Components\Form\Tags          tags(array $options = [])  // 标签输入（Tagify 多标签）
 * @method static Components\Form\MaskedInput   maskedInput(array $options = [])  // 输入掩码（电话/日期格式约束）
 * @method static Components\Form\Wizard        wizard(array $options = [])  // 向导（多步表单分步导航）
 * @method static Components\UI\Tooltip         tooltip(array $options = [])  // 文字提示气泡（hover 触发）
 * @method static Components\UI\Popover         popover(array $options = [])  // 弹出框（hover/click 富内容）
 * @method static Components\UI\Placeholder     placeholder(array $options = [])  // 占位符块（Bootstrap placeholder 闪烁）
 * @method static Components\UI\Collapse        collapse(array $options = [])  // 折叠面板（单/多展开）
 * @method static Components\UI\Scrollspy       scrollspy(array $options = [])  // 滚动监听（导航高亮当前区块）
 * @method static Components\UI\Ratio           ratio(array $options = [])  // 固定宽高比容器（视频/嵌入比例）
 * @method static Components\UI\Rating          rating(array $options = [])  // 评分（星星/点赞，只读/可交互）
 * @method static Components\UI\Ribbon          ribbon(array $options = [])  // 缎带角标（卡片右上角标签）
 * @method static Components\UI\Chip            chip(array $options = [])  // 筹码标签（可删除的紧凑标签）
 * @method static Components\UI\Stepper         stepper(array $options = [])  // 步骤条（水平步骤指示器，向导导航）
 * @method static Components\UI\DescriptionList descriptionList(array $options = [])  // 描述列表（术语/定义键值对）
 * @method static Components\UI\Toggle        switch(array $options = [])  // 开关切换（Toggle，PHP 保留字故类名 Toggle）
 * @method static Components\UI\CodeBlock     codeBlock(array $options = [])  // 代码块（语法高亮 + 复制按钮）
 * @method static Components\UI\EmptyState    empty(array $options = [])  // 空状态组件（别名，对应 EmptyState）
 * @method static Components\UI\Toolbar       toolbar(array $options = [])  // 工具栏容器（左右分栏操作区）
 * @method static Components\UI\SearchBox     searchBox(array $options = [])  // 搜索框（输入+按钮/图标）
 * @method static Components\UI\LoadingButton loadingButton(array $options = [])  // 加载按钮（点击后显示 spinner）
 * @method static Components\UI\Countdown     countdown(array $options = [])  // 倒计时（数字翻牌到零）
 * @method static Components\UI\CountUp       countUp(array $options = [])  // 数字滚动递增动画
 * @method static Components\UI\BackToTop     backToTop(array $options = [])  // 回到顶部悬浮按钮
 * @method static Components\Data\PricingCard   pricingCard(array $options = [])  // 价格方案卡（推荐标记/功能列表/按钮）
 * @method static Components\Data\Faq           faq(array $options = [])  // 常见问题（问答卡片列表）
 * @method static Components\Data\ProfileHeader profileHeader(array $options = [])  // 个人资料头部（封面+头像+统计）
 * @method static Components\Data\ProductCard   productCard(array $options = [])  // 商品卡（图/标题/价格/角标）
 * @method static Components\Data\Kanban        kanban(array $options = [])  // 看板（拖拽列/卡片，项目与工单管理）
 * @method static Components\Data\ChatBox       chatBox(array $options = [])  // 聊天框（消息气泡容器）
 * @method static Components\Data\InvoiceTable  invoiceTable(array $options = [])  // 发票明细表格
 * @method static Components\Data\MailList      mailList(array $options = [])  // 邮件列表（收件箱条目）
 * @method static Components\Data\FileManager   fileManager(array $options = [])  // 文件管理器（目录/文件网格）
 * @method static Components\Data\Widget        widget(array $options = [])  // 通用小部件卡（标题+内容+数值）
 * @method static Components\Data\ActivityFeed  activityFeed(array $options = [])  // 活动动态流（时间线式操作记录）
 * @method static Components\Data\Gallery       gallery(array $options = [])  // 图片画廊（网格+灯箱）
 * @method static Components\Data\BlogList      blogList(array $options = [])  // 博客列表（文章卡片流）
 * @method static Components\Data\InvoiceList   invoiceList(array $options = [])  // 发票列表（列表+状态+金额）
 * @method static Components\Data\SearchResults  searchResults(array $options = [])  // 搜索结果列表（标题+摘要+链接）
 * @method static Components\Data\PermissionMatrix permissionMatrix(array $options = [])  // 权限矩阵（角色×权限勾选表）
 * @method static Components\Data\ApiKeys       apiKeys(array $options = [])  // API 密钥管理（密钥列表+创建/撤销）
 * @method static Components\Data\CommentThread commentThread(array $options = [])  // 评论线程（嵌套回复列表）
 * @method static Components\Data\EmailCompose  emailCompose(array $options = [])  // 邮件撰写（收件人/主题/正文/附件）
 * @method static Components\Data\Customers      customers(array $options = [])  // 客户列表（电商客户管理）
 * @method static Components\Data\Orders         orders(array $options = [])  // 订单列表（电商订单管理）
 * @method static Components\Data\TaskList        taskList(array $options = [])  // 任务清单（勾选/优先级）
 * @method static Components\Data\Deals           deals(array $options = [])  // 交易/商机列表（CRM 看板式）
 * @method static Components\Data\OrderDetails   orderDetails(array $options = [])  // 订单详情（商品/收货/金额）
 * @method static Components\Data\ProductDetails productDetails(array $options = [])  // 商品详情（图集/规格/评价）
 * @method static Components\Data\Projects       projects(array $options = [])  // 项目列表（卡片/列表视图）
 * @method static Components\Data\ProjectDetails projectDetails(array $options = [])  // 项目详情（概览/进度/成员）
 * @method static Components\Data\Outlook        outlook(array $options = [])  // Outlook 风格邮件客户端
 * @method static Components\Data\ForumThread    forumThread(array $options = [])  // 论坛帖子（主题+回复线程）
 * @method static Components\Data\BlogArticle    blogArticle(array $options = [])  // 博客文章详情（正文+作者+评论）
 * @method static Components\Data\Roles          roles(array $options = [])  // 角色管理（角色列表+成员）
 * @method static Components\Data\InvoiceCreate  invoiceCreate(array $options = [])  // 发票创建表单
 * @method static Components\Data\TeamMember     teamMember(array $options = [])  // 团队成员卡（头像/职位/操作）
 * @method static Components\Data\Testimonial    testimonial(array $options = [])  // 用户证言（头像+评语+星标）
 * @method static Components\Data\TodoList       todoList(array $options = [])  // 待办列表（添加/完成）
 * @method static Components\Data\IssueTracker   issueTracker(array $options = [])  // 问题追踪（看板式缺陷管理）
 * @method static Components\Data\VoteList       voteList(array $options = [])  // 投票列表（选项+进度条）
 * @method static Components\Data\MetricCard     metricCard(array $options = [])  // 迷你指标卡（数字+图标+同比）
 * @method static Components\Data\Terms          terms(array $options = [])  // 服务条款页（长文本条款）
 * @method static Components\Data\ContactCard    contactCard(array $options = [])  // 联系人卡（头像/电话/邮件）
 * @method static Components\Data\CompanyCard    companyCard(array $options = [])  // 公司卡（logo/简介/链接）
 * @method static Components\Data\Clients         clients(array $options = [])  // 客户/客户端列表（apps-clients）
 * @method static Components\Data\Sellers         sellers(array $options = [])  // 卖家列表（电商卖家管理）
 * @method static Components\Data\ReviewList      reviewList(array $options = [])  // 评价列表（星级+内容+晒图）
 * @method static Components\Data\ProjectTeamBoard projectTeamBoard(array $options = [])  // 项目团队看板（成员任务分配）
 * @method static Components\Data\EmailApp       emailApp(array $options = [])  // 邮件应用整页（列表+阅读+撰写）
 * @method static Components\Data\ChatApp        chatApp(array $options = [])  // 聊天应用整页（会话+消息）
 * @method static Components\Data\ProfilePage    profilePage(array $options = [])  // 个人资料页（头部+标签页+动态）
 * @method static Components\Data\InvoiceDetail  invoiceDetail(array $options = [])  // 发票详情整页
 * @method static Components\Data\Companies      companies(array $options = [])  // 公司列表（apps-companies）
 * @method static Components\Data\ProductCategories productCategories(array $options = [])  // 商品分类管理（树形分类）
 * @method static Components\Data\ProductAdd     productAdd(array $options = [])  // 商品添加表单
 * @method static Components\Data\SellerDetails  sellerDetails(array $options = [])  // 卖家详情（店铺/商品/评价）
 * @method static Components\Data\Article        article(array $options = [])  // 文章详情（通用内容页）
 * @method static Components\Data\ProjectActivity projectActivity(array $options = [])  // 项目活动流（动态时间线）
 * @method static Components\Data\ShoppingCart   shoppingCart(array $options = [])  // 购物车（商品+数量+合计）
 * @method static Components\Data\Checkout       checkout(array $options = [])  // 结算页（地址/支付/提交）
 * @method static Components\Data\Marketplace    marketplace(array $options = [])  // 应用市场（插件/模板网格）
 * @method static Components\Data\AccountSettings accountSettings(array $options = [])  // 账户设置整页（tabs+表单）
 * @method static Components\Data\Sitemap        sitemap(array $options = [])  // 站点地图（层级链接导航）
 * @method static Components\Data\PrivacyPolicy  privacyPolicy(array $options = [])  // 隐私政策页（长文本）
 * @method static Components\Data\AppManage      appManage(array $options = [])  // 应用管理列表（apps-manage）
 * @method static Components\Data\Warehouse      warehouse(array $options = [])  // 仓库管理（库存/出入库）
 * @method static Components\Data\Refunds        refunds(array $options = [])  // 退款管理（退款单列表）
 * @method static Components\Data\Sales           sales(array $options = [])  // 销售数据（图表+列表）
 * @method static Components\Data\PurchasedOrders purchasedOrders(array $options = [])  // 已购订单（买家视图）
 * @method static Components\Data\Attributes     attributes(array $options = [])  // 商品属性管理（规格键值）
 * @method static Components\Data\EcommerceSettings ecommerceSettings(array $options = [])  // 电商设置（通用配置表单）
 * @method static Components\Data\ProductsGrid   productsGrid(array $options = [])  // 商品网格（卡片墙）
 * @method static Components\Data\ProductViews   productViews(array $options = [])  // 商品浏览统计（热度图表）
 * @method static Components\Data\AnalyticsDashboard analyticsDashboard(array $options = [])  // 数据分析仪表盘（多图表汇总）
 * @method static Components\UI\ColorPalette    colorPalette(array $options = [])  // 配色方案展示（色块+HEX 值）
 * @method static Components\UI\Typography       typography(array $options = [])  // 排版展示（display/标题/文本工具类示例）
 * @method static Components\UI\Utilities        utilities(array $options = [])  // Bootstrap 工具类展示（间距/弹性/边框等）
 * @method static Components\UI\IconSet         iconSet(array $options = [])  // 图标集展示（Tabler 图标网格）
 * @method static Components\UI\VideoEmbed      videoEmbed(array $options = [])  // 视频嵌入（响应式比例容器）
 * @method static Components\Table\TablesCustom tablesCustom(array $options = [])  // 自定义表格（可编辑/带控件的复杂表）
 * @method static Components\Misc\VideoPlayer    videoPlayer(array $options = [])  // 视频播放器（plyr 等）
 * @method static Components\Misc\I18n          i18n(array $options = [])  // 国际化（多语言切换演示）
 * @method static Components\Misc\PinBoard       pinBoard(array $options = [])  // 钉板（瀑布流便签卡片）
 * @method static Components\Misc\Masonry        masonry(array $options = [])  // 瀑布流布局（错落卡片墙）
 * @method static Components\Layout\Landing      landing(array $options = [])  // 落地页/营销首页（英雄区+特性+CTA）
 * @method static Components\Data\EcommerceDashboard ecommerceDashboard(array $options = [])  // 电商仪表盘（销售/订单/库存概览）
 * @method static Components\Data\WidgetsDashboard widgetsDashboard(array $options = [])  // 小部件仪表盘（多 widget 汇总）
 * @method static Components\Data\ModuleNav      moduleNav(array $options = [])  // 模块导航（企业级模块菜单）
 * @method static Components\Data\ModuleGrid     moduleGrid(array $options = [])  // 模块网格（功能模块卡片墙）
 * @method static Components\Data\DashboardGrid   dashboardGrid(array $options = [])  // 仪表盘网格（可拖拽 widget 布局）
 * @method static Components\Data\SettingsCenter  settingsCenter(array $options = [])  // 设置中心（分组设置入口）
 * @method static Components\Data\ReportPage      reportPage(array $options = [])  // 报表页（数据报表展示）
 * @method static Components\Data\CartSummary      cartSummary(array $options = [])  // 购物车摘要（结算侧栏小计）
 * @method static Components\Data\ChatConversationPanel chatConversationPanel(array $options = [])  // 聊天会话面板（单会话消息区）
 * @method static Components\Data\ChatMessageBubble chatMessageBubble(array $options = [])  // 聊天气泡（单条消息）
 * @method static Components\Data\FeatureComparisonTable featureComparisonTable(array $options = [])  // 功能对比表（方案×特性矩阵）
 * @method static Components\Data\FilterSidebar   filterSidebar(array $options = [])  // 筛选侧栏（分类/价格/标签过滤）
 * @method static Components\Data\OrderTrackingTimeline orderTrackingTimeline(array $options = [])  // 订单追踪时间线（物流状态流）
 * @method static Components\Data\SearchResultsRich searchResultsRich(array $options = [])  // 富搜索结果（分组+缩略图）
 * @method static Components\Data\StatMiniSparkline statMiniSparkline(array $options = [])  // 迷你指标+火花线（数字+趋势迷你图）
 * @method static Components\Data\SocialFeed      socialFeed(array $options = [])  // 社交动态流（朋友圈式信息流）
 * @method static Components\Data\FaqAccordion    faqAccordion(array $options = [])  // FAQ 手风琴（折叠问答）
 * @method static Components\Data\ContactList     contactList(array $options = [])  // 联系人列表（通讯录）
 * @method static Components\Data\UserProfile     userProfile(array $options = [])  // 用户资料卡（详情展示）
 * @method static Components\Data\InvoiceView     invoiceView(array $options = [])  // 发票查看（打印友好视图）
 * @method static Components\Form\FormElements   formElements(array $options = [])  // 表单元素集合（所有输入控件演示）
 * @method static Components\Form\FormLayout     formLayout(array $options = [])  // 表单布局（水平/垂直/网格排布）
 * @method static Components\Form\FormOtherPlugin formOtherPlugin(array $options = [])  // 其它表单插件（掩码/校验等组合）
 * @method static Components\Form\FormValidation formValidation(array $options = [])  // 表单校验（实时反馈示例）
 * @method static Components\UI\CommandPalette     commandPalette(array $options = [])  // 命令面板（⌘K 快捷命令）
 * @method static Components\UI\NotificationCenter notificationCenter(array $options = [])  // 通知中心（消息抽屉）
 * @method static Components\UI\DropzoneUpload     dropzoneUpload(array $options = [])  // 拖拽上传区（Dropzone 自定义版）
 */
final class XfAdmin
{
    /** 扩展包版本号（与 config/xfadmin.php 的 version 同步，用于运行时自检） */
    public const VERSION = '2.0.0';

    /** 组件注册表：alias => class */
    private static array $components = [
        // 布局
        'page'        => Components\Layout\Page::class,
        'sidenav'     => Components\Layout\Sidenav::class,
        'topbar'      => Components\Layout\Topbar::class,
        'topNav'      => Components\Layout\TopNav::class,
        'topnav'      => Components\Layout\TopNav::class,
        'pageTitle'   => Components\Layout\PageTitle::class,
        'footer'      => Components\Layout\Footer::class,
        'customizer'  => Components\Layout\Customizer::class,
        'authPage'    => Components\Layout\AuthPage::class,
        // auth 系列语义便捷别名（均指向 AuthPage，按 type 区分）
        'signIn'      => Components\Layout\AuthPage::class,
        'signUp'      => Components\Layout\AuthPage::class,
        'resetPass'   => Components\Layout\AuthPage::class,
        'newPass'     => Components\Layout\AuthPage::class,
        'twoFactor'   => Components\Layout\AuthPage::class,
        // 'lockScreen'  => Components\Layout\AuthPage::class,
        'deleteAccount' => Components\Layout\AuthPage::class,
        'successMail' => Components\Layout\AuthPage::class,
        'loginPin'    => Components\Layout\AuthPage::class,
        'errorPage'   => Components\Layout\ErrorPage::class,
        'comingSoon'  => Components\Layout\ComingSoon::class,
        'maintenance' => Components\Layout\Maintenance::class,
        'emptyState'  => Components\Layout\EmptyState::class,
        'lockScreen'  => Components\Layout\LockScreen::class,
        'landing'     => Components\Layout\Landing::class,
        // 导航
        'menu'        => Components\Navigation\Menu::class,
        // 栅格
        'row'         => Components\Grid\Row::class,
        'col'         => Components\Grid\Col::class,
        // 卡片
        'card'        => Components\UI\Card::class,
        'statCard'    => Components\UI\StatCard::class,
        // 表格
        'table'       => Components\Table\Table::class,
        'dataTable'   => Components\Table\DataTable::class,
        'tablesCustom' => Components\Table\TablesCustom::class,
        // 表单
        'form'        => Components\Form\Form::class,
        'input'       => Components\Form\Input::class,
        'textarea'    => Components\Form\Textarea::class,
        'select'      => Components\Form\Select::class,
        'check'       => Components\Form\Check::class,
        'slider'      => Components\Form\Slider::class,
        'dateRange'   => Components\Form\DateRangePicker::class,
        'dateRangePicker' => Components\Form\DateRangePicker::class,
        'datePicker'  => Components\Form\DatePicker::class,
        'editor'      => Components\Form\Editor::class,
        'upload'      => Components\Form\Upload::class,
        'colorPicker' => Components\Form\ColorPicker::class,
        'tags'        => Components\Form\Tags::class,
        'maskedInput' => Components\Form\MaskedInput::class,
        'wizard'      => Components\Form\Wizard::class,
        'passwordStrength' => Components\Form\PasswordStrength::class,
        'captcha'     => Components\Form\Captcha::class,
        'formElements' => Components\Form\FormElements::class,
        'formLayout' => Components\Form\FormLayout::class,
        'formOtherPlugin' => Components\Form\FormOtherPlugin::class,
        'formValidation' => Components\Form\FormValidation::class,
        // 图表 / 地图
        'apexChart'   => Components\Chart\ApexChart::class,
        'apexTree'    => Components\Chart\ApexTree::class,
        'apexSankey'  => Components\Chart\ApexSankey::class,
        'echart'      => Components\Chart\EChart::class,
        'vectorMap'   => Components\Chart\VectorMap::class,
        'leafletMap'  => Components\Chart\LeafletMap::class,
        'googleMap'   => Components\Chart\GoogleMap::class,
        // UI
        'alert'       => Components\UI\Alert::class,
        'badge'       => Components\UI\Badge::class,
        'button'      => Components\UI\Button::class,
        'dropdown'    => Components\UI\Dropdown::class,
        'modal'       => Components\UI\Modal::class,
        'offcanvas'   => Components\UI\Offcanvas::class,
        'tabs'        => Components\UI\Tabs::class,
        'accordion'   => Components\UI\Accordion::class,
        'progress'    => Components\UI\Progress::class,
        'spinner'     => Components\UI\Spinner::class,
        'pagination'  => Components\UI\Pagination::class,
        'listGroup'   => Components\UI\ListGroup::class,
        'avatar'      => Components\UI\Avatar::class,
        'icon'        => Components\UI\Icon::class,
        'toast'       => Components\UI\Toast::class,
        'timeline'    => Components\UI\Timeline::class,
        'carousel'    => Components\UI\Carousel::class,
        'breadcrumb'  => Components\UI\Breadcrumb::class,
        'tooltip'     => Components\UI\Tooltip::class,
        'popover'     => Components\UI\Popover::class,
        'placeholder' => Components\UI\Placeholder::class,
        'collapse'    => Components\UI\Collapse::class,
        'scrollspy'   => Components\UI\Scrollspy::class,
        'ratio'       => Components\UI\Ratio::class,
        'rating'      => Components\UI\Rating::class,
        'ribbon'      => Components\UI\Ribbon::class,
        'chip'        => Components\UI\Chip::class,
        'stepper'     => Components\UI\Stepper::class,
        'descriptionList' => Components\UI\DescriptionList::class,
        'loadingButton' => Components\UI\LoadingButton::class,
        'avatarGroup'  => Components\UI\AvatarGroup::class,
        'backToTop'    => Components\UI\BackToTop::class,
        'callout'      => Components\UI\Callout::class,
        'countdown'    => Components\UI\Countdown::class,
        'countUp'      => Components\UI\CountUp::class,
        'divider'      => Components\UI\Divider::class,
        'kbd'          => Components\UI\Kbd::class,
        'media'        => Components\UI\Media::class,
        'skeleton'     => Components\UI\Skeleton::class,
        'switch'       => Components\UI\Toggle::class,
        'codeBlock'    => Components\UI\CodeBlock::class,
        'empty'        => Components\UI\EmptyState::class,
        'toolbar'      => Components\UI\Toolbar::class,
        'searchBox'    => Components\UI\SearchBox::class,
        'colorPalette' => Components\UI\ColorPalette::class,
        'typography'   => Components\UI\Typography::class,
        'utilities'    => Components\UI\Utilities::class,
        'iconSet'      => Components\UI\IconSet::class,
        'videoEmbed'   => Components\UI\VideoEmbed::class,
        // 新增：对标 INSPINIA 缺失的业务组件
        'commandPalette'    => Components\UI\CommandPalette::class,
        'notificationCenter' => Components\UI\NotificationCenter::class,
        'dropzoneUpload'    => Components\UI\DropzoneUpload::class,
        // 数据 / 业务
        'pricingCard'  => Components\Data\PricingCard::class,
        'faq'          => Components\Data\Faq::class,
        'profileHeader'=> Components\Data\ProfileHeader::class,
        'productCard'  => Components\Data\ProductCard::class,
        'kanban'       => Components\Data\Kanban::class,
        'chatBox'      => Components\Data\ChatBox::class,
        'invoiceTable' => Components\Data\InvoiceTable::class,
        'mailList'     => Components\Data\MailList::class,
        'fileManager'  => Components\Data\FileManager::class,
        'widget'       => Components\Data\Widget::class,
        'activityFeed' => Components\Data\ActivityFeed::class,
        'gallery'      => Components\Data\Gallery::class,
        'blogList'     => Components\Data\BlogList::class,
        'invoiceList'  => Components\Data\InvoiceList::class,
        'searchResults'=> Components\Data\SearchResults::class,
        'permissionMatrix' => Components\Data\PermissionMatrix::class,
        'apiKeys'      => Components\Data\ApiKeys::class,
        'commentThread'=> Components\Data\CommentThread::class,
        'emailCompose' => Components\Data\EmailCompose::class,
        'customers'     => Components\Data\Customers::class,
        'orders'        => Components\Data\Orders::class,
        'taskList'      => Components\Data\TaskList::class,
        'deals'         => Components\Data\Deals::class,
        'orderDetails'  => Components\Data\OrderDetails::class,
        'productDetails'=> Components\Data\ProductDetails::class,
        'projects'      => Components\Data\Projects::class,
        'projectDetails'=> Components\Data\ProjectDetails::class,
        'outlook'       => Components\Data\Outlook::class,
        'forumThread'   => Components\Data\ForumThread::class,
        'blogArticle'   => Components\Data\BlogArticle::class,
        'roles'         => Components\Data\Roles::class,
        'invoiceCreate' => Components\Data\InvoiceCreate::class,
        'teamMember'    => Components\Data\TeamMember::class,
        'testimonial'   => Components\Data\Testimonial::class,
        'todoList'      => Components\Data\TodoList::class,
        'issueTracker'  => Components\Data\IssueTracker::class,
        'voteList'      => Components\Data\VoteList::class,
        'metricCard'    => Components\Data\MetricCard::class,
        'terms'         => Components\Data\Terms::class,
        'contactCard'   => Components\Data\ContactCard::class,
        'companyCard'   => Components\Data\CompanyCard::class,
        'clients'       => Components\Data\Clients::class,
        'sellers'       => Components\Data\Sellers::class,
        'reviewList'    => Components\Data\ReviewList::class,
        'projectTeamBoard' => Components\Data\ProjectTeamBoard::class,
        'emailApp'      => Components\Data\EmailApp::class,
        'chatApp'       => Components\Data\ChatApp::class,
        'profilePage'   => Components\Data\ProfilePage::class,
        'invoiceDetail' => Components\Data\InvoiceDetail::class,
        'companies'     => Components\Data\Companies::class,
        'productCategories' => Components\Data\ProductCategories::class,
        'productAdd'    => Components\Data\ProductAdd::class,
        'sellerDetails' => Components\Data\SellerDetails::class,
        'article'       => Components\Data\Article::class,
        'projectActivity' => Components\Data\ProjectActivity::class,
        'shoppingCart'   => Components\Data\ShoppingCart::class,
        'checkout'       => Components\Data\Checkout::class,
        'marketplace'    => Components\Data\Marketplace::class,
        'accountSettings' => Components\Data\AccountSettings::class,
        'sitemap'        => Components\Data\Sitemap::class,
        'privacyPolicy'  => Components\Data\PrivacyPolicy::class,
        'appManage'      => Components\Data\AppManage::class,
        'warehouse'      => Components\Data\Warehouse::class,
        'refunds'        => Components\Data\Refunds::class,
        'sales'           => Components\Data\Sales::class,
        'purchasedOrders' => Components\Data\PurchasedOrders::class,
        'attributes'     => Components\Data\Attributes::class,
        'ecommerceSettings' => Components\Data\EcommerceSettings::class,
        'productsGrid'   => Components\Data\ProductsGrid::class,
        'productViews'   => Components\Data\ProductViews::class,
        'analyticsDashboard' => Components\Data\AnalyticsDashboard::class,
        'ecommerceDashboard' => Components\Data\EcommerceDashboard::class,
        'widgetsDashboard' => Components\Data\WidgetsDashboard::class,
        // 后台模板可复用组件
        'moduleNav'   => Components\Data\ModuleNav::class,
        'moduleGrid'  => Components\Data\ModuleGrid::class,
        // 页面级可复用布局（仪表盘 / 设置中心 / 报表）
        'dashboardGrid' => Components\Data\DashboardGrid::class,
        'settingsCenter' => Components\Data\SettingsCenter::class,
        'reportPage'   => Components\Data\ReportPage::class,
        // 杂项
        'calendar'    => Components\Misc\Calendar::class,
        'treeView'    => Components\Misc\TreeView::class,
        'nestable'    => Components\Misc\Nestable::class,
        'lightbox'    => Components\Misc\Lightbox::class,
        'tour'        => Components\Misc\Tour::class,
        'clipboard'   => Components\Misc\ClipboardButton::class,
        'clipboardButton' => Components\Misc\ClipboardButton::class,
        'sweetAlert'  => Components\Misc\SweetAlert::class,
        'raw'         => Components\Misc\Raw::class,
        'tinycon'     => Components\Misc\Tinycon::class,
        'idleTimer'   => Components\Misc\IdleTimer::class,
        'animate'     => Components\Misc\Animate::class,
        'pdfViewer'   => Components\Misc\PdfViewer::class,
        'textDiff'    => Components\Misc\TextDiff::class,
        'pinBoard'    => Components\Misc\PinBoard::class,
        'masonry'     => Components\Misc\Masonry::class,
        'videoPlayer' => Components\Misc\VideoPlayer::class,
        'i18n' => Components\Misc\I18n::class,

        // —— 全量封装新增组件（对照 inspinia 模板补齐的高频可复用 UI 模式）——
        'twoFactorInput'      => Components\Form\TwoFactorInput::class,
        'quantityStepper'     => Components\Form\QuantityStepper::class,
        'statMiniSparkline'   => Components\Data\StatMiniSparkline::class,
        'cartSummary'         => Components\Data\CartSummary::class,
        'chatMessageBubble'   => Components\Data\ChatMessageBubble::class,
        'chatConversationPanel' => Components\Data\ChatConversationPanel::class,
        'dataTableToolbar'    => Components\Table\DataTableToolbar::class,
        'orderTrackingTimeline' => Components\Data\OrderTrackingTimeline::class,
        'featureComparisonTable' => Components\Data\FeatureComparisonTable::class,
        'filterSidebar'       => Components\Data\FilterSidebar::class,
        'accountSettingsPanel' => Components\Layout\AccountSettingsPanel::class,
        'searchResultsRich'   => Components\Data\SearchResultsRich::class,
        'invoicePrintButton'  => Components\UI\InvoicePrintButton::class,
        'socialFeed'          => Components\Data\SocialFeed::class,
        'faqAccordion'        => Components\Data\FaqAccordion::class,
        'contactList'         => Components\Data\ContactList::class,
        'userProfile'         => Components\Data\UserProfile::class,
        'invoiceView'         => Components\Data\InvoiceView::class,
    ];

    /** 全局配置（theme/brand/footer 等，Page 等组件的默认值来源） */
    private static array $config = [];

    /**
     * 读取 / 合并全局配置
     *
     * - 传入数组时：与既有配置做「递归合并」（深层键覆盖，不会整体替换），
     *   并联动更新静态资源基址（assets_url）与资源版本号（version，用于 ?v= 缓存刷新）；
     * - 不传参时：仅返回当前完整配置数组。
     *
     * 典型用法（应用引导阶段调用一次）：
     *   XfAdmin::config(require config_path('xfadmin.php'));
     *
     * @param  array|null  $config  待合并的配置数组；null 表示只读
     * @return array 合并后的完整全局配置
     */
    public static function config(?array $config = null): array
    {
        if ($config !== null) {
            self::$config = array_replace_recursive(self::$config, $config);
            if (isset($config['assets_url'])) {
                Assets::instance()->setBaseUrl((string) $config['assets_url']);
            }
            if (array_key_exists('version', $config)) {
                Assets::instance()->setVersion($config['version'] ?: null);
            }
        }
        return self::$config;
    }

    /**
     * 按「点号路径」读取单个全局配置项
     *
     * 例如：XfAdmin::setting('brand.logo')、XfAdmin::setting('theme', 'light')。
     *
     * @param  string  $key      配置键，支持 a.b.c 深层路径
     * @param  mixed   $default  键不存在时返回的默认值
     * @return mixed 配置值或默认值
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        return Support\Html::get(self::$config, $key, $default);
    }

    /**
     * CSRF 令牌解析（可扩展）。
     *
     * 扩展包本身框架无关，不直接耦合 Laravel/ThinkPHP 的 token 生成。
     * 由宿主框架在引导时注册解析器：
     *   XfAdmin::setCsrfResolver(fn () => csrf_token());
     * 未注册时返回空字符串（组件仍输出 _token 隐藏域占位，由框架 @csrf 覆盖亦可）。
     *
     * @return string
     */
    private static ?Closure $csrfResolver = null;

    /**
     * set Csrf Resolver（public静态方法）
     *
     * @param Closure $resolver resolver
     *
     * @return void result
     */
    public static function setCsrfResolver(Closure $resolver): void
    {
        self::$csrfResolver = $resolver;
    }

    /**
     * csrf Token（public静态方法）
     *
     * @return string result
     */
    public static function csrfToken(): string
    {
        if (self::$csrfResolver !== null) {
            return (string) (self::$csrfResolver)();
        }
        $fromConfig = self::setting('csrf_token');
        return is_string($fromConfig) ? $fromConfig : '';
    }

    /**
     * 按别名创建组件实例（组件工厂核心入口）
     *
     * 所有 XfAdmin::card(...)、XfAdmin::dataTable(...) 等魔术调用最终都会
     * 落到本方法。组件实例实现了 __toString / Stringable，可直接 echo 输出 HTML。
     *
     * @param  string  $alias    组件别名（见 $components 注册表，如 'card'、'dataTable'）
     * @param  array   $options  组件配置项（各组件的 defaults() 定义了可用键与默认值）
     * @return Component 组件实例
     *
     * @throws InvalidArgumentException 当别名未注册时抛出（错误信息中含全部可用别名）
     */
    public static function component(string $alias, array $options = []): Component
    {
        $class = self::$components[$alias] ?? null;
        // 大小写不敏感兜底：XfAdmin::datatable() 等驼峰写法差异不应导致致命错误
        if ($class === null) {
            foreach (self::$components as $name => $cls) {
                if (strcasecmp($name, $alias) === 0) {
                    $class = $cls;
                    break;
                }
            }
        }
        if ($class === null) {
            throw new InvalidArgumentException("XfAdmin: 未知组件 [{$alias}]，可用组件: " . implode(', ', array_keys(self::$components)));
        }
        return $class::make($options);
    }

    /**
     * 注册 / 覆盖自定义组件（开发者扩展点）
     *
     * - 传入新别名：注册新组件，之后即可用 XfAdmin::{$alias}([...]) 调用；
     * - 传入已存在的别名：覆盖内置实现（例如替换默认 Card 的渲染逻辑）。
     *
     * 自定义组件类必须继承 zxf\XfAdmin\Components\Component 抽象基类，
     * 并实现 defaults()（默认配置）与 html()（HTML 渲染）两个方法。
     *
     * @param  string  $alias           组件别名（建议小驼峰，如 'myWidget'）
     * @param  string  $componentClass  组件类完全限定名
     *
     * @throws InvalidArgumentException 当类未继承 Component 基类时抛出
     */
    public static function extend(string $alias, string $componentClass): void
    {
        if (! is_subclass_of($componentClass, Component::class)) {
            throw new InvalidArgumentException("XfAdmin: {$componentClass} 必须继承 " . Component::class);
        }
        self::$components[$alias] = $componentClass;
    }

    /**
     * 返回完整组件注册表
     *
     * 键为组件别名、值为组件类名（含 extend() 追加的自定义组件），
     * 可用于生成组件文档 / 演示目录，或做覆盖率校验。
     *
     * @return array<string, class-string<Component>> alias => class 映射
     */
    public static function componentList(): array
    {
        return self::$components;
    }

    /**
     * 判断某组件别名是否已注册（含 dateRange/clipboard 等同义别名）
     *
     * @param  string  $alias  组件别名
     * @return bool true 表示可通过 XfAdmin::{$alias}() 创建
     */
    public static function has(string $alias): bool
    {
        if (array_key_exists($alias, self::$components)) {
            return true;
        }
        // 与 component() 的大小写不敏感兜底保持一致：XfAdmin::datatable() 等驼峰差异也能命中
        foreach (self::$components as $name => $_cls) {
            if (strcasecmp($name, $alias) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 返回扩展包版本号（等同于 XfAdmin::VERSION 常量）
     *
     * @return string 语义化版本号，如 '1.0.0'
     */
    public static function version(): string
    {
        return self::VERSION;
    }

    /**
     * 获取静态资源管理器单例
     *
     * Assets 负责：CSS/JS 依赖收集与去重、资源 URL 拼接（含版本号）、
     * head()/scripts() 输出组装。组件在渲染时会自动向其注册所需插件资源。
     *
     * @return Assets 资源管理器单例
     */
    public static function assets(): Assets
    {
        return Assets::instance();
    }

    /**
     * 输出 <head> 内所需的全部标签（CSS 链接 + 主题配置内联脚本）
     *
     * 在自定义布局中于 </head> 前 echo 本方法返回值；
     * 使用 XfAdmin::page() 整页组件时无需手动调用（已内置）。
     *
     * @return string HTML 片段
     */
    public static function head(): string
    {
        return Assets::instance()->head();
    }

    /**
     * 输出 </body> 前所需的全部脚本（JS 文件 + 组件内联初始化代码）
     *
     * 必须在页面所有组件渲染完成后调用，否则后渲染组件的
     * 依赖脚本与初始化代码会缺失。
     *
     * @return string HTML 片段
     */
    public static function scripts(): string
    {
        return Assets::instance()->scripts();
    }

    /**
     * 将包内相对路径解析为可访问的资源 URL
     *
     * 例如 XfAdmin::asset('images/users/user-1.jpg')
     * => '/zxf/xfadmin/images/users/user-1.jpg?v=1.0.0'。
     * 对已含资源基址的路径具备幂等性（不会重复拼接）。
     *
     * @param  string  $path  相对 resources/assets/ 的路径
     * @return string 完整资源 URL（含版本参数）
     */
    public static function asset(string $path): string
    {
        return Assets::instance()->url($path);
    }

    /**
     * 解析图片地址（与 Component::img 行为一致，供静态上下文使用）。
     *
     * - 绝对地址（http(s)://、data:）原样返回，便于组件接收外链 / 内联图；
     * - 空字符串返回空；
     * - 其余按资源基址解析为 images/ 下的 URL。
     *
     * 组件内凡涉及【用户可配置的图片路径】，都应经本方法（或实例 $this->img()）
     * 解析，避免直接拼接 XfAdmin::asset('images/'.ltrim($path)) 导致外链 / data URI 失效。
     *
     * @param  string  $path
     * @return string
     */
    public static function img(string $path): string
    {
        if (preg_match('#^(https?:|data:)#i', $path)) {
            return $path;
        }
        if ($path === '') {
            // 空路径返回透明 1x1 GIF，避免组件输出 src="" 触发破图请求
            return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }
        return self::asset('images/' . ltrim($path, '/'));
    }

    /**
     * 生成 DataTables 服务端分页协议响应（搜索 / 过滤 / 排序 / 分页全由包内完成）
     *
     * 返回 {draw, recordsTotal, recordsFiltered, data} 结构，
     * 与 XfAdmin::dataTable(['ajax' => ...]) 前端组件直接对接。
     * 数据源既支持普通数组，也支持 Laravel 查询构造器（自动下推 where/limit）。
     *
     * 典型用法（Laravel 控制器）：
     *   return response()->json(XfAdmin::dataResponse($rows, request()->all(), [
     *       'searchable' => ['name', 'email'],                     // 全局搜索命中的列
     *       'filters'    => ['status', 'keyword' => ['name', 'email']], // 自定义过滤参数
     *   ]));
     *
     * @param  iterable|object  $rows     数组数据 / Laravel 查询构造器
     * @param  array            $params   请求参数（通常传 request()->all()，含 draw/start/length/order 等）
     * @param  array            $options  行为配置：searchable（可搜索列）、filters（过滤映射）等
     * @return array DataTables 协议数组（可直接 json 输出）
     */
    public static function dataResponse(iterable|object $rows, array $params = [], array $options = []): array
    {
        return Support\DataSet::response($rows, $params, $options);
    }

    /**
     * 魔术静态调用：把 XfAdmin::{alias}($options) 转发为组件工厂调用
     *
     * 例如 XfAdmin::card(['title' => 'x']) 等价于 XfAdmin::component('card', ['title' => 'x'])。
     * 全部可用别名见类顶部 @method 注解与 $components 注册表。
     *
     * @param  string  $name       组件别名
     * @param  array   $arguments  第一个元素为组件配置数组（可省略）
     * @return Component 组件实例（Stringable，可直接输出）
     *
     * @throws InvalidArgumentException 别名未注册时抛出
     */
    /** auth 语义别名 → 默认 type 映射（便捷方法自动注入） */
    private const AUTH_TYPE_ALIASES = [
        'signIn'       => 'sign-in',
        'signUp'       => 'sign-up',
        'resetPass'    => 'reset-pass',
        'newPass'      => 'new-pass',
        'twoFactor'    => 'two-factor',
        'lockScreen'   => 'lock-screen',
        'deleteAccount'=> 'delete-account',
        'successMail'  => 'success-mail',
        'loginPin'     => 'login-pin',
    ];

    /**
     * call Static（public静态方法）
     *
     * @param string $name name
     * @param array $arguments arguments
     *
     * @return Component result
     */
    public static function __callStatic(string $name, array $arguments): Component
    {
        $options = $arguments[0] ?? [];
        // auth 语义便捷别名：未显式指定 type 时自动注入默认 type
        if (isset(self::AUTH_TYPE_ALIASES[$name]) && ! isset($options['type'])) {
            $options['type'] = self::AUTH_TYPE_ALIASES[$name];
        }
        return self::component($name, $options);
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin;

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
 * @method static Components\Layout\Page       page(array $options = [])
 * @method static Components\Layout\Sidenav    sidenav(array $options = [])
 * @method static Components\Layout\Topbar     topbar(array $options = [])
 * @method static Components\Layout\TopNav     topNav(array $options = [])
 * @method static Components\Layout\PageTitle  pageTitle(array $options = [])
 * @method static Components\Layout\Footer     footer(array $options = [])
 * @method static Components\Layout\Customizer customizer(array $options = [])
 * @method static Components\Layout\AuthPage   authPage(array $options = [])
 * @method static Components\Layout\ErrorPage  errorPage(array $options = [])
 * @method static Components\Navigation\Menu   menu(array $options = [])
 * @method static Components\Grid\Row          row(array $options = [])
 * @method static Components\Grid\Col          col(array $options = [])
 * @method static Components\UI\Card           card(array $options = [])
 * @method static Components\UI\StatCard       statCard(array $options = [])
 * @method static Components\Table\Table       table(array $options = [])
 * @method static Components\Table\DataTable   dataTable(array $options = [])
 * @method static Components\Form\Form         form(array $options = [])
 * @method static Components\Form\Input        input(array $options = [])
 * @method static Components\Form\Textarea     textarea(array $options = [])
 * @method static Components\Form\Select       select(array $options = [])
 * @method static Components\Form\Check        check(array $options = [])
 * @method static Components\Form\Slider       slider(array $options = [])
 * @method static Components\Form\DateRangePicker dateRange(array $options = [])
 * @method static Components\Form\DateRangePicker dateRangePicker(array $options = [])
 * @method static Components\Form\Editor       editor(array $options = [])
 * @method static Components\Form\Upload       upload(array $options = [])
 * @method static Components\Form\ColorPicker  colorPicker(array $options = [])
 * @method static Components\Form\PasswordStrength passwordStrength(array $options = [])
 * @method static Components\Chart\ApexChart   apexChart(array $options = [])
 * @method static Components\Chart\ApexTree    apexTree(array $options = [])
 * @method static Components\Chart\ApexSankey  apexSankey(array $options = [])
 * @method static Components\Chart\EChart      echart(array $options = [])
 * @method static Components\Chart\VectorMap   vectorMap(array $options = [])
 * @method static Components\Chart\GoogleMap   googleMap(array $options = [])
 * @method static Components\Chart\LeafletMap   leafletMap(array $options = [])
 * @method static Components\UI\Alert          alert(array $options = [])
 * @method static Components\UI\Badge          badge(array $options = [])
 * @method static Components\UI\Button         button(array $options = [])
 * @method static Components\UI\Dropdown       dropdown(array $options = [])
 * @method static Components\UI\Modal          modal(array $options = [])
 * @method static Components\UI\Offcanvas      offcanvas(array $options = [])
 * @method static Components\UI\Tabs           tabs(array $options = [])
 * @method static Components\UI\Accordion      accordion(array $options = [])
 * @method static Components\UI\Progress       progress(array $options = [])
 * @method static Components\UI\Spinner        spinner(array $options = [])
 * @method static Components\UI\Pagination     pagination(array $options = [])
 * @method static Components\UI\ListGroup      listGroup(array $options = [])
 * @method static Components\UI\Avatar         avatar(array $options = [])
 * @method static Components\UI\Icon           icon(array $options = [])
 * @method static Components\UI\Toast          toast(array $options = [])
 * @method static Components\UI\Timeline       timeline(array $options = [])
 * @method static Components\UI\Carousel       carousel(array $options = [])
 * @method static Components\UI\Breadcrumb     breadcrumb(array $options = [])
 * @method static Components\Misc\Calendar     calendar(array $options = [])
 * @method static Components\Misc\TreeView     treeView(array $options = [])
 * @method static Components\Misc\Nestable     nestable(array $options = [])
 * @method static Components\Misc\Lightbox     lightbox(array $options = [])
 * @method static Components\Misc\Tour         tour(array $options = [])
 * @method static Components\Misc\ClipboardButton clipboard(array $options = [])
 * @method static Components\Misc\ClipboardButton clipboardButton(array $options = [])
 * @method static Components\Misc\SweetAlert   sweetAlert(array $options = [])
 * @method static Components\Misc\Raw          raw(array $options = [])
 * @method static Components\Misc\Tinycon      tinycon(array $options = [])
 * @method static Components\Misc\IdleTimer    idleTimer(array $options = [])
 * @method static Components\Misc\Animate      animate(array $options = [])
 * @method static Components\Misc\PdfViewer    pdfViewer(array $options = [])
 * @method static Components\Misc\TextDiff     textDiff(array $options = [])
 * @method static Components\Layout\ComingSoon  comingSoon(array $options = [])
 * @method static Components\Layout\Maintenance maintenance(array $options = [])
 * @method static Components\Layout\EmptyState  emptyState(array $options = [])
 * @method static Components\Layout\LockScreen  lockScreen(array $options = [])
 * @method static Components\Form\Tags          tags(array $options = [])
 * @method static Components\Form\MaskedInput   maskedInput(array $options = [])
 * @method static Components\Form\Wizard        wizard(array $options = [])
 * @method static Components\UI\Tooltip         tooltip(array $options = [])
 * @method static Components\UI\Popover         popover(array $options = [])
 * @method static Components\UI\Placeholder     placeholder(array $options = [])
 * @method static Components\UI\Collapse        collapse(array $options = [])
 * @method static Components\UI\Scrollspy       scrollspy(array $options = [])
 * @method static Components\UI\Ratio           ratio(array $options = [])
 * @method static Components\UI\Rating          rating(array $options = [])
 * @method static Components\UI\Ribbon          ribbon(array $options = [])
 * @method static Components\UI\Chip            chip(array $options = [])
 * @method static Components\UI\Stepper         stepper(array $options = [])
 * @method static Components\UI\DescriptionList descriptionList(array $options = [])
 * @method static Components\UI\Toggle        switch(array $options = [])
 * @method static Components\UI\CodeBlock     codeBlock(array $options = [])
 * @method static Components\UI\EmptyState    empty(array $options = [])
 * @method static Components\UI\Toolbar       toolbar(array $options = [])
 * @method static Components\UI\SearchBox     searchBox(array $options = [])
 * @method static Components\UI\LoadingButton loadingButton(array $options = [])
 * @method static Components\Data\PricingCard   pricingCard(array $options = [])
 * @method static Components\Data\Faq           faq(array $options = [])
 * @method static Components\Data\ProfileHeader profileHeader(array $options = [])
 * @method static Components\Data\ProductCard   productCard(array $options = [])
 * @method static Components\Data\Kanban        kanban(array $options = [])
 * @method static Components\Data\ChatBox       chatBox(array $options = [])
 * @method static Components\Data\InvoiceTable  invoiceTable(array $options = [])
 * @method static Components\Data\MailList      mailList(array $options = [])
 * @method static Components\Data\FileManager   fileManager(array $options = [])
 * @method static Components\Data\Widget        widget(array $options = [])
 * @method static Components\Data\ActivityFeed  activityFeed(array $options = [])
 * @method static Components\Data\Gallery       gallery(array $options = [])
 * @method static Components\Data\BlogList      blogList(array $options = [])
 * @method static Components\Data\InvoiceList   invoiceList(array $options = [])
 * @method static Components\Data\SearchResults  searchResults(array $options = [])
 * @method static Components\Data\PermissionMatrix permissionMatrix(array $options = [])
 * @method static Components\Data\ApiKeys       apiKeys(array $options = [])
 * @method static Components\Data\CommentThread commentThread(array $options = [])
 * @method static Components\Data\EmailCompose  emailCompose(array $options = [])
 * @method static Components\Data\Customers      customers(array $options = [])
 * @method static Components\Data\Orders         orders(array $options = [])
 * @method static Components\Data\OrderDetails   orderDetails(array $options = [])
 * @method static Components\Data\ProductDetails productDetails(array $options = [])
 * @method static Components\Data\Projects       projects(array $options = [])
 * @method static Components\Data\ProjectDetails projectDetails(array $options = [])
 * @method static Components\Data\Outlook        outlook(array $options = [])
 * @method static Components\Data\ForumThread    forumThread(array $options = [])
 * @method static Components\Data\BlogArticle    blogArticle(array $options = [])
 * @method static Components\Data\Roles          roles(array $options = [])
 * @method static Components\Data\InvoiceCreate  invoiceCreate(array $options = [])
 * @method static Components\Data\TeamMember     teamMember(array $options = [])
 * @method static Components\Data\Testimonial    testimonial(array $options = [])
 * @method static Components\Data\TodoList       todoList(array $options = [])
 * @method static Components\Data\IssueTracker   issueTracker(array $options = [])
 * @method static Components\Data\VoteList       voteList(array $options = [])
 * @method static Components\Data\MetricCard     metricCard(array $options = [])
 * @method static Components\Data\Terms          terms(array $options = [])
 * @method static Components\Data\ContactCard    contactCard(array $options = [])
 * @method static Components\Data\CompanyCard    companyCard(array $options = [])
 * @method static Components\Data\Clients         clients(array $options = [])
 * @method static Components\Data\Sellers         sellers(array $options = [])
 * @method static Components\Data\ReviewList      reviewList(array $options = [])
 * @method static Components\Data\ProjectTeamBoard projectTeamBoard(array $options = [])
 * @method static Components\Data\EmailApp       emailApp(array $options = [])
 * @method static Components\Data\ChatApp        chatApp(array $options = [])
 * @method static Components\Data\ProfilePage    profilePage(array $options = [])
 * @method static Components\Data\InvoiceDetail  invoiceDetail(array $options = [])
 * @method static Components\Data\Companies      companies(array $options = [])
 * @method static Components\Data\ProductCategories productCategories(array $options = [])
 * @method static Components\Data\ProductAdd     productAdd(array $options = [])
 * @method static Components\Data\SellerDetails  sellerDetails(array $options = [])
 * @method static Components\Data\Article        article(array $options = [])
 * @method static Components\Data\ProjectActivity projectActivity(array $options = [])
 * @method static Components\Misc\PinBoard       pinBoard(array $options = [])
 * @method static Components\Misc\Masonry        masonry(array $options = [])
 * @method static Components\Layout\Landing      landing(array $options = [])
 */
final class XfAdmin
{
    /** 扩展包版本号（与 config/xfadmin.php 的 version 同步，用于运行时自检） */
    public const VERSION = '1.0.0';

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
        // 表单
        'form'        => Components\Form\Form::class,
        'input'       => Components\Form\Input::class,
        'textarea'    => Components\Form\Textarea::class,
        'select'      => Components\Form\Select::class,
        'check'       => Components\Form\Check::class,
        'slider'      => Components\Form\Slider::class,
        'dateRange'   => Components\Form\DateRangePicker::class,
        'dateRangePicker' => Components\Form\DateRangePicker::class,
        'editor'      => Components\Form\Editor::class,
        'upload'      => Components\Form\Upload::class,
        'colorPicker' => Components\Form\ColorPicker::class,
        'tags'        => Components\Form\Tags::class,
        'maskedInput' => Components\Form\MaskedInput::class,
        'wizard'      => Components\Form\Wizard::class,
        'passwordStrength' => Components\Form\PasswordStrength::class,
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
    public static function __callStatic(string $name, array $arguments): Component
    {
        return self::component($name, $arguments[0] ?? []);
    }
}

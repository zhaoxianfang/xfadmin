<?php

declare(strict_types=1);

namespace XfAdmin;

use InvalidArgumentException;
use XfAdmin\Assets\Assets;
use XfAdmin\Components\Component;

/**
 * XfAdmin 组件工厂 / 全局配置入口
 *
 * 用法：
 *   XfAdmin::config(require 'config/xfadmin.php');
 *   echo XfAdmin::card(['title' => '标题', 'body' => '内容']);
 *   echo XfAdmin::dataTable(['columns' => [...], 'data' => [...]]);
 *   echo XfAdmin::head();     // <head> 内输出
 *   echo XfAdmin::scripts();  // </body> 前输出
 *
 * @method static Components\Layout\Page       page(array $options = [])
 * @method static Components\Layout\Sidenav    sidenav(array $options = [])
 * @method static Components\Layout\Topbar     topbar(array $options = [])
 * @method static Components\Layout\TopNav     topnav(array $options = [])
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
 * @method static Components\Chart\EChart      echart(array $options = [])
 * @method static Components\Chart\VectorMap   vectorMap(array $options = [])
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
        'echart'      => Components\Chart\EChart::class,
        'vectorMap'   => Components\Chart\VectorMap::class,
        'leafletMap'  => Components\Chart\LeafletMap::class,
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
        'pdfViewer'   => Components\Misc\PdfViewer::class,
        'textDiff'    => Components\Misc\TextDiff::class,
    ];

    /** 全局配置（theme/brand/footer 等，Page 等组件的默认值来源） */
    private static array $config = [];

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

    public static function setting(string $key, mixed $default = null): mixed
    {
        return Support\Html::get(self::$config, $key, $default);
    }

    /** 创建组件实例 */
    public static function component(string $alias, array $options = []): Component
    {
        $class = self::$components[$alias] ?? null;
        if ($class === null) {
            throw new InvalidArgumentException("XfAdmin: 未知组件 [{$alias}]，可用组件: " . implode(', ', array_keys(self::$components)));
        }

        return $class::make($options);
    }

    /** 开发者注册/覆盖自定义组件 */
    public static function extend(string $alias, string $componentClass): void
    {
        if (! is_subclass_of($componentClass, Component::class)) {
            throw new InvalidArgumentException("XfAdmin: {$componentClass} 必须继承 " . Component::class);
        }
        self::$components[$alias] = $componentClass;
    }

    public static function componentList(): array
    {
        return self::$components;
    }

    /** 判断某组件别名是否已注册（含别名） */
    public static function has(string $alias): bool
    {
        return array_key_exists($alias, self::$components);
    }

    /** 返回扩展包版本号 */
    public static function version(): string
    {
        return self::VERSION;
    }

    public static function assets(): Assets
    {
        return Assets::instance();
    }

    /** <head> 内输出（CSS + 主题配置） */
    public static function head(): string
    {
        return Assets::instance()->head();
    }

    /** </body> 前输出（JS + 内联初始化） */
    public static function scripts(): string
    {
        return Assets::instance()->scripts();
    }

    /** 资源 URL */
    public static function asset(string $path): string
    {
        return Assets::instance()->url($path);
    }

    public static function __callStatic(string $name, array $arguments): Component
    {
        return self::component($name, $arguments[0] ?? []);
    }
}

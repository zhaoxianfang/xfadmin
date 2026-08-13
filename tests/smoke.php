<?php

declare(strict_types=1);

/**
 * 冒烟测试：无框架环境下渲染全部组件（php tests/smoke.php）
 */

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'zxf\XfAdmin\\')) {
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});
require __DIR__ . '/../src/helpers.php';

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

XfAdmin::config(require __DIR__ . '/../config/xfadmin.php');

$failures = [];
$check = function (string $name, callable $fn) use (&$failures): void {
    try {
        $html = (string) $fn();
        if (trim($html) === '') {
            $failures[] = "$name: 输出为空";
        } else {
            echo "  ✔ {$name} (" . strlen($html) . " bytes)\n";
        }
    } catch (Throwable $e) {
        $failures[] = "$name: {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}";
    }
};

echo "== 组件渲染 ==\n";
$menu = [
    ['title' => '导航'],
    ['text' => '仪表盘', 'icon' => 'ti ti-home', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
    ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
        ['text' => '用户', 'url' => '/users'],
        ['text' => '更多', 'children' => [['text' => '深层', 'url' => '/deep', 'children' => [['text' => '四级', 'url' => '/l4']]]]],
    ]],
];

$check('menu(side)', fn () => XfAdmin::menu(['items' => $menu, 'current_url' => '/users']));
$check('menu(top)', fn () => XfAdmin::menu(['mode' => 'top', 'items' => $menu]));
$check('sidenav', fn () => XfAdmin::sidenav(['menu' => $menu, 'user' => ['name' => '张三', 'role' => '管理员']]));
$check('topbar', fn () => XfAdmin::topbar(['user' => ['name' => '张三', 'items' => [['text' => '退出', 'url' => '/logout']]], 'notifications' => ['items' => [['title' => '系统', 'text' => '有新订单', 'icon' => 'ti ti-bell']]]]));
$check('pageTitle', fn () => XfAdmin::pageTitle(['title' => '用户', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '用户']]]));
$check('footer', fn () => XfAdmin::footer());
$check('customizer', fn () => XfAdmin::customizer());
$check('row/col', fn () => XfAdmin::row(['gutter' => 3, 'cols' => [
    ['width' => ['md' => 6], 'content' => XfAdmin::card(['title' => 'A', 'body' => 'a'])],
    XfAdmin::card(['title' => 'B', 'body' => 'b']),
]]));
$check('card', fn () => XfAdmin::card(['title' => '卡片', 'tools' => ['collapse', 'refresh', 'close'], 'body' => '内容', 'footer' => 'foot']));
$check('statCard', fn () => XfAdmin::statCard(['title' => '用户数', 'counter' => 1234, 'icon' => 'ti ti-users', 'trend' => ['text' => '+5%', 'direction' => 'up']]));
$check('table', fn () => XfAdmin::table([
    'columns' => ['name' => '姓名', 'op' => ['label' => '操作', 'format' => fn ($r) => '<a href="#' . $r['id'] . '">编辑</a>', 'raw' => true]],
    'data'    => [['id' => 1, 'name' => '张<b>三</b>']],
    'striped' => true, 'hover' => true,
]));
$check('dataTable', fn () => XfAdmin::dataTable([
    'columns' => [
        'id' => 'ID', 'name' => '姓名',
        'status' => ['label' => '状态', 'badges' => ['启用' => 'success']],
        'op' => ['label' => '操作', 'template' => '<a href="/u/{id}">编辑</a>'],
    ],
    'data' => [['id' => 1, 'name' => '张三', 'status' => '启用']],
    'buttons' => ['copy', 'excel', 'pdf', 'print'],
    'select' => true, 'fixed_header' => true, 'column_filters' => true,
]));
$check('form+fields', fn () => XfAdmin::form([
    'action' => '/save', 'method' => 'PUT', 'validation' => true, 'ajax' => true,
    'csrf' => ['_token' => 'abc"<>'],
    'fields' => [
        XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true, 'prepend' => '@']),
        XfAdmin::input(['name' => 'phone', 'label' => '电话', 'mask' => '999-9999']),
        XfAdmin::textarea(['name' => 'remark', 'label' => '备注']),
        XfAdmin::select(['name' => 'city', 'label' => '城市', 'options' => ['bj' => '北京'], 'enhance' => 'choices', 'multiple' => true]),
        XfAdmin::check(['type' => 'switch', 'name' => 'on', 'label' => '启用', 'checked' => true]),
        XfAdmin::check(['type' => 'radio', 'name' => 'g', 'options' => ['m' => '男', 'f' => '女'], 'value' => 'f', 'inline' => true]),
        XfAdmin::slider(['name' => 'range', 'label' => '区间', 'min' => 0, 'max' => 100, 'value' => [20, 80], 'tooltips' => true]),
        XfAdmin::dateRange(['name' => 'date', 'label' => '日期', 'ranges' => true]),
        XfAdmin::editor(['name' => 'content', 'label' => '正文']),
        XfAdmin::upload(['driver' => 'dropzone', 'url' => '/upload']),
        XfAdmin::colorPicker(['name' => 'color', 'label' => '颜色', 'value' => '#ff0000']),
    ],
    'buttons' => '<button type="submit" class="btn btn-primary">提交</button>',
]));
$check('apexChart', fn () => XfAdmin::apexChart(['type' => 'donut', 'series' => [10, 20], 'labels' => ['A', 'B']]));
$check('echart', fn () => XfAdmin::echart(['options' => ['series' => [['type' => 'bar', 'data' => [1, 2]]]]]));
$check('vectorMap', fn () => XfAdmin::vectorMap(['markers' => [['name' => 'BJ', 'coords' => [39.9, 116.4]]]]));
$check('alert', fn () => XfAdmin::alert(['variant' => 'success', 'text' => 'OK', 'dismissible' => true, 'icon' => 'ti ti-check']));
$check('badge', fn () => XfAdmin::badge(['text' => '新', 'soft' => true, 'pill' => true]));
$check('button', fn () => XfAdmin::button(['text' => '保存', 'ladda' => true, 'icon' => 'ti ti-check']));
$check('dropdown', fn () => XfAdmin::dropdown(['text' => '操作', 'items' => [['text' => '编辑'], ['divider' => true], ['header' => '危险'], ['text' => '删除', 'class' => 'text-danger']]]));
$check('modal', fn () => XfAdmin::modal(['title' => '弹窗', 'body' => 'hi', 'trigger' => '打开', 'size' => 'lg', 'centered' => true, 'static' => true]));
$check('offcanvas', fn () => XfAdmin::offcanvas(['title' => '抽屉', 'body' => 'hi', 'trigger' => '打开']));
$check('tabs', fn () => XfAdmin::tabs(['items' => [['title' => 'A', 'content' => 'a'], ['title' => 'B', 'content' => 'b']], 'vertical' => true]));
$check('accordion', fn () => XfAdmin::accordion(['items' => [['title' => 'A', 'content' => 'a', 'open' => true], ['title' => 'B', 'content' => 'b']]]));
$check('progress', fn () => XfAdmin::progress(['bars' => [['value' => 30, 'variant' => 'success'], ['value' => 20, 'variant' => 'warning']], 'height' => 10]));
$check('spinner', fn () => XfAdmin::spinner(['spinkit' => 'wave']));
$check('pagination', fn () => XfAdmin::pagination(['total' => 500, 'per_page' => 10, 'current' => 7, 'url' => '/u?page={page}']));
$check('listGroup', fn () => XfAdmin::listGroup(['items' => [['text' => 'A', 'active' => true], ['text' => 'B', 'badge' => '3', 'url' => '/b']]]));
$check('avatar', fn () => XfAdmin::avatar(['group' => [['text' => 'ZS'], ['src' => '/a.jpg'], ['text' => '+5', 'variant' => 'info']]]));
$check('icon', fn () => XfAdmin::icon(['name' => 'home', 'size' => 'fs-24']));
$check('toast', fn () => XfAdmin::toast(['title' => '通知', 'body' => '成功', 'variant' => 'success', 'placement' => 'top-0 end-0']));
$check('timeline', fn () => XfAdmin::timeline(['items' => [['time' => '9:00', 'title' => '开始', 'text' => 'x']]]));
$check('carousel', fn () => XfAdmin::carousel(['items' => [['image' => '/1.jpg', 'caption' => '<h5>t</h5>'], ['image' => '/2.jpg']], 'indicators' => true]));
$check('breadcrumb', fn () => XfAdmin::breadcrumb(['items' => [['text' => '首页', 'url' => '/'], '列表'], 'divider' => '>']));
$check('calendar', fn () => XfAdmin::calendar(['events' => [['title' => '会议', 'start' => '2026-07-23']]]));
$check('treeView', fn () => XfAdmin::treeView(['data' => [['text' => '根', 'children' => [['text' => '子']]]], 'checkbox' => true]));
$check('nestable', fn () => XfAdmin::nestable(['items' => ['一', '二'], 'handle' => true, 'input' => 'sort']));
$check('lightbox', fn () => XfAdmin::lightbox(['images' => ['/1.jpg', ['src' => '/2.jpg', 'title' => '图']]]));
$check('tour', fn () => XfAdmin::tour(['steps' => [['target' => '#a', 'title' => 't', 'content' => 'c']], 'auto' => true]));
$check('clipboard', fn () => XfAdmin::clipboard(['text' => 'hello']));
$check('sweetAlert', fn () => XfAdmin::sweetAlert(['trigger' => '删除', 'title' => '确定？', 'icon' => 'warning', 'cancel_text' => '取消', 'confirm_url' => '/del']));
$check('raw', fn () => XfAdmin::raw(['html' => '<div>x</div>', 'plugins' => ['animate'], 'js' => 'console.log(1)', 'js_key' => 'demo']));

echo "\n== 新增组件渲染 ==\n";
$check('tooltip', fn () => XfAdmin::tooltip(['text' => '按钮', 'title' => '提示', 'placement' => 'top']));
$check('popover', fn () => XfAdmin::popover(['text' => '点我', 'title' => 't', 'content' => 'c', 'trigger' => 'click']));
$check('placeholder', fn () => XfAdmin::placeholder(['lines' => [12, 8, 6], 'animation' => 'wave']));
$check('collapse', fn () => XfAdmin::collapse(['trigger' => '切换', 'body' => '内容', 'open' => true]));
$check('scrollspy', fn () => XfAdmin::scrollspy(['items' => [['id' => 's1', 'label' => '一', 'content' => 'a'], ['id' => 's2', 'label' => '二', 'content' => 'b']]]));
$check('ratio', fn () => XfAdmin::ratio(['ratio' => '16x9', 'src' => 'https://x/y', 'type' => 'iframe']));
$check('rating', fn () => XfAdmin::rating(['value' => 3.5, 'count' => 128, 'show_value' => true]));
$check('ribbon', fn () => XfAdmin::ribbon(['text' => '推荐', 'variant' => 'danger', 'body' => '内容']));
$check('chip', fn () => XfAdmin::chip(['label' => '张三', 'dismissible' => true, 'icon' => 'ti ti-user']));
$check('stepper', fn () => XfAdmin::stepper(['steps' => [['title' => '下单', 'status' => 'done'], ['title' => '发货', 'status' => 'active'], ['title' => '签收']]]));
$check('descriptionList', fn () => XfAdmin::descriptionList(['items' => ['订单号' => '#1', '状态' => XfAdmin::badge(['text' => 'OK', 'variant' => 'success'])]]));
$check('pricingCard', fn () => XfAdmin::pricingCard(['name' => '专业版', 'price' => '¥199', 'featured' => true, 'features' => [['text' => '10 项目', 'enabled' => true], ['text' => 'API', 'enabled' => false]]]));
$check('faq', fn () => XfAdmin::faq(['items' => [['q' => '如何注册？', 'a' => '点击注册'], ['q' => '如何退款？', 'a' => '联系客服']]]));
$check('profileHeader', fn () => XfAdmin::profileHeader(['name' => '张三', 'role' => '工程师', 'stats' => [['label' => '粉丝', 'value' => '13k']], 'tabs' => [['label' => '概览', 'active' => true]]]));
$check('productCard', fn () => XfAdmin::productCard(['title' => '运动鞋', 'price' => '¥299', 'old_price' => '¥399', 'rating' => 4.5, 'rating_count' => 88, 'badge' => ['text' => '热销', 'variant' => 'danger']]));
$check('kanban', fn () => XfAdmin::kanban(['columns' => [['id' => 'todo', 'title' => '待办', 'cards' => [['title' => '设计', 'text' => 'x', 'members' => ['users/avatar-1.jpg'], 'meta' => ['comments' => 3]]]]]]));
$check('chatBox', fn () => XfAdmin::chatBox(['title' => '张三', 'status' => '在线', 'messages' => [['from' => 'them', 'text' => 'hi', 'time' => '10:00'], ['from' => 'me', 'text' => 'hello']]]));
$check('invoiceTable', fn () => XfAdmin::invoiceTable(['items' => [['name' => '产品A', 'qty' => 2, 'price' => 100]], 'summary' => [['label' => '合计', 'value' => 200, 'strong' => true]]]));
$check('mailList', fn () => XfAdmin::mailList(['items' => [['from' => '张三', 'subject' => '通知', 'excerpt' => 'x', 'time' => '10:30', 'unread' => true, 'starred' => true]]]));
$check('fileManager', fn () => XfAdmin::fileManager(['files' => [['name' => '文档.pdf', 'type' => 'pdf', 'size' => '2 MB'], ['name' => '图片', 'type' => 'folder', 'meta' => '32 文件']]]));
$check('widget', fn () => XfAdmin::widget(['title' => '营收', 'value' => '¥5.2万', 'icon' => 'ti ti-cash', 'trend' => ['value' => '8%', 'up' => true, 'text' => '较上周'], 'style' => 'progress', 'progress' => 72]));
$check('activityFeed', fn () => XfAdmin::activityFeed(['items' => [['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'action' => '评论', 'target' => '任务', 'time' => '2h', 'text' => '不错'], ['icon' => 'ti ti-check', 'variant' => 'success', 'user' => '系统', 'action' => '部署', 'time' => '昨天']]]));
$check('gallery', fn () => XfAdmin::gallery(['items' => [['src' => '01.jpg', 'title' => 'A', 'group' => 'design'], ['src' => '02.jpg', 'title' => 'B', 'group' => 'photo']], 'filter' => ['all' => '全部', 'design' => '设计']]));
$check('blogList', fn () => XfAdmin::blogList(['items' => [['image' => 'b1.jpg', 'category' => '技术', 'title' => '文章', 'excerpt' => '摘要', 'author' => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg'], 'date' => '2026-07-01', 'tags' => ['Laravel']]], 'layout' => 'grid']));
$check('invoiceList', fn () => XfAdmin::invoiceList(['items' => [['id' => 'INV-001', 'client' => '甲方', 'amount' => 12800, 'status' => 'paid', 'issued' => '2026-07-01', 'due' => '2026-07-15', 'actions' => '<a class="btn btn-sm btn-soft-primary">查看</a>']], 'summary' => [['label' => '总数', 'value' => 1, 'variant' => 'primary']]]));
$check('passwordStrength', fn () => XfAdmin::passwordStrength(['name' => 'pwd', 'label' => '密码', 'showRules' => true, 'minScore' => 2]));
$check('loadingButton', fn () => XfAdmin::loadingButton(['text' => '保存', 'variant' => 'success', 'driver' => 'spinner']));
$check('lockScreen', fn () => XfAdmin::lockScreen(['user' => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg']]));
$check('leafletMap', fn () => XfAdmin::leafletMap(['markers' => [['lat' => 39.9, 'lng' => 116.4, 'title' => '总部']], 'tiles' => null]));
$check('tinycon', fn () => XfAdmin::tinycon(['count' => 3]));
$check('idleTimer', fn () => XfAdmin::idleTimer(['timeout' => 60, 'onIdleUrl' => '/lock']));
$check('pdfViewer', fn () => XfAdmin::pdfViewer(['url' => '/files/a.pdf', 'height' => 400]));
$check('textDiff', fn () => XfAdmin::textDiff(['old' => "line1\nline2", 'new' => "line1\nline2 edited", 'mode' => 'inline']));
$check('searchResults', fn () => XfAdmin::searchResults(['query' => 'laravel', 'count' => 2, 'items' => [['title' => '文档', 'excerpt' => 'x', 'url' => '#']], 'filters' => ['全部' => 2, '文档' => 1]]));
$check('permissionMatrix', fn () => XfAdmin::permissionMatrix(['roles' => ['admin' => '管理员'], 'groups' => ['用户' => ['user.view' => '查看']], 'values' => ['admin' => ['user.view']]]));
$check('apiKeys', fn () => XfAdmin::apiKeys(['items' => [['name' => '生产', 'key' => 'sk-live-xxxx', 'created' => '2026-01-01', 'last_used' => '2天前']]]));
$check('commentThread', fn () => XfAdmin::commentThread(['items' => [['user' => '张三', 'text' => '好', 'replies' => [['user' => '李四', 'text' => '同意']]]]]));
$check('emailCompose', fn () => XfAdmin::emailCompose(['to' => 'a@b.com', 'subject' => 'hi', 'editor' => 'quill']));
$check('tags', fn () => XfAdmin::tags(['name' => 'tags', 'label' => '标签', 'value' => ['php', 'laravel'], 'whitelist' => ['php', 'vue'], 'max' => 5]));
$check('maskedInput', fn () => XfAdmin::maskedInput(['name' => 'phone', 'label' => '手机', 'mask' => '999-9999-9999']));
$check('wizard', fn () => XfAdmin::wizard(['steps' => [['title' => '账户', 'icon' => 'ti ti-user', 'content' => 'a'], ['title' => '完成', 'content' => 'b']], 'vertical' => false]));
$check('comingSoon', fn () => XfAdmin::comingSoon(['heading' => '即将上线', 'deadline' => '2026-12-31 00:00:00']));
$check('maintenance', fn () => XfAdmin::maintenance(['heading' => '维护中', 'contact' => 'a@b.com']));
$check('emptyState', fn () => XfAdmin::emptyState(['title' => '暂无数据', 'text' => '还没有记录', 'action' => '<a class="btn btn-primary">新建</a>']));
$check('emailApp', fn () => XfAdmin::emailApp([
    'folders'  => [['icon' => 'ti ti-inbox', 'text' => '收件箱', 'badge' => '3', 'active' => true]],
    'labels'   => [['text' => '工作', 'color' => 'primary']],
    'messages' => [['from' => '张伟', 'avatar' => 'users/user-1.jpg', 'subject' => '季度报表', 'preview' => '附件…', 'time' => '09:24', 'unread' => true]],
    'message'  => ['subject' => '季度报表', 'from' => ['name' => '张伟', 'email' => 'a@b.com', 'avatar' => 'users/user-1.jpg', 'time' => '09:24'], 'body' => '<p>正文</p>', 'attachments' => [['name' => '报表.xlsx', 'size' => '1.2 MB']]],
]));
$check('chatApp', fn () => XfAdmin::chatApp([
    'conversations' => [['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'last' => '好的', 'time' => '10:02', 'unread' => 2, 'active' => true, 'online' => true]],
    'peer'          => ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'online' => true, 'status' => '在线'],
    'messages'      => [['from' => 'other', 'text' => '你好', 'time' => '09:58'], ['from' => 'me', 'text' => '在的', 'time' => '09:59', 'avatar' => 'users/user-1.jpg']],
]));
$check('profilePage', fn () => XfAdmin::profilePage([
    'avatar' => 'users/user-1.jpg', 'name' => '张伟', 'verified' => true, 'role' => '产品经理',
    'meta'   => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
    'stats'  => [['value' => '128', 'label' => '项目']],
    'actions' => [['text' => '关注', 'class' => 'btn-primary', 'icon' => 'ti ti-user-plus']],
    'tabs'   => [['title' => '动态', 'content' => '<p>a</p>', 'active' => true], ['title' => '设置', 'content' => '<p>b</p>']],
]));
$check('companies', fn () => XfAdmin::companies([
    'companies' => [[
        'name' => '亚马逊', 'logo' => 'sellers/1.png', 'website' => 'www.amazon.com',
        'badges' => [['icon' => 'ti ti-map-pin', 'text' => '西雅图', 'color' => 'primary']],
        'desc' => '全球领先的电商与云计算公司。', 'employees' => '150 万+', 'revenue' => '$514B', 'rating' => 4,
    ]],
]));
$check('productCategories', fn () => XfAdmin::productCategories([
    'categories' => [[
        'name' => '家具家居', 'image' => 'products/1.png', 'slug' => 'furniture',
        'products' => 5248, 'orders' => '95.6k', 'earnings' => '¥4,050 万',
        'modified' => '2026-04-18 12:24', 'status' => ['text' => '启用', 'variant' => 'success'],
    ]],
]));
$check('productAdd', fn () => XfAdmin::productAdd([
    'brands' => ['Apple', 'Samsung'], 'categories' => ['手机数码'],
    'tags' => ['新品'], 'values' => ['name' => 'iPhone 17', 'sku' => 'IP17-001', 'stock' => 100],
]));
$check('sellerDetails', fn () => XfAdmin::sellerDetails([
    'seller' => ['name' => '极物优选旗舰店', 'logo' => 'sellers/1.png', 'verified' => true, 'rating' => 4.8,
        'desc' => '专注数码周边十年。', 'meta' => [['icon' => 'ti ti-map-pin', 'text' => '深圳']]],
    'stats' => [['label' => '总销售额', 'value' => '¥128 万', 'icon' => 'ti ti-currency-yen', 'color' => 'primary']],
    'products' => [['name' => '蓝牙耳机', 'image' => 'products/2.png', 'price' => '¥299', 'stock' => 320, 'sales' => '1.2k', 'status' => ['text' => '在售', 'variant' => 'success']]],
]));
$check('article', fn () => XfAdmin::article([
    'article' => [
        'title' => '如何构建高可用的后台管理系统', 'category' => '架构', 'date' => '2026-07-20', 'read_time' => '8 分钟',
        'author' => ['name' => '张三', 'avatar' => 'users/user-1.jpg', 'bio' => '资深后端工程师'],
        'cover' => 'blog/blog-post.jpg', 'body' => ['第一段正文。', '第二段正文。'], 'quote' => '架构的优雅在于取舍。',
        'tags' => ['PHP', 'Laravel'],
        'related' => [['title' => '缓存设计', 'excerpt' => '摘要', 'image' => 'blog/blog-1.jpg', 'date' => '2026-07-10']],
    ],
]));
$check('projectActivity', fn () => XfAdmin::projectActivity([
    'title' => '本周动态',
    'items' => [
        ['user' => '张三', 'avatar' => 'users/user-1.jpg', 'title' => '创建了任务', 'desc' => '说明', 'time' => '10 分钟前', 'color' => 'primary'],
        ['user' => '李四', 'avatar' => 'users/user-2.jpg', 'title' => '提交了代码', 'time' => '1 小时前', 'color' => 'success'],
    ],
]));
$check('invoiceDetail', fn () => XfAdmin::invoiceDetail([
    'title' => 'XF Admin', 'number' => 'INV-2026-0728', 'status' => ['text' => '待付款', 'color' => 'warning'],
    'meta'  => [['label' => '开票日期', 'value' => '2026-07-28']],
    'from'  => ['name' => '甲公司', 'lines' => ['地址1']], 'to' => ['name' => '乙公司', 'lines' => ['地址2']],
    'items' => [['name' => '企业版授权', 'desc' => '1 年', 'qty' => 2, 'price' => 4999]],
    'summary' => [['label' => '小计', 'value' => '¥9,998.00'], ['label' => '合计', 'value' => '¥11,097.78', 'strong' => true]],
    'notes' => '15 日内付款', 'actions' => [['text' => '打印', 'icon' => 'ti ti-printer', 'onclick' => 'window.print()']],
]));

echo "\n== 嵌套互不干扰 ==\n";
$nested = (string) XfAdmin::card([
    'title' => '嵌套',
    'body'  => XfAdmin::row(['cols' => [
        ['width' => ['md' => 6], 'content' => XfAdmin::tabs(['items' => [
            ['title' => '表单', 'content' => XfAdmin::form(['fields' => [XfAdmin::tags(['name' => 't', 'value' => ['a']])]])],
            ['title' => '看板', 'content' => XfAdmin::kanban(['columns' => [['id' => 'c1', 'title' => 'A', 'cards' => []]]])],
        ]])],
        ['width' => ['md' => 6], 'content' => XfAdmin::accordion(['items' => [['title' => 'FAQ', 'content' => XfAdmin::faq(['items' => [['q' => 'q', 'a' => 'a']]])]]])],
    ]]),
]);
if (trim($nested) === '') {
    $failures[] = '嵌套渲染: 输出为空';
} else {
    echo "  ✔ 嵌套 card>row>tabs(form/kanban)+accordion>faq (" . strlen($nested) . " bytes)\n";
}

echo "\n== 资源去重 ==\n";
Assets::reset();
XfAdmin::config(['assets_url' => '/zxf/xfadmin', 'version' => XfAdmin::VERSION]);
// 同一组件渲染 3 次 + 多组件共用插件
XfAdmin::dataTable(['columns' => ['a' => 'A'], 'data' => []])->render();
XfAdmin::dataTable(['columns' => ['a' => 'A'], 'data' => []])->render();
XfAdmin::dataTable(['columns' => ['a' => 'A'], 'data' => [], 'buttons' => ['pdf']])->render();
XfAdmin::select(['options' => [], 'enhance' => 'select2'])->render();   // 依赖 jquery
XfAdmin::treeView(['data' => []])->render();                            // 依赖 jquery
$js  = Assets::instance()->jsFiles();
$css = Assets::instance()->cssFiles();
$dupJs  = array_diff_assoc($js, array_unique($js));
$assert = function (bool $cond, string $msg) use (&$failures): void {
    if ($cond) {
        echo "  ✔ {$msg}\n";
    } else {
        $failures[] = $msg;
    }
};
$assert($dupJs === [], 'JS 无重复');
$assert(count(array_filter($js, fn ($f) => str_contains($f, 'jquery'))) === 1, 'jquery 仅加载一次');
$assert(count(array_filter($js, fn ($f) => str_contains($f, 'dataTables.min.js'))) === 1, 'datatables 仅加载一次（渲染3个表格）');
$assert(in_array('plugins/datatables/pdfmake.min.js', $js, true), 'pdf 按钮附加 pdfmake');
$head    = Assets::instance()->head();
$scripts = Assets::instance()->scripts();
$assert(substr_count($head, 'vendors.min.css') === 1, 'head 含 vendors.min.css 一次');
$assert(substr_count($scripts, 'xfadmin.js') === 1, 'scripts 含 xfadmin.js 一次');
$assert(str_contains($head, '?v=' . XfAdmin::VERSION), '版本号附加');

// 兜底：head() 先于组件渲染时，渲染期注册的插件 CSS 不应丢失（修复样式丢失）
Assets::reset();
XfAdmin::config(['assets_url' => '/zxf/xfadmin', 'version' => XfAdmin::VERSION]);
$headLate    = Assets::instance()->head(); // 此时尚无任何插件 CSS
XfAdmin::dataTable(['columns' => [['key' => 'a', 'title' => 'A']], 'data' => []])->render();
$scriptsLate = Assets::instance()->scripts();
$assert(str_contains($scriptsLate, 'datatables'), 'head() 之后渲染的 dataTable 插件 CSS 经 scripts() 兜底输出（修复样式丢失）');
$assert(! str_contains($headLate, 'datatables'), 'head() 先于渲染时插件 CSS 不在 head（验证兜底路径生效）');

echo "\n== 整页渲染 ==\n";
Assets::reset();
XfAdmin::config(['assets_url' => '/zxf/xfadmin']);
$check('page(vertical)', fn () => XfAdmin::page([
    'title'      => '仪表盘',
    'menu'       => $menu,
    'topbar'     => ['user' => ['name' => '张三']],
    'page_title' => ['title' => '仪表盘', 'breadcrumb' => [['text' => '首页']]],
    'content'    => [
        XfAdmin::row(['cols' => [XfAdmin::statCard(['title' => 'X', 'value' => '1'])]]),
        XfAdmin::card(['title' => '图表', 'body' => XfAdmin::apexChart(['series' => [['data' => [1, 2]]]])]),
    ],
]));
$pageHtml = (string) XfAdmin::page(['title' => 't', 'menu' => $menu, 'layout' => 'vertical', 'content' => XfAdmin::dataTable(['columns' => ['a' => 'A']])]);
$assert(str_contains($pageHtml, 'dataTables.min.js'), '页面自动包含组件依赖 JS');
$assert(str_contains($pageHtml, 'sidenav-menu'), 'vertical 布局渲染 sidenav');
$check('page(boxed+dark)', fn () => XfAdmin::page(['title' => 't', 'theme' => ['mode' => 'dark', 'layout_width' => 'boxed', 'sidenav_size' => 'compact'], 'menu' => $menu, 'content' => 'x']));
$check('authPage', fn () => XfAdmin::authPage(['title' => '登录', 'heading' => '欢迎', 'content' => XfAdmin::form(['fields' => [XfAdmin::input(['name' => 'u', 'label' => '账号'])]])]));
$check('errorPage', fn () => XfAdmin::errorPage(['code' => 404, 'message' => '页面不存在']));

echo "\n== 安全转义 ==\n";
$scriptJson = Html::scriptJson('</script><img src=x onerror=alert(1)>');
$assert(str_contains($scriptJson, '\u003C'), 'scriptJson 防 </script> 注入（HEX_TAG）');
$assert(! str_contains($scriptJson, '</script>'), 'scriptJson 不含原始 </script>');
$attrJson = Html::json('</script>"\'');
$assert(str_contains($attrJson, '&lt;'), 'json 用于属性时 HTML 转义 <（防 </script>）');
$assert(! str_contains($attrJson, '</script>'), 'json 用于属性时不含原始 </script>');
$malicious = '<svg/onload=alert(1)>';
$comment = (string) XfAdmin::commentThread(['items' => [['user' => $malicious, 'text' => $malicious]]]);
$assert(! str_contains($comment, '<svg/onload'), 'commentThread 转义用户输入（防 XSS）');
$pdfJson = Html::scriptJson('"></script><script>alert(1)</script>');
$assert(! str_contains($pdfJson, '"></script><script>'), 'pdfViewer url 经 scriptJson 转义（helper）');
$assert(str_contains($pdfJson, '\u003C/script\u003E'), 'pdfViewer url 含 HEX_TAG 编码（helper）');

echo "\n== 内联 JS 回归（防引入语法错误 / 监听泄漏） ==\n";
Assets::reset();
XfAdmin::leafletMap(['tiles' => null, 'markers' => [['lat' => 1, 'lng' => 2]]])->render();
$jsOut = Assets::instance()->scripts();
$assert(str_contains($jsOut, 'background = "#eef0f3"'), 'leafletMap 离线模式 JS 赋值正确（修复语法错误）');
$assert(! str_contains($jsOut, 'background:"#eef0f3"'), 'leafletMap 不再含错误冒号赋值');
XfAdmin::idleTimer(['timeout' => 30])->render();
$jsOut2 = Assets::instance()->scripts();
$assert(str_contains($jsOut2, '__xfIdleGlobalBound'), 'idleTimer 全局监听幂等保护（防重复绑定）');

echo "\n== 全局 API / 别名 ==\n";
$assert(XfAdmin::has('dataTable'), 'XfAdmin::has 已知组件返回 true');
$assert(! XfAdmin::has('__not_exist__'), 'XfAdmin::has 未知组件返回 false');
$assert(XfAdmin::has('dateRangePicker'), '别名 dateRangePicker 已注册（文档调用名可用）');
$assert(XfAdmin::has('clipboardButton'), '别名 clipboardButton 已注册（文档调用名可用）');
$assert(XfAdmin::version() === XfAdmin::VERSION, 'XfAdmin::version 返回包版本号');
$assert(is_string(XfAdmin::asset('css/xfadmin.css')) && str_contains(XfAdmin::asset('css/xfadmin.css'), 'xfadmin.css'), 'XfAdmin::asset 返回资源 URL');
$assert(trim((string) XfAdmin::dateRangePicker(['name' => 'd', 'label' => '日期'])) !== '', 'dateRangePicker 别名可正常渲染');
$assert(trim((string) XfAdmin::clipboardButton(['text' => 'hi'])) !== '', 'clipboardButton 别名可正常渲染');

// 8) DataTables 服务端处理（DataSet / XfAdmin::dataResponse）
echo "\n== DataTables 服务端处理（DataSet） ==\n";

$rows = [];
for ($i = 1; $i <= 53; $i++) {
    $rows[] = [
        'id'         => $i,
        'name'       => '用户' . $i,
        'email'      => 'u' . $i . '@x.com',
        'status'     => $i % 2 ? 'on' : 'off',
        'created_at' => '2026-07-' . str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
    ];
}
$searchCols = [
    ['data' => 'id', 'searchable' => 'false'],
    ['data' => 'name', 'searchable' => 'true'],
    ['data' => 'email', 'searchable' => 'true'],
    ['data' => 'status', 'searchable' => 'false'],
];
$ds = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'draw'    => 2,
    'start'   => 0,
    'length'  => 10,
    'search'  => ['value' => '用户1'],
    'columns' => $searchCols,
    'order'   => [['column' => 1, 'dir' => 'asc']],
], ['searchable' => ['name', 'email'], 'filters' => ['status']]);
$assert($ds['draw'] === 2, 'DataSet draw 透传');
$assert($ds['recordsTotal'] === 53, 'DataSet recordsTotal = 全量');
$assert($ds['recordsFiltered'] === 11 && count($ds['data']) === 10, 'DataSet 全局搜索「用户1」=> 11 条 / 分页 10 条');
$assert($ds['data'][0]['name'] === '用户1', 'DataSet 按 name 升序首行');

$dsOn = \zxf\XfAdmin\Support\DataSet::response($rows, ['length' => 10, 'search' => ['value' => ''], 'status' => 'on'], ['filters' => ['status']]);
$assert($dsOn['recordsFiltered'] === 27, 'DataSet 自定义过滤 status=on => 27 条');

$dsNull = \zxf\XfAdmin\Support\DataSet::response($rows, ['length' => 10, 'search' => ['value' => null], 'columns' => []], []);
$assert($dsNull['recordsFiltered'] === 53, 'DataSet 兼容 search[value]=null（ConvertEmptyStringsToNull 场景不崩）');

$dr = XfAdmin::dataResponse($rows, ['length' => 5], []);
$assert(isset($dr['draw'], $dr['recordsTotal'], $dr['recordsFiltered'], $dr['data']) && $dr['recordsTotal'] === 53, 'XfAdmin::dataResponse 委托 DataSet');

// 9) Tabs：footer / form / item badge
echo "\n== Tabs footer / form / badge ==\n";
$tabs = XfAdmin::tabs([
    'items' => [
        ['title' => '基础配置', 'badge' => '新', 'content' => 'a'],
        ['title' => '邮件配置', 'content' => 'b'],
    ],
    'footer' => '<button class="btn btn-primary">统一保存</button>',
]);
$tabsHtml = (string) $tabs;
$assert(str_contains($tabsHtml, 'xf-tabs-footer') && str_contains($tabsHtml, '统一保存'), 'Tabs footer 渲染');
$assert(str_contains($tabsHtml, 'badge'), 'Tabs item badge 渲染');

$tabsForm = XfAdmin::tabs([
    'items'  => [['title' => 'A', 'content' => 'a'], ['title' => 'B', 'content' => 'b']],
    'footer' => '<button class="btn">提交</button>',
    'form'   => ['action' => '/save', 'method' => 'POST', 'ajax' => true],
]);
$tf = (string) $tabsForm;
$assert(str_contains($tf, '<form') && str_contains($tf, 'data-xf="form"') && str_contains($tf, '/save'), 'Tabs form 包裹（多标签一次提交）');

// 10) Menu：子菜单箭头 + 徽标
echo "\n== Menu 箭头 + 徽标 ==\n";
$menuHtml = (string) XfAdmin::menu([
    'items' => [
        ['title' => '配置'],
        ['text' => '系统', 'icon' => 'ti ti-settings', 'badge' => ['text' => '谨慎', 'class' => 'bg-warning'], 'children' => [
            ['text' => '用户管理', 'url' => '/users'],
        ]],
    ],
    'current_url' => '/users',
]);
$assert(str_contains($menuHtml, 'menu-arrow'), 'Menu 子菜单小箭头标记');
$assert(str_contains($menuHtml, 'rounded-pill'), 'Menu badge rounded-pill 外观');

// 11) DataTable：filter_bar + 富单元格渲染器配置
echo "\n== DataTable filter_bar + 富单元格 ==\n";
$dt = XfAdmin::dataTable([
    'columns' => [
        'id'     => 'ID',
        'name'   => ['label' => '姓名', 'render' => 'input', 'xfCellInput' => ['url' => '/cell']],
        'ip'     => ['label' => 'IP', 'render' => 'ip'],
        'status' => ['label' => '状态', 'render' => 'switch', 'xfSwitch' => ['url' => '/switch']],
        'tags'   => ['label' => '标签', 'render' => 'tags'],
        'color'  => ['label' => '颜色', 'render' => 'color'],
        'op'     => ['label' => '操作', 'actions' => [
            ['text' => '查看', 'icon' => 'ti ti-eye', 'act' => 'view'],
            ['text' => '删除', 'icon' => 'ti ti-trash', 'act' => 'ajax', 'url' => '/del', 'confirm' => '确定删除？'],
        ]],
    ],
    'data'       => [],
    'filter_bar' => [
        ['name' => 'status', 'label' => '状态', 'options' => ['on' => '启用', 'off' => '停用']],
        ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
    ],
]);
$dtHtml = (string) $dt;
$assert(str_contains($dtHtml, 'xf-filter-bar'), 'DataTable filter_bar 渲染');
$assert(str_contains($dtHtml, 'xfRender'), 'DataTable 富单元格渲染器配置输出');
$assert(str_contains($dtHtml, 'data-xf="datatable"'), 'DataTable 标记 data-xf=datatable');

// 11.5) DataSet：紧凑协议（xfc/xfo/xfs）+ op 运算符过滤
echo "\n== DataSet 紧凑协议 + op 过滤 ==\n";
$dsc = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'draw' => 3, 'start' => 0, 'length' => 10,
    'xfc'  => 'id:0:1|name:1:1|email:1:1|status:0:1',
    'xfo'  => '1:d',
    'xfs'  => '用户1',
], ['searchable' => ['name', 'email']]);
$assert($dsc['recordsFiltered'] === 11, 'DataSet xfc/xfs 紧凑协议还原（搜索 11 条）');
$assert($dsc['data'][0]['name'] === '用户19', 'DataSet xfo 紧凑排序还原（name 降序）');

$dsOp = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'length' => 100, 'score_min' => '50',
], ['filters' => ['score_min' => ['field' => 'id', 'op' => '>=']]]);
$assert($dsOp['recordsFiltered'] === 4, 'DataSet op >= 过滤（id>=50 => 4 条）');

$dsIn = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'length' => 100, 'st' => 'on,off',
], ['filters' => ['st' => ['field' => 'status', 'op' => 'in']]]);
$assert($dsIn['recordsFiltered'] === 53, 'DataSet op in 过滤（多值逗号分隔）');

$dsBt = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'length' => 100, 'idr' => '10,20',
], ['filters' => ['idr' => ['field' => 'id', 'op' => 'between']]]);
$assert($dsBt['recordsFiltered'] === 11, 'DataSet op between 过滤（10~20 => 11 条）');

$dsDt = \zxf\XfAdmin\Support\DataSet::response($rows, [
    'length' => 100, 'to' => '2026-07-05',
], ['filters' => ['to' => ['field' => 'created_at', 'op' => 'date_to']]]);
$assert($dsDt['recordsFiltered'] > 0 && $dsDt['recordsFiltered'] < 53, 'DataSet op date_to 过滤（纯日期自动补 23:59:59）');

// 11.6) filter_bar 扩展控件 + 行编辑 action=edit + method=POST
echo "\n== filter_bar 扩展控件 / 行编辑 / POST ==\n";
$dtExt = (string) XfAdmin::dataTable([
    'columns' => [
        'id' => 'ID',
        'op' => ['label' => '操作', 'actions' => [
            ['label' => '编辑', 'action' => 'edit', 'ajax' => '/api/u/{id}', 'method' => 'PUT', 'fields' => [
                ['name' => 'nickname', 'label' => '昵称', 'required' => true],
                ['name' => 'vip', 'label' => 'VIP', 'type' => 'switch'],
            ]],
        ]],
    ],
    'ajax'        => '/api/data/u',
    'server_side' => true,
    'method'      => 'POST',
    'filter_bar'  => [
        ['name' => 'score', 'label' => '积分', 'type' => 'range', 'min' => 0],
        ['name' => 'reg', 'label' => '注册', 'type' => 'daterange'],
        ['name' => 'vips', 'label' => 'VIP', 'options' => ['1' => 'V1'], 'multiple' => true],
        ['name' => 'only_vip', 'label' => '', 'type' => 'checkbox', 'text' => '仅VIP'],
        ['name' => 'bal', 'label' => '余额', 'type' => 'number', 'min' => 0, 'step' => 100],
    ],
]);
$assert(str_contains($dtExt, 'data-filter="score_min"') && str_contains($dtExt, 'data-filter="score_max"'), 'filter_bar range => _min/_max 双输入');
$assert(str_contains($dtExt, 'data-filter="reg_from"') && str_contains($dtExt, 'data-filter="reg_to"'), 'filter_bar daterange => _from/_to 双输入');
$assert(str_contains($dtExt, 'multiple'), 'filter_bar select 多选');
$assert(str_contains($dtExt, 'form-check-input xf-filter'), 'filter_bar checkbox 控件');
$dtExtRaw = html_entity_decode($dtExt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$assert(str_contains($dtExtRaw, '"type":"POST"'), 'method=POST => ajax 转对象带 type');
$assert(str_contains($dtExtRaw, '"action":"edit"') && str_contains($dtExtRaw, 'nickname'), '行编辑 action=edit 配置带 fields 输出');

// 11.7) JS 回归：confirm 唯一定义 / 紧凑参数 / formDialog / editRow
$xfJs = (string) file_get_contents(__DIR__ . '/../resources/assets/js/xfadmin.js');
$assert(substr_count($xfJs, 'XFAdmin.confirm = function') === 1, 'xfadmin.js confirm 仅一处定义（修复覆盖导致确认回调失效）');
$assert(str_contains($xfJs, 'd.xfc') && str_contains($xfJs, 'd.xfo'), 'xfadmin.js serverSide 参数压缩（规避超长 URL 403）');
$assert(str_contains($xfJs, 'XFAdmin.formDialog') && str_contains($xfJs, 'XFAdmin.editRow'), 'xfadmin.js 表单弹窗 + 行编辑 API');
$appJs = (string) file_get_contents(__DIR__ . '/../resources/assets/js/app.js');
$assert(str_contains($appJs, 'if(!document.querySelector(this.translationKeySelector))return;'), 'app.js i18n 无 data-lang 时跳过（根治 en.json 404）');
$assert(str_contains($appJs, 'sessionStorage.setItem("__INSPINIA_LANG__"'), 'app.js 语言切换持久化与读取存储一致');

// 12) StatCard 响应式列宽
echo "\n== StatCard 响应式列宽 ==\n";
$sc = (string) XfAdmin::statCard(['title' => '总数', 'value' => '99', 'width' => ['sm' => 6, 'xl' => 3]]);
$assert(str_contains($sc, 'col-sm-6') && str_contains($sc, 'col-xl-3') && str_contains($sc, 'h-100'), 'StatCard 响应式列宽包裹 + h-100');
$scNo = (string) XfAdmin::statCard(['title' => '总数', 'value' => '99']);
$assert(!str_contains($scNo, 'col-') && str_contains($scNo, 'card'), 'StatCard 无 width 时不包裹列');
echo "ok\n";

Assets::reset();

echo "\n";
if ($failures !== []) {
    echo "FAILURES:\n";
    foreach ($failures as $f) {
        echo "  ✘ {$f}\n";
    }
    exit(1);
}
echo "ALL PASSED\n";

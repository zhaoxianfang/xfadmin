<?php
declare(strict_types=1);
/* 临时回归验证：资源去重 / 多实例 / 多页面隔离 */
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
        $f = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen('zxf\\XfAdmin\\'))) . '.php';
        if (is_file($f)) require $f;
    }
});

use zxf\XfAdmin\XfAdmin;
use zxf\XfAdmin\Assets\Assets;

$fail = 0;
function check(string $name, bool $ok): void {
    global $fail;
    echo ($ok ? "  ✔ " : "  ✘ ") . $name . PHP_EOL;
    if (! $ok) $fail++;
}

/* 1. 同一插件被多个组件依赖 → 资源只输出一次 */
Assets::reset();
$c1 = (string) XfAdmin::apexChart(['type' => 'line', 'series' => [['name' => 'a', 'data' => [1, 2]]]]);
$c2 = (string) XfAdmin::apexChart(['type' => 'bar', 'series' => [['name' => 'b', 'data' => [3, 4]]]]);
$scripts = Assets::instance()->scripts();
check('apexcharts.min.js 只引用一次', substr_count($scripts, 'apexcharts.min.js') === 1);

/* 2. 同一组件实例渲染两次 → 内联初始化 JS 不重复 */
Assets::reset();
$g = XfAdmin::gallery(['items' => [['image' => 'a.jpg']]]);
$h1 = (string) $g;
$h2 = (string) $g;
$scripts = Assets::instance()->scripts();
check('同实例两次渲染 id 稳定', $h1 === $h2 || substr_count($scripts, 'GLightbox') <= 2);
preg_match('/id="(gallery-[^"]+)"/', $h1, $gm);
check('内联初始化脚本按 key 去重', isset($gm[1]) && substr_count($scripts, 'getElementById("' . $gm[1] . '")') === 1);

/* 3. 相同代码两次注册（无 key）→ 只输出一次 */
Assets::reset();
Assets::instance()->inlineJs('console.log("dup-test");');
Assets::instance()->inlineJs('console.log("dup-test");');
$scripts = Assets::instance()->scripts();
check('无 key 相同内联 JS 去重', substr_count($scripts, 'dup-test') === 1);

/* 4. 同页多个 ProductDetails 实例 → tab id 不冲突 */
Assets::reset();
$pd = ['product' => ['name' => '测试商品', 'images' => ['a.jpg', 'b.jpg'], 'tabs' => [['title' => 'A', 'body' => 'a'], ['title' => 'B', 'body' => 'b']]]];
$p1 = (string) XfAdmin::productDetails($pd);
$p2 = (string) XfAdmin::productDetails($pd);
preg_match_all('/id="(xf-pdt[^"]+)"/', $p1 . $p2, $m);
check('ProductDetails 多实例 tab id 唯一', count($m[1]) === 4 && count(array_unique($m[1])) === 4);
check('ProductDetails 画廊 data-pd-gallery 标记', str_contains($p1, 'data-pd-gallery'));

/* 5. ChatBox XSS：text 转义、html 保留 */
Assets::reset();
$chat = (string) XfAdmin::chatBox(['messages' => [
    ['from' => 'them', 'text' => '<script>alert(1)</script>'],
    ['from' => 'me', 'html' => '<b>rich</b>'],
]]);
check('ChatBox text 已转义', ! str_contains($chat, '<script>alert(1)') && str_contains($chat, '&lt;script&gt;'));
check('ChatBox html 字段保留富文本', str_contains($chat, '<b>rich</b>'));

/* 6. 多页面隔离：第一页的插件不泄漏到第二页 */
Assets::reset();
$page1 = (string) XfAdmin::page(['title' => 'P1', 'content' => XfAdmin::apexChart(['type' => 'line', 'series' => [['name' => 'x', 'data' => [1]]]])]);
$page2 = (string) XfAdmin::page(['title' => 'P2', 'content' => '<p>纯静态页</p>']);
check('Page1 含 apexcharts', str_contains($page1, 'apexcharts.min.js'));
check('Page2 不泄漏 Page1 的 apexcharts', ! str_contains($page2, 'apexcharts.min.js'));
check('Page2 基础资源完整', str_contains($page2, 'app.min.css') || str_contains($page2, 'xfadmin.css'));

/* 7. reset 保留 baseUrl / version */
Assets::instance()->setBaseUrl('/custom/base')->setVersion('9.9.9');
Assets::reset();
$url = Assets::instance()->url('css/xfadmin.css');
check('reset 后保留 baseUrl', str_contains($url, '/custom/base'));
check('reset 后保留 version', str_contains($url, '9.9.9'));

/* 8. Icon lucide 分支注册 lucide 插件 */
Assets::reset();
(string) XfAdmin::icon(['name' => 'settings', 'lib' => 'lucide']);
check('Icon(lucide) 注册 lucide.min.js', str_contains(Assets::instance()->scripts(), 'lucide.min.js'));
Assets::reset();
(string) XfAdmin::icon(['name' => 'home']);
check('Icon(tabler) 不加载 lucide', ! str_contains(Assets::instance()->scripts(), 'lucide.min.js'));

/* 9. 页面级混合压力：一个页面塞入多类组件，资源无重复 */
Assets::reset();
$content = '';
$content .= XfAdmin::dataTable(['columns' => ['a' => 'A'], 'data' => [['a' => 1]]]);
$content .= XfAdmin::dataTable(['columns' => ['b' => 'B'], 'data' => [['b' => 2]]]);
$content .= XfAdmin::echart(['options' => ['series' => []]]);
$content .= XfAdmin::echart(['options' => ['series' => []]]);
$content .= XfAdmin::select(['name' => 's1', 'options' => ['a' => 'A'], 'enhance' => 'choices']);
$content .= XfAdmin::select(['name' => 's2', 'options' => ['b' => 'B'], 'enhance' => 'choices']);
$big = (string) XfAdmin::page(['title' => 'Mixed', 'content' => $content]);
foreach (['dataTables.min.js', 'echarts.min.js', 'choices.min.js', 'xfadmin.js'] as $res) {
    check("混合页 {$res} 仅引用一次", substr_count($big, $res) === 1);
}

/* 10. XfAdmin::img() 静态图片解析：外链/data 原样、空返回空、相对路径走 images/ */
Assets::reset();
Assets::instance()->setBaseUrl('/zxf/xfadmin')->setVersion('1.0.0');
check('img() http 外链原样返回', XfAdmin::img('https://cdn.example.com/a.jpg') === 'https://cdn.example.com/a.jpg');
check('img() data URI 原样返回', str_starts_with(XfAdmin::img('data:image/png;base64,AAAA'), 'data:image/png'));
check('img() 空字符串返回透明 GIF', str_starts_with(XfAdmin::img(''), 'data:image/gif'));
check('img() 相对路径解析到 images/', str_contains(XfAdmin::img('users/user-1.jpg'), '/zxf/xfadmin/images/users/user-1.jpg'));
check('img() 首斜杠被归一', str_contains(XfAdmin::img('/users/user-1.jpg'), 'images/users/user-1.jpg'));
check('img() 与 Component::img 行为一致（外链）', str_contains((string) XfAdmin::avatar(['src' => 'https://x.com/a.png']), 'https://x.com/a.png'));

/* 11. demo 路由完整性：index.php 中 match 的每个 require 目标文件必须存在 */
$demoIndex = (string) file_get_contents(__DIR__ . '/../demo/index.php');
preg_match_all("#require __DIR__ \\. '(/pages/[^']+)'#", $demoIndex, $dm);
$missing = array_filter(array_unique($dm[1]), fn ($p) => ! is_file(__DIR__ . '/../demo' . $p));
check('demo 路由引用的页面文件全部存在', $missing === [] && count($dm[1]) > 0);
/* 菜单 url 与路由覆盖：menu 中的本地 url（去掉 # 与外链）都能被 match 命中或落入 default 404 之外 */
preg_match_all("#'url' => '(/[a-z0-9]*)'#", $demoIndex, $um);
preg_match_all("#'([a-z0-9]+)'\\s*=> require#", $demoIndex, $rm);
$routes = array_merge($rm[1], ['home']);
$bad = array_filter(array_unique($um[1]), function ($u) use ($routes) {
    $r = trim($u, '/');
    $r = $r === '' ? 'home' : $r;
    return ! in_array($r, $routes, true);
});
check('demo 菜单链接全部有对应路由', $bad === []);

/* 12. CommentThread 头像：空头像不输出空 src img，改为首字母占位 */
Assets::reset();
$ct = (string) XfAdmin::commentThread(['items' => [
    ['user' => '张三', 'text' => 'hello', 'avatar' => ''],
    ['user' => '李四', 'text' => 'world', 'avatar' => 'users/user-1.jpg'],
]]);
check('CommentThread 空头像无空 src', ! str_contains($ct, 'src=""'));
check('CommentThread 空头像有首字母占位', str_contains($ct, '张'));
check('CommentThread 有头像走 images/ 解析', str_contains($ct, 'images/users/user-1.jpg'));

/* 13. Avatar 组件：输出对齐 INSPINIA 的 .avatar 包裹结构（尺寸类挂包裹元素，图片/文字均包裹） */
$avImg = (string) XfAdmin::avatar(['src' => 'users/user-1.jpg', 'size' => 'md']);
check('Avatar 图片：尺寸类挂在 .avatar 包裹元素', str_contains($avImg, 'class="avatar avatar-md"') && str_contains($avImg, 'class="img-fluid rounded-circle"'));
check('Avatar 图片：外链走 images/ 解析', str_contains($avImg, 'images/users/user-1.jpg'));
$avTxt = (string) XfAdmin::avatar(['text' => 'ZS', 'variant' => 'success', 'size' => 'lg']);
check('Avatar 文字：.avatar 包裹 + avatar-title 浅底', str_contains($avTxt, 'class="avatar avatar-lg"') && str_contains($avTxt, 'avatar-title bg-success-subtle text-success'));
check('Avatar 文字：输出大写缩写', str_contains($avTxt, '>ZS<'));
$avGrp = (string) XfAdmin::avatar(['group' => [['src' => 'users/user-1.jpg'], ['text' => '+5']]]);
check('Avatar 分组：外层 .avatar-group + 两个 .avatar 子项', str_starts_with($avGrp, '<div class="avatar-group"') && substr_count($avGrp, 'class="avatar avatar-') === 2);

/* 14. Gallery：对齐 INSPINIA misc-gallery.html 规范结构 */
Assets::reset();
$gal = (string) XfAdmin::gallery([
    'items'   => [['src' => 'gallery/1.jpg', 'title' => 'A', 'group' => 'design'], ['src' => 'gallery/2.jpg', 'group' => 'photo']],
    'filter'  => ['all' => '全部', 'design' => '设计', 'photo' => '摄影'],
    'masonry' => false,
]);
check('Gallery：卡片容器 + card-header 搜索/筛选', str_contains($gal, 'card-header') && str_contains($gal, 'app-search') && str_contains($gal, 'btn-ghost-primary'));
check('Gallery：badge-label 分类角标 + image-popup 灯箱 + card-img 图片', str_contains($gal, 'badge-label') && str_contains($gal, 'image-popup') && str_contains($gal, 'card-img rounded-2'));
check('Gallery：row-cols 响应式栅格 + data-group 筛选属性', str_contains($gal, 'row-cols') && str_contains($gal, 'data-group="design"'));

/* 15. Orders：对齐 INSPINIA ecommerce-orders.html 规范结构 + xftable 前端交互 */
Assets::reset();
$ord = (string) XfAdmin::orders(['orders' => [
    ['id' => '#WB1', 'customer' => '张三', 'avatar' => 'users/user-1.jpg', 'date' => '2026-07-20', 'total' => '¥99', 'status' => 'completed', 'paid' => true, 'payment' => '支付宝'],
    ['id' => '#WB2', 'customer' => '李四', 'date' => '2026-07-21', 'total' => '¥88', 'status' => 'pending', 'paid' => false, 'payment' => '微信'],
]]);
check('Orders：模板同款表格类 + thead-sm + fs-xxs 表头', str_contains($ord, 'table-custom table-select table-hover') && str_contains($ord, 'thead-sm') && str_contains($ord, 'text-uppercase fs-xxs'));
check('Orders：客户头像 .avatar 包裹 + 付款圆点 + badge-soft 状态', str_contains($ord, 'avatar avatar-sm') && str_contains($ord, 'ti-point-filled') && str_contains($ord, 'badge-soft-success'));
check('Orders：xftable 接线（搜索/筛选/分页/全选/批删）', str_contains($ord, 'data-xf="xftable"') && str_contains($ord, 'data-xftable-search') && str_contains($ord, 'data-xftable-filter="status"') && str_contains($ord, 'data-xftable-check-all') && str_contains($ord, 'data-xftable-pagination'));
check('Orders：行携带 data-status/data-paid 供筛选精确匹配', str_contains($ord, 'data-status="pending"') && str_contains($ord, 'data-paid="0"'));

/* 16. 头像统一（P2/P7）：渲染输出中不得出现裸 rounded-circle + width= 头像 img（应为 .avatar 包裹）
   注意：源码扫描会因 $this->e() 的 -> 含 > 字符导致 [^>]* 提前终止而“假通过”，
   故改为扫描“组件渲染后的 HTML 输出”——渲染结果不含 -> 操作符，正则才能可靠命中。 */
Assets::reset();
$bare = 0;
$samples = [];
try {
    $samples[] = (string) XfAdmin::testimonial(['items' => [['avatar' => 'users/avatar-2.jpg', 'name' => '李四', 'text' => '好评', 'rating' => 5]]]);
    $samples[] = (string) XfAdmin::chip(['label' => '张三', 'avatar' => 'users/avatar-1.jpg']);
    $samples[] = (string) XfAdmin::teamMember(['members' => [['avatar' => 'users/avatar-1.jpg', 'name' => '张三', 'role' => '产品经理']]]);
    $samples[] = (string) XfAdmin::voteList(['items' => [['votes' => 3, 'title' => '议题', 'author' => ['avatar' => 'users/user-7.jpg', 'name' => '陈晓']]]]);
    $samples[] = (string) XfAdmin::commentThread(['items' => [['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'text' => '很好']]]);
    $samples[] = (string) XfAdmin::chatApp([
        'conversations' => [['name' => '李娜', 'avatar' => 'users/user-2.jpg']],
        'peer'          => ['name' => '李娜', 'avatar' => 'users/user-2.jpg'],
        'messages'      => [['from' => 'other', 'text' => '你好', 'avatar' => 'users/user-2.jpg']],
    ]);
    $samples[] = (string) XfAdmin::activityFeed(['items' => [['avatar' => 'users/avatar-1.jpg', 'title' => '操作', 'time' => '刚刚']]]);
} catch (\Throwable $e) {
    check('头像样本渲染未抛异常', false);
}
$out = implode("\n", $samples);
if (preg_match('/<img[^>]*rounded-circle[^>]*width="\d+"/', $out)) {
    $bare++;
}
check('渲染输出中无裸 rounded-circle+width 头像 img（应为 .avatar 包裹）', $bare === 0);
// 正向校验：修复后的组件确实用 .avatar 包裹头像
check('Testimonial 头像用 .avatar 包裹', str_contains($out, 'class="avatar avatar-sm'));
check('Chip 头像用 .avatar 包裹', str_contains($out, 'class="avatar avatar-xs'));

/* 17. Kanban：对齐 INSPINIA project-kanban.html 规范结构 */
Assets::reset();
$kan = (string) XfAdmin::kanban(['columns' => [
    ['id' => 'todo', 'title' => '待办', 'variant' => 'danger', 'cards' => [
        ['title' => '设计首页', 'label' => '设计', 'variant' => 'info', 'text' => 't', 'members' => ['users/1.jpg'], 'due' => '今天', 'progress' => 60, 'comments' => 3, 'attachments' => 1],
    ]],
    ['id' => 'done', 'title' => '完成', 'cards' => []],
]]);
check('Kanban：.xf-kanban 外壳 + data-xf=kanban + .kanban-content 列容器 + .xf-kanban-col 列', str_contains($kan, 'xf-kanban') && str_contains($kan, 'data-xf="kanban"') && str_contains($kan, 'kanban-content') && str_contains($kan, 'xf-kanban-col'));
check('Kanban：卡片 badge-soft + ti-point-filled 标签 + xf-kanban-card-title 标题 + avatar-group-xs 成员', str_contains($kan, 'badge-soft-info') && str_contains($kan, 'ti-point-filled') && str_contains($kan, 'xf-kanban-card-title') && str_contains($kan, 'avatar-group avatar-group-xs'));
check('Kanban：拖拽容器 data-plugins=sortable + 截止日期 + 进度条 + 搜索框', str_contains($kan, 'data-plugins="sortable"') && str_contains($kan, 'ti-calendar-time') && str_contains($kan, 'progress-bar') && str_contains($kan, 'data-kanban-search'));

/* 18. EmailApp / MailList：对齐 INSPINIA email.html 表格式邮件行 */
Assets::reset();
$em = (string) XfAdmin::emailApp([
    'folders'  => [['icon' => 'ti ti-inbox', 'name' => '收件箱', 'count' => 3, 'active' => true]],
    'messages' => [['id' => 1, 'from' => '张三', 'avatar' => 'users/1.jpg', 'subject' => '周末', 'preview' => 'p', 'time' => '10:30', 'unread' => true, 'starred' => false, 'attachments' => 1]],
    'selected' => ['from' => '李四', 'avatar' => 'users/2.jpg', 'from_email' => 'l@x.com', 'subject' => 'Re', 'time' => '昨天', 'body' => 'hi', 'attachments' => [['name' => 'a.pdf', 'size' => '1MB']]],
]);
check('EmailApp：三栏 .email-app + 左 list-custom 文件夹 + data-xf=email', str_contains($em, 'email-app') && str_contains($em, 'list-custom') && str_contains($em, 'data-xf="email"'));
check('EmailApp：中间 table 邮件行（勾选/星标/avatar-xs/未读/stretched-link/附件）', str_contains($em, 'table table-hover table-select') && str_contains($em, 'email-item-check') && str_contains($em, 'email-action-btn') && str_contains($em, 'avatar-xs') && str_contains($em, 'unread') && str_contains($em, 'stretched-link') && str_contains($em, 'ti-paperclip'));
check('EmailApp：右侧阅读窗格 + 回复/转发', str_contains($em, 'email-read') && str_contains($em, '回复') && str_contains($em, '转发'));

Assets::reset();
$ml = (string) XfAdmin::mailList(['title' => '收件箱', 'items' => [['from' => '张三', 'avatar' => 'users/1.jpg', 'subject' => 's', 'preview' => 'p', 'time' => '10:30', 'unread' => true, 'starred' => true]]]);
check('MailList：表格式邮件行一致结构', str_contains($ml, 'email-table') && str_contains($ml, 'stretched-link') && str_contains($ml, 'avatar-xs') && str_contains($ml, 'unread'));

/* 19. Nestable：对齐 INSPINIA misc-nestable.html 的 .nested-sortable 嵌套结构 */
Assets::reset();
$nes = (string) XfAdmin::nestable([
    'items'  => [['content' => '设计', 'id' => 1, 'children' => [['content' => 'UI', 'id' => 11]]], '开发'],
    'handle' => true, 'input' => 'order',
]);
check('Nestable：根 list-group.nested-sortable + data-xf=nestable', str_contains($nes, 'list-group nested-sortable nested-sortable-handle') && str_contains($nes, 'data-xf="nestable"'));
check('Nestable：子级嵌套 list-group.nested-sortable + 把手 + data-id', str_contains($nes, '<div class="list-group nested-sortable nested-sortable-handle">') && str_contains($nes, 'sort-handle') && str_contains($nes, 'data-id="11"'));
check('Nestable：隐藏 input 同步排序', str_contains($nes, 'data-nestable-input'));

/* 20. Masonry：对齐 INSPINIA misc-masonry.html 的 row + col-* 结构 */
Assets::reset();
$msn = (string) XfAdmin::masonry(['columns' => 3, 'items' => ['<div class="card">A</div>', '<div class="card">B</div>']]);
check('Masonry：row g-4 容器 + data-xf=masonry + data-masonry', str_contains($msn, 'row g-4') && str_contains($msn, 'data-xf="masonry"') && str_contains($msn, 'data-masonry'));
check('Masonry：子项 col-xl-4 col-md-6 masonry-cell（3 列→xl-4）', str_contains($msn, 'col-xl-4 col-md-6 masonry-cell'));

/* 21. Calendar：对齐 INSPINIA calendar.html（卡片包裹 + 外部事件两栏 + bootstrap 主题） */
Assets::reset();
$cal = (string) XfAdmin::calendar([
    'editable'      => true,
    'externalEvents' => [['label' => '工作', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary']],
    'events'        => [['title' => '会', 'start' => '2026-07-23', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary']],
]);
check('Calendar：data-calendar-root 两栏 + #external-events 可拖拽源', str_contains($cal, 'data-calendar-root') && str_contains($cal, 'col-xl-3 col-lg-4 border-end') && str_contains($cal, 'id="external-events"') && str_contains($cal, 'external-event'));
check('Calendar：FullCalendar 配置对齐（bootstrap 主题 + 视图切换 + 可编辑/可拖入）', str_contains($cal, 'data-xf="calendar"') && str_contains($cal, 'themeSystem') && str_contains($cal, 'dayGridMonth,timeGridWeek,timeGridDay,listMonth') && str_contains($cal, 'editable'));

/* 22. DataTable 密度切换：density 按钮 + compact 初始类 */
Assets::reset();
$dtCompact = (string) XfAdmin::dataTable([
    'columns' => ['name' => '姓名', 'age' => '年龄'],
    'data'    => [['name' => '张三', 'age' => 20]],
    'density' => 'compact',
    'buttons' => ['density'],
]);
check('DataTable density=compact：表格含 xf-dt-compact 紧凑类', str_contains($dtCompact, 'xf-dt-compact'));
check('DataTable density 按钮：xf-btn-density 扩展按钮存在', str_contains($dtCompact, 'xf-btn-density') && str_contains($dtCompact, 'xfButton'));

/* 23. DataTable 完整过滤栏 filter_bar：表单 + 字段 + 重置 + 导出按钮 */
Assets::reset();
$dtFilter = (string) XfAdmin::dataTable([
    'columns' => ['name' => '姓名', 'position' => '职位'],
    'data'    => [['name' => '张三', 'position' => '工程师']],
    'buttons' => ['copy', 'excel', 'density'],
    'filter_bar' => [
        ['type' => 'search', 'name' => 'name', 'label' => '姓名', 'width' => 'col-md-4'],
        ['type' => 'select', 'name' => 'position', 'label' => '职位', 'options' => ['工程师' => '工程师']],
    ],
]);
check('DataTable filter_bar：form[data-xf-filter-for] 存在', str_contains($dtFilter, 'data-xf-filter-for'));
check('DataTable filter_bar：xf-filter 字段（data-filter=name）', str_contains($dtFilter, 'xf-filter') && str_contains($dtFilter, 'data-filter="name"'));
check('DataTable filter_bar：重置按钮 xf-filter-reset', str_contains($dtFilter, 'xf-filter-reset'));
check('DataTable 导出按钮：excel 按钮存在', str_contains($dtFilter, 'excelHtml5') || str_contains($dtFilter, 'ti-file-spreadsheet'));
check('DataTable 复制按钮：copy 按钮存在', str_contains($dtFilter, 'ti-copy'));

/* 24. 健壮性：全组件空数据渲染不抛异常 / 不产生 PHP 警告（生产可用基线）
   遍历 src/Components 下全部具体组件，以空配置 [] 渲染，捕获 Throwable 与 E_WARNING/E_NOTICE。 */
Assets::reset();
$compDir = __DIR__ . '/../src/Components';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($compDir));
$compClasses = [];
foreach ($rii as $f) {
    if (! $f->isFile() || $f->getExtension() !== 'php') continue;
    $rel = preg_replace('/\.php$/', '', substr($f->getPathname(), strlen($compDir) + 1));
    $cls = 'zxf\\XfAdmin\\Components\\' . str_replace('/', '\\', $rel);
    if ($cls === 'zxf\\XfAdmin\\Components\\Component') continue; // 抽象基类不可实例化
    $compClasses[] = $cls;
}
$bad = 0;
foreach ($compClasses as $cls) {
    if (! class_exists($cls)) continue;
    $errs = [];
    set_error_handler(function ($no, $str) use (&$errs) {
        if (! (error_reporting() & $no)) return;
        $errs[] = "WARN: $str";
    });
    try {
        $out = (string) new $cls([]);
        if (! is_string($out)) $errs[] = 'render-not-string';
    } catch (\Throwable $e) {
        $errs[] = 'THROW: ' . get_class($e) . ': ' . $e->getMessage();
    }
    restore_error_handler();
    if ($errs) {
        $bad++;
        fwrite(STDERR, "组件健壮性异常 $cls: " . implode('; ', array_slice($errs, 0, 2)) . PHP_EOL);
    }
}
check('全组件空数据渲染无异常/警告（' . count($compClasses) . ' 个组件）', $bad === 0);

/* 25. 安全性：用户输入经转义，不外泄原始脚本 / 不破坏属性
   - ActivityFeed.text 为纯文本槽位，应被转义（无裸 <script>）
   - ActivityFeed.href / target 等属性值经 e() 转义，引号与 < 不逃逸 */
Assets::reset();
$xss = '<script>alert(1)</script>';
$break = '" onmouseover="alert(2)" x="';
$sec = (string) XfAdmin::activityFeed(['items' => [
    ['user' => $xss, 'action' => $xss, 'text' => $xss, 'time' => $xss,
     'href' => $break, 'target' => $xss],
]]);
check('ActivityFeed 纯文本槽位转义：无裸 <script>', ! str_contains($sec, '<script>alert(1)</script>'));
check('ActivityFeed 属性值转义：引号/事件处理器不逃逸', ! str_contains($sec, 'onmouseover="alert(2)"'));
check('ActivityFeed 属性值转义：< 被转义为 &lt;', str_contains($sec, '&lt;script&gt;'));

/* 26. DataTable 交互增强：bulk / row_detail / row_group / state_save / export */
Assets::reset();
$dtRows = [
    ['id' => 1, 'name' => '张三', 'dept' => '技术部'],
    ['id' => 2, 'name' => '李四', 'dept' => '市场部'],
];
$dtHtml = (string) XfAdmin::datatable([
    'id'         => 'dt-reg-enh',
    'data'       => $dtRows,
    'state_save' => true,
    'export'     => ['copy', 'csv'],
    'bulk'       => ['actions' => [
        ['label' => '删除', 'url' => '/x/del?ids={ids}', 'confirm' => '确认？', 'reload' => false],
    ]],
    'row_detail' => ['columns' => ['name', 'dept']],
    'row_group'  => 'dept',
    'columns'    => [
        ['data' => 'name', 'title' => '姓名'],
        ['data' => 'dept', 'title' => '部门'],
    ],
]);
check('DataTable stateSave 写入 dt 配置', str_contains($dtHtml, 'stateSave'));
check('DataTable rowDetail/bulk/rowGroup 写入 xf 配置',
    str_contains($dtHtml, 'rowDetail') && str_contains($dtHtml, 'bulk') && str_contains($dtHtml, 'rowGroup'));
check('DataTable 辅助列注入（选择列在前、明细列次之）',
    str_contains($dtHtml, 'xf-dt-select-col') && str_contains($dtHtml, 'xf-dt-detail-col')
    && strpos($dtHtml, 'xf-dt-select-col') < strpos($dtHtml, 'xf-dt-detail-col'));
check('DataTable 批量操作栏渲染（含 {ids} 占位与确认文案）',
    str_contains($dtHtml, 'xf-dt-bulk') && str_contains($dtHtml, 'data-xf-bulk-action')
    && str_contains($dtHtml, '{ids}') && str_contains($dtHtml, 'data-reload="0"'));
check('DataTable export 生成导出按钮', str_contains($dtHtml, '"buttons"') || str_contains($dtHtml, 'buttons'));
check('DataTable thead th 带列 class', str_contains($dtHtml, '<th class="xf-dt-select-col">'));

/* 27. DataTable row_group 字符串写法兼容 + row_detail 与响应式互斥 */
Assets::reset();
$dtHtml2 = (string) XfAdmin::datatable([
    'id'         => 'dt-reg-rg',
    'data'       => $dtRows,
    'responsive' => true,
    'row_detail' => true,
    'columns'    => [['data' => 'name', 'title' => '姓名']],
]);
check('DataTable row_detail 启用时关闭 responsive（首列互斥）', ! str_contains($dtHtml2, '"responsive":true'));

/* 28. DataTable data-xf-config JSON 特殊字符安全（< > & 不裸露在属性中） */
Assets::reset();
$dtHtml3 = (string) XfAdmin::datatable([
    'id'      => 'dt-reg-xss',
    'data'    => [['id' => 1, 'name' => 'x']],
    'bulk'    => ['actions' => [['label' => '</div><script>alert(1)</script>', 'url' => '/x?a=1&b=2']]],
    'columns' => [['data' => 'name', 'title' => '姓名']],
]);
preg_match('/data-xf-config="([^"]*)"/', $dtHtml3, $xm);
check('DataTable 配置 JSON 中 </script> 不裸露', isset($xm[1]) && ! str_contains($xm[1], '</script>'));

/* 29. XfAdmin::has() 大小写不敏感（与 component() 兜底一致） */
check('XfAdmin::has 精确命中', XfAdmin::has('dataTable'));
check('XfAdmin::has 大小写不敏感命中 datatable', XfAdmin::has('datatable'));
check('XfAdmin::has 大小写不敏感命中 DataTable', XfAdmin::has('DataTable'));
check('XfAdmin::has 不存在别名返回 false', ! XfAdmin::has('noSuchComponent_xyz'));

/* 30. DataTable scroll_y / scroll_x / fixed_columns 接线（横向滚动 + sticky 固定列） */
Assets::reset();
$dtScroll = (string) XfAdmin::datatable([
    'id'        => 'dt-reg-scroll',
    'data'      => [['id' => 1, 'name' => '张三']],
    'scroll_y'  => '420px',
    'scroll_x'  => true,
    'fixed_columns' => ['left' => 1, 'right' => 1],
    'columns'   => [['data' => 'name', 'title' => '姓名']],
]);
preg_match('/data-xf-config="([^"]*)"/', $dtScroll, $xs);
$scfg = isset($xs[1]) ? json_decode(html_entity_decode($xs[1], ENT_QUOTES), true) : [];
check('DataTable scrollY 写入配置', isset($scfg['dt']['scrollY']) && $scfg['dt']['scrollY'] === '420px');
check('DataTable scrollX 写入配置', ! empty($scfg['dt']['scrollX']));
check('DataTable 固定列 → fixedColumns 左右都下发', isset($scfg['fixedColumns']['left'], $scfg['fixedColumns']['right']) && $scfg['fixedColumns']['left'] === 1 && $scfg['fixedColumns']['right'] === 1);

/* 31. DataTable 客户端大数据集自动 deferRender（阈值 100） */
Assets::reset();
$big = [];
for ($i = 1; $i <= 150; $i++) $big[] = ['id' => $i, 'name' => 'U' . $i];
$dtBig = (string) XfAdmin::datatable(['id' => 'dt-reg-big', 'data' => $big, 'columns' => [['data' => 'name', 'title' => '姓名']]]);
preg_match('/data-xf-config="([^"]*)"/', $dtBig, $xb);
$bcfg = isset($xb[1]) ? json_decode(html_entity_decode($xb[1], ENT_QUOTES), true) : [];
check('DataTable 150 行客户端数据自动开启 deferRender', ! empty($bcfg['dt']['deferRender']));

/* 32. DataTable 小数据集不开启 deferRender（保持 select-all 行为）/ 显式 defer_render=false 可禁用 */
Assets::reset();
$small = (string) XfAdmin::datatable(['id' => 'dt-reg-small', 'data' => [['id' => 1, 'name' => 'x']], 'columns' => [['data' => 'name', 'title' => '姓名']]]);
preg_match('/data-xf-config="([^"]*)"/', $small, $xs2);
$scfg2 = isset($xs2[1]) ? json_decode(html_entity_decode($xs2[1], ENT_QUOTES), true) : [];
check('DataTable 小数据集不自动开启 deferRender', empty($scfg2['dt']['deferRender']));
Assets::reset();
$noDefer = (string) XfAdmin::datatable(['id' => 'dt-reg-nodef', 'data' => $big, 'defer_render' => false, 'columns' => [['data' => 'name', 'title' => '姓名']]]);
preg_match('/data-xf-config="([^"]*)"/', $noDefer, $xn);
$ncfg = isset($xn[1]) ? json_decode(html_entity_decode($xn[1], ENT_QUOTES), true) : [];
check('DataTable defer_render=false 显式禁用', empty($ncfg['dt']['deferRender']));

/* 33. DataTable 边界：空 columns + 空 data 不抛异常、可正常渲染空 tbody */
Assets::reset();
$emptyDt = (string) XfAdmin::datatable(['id' => 'dt-reg-empty', 'data' => [], 'columns' => []]);
// 注：表头现默认带模板风格 class（thead-sm text-uppercase fs-xxs），故断言 <thead 前缀而非 <thead>
check('DataTable 空 columns/data 正常渲染（含 thead/tbody 容器）', str_contains($emptyDt, 'data-xf="datatable"') && str_contains($emptyDt, '<thead') && str_contains($emptyDt, '<tbody>'));

/* 33b. DataTable 默认外观与 INSPINIA 模板一致（斑马纹 + 垂直居中 + 模板表头） */
Assets::reset();
$tplLook = (string) XfAdmin::datatable(['id' => 'dt-reg-look', 'data' => [], 'columns' => [['data' => 'a', 'title' => 'A']]]);
check('DataTable 默认 table-striped（同模板）', str_contains($tplLook, 'table-striped'));
check('DataTable 默认 align-middle（同模板）', str_contains($tplLook, 'align-middle'));
check('DataTable 默认表头 thead-sm text-uppercase fs-xxs（同模板）', str_contains($tplLook, 'thead-sm text-uppercase fs-xxs'));

/* 34. XfAdmin::asset / img 幂等性与外链透传 */
Assets::reset();
$a1 = XfAdmin::asset('images/users/1.jpg');
$a2 = XfAdmin::asset($a1);   // 二次 asset 不应拼两遍
check('XfAdmin::asset 幂等（不重复拼接基址）', $a1 === $a2 && substr_count($a1, '//') <= 1);
check('XfAdmin::img 外链原样返回', XfAdmin::img('https://example.com/x.png') === 'https://example.com/x.png');
check('XfAdmin::img data:uri 原样返回', XfAdmin::img('data:image/png;base64,AAAA') === 'data:image/png;base64,AAAA');
check('XfAdmin::img 相对路径经 asset 解析', str_starts_with(XfAdmin::img('users/1.jpg'), 'http') || str_contains(XfAdmin::img('users/1.jpg'), '/zxf/xfadmin/'));

/* 35. 新组件：MetricCard 指标卡（计数动画 + 迷你图配置） */
Assets::reset();
$mc = (string) XfAdmin::metricCard(['title' => '收入', 'value' => 1234, 'prefix' => '¥', 'trend' => 5.5, 'chart' => 'donut', 'data' => [1, 2, 3]]);
check('MetricCard 输出计数元素与迷你图配置', str_contains($mc, 'data-xf-count="1234"') && str_contains($mc, 'data-xf="metric-chart"') && str_contains($mc, 'text-success'));
$mcDown = (string) XfAdmin::metricCard(['title' => 'x', 'value' => 1, 'trend' => -2, 'chart' => null]);
check('MetricCard 负趋势红色下降箭头', str_contains($mcDown, 'text-danger') && str_contains($mcDown, 'arrow-down'));

/* 36. 新组件：Animate 动画包装（load 直接输出类 / hover 延迟触发） */
Assets::reset();
$anLoad = (string) XfAdmin::animate(['animation' => 'bounce', 'trigger' => 'load', 'content' => 'X']);
check('Animate load 触发直接输出 animate__ 类', str_contains($anLoad, 'animate__animated') && str_contains($anLoad, 'animate__bounce'));
$anHover = (string) XfAdmin::animate(['animation' => 'pulse', 'trigger' => 'hover', 'infinite' => true, 'content' => 'X']);
check('Animate hover 触发不预置动画类且含修饰类', ! str_contains($anHover, 'animate__pulse') && str_contains($anHover, 'animate__infinite') && str_contains($anHover, 'data-xf-trigger="hover"'));

/* 37. 新组件：GoogleMap（免 Key iframe 嵌入）与 ApexTree（头像解析） */
Assets::reset();
$gm = (string) XfAdmin::googleMap(['place' => '北京', 'zoom' => 10, 'maptype' => 'satellite']);
check('GoogleMap 输出 iframe 嵌入且带卫星图层参数', str_contains($gm, 'maps.google.com/maps') && str_contains($gm, 'output=embed') && str_contains($gm, 't=k'));
$at = (string) XfAdmin::apexTree(['data' => ['id' => '1', 'name' => 'A', 'avatar' => 'users/user-1.jpg', 'children' => [['id' => '2', 'name' => 'B']]]]);
preg_match('/data-xf-config="([^"]*)"/', $at, $atm);
$atCfg = isset($atm[1]) ? json_decode(html_entity_decode($atm[1], ENT_QUOTES), true) : [];
check('ApexTree 输出配置且头像经 img 解析', str_contains($at, 'data-xf="apextree"') && str_contains((string) ($atCfg['data']['avatar'] ?? ''), '/zxf/xfadmin/images/users/user-1.jpg'));

/* 37b. 新组件：ApexSankey（桑基图，nodes/edges 结构 + 字符串节点简写） */
Assets::reset();
$sk = (string) XfAdmin::apexSankey([
    'height' => 360,
    'nodes'  => ['oil', ['id' => 'coal', 'title' => '煤炭', 'color' => '#fa5c7c']],
    'edges'  => [['source' => 'oil', 'target' => 'energy', 'value' => 15, 'color' => '#ffe5eb']],
]);
check('ApexSankey 输出 data-xf 声明与容器', str_contains($sk, 'data-xf="apexsankey"') && str_contains($sk, 'class="xf-apexsankey"'));
preg_match('/data-xf-config="([^"]*)"/', $sk, $skm);
$skCfg = isset($skm[1]) ? json_decode(html_entity_decode($skm[1], ENT_QUOTES), true) : [];
check('ApexSankey 节点规范：字符串简写补 title、id 保留、连线保留 value/color',
    isset($skCfg['nodes'][0]['title']) && $skCfg['nodes'][0]['title'] === 'oil'
    && $skCfg['nodes'][1]['title'] === '煤炭' && ($skCfg['nodes'][1]['color'] ?? '') === '#fa5c7c'
    && $skCfg['edges'][0]['value'] === 15 && ($skCfg['edges'][0]['color'] ?? '') === '#ffe5eb');
check('ApexSankey 资源接线（apexsankey + 全局 SVG 隔离护栏 + svg.min.js）',
    str_contains(Assets::instance()->scripts(), 'apexsankey.min.js')
    && str_contains(Assets::instance()->scripts(), 'svg.min.js')
    && str_contains(Assets::instance()->scripts(), 'svg-guard-pre.js')
    && str_contains(Assets::instance()->scripts(), 'svg-guard-post.js'));
check('ApexSankey 加载顺序：护栏在前、svg 在中、apexsankey 在后、后置护栏收尾',
    (function ($s) {
        $pre = strpos($s, 'svg-guard-pre.js');
        $svg = strpos($s, 'svg.min.js');
        $ask = strpos($s, 'apexsankey.min.js');
        $post = strpos($s, 'svg-guard-post.js');
        return $pre !== false && $svg !== false && $ask !== false && $post !== false
            && $pre < $svg && $svg < $ask && $ask < $post;
    })(Assets::instance()->scripts()));


/* 38. 新组件：Terms（有目录启用 scrollspy / 无目录不启用，避免 #! 选择器异常） */
Assets::reset();
$tWith = (string) XfAdmin::terms(['sections' => [['id' => 'a', 'title' => 'A', 'content' => 'x']], 'toc' => true]);
$tNo   = (string) XfAdmin::terms(['sections' => [['id' => 'a', 'title' => 'A', 'content' => 'x']], 'toc' => false]);
check('Terms 有目录时启用 scrollspy', str_contains($tWith, 'data-bs-spy="scroll"'));
check('Terms 无目录时不输出 scrollspy 属性', ! str_contains($tNo, 'data-bs-spy'));

/* 39. DataTable：page 弹窗单元格 / js: 自定义渲染器 / filter_bar 自定义 html 控件 */
Assets::reset();
$dtNew = (string) XfAdmin::datatable([
    'id' => 'dt-reg-new', 'data' => [['id' => 1, 'name' => 'x']],
    'columns' => [
        ['data' => 'name', 'title' => '名', 'render' => ['type' => 'page', 'url' => '/e/{id}', 'title' => '编辑 #{id}']],
        ['data' => 'id', 'title' => 'ID', 'render' => 'js:myCell'],
    ],
    'filter_bar' => [
        ['type' => 'search', 'name' => 'kw', 'label' => '关键词'],
        ['html' => '<input class="xf-filter" data-filter="grade" value="">', 'width' => 'col-3'],
    ],
]);
check('DataTable page 渲染器进入列配置', str_contains($dtNew, 'page') && str_contains($dtNew, '/e/{id}'));
check('DataTable js: 自定义渲染器进入列配置', str_contains($dtNew, 'js:myCell'));
check('DataTable filter_bar 自定义 html 控件原样注入', str_contains($dtNew, 'data-filter="grade"') && str_contains($dtNew, 'col-3'));

/* 40. Form：horizontal 布局输出 Grid 类与标签宽度变量 */
Assets::reset();
$fh = (string) XfAdmin::form(['layout' => 'horizontal', 'label_width' => 200, 'fields' => ['<div class="mb-3"><label class="form-label">A</label><input class="form-control"></div>']]);
check('Form horizontal 布局输出 xf-form-horizontal 与 --xf-label-width', str_contains($fh, 'xf-form-horizontal') && str_contains($fh, '--xf-label-width:200px'));
$fi = (string) XfAdmin::form(['layout' => 'inline', 'fields' => ['<input>']]);
check('Form layout=inline 等价旧 inline 写法', str_contains($fi, 'row-cols-lg-auto'));

/* 41. DataTable qr 渲染器：按需注入 qrcode 库 + 列配置含 type=qr（支持 URL/文本/中文） */
Assets::reset();
$dtQr = (string) XfAdmin::datatable([
    'id'      => 'dt-reg-qr',
    'data'    => [['id' => 1, 'url' => 'https://example.com/1']],
    'columns' => [
        ['data' => 'url', 'title' => '二维码', 'render' => ['type' => 'qr', 'text' => '{url}', 'size' => 96]],
    ],
]);
check('DataTable qr 列自动注入 qrcode.min.js', str_contains(Assets::instance()->scripts(), 'qrcode.min.js'));
preg_match('/data-xf-config="([^"]*)"/', $dtQr, $xq);
$qcfg = isset($xq[1]) ? json_decode(html_entity_decode($xq[1], ENT_QUOTES), true) : [];
$foundQr = false;
foreach (($qcfg['dt']['columns'] ?? []) as $c) {
    if (($c['xfRender']['type'] ?? null) === 'qr') { $foundQr = true; break; }
}
check('DataTable qr 列配置含 type=qr', $foundQr);

echo PHP_EOL . ($fail === 0 ? 'ALL REGRESSION PASSED' : "{$fail} FAILED") . PHP_EOL;
exit($fail === 0 ? 0 : 1);

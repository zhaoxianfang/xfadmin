<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

echo XfAdmin::page([
    'title'       => '业务应用 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/apps',
    'sidenav'     => ['user' => $user + ['avatar' => xf_asset('images/users/user-2.jpg')]],
    'topbar'      => ['user' => $user + ['avatar' => xf_asset('images/users/user-2.jpg')]],
    'page_title'  => ['title' => '业务应用组件', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '业务应用']]],
    'content'     => [
        XfAdmin::alert([
            'variant' => 'primary', 'icon' => 'ti ti-apps',
            'text'    => '本页集中演示全部业务 / 数据 / 导航 / 杂项组件，便于对照组件文档（docs/components-reference.md）查看实际渲染效果。',
        ]),

        // 导航：无限极菜单
        XfAdmin::card([
            'title' => '无限极菜单 Menu',
            'body'  => XfAdmin::menu([
                'mode'       => 'side',
                'current_url' => '/apps',
                'items' => [
                    ['title' => '分组'],
                    ['text' => '仪表盘', 'icon' => 'ti ti-layout-dashboard', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
                    ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
                        ['text' => '用户管理', 'url' => '/users'],
                        ['text' => '更多', 'children' => [['text' => '深层菜单', 'url' => '/deep']]],
                    ]],
                ],
            ]),
        ]),

        // 订单列表 + 订单详情
        XfAdmin::orders([
            'title'  => '订单管理 Orders',
            'orders' => [
                ['id' => '#ORD-1001', 'customer' => '张三', 'avatar' => 'users/user-1.jpg', 'email' => 'z@x.com',
                 'date' => '2026-07-20', 'time' => '10:10', 'items' => 3, 'total' => '¥320.00',
                 'status' => 'completed', 'paid' => true, 'payment' => '支付宝'],
                ['id' => '#ORD-1002', 'customer' => '李四', 'avatar' => 'users/user-2.jpg', 'email' => 'l@x.com',
                 'date' => '2026-07-21', 'time' => '14:02', 'items' => 1, 'total' => '¥88.00',
                 'status' => 'pending', 'paid' => false, 'payment' => '微信'],
            ],
            'searchable' => true, 'filterable' => true, 'selectable' => true, 'page_size' => 10,
        ]),
        XfAdmin::orderDetails([
            'order' => [
                'id' => '#ORD-1001', 'date' => '2026-07-20', 'status' => 'completed',
                'customer' => ['name' => '张三', 'email' => 'z@x.com', 'phone' => '138-0000-0000', 'address' => '北京市朝阳区'],
                'items' => [['name' => '无线耳机', 'sku' => 'SKU-001', 'qty' => 2, 'price' => '¥160.00', 'image' => 'products/1.png']],
                'subtotal' => '¥320.00', 'discount' => '-¥0', 'shipping' => '¥0', 'tax' => '¥0', 'total' => '¥320.00',
                'timeline' => [['title' => '已下单', 'time' => '07-20 10:00', 'done' => true], ['title' => '已发货', 'time' => '07-21 09:00', 'done' => true]],
                'notes' => '请尽快发货',
            ],
        ]),

        // 客户 / 客户(CRM) / 卖家
        XfAdmin::customers([
            'title' => '客户管理 Customers', 'view' => 'grid',
            'items' => [
                ['name' => '张三', 'email' => 'z@x.com', 'phone' => '138-0000-0000', 'avatar' => 'users/user-1.jpg',
                 'company' => 'XX 科技', 'location' => '北京', 'status' => 'active', 'tags' => ['企业', '复购'], 'orders' => 12, 'spent' => '¥1,200'],
                ['name' => '李四', 'email' => 'l@x.com', 'phone' => '139-0000-0000', 'avatar' => 'users/user-2.jpg',
                 'company' => 'YY 网络', 'location' => '上海', 'status' => 'vip', 'tags' => ['VIP'], 'orders' => 30, 'spent' => '¥9,800'],
            ],
        ]),
        XfAdmin::clients([
            'title' => '客户列表(CRM) Clients', 'searchable' => true, 'type_filter' => ['全部', 'VIP', '潜在'], 'add_text' => '新增客户',
            'clients' => [
                ['name' => 'Emily Parker', 'email' => 'emily@startupwave.io', 'avatar' => 'users/user-7.jpg',
                 'phone' => '+1 202-555-0147', 'country' => '美国', 'enrolled' => '2026-03-12', 'type' => 'VIP',
                 'job_title' => '采购总监', 'status' => ['text' => '活跃', 'variant' => 'success'], 'url' => '#'],
            ],
        ]),
        XfAdmin::sellers([
            'title' => '卖家管理 Sellers', 'searchable' => true, 'add_text' => '新增卖家',
            'sellers' => [
                ['name' => '北岸数码', 'avatar' => 'sellers/3.png', 'products' => 142, 'orders' => 3180, 'rating' => 4.8,
                 'location' => '深圳', 'balance' => '¥1.28M', 'rank' => '#2', 'status' => ['text' => '活跃', 'variant' => 'success']],
            ],
        ]),

        // 项目相关
        XfAdmin::projects([
            'title' => '项目管理 Projects',
            'projects' => [
                ['name' => '官网改版', 'client' => 'XX 集团', 'description' => '企业官网焕新', 'progress' => 75, 'status' => 'active',
                 'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k', 'tasks_done' => 20, 'tasks_total' => 30,
                 'members' => ['users/user-1.jpg', 'users/user-2.jpg'], 'color' => 'primary'],
            ],
        ]),
        XfAdmin::projectDetails([
            'project' => [
                'name' => '官网改版', 'client' => 'XX 集团', 'description' => '企业官网焕新', 'progress' => 75,
                'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k',
                'members' => [['name' => '张三', 'avatar' => 'users/user-1.jpg', 'role' => '设计']],
                'tasks' => [['title' => '首页设计', 'done' => true], ['title' => '前端开发', 'done' => false]],
                'activity' => [['user' => '李四', 'avatar' => 'users/user-2.jpg', 'text' => '更新了设计稿', 'time' => '2 小时前']],
                'files' => [['name' => '需求文档.pdf', 'size' => '1.2MB']],
            ],
        ]),
        XfAdmin::projectTeamBoard([
            'title' => '项目团队看板 ProjectTeamBoard', 'cols' => 3,
            'teams' => [
                ['code' => 'IT-01', 'name' => 'Design Team', 'badge' => ['text' => 'New', 'variant' => 'primary'],
                 'members' => ['users/user-7.jpg', 'users/user-8.jpg'], 'about' => '负责 UI/UX 设计。',
                 'projects' => 25, 'ranking' => '#5', 'budgets' => '$20.3M', 'progress' => 65, 'updated' => '1 hour ago', 'url' => '#'],
            ],
        ]),

        // 角色权限
        XfAdmin::roles([
            'title' => '角色与权限 Roles',
            'roles' => [
                ['name' => '超级管理员', 'description' => '拥有全部权限', 'users_count' => 1, 'permissions_count' => 48, 'color' => 'danger', 'guard' => 'admin'],
                ['name' => '编辑', 'description' => '内容管理', 'users_count' => 8, 'permissions_count' => 20, 'color' => 'info', 'guard' => 'web'],
            ],
        ]),

        // 个人主页
        XfAdmin::profilePage([
            'cover'  => 'stock/small-1.jpg',
            'avatar' => 'users/user-1.jpg', 'name' => '张伟', 'verified' => true, 'role' => '高级产品经理',
            'meta'   => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
            'stats'  => [['value' => '128', 'label' => '项目'], ['value' => '3.2k', 'label' => '粉丝']],
            'actions' => [['text' => '关注', 'class' => 'btn-primary', 'icon' => 'ti ti-user-plus']],
            'tabs'   => [
                ['title' => '动态', 'content' => '<p>这是个人主页动态区。</p>', 'active' => true],
                ['title' => '设置', 'content' => '<p>这是设置区。</p>'],
            ],
        ]),

        // 邮件应用三栏
        XfAdmin::emailApp([
            'folders'  => [['icon' => 'ti ti-inbox', 'name' => '收件箱', 'count' => 12, 'active' => true]],
            'messages' => [
                ['id' => 1, 'from' => '张三', 'avatar' => 'users/user-1.jpg', 'from_email' => 'z@x.com',
                 'subject' => '周末计划', 'preview' => '附件请查收…', 'time' => '10:30', 'unread' => true, 'starred' => false, 'attachments' => 1],
            ],
            'selected' => ['from' => '李四', 'from_email' => 'l@x.com', 'subject' => 'Re:周末计划', 'time' => '昨天', 'body' => '<p>收到，周六见。</p>',
                           'attachments' => [['name' => 'a.pdf', 'size' => '1MB']]],
            'view' => 'split',
        ]),
        XfAdmin::outlook([
            'folders'  => [['icon' => 'ti ti-inbox', 'name' => '收件箱', 'count' => 12, 'active' => true]],
            'messages' => [
                ['id' => 1, 'from' => '张三', 'avatar' => 'users/user-1.jpg', 'from_email' => 'z@x.com',
                 'subject' => '周末计划', 'preview' => '附件请查收…', 'time' => '10:30', 'unread' => true, 'starred' => false, 'attachments' => 1],
            ],
            'selected' => ['from' => '李四', 'from_email' => 'l@x.com', 'subject' => 'Re:周末计划', 'time' => '昨天', 'body' => '<p>收到，周六见。</p>',
                           'attachments' => [['name' => 'a.pdf', 'size' => '1MB']]],
        ]),

        // 聊天应用
        XfAdmin::chatApp([
            'conversations' => [
                ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'last' => '好的，明天见', 'time' => '10:02',
                 'unread' => 2, 'active' => true, 'online' => true, 'url' => '#'],
            ],
            'peer' => ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'online' => true, 'status' => '在线'],
            'messages' => [
                ['from' => 'other', 'text' => '你好！', 'time' => '09:58'],
                ['from' => 'me', 'text' => '在的，请讲', 'time' => '09:59'],
            ],
            'placeholder' => '输入消息…',
        ]),

        // 博客文章 + 论坛主题
        XfAdmin::blogArticle([
            'article' => [
                'title' => '如何高效搭建后台', 'category' => '技术', 'date' => '2026-07-20', 'read_time' => '8 分钟',
                'author' => ['name' => '张三', 'avatar' => 'users/user-1.jpg', 'bio' => '资深后端工程师'],
                'cover' => 'gallery/12.jpg',
                'body' => ['第一段正文内容。', '第二段正文内容，介绍组件化思想。'],
                'tags' => ['PHP', 'Laravel'],
                'related' => [['title' => '相关文章', 'excerpt' => '摘要…', 'image' => 'gallery/3.jpg', 'date' => '2026-07-10']],
            ],
        ]),
        XfAdmin::forumThread([
            'thread' => ['title' => '如何优化 Laravel 性能？', 'category' => '技术问答',
                         'author' => ['name' => '张三', 'avatar' => 'users/user-1.jpg'],
                         'views' => 320, 'replies' => 3, 'created_at' => '2026-07-20', 'body' => '如题，求经验。', 'tags' => ['PHP', 'Laravel']],
            'posts' => [
                ['author' => ['name' => '李四', 'avatar' => 'users/user-2.jpg', 'role' => '版主'], 'created_at' => '2026-07-21',
                 'body' => '用缓存与队列。', 'likes' => 12, 'is_solution' => true, 'attachments' => [['name' => 'code.php']]],
            ],
        ]),

        // 公司卡片 + 联系人卡片
        XfAdmin::companyCard([
            'cols' => 2,
            'companies' => [
                ['logo' => 'logos/amazon.svg', 'name' => '云杉科技', 'website' => 'www.example.com',
                 'tags' => [['text' => '上海', 'icon' => 'ti ti-map-pin', 'variant' => 'primary'],
                            ['text' => '电商', 'icon' => 'ti ti-shopping-cart', 'variant' => 'success']],
                 'desc' => '专注于企业级 SaaS。', 'stats' => ['员工' => '1200+', '年营收' => '¥5.1亿'], 'rating' => 4, 'follow' => true],
            ],
        ]),
        XfAdmin::contactCard([
            'cols' => 3,
            'contacts' => [
                ['avatar' => 'users/user-5.jpg', 'name' => '苏菲', 'role' => '首席 UI/UX 设计师', 'tag' => '管理员',
                 'rating' => 4.8, 'email' => 'sophia@example.com', 'phone' => '138-0000-0000', 'location' => '上海', 'url' => '#'],
            ],
        ]),

        // 商品评价 + 商品详情
        XfAdmin::reviewList([
            'title' => '商品评价 ReviewList',
            'summary' => ['avg' => 4.92, 'total' => 245,
                'dist' => [['star' => 5, 'count' => 128], ['star' => 4, 'count' => 24]],
                'note' => '来自真实购买用户的反馈', 'badge' => '+12 new this week'],
            'reviews' => [
                ['product_img' => 'products/2.png', 'product' => 'Wireless Earbuds', 'reviewer_avatar' => 'users/user-8.jpg',
                 'reviewer' => 'Sophia Lee', 'reviewer_email' => 'sophia.lee@digitalshop.com', 'rating' => 5,
                 'comment' => '音质出色。', 'date' => '2026-07-18', 'status' => ['text' => '已审核', 'variant' => 'success']],
            ],
        ]),
        XfAdmin::productDetails([
            'product' => [
                'name' => '无线降噪耳机', 'sku' => 'SKU-001', 'price' => '¥899', 'old_price' => '¥1099',
                'rating' => 4.5, 'reviews' => 128, 'stock' => '有货',
                'images' => ['products/1.png', 'products/2.png'],
                'description' => '主动降噪，40 小时续航。', 'features' => ['主动降噪', '40h 续航'],
                'variants' => ['color' => ['黑', '白', '蓝'], 'size' => ['S', 'M', 'L']],
                'category' => '数码', 'brand' => 'Acme',
                'tabs' => [['title' => '规格', 'body' => '详细规格…'], ['title' => '评价', 'body' => '用户评价…']],
                'related' => [['title' => '相关商品', 'price' => '¥199', 'image' => 'products/3.png']],
            ],
        ]),

        // 待办 / 投票 / 问题跟踪 / 贴板 / 瀑布流
        XfAdmin::todoList([
            'title' => '今日任务 TodoList',
            'items' => [
                ['text' => '回顾周报', 'done' => true, 'priority' => 'high'],
                ['text' => '发布版本', 'done' => false, 'priority' => 'medium'],
            ],
            'addable' => true,
        ]),
        XfAdmin::voteList([
            'title' => '社区投票 VoteList',
            'items' => [
                ['votes' => 35, 'title' => '远程办公是否应成为长期选项？', 'desc' => '本投票探讨工作方式。',
                 'author' => ['avatar' => 'users/user-7.jpg', 'name' => '陈晓'], 'date' => '2026-01-12', 'tag' => '职场',
                 'comments' => 89, 'ends' => '5 天后', 'total' => 1284, 'status' => '进行中', 'variant' => 'success', 'url' => '#'],
            ],
        ]),
        XfAdmin::issueTracker([
            'title' => '问题列表 IssueTracker', 'searchable' => true, 'add_text' => '新建问题',
            'issues' => [
                ['id' => 'ISSUE-104', 'title' => '移动端用户资料无法保存', 'status' => '进行中', 'variant' => 'warning',
                 'assignee' => ['avatar' => 'users/user-3.jpg', 'name' => '李雷'], 'created' => '2026-02-10',
                 'due' => '2026-02-18', 'labels' => ['Bug', 'Mobile'], 'progress' => 60, 'comments' => 8, 'url' => '#'],
            ],
        ]),
        XfAdmin::pinBoard([
            'notes' => [
                ['color' => 'warning', 'title' => '设计评审', 'text' => '周五前确认首页视觉', 'author' => '张三', 'time' => '10:30'],
                ['color' => 'info', 'title' => 'Bug 修复', 'text' => '登录页 500', 'author' => '李四', 'time' => '昨天'],
            ],
        ]),
        XfAdmin::masonry([
            'columns' => 3, 'gap' => 4,
            'items' => [
                '<div class="card p-3">瀑布流卡片 A</div>',
                '<div class="card p-3">瀑布流卡片 B</div>',
                ['html' => '<div class="card p-3">瀑布流卡片 C</div>'],
            ],
        ]),

        // 发票详情 + 发票创建
        XfAdmin::invoiceDetail([
            'logo'   => '', 'title' => 'XF Admin', 'number' => 'INV-2026-0728', 'status' => ['text' => '待付款', 'color' => 'warning'],
            'meta'   => [['label' => '开票日期', 'value' => '2026-07-28']],
            'from'   => ['name' => '深圳某某科技', 'lines' => ['南山区…', 'tax@x.com']],
            'to'     => ['name' => '北京某某集团', 'lines' => ['朝阳区…']],
            'items'  => [['name' => '企业版授权', 'desc' => '1 年', 'qty' => 2, 'price' => 4999]],
            'currency' => '¥',
            'summary'  => [['label' => '小计', 'value' => '¥9,998.00'], ['label' => '合计', 'value' => '¥11,097.78', 'strong' => true]],
            'notes'    => '请于 15 日内完成付款。',
            'actions'  => [['text' => '打印', 'icon' => 'ti ti-printer', 'class' => 'btn-soft-secondary', 'onclick' => 'window.print()']],
        ]),
        XfAdmin::invoiceCreate([
            'invoice' => [
                'number' => 'INV-2026-001',
                'from' => ['name' => '我方公司', 'address' => '…', 'email' => 'billing@x.com'],
                'to'   => ['name' => '客户公司', 'address' => '…', 'email' => 'c@x.com'],
                'items' => [['description' => '网站设计', 'qty' => 1, 'rate' => '¥8000', 'amount' => '¥8000']],
                'tax_rate' => 6, 'discount' => '¥0', 'notes' => '…', 'terms' => '…',
            ],
        ]),
    ],
]);

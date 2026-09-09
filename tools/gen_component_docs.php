<?php

/**
 * XfAdmin 组件参考文档生成器
 *
 * 从源码反射 + 注释解析，生成 docs/03-组件参考/ 下的分类组件文档：
 *   - 组件用途（取 src/XfAdmin.php 的 @method 中文注释，回退类 docblock）
 *   - 用法示例（取类 docblock 中的 XfAdmin::xxx([...]) 代码块）
 *   - 完整参数表（取 defaults()，含键名、默认值、类型、行尾中文注释）
 *   - 依赖插件（assets()）
 *
 * 用法：php tools/gen_component_docs.php
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require __DIR__ . '/../vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

$root = dirname(__DIR__);

// ------------------------------------------------------------------
// 1. 分组定义：输出文件 => [标题, 组件别名数组]
// ------------------------------------------------------------------
$GROUPS = [
    '01-layout.md' => [
        'title' => '布局 · 导航 · 栅格',
        'desc'  => '整页骨架、认证页、错误页、侧边栏、顶栏、水平导航、页脚、定制面板、面包屑与栅格系统。',
        'intro' => [
            '## 本章导读',
            '',
            '布局组件负责输出**完整 HTML 文档**或页面骨架，是后台页面的入口。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::page([',
            "    'title'      => '用户管理',",
            "    'menu'       => \$menu,              // 自动下发给 Sidenav / TopNav",
            "    'current_url'=> request()->path(),   // 自动高亮当前菜单",
            "    'topbar'     => ['search' => true, 'user' => [...]],",
            "    'page_title' => ['title' => '用户管理', 'breadcrumb' => [...]],",
            "    'content'    => \$content,           // 由 card / row / col / dataTable 等拼装",
            ']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `Page` **先渲染 body 再输出 head**（组件在渲染时注册 CSS/JS）；',
            '- 水平布局三种触发：`layout => horizontal` / `layout => topnav` / `topnav => [...]`；',
            '- 主题属性（`data-skin`、`data-bs-theme`、`data-menu-color` 等）必须显式输出，',
            '  模板 `config.js` 把「属性缺失」视为 `modern` / `gradient`；',
            '- `Page` **没有** `brand` / `dir` / `meta` / `head_extra` / `styles` 参数，',
            '  品牌与页脚走全局配置 `XfAdmin::setting(...)`；',
            '- `XfAdmin::lockScreen()` 得到的是独立 `LockScreen` 组件，',
            '  要用 AuthPage 的锁屏语义请写 `XfAdmin::authPage([\'type\' => \'lock-screen\'])`。',
            '',
        ],
        'aliases' => [
            'page', 'sidenav', 'topbar', 'topNav', 'topnav', 'pageTitle', 'footer', 'customizer',
            'authPage', 'signIn', 'signUp', 'resetPass', 'newPass', 'twoFactor',
            'lockScreen', 'deleteAccount', 'successMail', 'loginPin',
            'errorPage', 'comingSoon', 'maintenance', 'emptyState', 'landing',
            'accountSettingsPanel',
            'menu',
            'row', 'col',
        ],
    ],
    '02-ui.md' => [
        'title' => 'UI 基础组件',
        'desc'  => '卡片、按钮、徽章、模态框、选项卡、进度、头像、时间线、轮播等通用界面元素，共 55 个。',
        'intro' => [
            '## 本章导读',
            '',
            'UI 组件是构建页面的积木，可无限嵌套混用。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::card([',
            "    'title'  => '用户统计',",
            "    'tools'  => ['collapse', 'refresh', 'close'],   // 卡片工具按钮",
            "    'body'   => XfAdmin::row(['cols' => [",
            "        ['width' => 6, 'content' => XfAdmin::statCard([...])],",
            "        ['width' => 6, 'content' => XfAdmin::progress(['value' => 68])],",
            '    ]]),',
            "    'footer' => XfAdmin::button(['label' => '查看详情', 'variant' => 'primary']),",
            ']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- **文本槽位**（`title`/`label`/`text`）自动转义；**内容槽位**（`body`/`content`/`footer`）原样输出 HTML；',
            '- `variant` 走 `ENUM_VARIANT` 白名单（primary/secondary/success/danger/warning/info/light/dark/link），',
            '  非法值回退默认；',
            '- `size` 仅 `sm` / `lg`，非法值可能回退 `lg`；',
            '- 图标统一用 Tabler（`ti ti-*`），不要使用 `data-lucide`；',
            '- 需要 JS 定位元素时**显式传 `id`**（自动 id 为自增 `xf-N`，不稳定）；',
            '- `Modal` / `Offcanvas` 依赖 Bootstrap JS，确认 `XfAdmin::scripts()` 已输出。',
            '',
        ],
        'aliases' => [
            'card', 'statCard', 'alert', 'badge', 'button', 'dropdown', 'modal', 'offcanvas',
            'tabs', 'accordion', 'progress', 'spinner', 'pagination', 'listGroup',
            'avatar', 'avatarGroup', 'icon', 'iconSet', 'toast', 'timeline', 'carousel',
            'breadcrumb', 'tooltip', 'popover', 'placeholder', 'collapse', 'scrollspy',
            'ratio', 'rating', 'ribbon', 'chip', 'stepper', 'descriptionList',
            'loadingButton', 'divider', 'kbd', 'media', 'skeleton', 'switch', 'codeBlock',
            'empty', 'toolbar', 'searchBox', 'colorPalette', 'typography', 'utilities',
            'videoEmbed', 'commandPalette', 'notificationCenter', 'dropzoneUpload',
            'backToTop', 'callout', 'countdown', 'countUp', 'invoicePrintButton',
        ],
    ],
    '03-form.md' => [
        'title' => '表单组件',
        'desc'  => '表单容器与全部输入控件（输入/选择/复选/滑块/日期/编辑器/上传/颜色/标签/掩码/验证码/向导等）。',
        'intro' => [
            '## 本章导读',
            '',
            '表单组件分为**容器**（`form`）与**字段**（`input`/`select`/`check`/…）两类。',
            '大多数字段共享 `FieldWrapper` 提供的公共字段（见下方"公共字段"）。',
            '',
            '### 公共字段（FieldWrapper）',
            '',
            '| 参数 | 类型 | 默认 | 说明 |',
            '|---|---|---|---|',
            '| `name` | string\\|null | `null` | 字段名（空则不输出 `name`） |',
            '| `id` | string\\|null | `null` | 留空自动生成 |',
            '| `label` | string\\|null | `null` | `null` 时不渲染 `<label>` |',
            '| `help` | string\\|null | `null` | `.form-text`（强制转义） |',
            '| `required` | bool | `false` | 加 `required` + 红色星号 |',
            '| `disabled` | bool | `false` | |',
            '| `readonly` | bool | `false` | Select/Upload 未消费 |',
            '| `value` | mixed | `null` | 当前值 |',
            '| `placeholder` | string\\|null | `null` | |',
            '| `wrapper` | string\\|false\\|null | `\'mb-3\'` | 外层 div class；`false`/`null` 不包裹 |',
            '| `feedback` | array\\|null | `null` | `[\'valid\'=>…, \'invalid\'=>…]` |',
            '',
            '⚠️ **没有 `error` 键**（错误走 `feedback[\'invalid\']`）；**没有 `class` 键**（用 `->addClass()`）。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::form([',
            "    'action' => '/admin/users', 'method' => 'POST',",
            "    'ajax'   => true,        // data-xf-remote，前端托管提交",
            "    'csrf'   => true,        // ⚠️ 必须显式传，默认 [] 不注入",
            "    'fields' => [",
            "        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true]),",
            "        XfAdmin::select(['name' => 'role', 'label' => '角色', 'enhance' => 'choices', 'options' => [...]]),",
            '    ],',
            "    'buttons' => XfAdmin::button(['label' => '保存', 'type' => 'submit']),",
            ']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `Form` **不会自动渲染字段**，字段必须放进 `fields`；',
            '- `Form.csrf` 默认 `[]` → 默认不注入 `_token`；',
            '- `<form>` 恒输出 `method="POST"`，REST 请用 `_method` 隐藏域；',
            '- 子类 `defaults()` 用 `+` 合并 → 与 `fieldDefaults()` 同名键会被公共默认值覆盖；',
            '- `Select` 的 `groups` 非空会忽略 `options`；`placeholder` 仅单选生效；',
            '- `Input` 的 `mask` 优先于 `tags`；`MaskedInput` 的 `alias` 前端未消费；',
            '- `Tags`/`MaskedInput`/`Check`/`Captcha` 等未完全继承 FieldWrapper，参数表以各组件为准。',
            '',
        ],
        'aliases' => [
            'form', 'input', 'textarea', 'select', 'check', 'slider', 'dateRange',
            'dateRangePicker', 'datePicker', 'editor', 'upload', 'colorPicker', 'tags',
            'maskedInput', 'wizard', 'passwordStrength', 'captcha', 'twoFactorInput',
            'quantityStepper', 'formElements', 'formLayout', 'formOtherPlugin', 'formValidation',
        ],
    ],
    '04-table.md' => [
        'title' => '表格组件',
        'desc'  => '静态表格、DataTables 数据表格（服务端/客户端）、自定义表格与列表工具条。',
        'intro' => [
            '## 本章导读',
            '',
            '| 组件 | 适用场景 |',
            '|---|---|',
            '| `table` | 静态表格，服务端渲染真实 `<tbody>`（SEO/打印友好） |',
            '| `dataTable` | 数据表格：服务端/客户端、排序、搜索、分页、导出、行操作（**功能最全**） |',
            '| `tablesCustom` | 简单自定义表格（带控件单元格 + tfoot），无排序分页 |',
            '| `dataTableToolbar` | 独立工具条（搜索/筛选/每页条数/视图切换），可搭配任意表格 |',
            '',
            '### 完整能力见',
            '',
            '- [03-数据表格全指南](../04-进阶指南/03-datatable.md) —— 列定义、52 种渲染器、行操作、过滤栏、批量、导出、服务端对接、陷阱清单',
            '- [05-服务端数据协议](../02-核心架构/05-data-protocol.md) —— `XfAdmin::dataResponse()` 的请求/响应与 `filters` 语法',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::dataTable([',
            "    'id'          => 'user-table',",
            "    'ajax'        => '/admin/api/users',",
            "    'server_side' => true,",
            "    'method'      => 'POST',",
            "    'columns'     => [",
            "        ['key' => 'id', 'label' => 'ID'],",
            "        ['key' => 'name', 'label' => '姓名'],",
            "        ['key' => 'status', 'label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],",
            "        ['key' => '', 'label' => '操作', 'actions' => [...]],",
            '    ],',
            "    'filter_bar'  => [['name' => 'keyword', 'label' => '关键词', 'type' => 'text']],",
            ']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `row_detail` 与 `responsive` **互斥**（都占首列）；',
            '- 启用 `bulk`/`row_detail` 后，`order` 的列索引会被辅助列顶偏；',
            '- `data` 为空的列（操作列/明细列）必须 `defaultContent`（组件已自动补 `""`）；',
            '- 导出按钮用 `print` 而非 `printHtml5`；',
            '- 只有 `buttons`/`export` 中的 `pdf` 会自动加载 pdfmake（写在 `options.buttons` 里检测不到）；',
            '- `row_group` 对服务端模式无效；`row_url` 当前实现无效。',
            '',
        ],
        'aliases' => ['table', 'dataTable', 'tablesCustom', 'dataTableToolbar'],
    ],
    '05-chart.md' => [
        'title' => '图表与地图',
        'desc'  => 'ApexCharts 通用图表 / 树图 / 桑基图、ECharts、矢量地图、Leaflet 与 Google 地图。',
        'intro' => [
            '## 本章导读',
            '',
            '图表组件统一通过 `data-xf` 声明式初始化，支持主题联动（明暗切换自动重绘）。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::card([',
            "    'title' => '销售趋势',",
            "    'body'  => XfAdmin::apexChart([",
            "        'id'         => 'sales-chart',",
            "        'type'       => 'line',",
            "        'height'     => 320,",
            "        'series'     => [['name' => '今年', 'data' => [120, 200, 150, 280]]],",
            "        'categories' => ['1月','2月','3月','4月'],",
            '    ]),',
            ']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- 需要 JS 操作时**显式传 `id`**；',
            '- 图表在容器宽度为 0 时（隐藏标签页/折叠区）会延迟初始化，显示时需触发 `shown.bs.tab` 或手动 `XFAdmin.scan()`；',
            '- `apexSankey` 与 `apexcharts` 争抢 `window.SVG`，已用前后护栏包裹，勿调整加载顺序；',
            '- 避免同页同时使用 ApexCharts 与 ECharts（两套库，体积翻倍）；',
            '- 离线环境下地图瓦片请求外网会失败，可设 `tiles => null`。',
            '',
        ],
        'aliases' => ['apexChart', 'apexTree', 'apexSankey', 'echart', 'vectorMap', 'leafletMap', 'googleMap'],
    ],
    '06-data-ecommerce.md' => [
        'title' => '业务组件（一）电商 · 商品 · 订单 · 发票',
        'desc'  => '商品、分类、购物车、结算、订单、退款、库存、销售、发票与电商仪表盘。',
        'intro' => [
            '## 本章导读',
            '',
            '业务组件是**数据驱动的整块 UI**：传入业务数据数组即可渲染完整区块，',
            '适合快速搭建电商/订单/发票等后台页面。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::row([\'cols\' => [',
            "    ['width' => 8, 'content' => XfAdmin::orders(['orders' => \$orders])],",
            "    ['width' => 4, 'content' => XfAdmin::cartSummary(['items' => \$cart, 'total' => \$total])],",
            ']]);',
            '',
            'echo XfAdmin::productsGrid([\'products\' => \$products]);',
            'echo XfAdmin::invoiceDetail([\'invoice\' => \$invoice, \'items\' => \$items]);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- 业务组件多为**整页/整块**，通常作为 `content` 或 `card.body` 使用；',
            '- 数据字段缺失时会安全降级（显示占位或跳过），不会报错；',
            '- 图片字段统一走 `img()` 解析（外链 / data URI / 包内相对路径）；',
            '- 需要对接真实后端时，优先把业务数据格式化为组件期望的数组结构。',
            '',
        ],
        'aliases' => [
            'productCard', 'productsGrid', 'productCategories', 'productAdd', 'productDetails',
            'productViews', 'shoppingCart', 'cartSummary', 'checkout', 'orders', 'orderDetails',
            'orderTrackingTimeline', 'purchasedOrders', 'refunds', 'sales', 'customers',
            'sellers', 'sellerDetails', 'reviewList', 'attributes', 'ecommerceSettings',
            'ecommerceDashboard', 'marketplace', 'warehouse',
            'invoiceCreate', 'invoiceDetail', 'invoiceList', 'invoiceTable', 'invoiceView',
            'filterSidebar', 'featureComparisonTable',
        ],
    ],
    '07-data-content.md' => [
        'title' => '业务组件（二）内容 · 社区 · 文件',
        'desc'  => '文章、博客、评论、论坛、FAQ、画廊、文件管理、搜索结果、条款与隐私政策。',
        'intro' => [
            '## 本章导读',
            '',
            '内容与社区类组件：文章/博客/评论/论坛/FAQ/画廊/文件管理/搜索结果。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::blogList([\'posts\' => \$posts]);',
            'echo XfAdmin::commentThread([\'comments\' => \$comments, \'action\' => \'/comments\']);',
            'echo XfAdmin::gallery([\'items\' => [[\'image\' => \'gallery/1.jpg\', \'title\' => \'...\']]]);',
            'echo XfAdmin::fileManager([\'folders\' => [...], \'files\' => [...]]);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `CommentThread`、`Gallery` 带**自带内联 JS**（评论表单、画廊搜索），勿重复初始化；',
            '- 长文本组件（`terms`、`privacyPolicy`、`article`）的内容槽位为 `raw()`，',
            '  若内容来自用户输入需自行转义；',
            '- `Gallery` 依赖 lightbox 时会自动加载 glightbox 资源。',
            '',
        ],
        'aliases' => [
            'article', 'blogArticle', 'blogList', 'commentThread', 'faq', 'faqAccordion',
            'forumThread', 'gallery', 'terms', 'privacyPolicy', 'sitemap', 'socialFeed',
            'fileManager', 'searchResults', 'searchResultsRich', 'pricingCard', 'testimonial',
        ],
    ],
    '08-data-people.md' => [
        'title' => '业务组件（三）用户 · 组织 · 项目协作',
        'desc'  => '客户/公司/联系人、角色权限、团队成员、项目与任务、看板、商机、用户档案与账户设置。',
        'intro' => [
            '## 本章导读',
            '',
            '用户、组织与项目协作类组件：客户/公司/联系人、角色权限、团队、项目任务、看板、商机、档案。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::kanban([\'columns\' => [...], \'cards\' => [...]]);',
            'echo XfAdmin::permissionMatrix([\'roles\' => \$roles, \'permissions\' => \$perms, \'matrix\' => \$matrix]);',
            'echo XfAdmin::profilePage([\'user\' => \$user, \'tabs\' => [...]]);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `kanban` 拖拽需 SortableJS（自动加载），跨列移动会按 `data-xf-update-url` 持久化；',
            '- `permissionMatrix` 的勾选需自行接后端保存；',
            '- `profilePage` / `accountSettings` 为整页级组件，适合直接作为 `content`。',
            '',
        ],
        'aliases' => [
            'clients', 'companies', 'companyCard', 'contactCard', 'contactList', 'roles',
            'permissionMatrix', 'teamMember', 'projects', 'projectDetails', 'projectActivity',
            'projectTeamBoard', 'taskList', 'todoList', 'issueTracker', 'kanban', 'deals',
            'userProfile', 'profileHeader', 'profilePage', 'accountSettings', 'voteList',
            'activityFeed',
        ],
    ],
    '09-data-comm.md' => [
        'title' => '业务组件（四）沟通 · 邮件',
        'desc'  => '聊天应用与会话面板、邮件应用、邮件撰写、邮件列表与 Outlook 风格客户端。',
        'intro' => [
            '## 本章导读',
            '',
            '沟通与邮件类组件。聊天与邮件应用为整页级布局，会话面板/气泡为可复用片段。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::chatApp([\'contacts\' => \$contacts, \'messages\' => \$messages]);',
            'echo XfAdmin::emailApp([\'folders\' => [...], \'mails\' => [...]]);',
            'echo XfAdmin::chatConversationPanel([\'messages\' => \$messages, \'action\' => \'/chat/send\']);',
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- 聊天/邮件的前端交互由 `chat-scroll`、`chat-form`、`email` 等 widget 提供；',
            '- 发送消息会派发 `xf.chat.send` 事件，可监听后走 AJAX；',
            '- `outlook` 为 Outlook 风格三栏布局，适合整页使用。',
            '',
        ],
        'aliases' => [
            'chatApp', 'chatBox', 'chatConversationPanel', 'chatMessageBubble',
            'emailApp', 'emailCompose', 'mailList', 'outlook',
        ],
    ],
    '10-data-dashboard.md' => [
        'title' => '业务组件（五）仪表盘 · 模块 · 通用',
        'desc'  => '仪表盘网格、小部件、指标卡、分析报表、设置中心、模块导航/网格、API 密钥、应用管理与导入导出。',
        'intro' => [
            '## 本章导读',
            '',
            '仪表盘与通用业务组件：可拖拽仪表盘网格、小部件、指标卡、报表、设置中心、模块导航/网格、',
            'API 密钥、应用管理、导入导出。',
            '',
            '### 组合范式',
            '',
            '```php',
            'echo XfAdmin::dashboardGrid([\'widgets\' => [',
            "    ['id' => 'w1', 'title' => '销售', 'content' => \$chart, 'width' => 8],",
            "    ['id' => 'w2', 'title' => '订单', 'content' => \$table, 'width' => 4],",
            ']]);',
            '',
            'echo XfAdmin::settingsCenter([\'groups\' => [...]]);',
            'echo XfAdmin::importExport([\'export_url\' => \'/api/export\', \'import_url\' => \'/api/import\',',
            "                          'formats' => ['csv', 'xlsx']]);",
            '```',
            '',
            '### 约定与陷阱',
            '',
            '- `importExport` 的 `formats` 默认 `[]`（因数组并集合并特性），使用 `export_url` 简写时**必须显式传**；',
            '- `dashboardGrid` 拖拽依赖 SortableJS / Muuri（按配置自动加载）；',
            '- `settingsCenter` 的分组表单需自行接保存端点。',
            '',
        ],
        'aliases' => [
            'widget', 'metricCard', 'statMiniSparkline', 'widgetsDashboard', 'analyticsDashboard',
            'dashboardGrid', 'settingsCenter', 'reportPage', 'moduleNav', 'moduleGrid',
            'apiKeys', 'appManage', 'importExport',
        ],
    ],
    '11-misc.md' => [
        'title' => '杂项与交互增强',
        'desc'  => '日历、树形、拖拽排序、灯箱、引导漫游、剪贴板、SweetAlert、PDF 预览、文本对比、瀑布流、动画等。',
        'intro' => [
            '## 本章导读',
            '',
            '杂项组件多为**交互增强**，依赖第三方插件并由 `data-xf` widget 自动初始化。',
            '',
            '### widget 与依赖速查',
            '',
            '| 组件 | data-xf | 依赖插件 |',
            '|---|---|---',
            '| `calendar` | `calendar` | `fullcalendar` |',
            '| `treeView` | `jstree` | `jstree` + `jquery` |',
            '| `nestable` | `nestable` | `sortablejs` |',
            '| `lightbox` | `lightbox` | `glightbox` |',
            '| `masonry` / `pinBoard` | `masonry` | `masonry` |',
            '| `tour` | `tour` | `tourguide` |',
            '| `clipboard` | `clipboard` | `clipboard` |',
            '| `sweetAlert` | `sweetalert` | `sweetalert2` |',
            '| `pdfViewer` | — | `pdfjs` |',
            '| `textDiff` | — | `diff` |',
            '| `tinycon` | — | `tinycon` |',
            '| `animate` | `animate` | `animate`（CSS） |',
            '',
            '### 约定与陷阱',
            '',
            '- 依赖 jQuery 的组件（`treeView`、`nestable`）在无 jQuery 时静默降级；',
            '- `raw` 组件用于原样输出任意 HTML（不做任何转义，**仅限可信内容**）；',
            '- `idleTimer` 用于超时自动登出，需自行实现回调逻辑。',
            '',
        ],
        'aliases' => [
            'calendar', 'treeView', 'nestable', 'lightbox', 'tour', 'clipboard', 'clipboardButton',
            'sweetAlert', 'raw', 'tinycon', 'idleTimer', 'animate', 'pdfViewer', 'textDiff',
            'pinBoard', 'masonry', 'videoPlayer', 'i18n',
        ],
    ],
];

// ------------------------------------------------------------------
// 2. 收集源码元信息
// ------------------------------------------------------------------

/** 从 XfAdmin.php 的 @method 注释解析「别名 => 中文说明」 */
function parseMethodDocs(string $file): array
{
    $docs = [];
    $src = (string) file_get_contents($file);
    if (preg_match_all('/@method\s+static\s+[\\\\\w]+\s+(\w+)\s*\([^)]*\)\s*(?:\/\/|::)(.+)/', $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $docs[$x[1]] = trim($x[2]);
        }
    }
    return $docs;
}

/** 类 docblock 摘要（去掉 @ 标签行与示例行） */
function classSummary(string $class): string
{
    try {
        $rc = new ReflectionClass($class);
    } catch (Throwable) {
        return '';
    }
    $doc = (string) $rc->getDocComment();
    if ($doc === '') {
        return '';
    }
    $lines = [];
    foreach (explode("\n", $doc) as $line) {
        $l = trim(preg_replace('#^[/*]+\s?#', '', trim($line)) ?? '');
        if ($l === '' || str_starts_with($l, '@') || str_starts_with($l, 'XfAdmin::')) {
            continue;
        }
        $lines[] = $l;
    }
    return implode(' ', $lines);
}

/** 从类 docblock 抽取 XfAdmin::xxx([...]) 示例代码块（括号配对） */
function classExamples(string $class): array
{
    try {
        $rc = new ReflectionClass($class);
    } catch (Throwable) {
        return [];
    }
    $doc = (string) $rc->getDocComment();
    if ($doc === '') {
        return [];
    }
    // 去注释符
    $clean = [];
    foreach (explode("\n", $doc) as $line) {
        $clean[] = preg_replace('#^\s*\*\s?#', '', $line) ?? '';
    }
    $text = implode("\n", $clean);

    $out = [];
    $offset = 0;
    while (($pos = strpos($text, 'XfAdmin::', $offset)) !== false) {
        $start = $pos;
        $p = $pos + strlen('XfAdmin::');
        $depth = 0;
        $end = $p;
        $len = strlen($text);
        for ($i = $p; $i < $len; $i++) {
            $ch = $text[$i];
            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
                if ($depth === 0) {
                    $end = $i + 1;
                    break;
                }
            } elseif ($ch === "\n" && $depth === 0) {
                // 单行无括号调用（如 XfAdmin::page()），遇换行结束
                $end = $i;
                break;
            }
        }
        $code = trim(substr($text, $start, $end - $start));
        if ($code !== '' && ! str_contains($code, "\n\n")) {
            $out[] = rtrim($code, ';') . ';';
        }
        $offset = max($end, $pos + 1);
        if (count($out) >= 6) {
            break;
        }
    }
    // 去重
    return array_values(array_unique($out));
}

/** 解析 defaults() 源码，返回 [键 => ['value'=>..,'comment'=>..]] */
function parseDefaults(string $file): array
{
    $src = (string) (is_file($file) ? file_get_contents($file) : '');
    if ($src === '') {
        return [];
    }
    if (! preg_match('/function\s+defaults\s*\(\s*\)\s*(?::\s*array\s*)?\{/', $src, $m, PREG_OFFSET_CAPTURE)) {
        return [];
    }
    $start = $m[0][1] + strlen($m[0][0]);
    $depth = 1;
    $i = $start;
    $len = strlen($src);
    while ($i < $len && $depth > 0) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
        }
        $i++;
    }
    $body = substr($src, $start, $i - $start - 1);

    $rows = [];
    foreach (explode("\n", $body) as $line) {
        if (preg_match("/^\s*'([^']+)'\s*=>\s*(.+?)\s*,?\s*(?:\/\/\s*(.*))?$/", $line, $mm)) {
            $rows[$mm[1]] = [
                'value'   => rtrim(trim($mm[2]), ','),
                'comment' => isset($mm[3]) ? trim($mm[3]) : '',
            ];
        }
    }
    return $rows;
}

function guessType(mixed $v): string
{
    if (is_bool($v)) {
        return 'bool';
    }
    if (is_int($v)) {
        return 'int';
    }
    if (is_float($v)) {
        return 'float';
    }
    if (is_string($v)) {
        return 'string';
    }
    if (is_array($v)) {
        return $v === [] ? 'array' : (array_is_list($v) ? 'array' : 'array');
    }
    return 'mixed';
}

function dumpDefault(mixed $v): string
{
    if ($v === null) {
        return 'null';
    }
    if (is_bool($v)) {
        return $v ? 'true' : 'false';
    }
    if (is_string($v)) {
        return $v === '' ? "''" : "'" . $v . "'";
    }
    if (is_int($v) || is_float($v)) {
        return (string) $v;
    }
    if (is_array($v)) {
        if ($v === []) {
            return '[]';
        }
        if (array_is_list($v)) {
            $parts = [];
            foreach (array_slice($v, 0, 6) as $vv) {
                $parts[] = is_array($vv) ? '{…}' : (is_string($vv) ? "'" . $vv . "'" : json_encode($vv, JSON_UNESCAPED_UNICODE));
            }
            return '[' . implode(', ', $parts) . (count($v) > 6 ? ', …' : '') . ']';
        }
        $parts = [];
        $n = 0;
        foreach ($v as $k => $vv) {
            if ($n++ >= 6) {
                $parts[] = '…';
                break;
            }
            $parts[] = "'" . $k . "'=>" . (is_scalar($vv) ? (is_string($vv) ? "'" . $vv . "'" : var_export($vv, true)) : '{…}');
        }
        return '[' . implode(', ', $parts) . ']';
    }
    return 'mixed';
}

/**
 * 高频参数词典：键名 => 中文说明
 * 用于为 defaults() 中没有行尾注释的参数补齐说明（按 XfAdmin 全组件的语义归纳）
 */
const PARAM_DICT = [
    // 通用
    'id' => '根元素 id（留空自动生成唯一 id）',
    'class' => '附加到根元素的自定义 class',
    'style' => '附加到根元素的内联样式',
    'title' => '标题文本（部分组件为弹窗/tooltip 标题）',
    'subtitle' => '副标题文本',
    'text' => '正文/按钮文案（纯文本语义，输出时转义）',
    'label' => '标签文案（表单字段标签 / 按钮文案）',
    'body' => '主体内容（可为 HTML 字符串、组件实例或数组，原样输出）',
    'content' => '内容区（可为 HTML 字符串、组件实例或数组）',
    'footer' => '底部内容（原样输出）',
    'header' => '头部内容（原样输出）',
    'name' => '表单字段名 / 语义名称',
    'value' => '当前值（表单控件值 / 展示数值）',
    'url' => '链接地址（自动做安全协议校验）',
    'href' => '链接地址（同 url）',
    'target' => '链接打开方式，如 _blank',
    'icon' => 'Tabler 图标 class，如 `ti ti-user`',
    'image' => '图片地址（支持外链 / data URI / 包内 images 相对路径）',
    'src' => '资源地址（图片 / iframe / 文件）',
    'variant' => '语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束）',
    'size' => '尺寸：sm | lg（部分组件支持 md/xl）',
    'type' => '类型（各组件语义不同，详见该组件说明）',
    'items' => '条目数组（结构见各组件说明）',
    'data' => '数据数组（行数据 / 图表数据）',
    'columns' => '列定义数组',
    'rows' => '行数据数组',
    'options' => '透传给底层插件的原生配置（递归合并，优先级最高）',
    'placeholder' => '占位提示文案',
    'required' => '是否必填（渲染 required 属性 + 红色星号）',
    'disabled' => '是否禁用',
    'readonly' => '是否只读',
    'help' => '帮助说明文本（转义输出，渲染为 .form-text）',
    'active' => '是否激活 / 默认选中项',
    'align' => '对齐方式：start | center | end',
    'placement' => '弹出方位：top | bottom | left | right | start | end',
    'trigger' => '触发方式（hover / click / focus），或触发按钮文案',
    'count' => '数量 / 计数徽标数字',
    'badge' => '徽标文本或 `[\'text\'=>..,\'class\'=>..]`',
    'color' => '颜色值（#hex / rgb() / 具名色）',
    'width' => '宽度（数字=栅格列数或 CSS 长度）',
    'height' => '高度（CSS 长度，受安全白名单约束）',
    'padding' => '是否保留内边距',
    'tools' => '卡片工具按钮：`[\'collapse\',\'refresh\',\'close\']` 或自定义 HTML',
    'actions' => '操作区内容（按钮组 / 行操作定义）',
    'collapse' => '是否可折叠',
    'dismiss' => '是否可关闭（警告条 / 模态框）',
    'fade' => '是否启用淡入动画',
    'scrollable' => '内容超长时是否内部滚动',
    'centered' => '是否垂直居中',
    'static' => '点击遮罩不关闭（静态背景）',
    'close' => '是否显示关闭按钮',
    'wrap' => '是否换行',
    'striped' => '是否斑马纹',
    'hover' => '是否悬停高亮',
    'bordered' => '是否显示边框',
    'borderless' => '是否无边框',
    'sm' => '是否紧凑尺寸（table-sm / 小型）',
    'responsive' => '是否响应式（横向滚动 / 响应式表格）',
    'caption' => '表格 caption 文本',
    'empty' => '空数据提示文案',
    'limit' => '最多展示条数',
    'layout' => '布局模式（各组件不同，如 vertical/horizontal）',
    'theme' => '主题（light / dark / 图表主题名）',
    'mode' => '模式（各组件不同）',
    'driver' => '底层驱动（如编辑器 quill/summernote、上传 native/dropzone/filepond）',
    'ajax' => 'AJAX 地址或 DataTables 原生 ajax 配置',
    'method' => 'HTTP 方法（GET/POST/PUT/DELETE）',
    'action' => '表单提交地址 / 动作类型',
    'fields' => '字段定义数组（表单字段 / 详情字段）',
    'buttons' => '按钮定义数组',
    'steps' => '步骤数组（向导 / 步骤条）',
    'tabs' => '选项卡数组',
    'sections' => '分区数组',
    'filters' => '过滤条件定义',
    'search' => '是否启用搜索',
    'paging' => '是否分页',
    'ordering' => '是否允许排序',
    'order' => '排序规则，如 `[[0, \'asc\']]`',
    'page_length' => '每页条数',
    'toolbar' => '是否显示工具条',
    'show_header' => '是否显示头部',
    'show_footer' => '是否显示底部',
    'show_title' => '是否显示标题',
    'user' => '用户信息（name/avatar/email/role 等）',
    'brand' => '品牌信息（name/logo/url）',
    'menu' => '菜单数据数组',
    'extra' => '附加内容（原样输出）',
    'html' => '自定义 HTML（原样输出）',
    'raw' => '是否原样输出（不转义）',
    'format' => '格式化闭包或格式字符串',
    'template' => '模板字符串，支持 `{field}` 占位',
    'link' => '链接配置',
    'links' => '链接数组（导航 / 底部链接）',
    'meta' => '附加信息（时间 / 作者等）',
    'time' => '时间文本',
    'date' => '日期文本',
    'author' => '作者信息',
    'avatar' => '头像地址（自动解析为包内图片 URL）',
    'status' => '状态值 / 状态映射',
    'progress' => '进度百分比（0-100）',
    'percent' => '百分比数值',
    'tags' => '标签数组 / 是否启用标签输入',
    'price' => '价格数值',
    'amount' => '金额数值',
    'total' => '合计数值',
    'currency' => '货币符号',
    'description' => '描述文本',
    'summary' => '摘要文本',
    'category' => '分类',
    'sort' => '排序号',
    'level' => '层级 / 级别',
    'min' => '最小值',
    'max' => '最大值',
    'step' => '步长',
    'cols' => '列数（栅格 / 分区列数）',
    'gap' => '间距',
    'gutter' => '栅格间距（数字或 `[\'x\'=>2,\'y\'=>3]`）',
    'justify' => '主轴对齐（start/center/end/between/around）',
    'offset' => '栅格偏移',
    'multiple' => '是否多选',
    'enhance' => '增强插件：choices | select2',
    'inline' => '是否行内排列',
    'vertical' => '是否纵向排列',
    'autoplay' => '是否自动播放',
    'loop' => '是否循环',
    'interval' => '间隔时间（毫秒）',
    'duration' => '动画时长（毫秒）',
    'delay' => '延迟（毫秒）',
    'position' => '位置',
    'direction' => '方向',
    'animation' => '动画类名',
    'confirm' => '确认文案（非空则操作前确认）',
    'reload' => '操作成功后是否刷新',
    'dataset' => '数据集标识（批量操作 / 领域动作提交给后端）',
    'create' => '「新增」按钮配置',
    'bulk' => '批量操作配置',
    'export' => '导出按钮：true 或 `[\'copy\',\'excel\',\'csv\',\'pdf\',\'print\']`',
    'filter_bar' => '过滤工具条控件定义数组',
    'filter_auto' => '过滤条件变更即自动查询',
    'density' => '表格密度：compact 紧凑',
    'fixed_header' => '表头固定',
    'fixed_columns' => '固定列：`true` 或 `[\'left\'=>1,\'right\'=>1]`',
    'scroll_x' => '横向滚动',
    'scroll_y' => '纵向滚动高度',
    'server_side' => '服务端分页模式',
    'row_group' => '行分组字段名或 `[\'data\'=>..,\'empty\'=>..]`',
    'row_detail' => '行明细展开（true 或 `[\'columns\'=>[..]]`）',
    'state_save' => '保存表格状态（分页/排序/搜索）',
    'select' => '行选择：`true` 或 `[\'style\'=>\'multi\']`',
    'buttons_' => '按钮组',
    'language' => '语言包覆盖（DataTables）',
    'list' => '列表数据',
    'group' => '分组信息',
    'children' => '子项数组（树形 / 菜单）',
    'parent' => '父级信息',
    'key' => '字段名 / 键名（列定义中为数据字段）',
    'visible' => '是否可见',
    'searchable' => '是否参与搜索',
    'orderable' => '是否可排序',
    'sortable' => '是否可排序（orderable 别名）',
    'render' => '单元格渲染器（字符串类型名或配置数组）',
    'format_' => '格式化',
    'icon_bg' => '图标背景色',
    'trend' => '趋势值（正/负）',
    'sub' => '副标题 / 附属信息',
    'note' => '备注文本',
    'remark' => '备注',
    'phone' => '电话',
    'email' => '邮箱',
    'address' => '地址',
    'company' => '公司名',
    'role' => '角色',
    'department' => '部门',
    'qty' => '数量',
    'sku' => '商品编码',
    'stock' => '库存',
    'quantity' => '数量',
];

/** markdown 表格单元格转义 */
function cell(string $s): string
{
    return str_replace(['|', "\n"], ['\\|', ' '], $s);
}

// ------------------------------------------------------------------
// 3. 生成
// ------------------------------------------------------------------
$methodDocs = parseMethodDocs($root . '/src/XfAdmin.php');
$list       = XfAdmin::componentList();

$outDir = $root . '/docs/03-组件参考';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$assigned = [];
$stats    = [];
$index    = [];   // 用于生成 00-总览.md

foreach ($GROUPS as $file => $group) {
    $md             = [];
    $printedClasses = [];
    $md[] = '# ' . $group['title'];
    $md[] = '';
    $md[] = '> ' . $group['desc'];
    $md[] = '';
    $md[] = '<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；';
    $md[] = '     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->';
    $md[] = '';
    $md[] = '## 目录';
    $md[] = '';
    $toc = [];

    foreach ($group['aliases'] as $alias) {
        $class = $list[$alias] ?? null;
        if ($class === null) {
            continue;
        }
        $assigned[$alias] = true;

        try {
            $rc = new ReflectionClass($class);
        } catch (Throwable) {
            continue;
        }
        // 同类别名（如 topNav / topnav）只输出一次完整条目，其余折叠为别名提示
        if (isset($printedClasses[$class])) {
            $toc[] = '- `' . $alias . '` — 等价于 `' . $printedClasses[$class] . '`（同一组件类的别名）';
            $md[]  = '### `' . $alias . '`';
            $md[]  = '';
            $md[]  = '`' . $alias . '` 是 `' . $printedClasses[$class] . '` 的别名，指向同一个组件类 `' . ltrim($class, '\\') . '`，参数与用法完全一致。';
            $md[]  = '';
            $stats[$file] = ($stats[$file] ?? 0) + 1;
            $assigned[$alias] = true;
            continue;
        }
        $printedClasses[$class] = $alias;

        $fileRel = str_replace($root . '/', '', (string) $rc->getFileName());
        $summary = $methodDocs[$alias] ?? classSummary($class);
        if ($summary === '') {
            $summary = '（未提供说明）';
        }
        // 同类别名提示（同一类被多个别名引用）
        $sameClassAliases = array_keys($list, $class, true);
        $aliasNote        = '';
        if (count($sameClassAliases) > 1) {
            $aliasNote = ' 等 ' . implode(' / ', array_map(fn ($a) => '`' . $a . '`', $sameClassAliases));
        }

        // assets
        $assets = [];
        try {
            $am     = $rc->getMethod('assets');
            $assets = array_values(array_filter((array) $am->invoke($rc->newInstanceWithoutConstructor())));
        } catch (Throwable) {
        }
        // 带条件的 assets（源码里出现 'xxx' 字符串）
        $srcText   = (string) (is_file((string) $rc->getFileName()) ? file_get_contents((string) $rc->getFileName()) : '');
        $condAsset = [];
        if (preg_match('/function\s+assets\s*\(\s*\)\s*(?::\s*array\s*)?\{(.*?)\n    \}/s', $srcText, $am2)) {
            if (preg_match_all("/'([a-z0-9\-]+)'/i", $am2[1], $mm)) {
                foreach ($mm[1] as $a) {
                    if (isset(\zxf\XfAdmin\Assets\Assets::PLUGINS[$a])) {
                        $condAsset[] = $a;
                    }
                }
            }
        }
        $allAssets = array_values(array_unique(array_merge($assets, $condAsset)));

        // defaults
        $defaultsRows = parseDefaults((string) $rc->getFileName());
        $options      = [];
        try {
            $options = XfAdmin::component($alias, [])->options();
        } catch (Throwable) {
        }

        $toc[] = '- [`' . $alias . '`](#' . strtolower(str_replace(['_'], '-', $alias)) . ') — ' . cell(explode('。', $summary)[0]);

        $md[] = '---';
        $md[] = '';
        $md[] = '### `' . $alias . '`';
        $md[] = '';
        $md[] = $summary . '。';
        $md[] = '';
        $md[] = '> **类**：`' . ltrim($class, '\\') . '`' . $aliasNote;
        $md[] = '> **文件**：`' . $fileRel . '`（' . ($rc->getEndLine() - $rc->getStartLine() + 1) . ' 行）';
        $md[] = '> **依赖插件**：' . ($allAssets === [] ? '无' : implode('、', array_map(fn ($a) => '`' . $a . '`', $allAssets)));
        $md[] = '';

        // 示例
        $examples = classExamples($class);
        if ($examples !== []) {
            $md[] = '**用法示例**';
            $md[] = '';
            $md[] = '```php';
            foreach ($examples as $ex) {
                $md[] = $ex;
            }
            $md[] = '```';
            $md[] = '';
        }

        // 参数表
        if ($options !== []) {
            $md[] = '**配置参数**';
            $md[] = '';
            $md[] = '| 参数 | 类型 | 默认值 | 说明 |';
            $md[] = '|---|---|---|---|';
            foreach ($options as $k => $v) {
                $comment = $defaultsRows[$k]['comment'] ?? '';
                $rawVal  = $defaultsRows[$k]['value'] ?? null;
                $defTxt  = dumpDefault($v);
                if ($comment === '' && $rawVal !== null && strlen($rawVal) > 40) {
                    $comment = '';
                }
                if ($comment === '') {
                    $comment = PARAM_DICT[$k] ?? '';
                }
                if ($comment === '' && is_array($v) && $v !== []) {
                    $comment = '数组结构（见组件用法示例）';
                }
                $md[] = '| `' . $k . '` | ' . guessType($v) . ' | `' . cell($defTxt) . '` | ' . cell($comment) . ' |';
            }
            $md[] = '';
            // 参数类型提示
            $md[] = '> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。';
            $md[] = '';
        }

        $stats[$file] = ($stats[$file] ?? 0) + 1;
        $index[$file][] = [
            'alias'   => $alias,
            'summary' => $summary,
            'class'   => ltrim($class, '\\'),
            'assets'  => $allAssets,
            'file'    => $fileRel,
            'group'   => $group['title'],
            'doc'     => $file,
        ];
    }

    // 组装：标题/描述 → 目录(TOC) → 手写导读(intro) → 组件条目
    $pos   = array_search('## 目录', $md, true);
    $pos   = $pos === false ? 6 : $pos;
    $final = array_merge(
        array_slice($md, 0, $pos + 1),
        [''],
        $toc,
        [''],
        $group['intro'] ?? [],
        array_slice($md, $pos + 1)
    );
    file_put_contents($outDir . '/' . $file, implode("\n", $final) . "\n");
    echo "生成: docs/03-组件参考/{$file} (" . ($stats[$file] ?? 0) . " 个组件)\n";
}

// ------------------------------------------------------------------
// 4. 生成 00-总览.md（全组件索引）
// ------------------------------------------------------------------
$ov   = [];
$ov[] = '# 组件总览';
$ov[] = '';
$ov[] = '> 本文件由 `php tools/gen_component_docs.php` 自动生成。';
$ov[] = '> 组件别名共 **' . count($list) . '** 个（对应 **' . count(array_unique($list)) . '** 个组件类，部分类别名重复指向同一类）。';
$ov[] = '';
$ov[] = '## 分类统计';
$ov[] = '';
$ov[] = '| 分类文档 | 组件数 |';
$ov[] = '|---|---|';
foreach ($GROUPS as $file => $group) {
    $ov[] = '| [' . $group['title'] . '](' . $file . ') | ' . ($stats[$file] ?? 0) . ' |';
}
$ov[] = '';
$ov[] = '## 命名约定';
$ov[] = '';
$ov[] = '- **别名大小写不敏感**：`XfAdmin::datatable()` 与 `XfAdmin::dataTable()` 等价；';
$ov[] = '- **同类别名**：`topNav`/`topnav`、`dateRange`/`dateRangePicker`、`clipboard`/`clipboardButton` 指向同一类；';
$ov[] = '- **PHP 保留字规避**：`switch` → 类名 `Toggle`（别名 `switch`）；`empty` → 类名 `EmptyState`（别名 `empty`）；';
$ov[] = '- ⚠️ `lockScreen` 注册表中被 `LockScreen::class` 覆盖，得到的是独立整页组件而非 `AuthPage`；';
$ov[] = '  如需 AuthPage 的锁屏语义请用 `XfAdmin::authPage([\'type\' => \'lock-screen\'])`。';
$ov[] = '';

foreach ($GROUPS as $file => $group) {
    $ov[] = '---';
    $ov[] = '';
    $ov[] = '## [' . $group['title'] . '](' . $file . ')';
    $ov[] = '';
    $ov[] = $group['desc'];
    $ov[] = '';
    $ov[] = '| 别名 | 说明 | 组件类 | 依赖插件 |';
    $ov[] = '|---|---|---|---|';
    foreach ($index[$file] ?? [] as $row) {
        $short = explode('。', $row['summary'])[0];
        $short = explode('（', $short)[0];
        $ov[]  = '| [`' . $row['alias'] . '`](' . $file . '#' . strtolower($row['alias']) . ') | '
            . cell($short) . ' | `' . str_replace('zxf\\XfAdmin\\Components\\', '', $row['class']) . '` | '
            . ($row['assets'] === [] ? '—' : implode(' ', array_map(fn ($a) => '`' . $a . '`', $row['assets']))) . ' |';
    }
    $ov[] = '';
}
file_put_contents($outDir . '/00-总览.md', implode("\n", $ov) . "\n");
echo "生成: docs/03-组件参考/00-总览.md（全组件索引）\n";

// ------------------------------------------------------------------
// 5. 生成 06-附录/01-component-index.md（按字母序速查）
// ------------------------------------------------------------------
$appendixDir = $root . '/docs/06-附录';
if (! is_dir($appendixDir)) {
    mkdir($appendixDir, 0755, true);
}

$flat = [];
foreach ($index as $rows) {
    foreach ($rows as $r) {
        $flat[] = $r;
    }
}
usort($flat, fn ($a, $b) => strcasecmp($a['alias'], $b['alias']));

$ci   = [];
$ci[] = '# 组件索引（按别名排序）';
$ci[] = '';
$ci[] = '> 本文件由 `php tools/gen_component_docs.php` 自动生成。';
$ci[] = '> 共 **' . count($flat) . '** 个组件条目（注册表别名 **' . count($list) . '** 个，'
    . (count($list) - count($flat)) . ' 个为指向同一组件类的重复别名）。';
$ci[] = '> 按分类浏览见 [组件总览](../03-组件参考/00-总览.md)。';
$ci[] = '';
$ci[] = '| 别名 | 一句话说明 | 组件类 | 源文件 | 分类文档 |';
$ci[] = '|---|---|---|---|---|';
foreach ($flat as $r) {
    $short = trim(explode('。', explode('（', $r['summary'])[0])[0]);
    $ci[]  = '| `' . $r['alias'] . '` | ' . cell($short) . ' | `'
        . str_replace('zxf\\XfAdmin\\Components\\', '', $r['class']) . '` | `'
        . $r['file'] . '` | [' . $r['group'] . '](../03-组件参考/' . $r['doc'] . ') |';
}
file_put_contents($appendixDir . '/01-component-index.md', implode("\n", $ci) . "\n");
echo '生成: docs/06-附录/01-component-index.md（' . count($flat) . " 条）\n";

// ------------------------------------------------------------------
// 6. 生成 06-附录/02-option-index.md（高频参数字典）
// ------------------------------------------------------------------
$oi   = [];
$oi[] = '# 参数字典';
$oi[] = '';
$oi[] = '> 本文件由 `php tools/gen_component_docs.php` 自动生成（源自生成器内的 `PARAM_DICT`）。';
$oi[] = '> 列出跨组件高频出现的配置键及其统一语义。具体组件的完整参数以各组件文档为准。';
$oi[] = '';
$oi[] = '| 参数 | 说明 |';
$oi[] = '|---|---|';
$dict = PARAM_DICT;
ksort($dict, SORT_NATURAL | SORT_FLAG_CASE);
foreach ($dict as $k => $v) {
    $oi[] = '| `' . rtrim($k, '_') . '` | ' . cell($v) . ' |';
}
file_put_contents($appendixDir . '/02-option-index.md', implode("\n", $oi) . "\n");
echo "生成: docs/06-附录/02-option-index.md（参数字典）\n";

// ------------------------------------------------------------------
// 7. 生成 06-附录/03-plugin-index.md（插件资源索引）
// ------------------------------------------------------------------
$pi   = [];
$pi[] = '# 插件资源索引';
$pi[] = '';
$pi[] = '> 本文件由 `php tools/gen_component_docs.php` 自动生成（源自 `Assets::PLUGINS`）。';
$pi[] = '> 组件在 `assets()` 中返回插件名，由 `Assets::plugin()` 解析依赖并去重加载。';
$pi[] = '> 路径均相对于 `resources/assets/`。';
$pi[] = '';
$pi[] = '| 插件名 | CSS | JS | 依赖 | 被以下组件引用 |';
$pi[] = '|---|---|---|---|---|';
$usage = [];
foreach ($flat as $r) {
    foreach ($r['assets'] as $a) {
        $usage[$a][] = '`' . $r['alias'] . '`';
    }
}
foreach (\zxf\XfAdmin\Assets\Assets::PLUGINS as $name => $def) {
    $pi[] = '| `' . $name . '` | ' . (empty($def['css']) ? '—' : count($def['css']) . ' 个')
        . ' | ' . (empty($def['js']) ? '—' : count($def['js']) . ' 个')
        . ' | ' . (empty($def['deps']) ? '—' : implode(' ', array_map(fn ($d) => '`' . $d . '`', $def['deps'])))
        . ' | ' . (empty($usage[$name]) ? '—' : implode(' ', $usage[$name])) . ' |';
}
$pi[] = '';
$pi[] = '## 明细';
$pi[] = '';
foreach (\zxf\XfAdmin\Assets\Assets::PLUGINS as $name => $def) {
    $pi[] = '### `' . $name . '`';
    $pi[] = '';
    if (! empty($def['deps'])) {
        $pi[] = '- **依赖**：' . implode('、', array_map(fn ($d) => '`' . $d . '`', $def['deps']));
    }
    if (! empty($def['css'])) {
        $pi[] = '- **CSS**：';
        foreach ($def['css'] as $f) {
            $pi[] = '  - `' . $f . '`';
        }
    }
    if (! empty($def['js'])) {
        $pi[] = '- **JS**：';
        foreach ($def['js'] as $f) {
            $pi[] = '  - `' . $f . '`';
        }
    }
    $pi[] = '';
}
file_put_contents($appendixDir . '/03-plugin-index.md', implode("\n", $pi) . "\n");
echo '生成: docs/06-附录/03-plugin-index.md（' . count(\zxf\XfAdmin\Assets\Assets::PLUGINS) . " 个插件）\n";

// 未分配检查
$missing = array_diff(array_keys($list), array_keys($assigned));
if ($missing !== []) {
    echo "\n⚠️ 未归入任何分组的组件（请补进 \$GROUPS）：\n - " . implode("\n - ", $missing) . "\n";
}
echo "\n总组件数: " . count($list) . '，已分配: ' . count($assigned) . "\n";

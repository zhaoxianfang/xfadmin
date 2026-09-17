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
            'diyLayoutPage', 'diy',
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
 * 别名条目的专属示例（语义别名 / 同类别名）
 * 这些别名在文档中折叠为简短条目，此处为它们补齐可直接运行的最小示例
 */
/**
 * 手工补充的「数组元素结构」说明
 * 用于自动解析（foreach / 方法委托）无法覆盖的参数，内容均取自源码实际用法。
 * 格式：别名 => [参数名 => 说明文本]
 */
$STRUCT_NOTES = [
    'carousel' => [
        'items' => '`image`（图片地址）、`alt`、`caption`（标题）、`content`（内容）、`interval`（停留毫秒）',
    ],
    'avatarGroup' => [
        'items' => '字符串（图片 URL）或数组：`src`（图片）、`name`（名称，用于 title 与首字母回退）',
    ],
    'permissionMatrix' => [
        'roles'  => '`id`、`name`（角色名），也可直接传 `[\'admin\' => \'管理员\']` 键值形式',
        'groups' => '`name`（权限组名）、`permissions`（该组权限数组，元素为 `id`+`name` 或键=>文案）',
        'values' => '已勾选矩阵：`[角色id => [权限id => true]]`，用于回填',
    ],
    'metricCard' => [
        'data'   => '图表数值数组（配合 `chart` 类型：donut/pie/bar/line/area）',
        'labels' => '与 `data` 一一对应的标签数组',
        'trend'  => '趋势值（正数为上升、负数为下降）',
    ],
    'moduleGrid' => [
        'modules' => '`name`（模块名）、`icon`、`url`、`desc`（描述）',
    ],
    'treeView' => [
        'data'    => '`id`、`text`（节点文案）、`children`（子节点数组，递归）、`icon`、`disabled`',
        'options' => '透传 jsTree 原生配置',
    ],
    'dropzoneUpload' => [
        'value' => '已上传文件的地址列表（用于回显）',
    ],
    'countdown' => [
        'labels' => '四个单位文案，顺序为 `[\'天\',\'时\',\'分\',\'秒\']`',
    ],
    'tags' => [
        'whitelist' => '候选词字符串数组（Tagify 建议列表）',
    ],
    'check' => [
        'options' => '`值 => 文案` 键值数组；为空时进入「单控件模式」',
    ],
    'slider' => [
        'options' => '透传 noUiSlider 原生配置（递归合并，优先级最高）',
    ],
    'editor' => [
        'options' => '透传编辑器原生配置（quill 可传 `modules`，summernote 直接传选项）',
    ],
    'upload' => [
        'options' => '透传 dropzone / filepond 原生配置',
    ],
    'colorPicker' => [
        'options' => '透传 Pickr 原生配置',
    ],
    'dateRange' => [
        'options' => '透传 daterangepicker 原生配置（递归合并，优先级最高）',
    ],
    'dateRangePicker' => [
        'options' => '同 `dateRange`：透传 daterangepicker 原生配置',
    ],
    'datePicker' => [
        'options' => '透传 daterangepicker 原生配置（singleDatePicker 恒为 true）',
    ],
    'select' => [
        'options'          => '`值 => 文案` 或 `[[\'value\'=>..,\'label\'=>..,\'disabled\'=>..]]`',
        'groups'           => '`组名 => [值 => 文案]`；非空时忽略 `options`',
        'enhance_options'  => '透传 choices / select2 原生配置',
    ],
    'apexTree' => [
        'data'    => '`id`、`name`、`role`、`avatar`、`color`、`children`（子节点数组）',
        'options' => '透传 ApexTree 原生配置',
    ],
    'echart' => [
        'options' => 'ECharts 原生 option（`series`、`xAxis`、`yAxis`、`tooltip`…）',
    ],
    'vectorMap' => [
        'options' => 'jsVectorMap 原生配置（`map`、`markers`、`series`、`backgroundColor`…）',
    ],
    'leafletMap' => [
        'markers' => '`lat`、`lng`、`popup`（气泡内容）、`title`',
        'options' => '透传 Leaflet 原生配置',
    ],
    'googleMap' => [
        'markers' => '`lat`、`lng`、`title`、`info`',
        'options' => '透传 Google Maps 原生配置',
    ],
    'dataTable' => [
        'columns'    => '`key`/`data`（字段名）、`label`/`title`（表头）、`width`、`minWidth`、`class`、`sortable`、`searchable`、`visible`、`render`、`badges`、`template`、`actions`',
        'data'       => '本地行数据（每行字段与列 `key` 对应）',
        'filter_bar' => '`name`、`label`、`type`、`options`、`width`、`placeholder`、`min`/`max`/`step`、`html`',
        'buttons'    => '`copy`/`csv`/`excel`/`pdf`/`print`/`colvis`/`refresh`/`fullscreen`/`density`，或原生按钮配置数组',
        'order'      => '`[[列索引, \'asc\'|\'desc\']]`（启用 bulk/row_detail 后索引会被辅助列顶偏）',
        'options'    => '透传 DataTables 原生配置（递归合并，优先级最高）',
    ],
    'table' => [
        'columns' => '`键名 => 标题` 或 `[[\'key\'=>..,\'label\'=>..,\'class\'=>..,\'format\'=>闭包,\'raw\'=>bool]]`',
        'data'    => '行数据数组（数组或对象均可）',
    ],
    'tablesCustom' => [
        'columns' => '字符串（直接作表头）或 `[\'label\'=>..,\'class\'=>..]`',
        'rows'    => '按**位置顺序**输出单元格：字符串（转义）或 `[\'class\'=>..,\'html\'=>..]`（原样）',
        'footable'=> '表尾行，规则同 `rows`',
    ],
    'form' => [
        'fields' => '组件实例 / HTML 字符串 / 数组的混合列表（逐个原样输出）',
    ],
    'wizard' => [
        'steps' => '`title`（导航标题）、`icon`、`content`（面板内容，原样输出）',
    ],
    'formOtherPlugin' => [
        'plugins' => '启用哪些插件段：`mask`、`autosize`、`maxlength`、`touchspin`',
    ],
    'placeholder' => [
        'items' => '`width`（列宽，如 col-6）、`lines`（行数数组，每项可为 `class`/`width`）',
    ],
    'kbd' => [
        'items' => '按键文本数组，如 `[\'Ctrl\', \'K\']`',
    ],
    'widgetsDashboard' => [
        'widgets' => '`title`、`content`（组件或 HTML）、`width`（栅格宽度）',
    ],
    'dashboardGrid' => [
        'widgets' => '`id`、`title`、`content`、`width`（1-12）、`height`',
    ],
];

/**
 * 主条目的补充用法示例
 * 仅用于「源码 docblock 中没有示例」的组件（生成器自动检测），内容取自真实用法。
 */
$USAGE_EXAMPLES = [
    'dataTable' => [
        "// ① 客户端模式（本地数据）",
        'echo XfAdmin::dataTable([',
        "    'id'      => 'user-table',",
        "    'columns' => [",
        "        ['key' => 'id',    'label' => 'ID', 'width' => '70px'],",
        "        ['key' => 'name',  'label' => '姓名'],",
        "        ['key' => 'status','label' => '状态', 'badges' => ['1' => 'success', '0' => 'secondary']],",
        "        ['key' => 'created_at', 'label' => '注册时间', 'render' => ['type' => 'datetime', 'ago' => true]],",
        "        ['key' => '', 'label' => '操作', 'actions' => [",
        "            ['label' => '编辑', 'icon' => 'ti ti-pencil', 'action' => 'edit', 'ajax' => '/admin/users/{id}'],",
        "            ['label' => '删除', 'icon' => 'ti ti-trash', 'action' => 'ajax', 'class' => 'btn-soft-danger',",
        "             'ajax' => '/admin/users/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？'],",
        '        ]],',
        '    ],',
        "    'data'    => \$rows,",
        ']);',
        '',
        "// ② 服务端模式（配合 XfAdmin::dataResponse()）",
        'echo XfAdmin::dataTable([',
        "    'id'          => 'user-table',",
        "    'ajax'        => '/admin/api/users',",
        "    'server_side' => true,",
        "    'method'      => 'POST',",
        "    'columns'     => [ /* 同上 */ ],",
        "    'filter_bar'  => [",
        "        ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],",
        "        ['name' => 'status',  'label' => '状态',   'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],",
        "        ['name' => 'date',    'label' => '注册时间', 'type' => 'daterange'],",
        '    ],',
        "    'create' => ['page' => '/admin/users/create', 'label' => '新增用户'],",
        "    'bulk'   => ['actions' => [",
        "        ['label' => '批量删除', 'url' => '/admin/users/batch-delete', 'method' => 'DELETE', 'confirm' => '确认？'],",
        '    ]],',
        "    'export' => ['copy', 'excel', 'csv'],",
        ']);',
    ],
    'customizer' => [
        "// 主题定制面板：默认由 Page 自动渲染，也可单独使用",
        'echo XfAdmin::customizer([',
        "    'title'    => '界面定制',",
        "    'subtitle' => '快速配置布局、皮肤与偏好',",
        ']);',
        '',
        "// 关闭定制面板：XfAdmin::page(['customizer' => false, …])",
        "// 面板内的 radio name 与 <html data-*> 属性一一对应：",
        "//   data-skin / data-bs-theme / data-topbar-color / data-menu-color / data-sidenav-size / data-layout-position",
        "//   配置持久化在 sessionStorage['__INSPINIA_CONFIG__']（由 config.js 管理）",
    ],
];

// 注意：不能用 const —— 示例中含 `$menu` 等变量占位（const 表达式不允许变量）
$ALIAS_EXAMPLES = [
    // —— auth 语义别名（均指向 AuthPage，自动注入 type）——
    'signIn' => [
        "echo XfAdmin::signIn([                       // 等价 authPage(['type' => 'sign-in'])",
        "    'layout'  => 'split',                    // base | card | split",
        "    'action'  => '/admin/login',",
        "    'ajax'    => true,",
        "    'heading' => '欢迎回来',",
        "    'fields'  => [",
        "        'username' => ['label' => '账号', 'required' => true, 'autofocus' => true],",
        "        'password' => ['label' => '密码', 'type' => 'password', 'required' => true],",
        '    ],',
        "    'submit'  => ['text' => '登录', 'icon' => 'ti ti-login'],",
        "    'captcha' => (string) XfAdmin::captcha(['mode' => 'image', 'src' => '/captcha.png']),",
        ']);',
    ],
    'signUp' => [
        "echo XfAdmin::signUp([                       // 等价 authPage(['type' => 'sign-up'])",
        "    'layout' => 'card',",
        "    'action' => '/admin/register',",
        "    'ajax'   => true,",
        "    'fields' => [",
        "        'name'     => ['label' => '姓名', 'required' => true],",
        "        'email'    => ['label' => '邮箱', 'type' => 'email', 'required' => true],",
        "        'password' => ['label' => '密码', 'type' => 'password', 'required' => true],",
        "        'password_confirmation' => ['label' => '确认密码', 'type' => 'password', 'required' => true],",
        '    ],',
        "    'append' => '<div class=\"form-check mb-3\"><input class=\"form-check-input\" type=\"checkbox\" name=\"agree\" id=\"agree\">'",
        "             . '<label class=\"form-check-label\" for=\"agree\">我已阅读并同意服务条款</label></div>',",
        "    'loginRedirect' => '/admin/login',",
        ']);',
    ],
    'resetPass' => [
        "echo XfAdmin::resetPass([                    // 等价 authPage(['type' => 'reset-pass'])",
        "    'layout' => 'base',",
        "    'action' => '/admin/password/email',",
        "    'ajax'   => true,",
        "    'fields' => ['email' => ['label' => '注册邮箱', 'type' => 'email', 'required' => true, 'autofocus' => true]],",
        "    'submit' => ['text' => '发送重置链接'],",
        ']);',
    ],
    'newPass' => [
        "echo XfAdmin::newPass([                      // 等价 authPage(['type' => 'new-pass'])",
        "    'layout' => 'base',",
        "    'action' => '/admin/password/new',",
        "    'ajax'   => true,",
        "    'email'  => 'user@example.com',           // 展示（disabled）",
        "    'newPassShowCode' => false,               // 是否显示 6 位验证码分格输入",
        "    'newPassShowAgree' => true,",
        "    'fields' => [",
        "        'password'              => ['label' => '新密码', 'type' => 'password', 'required' => true],",
        "        'password_confirmation' => ['label' => '确认新密码', 'type' => 'password', 'required' => true],",
        '    ],',
        ']);',
    ],
    'twoFactor' => [
        "echo XfAdmin::twoFactor([                    // 等价 authPage(['type' => 'two-factor'])",
        "    'layout' => 'base',",
        "    'action' => '/admin/2fa',",
        "    'ajax'   => true,",
        "    'mask'   => 'u***@example.com',           // 提示验证码发送目标（可省略）",
        "    'submit' => ['text' => '验证'],",
        ']);',
        '// 组件内部渲染 6 个分格输入（name=\"code[]\"），由前端自动拼接为单个值提交',
    ],
    'loginPin' => [
        "echo XfAdmin::loginPin([                     // 等价 authPage(['type' => 'login-pin'])",
        "    'layout'   => 'base',",
        "    'action'   => '/admin/pin-login',",
        "    'ajax'     => true,",
        "    'pinGroup' => 6,                         // PIN 位数，也可写 fields['pin']['group']",
        "    'submit'   => ['text' => '登录'],",
        ']);',
    ],
    'deleteAccount' => [
        "echo XfAdmin::deleteAccount([                // 等价 authPage(['type' => 'delete-account'])",
        "    'layout' => 'base',",
        "    'action' => '/admin/account/delete',",
        "    'ajax'   => true,",
        "    'message' => '注销后数据不可恢复，请谨慎操作。',",
        "    'fields' => ['password' => ['label' => '请输入密码确认', 'type' => 'password', 'required' => true, 'autofocus' => true]],",
        "    'submit' => ['text' => '确认注销', 'variant' => 'danger'],",
        ']);',
    ],
    'successMail' => [
        "echo XfAdmin::successMail([                  // 等价 authPage(['type' => 'success-mail'])",
        "    'layout' => 'base',",
        "    'status' => '重置链接已发送，请查收邮箱。',",
        "    'loginRedirect' => '/admin/login',",
        ']);',
        '// 该语义页无表单，仅展示成功图标 + 提示 + 返回链接',
    ],
    // —— 同类别名 ——
    'topnav' => [
        "echo XfAdmin::page([",
        "    'layout' => 'horizontal',                 // 或 'topnav'",
        "    'topnav' => ['menu' => \$menu, 'search' => true],",
        ']);',
        '// 单独使用等价写法：',
        "echo XfAdmin::topnav(['menu' => \$menu, 'current_url' => '/admin']);",
    ],
    'dateRangePicker' => [
        "echo XfAdmin::dateRangePicker([              // 等价 XfAdmin::dateRange()",
        "    'name'     => 'range',",
        "    'label'    => '下单时间',",
        "    'ranges'   => true,                      // 今天/昨天/最近7天/最近30天/本月/上月",
        "    'timepicker' => false,",
        "    'format'   => 'YYYY-MM-DD',",
        ']);',
    ],
    'clipboardButton' => [
        "echo XfAdmin::clipboardButton([              // 等价 XfAdmin::clipboard()",
        "    'text'  => 'https://example.com/invite/abc123',",
        "    'label' => '复制邀请链接',",
        "    'icon'  => 'ti ti-copy',",
        ']);',
    ],
];

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
    'target' => '目标（组件语义不同：链接打开方式 `_blank` / 倒计时目标时间 / 数值目标）',
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
    'trigger_variant' => '触发按钮的语义变体（primary/secondary/…）',
    'icon_bg' => '图标背景色（语义色名）',
    'subtitle' => '副标题文本',
    'initial' => '初始值（编辑器 / 上传组件的已有内容）',
    'deadline' => '截止时间（倒计时目标，任意可被 strtotime 解析的字符串）',
    'subscribe' => '是否显示订阅表单',
    'contact' => '联系信息（渲染为 mailto 链接）',
    'sideImage' => '侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径）',
    'sideTitle' => '侧栏主标题',
    'sideText' => '侧栏说明文本',
    'sideList' => '侧栏要点列表 `[\'icon\'=>..,\'text\'=>..]`',
    'sideVariant' => '侧栏语义变体（primary/info/success/…）',
    'socialButtons' => '社交登录按钮 `[\'icon\'=>..,\'url\'=>..,\'label\'=>..]`',
    'loginRedirect' => '「去登录」链接地址',
    'registerRedirect' => '「去注册」链接地址',
    'footerLinks' => '页脚链接 `[[\'url\'=>..,\'text\'=>..]]`',
    'preloader' => '是否显示首屏加载动画',
    'container' => '内容区容器 class（默认 container-fluid）',
    'customizer' => '是否渲染主题定制面板',
    'page_title' => '页面标题区（数组则渲染 PageTitle 组件）',
    'current_url' => '当前 URL（用于菜单自动高亮）',
    'dataset' => '数据集标识（批量操作 / 领域动作提交给后端）',
    'enhance_options' => '透传给增强插件（choices/select2）的原生配置',
    'enhance' => '下拉增强：null（原生）| choices | select2',
    'whitelist' => '标签输入候选词数组',
    'ranges' => '日期快捷区间（今天/昨天/最近7天/最近30天/本月/上月）',
    'timepicker' => '是否带时间选择',
    'single' => '单日期模式（singleDatePicker）',
    'question' => '验证码题目（math 模式）',
    'refreshable' => '验证码可刷新（换一张）',
    'mask' => '输入掩码表达式，如 `999-9999-9999`',
    'alias' => '掩码别名（部分驱动支持）',
    'tooltips' => '滑块是否显示数值气泡',
    'connect' => 'noUiSlider connect 配置（数组自动为 true）',
    'showRules' => '是否展示密码规则清单',
    'minScore' => '最低分数要求（低于则禁用提交按钮）',
    'hint' => '输入框下方提示文本',
    'length' => '长度 / 位数（如 OTP 格数 4-8）',
    'signature' => '个性签名（两行截断）',
    'labels' => '标签或文案数组（组件语义不同：按钮文案 / 单位文案 / 图表标签）',
    'progress_' => '进度',
    'footer_' => '页脚',
    // —— 业务数据键（各业务组件特有的数据数组 / 字段）——
    'activeTab' => '默认激活的选项卡 id',
    'add_text' => '「添加」按钮文案',
    'align_middle' => '单元格垂直居中',
    'allow' => '允许的行为（如允许的文件类型 / 操作）',
    'alt' => '图片替代文本',
    'apps' => '应用列表（应用启动器）',
    'article' => '文章数据（标题 / 正文 / 作者 / 封面等）',
    'attributes' => '属性列表（商品规格键值对）',
    'bars' => '柱状数据数组',
    'bio' => '个人简介',
    'blocks' => '内容区块数组',
    'bottom' => '底部内容 / 底部间距',
    'brands' => '品牌列表',
    'chart' => '图表配置（类型 / 数据 / 颜色）',
    'charts' => '多个图表配置数组',
    'clients' => '客户端 / 客户列表',
    'col' => '列宽（栅格列数 1-12）',
    'colClass' => '列 class（栅格）',
    'collapsible' => '是否可折叠',
    'compact' => '紧凑模式',
    'companies' => '公司数据数组',
    'contactEmail' => '联系邮箱',
    'controls' => '控件配置（播放器 / 轮播控件等）',
    'conversations' => '会话列表（聊天）',
    'cover' => '封面图地址',
    'csrf' => '是否注入 CSRF 隐藏域（`true` 注入；`[]`/`false` 不注入；数组为自定义隐藏域）',
    'currentLocale' => '当前语言',
    'currentStep' => '当前步骤索引（向导 / 步骤条）',
    'discount' => '折扣（金额或百分比）',
    'due_at' => '到期时间',
    'edges' => '边（桑基图 / 关系图的连线数据）',
    'effectiveDate' => '生效日期',
    'events' => '事件数组（日历 / 时间线）',
    'externalEvents' => '外部可拖拽事件（日历）',
    'features' => '特性 / 功能列表',
    'files' => '文件列表',
    'folders' => '文件夹列表（文件管理器）',
    'footable' => '表尾行数据',
    'from' => '起始值 / 来源地址',
    'headerBg' => '表头背景语义色（如 `primary`）',
    'icons' => '图标列表',
    'intro' => '导语 / 简介文本',
    'invoice' => '发票数据（单号 / 金额 / 状态等）',
    'invoice_no' => '发票号',
    'issued_at' => '签发时间',
    'issues' => '问题 / 工单列表',
    'keys' => '键名数组',
    'locales' => '语言 / 区域列表',
    'maxApps' => '最多显示的应用数量',
    'me' => '当前用户（聊天气泡定位自己）',
    'members' => '成员列表',
    'muted' => '次要 / 弱化显示',
    'nav' => '导航项数组',
    'node_height' => '节点高度（树图，px）',
    'node_width' => '节点宽度（树图，px）',
    'nodes' => '节点数据（桑基图 / 关系图）',
    'notes' => '备注',
    'peer' => '对方（会话对象）信息',
    'permissions' => '权限列表',
    'plans' => '方案 / 计划列表',
    'plugins' => '启用的插件列表',
    'poster' => '视频封面图',
    'posts' => '文章 / 帖子列表',
    'pricing' => '价格方案配置',
    'product' => '商品数据',
    'project' => '项目数据',
    'projects' => '项目列表',
    'provider' => '服务提供者 / 地图瓦片源',
    'ratio' => '宽高比（如 `16/9`）',
    'recentActivity' => '最近活动列表',
    'recentOrders' => '最近订单列表',
    'refunds' => '退款单列表',
    'reviews' => '评价列表',
    'roles' => '角色列表（`id`+`name` 或键值形式）',
    'row_attrs' => '行属性回调 `fn($row): array`，返回 `<tr>` 属性',
    'seller' => '卖家信息',
    'sellers' => '卖家列表',
    'set' => '集合 / 预设值',
    'showBuiltin' => '是否显示内置区块',
    'soft' => '柔和（浅色底）样式',
    'store' => '店铺数据',
    'sub_categories' => '子分类列表',
    'subtitle_desc' => '副标题描述',
    'tax' => '税费',
    'tax_rate' => '税率',
    'teams' => '团队列表',
    'testimonials' => '用户证言列表',
    'thread' => '会话 / 主题贴数据',
    'toc' => '目录（Table of Contents）列表',
    'totalCapacity' => '总容量',
    'totalInventory' => '总库存',
    'totalUnique' => '独立访客总数',
    'totalViews' => '浏览总数',
    'tour' => '引导漫游配置（步骤数组）',
    'treeView' => '树形视图数据',
    'trend_text' => '趋势说明文案（如「较上周」）',
    'type_filter' => '类型筛选条件',
    'users' => '用户列表',
    'views' => '浏览量数据',
    'warehouses' => '仓库列表',
    // 演示型 / 杂项组件开关
    'calendar' => '日历配置 / 是否启用日历',
    'clipboard' => '剪贴板配置 / 是否启用复制',
    'lightbox' => '灯箱配置 / 是否启用灯箱',
    'nestable' => '可拖拽排序配置 / 是否启用',
    'pdfViewer' => 'PDF 预览配置',
    'sweetAlert' => 'SweetAlert 弹窗配置',
    'textDiff' => '文本差异对比配置',
    'tinycon' => 'favicon 角标配置',
    'dataTable' => '内嵌数据表格配置',
    'dataTableToolbar' => '内嵌表格工具条配置',
    'tablesCustom' => '内嵌自定义表格配置',
    'table' => '内嵌表格配置 / 关联表格 id',
    // —— 布局 / 认证页 / 业务组件补充 ——
    'wrapper' => '外层包裹容器 class（`false`/`null` 时不包裹）',
    'stats' => '统计指标数组（如 `[[\'value\'=>..,\'label\'=>..]]`）',
    'feedback' => '校验反馈文案 `[\'valid\'=>..,\'invalid\'=>..]`',
    'messages' => '消息列表（聊天/通知/消息中心条目）',
    'groups' => '分组数据（下拉分组 / 权限分组 / 设置分组）',
    'card' => '是否以卡片容器呈现（部分组件为遗留键）',
    'showBackToTop' => '是否显示「回到顶部」按钮',
    'products' => '商品数据数组',
    'prepend' => '前缀内容（原样输出，如输入组文本/图标）',
    'append' => '后缀内容（原样输出，常用于协议说明）',
    'copyright' => '版权文案',
    'view' => '详情视图配置（viewRow 引擎，见数据表格文档）',
    'tag' => '标签 / 渲染标签名',
    'submit' => '提交按钮配置（字符串或数组：text/class/variant/icon）',
    'subheading' => '副标题（回退历史字段 subtitle）',
    'sideOverlay' => '侧栏是否显示渐变遮罩',
    'sideImageSize' => '侧栏背景 background-size',
    'sideImagePosition' => '侧栏背景 background-position',
    'sideImageAlt' => '侧栏背景图无障碍文本',
    'horizontal' => '是否水平排列',
    'flush' => '是否无边框（list-group-flush）',
    'contacts' => '联系人列表',
    'categories' => '分类列表',
    'captcha' => '验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位',
    'bodyClass' => '追加到 <form> 的 class',
    'below' => '表单下方补充内容（原样输出）',
    'beforeForm' => '插入到 <form> 之前的内容（原样输出）',
    'afterForm' => '插入到 </form> 之后的内容（原样输出）',
    'backLink' => '返回链接配置（保留兼容）',
    'zoom' => '地图缩放级别',
    'values' => '数值集合（图表/表单默认值/矩阵勾选值）',
    'topProducts' => '热销商品列表',
    'to' => '结束值 / 目标地址',
    'table' => '关联表格 id 或表格配置',
    'suffix' => '后缀文本',
    'subtotal' => '小计金额',
    'shipping' => '运费',
    'series' => '图表数据系列',
    'selected' => '是否选中 / 选中值',
    'center' => '地图/图表中心坐标',
    'tiles' => '地图瓦片地址（null 时离线空白底图）',
    'markers' => '地图标记点数组',
    'map' => '地图名称（如 world）',
    'options_' => '配置项',
    'head' => '</head> 前附加内容（原样输出）',
    'scripts' => '</body> 前附加内容（原样输出）',
    'filter_auto' => '过滤条件变更即自动查询',
    'column_filters' => '表头追加列筛选输入行',
    'fixed_header' => '表头固定',
    'defer_render' => '延迟渲染（本地数据 ≥100 行自动开启）',
    'show_custom_search' => '启用自定义搜索占位',
    'created_row' => '行创建回调（全局 JS 函数名）',
    'draw_callback' => '绘制完成回调（全局 JS 函数名）',
    'head_class' => '追加到 thead 的 class',
    'scroll_x' => '横向滚动',
    'scroll_y' => '纵向滚动高度',
    'server_side' => '服务端分页模式',
    'row_id' => '行 DOM id 字段',
    'auto_width' => '自动列宽（false 会影响 scrollX 判定）',
    'length_menu' => '每页条数选项',
    'info' => '是否显示分页信息',
    'processing' => '是否显示加载遮罩',
    'filter_bar' => '过滤工具条控件定义数组',
    'create' => '「新增」按钮配置',
    'bulk' => '批量操作配置',
    'row_detail' => '行明细展开（true 或 `[\'columns\'=>[..]]`）',
    'row_group' => '行分组字段名或 `[\'data\'=>..,\'empty\'=>..]`',
    'state_save' => '保存表格状态（分页/排序/搜索）',
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

/**
 * 枚举常量 → 实际允许值（与 `src/Components/Component.php` 中的 ENUM_* 常量保持一致）
 * 当组件源码使用 `enum($this->get('x'), self::ENUM_X, 'default')` 时，据此列出全部可选项。
 */
const ENUM_VALUES = [
    'ENUM_VARIANT'          => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'],
    'ENUM_VARIANT_OUTLINE'  => ['outline-primary', 'outline-secondary', 'outline-success', 'outline-danger', 'outline-warning', 'outline-info', 'outline-light', 'outline-dark'],
    'ENUM_SIZE'             => ['sm', 'lg'],
    'ENUM_PLACEMENT'        => ['top', 'bottom', 'left', 'right', 'start', 'end'],
    'ENUM_BREAKPOINT'       => ['sm', 'md', 'lg', 'xl', 'xxl'],
];

/**
 * 通用参数「全部可选项」字典（覆盖未显式调用 `enum()` 助手、仅用字面默认值的组件）。
 * 键名来自 `defaults()` 实际字段；仅当组件声明了该参数才会注入，不会给无关组件凭空编造选项。
 * 取值为 XfAdmin 全组件的通用约定（Bootstrap / Tabler），可在「全参数示例」基础上进一步明确。
 */
const PARAM_OPTIONS = [
    'variant'    => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'],
    'variant_outline' => ['outline-primary', 'outline-secondary', 'outline-success', 'outline-danger', 'outline-warning', 'outline-info', 'outline-light', 'outline-dark'],
    'size'       => ['sm', 'lg'],
    'placement'  => ['top', 'bottom', 'left', 'right', 'start', 'end'],
    'breakpoint' => ['sm', 'md', 'lg', 'xl', 'xxl'],
    'align'      => ['start', 'center', 'end'],
    'trigger'    => ['hover', 'click', 'focus'],
    'position'   => ['top', 'bottom', 'left', 'right'],
    'direction'  => ['horizontal', 'vertical', 'up', 'down', 'left', 'right'],
    'theme'      => ['light', 'dark', 'auto'],
    'orientation'=> ['horizontal', 'vertical'],
];

/**
 * 从组件源码（html() 及全文）分析某个参数的真实用法语义
 *
 * 返回可直接写入文档「说明」列的提示数组，来源全部是源码事实：
 *   - enum() → 白名单常量名 / 字面数组 + 回退默认值
 *   - match() → 分支取值
 *   - raw() / e() / text() → 槽位语义（内容槽位 vs 文本槽位）
 *   - cssLen/cssColor/cssBackground/safeUrl/img/gridCol → 安全校验类型
 *   - if (get(...)) → 开关语义
 */
function paramHints(string $src, string $key, string $methodSrc, bool $withUsage = false): array
{
    $hints = [];
    // $this->get('key') 的各种书写形式
    $get = "\\\\?\\\$this->get\(\s*'" . preg_quote($key, '#') . "'\s*\)";
    $get = "\\\$\s*this\s*->\s*get\s*\(\s*'" . preg_quote($key, '#') . "'\s*\)";

    // enum 白名单：enum($this->get('x'), self::ENUM_X | [...], 'default')
    if (preg_match("#enum\(\s*{$get}\s*,\s*(self::(\w+)|\[([^\]]*)\])\s*,\s*'([^']*)'#", $methodSrc, $m)) {
        $fallback = $m[5] ?? '';
        if (! empty($m[3])) {
            // self::ENUM_X：解析为实际允许值（见 ENUM_VALUES），列出全部可选项
            $const = $m[3];
            $vals  = ENUM_VALUES[$const] ?? null;
            if ($vals !== null) {
                $hints[] = '可选值：' . implode(' / ', array_map(fn ($v) => '`' . $v . '`', $vals))
                    . '（白名单 `' . $const . '`'
                    . ($fallback !== '' ? '，非法值回退 `' . $fallback . '`' : '') . '）';
            } else {
                $hints[] = '枚举白名单 `' . $const . '`'
                    . ($fallback !== '' ? '，非法值回退 `' . $fallback . '`' : '');
            }
        } else {
            $vals = array_filter(array_map('trim', explode(',', (string) $m[4])),
                fn ($v) => $v !== '' && $v !== "'" );
            $vals = array_map(fn ($v) => trim($v, "'\" "), $vals);
            if ($vals !== []) {
                $hints[] = '可选值：' . implode(' / ', array_map(fn ($v) => '`' . $v . '`', array_slice($vals, 0, 12)))
                    . ($fallback !== '' ? '，其它值回退 `' . $fallback . '`' : '');
            }
        }
    }

    // match 分支
    if (preg_match("#match\s*\(\s*{$get}\s*\)\s*\{(.*?)\n\s{0,20}\}#s", $methodSrc, $mm)) {
        if (preg_match_all("#^\s*'([^']+)'\s*=>#m", $mm[1], $cases)) {
            $cases = array_values(array_unique($cases[1]));
            if ($cases !== []) {
                $hints[] = '分支取值：' . implode(' / ', array_map(fn ($v) => '`' . $v . '`', array_slice($cases, 0, 12)));
            }
        }
    }

    // 槽位语义
    if (preg_match("#\\\$\s*this\s*->\s*raw\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = '**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）';
    }
    if (preg_match("#\\\$\s*this\s*->\s*(?:e|text)\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = '**文本槽位**：输出前自动 HTML 转义';
    }

    // 安全校验
    if (preg_match("#cssLen\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = 'CSS 长度：仅接受 `数字+单位`（px/%/rem/em/vh/vw/pt/ch/fr）';
    }
    if (preg_match("#cssColor\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = 'CSS 颜色：`#hex` / `rgb()` / `hsl()` / 具名色 / `var(--x)`';
    }
    if (preg_match("#cssBackground\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = 'CSS 背景：纯色 / `linear-gradient()` / `url()`';
    }
    if (preg_match("#cssRatio\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = 'CSS 比例：`N/M` 或纯数字';
    }
    if (preg_match("#safeUrl\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = 'URL：经协议白名单校验（拦截 `javascript:` 等）';
    }
    if (preg_match("#->\s*img\s*\(\s*{$get}#", $methodSrc) || preg_match("#img\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = '图片路径：外链 / `data:` URI 原样，其余解析为包内 `images/`';
    }
    if (preg_match("#gridCol\s*\(\s*{$get}#", $methodSrc)) {
        $hints[] = '栅格宽度：数字（1-12）或 `[\'md\'=>6]`（断点白名单 sm/md/lg/xl/xxl）';
    }

    // 开关语义
    if (preg_match("#if\s*\(\s*!?\s*\\\$\s*this\s*->\s*get\s*\(\s*'" . preg_quote($key, '#') . "'\s*\)#", $methodSrc)) {
        $hints[] = '开关：非空 / 真值时启用对应区块';
    }
    if (preg_match("#\\\$\s*this\s*->\s*get\s*\(\s*'" . preg_quote($key, '#') . "'\s*\)\s*!==\s*(?:null|''|\w+)#", $methodSrc)) {
        $hints[] = '为 `null` 时不渲染该区块';
    }
    // 参与属性输出
    if (preg_match("#'((?:class|style|width|height|id|href|src|type|name|value|title|alt|target|role|data-[\w-]*))'\s*=>\s*{$get}#", $methodSrc, $am)) {
        $hints[] = '输出到 `' . $am[1] . '` 属性';
    }

    // 兜底：无任何语义命中时，给出源码中的实际用法行（压缩空白后截断，全文搜索）
    if ($withUsage && $hints === []) {
        if (preg_match_all('#^.*?\$this->get\(\s*\'' . preg_quote($key, '#') . '\'\s*\).*$#m', $src, $ln)) {
            $snippet = trim((string) preg_replace('#\s+#', ' ', $ln[0][0]));
            if ($snippet !== '') {
                if (mb_strlen($snippet) > 88) {
                    $snippet = mb_substr($snippet, 0, 85) . '…';
                }
                $hints[] = '源码用法：`' . $snippet . '`';
            }
        }
    }

    return array_values(array_unique($hints));
}

/** 提取组件的 html() 方法源码（用于参数语义分析） */
function methodSource(string $file, string $method = 'html'): string
{
    $src = (string) (is_file($file) ? file_get_contents($file) : '');
    if ($src === '') {
        return '';
    }
    if (! preg_match('/function\s+' . $method . '\s*\([^)]*\)\s*(?::\s*\w+\s*)?\{/', $src, $m, PREG_OFFSET_CAPTURE)) {
        return $src;
    }
    $start = $m[0][1] + strlen($m[0][0]);
    $depth = 1;
    $i     = $start;
    $len   = strlen($src);
    while ($i < $len && $depth > 0) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
        }
        $i++;
    }
    return substr($src, $start, $i - $start);
}

/**
 * 解析「数组型参数」的元素结构：从源码的 foreach 循环体中抽取子键
 *
 * 例：'items' => []，源码 foreach ($items as $it) { $it['title'] … $it['image'] }
 *     → 返回 ['title' => '', 'image' => '']（值为该子键的默认值/兜底，未发现则空串）
 *
 * 支持两种循环来源：
 *   ① foreach ($this->get('KEY') as $v)
 *   ② $var = $this->get('KEY'); foreach ($var as $v)
 * 并识别 `$v['k'] ?? '默认'`、`$v['k'] ?: '默认'`、`Html::get($v,'k')`
 */
function arrayItemKeys(string $src, string $optionKey): array
{
    $qk = preg_quote($optionKey, '#');

    // ① 收集「循环变量」（即 foreach … as $v 中的 $v）
    //    注意 get() 可能有第二参数（默认值），也可能被 (array)/array_values() 包裹
    $loopVars = [];

    // A. 直接遍历：foreach (... $this->get('KEY') ... as [$k =>] $v)
    if (preg_match_all('#foreach\s*\(.*?\$this->get\(\s*\'' . $qk . '\'\s*[,)].*?as\s*(?:\$\w+\s*=>\s*)?(\$\w+)\s*\)#',
        $src, $m)) {
        $loopVars = $m[1];
    }

    // B. 先赋值再遍历：$x = ... $this->get('KEY') ...;  foreach ($x as [$k =>] $v)
    if (preg_match_all('#\$(\w+)\s*=[^;\n]*\$this->get\(\s*\'' . $qk . '\'\s*[,)]#', $src, $m2)) {
        foreach (array_unique($m2[1]) as $name) {
            if (preg_match_all('#foreach\s*\(\s*\$' . preg_quote($name, '#') . '\s+as\s*(?:\$\w+\s*=>\s*)?(\$\w+)\s*\)#',
                $src, $fm)) {
                $loopVars = array_merge($loopVars, $fm[1]);
            }
            // 变量本身也可能直接被下标访问（$x['k']）
            $loopVars[] = '$' . $name;
        }
    }
    if ($loopVars === []) {
        return [];
    }
    $loopVars = array_values(array_unique($loopVars));

    $sub = [];
    foreach ($loopVars as $loopVar) {
        $body = loopBody($src, $loopVar);
        // 变量本身不是循环变量时（B 中的直接下标访问），退化为全文搜索
        if ($body === '') {
            $body = $src;
        }
        $lv = preg_quote($loopVar, '#');
        // $v['key'] ?? '默认'
        if (preg_match_all('#' . $lv . '\[\s*\'([^\']+)\'\s*\]\s*(?:\?\?|\?\?)?\s*(?:\'([^\']*)\')?#',
            $body, $sm, PREG_SET_ORDER)) {
            foreach ($sm as $s) {
                $sub[$s[1]] = $s[2] ?? ($sub[$s[1]] ?? '');
            }
        }
        // Html::get($v, 'key')
        if (preg_match_all('#Html::get\(\s*' . $lv . '\s*,\s*\'([^\']+)\'#', $body, $gm)) {
            foreach ($gm[1] as $g) {
                $sub[$g] = $sub[$g] ?? '';
            }
        }
        // $v->key（对象属性）
        if (preg_match_all('#' . $lv . '->(\w+)#', $body, $om)) {
            foreach ($om[1] as $o) {
                if (! in_array($o, ['count', 'length'], true)) {
                    $sub[$o] = $sub[$o] ?? '';
                }
            }
        }
        // 委托渲染：$this->card($v) / $this->row((array) $v) —— 元素结构在私有方法里
        if (preg_match_all('#\$this->(\w+)\(\s*(?:\(array\)\s*)?' . $lv . '\s*[,)]#', $body, $dm)) {
            foreach (array_unique($dm[1]) as $method) {
                if (! preg_match('#function\s+' . preg_quote($method, '#') . '\s*\(\s*(?:array\s+)?(\$\w+)#',
                    $src, $pdef)) {
                    continue;
                }
                $mb = functionBody($src, $method);
                if ($mb === '') {
                    continue;
                }
                $pv = preg_quote($pdef[1], '#');
                if (preg_match_all('#' . $pv . '\[\s*\'([^\']+)\'\s*\]\s*(?:\?\?)?\s*(?:\'([^\']*)\')?#',
                    $mb, $pm, PREG_SET_ORDER)) {
                    foreach ($pm as $p) {
                        $sub[$p[1]] = $p[2] ?? ($sub[$p[1]] ?? '');
                    }
                }
                if (preg_match_all('#' . $pv . '->(\w+)#', $mb, $po)) {
                    foreach ($po[1] as $o) {
                        $sub[$o] = $sub[$o] ?? '';
                    }
                }
            }
        }
    }
    // 去掉明显不是数据键的噪音
    unset($sub['_xf'], $sub['class']);
    return $sub;
}

/** 取某个方法的方法体（按括号配对） */
function functionBody(string $src, string $method): string
{
    if (! preg_match('#function\s+' . preg_quote($method, '#') . '\s*\(#', $src, $m, PREG_OFFSET_CAPTURE)) {
        return '';
    }
    $i    = $m[0][1] + strlen($m[0][0]);
    $len  = strlen($src);
    $depth = 1;
    while ($i < $len && $depth > 0) {
        if ($src[$i] === '(') {
            $depth++;
        } elseif ($src[$i] === ')') {
            $depth--;
        }
        $i++;
    }
    while ($i < $len && $src[$i] !== '{') {
        $i++;
    }
    $start = $i + 1;
    $depth = 1;
    $i     = $start;
    while ($i < $len && $depth > 0) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
        }
        $i++;
    }
    return substr($src, $start, max(0, $i - $start - 1));
}

/** 取 foreach 循环体（按大括号配对） */
function loopBody(string $src, string $loopVar): string
{
    // 注意：foreach 头部可能自带 `)`（如 foreach ((array) $this->get('x', []) as $v)），
    // 因此不能用 [^)]*，改用非贪婪 .*?（不匹配换行，安全）
    if (! preg_match('#foreach\s*\(.*?as\s*(?:\$\w+\s*=>\s*)?' . preg_quote($loopVar, '#') . '\s*\)\s*(?::|{)#',
        $src, $m, PREG_OFFSET_CAPTURE)) {
        return '';
    }
    $pos = $m[0][1] + strlen($m[0][0]);
    if (substr($m[0][0], -1) === '{') {
        $depth = 1;
        $i     = $pos;
        $len   = strlen($src);
        while ($i < $len && $depth > 0) {
            if ($src[$i] === '{') {
                $depth++;
            } elseif ($src[$i] === '}') {
                $depth--;
            }
            $i++;
        }
        return substr($src, $pos, $i - $pos - 1);
    }
    // foreach(…): … endforeach;
    if (preg_match('#endforeach#', $src, $em, PREG_OFFSET_CAPTURE, $pos)) {
        return substr($src, $pos, $em[0][1] - $pos);
    }
    return '';
}

/** 提取组件渲染的外层 class（用于「渲染骨架」提示） */
function domClasses(string $methodSrc): array
{
    $cls = [];
    if (preg_match_all('#<\s*(?:div|ul|table|section|header|footer|span|a|form)\s+[^>]*class="([^"\$]*)"#', $methodSrc, $m)) {
        foreach ($m[1] as $c) {
            foreach (explode(' ', $c) as $one) {
                $one = trim($one);
                if ($one !== '' && preg_match('/^[a-z][\w-]*$/i', $one)) {
                    $cls[$one] = true;
                }
            }
        }
    }
    if (preg_match_all("#Html::cls\(\s*'([^']+)'#", $methodSrc, $m2)) {
        foreach ($m2[1] as $c) {
            foreach (explode(' ', $c) as $one) {
                $one = trim($one);
                if ($one !== '') {
                    $cls[$one] = true;
                }
            }
        }
    }
    return array_slice(array_keys($cls), 0, 8);
}

/** 把默认值转成 PHP 字面量（用于生成可直接复制的示例） */
function phpLiteral(mixed $v, int $level = 0): string
{
    if ($v === null) {
        return 'null';
    }
    if (is_bool($v)) {
        return $v ? 'true' : 'false';
    }
    if (is_int($v) || is_float($v)) {
        return (string) $v;
    }
    if (is_string($v)) {
        if (strlen($v) > 60) {
            return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], substr($v, 0, 57)) . "…'";
        }
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $v) . "'";
    }
    if (is_array($v)) {
        if ($v === []) {
            return '[]';
        }
        if ($level >= 2) {
            return '[/* … */]';
        }
        $pad  = str_repeat('    ', $level + 1);
        $out  = "[\n";
        $n    = 0;
        foreach ($v as $k => $vv) {
            if ($n++ >= 10) {
                $out .= $pad . "// …\n";
                break;
            }
            $out .= $pad . (is_int($k) ? '' : phpLiteral((string) $k) . ' => ') . phpLiteral($vv, $level + 1) . ",\n";
        }
        return $out . str_repeat('    ', $level) . ']';
    }
    return 'null';
}

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

    // 去重：同一别名在同一分类里只输出一次（避免重复章节）
    $aliases = [];
    foreach ($group['aliases'] as $a) {
        if (! isset($aliases[$a])) {
            $aliases[$a] = true;
        }
    }
    foreach (array_keys($aliases) as $alias) {
        // 已被其它分类输出过的别名不再重复输出
        if (isset($assigned[$alias])) {
            continue;
        }
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
            if (isset($ALIAS_EXAMPLES[$alias])) {
                $md[] = '**用法示例**（该别名的典型写法）';
                $md[] = '';
                $md[] = '```php';
                foreach ($ALIAS_EXAMPLES[$alias] as $line) {
                    $md[] = $line;
                }
                $md[] = '```';
                $md[] = '';
            }
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

        $methodSrc = methodSource((string) $rc->getFileName(), 'html');
        $fullSrc   = (string) (is_file((string) $rc->getFileName()) ? file_get_contents((string) $rc->getFileName()) : '');

        // ① 用法示例（源码 docblock，无则用手写补充示例）
        $examples = classExamples($class);
        if ($examples === [] && isset($USAGE_EXAMPLES[$alias])) {
            $examples = $USAGE_EXAMPLES[$alias];
        }
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

        // ② 全参数示例（枚举式，必输出；值取 defaults 默认，可直接复制运行）
        if ($options !== []) {
            $md[] = '<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>';
            $md[] = '';
            $md[] = '```php';
            $md[] = "echo XfAdmin::{$alias}([";
            foreach ($options as $k => $v) {
                $line = phpLiteral($v, 1);
                $note = $defaultsRows[$k]['comment'] ?? (PARAM_DICT[$k] ?? '');
                if ($note !== '') {
                    $note = explode('。', $note)[0];
                }
                if (str_contains($line, "\n")) {
                    // 多行数组：注释写在键前
                    $md[] = '    // ' . $k . ($note !== '' ? '：' . $note : '');
                    $md[] = '    ' . "'" . $k . "' => " . $line . ',';
                } else {
                    $md[] = "    '" . $k . "' => " . $line . ','
                        . ($note !== '' ? '    // ' . $note : '');
                }
            }
            $md[] = ']);';
            $md[] = '```';
            $md[] = '';
            $md[] = '</details>';
            $md[] = '';
        }

        // ②b 数据结构（数组型参数的元素键，来自源码 foreach 解析）
        $structLines = [];
        foreach ($options as $k => $v) {
            if (! is_array($v)) {
                continue;
            }
            $sub  = arrayItemKeys($fullSrc, $k);
            $note = $STRUCT_NOTES[$alias][$k] ?? '';
            if ($sub === [] && $note === '') {
                continue;
            }
            if ($sub !== []) {
                $parts = [];
                foreach ($sub as $sk => $sv) {
                    $parts[] = '`' . $sk . '`' . ($sv !== '' ? '（默认 `' . $sv . '`）' : '');
                }
                $line = '- `' . $k . '[]` 元素键：' . implode('、', array_slice($parts, 0, 24));
                if ($note !== '') {
                    $line .= '；补充：' . $note;
                }
                $structLines[] = $line;
            } else {
                $structLines[] = '- `' . $k . '[]`：' . $note;
            }
        }
        if ($structLines !== []) {
            $md[] = '**数据结构**（数组元素可用键，由源码 `foreach` 解析）';
            $md[] = '';
            foreach ($structLines as $l) {
                $md[] = $l;
            }
            $md[] = '';
        }

        // ②c 渲染骨架（外层 class，便于自定义样式）
        $domCls = domClasses($methodSrc);
        if ($domCls !== []) {
            $md[] = '> **渲染骨架**：主要 class `' . implode('` `', $domCls) . '`';
            $md[] = '';
        }

        // ③ 参数表（含源码语义提示）
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
                    $comment = '数组结构（见「全参数示例」）';
                }
                // 源码语义提示（枚举白名单 / 槽位 / 安全校验 / 开关）
                // 当源码给出精确枚举时，以源码为准（比通用词典更准确）
                // 说明来自「通用词典」或为空时，追加源码真实用法，避免词典语义与组件不符
                $fromDict = $comment !== '' && ($defaultsRows[$k]['comment'] ?? '') === '';
                $hints    = paramHints($fullSrc, $k, $methodSrc, $comment === '' || $fromDict);
                $strong = false;
                foreach ($hints as $h) {
                    if (str_contains($h, '枚举白名单') || str_contains($h, '可选值') || str_contains($h, '分支取值')) {
                        $strong = true;
                        break;
                    }
                }
                // 通用「全部可选项」注入：源码未给出枚举/分支时，用 PARAM_OPTIONS 补全全部可选项；
                // 源码已给出枚举白名单（如 self::ENUM_X）则把具体值附在其后，避免只显示常量名。
                if (isset(PARAM_OPTIONS[$k])) {
                    $fullOpts = '可选值：' . implode(' / ', array_map(fn ($v) => '`' . $v . '`', PARAM_OPTIONS[$k]));
                    $hints    = array_values(array_filter($hints, fn ($h) => ! str_starts_with($h, '可选值：')));
                    $hasEnum  = false;
                    foreach ($hints as &$h) {
                        if (str_contains($h, '枚举白名单')) { $h .= '；' . $fullOpts; $hasEnum = true; }
                    }
                    unset($h);
                    if (! $hasEnum) { $hints[] = $fullOpts; }
                    $strong = $strong || true;
                }
                if ($hints !== []) {
                    $comment = $strong
                        ? implode('；', $hints)
                        : trim(($comment !== '' ? $comment . '；' : '') . implode('；', $hints), '；');
                }
                $md[] = '| `' . $k . '` | ' . guessType($v) . ' | `' . cell($defTxt) . '` | ' . cell($comment) . ' |';
            }
            $md[] = '';
            $md[] = '> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）'
                . '见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。';
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

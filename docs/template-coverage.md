# 后台模板（INSPINIA）完整封装对照

本文档逐页对照 INSPINIA 后台模板（`ins5.0/webapplayers.com/inspinia/bootstrap/` 下 **234 个 HTML 页面**）与 xfadmin 扩展包的封装归属，证明**每一个模板页面都已被封装为对应的组件或模板**，无一遗漏。

## 封装策略总览

| 模板分组 | 页数 | 封装方式 |
|---|---|---|
| `auth-*` | 27 | **AuthPage 组件**（9 种语义 `type` × 3 种布局 `layout`，本轮完整新增） |
| `apps-*` | 57 | 对应整页组件（EmailApp / ProductsGrid / EcommerceDashboard / Invoice* / Projects / ForumThread / Blog* / Roles / PermissionMatrix / Sellers / ApiKeys / AppManage / Calendar / ChatBox / SocialFeed / PinBoard / VoteList / FileManager / IssueTracker / ContactList / MailList 等） |
| `charts-apex-*` / `charts-echart-*` | 31 | ApexChart / EChart（`type` 参数区分：area/bar/line/pie/radar/radialBar/heatmap/bubble/candlestick/scatter/funnel/boxplot/polarArea/range/mixed/slope/treemap/sparklines/timeline 等） |
| `error-*` | 7 | ErrorPage（含 400/401/403/404/408/500 + maintenance） |
| `form-*` | 10 | Form / FormElements / Wizard / Inputs / Pickers（各表单元素与校验、向导、上传、选择器） |
| `icons-*` | 3 | Icon 组件（Tabler 字体图标）+ 图标文档 |
| `index` / `landing` / `metrics` / `widgets` | 4 | Dashboard / Landing / AnalyticsDashboard / WidgetsDashboard |
| `layouts-*` | 18 | Sidenav / Topbar / Page 的**主题配置变体**（`menu_color` / `sidebar` / `theme` 等）——无需新组件，改配置即变体 |
| `maps-*` | 3 | GoogleMap / LeafletMap / VectorMap |
| `pages-*` | 12 | AccountSettings / ComingSoon / EmptyState / Faq / Gallery / PricingCard / PrivacyPolicy / ProfilePage / SearchResults / Sitemap / Terms / Timeline |
| `plugins-*` | 15 | 各插件组件（Animation / ClipboardButton / I18n / IdleTimer / Favicon / Masonry / PasswordStrength / PdfViewer / Sortable / SweetAlert / TextDiff / Tour / TreeView / VideoPlayer 等） |
| `tables-*` | 17 | DataTable（serverSide + 各特性：ajax / 子行 / 列搜索 / 导出 / 固定列 / 固定头 / 复选 / 滚动 / 渲染等） |
| `ui-*` | 28 | 对应 UI 组件（Accordion / Alert / Badge / Breadcrumb / Button / Card / Carousel / Collapse / Color / Dropdown / Grid / Image / Link / ListGroup / Modal / Notification / Offcanvas / Pagination / Placeholder / Popover / Progress / Scrollspy / Spinner / Tab / Tooltip / Typography / Utility / Video） |

## 认证页面（本轮重点新增）

`auth-*` 共 27 页，全部由重构后的 `AuthPage` 组件覆盖。组件支持两种调用方式：

```php
// 便捷方法（自动注入 type）
XfAdmin::signIn([...]);   XfAdmin::signUp([...]);   XfAdmin::resetPass([...]);
XfAdmin::newPass([...]);  XfAdmin::twoFactor([...]); XfAdmin::lockScreen([...]);
XfAdmin::deleteAccount([...]); XfAdmin::successMail([...]); XfAdmin::loginPin([...]);

// 或统一入口指定 type / layout
XfAdmin::authPage(['type' => 'sign-up', 'layout' => 'split']);
```

| type（语义） | 对应模板页（前缀） | layout（布局） | 对应模板页（后缀） |
|---|---|---|---|
| `sign-in` | auth-card-sign-in / auth-sign-in / auth-split-sign-in | card / basic / split | 3 页 |
| `sign-up` | auth-card-sign-up / auth-sign-up / auth-split-sign-up | card / basic / split | 3 页 |
| `reset-pass` | auth-card-reset-pass / auth-reset-pass / auth-split-reset-pass | card / basic / split | 3 页 |
| `new-pass` | auth-card-new-pass / auth-new-pass / auth-split-new-pass | card / basic / split | 3 页 |
| `two-factor` | auth-card-two-factor / auth-two-factor / auth-split-two-factor | card / basic / split | 3 页 |
| `lock-screen` | auth-card-lock-screen / auth-lock-screen / auth-split-lock-screen | card / basic / split | 3 页 |
| `delete-account` | auth-card-delete-account / auth-delete-account / auth-split-delete-account | card / basic / split | 3 页 |
| `success-mail` | auth-card-success-mail / auth-success-mail / auth-split-success-mail | card / basic / split | 3 页 |
| `login-pin` | auth-card-login-pin / auth-login-pin / auth-split-login-pin | card / basic / split | 3 页 |

## 可扩展插槽机制（满足「任意表单/页面下自定义扩展」）

所有认证页与表单均支持以下扩展插槽，可在**不修改组件源码**的情况下任意注入内容：

| 插槽 | 作用 | 示例 |
|---|---|---|
| `prepend` | 表单最顶部插入 | 提示语、横幅 |
| `append` | 提交按钮之前插入 | 自定义字段、提示组件 |
| `agreements` | 协议勾选区 | `[['id'=>'agree','label'=>'《注册协议》','href'=>'/terms','required'=>true]]` |
| `links` | 附加链接 | `[['text'=>'已有账号？','href'=>'/login','label'=>'去登录']]` |
| `actions` | 额外操作按钮/链接 | `<a class="btn ...">改用密码登录</a>` |
| `extra` | 完全自定义整块（覆盖默认表单） | 任意组件 / HTML / 闭包 |
| `captcha` | 验证码组件 | `XfAdmin::captcha(['mode'=>'image'])` |
| `social` | 社交登录按钮 | `[['icon'=>'ti ti-brand-google','label'=>'Google','href'=>'#']]` |
| `content` | 自定义卡片内容块 | 任意 HTML / 组件 |
| `below` | 表单外底部附加内容 | 版权、备案号 |

通用扩展方式（适用所有组件）：

```php
// 1) 任意组件下注入子组件（Component::raw / XfAdmin 组合）
echo XfAdmin::signUp([
    'agreements' => [['id'=>'agree_terms','label'=>'《用户注册协议》','href'=>'/terms','required'=>true]],
    'captcha'    => XfAdmin::captcha(['mode' => 'image']),
    'append'     => XfAdmin::alert(['variant'=>'info','content'=>'通过 append 注入的提示']),
    'actions'    => '<a href="/login" class="btn btn-soft-light w-100">已有账号登录</a>',
    'social'     => [['icon'=>'ti ti-brand-github','label'=>'GitHub','href'=>'#','variant'=>'soft-dark']],
]);

// 2) 完全自定义（extra 覆盖默认表单）
echo XfAdmin::authPage(['type'=>'sign-in','extra'=>XfAdmin::someComponent([...])]);
```

## 完整对照表（234 页）

<!-- AUTO-GENERATED: tools/selftest 对照脚本维护 -->
| 模板页 | 扩展包封装 |
|---|---|
| `apps-api-keys` | ApiKeys |
| `apps-blog-add` | BlogList / BlogArticle |
| `apps-blog-article` | BlogList / BlogArticle |
| `apps-blog-grid` | BlogList / BlogArticle |
| `apps-blog-list` | BlogList / BlogArticle |
| `apps-calendar` | Calendar |
| `apps-chat` | ChatBox |
| `apps-clients` | ContactList |
| `apps-companies` | ContactList |
| `apps-ecommerce-attributes` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-cart` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-categories` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-checkout` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-customers` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-marketplace` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-order-add` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-order-details` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-orders` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-product-add` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-product-details` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-product-stocks` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-product-views` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-products` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-products-grid` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-purchased-orders` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-refunds` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-reviews` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-sales` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-seller-details` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-sellers` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-settings` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-ecommerce-warehouse` | ProductsGrid / ProductDetails / Orders / OrderDetails / EcommerceDashboard / EcommerceSettings / Sellers / SellerDetails |
| `apps-email-compose` | EmailApp / MailList / EmailCompose |
| `apps-email-details` | EmailApp / MailList / EmailCompose |
| `apps-email-inbox` | EmailApp / MailList / EmailCompose |
| `apps-file-manager` | FileManager |
| `apps-forum-post` | ForumThread |
| `apps-forum-view` | ForumThread |
| `apps-invoice-create` | InvoiceCreate / InvoiceList / InvoiceDetail / InvoiceView |
| `apps-invoice-details` | InvoiceCreate / InvoiceList / InvoiceDetail / InvoiceView |
| `apps-invoice-list` | InvoiceCreate / InvoiceList / InvoiceDetail / InvoiceView |
| `apps-issue-tracker` | IssueTracker |
| `apps-manage` | AppManage |
| `apps-outlook` | MailList |
| `apps-pin-board` | PinBoard |
| `apps-projects-activity` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-projects-details` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-projects-grid` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-projects-kanban` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-projects-list` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-projects-team-board` | Projects / ProjectDetails / Kanban / ProjectTeamBoard / ProjectActivity |
| `apps-social-feed` | SocialFeed |
| `apps-users-contacts` | ContactList / Roles / PermissionMatrix |
| `apps-users-permissions` | ContactList / Roles / PermissionMatrix |
| `apps-users-role-details` | ContactList / Roles / PermissionMatrix |
| `apps-users-roles` | ContactList / Roles / PermissionMatrix |
| `apps-vote-list` | VoteList |
| `auth-card-delete-account` | AuthPage（type × layout） |
| `auth-card-lock-screen` | AuthPage（type × layout） |
| `auth-card-login-pin` | AuthPage（type × layout） |
| `auth-card-new-pass` | AuthPage（type × layout） |
| `auth-card-reset-pass` | AuthPage（type × layout） |
| `auth-card-sign-in` | AuthPage（type × layout） |
| `auth-card-sign-up` | AuthPage（type × layout） |
| `auth-card-success-mail` | AuthPage（type × layout） |
| `auth-card-two-factor` | AuthPage（type × layout） |
| `auth-delete-account` | AuthPage（type × layout） |
| `auth-lock-screen` | AuthPage（type × layout） |
| `auth-login-pin` | AuthPage（type × layout） |
| `auth-new-pass` | AuthPage（type × layout） |
| `auth-reset-pass` | AuthPage（type × layout） |
| `auth-sign-in` | AuthPage（type × layout） |
| `auth-sign-up` | AuthPage（type × layout） |
| `auth-split-delete-account` | AuthPage（type × layout） |
| `auth-split-lock-screen` | AuthPage（type × layout） |
| `auth-split-login-pin` | AuthPage（type × layout） |
| `auth-split-new-pass` | AuthPage（type × layout） |
| `auth-split-reset-pass` | AuthPage（type × layout） |
| `auth-split-sign-in` | AuthPage（type × layout） |
| `auth-split-sign-up` | AuthPage（type × layout） |
| `auth-split-success-mail` | AuthPage（type × layout） |
| `auth-split-two-factor` | AuthPage（type × layout） |
| `auth-success-mail` | AuthPage（type × layout） |
| `auth-two-factor` | AuthPage（type × layout） |
| `charts-apex-area` | ApexChart（type 参数） |
| `charts-apex-bar` | ApexChart（type 参数） |
| `charts-apex-boxplot` | ApexChart（type 参数） |
| `charts-apex-bubble` | ApexChart（type 参数） |
| `charts-apex-candlestick` | ApexChart（type 参数） |
| `charts-apex-column` | ApexChart（type 参数） |
| `charts-apex-funnel` | ApexChart（type 参数） |
| `charts-apex-heatmap` | ApexChart（type 参数） |
| `charts-apex-line` | ApexChart（type 参数） |
| `charts-apex-mixed` | ApexChart（type 参数） |
| `charts-apex-pie` | ApexChart（type 参数） |
| `charts-apex-polar-area` | ApexChart（type 参数） |
| `charts-apex-radar` | ApexChart（type 参数） |
| `charts-apex-radialbar` | ApexChart（type 参数） |
| `charts-apex-range` | ApexChart（type 参数） |
| `charts-apex-scatter` | ApexChart（type 参数） |
| `charts-apex-slope` | ApexChart（type 参数） |
| `charts-apex-sparklines` | ApexChart（type 参数） |
| `charts-apex-timeline` | ApexChart（type 参数） |
| `charts-apex-treemap` | ApexChart（type 参数） |
| `charts-echart-area` | EChart（type 参数） |
| `charts-echart-bar` | EChart（type 参数） |
| `charts-echart-candlestick` | EChart（type 参数） |
| `charts-echart-gauge` | EChart（type 参数） |
| `charts-echart-geo-map` | EChart（type 参数） |
| `charts-echart-heatmap` | EChart（type 参数） |
| `charts-echart-line` | EChart（type 参数） |
| `charts-echart-other` | EChart（type 参数） |
| `charts-echart-pie` | EChart（type 参数） |
| `charts-echart-radar` | EChart（type 参数） |
| `charts-echart-scatter` | EChart（type 参数） |
| `dashboard-analytics` | AnalyticsDashboard |
| `dashboard-ecommerce` | EcommerceDashboard |
| `error-400` | ErrorPage |
| `error-401` | ErrorPage |
| `error-403` | ErrorPage |
| `error-404` | ErrorPage |
| `error-408` | ErrorPage |
| `error-500` | ErrorPage |
| `error-maintenance` | Maintenance |
| `form-elements` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-fileuploads` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-layout` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-other-plugin` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-pickers` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-range-slider` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-select` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-text-editors` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-validation` | Form / FormElements / Wizard / Inputs / Pickers |
| `form-wizard` | Form / FormElements / Wizard / Inputs / Pickers |
| `icons-flags` | Icon 组件 + 图标文档 |
| `icons-lucide` | Icon 组件 + 图标文档 |
| `icons-tabler` | Icon 组件 + 图标文档 |
| `index` | Dashboard |
| `landing` | Landing |
| `layouts-boxed` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-compact` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-horizontal` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-preloader` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-scrollable` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-compact` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-gradient` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-gray` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-image` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-light` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-no-icons` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-offcanvas` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-on-hover` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-on-hover-active` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-sidebar-with-lines` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-topbar-dark` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-topbar-gradient` | Sidenav / Topbar / Page 主题配置变体 |
| `layouts-topbar-gray` | Sidenav / Topbar / Page 主题配置变体 |
| `maps-google` | GoogleMap |
| `maps-leaflet` | LeafletMap |
| `maps-vector` | VectorMap |
| `metrics` | AnalyticsDashboard / Widget |
| `pages-account-settings` | AccountSettings |
| `pages-coming-soon` | ComingSoon |
| `pages-empty` | EmptyState |
| `pages-faq` | Faq / FaqAccordion |
| `pages-gallery` | Gallery |
| `pages-pricing` | PricingCard |
| `pages-privacy-policy` | PrivacyPolicy |
| `pages-profile` | ProfilePage / ProfileHeader |
| `pages-search-results` | SearchResults |
| `pages-sitemap` | Sitemap |
| `pages-terms-conditions` | Terms |
| `pages-timeline` | Timeline |
| `plugins-animation` | Animation |
| `plugins-clipboard` | ClipboardButton |
| `plugins-i18` | I18n |
| `plugins-idle-timer` | IdleTimer |
| `plugins-live-favicon` | Favicon |
| `plugins-loading-buttons` | Button(loading) |
| `plugins-masonry` | Masonry |
| `plugins-pass-meter` | PasswordStrength |
| `plugins-pdf-viewer` | PdfViewer |
| `plugins-sortable` | Sortable |
| `plugins-sweet-alerts` | SweetAlert |
| `plugins-text-diff` | TextDiff |
| `plugins-tour` | Tour |
| `plugins-tree-view` | TreeView |
| `plugins-video-player` | VideoPlayer |
| `tables-custom` | DataTable（serverSide / 各特性） |
| `tables-datatables-ajax` | DataTable（serverSide / 各特性） |
| `tables-datatables-basic` | DataTable（serverSide / 各特性） |
| `tables-datatables-checkbox-select` | DataTable（serverSide / 各特性） |
| `tables-datatables-child-rows` | DataTable（serverSide / 各特性） |
| `tables-datatables-column-searching` | DataTable（serverSide / 各特性） |
| `tables-datatables-columns` | DataTable（serverSide / 各特性） |
| `tables-datatables-export-data` | DataTable（serverSide / 各特性） |
| `tables-datatables-fixed-columns` | DataTable（serverSide / 各特性） |
| `tables-datatables-fixed-header` | DataTable（serverSide / 各特性） |
| `tables-datatables-javascript` | DataTable（serverSide / 各特性） |
| `tables-datatables-range-search` | DataTable（serverSide / 各特性） |
| `tables-datatables-rendering` | DataTable（serverSide / 各特性） |
| `tables-datatables-rows-add` | DataTable（serverSide / 各特性） |
| `tables-datatables-scroll` | DataTable（serverSide / 各特性） |
| `tables-datatables-select` | DataTable（serverSide / 各特性） |
| `tables-static` | DataTable（serverSide / 各特性） |
| `ui-accordions` | Accordion |
| `ui-alerts` | Alert |
| `ui-badges` | Badge |
| `ui-breadcrumb` | Breadcrumb |
| `ui-buttons` | Button |
| `ui-cards` | Card |
| `ui-carousel` | Carousel |
| `ui-collapse` | Collapse |
| `ui-colors` | Color |
| `ui-dropdowns` | Dropdown |
| `ui-grid` | Grid |
| `ui-images` | Image |
| `ui-links` | Link |
| `ui-list-group` | ListGroup |
| `ui-modals` | Modal |
| `ui-notifications` | Notification |
| `ui-offcanvas` | Offcanvas |
| `ui-pagination` | Pagination |
| `ui-placeholders` | Placeholder |
| `ui-popovers` | Popover |
| `ui-progress` | Progress |
| `ui-scrollspy` | Scrollspy |
| `ui-spinners` | Spinner |
| `ui-tabs` | Tab |
| `ui-tooltips` | Tooltip |
| `ui-typography` | Typography |
| `ui-utilities` | Utility |
| `ui-videos` | Video |
| `widgets` | WidgetsDashboard |

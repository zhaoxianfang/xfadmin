# 模板页面映射

XfAdmin 拆分自 **INSPINIA v4.1.0** 后台模板。本文给出模板页面与本包组件的对应关系，
便于"照着模板页面找组件"。

## 1. 对照原则

| 模板概念 | XfAdmin 实现 |
|---|---|
| 布局文件（`layouts-vertical.html` 等） | `XfAdmin::page()` + `layout`/`topnav` 参数 |
| 页面内容区 | `content` 参数（由若干组件拼装） |
| 组件/小部件 | 对应 `XfAdmin::{alias}()` 组件 |
| 页面专用 JS | 由 `data-xf` widget + `xfadmin.js` 自动初始化 |

## 2. 仪表盘类

| 模板页面 | 组件组合 |
|---|---|
| Dashboard / Analytics | `statCard` × 4 + `apexChart`（line/area）+ `dataTable` |
| E-commerce Dashboard | `ecommerceDashboard`（整页组件） |
| Analytics Dashboard | `analyticsDashboard`（整页组件） |
| Widgets | `widgetsDashboard` / `widget` / `metricCard` / `statMiniSparkline` |
| Dashboard Grid | `dashboardGrid`（可拖拽 widget 布局） |
| Reports | `reportPage` + `echart` / `apexChart` |

## 3. 应用类（Apps）

| 模板页面 | 组件 |
|---|---|
| Calendar | `calendar` |
| Chat | `chatApp` / `chatBox` / `chatConversationPanel` / `chatMessageBubble` |
| Email / Inbox | `emailApp` / `mailList` / `emailCompose` |
| Outlook 风格邮件 | `outlook` |
| Kanban Board | `kanban` / `projectTeamBoard` / `issueTracker` |
| File Manager | `fileManager` |
| Invoice List / Detail / Create | `invoiceList` / `invoiceDetail` / `invoiceCreate` / `invoiceTable` / `invoiceView` |
| API Keys | `apiKeys` |
| App Manage | `appManage` |
| Companies / Clients | `companies` / `clients` |
| Todo | `todoList` |
| Contacts | `contactList` / `contactCard` |

## 4. 电商类（Ecommerce）

| 模板页面 | 组件 |
|---|---|
| Products Grid | `productsGrid` / `productCard` |
| Product Details | `productDetails` |
| Product Add / Edit | `productAdd` |
| Product Categories | `productCategories` |
| Product Views | `productViews` |
| Attributes | `attributes` |
| Orders / Order Details | `orders` / `orderDetails` / `orderTrackingTimeline` |
| Purchased Orders | `purchasedOrders` |
| Shopping Cart | `shoppingCart` / `cartSummary` |
| Checkout | `checkout` |
| Customers | `customers` |
| Sellers / Seller Details | `sellers` / `sellerDetails` |
| Refunds | `refunds` |
| Reviews | `reviewList` |
| Sales | `sales` |
| Warehouse | `warehouse` |
| Ecommerce Settings | `ecommerceSettings` |
| Marketplace | `marketplace` |
| Filter Sidebar | `filterSidebar` |
| Feature Comparison | `featureComparisonTable` |

## 5. 内容 / 社区类

| 模板页面 | 组件 |
|---|---|
| Blog List | `blogList` |
| Blog Article | `blogArticle` |
| Article | `article` |
| Forum Thread | `forumThread` |
| Comment Thread | `commentThread` |
| FAQ | `faq` / `faqAccordion` |
| Gallery | `gallery` |
| Search Results | `searchResults` / `searchResultsRich` |
| Social Feed | `socialFeed` |
| Activity Feed | `activityFeed` |
| Terms / Privacy | `terms` / `privacyPolicy` |
| Sitemap | `sitemap` |
| Pricing | `pricingCard` |
| Testimonials | `testimonial` |

## 6. 用户 / 组织 / 项目

| 模板页面 | 组件 |
|---|---|
| User Profile | `profilePage` / `profileHeader` / `userProfile` |
| Account Settings | `accountSettings` / `accountSettingsPanel` |
| Team Member | `teamMember` |
| Roles / Permissions | `roles` / `permissionMatrix` |
| Projects / Details | `projects` / `projectDetails` / `projectActivity` |
| Task List | `taskList` |
| Deals | `deals` |
| Vote List | `voteList` |

## 7. 认证 / 状态页

| 模板页面 | 组件 |
|---|---|
| Sign In | `signIn` / `authPage(['type'=>'sign-in'])` |
| Sign Up | `signUp` |
| Reset Password | `resetPass` |
| New Password | `newPass` |
| Two Factor | `twoFactor` |
| Lock Screen | `lockScreen`（独立组件）/ `authPage(['type'=>'lock-screen'])` |
| Login PIN | `loginPin` |
| Delete Account | `deleteAccount` |
| Success Mail | `successMail` |
| 404 / 500 等 | `errorPage` |
| Coming Soon | `comingSoon` |
| Maintenance | `maintenance` |
| Landing | `landing` |

## 8. UI 组件页（模板 `ui-*` 系列）

模板的每个 UI 演示页都对应 `XfAdmin::widgets()` 演示（见 `demo/pages/widgets.php`）：

| 模板页 | 组件 |
|---|---|
| ui-accordions | `accordion` |
| ui-alerts | `alert` |
| ui-avatars | `avatar` / `avatarGroup` |
| ui-badges | `badge` |
| ui-breadcrumb | `breadcrumb` |
| ui-buttons | `button` / `loadingButton` |
| ui-cards | `card` |
| ui-carousel | `carousel` |
| ui-dropdowns | `dropdown` |
| ui-list-group | `listGroup` |
| ui-modals | `modal` |
| ui-offcanvas | `offcanvas` |
| ui-pagination | `pagination` |
| ui-placeholders | `placeholder` / `skeleton` |
| ui-progress | `progress` |
| ui-scrollspy | `scrollspy` |
| ui-spinners | `spinner` |
| ui-tabs | `tabs` |
| ui-toasts | `toast` |
| ui-tooltips-popovers | `tooltip` / `popover` |
| ui-typography | `typography` |
| ui-utilities | `utilities` |
| ui-icons | `iconSet` |
| ui-rating | `rating` |
| ui-ribbon | `ribbon` |
| ui-stepper | `stepper` |
| ui-timeline | `timeline` |
| ui-video | `videoEmbed` / `videoPlayer` |

## 9. 表单页（模板 `form-*` 系列）

| 模板页 | 组件 |
|---|---|
| form-elements | `formElements` |
| form-layout | `formLayout` |
| form-validation | `formValidation` |
| form-wizard | `wizard` |
| form-editors | `editor`（quill / summernote） |
| form-file-uploads | `upload` / `dropzoneUpload` |
| form-other-plugin | `formOtherPlugin` |
| form-input-mask | `maskedInput` |
| form-tags | `tags` |
| form-range-slider | `slider` |
| form-select2 / choices | `select`（`enhance`） |
| form-pickers | `datePicker` / `dateRangePicker` / `colorPicker` |

## 10. 表格页（模板 `tables-*`）

| 模板页 | 组件 |
|---|---|
| tables-basic | `table` |
| tables-datatables | `dataTable` |
| tables-custom | `tablesCustom` |
| 表格工具条 | `dataTableToolbar` |

## 11. 图表页（模板 `charts-*`）

| 模板页 | 组件 |
|---|---|
| charts-apex-line/bar/column/pie/radar… | `apexChart`（按 `type`） |
| charts-apex-tree | `apexTree` |
| charts-apex-sankey | `apexSankey` |
| charts-echarts | `echart` |
| maps-vector | `vectorMap` |
| maps-google | `googleMap` |
| maps-leaflet | `leafletMap` |

## 12. 杂项页（模板 `misc-*` / `extended-*`）

| 模板页 | 组件 |
|---|---|
| Full Calendar | `calendar` |
| Tree View | `treeView` |
| Nestable | `nestable` |
| Lightbox | `lightbox` |
| Tour | `tour` |
| Clipboard | `clipboard` |
| Sweet Alert | `sweetAlert` |
| PDF Viewer | `pdfViewer` |
| Text Diff | `textDiff` |
| Masonry | `masonry` / `pinBoard` |
| Animate | `animate` |
| Idle Timer | `idleTimer` |
| Tinycon | `tinycon` |
| I18n | `i18n` |
| Video Player | `videoPlayer` |

## 13. 从模板页面迁移的建议步骤

1. 在上表找到对应组件；
2. 用 `XfAdmin::page()` 搭骨架（layout / menu / topbar）；
3. 内容区用 `row` / `col` + `card` 布局；
4. 逐块替换为组件调用；
5. 需要交互的部分优先用组件内置能力（`data-xf`），不要复制模板的页面专用 JS；
6. 模板的 `pages/*.js` 演示脚本**不使用**（本包用 `xfadmin.js` 统一初始化）。

## 14. 覆盖率说明

- 组件数：**226 个别名 / 215 个类**；
- 模板页面：约 220 个页面，绝大多数可由组件组合还原；
- 少量高度定制的营销页（如极复杂的单页）建议用 `XfAdmin::raw()` 直接嵌入 HTML。

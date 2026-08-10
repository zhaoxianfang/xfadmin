# INSPINIA 后台页面 → 组件映射

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

本扩展包已对 INSPINIA v4.1.0（Full）后台模块约 220 个 HTML 页面进行梳理，将其功能与逻辑拆分为可复用的组件/模板。下表按页面族映射，标注覆盖组件与说明。

> 注：大量页面是「同一功能的不同皮肤/布局变体」（如 `auth-2/sign-in`、`sidebar-* layout`、`dashboards` 的多种配色）。这些变体统一由 `page`/`authPage` 的 `layout`、`theme`、`skin` 等参数承载，无需为每个变体单独建组件。

## 布局 / 骨架

| 模板页（示例） | 覆盖组件 | 说明 |
|---|---|---|
| `index` `dashboard-2` `dashboard-3` | `page` `statCard` `widget` `apexChart` `eChart` `table` `activityFeed` | 仪表盘由小部件+图表自由组合 |
| `layouts-*`、`vertical` `horizontal` `twocolumn`、`detached` | `page`(`layout`) `sidenav` `topbar` `customizer` | 布局/皮肤变体 |
| `sidebar-*` | `page`(`layout`) `menu` | 侧栏变体 |

## 认证 / 错误 / 状态页

| 模板页 | 覆盖组件 |
|---|---|
| `auth-*`（sign-in/up/recover/lock/otp/2fa） | `authPage` + `form`/`input`/`check`/`button`；锁屏专用 `lockScreen` |
| `error-*`（404/500/…） | `errorPage` |
| `pages-coming-soon` | `comingSoon` |
| `maintenance` | `maintenance` |
| `pages-empty` | `emptyState` |
| `auth-lock-screen` | `lockScreen` |

## 应用模块

| 模板页 | 覆盖组件 |
|---|---|
| `chat` | `chatBox` |
| `email` `outlook` | `mailList` |
| `email-compose` | `emailCompose` |
| `ecommerce-products` `ecommerce-product-details` `ecommerce-orders` | `productCard` `dataTable` `statCard` |
| `ecommerce-cart` `ecommerce-checkout` | `table` `form` |
| `file-manager` | `fileManager` |
| `calendar` | `calendar` |
| `project-*`（kanban/overview/list/activity/create） | `kanban` `widget` `activityFeed` `timeline` `form` |
| `clients` `companies` `contacts` | `dataTable` `avatar` `badge` |
| `invoice` `invoice-details` `invoice-create` | `invoiceList` `invoiceTable` `form` |
| `roles` `permissions` | `permissionMatrix` `dataTable` |
| `api-keys` | `apiKeys` |
| `metrics` | `metricCard` `statCard` `widget` |
| `pin-board` | `gallery`(`masonry`) |
| `pages-profile` | `profileHeader` `timeline` `activityFeed` |
| `pages-pricing` | `pricingCard` |
| `pages-faq` | `faq` |
| `pages-timeline` | `timeline` |
| `pages-search-results` | `searchResults` |
| `blog` `blog-details` `article` `forum` `forum-post` | `blogList` `commentThread` `card` |
| `pages-terms-conditions` | `terms` |
| `misc-animation` | `animate` |
| `misc-i18` | `XFAdmin.i18n`（JS 模块 + Topbar 语言菜单联动，见 docs/i18n.md） |
| `maps-google` | `googleMap` |
| `charts-apextree` | `apexTree` |
| `charts-apexsankey` | `apexSankey` |
| `form-layouts` | `form`（`layout`: vertical / horizontal / inline） |

## UI / 组件演示页

| 模板页 | 覆盖组件 |
|---|---|
| `ui-*`（alerts/badges/buttons/…） | 对应 `alert` `badge` `button` `dropdown` `modal` `offcanvas` `tabs` `accordion` `progress` `spinner` `pagination` `listGroup` `avatar` `icon` `toast` `timeline` `carousel` `breadcrumb` `tooltip` `popover` `placeholder` `collapse` `scrollspy` `ratio` `rating` `ribbon` `chip` `stepper` `descriptionList` |
| `widgets` | `widget` `statCard` |
| `cards` | `card` |
| `nestable` | `nestable` |
| `tree-view` | `treeView` |
| `tour` | `tour` |
| `sweet-alerts` | `sweetAlert` |
| `clipboard` | `clipboardButton` |

## 表单页

| 模板页 | 覆盖组件 |
|---|---|
| `form-elements` `form-inputs` `form-checkbox` `form-radio` `form-switch` | `input` `textarea` `select` `check` `form` |
| `form-select` `form-pickers` | `select`(choices/select2) `dateRangePicker` `slider` `colorPicker` |
| `form-mask` `form-format` | `maskedInput` `tags` |
| `form-editor` | `editor`(quill/summernote) |
| `form-upload` | `upload`(dropzone/filepond) |
| `form-validation` | `form`(+ HTML5/`novalidate`) |
| `form-wizard` | `wizard` |
| `misc-pass-meter` | `passwordStrength` |

## 表格页

| 模板页 | 覆盖组件 |
|---|---|
| `tables-basic` `tables-grid` | `table` |
| `tables-datatable` `tables-*`（ajax/buttons/…） | `dataTable` |

## 图表页

| 模板页 | 覆盖组件 |
|---|---|
| `apex-*` | `apexChart` |
| `echart-*` | `eChart` |
| `maps-*`(vectormap) | `vectorMap` |
| `maps-leaflet` | `leafletMap` |

## 杂项页

| 模板页 | 覆盖组件 |
|---|---|
| `misc-gallery` | `gallery` |
| `misc-masonry` | `gallery`(`masonry`) |
| `misc-pdf-viewer` | `pdfViewer` |
| `misc-text-diff` | `textDiff` |
| `misc-loading-buttons` | `loadingButton` |
| `misc-live-favicon` | `tinycon` |
| `misc-idle-timer` | `idleTimer` |
| `maps-*`(leaflet) | `leafletMap` |

## 覆盖说明

- **完全离线**：所有 CSS/JS 资源已内置（见 `assets.md`），模板依赖的 CDN 资源全部本地化；`leafletMap` 底图瓦片需在线瓦片源，设 `tiles=null` 可离线渲染标记/图形。
- **嵌套无干扰**：每个组件自动生成唯一 `id`，`row`/`col`/`card`/`tabs`/`accordion`/`wizard` 等可任意嵌套（见 `components.md` 通用参数与 smoke 测试）。
- **事件/交互**：所有交互通过 `data-xf` 运行时自动初始化（`xfadmin.js`），全局事件委托（chip 关闭、菜单、复制、评论回复）幂等注册，多次渲染不重复绑定。
- **第三方库全局冲突隔离**：`apexcharts` 内置旧版 svg.js 同样占用全局 `window.SVG`，与 `apexSankey` 依赖的 `@svgdotjs/svg.js` v3 同页共存会互相破坏（apexcharts 抛 `selectionRect.draggable is not a function` / `parser Error`）。已由 `svg-guard-pre.js`/`svg-guard-post.js` 护栏在加载 svg.js v3 前后暂存并恢复 `window.SVG`，v3 另存为 `window.svgdotjs`，彻底解耦二者（见 `src/Assets/Assets.php` 的 `apexsankey` 资源定义）。

## 有意未单独拆分为组件（归属说明）

以下 INSPINIA 页面内容**未**建立独立组件，按其性质归入既有原语或已被取代，审计记录如下，避免「漏覆盖」误判：

| 模板页（INSPINIA） | 处理方式 | 理由 |
|---|---|---|
| `graph-flot` `graph-morris` `graph-chartjs` `graph-rickshaw` `graph-peity` | 由 `apexChart` / `eChart` 覆盖 | 上述为已停止维护或过时的图表库，本包统一以 ApexCharts / ECharts 实现同等图表类型 |
| `graph-sparkline` | 由 `apexChart`(`sparkline` 类型) / `eChart` 覆盖 | 迷你趋势图可作为图表组件的预设，无需独立组件 |
| `typography` `icons`(`ion-icons`) `animations` `css-animations` `social-buttons` | 由原生 HTML + `card` / `icon` / `button` 覆盖 | 纯 CSS/静态内容演示页，依赖浏览器原生能力，按需在 Blade/视图中直接书写即可 |
| `grid-options` `buttons` `badges` `lists` `video` | 由 `row`/`col` / `button` / `badge` / `listGroup` / 原生 `<video>` 覆盖 | 基础排版/元素演示，已有对应原语 |
| `landing` `app-views/empty` 等纯展示页 | 由 `page` + `card`/`emptyState` 自由组合 | 仅布局容器差异，用 `page` 布局参数即可承载 |

> 若后续需要，可将 `graph-sparkline` 等封装为独立组件（`sparkline`），属增强项而非缺失项。

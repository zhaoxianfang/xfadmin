# 🔍 组件与模板快速查找索引

> 不知道用什么组件？从本文开始。按**场景** / **关键词** / **字母**三种方式检索。
> 完整分类索引见 [组件总览](03-组件参考/00-总览.md)，字母序全表见
> [06-附录/01-component-index.md](06-附录/01-component-index.md)。

---

## 1. 30 秒定位（决策树）

```
我要输出一个完整后台页面            → page（[配方](03-组件参考/12-页面模板.md#21-仪表盘页)）
├─ 左侧菜单 + 顶栏                 → page + sidenav + topbar
├─ 水平顶部导航                    → page(layout=horizontal) + topNav
├─ 登录 / 注册 / 找回 / 锁屏       → signIn / signUp / resetPass / newPass / lockScreen
├─ 404 / 500 / 维护 / 即将上线     → errorPage / maintenance / comingSoon
└─ 营销首页                        → landing

我要展示数据
├─ 表格（排序/搜索/分页/导出）      → dataTable（[全指南](04-进阶指南/03-datatable.md)）
├─ 简单静态表格                    → table / tablesCustom
├─ 看板（拖拽分状态）              → kanban / projectTeamBoard / issueTracker
├─ 卡片网格（商品/应用/模块）       → productsGrid / marketplace / moduleGrid
├─ 时间线 / 活动流                 → timeline / activityFeed / orderTrackingTimeline
├─ 图表                            → apexChart / echart（[全指南](04-进阶指南/05-charts.md)）
├─ 地图                            → vectorMap / leafletMap / googleMap
└─ 指标数字                        → statCard / metricCard / statMiniSparkline / widget

我要收集数据
├─ 表单容器                        → form
├─ 文本 / 多行 / 选择 / 复选开关   → input / textarea / select / check
├─ 日期 / 范围 / 颜色 / 滑块       → datePicker / dateRange / colorPicker / slider
├─ 富文本 / 上传 / 标签 / 掩码     → editor / upload / tags / maskedInput
├─ 分步表单                        → wizard / stepper
└─ 验证码 / 密码强度 / OTP         → captcha / passwordStrength / twoFactorInput

我要展示内容
├─ 文章 / 博客 / 论坛 / 评论       → article / blogList / forumThread / commentThread
├─ 画廊 / 文件管理                 → gallery / fileManager
├─ FAQ / 条款 / 搜索结果           → faq / terms / searchResults
└─ 邮件 / 聊天                     → emailApp / mailList / chatApp / chatBox

我要交互增强
├─ 弹窗 / 抽屉 / 提示              → modal / offcanvas / toast / sweetAlert
├─ 日历 / 树 / 拖拽排序            → calendar / treeView / nestable
├─ 灯箱 / 引导 / 剪贴板            → lightbox / tour / clipboard
└─ 命令面板 / 通知中心             → commandPalette / notificationCenter
```

---

## 2. 按场景速查

| 我想做… | 推荐组件 | 文档 |
|---|---|---|
| 后台整页骨架 | `page` | [01-layout](03-组件参考/01-layout.md) |
| 侧边栏多级菜单 | `sidenav` + `menu` | [02-导航系统](04-进阶指南/02-navigation.md) |
| 水平顶部导航 / Mega 菜单 | `topNav` | [02-导航系统](04-进阶指南/02-navigation.md) |
| 顶栏（搜索/通知/消息/用户/语言） | `topbar` | [01-layout](03-组件参考/01-layout.md) |
| 页面标题 + 面包屑 | `pageTitle` | [01-layout](03-组件参考/01-layout.md) |
| 主题定制面板 | `customizer` | [07-样式与主题](04-进阶指南/07-css-theming.md) |
| 登录页 | `signIn` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 注册页 | `signUp` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 找回密码 / 设置新密码 | `resetPass` / `newPass` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 两步验证 / PIN 登录 | `twoFactor` / `loginPin` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 锁屏 | `lockScreen` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 404 / 500 | `errorPage` | [08-认证页](04-进阶指南/08-auth-pages.md) |
| 维护中 / 即将上线 | `maintenance` / `comingSoon` | [01-layout](03-组件参考/01-layout.md) |
| 落地页 / 营销页 | `landing` | [12-页面模板](03-组件参考/12-页面模板.md#27-落地页营销首页) |
| 数据列表（服务端分页） | `dataTable` + `dataResponse()` | [03-数据表格](04-进阶指南/03-datatable.md) |
| 数据列表（本地数据） | `dataTable` / `table` | [04-table](03-组件参考/04-table.md) |
| 表格搜索/筛选/分页工具条 | `dataTableToolbar` | [04-table](03-组件参考/04-table.md) |
| 行内编辑 / 单元格渲染 | `dataTable` 的 `render` | [03-数据表格 §4](04-进阶指南/03-datatable.md#4-单元格渲染器) |
| 批量操作 / 导入导出 | `dataTable` 的 `bulk` / `importExport` | [03-数据表格](04-进阶指南/03-datatable.md) |
| 表单（含 AJAX 提交） | `form` | [04-表单](04-进阶指南/04-forms.md) |
| 文件上传 | `upload` / `dropzoneUpload` | [03-form](03-组件参考/03-form.md) |
| 富文本编辑 | `editor` | [03-form](03-组件参考/03-form.md) |
| 分步向导 | `wizard` / `stepper` | [03-form](03-组件参考/03-form.md) |
| 权限矩阵 | `permissionMatrix` | [08-data-people](03-组件参考/08-data-people.md) |
| 角色管理 | `roles` | [08-data-people](03-组件参考/08-data-people.md) |
| 项目 / 任务 / 团队 | `projects` / `taskList` / `projectTeamBoard` | [08-data-people](03-组件参考/08-data-people.md) |
| 看板 | `kanban` | [08-data-people](03-组件参考/08-data-people.md) |
| 客户 / 公司 / 联系人 | `customers` / `companies` / `contactList` | [06/08](03-组件参考/08-data-people.md) |
| 商品 / 订单 / 购物车 / 结算 | `productsGrid` / `orders` / `shoppingCart` / `checkout` | [06-data-ecommerce](03-组件参考/06-data-ecommerce.md) |
| 发票 | `invoiceList` / `invoiceDetail` / `invoiceCreate` / `invoiceView` | [06-data-ecommerce](03-组件参考/06-data-ecommerce.md) |
| 库存 / 仓库 | `warehouse` | [06-data-ecommerce](03-组件参考/06-data-ecommerce.md) |
| 文章 / 博客 / 论坛 | `article` / `blogList` / `forumThread` | [07-data-content](03-组件参考/07-data-content.md) |
| 评论 / 评分 | `commentThread` / `reviewList` / `rating` | [07-data-content](03-组件参考/07-data-content.md) |
| 图片画廊 / 文件管理 | `gallery` / `fileManager` | [07-data-content](03-组件参考/07-data-content.md) |
| FAQ / 定价 / 条款 | `faq` / `pricingCard` / `terms` | [07-data-content](03-组件参考/07-data-content.md) |
| 邮件客户端 | `emailApp` / `mailList` / `outlook` | [09-data-comm](03-组件参考/09-data-comm.md) |
| 聊天 | `chatApp` / `chatBox` / `chatConversationPanel` | [09-data-comm](03-组件参考/09-data-comm.md) |
| 仪表盘 / 小部件 | `dashboardGrid` / `widgetsDashboard` / `widget` | [10-data-dashboard](03-组件参考/10-data-dashboard.md) |
| 报表 / 分析 | `reportPage` / `analyticsDashboard` | [10-data-dashboard](03-组件参考/10-data-dashboard.md) |
| 设置中心 / 账户设置 | `settingsCenter` / `accountSettings` | [10/08](03-组件参考/10-data-dashboard.md) |
| 图表（折线/柱/饼…） | `apexChart` | [05-chart](03-组件参考/05-chart.md) |
| 复杂图表（热力/关系） | `echart` | [05-chart](03-组件参考/05-chart.md) |
| 组织架构树 | `apexTree` | [05-chart](03-组件参考/05-chart.md) |
| 桑基图 | `apexSankey` | [05-chart](03-组件参考/05-chart.md) |
| 地图 | `vectorMap` / `leafletMap` / `googleMap` | [05-chart](03-组件参考/05-chart.md) |
| 日历 / 日程 | `calendar` | [11-misc](03-组件参考/11-misc.md) |
| 树形 / 拖拽排序 | `treeView` / `nestable` | [11-misc](03-组件参考/11-misc.md) |
| 灯箱 / 引导 / 剪贴板 | `lightbox` / `tour` / `clipboard` | [11-misc](03-组件参考/11-misc.md) |
| PDF 预览 / 文本对比 | `pdfViewer` / `textDiff` | [11-misc](03-组件参考/11-misc.md) |
| 命令面板 (⌘K) | `commandPalette` | [02-ui](03-组件参考/02-ui.md) |
| 通知中心 | `notificationCenter` | [02-ui](03-组件参考/02-ui.md) |
| 空状态 / 骨架屏 / 加载 | `empty` / `skeleton` / `spinner` | [02-ui](03-组件参考/02-ui.md) |

---

## 3. 按关键词速查

| 关键词 | 组件 |
|---|---|
| 表格 table grid list | `dataTable` `table` `tablesCustom` `dataTableToolbar` |
| 分页 paging | `pagination` `dataTable` |
| 搜索 search filter | `searchBox` `searchResults` `filterSidebar` `dataTable.filter_bar` |
| 排序 sortable drag | `nestable` `treeView` `kanban` `sortable` |
| 表单 form input | `form` `input` `textarea` `select` `check` |
| 日期 date time | `datePicker` `dateRange` `calendar` |
| 上传 upload file | `upload` `dropzoneUpload` `fileManager` |
| 编辑 editor | `editor` |
| 标签 tag chip badge | `tags` `chip` `badge` |
| 状态 status | `badge` `status`(渲染器) `statusPill` `ribbon` |
| 进度 progress | `progress` `stepper` `rating` |
| 弹窗 modal dialog | `modal` `offcanvas` `dialog`(JS) `sweetAlert` |
| 提示 toast alert notify | `toast` `alert` `callout` `notificationCenter` |
| 按钮 button | `button` `loadingButton` |
| 图标 icon | `icon` `iconSet` |
| 头像 avatar user | `avatar` `avatarGroup` `userProfile` `profileHeader` |
| 卡片 card panel | `card` `statCard` `metricCard` `widget` `productCard` |
| 选项卡 tab | `tabs` `accountSettingsPanel` |
| 折叠 collapse accordion | `collapse` `accordion` |
| 导航 nav menu breadcrumb | `sidenav` `topNav` `menu` `breadcrumb` `pagination` |
| 图表 chart graph | `apexChart` `echart` `metricCard` |
| 地图 map | `vectorMap` `leafletMap` `googleMap` |
| 看板 kanban board | `kanban` `projectTeamBoard` `issueTracker` |
| 聊天 chat message | `chatApp` `chatBox` `chatConversationPanel` `chatMessageBubble` |
| 邮件 email inbox | `emailApp` `mailList` `emailCompose` `outlook` |
| 订单 order | `orders` `orderDetails` `purchasedOrders` `refunds` |
| 商品 product | `productsGrid` `productCard` `productDetails` `productAdd` |
| 购物车 cart | `shoppingCart` `cartSummary` `checkout` |
| 发票 invoice | `invoiceList` `invoiceDetail` `invoiceCreate` `invoiceView` `invoiceTable` |
| 客户 customer client | `customers` `clients` `contactCard` `contactList` `companies` |
| 权限 permission role | `roles` `permissionMatrix` |
| 项目 project task todo | `projects` `projectDetails` `taskList` `todoList` `projectActivity` |
| 文章 article blog post | `article` `blogList` `blogArticle` |
| 评论 comment review | `commentThread` `reviewList` |
| 图片 image gallery photo | `gallery` `carousel` `lightbox` `image`(渲染器) |
| 文件 file document | `fileManager` `pdfViewer` |
| 设置 setting config | `settingsCenter` `accountSettings` `ecommerceSettings` |
| 日志 log activity | `activityFeed` `timeline` |
| 打印 print | `invoicePrintButton` `bindInvoicePrint` |
| 复制 copy clipboard | `clipboard` `copy`(渲染器) |
| 二维码 qr | `qr`(渲染器) `qrcode` 插件 |
| 动画 animation | `animate` `countUp` `countdown` `skeleton` |
| 布局 layout grid row col | `page` `row` `col` `dashboardGrid` `masonry` |
| 国际化 i18n language | `i18n` `XFAdmin.i18n` |
| 空白 empty placeholder | `empty` `placeholder` `skeleton` |
| 工具条 toolbar action | `toolbar` `dataTableToolbar` `actions`(渲染器) |

---

## 4. 模板速查

| 模板 | 入口 | 文档 |
|---|---|---|
| 仪表盘页 | `page` + `statCard` + `apexChart` | [配方 2.1](03-组件参考/12-页面模板.md#21-仪表盘页) |
| 列表页 | `page` + `card` + `dataTable` | [配方 2.2](03-组件参考/12-页面模板.md#22-列表页服务端表格--过滤--批量--新增) |
| 表单页 | `page` + `card` + `form` | [配方 2.3](03-组件参考/12-页面模板.md#23-表单页新增--编辑) |
| 详情页 | `descriptionList` + `timeline` | [配方 2.4](03-组件参考/12-页面模板.md#24-详情页字段--时间线--操作) |
| 登录页 | `signIn` | [配方 2.5](03-组件参考/12-页面模板.md#25-登录页) |
| 错误/维护页 | `errorPage` / `maintenance` | [配方 2.6](03-组件参考/12-页面模板.md#26-错误页--维护页) |
| 落地页 | `landing` | [配方 2.7](03-组件参考/12-页面模板.md#27-落地页营销首页) |
| 演示站 11 个模板 | `demo/pages/*.php` | [§3 演示站](03-组件参考/12-页面模板.md#3-演示站页面模板demo) |
| INSPINIA 220+ 页面映射 | — | [09-模板页面映射](04-进阶指南/09-templates-pages.md) |

---

## 5. 文档地图

| 目录 | 内容 |
|---|---|
| [01-快速开始](01-快速开始/) | 安装、上手、核心概念、全局配置 |
| [02-核心架构](02-核心架构/) | 架构、组件基类、资源、安全、数据协议、门面、扩展 |
| [03-组件参考](03-组件参考/) | **226 个组件 + 页面模板**（每个含示例 + 全参数示例 + 参数表） |
| [04-进阶指南](04-进阶指南/) | 页面布局、导航、数据表格、表单、图表、JS API、样式、认证页、模板映射、框架集成、**Cookbook** |
| [05-运维](05-运维/) | 测试、部署、性能、问题排查 |
| [06-附录](06-附录/) | 组件索引、参数字典、插件索引、FAQ、升级、更新日志 |

> 做具体任务（分页 / 过滤 / 批量 / 导入导出 / 弹窗 / 上传 …）：
> 查 **[12-常见任务手册 Cookbook](04-进阶指南/12-cookbook.md)**（22 个任务的完整方案）。

> 找不到？在 [06-附录/01-component-index.md](06-附录/01-component-index.md)
> （全部 215 个组件条目按字母序）用浏览器 Ctrl+F 搜索。

# XfAdmin

> 基于 **INSPINIA v4.1.0** 后台模板拆分的 PHP 组件化后台 UI 扩展包 ·
> **156 个组件** · 纯原生 JS（不依赖 Node 构建）· 离线可用 ·
> 支持 **Laravel 11/12** 与 **ThinkPHP 8+** · 要求 **PHP ≥ 8.2**

`XfAdmin` 把 INSPINIA 后台模板的布局、导航、表格、表单、图表、UI 与业务组件全部拆分为
可独立调用的 PHP 类。每个组件 `XfAdmin::card([...])` 即渲染一段语义化、可嵌套、资源自管理的 HTML，
让你在任意 PHP 项目中“一行代码”拼出完整后台界面。

```php
echo XfAdmin::page([
    'title'   => '仪表盘',
    'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
]);
```

---

## ✨ 特性

- **纯 PHP 组件化**：每个 UI 元素都是一个 PHP 类，返回 `(string)` 即 HTML，无模板引擎强绑定。
- **156 个组件全覆盖**：布局(14) / 导航(1) / 栅格(2) / UI 基础(46) / 表单(14) / 图表地图(7) / 表格(2) / 数据业务(53) / 杂项(15)，
  并完整覆盖 INSPINIA 后台约 220 个页面（见 [页面映射](docs/pages.md)）。
- **按需加载 + 自动去重**：组件声明自身依赖的 CSS/JS，同一资源在一个页面内**最多加载一次**（见 [资源与去重](docs/assets.md)）。
- **离线可用**：所有第三方插件（jQuery、Bootstrap 5、DataTables、ApexCharts、Tagify、FullCalendar…）均随包内置，不请求外网。
- **任意嵌套互不干扰**：组件通过唯一 ID / 作用域隔离，可无限层级混用。
- **数据驱动**：所有组件接受标准 PHP 数组，自动渲染、填充、绑定事件。
- **主题可定制**：外观由 `config.js` 在浏览器端持久化，无需重新编译；亦可在后端覆盖 `theme` / `brand` 配置。
- **安全优先**：所有动态文本默认经 `Html::e()` 转义；内联 JSON 经 `Html::scriptJson()` 转义，
  从源头杜绝 XSS（见 [安全与转义规范](docs/security.md)）。
- **框架无关核心**：渲染核心不依赖任何框架，Laravel / ThinkPHP 仅做服务注册与资源发布。

---

## 📦 组件一览（共 156 个）

完整参数与示例见 [组件总览](docs/components.md)。

| 分类 | 数量 | 组件 |
|------|------|------|
| 布局与页面 | 13 | `page` `authPage` `errorPage` `comingSoon` `maintenance` `emptyState` `lockScreen` `landing` `sidenav` `topbar` `pageTitle` `footer` `customizer` |
| 导航 | 1 | `menu` |
| 栅格 | 2 | `row` `col` |
| UI 基础 | 46 | `card` `statCard` `alert` `badge` `button` `dropdown` `modal` `offcanvas` `tabs` `accordion` `progress` `spinner` `pagination` `listGroup` `avatar` `avatarGroup` `icon` `toast` `timeline` `carousel` `breadcrumb` `tooltip` `popover` `placeholder` `collapse` `scrollspy` `ratio` `rating` `ribbon` `chip` `stepper` `descriptionList` `loadingButton` `divider` `kbd` `media` `callout` `skeleton` `countdown` `countUp` `backToTop` `codeBlock` `toggle` `empty` `toolbar` `searchBox` |
| 表单 | 14 | `form` `input` `textarea` `select` `check` `slider` `dateRangePicker` `editor` `upload` `colorPicker` `tags` `maskedInput` `wizard` `passwordStrength` |
| 图表与地图 | 7 | `apexChart` `apexTree` `apexSankey` `eChart` `vectorMap` `leafletMap` `googleMap` |
| 表格 | 2 | `table` `dataTable` |
| 数据 / 业务 | 53 | `widget` `metricCard` `terms` `pricingCard` `faq` `profileHeader` `profilePage` `productCard` `kanban` `chatBox` `invoiceTable` `invoiceList` `mailList` `fileManager` `activityFeed` `gallery` `blogList` `searchResults` `permissionMatrix` `apiKeys` `commentThread` `emailCompose` `companies` `productCategories` `productAdd` `sellerDetails` `article` `projectActivity` `contactCard` `companyCard` `clients` `sellers` `users` `products` 等 |
| 杂项 | 15 | `calendar` `treeView` `nestable` `lightbox` `tour` `clipboardButton` `sweetAlert` `raw` `tinycon` `idleTimer` `pdfViewer` `textDiff` `toggle` `masonry` `animate` |

> **组件别名**：`dateRange` / `dateRangePicker`、`clipboard` / `clipboardButton` 互为别名，调用任一均可。

---

## 🚀 快速开始

### 安装

```bash
composer require zxf/xfadmin
```

### Laravel 11 / 12

服务提供者与门面通过 `extra.laravel` 自动注册。发布静态资源与配置：

```bash
php artisan vendor:publish --tag=xfadmin-assets   # 发布到 public/zxf/xfadmin
php artisan vendor:publish --tag=xfadmin-config   # 发布配置（可选）
```

在控制器中：

```php
use zxf\XfAdmin\XfAdmin;

public function index()
{
    return response(XfAdmin::page([
        'title'   => '仪表盘',
        'layout'  => 'vertical',
        'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
    ]));
}
```

在 Blade 中：

```blade
{!! XfAdmin::page(['title' => '首页', 'content' => $html]) !!}
```

### ThinkPHP 8

在 `config/service.php` 注册服务：

```php
return [
    \zxf\XfAdmin\ThinkPHP\Service::class,
];
```

发布资源：

```bash
php think xfadmin:publish
```

### 原生 PHP（独立使用）

参考 `demo/` 目录：配置 `XfAdmin::config()` 后直接 `echo XfAdmin::page([...])` 即可，无需框架。

运行演示（资源会自动从 `/zxf/xfadmin` 前缀自托管，无需发布）：

```bash
php -S 127.0.0.1:8900 demo/index.php     # 直接运行
# 或：php -S 127.0.0.1:8900 demo/router.php
```

若浏览器中 CSS/JS 全部 404，请检查资源前缀是否为 `/zxf/xfadmin`，以及资源是否发布/自托管（见
[资源与去重机制 · 排错](docs/assets.md)）。

---

## 🧱 核心概念

### 1. 组件即方法

`XfAdmin::<组件名>(array $options)` 返回一个组件对象，`(string)` 转换或 `echo` 时渲染为 HTML。

```php
$card = XfAdmin::card(['title' => '标题', 'body' => '内容']);
echo $card;                 // 渲染
$html = (string) $card;    // 取字符串
$card->render();            // 显式渲染
```

### 2. 组件可嵌套

任何接受 HTML 的字段（`body` / `content` / `footer` …）都能直接塞入另一个组件：

```php
echo XfAdmin::card([
    'title' => '用户列表',
    'body'  => XfAdmin::dataTable([
        'columns' => ['ID', '姓名', '邮箱'],
        'data'    => $users,
    ]),
]);
```

### 3. 数据驱动

所有列表型组件接受统一结构的数组，例如菜单：

```php
['text' => '菜单', 'icon' => 'ti ti-x', 'url' => '/x', 'badge' => ['text' => '9', 'class' => 'bg-danger'], 'children' => [...]]
```

### 4. 资源自动管理

组件在渲染时把自身所需的 CSS/JS 登记到全局资源管理器。整页组件（`page` / `authPage`）会在
`<head>` 与页尾统一输出并**自动去重**。手动布局时用：

```php
echo XfAdmin::assets()->head();     // 放入 <head>
// ... 你的内容 ...
echo XfAdmin::assets()->scripts();  // 放在 </body> 前
```

### 5. 门面全局方法

除渲染组件外，门面还提供：`config()` `page()` `component()` `extend()` `has()` `componentList()`
`version()` `asset()` `head()` `scripts()` `html()`。例如：

```php
XfAdmin::has('dataTable');          // 是否已注册
XfAdmin::version();                 // '1.0.0'
XfAdmin::asset('css/xfadmin.css');  // /zxf/xfadmin/css/xfadmin.css?v=1.0.0
```

---

## 🎨 主题与资源

外观（`skin` / `mode` / `layout`）由 `config.js` 在浏览器端持久化；资源路径 `assets_url` 可在配置中
改为 CDN 前缀，所有资源 URL 自动附加 `?v={version}` 做缓存刷新。详见 [资源与去重机制](docs/assets.md)。

## 🔒 安全模型

所有动态文本默认转义，内联脚本数据经 HEX 转义，前端对用户输入再做二次转义，形成纵深防御。
暴露给 JS 的 API 仅接受可信数据。详见 [安全与转义规范](docs/security.md)。

## 🧩 扩展自定义组件

继承 `zxf\XfAdmin\Component` 实现 `render()`，调用 `XfAdmin::extend('myWidget', MyWidget::class)` 即可像内置组件一样使用。
Laravel 用户可注册 Blade 指令与服务提供者。详见 [自定义与扩展](docs/extending.md) 与 [ThinkPHP 集成](docs/thinkphp.md)。

---

## 📚 文档

| 文档 | 说明 |
|------|------|
| [组件总览](docs/components.md) | 全部 136 个组件的参数、事件与门面全局方法 |
| [组件详细参考](docs/components-reference.md) | 每个组件的数据输入/输出、前端控件（data-xf）与可复制用法示例 |
| [布局与页面](docs/layout.md) | `page` / `authPage` / `errorPage` 等整页骨架与布局变体 |
| [表格](docs/tables.md) | 静态 `table` 与全功能 `dataTable` |
| [表单](docs/forms.md) | 字段、富文本、上传、向导、密码强度等 |
| [图表](docs/charts.md) | ApexCharts / ECharts / 矢量地图 / Leaflet / 组织树 / 谷歌地图 |
| [多语言 i18n](docs/i18n.md) | data-lang 翻译、语言切换与 Topbar 联动 |
| [资源与去重机制](docs/assets.md) | 资源加载、去重、版本号与 CDN |
| [安全与转义规范](docs/security.md) | XSS 防护与转义助手 |
| [自定义与扩展](docs/extending.md) | 编写并注册自定义组件、Laravel 集成 |
| [ThinkPHP 集成](docs/thinkphp.md) | ThinkPHP 8 安装、发布与用法 |
| [页面映射](docs/pages.md) | INSPINIA 页面 → 组件覆盖矩阵 |
| [更新日志](docs/CHANGELOG.md) | 版本迭代记录 |

---

## 🌐 浏览器兼容

支持 Chrome / Edge / Firefox / Safari 的最新 2 个稳定版本。不兼容 IE（依赖 Bootstrap 5 与现代 DOM API）。

---

## 🆕 更新日志

见 [CHANGELOG.md](docs/CHANGELOG.md)。

## 📄 许可

[MIT](LICENSE) © zxf

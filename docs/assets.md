# 资源与去重机制

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 设计目标

- **离线可用**：jQuery、Bootstrap、DataTables、ApexCharts、ECharts、Choices、Flatpickr、Quill、Dropzone、Tagify、Inputmask、SortableJS、SweetAlert2、FullCalendar、jsVectorMap 等全部随包内置于 `resources/assets`，运行时不请求任何外网 CDN。
- **按需加载**：组件只声明自己需要的资源，未使用的插件不会被加载。
- **最多一次**：同一 CSS/JS 在单次页面渲染中**只输出一次**，无论组件被调用多少次、多少组件共享同一依赖。

## 工作原理

1. 每个组件通过 `assets(): array` 声明依赖的插件句柄（如 `['datatables', 'apexcharts']`）。
2. 渲染时组件调用 `XfAdmin::assets()->plugin($handle)` 登记到全局资源管理器（单例）。
3. 资源管理器内部按 handle / 文件路径去重，并递归解析依赖（如 `datatables` 依赖 `jquery`）。
4. 整页组件在 `<head>` 输出 CSS、在 `</body>` 前输出 JS，顺序遵循注册与依赖顺序。

## 资源句柄

内置句柄（部分）：

| 句柄 | 内容 | 依赖 |
|------|------|------|
| `jquery` | jQuery | — |
| `datatables` | DataTables + 按钮/响应式（jszip 已内含） | jquery |
| `datatables-pdf` | DataTables PDF 导出（pdfmake+字体） | datatables |
| `apexcharts` | ApexCharts | — |
| `echarts` | ECharts | — |
| `choices` | Choices.js（select 搜索） | — |
| `select2` | Select2 | jquery |
| `daterangepicker` | 日期区间选择 | jquery, moment |
| `nouislider` | 滑块 | — |
| `pickr` | 颜色选择 | — |
| `quill` | 富文本 | — |
| `dropzone` / `filepond` | 文件上传 | — |
| `tagify` | 标签输入 | — |
| `inputmask` | 输入掩码 | — |
| `sortablejs` | 拖拽（kanban/nestable） | — |
| `sweetalert2` | 弹窗 | — |
| `fullcalendar` | 日历 | — |
| `jsvectormap` / `jsvectormap-world` | 矢量地图 | — / jsvectormap |
| `glightbox` | 图片灯箱 | — |
| `jstree` | 树形 | jquery |

> 完整句柄见 `XfAdmin\Assets\Assets::PLUGINS`。

基础资源（Bootstrap、模板核心样式与脚本）随 `vendors.min.css`、`app.min.css`、`xfadmin.css`、`config.js`、`app.js`、`xfadmin.js` 由整页组件始终输出。

## 手动布局时输出资源

```php
echo XfAdmin::assets()->head();      // 输出所有已登记 CSS + 内联样式
// ... 组件渲染（会继续登记 JS 依赖）...
echo XfAdmin::assets()->scripts();   // 输出所有已登记 JS + 内联脚本（去重）
```

> `scripts()` 应在**所有组件渲染之后**调用，才能收集到全部依赖。整页组件 `page` 已自动保证此顺序。

## 手动追加自定义资源

```php
$assets = XfAdmin::assets();
$assets->plugin('apexcharts');            // 按句柄声明内置插件（含依赖，去重）
$assets->css('/css/my.css');              // 追加自定义 CSS 文件（去重）
$assets->js('/js/my.js');                 // 追加自定义 JS 文件（去重）
$assets->inlineCss('.x{color:red}');      // 内联样式（可传第二参 key 去重）
$assets->inlineJs('console.log("ready")');// 内联脚本（可传第二参 key 去重）
```

同一 URL 多次 `css()/js()` 也只会输出一次。`plugin()` 中未知句柄会被静默忽略。

## 资源基础路径

由配置 `xfadmin.assets_url` 决定（默认 `/vendor/xfadmin`）。发布资源后即对应 `public/vendor/xfadmin`。可在配置中改为 CDN 前缀或版本化路径。

```php
// config/xfadmin.php
'assets_url' => '/vendor/xfadmin',
'version'    => '1.0.0',   // 追加 ?v= 做缓存刷新
```

### 使用 `XfAdmin::asset()` 生成资源 URL

```php
// 自动拼接 assets_url 与 version，输出：/vendor/xfadmin/css/xfadmin.css?v=1.0.0
<link rel="stylesheet" href="<?= XfAdmin::asset('css/xfadmin.css') ?>">
```

### 切换为远程 CDN

只需把 `assets_url` 改为 CDN 前缀，其余代码无需改动：

```php
'assets_url' => 'https://cdn.example.com/xfadmin',
```

## 版本号缓存

所有资源 URL 自动附加 `?v={version}`，升级时改配置即可让浏览器刷新缓存。

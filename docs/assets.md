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

> 完整句柄见 `zxf\XfAdmin\Assets\Assets::PLUGINS`。

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

由配置 `xfadmin.assets_url` 决定（默认 `/zxf/xfadmin`）。发布资源后即对应 `public/zxf/xfadmin`。可在配置中改为 CDN 前缀或版本化路径。

```php
// config/xfadmin.php
'assets_url' => '/zxf/xfadmin',
'version'    => '1.0.0',   // 追加 ?v= 做缓存刷新
```

### 使用 `XfAdmin::asset()` 生成资源 URL

```php
// 自动拼接 assets_url 与 version，输出：/zxf/xfadmin/css/xfadmin.css?v=1.0.0
<link rel="stylesheet" href="<?= XfAdmin::asset('css/xfadmin.css') ?>">
```

### 切换为远程 CDN

只需把 `assets_url` 改为 CDN 前缀，其余代码无需改动：

```php
'assets_url' => 'https://cdn.example.com/xfadmin',
```

## 版本号缓存

所有资源 URL 自动附加 `?v={version}`，升级时改配置即可让浏览器刷新缓存。

## 排错：资源全部 404

若浏览器控制台出现 `GET /zxf/xfadmin/css/xfadmin.css` 等 **全部 404**，说明请求的资源路径与文件实际位置不一致。按使用场景逐项核对：

### 1. Laravel（开箱即用，无需发布）

自 v1.0.0 起，Laravel 服务Provider 已内置**资源自动托管路由**：当请求路径以 `assets_url`
前缀（默认 `/zxf/xfadmin`）开头时，若 `public/zxf/xfadmin` 下没有对应静态文件，Laravel 会
自动把请求映射到本包 `resources/assets` 目录并流式返回（与 `demo/index.php` 行为一致）。
因此**默认情况下无需执行发布命令即可直接运行**，浏览器不会再出现全部 404。

发布到 `public` 仍然是**推荐的生产做法**（由 Web 服务器直接服务静态文件，性能更好、不进 PHP）：

```bash
php artisan vendor:publish --tag=xfadmin-assets
```

- 已发布的文件：Web 服务器直接命中，路由不触发；
- 未发布 / 仅发布部分：`public` 中缺失的文件自动回退到包内 `resources/assets`；
- 改过 `assets_url`：自动托管路由会跟随该前缀，请确保包内 `resources/assets` 下文件完整。

#### 自动托管的安全与缓存（参照 zxf/trace 的资源控制器）

`zxf\XfAdmin\Laravel\AssetController` 采用「控制器方法」而非闭包路由提供资源，确保
`php artisan route:cache` / `php artisan optimize` 能安全序列化（闭包路由会递归序列化整个
应用容器导致内存耗尽）。其安全与缓存策略：

- **扩展名白名单**：路由层与控制器层双重限制，仅允许 `css/js/mjs/map/json/svg/png/jpg/
  gif/ico/webp/avif/woff/woff2/ttf/eot/otf` 等静态资源，杜绝 `.php` 等被当作资源输出；
- **路径穿越防护**：`realpath` 后必须位于 `resources/assets` 根目录内，否则 404；
- **ETag + 304 Not Modified**：客户端已缓存且未变更时直接返回 304，零流量；
- **强缓存**：`Cache-Control: public, max-age=31536000` + `Expires` 1 年，配合 `?v={version}`
  做缓存刷新（升级版本号即让浏览器重新拉取）。

> 若仍 404，确认：① `assets_url` 前缀与访问路径一致；② 项目根 `vendor/zxf/xfadmin/resources/assets`
> 目录完整；③ 若手动改过 `config/xfadmin.php` 中的 `assets_url`，请清理配置缓存
> `php artisan config:clear`。

### 1b. ThinkPHP（需发布）

ThinkPHP 暂无自动托管路由，资源需先发布到 `public/zxf/xfadmin`：

```bash
php think xfadmin:publish
```

发布后 `public/zxf/xfadmin/css/xfadmin.css` 等文件必须存在。若你改过 `assets_url`，
请确保该目录与 `assets_url` 前缀一一对应（例如改为 CDN 时，文件必须能在 CDN 上以同样的相对路径访问）。

### 2. 原生 PHP 演示（`demo/`）

`demo/index.php` 已内置**自托管能力**：当请求路径以 `/zxf/xfadmin/` 开头时，直接把
`resources/assets/` 下的对应文件流式返回（带正确的 Content-Type 与缓存头）。因此：

- 直接以 `php -S 127.0.0.1:8900 demo/index.php` 运行即可，**无需手动发布**；
- 也可用内置服务器路由：`php -S 127.0.0.1:8900 demo/router.php`（行为一致）。

> 若仍 404，请确认访问地址确实以 `/zxf/xfadmin/` 为前缀，且项目根目录下 `resources/assets`
> 目录完整（包含所有 `css/`、`js/`、`plugins/` 等）。

### 3. 自定义资源路径

`assets_url` 既可为本地路径（如 `/zxf/xfadmin`），也可为远程 CDN（如 `https://cdn.example.com/xfadmin`）。
无论哪种，文件都必须在该前缀下**物理可达**，否则必然 404。

### 4. 固定 `?v=1.0.0` 拼参导致 404？

不会。`?v=...` 只是查询串，Web 服务器按路径 `/zxf/xfadmin/css/xfadmin.css` 寻址，忽略 `?` 之后的版本号。
若路径本身存在则返回 200，浏览器再按 `?v` 做缓存区分。

# 更新日志

本文档记录 XfAdmin 的版本迭代。版本号遵循语义化版本（MAJOR.MINOR.PATCH）。

---

## 重要修复（按钮类型缺失导致全表崩溃）

- **根因**：`buttons.colVis.min.js` 未随包发布，`colvis` 按钮类型在 DataTables 中未注册；`refresh`/`fullscreen` 使用的 `xfButton` 基础类型也从未注册。任意含 `colvis`/`refresh`/`fullscreen` 的表格在 `new DataTable()` 时抛出 `Cannot extend unknown button type`，导致该表初始化中断、无法渲染/筛选/刷新（演示中所有表格默认都带这两个按钮，故「全部表格异常」）。
- **修复**：在 `datatable` 初始化器内注册缺失的 `colvis`（自实现列显隐下拉菜单，纯前端、零依赖）与 `xfButton`（空基础类型，具体行为由刷新/全屏处理器接管）；`new DataTable()` 包 `try/catch`，单表异常不再中断其余组件初始化。
- **StatCard 响应式**：新增 `width` 选项（`['sm'=>6,'xl'=>3]` 生成 `col-sm-6 col-xl-3` 列包裹 + 卡片 `h-100`），仪表盘统计卡片改为响应式网格排版。
- `tests/smoke.php` 新增 StatCard 响应式列宽回归断言。

---

## 重要变更（v1.0.0 第二阶段维护更新）

本批更新以「消除 DataTable Ajax 原生报错、补齐富单元格与交互、完善导航与多标签表单」为目标，所有能力均在**包内完成**，使用方只需提供数据与最小接线代码。

### 新增：DataTables 服务端处理 `DataSet`

- 新增 `zxf\XfAdmin\Support\DataSet` 与门面方法 `XfAdmin::dataResponse($rows, $params, $options)`，统一生成 DataTables serverSide 协议
  （`draw` / `recordsTotal` / `recordsFiltered` / `data`）。全局搜索、列搜索、自定义 `filters`、多列排序、分页、行 `transform` **全部在包内完成**。
- 双管线：数组数据 / Laravel 查询构造器（鸭子类型识别 `count`/`forPage`/`get`，不强依赖 Laravel）。
- 全面容错：兼容 `ConvertEmptyStringsToNull` 把 `search[value]` 转为 `null`、参数为数组/标量混杂、字段缺失等场景，绝不 500；`length` 超上限自动收敛到 1000。
- 前端 `dataTable` 服务端模式默认 `errMode: 'none'` + `error.dt` 以 toast 提示，彻底消除 `DataTables warning: table id=xxxx - Ajax error` 原生弹窗。

### 新增：富单元格渲染器体系

- `dataTable` 列支持 19 种单元格渲染器：`text` / `input` / `copy` / `ip` / `switch` / `tags` / `color` / `image` / `avatar` /
  `progress` / `bool` / `link` / `code` / `datetime` / `money` / `truncate` / `rating` / `icon` / `view`，以及 `actions` 操作栏。
- 可编辑输入框（`input`）与状态开关（`switch`）在前端通过事件委托自动 POST 指定 URL，失败回滚；操作栏支持
  图标按钮、下拉菜单、`confirm` 确认、`ajax` 提交、整行复制与自定义事件。`XFAdmin.cellRenderers` 支持运行时扩展/覆盖。

### 新增：过滤工具栏 `filter_bar`

- `dataTable` 配置 `filter_bar` 后自动渲染 `select` / `text` / `date` / `radio` 筛选控件，变更后前端自动拼接查询串并重载表格，
  并附带「重置」按钮。后端 `DataSet` 通过 `filters` 选项直接消费这些参数，无需手动接线。

### 新增：Tabs 多标签一次提交

- `Tabs` 组件新增 `footer`（tab-content 下方的公共区域，如统一提交按钮）与 `form`（用 `<form>` 包裹全部面板 + footer，支持 `ajax`
  自动挂 `data-xf="form"`），实现「多标签、一次提交」。每个 `item` 支持 `badge` 标记。

### 新增：菜单箭头与徽标

- 侧边栏含子菜单的项自动追加 `<span class="menu-arrow">` 小箭头标记。
- 菜单项 `badge` 支持 `['text' => '谨慎', 'class' => 'bg-warning']`，默认 `rounded-pill` 外观（`pill => false` 可关闭）。

### 新增：CSRF 自动注入与前端请求封装

- `Page` 在 `csrf` 选项开启（或检测到 `csrf_token()` 函数）时自动注入 `<meta name="csrf-token">`。
- 前端 `XFAdmin.request` 封装 `fetch`，自动携带 CSRF、解析 JSON、失败 toast；`XFAdmin.copyText` / `confirm` / `dialog` /
  `viewRow` / `reloadTable` 等便捷 API 一并提供。

### 文档

- [表格](tables.md) 补充：服务端 `DataSet`/`dataResponse`、`filter_bar`、富单元格渲染器总表与操作栏用法。
- [扩展组件](extending.md) 补充：前端交互事件（`xf:action` / `xf:switch` / `xf:cell-input` / `data-xf-event` / `xf:copy`）、
  单元格渲染器扩展与 `XFAdmin` API 速查。
- [组件详细参考](components-reference.md) 补充 Tabs（footer/form/badge）与 Menu（箭头/徽标）条目。

### 测试

- `tests/smoke.php` 新增 `DataSet` 服务端处理、`XfAdmin::dataResponse`、Tabs（footer/form/badge）、Menu（箭头/徽标）、
  DataTable（filter_bar/富单元格）的回归断言，全量冒烟通过。

---

## 重要变更（v1.0.0 维护更新）

本批更新以“与 Composer 包名 `zxf/xfadmin` 完全一致、并修复资源加载”为目标：

### 命名空间统一为 `zxf\XfAdmin`

- 所有 PHP 类由旧的 `XfAdmin\` 前缀迁移至 `zxf\XfAdmin\`（与包名对齐）。
- 门面别名 `XfAdmin` 保持不变；Laravel 下仍用 `XfAdmin::xxx()`，原生 PHP / ThinkPHP 下用
  `use zxf\XfAdmin\XfAdmin;` 后同样 `XfAdmin::xxx()` 调用。
- Composer 自动发现配置同步更新：`extra.laravel.providers` → `zxf\XfAdmin\Laravel\XfAdminServiceProvider`、
  `extra.laravel.aliases` → `zxf\XfAdmin\Laravel\Facades\XfAdmin`、`extra.think.services` → `zxf\XfAdmin\ThinkPHP\Service`。

### 资源路径前缀改为 `/zxf/xfadmin`

- 默认 `assets_url` 由 `/vendor/xfadmin` 改为 `/zxf/xfadmin`。
- Laravel 资源发布目标 `public/zxf/xfadmin`，ThinkPHP `php think xfadmin:publish` 同样输出到 `public/zxf/xfadmin`。
- 旧的 `vendor/xfadmin` 前缀不再使用。

### 修复：演示资源全部 404

- `demo/index.php` 现在在脚本内直接自托管 `/zxf/xfadmin/*` 请求（映射到 `resources/assets`，带正确
  Content-Type 与缓存头），无需手动发布即可在浏览器正常加载 CSS/JS。
- `demo/router.php` 同步适配新前缀，并增加路径穿越（`..`）防护。

### 修复：Laravel 项目中所有 JS/CSS/图片资源 404

- 根因：Laravel 应用没有像 `demo/index.php` 那样的自托管拦截，资源仅在执行
  `vendor:publish --tag=xfadmin-assets` 发布到 `public/zxf/xfadmin` 后才可达；未发布时
  `/zxf/xfadmin/*` 请求进入 Laravel 路由却无匹配项，导致全部 404。
- 新增 `zxf\XfAdmin\Laravel\AssetController` 与 `XfAdminServiceProvider::boot()` 中的自动托管路由：
  请求到达时若 `public/zxf/xfadmin` 不存在对应静态文件，自动从包内 `resources/assets` 流式返回
  （带正确 Content-Type 与缓存头，含路径穿越 `..` 防护），**实现 Laravel 开箱即用、无需发布**。
- 已发布的静态文件由 Web 服务器直接服务、路由不命中；仅发布部分资源时，缺失文件自动回退到包内，无需重新全量发布。
- 控制器参照 `zxf/trace` 的 `AssetController` 实现强化：
  - 采用「控制器方法」而非闭包路由，确保 `route:cache` / `optimize` 能安全序列化（避免递归序列化
    应用容器导致内存耗尽）；
  - 路由层 + 控制器层**双重扩展名白名单**，仅放行静态资源（css/js/svg/png/jpg/gif/ico/json/
    map/woff/woff2/ttf 等），杜绝 `.php` 等被当作资源输出；
  - 新增 **ETag + 304 Not Modified** 与 **Cache-Control: max-age=31536000**（1 年）强缓存。
- ThinkPHP 暂无自动托管路由，仍需 `php think xfadmin:publish`。

### 修复：插件样式在 head 之后丢失

- 根因：组件在**渲染期**（`render()` 内）才登记插件 CSS，而 `head()` 通常在渲染前输出，导致这部分 CSS
  被静默丢弃（DataTables、Select2、FullCalendar 等插件样式失效）。
- 修复：`scripts()` 现在兜底补输出 `head()` 之后注册的 CSS / 内联 CSS，确保样式始终生效。

### 全面审计：资源注册与组件功能

- **资源可达性验证**：脚本化渲染全部 6 个 demo 页面，提取其中引用的每个 `/zxf/xfadmin/*` 资源 URL，
  逐一比对 `resources/assets` 物理文件，确认 **0 个 404**（home 35 / forms 63 / tables 78 / charts 83 /
  login 60 / error404 61 个资源全部可达）。`Assets::PLUGINS` 注册的 94 个 css/js 文件也全部存在，
  各组件 `assets()` 声明的插件键均与 `PLUGINS` 表一一对应，无静默丢失。
- **组件渲染验证**：对全部 **99 个具体组件**逐一 `render()`，无致命错误 / 异常（仅抽象基类 `Component`
  因不可实例化被跳过）。`page` / `authPage` / `errorPage` 整页骨架、`DataTable` / `Select` / `TreeView` /
  `ApiKeys` 等数据组件、`Html` 转义助手（ENT_QUOTES + `Html::scriptJson`）均通过审计，无 XSS 注入点。
- **修正文档示例中的无效图片路径**：`BlogList` 示例由 `images/blog/1.jpg` 改为真实存在的
  `images/blog/blog-1.jpg`，`Gallery` 示例由 `images/01.jpg` 改为 `images/gallery/1.jpg`，避免文档
  复制即 404。

### 新增：组件详细参考文档

- 新增 [组件详细参考](components-reference.md)：由代码反射自动生成，覆盖全部 99 个组件，逐项列出
  别名、用途、输入参数（数据）、前端控件（`data-xf`）与可复制用法示例，并在顶部说明数据输入 / 输出通用约定。
- [组件总览](components.md) 顶部新增「数据输入与输出约定」，并链接到详细参考；[README](README.md)
  文档目录同步新增条目。

---

## v1.0.0

首个正式版本。

### 新增

- **99 个组件**：覆盖布局与页面、导航、栅格、卡片与统计、表格、表单、图表与地图、UI 组件、
  业务/数据组件、杂项共 10 大类，完整映射 INSPINIA v4.1.0 后台约 220 个页面。
- **框架集成**：
  - Laravel 11 / 12：自动注册服务提供者、门面 `XfAdmin`、Blade 指令，支持资源与配置发布。
  - ThinkPHP 8：自动注册服务与助手函数，提供 `xfadmin:publish` 命令发布资源。
  - 原生 PHP：独立 `demo/` 演示，无需框架即可使用。
- **资源去重机制**：组件声明自身依赖，同一资源在单页内最多加载一次；`head()` 输出 CSS + 主题配置，
  `scripts()` 输出 JS + 初始化。
- **离线优先**：所有第三方插件（jQuery、Bootstrap 5、DataTables、ApexCharts、ECharts、Tagify、
  FullCalendar、pdf.js、GLightbox、SweetAlert2、Shepherd 等）随包内置，不请求外网。
- **门面全局方法**：`config()` `page()` `component()` `extend()` `has()` `componentList()`
  `version()` `asset()` `head()` `scripts()` `html()`，以及常量 `XfAdmin::VERSION`。
- **组件别名**：`dateRange` / `dateRangePicker`、`clipboard` / `clipboardButton` 互为别名。

### 安全

- 所有动态文本默认经 `Html::e()`（`ENT_QUOTES`，UTF-8，不二次编码）转义。
- 内联 `<script>` 中的 JSON 经 `Html::scriptJson()`（`JSON_HEX_TAG` / `JSON_HEX_AMP` 等）转义，
  防止 `</script>` 注入与存储型 XSS。
- 属性值统一通过 `Html::attrs()` 转义；前端 `xfadmin.js` 对用户输入再做 `escapeHtml()` 二次转义。
- 文档化可信边界：[安全与转义规范](security.md)。

### 文档

- `README.md` 组件速查、快速开始、核心概念与完整文档索引。
- `docs/` 下 10 篇专题文档（组件总览、布局、表格、表单、图表、资源、安全、扩展、ThinkPHP、页面映射）。

### 已知约束

- 不兼容 IE（依赖 Bootstrap 5 与现代 DOM API）。
- 主题切换依赖浏览器端 `localStorage`，SSR 场景需在服务端读取并预置。

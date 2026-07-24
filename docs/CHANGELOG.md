# 更新日志

本文档记录 XfAdmin 的版本迭代。版本号遵循语义化版本（MAJOR.MINOR.PATCH）。

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

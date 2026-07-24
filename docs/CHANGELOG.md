# 更新日志

本文档记录 XfAdmin 的版本迭代。版本号遵循语义化版本（MAJOR.MINOR.PATCH）。

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

# xfadmin 文档中心

本目录收录 xfadmin 扩展包的全部文档。所有组件与模板的详细参考已自动生成并保持同步。

## 快速导航

| 文档 | 内容 |
|------|------|
| [components-reference.md](components-reference.md) | **组件详细参考（自动生成）**：全部 **149 个组件 / 151 个别名**，含分类、类路径、描述、依赖资源、全部 `defaults()` 选项（含默认值与行内说明）、链式方法与实际调用示例 |
| [components.md](components.md) | 组件总览：设计理念、目录结构、命名约定、数据输入/输出与 `data-xf` 控件说明 |
| [categories/ui.md](categories/ui.md) | 基础 UI 组件分类清单（41 个）：Alert / Avatar / Button / Card / Tabs / Timeline … |
| [categories/data.md](categories/data.md) | 数据 / 业务组件分类清单：PricingCard / Kanban / ProfilePage / ProductCard … |
| [categories/navigation.md](categories/navigation.md) | 导航组件分类清单：Sidenav / Topbar / Breadcrumb / Dropdown … |
| [categories/layout.md](categories/layout.md) | 布局组件分类清单 |
| [categories/charts.md](categories/charts.md) | 图表组件分类清单：ApexChart / EChart / LeafletMap … |
| [categories/table.md](categories/table.md) | 表格组件分类清单：DataTable / Table 等 |
| [categories/form.md](categories/form.md) | 表单组件分类清单：Form / Field / Check … |
| [categories/grid.md](categories/grid.md) | 栅格 / 容器组件分类清单 |
| [categories/misc.md](categories/misc.md) | 杂项 / 工具组件分类清单：PdfViewer / Clipboard / Timer … |
| [templates.md](templates.md) | **页面模板**：`page` / `authPage` / `lockScreen` / `errorPage` / `comingSoon` / `maintenance` / `emptyState` / `landing` / `profilePage` 用法 |
| [layout.md](layout.md) | 布局系统：Page / Sidenav / Topbar / PageTitle 等整页骨架与定制 |
| [forms.md](forms.md) | 表单：Form / Field / 各类输入控件、校验、提交与 AJAX |
| [tables.md](tables.md) | 数据表格 DataTable：列渲染器（`user`/`avatar`/`badge`/`qr` 等）、服务端分页、批量操作、行明细、导出 |
| [charts.md](charts.md) | 图表与地图：ApexCharts / ECharts / Leaflet 等封装组件 |
| [pages.md](pages.md) | 模板映射：INSPINIA 页面族 → 覆盖组件对照表 |
| [assets.md](assets.md) | 资源管理：插件注册、按需加载、CSS/JS 资源清单 |
| [extending.md](extending.md) | 扩展开发：如何新增组件、渲染器、JS 绑定 |
| [i18n.md](i18n.md) | 多语言：翻译文件结构与前端 `XFAdmin.t()` 用法 |
| [security.md](security.md) | 安全：CSRF、XSS 防护、资源加载策略 |
| [thinkphp.md](thinkphp.md) | ThinkPHP 集成说明（兼容场景） |
| [STYLE_ALIGNMENT.md](STYLE_ALIGNMENT.md) | 样式对齐：与 INSPINIA 框架类冲突的处理约定 |
| [CHANGELOG.md](CHANGELOG.md) | 更新日志 |

## 组件参考如何生成 / 同步

`components-reference.md` 由 `tools/gen_docs.php` 扫描 `src/XfAdmin.php` 的组件注册表并反射每个组件的 `defaults()` / `assets()` / 公开方法自动生成：

```bash
php tools/gen_docs.php
```

新增或调整组件后重新运行即可让文档与代码保持同步，无需手工维护。

## 文档约定

- 调用统一形式：`XfAdmin::<alias>(array $options)`。所有组件支持通用键 `id` / `class` / `attributes`。
- 资源前缀统一为 `zxf/xfadmin`，无需发布即可在 `demo/` 中直接加载。
- 每个组件的「默认值（源码）」均来自其真实 `defaults()` 方法，确保示例与代码一致。

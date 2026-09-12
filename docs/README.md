# XfAdmin 文档中心

> **XfAdmin** —— 基于 INSPINIA v4.1.0 拆分的 PHP 组件化后台 UI 扩展包
> **226 个组件别名 / 215 个组件类** · 纯原生 JS（不依赖 Node 构建）· 离线可用 ·
> 支持 **Laravel 11/12** 与 **ThinkPHP 8+** · 要求 **PHP ≥ 8.2** · 当前版本 `2.1.0`

`XfAdmin` 把后台模板的布局、导航、栅格、UI 基础、表单、图表地图、表格、业务组件与杂项
全部拆分为可独立调用的 PHP 类。每个组件 `XfAdmin::card([...])` 即渲染一段语义化、可嵌套、
**资源自管理** 的 HTML，让你在任意 PHP 项目中"一行代码"拼出完整后台界面。

```php
echo XfAdmin::page([
    'title'   => '仪表盘',
    'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
]);
```

---

## 🔍 不知道用什么组件？

先查 **[快速查找索引（INDEX.md）](INDEX.md)** —— 提供决策树、按场景速查、
按关键词速查、模板速查四种检索方式，30 秒定位所需组件或模板。

| 检索方式 | 入口 |
|---|---|
| 按场景（"我要做列表页/表单/看板…"） | [INDEX.md §2](INDEX.md#2-按场景速查) |
| 按关键词（"上传/图表/权限…"） | [INDEX.md §3](INDEX.md#3-按关键词速查) |
| 按分类（226 组件分类浏览） | [03-组件参考/00-总览.md](03-组件参考/00-总览.md) |
| 按字母（A-Z 全表，Ctrl+F） | [06-附录/01-component-index.md](06-附录/01-component-index.md) |
| 按模板（整页配方） | [03-组件参考/12-页面模板.md](03-组件参考/12-页面模板.md) |

---

## 📖 文档地图

### 一、快速开始

| 文档 | 内容 |
|---|---|
| [01-安装与接入](01-快速开始/01-installation.md) | 环境要求、Composer 安装、Laravel / ThinkPHP / 原生 PHP 三种接入方式、静态资源发布与自托管 |
| [02-五分钟上手](01-快速开始/02-quickstart.md) | 第一个页面、组件嵌套、数据表格、表单、图表的最小示例与演示环境运行 |
| [03-核心概念](01-快速开始/03-concepts.md) | 组件、配置合并规则、渲染生命周期、资源去重、转义策略五大概念 |
| [04-全局配置](01-快速开始/04-configuration.md) | `config/xfadmin.php` 全部配置项详解（资源、主题、品牌、页脚、CSRF） |

### 二、核心架构

| 文档 | 内容 |
|---|---|
| [01-架构总览](02-核心架构/01-architecture.md) | 目录结构、类关系、渲染管线、前后端协作机制 |
| [02-组件基类](02-核心架构/02-component-base.md) | `Component` 抽象类全部方法、安全助手、枚举白名单、槽位语义 |
| [03-资源与插件](02-核心架构/03-assets.md) | `Assets` 管理器、`PLUGINS` 全表、去重算法、head/scripts 输出、自定义插件注册 |
| [04-安全与转义](02-核心架构/04-security.md) | XSS 防御体系、转义分级、CSS 值白名单、URL 协议白名单、审计工具 |
| [05-服务端数据协议](02-核心架构/05-data-protocol.md) | `DataSet` 请求/响应、压缩协议 `xfc/xfo/xfs`、`filters` 与 `op` 全表 |
| [06-门面与助手](02-核心架构/06-helpers.md) | `XfAdmin` 静态 API 全表、全局助手函数、Facade、Blade 指令 |
| [07-扩展机制](02-核心架构/07-extending.md) | 自定义组件、覆盖内置组件、自定义单元格渲染器、自定义 JS widget |

### 三、组件参考（226 个，100% 覆盖）

| 文档 | 组件数 | 内容 |
|---|---|---|
| [00-组件总览](03-组件参考/00-总览.md) | 226 | 全组件速查表与命名约定 |
| [01-布局·导航·栅格](03-组件参考/01-layout.md) | 27 | page / sidenav / topbar / topNav / authPage / errorPage / menu / row / col |
| [02-UI 基础组件](03-组件参考/02-ui.md) | 55 | card / button / modal / tabs / avatar / timeline / stepper … |
| [03-表单组件](03-组件参考/03-form.md) | 23 | form / input / select / editor / upload / wizard / captcha … |
| [04-表格组件](03-组件参考/04-table.md) | 4 | table / dataTable / tablesCustom / dataTableToolbar |
| [05-图表与地图](03-组件参考/05-chart.md) | 7 | apexChart / apexTree / apexSankey / echart / vectorMap / leafletMap / googleMap |
| [06-电商·商品·订单·发票](03-组件参考/06-data-ecommerce.md) | 31 | productsGrid / cartSummary / checkout / orders / invoice* … |
| [07-内容·社区·文件](03-组件参考/07-data-content.md) | 17 | article / blogList / commentThread / gallery / fileManager … |
| [08-用户·组织·项目协作](03-组件参考/08-data-people.md) | 23 | clients / roles / permissionMatrix / projects / kanban … |
| [09-沟通·邮件](03-组件参考/09-data-comm.md) | 8 | chatApp / chatBox / emailApp / mailList / outlook … |
| [10-仪表盘·模块·通用](03-组件参考/10-data-dashboard.md) | 13 | dashboardGrid / widget / metricCard / settingsCenter … |
| [11-杂项与交互增强](03-组件参考/11-misc.md) | 18 | calendar / treeView / lightbox / tour / pdfViewer / masonry … |
| [12-页面模板](03-组件参考/12-页面模板.md) | — | 整页级组件清单 + 7 个页面配方 + 演示站 11 个模板 |

> 组件参数表由 `php tools/gen_component_docs.php` 从源码反射自动生成，
> 保证与代码 100% 同步（参数、默认值、依赖插件、用法示例均取自源码注释）。

### 四、进阶指南

| 文档 | 内容 |
|---|---|
| [01-页面与布局](04-进阶指南/01-page-layout.md) | Page 深度用法、主题 data-* 属性、定制面板、多布局切换 |
| [02-导航系统](04-进阶指南/02-navigation.md) | 侧边栏多级菜单、水平导航、Mega Menu、菜单激活与权限 |
| [03-数据表格全指南](04-进阶指南/03-datatable.md) | 列定义、52 种单元格渲染器、行操作、过滤栏、批量、导出、服务端对接 |
| [04-表单全指南](04-进阶指南/04-forms.md) | 字段封装、校验、AJAX 提交、文件上传、向导、验证码、CSRF |
| [05-图表全指南](04-进阶指南/05-charts.md) | ApexCharts / ECharts / 地图配置与主题联动 |
| [06-前端 JS API](04-进阶指南/06-javascript-api.md) | `XFAdmin` 全局对象全部方法、data-* 契约、事件全表 |
| [07-样式与主题](04-进阶指南/07-css-theming.md) | CSS 变量、命名规范、暗色模式、自定义皮肤 |
| [08-认证与错误页](04-进阶指南/08-auth-pages.md) | 9 种认证页 × 3 种布局、错误页、维护页、锁屏 |
| [09-模板页面映射](04-进阶指南/09-templates-pages.md) | INSPINIA 220+ 页面与本包组件的对应关系 |
| [10-Laravel 集成](04-进阶指南/10-laravel.md) | 服务提供者、Facade、Blade 指令、资源路由 |
| [11-ThinkPHP 集成](04-进阶指南/11-thinkphp.md) | 服务注册、助手函数、发布命令 |
| [12-常见任务手册 Cookbook](04-进阶指南/12-cookbook.md) | 22 个高频任务（分页/过滤/行内编辑/批量/导入导出/弹窗/上传…）的完整方案 |
| [13-单元格渲染器手册](04-进阶指南/13-cell-renderers.md) | 56 个渲染器逐个示例：列配置、cfg 参数、事件与自定义 |

### 五、运维

| 文档 | 内容 |
|---|---|
| [01-测试体系](05-运维/01-testing.md) | 包内测试、运行时自检、Playwright 视觉回归 |
| [02-部署](05-运维/02-deploy.md) | 资源发布、CDN、缓存与版本刷新 |
| [03-性能优化](05-运维/03-performance.md) | 资源加载、大数据量渲染、缓存策略 |
| [04-问题排查](05-运维/04-troubleshooting.md) | 常见错误与全量陷阱清单 |

### 六、附录

| 文档 | 内容 |
|---|---|
| [01-组件索引](06-附录/01-component-index.md) | 226 个别名 → 类 → 分类 → 文档锚点速查 |
| [02-参数字典](06-附录/02-option-index.md) | 跨组件高频参数统一说明 |
| [03-插件资源索引](06-附录/03-plugin-index.md) | 内置 40+ 第三方插件清单与引用名 |
| [04-常见问题](06-附录/04-faq.md) | FAQ |
| [05-升级指南](06-附录/05-upgrade.md) | 版本升级注意事项 |
| [06-更新日志](06-附录/06-changelog.md) | CHANGELOG |
| [07-组件参考（自动生成）](06-附录/07-components-reference.md) | 单文件全组件参考（自动生成，便于检索） |

---

## 🎯 按场景快速定位

| 我想… | 去看 |
|---|---|
| 10 分钟跑起来 | [02-五分钟上手](01-快速开始/02-quickstart.md) |
| 写第一个后台页面 | [01-页面与布局](04-进阶指南/01-page-layout.md) |
| 做数据列表 + 分页 + 搜索 | [03-数据表格全指南](04-进阶指南/03-datatable.md) |
| 做增删改查表单 | [04-表单全指南](04-进阶指南/04-forms.md) |
| 画图表 | [05-图表全指南](04-进阶指南/05-charts.md) |
| 配菜单 / 多级导航 | [02-导航系统](04-进阶指南/02-navigation.md) |
| 改主题 / 换肤 / 暗色 | [07-样式与主题](04-进阶指南/07-css-theming.md) |
| 登录注册等认证页 | [08-认证与错误页](04-进阶指南/08-auth-pages.md) |
| 写自己的组件 | [07-扩展机制](02-核心架构/07-extending.md) |
| 前端二次开发 | [06-前端 JS API](04-进阶指南/06-javascript-api.md) |
| 排查"组件没样式/JS 没生效" | [04-问题排查](05-运维/04-troubleshooting.md) |

---

## 📌 文档约定

- **参数表** 中「默认值」为该组件 `defaults()` 中的实际默认值，`null` 表示不输出该特性。
- **转义语义** 三档：
  - `e()` / `text()` —— **纯文本**槽位，输出前 HTML 转义；
  - `raw()` —— **内容**槽位，原样输出 HTML / 组件 / 闭包 / 数组；
  - `enum()` / `cssLen()` 等 —— **受控**槽位，走白名单或格式校验。
- 示例代码默认使用 `use zxf\XfAdmin\XfAdmin;`（Laravel 中可直接用 `\XfAdmin` 门面）。
- 标记 ⚠️ 的段落为**易踩坑点**，务必阅读。

## 🔧 文档维护

组件参数文档由源码生成，修改组件后请重新生成：

```bash
php tools/gen_component_docs.php     # 重新生成 03-组件参考/*.md + 06-附录/01~03 索引
php tools/gen_component_matrix.php   # 导出组件矩阵 JSON（参数统计用）
php tools/audit_docs_coverage.php    # 文档覆盖审计：断言每个别名都有条目 + 参数表 + 示例
php tools/check_docs_links.php       # 文档内部链接检查（667 个链接全有效）
```

生成器为每个组件产出四段内容：

| 段落 | 来源 |
|---|---|
| 用法示例 | 类 docblock 中的 `XfAdmin::xxx([...])`；无则用手写补充示例（`$USAGE_EXAMPLES`） |
| 全参数示例 | 由 `defaults()` 生成，列出全部参数与默认值（可复制运行） |
| 数据结构 | 从源码 `foreach` / 私有渲染方法解析出数组元素可用键；无则用手写补充（`$STRUCT_NOTES`） |
| 渲染骨架 | 从 `html()` 提取组件主要 DOM class |
| 参数表 | `defaults()` 注释 → 参数词典 → **源码语义**（枚举白名单 / 槽位 / 安全校验 / 开关 / 真实用法） |

覆盖审计输出（当前基线）：

```
注册别名总数: 226
文档章节总数: 226
无文档条目: 0
无参数表: 0
无示例: 0
唯一组件类: 215
```

生成内容**均取自源码事实**：

| 部分 | 来源 |
|---|---|
| 组件说明 | `src/XfAdmin.php` 的 `@method` 中文注释（回退类 docblock） |
| 用法示例 | 类 docblock 中的 `XfAdmin::xxx([...])` 代码块 |
| 参数表 | `defaults()` 的键、默认值、类型、行尾注释（缺失时查 `PARAM_DICT` 词典） |
| 依赖插件 | `assets()` + 源码中出现的 `PLUGINS` 键 |

各分类文档的**「本章导读」**（组合范式 + 约定与陷阱）写在生成器的 `$GROUPS[...]['intro']` 中，
随生成一起输出 —— 修改导读请编辑 `tools/gen_component_docs.php` 后重新生成。

## 🧹 目录说明

- `docs/` 下仅保留本套文档体系（6 个编号目录 + `README.md` + `INDEX.md`），
  旧版零散文档与自检产物已清理；
- `06-附录/06-changelog.md`（更新日志）与 `06-附录/07-components-reference.md`
  （单文件全组件检索表）由旧文档提升保留；
- `tools/selftest/.build/` 为浏览器自检产物，已加入 `.gitignore`，
  运行 `bash tools/selftest/run.sh` 会重新生成。

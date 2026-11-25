# XfAdmin 扩展包 · AI 上下文索引（唯一入口 / 必读）

> **本仓库是 `zxf/xfadmin` 扩展包源码本身**（不是使用它的业务项目）。
> 任何 AI（Claude Code / WorkBuddy / Cursor / Copilot）进入本包开始工作前，按以下顺序执行：
>
> 1. 读本文件（已含包定位、真实统计、目录地图、硬性禁令、路由表，**不要再全仓遍历**）
> 2. 按「规则路由表」只加载与本次改动匹配的规则文件，不相关的不读
> 3. 定位代码按「检索协议」执行，命中即停
> 4. 改完 PHP 必须跑格式化与包内测试（见 [testing.md](testing.md)）
>
> 本目录是本包的**权威 AI 规范**：业务用法请见包内 `docs/`，本目录侧重「包自身如何维护/扩展/验证」。
> 规则与源码冲突时**以源码为准**，并回来修正规则（规则过期比没有规则更危险）。

---

## 一、包定位与真实统计（源码实测，勿凭记忆）

| 项 | 值 |
| --- | --- |
| 包名 | `zxf/xfadmin` |
| 定位 | 基于 INSPINIA v4.1.0 的 PHP 管理后台**组件化扩展包**（框架无关，Laravel/ThinkPHP 仅做服务注册） |
| 版本 | **1.0.4**（`src/XfAdmin.php::VERSION` + `config/xfadmin.php` 两处；**composer.json 不写 version**） |
| 组件类 | **225 个类文件**（`src/Components/**.php` 227 个 − `Component.php` 基类 − `Form/Concerns/FieldWrapper.php` trait） |
| 注册组件类 | **225 个唯一类引用**（注册表 237 个别名，其中 5 类有多别名：TopNav/AuthPage/DiyLayoutPage/DateRangePicker/ClipboardButton） |
| 注册别名 | **237 个**（`XfAdmin::$components`，含大小写/语义别名） |
| JS widget | **58 个**（`XFAdmin.register(...)`，`resources/assets/js/xfadmin.js`） |
| 全局函数 | 4 个（`xf_admin` / `xf_asset` / `xf_head` / `xf_scripts`，`src/helpers.php`） |
| PHP 依赖 | `php >= 8.2`、`ext-json`、`ext-mbstring`（渲染核心零框架依赖） |
| 建议框架 | `laravel/framework ^11/^12/^13`、`topthink/framework ^8` |
| 命名空间 | `zxf\XfAdmin\` → `src/`（PSR-4） |
| 测试 | `tests/{smoke,regression,audit}.php` + `tests/run-all.sh`（零外部依赖，离线可跑） |

> ⚠️ 统计口径：`README.md` / `composer.json` 描述里曾写「222 个组件 / 228+/231 个别名」，与源码实测
> （225 类 / 237 别名）不一致。改动组件数量相关内容时以本节为准，并同步修正描述文档。

---

## 二、组件分类地图（`src/Components/`）

| 分类 | 类文件数 | 代表组件 |
| --- | --- | --- |
| `Layout/` | 18 | `page` `authPage` `sidenav` `topbar` `footer` `adminBehaviors` `adminThemeHead` |
| `Navigation/` | 1 | `menu`（导航 DSL，单类多形态） |
| `Grid/` | 2 | `row` `col` |
| `UI/` | 62 | `card` `alert` `modal` `tabs` `button` `statCard` `dataTable` 之外的全部 UI 元素 + `rowActions` `toolbar` |
| `Form/` | 22(+trait) | `form` `input` `select` `upload` `editor` `wizard` `captcha` |
| `Table/` | 4 | `table` `dataTable` `tablesCustom` `dataTableToolbar` |
| `Chart/` | 7 | `apexChart` `echart` `vectorMap` `leafletMap` `apexTree` `apexSankey` |
| `Data/` | 92 | `widget` `metricCard` `kanban` `invoiceTable` 等数据填充型业务组件 |
| `Misc/` | 17 | `calendar` `treeView` `lightbox` `i18n` `raw` 等 |

---

## 三、规则路由表（按改动路径加载，避免无关上下文）

| 本次改动落在 | 必读规则 |
| --- | --- |
| 任意首次任务 / 不确定从哪下手 | `index.md`（本文件）→ `workflow.md` |
| 接入 AI / 规则体系本身 | `ai.md` |
| `src/Components/**` 新增/改组件、`XfAdmin.php` 注册表 | `components.md` + `architecture.md` + `conventions.md` |
| `src/Components/Table/DataTable.php`、渲染器、服务端协议 | `datatable.md`（叠加 `components.md`） |
| `resources/assets/js/**`、`src/Assets/**`、CSS 主题、widget、事件 | `frontend.md` |
| `config/xfadmin.php`、`src/Laravel/**`、`src/ThinkPHP/**`、Facade、Provider | `config.md` |
| 安全 / 转义 / XSS / raw 槽位 / 审计 | `security.md` |
| `tests/**`、`tools/**`、跑验证 | `testing.md` |
| `docs/**`、`README.md`、版本号 | `conventions.md`（§文档与版本） |

---

## 四、硬性禁令（违反即返工，无需再问）

1. **不改他人产物**：`vendor/`（本包自带依赖）、`tools/selftest/.build/`（生成物）、`composer.lock` 非必要不动。
2. **组件即源码**：组件 HTML 由 PHP `html()` 拼装，**无独立模板文件**；改逻辑改组件类，扩展用 `XfAdmin::extend()`，不要幻想改 `.blade` 模板。
3. **注释中文**：所有 PHP 注释/文档块统一**简体中文**（行内、方法、类、文件头）；PHPDoc 优先。
4. **命名语义化**：禁止单字母变量/方法/参数/循环变量（`$i`/`$e`/`$d` 不合格）。
5. **PHP 兼容**：以 8.2 为语法基线，不得用 8.3+ 独占语法（typed class constants、`json_validate()` 需回退）。
6. **转义**：用户可控数据一律走组件字符串参数（自动 `escape()`）；raw 槽位（`body`/`content`/`footer`/`actions`/`tools`/`menu`）塞用户数据 = 存储型 XSS。
7. **版本两处同步**：`XfAdmin::VERSION` + `config/xfadmin.php` 的 `version`（**composer.json 明确不写 version**）；改版本须两处同时改，否则 `?v=` 缓存刷新错乱。
8. **资源顺序**：`scripts()` 必须在页面所有组件 `render()` 之后调用（组件在 render 时才登记依赖）；整页 `page()` 已内置。
9. **格式化**：改完 PHP 必跑项目侧 `vendor/bin/pint --dirty --format agent`（本包无 pint 时需在项目根跑）。
10. **测试**：改动后必须 `bash tests/run-all.sh` 全绿；已有测试文件不得为「让测试通过」而改写断言。
11. **交付物**：新增/改动 `.js`/`.css` 需同时产出 `.min.js`/`.min.css`。
12. **插件名非路径**：组件 `assets()` 只返回 `Assets::PLUGINS` 的键名（如 `datatables`/`jquery`），不要写具体文件路径。

---

## 五、检索协议（把「全局遍历」换成「定点查询」）

按代价从低到高，命中即停：

1. **查组件**：`rg -n "'alias'" src/XfAdmin.php`（注册表）→ 直接拿到类名与文件。
2. **查类方法**：`rg -n "function xxx" src/`（**必须带路径限定**，禁止 `grep -r xxx .` 扫仓库根）。
3. **查 JS widget**：`rg -n "XFAdmin.register\('name'" resources/assets/js/xfadmin.js`。
4. **查配置项**：直接读 `config/xfadmin.php`（键名集中）。
5. **查统计/一致性**：用脚本解析注册表与 `find`（见 [workflow.md](workflow.md) 的统计命令），不要凭 README 的数字。
6. **读大文件**：先读 1–80 行拿类头/方法清单，再按方法名定位局部；`xfadmin.js` 约 7000 行，务必分段读。

**预算**：单次任务定位阶段 ≤ 6 次检索。超出后先输出「已定位到哪里 / 还缺什么」再决定。

---

## 六、常用命令

```bash
# 包内全量验证（推荐入口：语法+audit+smoke+regression）
bash tests/run-all.sh

# 单项
php tests/audit.php          # 静态审计（资源/注入/健壮性/前端契约）
php tests/smoke.php          # 冒烟
php tests/regression.php     # 回归

# PHP 语法（全 src）
find src -name '*.php' -print0 | xargs -0 -n1 php -l

# JS 语法
node --check resources/assets/js/xfadmin.js

# 文档工具（在包根）
php tools/check_docs_links.php        # 文档链接完整性
php tools/audit_docs_coverage.php     # 文档覆盖率
php tools/gen_component_docs.php      # 生成组件文档
php tools/gen_component_matrix.php    # 生成组件矩阵

# 版本/统计核对
grep -n "VERSION\|'version'" src/XfAdmin.php config/xfadmin.php
grep -c "XFAdmin.register(" resources/assets/js/xfadmin.js
```

---

## 七、维护约定

- 新增组件类 → 在 `XfAdmin::$components` 注册别名 + 写 `@method` 注解 + 补 `docs/` + 更新本目录统计数字。
- 新增 JS widget → 在 `xfadmin.js` `XFAdmin.register()` + PHP 侧提供声明节点组件（如 `rowActions`）+ 补测试。
- 新增规则文件 → 必须在本文件「规则路由表」登记，并在 `ai.md` 文件清单补齐。
- 组件数量/别名数量变化 → 同步修正 `index.md` 统计、`README.md`、`composer.json` 描述、`docs/`。
- 规则与代码冲突 → 以代码为准，回来修规则。

# 更新日志

本文档记录 XfAdmin 的版本迭代。版本号遵循语义化版本（MAJOR.MINOR.PATCH）。

---

## 本轮：Modal 修复 + 侧边栏修复 + 全面审计（2026-08-07）

### Modal 弹窗头部工具定位修复
- **问题**：`.xf-modal-header-tools` 未显示在弹窗右上角
- **修复**：追加 `margin-left: auto; flex-shrink: 0` 推至右侧

### 侧边栏溢出白底修复
- **问题**：菜单超屏时 SimpleBar 内容容器未继承暗色背景
- **修复**：`.sidenav-menu [data-simplebar]` / `.simplebar-content-wrapper` / `.simplebar-content` 统一 `background-color: inherit`

### CSS 审计修复
- `.xf-stepper-dot` / `.card-action` 重复定义合并

### DataTable show_detail 独立列
- dt-control 箭头列独立为辅助列，不占用业务列数据

### 文档刷新
- 运行 gen_docs.php / gen_category_docs.php，覆盖全部 155 组件

---

## 上一轮：yoc_cn 生产环境对齐 + 全组件覆盖 + 穿透修复 + 文档翻新（2026-08-06）

### yoc_cn 生产环境 CSS 修复吸收

- **DataTable 选中行样式**：改用 DataTables 原生 `--dt-row-selected` / `--dt-row-selected-text` CSS 变量（替代硬编码 ins-warning-rgb），与 Select 扩展一致，固定列选中行同步覆盖。
- **确认已吸收的 yoc_cn 补丁**（全量对比结果）：
  - `--ins-light-rgb` 亮/暗双模式覆盖 ✅
  - `dtfc-fixed-start/end` 暗色背景 ✅
  - `btn-xs` / `fix-md-box` / `z-999~z-99999` 工具类 ✅
  - `pt-0`~`pt-5` 间距覆盖 ✅
  - 双表头、固定列穿透、响应式溢出等均已就绪 ✅

### 固定列穿透修复（CSS 层叠上下文）

- **根因**：`position:sticky` 的 `<td>` 与非 sticky `<td>` 在同一 `<tr>` 内按 DOM 顺序渲染，非 sticky 元素无 `position` 上下文可绘制于 sticky 元素之上。
- **修复 ①**：`div.dt-scroll-body :not(.xf-dt-sticky)` → `position:relative; z-index:0` 强制入栈
- **修复 ②**：`div.dt-scroll-body` → `isolation:isolate` 独立层叠上下文
- **修复 ③**：`.xf-dt-sticky` 改用显式 `background-color`（替代 `background` 简写），防继承意外
- **修复 ④**：暗色模式全覆盖（固定列 + 非固定列 + 隐藏表头）

### 固定列 sticky 重算修复（JS）

- **修复 ①**：监听 `init.dt`（首次数据加载完触发）替代不存在的事件 `columns-adjusted.dt`
- **修复 ②**：新增 `ResizeObserver` 监听容器尺寸变化（侧边栏折叠 / 窗口 resize 时自动重算 sticky 偏移）
- **修复 ③**：超时 30ms → 50ms 确保 DOM 沉降

### 清理与优化

- 删除全部 `tools/**/*.mjs` 测试脚本（30 个）
- 删除 `tools/selftest/.build/` 编译产物
- 删除全部 `.map` 与 `.log` 文件

### 文档翻新

- **刷新全部 9 个分类文档**（`docs/categories/*.md`）：组件别名 / 说明 / 依赖资源 / 详细文档链接
- **刷新组件详细参考**（`docs/components-reference.md`）：154 类 / 156 别名，含 defaults() 选项键 + 默认值 + 注释说明 + 链式 setter + 依赖资源
- **刷新模板文档**（`docs/templates.md`）：10 个页面模板
- **README**：组件数 136 → 156，分类表重写（布局 14 / 导航 1 / 栅格 2 / UI 46 / 表单 14 / 图表 7 / 表格 2 / 数据 53 / 杂项 15）

### wsf Admin 演示模块全覆盖

- **DemoCatalog** 新增 20 个缺失组件：`apexTree`, `apexSankey`, `googleMap`, `avatarGroup`, `backToTop`, `callout`, `countdown`, `countUp`, `divider`, `kbd`, `media`, `skeleton`, `switch`, `codeBlock`, `empty`, `toolbar`, `searchBox`, `metricCard`, `terms`, `animate`
- **组件查询**：`/admin/demo/component/{alias}` 已覆盖所有 156 组件
- **组件全景**：`/admin/demo/showcase` 展示全部 156 组件（按分类聚合 + 总计数）
- **同步**：xfadmin 资源与源码已同步至 wsf vendor

---

## 本轮：ApexCharts 响应式修复（2026-08-05）

- **问题**：用户报告"调整浏览器宽度时后台顶部.topbar-menu和很多组件错乱、宽度异常"，Playwright 测试发现 `/charts` 页面在调整宽度时出现瞬态横向溢出（最大 107px @ 320px）
- **根因**：ApexCharts 缺少 resize 事件监听（ECharts 已有），调整浏览器宽度时图表未调用 `chart.resize()` 适应新容器宽度；图表宽度配置缺失（默认 null，未强制 100%）；容器约束不足（无 `max-width: 100%` 限制）
- **修复 1**：`src/Components/Chart/ApexChart.php` 默认宽度设为 `'100%'`，确保图表始终适应容器
- **修复 2**：`resources/assets/js/xfadmin.js` 添加 resize 事件监听 + 防抖（150ms），在调整浏览器宽度时自动调用 `chart.resize()`
- **修复 3**：`resources/assets/css/xfadmin.css` 添加图表容器约束（`max-width: 100%` + `overflow: hidden`），防止图表 SVG 超出容器
- **验证**：Playwright 压力测试（静态加载 8 断点 + 调整序列 16 次 + 快速调整 20 次 + JS 错误检查）**全部通过**，所有宽度横向溢出 0px（修复前最大 107px），无 JS 错误
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.8 节新增本轮说明
- **同步**：已同步到 `/Users/aha/www/wsf/vendor/zxf/xfadmin/`

## 本轮：响应式布局错乱修复 + DataTable 排序样式美化

- **顶栏不跟随侧栏（响应式错乱根因 A）**：xfadmin.css 原 `.app-topbar { margin: 0 }` 固定清零，JS 未接管时顶栏不随 `--ins-sidenav-width`（235/70/0）收缩而错位。改为 `.app-topbar { margin-left: var(--ins-sidenav-width) }` + `@media (max-width:991.98px){margin-left:0}` 兜底：JS 接管时 inline 优先仍正常，JS 不接管时 CSS 变量跟随侧栏。
- **landing 页窄屏 5px 水平溢出（根因 B）**：landing 下 `.container` padding 清零后 Bootstrap `.row` 的 `-15px` gutter 失去抵消外溢。`.landing-page .content-page { overflow-x: clip }`（clip 不破坏 sticky 顶栏）+ body 兜底，实测 375/576/768/992/1280 各档溢出均为 0。
- **全页响应式审计（Playwright，1920→375）**：`/tables` `/charts` `/widgets` `/apps` `/landing` 五页五档视口 `horizOverflow` 全为 0，无组件越界。
- **DataTable 排序样式美化（重大根因）**：xfadmin.css 全部 DataTable 样式作用域为 `.xf-datatable.dataTable`，但组件 `tableClass()` 从未输出 `xf-datatable` 类 → 表头底纹/排序图标/圆角/已排序列高亮**全部未生效**。修复：`DataTable.php` 的 `tableClass()` 新增 `xf-datatable` 标识类；`.dt-column-order` 统一 1.25em + `border-radius:.3rem` + hover 淡背景；已排序列 `th` 加主色浅底 + 标题 `font-weight:600`。
- **验证（Playwright `/tables`）**：可排序列 `orderDisplay:flex`、图标升序 `\eb26`/降序 `\eb27` 正确切换、点击 `dt-ordering-asc→desc` 状态正常、标题主色 `rgb(94,108,193)` 高亮、暗色无 JS 错误。
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.7 节新增本轮说明。

## 本轮：侧边栏菜单一致性 + DataTable 排序验证（用户测试发现专项）

- **侧边栏 active 高亮跟随模板**：删除 xfadmin 强加的 `border-left:3px 主色` + `background:主色 subtle !important` + `color:主色 !important` + `font-weight:600 !important`，交还模板 `.side-nav-item.active>a`（由 `--ins-sidenav-item-active-bg`/`--ins-sidenav-item-active-color` 驱动，暗色自动适配）。实测 active 颜色 rgb(207,223,241)/背景 rgb(41,68,97)/无左边框，与后台模板一致。
- **侧边栏 hover 跟随模板**：删除暗色段强加的 `.side-nav-link:hover { background: rgba(255,255,255,.06) }`，模板 hover 仅变色不加深色背景。
- **子菜单箭头旋转对齐模板**：`rotate(90deg)` → `rotate(-180deg)`（与模板 `.side-nav-item.active .menu-arrow` 一致）。
- **交互验证（Playwright 实测）**：`[data-bs-toggle=collapse]` 点击 `display:none→block`（Bootstrap Collapse 正常），箭头 `matrix(-1,0,0,-1,0,0)`；DataTable 排序 `aria-sort` 点击 `ascending→descending`，`.dt-column-order::before` 三态图标（`\ueb27` 降序）正确显示、首行数据随排序切换；全程无 JS 错误。
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.4 节新增侧边栏专项与 DataTable 验证记录。

## 本轮：DataTable 2.x 工具栏/分页布局选择器失效修复

- **根因**：项目 DataTables 为 **2.x**，DOM 结构为 `.dt-container > .dt-layout-row > .dt-layout-cell`（含 `.dt-search`/`.dt-length`/`.dt-paging`/`.dt-info`/`.dt-empty`）。旧 xfadmin.css 沿用 1.x 的 `.dataTables_wrapper .dataTables_*` 选择器，对 2.x **完全失效**，导致搜索框、长度菜单、分页器、信息栏、空数据态均无自定义样式（暗色适配缺失），与模板「工具栏随主题适配」预期不一致。
- **修复**（`resources/assets/css/xfadmin.css`）：将失效的 `.dataTables_*` 选择器具名重写为 2.x 的 `.dt-search`/`.dt-length`/`.dt-paging`/`.dt-info`（保留 `.dataTables_*` 作 1.x 兜底）；空数据态补 `.dt-empty`；`.dt-search input` 设 `min-width:16rem`、`.dt-length`/`.dt-info` 补 `margin-bottom` 留白；暗色下复用 Bootstrap 语义变量自动适配。
- **验证（Playwright 实测，浅/暗双主题，无 JS 错误）**：`.dt-search input` 计算宽度 152.375px（min-width 生效）；`.dt-info` 颜色浅色 rgb(76,76,92)/暗色 rgb(170,184,197)（随 `--bs-secondary-color` 自动切换）；容器实测类名 `dt-container dt-bootstrap5 dt-empty-footer`。
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.5 节新增本轮修复说明。

## 本轮：图表组件暗色主题热切换修复

- **根因**：`xfadmin.js` 的 `apexchart`/`echart` 注册函数不读取 `data-bs-theme`——ApexCharts `theme.mode` 恒为 `light`、ECharts `theme` 在 `init` 时固定，切暗色后图表文字/网格/背景与模板脱节；ApexTree/ApexSankey 仅初始化时读了一次主题，不热切换。
- **修复**（`resources/assets/js/xfadmin.js`）：新增 `xfCurrentThemeMode()` + 全局 `MutationObserver` 监听 `<html data-bs-theme>`，触发 `window.__xfApplyChartTheme(mode)`；Apex 注入 `theme.mode` 并热切换 `updateOptions`；ECharts 暗色走 `dispose+init('dark')+setOption` 重建；ApexTree/ApexSankey 登记 `rebuild()` 随主题重渲染。
- **验证（Playwright 实测 `/charts`，浅→暗切换，无 JS 错误）**：Apex 文字 `fill` 由 `rgb(155,166,183)` 变 `rgb(131,145,162)`；ECharts 中心像素由透明 `0,0,0` 变暗色 `16,12,42`；ApexTree/ApexSankey 重渲染正常。
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.6 节新增本轮说明。

## 本轮：模板样式一致性收敛（勿覆盖模板已定义类专项）

- **静态审计方法**：自写脚本逐条比对 `xfadmin.css` 与模板 `app.min.css` 的同名选择器 + 高危属性（position/width/margin*/padding*/top/right/z-index/background*/border*/box-shadow/color/font-weight 等），自动列出 xfadmin 覆盖模板且取值不同的冲突点，逐一定性修复。
- **致命回归修复**：`.app-topbar` 补回 `margin: 0`（模板带 `margin-left: var(--ins-sidenav-width)=235px`，fixed 顶栏会被右推、宽度压窄——此前修过但被回退）。
- **布局对齐模板**：`.content-page` 左右内边距由 `0 1.5rem` 改为 `0 .625rem`（跟随模板横向留白，仅补底部呼吸位）；`margin-left` 侧边栏位仍由模板响应式规则提供，不覆盖。
- **取消对模板已定义类的阴影/边框覆盖**：`.card` 仅保留 `transition`（删除硬编码 `box-shadow: var(--xf-shadow-soft)`）；`.modal-content`/`.dropdown-menu`/`.popover` 删除 `box-shadow !important`，交还模板 `--ins-box-shadow` 主题变量（暗色/定制面板切换不再失效）。仅 `.xf-toast-container .toast`（包内自定义）保留阴影。
- **表单控件跟随模板**：`.form-control:focus`/`.form-select:focus` 删除 Bootstrap 默认 `.2rem` 光晕覆盖，交还 INSPINIA `box-shadow:none` 聚焦设计；仅保留 `is-invalid`/`is-valid` 校验态阴影。`.input-group-text` 背景/文字改 `var(--bs-tertiary-bg)`/`var(--bs-body-color)`（暗色自动变深，与模板一致）；`.form-label` 字重改 `var(--ins-font-weight-semibold, 600)`。
- **表格行 hover 去重**：明亮段 `.table-hover` hover 统一为 `var(--bs-primary-bg-subtle)`（暗色自动适配），删除文件末尾冗余重复规则；暗色段同步收敛为语义变量。
- **DataTable 列排序全面修复**：禁用失效的 `th::after` 排序字形（压缩损坏），改用 `.dt-column-header` flex + `.dt-column-order::before` Tabler 三态字形（选择器/升/降），含 hover、键盘焦点、暗色适配、`sorting_1` 底色、点击区铺满。
- **主题默认值对齐模板**：`config/xfadmin.php` 的 `skin` classic→modern、`menu_color` dark→gradient、`sidenav_user` false→true；`Page.php` 改为对所有主题属性显式输出 `data-*`，与浏览器端 `config.js` 回落逻辑对齐。
- **`--ins-topbar-height` 级联修复**：模板该变量曾被手写改为 65px（官方 40px），还原 40px；`--xf-topbar-height` 改为 `var(--ins-topbar-height, 40px)` 跟随。
- **文档**：`docs/STYLE_ALIGNMENT.md` 第 4.3 节补录本轮收敛清单与审计方法、残留无害冲突；本 CHANGELOG 新增本条。
- **同步**：`src/` 与 `resources/` 已 `rsync -a --delete` 到 `/Users/aha/www/wsf/vendor/zxf/xfadmin/`；文档随 `docs/` 一并同步。

## 本轮：新增 9 个组件 + 全量运行时自测 + 分类/模板文档

- **新增 9 个常用组件**（均已通过 `php -l`、全量渲染 smoke、Playwright 交互自测）：
  - `divider` 分割线（带文字/图标/着色）
  - `skeleton` 骨架屏占位（text/circle/rect + 微光动画）
  - `callout` 强调提示框（info/success/warning/danger/primary）
  - `kbd` 键盘按键（多键以 `+` 连接）
  - `media` 媒体对象（图片/头像 + 标题 + 文本，可选整块可点击）
  - `avatarGroup` 头像组（重叠堆叠 + `+N` 溢出，复用 `avatar`）
  - `countdown` 倒计时（逐秒刷新，`data-xf="countdown"`）
  - `countUp` 数字滚动动画（进入视口触发，`IntersectionObserver`）
  - `backToTop` 返回顶部浮动按钮（滚动阈值出现）
  - 全部注册进 `src/XfAdmin.php` 的组件表，并补充 `xfadmin.css` 样式与 `xfadmin.js` 的 `XFAdmin.register` 交互绑定。
- **Bug 修复（基于全量运行时自测）**：
  - `ProductCard` / `ProfilePage` 在缺图时不再输出 `src=""` 破图（改为占位块/占位图标）。
  - `PdfViewer` 在 `url` 为空时不渲染下载链接、JS 不再向资源基址发起 404 请求。
  - **系统性修复**：`XfAdmin::img()` 与 `Component::img()` 对空路径返回透明 1×1 GIF，彻底消除所有组件「空 `src` 破图请求」这一整类问题。
- **运行时自测体系**：新增 `tools` 下的自测脚本思路——`render_all.php` 全量渲染 151 个别名零 fatal；Playwright（`/tmp/selftest.mjs` + 自研路由）对「全部组件」页与 7 个「页面模板」独立页做 **JS 错误 / 控制台错误 / 破图 / 本地 404 / 横向溢出** 全检，结果 **0 错误**；交互组件（countdown/countUp/backToTop）专项验证通过。
- **文档扩充**：新增按分类文档 `docs/categories/{ui,data,navigation,layout,charts,table,form,grid,misc}.md`（由 `tools/gen_category_docs.php` 自动生成，数据来自注册表 + docblock）与 `docs/templates.md`（9 个页面模板用法）；`components-reference.md` 同步至 **149 组件 / 151 别名**；`docs/README.md` 索引补全。

## 本轮：文档补全（全量组件详细参考）+ 代码健壮性收尾

- **文档完整性**：`docs/components-reference.md` 重写为**全量、自动生成**的组件详细参考，覆盖 `src/XfAdmin.php` 注册的全部 **140 个组件 / 142 个别名**（原文档仅 136 且大量条目为「见组件文档注释」占位）。每个组件含：分类、完整类路径、描述、依赖资源插件、全部 `defaults()` 选项（含默认值与行内说明）、公开链式方法、以及基于真实 `defaults()` 的实际调用示例；布局/业务类组件额外附 `defaults()` 源码字面量。新增 `docs/README.md` 文档中心索引。
- **文档同步机制**：新增 `tools/gen_docs.php`，扫描组件注册表并反射 `defaults()` / `assets()` / 公开方法生成参考文档；新增或调整组件后运行 `php tools/gen_docs.php` 即可让文档与代码保持同步。
- **强化**：`DataTable::assets()` 的 `qr` 检测增加 `is_array` 守卫，避免字符串简写列触发 `columnRender(string)` 类型错误（上一轮已修，本轮随文档同步确认）。
- **质量门禁**：`src/` 全量 `php -l`、 `resources/assets/js/**` 全量 `node --check` 均通过；140 个组件类 **0 个缺失描述**（docblock 覆盖完整）；`src/` 无 `TODO/FIXME/dd/var_dump/@deprecated` 残留。

## 本轮：DataTable 二维码渲染器（qr）+ 资源加载补全

- **`qr` 单元格渲染器**：基于 `qrcode-generator`（v1.4.4，MIT，已 vendored 至 `resources/assets/js/plugins/qrcode/`）。内容可为 URL / 纯文本 / 含中文的非 ASCII 内容（初始化时覆盖 `qrcode.stringToBytes` 为 UTF-8，彻底支持中文）；列配置 `'render' => ['type' => 'qr', 'text' => '{home}', 'size' => 96, 'ec' => 'M', 'color' => '#000', 'bg' => '#fff']`，`text` 省略时默认使用单元格数据。`Assets` 注册 `qrcode` 插件，DataTable 检测到 `qr` 列时自动按需注入，加载顺序位于 `xfadmin.js` 之前，渲染前库已就绪。
- 前端交互：点击二维码单元格弹窗放大（`XFAdmin.dialog`，6px 模块、白底黑点）；内容为 `http(s)` 链接且 `download !== false` 时单元格旁额外出现「打开链接」按钮。
- `demo/pages/tables.php` 新增「二维码单元格」卡片演示 URL / 中文文本 / 工号 三种二维码；`docs/tables.md` 渲染器总表与注释同步补充。
- 回归用例 39：校验 `qr` 列按需注入 `qrcode.min.js` 且列配置含 `type=qr`。

## 本轮：模板缺口收官（5 个新组件）+ DataTable 开发者自定义能力 + 弹窗父子页交互桥 + 多语言 i18n

> 逐页枚举 INSPINIA 220 个 HTML 页面比对后的缺口收官轮：metrics / misc-animation / misc-i18 /
> maps-google / charts-apextree / pages-terms-conditions / form-layouts 全部落地。

### 1. 新组件（5 个，均含中文注释与回归用例 35~38）
- `metricCard` 指标卡（metrics.html）：数值滚动计数（进入视口触发，easeOutCubic + 千分位）+ 趋势徽标（正绿升/负红降）+ ECharts 迷你图（donut/pie/bar/area/line）；支持 prefix/suffix/decimals/footer/整卡链接。
- `animate` 动画包装器（misc-animation.html）：animate.css 全集，`load/hover/click/scroll` 四种触发（scroll 用 IntersectionObserver），infinite/delay/speed/repeat 修饰；`load` 触发零 JS 依赖，非循环动画播完自动移除类可重复触发。
- `googleMap` 谷歌地图（maps-google.html）：免 API Key iframe 嵌入，place 地名搜索 / center 经纬度、zoom、roadmap/satellite、语言可配（需外网；离线用 leafletMap）。
- `apexTree` 组织架构树（charts-apextree.html）：apextree 插件（离线可用），节点头像/职位/边框色、四方向、展开收起、缩放工具栏，头像走 `img()` 统一解析，暗色主题自适应画布底色。
- `terms` 条款/协议页（pages-terms-conditions.html）：侧栏目录（sticky + scrollspy 高亮）+ 分节正文 + 更新时间 + 可选「同意」按钮；**目录关闭时不输出 scrollspy 属性**（避免 Bootstrap ScrollSpy 扫描到 `href="#!"` 抛非法选择器异常）。
- `apexSankey` 桑基图（charts-apexsankey.html，收官补遗）：apexsankey 插件 + svg.js（离线可用，Assets 自动按依赖注入 svgdotjs）；节点支持字符串简写（自动补 `title`），连线按 `value` 分配宽度并可单独着色；`order` 列排序 / `options` 原生配置透传 / 缩放工具栏 / 暗色主题字色适配。回归用例 37b。
- **DataTable 增强（单元格 + 操作）**：新增 `file` 渲染器（图标+文件名+人类可读大小+下载按钮，数据支持字符串 url 或 `{url,name,size,icon,download}`）、`avatarGroup` 渲染器（多人头像，超出折叠为 `+N`，无图回退姓名首字母）；新增 `download`（下载附件）、`print`（打印预览弹窗/行数据预览）、`share`（复制分享链接或唤起原生分享）三种行操作。同步补全 `docs/tables.md` 渲染器总表、操作类型表与完整示例。demo/pages/tables.php 新增「文件与协作」卡片。

### 2. DataTable 开发者自定义三件套（PHP + JS）
- **`page` 弹窗页单元格渲染器**：单元格文本变为链接，点击弹窗加载服务端页面；`url`/`title`/`text` 均支持 `{字段}` 行数据占位（URL 自动编码）；`size`/`frame`(iframe)/`reload`(关闭是否刷新表格，默认刷新)/`icon` 可配；复用声明式 `data-xf-page-dialog` 触发器并自动携带本表 id。
- **`js:` 自定义渲染器**：列配置 `'render' => 'js:函数名'` 直接调用开发者全局函数（支持点号路径如 `js:App.render.money`），签名与内置渲染器一致 `fn(data, row, cfg, meta)`；函数缺失或抛异常自动回退纯文本，不破坏表格。亦可继续用 `XFAdmin.cellRenderers.xxx = fn` 注册可复用渲染器。
- **filter_bar 自定义控件**：过滤项传 `['html' => '...', 'width' => 'col-3']` 注入任意 HTML，内部带 `class="xf-filter" data-filter="参数名"` 的控件自动参与查询参数采集，与 16 种内置控件混用。

### 3. 弹窗与父页面交互桥（resources/assets/js/xfadmin.js）
- `pageDialog` 增强：新增 `onClose(reloaded)` 回调；关闭时按需刷新表格（`tableEl`）或整页（`reloadPage`），并派发 `xf:dialog-closed` 事件（detail: url/reloaded）。
- 新增 `XFAdmin.dialogBridge`（弹窗子页 API，iframe 内 postMessage、非 iframe 自动降级本地）：`close()` / `closeAndReload()` / `markReload()` / `toast(msg, variant)` / `inDialog`。
- 父页全局 `message` 监听（仅识别 `__xf:'dialog'` 协议）：支持 close / reload / reload-close / toast 四种动作。

### 4. 多语言 i18n（misc-i18.html，纯前端，详见 docs/i18n.md）
- `XFAdmin.i18n`：`set(code)` 切换（fetch `data/translations/{code}.json` → 应用 `[data-lang]` → localStorage 记忆 → `xf:lang-changed` 事件）、`t(key)`、`apply(root)`、`base` 可覆盖（默认由 xfadmin.js script src 自动推导）。
- 首次翻译备份原文到 `data-xf-i18n-orig`，`set('')` 精确恢复；翻译文件缺失静默保持原文；启动自动恢复上次语言。
- Topbar 语言菜单（`data-lang-code`）自动接线，点击即切换。包内内置 7 语言 JSON（INSPINIA 原始数据）。

### 5. Form 布局（form-layouts.html）
- 新增 `layout`：`vertical`(默认) / `horizontal`(标签左置，CSS Grid 两栏，`label_width` 控制标签列宽 px，≤575px 自动回退纵向) / `inline`（等价旧 `inline => true` 写法，向后兼容）。

### 6. 其它
- `xfadmin.css` 末尾新增：指标卡悬浮抬升、条款目录左边框高亮、横向表单 Grid 布局、page 单元格指针、gmap/apextree 容器等样式段。
- demo：widgets 页新增 MetricCard×4 / i18n 演示 / Animate×3 / Terms；charts 页新增 ApexTree + GoogleMap；tables 页新增「开发者自定义」卡片（js: 渲染器 + page 弹窗单元格 + 自定义过滤控件）。
- 测试：`tests/regression.php` 新增用例 35~40，全量 PASS；Playwright 运行时自测 6 页 0 pageerror、0 破图，交互级验证（i18n 切换 / 计数动画 / page 弹窗 / dialogBridge toast / 自定义过滤控件）全 PASS。
- 文档：新增 `docs/i18n.md`；`docs/tables.md` 补「自定义过滤控件 / page 渲染器 / js: 渲染器 / dialogBridge」四节；`docs/charts.md` 补 apexTree/googleMap；`docs/components-reference.md` 补 5 组件详参；`docs/forms.md` 修正 layout 说明与实现一致。

---

## 本轮：鲁棒性 / 性能 / 一致性增强（has 大小写不敏感、DataTable 延迟渲染与滚动、空表格结构合法化、全量回归用例 29-34）

### 1. `XfAdmin::has()` 大小写不敏感（src/XfAdmin.php）
- 此前 `has()` 用 `array_key_exists`（精确匹配），而 `component()` 有大小写不敏感兜底（`XfAdmin::datatable()` 与 `XfAdmin::dataTable()` 都能命中）。两者行为不一致。
- 已让 `has()` 与 `component()` 一致：精确命中优先，否则大小写不敏感兜底。回归用例 29 覆盖三态（精确 / 小写 / 大写 / 不存在）。

### 2. DataTable 性能与布局选项（src/Components/Table/DataTable.php）
- 新增 `scroll_y`：固定高度纵向滚动区（如 `'420px'`），大表在有限视口内滚动而不撑长页面。
- 新增 `defer_render` 自动延迟渲染：客户端数据集 **行数 ≥ 100 时自动开启** `deferRender`（仅渲染可视行，显著降低首屏 DOM 构建耗时）；传 `defer_render => false` 可强制关闭（保留「全选所有行」的 DOM 行为）。小表与显式禁用均不开启。
- `scroll_x` / `fixed_columns`（左右固定列，CSS sticky 实现）此前已支持，本轮补回归用例 30 验证接线。

### 3. DataTable 空表格结构合法化（src/Components/Table/DataTable.php）
- 此前渲染的 `<table>` 仅含 `<thead>`，无 `<tbody>`，结构非法且初始化前会有布局抖动。
- 现始终输出 `<tbody></tbody>`，DataTables 在客户端/服务端填充行，HTML 始终合法。回归用例 33 验证空 columns/data 不抛异常且含 thead/tbody 容器。

### 4. 资产解析幂等性与外链透传（回归用例 34）
- 验证 `XfAdmin::asset()` 二次调用不重复拼接基址、`XfAdmin::img()` 对 `http(s)://` 与 `data:` URI 原样返回、相对路径经 `asset` 解析。

### 5. 测试
- `tests/regression.php` 新增用例 29-34（has 一致性 / scroll_y·scroll_x·fixed_columns 接线 / 大数据自动 deferRender / 小数据不开启 / 显式关闭 / 空表格渲染 / asset 幂等）。
- `smoke.php` + `regression.php` 全 PASS；跨 9 个 demo 页面的 Playwright 运行时自检：0 pageerror、0 本地破图、0 横向溢出。

---

## 本轮：DataTable 交互增强五件套（批量操作 / 行明细 / 行分组 / 状态持久化 / 一键导出）+ 多处缺陷修复

### 1. PHP：DataTable 新增 5 项配置（src/Components/Table/DataTable.php）
- `bulk`：批量操作。渲染批量操作栏（`.xf-dt-bulk`，勾选后显示已选数量），动作按钮支持 `label/icon/class/url/method/confirm/reload`，`url` 的 `{ids}` 占位自动替换为选中行 id；自动在最前注入选择列（`xf-dt-select-col`）。
- `row_detail`：行明细展开。`true` 或 `['columns'=>[...]]`，注入明细列（`xf-dt-detail-col`）；与 `responsive` 首列互斥（启用明细时自动关闭响应式）。
- `row_group`：行分组（本地数据源）。兼容字符串字段名与 `['data'=>..,'empty'=>..]` 两种写法（此前 demo 卡片 1 的 `'row_group'=>'office'` 一直未接线，本轮真正生效）。
- `state_save`：接线 DataTables 原生 `stateSave`（排序/分页/列显隐持久化 localStorage）。
- `export`：`true`（全格式）或数组指定导出按钮；含 `pdf` 时在 `assets()` 声明中自动加载 `datatables-pdf`（pdfmake）。

### 2. JS：新增 `XFAdmin.bindRowDetail` / `XFAdmin.bindBulk`（resources/assets/js/xfadmin.js）
- `bindRowDetail`：行首箭头按钮 → `row.child()` 展开键值详情（标签复用列头 title），`draw.dt` 后自动重注入按钮。
- `bindBulk`：行复选框 + 表头全选（含半选态 indeterminate）+ 批量操作栏（确认弹窗 → `XFAdmin.request` 提交 → 成功 toast → 服务端 `ajax.reload` / 本地 `draw`）。
- 行分组 `drawCallback`：按字段插入分组标题行（`.xf-dt-group-row`），colspan 按可见列数计算。

### 3. 缺陷修复
- **PHP**：`data-xf-config` 的 `json_encode` 补充 `JSON_HEX_TAG|JSON_HEX_AMP`（防 `</script>`、`&` 在属性中裸露）；所有 `data=null` 列统一补 `defaultContent`（此前仅操作列有，辅助列会触发 DataTables "Requested unknown parameter" 警告）；`stateSave` 赋值曾发生在 `$config` 拷入 `$xfConfig` 之后导致丢失（已提前）；`thead th` 现输出列 `class`（前端依赖 `thead th.xf-dt-select-col` 定位全选框）。
- **JS**：`idSafe()` 原会剥离 `[]` 等字符（`roles[]` 字段在 `formDialog.collect()` 中静默丢失），改为仅转义引号/反斜杠；`bindBulk` 中批量按钮位于表格外，`$el.find(this)` 空集 bug 修复为直接包装；`data-reload="0"` 判定改用 `attr()` 避免 jQuery `.data()` 数字歧义。

### 4. CSS（resources/assets/css/xfadmin.css）
- 新增 DataTable 增强样式段：选择列/明细列固定 40px 居中、明细展开面板、分组标题行（左色条徽标）、批量操作栏（入场动画）、表头底纹/圆角、行 hover、空态、列筛选行等整体美化。

### 5. 演示与测试
- `demo/pages/tables.php`：数据补充自增 `id`；新增卡片 7「交互增强」完整演示（批量启用/停用/删除 + 行明细 + 状态持久化 + copy/excel/csv 导出）。
- `tests/regression.php`：新增用例 26-28（五件套接线 / 辅助列顺序 / row_detail 与 responsive 互斥 / 配置 JSON `</script>` 不裸露）。
- 验证：`node --check`、`php -l`、smoke/regression 全 PASS；Playwright 运行时实测（明细展开、勾选批量栏计数、全选/半选、确认后请求 + toast、翻页后控件重注入、分组行渲染、13 个导出按钮）零 JS 错误。

---

## 本轮：页面弹窗通用化（pageDialog）+ DataTable「新增」弹窗按钮

### 1. JS：`XFAdmin.pageDialog(url, opts)` 通用页面弹窗（resources/assets/js/xfadmin.js）
- 原 `editPage` 升级为通用底层 `pageDialog`（拉取服务端页面 → 提取 `[data-xf-page-content]` → 表单 AJAX 接管 → 422 回填 → 成功刷新表格），`editPage` / `createPage` 成为语义化别名（默认标题「编辑」/「新增」），新增 `opts.onLoaded(body, modal)` 回调。
- 新增声明式触发器：任意元素加 `data-xf-page-dialog="url"` 即点击弹窗加载该页面；可选 `data-xf-title` / `data-xf-size` / `data-xf-frame`（iframe 模式）/ `data-xf-table`（提交成功后刷新指定表格）/ `data-xf-reload="false"`。
- 编辑动作弹窗标题兼容 `title` 键（原仅 `editTitle`），二者均支持 `{field}` 占位。

### 2. PHP：DataTable 新增 `create` 选项（src/Components/Table/DataTable.php）
- `'create' => '/xx/create'` 或 `['page'=>..,'label'=>..,'title'=>..,'size'=>..,'frame'=>..,'icon'=>..,'class'=>..]`，在表格上方渲染「新增」按钮（`.xf-dt-create`），点击后以弹窗加载服务端新建页面，提交成功自动刷新本表格。

### 3. 演示闭环（demo/）
- `demo/router.php` 新增 HTML 片段端点：`GET /api/demo/staff/{id}/edit` 与 `GET /api/demo/staff/create`（返回带 `[data-xf-page-content]` 的表单片段，提交走既有 Mock JSON 端点）。
- `demo/pages/tables.php` 新增卡片 6「弹窗加载服务端页面」：编辑按钮 page 模式 + `create` 新增按钮完整演示。

### 验证
- `node --check`、全量 `php -l`、`tests/smoke.php`、`tests/regression.php` 全 PASS；demo `/tables` 页按钮/动作接线与三端点（edit/create/store）实测 200。

---

## 本轮：DataTable 富单元格全家桶 + 个性化详情引擎 + 编辑/删除闭环

### 1. 新增 8 个单元格渲染器（resources/assets/js/xfadmin.js）
- `tooltip` 悬浮提示（Bootstrap Tooltip，`text` 模板 + `length` 截断展示）；`popover` 气泡提示（点击/聚焦弹出，`title`/`content` 支持 `{field}` 占位）。渲染后每次 `draw.dt` 自动初始化 Bootstrap 实例。
- `toggle` 按钮式状态切换：点击提交 `url`（POST）后翻转按钮文案/配色/图标，失败自动保持原状态；派发 `xf:toggle` 事件供业务扩展。
- `status` 彩色状态点、`trend` 涨跌趋势（红降绿升 + 图标）、`sparkline` 迷你趋势图（内联 SVG line/bar，零第三方依赖）。
- `timeline` 单元格时间线：值为 `[{time,title,text,color}]`，默认展示 `max` 条，超出出现「查看全部」按钮弹窗展示完整时间线。
- `dropdown` 单按钮下拉操作组；`buttons` 按钮组别名（与 `actions` 等价）。PHP 侧 `RENDER_SHORTCUTS` 同步登记 8 个快捷键，交互列默认禁排序范围扩大。

### 2. 行详情引擎（`XFAdmin.viewRow` 重写，彻底解决"详情内容单一"）
- 5 种布局：`kv`（默认键值对）/ `profile`（头像档案头 + 双列 kv）/ `tabs`（分区标签页）/ `sections`（分区堆叠）/ `template`（任意 HTML 模板，占位符自动转义）。
- 8 种分区类型：`kv` / `table`（子表格）/ `timeline` / `stats`（统计卡）/ `tags` / `progress` / `images` / `html`。
- `ajax` 详情接口：打开弹窗时拉取 `/api/x/{id}` 并合并进行数据展示（列表轻、详情全）。
- 零配置增强：省略配置时自动复用表格列头中文名（`__xfColMeta`）与列渲染器展示，详情弹窗与表格观感一致。

### 3. 编辑 / 删除操作闭环
- `action: delete` 新语义：确认框 → `ajax`（默认 DELETE）→ toast → 服务端表格自动刷新；本地数据源省略 `ajax` 时直接移除行。
- 编辑弹窗标题 `editTitle` 支持 `{field}` 占位插值；自动生成表单时字段标签复用列头中文名。

### 4. 基础缺陷修复
- **修复 `/tables` 演示页空白**：`demo/pages/tables.php` 末尾 `return $page;` 被 `match` 丢弃导致整页无输出，改为 `echo $page;`（"页面不显示"的直接根因）。
- `Component::raw()` 支持闭包惰性内容（`content` 传闭包不再致命错误）；`XfAdmin::component()` 别名解析大小写不敏感兜底（`datatable`/`dataTable` 等价）。

### 5. 演示与 Mock 闭环（demo/）
- `demo/router.php` 新增 Mock API `/api/demo/*`：GET 详情返回增量字段（登录记录/项目/安全日志时间线），POST/PUT/PATCH/DELETE 返回标准 JSON，让编辑、删除、状态切换、详情拉取在纯静态演示中形成完整闭环。
- `demo/pages/tables.php` 新增两张卡片：「全渲染器矩阵」（19 列覆盖全部渲染器 + 左2右1固定列 + 表头吸顶 + 三闭环操作）与「个性化详情布局」（同一行数据四种完全不同的详情弹窗：档案式/标签页/分区式/模板式）。

### 验证
- `node --check xfadmin.js`、全量 `php -l` 通过；`tests/smoke.php`、`tests/regression.php` 全 PASS。
- Playwright 真实浏览器闭环测试：5 表渲染、tooltip/popover 弹出、toggle 翻转、编辑表单保存（PUT）、删除确认（DELETE + 行移除）、单元格时间线弹窗、四种详情布局差异化渲染 —— 全部通过；本地破图 0、JS 错误 0。

---

## 本轮：组件文档补全 + 业务组件演示 + 约定加固

### 1. 完整文档补全（docs/components-reference.md）
- 审计发现 136 个已注册组件中有 30 个此前缺失独立文档条目：`menu`、布局 `landing`，以及业务/数据组件 `orders`、`orderDetails`、`customers`、`clients`、`sellers`、`projects`、`projectDetails`、`projectTeamBoard`、`roles`、`profilePage`、`emailApp`、`chatApp`、`outlook`、`blogArticle`、`forumThread`、`companyCard`、`contactCard`、`reviewList`、`todoList`、`voteList`、`issueTracker`、`invoiceDetail`、`invoiceCreate`、`productDetails`，杂项 `masonry`、`pinBoard`。
- 已为上述 30 个组件逐一补写「关键参数 + 输入示例 + 输出/行为」条目（按源码的 `defaults()` 与类注释逐字核对），并在文末新增「业务 / 数据 / 导航 / 杂项组件补遗（本轮补全 30 个）」章节。**当前 136 个已注册组件 100% 均有文档**。

### 2. 业务组件实时演示（demo/）
- 新增 `demo/pages/apps.php`：集中渲染全部 30 个业务/数据/导航/杂项组件，便于对照文档查看实际效果（已通过渲染自测，输出约 83KB 无异常/警告）。
- 新增 `demo/pages/landing.php`：独立落地页（`XfAdmin::landing()` 返回完整 `Stringable` 页面）。
- `demo/index.php` 菜单与路由同步接入 `/apps`、`/landing`；回归测试（路由↔菜单一致性、页面可达性）全部通过。

### 3. 资源解析约定加固（src/Components/Data/ReviewList.php）
- 评分星图由直接 `XfAdmin::asset('images/ratings.svg')` 改为 `$this->img('ratings.svg')`，统一走组件/静态上下文的图片解析约定（外链 / data URI 自动原样返回、空值返回空、其余按 `images/` 解析），消除潜在的解析不一致。

### 验证
- `php tests/smoke.php`：135 个组件全部渲染通过。
- `php tests/regression.php`：ALL REGRESSION PASSED（含路由/菜单一致性、DataTable、转义、空数据渲染）。
- 新建演示页渲染自测无异常/警告。

---

## 本轮：骨架 / 整页组件实时预览 + LockScreen 修复

### 1. LockScreen 组件修复（包内 `src/Components/Layout/LockScreen.php`）
- 修复两处缺陷：原实现只输出 HTML 片段（无 `<!DOCTYPE>`/`<head>`/资源引用，独立访问完全无样式）、且结尾多输出一个 `</div>`（标签不平衡）。
- 重写为与 `AuthPage` 一致的完整文档组装：主题属性（skin/mode）、favicon、`Assets::head()/scripts()`、`resetCollected()` 防多文档污染；样式类对齐 `auth-box` 体系；新增 `title/below/copyright/favicon/head/scripts/theme/lang` 选项（原有 `user/action/heading/text/brand` 兼容不变）。

### 2. 骨架 / 整页组件实时预览（演示宿主 `wsf/Modules/Admin`）
- 「页面与布局」分类下 11 个 `render=false` 组件（Page/Sidenav/Topbar/Footer/Customizer/AuthPage/ErrorPage/ComingSoon/Maintenance/LockScreen/Landing）的详情页由纯文字说明升级为 **iframe 内嵌真实渲染 + 新窗口打开**。
- 新增路由 `GET /admin/demo/preview/{alias}`（`DemoController::preview`，alias 白名单约束、非法 404）：整页组件复用页面模板渲染；骨架组件构造聚焦该部件的完整演示页（统计卡片 + 说明卡 + 多级菜单 + 顶栏通知/用户菜单 + 页脚 + 定制面板）。

### 验证
- 12 个预览页全部输出完整文档（含 3 个基础 CSS + app/xfadmin JS）、详情页均内嵌对应 iframe、非法 alias 404；包内 smoke/regression、企业链路自测、8 个组件组页渲染全部通过。

---

## 本轮：企业应用示范落地（宿主端 41 个企业管理数据集 + DSL 驱动闭环）

> 本节改动位于演示宿主 `wsf/Modules/Admin`（非包内），用于验证并示范 XfAdmin 组件在真实企业后台中的完整闭环用法。

### 1. 字段 DSL 驱动的企业应用目录（`Services/EnterpriseCatalog.php`）
- 以 `[name, label, type, opts]` 一处声明字段，自动派生：演示数据生成规则、DataTable 列（富单元格渲染）、过滤工具栏（select/select2/switch/range/daterange/关键词）、DataSet 过滤器（`=`/`like`/`>=`/`<=`/`date_from`/`date_to`）、行编辑白名单与编辑模态表单、统计卡片。
- 覆盖 5 大分区 31 个模块 41 个数据集：内容与创作（文章/博客/CMS/相册/文档/知识库）、营销与客户（CRM/订单/客服/工单/短链/舆情）、自动化与数据（爬虫/数据采集/定时任务/RPA/AI 办公/BI）、供应链与制造（ERP/WMS/SCM/SRM/TMS/EAM/MDM/DMS）、组织与协同（OA/HRM/PM/任务/BPM/低代码/ESB）。

### 2. 演示数据层（`Services/EnterpriseData.php`）
- 确定性生成（`mt_srand(crc32(dataset))`）+ 缓存持久化：行编辑 / 单元格开关 / 删除跨请求可见，形成真实管理闭环；编辑值按字段类型规范化（int/money/switch 等），非白名单字段拒绝。

### 3. 统一入口与接线
- 新增 `EnterpriseController`（`GET /admin/app/{module}/{page?}`）：统计卡片 + 服务端 DataTable（过滤栏 / 行编辑模态 / 行删除 / 开关单元格 / 导出按钮 / 固定列滚动）。
- `DataController` 三个写接口与查询接口按 `EnterpriseCatalog::has()` 分流至企业数据层；未知数据集仍 404。
- `AdminLayout::menu()` 由 EnterpriseCatalog 自动生成 5 个分区菜单（单页模块直链、多页模块二级菜单）。

### 验证
- 全链路自测 ALL PASS：41 个数据集派生配置完备、6 个抽样页面真实渲染（7 万+ 字节/页）、分页/枚举/范围/关键词过滤、行编辑（白名单拦截）、单元格开关、删除持久化、未知数据集 404；8 个组件演示组页在新菜单下渲染无回归；包内 smoke/regression 全部通过。

---

## 本轮：健壮性 & 安全加固（空数据渲染基线 + 转义纵深防御 + 演示补全）

### 1. 配置序列化纵深防御
- `src/Components/Component.php` 的 `initAttrs()` 在 `data-xf-config` 的 JSON 编码中新增 `JSON_HEX_TAG | JSON_HEX_AMP`（原有 `JSON_HEX_APOS | JSON_HEX_QUOT` 保留）。将 `< > &` 一并转义，杜绝配置中 `</script>` 断标签或 `&` 误解析导致的注入/解析异常（属性引号逃逸原本已防护，此为纵深防御）。

### 2. 纯文本槽位转义（XSS 加固）
- `Data/ActivityFeed.php` 的 `text` 为动态正文（多为用户生成内容），由 `raw()` 改为 `e()` 转义输出，避免存储型 XSS。说明：`ProjectActivity.desc`、 `Article.body`、 `Faq.answer`、 `Widget.value/footer`、 `ProductCard.price`、 `Toast.body`、 `ProfileHeader.actions` 等属于“开发者主动传入富文本/HTML”的槽位，保持 `raw()` 透传（符合组件库设计预期）。

### 3. 健壮性基线（生产可用）
- 全量 135 个具体组件在**空数据 `[]`** 下渲染：经审计**零异常、零 PHP 警告**（仅抽象基类 `Component` 不可实例化属正常）。
- 回归测试新增 **第 24 组**：遍历 `src/Components` 全部具体组件，以 `[]` 渲染并捕获 `Throwable` 与 `E_WARNING/E_NOTICE`，断言 `$bad === 0`，锁定“空/缺键数据不崩溃”的生产基线。
- 回归测试新增 **第 25 组**（安全性）：对 `ActivityFeed` 注入 `<script>` 文本与 `" onmouseover="` 属性断点，断言输出中无裸 `<script>`、引号/事件处理器不逃逸、`<` 被转义为 `&lt;`。

### 4. 演示补全（可见性 + 集成冒烟）
- `demo/pages/widgets.php` 新增区块覆盖 P7 数据卡片类：`StatCard`（4 张统计卡）、`Widget`（icon/progress 两种）、`TeamMember`（成员网格）、`Testimonial`（用户证言）、`ActivityFeed`（动态流）、`Chip`（头像/图标/可关闭/链接四种）、`VoteList`（投票）。既展示对齐成果，也作为组件集成冒烟。

### 验证
- 全量 `php -l` 0 错误；regression 第 24/25 组新增并 **ALL PASSED**；smoke **ALL PASSED**。

---

## 本轮：样式对齐 P7（其余组件收尾：头像统一 + 卡片对齐 + 全量中文注释补强）

### P7 · 头像收尾（P2 遗留"拼接式"裸头像清零）
- **回归测试第 16 组原为"扫源码"方式**：`preg_match('/<img[^>]*rounded-circle[^>]*width="\d+"/', $code)`。但组件内头像 img 多经 `$this->e(...)` 拼接，PHP 的 `->` 操作符含 `>` 字符，导致 `[^>]*` 在 `->` 处提前终止，**对所有拼接式 img 假通过**——`Testimonial` / `Chip` 长期漏检却显示"通过"。
- **改为扫描组件渲染后的 HTML 输出**（渲染结果不含 `->` 操作符，正则可靠命中），并新增正向校验：`Testimonial` 输出含 `class="avatar avatar-sm"`、`Chip` 输出含 `class="avatar avatar-xs"`。
- **修复三处拼接式裸头像**（统一 `.avatar` 包裹，移除 `width/height` 表现属性）：
  - `Data/Testimonial.php`：44px 裸 `<img class="rounded-circle" width="44" height="44">` → `<span class="avatar avatar-sm flex-shrink-0"><img class="img-fluid rounded-circle"></span>`。
  - `UI/Chip.php`：20px 裸 `<img class="rounded-circle" width="20" height="20">` → `<span class="avatar avatar-xs flex-shrink-0"><img class="img-fluid rounded-circle"></span>`。
  - `Data/VoteList.php`：作者头像 `<img class="img-fluid avatar-xs rounded-circle">`（尺寸类直挂 img）→ `<span class="avatar avatar-xs flex-shrink-0"><img class="img-fluid rounded-circle"></span>`，与全局头像规范一致。

### P7 · 卡片对齐 & 全量中文注释补强
- 复查 P7 范围的 `StatCard` `Widget` `ActivityFeed` `Timeline` `TeamMember` `Testimonial` `ReviewList` `VoteList` `ProfileHeader` `BlogList` `BlogArticle` `Article` `ForumThread` `SearchResults` `ProductCategories` `Sellers` `SellerDetails` `Projects` `ProjectActivity` `ProjectDetails` `ProductDetails` `Clients` `Customers` `CompanyCard` `ContactCard` `Chip` 等：头像均走 `.avatar` 包裹（或尺寸类直挂容器，二者 INSPINIA 均认可），卡片均用 Bootstrap/INSPINIA `card`/`card-body`/`card-header`/`badge-soft` 体系，无自造类名。
- 全量 134 个组件类均带中文文档注释（含用法示例）；关键逻辑补内联中文注释（如 `TeamMember` 有图/无图同尺寸对齐、`VoteList` 作者头像包裹说明）。

### 验证
- 全量 `php -l` / `node --check` 0 错误；regression 第 16 组改为渲染扫描并新增 Testimonial/Chip 正向校验 **ALL PASSED**；smoke ALL PASSED。

---

## 本轮：样式对齐 P6（DataTable 进阶：列筛选 / 导出 / 密度 / 完整过滤栏）

### P6 · 导出按钮修复（关键 bug）
- `Table/DataTable.php` `mapButton()` 原把 `copy/csv/excel/print` 直接当 DataTables `extend` 字符串（DataTables 仅认 `copyHtml5/csvHtml5/excelHtml5/printHtml5`，无 `copy` 类型）→ 导出按钮在控制台报 "Unknown button type" 且静默失效。
- 已改为正确 html5 extend，并补 Tabler 图标文本：`copy`→`ti-copy`、`csv`→`ti-file-text`、`excel`→`ti-file-spreadsheet`、`print`→`ti-printer`、`pdf`→`ti-file-type-pdf`、`colvis`→`ti-columns`。依赖 `datatables` 资源自带 `buttons.html5.min.js`/`buttons.print.min.js`/`jszip.min.js`；PDF 由 `assets()` 自动挂载 `datatables-pdf`。

### P6 · 密度切换（新增）
- `buttons => ['density']` 注入 `xf-btn-density` 包内扩展按钮；点击在 `<table>` 上切换 `.xf-dt-compact` 类（收紧 `thead th`/`tbody td` 内边距与字号，紧凑↔宽松）。
- 初始化可用 `density => 'compact'` 直接以紧凑态渲染（覆盖 `tableClass()` 追加 `xf-dt-compact`）。

### P6 · 列筛选 columnFilters
- `column_filters => true` 在表头下方渲染 `xf-filter-row` 每行（输入/下拉），按列实时筛选；`xfadmin.css` 新增 `.xf-filter-row` 样式（浅底、紧凑内边距）。

### P6 · 完整过滤栏 filter_bar（含本地模式）
- `xfadmin.js` `datatable` 模块 `reloadWithFilters` 新增**本地模式**：无 `ajax` 时，按 `data-filter` 名经 `colIndexByName`（`column.dataSrc()` → 列索引）映射到列并 `table.column(ci).search(fv)`，过滤栏对本地数据同样实时生效。
- `demo/pages/tables.php` 重构为三卡示范：① 基础数据表（行分组/排序/多选/固定表头）② 列筛选示范 ③ 完整过滤栏 + 工具栏（复制/CSV/Excel/打印/列显隐/全屏/密度切换），让全部能力可见。

### 验证
- 全量 `php -l` / `node --check` 0 错误；regression 新增第 22–23 组（密度切换 / 过滤栏+导出）**ALL PASSED**；smoke ALL PASSED。

---

## 本轮：样式对齐 P5（Nestable + Masonry + Calendar）

### P5 · 嵌套拖拽列表（对齐 misc-nestable.html）
- `Misc/Nestable.php` 从旧「jQuery Nestable `dd-*`」体系改为 **INSPINIA v4 原生 `.nested-sortable`**（SortableJS）结构：根 `list-group.nested-sortable`，子级在 `list-group-item` 内嵌 `list-group.nested-sortable`，递归渲染 `children`；`handle` 模式加 `.sort-handle` 把手；`input` 提供隐藏域在拖拽后写入 id 序列。
- `xfadmin.js` 新增 `nestable` 模块：对容器内所有 `.nested-sortable`（含嵌套）以 `group:'nested'` 初始化 SortableJS（ghostClass/animation/fallbackOnBody 与模板一致），拖拽结束同步隐藏 input。
- `xfadmin.css` 补全 `.nested-sortable` 视觉（浅底虚线边框容器、卡片项、把手色、拖拽态 `.sortable-drag` / `.sortable-item-ghost`）。

### P5 · 瀑布流（对齐 misc-masonry.html）
- `Misc/Masonry.php` 从 CSS 多列 `masonry-x` 改为 **INSPINIA 原生 `row` + `col-*`** 结构：`<div class="row g-4" data-xf="masonry" data-masonry>` + 子项 `<div class="col-xl-N col-md-6 masonry-cell">`（`columns` 2/3/4 → xl 跨度 6/4/3）。
- `xfadmin.js` 新增 `masonry` 模块：`global.Masonry` 存在时用 `itemSelector:'.masonry-cell'` + `percentPosition` 初始化（图片加载后重排，缺失则 setTimeout 兜底）；未加载时自动降级为 Bootstrap 网格（不破版）。
- 注册 `masonry` 资产（`plugins/masonry/masonry.pkgd.min.js`）。

### P5 · 日历（对齐 calendar.html）
- `Misc/Calendar.php` 包在 `.card` 内；提供 `externalEvents` 时渲染左侧「可拖拽事件」栏（两栏布局，含 `新建事件` 按钮 + `#external-events` 芯片），右侧 FullCalendar。
- 配置补齐 INSPINIA 同款选项：`themeSystem:'bootstrap'`、`headerToolbar`（月/周/日/列表切换）、`buttonText`（中文）、`editable/droppable/selectable/nowIndicator/dayMaxEvents`；事件 `className` 用 `bg-X-subtle text-X border-start border-3 border-X` 风格（与模板外部事件一致）。
- `xfadmin.js` `calendar` 模块新增：当 `config.externalEvents` 为真且 `FullCalendar.Draggable` 存在时，对 `#external-events` 初始化拖拽源（`itemSelector:'.external-event'`，`eventData` 取 `data-class`）。

### 验证
- 全量 `php -l` / `node --check` 0 错误；regression 新增第 19–21 组（Nestable/Masonry/Calendar 结构）**ALL PASSED**；smoke ALL PASSED；demo 6 页 HTTP 200、0 PHP 错误。

---

## 本轮：样式对齐 P4（看板 Kanban + 邮件中心 EmailApp/MailList/Outlook）

### P4 · 看板重写（对齐 project-kanban.html）
- `Data/Kanban.php` 重写为 INSPINIA 原生的 `.kanban-app` 结构：`.card` 外壳（card-header 含 `app-search` 搜索框 + 新增按钮）/ `.card-body.p-0` > `.kanban-content`(bg-light) > `.kanban-board`(列) > `.kanban-item`(列头，标题带计数 + 新增) + `.kanban-board-group.px-3[data-simplebar data-column]` > `ul.list-unstyled[data-kanban-list data-plugins=sortable]` > `li.kanban-item` > `.card.shadow.border-light`。
- 卡片：顶部 `badge-soft-{variant}`(带 `ti-point-filled`) 标签 + 下拉操作菜单；`link-reset` 标题；`avatar-group-xs` 成员 + 截止日期(`ti-calendar-time`) + 可选评论/附件；底部进度条(`progress-bar`)。
- `xfadmin.css` kanban 段重写为兼容型：移除与 `.kanban-app` 上下文冲突的覆盖（列宽/排列由 app.min.css 提供），仅保留抓取光标 + 卡片悬停微浮起。
- `xfadmin.js` kanban 模块补充：顶栏搜索按卡片文本过滤并实时更新列计数；新增按钮 `xf.kanban.add` 事件。
- 注册 `simplebar` 资产（`src/Assets/Assets.php`），供 `data-simplebar` 卡片组滚动。

### P4 · 邮件中心重写（对齐 email.html）
- `Data/EmailApp.php` 三栏 `.email-app`：左 `list-custom` 文件夹 + 中 `table table-hover table-select` 邮件行（勾选 `email-item-check` / 星标 `email-action-btn` / `avatar-xs` 头像 / 发件人 / `link-reset stretched-link` 主题+预览 / 时间 / 附件 `ti-paperclip`）+ 右阅读窗格(回复/转发)。未读行浅底 + 主题加粗。
- `Data/MailList.php` 改为与 EmailApp 一致的表格式邮件行（紧凑卡片版，复用于仪表盘）。
- `Data/Outlook.php` 列表头像 `avatar-sm`→`avatar-md`，对齐 outlook.html。
- `xfadmin.css` 新增 `.email-table` 段：`tr{position:relative}`（支撑 stretched-link 整行可点）、未读态、`email-action-btn` 星标色、激活文件夹态。
- `xfadmin.js` 新增 `email` 模块：列表内 `data-email-search` 防抖过滤邮件行。

### 验证
- 全量 `php -l` / `node --check` 0 错误；regression 新增第 17–18 组（Kanban 结构 / EmailApp+MailList 表格式邮件行）**ALL PASSED**；smoke ALL PASSED；demo 6 页 HTTP 200、0 PHP 错误。

---

## 本轮：样式对齐 P2（散落头像统一）+ P3（画廊/订单重写）+ xftable 交互模块

### P2 · 散落头像统一（10 处裸头像清零）
- `TeamMember`(80px→avatar-xxl)、`ActivityFeed`(40px→avatar-md)、`ProfileHeader`(96px→avatar-xxl)、`MailList`(36px→avatar-md)、`CommentThread`(40px→avatar-md)、`ChatBox`(36px→avatar-md / 32px→avatar-sm)、`BlogList`(28px→avatar-xs)、`ProjectDetails`(24px→avatar-xs)：全部由裸 `<img rounded-circle width=NN>` 改为 `.avatar avatar-{size}` 包裹结构；占位缩写与图片头像同尺寸（避免卡片高度跳动）。`ProductDetails` 的 64px 为商品缩略图非头像，保留。
- **事实修正（枚举核对）**：INSPINIA 真实惯例中尺寸类可直接作容器（`avatar-md` 直挂 2782 次 vs `.avatar avatar-sm` 组合 80 次），`xfadmin.css` 已兼容两种写法；唯一禁止的是裸表现属性写法——回归第 16 组自动扫描全部组件源码拦截回归。

### P3 · 画廊重写（对齐 misc-gallery.html）
- `Data/Gallery.php` 重写：card 容器 + card-header（`app-search` 搜索框 + `btn-ghost-primary` 分类筛选钮组）+ `row-cols` 响应式栅格 + 单项 `card border-0` + `badge-label` 分类角标 + `a.image-popup > img.card-img.rounded-2` 灯箱结构；搜索/筛选/Masonry 三者联动（任一变化后重排）。grid 模式按 `--xf-ratio` 等比裁切，masonry 模式保留原始比例。
- `xfadmin.css`：画廊段按新结构重写（aspect-ratio 裁切、悬停渐变浮层、筛选钮组）；新增 `.xf-card-search` 通用卡片内搜索框样式（修复框架 `.app-search-icon` 在白底卡片强制半透明白导致图标不可见）。

### P3 · 订单列表重写（对齐 ecommerce-orders.html）+ xftable
- `Data/Orders.php` 重写：模板同款 `table-custom table-select table-hover` + `thead-sm bg-light bg-opacity-25` + `text-uppercase fs-xxs` 表头；行结构逐列对齐（勾选框 / `link-reset` 订单号 / 日期+弱化时间 / `.avatar avatar-sm` 客户块 / 金额 / `ti-point-filled` 付款状态 / `badge-soft-*` 订单状态 / 支付方式 / 圆形浅色图标操作钮）；行携带 `data-status`/`data-paid` 供筛选精确匹配。
- **`xfadmin.js` 新增 `xftable` 模块**（对标 INSPINIA `custom-table.js` 的 `data-table-*` 体系）：前端搜索(防抖)/下拉筛选/分页(省略号折叠)/每页条数/全选(仅可见行)/批量删除/单行删除/统计信息，事件全委托。静态数据表格从此具备完整搜索筛选交互；服务端大数据量场景仍走 DataTable(`ajax`+`server_side`+`filter_bar`)。

### 验证
- 全量 `php -l` / `node --check` 0 错误；regression 新增第 14–16 组（Gallery 结构 / Orders 结构+接线 / 裸头像全源码扫描）**ALL PASSED**；smoke ALL PASSED；demo 5 页 HTTP 200、0 PHP 错误。

---

## 本轮：组件与 INSPINIA v4.1.0 样式对齐 · 枚举式审计（P1 基础）

### 根因（为何"与后台模板不一致"）
1. 组件用了"臆造类名"（`table-centered`/`search-box` 在 INSPINIA 中根本不存在）；
2. `Avatar` 未用 INSPINIA 规范 `.avatar` 包裹结构（尺寸类挂错元素 + 散落裸 `<img>`）；
3. 自定义 xf-* 组件（画廊/看板/邮件/嵌套/瀑布/日历）CSS 取值偏离 INSPINIA。

### 已完成（P1）
- **新建 `docs/STYLE_ALIGNMENT.md`**：枚举式（CSS/JS/数据 三层）对照审计 + 12 组件对照表 + 7 阶段执行计划 + 验证方法。直接回应"逐一对比 + 完善文档"。
- **`UI/Avatar.php` 重写**：严格对齐 INSPINIA——图片/文字均包 `<span class="avatar avatar-{size}">`，尺寸类挂包裹元素；支持 size/variant/soft/rounded/group，输出 `alt` 防 XSS。强中文注释。
- **`Data/CompanyCard.php` / `Data/ContactCard.php`**：裸头像（`<img class="avatar-lg">` / `<img width=64 height=64>`）改为规范 `.avatar` 包裹，消除"头像不一致"。`Clients`/`Customers` 经核查已为正确结构。
- **测试 `tests/regression.php` 新增第 13 组**：断言 Avatar 输出 `.avatar avatar-md` 包裹、外链解析、文字浅底缩写、分组 `.avatar-group`，防回归。
- 全量 `php -l` 0 错误；smoke + regression **ALL PASSED**；demo 7 页 HTTP 200、0 PHP 错误。

### 关键结论（DataTable 搜索/筛选）
用户点名"table 缺乏前后台搜索/筛选、缺自定义搜索表单项"。经核查能力**已具备**：`filter_bar`（text/select/range/date/daterange/number/checkbox/radio/multiple）+ `ajax`+`server_side`（JS 接线 `xfadmin.js` `register('datatable')` 已实现防抖/重置/重载）。后续 P6 在 demo `tables` 页增加完整 `filter_bar` 示例使其可见。

### 待续（P2–P7，见 STYLE_ALIGNMENT.md）
P2 客户/公司/联系人/评论/聊天/顶栏/侧栏头像统一改用 Avatar；P3 画廊+订单/发票表格取值对齐；P4 看板+邮件中心；P5 Nestable/Masonry/Calendar 补齐；P6 DataTable 列筛选/导出/密度 + demo 示范；P7 其余组件头像与卡片对齐 + 全量中文注释补强。

---

## 本轮：demo / 框架集成层 / CSS 覆盖率排查与修复 + 测试扩充

### 关键缺陷修复
- **demo `/widgets` 路由 500（HIGH）**：`demo/index.php` 的 `match` 分支 `require pages/widgets.php`，
  但该文件不存在；侧边栏「组件展示」与「系统」子菜单共 5 处链接指向 `/widgets`，点击即 fatal。
  新建 `demo/pages/widgets.php`（Alert / Badge / Button / Progress / Pagination / Rating / Tabs /
  Accordion / Timeline / Stepper 组件展示页）。已实测全部 7 个 demo 路由 HTTP 200、0 PHP 错误。
- **demo 资产服务加固（LOW→已修）**：`demo/router.php` 与 `demo/index.php` 内嵌资产服务原以
  `str_contains($rel,'..')` 为唯一防护，且 MIME `default => octet-stream` 会输出任意扩展名文件。
  改为：扩展名白名单（未知扩展一律 404）+ `realpath()` 包含性校验（防符号链接/规范化差异越界）；
  修正 `woff` MIME（原误标 `font/woff2`）、补 `webp`/`mjs`/`pdf`。实测穿越与 `.php` 请求均 404。
- **CSS 覆盖率补缺（MED）**：全量比对「组件发出的 xf-* 类 vs xfadmin.css 定义」，补上仅有的两个
  真实缺口——`.xf-article` 根容器排版（行高/段距/图片自适应圆角）与 `.xf-gallery` 根容器定位。
  JS 侧选择器经全量核对无死选择器/拼写错位。

### 已核实为正确（无需改动）
- Laravel `AssetController`（白名单 + realpath + ETag/304）、`XfAdminServiceProvider`、ThinkPHP
  `Service`/`PublishCommand` 实现规范；demo 图片引用全部存在；组件选项名与 demo 用法全部匹配；
  `helpers.php` 均有 `function_exists` 守卫。

### 测试扩充（tests/regression.php 新增 3 组 11 项断言）
- `XfAdmin::img()`：外链/data URI 原样、空串返回空、相对路径解析 `images/`、首斜杠归一、与
  `Component::img` 行为一致。
- demo 路由完整性：`match` 引用的页面文件必须全部存在；菜单本地链接必须都有对应路由（防再次
  出现「菜单指向不存在页面」）。
- CommentThread：空头像不输出空 `src`、渲染首字母占位；有头像走 `images/` 解析。
- 全量 `php -l` 通过；smoke + regression **ALL PASSED**。

## 本轮：全面错误 / 异常 / 健壮性排查与修复 + 能力扩展

### 关键缺陷修复
- **app.js 致命崩溃（HIGH）**：`App.init()` 首行裸引用 `lucide.createIcons`，而 `lucide` 未加载时
  `typeof lucide.createIcons` 直接抛 `ReferenceError`，导致 `App.init()` 整段失败（popover / tooltip /
  复制按钮等全部失效）。改为 `typeof lucide !== 'undefined'` 守卫，缺失时安全跳过。
- **xfadmin.js 重复弹窗（MED）**：DataTables `error.dt` 同时绑定原生事件与 jQuery 事件，各弹一条 toast，
  出错时弹两条。保留原生事件弹 toast，jQuery 分支仅做 `console.warn`，消除重复。
- **xfadmin.js 清理（LOW）**：移除已废弃的 lucide 增量渲染残留分支（图标已统一为 Tabler `ti`）。
- **CommentThread 破图（MED）**：头像为空时仍渲染空 `src` 破图，改为首字母占位 div；图片统一走
  `$this->img()` 解析，兼容 http(s)/data URI 与空值。
- **InvoiceCreate 空键告警（MED）**：`from`/`to` 缺省时 PHP 8 报「访问 null 偏移」。强转 `(array)` 兜底。
- **Select 占位失效（MED）**：多选场景下 `placeholder` 被注入插件配置却无对应空白 `<option>`，占位不显示。
  仅在单选时注入插件配置。
- **chart-echart-geo-map.js 引用错误（MED）**：iceland 散点图 `selectchanged` 回调引用未定义变量
  `option` → `ReferenceError`。将配置对象赋给 `option` 变量供回调引用。
- **misc-pdf-viewer.js 空守卫（LOW）**：`page_num` / `page_count` 元素缺失时 `renderPage` 抛错，加 null 守卫。
- **apps-chat.js 注入风险（LOW）**：用户名渲染由 `innerHTML` 改为 `textContent`，避免潜在 HTML 注入。

### 系统性健壮性增强
- **新增静态 `XfAdmin::img(string $path)` 助手**（与 `Component::img()` 行为一致）：将 36 个组件内
  直接拼接 `XfAdmin::asset('images/'.ltrim($path))` 的调用统一改为 `XfAdmin::img($path)`，并保留
  `\zxf\XfAdmin\XfAdmin` 完全限定前缀以确保各命名空间下可解析。相对路径输出不变，**新增**对
  `http(s)://`、`data:` 绝对地址的原样透传，修复「用户传入外链 / 内联图时图片 404」的功能缺陷。
- 全量 `php -l` 通过；`tests/smoke.php` 与 `tests/regression.php` **ALL PASSED**。

## 本轮：图片 / 头像尺寸一致性修复 + 组件拆分 + 文档完善

### 图片 / 头像展示尺寸与后台模板对齐（重点）

- **根因**：`xfadmin.css` 全局 `img { max-width:100%; height:auto }` 中的 `height:auto`
  **会覆盖** `<img>` 的 `height="N"` 表现属性（HTML 表现属性优先级最低）。凡是用 `width`/`height`
  属性、却又未加内联高度 / `object-fit` 的图片，在非方图源下被拉成原始比例，导致与 INSPINIA 模板尺寸错位。
- **修复（21 处组件内图片）**：为 `TeamMember` / `ProductDetails` / `MailList` / `ActivityFeed` / `CommentThread` /
  `BlogArticle` / `ProfileHeader` / `BlogList` / `OrderDetails` / `ContactCard` / `ProjectDetails` / `ChatBox` /
  `Testimonial` / `ReviewList` / `AuthPage` / `Topbar` / `Chip` 等组件中的头像 / 缩略图补上内联精确尺寸
  `style="width:Npx;height:Npx;object-fit:cover"`，确保渲染像素与预期严格一致。
- **Logo 变形修复**：独立页（404 / 500 / auth / coming-soon / maintenance / lock / landing）的宽 logo
  （142×40）因默认 `object-fit:fill` 被横向挤压。扩展 `.logo-dark/.logo-light/.auth-brand img` 规则为
  `height:26px; width:auto; object-fit:contain`，消除挤压。
- **顶栏用户头像对齐**：由 32px 改为 `avatar-md`（36px），与侧栏用户头像标尺一致。
- **防御性增强**：`xfadmin.css` 增加 `img[width][height] { object-fit: cover; }` 兜底，并补充「`height:auto` 陷阱」
  注释，说明固定尺寸图片必须由组件显式输出内联尺寸或挂 `.avatar` 尺寸类。
- **验证**：Playwright 全量审计 **154 个渲染页、5330 张图片 → 破图 0、变形 0**；头像渲染像素
  （avatar-xs=24 / sm=32 / md=36 / xl=48 / xxl=80）与 INSPINIA 标尺逐项一致。

### 新增组件（继续拆分，注册表 134 → 136）

- `article`（文章阅读页，对应 article.html）：标题 / 元信息 / 封面 / 作者块 / 正文 / 引用 / 标签 / 相关文章。
- `projectActivity`（项目动态时间线，对应 project-activity.html）：纵向时间线展示「谁 + 做了什么」，内嵌操作者头像。
- （上轮已拆分 `companies` / `productCategories` / `productAdd` / `sellerDetails`）
- 全部接入 wsf `DemoCatalog` 演示目录，组件渲染校验 **OK=136 / FAIL=0**、`validate_demos` 模板 **15/0**。

### 注释与文档

- `XfAdmin.php` 13 个公共方法全部补齐详细中文 docblock（参数 / 返回值 / 异常 / 示例）。
- 新增组件均带完整中文 docblock（配置项、示例、与 INSPINIA 页面对应关系）。
- `README.md` / `docs/components.md` / `docs/components-reference.md` 组件计数更新为 **136**；
  `components-reference.md` 新增 6 个组件条目与「图片 / 头像尺寸规范」附录（防回归）。

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

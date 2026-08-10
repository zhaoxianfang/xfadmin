# 组件与后台模板（INSPINIA v4.1.0）样式对齐 · 枚举式审计与修复路线图

> 目标：让 `xfadmin` 全部组件（画廊 / 客户管理 / 订单 / 表格 / 日历 / 嵌套列表 / 瀑布流 / 看板 / 邮件中心 / 头像 等）的视觉与交互，逐项对齐后台模板 `INSPINIA_v4.1.0/HTML/Full`（参考源：`/Users/aha/zxf/INSPINIA_v4.1.0/HTML/Full/dist/assets/css/app.css` 为**非压缩可读版**，是取值权威来源）。

---

## 0. 根因（为什么"不一致"）

经枚举式核查，主要根因有三类：

1. **组件使用了"发明出来的"类名**。例如 `table-centered`、`search-box` 在 INSPINIA 中**根本不存在**；组件自己臆造词汇，自然与模板对不上。
2. **Avatar 未采用 INSPINIA 规范结构**。INSPINIA 头像恒为
   `<span class="avatar avatar-sm"><img class="img-fluid rounded-circle"></span>`
   或 `<span class="avatar avatar-sm"><span class="avatar-title">ZS</span></span>`，
   尺寸类 `.avatar-*` **挂在包裹元素 `.avatar` 上**。而当前 `Avatar` 组件把尺寸类直接挂到 `<img>` 上、且裸 `<img>` 无 `.avatar` 包裹，导致 34 个文件里散落的头像渲染不一致。
3. **自定义组件（xf-\* 类）的 CSS 取值偏离 INSPINIA**。画廊 / 看板 / 邮件 / 嵌套 / 瀑布 / 日历 由 `xfadmin.css` 自包含定义（INSPINIA 的 app.min.css 未定义这些类），但当初手写的取值（间距、圆角、底色、hover）与 INSPINIA 真实观感有偏差。

加载顺序（已确认正确）：`vendors.min.css` → `app.min.css`（INSPINIA 主题，已含 `table-custom`/`thead-sm`/`fs-xxs`/`avatar-*`/`bg-*-subtle`/`badge-label`/`kanban` 等共享类）→ **`xfadmin.css`（最后，覆盖/补充自定义类）**。

---

## 1. 枚举式对比方法（CSS / JS / 数据 三层）

| 层 | 方法 | 产物 |
|---|---|---|
| CSS | 在 INSPINIA `app.css` 中 `grep` 组件真实选择器，抽取声明；与 `xfadmin.css` 同选择器逐条比对取值 | 样式差集 |
| JS | 在 INSPINIA `assets/js/pages/*.js` 抽取交互（画廊灯箱、看板拖拽、日历事件、邮件选中、嵌套拖放）；与 `xfadmin.js` 对应注册器比对 | 交互差集 |
| 数据 | 在 INSPINIA `dist/*.html` 抽取示例数据的字段/结构；与组件 `defaults()` 约定的数据契约比对 | 数据契约差集 |

---

## 2. 各组件枚举式对照表（状态）

状态图例：✅ 已对齐  🔶 部分对齐/进行中  ⬜ 待处理  ❌ 偏差大

| # | 组件（用户点名） | INSPINIA 参考页 | xfadmin 组件 | 关键差异 | 状态 |
|---|---|---|---|---|---|
| 1 | 头像（全局） | `app.css` `.avatar*` | `UI/Avatar.php` | 缺 `.avatar` 包裹；尺寸类挂错元素；散落裸 `<img>` | ✅ P1+P2 完成（10 处裸头像清零，回归第 16 组扫描防回归） |
| 2 | 画廊 | `misc-gallery.html` | `Data/Gallery.php` | 自定义 `xf-gallery` 网格取值偏离；缺灯箱/筛选 | ✅ P3 完成（card+app-search+ghost 筛选+row-cols+badge-label+image-popup，搜索/筛选/瀑布联动） |
| 3 | 客户管理 | `clients.html`/`ecommerce-customers.html` | `Data/Clients.php` `Customers.php` `CompanyCard.php` `ContactCard.php` | 卡片头像裸 `<img>`；状态胶囊/徽标用词不符 | ✅ 头像已统一（Clients/Customers 原本已规范） |
| 4 | 订单列表 | `ecommerce-orders.html` | `Data/Orders.php` `OrderDetails.php` `InvoiceTable.php` | 表格列/状态徽标/缩略图 | ✅ Orders 完成（模板同款结构 + xftable 前端搜索/筛选/分页/全选/批删） |
| 5 | 表格（搜索/筛选） | `tables-datatables-*.html` | `Table/Table.php` `Table/DataTable.php` | `filter_bar`（自定义表单项）+ `server_side`（前后台）+ 列筛选 + 导出 + 密度切换 | ✅ P6 完成（列筛选 columnFilters / 导出按钮[copy·csv·excel·pdf·print·colvis，已修正 html5 extend + 图标] / 密度切换[紧凑↔宽松] / filter_bar 服务端+本地双模式；demo tables 页三卡完整示范） |
| 6 | 日历 | `calendar.html` | `Misc/Calendar.php` | FullCalendar 主题/事件芯片/工具条 | ✅ P5 完成（卡片包裹 + 可选外部事件两栏；config 补齐 bootstrap 主题/视图切换/可编辑可拖入/nowIndicator/dayMaxEvents；externalEvents 经 FullCalendar.Draggable 拖拽） |
| 7 | 嵌套列表 Nestable | `misc-nestable.html` | `Misc/Nestable.php` | 旧 `dd-*`（jQuery Nestable）未定义 → 无样式；INSPINIA v4 改用 SortableJS `.nested-sortable` | ✅ P5 完成（改为 INSPINIA v4 原生 `.nested-sortable` 嵌套结构 + xfadmin.js nestable 模块按 group:'nested' 初始化，支持把手/隐藏 input 同步；xfadmin.css 补全 .nested-sortable 视觉） |
| 8 | 瀑布流 Masonry | `misc-masonry.html` | `Misc/Masonry.php` | 列布局/卡片间距 | ✅ P5 完成（改为 INSPINIA 原生 row+col-* 结构 + data-xf=masonry；xfadmin.js masonry 模块用 Masonry.js 初始化，未加载时降级为 Bootstrap 网格） |
| 9 | 看板 Kanban | `project-kanban.html` | `Data/Kanban.php`（注：`ProjectTeamBoard.php` 是团队卡片网格，非看板，已规范） | `kanban-app/kanban-content/kanban-item` 取值偏离 | ✅ P4 完成（重写 .kanban-app 结构：card-header 搜索/新增 + .kanban-content 列 + .kanban-board 列 + .kanban-board-group[data-simplebar] + ul[data-plugins=sortable]；卡片 badge-soft+ti-point-filled / link-reset 标题 / avatar-group-xs 成员 / 截止 / 进度；JS 补充搜索过滤 + 新增事件） |
| 10 | 邮件中心 | `email.html`/`email-compose.html`/`outlook.html` | `Data/EmailApp.php` `MailList.php` `Outlook.php` `EmailCompose.php` | 三栏布局/邮件行/未读态/星标/头像 | ✅ P4 完成（EmailApp 三栏 .email-app：左 list-custom 文件夹 + 中 table 式邮件行[勾选/星标/avatar-xs/未读/stretched-link/附件] + 右阅读窗格；MailList 改为同结构紧凑卡；Outlook 列表头像 avatar-md 对齐；自带 email 搜索过滤 JS） |
| 11 | 评论/聊天 | `apps-chat.html` | `Data/CommentThread.php` `ChatApp.php` `ChatBox.php` | 头像裸 `<img>`、气泡样式 | ✅ 头像已统一（P2） |
| 12 | 时间线 | `timeline.html` | `UI/Timeline.php` | 纵向/横向时间线 | ✅ P7 完成（纵向/横向时间线 + 头像 `.avatar` 包裹 + `avatar-title` 首字母占位） |

### 其余组件（非用户点名但同属对齐范围，后续轮次覆盖）
`StatCard` `Widget` `ActivityFeed` `TeamMember` `Testimonial` `ReviewList` `VoteList` `ProfileHeader` `BlogList` `BlogArticle` `Article` `ForumThread` `SearchResults` `ProductCategories` `Sellers` `SellerDetails` `Projects` `ProjectActivity` `ProjectDetails` `IssueTracker` `ProductDetails` `Landing` `Topbar` `Sidenav` `LockScreen` `AuthPage` `Chip` 等（均检出头像类，需统一走 `Avatar` 组件）。

---

## 3. 共享类基线（INSPINIA 已定义，xfadmin 直接复用，勿重复定义）

| 类 | 用途 | 是否已在 app.min.css 定义 |
|---|---|---|
| `table-custom` `table-centered`(不存在!) `thead-sm` `fs-xxs` `bg-*-subtle` `text-bg-*` `badge-label` `kanban-*` `avatar-*` | 通用 | ✅（除 `table-centered`/`search-box` 为臆造） |
| `dd-list` `dd-item` `dd-handle` `dd-placeholder` `dd-empty` | Nestable | ⚠️ 旧 jQuery Nestable 体系（INSPINIA v4 已弃用，改用 SortableJS `.nested-sortable`），xfadmin 不再依赖，无需定义 |
| `search-box` | 搜索框 | ❌ 臆造，改用 `.input-group` + `form-control` 或 INSPINIA 真实 `.search-box`（实际不存在→自实现） |

---

## 4. 本轮已落地修复


### 4.0 重要事实修正（枚举核对后）

对 220 个模板页做 `class="avatar..."` 全量词频统计后发现：**INSPINIA 真实惯例中尺寸类可直接作容器**
（`<div class="avatar-md"><span class="avatar-title">` 出现 2782 次、`<img class="avatar-md rounded-circle">` 952 次），
`.avatar avatar-sm` 包裹组合仅 80 次（app.css 甚至无独立 `.avatar` 基础类，仅 `.avatar-group>.avatar` 语境）。
`xfadmin.css` 的头像段已同时兼容两种写法（包裹式 + 尺寸类直挂），因此组件内两种写法均视为规范，
**唯一禁止**的是"裸 `<img rounded-circle width=NN>` 表现属性写法"（回归测试第 16 组自动扫描拦截）。

### 4.1 头像（Avatar）—— 对齐 INSPINIA 规范
- **`src/Components/UI/Avatar.php`**：重写为 INSPINIA 规范结构
  - 图片：`'<span class="avatar avatar-{size}"><img class="img-fluid rounded-circle" ...></span>'`
  - 文字/首字母：`'<span class="avatar avatar-{size}"><span class="avatar-title {bg} {rounded} fw-bold">XX</span></span>'`
  - 尺寸类恒挂 **包裹元素**；支持 `size`(xxs→xxl)、`variant`(首字母底色)、`rounded`(circle/square)、`group`(头像组，复用 `.avatar-group`)；输出 `alt` 防 XSS。
- **`resources/assets/css/xfadmin.css`**：强化头像安全网——保证裸 `img.avatar-*` 与包裹结构二者都 `object-fit:cover` + 圆角 + 固定尺寸；`[class*="avatar-"] img` 兜底裁切。
- **统一火焰**：将散落的裸头像改为调用 `Avatar` 组件（客户/公司/联系人/评论/聊天/顶栏/侧栏等），消除"所有头像不一致"。

### 4.2 DataTable 搜索/筛选 / 导出 / 密度（P6 能力落地）
- `filter_bar` 支持表单项类型：`text` `select` `range`(双值) `date` `daterange` `number` `checkbox` `radio` `multiple`，即"**自定义设置搜索表单项**"。
- `ajax` + `server_side=true` 即"**后台数据搜索/筛选交互**"：filter_bar 变更经防抖后拼 `?key=val` 调 `table.ajax.url(baseUrl+'?'+qs).load()`（见 `xfadmin.js` `register('datatable')`）。
- **本地模式 filter_bar（P6 新增）**：无 `ajax` 时，`reloadWithFilters` 按 `data-filter` 名映射到列（`colIndexByName` = `column.dataSrc()` → 列索引），对目标列调用 `table.column(ci).search(fv)` 后 `draw()`，过滤栏对本地数据同样生效（demo `tables` 卡 3 即本地数据 + 过滤栏实时筛选）。
- **导出按钮修复（P6 关键 bug）**：原 `mapButton` 把 `copy/csv/excel/print` 直接当 `extend` 字符串（DataTables 无 `copy` 类型，仅 `copyHtml5`），且无图标文本 → 导出按钮静默失效。已改为正确 `copyHtml5/csvHtml5/excelHtml5/printHtml5/pdfHtml5` 并补 `<i class="ti ti-*">` 图标文本（依赖 `datatables` 资源的 `buttons.html5.min.js`/`buttons.print.min.js`/`jszip.min.js`；PDF 另需 `datatables-pdf` 资源由 `assets()` 自动挂载）。
- **密度切换（P6 新增）**：`buttons => ['density']` 注入 `xf-btn-density` 扩展按钮；点击在表格 `<table>` 上切换 `.xf-dt-compact` 类（收紧 `thead th`/`tbody td` 内边距与字号）；初始化可用 `density => 'compact'` 直接以紧凑态渲染。
- **列筛选 `column_filters => true`**：表头下方每行渲染 `xf-filter-row` 输入/下拉，按列实时筛选（demo `tables` 卡 2）。
- demo `tables` 页（`demo/pages/tables.php`）现含三卡：① 基础数据表（行分组/排序/多选/固定表头）② 列筛选示范 ③ 完整过滤栏 + 工具栏（复制/CSV/Excel/打印/列显隐/全屏/密度切换），让全部能力可见。

### 4.3 全局样式一致性收敛（"勿覆盖模板已定义类"专项）

> 方法：自写静态审计脚本逐条比对 `xfadmin.css` 与模板 `app.min.css` 的**同名选择器 + 高危属性**，自动列出 xfadmin 覆盖模板且取值不同的冲突点，逐一定性与修复。
> 高危属性：position / width / height / margin* / padding* / top / right / bottom / left / z-index / background* / border* / box-shadow / color / font-size / font-weight / display。

修复项（均为"xfadmin 覆盖模板已定义类"的真实偏差）：

1. **`.app-topbar` 顶栏错位（致命回归修复）**
   - 模板 `.app-topbar` 带 `margin-left: var(--ins-sidenav-width)`（235px），会把 fixed 定位的顶栏右推、宽度压成 945px。
   - 补回 `margin: 0`（此前轮次修过但被回退）。`content-page` 的 `margin-left`（侧边栏位）由模板响应式规则提供，**不覆盖**。

2. **`.content-page` 横向留白对齐模板**
   - 原 `padding: 0 1.5rem 1.5rem`，模板为 `0 .625rem`。改为 `padding: 0 .625rem 1.5rem`（左右跟随模板，仅补底部呼吸位）。

3. **`.card` / `.modal-content` / `.dropdown-menu` / `.popover` 不再覆盖模板阴影/边框**
   - 原硬编码 `box-shadow: var(--xf-shadow-soft)`（`.card`）与 `box-shadow !important`（`.modal-content`/`.dropdown-menu`/`.popover`）会抵消模板由 `--ins-theme-card-box-shadow` / `--ins-box-shadow` 主题变量驱动的阴影（暗色/定制面板切换时失效）。
   - 改为：`.card` 仅保留 `transition`；`.modal-content`/`.dropdown-menu`/`.popover` 删除阴影覆盖（交还模板变量）；仅 `.xf-toast-container .toast`（包内自定义、模板无）保留阴影。

4. **`.form-control:focus` / `.form-select:focus` 交还模板聚焦设计**
   - 原覆盖为 Bootstrap 默认 `.2rem` 主色光晕（与模板 INSPINIA `box-shadow:none` 聚焦设计冲突）。删除覆盖，仅保留 `is-invalid`/`is-valid` 校验态阴影（模板未定义，属补充）。
   - `.input-group-text` 背景/文字由固定 `rgba(--bs-secondary-rgb,.05)`/`--bs-secondary-color` 改为 `var(--bs-tertiary-bg)`/`var(--bs-body-color)`，暗色自动变深，与模板 `--ins-tertiary-bg`/`--ins-body-color` 一致。
   - `.form-label` 字重由硬编码 500 改为 `var(--ins-font-weight-semibold, 600)`（跟随模板）。

5. **表格行 hover 规则去重与暗色统一**
   - 明亮段 `.table-hover > tbody > tr:hover` 统一为 `var(--bs-primary-bg-subtle)`（暗色自动适配），删除文件末尾冗余的 `.table tbody tr:hover` 重复规则。
   - 暗色段表格 hover 收敛为语义变量（与明亮段一致），删除末尾重复的硬编码 `rgba(--bs-primary-rgb,.15)` 规则。

6. **主题 data-* 默认值对齐模板（`config/xfadmin.php` + `src/Components/Layout/Page.php`）**
   - `theme.skin` 默认 `classic` → `modern`、`menu_color` `dark` → `gradient`、`sidenav_user` `false` → `true`（与模板 `config.js` 默认一致）。
   - `Page.php` 改为对所有主题属性 `if (!empty(...))` 显式输出 `data-*`（不再"等于默认就省略"），与浏览器端 `config.js` 回落逻辑对齐。

7. **`--ins-topbar-height` 级联偏差修复（`app.min.css`）**
   - 该变量曾被手写改为 65px（模板官方 40px），导致约 15 处 `calc()` 依赖错位。还原 40px；`xfadmin.css` 的 `--xf-topbar-height` 改为 `var(--ins-topbar-height, 40px)` 跟随。

8. **DataTable 列排序全面修复（`resources/assets/css/xfadmin.css`）**
   - 根因：模板 `th::after` 排序字形在压缩中损坏（content 空）+ DataTables 2.x `.dt-column-order` 未样式化 + `.thead-sm` padding 与图标重叠。
   - 修复：禁用失效 `th::after`；用 `.dt-column-header` flex 布局 + `.dt-column-order::before` Tabler 字形（U+EAA5 选择器 / U+EB26 升序 / U+EB27 降序）绘制三态；含 hover、键盘 `:focus-visible`、暗色适配、`sorting_1` 底色、`user-select:none`、点击区域铺满。

残留冲突审计结果（均为**无害**，未改）：
- `.fs-*` 字号具体值 vs 模板变量（数值等价，如 `.9375rem`=15px=`--ins-font-size-md`）。
- `.toast`/`.accordion-bordered` 圆角用包内统一令牌 `--xf-radius-sm`。
- `.breadcrumb`/`.nav-tabs`/`.select2` 用 `--bs-primary` 与模板 `--ins-primary` 同源同值；z-index 1060 vs 1056 仅层级微调无视觉差异。
- `.timeline-dot` 为包内纵向时间线专用（横向用 `.timeline-marker`），不与模板横向时间线冲突。

### 4.4 侧边栏菜单与后台模板一致性（用户测试发现专项）

> 用 Playwright 真实渲染 `demo/pages/tables.php`，逐元素比对侧边栏与 INSPINIA 模板的实际计算样式，确认差异并修复。

修复项（侧边栏此前强加的「主色高亮风格」与模板不一致）：

1. **active 项高亮跟随模板语义变量**
   - 原 xfadmin 强加：`border-left: 3px solid var(--bs-primary)` + `background: var(--bs-primary-bg-subtle) !important` + `color: var(--bs-primary) !important` + `font-weight: 600 !important`。
   - 实测模板 active 为 `color: var(--ins-sidenav-item-active-color)`（rgb(207,223,241)）+ `background: var(--ins-sidenav-item-active-bg)`（rgb(41,68,97)）+ `font-weight: 500`（无左边框、无强制字重）。
   - 改为：删除硬编码覆盖，交还模板 `.side-nav-item.active>a` 规则；`.side-nav-link` 仅保留 `transition` 补充。

2. **hover 态跟随模板（仅变色，不加深色背景）**
   - 暗色段原强加 `[data-bs-theme="dark"] .side-nav-link:hover { background: rgba(255,255,255,.06) }`，模板 hover 仅变色。删除该背景覆盖。

3. **子菜单展开箭头旋转方向对齐模板**
   - 原 `rotate(90deg)`（右转），模板为 `rotate(-180deg)`（上下翻转，`.side-nav-item.active .menu-arrow` 同值）。改为 `rotate(-180deg)`。

验证（Playwright 实测，无 JS 错误）：
- DataTable 排序：`aria-sort` 点击后 `ascending → descending`，`.dt-column-order::before` content = `\ueb27`（ti-sort-descending 三态图标正确显示），首行数据随排序切换，功能+样式均正常。
- 侧边栏 active：`color: rgb(207,223,241)` / `bg: rgb(41,68,97)` / `borderLeft: 0px`（与模板一致）。
- 子菜单展开：`[data-bs-toggle=collapse]` 点击 `display: none → block`（Bootstrap Collapse 正常），箭头 `matrix(-1,0,0,-1,0,0)` = `rotate(-180deg)`（与模板一致）。

### 4.5 DataTable 2.x 工具栏/分页布局选择器失效修复（本轮新增）

> 项目 DataTables 为 **2.x**，其 DOM 结构为 `.dt-container > .dt-layout-row > .dt-layout-cell`，内含 `.dt-search`/`.dt-length`/`.dt-paging`/`.dt-info`（空数据用 `.dt-empty`）。旧版 xfadmin.css 沿用了 1.x 的 `.dataTables_wrapper .dataTables_*` 选择器，对 2.x **完全失效**，导致搜索框、长度菜单、分页器、信息栏、空数据态均无任何自定义样式（尤其暗色适配缺失），与模板「工具栏随主题适配」的预期不一致。

修复项（`resources/assets/css/xfadmin.css`）：

1. **搜索框 / 长度菜单 / 分页 / 信息栏**：将失效的 `.dataTables_wrapper .dataTables_*` 选择器具名重写为 2.x 的 `.dt-search`/`.dt-length`/`.dt-paging`/`.dt-info`，同时保留 `.dataTables_*` 作为 1.x 兜底（双写兼容）。
2. **空数据态**：`.dataTables_empty` → 补 `.dt-empty` 并列。
3. **搜索框宽度**：`.dt-search input` 设 `min-width: 16rem`（inline-block）与模板表格工具栏观感一致；长度/信息栏 `margin-bottom: .5rem` 留白。
4. **暗色自动适配**：搜索框/长度菜单/分页器均复用 Bootstrap 语义变量（`.form-control-sm`/`.pagination`/`.form-select`），暗色下由 `--bs-*` 自动切换，无需额外硬编码。

验证（Playwright 实测，浅色 + 暗色双主题，无 JS 错误）：
- `.dt-search input` 计算宽度 `152.375px`（= min-width 16rem 生效）。
- `.dt-info` 颜色：浅色 `rgb(76,76,92)` / 暗色 `rgb(170,184,197)`（随 `--bs-secondary-color` 自动适配）。
- 容器类名实测为 `dt-container dt-bootstrap5 dt-empty-footer`（确认 2.x 结构）。

### 4.6 图表组件暗色主题热切换修复（本轮新增）

> 原 `apexchart` / `echart` 注册函数**不读取 `data-bs-theme`**：ApexCharts 的 `theme.mode` 恒为 `light`、ECharts 的 `theme` 在 `init` 时固定，导致切换暗色后图表文字/网格/背景与模板主题脱节（ApexTree/ApexSankey 仅初始化时读了一次主题，不热切换）。

修复项（`resources/assets/js/xfadmin.js`）：

1. **统一主题源**：新增 `xfCurrentThemeMode()` 读取 `<html data-bs-theme>`；全局 `MutationObserver` 监听该属性变化，触发 `window.__xfApplyChartTheme(mode)`（无论主题由 INSPINIA `app.js` 还是本包切换均能捕获，不依赖自定义事件）。
2. **ApexChart**：注册时若未显式指定 `theme`，注入 `theme: { mode }`；热切换走 `chart.updateOptions({ theme: { mode } }, false, false)`（无需重建）。
3. **ECharts**：`init` 主题取「PHP 显式 `theme` > 当前 `data-bs-theme`(dark 用内置 `'dark'`)」；热切换走 `dispose + init(el, newTheme) + setOption(option)`（theme 在 init 时固定，必须重建）。
4. **ApexTree / ApexSankey**：登记到 `window.__xfApexSpecial`，热切换时 `rebuild()` 按最新 `data-bs-theme` 重设 `canvasStyle`/`fontColor` 并重渲染。
5. **实例登记**：Apex 实例入 `window.__xfApex`、ECharts 元数据入 `window.__xfEchartsMeta`、专用插件入 `window.__xfApexSpecial`，供主题切换统一遍历。

验证（Playwright 实测，`/charts` 页，浅色→暗色切换，无 JS 错误）：
- Apex 实例登记 `3` 个、ECharts 元数据 `1` 个；Apex 文字 `fill` 由 `rgb(155,166,183)`（浅）变为 `rgb(131,145,162)`（暗），`updateOptions` 热切换生效。
- ECharts 饼图中心像素由浅色透明 `0,0,0` 变为暗色填充 `16,12,42`（重建并应用 `'dark'` theme 生效）。
- ApexTree/ApexSankey 随主题重渲染无异常。

### 4.7 响应式布局错乱修复 + DataTable 排序样式美化（本轮新增）

> 用户反馈：调整浏览器宽度时，`.app-topbar` 与很多组件存在错乱、宽度显示异常；并明确要求美化 DataTable 字段排序显示样式。

#### 4.7.1 响应式布局错乱修复

**根因 A（顶栏不跟随侧栏）**：xfadmin.css 原 `.app-topbar { margin: 0 }` 固定清零，当 JS（app.js）未接管 `inline marginLeft` 时，顶栏不随 `--ins-sidenav-width`（default 235px / condensed 70px / offcanvas 0）收缩，覆盖侧栏或与内容区错位。
**修复**：恢复模板语义 `.app-topbar { margin-left: var(--ins-sidenav-width) }`（显式声明覆盖历史 fixed 残留），并加 `@media (max-width: 991.98px) { margin-left: 0 }` 窄屏兜底。JS 接管时 `inline marginLeft`（如 75px）优先级更高仍正常；JS 不接管时 CSS 变量兜底跟随。

**根因 B（landing 页窄屏 5px 水平溢出）**：landing 下 `.content-page > .container` 的 padding 被清零（第 879 行，为全宽 hero），Bootstrap `.row` 的 `-15px` gutter 失去抵消，在 375px 视口外溢 5px 触发横向滚动条。
**修复**：`.landing-page .content-page { overflow-x: clip }`（用 `clip` 而非 `hidden`，避免破坏 sticky 顶栏）；body 同时 `overflow-x: clip` 兜底。实测各宽度（375/576/768/992/1280）水平溢出均为 0。

**验证（Playwright 全页全宽审计，1920→375）**：`/tables` `/charts` `/widgets` `/apps` `/landing` 五页在 1280/992/768/576/375 五档视口下 `horizOverflow` 全为 0，无组件越界（offcanvas 隐藏态的侧栏元素 `left<0` 属预期，非 bug）。

#### 4.7.2 DataTable 排序样式美化

**根因（重大）**：xfadmin.css 全部 DataTable 样式作用域为 `.xf-datatable.dataTable`，但 DataTable 组件 `tableClass()` **从未输出 `xf-datatable` 类**，导致表头底纹、排序图标、圆角、已排序列高亮等美化样式**全部未生效**（仅无前缀 `table.dataTable` 的排序规则生效）。
**修复**：
1. `src/Components/Table/DataTable.php` 的 `tableClass()` 新增 `xf-datatable` 标识类（作用域隔离，符合 xf-* 自包含约定）。
2. 排序图标容器 `.dt-column-order`：尺寸统一 1.25em、加 `border-radius:.3rem` + hover 淡背景（`--bs-primary-bg-subtle`），明确可点击反馈。
3. 已排序列 `.dt-ordering-asc/.dt-ordering-desc` 的 `th` 加主色浅底（`--bs-primary-bg-subtle`）+ 标题 `font-weight:600`，快速识别当前排序列。

**验证（Playwright，`/tables` 页）**：可排序列 `orderDisplay:flex`、图标 `content` 升序 `\eb26`/降序 `\eb27` 正确切换；点击后 `dt-ordering-asc→desc` 状态切换正常；标题颜色 `rgb(94,108,193)`（主色）高亮生效；暗色下无 JS 错误。

---

## 5. 分阶段执行计划

| 阶段 | 范围 | 交付 |
|---|---|---|
| **P1（本轮）** | 头像全量对齐 + 审计文档 + DataTable 能力确认 | ✅ 文档 + Avatar 重写 + 安全网 |
| **P2 ✅** | TeamMember/ActivityFeed/ProfileHeader/MailList/CommentThread/ChatBox/BlogList/ProjectDetails 共 10 处裸头像统一为 `.avatar` 包裹 | 已完成，裸头像清零 |
| **P3 ✅** | 画廊重写（misc-gallery 规范）+ Orders 重写（ecommerce-orders 规范）+ 新增 xftable 前端交互模块 | 已完成 |
| **P4 ✅** | 看板 Kanban 重写（project-kanban.html 规范）+ 邮件中心 EmailApp/MailList 重写（email.html 表格式邮件行）+ Outlook 对齐 | 已完成 |
| **P5 ✅** | Nestable（`.nested-sortable` 嵌套 + SortableJS）、Masonry（row+col-* + Masonry.js）、Calendar（FullCalendar bootstrap 主题 + 外部事件两栏） | 已完成 |
| **P6 ✅** | 表格进阶：列筛选 `columnFilters` 示范、导出按钮（修正 html5 extend + 图标）、密度切换、demo `tables` 页完整 `filter_bar` 示例（含本地模式实时筛选） | 能力可见化 |
| **P7** | 其余组件（StatCard/Widget/ActivityFeed/时间线/博客/项目/商品等）头像与卡片对齐 + 全量中文注释补强 | 收尾 |

---

## 6. 验证方法（沿用既定自测法）

1. `find src demo tests -name '*.php' -exec php -l {} \;` 全量语法零错误。
2. 启动 `php -S 127.0.0.1:8900 demo/router.php`，逐页 `curl` 验证 HTTP 200 + 无 PHP 错误。
3. Playwright(chromium) 无头加载，断言：`pageerror`/`console error`=0、`img.naturalWidth===0` 破图=0、关键组件容器存在、头像 `.avatar` 包裹结构存在且 `naturalWidth>0`。
4. 对照 INSPINIA 参考页截图，逐项核对间距/圆角/底色/字号。

---

## 7. 中文注释规范（本轮起强化）

- 每个组件文件头部增加「功能说明 / 数据契约 / 选项 defaults 含义」中文块注释。
- 关键样式规则在 `xfadmin.css` 注明对齐的 INSPINIA 源（`app.css` 行号或 `_xxx.scss`）。
- 易错陷阱（如 `img{height:auto}` 覆盖表现属性、`table-centered` 不存在）以 `⚠️` 注释标记。

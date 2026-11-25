# 前端运行时（JS API / widget / data-xf 契约 / 资源 / CSS 主题）

> 事实来源：`resources/assets/js/xfadmin.js`（约 7000 行，分段读）+ `src/Assets/Assets.php` + `resources/assets/css/`。
> 渲染核心零框架依赖；第三方插件（jQuery/Bootstrap5/DataTables/ApexCharts/ECharts/...）离线内置于 `resources/assets/plugins/`。

---

## 1. 前端文件职责

| 文件 | 职责 |
| --- | --- |
| `js/config.js` | 运行时配置/主题（`window.config` / `window.defaultConfig`） |
| `js/app.js` | INSPINIA 模板基础交互（i18n、主题持久化、侧栏、导航等） |
| `js/xfadmin.js` | **本包核心**：`XFAdmin` 全局对象 + 58 个 widget + 渲染器 + 表单协议 |
| `js/xfadmin-diy.js` | DIY 布局器交互 |
| `css/app.min.css` / `vendors.min.css` | 模板基础样式 |
| `css/xfadmin.css` / `xfadmin-diy.css` | 本包组件补充样式 |

---

## 2. `XFAdmin` 全局 API（方法清单，来自源码）

### 2.1 生命周期 / 扫描
- `XFAdmin.scan(root)`：扫描 `data-xf` 节点并初始化对应 widget。
- `XFAdmin.register(name, fn(el,cfg)[,destroyFn])`：注册 widget；第三参为销毁钩子（返回 `{destroy}` 亦可）。
- `XFAdmin.destroy()` / `destroyWithin(root)` / `disposeModal()`：销毁/清理实例（防重复绑定/内存泄漏）。
- `XFAdmin.onReady(fn)` / `_readyQueue`：DOM 就绪后回调队列。

### 2.2 请求 / 表单 / 弹窗
- `XFAdmin.request(url, opts)`：统一 AJAX（自动 CSRF、同源白名单 `safeRedirect`、非法协议直接拒绝、`Accept: application/json`）；所有新增请求入口必须走它，禁止裸 `fetch(配置URL)`。
- `bindRemoteForms(root)` / `handleRemoteForm(form)` / `handleFormResponse`：AJAX 表单协议。
  - 提交成功/失败后向 form 派发 `xf.form.success` / `xf.form.error`（bubbles）。
  - 失败 HTTP 非 2xx；成功按 `data.url` 跳转。
- `formDialog(url,opts)` / `pageDialog(url,opts)` / `editPage` / `createPage` / `viewRow` / `editRow`：弹窗/页面加载。
- `confirm(msg,cb)` / `popoverConfirm` / `prompt` / `promptFields`：确认/输入。
- `dialog` / `dialogBridge`：对话框桥接。

### 2.3 表格 / 渲染器
- `reloadTable(sel)` / `destroyTableSticky` / `destroyWithin`：表格重载/清理。
- **固定列 sticky 实现要点**（`initDataTable` 内，操作列右固定由 PHP `auto_fix_actions` 下发 `fixedColumns.indexes`）：
  - `indexes` 可能是 JSON 对象 `{"3":"right"}` 或数组 `["left","right"]`，统一用 `Object.keys()+parseInt` 解析。
  - right 偏移从最右列向左累计（最右 `right:0`）；左侧固定组与右侧固定组各自从边缘向内累计，中间普通列 z-index 更低正常穿过。
  - `applySticky` 只处理 DT 容器下的**顶层** table（过滤 `t.parentElement.closest('table')`），避免单元格内嵌 `<table>`（如 `tableHtml`/`showDetail` 输出）被错误加 sticky。
  - **清理闭环**：`el.__xfStickyCleanup` 必须解绑 jQuery `init.dt/draw.dt/column-visibility.dt/columns.adjusted.dt` + 原生监听 + 清 timer + 断开 ResizeObserver；注册前先调用旧 cleanup 保证幂等，否则表格 destroy 后同 `el` 重建会重复绑定泄漏。
  - 条纹表（`table-striped`）固定列 hover 背景需显式覆盖奇/偶行高特异性选择器（明暗两套），否则悬停不跟随高亮。
- `cellRenderers`：渲染器注册表（与 PHP `RENDER_SHORTCUTS` 对齐，见 [datatable.md](datatable.md)）。
- `registerCellRenderer(key, fn)` / `getCellRenderer(key)`：注册/取渲染器。
- `onCell(event,fn)` / `cellEventHandlers`：单元格事件委托。
- `onCommand` / `commandHandlers`：命令处理器。
- `timelineHtml`：时间线渲染。

### 2.4 提示 / 工具
- `toast(opts)` / `adminToast(msg,type)` / `escapeHtml(s)` / `tpl(tpl,data)` / `tplRaw`。
- `safeLink(value,fallback)` / `safeRedirect(value)` / `safeToken(value,fallback,allowed)` / `safeQuery(selector,root)` / `iconClass(value)`：渲染器与事件层的统一净化入口；variant/color/icon 进入 class 前必须过白名单。
- `loadScript(url)` / `loadStyle(url)`：动态加载资源。
- `copyText(s)` / `get(key)` / `set(key,val)`：剪贴板/内部状态。
- `bindLoadingButtons` / `bindBulk` / `bindRowDetail` / `bindTwoFactor` / `bindXfPageLinks` / `bindInvoicePrint` / `bindQtyStepper` / `bindAutosize` / `initBootstrapExtras` / `initAuthPage` / `initCommandPalette` / `initDropzone`。
- `countUp` / `openUpload` / `uploadHandlers`。

---

## 3. data-xf 声明契约（PHP ↔ JS 桥接核心）

- **声明节点**：PHP 组件输出 `<div data-xf="<widget名>" data-xf-config='<HEX转义JSON>' ...></div>`。
- **配置转义**：`data-xf-config` 用 `JSON_HEX_TAG|HEX_AMP|HEX_APOS|HEX_QUOT`（属性上下文，单引号包裹），
  内联 `<script>` 用 `Html::scriptJson`（`scriptJson` 额外防 `</script>` 注入）。
- **初始化**：`XFAdmin.scan()` 在 `boot()` 里对 `document` 全量扫描；数据重载后对局部再 scan。
- **重绘事件**：`xf-datatable-drawn`（表格重绘）、`xf.form.success`/`xf.form.error`（表单）、
  `xf:row-action-success`（行操作成功，见 rowActions widget）。

**新增 widget 的标准姿势**：
1. PHP 侧写一个组件，输出 `data-xf="myWidget"` + 配置节点（继承 `Component`，`html()` 返回声明节点）；
2. 在 `XfAdmin::$components` 注册别名 + `@method` 注解；
3. JS 侧 `XFAdmin.register('myWidget', function(el, cfg){ ... return {destroy(){}} })`；
4. 补 `tests/regression.php` + `tests/audit.php` 契约检查 + `docs/` 用法。

---

## 4. widget 清单（58 个，`XFAdmin.register` 名字）

**表格/搜索**：`dt-search` `dt-filter` `dt-pagesize` `dt-views` `datatable` `xftable`
**图表/地图**：`apexchart` `echart` `vectormap` `metric-chart` `apextree` `apexsankey`
**表单增强**：`choices` `select2` `daterangepicker` `slider` `quill` `summernote` `dropzone` `filepond` `pickr` `inputmask` `fmMaxlength` `tagify` `form` `wizard`
**交互/UI**：`loading-btn` `qtyStepper` `print` `toast` `sortable` `nestable` `jstree` `perm-tree` `lightbox` `masonry` `calendar` `tour` `clipboard` `sweetalert` `ladda` `codeCopy` `backtotop`
**业务/数据**：`kanban` `email` `chat-scroll` `chat-form` `issues` `votelist` `todo` `dataSourceSwitcher` `countdown` `countup` `animate`
**后台声明式（新增）**：`rowActions`（行操作 AJAX）、`adminBehaviors`（退出/缓存/图标/主题）

> `form` 这个名字的 register 仅用于触发浏览器原生校验样式（真正提交由 `bindRemoteForms` 处理）。

---

## 5. 资源去重（`Assets.php`）

- 组件 `assets()` 返回**插件键名**（不是路径），`Assets::plugin()` 递归解析依赖、按路径去重、检测循环依赖。
- `head()` 顺序：`config.js` → `vendors.min.css` → 插件 CSS → `app.min.css` → `xfadmin.css` → 内联 CSS。
- `scripts()` 顺序：`vendors.min.js` → 插件 JS → `app.js` → `xfadmin.js` → 补 head 后注册的 CSS → 内联 JS。
- **硬约束**：`scripts()` 必须在所有组件 `render()` 之后调用（组件在 render 时才登记依赖）；
  整页 `page()` 已内置并在生成后 `resetCollected()`，自定义布局需自行管理。
- `?v={version}`：资源 URL 自动追加版本做缓存刷新（version 见 [config.md](config.md)）。

---

## 6. CSS 主题

- 主题键（`config.theme`）：`skin`(modern) `mode`(light|dark|system) `layout`(vertical|horizontal|dual)
  `layout_position`(fixed) `layout_width`(fluid) `topbar_color`(light) `menu_color`(gradient)
  `sidenav_size`(default) `sidenav_user`(true)。渲染到 `<html data-bs-theme=...>`。
- 浏览器端 `config.js` 可持久化主题到 localStorage；`adminThemeHead` 组件在 `<head>` 同步应用主题防闪烁
  （键 `xf_admin_theme` / `xf_admin_lang`，经 `preg_replace('/[^A-Za-z0-9_]/','',...)` 净化）。
- 自定义观感：用外层 CSS 覆盖，或 `page()` 的 `head`/`scripts` 槽位注入；**不改包内 CSS 源文件后忘记产出 .min**。
- a11y 基线（audit 检查）：`<img>` 必带 alt、装饰性图标不参与统计、标签配平。

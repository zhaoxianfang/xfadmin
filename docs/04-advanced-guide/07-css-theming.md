# 样式与主题

## 1. 样式文件与加载顺序

| 文件 | 说明 |
|---|---|
| `css/vendors.min.css` | 第三方库样式（Bootstrap 基础 + 插件） |
| `css/app.min.css` | **INSPINIA v4.1.0 主题**（定义 `--ins-*` 变量与大量组件类） |
| `css/xfadmin.css` | **包内自定义样式**（最后加载，可覆盖框架） |

`Assets::head()` 中顺序：`vendors.min.css` → 插件 CSS → `app.min.css` → `xfadmin.css`。

## 2. 类命名规范

| 前缀 | 归属 | 示例 |
|---|---|---|
| `xf-*` | 包内自有组件 | `.xf-toast-container`、`.xf-kanban-*`、`.xf-wizard-*`、`.xf-stepper-*`、`.xf-filter-bar`、`.xf-lbtn*`、`.xf-invoice-list`、`.xf-vote-box`、`.xf-todo-*`、`.xf-code-block`、`.xf-cmd-*`、`.xf-2fa-*`、`.xf-qty-*` |
| `xf-dt-*` | DataTable 增强 | `.xf-dt-toolbar`、`.xf-dt-bulk`、`.xf-dt-compact`、`.xf-dt-sticky`、`.xf-dt-detail-col`、`.xf-dt-select-col`、`.xf-dt-group-row`、`.xf-dt-search-form` |
| `xf-cell-*` | 单元格渲染器产物 | `.xf-cell-qr`、`.xf-cell-switch`、`.xf-cell-input`、`.xf-cell-select`、`.xf-cell-images`、`.xf-row-actions` |
| `xf-view-*` | `viewRow` 详情弹窗 | `.xf-view-kv`、`.xf-view-tl`、`.xf-view-stat`、`.xf-view-section` |
| `xf-rich-*` | rich 渲染器 | `.xf-rich-cell`、`.xf-rich-title`、`.xf-rich-badge` |
| `dt-*` | DataTables 2 原生容器（非本包定义，仅做样式适配） | `.dt-container`、`.dt-scroll-head`、`.dt-scroll-body`、`.dt-processing`、`.dt-empty` |
| `side-nav*` / `sidenav-*` | 左侧导航 | `.side-nav`、`.side-nav-item`、`.side-nav-link`、`.sub-menu`、`.menu-icon`、`.menu-arrow`、`.sidenav-menu`、`.sidenav-toggle-button` |
| `topnav-*` / `topbar-*` / `app-topbar` | 顶部导航 | `.topnav-inline`、`.topnav-toggle-button`、`.topbar-menu`、`.nav-user`、`.notify-item` |
| `auth-*` | 认证页 | `.auth-page-wrapper`、`.auth-box`、`.auth-brand*`、`.auth-card-media`、`.auth-split-media--primary`、`.auth-lock-*`、`.two-factor` |
| `kanban-*` | 看板外壳 | `.kanban-content` |
| 无前缀工具 | 尺寸/软色补充 | `.fs-12/13/15/…`、`fs-xxs…fs-xxl`、`.w-sm/md/lg`、`.border-dashed`、`.btn-soft-{color}`、`.badge-soft-*`、`.card-action`、`.avatar-group` |

## 3. 设计令牌（CSS 变量）

### 3.1 Bootstrap 桥接层

`xfadmin.css` 顶部把 Bootstrap 变量桥接到 INSPINIA 的 `--ins-*`：

```css
:root {
    --bs-primary: var(--ins-primary, #3e60d5);
    /* … 共 20+ 项 … */
}
```

### 3.2 包内自有令牌

| 变量 | 说明 |
|---|---|
| `--xf-radius` / `--xf-radius-sm` | 圆角 |
| `--xf-shadow` / `-sm` / `-lg` / `-soft` / `-card` / `-hover` | 阴影层级 |
| `--xf-sidebar-width` / `-condensed` | 侧栏宽度 |
| `--xf-sidebar-bg(-dark)` / `-fg(-dark)` / `-active(-bg)` / `-border` | 侧栏配色 |
| `--xf-topbar-height` / `--xf-topbar-bg` | 顶栏 |
| `--xf-logo-h` | Logo 高度 |
| `--xf-bg-app(-rgb)` | 应用背景 |
| `--xf-spacer` | 基础间距 |

### 3.3 暗色模式

统一由 `[data-bs-theme="dark"]` 覆盖（重设上述令牌 + 组件级规则）。

## 4. 主题切换

### 4.1 服务端输出

```php
XfAdmin::page(['theme' => ['mode' => 'dark', 'menu_color' => 'dark'], ...]);
```

输出 `<html data-bs-theme="dark" data-menu-color="dark" …>`。

### 4.2 浏览器端切换

| 触发 | 行为 |
|---|---|
| `#light-dark-mode` 按钮 | 切换 `data-bs-theme`（light/dark/system） |
| 定制面板 radio | 切换 `data-skin` / `data-topbar-color` / `data-menu-color` / `data-sidenav-size` / `data-layout-position` |
| `#reset-layout` 按钮 | 重置为 `defaultConfig` |
| localStorage/sessionStorage | 持久化 `__INSPINIA_CONFIG__` |

### 4.3 可选值

| 属性 | 可选值 |
|---|---|
| `data-skin` | `classic` `material` `modern` `saas` `minimal` `flat` |
| `data-bs-theme` | `light` `dark` `system` |
| `data-topbar-color` | `light` `dark` `gray` `gradient` |
| `data-menu-color` | `light` `dark` `gray` `gradient` `image` |
| `data-sidenav-size` | `default` `compact` `condensed` `on-hover` `on-hover-active` `offcanvas` `full` `fullscreen` |
| `data-layout-position` | `fixed` `scrollable` |
| `data-layout-width` | `fluid` `boxed` |
| `data-layout` | `vertical` `horizontal`（水平导航时输出 `topnav`） |

## 5. 图标

统一使用 **Tabler 字体图标**（`ti ti-*`）：

```php
XfAdmin::button(['label' => '保存', 'icon' => 'ti ti-device-floppy']);
XfAdmin::statCard(['icon' => 'ti ti-shopping-cart', ...]);
```

⚠️ Topbar 早期版本用过 `data-lucide`（需 `window.lucide`），已全部改为 Tabler。
`app.js` 仍会调用 `lucide.createIcons()`（用于模板遗留页面）。

## 6. 自定义样式

### 6.1 追加自己的 CSS

```php
XfAdmin::assets()->css('css/my-theme.css');
```

或在 `Page` 的 `head` 参数中：

```php
XfAdmin::page([
    'head' => '<link rel="stylesheet" href="/css/my.css">',
    // 或
    'head' => '<style>.my-class{color:red}</style>',
]);
```

### 6.2 覆盖原则（重要）

⚠️ **不要重写 `app.min.css` 已定义的类**（尤其 `position`/`width`/`margin`/`z-index`/`background`/`padding`/`top`/`right`）。
新增样式前先确认 `app.min.css` 是否已定义该类。已定义的类若要调整，
只补充框架**未定义**的属性。

历史修复案例：

- 删除对 `.card` 的整体重写（框架已定义 box-shadow/border-radius）；
- `.card-radio` 不覆盖框架的 `padding:0`；
- `.topbar-badge` 不重写 `top`/`right`；
- `.app-topbar` 需显式 `margin:0`（框架设了 `margin-left: var(--ins-sidenav-width)` = 235px，会导致顶栏右移）；
- `.page-title-head` 需重置框架 `margin: 0 -1.25rem` 的横向出血；
- `.timeline-content` 需重置框架的 `position:absolute`（横向时间线泄漏到纵向时间线）。

## 7. 打印

```css
@media print {
    body.xf-printing { /* 仅打印 .xf-invoice-print-area，其余隐藏 */ }
}
```

由 `bindInvoicePrint` 触发：给目标加 `.xf-invoice-print-area`、
`body` 加 `.xf-printing`，然后 `print()`，`afterprint` 清理。
⚠️ 直接 Ctrl+P 时不受影响（避免整页空白）。

## 8. 响应式断点

| 断点 | 行为 |
|---|---|
| ≥1200px | 完整侧边栏 |
| 992–1199px | 侧边栏按配置（config.js 在 ≤1140px 强制 condensed） |
| <992px | 侧边栏变抽屉（`body.sidenav-open`）；TopNav 折叠按钮 `.topnav-toggle-button` 显示 |
| ≤767px | 侧边栏强制 `offcanvas` |

## 9. 常见问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 组件无样式 | 未输出 `head()`，或 `xfadmin.css` 404 | 检查资源 |
| 组件样式与模板不同 | `xfadmin.css` 覆盖了框架类，或框架属性"泄漏" | 检查是否重写了框架已有属性 |
| 暗色模式部分不生效 | 组件用了硬编码颜色 | 改用 CSS 变量 / `text-bg-*` |
| 顶栏右移 235px | 框架 `margin-left`，自定义布局未重置 | `.app-topbar { margin: 0 }` |
| 表格横向滚动条双重 | `.table-responsive` 与 DataTables scrollX 同时存在 | 组件已按规则互斥，检查是否手工加了包裹 |
| 打印空白 | 未加 `body.xf-printing` | 用 `bindInvoicePrint` 的按钮触发 |

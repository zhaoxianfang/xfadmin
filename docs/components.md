# 组件总览

所有组件通过 `XfAdmin::<name>(array $options)` 调用。下表列出全部组件、用途与关键参数。所有组件均支持传入 `id`、`class`、`attributes`（自定义 HTML 属性数组）等通用键。

> 每个组件的**数据输入 / 输出、前端控件（`data-xf`）与可复制用法示例**见 → [组件详细参考](components-reference.md)（由代码反射自动生成，覆盖全部 99 个组件）。

## 数据输入与输出约定

- **输入**：统一为关联数组 `XfAdmin::<name>(['key' => 'val'])`；也支持 `XfAdmin::<name>([])->key('val')` 链式设置（组件均提供同名 setter）。含用户数据的字段一律经 `Html::e()` 转义，防止 XSS。
- **输出（HTML）**：组件 `render()` 返回 HTML 字符串片段；整页组件（`page` / `authPage` / `errorPage`）返回完整 `<!DOCTYPE html>` 文档。
- **输出（CSS/JS）**：组件在渲染期声明所需插件资源，`XfAdmin` 自动去重登记到 `head()`（CSS）与 `scripts()`（JS + 初始化脚本）。
- **前端交互**：需 JS 行为的组件在根元素输出 `data-xf="<widget>"`，由 `xfadmin.js` 或组件内联 `XFAdmin.register` 初始化，并派发 `xf.<name>.*` 标准事件。

## XfAdmin 门面全局方法

除 `XfAdmin::<name>($options)` 渲染组件外，门面还提供以下全局方法：

| 方法 | 说明 |
|------|------|
| `XfAdmin::config(array $cfg)` | 运行时覆盖配置：`assets_url`、`version`、`theme`、`brand`、`defaults`、`footer` 等 |
| `XfAdmin::page(array $options)` | 快捷渲染整页并返回 `Page` 对象（可继续 `->head()` / `->scripts()`） |
| `XfAdmin::component(string $name, array $options = [])` | 以字符串别名动态创建组件实例（等价于 `XfAdmin::<name>()`） |
| `XfAdmin::extend(string $alias, string $class)` | 注册自定义组件类（需继承 `zxf\XfAdmin\Component`） |
| `XfAdmin::has(string $alias): bool` | 判断组件 / 别名是否已注册 |
| `XfAdmin::componentList(): array` | 返回全部已注册组件（别名 => 类名） |
| `XfAdmin::version(): string` | 返回扩展包版本号（常量 `XfAdmin::VERSION`） |
| `XfAdmin::asset(string $path): string` | 生成带版本号的资源 URL（`assets_url` + `?v=version`） |
| `XfAdmin::head(bool $full = true): string` | 输出 `<head>` 资源（CSS + 主题配置 `config.js`） |
| `XfAdmin::scripts(): string` | 输出页面底部脚本（JS + 初始化） |
| `XfAdmin::html(string $html): Component` | 包裹原生 HTML，使其可参与 `page()` 渲染 |

> **组件别名**：`dateRange` 与 `dateRangePicker`、`clipboard` 与 `clipboardButton` 互为别名，调用任一均可；下表统一以文档惯用名 `dateRangePicker` / `clipboardButton` 展示。

## 通用参数

| 参数 | 说明 |
|------|------|
| `id` | 元素 ID，缺省自动生成唯一 ID（保证多次调用不冲突） |
| `class` | 追加到根元素的 CSS 类（字符串或数组） |
| `attributes` | 额外 HTML 属性，如 `['data-x' => '1']` |

---

## 一、布局与页面

| 组件 | 说明 | 关键参数 |
|------|------|----------|
| `page` | 完整后台整页骨架 | `title` `layout` `menu` `user` `content` `page_title` `customizer` |
| `authPage` | 登录/注册等认证整页 | `title` `content` `card` `logo` `brand_side` |
| `errorPage` | 错误页（404/500） | `code` `title` `message` `image` `home_url` |
| `comingSoon` | 即将上线页（带倒计时） | `heading` `message` `deadline` `subscribe` |
| `maintenance` | 维护中页 | `heading` `message` `contact` |
| `emptyState` | 空状态占位 | `icon` `image` `title` `text` `action` |
| `lockScreen` | 锁屏整页（输入密码解锁） | `user`(`name`/`avatar`) `action` `heading` `brand` |
| `sidenav` | 左侧栏（含 logo、菜单、用户） | `menu` `user` `brand` |
| `topbar` | 顶部栏（搜索、通知、用户菜单） | `user` `notifications` `apps` `search` |
| `topnav` | 水平顶部导航（horizontal 布局） | `menu` |
| `pageTitle` | 页面标题 + 面包屑 | `title` `breadcrumb` `actions` |
| `footer` | 页脚 | `copyright` `links` |
| `customizer` | 右侧主题定制面板 | `options` |

布局详情见 [布局与页面](layout.md)。

---

## 二、栅格

| 组件 | 说明 | 关键参数 |
|------|------|----------|
| `row` | 栅格行 | `gutter` `cols`（列数组，元素可为组件或 `['width'=>['md'=>6],'content'=>...]`） |
| `col` | 栅格列 | `width`（`['md'=>6,'xl'=>3]`） `content` |

```php
XfAdmin::row(['gutter' => 3, 'cols' => [
    ['width' => ['md' => 8], 'content' => XfAdmin::card(['body' => '主'])],
    ['width' => ['md' => 4], 'content' => XfAdmin::card(['body' => '侧'])],
]]);
```

---

## 三、卡片与统计

| 组件 | 说明 | 关键参数 |
|------|------|----------|
| `card` | 卡片 | `title` `body` `footer` `tools`（`['collapse','refresh','close']`） `header_class` |
| `statCard` | 统计卡 | `title` `value` `icon` `variant` `trend` |
| `widget` | 多样式仪表盘小部件 | `style`(icon/progress) `title` `value` `icon` `trend` `progress` |

---

## 四、表格

| 组件 | 说明 |
|------|------|
| `table` | 静态表格（条纹/边框/悬停/响应式） |
| `dataTable` | 全功能表格（排序、搜索、分页、筛选、列显隐、导出 PDF/Excel/CSV、响应式） |

详见 [表格](tables.md)。

---

## 五、表单

| 组件 | 说明 |
|------|------|
| `form` | 表单容器（水平/垂直/行内布局、校验） |
| `input` `textarea` `select` `check` | 基础字段 |
| `slider` `dateRangePicker` `colorPicker` | 增强字段 |
| `editor` | 富文本（Quill） |
| `upload` | 文件上传（Dropzone） |
| `tags` | 标签输入（Tagify） |
| `maskedInput` | 输入掩码（Inputmask） |
| `wizard` | 分步向导（纯原生 JS） |
| `passwordStrength` | 密码强度计（规则清单 + 进度条，`minScore` 可联动禁用提交） | `name` `label` `showRules` `minScore` |

详见 [表单](forms.md)。

---

## 六、图表与地图

| 组件 | 说明 | 关键参数 |
|------|------|----------|
| `apexChart` | ApexCharts | `type` `series` `categories` `options`（原样合并） `height` |
| `eChart` | ECharts | `options`（完整 ECharts 配置） `height` |
| `vectorMap` | 矢量地图（jsVectorMap） | `map` `markers` `options` |
| `leafletMap` | Leaflet 交互地图（标记/圆/多边形；底图可离线关闭） | `markers` `circles` `polygons` `tiles`(null=离线) `center` `zoom` |

详见 [图表](charts.md)。

---

## 七、UI 组件

| 组件 | 说明 | 关键参数 |
|------|------|----------|
| `alert` | 提示条 | `variant` `message` `dismissible` `icon` |
| `badge` | 徽标 | `text` `variant` `pill` `soft` |
| `button` | 按钮 | `label` `variant` `size` `icon` `href` `loading` |
| `dropdown` | 下拉菜单 | `label` `items` `variant` `align` |
| `modal` | 模态框 | `id` `title` `body` `footer` `size` |
| `offcanvas` | 侧滑面板 | `id` `title` `body` `placement` |
| `tabs` | 选项卡 | `items`（`['title'=>..,'content'=>..,'active'=>..]`） `pills` `vertical` |
| `accordion` | 手风琴 | `items` `flush` `always_open` |
| `progress` | 进度条 | `value` `variant` `striped` `animated` `height` |
| `spinner` | 加载指示 | `variant` `size` `type`(border/grow) |
| `pagination` | 分页 | `current` `total` `url` `size` |
| `listGroup` | 列表组 | `items` `flush` `numbered` |
| `avatar` | 头像 | `src` `text` `size` `status` `shape` |
| `icon` | 图标 | `name` `size` `class` |
| `toast` | 通知（服务端预渲染） | `title` `message` `variant` |
| `timeline` | 时间线 | `items` |
| `carousel` | 轮播 | `items` `indicators` `controls` `interval` |
| `breadcrumb` | 面包屑 | `items` |
| `tooltip` | 文字提示 | `text` `title` `placement` `trigger` |
| `popover` | 弹出框 | `text` `title` `content` `placement` `trigger` |
| `placeholder` | 骨架占位 | `lines` `animation`(glow/wave) |
| `collapse` | 折叠 | `trigger` `body` `open` `horizontal` |
| `scrollspy` | 滚动监听 | `items` `height` `nav_width` |
| `ratio` | 响应式媒体容器 | `ratio` `src` `type`(iframe/video/content) |
| `rating` | 星级评分 | `value` `max` `variant` `count` |
| `ribbon` | 缎带角标 | `text` `variant` `position` `body` |
| `chip` | 标签胶囊 | `label` `avatar` `icon` `dismissible` |
| `stepper` | 步骤条（只读） | `steps`（`status`: done/active/pending） `vertical` |
| `descriptionList` | 描述列表（键值对） | `items` `horizontal` `label_width` |
| `loadingButton` | 加载/忙碌按钮（点击显示 spinner，防重复提交） | `text` `variant` `driver`(spinner/ladda) `type` |

---

## 八、业务/数据组件

| 组件 | 对应模板页 | 关键参数 |
|------|-----------|----------|
| `pricingCard` | pages-pricing | `name` `price` `features` `featured` `button` |
| `faq` | pages-faq | `items`（`['q'=>..,'a'=>..]`） `open` |
| `profileHeader` | pages-profile | `cover` `avatar` `name` `role` `stats` `tabs` |
| `productCard` | ecommerce-products | `image` `title` `price` `old_price` `rating` `badge` |
| `kanban` | project-kanban | `columns`（含 `cards`） `sortable` |
| `chatBox` | chat | `title` `messages` `input` |
| `invoiceTable` | invoice-details | `items` `summary` `currency` |
| `mailList` | email | `items` `checkbox` |
| `fileManager` | file-manager | `files`（`type`: folder/pdf/doc/…） `cols` |
| `activityFeed` | project-activity | `items` |
| `gallery` | pages-gallery | `items`(`src`/`title`/`group`) `masonry` `lightbox` `filter` `cols` |
| `blogList` | blog / blog-details | `items`(`image`/`category`/`author`/`tags`) `layout`(grid/list) `cols` |
| `invoiceList` | invoice | `items`(`id`/`client`/`amount`/`status`) `summary` `currency` |
| `searchResults` | pages-search-results | `query` `count` `items`(`title`/`excerpt`/`tags`) `filters` `pagination` |
| `permissionMatrix` | roles / permissions | `roles` `groups`(`权限分组`) `values` `readOnly` |
| `apiKeys` | api-keys | `items`(`name`/`key`/`created`/`last_used`) `reveal` `regenerate` |
| `commentThread` | article / forum-post | `items`(`user`/`text`/`replies`) `form` `maxDepth` |
| `emailCompose` | email-compose | `to` `subject` `body` `editor`(quill/textarea) `action` |

---

## 九、杂项组件

| 组件 | 说明 |
|------|------|
| `calendar` | 日历（FullCalendar） |
| `treeView` | 树形视图 |
| `nestable` | 可拖拽无限层级列表 |
| `lightbox` | 图片灯箱（GLightbox） |
| `tour` | 新手引导（Shepherd/driver） |
| `clipboardButton` | 复制到剪贴板 |
| `sweetAlert` | 弹窗提示（SweetAlert2，返回触发脚本） |
| `raw` | 原样输出任意 HTML（可携带资源依赖声明） |
| `tinycon` | 浏览器标签角标通知（favicon 未读计数） | `count` `color` `background` |
| `idleTimer` | 空闲计时器（无操作超时触发跳转/回调） | `timeout` `warn` `onIdleUrl` `onIdle` |
| `pdfViewer` | PDF 查看器（pdf.js，完全离线） | `url` `height` `toolbar` `download` |
| `textDiff` | 文本差异对比（jsdiff） | `old` `new` `mode`(inline/split) |

---

## 事件交互

需要 JS 行为的组件在前端由 `xfadmin.js` 统一初始化（`data-xf="..."`），并派发标准事件，便于你监听：

| 事件 | 触发组件 | detail |
|------|----------|--------|
| `xf.wizard.change` | wizard | `{ step }` |
| `xf.wizard.finish` | wizard | `{ step }` |
| `xf.kanban.move` | kanban | `{ from, to, oldIndex, newIndex, item }` |
| `xf.chat.send` | chatBox | `{ text }` |
| `xf.chip.close` | chip | — |
| `xf.countdown.end` | comingSoon | — |

```js
document.addEventListener('xf.kanban.move', (e) => {
    fetch('/api/kanban/move', { method: 'POST', body: JSON.stringify(e.detail) });
});
```

---

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

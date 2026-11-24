# 组件体系（注册表 / 基类 API / 通用参数 / 代表组件）

> 事实来源：`src/XfAdmin.php`（注册表）、`src/Components/Component.php`（基类）、各组件 `defaults()`/`html()`。
> 数量基线见 `index.md`（225 类 / 237 别名）。

---

## 1. XfAdmin 门面分发（`src/XfAdmin.php`，final 静态类）

所有 `XfAdmin::<组件名>(array $options)` 经 `__callStatic` → `component()`。

- **注册表**：`private static array $components = ['card' => Components\UI\Card::class, ...]`（237 别名）。
- `component($alias, $options)`：命中 → `$class::make($options)`；未命中做**大小写不敏感兜底**
  （`strcasecmp`，结果缓存）；仍失败抛 `InvalidArgumentException`（错误信息含全部可用别名）。
- **别名/驼峰容错**：`topnav`↔`topNav`、`dateRange`↔`dateRangePicker`、`clipboard`↔`clipboardButton`、
  `empty`↔`emptyState`、`diy`↔`diyLayoutPage`。
- **auth 语义别名**：`signIn/signUp/resetPass/newPass/twoFactor/deleteAccount/successMail/loginPin`
  未显式传 `type` 时自动注入对应 type（如 `signIn`→`sign-in`）。

**全局方法（门面直接调用）**

| 方法 | 作用 |
| --- | --- |
| `config(?array)` / `setting('a.b',$d)` | 递归合并/点号读取全局配置 |
| `component($alias,$options)` / `extend($alias,$class)` / `has($alias)` / `componentList()` | 工厂/扩展/查询/全表 |
| `version()` / `assets()` / `asset($path)` / `img($path)` | 版本/资源单例/资源URL/图片解析 |
| `head()` / `scripts()` | `<head>` CSS+主题脚本 / `</body>` 前 JS（顺序见 architecture.md） |
| `builder()` / `grid()` / `admin()` | 链式构建器 |
| `dataResponse($rows,$params,$options)` | DataTables 服务端协议响应（→ `DataSet::response`） |
| `setCsrfResolver($fn)` / `csrfToken()` | CSRF 令牌解析（Laravel 下由 Provider 接线） |
| `errorBlock()/emptyHint()/emptyDash()` | 行内错误/空数据片段 |
| `jsTag()/cssTag()/imgTag()/fontFace()/assetRoute()` | 资源引用（支持 `@路由名`/相对/绝对） |

---

## 2. 组件基类 API（`Component.php`）

### 2.1 通用控制参数（所有组件零改动即得）

| 键 | 类型 | 含义 |
| --- | --- | --- |
| `id` | string | 根元素 id |
| `class` | string | 根元素附加 class（自动去重） |
| `style` | string\|array | 内联样式（经 `safeStyle()` 净化） |
| `attrs` | array | 批量自定义属性 |
| `hidden` | bool | true 不渲染任何内容 |
| `when` | bool\|Closure | 条件渲染；闭包惰性求值（入参组件自身） |
| `visible` | bool | false 不渲染 |
| `col` | int\|array | 响应式栅格列宽（int→`col-md-N col-xl-N`；array→`['md'=>6,'xl'=>3]`） |

> 优先级 **hidden > when > visible**。`width` **不是**统一栅格保留字（ApexChart/StatCard 等用它表尺寸），栅格只用 `col`。

### 2.2 槽位渲染

- `raw($v)`：**不转义**槽位（字符串原样/闭包惰性/组件渲染/数组拼接）——用于 `body`/`content`/`footer`/`actions`/`tools`/`menu`。
- `text($v)`：语义纯文本但允许组件——字符串一律 `escape()`，组件/闭包/数组按 `raw()`。
- `img($path)`：图片地址解析（http(s)/`//`/`data:image/` 原样，其余走 `asset('images/')`，空回退 1×1 GIF，拦截伪协议）。
- `iconClass($v)`：图标 class 净化（仅 `[a-zA-Z0-9_\- ]`）。
- `safeUrl($v)`：链接白名单（放行 http(s)/`//`/`/`/`#`/`mailto:`/`tel:`/`data:image/`/`javascript:void(0)`，拦截危险伪协议）。

### 2.3 安全助手（防注入）

- `escape()` = `Html::escape()`（`htmlspecialchars(ENT_QUOTES|UTF-8)`；数组/对象→JSON；null→''）。`@deprecated e()` 等同。
- `enum($v,$allowed,$default)`：枚举白名单，非法回退默认。
- CSS 安全：`cssLen()` `cssRatio()` `cssColor()` `cssBackground()` `safeStyle()` `safeCssValue()`（拦截 `;{}`、`url(`、`expression(`、`<`）。
- 常量白名单：`ENUM_VARIANT` `ENUM_VARIANT_OUTLINE` `ENUM_SIZE(['sm','lg'])` `ENUM_PLACEMENT` `ENUM_BREAKPOINT`。

---

## 3. 九大分类代表组件选项（逐字来自 `defaults()`）

### 3.1 Layout（`Layout/`）
- **page**：`lang` `title` `description` `keywords` `favicon` `layout`(vertical|horizontal|dual) `theme`(array) `menu` `current_url` `sidenav`([]|false) `topbar`([]|false) `topnav` `page_title` `content` `container` `footer`([]|false) `customizer` `preloader` `head` `scripts` `body_class` `body_attrs` `csrf`。
- **authPage**（含语义别名）：`type` `layout`(base|card|split) `title` `heading` `subheading` `action` `ajax` `redirectDelay` `submit` `buttons` `fields`(关联数组，key=字段名) `captcha`(bool|string) `links` `below`/`above` `beforeForm`/`afterForm` `prepend`/`append` `content` `copyright`。`fields[*]` 可带 `before`/`after`。
- **errorPage**：`code`(400/401/403/404/408/500/maintenance) `title` `message` `links`。
- **sidenav**：`brand_name` `brand_url` `user`(name/email/role/avatar) `menu` `current_url`。
- **topbar**：`user` `languages[]` `notifications`(count/items/all_url)。
- **footer**：`text` `right` `icp` `icp_url` `police` `police_url` `police_icon` `links[]` `support` `brand_url` `groups[]`。
- **adminBehaviors**（新增）：`logout`{selector,url,token,method?} `refresh`{selector,url,token} `iconPicker`{trigger,url,inputTarget,previewSelector?,batch?} `theme`(true|array)。输出 `data-xf="adminBehaviors"` 声明节点。
- **adminThemeHead**（新增）：`storageKey`(xf_admin_theme) `langKey`(xf_admin_lang)。输出 `<head>` 防闪烁同步脚本。

### 3.2 Navigation（`Navigation/`）
- **menu**：`mode`(side) `items`[] `current_url`。每项：`text`(或 label/title) `icon` `url`(或 href) `badge`(string|array) `children`(递归) `id` `active` `disabled` `target`；仅含 `title` 无 url/children = 分组标题。

### 3.3 Grid
- **row** / **col**：栅格行列。

### 3.4 UI（`UI/`，62 类）
- **card**：`title` `subtitle` `tools`(['collapse','refresh','close']|自定义HTML) `actions` `body`(raw) `footer`(raw) `padding`(true) `hoverable` `header_class` `body_class` `title_tag`(h4)。
- **statCard**：`title` `value` `icon` `variant` `trend`([text,direction,label]) `url` `counter` `prefix` `suffix` `width`。
- **alert**：`variant` `text`(message/content) `heading`(title) `icon` `dismissible` `soft`。
- **modal**：`title` `body` `footer` `size`(sm|lg|xl|fullscreen) `centered` `scrollable` `fade` `static` `close` `trigger` `trigger_variant`。
- **dropdown**：`text` `variant` `size` `items[]`(text/url/icon/class/divider/header) `direction` `align` `split` `toggle` `menu`。
- **tabs**：`style`(tabs|pills|underline) `items[]`(title/label/icon/content/active/disabled/badge/id) `justified` `vertical` `fade` `footer` `form`。
- **toast**：`title` `body` `time` `variant` `autohide` `delay`(5000) `show` `placement`。
- **badge**：`text` `variant` `pill` `soft`。**button**：`text` `variant` `size` `icon` `loading` `outline` `disabled` `href`（注意是 href 不是 url）。
- **rowActions**（新增）：`selector` `table` `confirm_delete` `delete_class` `status_tpl` `ok_text` `fail_text` `token`。输出 `data-xf="rowActions"` 声明节点。
- **toolbar**：`left` / `right`（已渲染 HTML 按钮拼接；不是 items）。
- **descriptionList**：`items`(标签=>值) `horizontal` `label_width` `striped`。
- 其余：`progress` `spinner` `pagination` `listGroup` `avatar` `avatarGroup` `icon` `timeline` `carousel` `breadcrumb` `tooltip` `popover` `placeholder` `collapse` `scrollspy` `ratio` `rating` `ribbon` `chip` `stepper` `loadingButton` `backToTop` `statusTag` `statRow` `confirmDialog` `actionBar` `filterBar` `callout` `countdown` `countUp` `divider` `kbd` `media` `skeleton` `switch` `codeBlock` `empty` `searchBox` `colorPalette` `typography` `utilities` `iconSet` `videoEmbed` `commandPalette` `notificationCenter` `dropzoneUpload` `invoicePrintButton` `dataSourceSwitcher`。

### 3.5 Form（`Form/`，22 类 + trait）
- **form**：`action` `method`(POST) `enctype` `validation` `ajax`/`remote` `redirect` `reset` `reload`(true) `inline` `layout`(vertical|horizontal|inline) `label_width`(180) `fields[]` `content` `buttons`(raw) `csrf`(true|false|[name=>value]) `file`。
  > `ajax`/`remote` → `data-xf-remote`；成功契约 `reload`/`reset`/`redirect`（优先级 url > redirect > reload > reset）；含文件自动 multipart。
- **input**（fieldDefaults）：`name` `id` `label` `help` `required` `disabled` `readonly` `value` `placeholder` `wrapper` `feedback` `state` `type` `size` `mask` `tags` `prepend` `append` `min` `max` `step` `pattern` `autocomplete`。
- **select**：`options`([]|[['value','label','disabled']]) `groups[]` `multiple` `size` `enhance`(choices|select2) `enhance_options[]`。
- **upload**：`driver`(native|dropzone|filepond) `url` `multiple` `accept` `max_size`(MB) `text` `options[]`。
- **editor**：`driver`(quill|summernote) `theme` `height`(260) `options[]`。
- 其余：`textarea` `check`/`radio` `slider` `datePicker` `dateRangePicker` `colorPicker` `tags` `maskedInput` `wizard` `passwordStrength` `captcha`(image|math|slide) `twoFactorInput` `quantityStepper` `formElements` `formLayout` `formOtherPlugin` `formValidation`。字段通用包装见 `Form/Concerns/FieldWrapper.php`。

### 3.6 Table（`Table/`，4 类）
- **table**（静态）/ **tablesCustom**：简单表格。
- **dataTable**：见 [datatable.md](datatable.md)。
- **dataTableToolbar**：表格搜索/筛选/分页工具条。

### 3.7 Chart（`Chart/`，7 类）
- **apexChart**：`type` `series` `labels` `colors` `height` 等。
- **echart** `vectorMap` `leafletMap` `googleMap` `apexTree` `apexSankey`。

### 3.8 Data（`Data/`，92 类）
- **widget**：`style`(icon|progress|chart|minimal) `title` `value` `icon` `variant` `trend`([value,up,text]) `progress` `footer`。
- **metricCard**：`title` `value` `prefix` `suffix` `decimals` `trend` `trend_text` `chart`(donut|pie|bar|area|line|null) `data[]` `labels[]` `color` `icon` `footer` `url`。
- 其余 90+ 均为「数据填充型」业务组件（`kanban` `invoiceTable` `mailList` `fileManager` `permissionMatrix` `orders` `projects` …），键名以各自 `defaults()` 为准，调用前先读源码。

### 3.9 Misc（`Misc/`，17 类）
- `calendar` `treeView` `nestable` `lightbox` `tour` `clipboardButton` `sweetAlert` `raw` `tinycon` `idleTimer` `animate` `masonry` `pdfViewer` `textDiff` `pinBoard` `videoPlayer` `i18n`。

---

## 4. 全局辅助函数（`src/helpers.php`，autoload.files）

| 函数 | 等价 |
| --- | --- |
| `xf_admin($component=null,$options=[])` | 不传参→`XfAdmin::class`；传参→`XfAdmin::component()` |
| `xf_asset($path)` | `XfAdmin::asset($path)` |
| `xf_head()` | `XfAdmin::head()` |
| `xf_scripts()` | `XfAdmin::scripts()` |

> 在非 Blade 纯 PHP 上下文调用组件时，Blade 的 `e()` 可能不可用，组件内部已用 `htmlspecialchars`/`escape()`。

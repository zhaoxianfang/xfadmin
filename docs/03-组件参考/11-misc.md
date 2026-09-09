# 杂项与交互增强

> 日历、树形、拖拽排序、灯箱、引导漫游、剪贴板、SweetAlert、PDF 预览、文本对比、瀑布流、动画等。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`calendar`](#calendar) — 日历（FullCalendar 事件视图）
- [`treeView`](#treeview) — 树形视图（可展开节点）
- [`nestable`](#nestable) — 可拖拽排序列表（嵌套 sortable）
- [`lightbox`](#lightbox) — 灯箱（点击图片放大预览）
- [`tour`](#tour) — 新手指引漫游（分步高亮引导）
- [`clipboard`](#clipboard) — 剪贴板复制按钮（clipboard.js）
- `clipboardButton` — 等价于 `clipboard`（同一组件类的别名）
- [`sweetAlert`](#sweetalert) — SweetAlert2 弹窗（confirm/toast 等）
- [`raw`](#raw) — 原生 HTML 透传（不包裹任何结构的原样输出组件）
- [`tinycon`](#tinycon) — 动态 favicon 角标（未读消息数）
- [`idleTimer`](#idletimer) — 空闲计时器（用户无操作超时处理）
- [`animate`](#animate) — 入场动画（滚动触发元素动画）
- [`pdfViewer`](#pdfviewer) — PDF 预览（pdf.js 内嵌查看）
- [`textDiff`](#textdiff) — 文本差异对比（merge-diff 高亮）
- [`pinBoard`](#pinboard) — 钉板（瀑布流便签卡片）
- [`masonry`](#masonry) — 瀑布流布局（错落卡片墙）
- [`videoPlayer`](#videoplayer) — 视频播放器（plyr 等）
- [`i18n`](#i18n) — 国际化（多语言切换演示）

## 本章导读

杂项组件多为**交互增强**，依赖第三方插件并由 `data-xf` widget 自动初始化。

### widget 与依赖速查

| 组件 | data-xf | 依赖插件 |
|---|---|---
| `calendar` | `calendar` | `fullcalendar` |
| `treeView` | `jstree` | `jstree` + `jquery` |
| `nestable` | `nestable` | `sortablejs` |
| `lightbox` | `lightbox` | `glightbox` |
| `masonry` / `pinBoard` | `masonry` | `masonry` |
| `tour` | `tour` | `tourguide` |
| `clipboard` | `clipboard` | `clipboard` |
| `sweetAlert` | `sweetalert` | `sweetalert2` |
| `pdfViewer` | — | `pdfjs` |
| `textDiff` | — | `diff` |
| `tinycon` | — | `tinycon` |
| `animate` | `animate` | `animate`（CSS） |

### 约定与陷阱

- 依赖 jQuery 的组件（`treeView`、`nestable`）在无 jQuery 时静默降级；
- `raw` 组件用于原样输出任意 HTML（不做任何转义，**仅限可信内容**）；
- `idleTimer` 用于超时自动登出，需自行实现回调逻辑。


---

### `calendar`

日历（FullCalendar 事件视图）。

> **类**：`zxf\XfAdmin\Components\Misc\Calendar`
> **文件**：`src/Components/Misc/Calendar.php`（88 行）
> **依赖插件**：`fullcalendar`

**用法示例**

```php
XfAdmin::calendar([
    'events' => [['title' => '会议', 'start' => '2026-07-23', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary']],
    'editable' => true,
    'externalEvents' => [
        ['label' => '工作', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary'],
        ['label' => '私人', 'className' => 'bg-success-subtle text-success border-start border-3 border-success'],
    ],
    'options' => [ ... ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `events` | array | `[]` |  |
| `editable` | bool | `false` |  |
| `locale` | string | `'zh-cn'` |  |
| `externalEvents` | array | `[]` |  |
| `addText` | string | `'新建事件'` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `treeView`

树形视图（可展开节点）。

> **类**：`zxf\XfAdmin\Components\Misc\TreeView`
> **文件**：`src/Components/Misc/TreeView.php`（53 行）
> **依赖插件**：`jstree`

**用法示例**

```php
XfAdmin::treeView([
    'data' => [
        ['text' => '根节点', 'state' => ['opened' => true], 'children' => [
            ['text' => '子节点1', 'icon' => 'ti ti-file'],
        ]],
    ],
    'checkbox' => true,
    'dnd'      => true,      // 拖拽
    'options'  => [],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `checkbox` | bool | `false` |  |
| `dnd` | bool | `false` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `nestable`

可拖拽排序列表（嵌套 sortable）。

> **类**：`zxf\XfAdmin\Components\Misc\Nestable`
> **文件**：`src/Components/Misc/Nestable.php`（72 行）
> **依赖插件**：`sortablejs`

**用法示例**

```php
XfAdmin::nestable([
    'items'  => [
        ['content' => '设计阶段', 'id' => 1, 'children' => [
            ['content' => 'UI 设计', 'id' => 11],
            ['content' => '前端', 'id' => 12, 'children' => [['content' => '组件', 'id' => 121]]],
        ]],
        '开发阶段',
    ],
    'handle' => true,   // 渲染拖拽把手（.sort-handle）
    'input'  => 'order', // 隐藏 input 名，拖拽后写入逗号分隔的 id 序列
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `handle` | bool | `false` |  |
| `input` | mixed | `null` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `lightbox`

灯箱（点击图片放大预览）。

> **类**：`zxf\XfAdmin\Components\Misc\Lightbox`
> **文件**：`src/Components/Misc/Lightbox.php`（67 行）
> **依赖插件**：`glightbox`、`masonry`

**用法示例**

```php
XfAdmin::lightbox([
    'images' => [
        ['src' => '/big1.jpg', 'thumb' => '/small1.jpg', 'title' => '图一'],
        '/big2.jpg',
    ],
    'columns' => 3,
    'masonry' => false,
    'gallery' => 'gallery-a',   // 同名画廊内左右切换
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `images` | array | `[]` |  |
| `columns` | int | `3` | 列定义数组 |
| `masonry` | bool | `false` |  |
| `gallery` | string | `'xf-gallery'` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `tour`

新手指引漫游（分步高亮引导）。

> **类**：`zxf\XfAdmin\Components\Misc\Tour`
> **文件**：`src/Components/Misc/Tour.php`（46 行）
> **依赖插件**：`tourguide`

**用法示例**

```php
XfAdmin::tour([
    'steps' => [
        ['target' => '#menu', 'title' => '导航菜单', 'content' => '在这里切换功能模块'],
        ['target' => '#search', 'title' => '搜索', 'content' => '全局搜索'],
    ],
    'auto'  => true,     // 页面加载后自动开始
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `steps` | array | `[]` | 步骤数组（向导 / 步骤条） |
| `auto` | bool | `false` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `clipboard`

剪贴板复制按钮（clipboard.js）。

> **类**：`zxf\XfAdmin\Components\Misc\ClipboardButton` 等 `clipboard` / `clipboardButton`
> **文件**：`src/Components/Misc/ClipboardButton.php`（50 行）
> **依赖插件**：`clipboard`

**用法示例**

```php
XfAdmin::clipboard(['text' => '要复制的内容', 'label' => '复制']);
XfAdmin::clipboard(['target' => '#code-block', 'label' => '复制代码']);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义） |
| `target` | mixed | `null` | 链接打开方式，如 _blank |
| `label` | string | `'复制'` | 标签文案（表单字段标签 / 按钮文案） |
| `variant` | string | `'light'` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `success` | string | `'已复制！'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

### `clipboardButton`

`clipboardButton` 是 `clipboard` 的别名，指向同一个组件类 `zxf\XfAdmin\Components\Misc\ClipboardButton`，参数与用法完全一致。

---

### `sweetAlert`

SweetAlert2 弹窗（confirm/toast 等）。

> **类**：`zxf\XfAdmin\Components\Misc\SweetAlert`
> **文件**：`src/Components/Misc/SweetAlert.php`（74 行）
> **依赖插件**：`sweetalert2`

**用法示例**

```php
XfAdmin::sweetAlert([
    'trigger' => '删除',
    'trigger_variant' => 'danger',
    'title'   => '确定删除？',
    'text'    => '删除后不可恢复',
    'icon'    => 'warning',
    'confirm_text' => '删除',
    'cancel_text'  => '取消',
    'confirm_url'  => '/users/1/delete',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `trigger` | mixed | `null` | 触发方式（hover / click / focus），或触发按钮文案 |
| `trigger_variant` | string | `'primary'` |  |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `text` | mixed | `null` | 正文/按钮文案（纯文本语义，输出时转义） |
| `icon` | mixed | `null` | success \| error \| warning \| info \| question |
| `confirm_text` | string | `'确定'` |  |
| `cancel_text` | mixed | `null` | 非空则显示取消按钮 |
| `confirm_url` | mixed | `null` |  |
| `confirm_js` | mixed | `null` |  |
| `auto` | bool | `false` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `raw`

原生 HTML 透传（不包裹任何结构的原样输出组件）。

> **类**：`zxf\XfAdmin\Components\Misc\Raw`
> **文件**：`src/Components/Misc/Raw.php`（46 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::raw(['html' => '<div id="custom"></div>', 'plugins' => ['apexcharts'], 'js' => 'console.log("init")']);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `html` | string | `''` | 自定义 HTML（原样输出） |
| `plugins` | array | `[]` |  |
| `js` | mixed | `null` | 追加内联 JS（自动去重可传 js_key） |
| `js_key` | mixed | `null` |  |
| `css` | mixed | `null` |  |
| `css_key` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `tinycon`

动态 favicon 角标（未读消息数）。

> **类**：`zxf\XfAdmin\Components\Misc\Tinycon`
> **文件**：`src/Components/Misc/Tinycon.php`（47 行）
> **依赖插件**：`tinycon`

**用法示例**

```php
XfAdmin::tinycon(['count' => 5, 'color' => '#e63757']);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `count` | int | `0` | 数量 / 计数徽标数字 |
| `color` | string | `'#e63757'` | 颜色值（#hex / rgb() / 具名色） |
| `background` | string | `'#3e60d5'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `idleTimer`

空闲计时器（用户无操作超时处理）。

> **类**：`zxf\XfAdmin\Components\Misc\IdleTimer`
> **文件**：`src/Components/Misc/IdleTimer.php`（72 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::idleTimer([
    'timeout'  => 60,           // 秒
    'onIdle'   => 'alert("您已离开一会儿")',  // 客户端回调（谨慎使用，建议用 onIdleUrl）
    'onIdleUrl'=> '/lock',       // 触发时跳转
    'warn'     => 10,            // 提前多少秒提醒（0=不提醒）
    'warnText' => '即将因闲置而锁定',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `timeout` | int | `60` |  |
| `warn` | int | `0` |  |
| `warnText` | string | `'您已闲置，即将自动锁定'` |  |
| `onIdleUrl` | string | `''` |  |
| `onIdle` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `animate`

入场动画（滚动触发元素动画）。

> **类**：`zxf\XfAdmin\Components\Misc\Animate`
> **文件**：`src/Components/Misc/Animate.php`（73 行）
> **依赖插件**：`animate`

**用法示例**

```php
XfAdmin::animate([
    'animation' => 'bounce',        // 动画名（不带 animate__ 前缀）：bounce/flash/pulse/shakeX/
                                    // fadeIn/fadeInUp/zoomIn/slideInLeft/flip/heartBeat...（animate.css 全集）
    'trigger'   => 'load',          // load | hover | click | scroll
    'infinite'  => false,           // 无限循环
    'delay'     => null,            // 延迟：'1s'/'2s'... 或 animate.css 档位 1|2|3|4|5
    'speed'     => null,            // 速度：'slow' | 'slower' | 'fast' | 'faster'
    'repeat'    => null,            // 重复次数：1|2|3
    'content'   => '<h4>内容</h4>',  // 被包裹的任意 HTML / 组件
    'tag'       => 'div',           // 外层标签
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `animation` | string | `'bounce'` | 动画类名 |
| `trigger` | string | `'load'` | 触发方式（hover / click / focus），或触发按钮文案 |
| `infinite` | bool | `false` |  |
| `delay` | mixed | `null` | 延迟（毫秒） |
| `speed` | mixed | `null` |  |
| `repeat` | mixed | `null` |  |
| `content` | string | `''` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `tag` | string | `'div'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `pdfViewer`

PDF 预览（pdf.js 内嵌查看）。

> **类**：`zxf\XfAdmin\Components\Misc\PdfViewer`
> **文件**：`src/Components/Misc/PdfViewer.php`（88 行）
> **依赖插件**：`pdfjs`

**用法示例**

```php
XfAdmin::pdfViewer([
    'url'    => '/files/doc.pdf',
    'height' => 600,
    'toolbar'=> true,    // 页码/缩放/下载工具栏
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `url` | string | `''` | 链接地址（自动做安全协议校验） |
| `height` | int | `600` | 高度（CSS 长度，受安全白名单约束） |
| `toolbar` | bool | `true` | 是否显示工具条 |
| `download` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `textDiff`

文本差异对比（merge-diff 高亮）。

> **类**：`zxf\XfAdmin\Components\Misc\TextDiff`
> **文件**：`src/Components/Misc/TextDiff.php`（56 行）
> **依赖插件**：`diff`

**用法示例**

```php
XfAdmin::textDiff([
    'old' => $v1,
    'new' => $v2,
    'mode'=> 'inline',   // inline | split
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `old` | string | `''` |  |
| `new` | string | `''` |  |
| `mode` | string | `'inline'` | 模式（各组件不同） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `pinBoard`

钉板（瀑布流便签卡片）。

> **类**：`zxf\XfAdmin\Components\Misc\PinBoard`
> **文件**：`src/Components/Misc/PinBoard.php`（45 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::pinBoard([
    'notes' => [
        ['color'=>'warning','title'=>'设计评审','text'=>'周五前确认首页视觉','author'=>'张三','time'=>'10:30'],
        ['color'=>'info','title'=>'Bug 修复','text'=>'登录页 500','author'=>'李四','time'=>'昨天'],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `notes` | array | `[]` |  |
| `addable` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `masonry`

瀑布流布局（错落卡片墙）。

> **类**：`zxf\XfAdmin\Components\Misc\Masonry`
> **文件**：`src/Components/Misc/Masonry.php`（47 行）
> **依赖插件**：`masonry`

**用法示例**

```php
XfAdmin::masonry([
    'columns' => 3,
    'gap'     => 4,
    'items'   => [
        '<div class="card">…</div>',
        ['html' => '<div class="card">…</div>'],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | int | `3` | 列定义数组 |
| `gap` | int | `4` | 间距 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `videoPlayer`

视频播放器（plyr 等）。

> **类**：`zxf\XfAdmin\Components\Misc\VideoPlayer`
> **文件**：`src/Components/Misc/VideoPlayer.php`（79 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::videoPlayer([
    'src' => 'videos/sample.mp4',
    'poster' => 'images/bg-pattern.png',
    'type' => 'video/mp4',
    'width' => '100%',
    'autoplay' => false,
    'controls' => true,
    'loop' => false,
    'muted' => false,
    'title' => '视频标题',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `src` | string | `''` | 资源地址（图片 / iframe / 文件） |
| `poster` | string | `''` |  |
| `type` | string | `'video/mp4'` | 类型（各组件语义不同，详见该组件说明） |
| `width` | string | `'100%'` | 宽度（数字=栅格列数或 CSS 长度） |
| `autoplay` | bool | `false` | 是否自动播放 |
| `controls` | bool | `true` |  |
| `loop` | bool | `false` | 是否循环 |
| `muted` | bool | `false` |  |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `i18n`

国际化（多语言切换演示）。

> **类**：`zxf\XfAdmin\Components\Misc\I18n`
> **文件**：`src/Components/Misc/I18n.php`（149 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::i18n([
    'currentLocale' => 'zh-CN',
    'locales' => [
        'en' => ['name' => 'English', 'flag' => 'flags/us.svg'],
        'zh-CN' => ['name' => '简体中文', 'flag' => 'flags/cn.svg'],
        'ja' => ['name' => '日本語', 'flag' => 'flags/jp.svg'],
    ],
    'demoKeys' => [
        'greeting' => '你好，世界！',
        'welcome' => '欢迎来到管理后台',
        'save' => '保存',
        'cancel' => '取消',
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `currentLocale` | string | `'zh-CN'` |  |
| `locales` | array | `[]` |  |
| `demoKeys` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


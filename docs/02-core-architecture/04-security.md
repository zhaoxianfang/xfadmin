# 安全与转义

XfAdmin 采用**纵深防御**：每一处输出都按「槽位语义」选择转义强度，
并对结构性字段（会进入属性或标签名的值）做白名单校验。

## 1. 转义工具（Support\Html）

| 方法 | 用途 | 编码标志 |
|---|---|---|
| `Html::e($v)` | 通用 HTML 转义 | `htmlspecialchars(ENT_QUOTES, 'UTF-8', false)`（不双编码） |
| `Html::attrs($arr)` | 属性拼接 | 值经 `e()`；数组值 JSON 化（含 `HEX_TAG/AMP/APOS/QUOT`） |
| `Html::json($v)` | `data-*` 属性中的 JSON | `json_encode` + `e()`（**不含 HEX_TAG**，靠 `e()` 兜底） |
| `Html::scriptJson($v)` | **内联 `<script>`** 中的 JSON | `json_encode` 含 `HEX_TAG/HEX_AMP/HEX_APOS/HEX_QUOT`，**不再 `e()`** |
| `Html::cls(...$groups)` | class 合并去重 | 过滤空串 + `array_unique` |
| `Html::get($arr, 'a.b.c', $default)` | 点式读取 | — |
| `Html::set(&$arr, 'a.b.c', $v)` | 点式写入 | 自动建中间层 |

⚠️ **`Html::json()` 与 `Html::scriptJson()` 不可互换**：

```php
// ✅ 属性中：e() 会把 < 变成 &lt;，安全
'<div data-cfg="' . Html::json($cfg) . '">';

// ✅ 内联脚本中：必须 HEX_TAG，否则 </script> 会提前闭合标签
'<script>var cfg = ' . Html::scriptJson($cfg) . ';</script>';

// ❌ 内联脚本中用 Html::json() —— 存在 </script> 断标签注入风险
```

组件内生成 `data-xf-config` 请用 `initAttrs()`（内部已带全部 `JSON_HEX_*`）。

## 2. 槽位语义分级

组件的每个配置项都属于以下三类之一：

### A. 纯文本槽位 —— `e()` / `text()`

转义后输出。适用：`title`、`label`、`text`、`name`、`value`、`message`、
`heading`、`subtitle`、`copyright`、`help` 等。

```php
$this->e($this->get('title'));
$this->text($this->get('label'));     // 允许传组件/闭包，字符串则转义
```

### B. 内容槽位 —— `raw()`（原样输出 HTML）

适用：`body`、`content`、`slot`、`footer`、`left`、`right`、`actions`、
`below`、`before`、`after`、`head`、`scripts`、`prepend`、`append` 等。

```php
$this->raw($this->get('body'));       // 支持 Stringable / Closure / 数组
```

> ⚠️ **调用方责任**：这些字段若接收用户输入，调用方必须自行转义或保证可信。
> 组件注释中会明确标注"原样输出 HTML"。

### C. 受控槽位 —— 白名单 / 格式校验

| 场景 | 方法 | 说明 |
|---|---|---|
| 枚举（variant/size/type/placement/tag/align/trigger） | `enum($v, $allowed, $default)` | 非法值回退默认 |
| CSS 长度 | `cssLen($v, $default)` | `-?数字 + 单位` |
| CSS 比例 | `cssRatio($v, '4/3')` | `N/M` |
| CSS 颜色 | `cssColor($v, '')` | `#hex` / `rgb()` / 具名色 / `var()` |
| CSS 背景 | `cssBackground($v, '')` | 纯色 / 渐变 / `url()` |
| URL | `safeUrl($v, '#')` | 协议白名单 |
| 栅格宽度 | `gridCol($w)` | 断点白名单 + 1~12 夹紧 |

```php
// ❌ 危险：'variant' => 'primary" onload="alert(1)'
// ✅ enum() 校验后非法值回退 'primary'，无法逃逸出 class 属性
```

## 3. URL 安全（safeUrl）

```
放行：
  //host/path          协议相对
  http(s)://…         绝对
  /path               根相对
  #anchor             锚点
  mailto: / tel:
  data:image/…        图片 data URI
  path/to/x           无 scheme 相对路径
  javascript:void(0)  设计性 no-op（含 void(0) / void() / ; / 裸 javascript:）

拦截（返回默认 '#'）：
  javascript:alert(1)
  vbscript:…
  data:text/html;…    非图片 data URI
  其它未知伪协议
```

## 4. JSON 输出安全

组件输出到 `data-xf-config` 的 JSON 统一使用：

```php
json_encode($config,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
```

| 标志 | 防御目标 |
|---|---|
| `JSON_HEX_TAG` | `<` `>` → `\u003C` `\u003E`，防 `</script>` 断标签 |
| `JSON_HEX_AMP` | `&` → `\u0026`，防实体误解析 |
| `JSON_HEX_APOS` / `JSON_HEX_QUOT` | `'` `"` → 转义，防属性引号逃逸 |

> 副作用：`options` 中若含 `<`、`>`、`&` 会被转义为 `\u003C` 等，
> 前端 `JSON.parse()` 后还原为正常字符，功能不受影响。

## 5. 历史修复清单（真实漏洞，勿回退）

以下均为本包已修复的真实 XSS / 注入点，**维护时不得回退**：

### A. 结构性字段未转义（可被 `"` 逃逸注入属性）

| # | 组件 | 字段 | 修复 |
|---|---|---|---|
| 1 | `Animate` | `tag` 直接进标签名（高危） | 白名单 `div/span/section/article/p/header/footer/main/aside/blockquote/h1-6/li/figure` |
| 2 | `Tabs` | `style` 拼 class | `e()` |
| 3 | `Progress` | `variant` 拼 class | `e()` |
| 4 | `Avatar` | `variant`/`size`/`rounded` 拼 class | `e()` |
| 5 | `Popover` | `content`/`title`/`placement`/`trigger` 进 `data-*` | `e()` |
| 6 | `Tooltip` | `title`/`placement`/`custom_class`/`trigger` 进属性 | `e()` |
| 7 | `Modal` | `size` 拼 class | `e()` |
| 8 | `Offcanvas` | `placement` 拼 class | `e()` |
| 9 | `Toast` | `variant` 拼 class | `e()` |
| 10 | `Dropdown` | `variant`/`size` 拼 class | `e()` |

### B. 文本字段由 raw 改为 e

`Alert.text`、`Ribbon.text`、`ComingSoon.message`、`Maintenance.message`、
`Widget.value`、`PricingCard.price`。

## 6. 审计工具

包内提供运行时模糊审计脚本：

```bash
php tools/selftest/xss_audit.php
```

原理：

1. 对每个已注册组件，把唯一 payload `<xfxss-payload>` 注入**全部文本展示字段**；
2. 渲染后检测输出 HTML 是否含字面未转义子串
   （经 `e()` 后 `<` 会变成 `&lt;`，因此字面出现即未转义）；
3. 跳过结构性字段（`class`/`id`/`variant`/`size`/`placement`/`tag`/`type`…）与
   明确的内容槽位（`body`/`content`/`slot`/`footer`/`left`/`right`/`toggle`/`menu`/`head`/`scripts`/`below`…）；
4. `RAW_TEXT_EXEMPT` 豁免：`Popover`/`Tooltip` 的 `text` 字段（触发元素内容，设计上原样输出）。

三道护栏（已固化在 `tools/selftest/run.sh`）：

```
xss_audit → asset_check → Playwright selftest
```

当前状态：**156+ 组件全部 PASS**。

## 7. 调用方安全清单

在你的业务代码中使用 XfAdmin 时：

1. ✅ 用户可控的**文本**放进 `title`/`label`/`text` 等文本槽位（自动转义）；
2. ⚠️ 用户可控内容放进 `body`/`content` 等**内容槽位前必须自行转义**：

```php
use zxf\XfAdmin\Support\Html;

'body' => Html::e($userInput),
// 或
'body' => XfAdmin::alert(['variant' => 'info', 'text' => $userInput]),
```

3. ✅ 用户可控数据放进内联 `<script>` 时用 `Html::scriptJson()`；
4. ⚠️ 不要把用户输入作为 `variant`/`size`/`type`/`class`/`style` 等结构性字段
   （虽经白名单/转义，但语义上不应由用户控制）；
5. ✅ 链接类字段（`url`/`href`/`src`）由 `safeUrl()` 兜底，但仍建议校验业务合法性；
6. ✅ 富文本编辑器（Quill）的初始 HTML 是 `raw()` 输出的 —— **必须保证可信**；
   Summernote 模式则为 `e()` 转义。

## 8. 其它安全机制

| 机制 | 说明 |
|---|---|
| CSRF | 表单 `csrf_field()`；AJAX 自动带 `X-CSRF-TOKEN`（`XFAdmin.csrf()` 读 `<meta name="csrf-token">`） |
| 419 处理 | `handleFormResponse` 对 419 给出「页面已过期（CSRF）」提示 |
| 资源路径穿越 | `AssetController` 用 `realpath` 包含性校验，禁止访问 `resources/assets` 之外的文件 |
| 资源扩展名 | 20 种白名单，其它一律 404 |
| 单词典去重 | 依赖缺失只警告一次，避免控制台刷屏掩盖真实错误 |

下一步：[05-服务端数据协议](05-data-protocol.md)

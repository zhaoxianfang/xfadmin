# 安全规范（转义 / raw 槽位 / 注入防护 / 审计清单）

> 事实来源：`src/Support/Html.php`、`Component.php` 安全助手、`tests/audit.php`。
> 本包默认「安全优先」：组件字符串参数自动转义；风险集中在 raw 槽位与内联 JSON。

---

## 1. 两大上下文，两套转义（务必分清）

| 上下文 | 方法 | 转义标志 |
| --- | --- | --- |
| **HTML 文本/属性** | `Html::escape()`（`Component::escape()`） | `htmlspecialchars(ENT_QUOTES, UTF-8)` |
| **HTML 属性值中的 JSON**（如 `data-xf-config='...'`） | `Html::json()` | `JSON_HEX_APOS\|HEX_QUOT\|HEX_TAG\|HEX_AMP` |
| **内联 `<script>` 中的 JSON** | `Html::scriptJson()` | `UNESCAPED_UNICODE\|UNESCAPED_SLASHES\|HEX_TAG\|HEX_APOS\|HEX_QUOT\|HEX_AMP`（防 `</script>` 注入） |

- 前端初始化配置一律走 `Component::initAttrs()` → `data-xf-config`（属性上下文 HEX 转义）。
- 组件内联脚本里的配置走 `Html::scriptJson`，**绝不**用裸 `json_encode` 拼进 `<script>`。

---

## 2. raw 槽位 = 存储型 XSS 汇点（最高危）

- `Component::raw()` **不转义**：`body` `content` `footer` `actions` `tools` `menu` 等容器；
  字符串原样输出、闭包惰性求值、组件/Stringable 渲染、数组拼接。
- **禁止**把用户可控数据直接塞进 raw 槽位。正确做法：
  - 用组件的转义型字符串参数（`text`/`title`/`label`/`help`/`feedback` 等，内部 `escape()`）；
  - 或调用方先 `escape()`/e() 再传入；
  - 或新增专用组件用 `text()` 槽位处理。
- 已修复案例：`Footer` 的 `brand_url` 曾 `safeUrl()` 后未转义直接拼 `href`，
  修复为 `$this->escape($this->safeUrl($brandUrl))`（audit 项）。所有 URL 输出都要「白名单校验 + 转义」双管齐下。

---

## 3. URL / 图标 / CSS 白名单

- `safeUrl($v)`：放行 http(s)://、`//`、`/`、`#`、`mailto:`、`tel:`、`data:image/`、`javascript:void(0)`；
  拦截危险伪协议（javascript 执行、vbscript 等）回落 `#`。
- `img($path)`：`http(s)`/`//`/`data:image/` 原样，其余走 `asset('images/')`，空路径回退 1×1 GIF。
- `iconClass($v)`：仅保留 `[a-zA-Z0-9_\- ]`，防 class 属性注入。
- `enum($v,$allowed,$default)`：变体/尺寸/颜色等全部走白名单，非法值回退默认（防任意 class 注入）。
- CSS 安全助手：`safeStyle()` `safeCssValue()` `cssLen()` `cssRatio()` `cssColor()` `cssBackground()`
  拦截 `;{}`、`url(`、`expression(`、`<`、`javascript:`（防 CSS 注入/点击劫持）。
- `Html::attrs()`：属性名白名单 `[a-zA-Z_][a-zA-Z0-9_:.-]*`，非法名跳过；值均转义；`class` 走 `cls()`。

---

## 4. 扩展/覆盖的安全边界

- `XfAdmin::extend()` 注册的类必须 `is_subclass_of(Component::class)`，否则抛异常。
- 自定义组件若用 raw 槽位输出用户提供内容，须自行 `escape()`；不要绕过基类安全助手。
- e() 在组件渲染期（非 Blade 的纯 PHP 上下文）可能不可用，用 `escape()`/`htmlspecialchars`。

---

## 5. 审计清单（`tests/audit.php`，51 项，改动后必全绿）

覆盖（节选）：
- 文本字段注入 payload 均未裸输出（如 footer.brand_url）；
- promptFields 字段文案经 escapeHtml；
- 图表主题切换时清理已移除实例；
- jQuery 依赖缺失有明确告警；
- 组件提供 destroy 钩子、register 支持第三参销毁函数；
- countdown/countup/backtotop 返回 destroy；DataTable 销毁反注册 ResizeObserver；
- 工具条四类交互已接线；打印作用域限定 `body.xf-printing`；
- preloader z-index 不高于 toast、select2 层级低于模态框；
- auth-split 无重复定义；HTML 标签配平；所有 `<img>` 带 alt（装饰性图标豁免）；
  声明式组件 `data-xf-config` 统一 HEX 转义（rowActions/adminBehaviors）。

**新增组件/widget 时**：若引入新的文本输出、URL、内联脚本或 DOM 交互，考虑在 `audit.php` 增加对应检查项。

---

## 6. 编码红线（安全视角）

1. 内联 JSON 用 `scriptJson`/`json`，不用裸 `json_encode`。
2. URL 输出 = `safeUrl` 白名单 + `escape` 转义。
3. raw 槽位不接用户数据。
4. 所有 `class`/`style`/属性走白名单或安全助手。
5. 交互 widget 提供 destroy 钩子，避免重复绑定/内存泄漏。
6. 不硬编码密钥/令牌；CSRF 通过 `setCsrfResolver` 注入。

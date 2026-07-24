# 安全与转义规范

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

本扩展包默认 **XSS 安全（secure-by-default）**：所有组件在渲染用户输入的文本、URL、表格字段值时都会自动转义。本文档说明转义模型、可用助手、已知的可信边界（需开发者自行把关的地方）以及最佳实践。

## 1. 转义助手

| 助手 | 用途 | 关键标志 |
| --- | --- | --- |
| `Html::e($v)` | 转义 HTML 文本/属性值（`ENT_QUOTES`，UTF-8，不二次编码） | `& " ' < >` 全部转义 |
| `Html::json($v)` | 把数据写入 **`data-xf-config` 等 HTML 属性** 内的 JSON | `JSON_HEX_APOS \| JSON_HEX_QUOT` + `e()` 二次 HTML 转义 |
| `Html::scriptJson($v)` | 把数据写入 **内联 `<script>` 里的 JS 字符串字面量** | `JSON_HEX_TAG \| JSON_HEX_AMP \| JSON_HEX_APOS \| JSON_HEX_QUOT` |

**切勿混用**：`Html::json()` 会做 HTML 实体转义（`<`→`&lt;`），它只能用于 HTML 属性。若把它的值塞进 `<script>` 的 JS 字符串，浏览器在脚本上下文**不会**解码 HTML 实体，会得到错误字符串。内联脚本请一律用 `Html::scriptJson()`。

## 2. 组件渲染层的自动转义

- 所有 `Component` 子类在拼接 HTML 时，凡涉及 `user` / `text` / `title` / `excerpt` / `label` / `content` / `url` 等用户字段，都应通过 `$this->e()` 转义（如 `CommentThread`、`SearchResults`、`Breadcrumb` 等已在基类与子类中统一处理）。
- `DataTable` 的：
  - **`xfTemplate` 模板列**：模板结构（开发者代码）可信，但 `{field}` 占位符被替换的**字段值**在 `xfadmin.js` 渲染时会被 `escapeHtml()` 转义，杜绝存储型 XSS。
  - **`xfBadges` 徽章列**：单元格 `data` 值同样在 JS 侧转义后渲染。
- 富文本编辑器（`Editor`/`Quill`/`Summernote`）按设计输出 HTML，属于业务层的“可信富文本”，其 XSS 防护应在服务端做 HTML 消毒（建议用 `HTMLPurifier` 之类），扩展包不在此拦截。

## 3. 已知的可信边界（开发者需自行把关）

以下入口默认信任调用方传入的内容，因为它们接收的是**开发者提供的代码/配置**，不是终端用户输入：

1. **`Raw` 组件**：`XfAdmin::raw($html)` 原样输出，不做任何转义。仅用于渲染你完全可控的 HTML 片段。
2. **`SweetAlert` 的 `confirmJs`**：在用户确认时通过 `new Function(config.confirmJs)()` 执行。该字符串来自 `data-xf-config`（开发者配置），**绝不**应拼接任何终端用户输入，否则等同任意代码执行。
3. **`DataTable` 开发者自定义的 `render` 函数**：属于开发者代码，自行负责其中字段的转义。
4. **`pagination` 等接受 HTML 片段的选项**：按 `raw` 处理，需调用方保证安全。

## 4. 内联脚本安全约定（组件作者必读）

在组件内联 JS（`inlineJs`）里需要把组件参数（尤其是 `url`、`id` 等）拼进 `<script>` 块时，**禁止**直接 `json_encode($v)`，否则值里若含 `</script>`、双引号或换行会破坏脚本甚至注入。正确写法：

```php
use XfAdmin\Support\Html;

$js = 'var url=' . Html::scriptJson($url) . ';';   // ✅ 带 JSON_HEX_TAG
// 禁止：'var url=' . json_encode($url) . ';'      // ❌ 可被 </script> 截断
```

已按此规约修复的组件：`PdfViewer`、`EmailCompose`、`Gallery`。凡是通过 `data-xf-config` 属性传参的组件（如 `DataTable`、`Editor`、`ColorPicker` 等）走 `Html::json()` + `Html::attrs()` 的 `e()` 双保险，无需改动。

## 5. 最佳实践清单

- 渲染用户输入文本 → 一律 `$this->e()` / `Html::e()`。
- 渲染用户输入进属性 → 走 `Html::attrs()`（内部 `e()`）或 `Html::json()`。
- 内联 `<script>` 里嵌入参数 → `Html::scriptJson()`。
- 富文本落库前 → 服务端 HTML 消毒。
- 不要把终端用户输入拼进 `Raw`、`confirmJs`、自定义 `render`、SQL、`eval` 等可信边界。
- 转义应在**输出时**做，而非存储时；模板/组件是统一的转义落点。

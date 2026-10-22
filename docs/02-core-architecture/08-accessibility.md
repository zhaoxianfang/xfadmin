# 可访问性（Accessibility / a11y）

XfAdmin 的所有 UI 组件都遵循 **WCAG 2.1 AA** 与 **WAI-ARIA 1.2** 实践，
并贴合 Bootstrap 5 的静态标记无障碍规范。本文是**组件无障碍基线的权威参考**，
所有新增 / 修改组件都必须满足本文的基线要求。

> 源码：`src/Components/Component.php` 及各 `src/Components/UI|Form|Data|Layout/*`。

---

## 1. 标准基线

| 维度 | 遵循规范 | 说明 |
|---|---|---|
| 文本对比度 | WCAG 2.1 AA（≥ 4.5:1 正文 / ≥ 3:1 大字） | 由 Bootstrap 主题保证，自定义色需自检 |
| 语义结构 | 正确的 landmark / heading 层级 | `page` 提供 `header/main/footer` landmark；`h1~h6` 不跳级 |
| 键盘可达 | 全部可交互元素可 Tab 聚焦、可回车/空格激活 | 链接/按钮/折叠/分页/抽屉/命令面板等 |
| 焦点管理 | 模态 / 抽屉 / 命令面板打开时焦点移入、关闭时归还 | 由 Bootstrap JS 处理，标记需提供 `role`/`aria-modal` |
| 屏幕阅读器 | 为每个交互控件提供可读名称与状态 | `aria-label` / `aria-labelledby` / `aria-current` / `aria-expanded` |
| 装饰图 | 纯装饰图标 / 图片标记为 `aria-hidden="true"` | 避免读屏念出无意义图标名 |
| 信息图 | 承载含义的图标 / 图片提供 `role="img"` + `aria-label` | 如状态图标、功能图标 |

> ⚠️ **装饰 vs 信息**：图标若仅用于点缀（无独立语义），必须 `aria-hidden`；
> 若图标本身传达含义（如"警告"图标、功能入口图标），应提供 `aria-label` 或可见文本替代。

---

## 2. 组件无障碍基线要求

### 2.1 交互控件（按钮 / 链接 / 开关）

- 链接型按钮（`href` + `role="button"`）与 `<button>` 都应可被键盘聚焦；
- **禁用态**若以链接呈现，必须同时加 `aria-disabled="true"` 与 `tabindex="-1"`，
  否则禁用链接仍可被 Tab 聚焦并回车激活；
- 图标按钮 / 无可见文本的按钮必须提供 `aria-label`。

```php
// ✅ 禁用链接态（XfAdmin\UI\Button 已落地）
if ($disabled) {
    $attrs['aria-disabled'] = 'true';
    $attrs['tabindex']      = '-1';
}

// ✅ 纯图标按钮
'<button type="button" class="btn btn-icon" aria-label="' . $this->e($label) . '">'
```

### 2.2 折叠 / 手风琴 / 选项卡（状态与关联）

- 触发按钮必须声明 `aria-expanded`（展开 true / 折叠 false）；
- 触发器与受控区域必须**双向关联**：按钮 `aria-controls="面板id"`，面板 `aria-labelledby="按钮id"`；
- 选项卡需实现完整 `tablist` / `tab` / `tabpanel` + `aria-selected` + roving tabindex
  （`Tabs` 组件已完整实现，可直接复用）。

```php
// ✅ 手风琴关联（XfAdmin\UI\Accordion 已落地）
'<button ... aria-expanded="' . ($open ? 'true' : 'false') . '"'
. ' aria-controls="' . $this->e($panelId) . '">'                 // 按钮指向面板
. '<div id="' . $this->e($panelId) . '" ... aria-labelledby="'    // 面板指回按钮
. $this->e($headerId) . '">'
```

### 2.3 对话框 / 抽屉 / 命令面板（焦点语义）

- 顶层容器必须有 `role="dialog"` 与 `aria-modal="true"`；
- 必须提供可朗读标题：`aria-labelledby` 指向标题元素的 `id`（标题必须带 `id`）；
- `aria-hidden="true"` 仅用于**非活动**的浮层（Bootstrap 在打开时自动移除此属性）。

```php
// ✅ 对话框（XfAdmin\UI\Modal / CommandPalette 已落地）
'<div class="modal" role="dialog" aria-modal="true" aria-labelledby="'
. $this->e($titleId) . '">'
. '<h5 id="' . $this->e($titleId) . '">' . $this->e($title) . '</h5>'

// ✅ 抽屉标题关联（XfAdmin\UI\Offcanvas 已落地）
'<div class="offcanvas" aria-labelledby="' . $this->e($labelId) . '">'
. '<h5 class="offcanvas-title" id="' . $this->e($labelId) . '">'
```

### 2.4 表单字段（label / 描述 / 错误）

- 每个输入控件必须有可见 `<label for="控件id">`，且 `for` 与控件 `id` 一致；
- 帮助文本 / 校验反馈通过 `aria-describedby` 关联到控件；
- 校验失败需输出 `aria-invalid="true"`。

> ✅ **已统一实现**：全部 19 个表单字段组件均经由 `Form\Concerns\FieldWrapper`
> 的 `wrapField($control, $id)` 自动接线（label for + describeAttrs + stateAria），
> 无需逐组件重复。新增表单组件请复用 `FieldWrapper`，不要手写 label。

### 2.5 进度 / 范围（数值语义）

- 进度条必须声明 `role="progressbar"` + `aria-valuenow` / `aria-valuemin` / `aria-valuemax`；
- **数值必须钳制在 [min, max]**（如进度钳制 [0,100]），否则宽度会溢出容器，
  且 `aria-valuenow` 与 `aria-valuemax` 矛盾。

```php
// ✅ 数值钳制（XfAdmin\UI\Progress 已落地）
$value = max(0.0, min(100.0, (float) ($bar['value'] ?? 0)));
```

### 2.6 导航 / 分页（landmark 与当前项）

- 分页 / 翻页组应使用 `<nav>` 并声明 `aria-label`（如"分页导航"）；
- 当前页链接必须加 `aria-current="page"`，便于读屏识别位置。

```php
// ✅ 分页（XfAdmin\UI\Pagination 已落地）
'<nav aria-label="' . $this->e($this->get('aria_label', '分页导航')) . '">'
. '<a class="page-link" aria-current="page" href="...">'   // 当前页
```

### 2.7 图像 / 图标（装饰 vs 信息）

- 装饰性 `<i>` / `<img>` 必须 `aria-hidden="true"`；
- 信息性图标应提供 `title` 参数：传入后输出 `role="img"` + `aria-label`，否则视为装饰图标 `aria-hidden`。

```php
// ✅ 图标（XfAdmin\UI\Icon 已落地，支持 tabler / lucide 双库）
$title = $this->get('title');
if ($title !== null) {
    $aria = ['role' => 'img', 'aria-label' => $this->e($title)];   // 有含义
} else {
    $aria = ['aria-hidden' => 'true'];                              // 装饰
}
```

### 2.8 轮播 / 滑块（指示器可读）

- 轮播指示器按钮应提供 `aria-label`（如"第 N 张"），否则读屏仅念"按钮"。

```php
// ✅ 轮播指示器（XfAdmin\UI\Carousel 已落地）
'<button type="button" ... aria-label="' . $this->e(sprintf('第 %d 张', $i + 1)) . '">'
```

### 2.9 隐藏文件输入（上传）

- `display:none` 的 `<input type="file">` 必须补 `aria-label`，否则被聚焦时读屏无法命名。

```php
// ✅ 拖拽上传（XfAdmin\UI\DropzoneUpload 已落地）
'<input type="file" class="xf-dropzone-input d-none" aria-label="'
. $this->e($this->get('hint', '选择文件上传')) . '">'
```

---

## 3. 历史修复清单（真实缺陷，勿回退）

以下均为本包已修复的真实无障碍缺陷，**维护时不得回退**：

| # | 组件 | 缺陷 | 修复 |
|---|---|---|---|
| 1 | `Accordion` | header 按钮无 `aria-controls`、面板无 `aria-labelledby` | 新增 header id，按钮 `aria-controls`，面板 `aria-labelledby` |
| 2 | `Modal` | `.modal` 缺 `role="dialog"` / `aria-modal` | 补两属性 |
| 3 | `Progress` | `value` 未钳制 [0,100]，越界溢出且 aria 矛盾 | `max(0,min(100,...))` |
| 4 | `Avatar` | 图标头像 `<i>` 缺 `aria-hidden` | 补 `aria-hidden="true"` |
| 5 | `Button` | 禁用链接态仍可聚焦/激活 | 补 `aria-disabled` + `tabindex="-1"` |
| 6 | `Icon` | 装饰图标缺 `aria-hidden`；缺信息图标语义 | 默认 `aria-hidden`；新增 `title` 参数（`role=img`+`aria-label`） |
| 7 | `Pagination` | `<nav>` 缺 `aria-label`、当前页缺 `aria-current` | 新增 `aria_label` 参数 + `aria-current="page"` |
| 8 | `Offcanvas` | 标题无 `id`、容器缺 `aria-labelledby` | 标题补 id，容器补 `aria-labelledby` |
| 9 | `Tooltip` | `tag='a'` 时未补 `role`/`tabindex`（与 Popover 不一致） | 对齐 Popover：`role="button"` + `tabindex="0"` |
| 10 | `DropzoneUpload` | 隐藏 file input 无 `aria-label` | 补 `aria-label`（取 `hint` 或默认"选择文件上传"） |
| 11 | `CommandPalette` | 模态容器缺对话框语义、搜索框无 `aria-label` | 容器 `role="dialog"`+`aria-modal`+`aria-label`；input `aria-label` |
| 12 | `Carousel` | 指示器按钮缺上下文 | 补 `aria-label="第 N 张"` |

> 表单组件（Input/Select/Check/Textarea 等 19 个）经 `FieldWrapper` 统一接线，
> 无障碍关联已完善，**无需改动**。

---

## 4. 组件无障碍自检清单（开发者必查）

新增或修改任意组件时，逐项核对：

- [ ] 可交互元素是否可 Tab 聚焦、可回车/空格激活？
- [ ] 禁用态（链接呈现）是否补 `aria-disabled` + `tabindex="-1"`？
- [ ] 纯图标 / 无文本按钮是否有 `aria-label`？
- [ ] 装饰图标 / 图片是否 `aria-hidden="true"`？信息图标是否 `role="img"`+`aria-label`？
- [ ] 折叠 / 选项卡触发器是否有 `aria-expanded` 且 `aria-controls` 指向面板？
- [ ] 面板是否 `aria-labelledby` 指回触发器？
- [ ] 对话框 / 抽屉 / 命令面板是否有 `role="dialog"` + `aria-modal` + `aria-labelledby`（标题带 id）？
- [ ] 表单控件是否有 `<label for>` + `aria-describedby`（help/反馈）+ `aria-invalid`？
- [ ] 进度条是否 `role="progressbar"` + 三 aria-value，且数值已钳制区间？
- [ ] 分页 / 翻页 `<nav>` 是否 `aria-label`，当前项是否 `aria-current`？
- [ ] 轮播 / 滑块指示器是否有 `aria-label`？
- [ ] 隐藏 file input 是否有 `aria-label`？

---

## 5. 验证方式

| 方式 | 说明 |
|---|---|
| 键盘走查 | 仅用 Tab / Shift+Tab / Enter / 方向键遍历组件，确认无焦点陷阱、无不可达控件 |
| 屏幕阅读器 | VoiceOver（macOS）/ NVDA（Windows）朗读，确认名称与状态正确 |
| 对比度 | 浏览器 devtools 无障碍面板 / axe DevTools 检查文本对比度 |
| 回归 | 改动后运行 `php -l` 与 `vendor/bin/pint`；UI 改动建议经 `tools/selftest` 回归 |

下一步：[07-扩展机制](07-extending.md)

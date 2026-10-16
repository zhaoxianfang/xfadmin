# 14. 链式构建器（Builder / GridBuilder / AdminBuilder）

> 用 PHP 链式调用替代手写 HTML，彻底告别控制器里的
> `<div class="col-md-6">...</div>`。

## 1. 为什么需要 Builder

传统做法里，控制器为了拼一个后台页面，常常内嵌大量前端代码：

```php
// ❌ 反例：控制器内嵌 HTML（难维护、难复用、易 XSS）
$html = '<div class="row">';
foreach ($cards as $c) {
    $html .= '<div class="col-md-6 col-xl-3">';
    $html .= '<div class="card card-sm"><div class="card-body">';
    $html .= '<div class="fw-bold">' . $c['value'] . '</div>';
    $html .= '<div class="text-secondary">' . $c['title'] . '</div>';
    $html .= '</div></div></div>';
}
$html .= '</div>';
```

Builder 体系把这类结构抽象为**可链式、可闭包、可嵌套**的构建方法：

```php
// ✅ 正例：链式构建器（结构清晰、自动转义、白名单标签）
use zxf\XfAdmin\XfAdmin;

echo XfAdmin::admin()
    ->statCards([
        ['title' => '今日订单', 'value' => 128, 'variant' => 'primary'],
        ['title' => '营收', 'value' => '¥12,800', 'variant' => 'success'],
    ]);
```

## 2. 三层构建器

| 类 | 定位 | 入口 | 核心能力 |
|---|---|---|---|
| `Builder` | 底层 HTML 节点构建 | `XfAdmin::builder()` | `push`/`end`/`add`/`text`/`when` |
| `GridBuilder` | 栅格布局 | `XfAdmin::grid()` | `newRow`/`col`/`mdCol`/`card`/`cardBody` |
| `AdminBuilder` | 后台业务 | `XfAdmin::admin()` | `statCards`/`kv`/`alert`/`badge`/`field`…（26+ 方法） |

三者是继承关系：`AdminBuilder extends GridBuilder extends Builder`，
因此 `AdminBuilder` 同时拥有栅格与底层能力，可无缝混用。

## 3. Builder 底层 API

### 3.1 核心方法

| 方法 | 签名 | 说明 |
|---|---|---|
| `push()` | `push(string $tag, array $attrs = []): static` | 开标签并压栈（标签名白名单内） |
| `end()` | `end(): static` | 闭合栈顶标签 |
| `endAll()` | `endAll(): static` | 一次性闭合所有未闭合标签 |
| `add()` | `add(mixed $content): static` | 追加内容（不转义，支持字符串/组件/闭包/数组） |
| `text()` | `text(mixed $text): static` | 追加转义后的纯文本 |
| `raw()` | `raw(string $html): static` | 原样追加（跳过解析） |
| `when()` | `when(bool $cond, Closure $cb): static` | 条件分支 |
| `whenElse()` | `whenElse(bool $cond, Closure $then, ?Closure $else): static` | 条件分支（带 else） |
| `render()` | `render(): string` | 渲染完整 HTML（自动闭合未闭合标签） |

### 3.2 基本示例

```php
use zxf\XfAdmin\Support\Builder\Builder;

$b = new Builder();
$html = $b->push('div', ['class' => 'box'])
    ->add('<strong>标题</strong>')
    ->push('ul')
        ->add('<li>项目一</li>')
        ->add('<li>项目二</li>')
    ->end()
    ->end()
    ->render();
// <div class="box"><strong>标题</strong><ul><li>项目一</li><li>项目二</li></ul></div>
```

### 3.3 闭包与惰性求值

`add()` 接受闭包时惰性求值，闭包接收当前构建器实例：

```php
$b->add(fn ($builder) => $builder->push('p')->text('惰性内容')->end());
```

> ⚠️ **防护**：闭包若误返回 `$this`（构建器自身），框架会拦截并返回空串，
> 避免整棵树被重复渲染。

### 3.4 标签白名单

`push()` 的标签名受 `Node::TAGS` 白名单约束（覆盖结构/布局/表单/表格/语义化标签），
非法标签自动回退为 `div`，从根本上杜绝 `<script>`/`<style>` 注入。

## 4. GridBuilder 栅格 API

| 方法 | 说明 |
|---|---|
| `newRow($class, $cb?)` / `row(...)` | 开启一行 `<div class="row">` |
| `endRow()` | 闭合当前行 |
| `col($width, $cb?)` | 开启一列（纯 `col` / 数字列宽） |
| `smCol/mdCol/lgCol/xlCol/xxlCol($width, $cb?)` | 指定断点列宽 |
| `endCol()` | 闭合当前列 |
| `card($class, $cb?)` | 开启卡片 `<div class="card">` |
| `cardHeader($title, $cb?)` | 卡片头部（自闭合；纯字符串标题自动用 `<h5 class="card-title mb-0">` 包裹） |
| `cardBody($content, $cb?)` | 卡片主体（自闭合） |
| `cardFooter($content, $cb?)` | 卡片底部（自闭合） |
| `cardTitle($title)` | 卡片标题（card-header 便捷封装） |
| `endCard()` | 闭合当前卡片 |

### 4.1 列宽规则

- `null` / `''` / `'auto'` → `col` 或 `col-{bp}`；
- 数字 `6` → `col-6`（无断点）或 `col-12 col-md-6`（指定断点，自动加 col-12 兜底）；
- 数组 `['md' => 6, 'xl' => 4]` → `col-12 col-md-6 col-xl-4`（断点白名单校验）。

### 4.2 完整示例

```php
use zxf\XfAdmin\XfAdmin;

// 方式一：无参获取实例，自由链式
echo XfAdmin::grid()
    ->newRow()
        ->mdCol(6)
            ->card()
                ->cardHeader('统计一')
                ->cardBody('内容 A')
            ->endCard()
        ->endCol()
        ->mdCol(6)
            ->card('border-danger')
                ->cardHeader('统计二')
                ->cardBody('内容 B')
            ->endCard()
        ->endCol()
    ->endRow();

// 方式二：闭包回调（作用域式书写，闭包内 $c 即当前列）
echo XfAdmin::grid(fn ($g) => $g
    ->newRow()
        ->mdCol(6, fn ($c) => $c->card()->cardHeader('A')->cardBody('内容A')->endCard())
        ->mdCol(6, fn ($c) => $c->card()->cardHeader('B')->cardBody('内容B')->endCard())
    ->endRow()
);
```

## 5. AdminBuilder 后台 API

### 5.1 方法总览

| 分类 | 方法 |
|---|---|
| 指标卡 | `statCards()` `infoCards()` `kv()` |
| 键值详情 | `kvList()` `kvTable()` `infoCard()` |
| 告警提示 | `alert()` `cardAlert()` `notice()` `errorText()` `emptyText()` |
| 徽章标签 | `badge()` `levelBadge()` `codeBadge()` `statusBadge()` `pushBadge()` `pushSpan()` |
| 表单 | `field()` `fieldGrid()` `formActions()` `permissionForm()` |
| 详情操作 | `detailButton()` `summaryBar()` `buttonGroup()` `quickLinks()` `toolbar()` |
| 结构面板 | `panel()` `panelRow()` `miniTable()` `listGroup()` `dialogListItem()` `treeWidget()` |
| 内容容器 | `pre()` `jsonBlock()` `scrollBlock()` `jsonScrollBlock()` `tablePane()` `dialogContent()` `dialogEnd()` `accordion()` |
| 通用区块 | `section()` |

### 5.2 常用示例

```php
use zxf\XfAdmin\XfAdmin;

// 指标卡 + 键值对 + 告警
echo XfAdmin::admin()
    ->statCards([
        ['title' => '今日订单', 'value' => 128, 'icon' => 'ti ti-shopping-cart', 'variant' => 'primary', 'delta' => ['value' => '12%', 'direction' => 'up']],
        ['title' => '营收', 'value' => '¥12,800', 'icon' => 'ti ti-currency-yen', 'variant' => 'success'],
    ])
    ->kv('总订单', '128 笔')
    ->alert('数据加载成功', 'success', 'ti ti-check', true);
```

### 5.3 表单字段

```php
echo XfAdmin::admin()
    ->fieldGrid([
        ['label' => '用户名', 'control' => '<input class="form-control" name="name">', 'required' => true, 'width' => 6],
        ['label' => '邮箱', 'control' => '<input class="form-control" name="email">', 'width' => 6],
    ])
    ->formActions('保存', '/admin/users');
```

### 5.4 新增通用方法示例

本轮新增的缺失通用方法，覆盖「状态徽章 / 页面区块 / 可滚动代码块」三类后台高频场景：

```php
use zxf\XfAdmin\XfAdmin;

echo XfAdmin::admin()
    // 业务状态 → 语义徽章（内置 active/disabled/pending/banned/processing 等映射，其余回落 primary）
    ->statusBadge('active')                 // 绿色 success 徽章
    ->statusBadge('pending', '待审')        // 自定义文案

    // 带标题的卡片区块容器（标题进 card-header，内容进 card-body）
    ->section('请求上下文', '<pre>…</pre>')
    ->section(null, '<p>无标题区块</p>')     // title 传 null 则不渲染 header

    // 可滚动文本块（maxHeight / 自动换行 / 字号）
    ->scrollBlock($rawText, 320, true, 12)  // max-height:320px、white-space:pre-wrap、字号 12px

    // 可滚动 JSON 块（字符串透传、数组/对象美化输出；空值短路不渲染）
    ->jsonScrollBlock($payload, 320, 12);
```

> 这些方法均为链式（`return $this`），可与栅格方法（`newRow`/`mdCol`/`card`）无缝混用；
> `scrollBlock`/`jsonScrollBlock` 曾以 `LogController` 内联私有方法重复出现，现已下沉为包内通用方法。

## 6. 安全设计

1. **标签白名单**：`Node::TAGS` 限制可渲染标签；
2. **属性转义**：所有属性经 `Html::attrs()` 转义 + 属性名白名单校验；
3. **深度上限**：`MAX_DEPTH = 256` 防无限嵌套；
4. **URL 净化**：`safeUrl()` 拦截 `javascript:`/`vbscript:` 等伪协议；
5. **语义枚举白名单**：`variant`/`type` 等非法值回退默认，防 CSS 类注入；
6. **闭包自引用防护**：闭包返回构建器自身时返回空串。

## 7. 资源按路由名/路径引入

```php
use zxf\XfAdmin\XfAdmin;

// 按路由名引入（需先注册解析器）
XfAdmin::setRouteResolver(fn (string $name) => route($name));
echo XfAdmin::jsTag('@admin.dashboard');      // <script src="...">
echo XfAdmin::cssTag('@admin.dashboard');     // <link rel="stylesheet" ...>
echo XfAdmin::imgTag('@admin.logo', 'logo');  // <img src="..." alt="logo">

// 按包内资源路径引入
echo XfAdmin::jsTag('js/xfadmin.js', true);   // defer
echo XfAdmin::cssTag('css/xfadmin.css');
echo XfAdmin::fontFace('XF', 'fonts/xf.woff2');
```

## 8. DataSourceSwitcher 数据源切换器

同区块内切换多个数据源面板（前端自动绑定切换，无需内联 script）：

```php
echo XfAdmin::dataSourceSwitcher([
    'sources' => [
        ['key' => 'live', 'label' => '实时', 'active' => true],
        ['key' => 'history', 'label' => '历史'],
    ],
    'panes' => [
        'live' => '<p>实时数据面板</p>',
        'history' => '<p>历史数据面板</p>',
    ],
]);
```

面板唯一 id 可经 `DataSourceSwitcher::paneId($key, $prefix)` 静态方法复用。

## 9. Footer 页脚增强

```php
echo XfAdmin::footer([
    'text' => '© 2026 XX公司',
    'icp' => '粤ICP备2026000000号',               // ICP 备案号
    'police' => '粤公网安备 44030000000000号',     // 网安备案号
    'links' => [
        ['label' => '关于我们', 'url' => '/about'],
        ['label' => '联系我们', 'url' => '/contact'],
    ],
    'support' => '技术支持：XX团队',
    'brand_url' => '/',                            // 版权品牌跳转
]);
```

> 更多页脚配置项见 [配置文档](../01-getting-started/04-configuration.md) 的 `footer` 段。

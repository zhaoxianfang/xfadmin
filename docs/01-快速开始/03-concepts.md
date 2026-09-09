# 核心概念

理解这五个概念，就能读懂 XfAdmin 的全部行为。

## 1. 组件（Component）

**一切皆组件。** 每个 UI 元素都是一个继承 `zxf\XfAdmin\Components\Component` 的 PHP 类：

```php
$card = XfAdmin::card(['title' => '标题']);   // 返回 Component 实例
echo $card;                                   // __toString() → render() → HTML
$html = $card->render();                      // 等价
```

组件同时是**配置容器**与**渲染器**：

```php
$card = XfAdmin::card([])
    ->set('title', '动态设置标题')            // 点式写入
    ->set(['body' => '内容', 'footer' => '页脚'])
    ->addClass('shadow-sm')                   // 追加 class（与内部 class 合并去重）
    ->attr('data-role', 'panel')              // 追加任意 HTML 属性
    ->id('my-card');                          // 设置根元素 id

echo $card->get('title');        // '动态设置标题'
print_r($card->options());       // 完整配置数组
```

### 组件三要素

| 要素 | 方法 | 作用 |
|---|---|---|
| `defaults()` | 组件内定义 | 声明全部可用参数与默认值 |
| `assets()` | 组件内定义 | 声明依赖的第三方插件（自动去重加载） |
| `html()` | 组件内定义 | 生成 HTML |

## 2. 配置合并规则（⚠️ 最容易踩的坑）

构造时执行：

```php
$this->options = array_replace_recursive($this->defaults(), $options);
```

**三条推论**：

1. **递归合并，不是替换。** 嵌套数组会逐层合并：

```php
// defaults: ['options' => ['a' => 1, 'b' => 2]]
XfAdmin::foo(['options' => ['b' => 3]]);
// 结果：['options' => ['a' => 1, 'b' => 3]]   ← a 保留
```

2. **索引数组按「并集」合并（键位对齐），不是整体覆盖。**
   基类注释明确指出：若 `defaults` 中某数组键有默认值 `['csv','xlsx','json']`，
   调用方传 `['csv','json']` 会并集成 `['csv','json','json']`（键 0/1 取调用方、
   键 2 补默认值）。因此**设计可覆盖的列表型选项时默认值应留空 `[]`**，
   或在归一逻辑里去重。遇到"数组选项莫名多出重复项"先怀疑这里。

3. **表单类组件的公共字段用 `+` 合并**：

```php
// Input::defaults()
return $this->fieldDefaults() + ['type' => 'text', ...];
```

`+` 运算**左侧优先**，因此子类若重定义 `name`、`value`、`wrapper` 等同名键会被
`fieldDefaults()` 的值**静默覆盖**。

## 3. 渲染生命周期

```
XfAdmin::card([...])
   │
   ├─ __construct：array_replace_recursive(defaults(), $options)
   │
   └─ render()（或 echo / (string)）
        ├─ $plugins = $this->assets()            读取依赖（可含条件判断）
        ├─ Assets::instance()->plugin(...$plugins)  幂等注册 + 解析 deps + 收集 css/js
        └─ html()                                 返回 HTML 字符串
```

每个请求最后：

```
Page 组件内部：
  1) 先渲染 body（所有组件在这一步完成资源注册）
  2) 再渲染 head → Assets::head()（CSS + 主题脚本）
  3) 最后 Assets::scripts()（JS + 内联初始化）
  4) Assets::resetCollected()（同请求渲染多个完整页面时互不污染）
```

⚠️ **为什么必须"先 body 后 head"**：组件在渲染时才注册自己需要的 CSS/JS，
head 必须先收集完才能输出完整列表。`scripts()` 虽有兜底补 CSS 机制，但顺序上仍以先收集为佳。

⚠️ **render() 不做结果缓存**：同一实例多次渲染会各自生成独立 `uid`，互不干扰；
跨完整页面复用实例时也能在资源状态重置后重新注册初始化脚本。

## 4. 资源去重（Assets）

```php
// 三个组件都依赖 datatables
echo XfAdmin::dataTable([...]);
echo XfAdmin::dataTable([...]);
echo XfAdmin::table([...]);       // 静态表不需要

// 最终 <head>/<body> 中 datatables 的 CSS/JS 只出现一次
```

去重由三个集合实现：`$plugins`（插件名）、`$css`（文件路径）、`$js`（文件路径），
以及 `$inlineJs` / `$inlineCss`（按 key 或 `md5(内容)` 去重）。

```php
Assets::instance()->inlineJs($code, 'my-key');   // 同 key 只输出一次
Assets::instance()->inlineJs($code);             // 按内容去重
```

详细机制见 [03-资源与插件](../02-核心架构/03-assets.md)。

## 5. 转义策略（安全模型）

组件内部有三类槽位，决定了传入内容如何被处理：

| 槽位 | 方法 | 语义 | 适用 |
|---|---|---|---|
| **纯文本** | `e()` / `text()` | `htmlspecialchars(ENT_QUOTES)` | title、label、版权、按钮文案等 |
| **内容** | `raw()` | **原样输出** | body、content、footer、left/right 等 |
| **受控** | `enum()` / `cssLen()` / `cssColor()` / `safeUrl()` | 白名单或格式校验 | variant、size、style 中的长度/颜色、href/src |

```php
// 文本槽位：传字符串自动转义，传组件/闭包按 raw 渲染
$this->text($title);

// 内容槽位：支持组件实例、Stringable、闭包、数组
$this->raw($body);

// 受控槽位：非法值回退默认，杜绝注入
$this->enum($variant, self::ENUM_VARIANT, 'primary');
$this->cssLen($height, '320px');        // 仅接受 数字+单位
$this->safeUrl($url, '#');              // 拦截 javascript: 等伪协议
```

**调用方责任**：

- `body` / `content` 等**内容槽位**会原样输出 HTML —— 若把用户输入直接塞进去会产生 XSS。
  用户可控文本请用 `XfAdmin::raw()` 之外的方式，或先自行 `e()` 转义：

```php
use zxf\XfAdmin\Support\Html;

'body' => Html::e($userInput),            // 手动转义
'body' => XfAdmin::alert(['text' => $userInput]),   // 或用文本组件承载
```

- 内联 `<script>` 中输出变量必须用 `Html::scriptJson()`（带 `JSON_HEX_TAG`，防 `</script>` 断标签），
  属性中的 JSON 用 `Html::json()`。详见 [04-安全与转义](../02-核心架构/04-security.md)。

## 6. 前端协作：声明式初始化

PHP 组件输出带 `data-xf` 的元素，前端 `xfadmin.js` 自动扫描并初始化：

```html
<div data-xf="apexchart" data-xf-config='{"type":"line","series":[...]}'></div>
```

```js
XFAdmin.register('apexchart', function (el, config) {
    const chart = new ApexCharts(el, config);
    chart.render();
    return chart;
});
```

- `XFAdmin.scan(root)` 扫描 `root` 内所有 `[data-xf]`，跳过已初始化（`el.__xfInited`）的元素；
- `data-xf-config` 必须是合法 JSON（PHP 侧已用 `JSON_HEX_TAG|HEX_AMP|HEX_APOS|HEX_QUOT` 编码）；
- 依赖缺失（如没有 jQuery）时只警告一次，不阻断整页；
- AJAX 注入新内容后调用 `XFAdmin.scan(newNode)`；替换内容前调用 `XFAdmin.destroyWithin(oldNode)`。

## 7. 唯一 ID 机制

组件通过 `uid()` 生成自增 id，避免同页多个同类组件冲突：

```php
protected function uid(string $prefix = 'xf'): string
{
    return $prefix . '-' . (++self::$uidCounter);   // xf-1、xf-2 …
}

protected function resolveId(string $prefix): string
{
    // 优先级：->id('xxx') > ['id' => 'xxx'] > 自动生成（并回写）
}
```

因此**需要 JS 定位元素时请显式传 `id`**（如 `'id' => 'user-table'`），不要依赖自动 id。

## 8. 命名与别名

- 组件别名**大小写不敏感**：`XfAdmin::datatable()` 与 `XfAdmin::dataTable()` 等价；
- 多个别名可指向同一类：`topNav` / `topnav`、`dateRange` / `dateRangePicker`、
  `clipboard` / `clipboardButton`、`emptyState`(Layout) vs `empty`(UI)；
- PHP 保留字不能作类名：`switch` → 类名 `Toggle`（别名仍为 `switch`）、
  `empty` → 类名 `EmptyState`（别名为 `empty`）；
- ⚠️ `lockScreen` 在注册表中**先指向 `AuthPage` 后被 `LockScreen` 覆盖**，
  因此 `XfAdmin::lockScreen()` 得到的是独立整页组件；要用 AuthPage 的锁屏语义
  请显式 `XfAdmin::authPage(['type' => 'lock-screen'])`。

下一步：[04-全局配置](04-configuration.md)

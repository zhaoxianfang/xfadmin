# 组件基类（Component）

所有组件的公共能力都来自 `zxf\XfAdmin\Components\Component`。
本文是**组件开发与使用**的权威参考。

> 源码：`src/Components/Component.php`（466 行）

## 1. 类定义

```php
abstract class Component implements Stringable
{
    protected array $options = [];     // 合并后的配置
    protected array $attributes = [];  // 附加到根元素的自定义属性
    private static int $uidCounter = 0;

    abstract protected function html(): string;   // 子类实现
    protected function defaults(): array { return []; }   // 子类覆写
    protected function assets(): array  { return []; }    // 子类覆写
}
```

## 2. 生命周期方法

| 方法 | 可见性 | 说明 |
|---|---|---|
| `__construct(array $options = [])` | public | `array_replace_recursive(defaults(), $options)` |
| `make(array $options = []): static` | public static | 静态工厂，推荐用法 |
| `defaults(): array` | protected | 默认配置（子类覆写） |
| `assets(): array` | protected | 依赖插件名数组（子类覆写） |
| `html(): string` | protected abstract | 生成 HTML（子类实现） |
| `render(): string` | public | 注册资源 + 调用 `html()` |
| `__toString(): string` | public | 同 `render()` |

```php
class MyWidget extends Component
{
    protected function defaults(): array
    {
        return ['title' => null, 'body' => '', 'variant' => 'primary'];
    }

    protected function assets(): array
    {
        return [];                       // 需要时返回 ['select2', 'jquery']
    }

    protected function html(): string
    {
        $id = $this->resolveId('xf-my');
        return '<div' . $this->attrs(['id' => $id, 'class' => 'my-widget']) . '>'
            . $this->e($this->get('title'))
            . $this->raw($this->get('body'))
            . '</div>';
    }
}
```

## 3. 配置读写

```php
$card = XfAdmin::card(['title' => 'A']);

$card->set('title', 'B');                 // 点式写入
$card->set(['body' => 'x', 'footer' => 'y']);   // 数组（递归合并）
$card->set('options.deep.key', 1);        // 支持 a.b.c 深层路径

$card->get('title');                      // 'B'
$card->get('missing', '默认');             // '默认'

$card->options();                         // 完整配置数组
```

## 4. 属性与 class

```php
XfAdmin::card(['title' => 'x'])
    ->id('my-card')                        // id
    ->addClass('shadow-sm', 'mb-3')        // 追加 class（与内部 class 合并去重）
    ->attr('data-role', 'panel')           // 任意属性
    ->attr(['data-x' => '1', 'data-y' => '2']);
```

`attrs()` 合并规则：

```php
protected function attrs(array $base = []): string
{
    $merged = $base;
    foreach ($this->attributes as $name => $value) {
        if ($name === 'class') {
            $merged['class'] = Html::cls($base['class'] ?? '', $value);   // class 合并
        } else {
            $merged[$name] = $value;                                      // 其余直接覆盖
        }
    }
    return Html::attrs($merged);
}
```

⚠️ `attr('x', true)` 输出 `x`（布尔属性）；`attr('x', false)` 或 `null` 则**不输出该属性**。

## 5. 安全助手（重点）

### 5.1 `e(mixed $value): string` —— 文本转义

```php
$this->e($title);
// htmlspecialchars($v, ENT_QUOTES, 'UTF-8', false)
// null → ''；数组/不可字符串化对象 → json_encode 后转义
```

### 5.2 `raw(mixed $value): string` —— 内容槽位（原样输出）

```php
$this->raw($body);
// null → ''；Closure → 调用后递归；数组 → 逐个递归拼接；其余 → (string)
```

支持四种输入：

```php
'body' => '<p>HTML</p>',
'body' => XfAdmin::alert(['text' => 'x']),        // Component（Stringable）
'body' => fn () => XfAdmin::table([...]),          // 闭包（惰性）
'body' => [$comp1, '<hr>', $comp2],                // 数组
```

### 5.3 `text(mixed $value): string` —— 「语义纯文本但允许传组件」

```php
$this->text($title);
// Stringable / Closure / 数组 → raw()；其余 → e()
```

与 `raw()` 的区别：传字符串时**一律转义**。用于标题、版权、按钮文案这类字段。

### 5.4 `enum(mixed $value, array $allowed, string $default): string`

枚举白名单，非法值回退默认 —— 防止任意 CSS 类 / 属性注入。

```php
$this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');
```

内置常量：

```php
protected const ENUM_VARIANT =
    ['primary','secondary','success','danger','warning','info','light','dark','link'];
protected const ENUM_VARIANT_OUTLINE =
    ['outline-primary','outline-secondary','outline-success','outline-danger',
     'outline-warning','outline-info','outline-light','outline-dark'];
protected const ENUM_SIZE      = ['sm', 'lg'];
protected const ENUM_PLACEMENT = ['top','bottom','left','right','start','end'];
protected const ENUM_BREAKPOINT = ['sm','md','lg','xl','xxl'];
```

### 5.5 CSS 值安全助手（防 `style` 注入）

`e()` 不转义 `; : ( ) /`，无法阻止 `10px;position:fixed;inset:0` 这类分号注入。
因此涉及 `style` 属性的选项必须用以下方法：

| 方法 | 接受格式 | 默认 |
|---|---|---|
| `cssLen($v, $default)` | `-?数字 + px/%/rem/em/vh/vw/pt/ch/fr` | `''` |
| `cssRatio($v, $default = '4/3')` | `N/M` 或纯数字（`x` `X` `:` 归一为 `/`） | `4/3` |
| `cssColor($v, $default)` | `#hex`、`rgb()/rgba()/hsl()/hsla()`、具名色、`var(--x)`、`transparent` | `''` |
| `cssBackground($v, $default)` | 纯色、`linear/radial/conic-gradient()`、`url()`（限 http/https/`/`/`data:image/`） | `''` |

```php
'height' => '320px',            // ✅
'height' => '320px;position:fixed',  // ❌ 被拒绝，回退默认
```

### 5.6 `safeUrl(mixed $value, string $default = '#'): string`

| 判定 | 结果 |
|---|---|
| 空串 / `#` | 原样返回 |
| `javascript:void(0)`、`javascript:;`、裸 `javascript:` | 放行（设计性 no-op） |
| `//host`、`http(s)://`、`/`、`#`、`mailto:`、`tel:`、`data:image/`、无 scheme 相对路径 | 放行 |
| 其它伪协议（`javascript:alert(1)`、`vbscript:`、非图片 `data:`） | 拦截，返回 `$default` |

用于所有 `href` / `url` / `src` 选项。

### 5.7 `img(mixed $path): string`

```php
protected function img(mixed $path): string
{
    $p = trim((string) $path);
    if ($p === '') return 'data:image/gif;base64,R0lGODlhAQAB…';  // 透明 1×1，防破图
    if (preg_match('#^(?:https?:)?//|^data:#i', $p)) return $p;   // 外链 / data URI 原样
    return \zxf\XfAdmin\XfAdmin::asset('images/' . ltrim($p, '/'));
}
```

静态上下文等价物：`XfAdmin::img($path)`。

⚠️ **禁止**在组件内直接拼接 `XfAdmin::asset('images/' . ltrim($path))` —— 会丢失
外链 / data URI 支持。一律用 `$this->img()` 或 `XfAdmin::img()`。

## 6. 栅格助手 `gridCol()`

```php
$this->gridCol(6);                        // 'col-12 col-md-6 col-xl-6'
$this->gridCol(['md' => 6, 'xl' => 3]);   // 'col-12 col-md-6 col-xl-3'
$this->gridCol(null);                     // ''（不需要包裹列）
```

- 断点走 `ENUM_BREAKPOINT` 白名单（非法断点跳过该项）；
- 列数夹紧 1~12（超范围回退 `col-12`）。

> ⚠️ `Col` 组件自身**未使用** `gridCol()`，其 `width/offset/order` 为直接拼接，
> 需调用方保证值可信。

## 7. 唯一 ID

```php
protected function uid(string $prefix = 'xf'): string   // 'xf-1'、'xf-2'…
protected function resolveId(string $prefix): string
{
    if (!empty($this->attributes['id'])) return (string) $this->attributes['id'];  // ->id()
    if ($this->get('id')) return (string) $this->get('id');                        // ['id'=>…]
    return $this->attributes['id'] = $this->uid($prefix);                          // 生成并回写
}
```

## 8. 声明式初始化属性 `initAttrs()`

```php
protected function initAttrs(string $widget, array $config = []): array
{
    $attrs = ['data-xf' => $widget];
    if ($config !== []) {
        $attrs['data-xf-config'] = json_encode($config,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    return $attrs;
}
```

用法：

```php
$attrs = $this->initAttrs('apexchart', ['type' => 'line', 'series' => $series]);
// ['data-xf' => 'apexchart', 'data-xf-config' => '{"type":"line",…}']

// 合并到根元素
'<div' . $this->attrs(['class' => 'chart'] + $attrs) . '></div>';
```

⚠️ 四个 `JSON_HEX_*` 标志是纵深防御：
- `HEX_TAG` —— 防 `</script>` 断标签；
- `HEX_APOS` / `HEX_QUOT` —— 防属性引号逃逸；
- `HEX_AMP` —— 防 `&` 被误解析为实体。

## 9. 资源注册

```php
protected function assets(): array
{
    $a = [];
    if ($this->get('mask')) $a[] = 'inputmask';
    if ($this->get('tags')) $a[] = 'tagify';
    return $a;                       // 条件依赖：只在真正用到时加载
}
```

`render()` 中：

```php
$plugins = $this->assets();
if ($plugins !== []) {
    Assets::instance()->plugin(...$plugins);   // 幂等，自动解析 deps
}
```

详见 [03-资源与插件](03-assets.md)。

## 10. 完整方法清单

| 分类 | 方法 | 签名 |
|---|---|---|
| 工厂 | `make` | `static make(array $options = []): static` |
| 渲染 | `render` | `render(): string` |
| 渲染 | `__toString` | `__toString(): string` |
| 配置 | `set` | `set(string\|array $key, mixed $value = null): static` |
| 配置 | `get` | `get(string $key, mixed $default = null): mixed` |
| 配置 | `options` | `options(): array` |
| 属性 | `attr` | `attr(string\|array $name, mixed $value = true): static` |
| 属性 | `addClass` | `addClass(string ...$classes): static` |
| 属性 | `id` | `id(string $id): static` |
| 属性 | `attrs` | `protected attrs(array $base = []): string` |
| 转义 | `e` | `protected e(mixed $value): string` |
| 转义 | `raw` | `protected raw(mixed $value): string` |
| 转义 | `text` | `protected text(mixed $value): string` |
| 受控 | `enum` | `protected enum(mixed $v, array $allowed, string $default): string` |
| 受控 | `cssLen` | `protected cssLen(mixed $v, string $default = ''): string` |
| 受控 | `cssRatio` | `protected cssRatio(mixed $v, string $default = '4/3'): string` |
| 受控 | `cssColor` | `protected cssColor(mixed $v, string $default = ''): string` |
| 受控 | `cssBackground` | `protected cssBackground(mixed $v, string $default = ''): string` |
| 受控 | `safeUrl` | `protected safeUrl(mixed $v, string $default = '#'): string` |
| 栅格 | `gridCol` | `protected gridCol(mixed $width): string` |
| 资源 | `img` | `protected img(mixed $path): string` |
| ID | `uid` | `protected uid(string $prefix = 'xf'): string` |
| ID | `resolveId` | `protected resolveId(string $prefix): string` |
| 初始化 | `initAttrs` | `protected initAttrs(string $widget, array $config = []): array` |
| 子类实现 | `defaults` | `protected defaults(): array` |
| 子类实现 | `assets` | `protected assets(): array` |
| 子类实现 | `html` | `protected html(): string` |

## 11. 编写组件的检查清单

1. ✅ 类名避开 PHP 保留字（`switch` → `Toggle`、`empty` → `EmptyState`）；
2. ✅ `defaults()` 声明全部参数，列表型可覆盖选项默认值留 `[]`；
3. ✅ 结构性枚举字段（variant/size/type/placement/tag/align）必须 `enum()`；
4. ✅ 文本展示字段（title/label/text/name/value/message）必须 `e()` / `text()`；
5. ✅ 内容容器（body/content/slot/footer）用 `raw()`，并在注释中注明"原样输出 HTML"；
6. ✅ `style` 中的长度/颜色/背景用 `cssLen()` / `cssColor()` / `cssBackground()`；
7. ✅ 链接类选项用 `safeUrl()`；
8. ✅ 图片路径用 `$this->img()`；
9. ✅ 需要 JS 初始化时用 `initAttrs()` 输出 `data-xf` + `data-xf-config`；
10. ✅ 需要前端资源的在 `assets()` 声明；
11. ✅ 需要定位的元素用 `resolveId()` 生成 id。

下一步：[03-资源与插件](03-assets.md)

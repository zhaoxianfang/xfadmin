# 门面与助手

## 1. `XfAdmin` 门面（静态 API 全表）

> 源码：`src/XfAdmin.php`

### 1.1 组件工厂

| 方法 | 签名 | 说明 |
|---|---|---|
| `__callStatic` | `XfAdmin::{alias}(array $options = []): Component` | 创建组件（226 个别名） |
| `component` | `component(string $alias, array $options = []): Component` | 显式工厂，未注册抛 `InvalidArgumentException` |
| `extend` | `extend(string $alias, string $class): void` | 注册/覆盖组件 |
| `componentList` | `componentList(): array` | 全部 `alias => class` |
| `has` | `has(string $alias): bool` | 别名是否已注册（大小写不敏感） |

```php
XfAdmin::card(['title' => 'x']);                  // 魔术调用
XfAdmin::component('card', ['title' => 'x']);     // 等价
XfAdmin::has('dataTable');                        // true
count(XfAdmin::componentList());                  // 226
```

⚠️ 未注册别名抛异常，异常信息含全部可用别名。别名查找**大小写不敏感**
（`XfAdmin::datatable()` 也能命中）。

### 1.2 全局配置

| 方法 | 签名 | 说明 |
|---|---|---|
| `config` | `config(?array $config = null): array` | 传数组 = 递归合并并返回；不传 = 只读返回 |
| `setting` | `setting(string $key, mixed $default = null): mixed` | 点号读取，如 `setting('brand.logo')` |
| `version` | `version(): string` | 返回 `XfAdmin::VERSION`（`2.1.0`） |

`config()` 的副作用：

```php
if (isset($config['assets_url'])) Assets::instance()->setBaseUrl((string) $config['assets_url']);
if (array_key_exists('version', $config)) Assets::instance()->setVersion($config['version'] ?: null);
```

### 1.3 资源管理

| 方法 | 签名 | 说明 |
|---|---|---|
| `assets` | `assets(): Assets` | 资源管理器单例 |
| `head` | `head(): string` | `<head>` 内资源（CSS + 主题脚本） |
| `scripts` | `scripts(): string` | `</body>` 前资源（JS + 内联脚本） |
| `asset` | `asset(string $path): string` | 解析为完整资源 URL（幂等 + 版本号） |
| `img` | `img(string $path): string` | 图片解析：外链/data 原样，空→透明 GIF，其余 `images/` |

### 1.4 CSRF

| 方法 | 签名 | 说明 |
|---|---|---|
| `setCsrfResolver` | `setCsrfResolver(Closure $resolver): void` | 注册令牌解析器 |
| `csrfToken` | `csrfToken(): string` | 取令牌（解析器 → `setting('csrf_token')` → `''`） |

```php
// Laravel 服务提供者已自动注册；其它框架手工注册
XfAdmin::setCsrfResolver(fn () => csrf_token());
```

### 1.5 服务端数据

| 方法 | 签名 |
|---|---|
| `dataResponse` | `dataResponse(iterable\|object $rows, array $params = [], array $options = []): array` |

详见 [05-服务端数据协议](05-data-protocol.md)。

### 1.6 auth 语义别名自动注入

`XfAdmin::signIn()` 等语义别名会自动注入 `type`（除非显式传 `type`）：

| 别名 | 注入 type |
|---|---|
| `signIn` | `sign-in` |
| `signUp` | `sign-up` |
| `resetPass` | `reset-pass` |
| `newPass` | `new-pass` |
| `twoFactor` | `two-factor` |
| `lockScreen` | `lock-screen`（但见下方 ⚠️） |
| `deleteAccount` | `delete-account` |
| `successMail` | `success-mail` |
| `loginPin` | `login-pin` |

⚠️ 注册表中 `lockScreen` **先指向 `AuthPage` 后被 `LockScreen::class` 覆盖**，
因此 `XfAdmin::lockScreen()` 得到的是独立整页组件，注入的 `type` 不生效。
要用 AuthPage 的锁屏语义：`XfAdmin::authPage(['type' => 'lock-screen'])`。

## 2. 全局助手函数

> 源码：`src/helpers.php`（Composer `files` 自动加载）

| 函数 | 等价 |
|---|---|
| `xf_admin(?string $component = null, array $options = [])` | 无参返回 `XfAdmin::class`；有参返回组件实例 |
| `xf_asset(string $path)` | `XfAdmin::asset($path)` |
| `xf_head()` | `XfAdmin::head()` |
| `xf_scripts()` | `XfAdmin::scripts()` |

```php
echo xf_admin('card', ['title' => '标题', 'body' => '内容']);
$class = xf_admin();            // 'zxf\XfAdmin\XfAdmin'，可用于 $class::page([...])
echo xf_asset('images/logo.png');
```

```blade
<head>{!! xf_head() !!}</head>
<body>
    {!! $content !!}
    {!! xf_scripts() !!}
</body>
```

> ThinkPHP 服务还注册了 `xfadmin()` 助手，行为与 `xf_admin()` 一致。

## 3. Laravel Facade

```php
use XfAdmin;      // 别名已由 extra.laravel.aliases 注册

XfAdmin::page([...]);        // 转发到 \zxf\XfAdmin\XfAdmin::page()
XfAdmin::card([...]);
```

实现类 `zxf\XfAdmin\Laravel\Facades\XfAdmin`：

```php
protected static function getFacadeAccessor() { return 'xfadmin'; }
// __callStatic 全部转发到 \zxf\XfAdmin\XfAdmin::$method(...)
```

⚠️ 转发**绕过容器解析**（组件是无状态静态工厂），因此 Facade 与直接调用完全等价。

## 4. Blade 指令

| 指令 | 展开 |
|---|---|
| `@xfHead` | `<?php echo \zxf\XfAdmin\XfAdmin::head(); ?>` |
| `@xfScripts` | `<?php echo \zxf\XfAdmin\XfAdmin::scripts(); ?>` |
| `@xf('card', [...])` | `<?php echo \zxf\XfAdmin\XfAdmin::component('card', [...]); ?>` |

```blade
@extends('layouts.admin')

@section('content')
    @xf('card', ['title' => '统计', 'body' => $body])
    @xf('dataTable', ['id' => 't1', 'columns' => $columns, 'ajax' => '/api/x'])
@endsection
```

## 5. Support\Html（静态工具）

```php
use zxf\XfAdmin\Support\Html;

Html::e($value);                        // HTML 转义
Html::attrs(['class' => 'a', 'id' => 'b', 'disabled' => true]);
// => ' class="a" id="b" disabled'
Html::cls('a', ['b' => true, 'c' => false], 'd');   // => 'a b d'
Html::json($cfg);                       // data-* 属性用 JSON
Html::scriptJson($cfg);                 // 内联 <script> 用 JSON（含 HEX_TAG）
Html::get($arr, 'a.b.c', '默认');        // 点式读取
Html::set($arr, 'a.b.c', 123);          // 点式写入（引用）
```

详见 [04-安全与转义](04-security.md)。

## 6. Support\DemoMenu

演示菜单数据（5 级子菜单 + 两种 Mega 面板），用于 `demo/pages/topnav.php`
与 wsf 侧的 TopNav 演示：

```php
use zxf\XfAdmin\Support\DemoMenu;

DemoMenu::menu();         // 侧边栏菜单（多级）
DemoMenu::topNavMenu();   // 水平导航菜单（含 mega）
```

## 7. 自检片段

```php
// 版本与组件数
echo XfAdmin::version();                 // 2.1.0
echo count(XfAdmin::componentList());    // 226

// 资源 URL
echo XfAdmin::asset('css/app.min.css');

// 快速渲染测试
echo XfAdmin::card(['title' => 'ok', 'body' => 'ok']);

// 数据协议测试
$resp = XfAdmin::dataResponse(
    [['id' => 1, 'name' => 'a'], ['id' => 2, 'name' => 'b']],
    ['draw' => 1, 'start' => 0, 'length' => 1]
);
print_r($resp);
// ['draw' => 1, 'recordsTotal' => 2, 'recordsFiltered' => 2, 'data' => [['id'=>1,'name'=>'a']]]
```

下一步：[07-扩展机制](07-extending.md)

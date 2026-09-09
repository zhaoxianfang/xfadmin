# 安装与接入

## 1. 环境要求

| 项目 | 要求 | 说明 |
|---|---|---|
| PHP | `>= 8.2` | 使用了 `readonly`、`enum`、构造器属性提升等特性 |
| 扩展 | `ext-json` | 组件配置序列化 |
| 框架（可选） | Laravel `^11.0 \| ^12.0` 或 ThinkPHP `^8.0` | 非必须，核心渲染层**框架无关** |
| 前端构建 | 无 | 所有第三方库随包内置，无需 Node / Webpack / Vite |

> 包内不含任何 PHP 框架依赖（`composer.json` 的 `require` 仅有 `php` 与 `ext-json`），
> Laravel 与 ThinkPHP 仅列在 `suggest` 中，通过 `extra.laravel` / `extra.think` 自动注册。

## 2. Composer 安装

```bash
composer require zxf/xfadmin
```

安装后目录结构（位于 `vendor/zxf/xfadmin/`）：

```
vendor/zxf/xfadmin/
├── config/xfadmin.php        # 默认配置
├── src/                      # PHP 源码（229 个文件）
│   ├── XfAdmin.php           # 门面：组件工厂 / 全局配置 / 资源输出
│   ├── Assets/Assets.php     # 资源管理器（插件表 + 去重）
│   ├── Components/           # 全部组件（Layout/UI/Form/Table/Chart/Data/Misc/Grid/Navigation）
│   ├── Support/              # Html 工具类、DataSet 服务端数据、DemoMenu
│   ├── Laravel/              # 服务提供者、Facade、AssetController
│   ├── ThinkPHP/             # 服务、发布命令
│   └── helpers.php           # 全局助手函数
├── resources/assets/         # 静态资源（css/js/images/plugins/data）
└── demo/                     # 独立可运行的演示站
```

## 3. Laravel 11 / 12 接入

服务提供者与门面通过 `composer.json` 的 `extra.laravel` **自动注册**，无需手工添加。

### 3.1 发布静态资源与配置

```bash
# 发布资源到 public/zxf/xfadmin（推荐生产环境使用，由 Web 服务器直接服务）
php artisan vendor:publish --tag=xfadmin-assets

# 发布配置文件到 config/xfadmin.php（可选，用于覆盖默认配置）
php artisan vendor:publish --tag=xfadmin-config
```

> **不发布也能用**：包内 `XfAdminServiceProvider` 会注册一条自托管路由
> （默认前缀 `/zxf/xfadmin`），由 `AssetController` 直接读取 `vendor/` 内的资源并输出，
> 带 ETag 与一年强缓存。开发环境推荐不发布，避免 `vendor` 更新后 `public` 里是旧资源。

### 3.2 控制器中渲染

```php
<?php

namespace App\Http\Controllers\Admin;

use zxf\XfAdmin\XfAdmin;

class DashboardController extends Controller
{
    public function index()
    {
        return response(XfAdmin::page([
            'title'   => '仪表盘',
            'layout'  => 'vertical',
            'menu'    => config('admin.menu'),      // 侧边栏菜单数据
            'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
        ]));
    }
}
```

### 3.3 Blade 中使用

```blade
{{-- 整页 --}}
{!! XfAdmin::page(['title' => '首页', 'content' => $html]) !!}

{{-- 局部组件 --}}
{!! XfAdmin::card(['title' => '统计', 'body' => $body]) !!}

{{-- 或 Blade 指令（见 04-进阶指南/10-laravel.md） --}}
@xf('card', ['title' => '统计', 'body' => $body])
```

### 3.4 自定义布局（手动接管 head / scripts）

```blade
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    {!! XfAdmin::head() !!}      {{-- 必须先于任何组件渲染？见下方说明 --}}
</head>
<body>
    {!! $content !!}
    {!! XfAdmin::scripts() !!}   {{-- 必须在所有组件渲染完成后输出 --}}
</body>
</html>
```

⚠️ **顺序陷阱**：`XfAdmin::head()` 输出的是「此刻为止已注册的 CSS」。
若组件在 `head()` 之后才渲染，其 CSS 会由 `scripts()` **兜底补输出**（`Assets::scripts()`
会对比 `head()` 时记录的列表补发差异），因此上面的写法可用，但插件 CSS 会出现在
`</body>` 前，可能导致首屏闪烁。**推荐做法**：先在控制器里把内容渲染成字符串
（组件渲染即注册资源），再输出到模板：

```php
$content = (string) XfAdmin::card(['title' => '欢迎', 'body' => '...']); // 先渲染
return view('admin.page', ['content' => $content, 'title' => '首页']);
```

## 4. ThinkPHP 8 接入

在 `config/service.php` 中注册服务：

```php
return [
    \zxf\XfAdmin\ThinkPHP\Service::class,
];
```

发布资源：

```bash
php think xfadmin:publish            # 复制到 public/zxf/xfadmin
php think xfadmin:publish --force    # 覆盖已存在文件
```

使用：

```php
use zxf\XfAdmin\XfAdmin;

public function index()
{
    return XfAdmin::page([
        'title'   => '仪表盘',
        'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello']),
    ]);
}
```

或使用助手函数：

```php
return xfadmin('page', ['title' => '仪表盘', 'content' => $body]);
```

详见 [11-ThinkPHP 集成](../04-进阶指南/11-thinkphp.md)。

## 5. 原生 PHP（无框架）

核心层不依赖任何框架，只需引入 Composer 自动加载 + 合并一次配置：

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

// 1) 合并全局配置（可选，不调用则使用内置默认值）
XfAdmin::config(require __DIR__ . '/vendor/zxf/xfadmin/config/xfadmin.php');

// 2) 直接 echo 组件
echo XfAdmin::page([
    'title'   => '独立页面',
    'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
]);
```

包内 `demo/` 就是纯原生实现，可直接运行：

```bash
php -S 127.0.0.1:8900 demo/index.php
# 或（含 Mock API 路由）
php -S 127.0.0.1:8900 demo/router.php
```

浏览器打开 `http://127.0.0.1:8900` 即可看到完整演示。

## 6. 静态资源工作原理

| 场景 | 资源 URL | 服务方 |
|---|---|---|
| 未发布（默认） | `/zxf/xfadmin/css/app.min.css?v=2.1.0` | Laravel：`AssetController` 路由；ThinkPHP/原生：需自行配置路由或直接发布 |
| 已发布 | `/zxf/xfadmin/...` | Web 服务器直接读取 `public/zxf/xfadmin` |
| 远程 CDN | 配置 `assets_url` 为 `https://cdn.example.com/xfadmin` | CDN（`assets_url` 含 `http(s)://` 时不再注册自托管路由） |

资源 URL 统一由 `Assets::url()` 生成，特性：

- **幂等**：对已含资源基址的路径不会重复拼接（避免 `assets_url` 拼两遍）；
- **自动剥离旧版本号**：二次 `asset()` 时去掉已带的 `?v=`；
- **统一追加版本号**：末尾追加 `?v={version}`，改版本即刷新浏览器缓存。

```php
XfAdmin::asset('images/logo.png');
// => /zxf/xfadmin/images/logo.png?v=2.1.0

XfAdmin::asset('/zxf/xfadmin/images/logo.png?v=1.0.0');  // 幂等
// => /zxf/xfadmin/images/logo.png?v=2.1.0
```

## 7. 验证安装

```php
// 版本自检
echo XfAdmin::version();          // 2.1.0（XfAdmin::VERSION）

// 组件注册表自检
$list = XfAdmin::componentList();
echo count($list);                // 226

// 渲染自检（应输出 <div class="card">…）
echo XfAdmin::card(['title' => 'ok', 'body' => '安装成功']);
```

若无任何输出或报类不存在，检查：

1. `composer dump-autoload` 是否执行；
2. PHP 版本是否 ≥ 8.2（`php -v`）；
3. 页面是否输出了 `XfAdmin::head()` / `scripts()`（否则没有 CSS/JS，看起来像"没生效"）。

下一步：[02-五分钟上手](02-quickstart.md)

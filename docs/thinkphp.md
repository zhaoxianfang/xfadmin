# ThinkPHP 8 集成

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

## 安装

```bash
composer require xfadmin/xfadmin
```

## 注册服务

编辑 `config/service.php`：

```php
return [
    \XfAdmin\ThinkPHP\Service::class,
];
```

## 发布静态资源

```bash
php think xfadmin:publish
```

资源会复制到 `public/vendor/xfadmin`（对应默认 `assets_url`）。

## 配置

发布后编辑 `config/xfadmin.php`（若无则创建），键同 Laravel 版本：

```php
return [
    'assets_url' => '/vendor/xfadmin',
    'version'    => '1.0.0',
    'theme'      => 'light',
    'layout'     => 'vertical',
];
```

## 在控制器中使用

```php
namespace app\controller;

use XfAdmin\XfAdmin;

class Index
{
    public function index()
    {
        return XfAdmin::page([
            'title'   => '仪表盘',
            'menu'    => $this->menu(),
            'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
        ]);
    }

    private function menu(): array
    {
        return [
            ['text' => '首页', 'icon' => 'ti ti-home', 'url' => url('index/index')->build()],
        ];
    }
}
```

## 在模板中使用

ThinkPHP 模板里可直接调用（若已注册助手函数）：

```php
{:xfadmin('card', ['title' => '卡片', 'body' => '内容'])}
```

或在模板顶部 `use`：

```php
<?php use XfAdmin\XfAdmin; ?>
<?= XfAdmin::dataTable(['columns' => ['ID', '名称'], 'data' => $list]) ?>
```

## CSRF

ThinkPHP 下 `form` 组件的 `csrf` 选项会输出 `{:token()}`（若模板引擎解析）或隐藏 token 字段。可关闭后自行处理：

```php
XfAdmin::form(['csrf' => false, 'fields' => [...]]);
```

## 与 Laravel 的差异

| 项 | Laravel | ThinkPHP |
|----|---------|----------|
| 服务注册 | 自动（extra.laravel） | 手动加 `Service` |
| 资源发布 | `vendor:publish` | `php think xfadmin:publish` |
| CSRF | `@csrf` token | `token()` |
| 门面 | `XfAdmin\Laravel\Facades\XfAdmin` | 直接用 `XfAdmin\XfAdmin` |

其余组件 API 完全一致。

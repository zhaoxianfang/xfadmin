# ThinkPHP 集成

## 1. 自动注册

`composer.json`：

```json
"extra": {
    "think": {
        "services": ["zxf\\XfAdmin\\ThinkPHP\\Service"],
        "config": { "xfadmin": "config/xfadmin.php" }
    }
}
```

若框架未自动发现，手工在 `config/service.php` 注册：

```php
return [
    \zxf\XfAdmin\ThinkPHP\Service::class,
];
```

## 2. 服务（`src/ThinkPHP/Service.php`）

| 方法 | 行为 |
|---|---|
| `register()` | `$app->bind('xfadmin', XfAdmin::class)`；注册全局助手 `xfadmin()` |
| `boot()` | 读 `config('xfadmin')`，为空则 `require config/xfadmin.php`；`XfAdmin::config($config)`；注册 `PublishCommand` |

## 3. 助手函数

```php
// 创建组件
echo xfadmin('card', ['title' => '标题', 'body' => '内容']);

// 无参返回类名（用于静态调用）
$class = xfadmin();          // 'zxf\XfAdmin\XfAdmin'
echo $class::page([...]);

// 资源
echo xf_head();
echo xf_scripts();
echo xf_asset('images/logo.png');
```

> 包内 `src/helpers.php` 还提供 `xf_admin()`，与 `xfadmin()` 行为一致。

## 4. 发布资源

```bash
php think xfadmin:publish            # 复制到 public/zxf/xfadmin
php think xfadmin:publish --force    # 覆盖已存在文件
```

| 行为 | 说明 |
|---|---|
| 源 → 目标 | `vendor/zxf/xfadmin/resources/assets` → `{rootPath}public/zxf/xfadmin` |
| 保护逻辑 | 目标已存在文件且未加 `--force` → 警告并**跳过**（exit 0，不覆盖） |
| 错误 | 源目录不存在 → 报错 exit 1 |
| 复制 | `RecursiveDirectoryIterator` + `SELF_FIRST`；非 force 时已存在的目标文件跳过 |

⚠️ ThinkPHP **没有** Laravel 那样的资源自托管路由。未发布时页面会 404，
因此**必须先执行 `php think xfadmin:publish`**。

## 5. 控制器示例

```php
<?php

namespace app\admin\controller;

use think\facade\Request;
use zxf\XfAdmin\XfAdmin;

class Index
{
    public function index()
    {
        return XfAdmin::page([
            'title'   => '仪表盘',
            'menu'    => config('admin.menu'),
            'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello XfAdmin']),
        ]);
    }

    public function users()
    {
        // DataTable 服务端数据源
        return json(XfAdmin::dataResponse(
            \app\admin\model\User::where('status', 1),
            Request::param(),
            [
                'searchable' => ['name', 'email'],
                'filters'    => [
                    'keyword'   => ['name', 'email'],
                    'date_from' => ['field' => 'create_time', 'op' => 'date_from'],
                ],
            ]
        ));
    }
}
```

⚠️ `DataSet` 通过鸭子类型识别查询构造器（`count()` + `forPage()` + `get()`）。
ThinkPHP 的 `Query` 对象具备这些方法，但**字段与排序语法需与 Laravel 兼容**。
若遇到不兼容，可先 `->select()->toArray()` 转为数组再用数组管线：

```php
$rows = \app\admin\model\User::select()->toArray();
return json(XfAdmin::dataResponse($rows, Request::param(), [...]));
```

## 6. 配置

```php
// config/xfadmin.php
return [
    'assets_url' => '/zxf/xfadmin',
    'version'    => '2.1.0',
    'theme'      => ['skin' => 'modern', 'mode' => 'light', 'menu_color' => 'gradient'],
    'brand'      => ['name' => '我的后台'],
    'footer'     => ['text' => '© 2026 我的公司'],
];
```

修改后清运行时缓存：

```bash
php think clear          # 或
rm -rf runtime/cache/*
```

## 7. 路由

```php
// route/app.php
Route::group('admin', function () {
    Route::get('/', 'admin.Index/index');
    Route::get('users', 'admin.User/index');
    Route::get('api/users', 'admin.User/api');
});
```

## 8. CSRF 与 AJAX

ThinkPHP 默认无 CSRF 中间件。若自行启用，请注册解析器：

```php
// 在服务或中间件中
XfAdmin::setCsrfResolver(function () {
    return request()->token();      // 或你的令牌生成逻辑
});
```

前端 `XFAdmin.csrf()` 读 `<meta name="csrf-token">`，
`Page` 组件会自动输出该 meta（有令牌时）。

## 9. 常见问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 页面无样式 | 未发布资源 | `php think xfadmin:publish` |
| `xfadmin()` 未定义 | 服务未注册 | 检查 `config/service.php` |
| 资源 URL 404 | `assets_url` 与发布目录不一致 | 统一为 `/zxf/xfadmin` |
| 服务端表格报错 | 查询构造器方法不兼容 | 转数组后再交给 `dataResponse` |
| 改配置不生效 | runtime 缓存 | `php think clear` |

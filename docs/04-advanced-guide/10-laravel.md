# Laravel 集成

## 1. 自动注册

`composer.json`：

```json
"extra": {
    "laravel": {
        "providers": ["zxf\\XfAdmin\\Laravel\\XfAdminServiceProvider"],
        "aliases": { "XfAdmin": "zxf\\XfAdmin\\Laravel\\Facades\\XfAdmin" }
    }
}
```

无需手工添加到 `config/app.php`。

## 2. 服务提供者

> 源码：`src/Laravel/XfAdminServiceProvider.php`

### `register()`

```php
$this->mergeConfigFrom(__DIR__.'/../../config/xfadmin.php', 'xfadmin');

$this->app->singleton('xfadmin', function () {
    XfAdmin::config(config('xfadmin', []));
    return new XfAdmin();
});
```

### `boot()`

1. 再次 `XfAdmin::config(config('xfadmin'))`（不解析 singleton 也生效）；
2. 控制台下注册发布：
   - `--tag=xfadmin-assets`：`resources/assets` → `public_path('zxf/xfadmin')`
   - `--tag=xfadmin-config`：`config/xfadmin.php` → `config_path('xfadmin.php')`
3. 注册资源自托管路由（见 §4）；
4. 注册 Blade 指令（见 §3）。

## 3. Blade 指令

| 指令 | 展开 |
|---|---|
| `@xfHead` | `<?php echo \zxf\XfAdmin\XfAdmin::head(); ?>` |
| `@xfScripts` | `<?php echo \zxf\XfAdmin\XfAdmin::scripts(); ?>` |
| `@xf('card', [...])` | `<?php echo \zxf\XfAdmin\XfAdmin::component('card', [...]); ?>` |

```blade
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @xfHead
</head>
<body>
    @xf('card', ['title' => '统计', 'body' => $body])
    @xfScripts
</body>
</html>
```

## 4. Facade

```php
use XfAdmin;                        // 已注册的别名

XfAdmin::page([...]);
XfAdmin::card([...]);
XfAdmin::dataResponse($query, request()->all(), [...]);
```

实现：

```php
protected static function getFacadeAccessor() { return 'xfadmin'; }
// __callStatic 全部转发到 \zxf\XfAdmin\XfAdmin::$method(...)
```

⚠️ 转发**绕过容器解析**（组件为无状态静态工厂），
因此 `XfAdmin::page()` 与 `\zxf\XfAdmin\XfAdmin::page()` 完全等价。
推荐在控制器里直接 `use zxf\XfAdmin\XfAdmin;`。

## 5. 资源自托管路由

```php
Route::get('{prefix}/{path}', [AssetController::class, 'serve'])
    ->where('path', '(?i).*\.(css|js|mjs|map|json|svg|png|jpe?g|gif|ico|webp|avif|woff2?|ttf|eot|otf)$')
    ->name('xfadmin.assets');
```

| 项 | 值 |
|---|---|
| 注册条件 | `AssetController::prefix() !== ''` 且前缀**不是** `http(s)://`（CDN 不自托管） |
| 前缀 | `ltrim(config('xfadmin.assets_url', '/zxf/xfadmin'), '/')` |
| MIME 白名单 | 20 种（css/js/mjs/map/json/svg/png/jpg/jpeg/gif/ico/webp/avif/woff/woff2/ttf/eot/otf） |
| 安全 | 扩展名不在白名单 → 404；`realpath` 必须位于 `resources/assets` 内 + `is_file` + `is_readable` |
| 缓存 | `ETag = md5(path . ':' . filemtime)`；命中 → **304 空响应**；`Cache-Control: public, max-age=31536000`；`Expires` +1 年 |

⚠️ 用**控制器方法**而非闭包路由 —— 闭包会隐式绑定容器，导致 `route:cache` 序列化失败。
因此 `php artisan optimize` / `route:cache` 可安全使用。

## 6. 发布资源

```bash
php artisan vendor:publish --tag=xfadmin-assets    # public/zxf/xfadmin
php artisan vendor:publish --tag=xfadmin-config    # config/xfadmin.php
```

发布后建议配置 Nginx 直接服务静态文件（性能优于 PHP 托管）：

```nginx
location /zxf/xfadmin/ {
    alias /var/www/html/public/zxf/xfadmin/;
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 7. 控制器示例

### 7.1 整页

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use zxf\XfAdmin\XfAdmin;

class DashboardController extends Controller
{
    public function index()
    {
        return response(XfAdmin::page([
            'title'   => '仪表盘',
            'menu'    => config('admin.menu'),
            'content' => XfAdmin::card(['title' => '欢迎', 'body' => 'Hello']),
        ]));
    }
}
```

### 7.2 服务端表格接口

```php
public function users(Request $request)
{
    return response()->json(XfAdmin::dataResponse(
        User::query(),
        $request->all(),
        [
            'searchable' => ['name', 'email'],
            'filters'    => [
                'status'    => ['field' => 'status', 'op' => '='],
                'keyword'   => ['name', 'email'],
                'date_from' => ['field' => 'created_at', 'op' => 'date_from'],
                'date_to'   => ['field' => 'created_at', 'op' => 'date_to'],
            ],
        ]
    ));
}
```

### 7.3 AJAX 表单保存

```php
public function store(Request $request)
{
    $data = $request->validate([
        'name'  => 'required|string|max:50',
        'email' => 'required|email|unique:users',
    ]);

    User::create($data);

    return response()->json([
        'ok'      => true,                 // 顶层
        'message' => '保存成功',
        'url'     => route('admin.users.index'),
    ]);
}
```

校验失败时 Laravel 自动返回 422 + `{message, errors}`，前端自动回填字段错误。

## 8. CSRF

- 表单：`csrf_field()`（Laravel 中 `XfAdmin` 自动探测并使用）；
- AJAX：`XFAdmin.csrf()` 读 `<meta name="csrf-token">`，请求自动带 `X-CSRF-TOKEN` 头；
- 组件内的令牌来源：`XfAdmin::setCsrfResolver(fn () => csrf_token())`（服务提供者已自动注册）。

## 9. 中间件与路由

```php
// routes/web.php
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/api/users', [UserController::class, 'api']);   // DataTable 数据源
});
```

⚠️ DataTable 的 `ajax` 地址若需要会话/权限，必须放在 `web` 中间件组内；
否则未登录会 302 到登录页，前端表现为表格一直加载或报错。

## 10. 配置缓存注意事项

```bash
php artisan config:clear    # 修改 config/xfadmin.php 后
php artisan view:clear      # 修改 Blade 后
php artisan optimize        # 生产环境（含 route:cache，本包兼容）
```

## 11. 常见问题

| 现象 | 原因 | 处理 |
|---|---|---|
| `Class "XfAdmin" not found` | Facade 别名未注册 | `composer dump-autoload`，或直接用 `\zxf\XfAdmin\XfAdmin` |
| 资源 404 | 未发布且自托管路由未注册 | 执行 `vendor:publish --tag=xfadmin-assets`，或检查 `assets_url` |
| 改配置不生效 | 配置缓存 | `php artisan config:clear` |
| AJAX 返回登录页 HTML | 接口未走 `web` 中间件或 session 过期 | 检查路由中间件；前端 419 会提示「页面已过期」 |
| `route:cache` 失败 | （本包已用控制器方法，不应发生） | 检查是否自己注册了闭包路由 |
| 表格服务端 419 | CSRF | 确认 `<meta name="csrf-token">` 存在，或用 `method => 'POST'` |

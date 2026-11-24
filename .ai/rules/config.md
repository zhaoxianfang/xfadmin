# 配置与框架集成（config / Provider / Facade / 指令 / ThinkPHP）

---

## 1. 版本管理（重要）

| 位置 | 值 | 说明 |
| --- | --- | --- |
| `src/XfAdmin.php` | `public const VERSION = '1.0.3'` | 门面常量，运行时自检 |
| `config/xfadmin.php` | `'version' => '1.0.3'` | 资源 `?v=` 缓存版本号 |
| `composer.json` | **不写 version** | 本包约定：由上述两处管理，composer.json 不硬编码 |

> 改版本必须同步前两处；composer.json 不要加回 version 字段。
> `README.md`/`docs/06-appendix/06-changelog.md` 的版本示例与变更记录也要同步。

---

## 2. `config/xfadmin.php` 全部键（默认值）

| 键 | 默认值 | 说明 |
| --- | --- | --- |
| `assets_url` | `/zxf/xfadmin` | 资源基址；可改 CDN；影响 `XfAdmin::asset()` 前缀 |
| `version` | `1.0.3` | 缓存版本号（与 `XfAdmin::VERSION` 同步） |
| `theme` | `skin=modern` · `mode=light` · `layout=vertical` · `layout_position=fixed` · `layout_width=fluid` · `topbar_color=light` · `menu_color=gradient` · `sidenav_size=default` · `sidenav_user=true` | 渲染到 `<html data-*>`，运行时可被 config.js 持久化覆盖 |
| `brand` | `name=XfAdmin` · `logo`/`logo_dark`/`logo_sm`/`favicon=null` · `home_url=/` | 品牌信息 |
| `footer` | `text=null`(自动版权) · `right` · `icp` · `icp_url` · `police` · `police_url` · `police_icon` · `links[]` · `support` · `brand_url` · `groups[]` | 页脚 |

**读取/合并**：`XfAdmin::config($array)` 递归合并；`XfAdmin::setting('a.b',$default)` 点号读取。
**发布**：
- `--tag=xfadmin-assets` → `public/zxf/xfadmin`
- `--tag=xfadmin-config` → `config/xfadmin.php`
- 未发布时 `AssetController` 把 `/zxf/xfadmin/{path}` 映射包内 resources/assets 流式返回（扩展名白名单过滤）。

---

## 3. Laravel 集成（`src/Laravel/`）

### 3.1 自动注册（composer.json `extra.laravel`）
- Provider：`zxf\XfAdmin\Laravel\XfAdminServiceProvider`
- Facade 别名：`XfAdmin` → `zxf\XfAdmin\Laravel\Facades\XfAdmin`

### 3.2 ServiceProvider 生命周期
- `register()`：`mergeConfigFrom(config/xfadmin.php → 'xfadmin')`；注册单例 `'xfadmin'`
  （闭包内 `XfAdmin::config($app['config']->get('xfadmin'))`）。
- `boot()`：
  1. 再次 `XfAdmin::config(...)` 应用配置；
  2. `runningInConsole()` 注册两个 publish tag；
  3. 注册资源路由 `GET {prefix}/{path}` → `AssetController::serve`（仅本地前缀）；
  4. 注册 Blade 指令 `@xfHead` / `@xfScripts` / `@xf`；
  5. `app->terminating()` 中 `Assets::reset()`（兼容 Swoole/Octane 常驻内存）；
  6. `AboutCommand::add` 注入版本信息。

### 3.3 Blade 指令
- `@xfHead` → `XfAdmin::head()`（`<head>` 内 CSS/主题脚本）
- `@xfScripts` → `XfAdmin::scripts()`（`</body>` 前 JS）
- `@xf('card', ['title'=>'x'])` → `XfAdmin::component(...)`

### 3.4 CSRF 接线
业务项目须在 Provider 的 `boot()` 注册：
```php
XfAdmin::setCsrfResolver(fn (): string => (string) csrf_token());
```
否则 `form` 的 `_token` 为空 → AJAX 提交被 Laravel 判 419。（yoc 项目在 `AdminServiceProvider::boot` 已做。）

---

## 4. ThinkPHP 集成（`src/ThinkPHP/`）

- `extra.think.services` → `zxf\XfAdmin\ThinkPHP\Service`（配置 `xfadmin` → `config/xfadmin.php`）。
- `PublishCommand` 提供资源发布命令。
- 渲染核心不依赖框架，ThinkPHP 下直接用 `XfAdmin::component()` 或 `xf_admin()`。

---

## 5. composer.json 元数据（改 autoload/extra 时参考）

```json
"autoload": {
  "psr-4": { "zxf\\XfAdmin\\": "src/" },
  "files": ["src/helpers.php"]
},
"extra": {
  "laravel": {
    "providers": ["zxf\\XfAdmin\\Laravel\\XfAdminServiceProvider"],
    "aliases": { "XfAdmin": "zxf\\XfAdmin\\Laravel\\Facades\\XfAdmin" }
  },
  "think": {
    "services": ["zxf\\XfAdmin\\ThinkPHP\\Service"],
    "config": { "xfadmin": "config/xfadmin.php" }
  }
}
```

> 改 composer.json 后业务项目需 `composer dump-autoload`（若改了 PSR-4/files）。
> 本包作为 path/vcs 依赖时，业务项目 vendor 中的 composer.json 可能被 composer 注入 version——
> 那是 composer 行为，与本包源码无关；源码 composer.json 始终不写 version。

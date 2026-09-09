# 部署

## 1. 部署方式选择

| 方式 | 适用场景 | 性能 | 维护成本 |
|---|---|---|---|
| **发布到 public**（推荐生产） | 所有场景 | 最高（Web 服务器直出） | 升级后需重新发布 |
| **自托管路由**（仅 Laravel） | 开发 / 小流量 | 中（PHP 处理，但有 ETag + 强缓存） | 零维护 |
| **CDN** | 多机房 / 大流量 | 最高 | 需同步资源 |

## 2. Laravel 部署

### 2.1 发布资源

```bash
php artisan vendor:publish --tag=xfadmin-assets --force
php artisan vendor:publish --tag=xfadmin-config       # 首次
```

### 2.2 生产优化

```bash
php artisan config:cache
php artisan route:cache      # 本包资源路由用控制器方法，兼容
php artisan view:cache
php artisan optimize
```

⚠️ 修改 `config/xfadmin.php` 后必须 `php artisan config:clear` 再重新缓存。

### 2.3 Nginx 静态资源配置

```nginx
location ^~ /zxf/xfadmin/ {
    alias /var/www/html/public/zxf/xfadmin/;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;

    # 安全：只允许静态资源扩展名
    location ~* \.(php|env|log|sql)$ { deny all; }
}
```

## 3. ThinkPHP 部署

```bash
php think xfadmin:publish --force
php think clear
```

⚠️ ThinkPHP **没有**自托管路由 —— 未发布资源会全部 404，**必须发布**。

## 4. CDN 部署

```php
// config/xfadmin.php
'assets_url' => 'https://cdn.example.com/xfadmin',
```

- `assets_url` 含 `http(s)://` 时，Laravel **不再注册**自托管路由；
- 需自行把 `resources/assets/` 同步到 CDN 对应路径；
- 建议开启 CDN 的压缩与 HTTP/2。

## 5. 版本与缓存刷新

资源 URL 末尾追加 `?v={version}`：

```php
'version' => '2.1.0',
```

升级包版本后：

1. 改 `version`（或设为与包版本一致）；
2. 若用发布方式，重新 `vendor:publish --force`；
3. 若用 CDN，刷新 CDN 缓存。

> `Assets::url()` 对已含版本号的路径具备幂等性，二次 `asset()` 不会叠加 `?v=`。

## 6. 目录权限

```bash
# Laravel
chown -R www-data:www-data public/zxf/xfadmin
chmod -R 755 public/zxf/xfadmin
```

只读即可，无需写权限。

## 7. 环境变量建议

```env
# .env
XFADMIN_ASSETS_URL=/zxf/xfadmin
XFADMIN_VERSION=2.1.0
```

```php
// config/xfadmin.php
'assets_url' => env('XFADMIN_ASSETS_URL', '/zxf/xfadmin'),
'version'    => env('XFADMIN_VERSION', '2.1.0'),
```

## 8. 多环境配置

| 环境 | 建议 |
|---|---|
| 本地开发 | 不发布，用自托管路由（Laravel）或 `php -S` + `demo/router.php` |
| 测试/预发 | 发布资源，开启 `route:cache` |
| 生产 | 发布资源 + CDN + `optimize` |

## 9. 部署检查清单

- [ ] PHP ≥ 8.2
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] 资源已发布（或 CDN 已同步）且 `/zxf/xfadmin/css/app.min.css` 返回 200
- [ ] `assets_url` 与实际路径一致
- [ ] `version` 已更新（刷新浏览器缓存）
- [ ] 配置缓存已清理/重建
- [ ] 页面输出的 `<html>` 含 `data-skin` / `data-bs-theme` 等属性
- [ ] 无 JS 控制台错误（浏览器 F12 检查）
- [ ] 表格 AJAX 接口在已登录状态下返回 200（非 302 到登录页）

## 10. 回滚

1. 恢复上一版本的 `vendor/zxf/xfadmin`；
2. 重新发布资源（`--force`）；
3. `php artisan optimize:clear`；
4. 清 CDN 缓存。

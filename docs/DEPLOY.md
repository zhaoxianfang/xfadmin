# 部署与发布

> 文档导航：[README](../README.md) · [资源机制](assets.md) · [测试](TESTING.md) · [组件开发](DEVELOPMENT.md)

xfadmin 以 composer 包形式分发，资源**默认无需发布**（由 `AssetController` 从 vendor 内托管）。本文说明安装、资源发布、版本管理与本地演示同步。

---

## 一、Laravel 安装

```bash
composer require zxf/xfadmin
```

自动注册 `XfAdminServiceProvider` 与门面 `XfAdmin`。发布配置（可选）：

```bash
php artisan vendor:publish --provider="zxf\XfAdmin\Laravel\XfAdminServiceProvider" --tag=config
```

### 资源发布（可选）

默认 Laravel 通过 `AssetController` 直接从 vendor 包内 `resources/assets` 服务资源，**无需发布**到 `public/`。若需发布（如 CDN 化、合并到构建管线）：

```bash
php artisan vendor:publish --provider="zxf\XfAdmin\Laravel\XfAdminServiceProvider" --tag=assets
```

---

## 二、ThinkPHP 安装

```bash
composer require zxf/xfadmin
```

服务在 `extra.think.services` 自动注册。发布静态资源：

```bash
php think xfadmin:publish            # 首次发布
php think xfadmin:publish --force    # 覆盖已发布资源
```

ThinkPHP 的 `public/zxf/xfadmin` 需要被 Web 服务器可访问（不像 Laravel 有 AssetController 托管，TP 需物理发布）。

---

## 三、资源托管机制

| 框架 | 资源位置 | 是否需要发布 |
|------|----------|--------------|
| Laravel | vendor 内 `resources/assets`，由 `AssetController` 路由 `/zxf/xfadmin/...` 托管 | 否（默认） |
| ThinkPHP | `public/zxf/xfadmin`（物理目录） | 是（`php think xfadmin:publish`） |

`XfAdmin::asset('images/logo.svg')` 会返回正确的 URL 前缀（Laravel 走 `AssetController`，TP 走 `public/zxf/xfadmin`）。组件内图片统一用 `$this->img()` / `XfAdmin::img()`，二者自动路由。

---

## 四、版本管理

- 版本号在 `config/xfadmin.php` 的 `version` 与 `composer.json` 中维护，二者应一致。
- 资源 URL 自动带 `?v=<version>` 查询串防缓存（见 [资源机制](assets.md)）。
- 升级前查看 [CHANGELOG.md](CHANGELOG.md) 与 [UPGRADE.md](UPGRADE.md)。

---

## 五、本地演示同步（开发期）

源码改动在 **包内**（`src/` `resources/` `docs/` `config/`）完成后，wsf 演示通过 composer 包路径引用，需手动 rsync 同步：

```bash
rsync -a --delete src/ /Users/aha/www/wsf/vendor/zxf/xfadmin/src/
rsync -a --delete resources/ /Users/aha/www/wsf/vendor/zxf/xfadmin/resources/
```

> ⚠️ 这是演示开发期约定。生产环境通过 `composer update zxf/xfadmin` 拉取正式发布的版本，不走 rsync。

---

## 六、CI 检查清单

发布前确保四道护栏全绿：

```bash
php -l src/XfAdmin.php                              # 语法
```

---

## 七、发布到 Packagist

1. 在 GitHub 打 tag（与 `composer.json` 版本一致）
2. Packagist 自动拉取；用户 `composer update` 即可
3. 重大变更同步更新 `CHANGELOG.md` 与 `UPGRADE.md`

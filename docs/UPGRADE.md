# 版本升级指南

> 文档导航：[README](../README.md) · [更新日志](CHANGELOG.md) · [部署发布](DEPLOY.md)

本文说明从旧版本升级到 xfadmin 时的注意点与迁移路径。

---

## 升级流程（通用）

```bash
composer update zxf/xfadmin
php artisan view:clear        # Laravel：清视图缓存
php artisan config:clear       # Laravel：如涉及配置
php think xfadmin:publish --force   # ThinkPHP：重新发布资源
```

升级前请先阅读目标版本的 [CHANGELOG.md](CHANGELOG.md) 与本文件对应小节。

---

## 升级到 v2.0.0

### 变更摘要
- 组件数增长至 200；新增组件无需额外配置，开箱即用。
- **ThinkPHP 用户**：新增全局助手 `xfadmin()`。若你的项目已自行定义了同名函数，请改名以避免冲突（composer 自动加载时 `function_exists` 守卫会跳过包内定义，但可能产生重复定义 fatal）。
- **ThinkPHP 资源发布**：`php think xfadmin:publish` 现在默认**不覆盖**已有资源（更安全）；需要强制覆盖加 `--force`。
- `config/xfadmin.php` 版本号升至 `2.0.0`，资源 URL 自动带 `?v=2.0.0` 防缓存。

### 需要检查的点
- 若你曾直接修改 `vendor/zxf/xfadmin/resources/assets/css/xfadmin.css`：升级会被覆盖，请把改动迁移到项目自有样式表或提 PR 回包内。
- 若你依赖 `data-reload` 的 `"0"` 判定：确认前端仍用 `attr()` 读取（v2 起已固化，无需改动）。

### 无需改动的点
- 所有 `XfAdmin::<alias>()` 调用签名保持不变。
- DataTables 服务端协议（DataSet / 紧凑协议 / cellRenderers）保持兼容。
- 安全转义规范（所有组件文本/枚举字段 `$this->e()`）为内部强化，对外 API 不变。

---

## 历史升级注意事项

### v1.x → 当前
- `topbar` 图标已从 `data-lucide` 切换为 Tabler `ti ti-*`；若你手写 topbar 扩展并依赖 lucide，请改为 `ti`。
- 侧边栏 `sidenav` 与顶部导航 `topNav` 已并存；`theme.layout` 控制默认布局（`vertical`/`horizontal`）。

---

## 回滚

如升级后出现不兼容：

```bash
composer require zxf/xfadmin:<旧版本号>
php think xfadmin:publish --force   # TP
```

---

## 破坏性变更声明

xfadmin 在 **MAJOR** 版本才会引入破坏性变更；**MINOR/PATCH** 保持向后兼容。升级前关注 CHANGELOG 中标记 **[BREAKING]** 的条目。

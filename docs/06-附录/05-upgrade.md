# 升级指南

## 1. 通用升级步骤

```bash
composer update zxf/xfadmin

# 1) 更新静态资源
php artisan vendor:publish --tag=xfadmin-assets --force    # Laravel
php think xfadmin:publish --force                          # ThinkPHP

# 2) 更新版本号（刷新浏览器缓存）
#    config/xfadmin.php: 'version' => '<新版本>'

# 3) 清缓存
php artisan optimize:clear        # Laravel
php think clear                   # ThinkPHP

# 4) 回归验证
bash tests/run-all.sh
```

## 2. 版本兼容性

| 版本 | PHP | Laravel | ThinkPHP | 说明 |
|---|---|---|---|---|
| 2.x | ≥ 8.2 | 11 / 12 | 8+ | 当前版本 |
| 1.x | ≥ 8.1 | 9 / 10 | 6 / 8 | 已停止维护 |

## 3. 破坏性变更清单

升级前请逐项确认：

### 3.1 组件行为变更

| 变更 | 影响 | 适配 |
|---|---|---|
| `Card` 工具按钮改用 `data-action="card-toggle/card-close/card-refresh"` | 旧的 `data-toggle="collapse/remove/reload"` 失效 | 已自动输出新属性，无需改动（除非自定义 CSS 依赖旧属性） |
| `DataTable` 使用 DataTables 2 的 `layout` 取代 `dom` | 自定义 `options.dom` 无效 | 改用 `options.layout` |
| DataTable 默认 `responsive` 改为 `false`（模板对齐） | 表格不再自动响应式 | 需要时显式 `'responsive' => true` |
| DataTable `tableClass()` 追加 `mb-0` | 表格下边距变化 | 一般无需处理 |
| `lockScreen` 别名指向独立 `LockScreen` 组件 | 原 AuthPage 锁屏参数失效 | 用 `authPage(['type' => 'lock-screen'])` |
| `Icon` 组件默认使用 Tabler | `data-lucide` 图标不显示 | 统一改用 `ti ti-*` |
| 图片组件默认 `object-fit: cover` + 固定高度 | 竖图不再被无限拉高 | 需要原图比例时自加 CSS |
| Leaflet 初始化增加 `invalidateSize` + `_resetView` | 瓦片不再错位 | 无需改动 |

### 3.2 安全加固（可能影响显示）

以下字段由「原样输出」改为「转义输出」，若你原本传入 HTML 需改为组件：

| 组件 | 字段 |
|---|---|
| `Alert` | `text` |
| `Ribbon` | `text` |
| `ComingSoon` / `Maintenance` | `message` |
| `Widget` | `value` |
| `PricingCard` | `price` |

以下**结构性**字段现在走白名单/转义，非法值会回退默认：

`Animate.tag`、`Tabs.style`、`Progress.variant`、`Avatar.variant/size/rounded`、
`Popover.content/title/placement/trigger`、`Tooltip.*`、`Modal.size`、
`Offcanvas.placement`、`Toast.variant`、`Dropdown.variant/size`。

⚠️ 不要为了"恢复显示"而回退这些加固 —— 它们是真实 XSS 修复。
若需要富内容，请使用对应的内容槽位（`body`/`content`）。

### 3.3 参数默认值变化

| 组件/选项 | 旧 | 新 | 适配 |
|---|---|---|---|
| `Form.csrf` | （文档写 true） | `[]`（不注入） | 需要令牌时显式 `'csrf' => true` |
| `ImportExport.formats` | `['csv','xlsx','json']` | `[]` | 使用 `export_url` 简写时必须显式传 `formats` |
| DataTable `responsive` | `true` | `false` | 需要时显式开启 |

## 4. 升级后自检

```bash
# 1) 包内回归
bash tests/run-all.sh

# 2) 资源完整性 + XSS 审计
php tools/selftest/asset_check.php
php tools/selftest/xss_audit.php

# 3) 浏览器级自测（可选，需 Playwright）
bash tools/selftest/run.sh

# 4) 重新生成组件文档
php tools/gen_component_docs.php
```

页面级检查：

- [ ] 页面样式正常（无 404 资源）
- [ ] `<html>` 含 `data-skin` / `data-bs-theme` 等属性
- [ ] 表格排序/搜索/分页正常
- [ ] 表单 AJAX 提交与错误回填正常
- [ ] 图表正常渲染且随主题切换
- [ ] 浏览器控制台无错误

## 5. 回滚

```bash
composer require zxf/xfadmin:<旧版本>
php artisan vendor:publish --tag=xfadmin-assets --force
php artisan optimize:clear
```

同时回滚 `config/xfadmin.php` 的 `version` 与自定义修改。

## 6. 从 1.x 升级到 2.x

主要差异：

| 项 | 1.x | 2.x |
|---|---|---|
| PHP | ≥ 8.1 | ≥ 8.2 |
| 组件数 | ~142 | 226 个别名 / 215 类 |
| 图标 | lucide | Tabler（`ti ti-*`） |
| 表格 | DataTables 1.x | DataTables 2.x（`layout` 取代 `dom`） |
| 资源 | 部分外链 | 全内置、离线可用 |
| 安全 | 部分转义 | 全量审计 + 白名单 |

升级步骤：

1. 升级 PHP 到 8.2+；
2. `composer require zxf/xfadmin:^2.0`；
3. 全局替换 `data-lucide` → `ti ti-*` 图标；
4. 自定义 `options.dom` 改为 `options.layout`；
5. 检查上文的破坏性变更清单；
6. 按 §4 自检。

## 7. 获取帮助

- 查阅 [文档中心](../README.md)；
- 检索 [组件索引](01-component-index.md) 与 [问题排查](../05-运维/04-troubleshooting.md)；
- 提交 issue 时附：版本、PHP/框架版本、组件别名、配置数组、输出片段、控制台错误。

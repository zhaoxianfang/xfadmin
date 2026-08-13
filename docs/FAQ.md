# 常见问题与故障排查

> 文档导航：[README](../README.md) · [组件开发](DEVELOPMENT.md) · [安全规范](security.md) · [风格对齐](STYLE_ALIGNMENT.md)

本文汇总 xfadmin 使用与开发中的高频问题与排错路径。

---

## 一、页面/组件 JS 报错

**症状**：组件渲染出来了，但交互失效、控制台报 `xxx is not defined` / `Unexpected token`。

- 确认组件声明的 `assets()` 资源 key 已注册在 `src/Assets/Assets.php` 的 `PLUGINS`，且文件真实存在（跑 `php tools/selftest/asset_check.php`）。
- 检查内联脚本括号是否平衡（曾出现 CommentThread 因 `}` 不匹配致命，已重写为 `XFAdmin.register(...)`）。
- 离线环境（无外网）须拦截外部请求，否则 OSM 瓦片 / Google 字体导致 `load` 事件永不触发——Playwright 中用 `route.abort()` 非本地请求。

---

## 二、图片破图（naturalWidth = 0）

**症状**：图片位置空白、控制台无报错。

- 组件内图片应走 `$this->img($path)`（外链/data URI 原样返回，其余按 `images/` 解析）。
- 禁止直接拼 `XfAdmin::asset('images/'.ltrim($path))`——会丢失外链支持且命名空间解析不一致。
- 轮播/商品图需固定高度 + `object-fit:cover`，否则竖图被无限拉高（见记忆 46089229）。

---

## 三、DataTable「更多」下拉不弹出

**症状**：操作列「更多」按钮点击无反应 / 菜单飞出视口。

- 仅在 `scroll_x` + `fixed_columns` 场景出现。
- 修复逻辑在 `initDtDropdownFix`：必须在 `shown.bs.dropdown` 的 `requestAnimationFrame` 内改 `position:fixed` + 基于 `getBoundingClientRect()` 重定位。同步写入会被 Bootstrap5 的 Popper 收尾覆盖。

---

## 四、DataTable 行明细不显示 / 与 responsive 冲突

- `row_detail` 启用时**必须关闭** `responsive`（二者互斥）。`DataTable.php` 的 `html()` 会 `set('responsive', false)`，且 `tableClass()` 不再输出 `dt-responsive`。

---

## 五、state_save 不生效

- **必须在 `$config` 拷入 `$xfConfig['dt']` 之前**赋值；拷贝后再改 `$config` 会丢失。

---

## 六、批量操作无效 / data-reload 失效

- 批量按钮在表格外，事件回调里用 `jQuery(this)` 而非 `$el.find(this)`（后者为空集）。
- `data-reload` 判定用 `attr()` 读取（`.data()` 会把 `"0"` 转数字 → 误判为 true）。

---

## 七、XSS 审计报「未转义」

- `xss_audit.php` 注入 `<xfxss-payload>` 到文本字段，若输出 HTML 含字面未转义子串即告警。
- 检查：`title`/`text` 等文本字段是否 `$this->e()`；`variant`/`type`/`size` 等枚举拼 class 是否 `$this->e()`；内联 JSON 是否走 `Html::scriptJson()`。
- 内容容器（`body`/`content` 等）可 raw，但调用方须保证可信。

---

## 八、框架样式冲突 / 新组件无样式

- 新样式写在 `xfadmin.css`（最后加载，可覆盖 INSPINIA 框架 `app.min.css`）。
- **新增类前先确认 `app.min.css` 是否已定义同名类**——已定义的一律不要重写（尤其 position/width/margin/z-index/background/padding/top/right），只补框架未定义属性（见 [风格对齐](STYLE_ALIGNMENT.md)）。
- 图标统一用 Tabler `ti ti-*`；不要用 `data-lucide`（未注册插件→不可见）。

---

## 九、wsf 演示 403 / 302

- `/admin/api/data/{dataset}` 受 admin 中间件保护，未登录会被 302 到 `/admin/login`（**非代码 bug**）。
- 本地 `php artisan serve` 测页面可能一直 403——安全中间件的 SSRF 拦截把 127.0.0.1 回环 Host 判为拦截。真实域名访问不受影响；绕过 HTTP 验证可用 Console Kernel 引导后直接 `view()->render()`。

---

## 十、ThinkPHP 资源 404

- TP 需物理发布资源：`php think xfadmin:publish`（首次）或 `--force`（覆盖）。
- 确认 `public/zxf/xfadmin` 可被 Web 服务器访问。
- `xfadmin()` 助手函数在 `Service::register()` 内注册；若未生效，确认 `extra.think.services` 已配置且 `composer dump-autoload`。

---

## 十一、组件数/文档计数不一致

- 组件注册表在 `src/XfAdmin.php`，文档由 `tools/gen_*.php` 扫描生成，README/composer 计数需手动同步。
- 新增组件后运行：
  ```bash
  php tools/gen_category_docs.php && php tools/gen_docs.php
  ```
- 并刷新 `README.md` / `composer.json` / `config/xfadmin.php` 的计数与版本。

---

## 十二、同步 wsf 后无变化

- 确认 rsync 路径正确：`src/` → `wsf/vendor/zxf/xfadmin/src/`，`resources/` → `wsf/vendor/zxf/xfadmin/resources/`。
- 资源经 `AssetController` 托管，无需发布到 `public/`。
- 改完清 Laravel 缓存：`php artisan view:clear && php artisan config:clear`（如涉及配置）。

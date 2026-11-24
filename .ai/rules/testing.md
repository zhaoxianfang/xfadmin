# 测试与验证（包内三套测试 / 工具 / selftest）

> 全部零外部依赖、离线可跑。统一入口 `bash tests/run-all.sh`（退出码 0=全绿）。

---

## 1. 统一入口 `tests/run-all.sh`

按顺序执行并汇总（任一失败退出 1）：

1. **PHP 语法检查**：`find src -name '*.php' | xargs -n1 -P8 php -l`（过滤 "No syntax errors"）
2. **JS 语法检查**：有 node 时对 `resources/assets/js/*.js` 逐个 `node --check`
3. **静态审计**：`php -d error_reporting=... tests/audit.php`
4. **冒烟**：`php tests/smoke.php`
5. **回归**：`php tests/regression.php`

成功输出 `ALL GREEN`。

---

## 2. 三套测试职责

| 文件 | 职责 | 覆盖重点 |
| --- | --- | --- |
| `tests/smoke.php` | 冒烟：组件能渲染、基础断言 | filter_bar 控件、method=POST→ajax、行编辑配置、JS 契约（confirm 单定义、serverSide 压缩、弹窗+行编辑 API）、app.js i18n/语言持久化、StatCard 响应式列宽 |
| `tests/regression.php` | 回归：具体 bug 修复点 | importExport 导出/导入/CSRF、DataTable fixed_columns 精确索引/中间列/字段名、自定义搜索面板、`avatarStack`/`money`/`select` 渲染器白名单、三个声明式组件（rowActions/adminBehaviors/adminThemeHead）声明节点与键名净化 |
| `tests/audit.php` | 静态审计：安全/健壮性/前端契约 | 51 项（见 [security.md](security.md) §5），含 HTML 标签配平、img alt、XSS payload、destroy 钩子、层级 z-index、声明式组件 HEX 转义 |

---

## 3. 测试纪律

- **不得为让测试通过而改写既有断言**；测试失败先修源码。
- 新增组件/widget/参数 → 在 `regression.php` 加断言，在 `audit.php` 加契约检查。
- 测试用纯 PHP（无 PHPUnit），断言为包内简单 helper；沿用现有风格，不引入新框架。

---

## 4. 工具脚本（`tools/`）

| 脚本 | 用途 |
| --- | --- |
| `gen_component_docs.php` | 生成组件文档 |
| `gen_component_matrix.php` | 生成组件矩阵 |
| `check_docs_links.php` | 检查文档链接完整性 |
| `audit_docs_coverage.php` | 检查文档覆盖率 |
| `selftest/build.php` | 生成 selftest 页面（→ `selftest/.build/`，生成物不改） |
| `selftest/router.php` | selftest 本地路由 |
| `selftest/run.sh` | 运行 selftest |
| `selftest/selftest.mjs` | 浏览器端自测脚本 |
| `selftest/asset_check.php` | 资源检查 |
| `selftest/xss_audit.php` | XSS 审计 |

> `tests/demo_smoke.sh`：demo 站点冒烟。

---

## 5. 验证命令速查

```bash
# 包根：全量
bash tests/run-all.sh

# 单项
php tests/audit.php && php tests/smoke.php && php tests/regression.php

# 语法
find src -name '*.php' -print0 | xargs -0 -n1 php -l
node --check resources/assets/js/xfadmin.js

# 文档
php tools/check_docs_links.php
php tools/audit_docs_coverage.php

# selftest（需要时）
bash tools/selftest/run.sh

# 业务项目侧集成验证（yoc 项目根）：
#   vendor/bin/pint --dirty --format agent
#   php artisan route:list
#   php artisan test
```

---

## 6. 与业务项目（如 yoc）的关系

- 本包源码改动后须同步到业务项目 vendor 并保持字节一致（`src/`、`resources/assets/js/`、`composer.json`、
  `config/xfadmin.php` 等；`.DS_Store` 元数据除外）。
- 项目侧有 `Modules/Admin` 继承基座与更多 feature 测试，本包改动后应在项目侧再跑一遍集成测试。
- 版本号两处同步（见 [config.md](config.md)），同步后业务项目缓存 `?v=` 自动刷新。

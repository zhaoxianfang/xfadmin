# 编码与交付规范（注释 / 命名 / PHP 基线 / 格式化 / 版本 / 文档）

---

## 1. 编码规范

1. **注释中文**：所有 PHP 注释/文档块统一简体中文（行内、方法、块、类、文件头）；
   PHPDoc 优先于行内注释；`@param`/`@return`/`@example`/`@scenario` 按需补全。
2. **命名语义化**：禁止单字母变量/方法/参数/循环变量（`$i`/`$e`/`$d` 不合格），必须语义化。
3. **严格类型**：`declare(strict_types=1);`；方法参数/返回类型显式声明；数组形状用 PHPDoc array shape。
4. **PHP 8.2 基线**：代码须兼容 8.2/8.3/8.4/8.5，不得用 8.3+ 独占语法：
   - typed class constants ❌；
   - `json_validate()` 需回退 `json_last_error()`；
   - 其它 8.3+ 特性按需回退。
5. **控制结构**：始终用花括号（即使单行体）。
6. **构造器提升**：PHP 8 构造器属性提升（非必要不写空 `__construct()`）。
7. **组件写法**：继承 `Component`，实现 `defaults()`（返回默认配置数组）+ `html()`（返回 HTML）；
   依赖插件在 `assets()` 返回键名。

---

## 2. 格式化

- 本包无独立 pint 配置；在业务项目（如 yoc）根运行 `vendor/bin/pint --dirty --format agent` 格式化改动的 PHP。
- 提交前确认无格式问题（pint 输出 `passed`）。

---

## 3. 版本管理

| 位置 | 值 | 是否写 |
| --- | --- | --- |
| `src/XfAdmin.php` | `public const VERSION` | ✅ 必写 |
| `config/xfadmin.php` | `'version'` | ✅ 必写 |
| `composer.json` | version | ❌ **不写** |
| `README.md` / `docs/06-appendix/06-changelog.md` | 版本示例/变更记录 | ✅ 同步 |

> 三处口径：本包只维护两处常量；composer.json 明确不写 version（业务 vendor 中可能被 composer 注入，与本包无关）。
> 组件/别名数量变化也要同步 `index.md` 统计、`README.md`、`composer.json` 描述、`docs/`。

---

## 4. 文档规范

- 面向使用者的文档在 `docs/`（6 大类，见 [architecture.md](architecture.md) §8），面向 AI 维护的在 `.ai/rules/`。
- 组件文档可由 `tools/gen_component_docs.php` 生成；改组件参数后同步对应 `docs/03-component-reference/` 与
  `docs/04-advanced-guide/` 条目。
- 文档链接用 `tools/check_docs_links.php` 校验；覆盖率用 `audit_docs_coverage.php`。
- 新增组件/别名/widget 时：注册表 + `@method` 注解 + `docs/` + `.ai/rules/index.md` 统计 + 变更日志，五处齐动。

---

## 5. 资源交付

- 新增/改动 `.js`/`.css` 必须同时产出 `.min.js`/`.min.css`（压缩版本号与源文件一致）。
- 组件内联 CSS/JS 通过 `Assets` 收集输出；不在 PHP 组件里输出大段内联脚本（交互走 widget + 声明节点）。
- 第三方插件离线内置于 `resources/assets/plugins/`，不引外网 CDN。

---

## 6. 提交前检查清单

- [ ] 中文注释、语义化命名、PHP 8.2 基线
- [ ] 组件改动：`defaults()`/`html()`/`assets()` + 注册表 + `@method`
- [ ] 前端改动：widget 有 destroy 钩子；`data-xf-config` 用 HEX 转义；白名单与前端渲染器对齐
- [ ] 安全：raw 槽位无用户数据；URL 白名单+转义；内联 JSON 用 scriptJson
- [ ] 版本两处同步、composer.json 无 version
- [ ] 文档与统计数字同步（README/docs/.ai index）
- [ ] `bash tests/run-all.sh` 全绿
- [ ] 项目侧 pint + route:list + artisan test（如涉及集成）
- [ ] vendor 与源码字节一致（src/resources-js/composer/config；`.DS_Store` 除外）

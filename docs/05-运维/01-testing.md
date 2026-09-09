# 测试体系

XfAdmin 提供**四层测试**：语法检查、静态审计、PHP 冒烟/回归、浏览器级自测。全部可离线运行。

## 1. 一键回归

```bash
bash tests/run-all.sh
# 退出码：0 = 全绿，1 = 有失败
```

执行内容：

| 阶段 | 命令 | 说明 |
|---|---|---|
| PHP 语法检查 | `php -l` 遍历 `src/**/*.php` | 并行 8 路 |
| JS 语法检查 | `node --check resources/assets/js/*.js` | 无 node 时跳过 |
| 静态审计 | `php -d error_reporting="E_ALL & ~E_DEPRECATED" tests/audit.php` | 四道防线 |
| 冒烟测试 | `php tests/smoke.php` | 无框架渲染全部组件 |
| 回归测试 | `php tests/regression.php` | 资源去重 / 多实例 / 多页面隔离 |

## 2. 静态审计（`tests/audit.php`）

四道防线：

| # | 防线 | 内容 |
|---|---|---|
| 1 | **资源完整性** | 组件 `assets()` 声明的插件必须已注册在 `Assets::PLUGINS`；`PLUGINS` 声明的文件（含 deps 递归）必须真实存在 |
| 2 | **注入模糊审计** | 对每个组件的「文本展示字段」注入 payload，检测输出是否未转义（详见 [04-安全与转义](../02-核心架构/04-security.md#6-审计工具)） |
| 3 | **健壮性审计** | 极端/非法输入不得触发 PHP 致命错误 |
| 4 | **前端契约审计** | 校验 `xfadmin.js` / `xfadmin.css` 中已修复的关键点仍然存在（防回退） |

```bash
php tests/audit.php; echo "exit=$?"
```

## 3. 冒烟测试（`tests/smoke.php`）

无框架环境下**渲染全部已注册组件**，确保不抛异常且输出非空：

```bash
php tests/smoke.php
```

覆盖：

- 全部 226 个别名的默认渲染；
- 常见参数组合（variant/size/type 等枚举）；
- 边界输入（`null`、空数组、超长字符串）。

## 4. 回归测试（`tests/regression.php`）

关键行为回归：

| 用例 | 断言 |
|---|---|
| 资源去重 | 同一插件被多组件依赖 → 引用只出现一次 |
| 内联 JS 去重 | 同一实例渲染两次 → 初始化代码不重复 |
| 多页面隔离 | 渲染两个 `Page` 后资源状态重置（不互相污染） |
| 组件嵌套 | 深层嵌套组件渲染正确 |
| 配置合并 | 点式 `set/get`、递归合并行为 |
| 数据协议 | `DataSet` 的过滤/排序/分页结果 |
| 转义 | 文本字段转义、内容槽位原样 |

```bash
php tests/regression.php
```

## 5. 演示站冒烟（`tests/demo_smoke.sh`）

```bash
bash tests/demo_smoke.sh
```

启动 `php -S` + `demo/router.php`，逐个请求演示页并断言 HTTP 200 + 关键 DOM 存在。

## 6. 浏览器级自测（`tools/selftest/`）

Playwright 驱动的真实浏览器回归，五件套：

| 文件 | 作用 |
|---|---|
| `build.php` | 用包内 autoloader 渲染 `XfAdmin::componentList()` 全部组件到 `.build/`，每组件生成 `doc_<alias>.html` + `all.html` 总览 + `doc_index.json` |
| `router.php` | `/zxf/xfadmin/*` 服务 `resources/assets`；`/doc/<alias>` 服务单组件页 |
| `xss_audit.php` | XSS 模糊审计（见上） |
| `asset_check.php` | 资源完整性校验 |
| `selftest.mjs` | Playwright 加载总览页 + 每个组件独立页，检查 pageerror / console error / 本地 4xx / 破图 / 横向溢出 |
| `run.sh` | 一键：`build → xss_audit → asset_check → 起服务 → Playwright` |

```bash
bash tools/selftest/run.sh
# 默认端口 8919，可用 PORT=xxxx 覆盖
```

检查项：

- `pageErrors` —— 页面 JS 异常；
- `consoleErrors` —— 控制台错误；
- `failedLocal` —— 本地资源 4xx（外网请求被 `route.abort()` 拦截，离线可跑）；
- `brokenImgs` —— `naturalWidth === 0` 的破图；
- `overflow` —— 横向溢出；
- `docErrors` —— 逐组件错误明细。

⚠️ 环境要求：

- Playwright 需从本机已有安装引入（脚本内使用绝对路径，如
  `/Users/aha/www/xfeditor/node_modules/playwright`）；
- Chrome 可执行路径：`/Applications/Google Chrome.app/Contents/MacOS/Google Chrome`；
- 若无外网，`picsum.photos` 等外链图会破图 —— 需在脚本中过滤或使用本地图。

## 7. 文档生成工具

| 脚本 | 作用 |
|---|---|
| `tools/gen_component_docs.php` | 生成 `docs/03-组件参考/` 全部组件文档（参数表 + 示例 + 依赖） |
| `tools/gen_component_matrix.php` | 导出组件矩阵 JSON（参数统计） |
| `tools/gen_docs.php` | 生成 `docs/components-reference.md`（单文件全组件参考） |
| `tools/gen_category_docs.php` | 生成 `docs/categories/*.md` |

改动组件源码（`defaults()`、类注释、`@method 说明`）后请重新生成文档：

```bash
php tools/gen_component_docs.php
```

## 8. 编写自己的测试

推荐模式（与包内一致，零依赖）：

```php
<?php
declare(strict_types=1);

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
        $f = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen('zxf\\XfAdmin\\'))) . '.php';
        if (is_file($f)) require $f;
    }
});

use zxf\XfAdmin\XfAdmin;

$fail = 0;
function check(string $name, bool $ok): void
{
    global $fail;
    echo ($ok ? "  ✔ " : "  ✘ ") . $name . PHP_EOL;
    if (! $ok) $fail++;
}

$html = (string) XfAdmin::card(['title' => '测试', 'body' => '内容']);
check('card 输出包含 card 类', str_contains($html, 'class="card'));
check('card 输出包含标题', str_contains($html, '测试'));

exit($fail === 0 ? 0 : 1);
```

## 9. CI 集成建议

```yaml
# GitHub Actions 示例
- name: PHP 语法 + 静态审计 + 冒烟 + 回归
  run: bash tests/run-all.sh

- name: 组件资源自检
  run: php tools/selftest/asset_check.php

- name: XSS 审计
  run: php tools/selftest/xss_audit.php

# 可选（需 Playwright + Chrome）
- name: 浏览器级自测
  run: bash tools/selftest/run.sh
```

## 10. 测试结果判读

| 输出 | 含义 |
|---|---|
| `ALL GREEN` | 全部通过 |
| `--- XXX: FAIL` | 该环节失败，向上滚动查看具体 `✘` 项 |
| `✘ 组件 xxx: 未转义` | XSS 审计发现隐患，检查该组件的文本字段是否漏了 `e()`/`text()` |
| `✘ 插件 xxx 文件缺失` | `PLUGINS` 声明了不存在的文件，补齐或修正路径 |
| `pageErrors > 0` | 前端 JS 异常，检查组件输出的内联脚本与 `data-xf` 契约 |

# 运行时自测与质量保障

本包提供两套可重复运行的回归脚本，用于「保证全部组件 / 模板的样式、布局、事件、交互、功能全部正常」。

> 路径约定：以下命令均从仓库根目录执行。

## 1. 运行时自测（Playwright 无头浏览器）

`tools/selftest/` 目录下：

| 文件 | 作用 |
| --- | --- |
| `build.php` | 用包内 autoloader 渲染**全部已注册组件**到 `tools/selftest/.build/`，每个组件生成独立页 `doc_<alias>.html`，并生成总览页 `all.html` 与索引 `doc_index.json` |
| `router.php` | 自测路由：`/zxf/xfadmin/*` 直接服务 `resources/assets`（无需发布资源），`/doc/<alias>` 服务单组件页 |
| `selftest.mjs` | Playwright 脚本：加载总览页与**每个组件独立页**，检查 JS 致命错误、控制台错误、本地 404、破图（naturalWidth=0）、横向溢出 |
| `run.sh` | 一键执行：`build.php` → `php -S` 起服务 → `node selftest.mjs` |

### 运行

```bash
bash tools/selftest/run.sh
```

前置依赖：

- `php`（CLI）
- `node` + 本机 Playwright（`/Users/aha/www/xfeditor/node_modules/playwright`，脚本内为绝对 import 路径；若换机器请改为本地 `playwright` 依赖）
- 外网请求会被 `selftest.mjs` 拦截（`route.abort`），因此离线也能跑，仅检测本地资源

### 判定标准

脚本输出 JSON 报告，并以退出码 0/1 表示 PASS/FAIL：

- `pageErrors === 0`（无未捕获 JS 异常）
- `consoleErrors === 0`（无控制台 error）
- `failedLocal === 0`（本地资源无 4xx/5xx）
- `brokenImgs === 0`（无破图）
- `overflow.scrollW <= clientW`（无横向溢出）
- `docErrors === {}`（每个组件独立页均无异常）

新增 / 修改组件后，请运行本脚本确认仍为 PASS。

## 2. 资源依赖完整性校验

`tools/selftest/asset_check.php`：

- 校验**每个组件** `assets()` 返回的依赖包名，是否都已注册在 `src/Assets/Assets.php` 的 `PLUGINS`
- 校验 `PLUGINS` 中声明的每个 js/css 文件，是否真实存在于 `resources/assets/`（含 `deps` 递归展开）

```bash
php tools/selftest/asset_check.php
```

该脚本能提前发现「组件引用了未注册的资产 key」或「资源包声明了磁盘上缺失的文件」这类会导致交互静默失效的问题。

## 3. 文档生成

组件文档由注册表 + 类 docblock 自动生成，修改组件后请重跑：

```bash
php tools/gen_category_docs.php   # 刷新 docs/categories/*.md
php tools/gen_docs.php            # 刷新 docs/components-reference.md
```

## 4. 已知约束

- `build.php` / `asset_check.php` 使用包内 autoloader（未发布到 vendor 时也能运行）。
- 布局级组件（page/sidenav/topbar/.../emptyState 等）本身返回完整页面，单独成页，不并入总览页 `all.html`。
- 自测基于「空配置」渲染所有组件；若某组件需要真实数据才能体现交互，请在其组件说明中补充手工验证步骤。

## 5. CSS 与模板一致性静态审计

修改 `resources/assets/css/xfadmin.css` 后，可用以下一次性脚本（无需启动服务）比对 xfadmin 与
模板 `app.min.css` 的**同名选择器 + 高危属性**，自动列出「覆盖模板已定义类且取值不同」的冲突点，
用于防止误覆盖模板由 CSS 变量驱动的主题样式：

```bash
node /tmp/css_conflict_audit.js
# 脚本读取 xfadmin.css 与 app.min.css，输出 CONFLICTS 清单（sel|prop|xf值|tpl值）
```

规则（详见 `docs/STYLE_ALIGNMENT.md` 第 3/4.3 节）：

- **禁止**用固定值覆盖模板由 `--ins-*` 变量驱动的 `box-shadow` / `border` / `background` / `margin` 等，
  应交还模板变量（或改用 Bootstrap 语义变量 `--bs-*`，暗色自动适配）。
- 包内自定义类（`xf-*`）模板未定义，可自包含，不受此限。
- 审计结果中**数值等价**（如 `.9375rem` = `--ins-font-size-md`）或**同源变量**（`--bs-primary` = `--ins-primary`）的冲突属无害，可忽略。
- 删除 `/tmp/css_conflict_audit.js` 不会破坏仓库（脚本为临时诊断工具，未纳入版本管理）。

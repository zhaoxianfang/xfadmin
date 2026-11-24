# 任务执行协议（每个任务都按这个流程走）

目标：**用最少的检索拿到足够上下文**，按场景精准读取 `.ai/rules/`；改完必验证，形成闭环。

---

## 0. 开工顺序（固定四步，约 3–6 次工具调用）

```
① 读 .ai/rules/index.md        —— 真实统计、分类地图、硬性禁令、路由表
   ↓
② 按「场景表」加载匹配规则      —— 只加载与本次改动相关的 1–2 个规则文件
   ↓
③ 定点定位代码（检索阶梯）       —— 注册表→类名→方法，命中即停
   ↓
④ 改动 + 验证闭环               —— 中文注释 → 格式化 → 测试
```

> 不确定读哪个规则文件，看 `ai.md` 第 4 节场景速查。

---

## 1. 按场景精准加载（命中即停，不通读全部）

| 任务场景 | 必读（按顺序） | 跳过 |
| --- | --- | --- |
| 首次接手 / 不确定 | `index.md` → `workflow.md`（本文件） → `ai.md` | 专项规则 |
| 新增/改组件类 | `components.md` → `architecture.md` → `conventions.md` | 前端/测试细节 |
| 改 DataTable / 渲染器 | `datatable.md` + `components.md` | 其它组件 |
| 改 JS widget / 资源 | `frontend.md` | PHP 业务层 |
| 改配置 / 集成 / Provider | `config.md` | 组件细节 |
| 排查 XSS / 转义 | `security.md` | 无关组件 |
| 跑测试 / 加测试 | `testing.md` | — |
| 改版本 / 写文档 | `conventions.md` | — |

**识别技巧**：
- 看路径：`src/Components/**` → `components.md`；`resources/assets/js/**` → `frontend.md`；`config/**`/`src/Laravel/**` → `config.md`。
- 看动词：「加组件」→ 注册表+注解+文档+统计；「改固定列」→ `datatable.md`；「修 XSS」→ `security.md`。

---

## 2. 检索阶梯（命中即停，不逐层下钻）

1. **查组件别名**：`rg -n "'alias'" src/XfAdmin.php` → 拿到类名与命名空间。
2. **查类/方法**：`rg -n "function xxx" src/`（带路径限定；**禁止** `grep -r xxx .` 扫仓库根）。
3. **查 JS widget**：`rg -n "XFAdmin.register\('name'" resources/assets/js/xfadmin.js`。
4. **查配置**：直接读 `config/xfadmin.php`（键名集中，带中文注释）。
5. **读大文件分段**：`xfadmin.js` 约 7000 行、`DataTable.php`/`Component.php` 较大，先读 1–80 行 + 方法名定位局部。
6. **需要结构才列目录**：`find src/Components -maxdepth 1 -type d`，禁止对整个仓库递归 tree。

**预算**：定位阶段 ≤ 6 次调用。超出后停下来输出：已确认什么、还缺什么、准备怎么查。

---

## 3. 统计口径（用脚本实测，别信旧文档）

组件/别名数量随版本变化，需要引用时按此实测：

```bash
# 组件类文件数（含基类/trait）
find src/Components -name '*.php' | wc -l

# 注册别名数（解析 $components 数组）
python3 -c "
import re
s=open('src/XfAdmin.php',encoding='utf-8').read()
i=s.index('[', s.index('private static array \$components = [')); d=0
for j in range(i,len(s)):
    if s[j]=='[': d+=1
    elif s[j]==']':
        d-=1
        if d==0: e=j; break
print(len(re.findall(r\"'[A-Za-z0-9_]+'\s*=>\s*[A-Za-z0-9_\\\\]+::class\", s[i+1:e])))
"

# JS widget 数
grep -c "XFAdmin.register(" resources/assets/js/xfadmin.js

# 版本两处
grep -n "VERSION\|'version'" src/XfAdmin.php config/xfadmin.php
```

> 当前基线（2026-11 实测）：225 组件类 / 237 别名 / 58 widget / 版本 1.0.3。

---

## 4. 验证闭环（改动后必做）

| 改动类型 | 验证命令 |
| --- | --- |
| 任意 PHP 改动 | `find src -name '*.php' -print0 \| xargs -0 -n1 php -l` |
| 任意 JS 改动 | `node --check resources/assets/js/xfadmin.js`（及改动的 `.js`） |
| 全量回归 | `bash tests/run-all.sh`（应输出 `ALL GREEN`） |
| 增加组件/widget | 补 `tests/regression.php` 断言 + `tests/audit.php` 契约检查 |
| 文档改动 | `php tools/check_docs_links.php`、`php tools/audit_docs_coverage.php` |
| 项目侧集成改动 | 在 yoc 项目根跑 `vendor/bin/pint --dirty --format agent` + `php artisan test` |
| 版本改动 | 确认 `XfAdmin::VERSION` = `config.version`，composer.json 无 version |

**闭环要求**：改完 → lint → run-all.sh 全绿 → 更新受影响的 `.ai` 规则与 `docs/` → 提交。
任何测试失败先修复，不要跳过。

---

## 5. 判断「要不要读某文件」

按需读，不预读：

| 要做什么 | 必读 | 可跳过 |
| --- | --- | --- |
| 加一个组件别名 | `XfAdmin.php` 注册表 + 组件类 + `docs/03-component-reference/` | 前端 JS |
| 改某组件参数 | 该组件类 `defaults()`/`html()` + 对应文档 | 其它组件 |
| 改一个 widget | `xfadmin.js` 该 register 段 + 触发它的 PHP 组件 | 无关 widget |
| 修一个 XSS | 涉事组件 + `security.md` + `tests/audit.php` | 无关组件 |
| 改版本号 | `conventions.md` §版本 + 两处常量文件 | — |

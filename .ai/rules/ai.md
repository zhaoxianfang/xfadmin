# AI 接入与规则体系使用说明

适用：所有进入 `zxf/xfadmin` 包源码的 AI 工具（Claude Code / WorkBuddy / Cursor / Copilot）。

本文件是 `.ai` 规则体系的**使用说明书**，与 `index.md`（入口/路由表）、`workflow.md`（执行协议）配套。
**所有分析与文档一律使用简体中文。**

---

## 1. 本包的上下文通道（职责不可混淆）

| 通道 | 位置 | 用途 |
| --- | --- | --- |
| **A. AI 规则（权威）** | `.ai/rules/*.md` | 给所有 AI 的硬约束/路由表/源码级事实，长期人工演进 |
| **B. 包内文档（面向使用者）** | `docs/**`、`README.md` | 组件用法、参数、指南，面向「用包的人」 |
| **C. 系统信息（历史档案）** | `system_info.md`（100KB） | 早期系统梳理，体量大，**按需查**不要通读 |
| **D. 生成物** | `tools/selftest/.build/` | 组件文档/自测 HTML，由工具生成，不改 |

**铁律**：组件行为/参数事实以 `src/` 源码为准；文档与源码冲突时改文档；`.ai` 规则记录「怎么维护这个包」，
`docs/` 记录「怎么用这个包」，两者不要互相复制大段内容。

---

## 2. 开工标准动作（固定四步）

```
① 读 .ai/rules/index.md         —— 真实统计、分类地图、硬性禁令、路由表（已含全貌，勿再全局探索）
   ↓
② 按「规则路由表」加载匹配文件   —— 只加载与本次改动相关的 1–2 个规则文件
   ↓
③ 定点定位代码                  —— 注册表→类名→方法，命中即停，≤6 次检索
   ↓
④ 改动 + 验证                   —— 中文注释/语义化命名 → 格式化 → bash tests/run-all.sh
```

> 不确定读哪个规则文件时，看本文件第 4 节场景速查，不要盲猜或预读全部。

---

## 3. 规则文件清单（`.ai/rules/`）

| 文件 | 主题 |
| --- | --- |
| `index.md` | 唯一入口：包定位、真实统计、分类地图、硬性禁令、路由表、检索协议、常用命令 |
| `ai.md` | 本文件：规则体系使用说明、场景速查、演进纪律、红线 |
| `workflow.md` | 任务执行协议：场景路由、检索阶梯、统计口径、验证闭环 |
| `architecture.md` | 架构与目录结构：分层、渲染生命周期、扩展点、构建器、DataProvider |
| `components.md` | 组件体系：注册表机制、基类 API、通用参数、九大分类、代表组件选项 |
| `datatable.md` | DataTable 专项：固定列、自定义搜索、渲染器、服务端协议 |
| `frontend.md` | 前端运行时：XFAdmin JS API、widget、data-xf 契约、事件、资源去重、CSS 主题 |
| `config.md` | 配置与集成：config 全键、版本管理、Laravel/ThinkPHP 集成、Provider/Facade/指令 |
| `security.md` | 安全规范：转义体系、raw 槽位、safeUrl/safeStyle、JSON_HEX、审计清单 |
| `testing.md` | 测试与验证：三套测试、run-all.sh、工具脚本、selftest、验证命令 |
| `conventions.md` | 编码与交付规范：中文注释、语义化、PHP 基线、格式化、版本同步、文档规范 |

---

## 4. 按场景读取哪些规则文件（速查）

| 你要做的事 | 必读（按顺序） |
| --- | --- |
| 首次接手 / 不确定从哪下手 | `index.md` → `workflow.md` |
| 新增/修改一个组件 | `components.md` → `architecture.md` → `conventions.md` |
| 改 DataTable / 渲染器 / 固定列 / 搜索 | `datatable.md`（+ `components.md`） |
| 改 JS 行为 / 加 widget / 调资源加载 | `frontend.md` |
| 改配置 / Provider / Facade / 路由 / ThinkPHP | `config.md` |
| 排查 XSS / 转义 / 属性注入 | `security.md` |
| 跑测试 / 加测试 / 查覆盖率 | `testing.md` |
| 改版本号 / 写文档 / 提交前检查 | `conventions.md` |

---

## 5. 规则文件的演进纪律

1. **单一事实源**：同一约束只在一处定义，别处在路由表引用，不复制粘贴（避免漂移）。
2. **路由表同步**：新增规则文件后必须在 `index.md` 路由表登记，并在本文件清单补齐。
3. **统计要实测**：组件/别名/widget 数量随版本变化，写入规则前用脚本从源码实测（见 `workflow.md`）。
4. **中文优先**：规则、注释、文档一律中文；代码示例中的标识符/键名可英文。
5. **禁止冗余**：不为「一次性任务」建规则文件；确有复用价值的约定才沉淀。
6. **与源码一致**：规则过期立即修，不确定就查源码，不臆测 API。

---

## 6. AI 不应做的事（红线）

- ❌ 不读 `index.md` 就全仓 `grep -r` / `tree` 遍历。
- ❌ 一次性预读 10+ 个规则文件或通读 100KB 的 `system_info.md`「先了解全貌」。
- ❌ 凭 README 的旧数字（222 组件/231 别名）判断当前规模，而不实测源码。
- ❌ 试图寻找组件模板文件（不存在，HTML 由 PHP 拼装）。
- ❌ 把用户可控数据塞进 raw 槽位（`body`/`content`/`footer`/`actions`/`tools`/`menu`）。
- ❌ 在 `composer.json` 里加回 `version` 字段（本包约定由两处常量管理版本）。
- ❌ 为让测试通过而改写既有测试断言。
- ❌ 手写 `.min.js`/`.min.css`（应由源文件和工具产出，且两者都要交付）。

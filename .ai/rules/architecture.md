# 架构与目录结构

## 1. 完整目录树（包根）

```
xfadmin/
├── composer.json              # 元数据/autoload/extra.laravel/extra.think（不写 version）
├── README.md                  # 面向使用者的总览
├── system_info.md             # 早期系统梳理（100KB，按需查，勿通读）
├── config/xfadmin.php         # 全局配置（assets_url/version/theme/brand/footer）
├── src/
│   ├── XfAdmin.php            # final 静态门面：组件注册表 + 工厂 + 全局方法 + VERSION
│   ├── helpers.php            # 全局函数（autoload.files）：xf_admin/xf_asset/xf_head/xf_scripts
│   ├── Assets/Assets.php      # 资源管理器单例：插件去重、head()/scripts()、?v=version
│   ├── Support/
│   │   ├── Html.php           # 转义/属性/class/JSON/scriptJson 安全助手
│   │   ├── DataSet.php        # DataTables 服务端协议处理器（dataResponse）
│   │   ├── DiyRenderer.php    # DIY 布局服务端渲染
│   │   ├── DemoMenu.php       # 演示菜单
│   │   ├── Builder/{Builder,GridBuilder,AdminBuilder,Node}.php  # 链式构建器
│   │   └── Concerns/HasPriceFormat.php
│   ├── Laravel/
│   │   ├── XfAdminServiceProvider.php  # 服务注册/配置合并/publish/路由/Blade 指令/terminating
│   │   ├── Facades/XfAdmin.php         # Laravel 门面
│   │   └── AssetController.php         # /zxf/xfadmin/{path} 资源流式返回（扩展名白名单）
│   ├── ThinkPHP/
│   │   ├── Service.php        # ThinkPHP 服务注册
│   │   └── PublishCommand.php # 资源发布命令
│   └── Components/            # 组件（见 index.md 分类地图，225 类）
│       ├── Component.php      # 抽象基类（所有组件继承，Stringable）
│       ├── Layout/ Navigation/ Grid/ UI/ Form/ Table/ Chart/ Data/ Misc/
│       └── Form/Concerns/FieldWrapper.php  # 字段通用包装 trait
├── resources/
│   ├── assets/
│   │   ├── css/               # app.css/min、vendors.min.css、xfadmin.css、xfadmin-diy.css、fonts/
│   │   ├── js/                # config.js、app.js、xfadmin.js、xfadmin-diy.js、plugins/
│   │   ├── plugins/           # jQuery/Bootstrap/DataTables/ApexCharts/ECharts/... 离线内置
│   │   ├── data/              # datatables.json、translations/、演示数据
│   │   └── images/            # 图标/插画/flags
│   └── views/                 # 极少量 Blade（如验证码片段 captcha.blade.php）
├── docs/                      # 面向使用者的文档（6 大类，见下）
├── tests/                     # smoke/regression/audit + run-all.sh + demo_smoke.sh
├── tools/                     # 文档生成/检查工具 + selftest/（含 .build/ 生成物）
└── .ai/rules/                 # 本 AI 规则体系
```

---

## 2. 分层职责

| 层 | 职责 | 禁止 |
| --- | --- | --- |
| `XfAdmin.php` | 门面分发、组件注册、全局配置、资源入口 | 具体 HTML 拼装 |
| `Components/*` | 组件 HTML 生成（`defaults()`+`html()`）、依赖声明（`assets()`） | 业务逻辑/数据查询 |
| `Support/*` | 纯工具（转义、DataTables 协议、构建器） | UI 输出 |
| `Assets/*` | 资源去重与输出顺序 | 组件逻辑 |
| `Laravel/*` `ThinkPHP/*` | 框架服务注册（配置合并、路由、指令、publish） | 组件渲染逻辑 |
| `resources/assets/*` | 静态 JS/CSS/插件（离线内置） | 业务 URL 硬编码（应由配置注入） |

---

## 3. 组件渲染生命周期（`src/Components/Component.php`）

所有组件继承 `zxf\XfAdmin\Components\Component implements Stringable`。

1. `__construct(array $options)`：`$options = array_replace_recursive($this->defaults(), $options)`
   （**深合并**，子数组递归覆盖）→ `bootCommonOptions()`（归一化 `class`/`id`/`style`/`attrs`）。
2. `defaults()`：子类定义的默认配置（缺省值来源）。
3. `assets()`：返回依赖插件名数组（键名，见 `Assets::PLUGINS`），默认 `[]`。
4. `render()` / `__toString()`：先 `Assets::instance()->plugin(...$plugins)` 登记依赖（**幂等去重**），
   再 `renderWithWrapper()`。
5. `renderWithWrapper()`：条件渲染（`hidden` > `when` > `visible`）→ 调 `html()` → 若有 `col` 用
   `<div class="col-*">` 包裹。
6. `html()`：子类实现，返回 HTML 字符串。

**配置访问**：`set()/get()` 点式路径（`a.b.c`）、`options()` 完整数组。
**第一公民参数**：`class`（addClass 去重）、`id`（attr）、`style`（safeStyle 净化）、`attrs`（批量）。

---

## 4. 扩展点（唯一正确方式）

```php
// 自定义/覆盖组件
XfAdmin::extend('myWidget', \App\Xf\MyWidget::class);  // 类须 extends Component
echo XfAdmin::myWidget(['title' => 'x']);

// 覆盖内置别名
XfAdmin::extend('card', \App\Xf\MyCard::class);
```

- 类必须 `is_subclass_of(Component::class)`，否则抛 `InvalidArgumentException`。
- **禁止直接改 `vendor/zxf/xfadmin/**`**（composer update 会丢失，且无法与源码字节一致）。

---

## 5. 链式构建器（`src/Support/Builder/`）

- `XfAdmin::builder()` → `Builder`、`XfAdmin::grid()` → `GridBuilder`、`XfAdmin::admin()` → `AdminBuilder`。
- PHP 链式拼 HTML（不写 `<div>`）：`newRow()->xlCol(8)->card()->cardHeader()->cardBody()->endCard()->endCol()->endRow()->render()`。
- 节点模型在 `Node.php`。

---

## 6. 资源管理器（`src/Assets/Assets.php`）

- 单例 `instance()`；`reset()` / `resetCollected()` 清空收集（保留 baseUrl/version）。
- `plugin(...$names)`：标记已加载 → 递归解析 `deps` → 有序收集 `css[]`/`js[]`（按路径键去重）；循环依赖检测。
- `head()`：`config.js` → `vendors.min.css` → 插件 CSS → `app.min.css` → `xfadmin.css` → 内联 CSS。
- `scripts()`：`vendors.min.js` → 插件 JS → `app.js` → `xfadmin.js` → 兜底补 head 后注册的 CSS → 内联 JS。
- `url($path)`：相对路径拼 `baseUrl`（默认 `/zxf/xfadmin`）并追加 `?v={version}`；http(s)/`//` 原样。
- 插件键名（`assets()` 只能用这些）：`jquery` `moment` `datatables` `datatables-pdf` `apexcharts`
  `apextree` `apexsankey` `echarts` `qrcode` `choices` `select2` `daterangepicker` `quill` `summernote`
  `dropzone` `filepond` `jstree` `sortablejs` `fullcalendar` `pdfjs` `diy` 等（完整表见源码 `PLUGINS`）。

---

## 7. 内置组件与 JS widget 的桥接

- 组件 PHP 侧输出 `data-xf="<widget名>"` + `data-xf-config='<HEX转义JSON>'` 声明节点（空 div）。
- 前端 `XFAdmin.scan()` 扫描 `data-xf` 节点 → 调对应 `XFAdmin.register(name, fn)` 的回调初始化。
- 典型：`rowActions` / `adminBehaviors` / `perm-tree` / `datatable` / `apexchart` 等。
- 新增交互的正确姿势：PHP 组件输出声明节点 + JS 侧 register widget，**不要**在 PHP 里内联交互脚本。

---

## 8. 文档体系（`docs/`）

| 目录 | 内容 |
| --- | --- |
| `01-getting-started/` | 安装、快速上手、概念、配置 |
| `02-core-architecture/` | 包总览、架构、组件基类、资源、安全、数据协议、助手、扩展、a11y |
| `03-component-reference/` | 00 总览 + 01~12 分类组件参考 |
| `04-advanced-guide/` | 页面布局、导航、DataTable、表单、图表、JS API、主题、认证页、模板映射、Laravel/ThinkPHP、Cookbook、单元格渲染器、链式构建器、15 后台声明式开发 |
| `05-operations/` | 测试、部署、性能、排错 |
| `06-appendix/` | 组件索引、选项索引、插件索引、FAQ、升级、变更日志、组件参考 |

> 组件文档可由 `tools/gen_component_docs.php` 生成；链接完整性用 `tools/check_docs_links.php` 检查。

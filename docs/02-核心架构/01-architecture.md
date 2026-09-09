# 架构总览

## 1. 设计目标

| 目标 | 实现 |
|---|---|
| 框架无关 | 核心渲染层零框架依赖，Laravel / ThinkPHP 只做服务注册与资源发布 |
| 零构建 | 所有第三方库随包内置（40+ 插件），无需 Node / Webpack / Vite |
| 离线可用 | 不请求任何外网资源（地图瓦片等外部服务除外） |
| 按需加载 | 组件声明依赖，同一资源一页内最多加载一次 |
| 安全优先 | 转义分级 + 枚举白名单 + CSS 值校验 + URL 协议白名单 |
| 易扩展 | `XfAdmin::extend()` 注册/覆盖组件；`XFAdmin.register()` 注册前端 widget |

## 2. 目录结构

```
src/
├── XfAdmin.php                  ★ 门面：组件工厂 / 全局配置 / 资源输出 / 数据响应
├── helpers.php                  全局助手函数（xf_admin / xf_asset / xf_head / xf_scripts）
├── Assets/
│   └── Assets.php               ★ 资源管理器：PLUGINS 表、去重、head()/scripts()
├── Components/
│   ├── Component.php            ★ 抽象基类：配置、属性、安全助手、渲染
│   ├── Layout/                  整页、侧栏、顶栏、水平导航、认证页、错误页、栅格…（17 个）
│   ├── Navigation/
│   │   └── Menu.php             侧边栏菜单 DSL
│   ├── Grid/                    Row / Col
│   ├── UI/                      55 个基础 UI 组件
│   ├── Form/                    23 个表单组件 + Concerns/FieldWrapper
│   ├── Table/                   Table / DataTable / TablesCustom / DataTableToolbar
│   ├── Chart/                   ApexChart / ApexTree / ApexSankey / EChart / 3 个地图
│   ├── Data/                    92 个业务组件（电商/内容/协作/沟通/仪表盘）
│   └── Misc/                    18 个杂项（日历/树/拖拽/灯箱/PDF…）
├── Support/
│   ├── Html.php                 HTML 工具：e / attrs / cls / json / scriptJson / get / set
│   ├── DataSet.php              ★ 服务端数据协议（搜索/过滤/排序/分页）
│   └── DemoMenu.php             演示菜单数据
├── Laravel/
│   ├── XfAdminServiceProvider.php   服务注册、Blade 指令、资源路由
│   ├── AssetController.php          资源自托管（ETag + 强缓存）
│   └── Facades/XfAdmin.php          Facade
└── ThinkPHP/
    ├── Service.php                  服务注册 + 助手函数
    └── PublishCommand.php           php think xfadmin:publish

resources/assets/
├── css/     app.min.css（INSPINIA 主题）、vendors.min.css、xfadmin.css（包内自定义）
├── js/      config.js、app.js（框架运行时）、vendors.min.js、xfadmin.js（包运行时核心）
│            pages/*.js（演示页脚本）、plugins/（非 npm 来源插件）
├── images/  users/、products/、svg/、flags/、files/…
├── data/    datatables.json、treeview-data.json、translations/*.json、usa_geo.json
└── plugins/ 40+ 第三方库（jquery、bootstrap、datatables、apexcharts、echarts…）
```

## 3. 类关系

```
                 ┌──────────────────────────┐
                 │  zxf\XfAdmin\XfAdmin     │  门面（final class）
                 │  __callStatic(alias)     │
                 │  component/ config/ ...  │
                 └────────────┬─────────────┘
                              │ component($alias, $options) → $class::make($options)
                              ▼
                 ┌──────────────────────────┐
                 │  Components\Component    │  抽象基类（implements Stringable）
                 │  ├ defaults()            │  默认配置
                 │  ├ assets()              │  依赖插件
                 │  ├ html()                │  渲染（抽象）
                 │  ├ set/get/attr/id/...   │  配置与属性
                 │  └ e/raw/text/enum/...   │  安全助手
                 └────────────┬─────────────┘
        ┌──────────┬──────────┼──────────┬──────────┬─────────┐
        ▼          ▼          ▼          ▼          ▼         ▼
     Layout       UI        Form       Table      Chart     Data / Misc
   (17 个)     (55 个)    (23 个)     (4 个)     (7 个)    (110 个)

继承特例：
  DataTable extends Table          （继承静态表的外观参数）
  ErrorPage / ComingSoon / Maintenance extends AuthPage
  EmptyState（Layout）与 EmptyState（UI）同名不同类
  Toggle（类名）↔ switch（别名）、EmptyState（类名）↔ empty（别名）
```

## 4. 渲染管线

### 4.1 单次组件渲染

```
XfAdmin::card(['title' => 'x'])
  ① component('card', $opts)  → 查注册表 → Card::make($opts)
  ② __construct               → array_replace_recursive(defaults(), $opts)
  ③ echo $component           → __toString() → render()
  ④ render():
       a. $plugins = $this->assets()          （可含条件：如 Input 的 mask/tags）
       b. Assets::instance()->plugin(...$plugins)
            - 查 PLUGINS[$name]
            - 递归解析 deps（如 select2 → jquery）
            - 收集 css[] / js[]
       c. return $this->html()                 （生成 HTML 字符串）
```

### 4.2 整页渲染（Page）

```
Page::html()
  ① 计算主题（内置默认 ← 全局 config ← 页面 theme）
  ② 组装 body：
       <div class="wrapper">
         TopNav（水平）  或  Sidenav + Topbar（垂直）
         <div class="content-page">
           <div class="{container}">
             {page_title}
             {content}          ← 所有业务组件在此渲染，完成资源注册
           </div>
           {footer}
         </div>
       </div>
       {customizer}
  ③ Assets::head()      → config.js + vendors.css + 插件 CSS + app.css + xfadmin.css + 内联 CSS
  ④ Assets::scripts()   → vendors.js + 插件 JS + app.js + xfadmin.js + 兜底 CSS + 内联 JS
  ⑤ 组装完整 HTML 文档
  ⑥ Assets::resetCollected()   ← 支持同请求渲染多个完整页面（邮件、PDF、测试）
```

> **Page 的 `container`** 默认 `container-fluid`，传 `''` 可去掉容器（Landing 页即如此）。
> **AuthPage::document()** 不调用 `resetCollected()`（`Page` 与 `LockScreen` 会）。

## 5. 前后端协作机制

### 5.1 声明式初始化

```html
<!-- PHP 输出 -->
<div data-xf="apexchart" data-xf-config='{"type":"line","series":[…]}' id="chart-1"></div>
```

```js
// 前端自动执行
XFAdmin.scan(document)
  → querySelectorAll('[data-xf]')
  → name = el.dataset.xf
  → fn = XFAdmin.widgets[name]
  → instance = fn(el, JSON.parse(el.dataset.xfConfig || '{}'))
  → el.__xfInited = true
```

### 5.2 资源加载顺序

| 位置 | 内容 |
|---|---|
| `<head>` | `js/config.js`（主题配置，同步执行避免闪烁）→ `css/vendors.min.css` → 插件 CSS → `css/app.min.css` → `css/xfadmin.css` → 内联 `<style>` |
| `</body>` 前 | `js/vendors.min.js` → 插件 JS → `js/app.js`（框架运行时）→ `js/xfadmin.js`（包核心）→ 兜底 CSS → 内联 `<script>` |

`xfadmin.js` 在 `DOMContentLoaded` 时 `boot()`：扫描组件、绑定远程表单、绑定加载按钮、
绑定分页链接、初始化密码强度/验证码/打印等。

### 5.3 数据流（服务端表格）

```
DataTable(server_side: true)
   │  ajax.data 钩子（xfadmin.js）
   ├─ 原生参数：draw / start / length / search[value] / order / columns
   └─ 压缩协议：xfc（列定义）、xfo（排序）、xfs（全局搜索）   ← 减少 URL 长度
        ↓ HTTP
DataSet::parseRequest() 还原为标准结构
        ↓
DataSet::response()  →  过滤 → 搜索 → 排序 → 分页
        ↓
{draw, recordsTotal, recordsFiltered, data}
        ↓
DataTables 渲染行
```

详见 [05-服务端数据协议](05-data-protocol.md)。

## 6. 关键设计决策与取舍

| 决策 | 原因 |
|---|---|
| 组件渲染不做结果缓存 | 内联初始化 JS 按 key/内容去重已保证不重复；多实例需独立 uid |
| `options` 参数作为最终覆盖层 | 允许透传任意 DataTables / ApexCharts 原生配置，避免包成为能力瓶颈 |
| 固定列用 CSS sticky 自实现 | 不引入 DataTables 商业扩展 FixedColumns |
| `colvis`、`print/printHtml5` 前端兜底注册 | 防止"Cannot extend unknown button type"中断整表初始化 |
| 图片路径统一走 `XfAdmin::img()` / `$this->img()` | 同时支持外链、data URI 与包内相对路径 |
| 空图片路径返回透明 1×1 GIF | 避免 `src=""` 触发破图请求 |
| 资源自托管用控制器而非闭包路由 | 闭包路由会导致 `route:cache` 序列化失败 |
| apexsankey 不走 deps，用前后护栏包裹 | 与 apexcharts 内置 svg.js 争抢 `window.SVG`，需顺序加载与恢复 |

## 7. 性能特征

- **资源注册**：O(1) 哈希去重，无重复磁盘/网络 IO；
- **组件渲染**：纯字符串拼接，无模板引擎编译开销；
- **大数据表格**：服务端模式下 PHP 侧只查当前页（`forPage`），JS 侧 `deferRender`（本地 ≥100 行自动开）；
- **内联 JS**：按 key 去重，同一段初始化代码只输出一次。

## 8. 扩展点总览

| 扩展点 | 位置 | 说明 |
|---|---|---|
| 自定义组件 | `XfAdmin::extend('myWidget', MyClass::class)` | 继承 `Component` 即可 |
| 覆盖内置组件 | 同上，传入已存在的别名 | 如替换默认 Card |
| 自定义单元格渲染器 | `XFAdmin.registerCellRenderer(name, fn)` / `render => 'js:App.render.x'` | 表格列渲染 |
| 自定义前端 widget | `XFAdmin.register(name, initFn, destroyFn?)` | 配合 `data-xf="name"` |
| 自定义插件资源 | `Assets::PLUGINS`（源码常量）或 `Assets::instance()->css()/js()` | 追加资源 |
| 命令面板动作 | `XFAdmin.onCommand(name, fn)` | ⌘K 快捷命令 |
| 单元格事件 | `XFAdmin.onCell(name, fn)` | 列上配置 `event` |

详见 [07-扩展机制](07-extending.md)。

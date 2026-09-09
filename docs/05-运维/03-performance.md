# 性能优化

## 1. 资源加载优化

### 1.1 按需加载（内置）

组件在渲染时声明依赖，同一资源一页内只加载一次：

```php
// 未使用 select2 的页面不会加载 select2
XfAdmin::card(['title' => 'x']);              // 0 个插件
XfAdmin::select(['enhance' => 'select2', …]); // + select2 + jquery
```

**优化建议**：

- 只在需要时启用增强控件（`enhance => 'choices'|'select2'`）；
- 只在需要时启用导出（`export` 含 `pdf` 会额外加载 pdfmake + vfs_fonts，约 1MB）；
- 避免在一个页面同时使用 apexcharts + echarts（两套图表库）。

### 1.2 资源发布 vs 自托管

| 方式 | 首屏影响 |
|---|---|
| 自托管路由（Laravel） | 每个资源走一次 PHP（有 ETag，命中 304 后几乎零开销） |
| 发布到 public | Web 服务器直出，最快 |
| CDN | 最快 + 就近访问 |

生产环境**务必发布到 public 或 CDN**。

### 1.3 版本号与缓存

`?v={version}` + `Cache-Control: max-age=31536000` + ETag，
使静态资源只在新版本时重新下载。

## 2. 表格性能

### 2.1 服务端模式（推荐）

```php
'ajax' => '/api/users', 'server_side' => true, 'method' => 'POST',
```

- PHP 侧只查当前页（`forPage`），不加载全表；
- 前端用压缩协议 `xfc/xfo/xfs`，避免 URL 过长。

### 2.2 本地数据

- 行数 < 100：默认不开启 `deferRender`（保持全选行为）；
- 行数 ≥ 100：自动开启 `deferRender`；
- 超过数千行请改用服务端模式。

### 2.3 列与渲染器

- 交互型渲染器（`input`/`select`/`switch`/`toggle`/`timeline`/`sparkline`）会为每个单元格生成 DOM，
  避免在大数据量表格中滥用；
- `qr` 渲染器会为每个单元格生成二维码（并加载 qrcode 库），慎用；
- 固定列（`fixed_columns`）用 CSS sticky 实现，列数越多计算越频繁，建议 ≤ 2 列。

### 2.4 减少自动增强

| 选项 | 影响 | 建议 |
|---|---|---|
| `column_filters` | 每列一个输入框 + 防抖查询 | 大数据量时关闭 |
| `fixed_columns` | ResizeObserver + draw 重算 | 固定 1 列即可 |
| `fixed_header` | FixedHeader 扩展 | 需要时再开 |
| `state_save` | localStorage 读写 | 影响很小 |
| `buttons` 含 `colvis` | 生成列菜单 | 需要时再开 |

## 3. 页面渲染性能

- 组件渲染是纯字符串拼接，无模板编译；
- 嵌套层级对性能影响很小；
- 避免在循环中反复创建同类组件（可复用配置数组）；
- `Page` 渲染后调用 `Assets::resetCollected()`，同请求多页面渲染互不污染。

## 4. 前端运行时

| 机制 | 说明 |
|---|---|
| `XFAdmin.scan()` | 只初始化 `[data-xf]` 且未 `__xfInited` 的元素 |
| 资源去重 | `XFAdmin._loaded` 记录已加载 URL，同一 URL 只加载一次 |
| 按需初始化 | 图表在容器宽度为 0 时延迟初始化（Tabs/折叠区） |
| 列宽同步 | ResizeObserver 200ms 防抖 + window resize 300ms 防抖 |
| Tooltip/Popover | `draw.dt` 后重建（仅在表格中使用时） |

**优化建议**：

- AJAX 替换内容前调用 `XFAdmin.destroyWithin(oldNode)`，避免定时器/监听泄漏；
- 长列表优先用服务端分页，而非一次性渲染；
- 图表较多时考虑懒加载（滚动到视口再 `XFAdmin.scan()`）。

## 5. 数据库（服务端模式）

```php
'searchable' => ['name', 'email'],   // 尽量少，且确保有索引
```

- 全局搜索字段应建索引（或至少避免大字段 LIKE）；
- `filters` 中 `=`、`in` 的字段建索引；
- `date_from/date_to` 字段建索引；
- 避免 `transform` 中做重查询（它会对当前页每行执行）。

## 6. 缓存策略

| 层级 | 方案 |
|---|---|
| 静态资源 | `?v=` + 一年强缓存 + ETag |
| 配置 | Laravel `config:cache` / ThinkPHP runtime 缓存 |
| 菜单数据 | 自行缓存（如 `Cache::remember('admin.menu', 3600, fn () => …)`） |
| 表格数据 | 服务端接口自行加缓存（注意与分页参数组合） |

## 7. 常见性能问题

| 现象 | 原因 | 优化 |
|---|---|---|
| 首屏白屏时间长 | 加载了过多插件 | 精简组件；去掉 pdf 导出；发布资源 |
| 表格卡顿 | 本地渲染几千行 | 改服务端模式 |
| 表格拖动卡 | 固定列过多 / 列数过多 | 减少固定列；关闭 `column_filters` |
| 图表切换慢 | 同时加载两套图表库 | 只用一套 |
| 内存占用高 | 数组中转大数据集 | 用查询构造器下推条件 |
| AJAX 响应慢 | 搜索字段无索引 | 加索引 / 缩小 `searchable` |

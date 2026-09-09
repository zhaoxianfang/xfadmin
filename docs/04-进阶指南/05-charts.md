# 图表全指南

XfAdmin 提供 4 类图表/地图组件，全部通过 `data-xf` 声明式初始化，支持主题联动。

## 1. 组件总览

| 别名 | 底层库 | 用途 |
|---|---|---|
| `apexChart` | ApexCharts | 通用图表（折线/柱/饼/雷达/面积…） |
| `apexTree` | ApexTree | 组织架构树 |
| `apexSankey` | ApexSankey + svg.js | 桑基图（流量关系） |
| `echart` | Apache ECharts | 通用可视化（含热力/关系图等） |
| `metric-chart`（内部 widget） | ECharts | 迷你图表（donut/pie/bar/line/area） |
| `vectorMap` | jsVectorMap | 矢量地图 |
| `leafletMap` | Leaflet | 交互式地图（支持离线瓦片） |
| `googleMap` | Google Maps | Google 地图 |

## 2. ApexCharts（`apexChart`）

```php
echo XfAdmin::apexChart([
    'id'         => 'sales-chart',
    'type'       => 'line',            // line | bar | area | pie | donut | radar | scatter…
    'height'     => 320,
    'series'     => [
        ['name' => '今年', 'data' => [120, 200, 150, 280, 220, 310]],
        ['name' => '去年', 'data' => [90, 150, 130, 210, 180, 250]],
    ],
    'categories' => ['1月','2月','3月','4月','5月','6月'],
    'options'    => [                  // 透传 ApexCharts 原生配置（优先级最高）
        'stroke'  => ['curve' => 'smooth', 'width' => 3],
        'markers' => ['size' => 4],
        'dataLabels' => ['enabled' => false],
    ],
]);
```

| 参数 | 默认 | 说明 |
|---|---|---|
| `id` | 自动生成 | 容器 id（需 JS 操作时请显式指定） |
| `type` | `'line'` | 图表类型 |
| `height` | `320` | 高度（px） |
| `series` | `[]` | 数据系列 |
| `categories` | `[]` | X 轴分类（非时序图） |
| `options` | `[]` | 透传原生配置 |

前端 widget（`XFAdmin.register('apexchart')`）：

- 未给 `theme` 时自动跟随 `data-bs-theme`（明暗切换自动重绘）；
- 颜色默认取 `--ins-*` CSS 变量（与主题一致）；
- 支持 `data-colors` 属性覆盖配色。

### 主题联动

`app.js` 的 `CustomApexChart` 维护静态实例池，`MutationObserver` 监听
`<html data-skin>` 与 `data-bs-theme` 变化 → `rerenderAll()`。

## 3. 组织架构树（`apexTree`）

```php
echo XfAdmin::apexTree([
    'id'     => 'org-tree',
    'height' => 500,
    'data'   => [[
        'id' => 1, 'name' => '张三', 'role' => 'CEO', 'avatar' => 'users/user-1.jpg',
        'color' => '#3e60d5',
        'children' => [
            ['id' => 2, 'name' => '李四', 'role' => 'CTO', 'avatar' => 'users/user-2.jpg'],
            ['id' => 3, 'name' => '王五', 'role' => 'CFO', 'avatar' => 'users/user-3.jpg'],
        ],
    ]],
    'nodeWidth'  => 150,
    'nodeHeight' => 60,
    'direction'  => 'top',
]);
```

## 4. 桑基图（`apexSankey`）

```php
echo XfAdmin::apexSankey([
    'id'    => 'flow',
    'nodes' => [
        ['id' => 'pv',  'title' => '访问', 'color' => '#3e60d5'],
        ['id' => 'cart', 'title' => '加购', 'color' => '#22c55e'],
        ['id' => 'paid', 'title' => '支付', 'color' => '#f59e0b'],
    ],
    'edges' => [
        ['source' => 'pv',   'target' => 'cart', 'value' => 8000],
        ['source' => 'cart', 'target' => 'paid', 'value' => 2200],
    ],
    'height' => 400,
]);
```

⚠️ 依赖 svg.js v3，与 apexcharts 内置的旧版 svg.js 冲突；
包内用「前置护栏 → svg.js v3 → apexsankey → 后置护栏恢复」顺序加载（见
[03-资源与插件](../02-核心架构/03-assets.md#6-特殊加载顺序apexsankey)）。

## 5. ECharts（`echart`）

```php
echo XfAdmin::echart([
    'id'      => 'chart-1',
    'height'  => 360,
    'theme'   => null,                 // null 时按 data-bs-theme 自动选（dark → 'dark'）
    'options' => [
        'tooltip' => ['trigger' => 'axis'],
        'legend'  => ['data' => ['销量']],
        'xAxis'   => ['type' => 'category', 'data' => ['Mon','Tue','Wed','Thu','Fri']],
        'yAxis'   => ['type' => 'value'],
        'series'  => [['name' => '销量', 'type' => 'bar', 'data' => [120, 200, 150, 80, 70]]],
    ],
]);
```

`metric-chart` widget（内部使用，供 `metricCard` 等组件调用）：

```js
// data-xf="metric-chart" data-xf-config='{"type":"donut","data":[1,2],"labels":["a","b"]}'
```

支持 `type`：`donut | pie | bar | line | area`。

## 6. 地图

### 6.1 矢量地图（`vectorMap`）

```php
echo XfAdmin::vectorMap([
    'id'      => 'world-map',
    'map'     => 'world',              // 需同时加载 jsvectormap-world 资源
    'height'  => 400,
    'options' => [
        'markers' => [['coords' => [39.9, 116.4], 'name' => '北京']],
        'series'  => ['regions' => [['values' => ['CN' => 100], 'scale' => ['#e8f0fe'], 'normalizeFunction' => 'polynomial']]],
    ],
]);
```

### 6.2 Leaflet（`leafletMap`）

```php
echo XfAdmin::leafletMap([
    'id'     => 'map',
    'center' => [39.9, 116.4],
    'zoom'   => 12,
    'height' => 400,
    'tiles'  => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',   // null 时离线（空白底图）
    'markers'=> [
        ['lat' => 39.9, 'lng' => 116.4, 'popup' => '北京'],
    ],
]);
```

⚠️ 初始化顺序（已内置修复）：`invalidateSize()` → `setView(center, zoom, {animate:false})`
→ `_resetView(getCenter(), getZoom())`，外层 rAF + `setTimeout(200)` + ResizeObserver 持续校正。
缺少任一步都会导致瓦片错位（跑到视口外）。

### 6.3 Google 地图（`googleMap`）

```php
echo XfAdmin::googleMap([
    'id' => 'gmap', 'center' => ['lat' => 39.9, 'lng' => 116.4], 'zoom' => 12,
    'api_key' => 'YOUR_KEY', 'height' => 400,
]);
```

## 7. 主题与配色联动

| 机制 | 说明 |
|---|---|
| `--ins-*` CSS 变量 | 图表配色默认从 CSS 变量取色（`ins(name, alpha)` 辅助函数） |
| `data-bs-theme` | 明暗切换 → Apex/EChart 自动重绘（MutationObserver） |
| `data-skin` | 皮肤切换 → 图表重绘 |
| `data-sidenav-size` | 侧栏尺寸变化 → ECharts `resize()` |
| `data-colors` 属性 | 单图覆盖配色（逗号分隔 hex） |

## 8. 常见问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 图表空白 | 容器高度为 0（在隐藏标签页/折叠区） | 触发 `shown.bs.tab` 或手动 `XFAdmin.scan()` |
| 图表不随主题变化 | 未加载 `app.js` 或 MutationObserver 失效 | 确认 `scripts()` 输出 |
| 两个图表库冲突 | apexsankey 与 apexcharts 争抢 `window.SVG` | 已内置护栏，确认资源顺序 |
| 地图瓦片错位 | 容器 0 尺寸时初始化 | 组件已内置三重校正；自定义容器需保证有高度 |
| 离线环境地图空白 | `tiles` 指向外网 | 设 `tiles => null` 或部署本地瓦片 |
| 中文乱码 | 字体缺失 | 设置 `options.fontFamily` |

# 图表与地图

> ApexCharts 通用图表 / 树图 / 桑基图、ECharts、矢量地图、Leaflet 与 Google 地图。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`apexChart`](#apexchart) — ApexCharts 图表（折线/柱/饼/雷达等）
- [`apexTree`](#apextree) — ApexCharts 树形图
- [`apexSankey`](#apexsankey) — ApexCharts 桑基图（流量关系）
- [`echart`](#echart) — ECharts 图表（通用可视化）
- [`vectorMap`](#vectormap) — 矢量地图（jsVectorMap）
- [`leafletMap`](#leafletmap) — Leaflet 地图（支持离线瓦片）
- [`googleMap`](#googlemap) — Google 地图

## 本章导读

图表组件统一通过 `data-xf` 声明式初始化，支持主题联动（明暗切换自动重绘）。

### 组合范式

```php
echo XfAdmin::card([
    'title' => '销售趋势',
    'body'  => XfAdmin::apexChart([
        'id'         => 'sales-chart',
        'type'       => 'line',
        'height'     => 320,
        'series'     => [['name' => '今年', 'data' => [120, 200, 150, 280]]],
        'categories' => ['1月','2月','3月','4月'],
    ]),
]);
```

### 约定与陷阱

- 需要 JS 操作时**显式传 `id`**；
- 图表在容器宽度为 0 时（隐藏标签页/折叠区）会延迟初始化，显示时需触发 `shown.bs.tab` 或手动 `XFAdmin.scan()`；
- `apexSankey` 与 `apexcharts` 争抢 `window.SVG`，已用前后护栏包裹，勿调整加载顺序；
- 避免同页同时使用 ApexCharts 与 ECharts（两套库，体积翻倍）；
- 离线环境下地图瓦片请求外网会失败，可设 `tiles => null`。


---

### `apexChart`

ApexCharts 图表（折线/柱/饼/雷达等）。

> **类**：`zxf\XfAdmin\Components\Chart\ApexChart`
> **文件**：`src/Components/Chart/ApexChart.php`（70 行）
> **依赖插件**：`apexcharts`

**用法示例**

```php
XfAdmin::apexChart([
    'type'   => 'line',
    'height' => 350,
    'series' => [['name' => '销量', 'data' => [10, 41, 35, 51]]],
    'labels' => ['一月', '二月', '三月', '四月'],       // 便捷 xaxis.categories / 饼图 labels
    'colors' => ['#3e60d5'],
    'options'=> [ ... 透传 ApexCharts 原生配置，最高优先级 ... ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `type` | string | `'line'` | 类型（各组件语义不同，详见该组件说明） |
| `height` | int | `350` | 高度（CSS 长度，受安全白名单约束） |
| `width` | mixed | `null` | 宽度（数字=栅格列数或 CSS 长度） |
| `series` | array | `[]` |  |
| `labels` | mixed | `null` |  |
| `colors` | mixed | `null` |  |
| `sparkline` | bool | `false` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `apexTree`

ApexCharts 树形图。

> **类**：`zxf\XfAdmin\Components\Chart\ApexTree`
> **文件**：`src/Components/Chart/ApexTree.php`（72 行）
> **依赖插件**：`apextree`

**用法示例**

```php
XfAdmin::apexTree([
    'height'    => 500,
    'direction' => 'top',              // top | bottom | left | right（树的生长方向）
    'data'      => [                   // 嵌套节点：id/name 必填，其余可选
        'id'   => '1',
        'name' => '董事长',
        'role' => 'CEO',               // 副标题（职位）
        'avatar' => 'users/user-1.jpg',     // 头像（相对 images/ 或完整 URL）
        'color'  => '#3e60d5',         // 节点边框色
        'children' => [ [...], [...] ],
    ],
    'node_width'  => 150,
    'node_height' => 60,
    'collapsible' => true,             // 节点可展开收起
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `height` | int | `500` | 高度（CSS 长度，受安全白名单约束） |
| `direction` | string | `'top'` | 方向 |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `node_width` | int | `150` |  |
| `node_height` | int | `60` |  |
| `collapsible` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `apexSankey`

ApexCharts 桑基图（流量关系）。

> **类**：`zxf\XfAdmin\Components\Chart\ApexSankey`
> **文件**：`src/Components/Chart/ApexSankey.php`（69 行）
> **依赖插件**：`apexsankey`

**用法示例**

```php
XfAdmin::apexSankey([
    'height' => 400,
    'nodes'  => [                          // 节点：id 必填，title 缺省取 id
        ['id' => 'oil',  'title' => '石油'],
        ['id' => 'coal', 'title' => '煤炭', 'color' => '#fa5c7c'],
        ['id' => 'energy', 'title' => '能源'],
    ],
    'edges'  => [                          // 连线：source/target/value 必填，color 可选
        ['source' => 'oil',  'target' => 'energy', 'value' => 15],
        ['source' => 'coal', 'target' => 'energy', 'value' => 25, 'color' => '#ffe5eb'],
    ],
    'node_width' => 20,                    // 节点条宽度（px）
    'toolbar'    => true,                  // 是否显示缩放工具栏
    'order'      => null,                  // 可选：各列节点排序（apexsankey options.order 原生结构）
    'options'    => [ ... 透传 apexsankey 原生图形配置，最高优先级 ... ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `height` | int | `400` | 高度（CSS 长度，受安全白名单约束） |
| `nodes` | array | `[]` |  |
| `edges` | array | `[]` |  |
| `node_width` | int | `20` |  |
| `toolbar` | bool | `true` | 是否显示工具条 |
| `order` | mixed | `null` | 排序规则，如 `[[0, 'asc']]` |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `echart`

ECharts 图表（通用可视化）。

> **类**：`zxf\XfAdmin\Components\Chart\EChart`
> **文件**：`src/Components/Chart/EChart.php`（52 行）
> **依赖插件**：`echarts`

**用法示例**

```php
XfAdmin::echart([
    'height'  => 350,
    'options' => [
        'xAxis'  => ['type' => 'category', 'data' => ['Mon', 'Tue']],
        'yAxis'  => ['type' => 'value'],
        'series' => [['type' => 'bar', 'data' => [120, 200]]],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `height` | int | `350` | 高度（CSS 长度，受安全白名单约束） |
| `theme` | mixed | `null` | 主题（light / dark / 图表主题名） |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `vectorMap`

矢量地图（jsVectorMap）。

> **类**：`zxf\XfAdmin\Components\Chart\VectorMap`
> **文件**：`src/Components/Chart/VectorMap.php`（48 行）
> **依赖插件**：`jsvectormap-world`

**用法示例**

```php
XfAdmin::vectorMap([
    'map'     => 'world',      // world | world_merc
    'height'  => 360,
    'markers' => [['name' => 'Beijing', 'coords' => [39.9, 116.4]]],
    'options' => [ ...透传 jsVectorMap 配置... ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `map` | string | `'world'` |  |
| `height` | int | `360` | 高度（CSS 长度，受安全白名单约束） |
| `markers` | array | `[]` |  |
| `options` | array | `[]` | 透传给底层插件的原生配置（递归合并，优先级最高） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `leafletMap`

Leaflet 地图（支持离线瓦片）。

> **类**：`zxf\XfAdmin\Components\Chart\LeafletMap`
> **文件**：`src/Components/Chart/LeafletMap.php`（83 行）
> **依赖插件**：`leaflet`

**用法示例**

```php
XfAdmin::leafletMap([
    'height'  => 400,
    'center'  => [39.9, 116.4],
    'zoom'    => 11,
    'tiles'   => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', // null=离线无底图
    'markers' => [['lat'=>39.9,'lng'=>116.4,'title'=>'总部','popup'=>'说明']],
    'circles' => [['lat'=>39.9,'lng'=>116.4,'radius'=>1200,'color'=>'#3e60d5']],
    'polygons'=> [['points'=>[[39.9,116.4],[39.91,116.41]],'color'=>'#198754']],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `height` | int | `400` | 高度（CSS 长度，受安全白名单约束） |
| `center` | array | `[39.9042, 116.4074]` | 数组结构（见组件用法示例） |
| `zoom` | int | `11` |  |
| `tiles` | string | `'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'` | {s}.tile.openstreetmap.org/{z}/{x}/{y}.png', |
| `markers` | array | `[]` |  |
| `circles` | array | `[]` |  |
| `polygons` | array | `[]` |  |
| `lines` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `googleMap`

Google 地图。

> **类**：`zxf\XfAdmin\Components\Chart\GoogleMap`
> **文件**：`src/Components/Chart/GoogleMap.php`（55 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::leafletMap 并设置 tiles=null）。;
XfAdmin::googleMap([
    'height'  => 400,
    'place'   => '北京市朝阳区',            // 地点搜索（优先于 center）
    'center'  => [39.9042, 116.4074],      // 经纬度定位 [lat, lng]
    'zoom'    => 12,                       // 缩放级别 1~21
    'maptype' => 'roadmap',                // roadmap 普通 | satellite 卫星
    'language'=> 'zh-CN',                  // 界面语言
    'rounded' => true,                     // 圆角容器
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `height` | int | `400` | 高度（CSS 长度，受安全白名单约束） |
| `place` | mixed | `null` |  |
| `center` | array | `[39.9042, 116.4074]` | 数组结构（见组件用法示例） |
| `zoom` | int | `12` |  |
| `maptype` | string | `'roadmap'` |  |
| `language` | string | `'zh-CN'` | 语言包覆盖（DataTables） |
| `rounded` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


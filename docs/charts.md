# 图表与地图

> 文档导航：[README](../README.md) · [组件总览](components.md) · [静态资源](assets.md) · [布局](layout.md) · [表单](forms.md) · [表格](tables.md) · [图表](charts.md) · [安全规范](security.md) · [扩展组件](extending.md) · [ThinkPHP](thinkphp.md) · [页面映射](pages.md)

所有图表组件在服务端只渲染容器 + 初始化脚本，数据以 JSON 注入，前端由 `xfadmin.js` 惰性初始化，**同一图表库在一页内只加载一次**。

## ApexCharts `apexChart`

```php
XfAdmin::apexChart([
    'type'   => 'area',                 // line/area/bar/column/pie/donut/radialBar/heatmap...
    'height' => 350,
    'series' => [
        ['name' => '销量', 'data' => [30, 40, 35, 50, 49, 60, 70]],
        ['name' => '退货', 'data' => [5, 8, 6, 9, 7, 10, 8]],
    ],
    'categories' => ['一', '二', '三', '四', '五', '六', '日'],
    'colors'  => ['#3e60d5', '#ef5f5f'],
    'options' => [                      // 透传任意 ApexCharts 原生配置（深度合并）
        'stroke' => ['curve' => 'smooth'],
        'legend' => ['position' => 'top'],
    ],
]);
```

饼图/环形图：

```php
XfAdmin::apexChart([
    'type'   => 'donut',
    'series' => [44, 55, 13, 33],
    'labels' => ['A', 'B', 'C', 'D'],
]);
```

## ECharts `eChart`

```php
XfAdmin::eChart([
    'height'  => 400,
    'options' => [                      // 完整 ECharts option
        'tooltip' => ['trigger' => 'axis'],
        'xAxis'   => ['type' => 'category', 'data' => ['周一', '周二', '周三']],
        'yAxis'   => ['type' => 'value'],
        'series'  => [['type' => 'bar', 'data' => [120, 200, 150]]],
    ],
]);
```

## 矢量地图 `vectorMap`

```php
XfAdmin::vectorMap([
    'map'     => 'world',               // world / us_aea / 等内置地图 JSON
    'height'  => 360,
    'markers' => [
        ['name' => '北京', 'coords' => [39.9, 116.4]],
        ['name' => '上海', 'coords' => [31.2, 121.5]],
    ],
    'options' => [
        'regionStyle' => ['initial' => ['fill' => '#e5e7eb']],
    ],
]);
```

---

## 组织架构树 `apexTree`

基于 `apextree` 插件（离线可用），以可展开/缩放的树状节点展示组织架构、层级关系或血缘图。

```php
echo XfAdmin::apexTree([
    'height'    => 500,
    'direction' => 'top',               // top | bottom | left | right 树的生长方向
    'data'      => [
        'id'      => '1',
        'name'    => '董事长',
        'role'    => 'CEO',              // 节点副标题
        'avatar'  => 'users/user-1.jpg', // 头像（相对 images/ 或完整 URL，自动解析）
        'color'   => '#3e60d5',          // 节点边框色
        'children' => [
            ['id' => '2', 'name' => '技术 VP', 'role' => 'VP', 'avatar' => 'users/user-2.jpg',
             'children' => [['id' => '4', 'name' => '前端组长'], ['id' => '5', 'name' => '后端组长']]],
            ['id' => '3', 'name' => '市场 VP'],
        ],
    ],
    'node_width'  => 150,
    'node_height' => 60,
    'collapsible' => true,
]);
```

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `data` | array | — | 树根节点，嵌套 `children`；字段：`id`(必填)、`name`、`role`、`avatar`、`color` |
| `direction` | string | `top` | 树生长方向 |
| `height` | int | `500` | 画布高度 px |
| `node_width` / `node_height` | int | `150`/`60` | 节点尺寸 |
| `collapsible` | bool | `true` | 节点是否可展开/收起 |

---

## 桑基图 `apexSankey`

基于 `apexsankey` 插件（离线可用，自动注入 svg.js 依赖），以「来源 → 去向」的流向带展示数值构成，常用于流量来源、能源流向、招聘漏斗等。连线宽度按 `value` 自动分配，支持缩放工具栏与暗色主题适配。

```php
echo XfAdmin::apexSankey([
    'height' => 380,
    // 节点：字符串简写等价 ['id'=>'oil','title'=>'oil']；可设 color 指定节点色
    'nodes'  => [
        'search',
        'social',
        ['id' => 'visit', 'title' => '访问量'],
        ['id' => 'signup', 'title' => '注册', 'color' => '#3e60d5'],
    ],
    // 连线：source/target/value 必填；color 可选（单独着色某条连线）
    'edges'  => [
        ['source' => 'search', 'target' => 'visit', 'value' => 60],
        ['source' => 'social', 'target' => 'visit', 'value' => 25, 'color' => '#fa5c7c'],
        ['source' => 'visit',  'target' => 'signup', 'value' => 40],
    ],
    'node_width' => 20,          // 节点条宽度 px
    'toolbar'    => true,         // 是否显示缩放工具栏
    // 'order' => [[['search','social']],[['visit']],[['signup']]], // 各列节点显示顺序（可选）
    // 'options' => [ 'edgeOpacity' => 0.2 ],  // 透传 apexsankey 原生图形配置，优先级最高
]);
```

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `nodes` | array | `[]` | 节点列表；元素可为字符串（简写）或 `['id'=>,'title'=>,'color'=>]`；`title` 省略时取 `id` |
| `edges` | array | `[]` | 连线列表；`source`/`target`/`value` 必填，`color` 可选 |
| `height` | int | `400` | 画布高度 px |
| `node_width` | int | `20` | 节点条宽度 px |
| `toolbar` | bool | `true` | 是否显示缩放工具栏 |
| `order` | array\|null | `null` | 各列节点显示顺序（apexsankey 原生 `options.order` 结构） |
| `options` | array | `[]` | 透传 apexsankey 原生图形配置（如 `edgeOpacity`、`nodeBorderWidth`），优先级最高 |

> 离线可用：资源由 `assets()` 返回 `['apexsankey']`，`Assets` 会自动按依赖注入 `svgdotjs`。

---

## 谷歌地图 `googleMap`

采用**免 API Key 的 iframe 嵌入**，支持按地名搜索或经纬度定位，可切换卫星视图。
> 注意：底图数据来自 Google 在线服务，离线/无外网环境不可用；离线场景请改用 `leafletMap(tiles: null)`。

```php
echo XfAdmin::googleMap([
    'height'  => 400,
    'place'   => '北京市朝阳区',          // 按地名搜索（优先于 center）
    // 'center' => [39.9042, 116.4074],   // 或经纬度定位 [lat, lng]
    'zoom'    => 12,                       // 1~21
    'maptype' => 'roadmap',                // roadmap 普通 | satellite 卫星
    'language'=> 'zh-CN',
    'rounded' => true,
]);
```

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `place` | string | — | 地名搜索（优先级高于 center） |
| `center` | array | `[39.9, 116.4]` | 经纬度定位 `[lat, lng]` |
| `zoom` | int | `12` | 缩放级别 1~21 |
| `maptype` | string | `roadmap` | `roadmap` 普通 / `satellite` 卫星 |
| `language` | string | `zh-CN` | 界面语言 |
| `rounded` | bool | `true` | 容器是否圆角 |

---

## 动态更新

图表实例挂在容器元素的 `_xf` 上，可在前端更新：

```js
const el = document.getElementById('my-chart');   // 传 id 给组件
// ApexCharts
el._xf.chart.updateSeries([{ data: [1, 2, 3] }]);
// ECharts
el._xf.chart.setOption({ series: [{ data: [4, 5, 6] }] });
```

```php
XfAdmin::apexChart(['id' => 'my-chart', 'type' => 'line', 'series' => [...]]);
```

### Leaflet 交互地图 `leafletMap`

标记、圆、多边形、折线；底图瓦片默认来自 OpenStreetMap（需在线）。设置 `tiles => null` 可**完全离线**渲染（仅显示标记/图形，无底图）。

```php
echo XfAdmin::leafletMap([
    'height' => 420,
    'center' => [39.9042, 116.4074],
    'zoom'   => 12,
    'tiles'  => null,                 // 离线：无底图
    'markers' => [
        ['lat' => 39.90, 'lng' => 116.40, 'title' => '总部', 'popup' => '详细说明'],
    ],
    'circles' => [['lat' => 39.90, 'lng' => 116.40, 'radius' => 1200, 'color' => '#3e60d5']],
]);
```

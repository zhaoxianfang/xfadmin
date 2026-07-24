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

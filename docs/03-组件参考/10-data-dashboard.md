# 业务组件（五）仪表盘 · 模块 · 通用

> 仪表盘网格、小部件、指标卡、分析报表、设置中心、模块导航/网格、API 密钥、应用管理与导入导出。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`widget`](#widget) — 通用小部件卡（标题+内容+数值）
- [`metricCard`](#metriccard) — 迷你指标卡（数字+图标+同比）
- [`statMiniSparkline`](#statminisparkline) — 迷你指标+火花线（数字+趋势迷你图）
- [`widgetsDashboard`](#widgetsdashboard) — 小部件仪表盘（多 widget 汇总）
- [`analyticsDashboard`](#analyticsdashboard) — 数据分析仪表盘（多图表汇总）
- [`dashboardGrid`](#dashboardgrid) — 仪表盘网格（可拖拽 widget 布局）
- [`settingsCenter`](#settingscenter) — 设置中心（分组设置入口）
- [`reportPage`](#reportpage) — 报表页（数据报表展示）
- [`moduleNav`](#modulenav) — 模块导航（企业级模块菜单）
- [`moduleGrid`](#modulegrid) — 模块网格（功能模块卡片墙）
- [`apiKeys`](#apikeys) — API 密钥管理（密钥列表+创建/撤销）
- [`appManage`](#appmanage) — 应用管理列表（apps-manage）
- [`importExport`](#importexport) — 批量导入/导出工具条（DataTable / 管理页通用） 对标后台高频「导出 CSV/Excel/JSON + 导入文件」操作区，自我包含（Bootstrap 下拉 + 模态框）， 不依赖额外前端插件

## 本章导读

仪表盘与通用业务组件：可拖拽仪表盘网格、小部件、指标卡、报表、设置中心、模块导航/网格、
API 密钥、应用管理、导入导出。

### 组合范式

```php
echo XfAdmin::dashboardGrid(['widgets' => [
    ['id' => 'w1', 'title' => '销售', 'content' => $chart, 'width' => 8],
    ['id' => 'w2', 'title' => '订单', 'content' => $table, 'width' => 4],
]]);

echo XfAdmin::settingsCenter(['groups' => [...]]);
echo XfAdmin::importExport(['export_url' => '/api/export', 'import_url' => '/api/import',
                          'formats' => ['csv', 'xlsx']]);
```

### 约定与陷阱

- `importExport` 的 `formats` 默认 `[]`（因数组并集合并特性），使用 `export_url` 简写时**必须显式传**；
- `dashboardGrid` 拖拽依赖 SortableJS / Muuri（按配置自动加载）；
- `settingsCenter` 的分组表单需自行接保存端点。


---

### `widget`

通用小部件卡（标题+内容+数值）。

> **类**：`zxf\XfAdmin\Components\Data\Widget`
> **文件**：`src/Components/Data/Widget.php`（63 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::widget([
    'style'   => 'icon',          // icon|progress|chart|minimal
    'title'   => '总营收',
    'value'   => '¥52,000',
    'icon'    => 'ti ti-currency-yen',
    'variant' => 'primary',
    'trend'   => ['value' => '8.2%', 'up' => true, 'text' => '较上周'],
    'progress'=> 72,             // style=progress 时
    'footer'  => null,
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `style` | string | `'icon'` | 附加到根元素的内联样式 |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值） |
| `icon` | mixed | `null` | Tabler 图标 class，如 `ti ti-user` |
| `variant` | string | `'primary'` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `trend` | mixed | `null` | 趋势值（正/负） |
| `progress` | mixed | `null` | 进度百分比（0-100） |
| `footer` | mixed | `null` | 底部内容（原样输出） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `metricCard`

迷你指标卡（数字+图标+同比）。

> **类**：`zxf\XfAdmin\Components\Data\MetricCard`
> **文件**：`src/Components/Data/MetricCard.php`（98 行）
> **依赖插件**：`echarts`

**用法示例**

```php
XfAdmin::metricCard([
    'title'    => '总收入',                 // 指标名称
    'value'    => 368425,                  // 指标数值（数字时自动滚动计数）
    'prefix'   => '¥',                     // 数值前缀（货币符号等）
    'suffix'   => '',                      // 数值后缀（%、单 等）
    'decimals' => 0,                       // 计数动画保留小数位
    'trend'    => 12.5,                    // 变化率（正数=上升绿色，负数=下降红色；null 不显示）
    'trend_text' => '较上周',               // 变化率说明文字
    'chart'    => 'donut',                 // 迷你图类型：donut|pie|bar|area|line|null
    'data'     => [40, 68, 52, 80, 63],    // 迷你图数据（donut/pie 为各扇区值，其余为序列值）
    'labels'   => [],                      // donut/pie 扇区名称（可选）
    'color'    => '#3e60d5',               // 迷你图主色
    'icon'     => 'ti ti-coin',            // 左上角图标（无 chart 时展示更醒目）
    'footer'   => null,                    // 底部附加 HTML（如链接）
    'url'      => null,                    // 整卡跳转链接
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `value` | int | `0` | 当前值（表单控件值 / 展示数值） |
| `prefix` | string | `''` |  |
| `suffix` | string | `''` |  |
| `decimals` | int | `0` |  |
| `trend` | mixed | `null` | 趋势值（正/负） |
| `trend_text` | string | `'较上周'` |  |
| `chart` | string | `'donut'` |  |
| `data` | array | `[]` | 数据数组（行数据 / 图表数据） |
| `labels` | array | `[]` |  |
| `color` | string | `'#3e60d5'` | 颜色值（#hex / rgb() / 具名色） |
| `icon` | mixed | `null` | Tabler 图标 class，如 `ti ti-user` |
| `footer` | mixed | `null` | 底部内容（原样输出） |
| `url` | mixed | `null` | 链接地址（自动做安全协议校验） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `statMiniSparkline`

迷你指标+火花线（数字+趋势迷你图）。

> **类**：`zxf\XfAdmin\Components\Data\StatMiniSparkline`
> **文件**：`src/Components/Data/StatMiniSparkline.php`（96 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::statMiniSparkline([
    'label' => '月活跃用户',
    'value' => '12,480',
    'delta' => 12.5,          // 正涨 / 负跌
    'series' => [10, 14, 12, 18, 16, 22, 20, 26],
    'variant' => 'primary',
    'icon' => 'ti ti-users',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `label` | string | `''` | 标签文案（表单字段标签 / 按钮文案） |
| `value` | string | `''` | 当前值（表单控件值 / 展示数值） |
| `delta` | mixed | `null` |  |
| `series` | array | `[]` |  |
| `variant` | string | `'primary'` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `icon` | mixed | `null` | Tabler 图标 class，如 `ti ti-user` |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `widgetsDashboard`

小部件仪表盘（多 widget 汇总）。

> **类**：`zxf\XfAdmin\Components\Data\WidgetsDashboard`
> **文件**：`src/Components/Data/WidgetsDashboard.php`（250 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::widgetsDashboard([
    'widgets' => ['stats', 'charts', 'messages', 'activity', 'tasks', 'calendar'],
    'currency' => '¥',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `widgets` | array | `['stats', 'charts', 'messages', 'activity', 'tasks']` | 数组结构（见组件用法示例） |
| `currency` | string | `'¥'` | 货币符号 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `analyticsDashboard`

数据分析仪表盘（多图表汇总）。

> **类**：`zxf\XfAdmin\Components\Data\AnalyticsDashboard`
> **文件**：`src/Components/Data/AnalyticsDashboard.php`（98 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::analyticsDashboard([
    'stats' => [
        ['title' => 'Today\'s Sales', 'value' => '$8,852', 'icon' => 'ti-currency-dollar', 'color' => 'primary', 'change' => 20],
        ['title' => 'Visitors', 'value' => '8,549', 'icon' => 'ti-users', 'color' => 'success', 'change' => -2],
        ['title' => 'Conversion', 'value' => '4.48%', 'icon' => 'ti-trending-up', 'color' => 'warning', 'change' => 8],
        ['title' => 'Bounce Rate', 'value' => '42.2%', 'icon' => 'ti-activity', 'color' => 'danger', 'change' => -5],
    ],
    'recentActivity' => [
        ['user' => 'John D.', 'action' => 'completed purchase', 'target' => '#ORD-5001', 'time' => '2 min ago', 'icon' => 'ti-shopping-cart', 'color' => 'primary'],
        ...
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stats` | array | `[]` |  |
| `recentActivity` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `dashboardGrid`

仪表盘网格（可拖拽 widget 布局）。

> **类**：`zxf\XfAdmin\Components\Data\DashboardGrid`
> **文件**：`src/Components/Data/DashboardGrid.php`（85 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::dashboardGrid([
    'stats' => [
        ['title' => '今日销售额', 'value' => '¥8,852', 'icon' => 'ti-currency-dollar', 'variant' => 'primary', 'trend' => 20],
        ...
    ],
    'charts' => [
        ['title' => '运营趋势', 'width' => 8, 'body' => XfAdmin::apexChart([...])],
        ['title' => '用户构成', 'width' => 4, 'body' => XfAdmin::apexChart([...])],
    ],
    'bottom' => XfAdmin::tabs([...]),   // 底部标签页（可空）
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stats` | array | `[]` |  |
| `charts` | array | `[]` |  |
| `bottom` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `settingsCenter`

设置中心（分组设置入口）。

> **类**：`zxf\XfAdmin\Components\Data\SettingsCenter`
> **文件**：`src/Components/Data/SettingsCenter.php`（57 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::settingsCenter([
    'title'  => '系统设置',
    'groups' => [
        [
            'id'     => 'general',                      // 锚点 id
            'icon'   => 'ti-settings',                 // 左侧图标
            'label'  => '常规',
            'title'  => '常规设置',                    // 右侧面板标题
            'desc'   => '站点基础信息',
            'body'   => XfAdmin::form([...]),          // 右侧内容（组件/HTML）
        ],
        ...
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'设置'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `groups` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `reportPage`

报表页（数据报表展示）。

> **类**：`zxf\XfAdmin\Components\Data\ReportPage`
> **文件**：`src/Components/Data/ReportPage.php`（55 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::reportPage([
    'title'    => '销售报表',
    'filters'  => XfAdmin::form([...]),                 // 筛选栏表单
    'charts'   => [
        ['title' => '趋势', 'width' => 12, 'body' => XfAdmin::apexChart([...])],
        ['title' => '构成', 'width' => 6,  'body' => XfAdmin::apexChart([...])],
        ['title' => '排行', 'width' => 6,  'body' => XfAdmin::apexChart([...])],
    ],
    'table'    => XfAdmin::dataTable([...]),            // 底部明细表
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'报表'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `filters` | string | `''` | 过滤条件定义 |
| `charts` | array | `[]` |  |
| `table` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `moduleNav`

模块导航（企业级模块菜单）。

> **类**：`zxf\XfAdmin\Components\Data\ModuleNav`
> **文件**：`src/Components/Data/ModuleNav.php`（65 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::moduleNav([
    'items'   => [
        ['label' => '概览', 'url' => '/admin/app/crm', 'active' => true],
        ['label' => '线索', 'url' => '/admin/app/crm/leads'],
    ],
    'type'    => 'pills',          // pills(默认) | tabs | underline
    'align'   => 'start',          // start(默认) | center | end
    'class'   => 'xf-module-subnav',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `sections` | array | `[]` | 分区数组 |
| `type` | string | `'pills'` | pills \| tabs \| underline |
| `align` | string | `'start'` | start \| center \| end |
| `class` | string | `'xf-module-subnav'` | 附加到根元素的自定义 class |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `moduleGrid`

模块网格（功能模块卡片墙）。

> **类**：`zxf\XfAdmin\Components\Data\ModuleGrid`
> **文件**：`src/Components/Data/ModuleGrid.php`（67 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::moduleGrid([
    'sections' => [
        '内容与创作' => [
            ['name' => '博客', 'desc' => '...', 'icon' => 'ti ti-article', 'url' => '/admin/app/blog'],
            ...
        ],
        '营销与客户' => [ ... ],
    ],
    'columns'  => 3,   // 每分区每行卡片数（手机 1 / 平板 2 / 桌面 columns）
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `sections` | array | `[]` | 分区数组 |
| `columns` | int | `4` | 桌面列数 |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `subtitle` | string | `''` | 副标题文本 |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `apiKeys`

API 密钥管理（密钥列表+创建/撤销）。

> **类**：`zxf\XfAdmin\Components\Data\ApiKeys`
> **文件**：`src/Components/Data/ApiKeys.php`（95 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::apiKeys([
    'items' => [
        ['name' => '生产环境', 'key' => 'sk-live-xxxx', 'created' => '2026-01-01', 'last_used' => '2天前'],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `reveal` | bool | `true` |  |
| `regenerate` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `appManage`

应用管理列表（apps-manage）。

> **类**：`zxf\XfAdmin\Components\Data\AppManage`
> **文件**：`src/Components/Data/AppManage.php`（198 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::appManage([
    'stats' => [
        ['title' => 'Total Apps', 'value' => '128', 'icon' => 'ti-apps', 'color' => 'primary', 'change' => '+12%'],
        ['title' => 'Active Users', 'value' => '8,549', 'icon' => 'ti-users', 'color' => 'success', 'change' => '+5.3%'],
        ['title' => 'Revenue', 'value' => '$24.8k', 'icon' => 'ti-chart-bar', 'color' => 'warning', 'change' => '+18.2%'],
        ['title' => 'Downtime', 'value' => '0.02%', 'icon' => 'ti-activity', 'color' => 'danger', 'change' => '-0.3%'],
    ],
    'apps' => [
        ['name' => 'App Name', 'icon' => 'ti-brand-slack', 'color' => 'primary', 'status' => 'active', 'description' => 'An amazing application for your daily needs.', 'users' => 1250, 'rating' => 4.8],
        // ...
    ],
    'maxApps' => 10,
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stats` | array | `[]` |  |
| `apps` | array | `[]` |  |
| `maxApps` | int | `10` |  |
| `search` | string | `''` | 是否启用搜索 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `importExport`

批量导入/导出工具条（DataTable / 管理页通用） 对标后台高频「导出 CSV/Excel/JSON + 导入文件」操作区，自我包含（Bootstrap 下拉 + 模态框）， 不依赖额外前端插件。导出项为真链接（指向后端导出端点），导入为带文件域的模态表单 （action 指向后端导入端点，CSRF 由宿主通过 csrf 选项注入）。 'exports' => [ ['label' => 'CSV',  'format' => 'csv',  'url' => '/admin/users/export?fmt=csv'], ['label' => 'Excel','format' => 'xlsx', 'url' => '/admin/users/export?fmt=xlsx'], ['label' => 'JSON', 'format' => 'json', 'url' => '/admin/users/export?fmt=json'], ], // 简写：export_url + formats 自动生成上述 exports // 'export_url' => '/admin/users/export?fmt=', 'formats' => ['csv','xlsx','json'], 'import' => [ 'url'    => '/admin/users/import', 'accept' => '.csv,.xlsx', 'title'  => '导入用户', ], 'csrf' => csrf_field(),   // 宿主注入隐藏域（如 Laravel csrf_field()） ])。

> **类**：`zxf\XfAdmin\Components\Data\ImportExport`
> **文件**：`src/Components/Data/ImportExport.php`（118 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::importExport([
    'exports' => [
        ['label' => 'CSV',  'format' => 'csv',  'url' => '/admin/users/export?fmt=csv'],
        ['label' => 'Excel','format' => 'xlsx', 'url' => '/admin/users/export?fmt=xlsx'],
        ['label' => 'JSON', 'format' => 'json', 'url' => '/admin/users/export?fmt=json'],
    ],
    // 简写：export_url + formats 自动生成上述 exports
    // 'export_url' => '/admin/users/export?fmt=', 'formats' => ['csv','xlsx','json'],
    'import' => [
        'url'    => '/admin/users/import',
        'accept' => '.csv,.xlsx',
        'title'  => '导入用户',
    ],
    'csrf' => csrf_field(),   // 宿主注入隐藏域（如 Laravel csrf_field()）
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `exports` | array | `[]` | [['label','format','url']] |
| `export_url` | string | `''` | 简写基址 |
| `formats` | array | `[]` | 简写格式列表（默认空；使用 export_url 简写时必须显式传 formats） |
| `import` | mixed | `null` | null \| ['url','accept','title'] |
| `export_label` | string | `'导出'` |  |
| `import_label` | string | `'导入'` |  |
| `csrf` | string | `''` | 宿主注入的隐藏域 HTML（如 csrf_field()） |
| `class` | string | `''` | 附加到根元素的自定义 class |
| `id` | mixed | `null` | 根元素 id（留空自动生成唯一 id） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。


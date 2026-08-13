# DataTables 服务端协议

> 文档导航：[README](../README.md) · [表格](tables.md) · [组件开发](DEVELOPMENT.md)

`dataTable` 组件在 `serverSide => true` 时，前端 DataTables 会向后端发 `draw` / `start` / `length` / `search` / ordering 等标准参数；后端必须返回 DataTables 约定的 JSON 协议。本文说明 xfadmin 的 PHP 端如何生成该协议，以及配套的**紧凑传输协议**与**单元格渲染协议**。

---

## 一、标准 DataTables 响应

```json
{
  "draw": 2,
  "recordsTotal": 57,
  "recordsFiltered": 12,
  "data": [
    { "id": 1, "name": "Alice", "status": "active" },
    { "id": 2, "name": "Bob",   "status": "banned" }
  ]
}
```

- `draw`：原样回传前端的 `draw`（防 CSRF/乱序）
- `recordsTotal`：未过滤的总行数
- `recordsFiltered`：当前过滤后的行数
- `data`：当前页的行对象数组（键名对应 `columns[].data`）

---

## 二、DataSet（包内统一数据层）

`zxf\XfAdmin\Support\DataSet` 封装了「请求解析 → 搜索 → 过滤 → 排序 → 分页 → 响应」全流程，避免在控制器里手写 SQL。

```php
use zxf\XfAdmin\Support\DataSet;

return DataSet::response(
    DB::table('demo_users'),
    $request->all(),
    [
        'searchable' => ['nickname', 'email', 'phone'],
        'filters'    => [
            'status',
            'keyword'     => ['nickname', 'email'],
            'vips'        => ['field' => 'vip',  'op' => 'in'],
            'score_min'   => ['field' => 'score', 'op' => '>='],
            'reg_from'    => ['field' => 'created_at', 'op' => 'date_from'],
        ],
    ]
);
```

### 过滤规则语义（`op`）

| op | 含义 | 示例 |
|----|------|------|
| `=` | 等值 | `['field'=>'status','op'=>'=']` |
| `in` | IN 集合 | `['field'=>'vip','op'=>'in']` |
| `>=` / `<=` | 区间 | `score_min` / `score_max` |
| `date_from` / `date_to` | 日期起止（自动拼 `00:00:00`/`23:59:59`） | `reg_from` / `reg_to` |

`keyword` 是一组字段的 OR 模糊匹配；`filters` 中未在请求出现的键会被忽略（等效「全部」）。

---

## 三、紧凑协议（xfc / xfo / xfs）

为减小大列表的传输体积，`dataTable` 支持三种压缩参数，由前端 `DataTable.php` 在 `serverSide` 模式下自动协商：

| 参数 | 含义 |
|------|------|
| `xfc` | columns 配置指纹（列定义 hash，后端据此还原列映射，前端可省略重复传列） |
| `xfo` | ordering 的紧凑编码 |
| `xfs` | search 的紧凑编码 |

后端用 `DataSet` 解析这些参数即可，无需控制器关心编码细节。**wsf 演示的 `AdminData::query()` 已使用该协议**。

---

## 四、单元格渲染协议（cellRenderers）

`dataTable` 的 `columns[].render` 可为字符串键（如 `'user'` / `'enum'` / `'money'`），由前端 `xfadmin.js` 的 `XFAdmin.cellRenderers` 处理。常见渲染器：

```
code / user / enum / money / number / progress / percent /
switch / bool / tags / datetime / phone / email / ip / url /
rating / filesize / image / truncate
```

后端只需在 `data` 行中提供原始值，前端按 `render` 键渲染成徽标/头像/进度条等。wsf 的企业模块（`EnterpriseCatalog`）即由字段 DSL 自动派生 `columns` 并套用这些渲染器。

### 自定义渲染器（前端）

```js
XFAdmin.cellRenderers.myRender = function (value, row, col) {
    return '<span class="badge bg-info">' + value + '</span>';
};
```

---

## 五、行内编辑 / 状态开关

- `editable: true` 开启单元格编辑；前端把改动 POST 到 `options.url`（或 `data-url`），带 `id` / `field` / `value`。
- `switch` 类列：点击开关即 POST 当前行 `id` 与字段新值，由 `XFAdmin.bindSwitch` 处理。

后端落库示例（wsf `AdminData::updateField`）：

```php
public static function updateField(string $dataset, int $id, string $field, mixed $value): bool
{
    $table = self::table($dataset);
    return DB::table($table)->where('id', $id)->update([$field => $value]);
}
```

---

## 六、批量操作（bulk）

`dataTable` 的 `bulk` 配置在表格上方渲染 `.xf-dt-bulk` 操作栏（`data-dt` 指向表 id）。按钮属性：

| 属性 | 说明 |
|------|------|
| `data-xf-bulk-action` | 动作标识 |
| `data-url` | 请求地址，`{ids}` 占位被选中 id 替换 |
| `data-method` | POST / PUT / DELETE |
| `data-confirm` | 确认文案（空则不确认） |
| `data-reload` | 操作后是否重载表格（`"1"` / `"0"`，用 `attr()` 读取，勿用 `.data()` 否则 `"0"` 转数字） |

前端 `XFAdmin.bindBulk` 注入行复选框 + 表头全选（含 `indeterminate`）。**陷阱**：批量按钮在表格外，事件回调里必须 `jQuery(this)` 而非 `$el.find(this)`（后者为空集）；`data-reload` 判定要用 `attr()`。

---

## 七、高级特性

| 特性 | 配置 | 说明 |
|------|------|------|
| `row_detail` | `true` | 行明细展开（自动插入明细列；启用时**强制关闭** responsive，二者互斥） |
| `row_group` | `'office'` 或 `['data'=>'office','empty'=>'未分组']` | 本地数据源分组（无 RowGroup 插件，drawCallback 注入 `.xf-dt-group-row`） |
| `state_save` | `true` | 走 DataTables 原生 `stateSave`。**陷阱**：必须在 `$config` 拷入 `$xfConfig['dt']` **之前**赋值 |
| `export` | `true` 或 `['excel','pdf']` | 导出控件；含 `pdf` 时须在 `assets()` 声明内追加 `'datatables-pdf'` |
| `fixed_columns` | `['right'=>1]` | 冻结操作列；配合 `scroll_x` 时需 `initDtDropdownFix`（见下） |

---

## 八、滚动 + 冻结列下的下拉修复

开启 `scroll_x` + `fixed_columns` 时，Bootstrap5 dropdown 的 Popper 定位会被滚动容器与冻结列偏移推到视口外（表现为「更多」按钮不弹出）。

修复逻辑在 `xfadmin.js` 的 `initDtDropdownFix`（IIFE 顶层注册 document 委托）：**必须在 `shown.bs.dropdown` 回调里用 `requestAnimationFrame` 延迟一帧**，把菜单改为 `position:fixed` 并基于按钮 `getBoundingClientRect()` 重定位（含右/下边界翻转）。原因是 Bootstrap5 在 `shown` 之后还有一次 Popper 收尾定位，会覆盖同步写入的 fixed。

---

## 九、wsf 演示对接示例

wsf 的 `AdminData::query()` 已封装好 DataSet 调用；控制器只需：

```php
public function data(Request $request, string $dataset)
{
    if (! method_exists($this, 'canManage') || $this->canManage($dataset)) {
        return response()->json(AdminData::query($dataset, $request->all()));
    }
    return response()->json(['draw'=>1,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]]);
}
```

前端 `dataTable` 组件通过 `url` + CSRF（`X-CSRF-TOKEN` header）自动拉取。**注意**：`/admin/api/data/{dataset}` 受 admin 中间件保护，未登录会被 302 到 `/admin/login`（非代码 bug）。

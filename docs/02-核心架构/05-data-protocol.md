# 服务端数据协议（DataSet）

`zxf\XfAdmin\Support\DataSet` 为表格提供**开箱即用的服务端分页/搜索/过滤/排序**能力，
无需自己解析 DataTables 参数。

> 源码：`src/Support/DataSet.php`（641 行）

## 1. 入口

```php
XfAdmin::dataResponse(iterable|object $rows, array $params = [], array $options = []): array
// 等价 Support\DataSet::response()
```

| 参数 | 说明 |
|---|---|
| `$rows` | 数组 / `Traversable` / Laravel 查询构造器（鸭子类型识别） |
| `$params` | 请求参数，通常 `request()->all()` |
| `$options` | `searchable`（全局搜索字段）、`filters`（过滤映射）、`transform`（行输出闭包） |

返回：

```php
[
    'draw'            => int,   // 请求序号，原样返回
    'recordsTotal'    => int,   // 过滤前总数
    'recordsFiltered' => int,   // 过滤后总数
    'data'            => array, // 当前页数据
]
```

## 2. 数据源识别

```php
is_object($rows) && ! $rows instanceof Traversable
&& method_exists($rows, 'count') && method_exists($rows, 'forPage') && method_exists($rows, 'get')
    → fromBuilder()    // 条件全部下推到数据库
其余
    → fromArray()      // 内存中过滤排序
```

| 数据源 | 行为 |
|---|---|
| Laravel `Builder` / `Eloquent\Builder` | `count()` / `where()` / `orderBy()` / `forPage()->get()` |
| `Collection` / 数组 / `Traversable` | 内存 `array_filter` + `usort` + `array_slice` |

## 3. 请求参数

### 3.1 DataTables 原生参数

| 参数 | 说明 |
|---|---|
| `draw` | 请求序号 |
| `start` | 偏移量 |
| `length` | 每页条数（负或 > 1000 时夹到 1000；`DataSet::$maxLength = 1000`） |
| `search[value]` / `search` | 全局搜索词 |
| `order[i][column]` / `order[i][dir]` | 排序（列索引 + `asc`/`desc`） |
| `columns[i][data]` / `[searchable]` / `[orderable]` / `[search][value]` | 列定义与列搜索 |

### 3.2 压缩协议（包内自定义）

服务端模式下 `xfadmin.js` 会注入 `ajax.data` 钩子，**删除**原生的
`columns`/`order`/`search`，改为三个紧凑参数：

| 参数 | 格式 | 示例 |
|---|---|---|
| `xfc` | `data:searchable:orderable[:search]`，多列用 `\|` 连接，值 `encodeURIComponent` | `id:1:0\|name:1:1:张` |
| `xfo` | `列序号:d`（降序）或 `列序号:a`，多列用 `\|` 连接 | `0:d\|2:a` |
| `xfs` | 全局搜索词 | `abc` |

`DataSet::expandCompact()` 在**原生同名参数为空时**才启用压缩协议还原。

> 目的：避免长 URL 被 WAF / 服务器拒绝；配合 `method => 'POST'` 效果更佳。

### 3.3 保留参数

以下参数不参与自定义过滤：

```php
RESERVED = ['draw','start','length','search','order','columns','_','_token','xfc','xfo','xfs'];
```

## 4. filters 映射语法（`$options['filters']`）

| 写法 | 语义 | 示例 |
|---|---|---|
| `'status'`（数字键） | 参数名 = 字段名，`=` 精确匹配 | `'status'` |
| `'level' => 'level'` | 参数名 => 字段名，`=` | `'kw' => 'name'` |
| `'keyword' => ['name','email']` | 参数名 => 多字段 **LIKE OR** | `'keyword' => ['name','email']` |
| `['field' => 'score', 'op' => '>=']` | 运算符规则 | 见 §5 |
| `['fields' => ['name','email'], 'op' => 'like']` | 多字段 LIKE OR | |
| `['field' => 'vip', 'op' => 'in']` | 逗号分隔 → `whereIn` | |
| `['field' => 'created_at', 'op' => 'date_from']` | 日期下界 | |
| `['field' => 'created_at', 'op' => 'date_to']` | 日期上界（纯 `Y-m-d` 自动补 `23:59:59`） | |
| `['field' => 'balance', 'op' => 'between']` | 值 `"min,max"`，任一端空退化为单边 | |
| `'date_from' => fn($row, $v)`（数组管线） | 闭包自定义 | 返回 `bool` |
| `'date_from' => fn($query, $v)`（Builder 管线） | 闭包自定义 | 修改查询，不返回 |

⚠️ **两种管线的闭包签名不同**：

```php
// 数组管线：fn($row, $value): bool —— 返回是否保留该行
'filters' => ['adult' => fn ($row, $v) => (int) DataSet::field($row, 'age') >= 18];

// Builder 管线：fn($query, $value): void —— 直接修改查询
'filters' => ['adult' => fn ($query, $v) => $query->where('age', '>=', 18)];
```

⚠️ 参数值为空字符串 `''` 一律跳过（兼容 Laravel 的 `ConvertEmptyStringsToNull` 中间件）。

## 5. 运算符（op）全表

| op（含别名） | 数组管线 | Builder 管线 |
|---|---|---|
| `=` / `eq` | 字符串全等 | `where =` |
| `!=` / `neq` | 不等 | `where !=` |
| `>` `>=` `<` `<=` / `gt` `gte` `lt` `lte` | 数值感知比较 | 同名 `where` |
| `in` / `not_in` | 逗号分隔包含判定 | `whereIn` / `whereNotIn` |
| `between` | `"min,max"` | 两段 `where >=` / `<=` |
| `date_from` / `date_to` | 比较 `>=` / `<=`（date_to 补时间） | 同名 |
| `like` | **多字段 OR** 包含（大小写不敏感） | `orWhere like` 包在 `where(closure)` 内 |
| 其它 / 缺省 | `=` | `=` |

- 非 `like` 的 op 只作用于 `fields[0]`；
- 识别规则：定义为数组且含 `op`/`field`/`fields` 之一 → op 规则；
  否则数组 = 多字段 LIKE，字符串 = 单字段 `=`。

## 6. 搜索

### 6.1 全局搜索字段来源（优先级）

1. `$options['searchable']`（推荐显式声明）；
2. 请求中 `searchable` 为真的列；
3. 首行的全部标量字段（兜底）。

```php
'searchable' => ['name', 'email', 'phone'],
```

- 数组管线：`mb_strtolower + str_contains`（大小写不敏感）；
- Builder 管线：`orWhere like` 包在 `where(closure)` 内（避免与其它条件冲突），
  like 值用 `addcslashes($v, '%_\\')` 转义。

### 6.2 列搜索

`columns[i][search][value]` 非空且该列 `data` 非空时生效：

- 数组管线：`str_contains` 匹配；
- Builder 管线：`where like`（⚠️ **不检查 `searchable` 标志**）。

## 7. 排序

- 只保留「列存在 && `orderable` && `data` 非空」的排序项；
- 数组管线：`usort` 多列比较 —— 双方均为数值用 `<=>`，否则 `strnatcasecmp`；
- Builder 管线：`orderBy($field, $dir)` 多列。

⚠️ Builder 管线**直接使用请求传来的列名**，未做字段白名单。
生产环境建议自行校验或限制（防止枚举列名探测）。

## 8. 分页

```php
// Builder 管线
$page = floor($start / $length) + 1;
$query->forPage($page, $length ?: $maxLength)->get();

// 数组管线
array_slice($data, $start, $length > 0 ? $length : null);
```

| `length` | Builder | 数组 |
|---|---|---|
| `> 0` | 取该页数 | 取该条数 |
| `0` / 负数 / `> 1000` | 夹到 1000 | `null` → 返回全部 |

## 9. transform（行输出转换）

```php
'transform' => function ($row) {
    $row['status_text'] = $row['status'] ? '正常' : '禁用';
    return $row;
},
```

⚠️ `transform` 在**分页之后**执行，因此 `recordsTotal` / `recordsFiltered` 不受其影响。
有 `toArray()` 方法的对象会自动转为数组。

## 10. 工具方法

| 方法 | 说明 |
|---|---|
| `DataSet::field($row, 'a.b.c')` | 取值：数组走 `Html::get`（点语法），对象逐段 `->{seg}` |
| `DataSet::scalar($v)` | bool→int，null→''，非标量 → `json_encode`（中文不转义） |
| `DataSet::str($v, $default)` / `int($v, $default)` | 安全转换，非标量返回默认 |

## 11. 完整示例

### Laravel 控制器

```php
use zxf\XfAdmin\XfAdmin;

public function index(Request $request)
{
    return response()->json(XfAdmin::dataResponse(
        User::query()->with('department'),
        $request->all(),
        [
            'searchable' => ['name', 'email', 'phone'],
            'filters'    => [
                'status'    => ['field' => 'status', 'op' => '='],
                'role'      => ['field' => 'role_id', 'op' => 'in'],
                'keyword'   => ['name', 'email'],
                'date_from' => ['field' => 'created_at', 'op' => 'date_from'],
                'date_to'   => ['field' => 'created_at', 'op' => 'date_to'],
                'score_min' => ['field' => 'score', 'op' => '>='],
            ],
            'transform'  => fn ($row) => $row + ['dept' => $row->department?->name],
        ]
    ));
}
```

### 数组数据源

```php
$rows = [
    ['id' => 1, 'name' => '张三', 'dept' => '技术部', 'score' => 88],
    ['id' => 2, 'name' => '李四', 'dept' => '市场部', 'score' => 92],
];

return response()->json(XfAdmin::dataResponse($rows, request()->all(), [
    'searchable' => ['name', 'dept'],
    'filters'    => ['dept' => ['field' => 'dept', 'op' => '=']],
]));
```

### 前端

```php
echo XfAdmin::dataTable([
    'id'          => 'user-table',
    'ajax'        => '/admin/api/users',
    'server_side' => true,
    'method'      => 'POST',
    'columns'     => [...],
    'filter_bar'  => [
        ['name' => 'keyword', 'label' => '关键词', 'type' => 'text'],
        ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => [...]],
        ['name' => 'date', 'label' => '注册时间', 'type' => 'daterange'],
        // daterange 产出 date_from / date_to，与后端 filters 键名对应
    ],
]);
```

⚠️ **前端过滤控件名与后端 `filters` 键必须配对**：

| 前端 `type` | 产出的参数名 | 后端建议 op |
|---|---|---|
| `text` / `search` / `select` / `radio` / `year` / `color` | `name` | `=` 或 `like` |
| `number` | `name` | `=`、`>=`、`<=` |
| `range` | `name_min` / `name_max` | 两条 `>=` / `<=` |
| `date` / `datetime` / `time` / `month` / `week` | `name` | `=`、`date_from` |
| `daterange` / `datetimerange` / `timerange` | `name_from` / `name_to` | `date_from` / `date_to` |
| `slider` / `range-slider` | `name` = `"lo,hi"` | `between` |
| `checkboxes` / `tree` / `multiple` select | `name` = 逗号串 | `in` |

## 12. 响应兼容

前端 `dataSrc` 智能兼容层接受以下结构（便于对接第三方返回）：

```php
['data' => [...]]     // 标准
['list' => [...]]     // 兼容
['rows' => [...]]     // 兼容
[...]                 // 纯数组
```

## 13. 陷阱清单

1. `length` 被夹到 `[0, 1000]`，`DataSet::$maxLength` 可调；
2. Builder 管线的 `orderBy` 直接用请求列名，**未做白名单**；
3. Builder 管线的列搜索不检查 `searchable`；
4. 两种管线的闭包签名不同（`fn($row,$v):bool` vs `fn($query,$v):void`）；
5. 数组管线中 `between` / `in` 的逗号分隔值按 `trim` 后严格字符串比较，类型不一致会失配；
6. `transform` 在分页后执行，不影响计数；
7. 前端 `row_group` 对服务端模式无效（服务端已分页，分组只能插在当前页）；
8. `xfc/xfo/xfs` 仅在原生同名参数为空时生效。

下一步：[06-门面与助手](06-helpers.md)

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support;

use Closure;
use Traversable;

/**
 * DataTables 服务端数据处理器（框架无关）
 *
 * 使用方只需把「数据 + 请求参数」交给本类，即可获得符合 DataTables
 * serverSide 协议（draw / recordsTotal / recordsFiltered / data）的响应数组，
 * 全局搜索、列搜索、自定义过滤、排序、分页全部由包内完成。
 *
 * 数组数据：
 *   return DataSet::response($rows, $_GET, [
 *       'searchable' => ['name', 'email', 'ip'],          // 全局搜索字段（缺省 = 首行全部标量字段）
 *       'filters'    => [
 *           'status',                                      // 请求参数 status=xx 精确匹配同名字段
 *           'level'   => 'level',                          // 参数名 => 字段名 精确匹配
 *           'keyword' => ['name', 'email'],                // 参数名 => 多字段模糊匹配
 *           'date_from' => fn ($row, $v) => $row['created_at'] >= $v,   // 闭包自定义
 *       ],
 *       'transform'  => fn ($row) => $row + ['extra' => '...'],          // 行输出转换
 *   ]);
 *
 * 查询构造器（Laravel Query/Eloquent Builder，鸭子类型自动识别）：
 *   return DataSet::response(User::query(), request()->all(), [...]);
 *
 * 请求参数解析对「空字符串被转为 null」（Laravel ConvertEmptyStringsToNull）、
 * 参数为数组/标量混杂等情况全部做了健壮兜底，避免任何 500。
 */
class DataSet
{
    /** DataTables 协议保留参数，不参与自定义过滤 */
    protected const RESERVED = ['draw', 'start', 'length', 'search', 'order', 'columns', '_', '_token'];

    /** 单页最大条数（防御 length=-1 / 超大值拖垮服务） */
    public static int $maxLength = 1000;

    // ------------------------------------------------------------------
    // 入口
    // ------------------------------------------------------------------

    /**
     * 生成 DataTables serverSide 协议响应数组
     *
     * @param  iterable|object  $rows     数组数据 / Traversable / Laravel 查询构造器
     * @param  array            $params   请求参数（如 $_GET、request()->all()）
     * @param  array            $options  searchable | filters | transform | columns_map
     */
    public static function response(iterable|object $rows, array $params = [], array $options = []): array
    {
        $req = self::parseRequest($params);

        // Laravel Query/Eloquent Builder（鸭子类型：有 count + forPage + get）
        if (is_object($rows) && ! $rows instanceof Traversable
            && method_exists($rows, 'count') && method_exists($rows, 'forPage') && method_exists($rows, 'get')) {
            return self::fromBuilder($rows, $req, $params, $options);
        }

        $data = is_array($rows) ? array_values($rows) : iterator_to_array($rows, false);

        return self::fromArray($data, $req, $params, $options);
    }

    // ------------------------------------------------------------------
    // 请求解析（全面容错）
    // ------------------------------------------------------------------

    /** 安全取标量字符串（null / 数组 / 标量 全兼容） */
    public static function str(mixed $value, string $default = ''): string
    {
        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return $default;
    }

    /** 安全取整数 */
    public static function int(mixed $value, int $default = 0): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    /** 解析 DataTables 请求参数为规范结构 */
    public static function parseRequest(array $params): array
    {
        $length = self::int($params['length'] ?? null, 10);
        if ($length < 0 || $length > self::$maxLength) {
            $length = self::$maxLength;
        }

        // 全局搜索：search[value]（可能为 null / 字符串 / 数组）
        $search = $params['search'] ?? null;
        $searchValue = is_array($search) ? self::str($search['value'] ?? null) : self::str($search);

        // 列定义与列搜索
        $columns = [];
        foreach ((array) ($params['columns'] ?? []) as $i => $col) {
            if (! is_array($col)) {
                continue;
            }
            $colSearch = $col['search'] ?? null;
            $columns[(int) $i] = [
                'data'       => self::str($col['data'] ?? null),
                'searchable' => self::str($col['searchable'] ?? 'true') !== 'false',
                'orderable'  => self::str($col['orderable'] ?? 'true') !== 'false',
                'search'     => is_array($colSearch) ? self::str($colSearch['value'] ?? null) : self::str($colSearch),
            ];
        }

        // 排序
        $order = [];
        foreach ((array) ($params['order'] ?? []) as $o) {
            if (! is_array($o)) {
                continue;
            }
            $idx = self::int($o['column'] ?? null, -1);
            if ($idx >= 0 && isset($columns[$idx]) && $columns[$idx]['orderable'] && $columns[$idx]['data'] !== '') {
                $order[] = [
                    'field' => $columns[$idx]['data'],
                    'dir'   => strtolower(self::str($o['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
                ];
            }
        }

        return [
            'draw'    => self::int($params['draw'] ?? null, 1),
            'start'   => max(0, self::int($params['start'] ?? null, 0)),
            'length'  => $length,
            'search'  => $searchValue,
            'columns' => $columns,
            'order'   => $order,
        ];
    }

    // ------------------------------------------------------------------
    // 数组数据管线
    // ------------------------------------------------------------------

    protected static function fromArray(array $data, array $req, array $params, array $options): array
    {
        $total = count($data);

        // 1. 自定义过滤
        $data = self::applyFilters($data, $params, $options);

        // 2. 全局搜索
        $searchable = self::searchableFields($data, $req, $options);
        if ($req['search'] !== '' && $searchable !== []) {
            $kw   = mb_strtolower($req['search']);
            $data = array_values(array_filter($data, function ($row) use ($kw, $searchable) {
                foreach ($searchable as $field) {
                    if (self::contains(self::field($row, $field), $kw)) {
                        return true;
                    }
                }

                return false;
            }));
        }

        // 3. 列搜索
        foreach ($req['columns'] as $col) {
            if ($col['search'] !== '' && $col['data'] !== '') {
                $kw   = mb_strtolower($col['search']);
                $data = array_values(array_filter($data, fn ($row) => self::contains(self::field($row, $col['data']), $kw)));
            }
        }

        $filtered = count($data);

        // 4. 排序（支持多列）
        if ($req['order'] !== []) {
            usort($data, function ($a, $b) use ($req) {
                foreach ($req['order'] as $o) {
                    $va = self::field($a, $o['field']);
                    $vb = self::field($b, $o['field']);
                    $cmp = (is_numeric($va) && is_numeric($vb)) ? ($va <=> $vb) : strnatcasecmp((string) self::scalar($va), (string) self::scalar($vb));
                    if ($cmp !== 0) {
                        return $o['dir'] === 'desc' ? -$cmp : $cmp;
                    }
                }

                return 0;
            });
        }

        // 5. 分页
        $page = array_slice($data, $req['start'], $req['length'] > 0 ? $req['length'] : null);

        // 6. 行转换
        $page = self::transformRows($page, $options);

        return [
            'draw'            => $req['draw'],
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $page,
        ];
    }

    /** 应用 filters 选项定义的自定义过滤 */
    protected static function applyFilters(array $data, array $params, array $options): array
    {
        foreach ((array) ($options['filters'] ?? []) as $key => $def) {
            // ['status', ...] 形式：参数名与字段名一致，精确匹配
            if (is_int($key)) {
                $key = (string) $def;
                $def = $key;
            }
            if (in_array($key, self::RESERVED, true)) {
                continue;
            }
            $value = self::str($params[$key] ?? null);
            if ($value === '') {
                continue;
            }

            if ($def instanceof Closure) {
                $data = array_values(array_filter($data, fn ($row) => (bool) $def($row, $value)));
            } elseif (is_array($def)) {
                // 多字段模糊匹配
                $kw   = mb_strtolower($value);
                $data = array_values(array_filter($data, function ($row) use ($def, $kw) {
                    foreach ($def as $field) {
                        if (self::contains(self::field($row, (string) $field), $kw)) {
                            return true;
                        }
                    }

                    return false;
                }));
            } else {
                // 单字段精确匹配
                $field = (string) $def;
                $data  = array_values(array_filter($data, fn ($row) => (string) self::scalar(self::field($row, $field)) === $value));
            }
        }

        return $data;
    }

    /** 计算全局搜索字段：options.searchable > 请求列 searchable > 首行标量字段 */
    protected static function searchableFields(array $data, array $req, array $options): array
    {
        if (! empty($options['searchable'])) {
            return array_values((array) $options['searchable']);
        }
        $fields = [];
        foreach ($req['columns'] as $col) {
            if ($col['searchable'] && $col['data'] !== '') {
                $fields[] = $col['data'];
            }
        }
        if ($fields !== []) {
            return $fields;
        }
        $first = $data[0] ?? null;
        if (is_array($first)) {
            foreach ($first as $k => $v) {
                if (is_scalar($v) || $v === null) {
                    $fields[] = (string) $k;
                }
            }
        }

        return $fields;
    }

    /** 行输出转换 */
    protected static function transformRows(array $rows, array $options): array
    {
        $transform = $options['transform'] ?? null;
        if ($transform instanceof Closure) {
            $rows = array_map($transform, $rows);
        }

        return array_values(array_map(
            fn ($row) => is_object($row) && method_exists($row, 'toArray') ? $row->toArray() : $row,
            $rows
        ));
    }

    // ------------------------------------------------------------------
    // Laravel 查询构造器管线（鸭子类型，不强依赖 Laravel）
    // ------------------------------------------------------------------

    protected static function fromBuilder(object $query, array $req, array $params, array $options): array
    {
        $total = (int) (clone $query)->count();

        // 自定义过滤
        foreach ((array) ($options['filters'] ?? []) as $key => $def) {
            if (is_int($key)) {
                $key = (string) $def;
                $def = $key;
            }
            if (in_array($key, self::RESERVED, true)) {
                continue;
            }
            $value = self::str($params[$key] ?? null);
            if ($value === '') {
                continue;
            }
            if ($def instanceof Closure) {
                $def($query, $value);
            } elseif (is_array($def)) {
                $fields = $def;
                $query->where(function ($q) use ($fields, $value) {
                    foreach ($fields as $field) {
                        $q->orWhere((string) $field, 'like', '%' . addcslashes($value, '%_\\') . '%');
                    }
                });
            } else {
                $query->where((string) $def, '=', $value);
            }
        }

        // 全局搜索
        $searchable = (array) ($options['searchable'] ?? []);
        if ($searchable === []) {
            foreach ($req['columns'] as $col) {
                if ($col['searchable'] && $col['data'] !== '') {
                    $searchable[] = $col['data'];
                }
            }
        }
        if ($req['search'] !== '' && $searchable !== []) {
            $kw = '%' . addcslashes($req['search'], '%_\\') . '%';
            $query->where(function ($q) use ($searchable, $kw) {
                foreach ($searchable as $field) {
                    $q->orWhere((string) $field, 'like', $kw);
                }
            });
        }

        // 列搜索
        foreach ($req['columns'] as $col) {
            if ($col['search'] !== '' && $col['data'] !== '') {
                $query->where($col['data'], 'like', '%' . addcslashes($col['search'], '%_\\') . '%');
            }
        }

        $filtered = (int) (clone $query)->count();

        // 排序
        foreach ($req['order'] as $o) {
            $query->orderBy($o['field'], $o['dir']);
        }

        // 分页
        $length = $req['length'] > 0 ? $req['length'] : self::$maxLength;
        $page   = (int) floor($req['start'] / $length) + 1;
        $rows   = $query->forPage($page, $length)->get();
        $rows   = is_array($rows) ? $rows : (method_exists($rows, 'all') ? $rows->all() : iterator_to_array($rows, false));

        return [
            'draw'            => $req['draw'],
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => self::transformRows($rows, $options),
        ];
    }

    // ------------------------------------------------------------------
    // 工具
    // ------------------------------------------------------------------

    /** 取行字段（支持 a.b.c 点语法、数组/对象） */
    public static function field(mixed $row, string $key): mixed
    {
        if (is_array($row)) {
            return Html::get($row, $key);
        }
        if (is_object($row)) {
            foreach (explode('.', $key) as $seg) {
                if (is_array($row)) {
                    $row = $row[$seg] ?? null;
                } elseif (is_object($row)) {
                    $row = $row->{$seg} ?? null;
                } else {
                    return null;
                }
            }

            return $row;
        }

        return null;
    }

    protected static function scalar(mixed $value): string|int|float
    {
        if (is_scalar($value)) {
            return is_bool($value) ? (int) $value : $value;
        }

        return $value === null ? '' : (string) json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    protected static function contains(mixed $value, string $lowerKeyword): bool
    {
        $str = mb_strtolower((string) self::scalar($value));

        return $str !== '' && str_contains($str, $lowerKeyword);
    }
}

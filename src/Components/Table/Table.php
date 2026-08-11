<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use Closure;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 静态表格（多种渲染风格）
 *
 * XfAdmin::table([
 *     'columns' => [
 *         'name' => '姓名',
 *         'age'  => ['label' => '年龄', 'class' => 'text-center'],
 *         'op'   => ['label' => '操作', 'format' => fn ($row) => '<a href="/edit/' . $row['id'] . '">编辑</a>', 'raw' => true],
 *     ],
 *     'data'    => [['id'=>1,'name'=>'张三','age'=>20], ...],
 *     'striped' => true, 'hover' => true, 'bordered' => false, 'sm' => false,
 *     'variant' => null,          // dark|light|primary...  => table-*
 *     'responsive' => true,
 *     'caption' => null,
 *     'empty'   => '暂无数据',
 * ])
 */
class Table extends Component
{
    protected function defaults(): array
    {
        return [
            'columns'      => [],
            'data'         => [],
            'striped'      => false,
            'striped_cols' => false,
            'hover'        => false,
            'bordered'     => false,
            'borderless'   => false,
            'sm'           => false,
            'align_middle' => false,
            'centered'     => false,
            'nowrap'       => false,
            'variant'      => null,
            'head_variant' => null,   // light|dark => table-*
            'responsive'   => true,
            'caption'      => null,
            'empty'        => '暂无数据',
            'row_attrs'    => null,   // fn($row): array
        ];
    }

    /** 规范化列定义（兼容 DataTables 原生风格：title/data/orderable/xfBadges/xfTemplate 别名） */
    protected function columns(): array
    {
        $columns = [];
        foreach ((array) $this->get('columns', []) as $key => $col) {
            if (is_int($key)) {
                $col = is_array($col) ? $col : ['key' => $col, 'label' => $col];
                $key = $col['key'] ?? $col['data'] ?? '';
            } elseif (! is_array($col)) {
                $col = ['label' => $col];
            }
            // DataTables 原生风格别名
            if (isset($col['data']) && ! isset($col['key'])) {
                $col['key'] = (string) $col['data'];
            }
            if (isset($col['title']) && ! isset($col['label'])) {
                $col['label'] = $col['title'];
            }
            if (isset($col['orderable']) && ! isset($col['sortable'])) {
                $col['sortable'] = (bool) $col['orderable'];
            }
            if (isset($col['xfBadges']) && ! isset($col['badges'])) {
                $col['badges'] = $col['xfBadges'];
            }
            if (isset($col['xfTemplate']) && ! isset($col['template'])) {
                $col['template'] = $col['xfTemplate'];
            }
            $col['key']   ??= is_string($key) ? $key : '';
            $col['label'] ??= $col['key'];
            $columns[]      = $col;
        }

        return $columns;
    }

    protected function cell(array $col, mixed $row): string
    {
        $format = $col['format'] ?? null;
        if ($format instanceof Closure) {
            $value = $format($row);

            return ! empty($col['raw']) || ! is_scalar($value) ? $this->raw($value) : $this->e($value);
        }

        $value = is_array($row) ? Html::get($row, (string) $col['key']) : ($row->{$col['key']} ?? null);

        return ! empty($col['raw']) ? $this->raw($value) : $this->e($value);
    }

    protected function tableClass(): string
    {
        return Html::cls('table', [
            'table-striped'         => $this->get('striped'),
            'table-striped-columns' => $this->get('striped_cols'),
            'table-hover'           => $this->get('hover'),
            'table-bordered'        => $this->get('bordered'),
            'table-borderless'      => $this->get('borderless'),
            'table-sm'              => $this->get('sm'),
            'align-middle'          => $this->get('align_middle'),
            'table-centered'        => $this->get('centered'),
            'table-nowrap'          => $this->get('nowrap'),
        ], $this->get('variant') ? 'table-' . $this->enum($this->get('variant'), ['light', 'dark', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'striped', 'striped-dark'], 'light') : '');
    }

    protected function html(): string
    {
        $columns = $this->columns();
        $data    = (array) $this->get('data', []);

        $html = '<table' . $this->attrs(['class' => $this->tableClass()]) . '>';
        if ($this->get('caption')) {
            $html .= '<caption>' . $this->e($this->get('caption')) . '</caption>';
        }

        $html .= '<thead' . ($this->get('head_variant') ? ' class="table-' . $this->enum($this->get('head_variant'), ['light', 'dark', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'striped', 'striped-dark'], 'light') . '"' : '') . '><tr>';
        foreach ($columns as $col) {
            $attrs = ['class' => $col['class'] ?? null];
            if (isset($col['width'])) {
                $attrs['style'] = 'width:' . $col['width'] . ';';
            }
            $html .= '<th' . Html::attrs($attrs) . '>' . $this->e($col['label']) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if ($data === []) {
            $html .= '<tr><td colspan="' . count($columns) . '" class="text-center text-muted py-4">' . $this->e($this->get('empty')) . '</td></tr>';
        }

        $rowAttrs = $this->get('row_attrs');
        foreach ($data as $row) {
            $attrs = $rowAttrs instanceof Closure ? (array) $rowAttrs($row) : [];
            $html .= '<tr' . Html::attrs($attrs) . '>';
            foreach ($columns as $col) {
                $html .= '<td' . Html::attrs(['class' => $col['cell_class'] ?? null]) . '>' . $this->cell($col, $row) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if ($this->get('responsive')) {
            $html = '<div class="table-responsive">' . $html . '</div>';
        }

        return $html;
    }
}

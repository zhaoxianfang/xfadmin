<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use zxf\XfAdmin\Components\Component;

/**
 * 自定义表格（tables-custom.html）
 *
 * XfAdmin::tablesCustom([
 *     'columns' => ['#', 'Name', 'Email', 'Role', 'Status'],
 *     'rows' => [
 *         ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'role' => 'Admin', 'status' => 'active'],
 *         ...
 *     ],
 *     'headerBg' => 'primary',
 *     'striped' => true,
 *     'hover' => true,
 *     'bordered' => true,
 *     'compact' => false,
 *     'footable' => [],
 * ])
 */
class TablesCustom extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'columns' => [],
            'rows' => [],
            'headerBg' => '',
            'striped' => true,
            'hover' => true,
            'bordered' => true,
            'compact' => false,
            'footable' => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $columns = (array) $this->get('columns', []);
        $rows = (array) $this->get('rows', []);
        // headerBg 白名单：非法值不输出 table-* 类（与其它组件的枚举选项一致）
        $headerBg = $this->enum($this->get('headerBg'), ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'], '');
        $striped = (bool) $this->get('striped', true);
        $hover = (bool) $this->get('hover', true);
        $bordered = (bool) $this->get('bordered', true);
        $compact = (bool) $this->get('compact', false);
        $footable = (array) $this->get('footable', []);

        $tableClass = 'table';
        if ($striped) {
            $tableClass .= ' table-striped';
        }
        if ($hover) {
            $tableClass .= ' table-hover';
        }
        if ($bordered) {
            $tableClass .= ' table-bordered';
        }
        if ($compact) {
            $tableClass .= ' table-sm';
        }
        $tableClass .= ' mb-0';

        $html = '<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="'.$tableClass.'">';

        // 表头
        if (! empty($columns)) {
            $html .= '<thead>';
            if ($headerBg) {
                $html .= '<tr class="table-'.$this->e($headerBg).'">';
            } else {
                $html .= '<tr>';
            }
            foreach ($columns as $col) {
                if (is_array($col)) {
                    $html .= '<th class="'.$this->e($col['class'] ?? '').'">'.$this->e($col['label'] ?? '').'</th>';
                } else {
                    $html .= '<th>'.$this->e((string) $col).'</th>';
                }
            }
            $html .= '</tr></thead>';
        }
        // 表体：按 columns 定义逐列取格（与表头一一对应）。
        // 旧写法 foreach ($row as $key => $value) 会输出行内全部字段，行字段顺序/数量
        // 与 columns 不一致时单元格错位；缺字段一律输出空串。
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $row = (array) $row;
            $rowValues = array_values($row);
            $html .= '<tr>';
            foreach ($columns as $i => $col) {
                if (is_array($col)) {
                    // 列显式声明字段名（key/data/field）时按键取格，否则按位置顺序取格
                    $fieldKey = $col['key'] ?? $col['data'] ?? $col['field'] ?? null;
                    $value = $fieldKey !== null ? ($row[$fieldKey] ?? '') : ($rowValues[$i] ?? '');
                } else {
                    $value = $rowValues[$i] ?? '';
                }
                if (is_array($value)) {
                    $html .= '<td class="'.$this->e($value['class'] ?? '').'">'.$this->raw($value['html'] ?? '').'</td>';
                } else {
                    $html .= '<td>'.$this->e((string) $value).'</td>';
                }
            }
            $html .= '</tr>';
        }
        if (empty($rows)) {
            $colspan = count($columns) ?: 1;
            $html .= '<tr><td colspan="'.$colspan.'" class="text-center text-muted py-4">暂无数据</td></tr>';
        }
        $html .= '</tbody>';

        // 表尾
        if (! empty($footable)) {
            $html .= '<tfoot>';
            foreach ($footable as $row) {
                $row = (array) $row;
                $html .= '<tr>';
                foreach ($row as $value) {
                    if (is_array($value)) {
                        $html .= '<td class="'.$this->e($value['class'] ?? '').'">'.$this->raw($value['html'] ?? '').'</td>';
                    } else {
                        $html .= '<td>'.$this->e((string) $value).'</td>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</tfoot>';
        }
        $html .= '</table></div></div></div>';

        return $html;
    }
}

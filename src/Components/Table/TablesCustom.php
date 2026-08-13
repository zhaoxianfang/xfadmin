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

    protected function html(): string
    {
        $columns = (array) $this->get('columns', []);
        $rows = (array) $this->get('rows', []);
        $headerBg = (string) $this->get('headerBg', '');
        $striped = (bool) $this->get('striped', true);
        $hover = (bool) $this->get('hover', true);
        $bordered = (bool) $this->get('bordered', true);
        $compact = (bool) $this->get('compact', false);
        $footable = (array) $this->get('footable', []);

        $tableClass = 'table';
        if ($striped) $tableClass .= ' table-striped';
        if ($hover) $tableClass .= ' table-hover';
        if ($bordered) $tableClass .= ' table-bordered';
        if ($compact) $tableClass .= ' table-sm';
        $tableClass .= ' mb-0';

        $html = '<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="' . $tableClass . '">';

        // 表头
        if (!empty($columns)) {
            $html .= '<thead>';
            if ($headerBg) {
                $html .= '<tr class="table-' . $this->e($headerBg) . '">';
            } else {
                $html .= '<tr>';
            }
            foreach ($columns as $col) {
                if (is_array($col)) {
                    $html .= '<th class="' . $this->e($col['class'] ?? '') . '">' . $this->e($col['label'] ?? '') . '</th>';
                } else {
                    $html .= '<th>' . $this->e((string) $col) . '</th>';
                }
            }
            $html .= '</tr></thead>';
        }

        // 表体
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $row = (array) $row;
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $html .= '<td class="' . $this->e($value['class'] ?? '') . '">' . $this->raw($value['html'] ?? '') . '</td>';
                } else {
                    $html .= '<td>' . $this->e((string) $value) . '</td>';
                }
            }
            $html .= '</tr>';
        }

        if (empty($rows)) {
            $colspan = count($columns) ?: 1;
            $html .= '<tr><td colspan="' . $colspan . '" class="text-center text-muted py-4">暂无数据</td></tr>';
        }

        $html .= '</tbody>';

        // 表尾
        if (!empty($footable)) {
            $html .= '<tfoot>';
            foreach ($footable as $row) {
                $row = (array) $row;
                $html .= '<tr>';
                foreach ($row as $value) {
                    if (is_array($value)) {
                        $html .= '<td class="' . $this->e($value['class'] ?? '') . '">' . $this->raw($value['html'] ?? '') . '</td>';
                    } else {
                        $html .= '<td>' . $this->e((string) $value) . '</td>';
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

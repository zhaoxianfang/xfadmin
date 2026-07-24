<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use zxf\XfAdmin\Support\Html;

/**
 * 全功能数据表格（基于 DataTables，前端由 xfadmin.js 自动初始化）
 *
 * 支持：搜索、排序、分页、多选、导出（Excel/CSV/打印/PDF）、固定表头、
 * 响应式折叠、列筛选、AJAX / 服务端模式、自定义列渲染模板等
 *
 * XfAdmin::dataTable([
 *     'columns' => [
 *         'id'     => ['label' => 'ID', 'sortable' => true],
 *         'name'   => '姓名',
 *         'status' => ['label' => '状态', 'badges' => ['启用' => 'success', '禁用' => 'danger']],
 *         'op'     => ['label' => '操作', 'template' => '<a href="/user/{id}/edit" class="btn btn-sm btn-soft-primary">编辑</a>', 'sortable' => false],
 *     ],
 *     'data'       => [...],                 // 本地数据；或使用 ajax
 *     'ajax'       => '/api/users',          // 远程数据（返回 {data: [...]}）
 *     'server_side'=> false,                 // 服务端分页/排序/搜索
 *     'searching'  => true,
 *     'ordering'   => true,
 *     'paging'     => true,
 *     'page_length'=> 10,
 *     'buttons'    => ['copy', 'csv', 'excel', 'print'],   // 导出按钮（excel/pdf 自动附加依赖）
 *     'select'     => true | 'multi',        // 行选择
 *     'fixed_header' => true,
 *     'responsive' => true,
 *     'column_filters' => true,              // 每列底部筛选输入框
 *     'options'    => [],                    // 透传 DataTables 原生配置（最高优先级）
 * ])
 */
class DataTable extends Table
{
    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            'ajax'           => null,
            'server_side'    => false,
            'searching'      => true,
            'ordering'       => true,
            'paging'         => true,
            'info'           => true,
            'page_length'    => 10,
            'length_menu'    => [10, 25, 50, 100],
            'buttons'        => [],
            'select'         => false,
            'fixed_header'   => false,
            'responsive'     => false,
            'column_filters' => false,
            'order'          => [],       // [[0, 'asc']]
            'language'       => null,     // DataTables 语言包配置数组
            'options'        => [],
        ]);
    }

    protected function assets(): array
    {
        $assets = ['datatables'];
        foreach ((array) $this->get('buttons', []) as $btn) {
            if ((is_string($btn) ? $btn : ($btn['extend'] ?? '')) === 'pdf') {
                $assets[] = 'datatables-pdf';
            }
        }

        return $assets;
    }

    protected function html(): string
    {
        $id      = $this->resolveId('xf-datatable');
        $columns = $this->columns();

        // ---------- 组装 DataTables 配置 ----------
        $dtColumns = [];
        foreach ($columns as $col) {
            $c = ['data' => $col['key'] !== '' ? $col['key'] : null];
            if (isset($col['sortable'])) {
                $c['orderable'] = (bool) $col['sortable'];
            }
            if (isset($col['searchable'])) {
                $c['searchable'] = (bool) $col['searchable'];
            }
            if (isset($col['visible'])) {
                $c['visible'] = (bool) $col['visible'];
            }
            if (isset($col['width'])) {
                $c['width'] = $col['width'];
            }
            if (isset($col['class'])) {
                $c['className'] = $col['class'];
            }
            // 模板渲染：{field} 占位符（由 xfadmin.js 解析）
            if (isset($col['template'])) {
                $c['xfTemplate'] = $col['template'];
            }
            // 徽章映射：值 => bootstrap 颜色
            if (isset($col['badges'])) {
                $c['xfBadges'] = $col['badges'];
            }
            $dtColumns[] = $c;
        }

        $config = [
            'columns'    => $dtColumns,
            'searching'  => (bool) $this->get('searching'),
            'ordering'   => (bool) $this->get('ordering'),
            'paging'     => (bool) $this->get('paging'),
            'info'       => (bool) $this->get('info'),
            'pageLength' => (int) $this->get('page_length'),
            'lengthMenu' => $this->get('length_menu'),
        ];

        if ($this->get('order') !== []) {
            $config['order'] = $this->get('order');
        }
        if ($this->get('ajax')) {
            $config['ajax'] = $this->get('ajax');
            if ($this->get('server_side')) {
                $config['serverSide'] = true;
                $config['processing'] = true;
            }
        } else {
            $config['data'] = array_values((array) $this->get('data', []));
        }
        if ($this->get('fixed_header')) {
            $config['fixedHeader'] = true;
        }
        if ($this->get('responsive')) {
            $config['responsive'] = true;
        }
        if ($this->get('select')) {
            $config['select'] = $this->get('select') === true ? ['style' => 'multi'] : ['style' => (string) $this->get('select')];
        }

        $buttons = (array) $this->get('buttons', []);
        if ($buttons !== []) {
            $config['buttons'] = array_map(
                fn ($b) => is_string($b) ? ['extend' => $b === 'pdf' ? 'pdfHtml5' : ($b === 'excel' ? 'excelHtml5' : ($b === 'csv' ? 'csvHtml5' : $b)), 'className' => 'btn btn-sm btn-secondary'] : $b,
                $buttons
            );
            $config['layout'] = [
                'topStart' => ['buttons'],
                'topEnd'   => ['search'],
            ];
        }
        if ($this->get('language')) {
            $config['language'] = $this->get('language');
        }

        // 透传原生配置（最高优先级）
        $config = array_replace_recursive($config, (array) $this->get('options', []));

        $xfConfig = [
            'dt'            => $config,
            'columnFilters' => (bool) $this->get('column_filters'),
        ];

        // ---------- HTML ----------
        $html = '<table' . $this->attrs([
            'id'             => $id,
            'class'          => Html::cls($this->tableClass(), 'w-100'),
            'data-xf'        => 'datatable',
            'data-xf-config' => json_encode($xfConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '>';

        $html .= '<thead' . ($this->get('head_variant') ? ' class="table-' . $this->e($this->get('head_variant')) . '"' : '') . '><tr>';
        foreach ($columns as $col) {
            $html .= '<th>' . $this->e($col['label']) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '</table>';

        if ($this->get('responsive') === false) {
            $html = '<div class="table-responsive">' . $html . '</div>';
        }

        return $html;
    }
}

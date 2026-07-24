<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use zxf\XfAdmin\Support\Html;

/**
 * 全功能数据表格（基于 DataTables，前端由 xfadmin.js 自动初始化）
 *
 * 支持：搜索、排序、分页、多选、导出（Excel/CSV/打印/PDF）、固定表头、
 * 响应式折叠、列筛选、AJAX / 服务端模式、超丰富单元格渲染器、行操作栏等
 *
 * XfAdmin::dataTable([
 *     'columns' => [
 *         'id'     => ['label' => 'ID', 'sortable' => true],
 *         'name'   => ['label' => '姓名', 'render' => 'input'],                    // 单元格输入框
 *         'token'  => ['label' => '令牌', 'render' => 'copy'],                     // 带复制按钮
 *         'ip'     => ['label' => 'IP', 'render' => 'ip'],                        // IP 展示（等宽字体 + 复制）
 *         'status' => ['label' => '状态', 'render' => ['type' => 'switch', 'url' => '/api/toggle/{id}']],  // 状态切换
 *         'roles'  => ['label' => '角色', 'render' => 'tags'],                     // 标签组
 *         'color'  => ['label' => '颜色', 'render' => 'color'],                    // 颜色块
 *         'level'  => ['label' => '级别', 'badges' => ['ERROR' => 'danger']],      // 徽章映射
 *         'op'     => ['label' => '操作', 'actions' => [                          // 行操作栏
 *             ['label' => '编辑', 'icon' => 'ti ti-edit', 'url' => '/edit/{id}'],
 *             ['label' => '删除', 'icon' => 'ti ti-trash', 'class' => 'btn-soft-danger',
 *              'ajax' => '/del/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？', 'reload' => true],
 *             ['label' => '详情', 'action' => 'view'],
 *             ['label' => '复制行', 'action' => 'copy-row'],
 *         ]],
 *     ],
 *     'data'        => [...],                 // 本地数据；或使用 ajax
 *     'ajax'        => '/api/users',          // 远程数据（返回 {data: [...]}）
 *     'server_side' => false,                 // 服务端分页/排序/搜索（配合 Support\DataSet）
 *     'buttons'     => ['copy', 'csv', 'excel', 'print', 'colvis', 'refresh'],
 *     'select'      => true | 'multi' | 'single',
 *     'fixed_header'   => true,
 *     'responsive'     => true,
 *     'column_filters' => true,               // 每列底部筛选输入框
 *     'row_id'      => 'id',                  // 行 id 字段（行操作定位用）
 *     'options'     => [],                    // 透传 DataTables 原生配置（最高优先级）
 * ])
 *
 * 内置单元格渲染器（render 可为字符串或 ['type' => ..., ...配置]）：
 *   input | copy | ip | switch | tags | color | image | avatar | progress |
 *   bool | link | code | datetime | money | truncate | rating | icon | actions
 */
class DataTable extends Table
{
    /** 快捷渲染器列键（列定义中出现即转为 render 配置） */
    protected const RENDER_SHORTCUTS = ['switch', 'copy', 'tags', 'color', 'progress', 'link', 'image'];

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
            'language'       => null,     // DataTables 语言包配置数组（缺省内置中文）
            'row_id'         => null,     // 行 id 字段
            'auto_width'     => null,
            'scroll_x'       => false,    // 横向滚动（列多时）
            'filter_bar'     => [],       // 过滤工具栏（服务端模式自动拼接查询参数并重载）
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

    /** 解析单列的 xfRender 配置（render / actions / 快捷键） */
    protected function columnRender(array $col): ?array
    {
        // 行操作栏
        if (isset($col['actions']) && is_array($col['actions'])) {
            return ['type' => 'actions', 'items' => array_values($col['actions'])];
        }
        // 显式 render
        if (isset($col['render'])) {
            return is_array($col['render']) ? $col['render'] + ['type' => 'text'] : ['type' => (string) $col['render']];
        }
        // 快捷键：'switch' => '/url'、'copy' => true、'tags' => true ...
        foreach (self::RENDER_SHORTCUTS as $key) {
            if (isset($col[$key]) && $col[$key] !== false) {
                $cfg = ['type' => $key];
                if (is_string($col[$key])) {
                    $cfg['url'] = $col[$key];
                } elseif (is_array($col[$key])) {
                    $cfg += $col[$key];
                }

                return $cfg;
            }
        }

        return null;
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
            // 富单元格渲染器
            $render = $this->columnRender($col);
            if ($render !== null) {
                $c['xfRender'] = $render;
                // 操作/交互列默认不排序不搜索
                if (in_array($render['type'], ['actions', 'switch', 'input'], true)) {
                    $c['orderable']  ??= isset($col['sortable']) ? (bool) $col['sortable'] : false;
                    $c['searchable'] ??= isset($col['searchable']) ? (bool) $col['searchable'] : false;
                }
                // data 为空的操作列使用整行数据
                if ($col['key'] === '' || ! isset($col['key'])) {
                    $c['data'] = null;
                    $c['defaultContent'] = '';
                }
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
        if ($this->get('scroll_x')) {
            $config['scrollX'] = true;
        }
        if ($this->get('auto_width') !== null) {
            $config['autoWidth'] = (bool) $this->get('auto_width');
        }
        if ($this->get('row_id')) {
            $config['rowId'] = (string) $this->get('row_id');
        }
        if ($this->get('select')) {
            $config['select'] = $this->get('select') === true ? ['style' => 'multi'] : ['style' => (string) $this->get('select')];
        }

        $buttons = (array) $this->get('buttons', []);
        if ($buttons !== []) {
            $config['buttons'] = array_map([$this, 'mapButton'], $buttons);
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
        if ((array) $this->get('filter_bar', []) !== []) {
            $xfConfig['filterBar'] = true;
        }

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

        if ($this->get('responsive') === false && ! $this->get('scroll_x')) {
            $html = '<div class="table-responsive">' . $html . '</div>';
        }

        // 过滤工具栏（前端自动接线：变更 => 拼接查询参数 => 重载表格）
        $filterBar = (array) $this->get('filter_bar', []);
        if ($filterBar !== []) {
            $html = $this->renderFilterBar($id, $filterBar) . $html;
        }

        return $html;
    }

    /**
     * 渲染过滤工具栏
     *
     * 'filter_bar' => [
     *     ['name' => 'status', 'label' => '状态', 'options' => ['on' => '启用', 'off' => '停用']],   // select
     *     ['name' => 'keyword', 'label' => '关键词', 'type' => 'text', 'placeholder' => '姓名/邮箱'],
     *     ['name' => 'date_from', 'label' => '开始日期', 'type' => 'date'],
     *     ['name' => 'level', 'type' => 'radio', 'options' => [...]],   // 按钮组单选
     * ]
     */
    protected function renderFilterBar(string $tableId, array $controls): string
    {
        $html = '<form class="row g-2 mb-3 xf-filter-bar" data-xf-filter-for="' . $this->e($tableId) . '" onsubmit="return false;">';
        foreach ($controls as $c) {
            if (! is_array($c) || empty($c['name'])) {
                continue;
            }
            $name        = (string) $c['name'];
            $type        = (string) ($c['type'] ?? 'select');
            $width       = (string) ($c['width'] ?? 'col-6 col-md-3 col-xl-2');
            $placeholder = (string) ($c['placeholder'] ?? '');
            $html .= '<div class="' . $this->e($width) . '">';
            if (! empty($c['label'])) {
                $html .= '<label class="form-label mb-1">' . $this->e($c['label']) . '</label>';
            }
            if ($type === 'date') {
                $html .= '<input type="date" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '">';
            } elseif ($type === 'text') {
                $html .= '<input type="text" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '" placeholder="' . $this->e($placeholder) . '">';
            } elseif ($type === 'radio') {
                $html .= '<div class="btn-group btn-group-sm w-100 xf-filter-radio" data-filter="' . $this->e($name) . '" role="group">';
                $html .= '<button type="button" class="btn btn-outline-secondary active" data-value="">全部</button>';
                foreach ((array) ($c['options'] ?? []) as $v => $t) {
                    $html .= '<button type="button" class="btn btn-outline-secondary" data-value="' . $this->e($v) . '">' . $this->e($t) . '</button>';
                }
                $html .= '</div>';
            } else {
                $html .= '<select class="form-select form-select-sm xf-filter" data-filter="' . $this->e($name) . '">'
                    . '<option value="">' . $this->e($placeholder !== '' ? $placeholder : '全部') . '</option>';
                foreach ((array) ($c['options'] ?? []) as $v => $t) {
                    $html .= '<option value="' . $this->e($v) . '">' . $this->e($t) . '</option>';
                }
                $html .= '</select>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="col-6 col-md-2 col-xl-1 d-flex align-items-end">'
            . '<button type="button" class="btn btn-sm btn-soft-secondary w-100 xf-filter-reset"><i class="ti ti-rotate"></i> 重置</button></div>';
        $html .= '</form>';

        return $html;
    }

    /** 按钮映射：字符串简写 => DataTables 按钮配置（refresh/colvis/fullscreen 为包内扩展按钮） */
    protected function mapButton(mixed $b): array
    {
        if (! is_string($b)) {
            return (array) $b;
        }

        // 包内扩展按钮（由 xfadmin.js 转换为带 action 的真实按钮）
        if (in_array($b, ['refresh', 'fullscreen'], true)) {
            return ['xfButton' => $b, 'className' => 'btn btn-sm btn-secondary'];
        }

        $extendMap = [
            'pdf'    => 'pdfHtml5',
            'excel'  => 'excelHtml5',
            'csv'    => 'csvHtml5',
            'colvis' => 'colvis',
        ];
        $btn = ['extend' => $extendMap[$b] ?? $b, 'className' => 'btn btn-sm btn-secondary'];
        if ($b === 'colvis') {
            $btn['text'] = '<i class="ti ti-columns"></i> 列显示';
        }

        return $btn;
    }
}

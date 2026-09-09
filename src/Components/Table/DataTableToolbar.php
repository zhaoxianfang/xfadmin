<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use zxf\XfAdmin\Components\Component;

/**
 * 列表工具条
 *
 * 搜索 + 列筛选 + 每页条数 + 批量操作按钮 + 视图切换，
 * 复刻文件管理器 / 订单等页面的列表工具栏。
 *
 * XfAdmin::dataTableToolbar([
 *     'table'    => 'dt-orders',        // 关联的表格 id；留空则自动查找同容器中最近的表格
 *     'search'   => true,
 *     'searchPlaceholder' => '搜索...',
 *     'filters'  => [ ['label'=>'全部', 'value'=>''], ['label'=>'待处理','value'=>'pending'] ],
 *     'filterField' => 'status',        // 筛选绑定的列（列索引或 DataTables 列名）
 *     'pageSize' => [10, 20, 50],
 *     'actions'  => [ ['label'=>'导出','variant'=>'outline-secondary','icon'=>'ti ti-download'] ],
 *     'views'    => [ ['label'=>'网格','icon'=>'ti ti-layout-grid','active'=>true], ['label'=>'列表','icon'=>'ti ti-list'] ],
 * ])
 *
 * 交互由 xfadmin.js 的 dt-search / dt-filter / dt-pagesize / dt-views 四个 widget 托管：
 * 搜索与筛选为输入防抖后重绘、每页条数调用 page.len()、视图切换同步 active 并派发
 * xf.dtview.change 事件（detail: {view, button, api}）供业务切换渲染形态。
 */
class DataTableToolbar extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'table'            => null,    // 关联表格 id（不含 #）；留空则自动就近查找
            'search'           => true,
            'searchPlaceholder' => '搜索...',
            'filters'          => [],
            'filterField'      => null,    // 筛选列：列索引（0 起）或 DataTables 列名
            'pageSize'         => [10, 20, 50],
            'actions'          => [],
            'views'            => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $search = $this->get('search');
        $ph     = $this->get('searchPlaceholder');
        $filters = (array) $this->get('filters', []);
        $field  = $this->get('filterField');
        $sizes  = (array) $this->get('pageSize', []);
        $actions = (array) $this->get('actions', []);
        $views  = (array) $this->get('views', []);
        // 关联表格：所有交互控件共用 data-xf-table，JS 侧据此定位 DataTables 实例
        $tableAttr = $this->get('table') ? ' data-xf-table="' . $this->e((string) $this->get('table')) . '"' : '';
        $fieldAttr = ($field !== null && $field !== '') ? ' data-field="' . $this->e((string) $field) . '"' : '';

        $html = '<div class="xf-dt-toolbar d-flex flex-wrap gap-2 align-items-center mb-3">';

        if ($search) {
            $html .= '<div class="flex-grow-1" style="min-width:200px;"><div class="app-search position-relative">'
                . '<input type="search" class="form-control" placeholder="' . $this->e($ph) . '" data-xf="dt-search"' . $tableAttr . '>'
                . '<i class="ti ti-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i></div></div>';
        }
        if ($filters) {
            $html .= '<select class="form-select" style="width:auto" data-xf="dt-filter"' . $tableAttr . $fieldAttr . '>'
                . '<option value="">' . $this->e($filters[array_key_first($filters)]['label'] ?? '筛选') . '</option>';
            foreach ($filters as $f) {
                $f = (array) $f;
                $html .= '<option value="' . $this->e($f['value'] ?? '') . '">' . $this->e($f['label'] ?? '') . '</option>';
            }
            $html .= '</select>';
        }
        if ($sizes) {
            $html .= '<select class="form-select" style="width:auto" data-xf="dt-pagesize"' . $tableAttr . '><option value="">每页</option>';
            foreach ($sizes as $s) {
                $html .= '<option value="' . (int) $s . '">' . (int) $s . ' 条</option>';
            }
            $html .= '</select>';
        }
        if ($actions) {
            $html .= '<div class="btn-group" role="group">';
            foreach ($actions as $a) {
                $a = (array) $a;
                $variant = $this->enum($a['variant'] ?? 'outline-secondary', array_merge(self::ENUM_VARIANT, self::ENUM_VARIANT_OUTLINE), 'outline-secondary');
                $icon = $a['icon'] ?? '';
                $html .= '<button type="button" class="btn btn-' . $variant . '"><i class="' . $this->e($icon) . '"></i> ' . $this->e($a['label'] ?? '') . '</button>';
            }
            $html .= '</div>';
        }
        if ($views) {
            $html .= '<div class="btn-group" role="group" data-xf="dt-views"' . $tableAttr . '>';
            foreach ($views as $i => $v) {
                $v = (array) $v;
                $active = ! empty($v['active']) ? ' active' : '';
                // data-view 供业务识别当前视图；未给 name 时退回索引
                $viewKey = (string) ($v['name'] ?? $i);
                $html .= '<button type="button" class="btn btn-outline-secondary' . $active . '" data-view="' . $this->e($viewKey) . '"'
                    . ' title="' . $this->e($v['label'] ?? '') . '"><i class="' . $this->e($v['icon'] ?? '') . '"></i></button>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

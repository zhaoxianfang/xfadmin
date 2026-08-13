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
 *     'search'   => true,
 *     'searchPlaceholder' => '搜索...',
 *     'filters'  => [ ['label'=>'全部', 'value'=>''], ['label'=>'待处理','value'=>'pending'] ],
 *     'pageSize' => [10, 20, 50],
 *     'actions'  => [ ['label'=>'导出','variant'=>'outline-secondary','icon'=>'ti ti-download'] ],
 *     'views'    => [ ['label'=>'网格','icon'=>'ti ti-layout-grid','active'=>true], ['label'=>'列表','icon'=>'ti ti-list'] ],
 * ])
 */
class DataTableToolbar extends Component
{
    protected function defaults(): array
    {
        return [
            'search'           => true,
            'searchPlaceholder' => '搜索...',
            'filters'          => [],
            'pageSize'         => [10, 20, 50],
            'actions'          => [],
            'views'            => [],
        ];
    }

    protected function html(): string
    {
        $search = $this->get('search');
        $ph     = $this->get('searchPlaceholder');
        $filters = (array) $this->get('filters', []);
        $sizes  = (array) $this->get('pageSize', []);
        $actions = (array) $this->get('actions', []);
        $views  = (array) $this->get('views', []);

        $html = '<div class="xf-dt-toolbar d-flex flex-wrap gap-2 align-items-center mb-3">';

        if ($search) {
            $html .= '<div class="flex-grow-1" style="min-width:200px;"><div class="app-search position-relative">'
                . '<input type="search" class="form-control" placeholder="' . $this->e($ph) . '" data-xf="dt-search">'
                . '<i class="ti ti-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i></div></div>';
        }

        if ($filters) {
            $html .= '<select class="form-select" style="width:auto" data-xf="dt-filter"><option value="">' . $this->e($filters[0]['label'] ?? '筛选') . '</option>';
            foreach ($filters as $f) {
                $html .= '<option value="' . $this->e($f['value'] ?? '') . '">' . $this->e($f['label'] ?? '') . '</option>';
            }
            $html .= '</select>';
        }

        if ($sizes) {
            $html .= '<select class="form-select" style="width:auto" data-xf="dt-pagesize"><option value="">每页</option>';
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
            $html .= '<div class="btn-group" role="group" data-xf="dt-views">';
            foreach ($views as $v) {
                $v = (array) $v;
                $active = ! empty($v['active']) ? ' active' : '';
                $html .= '<button type="button" class="btn btn-outline-secondary' . $active . '"><i class="' . $this->e($v['icon'] ?? '') . '"></i></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}

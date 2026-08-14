<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 仓库管理（apps-ecommerce-warehouse.html）
 *
 * XfAdmin::warehouse([
 *     'warehouses' => [
 *         ['name' => '主仓库', 'location' => '上海浦东', 'manager' => '张三', 'capacity' => 15000, 'used' => 10234, 'status' => 'active', 'products' => 345],
 *         ...
 *     ],
 *     'totalCapacity' => '45,000',
 *     'totalInventory' => '28,564',
 * ])
 */
class Warehouse extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'warehouses' => [],
            'totalCapacity' => '0',
            'totalInventory' => '0',
            'title' => '仓库管理',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $warehouses = (array) $this->get('warehouses', []);
        $totalCapacity = (string) $this->get('totalCapacity', '0');
        $totalInventory = (string) $this->get('totalInventory', '0');
        $title = (string) $this->get('title', '仓库管理');

        $html = '';

        // 统计卡片
        $html .= '<div class="row mb-3"><div class="col-md-6"><div class="card text-bg-primary"><div class="card-body">';
        $html .= '<h6 class="text-white-50">总库存容量</h6><h2>' . $this->e($totalCapacity) . '</h2></div></div></div>';
        $html .= '<div class="col-md-6"><div class="card text-bg-success"><div class="card-body">';
        $html .= '<h6 class="text-white-50">当前库存量</h6><h2>' . $this->e($totalInventory) . '</h2></div></div></div></div>';

        // 仓库列表
        $html .= '<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">' . $this->e($title) . '</h5>'
            . '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>添加仓库</button></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">仓库名称</th><th>位置</th><th>负责人</th><th>总容量</th><th>已使用</th><th>使用率</th><th>状态</th><th class="text-end pe-3">操作</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($warehouses as $wh) {
            $wh = (array) $wh;
            $name = (string) ($wh['name'] ?? '');
            $location = (string) ($wh['location'] ?? '');
            $manager = (string) ($wh['manager'] ?? '');
            $capacity = (int) ($wh['capacity'] ?? 0);
            $used = (int) ($wh['used'] ?? 0);
            $status = (string) ($wh['status'] ?? 'active');
            $products = (int) ($wh['products'] ?? 0);
            $pct = $capacity > 0 ? round($used / $capacity * 100, 1) : 0;
            $barColor = $pct > 80 ? 'danger' : ($pct > 60 ? 'warning' : 'success');
            $statusBadge = match ($status) {
                'active' => '<span class="badge text-bg-success">运行中</span>',
                'inactive' => '<span class="badge text-bg-warning">暂停</span>',
                default => '<span class="badge text-bg-secondary">' . $this->e($status) . '</span>',
            };

            $html .= '<tr><td class="ps-3"><a href="javascript:void(0)" class="fw-semibold">' . $this->e($name) . '</a></td>';
            $html .= '<td>' . $this->e($location) . '</td><td>' . $this->e($manager) . '</td>';
            $html .= '<td>' . number_format($capacity) . '</td><td>' . number_format($used) . '</td>';
            $html .= '<td><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:6px">';
            $html .= '<div class="progress-bar bg-' . $barColor . '" style="width:' . $pct . '%"></div></div>';
            $html .= '<small>' . $pct . '%</small></div></td>';
            $html .= '<td>' . $statusBadge . '</td>';
            $html .= '<td class="text-end pe-3"><div class="btn-group btn-group-sm">'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-pencil"></i></button>'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-trash"></i></button></div></td></tr>';
        }
        if (empty($warehouses)) {
            $html .= '<tr><td colspan="8" class="text-center text-muted py-4">没有仓库数据</td></tr>';
        }
        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

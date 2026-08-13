<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 销售仪表板（apps-ecommerce-sales.html）
 *
 * XfAdmin::sales([
 *     'stats' => [
 *         ['title' => '总收入', 'value' => '$128,430', 'icon' => 'ti-currency-dollar', 'color' => 'primary', 'change' => '+12.5%'],
 *         ['title' => '订单数', 'value' => '2,845', 'icon' => 'ti-shopping-cart', 'color' => 'success', 'change' => '+8.2%'],
 *         ['title' => '客户数', 'value' => '1,240', 'icon' => 'ti-users', 'color' => 'warning', 'change' => '+15.7%'],
 *         ['title' => '退款率', 'value' => '2.3%', 'icon' => 'ti-receipt-refund', 'color' => 'danger', 'change' => '-0.5%'],
 *     ],
 *     'recentOrders' => [
 *         ['id' => '#ORD-5001', 'customer' => '客户名', 'amount' => 89.00, 'date' => '2025-08-10', 'status' => 'completed'],
 *         ...
 *     ],
 *     'topProducts' => [
 *         ['name' => '商品名', 'sold' => 256, 'revenue' => 12800.00, 'growth' => 12],
 *         ...
 *     ],
 *     'currency' => '$',
 * ])
 */
class Sales extends Component
{
    protected function defaults(): array
    {
        return [
            'stats' => [],
            'recentOrders' => [],
            'topProducts' => [],
            'currency' => '¥',
        ];
    }

    protected function html(): string
    {
        $stats = (array) $this->get('stats', []);
        $recentOrders = (array) $this->get('recentOrders', []);
        $topProducts = (array) $this->get('topProducts', []);
        $currency = (string) $this->get('currency', '¥');

        $html = '';

        // 统计卡片
        if (!empty($stats)) {
            $colClass = 'col-md-' . (12 / max(1, min(4, count($stats))));
            $html .= '<div class="row mb-4">';
            foreach ($stats as $stat) {
                $stat = (array) $stat;
                $color = (string) ($stat['color'] ?? 'primary');
                $icon = (string) ($stat['icon'] ?? 'ti-trending-up');
                $html .= '<div class="' . $colClass . '"><div class="card"><div class="card-body">';
                $html .= '<div class="d-flex justify-content-between align-items-start">';
                $html .= '<div><span class="text-muted small">' . $this->e($stat['title'] ?? '') . '</span>';
                $html .= '<h3 class="mt-2 mb-0">' . $this->e($stat['value'] ?? '') . '</h3>';
                if (!empty($stat['change'])) {
                    $changeClass = str_starts_with((string) $stat['change'], '+') ? 'success' : 'danger';
                    $html .= '<small class="text-' . $changeClass . '"><i class="ti ti-arrow-' . ($changeClass === 'success' ? 'up' : 'down') . '-right"></i> '
                        . $this->e($stat['change']) . ' vs上月</small>';
                }
                $html .= '</div>';
                $html .= '<div class="bg-' . $this->e($color) . ' bg-opacity-10 rounded p-2">'
                    . '<i class="ti ' . $this->e($icon) . ' fs-24 text-' . $this->e($color) . '"></i></div>';
                $html .= '</div></div></div></div>';
            }
            $html .= '</div>';
        }

        // 最近订单 & 热销商品
        $html .= '<div class="row"><div class="col-lg-6">';
        $html .= '<div class="card"><div class="card-header"><h5 class="mb-0">最近订单</h5></div><div class="card-body p-0">';
        $html .= '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th class="ps-3">订单号</th><th>客户</th><th>金额</th><th>日期</th><th>状态</th></tr></thead><tbody>';

        foreach ($recentOrders as $order) {
            $order = (array) $order;
            $status = (string) ($order['status'] ?? 'completed');
            $statusBadge = match ($status) {
                'completed' => '<span class="badge text-bg-success">已完成</span>',
                'pending' => '<span class="badge text-bg-warning">待处理</span>',
                'cancelled' => '<span class="badge text-bg-danger">已取消</span>',
                default => '<span class="badge text-bg-secondary">' . $this->e($status) . '</span>',
            };

            $html .= '<tr><td class="ps-3"><a href="javascript:void(0)">' . $this->e($order['id'] ?? '') . '</a></td>';
            $html .= '<td>' . $this->e($order['customer'] ?? '') . '</td>';
            $html .= '<td>' . $currency . number_format((float) ($order['amount'] ?? 0), 2) . '</td>';
            $html .= '<td>' . $this->e($order['date'] ?? '') . '</td><td>' . $statusBadge . '</td></tr>';
        }

        if (empty($recentOrders)) {
            $html .= '<tr><td colspan="5" class="text-center text-muted py-3">暂无近期订单</td></tr>';
        }

        $html .= '</tbody></table></div></div></div></div>';

        // 热销商品
        $html .= '<div class="col-lg-6"><div class="card"><div class="card-header"><h5 class="mb-0">热销商品 TOP5</h5></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">#</th><th>商品</th><th>销量</th><th>收入</th><th>增长</th></tr></thead><tbody>';

        foreach ($topProducts as $i => $p) {
            $p = (array) $p;
            $rank = $i + 1;
            $growth = (int) ($p['growth'] ?? 0);
            $growthClass = $growth >= 0 ? 'text-success' : 'text-danger';

            $html .= '<tr><td class="ps-3 text-muted">' . $rank . '</td>';
            $html .= '<td>' . $this->e($p['name'] ?? '') . '</td>';
            $html .= '<td>' . number_format((int) ($p['sold'] ?? 0)) . '</td>';
            $html .= '<td>' . $currency . number_format((float) ($p['revenue'] ?? 0), 2) . '</td>';
            $html .= '<td class="' . $growthClass . '"><i class="ti ti-arrow-' . ($growth >= 0 ? 'up' : 'down') . '-right"></i> ' . abs($growth) . '%</td></tr>';
        }

        if (empty($topProducts)) {
            $html .= '<tr><td colspan="5" class="text-center text-muted py-3">暂无数据</td></tr>';
        }

        $html .= '</tbody></table></div></div></div></div></div>';

        return $html;
    }
}

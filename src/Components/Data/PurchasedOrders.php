<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 已采购订单（apps-ecommerce-purchased-orders.html）
 *
 * XfAdmin::purchasedOrders([
 *     'orders' => [
 *         ['id' => '#PO-1001', 'supplier' => '供应商A', 'items' => 45, 'total' => 12500.00, 'date' => '2025-08-01', 'status' => 'received', 'deliveryDate' => '2025-08-08'],
 *         ...
 *     ],
 *     'currency' => '¥',
 * ])
 */
class PurchasedOrders extends Component
{
    protected function defaults(): array
    {
        return [
            'orders' => [],
            'currency' => '¥',
            'title' => '采购订单',
        ];
    }

    protected function html(): string
    {
        $orders = (array) $this->get('orders', []);
        $currency = (string) $this->get('currency', '¥');
        $title = (string) $this->get('title', '采购订单');

        $html = '<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">' . $this->e($title) . '</h5>'
            . '<div class="d-flex gap-2"><select class="form-select form-select-sm" style="width:auto"><option>全部状态</option>'
            . '<option>已接收</option><option>运输中</option><option>待确认</option></select>'
            . '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>新建采购</button></div></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">采购编号</th><th>供应商</th><th>商品数</th><th>总金额</th><th>采购日期</th><th>预计到货</th><th>状态</th><th class="text-end pe-3">操作</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($orders as $order) {
            $order = (array) $order;
            $id = (string) ($order['id'] ?? '');
            $supplier = (string) ($order['supplier'] ?? '');
            $items = (int) ($order['items'] ?? 0);
            $total = (float) ($order['total'] ?? 0);
            $date = (string) ($order['date'] ?? '');
            $status = (string) ($order['status'] ?? 'pending');
            $deliveryDate = (string) ($order['deliveryDate'] ?? '');

            $statusBadge = match ($status) {
                'received' => '<span class="badge text-bg-success">已接收</span>',
                'shipping' => '<span class="badge text-bg-info">运输中</span>',
                'pending' => '<span class="badge text-bg-warning">待确认</span>',
                'cancelled' => '<span class="badge text-bg-danger">已取消</span>',
                default => '<span class="badge text-bg-secondary">' . $this->e($status) . '</span>',
            };

            $html .= '<tr><td class="ps-3"><a href="javascript:void(0)" class="fw-semibold">' . $this->e($id) . '</a></td>';
            $html .= '<td>' . $this->e($supplier) . '</td><td>' . $items . '</td>';
            $html .= '<td class="fw-semibold">' . $currency . number_format($total, 2) . '</td>';
            $html .= '<td>' . $this->e($date) . '</td><td>' . $this->e($deliveryDate) . '</td>';
            $html .= '<td>' . $statusBadge . '</td>';
            $html .= '<td class="text-end pe-3"><div class="btn-group btn-group-sm">'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-eye"></i></button>'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-pencil"></i></button></div></td></tr>';
        }

        if (empty($orders)) {
            $html .= '<tr><td colspan="8" class="text-center text-muted py-4">暂无采购记录</td></tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

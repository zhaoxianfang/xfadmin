<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 退款管理（apps-ecommerce-refunds.html）
 *
 * XfAdmin::refunds([
 *     'refunds' => [
 *         ['id' => '#REF-1001', 'orderId' => '#ORD-5001', 'customer' => '客户名', 'amount' => 89.00, 'reason' => '质量问题', 'date' => '2025-08-10', 'status' => 'pending', 'items' => 2],
 *         ...
 *     ],
 *     'currency' => '$',
 * ])
 */
class Refunds extends Component
{
    protected function defaults(): array
    {
        return [
            'refunds' => [],
            'currency' => '¥',
            'title' => '退款管理',
        ];
    }

    protected function html(): string
    {
        $refunds = (array) $this->get('refunds', []);
        $currency = (string) $this->get('currency', '¥');
        $title = (string) $this->get('title', '退款管理');

        $html = '<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">' . $this->e($title) . '</h5>';
        $html .= '<div class="d-flex gap-2"><select class="form-select form-select-sm" style="width:auto"><option>全部状态</option>'
            . '<option>待处理</option><option>已批准</option><option>已拒绝</option><option>已完成</option></select>';
        $html .= '<div class="input-group input-group-sm" style="width:250px"><input type="text" class="form-control" placeholder="搜索...">'
            . '<button class="btn btn-outline-secondary"><i class="ti ti-search"></i></button></div></div></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">退款编号</th><th>订单号</th><th>客户</th><th>金额</th><th>商品数</th><th>原因</th><th>日期</th><th>状态</th><th class="text-end pe-3">操作</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($refunds as $ref) {
            $ref = (array) $ref;
            $id = (string) ($ref['id'] ?? '');
            $orderId = (string) ($ref['orderId'] ?? '');
            $customer = (string) ($ref['customer'] ?? '');
            $amount = (float) ($ref['amount'] ?? 0);
            $reason = (string) ($ref['reason'] ?? '');
            $date = (string) ($ref['date'] ?? '');
            $status = (string) ($ref['status'] ?? 'pending');
            $items = (int) ($ref['items'] ?? 1);

            $statusBadge = match ($status) {
                'pending' => '<span class="badge text-bg-warning">待处理</span>',
                'approved' => '<span class="badge text-bg-info">已批准</span>',
                'rejected' => '<span class="badge text-bg-danger">已拒绝</span>',
                'completed' => '<span class="badge text-bg-success">已完成</span>',
                default => '<span class="badge text-bg-secondary">' . $this->e($status) . '</span>',
            };

            $html .= '<tr><td class="ps-3"><a href="javascript:void(0)" class="fw-semibold">' . $this->e($id) . '</a></td>';
            $html .= '<td>' . $this->e($orderId) . '</td><td>' . $this->e($customer) . '</td>';
            $html .= '<td class="fw-semibold text-danger">' . $currency . number_format($amount, 2) . '</td>';
            $html .= '<td>' . $items . '</td><td>' . $this->e($reason) . '</td><td>' . $this->e($date) . '</td>';
            $html .= '<td>' . $statusBadge . '</td>';
            $html .= '<td class="text-end pe-3"><div class="btn-group btn-group-sm">';
            if ($status === 'pending') {
                $html .= '<button class="btn btn-outline-success" title="批准"><i class="ti ti-check"></i></button>';
                $html .= '<button class="btn btn-outline-danger" title="拒绝"><i class="ti ti-x"></i></button>';
            }
            $html .= '<button class="btn btn-outline-secondary"><i class="ti ti-eye"></i></button></div></td></tr>';
        }

        if (empty($refunds)) {
            $html .= '<tr><td colspan="9" class="text-center text-muted py-4">暂无退款记录</td></tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

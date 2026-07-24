<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 发票列表（invoice.html）—— 表格形式呈现多张发票，含状态、金额、操作
 *
 * XfAdmin::invoiceList([
 *     'items' => [
 *         [
 *             'id'      => 'INV-001',
 *             'client'  => '北京科技有限公司',
 *             'amount'  => 12800.00,
 *             'status'  => 'paid',          // paid | unpaid | pending | overdue
 *             'issued'  => '2026-07-01',
 *             'due'     => '2026-07-15',
 *             'actions' => '<a href="#" class="btn btn-sm btn-soft-primary">查看</a>',
 *         ],
 *     ],
 *     'currency' => '¥',
 * ])
 */
class InvoiceList extends Component
{
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'currency' => '¥',
            'title'    => '发票',
            'summary'  => [],       // 顶部统计卡片：['label'=>,'value'=>,'variant'=>]
        ];
    }

    protected static array $statusMap = [
        'paid'    => ['text' => '已付款', 'variant' => 'success'],
        'unpaid'  => ['text' => '未付款', 'variant' => 'danger'],
        'pending' => ['text' => '待处理', 'variant' => 'warning'],
        'overdue' => ['text' => '已逾期', 'variant' => 'danger'],
    ];

    protected function html(): string
    {
        $id    = $this->resolveId('invoice-list');
        $items = array_values((array) $this->get('items', []));
        $cur   = $this->e($this->get('currency'));

        $html = '<div' . $this->attrs(['class' => 'xf-invoice-list', 'id' => $id]) . '>';

        // 统计卡片
        if (! empty($this->get('summary'))) {
            $html .= '<div class="row g-3 mb-3">';
            foreach ((array) $this->get('summary') as $s) {
                $s = (array) $s;
                $variant = $s['variant'] ?? 'primary';
                $html .= '<div class="col-md-3 col-6"><div class="card border-0 shadow-sm"><div class="card-body">'
                    . '<div class="text-muted small">' . $this->e($s['label'] ?? '') . '</div>'
                    . '<div class="h4 mb-0 text-' . $this->e($variant) . '">' . $this->e($s['value'] ?? '') . '</div></div></div></div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="card border-0 shadow-sm"><div class="card-body p-0">';
        $html .= '<div class="table-responsive"><table class="table table-hover align-middle mb-0">';
        $html .= '<thead class="table-light"><tr>'
            . '<th>发票号</th><th>客户</th><th>金额</th><th>状态</th><th>开票日</th><th>到期日</th><th class="text-end">操作</th>'
            . '</tr></thead><tbody>';

        foreach ($items as $it) {
            $it = (array) $it;
            $status = self::$statusMap[$it['status'] ?? ''] ?? ['text' => $it['status'] ?? '未知', 'variant' => 'secondary'];
            $amount = is_numeric($it['amount'] ?? null)
                ? $cur . number_format((float) $it['amount'], 2)
                : $this->e($it['amount'] ?? '');
            $html .= '<tr>'
                . '<td class="fw-medium">' . $this->e($it['id'] ?? '') . '</td>'
                . '<td>' . $this->e($it['client'] ?? '') . '</td>'
                . '<td class="fw-semibold">' . $amount . '</td>'
                . '<td>' . (string) XfAdmin::badge(['text' => $status['text'], 'variant' => $status['variant']]) . '</td>'
                . '<td>' . $this->e($it['issued'] ?? '') . '</td>'
                . '<td>' . $this->e($it['due'] ?? '') . '</td>'
                . '<td class="text-end">' . $this->raw($it['actions'] ?? '') . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        return $html . '</div>';
    }
}

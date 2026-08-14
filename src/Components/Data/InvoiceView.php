<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 发票详情页（apps-invoice-details.html）
 *
 * 发票头（买家/卖家/发票号/日期）+ 明细表 + 合计。
 *
 * XfAdmin::invoiceView([
 *     'invoice_no' => 'INV-2026-0812',
 *     'issued_at'  => '2026-08-12',
 *     'due_at'     => '2026-09-11',
 *     'from'       => ['name' => 'WSF 科技有限公司', 'address' => '北京市朝阳区xx路1号', 'tax' => '91110105XXXX'],
 *     'to'         => ['name' => '示例客户有限公司', 'address' => '上海市浦东新区yy路2号', 'tax' => '91310115YYYY'],
 *     'items'      => [
 *         ['desc' => '企业版年度授权', 'qty' => 1, 'price' => 12800.00],
 *         ['desc' => '专属技术支持（年）', 'qty' => 1, 'price' => 3600.00],
 *     ],
 *     'tax_rate' => 0.06,
 * ])
 */
class InvoiceView extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'invoice_no' => '',
            'issued_at'  => '',
            'due_at'     => '',
            'from'       => [],
            'to'         => [],
            'items'      => [],
            'tax_rate'   => 0.06,
            'currency'   => '¥',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $invoiceNo = (string) $this->get('invoice_no', '');
        $issuedAt  = (string) $this->get('issued_at', '');
        $dueAt     = (string) $this->get('due_at', '');
        $from      = (array) $this->get('from', []);
        $to        = (array) $this->get('to', []);
        $items     = (array) $this->get('items', []);
        $taxRate   = (float) $this->get('tax_rate', 0.06);
        $currency  = (string) $this->get('currency', '¥');
        $safeCur   = $this->e($currency);

        $fmt = fn ($n) => $safeCur . number_format((float) $n, 2);

        $party = function (array $p, string $label) use ($safeCur): string {
            $name    = (string) ($p['name'] ?? '');
            $address = (string) ($p['address'] ?? '');
            $tax     = (string) ($p['tax'] ?? '');

            $h = '<div><small class="text-muted text-uppercase">' . $this->e($label) . '</small>';
            $h .= '<div class="fw-semibold">' . $this->e($name) . '</div>';
            if ($address !== '') {
                $h .= '<div class="text-muted small">' . $this->e($address) . '</div>';
            }
            if ($tax !== '') {
                $h .= '<div class="text-muted small">税号：' . $this->e($tax) . '</div>';
            }
            return $h . '</div>';
        };

        $html = '<div class="card"><div class="card-body">';

        // 头部
        $html .= '<div class="d-flex justify-content-between align-items-start mb-4">';
        $html .= '<div><h4 class="mb-1">发票 INVOICE</h4><div class="text-muted">#' . $this->e($invoiceNo) . '</div></div>';
        $html .= '<div class="text-end"><div class="text-muted small">开票日期：' . $this->e($issuedAt) . '</div><div class="text-muted small">到期日期：' . $this->e($dueAt) . '</div></div>';
        $html .= '</div>';

        // 双方
        $html .= '<div class="row mb-4"><div class="col-md-6">' . $party($from, '开票方') . '</div><div class="col-md-6 text-md-end">' . $party($to, '收票方') . '</div></div>';

        // 明细
        $html .= '<div class="table-responsive"><table class="table"><thead><tr><th>项目</th><th class="text-end">数量</th><th class="text-end">单价</th><th class="text-end">金额</th></tr></thead><tbody>';

        $subtotal = 0.0;
        if (empty($items)) {
            $html .= '<tr><td colspan="4" class="text-center text-muted py-3">暂无明细</td></tr>';
        }
        foreach ($items as $it) {
            $it   = (array) $it;
            $desc = (string) ($it['desc'] ?? '');
            $qty  = (float) ($it['qty'] ?? 0);
            $price = (float) ($it['price'] ?? 0);
            $amount = $qty * $price;
            $subtotal += $amount;

            $html .= '<tr><td>' . $this->e($desc) . '</td><td class="text-end">' . $qty . '</td><td class="text-end">' . $fmt($price) . '</td><td class="text-end">' . $fmt($amount) . '</td></tr>';
        }
        $tax = $subtotal * $taxRate;
        $html .= '</tbody><tfoot>';
        $html .= '<tr><td colspan="3" class="text-end fw-semibold">小计</td><td class="text-end">' . $fmt($subtotal) . '</td></tr>';
        $html .= '<tr><td colspan="3" class="text-end fw-semibold">税额（' . ($taxRate * 100) . '%）</td><td class="text-end">' . $fmt($tax) . '</td></tr>';
        $html .= '<tr><td colspan="3" class="text-end h5 fw-bold">合计</td><td class="text-end h5 fw-bold">' . $fmt($subtotal + $tax) . '</td></tr>';
        $html .= '</tfoot></table></div></div></div>';

        return $html;
    }
}

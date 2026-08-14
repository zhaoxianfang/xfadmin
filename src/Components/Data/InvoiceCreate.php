<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 发票创建 / 编辑（invoice-create.html）
 *
 * XfAdmin::invoiceCreate([
 *     'invoice' => [
 *         'number' => 'INV-2026-001',
 *         'from' => ['name'=>'我方公司','address'=>'…','email'=>'billing@x.com'],
 *         'to' => ['name'=>'客户公司','address'=>'…','email'=>'c@x.com'],
 *         'items' => [['description'=>'网站设计','qty'=>1,'rate'=>'¥8000','amount'=>'¥8000']],
 *         'tax_rate' => 6, 'discount' => '¥0',
 *         'notes' => '…', 'terms' => '…',
 *     ],
 * ])
 */
class InvoiceCreate extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['invoice' => []];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $inv = (array) $this->get('invoice', []);
        if (empty($inv)) {
            return '';
        }
        $html = '<div class="card"><div class="card-body">';

        // 头部
        $html .= '<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">';
        $html .= '<div><h4 class="mb-1">发票 ' . $this->e($inv['number'] ?? '') . '</h4><span class="badge bg-warning-subtle text-warning">草稿</span></div>';
        $html .= '<div class="text-end"><button class="btn btn-primary btn-sm me-1"><i class="ti ti-device-floppy me-1"></i>保存</button><button class="btn btn-light btn-sm"><i class="ti ti-printer me-1"></i>预览</button></div>';
        $html .= '</div>';

        // 双方（from/to 可能缺省，强转数组避免 PHP 8 访问 null 偏移告警）
        $from = (array) ($inv['from'] ?? []);
        $to   = (array) ($inv['to']   ?? []);
        $html .= '<div class="row g-3 mb-4">';
        $html .= '<div class="col-md-6"><label class="form-label small text-muted">开票方</label><input class="form-control mb-2" value="' . $this->e($from['name'] ?? '') . '"><input class="form-control mb-2" value="' . $this->e($from['address'] ?? '') . '"><input class="form-control" value="' . $this->e($from['email'] ?? '') . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label small text-muted">收票方</label><input class="form-control mb-2" value="' . $this->e($to['name'] ?? '') . '"><input class="form-control mb-2" value="' . $this->e($to['address'] ?? '') . '"><input class="form-control" value="' . $this->e($to['email'] ?? '') . '"></div>';
        $html .= '</div>';

        // 明细
        $html .= '<div class="table-responsive mb-3"><table class="table align-middle"><thead><tr><th>描述</th><th style="width:90px">数量</th><th style="width:130px">单价</th><th style="width:130px">金额</th><th></th></tr></thead><tbody>';
        $total = 0;
        foreach ((array) ($inv['items'] ?? []) as $it) {
            $it     = (array) $it;
            $amount = (float) preg_replace('/[^\d.]/', '', (string) ($it['amount'] ?? 0));
            $total += $amount;
            $html .= '<tr>';
            $html .= '<td><input class="form-control" value="' . $this->e($it['description'] ?? '') . '"></td>';
            $html .= '<td><input class="form-control" value="' . (int) ($it['qty'] ?? 1) . '"></td>';
            $html .= '<td><input class="form-control" value="' . $this->e($it['rate'] ?? '') . '"></td>';
            $html .= '<td><input class="form-control" value="' . $this->e($it['amount'] ?? '') . '"></td>';
            $html .= '<td><button class="btn btn-sm btn-icon btn-light text-danger"><i class="ti ti-trash"></i></button></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        $html .= '<button class="btn btn-sm btn-light mb-3"><i class="ti ti-plus me-1"></i>添加明细行</button>';

        // 汇总
        $taxRate = (float) ($inv['tax_rate'] ?? 0);
        $tax     = $total * $taxRate / 100;
        $grand   = $total + $tax;
        $html .= '<div class="row justify-content-end"><div class="col-md-5"><div class="d-flex justify-content-between mb-2"><span class="text-muted">小计</span><span>' . $this->money($total) . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">税率 (' . $taxRate . '%)</span><span>' . $this->money($tax) . '</span></div>';
        $html .= '<div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2"><span>应付总额</span><span class="text-primary">' . $this->money($grand) . '</span></div></div></div>';

        if (! empty($inv['notes']) || ! empty($inv['terms'])) {
            $html .= '<div class="row g-3 mt-1"><div class="col-md-6"><label class="form-label small text-muted">备注</label><textarea class="form-control" rows="2">' . $this->e($inv['notes'] ?? '') . '</textarea></div>'
                . '<div class="col-md-6"><label class="form-label small text-muted">条款</label><textarea class="form-control" rows="2">' . $this->e($inv['terms'] ?? '') . '</textarea></div></div>';
        }
        return $html . '</div></div>';
    }

    /**
     * money（private实例方法）
     *
     * @param float $v v
     *
     * @return string result
     */
    private function money(float $v): string
    {
        return '¥' . number_format($v, 2);
    }
}

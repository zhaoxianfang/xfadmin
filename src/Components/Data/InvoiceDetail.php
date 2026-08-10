<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 发票详情页（抬头 / 双方信息 / 明细 / 汇总 / 操作）—— INSPINIA invoice.html 整页抽取
 *
 * XfAdmin::invoiceDetail([
 *     'logo'     => '',                                       // 图片路径或留空显示标题
 *     'title'    => 'XF Admin',
 *     'number'   => 'INV-2026-0728',
 *     'status'   => ['text' => '待付款', 'color' => 'warning'],
 *     'meta'     => [['label' => '开票日期', 'value' => '2026-07-28']],
 *     'from'     => ['name' => '深圳某某科技', 'lines' => ['南山区…', 'tax@x.com']],
 *     'to'       => ['name' => '北京某某集团', 'lines' => ['朝阳区…']],
 *     'items'    => [['name' => '企业版授权', 'desc' => '1 年', 'qty' => 2, 'price' => 4999]],
 *     'currency' => '¥',
 *     'summary'  => [['label' => '小计', 'value' => '¥9,998.00'], ['label' => '合计', 'value' => '¥11,097.78', 'strong' => true]],
 *     'notes'    => '请于 15 日内完成付款。',
 *     'actions'  => [['text' => '打印', 'icon' => 'ti ti-printer', 'class' => 'btn-soft-secondary', 'onclick' => 'window.print()']],
 * ])
 */
class InvoiceDetail extends Component
{
    protected function defaults(): array
    {
        return [
            'logo'     => '',
            'title'    => '',
            'number'   => '',
            'status'   => [],
            'meta'     => [],
            'from'     => [],
            'to'       => [],
            'items'    => [],
            'currency' => '¥',
            'summary'  => [],
            'notes'    => '',
            'actions'  => [],
        ];
    }

    protected function html(): string
    {
        $cur = (string) $this->get('currency', '¥');

        $html = '<div' . $this->attrs(['class' => 'card border-0 shadow-sm xf-invoice-detail']) . '><div class="card-body p-4">';

        // ---- 抬头 ----
        $html .= '<div class="d-flex flex-wrap justify-content-between align-items-start mb-4">';
        $html .= '<div>';
        if ($this->get('logo')) {
            $html .= '<img src="' . $this->e($this->img($this->get('logo'))) . '" alt="logo" style="height:36px;" class="mb-2 d-block">';
        } elseif ($this->get('title')) {
            $html .= '<h4 class="mb-2">' . $this->e($this->get('title')) . '</h4>';
        }
        $html .= '<div class="text-muted">发票号：<span class="fw-semibold text-body">' . $this->e($this->get('number')) . '</span></div>';
        $html .= '</div><div class="text-end">';
        $status = (array) $this->get('status', []);
        if ($status) {
            $sc = $this->e($status['color'] ?? 'secondary');
            $html .= '<span class="badge bg-' . $sc . '-subtle text-' . $sc . ' fs-6 mb-2">' . $this->e($status['text'] ?? '') . '</span>';
        }
        foreach ((array) $this->get('meta', []) as $m) {
            $html .= '<div class="small text-muted">' . $this->e($m['label'] ?? '') . '：<span class="text-body">' . $this->e($m['value'] ?? '') . '</span></div>';
        }
        $html .= '</div></div>';

        // ---- 双方信息 ----
        $party = function (string $caption, array $p): string {
            if (! $p) {
                return '';
            }
            $s = '<div class="col-md-6"><div class="text-muted text-uppercase small mb-1">' . $this->e($caption) . '</div>'
                . '<div class="fw-semibold">' . $this->e($p['name'] ?? '') . '</div>';
            foreach ((array) ($p['lines'] ?? []) as $line) {
                $s .= '<div class="text-muted small">' . $this->e($line) . '</div>';
            }

            return $s . '</div>';
        };
        $html .= '<div class="row g-3 mb-4">' . $party('开票方', (array) $this->get('from', []))
            . $party('收票方', (array) $this->get('to', [])) . '</div>';

        // ---- 明细 ----
        $items = (array) $this->get('items', []);
        if ($items) {
            $html .= '<div class="table-responsive"><table class="table table-nowrap align-middle mb-0">'
                . '<thead class="bg-light bg-opacity-50"><tr><th style="width:40px;">#</th><th>项目</th>'
                . '<th class="text-center" style="width:90px;">数量</th><th class="text-end" style="width:130px;">单价</th>'
                . '<th class="text-end" style="width:130px;">金额</th></tr></thead><tbody>';
            $i = 0;
            foreach ($items as $it) {
                $qty    = (float) ($it['qty'] ?? 1);
                $price  = (float) ($it['price'] ?? 0);
                $amount = $it['amount'] ?? $qty * $price;
                $html .= '<tr><td>' . ++$i . '</td><td><span class="fw-semibold">' . $this->e($it['name'] ?? '') . '</span>'
                    . (! empty($it['desc']) ? '<div class="small text-muted">' . $this->e($it['desc']) . '</div>' : '')
                    . '</td><td class="text-center">' . $this->e($qty) . '</td>'
                    . '<td class="text-end">' . $this->e($cur . number_format($price, 2)) . '</td>'
                    . '<td class="text-end">' . $this->e(is_numeric($amount) ? $cur . number_format((float) $amount, 2) : (string) $amount) . '</td></tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ---- 汇总 ----
        $summary = (array) $this->get('summary', []);
        if ($summary) {
            $html .= '<div class="row justify-content-end mt-3"><div class="col-md-4">';
            foreach ($summary as $s) {
                $strong = ! empty($s['strong']);
                $html .= '<div class="d-flex justify-content-between py-1' . ($strong ? ' border-top fw-bold fs-5' : '') . '">'
                    . '<span class="' . ($strong ? '' : 'text-muted') . '">' . $this->e($s['label'] ?? '') . '</span>'
                    . '<span>' . $this->e($s['value'] ?? '') . '</span></div>';
            }
            $html .= '</div></div>';
        }

        // ---- 备注 + 操作 ----
        if ($this->get('notes')) {
            $html .= '<div class="alert alert-light mt-4 mb-0"><i class="ti ti-info-circle me-1"></i>' . $this->e($this->get('notes')) . '</div>';
        }
        $actions = (array) $this->get('actions', []);
        if ($actions) {
            $html .= '<div class="d-flex gap-2 justify-content-end mt-4 d-print-none">';
            foreach ($actions as $a) {
                $attrsExtra = ! empty($a['onclick']) ? ' onclick="' . $this->e($a['onclick']) . '"' : '';
                $html .= '<a href="' . $this->e($a['url'] ?? 'javascript:;') . '" class="btn ' . $this->e($a['class'] ?? 'btn-soft-secondary') . '"' . $attrsExtra . '>'
                    . (! empty($a['icon']) ? '<i class="' . $this->e($a['icon']) . ' me-1"></i>' : '')
                    . $this->e($a['text'] ?? '') . '</a>';
            }
            $html .= '</div>';
        }

        return $html . '</div></div>';
    }
}

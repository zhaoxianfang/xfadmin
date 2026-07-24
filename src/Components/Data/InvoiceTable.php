<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 发票明细表（invoice-details.html）—— 含合计区
 *
 * XfAdmin::invoiceTable([
 *     'items' => [
 *         ['name' => '产品A', 'desc' => '说明', 'qty' => 2, 'price' => 100, 'total' => 200],
 *     ],
 *     'currency' => '¥',
 *     'summary'  => [
 *         ['label' => '小计', 'value' => 200],
 *         ['label' => '合计', 'value' => 220, 'strong' => true],
 *     ],
 * ])
 */
class InvoiceTable extends Component
{
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'currency' => '¥',
            'summary'  => [],
        ];
    }

    protected function html(): string
    {
        $cur = $this->e($this->get('currency'));

        $html = '<div' . $this->attrs(['class' => 'table-responsive']) . '>';
        $html .= '<table class="table table-borderless text-nowrap mb-0">';
        $html .= '<thead class="bg-light bg-opacity-50"><tr>';
        $html .= '<th style="width:40px;">#</th><th>项目</th><th class="text-center">数量</th><th class="text-end">单价</th><th class="text-end">金额</th>';
        $html .= '</tr></thead><tbody>';

        foreach (array_values((array) $this->get('items', [])) as $i => $item) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td><h5 class="mb-0">' . $this->e($item['name'] ?? '') . '</h5>';
            if (! empty($item['desc'])) {
                $html .= '<small class="text-muted">' . $this->e($item['desc']) . '</small>';
            }
            $html .= '</td>';
            $html .= '<td class="text-center">' . $this->e($item['qty'] ?? 1) . '</td>';
            $html .= '<td class="text-end">' . $cur . $this->e(number_format((float) ($item['price'] ?? 0), 2)) . '</td>';
            $total = $item['total'] ?? ((float) ($item['price'] ?? 0) * (float) ($item['qty'] ?? 1));
            $html .= '<td class="text-end">' . $cur . $this->e(number_format((float) $total, 2)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        $summary = (array) $this->get('summary', []);
        if ($summary) {
            $html .= '<div class="d-flex justify-content-end mt-3"><table class="table table-borderless w-auto mb-0">';
            foreach ($summary as $s) {
                $strong = ! empty($s['strong']);
                $html .= '<tr' . ($strong ? ' class="border-top"' : '') . '>';
                $html .= '<td class="text-end pe-4' . ($strong ? ' fw-bold fs-5' : ' text-muted') . '">' . $this->e($s['label'] ?? '') . '</td>';
                $html .= '<td class="text-end' . ($strong ? ' fw-bold fs-5' : '') . '">' . $cur . $this->e(number_format((float) ($s['value'] ?? 0), 2)) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table></div>';
        }

        return $html;
    }
}

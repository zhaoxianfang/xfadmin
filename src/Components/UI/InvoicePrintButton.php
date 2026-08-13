<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 发票打印 / 下载按钮
 *
 * 触发 window.print()（配合打印样式仅打印发票区域），复刻 inspinia apps-invoice 的打印按钮。
 *
 * XfAdmin::invoicePrintButton([
 *     'text'    => '打印 / 下载 PDF',
 *     'variant' => 'primary',
 *     'target'  => '#invoice-print-area',  // 可选：仅打印该区域
 * ])
 */
class InvoicePrintButton extends Component
{
    protected function defaults(): array
    {
        return [
            'text'   => '打印 / 下载 PDF',
            'variant' => 'primary',
            'target' => null,
            'icon'   => 'ti ti-printer',
        ];
    }

    protected function html(): string
    {
        $text    = $this->get('text');
        $variant = $this->enum($this->get('variant'), array_merge(self::ENUM_VARIANT, self::ENUM_VARIANT_OUTLINE), 'primary');
        $icon    = $this->get('icon');
        $target  = $this->get('target');

        $cfg = $target ? ['target' => $target] : [];
        $attrs = $this->initAttrs('print', $cfg);

        $attrStr = '';
        foreach ($attrs as $k => $v) {
            $attrStr .= ' ' . $k . '="' . $this->e($v) . '"';
        }

        return '<button type="button" class="btn btn-' . $variant . '"' . $attrStr . '>'
            . ($icon ? '<i class="' . $this->e($icon) . ' me-1"></i>' : '') . $this->e($text) . '</button>';
    }
}

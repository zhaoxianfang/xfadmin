<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 步骤条（只读进度展示，如订单进度 ecommerce-order-details.html）
 *
 * XfAdmin::stepper([
 *     'steps' => [
 *         ['title' => '已下单', 'text' => '10:00', 'status' => 'done'],
 *         ['title' => '已发货', 'text' => '12:00', 'status' => 'active'],
 *         ['title' => '已签收', 'status' => 'pending'],
 *     ],
 *     'vertical' => false,
 *     'variant'  => 'primary',
 * ])
 */
class Stepper extends Component
{
    protected function defaults(): array
    {
        return [
            'steps'    => [],
            'vertical' => false,
            'variant'  => 'primary',
        ];
    }

    protected function html(): string
    {
        $variant  = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');
        $vertical = (bool) $this->get('vertical');
        $cls = Html::cls('xf-stepper', ['xf-stepper-vertical' => $vertical, 'xf-stepper-horizontal' => ! $vertical]);

        $html = '<div' . $this->attrs(['class' => $cls]) . '>';
        foreach (array_values((array) $this->get('steps', [])) as $i => $s) {
            // 标量（字符串）容错：非数组项按 title 处理，避免 PHP 8 下标访问致命错误
            if (! is_array($s)) {
                $s = ['title' => (string) $s];
            }
            $status = $s['status'] ?? 'pending';
            [$dotCls, $icon] = match ($status) {
                'done'   => ['bg-' . $variant . ' text-white', '<i class="ti ti-check"></i>'],
                'active' => ['bg-' . $variant . '-subtle text-' . $variant . ' border border-' . $variant, (string) ($i + 1)],
                default  => ['bg-light text-muted', (string) ($i + 1)],
            };
            $html .= '<div class="xf-stepper-item ' . $this->e($status) . '">';
            $html .= '<div class="xf-stepper-dot ' . $dotCls . '">' . $icon . '</div>';
            $html .= '<div class="xf-stepper-body"><h5 class="mb-0">' . $this->e($s['title'] ?? '') . '</h5>';
            if (! empty($s['text'])) {
                $html .= '<small class="text-muted">' . $this->e($s['text']) . '</small>';
            }
            $html .= '</div></div>';
        }

        return $html . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

/**
 * 数量步进器
 *
 * − 数字 + 按钮组，含 min / max / step，复刻电商购物车数量控件。
 *
 * XfAdmin::quantityStepper([
 *     'name'  => 'qty',
 *     'value' => 1,
 *     'min'   => 1,
 *     'max'   => 99,
 *     'step'  => 1,
 *     'size'  => 'md',   // sm | md | lg
 * ])
 */
class QuantityStepper extends Component
{
    protected function defaults(): array
    {
        return [
            'name'  => 'qty',
            'value' => 1,
            'min'   => 1,
            'max'   => 99,
            'step'  => 1,
            'size'  => 'md',
        ];
    }

    protected function html(): string
    {
        $name  = $this->get('name');
        $value = (int) $this->get('value');
        $min   = (int) $this->get('min');
        $max   = (int) $this->get('max');
        $step  = (int) $this->get('step');
        $size  = $this->enum($this->get('size'), ['sm', 'md', 'lg'], 'md');
        $id    = $this->resolveId('xf-qty');

        $btnSize = $size === 'sm' ? 'btn-sm' : ($size === 'lg' ? 'btn-lg' : '');
        $inputSize = $size === 'sm' ? 'form-control-sm' : ($size === 'lg' ? 'form-control-lg' : '');

        return '<div class="xf-qty input-group ' . $btnSize . ' w-auto" data-xf="qtyStepper"'
            . ' data-min="' . $min . '" data-max="' . $max . '" data-step="' . $step . '">'
            . '<button type="button" class="btn btn-outline-secondary xf-qty-dec" tabindex="-1">−</button>'
            . '<input type="text" class="form-control ' . $inputSize . ' text-center xf-qty-input" style="width:64px"'
            . ' name="' . $this->e($name) . '" value="' . $value . '" inputmode="numeric" aria-label="' . $this->e($name) . '">'
            . '<button type="button" class="btn btn-outline-secondary xf-qty-inc" tabindex="-1">+</button>'
            . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 加载/忙碌按钮（misc-loading-buttons）—— 点击后显示 spinner，避免重复提交
 *
 * XfAdmin::loadingButton([
 *     'text'    => '保存',
 *     'variant' => 'primary',
 *     'driver'  => 'spinner',   // spinner | ladda
 *     'type'    => 'submit',
 *     'size'    => '',          // lg | sm
 * ])
 */
class LoadingButton extends Component
{
    protected function defaults(): array
    {
        return [
            'text'    => '提交',
            'variant' => 'primary',
            'driver'  => 'spinner',
            'type'    => 'button',
            'size'    => '',
            'name'    => null,
            'value'   => null,
            'icon'    => '',
        ];
    }

    protected function assets(): array
    {
        return $this->get('driver') === 'ladda' ? ['ladda'] : [];
    }

    protected function html(): string
    {
        $id = $this->resolveId('lbtn');
        $driver = $this->get('driver') === 'ladda' ? 'ladda' : 'spinner';
        $size = $this->get('size') ? ' btn-' . $this->enum($this->get('size'), self::ENUM_SIZE, 'lg') : '';
        $icon = $this->get('icon') ? '<i class="' . $this->e($this->get('icon')) . ' me-1"></i>' : '';

        $attrs = [
            'type'    => $this->get('type'),
            'id'      => $id,
            'class'   => Html::cls('btn btn-' . $this->enum($this->get('variant'), array_merge(self::ENUM_VARIANT, self::ENUM_VARIANT_OUTLINE), 'primary') . $size . ' xf-lbtn', $this->get('class')),
            'data-xf' => 'loading-btn',
            'data-driver' => $driver,
        ];
        if ($this->get('name')) {
            $attrs['name'] = $this->get('name');
        }
        if ($this->get('value') !== null) {
            $attrs['value'] = $this->get('value');
        }

        $html = '<button' . $this->attrs($attrs) . '>';
        $html .= '<span class="xf-lbtn-label">' . $icon . $this->e($this->get('text')) . '</span>';
        $html .= '<span class="xf-lbtn-spinner spinner-border spinner-border-sm d-none" role="status"></span>';
        $html .= '</button>';

        return $html;
    }
}

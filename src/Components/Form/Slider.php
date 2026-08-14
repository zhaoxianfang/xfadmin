<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;

/**
 * 范围滑块（noUiSlider）
 *
 * XfAdmin::slider(['name' => 'price', 'label' => '价格区间', 'min' => 0, 'max' => 1000, 'value' => [100, 500], 'tooltips' => true])
 */
class Slider extends Component
{
    use FieldWrapper;

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'min'      => 0,
            'max'      => 100,
            'step'     => 1,
            'tooltips' => false,
            'connect'  => null,
            'options'  => [],  // 透传 noUiSlider 配置
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['nouislider'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id    = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-slider');
        $value = $this->get('value') ?? $this->get('min');

        $config = [
            'start'    => is_array($value) ? array_values($value) : [$value],
            'range'    => ['min' => (float) $this->get('min'), 'max' => (float) $this->get('max')],
            'step'     => (float) $this->get('step'),
            'tooltips' => $this->get('tooltips'),
            'connect'  => $this->get('connect') ?? (is_array($value) ? true : 'lower'),
            'input'    => $this->get('name'),  // 同步到隐藏 input
        ];
        $config = array_replace_recursive($config, (array) $this->get('options', []));

        $control = '<div' . $this->attrs([
            'id'             => $id,
            'data-xf'        => 'slider',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';

        if ($this->get('name')) {
            $control .= '<input type="hidden" name="' . $this->e($this->get('name')) . '" value="'
                . $this->e(is_array($value) ? implode(',', $value) : $value) . '">';
        }
        return $this->wrapField($control, $id);
    }
}

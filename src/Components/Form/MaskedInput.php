<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 输入掩码（Inputmask，form-pickers.html / form-other-plugins.html）
 *
 * XfAdmin::maskedInput([
 *     'name'  => 'phone',
 *     'label' => '手机号',
 *     'mask'  => '999-9999-9999',   // 或 alias: 'email' / 'currency' / 'datetime'
 *     'value' => '',
 *     'placeholder' => '___-____-____',
 * ])
 */
class MaskedInput extends Component
{
    use FieldWrapper;

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['inputmask'];
    }

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'name'        => '',
            'label'       => null,
            'mask'        => null,
            'alias'       => null,
            'value'       => '',
            'placeholder' => null,
            'help'        => null,
            'col'         => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $config = array_filter([
            'mask'  => $this->get('mask'),
            'alias' => $this->get('alias'),
        ], fn ($v) => $v !== null);

        $attrs = $this->initAttrs('inputmask', $config);
        $attrs['type']  = 'text';
        $attrs['class'] = Html::cls('form-control');
        $attrs['name']  = $this->get('name');
        $attrs['value'] = $this->get('value');
        if ($this->get('placeholder')) {
            $attrs['placeholder'] = $this->get('placeholder');
        }
        $id = $this->get('id') ?? $this->uid('xf-mask');
        $attrs['id'] = $id;
        $custom = $this->attributes;
        unset($custom['id']);
        foreach ($custom as $k => $v) {
            $attrs[$k] = $k === 'class' ? Html::cls($attrs['class'] ?? '', $v) : $v;
        }
        return $this->wrapField('<input' . Html::attrs($attrs) . '>', $id);
    }
}

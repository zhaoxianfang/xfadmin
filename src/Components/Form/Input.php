<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 输入框（text/email/password/number/... + 输入掩码 + 标签输入 + 前后缀组）
 *
 * XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true])
 * XfAdmin::input(['name' => 'phone', 'label' => '电话', 'mask' => '999-9999-9999'])
 * XfAdmin::input(['name' => 'tags', 'label' => '标签', 'tags' => true, 'value' => 'php,laravel'])
 * XfAdmin::input(['name' => 'price', 'prepend' => '￥', 'append' => '.00'])
 */
class Input extends Component
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
            'type'    => 'text',
            'size'    => null,     // sm | lg
            'mask'    => null,     // inputmask 表达式
            'tags'    => false,    // tagify 标签输入
            'prepend' => null,
            'append'  => null,
            'min'     => null,
            'max'     => null,
            'step'    => null,
            'pattern' => null,
            'autocomplete' => null,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        $assets = [];
        if ($this->get('mask')) {
            $assets[] = 'inputmask';
        }
        if ($this->get('tags')) {
            $assets[] = 'tagify';
        }
        return $assets;
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id    = $this->resolveId('xf-input');
        $attrs = [
            'type'        => $this->get('type'),
            'class'       => Html::cls('form-control', $this->get('size') ? 'form-control-' . $this->enum($this->get('size'), self::ENUM_SIZE, 'lg') : ''),
            'id'          => $id,
            'name'        => $this->get('name'),
            'value'       => $this->get('value'),
            'placeholder' => $this->get('placeholder'),
            'required'    => (bool) $this->get('required'),
            'disabled'    => (bool) $this->get('disabled'),
            'readonly'    => (bool) $this->get('readonly'),
            'min'         => $this->get('min'),
            'max'         => $this->get('max'),
            'step'        => $this->get('step'),
            'pattern'     => $this->get('pattern'),
            'autocomplete' => $this->get('autocomplete'),
        ];

        if ($this->get('mask')) {
            $attrs['data-xf']        = 'inputmask';
            $attrs['data-xf-config'] = json_encode(['mask' => $this->get('mask')], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        } elseif ($this->get('tags')) {
            $attrs['data-xf'] = 'tagify';
        }
        // 合并自定义属性（id 除外，已消费）
        $custom = $this->attributes;
        unset($custom['id']);
        foreach ($custom as $k => $v) {
            $attrs[$k] = $k === 'class' ? Html::cls($attrs['class'], $v) : $v;
        }
        $control = '<input' . Html::attrs($attrs) . '>';

        if ($this->get('prepend') !== null || $this->get('append') !== null) {
            $control = '<div class="input-group">'
                . ($this->get('prepend') !== null ? '<span class="input-group-text">' . $this->raw($this->get('prepend')) . '</span>' : '')
                . $control
                . ($this->get('append') !== null ? '<span class="input-group-text">' . $this->raw($this->get('append')) . '</span>' : '')
                . '</div>';
        }
        return $this->wrapField($control, $id);
    }
}

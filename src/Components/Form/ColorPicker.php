<?php

declare(strict_types=1);

namespace XfAdmin\Components\Form;

use XfAdmin\Components\Component;
use XfAdmin\Components\Form\Concerns\FieldWrapper;

/**
 * 颜色选择器（Pickr）
 *
 * XfAdmin::colorPicker(['name' => 'color', 'label' => '主题色', 'value' => '#3e60d5', 'theme' => 'classic'])
 */
class ColorPicker extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'theme'   => 'classic',  // classic | monolith | nano
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return ['pickr'];
    }

    protected function html(): string
    {
        $id     = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-color');
        $config = array_replace_recursive([
            'theme'   => $this->get('theme'),
            'default' => $this->get('value') ?: '#3e60d5',
            'input'   => $this->get('name'),
        ], (array) $this->get('options', []));

        $control = '<div class="d-flex align-items-center gap-2">'
            . '<div id="' . $this->e($id) . '" data-xf="pickr" data-xf-config="' . $this->e(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) . '"></div>'
            . ($this->get('name') ? '<input type="hidden" name="' . $this->e($this->get('name')) . '" value="' . $this->e($this->get('value')) . '">' : '')
            . '</div>';

        return $this->wrapField($control, $id);
    }
}

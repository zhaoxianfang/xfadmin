<?php

declare(strict_types=1);

namespace XfAdmin\Components\Form;

use XfAdmin\Components\Component;
use XfAdmin\Components\Form\Concerns\FieldWrapper;
use XfAdmin\Support\Html;

/**
 * 多行文本框
 *
 * XfAdmin::textarea(['name' => 'remark', 'label' => '备注', 'rows' => 4])
 */
class Textarea extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'rows'      => 3,
            'maxlength' => null,
        ];
    }

    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-textarea');

        $control = '<textarea' . $this->attrs([
            'class'       => 'form-control',
            'id'          => $id,
            'name'        => $this->get('name'),
            'rows'        => $this->get('rows'),
            'placeholder' => $this->get('placeholder'),
            'maxlength'   => $this->get('maxlength'),
            'required'    => (bool) $this->get('required'),
            'disabled'    => (bool) $this->get('disabled'),
            'readonly'    => (bool) $this->get('readonly'),
        ]) . '>' . $this->e($this->get('value')) . '</textarea>';

        return $this->wrapField($control, $id);
    }
}

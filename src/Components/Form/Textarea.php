<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 多行文本框
 *
 * XfAdmin::textarea(['name' => 'remark', 'label' => '备注', 'rows' => 4])
 */
class Textarea extends Component
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
            'rows' => 3,
            'maxlength' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-textarea');

        $attrs = [
            'class' => Html::cls('form-control', $this->controlStateClass()),
            'id' => $id,
            'name' => $this->get('name'),
            'rows' => $this->get('rows'),
            'placeholder' => $this->get('placeholder'),
            'maxlength' => $this->get('maxlength'),
            'required' => (bool) $this->get('required'),
            'disabled' => (bool) $this->get('disabled'),
            'readonly' => (bool) $this->get('readonly'),
        ];
        // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义
        $attrs = array_merge($attrs, $this->describeAttrs($id), $this->stateAria());

        $control = '<textarea'.$this->attrs($attrs).'>'.$this->escape($this->get('value')).'</textarea>';

        return $this->wrapField($control, $id);
    }
}

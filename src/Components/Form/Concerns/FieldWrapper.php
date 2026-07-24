<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form\Concerns;

/**
 * 表单字段通用包装：label / 帮助文本 / 校验反馈 / 外层间距
 */
trait FieldWrapper
{
    protected function fieldDefaults(): array
    {
        return [
            'name'        => null,
            'id'          => null,
            'label'       => null,
            'help'        => null,
            'required'    => false,
            'disabled'    => false,
            'readonly'    => false,
            'value'       => null,
            'placeholder' => null,
            'wrapper'     => 'mb-3',   // 外层 class；false 则不渲染外层
            'feedback'    => null,     // ['valid' => '', 'invalid' => '']
        ];
    }

    /** 包装控件：label + control + help + feedback */
    protected function wrapField(string $control, string $forId): string
    {
        $label = '';
        if ($this->get('label') !== null) {
            $label = '<label for="' . $this->e($forId) . '" class="form-label">' . $this->e($this->get('label'))
                . ($this->get('required') ? ' <span class="text-danger">*</span>' : '')
                . '</label>';
        }

        $extra = '';
        $feedback = (array) ($this->get('feedback') ?? []);
        if (isset($feedback['valid'])) {
            $extra .= '<div class="valid-feedback">' . $this->e($feedback['valid']) . '</div>';
        }
        if (isset($feedback['invalid'])) {
            $extra .= '<div class="invalid-feedback">' . $this->e($feedback['invalid']) . '</div>';
        }
        if ($this->get('help') !== null) {
            $extra .= '<div class="form-text">' . $this->raw($this->get('help')) . '</div>';
        }

        $inner = $label . $control . $extra;

        $wrapper = $this->get('wrapper');
        if ($wrapper === false || $wrapper === null) {
            return $inner;
        }

        return '<div class="' . $this->e($wrapper) . '">' . $inner . '</div>';
    }
}

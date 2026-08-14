<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 单日期 / 日期时间选择器（form-pickers.html）
 *
 * 独立于 DateRangePicker，专用于单个日期或日期时间字段。
 * 基于 daterangepicker 的 singleDatePicker 模式。
 *
 * XfAdmin::datePicker(['name' => 'birthday', 'label' => '生日'])
 * XfAdmin::datePicker(['name' => 'meet_at', 'label' => '会议时间', 'timepicker' => true, 'format' => 'YYYY-MM-DD HH:mm'])
 *
 * 扩展性：
 *  - min/max：限制可选日期范围
 *  - options：透传 daterangepicker 原生配置
 *  - prepend / append：在控件前后插入任意 HTML 或组件
 */
class DatePicker extends Component
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
            'timepicker' => false,
            'format'     => 'YYYY-MM-DD',
            'min'        => null,
            'max'        => null,
            'options'    => [],
            'prepend'    => '',
            'append'     => '',
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['daterangepicker'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-datepicker');

        $config = array_replace_recursive([
            'singleDatePicker' => true,
            'timePicker'        => (bool) $this->get('timepicker'),
            'locale'            => ['format' => $this->get('format')],
            'minDate'           => $this->get('min'),
            'maxDate'           => $this->get('max'),
        ], (array) $this->get('options', []));

        $control = (string) $this->get('prepend', '')
            . '<input' . Html::attrs([
                'type'           => 'text',
                'class'          => 'form-control',
                'id'             => $id,
                'name'           => $this->get('name'),
                'value'          => $this->get('value'),
                'placeholder'    => $this->get('placeholder'),
                'required'       => (bool) $this->get('required'),
                'disabled'       => (bool) $this->get('disabled'),
                'readonly'       => (bool) $this->get('readonly'),
                'data-xf'        => 'daterangepicker',
                'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            ]) . '>'
            . (string) $this->get('append', '');

        return $this->wrapField($control, $id);
    }
}

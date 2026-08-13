<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 日期 / 日期范围 / 日期时间选择器（Date Range Picker）
 *
 * XfAdmin::dateRange(['name' => 'date', 'label' => '日期', 'single' => true])
 * XfAdmin::dateRange(['name' => 'range', 'label' => '时段', 'format' => 'YYYY-MM-DD', 'ranges' => true])
 */
class DateRangePicker extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'single'     => false,
            'timepicker' => false,
            'format'     => 'YYYY-MM-DD',
            'ranges'     => false,    // 快捷区间（今天/最近7天/本月...）
            'options'    => [],       // 透传 daterangepicker 配置
        ];
    }

    protected function assets(): array
    {
        return ['daterangepicker'];
    }

    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-daterange');

        $config = array_replace_recursive([
            'singleDatePicker' => (bool) $this->get('single'),
            'timePicker'       => (bool) $this->get('timepicker'),
            'locale'           => ['format' => $this->get('format')],
            'xfRanges'         => (bool) $this->get('ranges'),
        ], (array) $this->get('options', []));

        $control = '<input' . Html::attrs([
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
        ]) . '>';

        return $this->wrapField($control, $id);
    }
}

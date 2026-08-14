<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 下拉选择（原生 / Choices.js 增强 / Select2 增强，支持分组、多选、搜索、远程）
 *
 * XfAdmin::select([
 *     'name'    => 'city',
 *     'label'   => '城市',
 *     'options' => ['bj' => '北京', 'sh' => '上海'],                 // 或 [['value'=>,'label'=>,'disabled'=>]]
 *     'groups'  => ['直辖市' => ['bj' => '北京'], ...],              // 分组
 *     'value'   => 'bj',            // 多选传数组
 *     'multiple'=> false,
 *     'enhance' => 'choices',       // null | choices | select2
 *     'enhance_options' => [],      // 透传给增强插件
 * ])
 */
class Select extends Component
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
            'options'         => [],
            'groups'          => [],
            'multiple'        => false,
            'size'            => null,
            'enhance'         => null,
            'enhance_options' => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return match ($this->get('enhance')) {
            'choices' => ['choices'],
            'select2' => ['select2'],
            default   => [],
        };
    }

    /**
     * render Options（protected实例方法）
     *
     * @param array $options options
     *
     * @return string result
     */
    protected function renderOptions(array $options): string
    {
        $selected = (array) ($this->get('value') ?? []);
        $selected = array_map('strval', $selected);
        $html     = '';

        foreach ($options as $value => $label) {
            if (is_array($label)) {
                $opt   = $label;
                $value = $opt['value'] ?? $value;
                $label = $opt['label'] ?? $value;
            } else {
                $opt = [];
            }
            $html .= '<option' . Html::attrs([
                'value'    => $value,
                'selected' => in_array((string) $value, $selected, true),
                'disabled' => (bool) ($opt['disabled'] ?? false),
            ]) . '>' . $this->e($label) . '</option>';
        }
        return $html;
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-select');

        $attrs = [
            'class'    => Html::cls('form-select', $this->get('size') ? 'form-select-' . $this->enum($this->get('size'), self::ENUM_SIZE, 'lg') : ''),
            'id'       => $id,
            'name'     => $this->get('name') . ($this->get('multiple') && $this->get('name') && ! str_ends_with((string) $this->get('name'), '[]') ? '[]' : ''),
            'multiple' => (bool) $this->get('multiple'),
            'required' => (bool) $this->get('required'),
            'disabled' => (bool) $this->get('disabled'),
        ];

        if ($this->get('enhance')) {
            $enhanceCfg = (array) $this->get('enhance_options', []);
            // 多选原生 select 无空白占位项，Choices/Select2 占位需由插件自身提供；
            // 仅在单选时把 placeholder 注入插件配置，避免「配置有占位却无 DOM 占位项」失效
            if ($this->get('placeholder') && ! $this->get('multiple')) {
                $enhanceCfg['placeholder'] = $this->get('placeholder');
            }
            $attrs['data-xf']        = $this->get('enhance');
            $attrs['data-xf-config'] = json_encode(
                $enhanceCfg,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        }
        $inner = '';
        if ($this->get('placeholder') && ! $this->get('multiple')) {
            $inner .= '<option value="">' . $this->e($this->get('placeholder')) . '</option>';
        }
        $groups = (array) $this->get('groups', []);
        if ($groups !== []) {
            foreach ($groups as $groupLabel => $groupOptions) {
                $inner .= '<optgroup label="' . $this->e($groupLabel) . '">' . $this->renderOptions((array) $groupOptions) . '</optgroup>';
            }
        } else {
            $inner .= $this->renderOptions((array) $this->get('options', []));
        }
        $control = '<select' . Html::attrs($attrs) . '>' . $inner . '</select>';

        return $this->wrapField($control, $id);
    }
}

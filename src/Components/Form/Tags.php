<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 标签输入（Tagify，form-other-plugins.html）
 *
 * XfAdmin::tags([
 *     'name'      => 'tags',
 *     'label'     => '标签',
 *     'value'     => ['php', 'laravel'],
 *     'whitelist' => ['php','laravel','vue','react'],
 *     'max'       => 5,
 *     'placeholder' => '输入后回车',
 * ])
 */
class Tags extends Component
{
    use FieldWrapper;

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['tagify'];
    }

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'name' => '',
            'label' => null,
            'value' => [],
            'whitelist' => [],
            'max' => null,
            'placeholder' => '',
            'help' => null,
            'state' => null,     // 'valid' | 'invalid' | null（显式校验着色，配合服务端回显）
            'col' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $value = $this->get('value');
        $value = is_array($value) ? implode(',', $value) : (string) $value;

        $config = array_filter([
            'whitelist' => $this->get('whitelist') ?: null,
            'maxTags' => $this->get('max'),
        ], fn ($v) => $v !== null && $v !== []);

        $attrs = $this->initAttrs('tagify', $config);
        $attrs['class'] = Html::cls('form-control', $this->controlStateClass());
        $attrs['name'] = $this->get('name');
        $attrs['value'] = $value;
        if ($this->get('placeholder')) {
            $attrs['placeholder'] = $this->get('placeholder');
        }
        $id = $this->get('id') ?? $this->uid('xf-tags');
        $attrs['id'] = $id;
        $custom = $this->attributes;
        unset($custom['id']);
        foreach ($custom as $k => $v) {
            $attrs[$k] = $k === 'class' ? Html::cls($attrs['class'] ?? '', $v) : $v;
        }
        // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义（置于自定义属性之后，确保不被覆盖）
        $attrs = array_merge($attrs, $this->describeAttrs($id), $this->stateAria());
        $input = '<input type="text"'.Html::attrs($attrs).'>';

        return $this->wrapField($input, $id);
    }
}

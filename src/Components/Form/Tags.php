<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Components\Component;
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

    protected function assets(): array
    {
        return ['tagify'];
    }

    protected function defaults(): array
    {
        return [
            'name'        => '',
            'label'       => null,
            'value'       => [],
            'whitelist'   => [],
            'max'         => null,
            'placeholder' => '',
            'help'        => null,
            'col'         => null,
        ];
    }

    protected function html(): string
    {
        $value = $this->get('value');
        $value = is_array($value) ? implode(',', $value) : (string) $value;

        $config = array_filter([
            'whitelist' => $this->get('whitelist') ?: null,
            'maxTags'   => $this->get('max'),
        ], fn ($v) => $v !== null && $v !== []);

        $attrs = $this->initAttrs('tagify', $config);
        $attrs['class'] = Html::cls('form-control');
        $attrs['name']  = $this->get('name');
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
        $input = '<input type="text"' . Html::attrs($attrs) . '>';

        return $this->wrapField($input, $id);
    }
}

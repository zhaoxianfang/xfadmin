<?php

declare(strict_types=1);

namespace XfAdmin\Components\Form;

use XfAdmin\Components\Component;
use XfAdmin\Components\Form\Concerns\FieldWrapper;

/**
 * 富文本编辑器（Quill / Summernote）
 *
 * XfAdmin::editor(['name' => 'content', 'label' => '正文', 'driver' => 'quill', 'theme' => 'snow', 'height' => 300, 'value' => '<p>初始内容</p>'])
 */
class Editor extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'driver'  => 'quill',   // quill | summernote
            'theme'   => 'snow',    // quill: snow | bubble
            'height'  => 260,
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return $this->get('driver') === 'summernote' ? ['summernote'] : ['quill'];
    }

    protected function html(): string
    {
        $id     = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-editor');
        $driver = $this->get('driver');

        $config = array_replace_recursive([
            'driver' => $driver,
            'theme'  => $this->get('theme'),
            'height' => (int) $this->get('height'),
            'input'  => $this->get('name'),
        ], (array) $this->get('options', []));

        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);

        if ($driver === 'summernote') {
            $control = '<textarea' . $this->attrs([
                'id'             => $id,
                'name'           => $this->get('name'),
                'data-xf'        => 'summernote',
                'data-xf-config' => $json,
            ]) . '>' . $this->e($this->get('value')) . '</textarea>';
        } else {
            $control = '<div' . $this->attrs([
                'id'             => $id,
                'data-xf'        => 'quill',
                'data-xf-config' => $json,
                'style'          => 'height:' . (int) $this->get('height') . 'px;',
            ]) . '>' . $this->raw($this->get('value')) . '</div>';
            if ($this->get('name')) {
                $control .= '<input type="hidden" name="' . $this->e($this->get('name')) . '" value="' . $this->e($this->get('value')) . '">';
            }
        }

        return $this->wrapField($control, $id);
    }
}

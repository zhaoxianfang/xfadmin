<?php

declare(strict_types=1);

namespace XfAdmin\Components\Form;

use XfAdmin\Components\Component;
use XfAdmin\Components\Form\Concerns\FieldWrapper;
use XfAdmin\Support\Html;

/**
 * 文件上传（原生 / Dropzone 拖拽 / FilePond）
 *
 * XfAdmin::upload(['name' => 'file', 'label' => '附件'])                                  // 原生
 * XfAdmin::upload(['driver' => 'dropzone', 'url' => '/upload', 'label' => '拖拽上传'])
 * XfAdmin::upload(['driver' => 'filepond', 'name' => 'avatar', 'multiple' => true])
 */
class Upload extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'driver'   => 'native',  // native | dropzone | filepond
            'url'      => null,      // 上传地址
            'multiple' => false,
            'accept'   => null,
            'max_size' => null,      // MB
            'text'     => '点击或拖拽文件到此处上传',
            'options'  => [],
        ];
    }

    protected function assets(): array
    {
        return match ($this->get('driver')) {
            'dropzone' => ['dropzone'],
            'filepond' => ['filepond'],
            default    => [],
        };
    }

    protected function html(): string
    {
        $id     = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-upload');
        $driver = $this->get('driver');

        if ($driver === 'dropzone') {
            $config  = array_replace_recursive([
                'url'           => $this->get('url'),
                'maxFilesize'   => $this->get('max_size'),
                'acceptedFiles' => $this->get('accept'),
                'paramName'     => $this->get('name') ?? 'file',
            ], (array) $this->get('options', []));
            $control = '<div' . $this->attrs([
                'id'             => $id,
                'class'          => 'dropzone',
                'data-xf'        => 'dropzone',
                'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
            ]) . '><div class="dz-message needsclick"><i class="ti ti-cloud-upload h1 text-muted"></i><h4>' . $this->e($this->get('text')) . '</h4></div></div>';

            return $this->wrapField($control, $id);
        }

        if ($driver === 'filepond') {
            $config  = array_replace_recursive([
                'allowMultiple' => (bool) $this->get('multiple'),
                'server'        => $this->get('url'),
                'maxFileSize'   => $this->get('max_size') ? $this->get('max_size') . 'MB' : null,
            ], (array) $this->get('options', []));
            $control = '<input' . Html::attrs([
                'type'           => 'file',
                'id'             => $id,
                'name'           => $this->get('name'),
                'multiple'       => (bool) $this->get('multiple'),
                'accept'         => $this->get('accept'),
                'data-xf'        => 'filepond',
                'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
            ]) . '>';

            return $this->wrapField($control, $id);
        }

        $control = '<input' . Html::attrs([
            'type'     => 'file',
            'class'    => 'form-control',
            'id'       => $id,
            'name'     => $this->get('name'),
            'multiple' => (bool) $this->get('multiple'),
            'accept'   => $this->get('accept'),
            'required' => (bool) $this->get('required'),
            'disabled' => (bool) $this->get('disabled'),
        ]) . '>';

        return $this->wrapField($control, $id);
    }
}

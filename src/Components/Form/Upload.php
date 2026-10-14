<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

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

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return $this->fieldDefaults() + [
            'driver' => 'native',  // native | dropzone | filepond
            'url' => null,      // 上传地址
            'multiple' => false,
            'accept' => null,
            'max_size' => null,      // MB
            'text' => '点击或拖拽文件到此处上传',
            'options' => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return match ($this->get('driver')) {
            'dropzone' => ['dropzone'],
            'filepond' => ['filepond'],
            default => [],
        };
    }

    /**
     * max_size → FilePond maxFileSize 文案（如 '2MB'）。
     *
     * 仅「正数」才输出限制值；0 / '0' / 空值 / null / 非数字 / 负数一律视为
     * 「不限制」，返回 null 不输出该配置——旧写法用真值判断，会把 -5、'abc'
     * 这类非法值也拼成 '-5MB' / 'abcMB' 透传给插件。
     */
    private function maxFileSizeLabel(): ?string
    {
        $v = $this->get('max_size');
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        if (! is_numeric($s) || (float) $s <= 0) {
            return null;
        }

        return $s.'MB';
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-upload');
        $driver = $this->get('driver');

        if ($driver === 'dropzone') {
            // name 为空串时回退通用字段名 file，避免 dropzone 提交空 paramName
            $paramName = (string) $this->get('name') ?: 'file';
            $config = array_replace_recursive([
                'url' => $this->get('url'),
                'maxFilesize' => $this->get('max_size'),
                'acceptedFiles' => $this->get('accept'),
                'paramName' => $paramName,
            ], (array) $this->get('options', []));
            // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义（落到拖拽容器）
            $dzAttrs = $this->controlAttrs([
                'id' => $id,
                'class' => 'dropzone',
                'data-xf' => 'dropzone',
                'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            ], $id);
            $control = '<div'.Html::attrs($dzAttrs).'><div class="dz-message needsclick"><i class="ti ti-cloud-upload h1 text-muted"></i><h4>'.$this->e($this->get('text')).'</h4></div></div>';

            return $this->wrapField($control, $id);
        }
        if ($driver === 'filepond') {
            $config = array_replace_recursive([
                'allowMultiple' => (bool) $this->get('multiple'),
                'server' => $this->get('url'),
                'maxFileSize' => $this->maxFileSizeLabel(),
            ], (array) $this->get('options', []));
            // 不限制时整个移除 maxFileSize 键：输出 "maxFileSize":null 不如不输出
            if (($config['maxFileSize'] ?? null) === null) {
                unset($config['maxFileSize']);
            }
            $fpAttrs = [
                'type' => 'file',
                'id' => $id,
                'name' => $this->get('name'),
                'multiple' => (bool) $this->get('multiple'),
                'accept' => $this->get('accept'),
                'data-xf' => 'filepond',
                'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            ];
            $fpAttrs = array_merge($fpAttrs, $this->describeAttrs($id), $this->stateAria());

            return $this->wrapField('<input'.Html::attrs($fpAttrs).'>', $id);
        }
        $nativeAttrs = [
            'type' => 'file',
            'class' => Html::cls('form-control', $this->controlStateClass()),
            'id' => $id,
            'name' => $this->get('name'),
            'multiple' => (bool) $this->get('multiple'),
            'accept' => $this->get('accept'),
            'required' => (bool) $this->get('required'),
            'disabled' => (bool) $this->get('disabled'),
        ];
        $nativeAttrs = array_merge($nativeAttrs, $this->describeAttrs($id), $this->stateAria());

        $control = '<input'.Html::attrs($nativeAttrs).'>';

        return $this->wrapField($control, $id);
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 富文本编辑器（Quill / Summernote）
 *
 * XfAdmin::editor(['name' => 'content', 'label' => '正文', 'driver' => 'quill', 'theme' => 'snow', 'height' => 300, 'value' => '<p>初始内容</p>'])
 */
class Editor extends Component
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
            'driver' => 'quill',   // quill | summernote
            'theme' => 'snow',    // quill: snow | bubble
            'height' => 260,
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
        // driver 白名单兜底 quill：非法驱动名不会意外加载 summernote 资源
        $driver = $this->enum($this->get('driver'), ['quill', 'summernote'], 'quill');

        return $driver === 'summernote' ? ['summernote'] : ['quill'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-editor');
        // driver 白名单兜底 quill：非法值统一按 quill 渲染，不产生未定义分支
        $driver = $this->enum($this->get('driver'), ['quill', 'summernote'], 'quill');

        $config = array_replace_recursive([
            'driver' => $driver,
            'theme' => $this->get('theme'),
            'height' => (int) $this->get('height'),
            'input' => $this->get('name'),
        ], (array) $this->get('options', []));

        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        if ($driver === 'summernote') {
            $sumAttrs = [
                'id' => $id,
                'name' => $this->get('name'),
                'class' => Html::cls('form-control', $this->controlStateClass()),
                'data-xf' => 'summernote',
                'data-xf-config' => $json,
            ];
            // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义
            $sumAttrs = array_merge($sumAttrs, $this->describeAttrs($id), $this->stateAria());

            $control = '<textarea'.$this->attrs($sumAttrs).'>'.$this->e($this->get('value')).'</textarea>';
        } else {
            $control = '<div'.$this->attrs([
                'id' => $id,
                'data-xf' => 'quill',
                'data-xf-config' => $json,
                'style' => 'height:'.(int) $this->get('height').'px;',
            ]).'>'.$this->raw($this->get('value')).'</div>';
            if ($this->get('name')) {
                // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义（落到真实提交用的隐藏 input）
                $qAttrs = array_merge([
                    'type' => 'hidden',
                    'name' => $this->get('name'),
                    'value' => $this->get('value'),
                ], $this->describeAttrs($id), $this->stateAria());
                $control .= '<input'.Html::attrs($qAttrs).'>';
            }
        }

        return $this->wrapField($control, $id);
    }
}

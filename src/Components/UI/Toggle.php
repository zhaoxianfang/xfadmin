<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 开关控件（基于 Bootstrap form-switch，支持颜色变体/尺寸/标签）
 *
 * 注意：类名用 Toggle（switch 是 PHP 保留字）；对外仍通过别名 switch 调用：
 *   XfAdmin::switch([
 *     'name'     => 'notify',          // 表单字段名
 *     'checked'  => true,              // 是否默认开启
 *     'disabled' => false,
 *     'label'    => '开启通知',         // 右侧文字标签（可空）
 *     'value'    => '1',
 *     'variant'  => 'primary',         // primary/success/danger/warning/info（通过 accent-color 着色）
 *     'size'     => '',                // '' | 'sm' | 'lg'
 *   ])
 */
class Toggle extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'name' => '',
            'checked' => false,
            'disabled' => false,
            'label' => '',
            'value' => '1',
            'variant' => 'primary',
            'size' => '',
            'id' => null,
            'class' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-switch');

        $wrapClass = 'form-check form-switch';
        // size 白名单（sm|lg）：非法/空值都不加尺寸类，防止任意 class 注入
        $size = $this->get('size') !== '' ? $this->enum($this->get('size'), self::ENUM_SIZE, '') : '';
        if ($size !== '') {
            $wrapClass .= ' form-switch-'.$size;
        }
        if ($this->get('class') !== '') {
            $wrapClass .= ' '.$this->get('class');
        }
        $input = '<input id="'.$this->e($id).'" class="form-check-input xf-switch-input" type="checkbox" role="switch"'
            .($this->get('name') !== '' ? ' name="'.$this->e($this->get('name')).'"' : '')
            .' value="'.$this->e((string) $this->get('value')).'"'
            .($this->get('checked') ? ' checked' : '')
            .($this->get('disabled') ? ' disabled' : '')
            // variant 白名单（data-variant 供 JS/CSS 着色），非法值回退默认 primary
            .' data-variant="'.$this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary').'">';

        if ($this->get('label') !== '') {
            return '<div'.$this->attrs(['class' => $wrapClass]).'>'
                .$input
                .'<label class="form-check-label" for="'.$this->e($id).'">'.$this->e($this->get('label')).'</label>'
                .'</div>';
        }

        return '<div'.$this->attrs(['class' => $wrapClass]).'>'.$input.'</div>';
    }
}

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
            'name'     => '',
            'checked'  => false,
            'disabled' => false,
            'label'    => '',
            'value'    => '1',
            'variant'  => 'primary',
            'size'     => '',
            'id'       => null,
            'class'    => '',
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
        if ($this->get('size') !== '') {
            $wrapClass .= ' form-switch-' . $this->get('size');
        }
        if ($this->get('class') !== '') {
            $wrapClass .= ' ' . $this->get('class');
        }
        $input = '<input id="' . $this->e($id) . '" class="form-check-input xf-switch-input" type="checkbox" role="switch"'
            . ($this->get('name') !== '' ? ' name="' . $this->e($this->get('name')) . '"' : '')
            . ' value="' . $this->e((string) $this->get('value')) . '"'
            . ($this->get('checked') ? ' checked' : '')
            . ($this->get('disabled') ? ' disabled' : '')
            . ' data-variant="' . $this->e($this->get('variant')) . '">';

        if ($this->get('label') !== '') {
            return '<div' . $this->attrs(['class' => $wrapClass]) . '>'
                . $input
                . '<label class="form-check-label" for="' . $this->e($id) . '">' . $this->e($this->get('label')) . '</label>'
                . '</div>';
        }
        return '<div' . $this->attrs(['class' => $wrapClass]) . '>' . $input . '</div>';
    }
}

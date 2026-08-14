<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 分割线（带可选文字 / 图标），用于区块之间的视觉分隔
 *
 * XfAdmin::divider([
 *     'text'    => '或',            // 中间文字（留空则为纯线条）
 *     'icon'    => 'ti ti-point',   // 可选中间图标
 *     'variant' => '',              // 可选颜色：primary/success/danger/...（用于线条着色）
 * ])
 */
class Divider extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text'    => '',
            'icon'    => '',
            'variant' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $variant = (string) $this->get('variant');
        $text    = (string) $this->get('text');
        $icon    = (string) $this->get('icon');
        $inner   = '';
        if ($text !== '' || $icon !== '') {
            $inner = '<span class="xf-divider-inner">'
                . ($icon !== '' ? '<i class="' . $this->e($icon) . ' me-1"></i>' : '')
                . $this->e($text) . '</span>';
        }
        $cls = 'xf-divider' . ($variant !== '' ? ' xf-divider-' . $this->e($variant) : '');

        return '<div' . $this->attrs(['class' => $cls]) . '>' . $inner . '</div>';
    }
}

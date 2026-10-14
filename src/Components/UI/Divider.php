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
            'text' => '',
            'icon' => '',
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
        // variant 白名单（拼 xf-divider-* 类）；默认为空（纯线条，不着色），非法值同样不加类
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, '');
        $text = (string) $this->get('text');
        $icon = (string) $this->get('icon');
        $inner = '';
        if ($text !== '' || $icon !== '') {
            $inner = '<span class="xf-divider-inner">'
                .($icon !== '' ? '<i class="'.$this->iconClass($icon).' me-1"></i>' : '')
                .$this->e($text).'</span>';
        }
        $cls = 'xf-divider'.($variant !== '' ? ' xf-divider-'.$variant : '');

        return '<div'.$this->attrs(['class' => $cls]).'>'.$inner.'</div>';
    }
}

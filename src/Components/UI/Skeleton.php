<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 骨架屏占位（加载态），用于内容尚未就绪时的占位提示
 *
 * XfAdmin::skeleton([
 *     'lines'    => 3,        // 文本占位行数（type=text 时生效）
 *     'type'     => 'text',   // text | circle | rect
 *     'width'    => '100%',   // 占位宽度
 *     'height'   => 16,       // rect 时高度
 *     'circle'   => 48,       // circle 直径（覆盖 height）
 *     'animated' => true,     // 是否启用微光动画
 * ])
 */
class Skeleton extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'lines'    => 3,
            'type'     => 'text',
            'width'    => '100%',
            'height'   => 16,
            'circle'   => 48,
            'animated' => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $type     = (string) $this->get('type');
        $animated = $this->get('animated') ? ' xf-skeleton-animated' : '';
        $cls      = 'xf-skeleton' . $animated;

        if ($type === 'circle') {
            $d = (int) $this->get('circle');

            return '<span' . $this->attrs(['class' => $cls . ' xf-skeleton-circle', 'style' => "width:{$d}px;height:{$d}px"]) . '></span>';
        }
        if ($type === 'rect') {
            $h = (int) $this->get('height');
            $w = $this->e($this->get('width'));

            return '<span' . $this->attrs(['class' => $cls . ' xf-skeleton-rect', 'style' => "width:{$w};height:{$h}px"]) . '></span>';
        }
        $n   = max(1, (int) $this->get('lines'));
        $w   = $this->e($this->get('width'));
        $out = '';
        for ($i = 0; $i < $n; $i++) {
            $mw = ($i === $n - 1 && $n > 1) ? '60%' : $w; // 末行略短，更接近真实文本
            // 循环内只输出自身 class/style，根属性（id/class/data-*）由外层 wrap 承载，避免重复 id
            $out .= '<span' . Html::attrs(['class' => $cls . ' xf-skeleton-line', 'style' => "width:{$mw}"]) . '></span>';
        }
        return '<div' . $this->attrs(['class' => 'xf-skeleton-wrap']) . '>' . $out . '</div>';
    }
}

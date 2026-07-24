<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 缎带角标（用于卡片角落标记）
 *
 * XfAdmin::ribbon([
 *     'text'    => '推荐',
 *     'variant' => 'danger',
 *     'position'=> 'left',        // left|right
 *     'body'    => '卡片内容',
 * ])
 */
class Ribbon extends Component
{
    protected function defaults(): array
    {
        return [
            'text'     => '',
            'variant'  => 'primary',
            'position' => 'left',
            'body'     => '',
            'shape'    => null,   // null|round
        ];
    }

    protected function html(): string
    {
        $pos     = $this->get('position') === 'right' ? 'end' : 'start';
        $variant = $this->e($this->get('variant'));
        $round   = $this->get('shape') === 'round' ? ' ribbon-round' : '';

        $html  = '<div' . $this->attrs(['class' => 'position-relative']) . '>';
        $html .= '<div class="ribbon ribbon-' . $pos . ' bg-' . $variant . ' text-white' . $round . '">' . $this->raw($this->get('text')) . '</div>';
        $html .= '<div class="ribbon-content">' . $this->raw($this->get('body')) . '</div>';
        $html .= '</div>';

        return $html;
    }
}

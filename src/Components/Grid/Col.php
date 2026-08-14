<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Grid;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 栅格列
 *
 * XfAdmin::col(['width' => 6, 'content' => ...])
 * XfAdmin::col(['width' => ['md' => 6, 'xl' => 4], 'offset' => ['md' => 3], 'content' => ...])
 */
class Col extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'width'   => null,  // int | 'auto' | [breakpoint => width]
            'offset'  => null,
            'order'   => null,
            'content' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $classes = [];
        $width   = $this->get('width');

        if ($width === null) {
            $classes[] = 'col';
        } elseif (is_array($width)) {
            foreach ($width as $bp => $w) {
                $classes[] = is_int($bp) ? 'col-' . $w : 'col-' . $bp . '-' . $w;
            }
        } else {
            $classes[] = 'col-' . $width;
        }
        foreach ((array) ($this->get('offset') ?? []) as $bp => $o) {
            $classes[] = is_int($bp) ? 'offset-' . $o : 'offset-' . $bp . '-' . $o;
        }
        foreach ((array) ($this->get('order') ?? []) as $bp => $o) {
            $classes[] = is_int($bp) ? 'order-' . $o : 'order-' . $bp . '-' . $o;
        }
        return '<div' . $this->attrs(['class' => Html::cls($classes)]) . '>' . $this->raw($this->get('content')) . '</div>';
    }
}

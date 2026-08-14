<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Grid;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 栅格行
 *
 * XfAdmin::row([
 *     'gutter' => 3,                        // g-3；也可 ['x' => 2, 'y' => 3]
 *     'cols'   => [
 *         ['width' => 6, 'content' => $cardA],                 // col-6
 *         ['width' => ['md' => 6, 'xl' => 4], 'content' => $cardB],
 *         $cardC,                                              // 自动 col
 *     ],
 * ])
 */
class Row extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'gutter'  => null,
            'align'   => null,   // start|center|end  => align-items-*
            'justify' => null,   // start|center|end|between|around => justify-content-*
            'cols'    => [],
            'content' => null,   // 直接传内容（可与 cols 混用）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $classes = ['row'];
        $gutter  = $this->get('gutter');
        if (is_array($gutter)) {
            foreach ($gutter as $axis => $v) {
                $classes[] = 'g' . ($axis === 'x' || $axis === 'y' ? $axis : '') . '-' . $v;
            }
        } elseif ($gutter !== null) {
            $classes[] = 'g-' . $gutter;
        }
        if ($this->get('align')) {
            $classes[] = 'align-items-' . $this->get('align');
        }
        if ($this->get('justify')) {
            $classes[] = 'justify-content-' . $this->get('justify');
        }
        $inner = '';
        foreach ((array) $this->get('cols', []) as $col) {
            if ($col instanceof Col) {
                $inner .= $col->render();
            } elseif (is_array($col)) {
                $inner .= Col::make($col)->render();
            } else {
                $inner .= Col::make(['content' => $col])->render();
            }
        }
        $inner .= $this->raw($this->get('content'));

        return '<div' . $this->attrs(['class' => Html::cls($classes)]) . '>' . $inner . '</div>';
    }
}

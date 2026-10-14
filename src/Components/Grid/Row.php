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
            'gutter' => null,
            'align' => null,   // start|center|end  => align-items-*
            'justify' => null,   // start|center|end|between|around => justify-content-*
            'cols' => [],
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
        $gutter = $this->get('gutter');
        if (is_array($gutter)) {
            foreach ($gutter as $axis => $v) {
                // 轴名限 x/y、值限 0-5 整数：非法项跳过，杜绝任意 class 注入
                if (! in_array($axis, ['x', 'y'], true) || ! is_numeric($v)) {
                    continue;
                }
                $n = (int) $v;
                if ($n < 0 || $n > 5) {
                    continue;
                }
                $classes[] = 'g'.$axis.'-'.$n;
            }
        } elseif ($gutter !== null && is_numeric($gutter)) {
            $n = (int) $gutter;
            if ($n >= 0 && $n <= 5) {
                $classes[] = 'g-'.$n;
            }
        }
        if ($this->get('align')) {
            // align-items-* 白名单（文档值域 start|center|end）
            $align = $this->enum($this->get('align'), ['start', 'center', 'end'], '');
            if ($align !== '') {
                $classes[] = 'align-items-'.$align;
            }
        }
        if ($this->get('justify')) {
            // justify-content-* 白名单（文档值域 start|center|end|between|around）
            $justify = $this->enum($this->get('justify'), ['start', 'center', 'end', 'between', 'around'], '');
            if ($justify !== '') {
                $classes[] = 'justify-content-'.$justify;
            }
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

        return '<div'.$this->attrs(['class' => Html::cls($classes)]).'>'.$inner.'</div>';
    }
}

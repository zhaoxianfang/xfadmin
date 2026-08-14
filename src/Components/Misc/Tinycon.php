<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 浏览器标签角标通知（misc-live-favicon）—— 在 favicon 上显示未读数量
 *
 * XfAdmin::tinycon(['count' => 5, 'color' => '#e63757'])
 * 运行时可更新：XFAdmin.tinycon.set(12)
 */
class Tinycon extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['count' => 0, 'color' => '#e63757', 'background' => '#3e60d5'];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['tinycon'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('tinycon');
        $this->set('id', $id);
        $this->set('data-count', (int) $this->get('count'));
        $this->set('data-color', $this->get('color'));
        $this->set('data-bg', $this->get('background'));
        $html = '<span' . $this->attrs(['id' => $id, 'style' => 'display:none']) . '></span>';

        $js = 'XFAdmin.register("tinycon",function(el){if(!window.Tinycon)return;'
            . 'var c=parseInt(el.getAttribute("data-count")||"0",10);'
            . 'var color=el.getAttribute("data-color"),bg=el.getAttribute("data-bg");'
            . 'if(color)Tinycon.setBubbleColor(color);if(bg)Tinycon.setBubbleBackgroundColor(bg);'
            . 'if(c>0)Tinycon.setBubble(c);'
            . 'XFAdmin.tinycon={set:function(n){Tinycon.setBubble(n);}};});';
        XfAdmin::assets()->inlineJs($js, 'xf-tinycon');

        return $html;
    }
}

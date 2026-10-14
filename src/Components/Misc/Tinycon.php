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
        // data-* 必须直接放入 attrs() 数组才会输出到 DOM（此前仅 set() 写入配置，JS 端 getAttribute 取不到）；
        // attrs() 内部会对值做 HTML 转义，data-count 走 (int) 强转，颜色值直接传入即可。
        // data-xf="tinycon" 是前端扫描入口（XFAdmin.scan 只处理 [data-xf] 元素），缺失会导致注册的处理器永不触发。
        $html = '<span'.$this->attrs([
            'id' => $id,
            'data-xf' => 'tinycon',
            'style' => 'display:none',
            'data-count' => (int) $this->get('count'),
            'data-color' => $this->e($this->get('color')),
            'data-bg' => $this->e($this->get('background')),
        ]).'></span>';

        $js = 'XFAdmin.register("tinycon",function(el){if(!window.Tinycon)return;'
            .'var c=parseInt(el.getAttribute("data-count")||"0",10);'
            .'var color=el.getAttribute("data-color"),bg=el.getAttribute("data-bg");'
            .'if(color)Tinycon.setBubbleColor(color);if(bg)Tinycon.setBubbleBackgroundColor(bg);'
            .'if(c>0)Tinycon.setBubble(c);'
            .'XFAdmin.tinycon={set:function(n){Tinycon.setBubble(n);}};});';
        XfAdmin::assets()->inlineJs($js, 'xf-tinycon');

        return $html;
    }
}

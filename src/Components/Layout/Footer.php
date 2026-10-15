<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 页脚
 *
 * XfAdmin::footer(['text' => '© 2026 XX公司', 'right' => '<a href="#">帮助</a>'])
 */
class Footer extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text' => null,
            'right' => null,
            'container' => 'fluid', // 外层容器：fluid=container-fluid / boxed=container / false=不包裹
            'align' => 'start',     // start | center | end（版权与右侧内容的对齐）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $text = $this->get('text') ?? XfAdmin::setting('footer.text');
        $right = $this->get('right') ?? XfAdmin::setting('footer.right');

        if ($text === null) {
            $brand = XfAdmin::setting('brand.name', 'XfAdmin');
            $text = '© '.date('Y').' '.$brand;
        }
        // 容器与对齐（对齐白名单，非法值回落默认）
        $containerCls = $this->enum($this->get('container'), ['fluid' => 'container-fluid', 'boxed' => 'container', '' => ''], 'container-fluid');
        $align = $this->enum($this->get('align'), ['start' => 'start', 'center' => 'center', 'end' => 'end'], 'start');
        $leftAlign = $align === 'center' ? 'text-center' : 'text-center text-md-'.$align;
        $rightAlign = $align === 'center' ? 'text-md-center' : 'text-md-'.$align;
        $html = '<footer'.$this->attrs(['class' => 'footer']).'>'.($containerCls ? '<div class="'.$containerCls.'">' : '').'<div class="row">';
        // text 是版权/说明文本（默认 "© YYYY 品牌"），语义为纯文本 → 转义；
        // right 常放链接等 HTML → 保持原样输出（调用方需保证可信）
        $html .= '<div class="col-md-6 '.$leftAlign.'">'.$this->text($text).'</div>';
        $html .= '<div class="col-md-6"><div class="'.$rightAlign.' d-none d-md-block">'.$this->raw($right).'</div></div>';
        $html .= '</div>'.($containerCls ? '</div>' : '').'</footer>';

        return $html;
    }
}

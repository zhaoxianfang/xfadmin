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
            'text'  => null,
            'right' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $text  = $this->get('text') ?? XfAdmin::setting('footer.text');
        $right = $this->get('right') ?? XfAdmin::setting('footer.right');

        if ($text === null) {
            $brand = XfAdmin::setting('brand.name', 'XfAdmin');
            $text  = '© ' . date('Y') . ' ' . $brand;
        }
        $html  = '<footer' . $this->attrs(['class' => 'footer']) . '><div class="container-fluid"><div class="row">';
        // text 是版权/说明文本（默认 "© YYYY 品牌"），语义为纯文本 → 转义；
        // right 常放链接等 HTML → 保持原样输出（调用方需保证可信）
        $html .= '<div class="col-md-6 text-center text-md-start">' . $this->text($text) . '</div>';
        $html .= '<div class="col-md-6"><div class="text-md-end d-none d-md-block">' . $this->raw($right) . '</div></div>';
        $html .= '</div></div></footer>';

        return $html;
    }
}

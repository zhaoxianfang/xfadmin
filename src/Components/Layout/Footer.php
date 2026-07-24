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
    protected function defaults(): array
    {
        return [
            'text'  => null,
            'right' => null,
        ];
    }

    protected function html(): string
    {
        $text  = $this->get('text') ?? XfAdmin::setting('footer.text');
        $right = $this->get('right') ?? XfAdmin::setting('footer.right');

        if ($text === null) {
            $brand = XfAdmin::setting('brand.name', 'XfAdmin');
            $text  = '© ' . date('Y') . ' ' . $brand;
        }

        $html  = '<footer' . $this->attrs(['class' => 'footer']) . '><div class="container-fluid"><div class="row">';
        $html .= '<div class="col-md-6 text-center text-md-start">' . $this->raw($text) . '</div>';
        $html .= '<div class="col-md-6"><div class="text-md-end d-none d-md-block">' . $this->raw($right) . '</div></div>';
        $html .= '</div></div></footer>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 返回顶部浮动按钮，滚动超过阈值后出现
 *
 * XfAdmin::backToTop([
 *     'offset' => 300,        // 滚动多少像素后显示
 *     'icon'   => 'ti ti-arrow-up',
 * ])
 */
class BackToTop extends Component
{
    protected function defaults(): array
    {
        return [
            'offset' => 300,
            'icon'   => 'ti ti-arrow-up',
        ];
    }

    protected function html(): string
    {
        $cfg  = ['offset' => (int) $this->get('offset')];
        $icon = (string) $this->get('icon');

        return '<button type="button"' . $this->attrs(['class' => 'xf-back-to-top btn btn-primary rounded-circle shadow'])
            . ' data-xf="backtotop" aria-label="返回顶部" data-xf-config="' . $this->e(json_encode(
                $cfg,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            )) . '"><i class="' . $this->e($icon) . '"></i></button>';
    }
}

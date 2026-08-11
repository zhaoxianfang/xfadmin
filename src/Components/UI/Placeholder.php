<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 骨架占位（Bootstrap Placeholder）—— 加载态占位内容
 *
 * XfAdmin::placeholder([
 *     'lines'   => [12, 6, 8, 12],   // 每行占用的栅格列数
 *     'glow'    => true,             // glow|wave 动画
 *     'animation' => 'glow',
 *     'variant' => null,            // primary/secondary...
 *     'size'    => null,            // xs|sm|lg
 * ])
 * 或直接自定义 body。
 */
class Placeholder extends Component
{
    protected function defaults(): array
    {
        return [
            'lines'     => [12, 8, 10, 6],
            'animation' => 'glow',   // glow|wave|null
            'variant'   => null,
            'size'      => null,     // xs|sm|lg
            'body'      => null,
        ];
    }

    protected function html(): string
    {
        $wrapClass = Html::cls([
            'placeholder-glow' => $this->get('animation') === 'glow',
            'placeholder-wave' => $this->get('animation') === 'wave',
        ]);

        $html = '<div' . $this->attrs(['class' => $wrapClass]) . '>';

        if ($this->get('body') !== null) {
            $html .= $this->raw($this->get('body'));
        } else {
            $sizeCls    = $this->get('size') ? ' placeholder-' . $this->enum($this->get('size'), ['xs', 'sm', 'lg'], 'lg') : '';
            $variantCls = $this->get('variant') ? ' bg-' . $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary') : '';
            foreach ((array) $this->get('lines', []) as $col) {
                $col = max(1, min(12, (int) $col));
                $html .= '<p class="mb-2"><span class="placeholder col-' . $col . $sizeCls . $variantCls . '"></span></p>';
            }
        }

        return $html . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 星级评分（纯展示，无外部依赖）
 *
 * XfAdmin::rating([
 *     'value'   => 3.5,
 *     'max'     => 5,
 *     'variant' => 'warning',
 *     'size'    => null,          // fs-3 等
 *     'show_value' => true,
 *     'count'   => 128,           // 评价数
 * ])
 */
class Rating extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'value'      => 0,
            'max'        => 5,
            'variant'    => 'warning',
            'size'       => null,
            'show_value' => false,
            'count'      => null,
            'icon_full'  => 'ti ti-star-filled',
            'icon_half'  => 'ti ti-star-half-filled',
            'icon_empty' => 'ti ti-star',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $value   = (float) $this->get('value');
        $max     = (int) $this->get('max');
        $variant = $this->e($this->get('variant'));
        $size    = $this->get('size') ? ' ' . $this->e($this->get('size')) : '';

        $html = '<span' . $this->attrs(['class' => Html::cls('d-inline-flex align-items-center gap-1 text-' . $variant)]) . '>';
        for ($i = 1; $i <= $max; $i++) {
            if ($value >= $i) {
                $icon = $this->get('icon_full');
            } elseif ($value >= $i - 0.5) {
                $icon = $this->get('icon_half');
            } else {
                $icon = $this->get('icon_empty');
            }
            $html .= '<i class="' . $this->e($icon) . $size . '"></i>';
        }
        if ($this->get('show_value')) {
            $html .= '<span class="ms-1 text-body fw-medium">' . $this->e(rtrim(rtrim(number_format($value, 1), '0'), '.')) . '</span>';
        }
        if ($this->get('count') !== null) {
            $html .= '<span class="ms-1 text-muted">(' . $this->e($this->get('count')) . ')</span>';
        }
        return $html . '</span>';
    }
}

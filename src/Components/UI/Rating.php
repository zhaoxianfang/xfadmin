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
            'value' => 0,
            'max' => 5,
            'variant' => 'warning',
            'size' => null,
            'show_value' => false,
            'count' => null,
            'icon_full' => 'ti ti-star-filled',
            'icon_half' => 'ti ti-star-half-filled',
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
        $value = (float) $this->get('value');
        // max 夹紧 1~20：防止 0/负数导致不渲染星级、超大值渲染过多图标
        $max = max(1, min(20, (int) $this->get('max')));
        // variant 白名单（拼 text-* 类），非法值回退默认 warning
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'warning');
        $size = $this->get('size') ? ' '.$this->escape($this->get('size')) : '';

        $html = '<span'.$this->attrs(['class' => Html::cls('d-inline-flex align-items-center gap-1 text-'.$variant)]).'>';
        for ($i = 1; $i <= $max; $i++) {
            if ($value >= $i) {
                $icon = $this->get('icon_full');
            } elseif ($value >= $i - 0.5) {
                $icon = $this->get('icon_half');
            } else {
                $icon = $this->get('icon_empty');
            }
            $html .= '<i class="'.$this->escape($icon).$size.'"></i>';
        }
        if ($this->get('show_value')) {
            $html .= '<span class="ms-1 text-body fw-medium">'.$this->escape(rtrim(rtrim(number_format($value, 1), '0'), '.')).'</span>';
        }
        if ($this->get('count') !== null) {
            $html .= '<span class="ms-1 text-muted">('.$this->escape($this->get('count')).')</span>';
        }

        return $html.'</span>';
    }
}

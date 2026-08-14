<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 购物车结算摘要卡
 *
 * 小计 / 运费 / 优惠 / 总计 + 优惠码输入，复刻电商购物车结算侧栏。
 *
 * XfAdmin::cartSummary([
 *     'subtotal'  => 1280.00,
 *     'shipping'  => 20.00,
 *     'discount'  => 100.00,
 *     'currency'  => '￥',
 *     'promo'     => true,         // 是否显示优惠码输入
 *     'button'    => ['text' => '去结算', 'variant' => 'primary'],
 * ])
 */
class CartSummary extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'subtotal' => 0,
            'shipping' => 0,
            'discount' => 0,
            'currency' => '￥',
            'promo'    => true,
            'button'   => ['text' => '去结算', 'variant' => 'primary'],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $cur     = $this->get('currency');
        $sub     = (float) $this->get('subtotal');
        $ship    = (float) $this->get('shipping');
        $disc    = (float) $this->get('discount');
        $promo   = $this->get('promo');
        $btn     = (array) $this->get('button', []);
        $total   = $sub + $ship - $disc;
        $variant = $this->enum($btn['variant'] ?? 'primary', self::ENUM_VARIANT, 'primary');

        $money = fn($v) => $this->e($cur) . number_format($v, 2);

        $html = '<div class="card border-0 shadow-sm">';
        $html .= '<div class="card-header bg-transparent border-bottom fw-semibold">订单摘要</div>';
        $html .= '<div class="card-body">';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">小计</span><span>' . $money($sub) . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">运费</span><span>' . $money($ship) . '</span></div>';
        if ($disc > 0) {
            $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">优惠</span><span class="text-danger">-' . $money($disc) . '</span></div>';
        }
        $html .= '<hr>';
        $html .= '<div class="d-flex justify-content-between mb-3"><span class="fw-semibold">合计</span><span class="fw-bold fs-5">' . $money($total) . '</span></div>';

        if ($promo) {
            $html .= '<div class="input-group mb-3">'
                . '<input type="text" class="form-control" name="promo_code" placeholder="优惠码">'
                . '<button class="btn btn-outline-secondary" type="button">应用</button></div>';
        }
        $html .= '<button type="button" class="btn btn-' . $variant . ' w-100">' . $this->e($btn['text'] ?? '去结算') . '</button>';
        $html .= '</div></div>';

        return $html;
    }
}

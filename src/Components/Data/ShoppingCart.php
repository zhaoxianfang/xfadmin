<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Concerns\HasPriceFormat;
use zxf\XfAdmin\XfAdmin;

/**
 * 购物车列表（apps-ecommerce-cart.html）
 *
 * XfAdmin::shoppingCart([
 *     'items' => [
 *         ['image' => 'products/p1.png', 'name' => '商品名', 'sku' => 'SKU-001', 'price' => 899, 'qty' => 2, 'subtotal' => 1798, 'color' => '黑色', 'size' => 'M'],
 *         // ...
 *     ],
 *     'subtotal' => 1798,
 *     'shipping' => 0,
 *     'tax' => 233.74,
 *     'total' => 2031.74,
 * ])
 */
class ShoppingCart extends Component
{
    use HasPriceFormat;
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items' => [],
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'currency' => '¥',
            'emptyMessage' => '购物车为空',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        $currency = (string) $this->get('currency', '¥');

        if (empty($items)) {
            return '<div class="text-center py-5"><div class="mb-3"><i class="ti ti-shopping-cart fs-48 text-muted"></i></div>'
                . '<h5 class="text-muted">' . $this->e($this->get('emptyMessage')) . '</h5>'
                . '<a href="javascript:void(0)" class="btn btn-primary mt-2">继续购物</a></div>';
        }
        $html = '<div class="row g-4"><div class="col-lg-8">';
        $html .= '<div class="card"><div class="card-body p-0">';
        $html .= '<div class="table-responsive"><table class="table table-borderless mb-0">';
        $html .= '<thead class="border-bottom"><tr>'
            . '<th class="ps-3 py-3" style="width:50%">商品</th>'
            . '<th class="py-3 text-center">单价</th>'
            . '<th class="py-3 text-center">数量</th>'
            . '<th class="py-3 text-end">小计</th>'
            . '<th class="pe-3 py-3" style="width:40px"></th></tr></thead>';
        $html .= '<tbody>';

        foreach ($items as $item) {
            $item = (array) $item;
            $img = !empty($item['image']) ? XfAdmin::img((string) $item['image']) : '';
            $name = (string) ($item['name'] ?? '');
            $sku = (string) ($item['sku'] ?? '');
            $price = $this->formatPrice((float) ($item['price'] ?? 0), $currency);
            $qty = (int) ($item['qty'] ?? 1);
            $subtotal = $this->formatPrice((float) ($item['subtotal'] ?? 0), $currency);
            $color = (string) ($item['color'] ?? '');
            $size = (string) ($item['size'] ?? '');

            $html .= '<tr class="align-middle border-bottom">';
            $html .= '<td class="ps-3 py-3"><div class="d-flex align-items-center gap-3">';
            if ($img) {
                $html .= '<img src="' . $this->e($img) . '" width="64" height="64" class="rounded border object-fit-cover" alt="" style="width:64px;height:64px">';
            }
            $html .= '<div><div class="fw-semibold">' . $this->e($name) . '</div>';
            if ($sku) {
                $html .= '<small class="text-muted">' . $this->e($sku) . '</small>';
            }
            if ($color || $size) {
                $meta = [];
                if ($color) $meta[] = '颜色: ' . $this->e($color);
                if ($size) $meta[] = '尺寸: ' . $this->e($size);
                $html .= '<div><small class="text-muted">' . implode(' / ', $meta) . '</small></div>';
            }
            $html .= '</div></div></td>';
            $html .= '<td class="text-center">' . $price . '</td>';
            $html .= '<td class="text-center"><div class="input-group input-group-sm mx-auto" style="max-width:110px">'
                . '<button class="btn btn-outline-secondary btn-cart-qty-minus" type="button">-</button>'
                . '<input type="text" class="form-control text-center" value="' . $qty . '" readonly>'
                . '<button class="btn btn-outline-secondary btn-cart-qty-plus" type="button">+</button></div></td>';
            $html .= '<td class="text-end fw-semibold">' . $subtotal . '</td>';
            $html .= '<td class="pe-3"><a href="javascript:void(0)" class="text-muted btn-cart-remove" title="移除"><i class="ti ti-x"></i></a></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div></div>';

        // 继续购物
        $html .= '<div class="mt-3"><a href="javascript:void(0)" class="text-decoration-none"><i class="ti ti-arrow-left me-1"></i>继续购物</a></div>';
        $html .= '</div>';

        // 订单摘要
        $html .= '<div class="col-lg-4"><div class="card"><div class="card-body">';
        $html .= '<h5 class="card-title mb-3">订单摘要</h5>';

        $subtotalVal = (float) $this->get('subtotal', 0);
        $shippingVal = (float) $this->get('shipping', 0);
        $taxVal = (float) $this->get('tax', 0);
        $discountVal = (float) $this->get('discount', 0);
        $totalVal = (float) $this->get('total', 0);

        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">商品小计</span>'
            . '<span>' . $this->formatPrice($subtotalVal, $currency) . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">运费</span>'
            . '<span>' . ($shippingVal > 0 ? $this->formatPrice($shippingVal, $currency) : '免运费') . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">税费</span>'
            . '<span>' . $this->formatPrice($taxVal, $currency) . '</span></div>';
        if ($discountVal > 0) {
            $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">优惠</span>'
                . '<span class="text-success">-' . $this->formatPrice($discountVal, $currency) . '</span></div>';
        }
        $html .= '<hr>';
        $html .= '<div class="d-flex justify-content-between mb-3"><span class="fw-bold">合计</span>'
            . '<span class="fw-bold fs-5">' . $this->formatPrice($totalVal, $currency) . '</span></div>';

        // 优惠码
        $html .= '<div class="mb-3"><div class="input-group"><input type="text" class="form-control" placeholder="优惠码">'
            . '<button class="btn btn-outline-secondary" type="button">使用</button></div></div>';

        $html .= '<a href="javascript:void(0)" class="btn btn-primary w-100">去结算</a>';
        $html .= '</div></div></div></div>';

        return $html;
    }
}

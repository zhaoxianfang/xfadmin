<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Concerns\HasPriceFormat;
use zxf\XfAdmin\XfAdmin;

/**
 * 电商结算页（apps-ecommerce-checkout.html）
 *
 * XfAdmin::checkout([
 *     'steps' => [
 *         ['label' => 'Billing info', 'icon' => 'ti-home', 'done' => true],
 *         ['label' => 'Shipping info', 'icon' => 'ti-truck-delivery', 'active' => true],
 *         ['label' => 'Payment info', 'icon' => 'ti-credit-card'],
 *         ['label' => 'Finish', 'icon' => 'ti-check'],
 *     ],
 *     'orderSummary' => [
 *         'subtotal' => 1798.00,
 *         'shipping' => 29.00,
 *         'tax' => 233.74,
 *         'total' => 2060.74,
 *     ],
 *     'currency' => '$',
 *     'currentStep' => 1, // zero-indexed
 * ])
 */
class Checkout extends Component
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
            'steps' => [],
            'orderSummary' => [
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0,
            ],
            'currency' => '¥',
            'currentStep' => 0,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $steps = (array) $this->get('steps', []);
        $summary = (array) $this->get('orderSummary', []);
        $currency = (string) $this->get('currency', '¥');
        $currentStep = (int) $this->get('currentStep', 0);

        if (empty($steps)) {
            $steps = [
                ['label' => '账单信息', 'icon' => 'ti-home'],
                ['label' => '配送信息', 'icon' => 'ti-truck-delivery'],
                ['label' => '支付信息', 'icon' => 'ti-credit-card'],
                ['label' => '完成', 'icon' => 'ti-check'],
            ];
        }
        $html = '<div class="row g-4"><div class="col-lg-8">';

        // 步骤指示器
        $html .= '<div class="card"><div class="card-body py-3"><div class="xf-checkout-steps d-flex align-items-center">';
        foreach ($steps as $i => $step) {
            $step = (array) $step;
            $icon = (string) ($step['icon'] ?? 'ti-circle');
            $label = (string) ($step['label'] ?? '');
            $isActive = $i === $currentStep;
            $isDone = ($i < $currentStep) || !empty($step['done']);

            if ($i > 0) {
                $lineClass = $isDone ? ' bg-primary' : ' bg-light';
                $html .= '<div class="flex-grow-1 mx-2" style="height:2px"><div class="h-100' . $lineClass . '"></div></div>';
            }
            $badgeClass = $isDone ? 'bg-primary' : ($isActive ? 'bg-primary' : 'bg-light text-muted');
            $textClass = $isActive ? 'fw-semibold text-dark' : ($isDone ? 'text-primary' : 'text-muted');
            $html .= '<div class="d-flex flex-column align-items-center"><div class="rounded-circle d-inline-flex align-items-center justify-content-center '
                . $badgeClass . '" style="width:36px;height:36px"><i class="ti ' . $this->e($icon) . '"></i></div>';
            $html .= '<small class="mt-1 ' . $textClass . '">' . $this->e($label) . '</small></div>';
        }
        $html .= '</div></div></div>';

        // 账单信息表单（step 0）
        $html .= '<div class="card mt-3"><div class="card-header"><h5 class="mb-0">账单信息</h5></div><div class="card-body"><div class="row g-3">';
        $html .= '<div class="col-md-6"><label class="form-label">名</label><input type="text" class="form-control" placeholder="First name"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">姓</label><input type="text" class="form-control" placeholder="Last name"></div>';
        $html .= '<div class="col-12"><label class="form-label">地址</label><input type="text" class="form-control" placeholder="Street address"></div>';
        $html .= '<div class="col-md-4"><label class="form-label">国家</label><select class="form-select"><option>中国</option><option>美国</option></select></div>';
        $html .= '<div class="col-md-4"><label class="form-label">省/州</label><select class="form-select"><option>选择省/州</option></select></div>';
        $html .= '<div class="col-md-4"><label class="form-label">邮编</label><input type="text" class="form-control" placeholder="ZIP"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">电子邮箱</label><input type="email" class="form-control" placeholder="email@example.com"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">手机号</label><input type="text" class="form-control" placeholder="+86"></div>';
        $html .= '<div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="sameAsShipping"><label class="form-check-label" for="sameAsShipping">配送地址与账单地址相同</label></div></div>';
        $html .= '</div></div></div>';

        // 操作按钮
        $html .= '<div class="d-flex justify-content-between mt-3">';
        $html .= '<button class="btn btn-outline-secondary">上一步</button>';
        $html .= '<button class="btn btn-primary">下一步</button>';
        $html .= '</div>';

        $html .= '</div>';

        // 右侧订单摘要
        $html .= '<div class="col-lg-4">';
        $html .= $this->renderOrderSummary($summary, $currency);
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * render Order Summary（private实例方法）
     *
     * @param array $summary summary
     * @param string $currency currency
     *
     * @return string result
     */
    private function renderOrderSummary(array $summary, string $currency): string
    {
        $subtotal = (float) ($summary['subtotal'] ?? 0);
        $shipping = (float) ($summary['shipping'] ?? 0);
        $tax = (float) ($summary['tax'] ?? 0);
        $discount = (float) ($summary['discount'] ?? 0);
        $total = (float) ($summary['total'] ?? 0);

        $html = '<div class="card"><div class="card-body"><h5 class="card-title mb-3">订单摘要</h5>';

        // 简化的订单项
        $items = (array) ($summary['items'] ?? []);
        if (!empty($items)) {
            $html .= '<div class="mb-3">';
            foreach ($items as $item) {
                $item = (array) $item;
                $html .= '<div class="d-flex justify-content-between mb-2"><span class="small">'
                    . $this->e($item['name'] ?? '') . ' x' . (int) ($item['qty'] ?? 1) . '</span>'
                    . '<span>' . $this->formatPrice((float) ($item['price'] ?? 0), $currency) . '</span></div>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">小计</span><span>' . $this->formatPrice($subtotal, $currency) . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">运费</span><span>' . ($shipping > 0 ? $this->formatPrice($shipping, $currency) : '免运费') . '</span></div>';
        $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">税费</span><span>' . $this->formatPrice($tax, $currency) . '</span></div>';
        if ($discount > 0) {
            $html .= '<div class="d-flex justify-content-between mb-2"><span class="text-muted">优惠</span><span class="text-success">-' . $this->formatPrice($discount, $currency) . '</span></div>';
        }
        $html .= '<hr>';
        $html .= '<div class="d-flex justify-content-between mb-3"><span class="fw-bold">合计</span><span class="fw-bold fs-5">' . $this->formatPrice($total, $currency) . '</span></div>';

        $html .= '<a href="javascript:void(0)" class="btn btn-primary w-100">确认支付</a>';
        $html .= '</div></div>';

        return $html;
    }
}

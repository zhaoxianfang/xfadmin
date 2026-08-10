<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 订单详情（ecommerce-order-details.html）
 *
 * XfAdmin::orderDetails([
 *     'order' => [
 *         'id' => '#ORD-1001', 'date' => '2026-07-20', 'status' => 'completed',
 *         'customer' => ['name'=>'张三','email'=>'z@x.com','phone'=>'138…','address'=>'北京市…'],
 *         'items' => [['name'=>'商品A','sku'=>'SKU-1','qty'=>2,'price'=>'¥99','image'=>'products/1.png']],
 *         'subtotal' => '¥198','discount' => '-¥10','shipping' => '¥0','tax' => '¥0','total' => '¥188',
 *         'timeline' => [['title'=>'已下单','time'=>'07-20 10:00','done'=>true],['title'=>'已发货','time'=>'07-21','done'=>true]],
 *         'notes' => '请尽快发货',
 *     ],
 * ])
 */
class OrderDetails extends Component
{
    protected function defaults(): array
    {
        return ['order' => []];
    }

    private const STATUS = [
        'pending'    => 'warning',
        'processing' => 'info',
        'completed'  => 'success',
        'refunded'   => 'purple',
        'cancelled'  => 'danger',
    ];

    protected function html(): string
    {
        $o = (array) $this->get('order', []);
        if (empty($o)) {
            return '';
        }

        $status = $o['status'] ?? 'pending';
        $sCls   = self::STATUS[$status] ?? 'secondary';

        $html = '<div class="row g-3">';

        // 左：商品 + 时间线
        $html .= '<div class="col-lg-8">';

        // 商品表
        $html .= '<div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center">'
            . '<h6 class="mb-0">订单 ' . $this->e($o['id'] ?? '') . '</h6>'
            . '<span class="badge bg-' . $sCls . '-subtle text-' . $sCls . '">' . $this->e($o['status_text'] ?? $status) . '</span>'
            . '</div><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>商品</th><th>单价</th><th>数量</th><th class="text-end">小计</th></tr></thead><tbody>';

        foreach ((array) ($o['items'] ?? []) as $it) {
            $it    = (array) $it;
            $image = ! empty($it['image']) ? \zxf\XfAdmin\XfAdmin::img((string) $it['image']) : '';
            $html .= '<tr>';
            $html .= '<td><div class="d-flex align-items-center"><img src="' . $this->e($image) . '" width="44" height="44" class="rounded me-2 object-fit-cover" alt="" style="width:44px;height:44px;"><div><div class="fw-semibold">' . $this->e($it['name'] ?? '') . '</div><small class="text-muted">' . $this->e($it['sku'] ?? '') . '</small></div></div></td>';
            $html .= '<td>' . $this->e($it['price'] ?? '') . '</td>';
            $html .= '<td>' . (int) ($it['qty'] ?? 1) . '</td>';
            $html .= '<td class="text-end fw-semibold">' . $this->e($it['amount'] ?? $it['price'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div></div>';

        // 时间线
        if (! empty($o['timeline'])) {
            $html .= '<div class="card"><div class="card-header"><h6 class="mb-0">物流进度</h6></div><div class="card-body">';
            $html .= '<div class="timeline-x">';
            foreach ((array) $o['timeline'] as $t) {
                $t     = (array) $t;
                $done  = ! empty($t['done']);
                $html .= '<div class="timeline-item' . ($done ? ' is-done' : '') . '">';
                $html .= '<div class="timeline-marker"></div>';
                $html .= '<div class="timeline-content"><div class="fw-semibold">' . $this->e($t['title'] ?? '') . '</div>';
                if (! empty($t['time'])) {
                    $html .= '<small class="text-muted">' . $this->e($t['time']) . '</small>';
                }
                $html .= '</div></div>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        // 右：客户 + 金额
        $html .= '<div class="col-lg-4">';
        $c = (array) ($o['customer'] ?? []);
        if ($c) {
            $html .= '<div class="card mb-3"><div class="card-header"><h6 class="mb-0">客户信息</h6></div><div class="card-body small">';
            $html .= '<div class="mb-2"><span class="text-muted">姓名：</span>' . $this->e($c['name'] ?? '') . '</div>';
            $html .= '<div class="mb-2"><span class="text-muted">邮箱：</span>' . $this->e($c['email'] ?? '') . '</div>';
            if (! empty($c['phone'])) {
                $html .= '<div class="mb-2"><span class="text-muted">电话：</span>' . $this->e($c['phone']) . '</div>';
            }
            if (! empty($c['address'])) {
                $html .= '<div><span class="text-muted">地址：</span>' . $this->e($c['address']) . '</div>';
            }
            $html .= '</div></div>';
        }
        $html .= '<div class="card"><div class="card-header"><h6 class="mb-0">金额汇总</h6></div><div class="card-body">';
        $html .= $this->amountRow('小计', $o['subtotal'] ?? '');
        $html .= $this->amountRow('优惠', $o['discount'] ?? '', 'text-danger');
        $html .= $this->amountRow('运费', $o['shipping'] ?? '');
        $html .= $this->amountRow('税费', $o['tax'] ?? '');
        $html .= '<hr><div class="d-flex justify-content-between fw-bold"><span>合计</span><span class="text-primary">' . $this->e($o['total'] ?? '') . '</span></div>';
        if (! empty($o['notes'])) {
            $html .= '<div class="alert alert-light mt-3 mb-0 small">' . $this->e($o['notes']) . '</div>';
        }
        $html .= '</div></div></div>';

        return $html . '</div>';
    }

    private function amountRow(string $label, $value, string $cls = ''): string
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return '<div class="d-flex justify-content-between mb-2"><span class="text-muted">' . $this->e($label) . '</span><span class="' . $cls . '">' . $this->e($value) . '</span></div>';
    }
}

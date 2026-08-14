<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 卖家 / 店铺详情页（对齐 INSPINIA ecommerce-seller-details.html）
 *
 * 上部店铺资料卡（Logo、店名、认证标、评分、联系方式、操作按钮）
 * + 统计卡行（销售额 / 订单 / 商品 / 好评率）
 * + 在售商品表。
 *
 * XfAdmin::sellerDetails([
 *     'seller' => [
 *         'name'     => '极物优选旗舰店',
 *         'logo'     => 'sellers/1.png',
 *         'verified' => true,
 *         'rating'   => 4.8,
 *         'desc'     => '专注数码周边十年。',
 *         'meta'     => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
 *     ],
 *     'stats'    => [['label' => '总销售额', 'value' => '¥128 万', 'icon' => 'ti ti-currency-yen', 'color' => 'primary']],
 *     'products' => [['name' => '蓝牙耳机', 'image' => 'products/2.png', 'price' => '¥299', 'stock' => 320, 'sales' => '1.2k', 'status' => ['text' => '在售', 'variant' => 'success']]],
 * ])
 */
class SellerDetails extends Component
{
    /** 默认配置 */
    protected function defaults(): array
    {
        return [
            'seller'   => [],
            'stats'    => [],
            'products' => [],
            'actions'  => [],
        ];
    }

    /** 渲染卖家详情页 */
    protected function html(): string
    {
        $seller = (array) $this->get('seller', []);
        if (empty($seller)) {
            return '';
        }
        $html = '<div' . $this->attrs(['class' => 'xf-seller-details']) . '>';
        $html .= $this->profileCard($seller);
        $html .= $this->statsRow((array) $this->get('stats', []));
        $html .= $this->productsTable((array) $this->get('products', []));

        return $html . '</div>';
    }

    /** 店铺资料卡 */
    protected function profileCard(array $s): string
    {
        $html = '<div class="card"><div class="card-body"><div class="d-flex flex-wrap align-items-center gap-3">';

        if (! empty($s['logo'])) {
            $html .= '<div class="avatar avatar-xxl flex-shrink-0"><img src="' . $this->e($this->img($s['logo'])) . '" alt="" class="img-fluid rounded"></div>';
        }
        $html .= '<div class="flex-grow-1">';
        $html .= '<h4 class="mb-1 fw-bold">' . $this->e($s['name'] ?? '');
        if (! empty($s['verified'])) {
            $html .= ' <i class="ti ti-rosette-discount-check-filled text-primary" title="已认证"></i>';
        }
        $html .= '</h4>';

        // 评分
        if (isset($s['rating'])) {
            $rate = (float) $s['rating'];
            $full = (int) floor($rate);
            $html .= '<div class="text-warning mb-1">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= '<i class="ti ti-star' . ($i <= $full ? '-filled' : '') . '"></i>';
            }
            $html .= ' <span class="text-muted fs-sm">' . $this->e((string) $rate) . '</span></div>';
        }
        if (! empty($s['desc'])) {
            $html .= '<p class="text-muted mb-2">' . $this->e($s['desc']) . '</p>';
        }
        // 元信息（地址 / 电话 / 邮箱等）
        $meta = (array) ($s['meta'] ?? []);
        if (! empty($meta)) {
            $html .= '<div class="d-flex flex-wrap gap-3 text-muted fs-sm">';
            foreach ($meta as $m) {
                $html .= '<span>' . (! empty($m['icon']) ? '<i class="' . $this->e($m['icon']) . ' me-1"></i>' : '') . $this->e($m['text'] ?? '') . '</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // 操作按钮
        $actions = (array) $this->get('actions', []);
        if (empty($actions)) {
            $actions = [
                ['text' => '联系卖家', 'class' => 'btn-primary', 'icon' => 'ti ti-message'],
                ['text' => '关注店铺', 'class' => 'btn-soft-secondary', 'icon' => 'ti ti-heart'],
            ];
        }
        $html .= '<div class="d-flex gap-2 align-self-start">';
        foreach ($actions as $a) {
            $html .= '<button type="button" class="btn ' . $this->e($a['class'] ?? 'btn-primary') . '"'
                . (! empty($a['onclick']) ? ' onclick="' . $this->e($a['onclick']) . '"' : '') . '>'
                . (! empty($a['icon']) ? '<i class="' . $this->e($a['icon']) . ' me-1"></i>' : '')
                . $this->e($a['text'] ?? '') . '</button>';
        }
        $html .= '</div>';

        return $html . '</div></div></div>';
    }

    /** 统计卡行 */
    protected function statsRow(array $stats): string
    {
        if (empty($stats)) {
            return '';
        }
        $col  = 'col-md-' . intdiv(12, max(1, min(4, count($stats))));
        $html = '<div class="row g-3 mb-3">';
        foreach ($stats as $st) {
            $st    = (array) $st;
            $color = $this->e($st['color'] ?? 'primary');
            $html .= '<div class="' . $col . '"><div class="card mb-0"><div class="card-body d-flex align-items-center gap-3">'
                . '<div class="avatar avatar-lg flex-shrink-0"><span class="avatar-title rounded-circle bg-' . $color . '-subtle text-' . $color . ' fs-4">'
                . '<i class="' . $this->e($st['icon'] ?? 'ti ti-chart-bar') . '"></i></span></div>'
                . '<div><h4 class="mb-0 fw-bold">' . $this->e((string) ($st['value'] ?? '')) . '</h4>'
                . '<span class="text-muted fs-sm">' . $this->e($st['label'] ?? '') . '</span></div>'
                . '</div></div></div>';
        }
        return $html . '</div>';
    }

    /** 在售商品表 */
    protected function productsTable(array $products): string
    {
        if (empty($products)) {
            return '';
        }
        $html = '<div class="card mb-0"><div class="card-header"><h5 class="card-title mb-0">在售商品</h5></div>'
            . '<div class="card-body p-0"><div class="table-responsive">'
            . '<table class="table table-custom table-centered table-hover w-100 mb-0">'
            . '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">'
            . '<th>商品</th><th>价格</th><th>库存</th><th>销量</th><th>状态</th><th class="text-center">操作</th>'
            . '</tr></thead><tbody>';

        foreach ($products as $p) {
            $p = (array) $p;
            $html .= '<tr><td><div class="d-flex align-items-center">';
            if (! empty($p['image'])) {
                $html .= '<div class="avatar-md me-3"><img src="' . $this->e($this->img($p['image'])) . '" alt="" class="img-fluid rounded"></div>';
            }
            $html .= '<h5 class="mb-0 fs-base"><a href="' . $this->e($p['url'] ?? '#') . '" class="link-reset">' . $this->e($p['name'] ?? '') . '</a></h5>';
            $html .= '</div></td>';
            $html .= '<td class="fw-medium">' . $this->e((string) ($p['price'] ?? '')) . '</td>';
            $html .= '<td>' . $this->e((string) ($p['stock'] ?? '')) . '</td>';
            $html .= '<td>' . $this->e((string) ($p['sales'] ?? '')) . '</td>';

            $status  = $p['status'] ?? null;
            $text    = is_array($status) ? ($status['text'] ?? '') : (string) $status;
            $variant = is_array($status) ? ($status['variant'] ?? 'secondary') : 'secondary';
            $html   .= '<td>' . ($text !== ''
                ? '<span class="badge bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' fs-xxs">' . $this->e($text) . '</span>'
                : '') . '</td>';

            $html .= '<td><div class="d-flex justify-content-center gap-1">'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>'
                . '</div></td></tr>';
        }
        return $html . '</tbody></table></div></div></div>';
    }
}

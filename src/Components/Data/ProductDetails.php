<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 商品详情（ecommerce-product-details.html）
 *
 * XfAdmin::productDetails([
 *     'product' => [
 *         'name' => '无线降噪耳机', 'sku' => 'SKU-001', 'price' => '¥899', 'old_price' => '¥1099',
 *         'rating' => 4.5, 'reviews' => 128, 'stock' => '有货',
 *         'images' => ['products/1.png','products/2.png'],
 *         'description' => '...', 'features' => ['主动降噪','40h 续航'],
 *         'variants' => ['color' => ['黑','白','蓝'], 'size' => ['S','M','L']],
 *         'category' => '数码', 'brand' => 'Acme',
 *         'tabs' => [['title'=>'规格','body'=>'...'],['title'=>'评价','body'=>'...']],
 *         'related' => [ ['title'=>..,'price'=>..,'image'=>..], ... ],
 *     ],
 * ])
 */
class ProductDetails extends Component
{
    protected function defaults(): array
    {
        return ['product' => []];
    }

    protected function html(): string
    {
        $p = (array) $this->get('product', []);
        if (empty($p)) {
            return '';
        }

        $images = (array) ($p['images'] ?? []);
        $main   = $images[0] ?? ($p['image'] ?? '');
        $mainUrl = $main ? \zxf\XfAdmin\XfAdmin::img((string) $main) : '';

        $html = '<div class="row g-4" data-pd-gallery>';

        // 画廊（主图用 class 定位、事件委托切换，支持同页多实例互不干扰）
        $html .= '<div class="col-lg-5">';
        $html .= '<div class="border rounded overflow-hidden mb-2"><img src="' . $this->e($mainUrl) . '" class="w-100 object-fit-cover pd-main" alt=""></div>';
        if (count($images) > 1) {
            $html .= '<div class="d-flex gap-2 flex-wrap">';
            foreach ($images as $img) {
                $url = \zxf\XfAdmin\XfAdmin::img((string) $img);
                $html .= '<img src="' . $this->e($url) . '" width="64" height="64" class="rounded border pd-thumb object-fit-cover" style="width:64px;height:64px;cursor:pointer" alt="" data-full="' . $this->e($url) . '">';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // 信息
        $html .= '<div class="col-lg-7">';
        $html .= '<small class="text-primary fw-semibold">' . $this->e($p['category'] ?? '') . '</small>';
        $html .= '<h3 class="mt-1">' . $this->e($p['name'] ?? '') . '</h3>';

        if (! empty($p['rating']) || ! empty($p['reviews'])) {
            $html .= '<div class="d-flex align-items-center gap-2 mb-3">';
            $html .= $this->stars((float) ($p['rating'] ?? 0));
            if (! empty($p['reviews'])) {
                $html .= '<small class="text-muted">' . $this->e($p['rating'] ?? 0) . ' · ' . (int) $p['reviews'] . ' 条评价</small>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="d-flex align-items-baseline gap-2 mb-3">';
        $html .= '<span class="fs-3 fw-bold text-primary">' . $this->e($p['price'] ?? '') . '</span>';
        if (! empty($p['old_price'])) {
            $html .= '<span class="text-muted text-decoration-line-through">' . $this->e($p['old_price']) . '</span>';
        }
        $html .= '</div>';

        if (! empty($p['stock'])) {
            $html .= '<div class="mb-3"><span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>' . $this->e($p['stock']) . '</span></div>';
        }

        if (! empty($p['description'])) {
            $html .= '<p class="text-muted">' . $this->e($p['description']) . '</p>';
        }

        // 变体
        foreach ((array) ($p['variants'] ?? []) as $label => $opts) {
            $opts  = (array) $opts;
            $isColor = $label === 'color' || $label === '颜色';
            $html .= '<div class="mb-3"><span class="text-muted small d-block mb-1">' . $this->e($label) . '</span><div class="d-flex gap-2 flex-wrap">';
            foreach ($opts as $opt) {
                if ($isColor) {
                    $html .= '<button class="btn btn-sm rounded-circle p-0 border" style="width:28px;height:28px;background:' . $this->e($opt) . '" title="' . $this->e($opt) . '"></button>';
                } else {
                    $html .= '<button class="btn btn-sm btn-outline-secondary">' . $this->e($opt) . '</button>';
                }
            }
            $html .= '</div></div>';
        }

        $html .= '<div class="d-flex gap-2 align-items-center mb-3">';
        $html .= '<div class="input-group input-group-sm" style="width:120px"><button class="btn btn-outline-secondary" type="button">-</button><input type="text" class="form-control text-center" value="1"><button class="btn btn-outline-secondary" type="button">+</button></div>';
        $html .= '<button class="btn btn-primary"><i class="ti ti-shopping-cart me-1"></i>加入购物车</button>';
        $html .= '<button class="btn btn-outline-danger btn-icon" title="收藏"><i class="ti ti-heart"></i></button>';
        $html .= '</div>';

        if (! empty($p['features'])) {
            $html .= '<ul class="list-inline mb-0">';
            foreach ((array) $p['features'] as $f) {
                $html .= '<li class="list-inline-item"><span class="badge bg-light text-dark"><i class="ti ti-circle-check text-success me-1"></i>' . $this->e($f) . '</span></li>';
            }
            $html .= '</ul>';
        }
        $html .= '</div></div>';

        // Tabs
        if (! empty($p['tabs'])) {
            $html .= $this->tabs((array) $p['tabs']);
        }

        // 相关商品
        if (! empty($p['related'])) {
            $html .= '<div class="mt-2"><h5 class="mb-3">相关推荐</h5><div class="row g-3">';
            foreach ((array) $p['related'] as $r) {
                $r     = (array) $r;
                $rImg  = ! empty($r['image']) ? \zxf\XfAdmin\XfAdmin::img((string) $r['image']) : '';
                $html .= '<div class="col-lg-3 col-6"><div class="card h-100"><img src="' . $this->e($rImg) . '" class="card-img-top object-fit-cover" height="140" alt="" style="height:140px;"><div class="card-body p-2"><div class="fw-semibold small text-truncate">' . $this->e($r['title'] ?? '') . '</div><div class="text-primary small fw-bold">' . $this->e($r['price'] ?? '') . '</div></div></div></div>';
            }
            $html .= '</div></div>';
        }

        return $html;
    }

    private function stars(float $rating): string
    {
        $html = '<span class="text-warning">';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= '<i class="ti ti-star-filled"></i>';
            } elseif ($rating >= $i - 0.5) {
                $html .= '<i class="ti ti-star-half-filled"></i>';
            } else {
                $html .= '<i class="ti ti-star"></i>';
            }
        }

        return $html . '</span>';
    }

    private function tabs(array $tabs): string
    {
        // 每个实例独立的 tab id 前缀，避免同页多实例互相切换
        $prefix = $this->uid('xf-pdt');

        $html = '<div class="card mt-4"><div class="card-header p-0 border-bottom-0"><ul class="nav nav-tabs card-header-tabs px-3 pt-2" role="tablist">';
        foreach ($tabs as $i => $t) {
            $t      = (array) $t;
            $active = $i === 0 ? ' active' : '';
            $id     = $prefix . '-' . $i;
            $html .= '<li class="nav-item" role="presentation"><button class="nav-link' . $active . '" data-bs-toggle="tab" data-bs-target="#' . $this->e($id) . '" type="button" role="tab">' . $this->e($t['title'] ?? '') . '</button></li>';
        }
        $html .= '</ul></div><div class="card-body"><div class="tab-content">';
        foreach ($tabs as $i => $t) {
            $t    = (array) $t;
            $show = $i === 0 ? ' show active' : '';
            $id   = $prefix . '-' . $i;
            $html .= '<div class="tab-pane fade' . $show . '" id="' . $this->e($id) . '" role="tabpanel">' . $this->raw($t['body'] ?? '') . '</div>';
        }

        return $html . '</div></div></div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 商城市场首页（apps-ecommerce-marketplace.html）
 *
 * XfAdmin::marketplace([
 *     'categories' => [
 *         ['title' => 'For Men', 'icon' => 'ti-trending-up', 'bg' => 'success', 'children' => ['Sports suits', 'Trousers', 'Jackets and coats', 'Shirts'], 'image' => 'products/1.png'],
 *         ['title' => 'For Women', 'bg' => 'warning', 'children' => ['Dresses', 'Pants and jeans', 'Shirts and blouses', 'Sweatshirts'], 'image' => 'products/2.png'],
 *         ['title' => 'Accessories', 'bg' => 'danger', 'children' => ['Caps and hats', 'Sunglasses', 'Handbags', 'Jewelry'], 'image' => 'products/3.png'],
 *     ],
 *     'filters' => ['Best Sellers', 'New Arrived', 'Sale Items', 'Top Rated'],
 *     'products' => [
 *         ['title' => '商品名', 'image' => 'products/1.png', 'price' => 764.15, 'old_price' => 899.00, 'rating' => 3, 'reviews' => 45, 'badge' => '15% OFF', 'badge_color' => 'success'],
 *         // ...
 *     ],
 *     'currency' => '$',
 *     'columns' => [4, 3, 2, 1], // xl, lg, sm 列数
 * ])
 */
class Marketplace extends Component
{
    protected function defaults(): array
    {
        return [
            'categories' => [],
            'filters' => [],
            'products' => [],
            'currency' => '$',
            'columns' => [4, 3, 2, 1],
            'subtitle' => 'Find Your Perfect Style',
            'subtitle_desc' => '👕 Discover styles tailored for everyone',
        ];
    }

    protected function html(): string
    {
        $html = '';

        // 分类卡片行
        $categories = (array) $this->get('categories', []);
        if (!empty($categories)) {
            $html .= $this->renderCategories($categories);
        }

        // 筛选器行
        $filters = (array) $this->get('filters', []);
        if (!empty($filters)) {
            $html .= $this->renderFilters($filters);
        }

        // 商品网格
        $products = (array) $this->get('products', []);
        if (!empty($products)) {
            $html .= $this->renderProducts($products);
        }

        return $html;
    }

    private function renderCategories(array $categories): string
    {
        $cols = (array) $this->get('columns', [4, 3, 2, 1]);
        $colClass = 'col-md-' . (12 / max(1, min(3, count($categories))));

        $html = '<div class="row pt-3">';
        foreach ($categories as $cat) {
            $cat = (array) $cat;
            $bg = $this->enum($cat['bg'] ?? 'primary', ['primary','secondary','success','danger','warning','info','dark','light'], 'primary');
            $title = (string) ($cat['title'] ?? '');
            $image = !empty($cat['image']) ? XfAdmin::img((string) $cat['image']) : '';
            $children = (array) ($cat['children'] ?? []);

            $html .= '<div class="' . $colClass . '"><div class="card bg-' . $this->e($bg) . ' bg-opacity-10 bg-gradient"><div class="card-body pb-0 d-flex align-items-center justify-content-between">';
            $html .= '<div><h5 class="fw-semibold mb-3">' . $this->e($title) . '</h5>';
            if (!empty($children)) {
                $html .= '<ul class="list-unstyled mb-0 text-body">';
                foreach ($children as $child) {
                    $html .= '<li><a href="javascript:void(0)" class="text-reset d-block py-1">' . $this->e((string) $child) . '</a></li>';
                }
                $html .= '</ul>';
            }
            $html .= '<a href="javascript:void(0)" class="fw-semibold link-reset d-inline-block my-3">View All <i class="ti ti-arrow-right align-middle fs-lg"></i></a></div>';
            if ($image) {
                $html .= '<img src="' . $this->e($image) . '" alt="' . $this->e($title) . '" class="img-fluid mt-auto" style="max-height:220px" />';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderFilters(array $filters): string
    {
        $subtitle = (string) $this->get('subtitle', '');
        $subtitleDesc = (string) $this->get('subtitle_desc', '');

        $html = '<div class="row pt-4"><div class="col-12 text-center">';
        if ($subtitleDesc) {
            $html .= '<span class="text-muted rounded-3 d-inline-block">' . $this->e($subtitleDesc) . '</span>';
        }
        if ($subtitle) {
            $html .= '<h3 class="mt-2 fw-bold mb-4">' . $this->raw($subtitle) . '</h3>';
        }
        $html .= '<div class="d-flex pt-1 justify-content-center align-items-center gap-1">';
        $first = true;
        foreach ($filters as $i => $filter) {
            $active = $first ? ' text-primary' : ' text-muted';
            $html .= '<a href="javascript:void(0)" class="badge badge-default rounded-pill px-3 py-2 fs-6' . $active . '">' . $this->e((string) $filter) . '</a>';
            $first = false;
        }
        $html .= '</div></div></div>';

        return $html;
    }

    private function renderProducts(array $products): string
    {
        $cols = (array) $this->get('columns', [4, 3, 2, 1]);
        $currency = (string) $this->get('currency', '$');
        $colClass = 'row-cols-xxl-' . ($cols[0] ?? 4)
            . ' row-cols-lg-' . ($cols[1] ?? 3)
            . ' row-cols-sm-' . ($cols[2] ?? 2)
            . ' row-col-1';

        $html = '<div class="row ' . $colClass . ' mt-3">';
        foreach ($products as $product) {
            $product = (array) $product;
            $html .= $this->renderProductCard($product, $currency);
        }
        $html .= '</div>';

        return $html;
    }

    private function renderProductCard(array $p, string $currency): string
    {
        $title = (string) ($p['title'] ?? '');
        $image = !empty($p['image']) ? XfAdmin::img((string) $p['image']) : '';
        $price = (float) ($p['price'] ?? 0);
        $oldPrice = isset($p['old_price']) ? (float) $p['old_price'] : 0;
        $rating = (int) ($p['rating'] ?? 0);
        $reviews = (int) ($p['reviews'] ?? 0);
        $badge = (string) ($p['badge'] ?? '');
        $badgeColor = (string) ($p['badge_color'] ?? 'danger');

        $html = '<div class="col"><article class="card card-h-100">';

        // 折扣徽章
        if ($badge) {
            $html .= '<div class="badge text-bg-' . $this->e($badgeColor) . ' badge-label fs-base rounded position-absolute top-0 start-0 m-3">'
                . $this->e($badge) . '</div>';
        }

        $html .= '<div class="card-body">';
        if ($image) {
            $html .= '<div class="p-3"><img src="' . $this->e($image) . '" alt="' . $this->e($title) . '" class="img-fluid" /></div>';
        }
        $html .= '<h6 class="card-title fs-sm lh-base mb-2"><a href="javascript:void(0)" class="link-reset">' . $this->e($title) . '</a></h6>';

        // 评分
        if ($rating > 0 || $reviews > 0) {
            $html .= '<div><span class="text-warning">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= $i <= $rating ? '<i class="ti ti-star-filled"></i>' : '<i class="ti ti-star"></i>';
            }
            $html .= '</span>';
            if ($reviews > 0) {
                $html .= '<span class="ms-1"><a href="javascript:void(0)" class="link-reset fw-semibold">(' . $reviews . ')</a></span>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        // 价格
        $html .= '<div class="card-footer bg-transparent d-flex justify-content-between border-dashed border-top">';
        $html .= '<div class="d-flex justify-content-start align-items-center gap-2">';
        $priceClass = $oldPrice > 0 ? 'text-danger' : 'text-success';
        $html .= '<h4 class="' . $priceClass . ' d-flex align-items-center gap-2 mb-0">';
        if ($oldPrice > 0) {
            $html .= '<span class="text-muted text-decoration-line-through">' . $currency . number_format($oldPrice, 2) . '</span>';
        }
        $html .= $currency . number_format($price, 2) . '</h4></div>';
        $html .= '<a class="btn btn-sm btn-icon btn-primary" href="javascript:void(0)"><i class="ti ti-basket fs-lg"></i></a>';
        $html .= '</div>';

        $html .= '</article></div>';

        return $html;
    }
}

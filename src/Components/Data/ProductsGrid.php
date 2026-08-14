<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 商品网格视图（apps-ecommerce-products-grid.html）
 *
 * XfAdmin::productsGrid([
 *     'products' => [
 *         ['title' => '商品名', 'image' => 'products/1.png', 'price' => 89.00, 'oldPrice' => 120.00, 'category' => '电子产品', 'stock' => 45, 'status' => 'active'],
 *         ...
 *     ],
 *     'currency' => '¥',
 *     'columns' => [4, 3, 2, 1], // xl, lg, md, sm
 * ])
 */
class ProductsGrid extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'products' => [],
            'currency' => '¥',
            'columns' => [4, 3, 2, 1],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $products = (array) $this->get('products', []);
        $currency = (string) $this->get('currency', '¥');
        $cols = (array) $this->get('columns', [4, 3, 2, 1]);

        $colClass = 'row-cols-xxl-' . ($cols[0] ?? 4)
            . ' row-cols-lg-' . ($cols[1] ?? 3)
            . ' row-cols-md-' . ($cols[2] ?? 2)
            . ' row-cols-1';

        // 工具栏
        $html = '<div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '<div><h5 class="mb-0">所有商品 <span class="badge text-bg-secondary rounded-pill ms-2">' . count($products) . '</span></h5></div>';
        $html .= '<div class="d-flex gap-2"><div class="btn-group btn-group-sm">'
            . '<button class="btn btn-outline-secondary active"><i class="ti ti-layout-grid"></i></button>'
            . '<button class="btn btn-outline-secondary"><i class="ti ti-list"></i></button></div>'
            . '<select class="form-select form-select-sm" style="width:auto"><option>默认排序</option><option>价格: 低到高</option><option>价格: 高到低</option><option>最新</option></select>'
            . '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>添加商品</button></div></div>';

        // 商品网格
        $html .= '<div class="row ' . $colClass . ' g-3">';
        foreach ($products as $p) {
            $p = (array) $p;
            $html .= $this->renderProductCard($p, $currency);
        }
        if (empty($products)) {
            $html .= '<div class="col-12 text-center py-5"><div class="text-muted"><i class="ti ti-package fs-48 d-block mb-3"></i>'
                . '<h5>暂无商品</h5><p>点击"添加商品"开始上架</p></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * render Product Card（private实例方法）
     *
     * @param array $p p
     * @param string $currency currency
     *
     * @return string result
     */
    private function renderProductCard(array $p, string $currency): string
    {
        $title = (string) ($p['title'] ?? '');
        $image = !empty($p['image']) ? XfAdmin::img((string) $p['image']) : '';
        $price = (float) ($p['price'] ?? 0);
        $oldPrice = isset($p['oldPrice']) ? (float) $p['oldPrice'] : 0;
        $category = (string) ($p['category'] ?? '');
        $stock = (int) ($p['stock'] ?? 0);
        $status = (string) ($p['status'] ?? 'active');

        $statusBadge = match ($status) {
            'active' => '',
            'draft' => '<span class="badge text-bg-warning position-absolute top-0 start-0 m-2">草稿</span>',
            'inactive' => '<span class="badge text-bg-secondary position-absolute top-0 start-0 m-2">下架</span>',
            default => '',
        };

        $html = '<div class="col"><div class="card h-100">';
        $html .= $statusBadge;

        if ($image) {
            $html .= '<img src="' . $this->e($image) . '" class="card-img-top" alt="' . $this->e($title) . '" style="height:180px;object-fit:cover">';
        }
        $html .= '<div class="card-body d-flex flex-column">';
        if ($category) {
            $html .= '<small class="text-muted text-uppercase">' . $this->e($category) . '</small>';
        }
        $html .= '<h6 class="card-title mt-1"><a href="javascript:void(0)" class="text-reset stretched-link">' . $this->e($title) . '</a></h6>';

        $html .= '<div class="mt-auto"><div class="d-flex align-items-center gap-2">';
        if ($oldPrice > 0) {
            $html .= '<span class="text-muted text-decoration-line-through small">' . $currency . number_format($oldPrice, 2) . '</span>';
        }
        $html .= '<span class="fw-bold text-danger">' . $currency . number_format($price, 2) . '</span></div>';

        if ($stock > 0) {
            $stockColor = $stock < 10 ? 'text-danger' : 'text-success';
            $html .= '<small class="' . $stockColor . '">库存: ' . $stock . '</small>';
        } else {
            $html .= '<small class="text-danger">缺货</small>';
        }
        $html .= '</div>';

        $html .= '</div></div></div>';

        return $html;
    }
}

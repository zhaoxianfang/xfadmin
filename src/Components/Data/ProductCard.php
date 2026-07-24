<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 商品卡片（ecommerce-products-grid.html）
 *
 * XfAdmin::productCard([
 *     'image'    => 'products/1.png',
 *     'title'    => '男士运动鞋',
 *     'category' => '鞋类',
 *     'price'    => '¥299',
 *     'old_price'=> '¥399',
 *     'rating'   => 4.5,
 *     'rating_count' => 128,
 *     'badge'    => ['text' => '热销', 'variant' => 'danger'],
 *     'href'     => '#',
 *     'actions'  => 'HTML',
 * ])
 */
class ProductCard extends Component
{
    protected function defaults(): array
    {
        return [
            'image'        => null,
            'title'        => '',
            'category'     => null,
            'price'        => null,
            'old_price'    => null,
            'rating'       => null,
            'rating_count' => null,
            'badge'        => null,
            'href'         => '#',
            'actions'      => null,
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'card h-100']) . '>';
        $html .= '<div class="position-relative">';
        if ($this->get('badge')) {
            $b = (array) $this->get('badge');
            $html .= '<span class="badge bg-' . $this->e($b['variant'] ?? 'primary') . ' position-absolute top-0 start-0 m-2">' . $this->e($b['text'] ?? '') . '</span>';
        }
        $img = $this->get('image') ? XfAdmin::asset('images/' . ltrim((string) $this->get('image'), '/')) : '';
        $html .= '<a href="' . $this->e($this->get('href')) . '"><img src="' . $this->e($img) . '" class="card-img-top p-3" alt="' . $this->e($this->get('title')) . '"></a>';
        $html .= '</div>';

        $html .= '<div class="card-body">';
        if ($this->get('category')) {
            $html .= '<small class="text-muted">' . $this->e($this->get('category')) . '</small>';
        }
        $html .= '<h5 class="my-1"><a href="' . $this->e($this->get('href')) . '" class="text-body">' . $this->e($this->get('title')) . '</a></h5>';

        if ($this->get('rating') !== null) {
            $html .= (new \zxf\XfAdmin\Components\UI\Rating([
                'value' => (float) $this->get('rating'),
                'count' => $this->get('rating_count'),
                'size'  => 'fs-6',
            ]))->render();
        }

        $html .= '<div class="d-flex align-items-center gap-2 mt-2">';
        if ($this->get('price') !== null) {
            $html .= '<h5 class="mb-0 text-primary">' . $this->raw($this->get('price')) . '</h5>';
        }
        if ($this->get('old_price') !== null) {
            $html .= '<span class="text-muted text-decoration-line-through">' . $this->raw($this->get('old_price')) . '</span>';
        }
        $html .= '</div>';

        if ($this->get('actions')) {
            $html .= '<div class="mt-3">' . $this->raw($this->get('actions')) . '</div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

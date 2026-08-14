<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 电商卖家列表（INSPINIA ecommerce-sellers.html）
 *
 * XfAdmin::sellers([
 *     'title'      => '卖家管理',
 *     'searchable' => true,
 *     'sellers'    => [
 *         [
 *             'name'    => '北岸数码',
 *             'avatar'  => 'sellers/3.png',
 *             'products'=> 142,
 *             'orders'  => 3180,
 *             'rating'  => 4.8,
 *             'location'=> '深圳',
 *             'balance' => '¥1.28M',
 *             'rank'    => '#2',
 *             'status'  => ['text' => '活跃', 'variant' => 'success'],
 *         ],
 *     ],
 * ])
 */
class Sellers extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'      => '卖家管理',
            'searchable' => true,
            'add_text'   => '新增卖家',
            'sellers'    => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $sellers = (array) $this->get('sellers', []);
        if (empty($sellers)) {
            return '';
        }
        $html = '<div' . $this->attrs(['class' => 'card xf-sellers']) . '>';

        $html .= '<div class="card-header border-light justify-content-between">';
        $html .= '<h5 class="card-title mb-0">' . $this->e($this->get('title')) . '</h5>';
        $html .= '<div class="d-flex gap-2">';
        if ($this->get('searchable')) {
            $html .= '<div class="search-box"><input type="text" class="form-control form-control-sm" placeholder="搜索卖家…"></div>';
        }
        $html .= '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>' . $this->e($this->get('add_text')) . '</button>';
        $html .= '</div></div>';

        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-custom table-centered table-select table-hover w-100 mb-0">';
        $html .= '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">'
            . '<th class="ps-3" style="width:1%;"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></th>'
            . '<th>Seller</th><th>Products</th><th>Orders</th><th>Rating</th><th>Location</th>'
            . '<th>Balance</th><th>Rank</th><th style="width:1%;">Report</th></tr></thead><tbody>';

        foreach ($sellers as $s) {
            $html .= '<tr>';
            $html .= '<td class="ps-3"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></td>';
            $html .= '<td><div class="d-flex align-items-center">';
            $html .= '<div class="avatar-md me-3">';
            if (! empty($s['avatar'])) {
                $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $s['avatar'])) . '" alt="" class="img-fluid rounded">';
            } else {
                $html .= '<span class="avatar-title rounded bg-primary-subtle text-primary">' . $this->e(mb_substr((string) ($s['name'] ?? '?'), 0, 1)) . '</span>';
            }
            $html .= '</div><div><h6 class="mb-1"><a href="#" class="link-reset">' . $this->e($s['name'] ?? '') . '</a></h6>'
                . '<p class="text-muted fs-xs mb-0">' . $this->e($s['location'] ?? '') . '</p></div></div></td>';
            $html .= '<td class="text-nowrap">' . $this->e($s['products'] ?? 0) . '</td>';
            $html .= '<td class="text-nowrap">' . $this->e(number_format((int) ($s['orders'] ?? 0))) . '</td>';
            $html .= '<td>' . $this->stars((float) ($s['rating'] ?? 0)) . '</td>';
            $html .= '<td>' . $this->e($s['location'] ?? '') . '</td>';
            $html .= '<td class="text-nowrap fw-medium">' . $this->e($s['balance'] ?? '') . '</td>';
            $html .= '<td><span class="badge text-bg-light">' . $this->e($s['rank'] ?? '') . '</span></td>';
            $html .= '<td><a href="#" class="btn btn-light btn-sm"><i class="ti ti-download me-1"></i>报表</a></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';

        $html .= '<div class="card-footer border-0"><div class="d-flex justify-content-between align-items-center">'
            . '<span class="text-muted">' . count($sellers) . ' 位卖家</span>'
            . '<nav><ul class="pagination pagination-sm mb-0 justify-content-center"><li class="page-item disabled"><a class="page-link" href="#">上一页</a></li><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item"><a class="page-link" href="#">2</a></li><li class="page-item"><a class="page-link" href="#">下一页</a></li></ul></nav>'
            . '</div></div>';

        return $html . '</div>';
    }

    /**
     * stars（protected实例方法）
     *
     * @param float $rating rating
     *
     * @return string result
     */
    protected function stars(float $rating): string
    {
        $full = (int) floor($rating);
        $html = '<span class="text-warning fs-base d-inline-flex align-items-center gap-1">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $full
                ? '<i class="ti ti-star-filled"></i>'
                : '<i class="ti ti-star text-muted opacity-50"></i>';
        }
        $html .= '<span class="text-muted fs-xs ms-1">' . number_format($rating, 1) . '</span></span>';
        return $html;
    }
}

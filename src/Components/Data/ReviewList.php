<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 商品评价列表（INSPINIA ecommerce-reviews.html）
 *
 * XfAdmin::reviewList([
 *     'title'   => '商品评价',
 *     'summary' => [
 *         'avg'   => 4.92,
 *         'total' => 245,
 *         'dist'  => [
 *             ['star' => 5, 'count' => 128],
 *             ['star' => 4, 'count' => 24],
 *             ['star' => 3, 'count' => 15],
 *             ['star' => 2, 'count' => 8],
 *             ['star' => 1, 'count' => 5],
 *         ],
 *         'note'  => '来自真实购买用户的反馈',
 *         'badge' => '+12 new this week',
 *     ],
 *     'reviews' => [
 *         [
 *             'product_img'     => 'products/2.png',
 *             'product'         => 'Wireless Earbuds',
 *             'reviewer_avatar' => 'users/user-8.jpg',
 *             'reviewer'        => 'Sophia Lee',
 *             'reviewer_email'  => 'sophia.lee@digitalshop.com',
 *             'rating'          => 5,
 *             'comment'         => '音质出色，佩戴舒适。',
 *             'date'            => '2026-07-18',
 *             'status'          => ['text' => '已审核', 'variant' => 'success'],
 *         ],
 *     ],
 * ])
 */
class ReviewList extends Component
{
    protected function defaults(): array
    {
        return [
            'title'   => '商品评价',
            'summary' => [],
            'reviews' => [],
        ];
    }

    protected function html(): string
    {
        $reviews = (array) $this->get('reviews', []);
        if (empty($reviews)) {
            return '';
        }

        $html = '<div' . $this->attrs(['class' => 'card xf-reviews']) . '>';

        // 顶部评分汇总
        $summary = (array) $this->get('summary', []);
        if (! empty($summary)) {
            $avg  = (float) ($summary['avg'] ?? 0);
            $total = (int) ($summary['total'] ?? 0);
            $html .= '<div class="row g-0 align-items-center border-bottom border-light">';
            $html .= '<div class="col-xl-6 border-end border-dashed"><div class="d-flex align-items-center gap-4 p-4">';
            $html .= '<img src="' . $this->e($this->img('ratings.svg')) . '" alt="" height="80" style="height:80px;">';
            $html .= '<div><h3 class="text-primary d-flex align-items-center gap-2 mb-2 fw-bold">' . number_format($avg, 2) . ' <i class="ti ti-star-filled"></i></h3>';
            $html .= '<p class="mb-2">Based on ' . number_format($total) . ' verified reviews</p>';
            if (! empty($summary['note'])) {
                $html .= '<p class="pe-2 h6 text-muted mb-2 lh-base">' . $this->e($summary['note']) . '</p>';
            }
            if (! empty($summary['badge'])) {
                $html .= '<span class="badge badge-label text-bg-success">' . $this->e($summary['badge']) . '</span>';
            }
            $html .= '</div></div></div>';

            $html .= '<div class="col-xl-6 p-3">';
            $dist = (array) ($summary['dist'] ?? []);
            foreach ($dist as $d) {
                $star  = (int) ($d['star'] ?? 5);
                $count = (int) ($d['count'] ?? 0);
                $pct   = $total > 0 ? round($count / $total * 100) : 0;
                $html .= '<div class="d-flex align-items-center gap-2 mb-2">'
                    . '<div class="flex-shrink-0 text-nowrap" style="width:50px;">' . $star . ' Star</div>'
                    . '<div class="progress w-100 bg-label-primary" style="height:8px;"><div class="progress-bar bg-primary" role="progressbar" style="width:' . $pct . '%;"></div></div>'
                    . '<div class="flex-shrink-0 text-end" style="width:30px;"><span class="badge text-bg-light">' . $count . '</span></div>'
                    . '</div>';
            }
            $html .= '</div></div>';
        }

        // 工具栏
        $html .= '<div class="card-header border-light justify-content-between"><h5 class="card-title mb-0">' . $this->e($this->get('title')) . '</h5>';
        $html .= '<div class="d-flex gap-2"><div class="search-box"><input type="text" class="form-control form-control-sm" placeholder="搜索评价…"></div>'
            . '<select class="form-select form-select-sm my-1 my-md-0" style="width:auto;"><option>全部状态</option><option>已审核</option><option>待审核</option></select></div>';
        $html .= '</div>';

        // 表格
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-custom table-centered table-select table-hover w-100 mb-0">';
        $html .= '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">'
            . '<th class="ps-3" style="width:1%;"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></th>'
            . '<th>Product</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Status</th><th class="text-center">Actions</th>'
            . '</tr></thead><tbody>';

        foreach ($reviews as $r) {
            $html .= '<tr>';
            $html .= '<td class="ps-3"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></td>';
            $html .= '<td><div class="d-flex align-items-center"><div class="avatar-lg me-3">';
            if (! empty($r['product_img'])) {
                $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $r['product_img'])) . '" alt="" class="img-fluid rounded">';
            } else {
                $html .= '<span class="avatar-title rounded bg-primary-subtle text-primary">P</span>';
            }
            $html .= '</div><h6 class="mb-0"><a href="#" class="link-reset">' . $this->e($r['product'] ?? '') . '</a></h6></div></td>';
            $html .= '<td><div class="d-flex justify-content-start align-items-center gap-2"><div class="avatar avatar-sm">';
            if (! empty($r['reviewer_avatar'])) {
                $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $r['reviewer_avatar'])) . '" alt="" class="img-fluid rounded-circle">';
            } else {
                $html .= '<span class="avatar-title rounded-circle bg-primary-subtle text-primary">' . $this->e(mb_substr((string) ($r['reviewer'] ?? '?'), 0, 1)) . '</span>';
            }
            $html .= '</div><div><h6 class="text-nowrap fs-sm mb-0 lh-base">' . $this->e($r['reviewer'] ?? '') . '</h6>'
                . '<p class="text-muted fs-xs mb-0">' . $this->e($r['reviewer_email'] ?? '') . '</p></div></div></td>';
            $html .= '<td>' . $this->stars((int) ($r['rating'] ?? 0)) . '</td>';
            $html .= '<td class="text-muted small" style="max-width:280px;">' . $this->e($r['comment'] ?? '') . '</td>';
            $html .= '<td class="text-nowrap">' . $this->e($r['date'] ?? '') . '</td>';
            $html .= '<td>' . $this->badge($r['status'] ?? null) . '</td>';
            $html .= '<td class="text-center"><div class="d-flex justify-content-center align-items-center gap-1">'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle text-danger"><i class="ti ti-trash"></i></a></div></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div></div>';
        $html .= '<div class="card-footer border-0"><div class="d-flex justify-content-between align-items-center">'
            . '<span class="text-muted">' . count($reviews) . ' 条评价</span>'
            . '<nav><ul class="pagination pagination-sm mb-0 justify-content-center"><li class="page-item disabled"><a class="page-link" href="#">上一页</a></li><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item"><a class="page-link" href="#">2</a></li><li class="page-item"><a class="page-link" href="#">下一页</a></li></ul></nav>'
            . '</div></div>';

        return $html . '</div>';
    }

    protected function stars(int $rating): string
    {
        $html = '<span class="text-warning fs-base">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $rating ? '<i class="ti ti-star-filled"></i>' : '<i class="ti ti-star text-muted opacity-50"></i>';
        }
        return $html . '</span>';
    }

    protected function badge(mixed $status): string
    {
        if (empty($status)) {
            return '';
        }
        $text    = $status['text'] ?? (is_string($status) ? $status : '');
        $variant = $status['variant'] ?? 'secondary';
        return '<span class="badge bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' badge-label">' . $this->e($text) . '</span>';
    }
}

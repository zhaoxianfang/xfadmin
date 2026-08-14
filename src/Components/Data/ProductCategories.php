<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 商品分类管理表（对齐 INSPINIA ecommerce-categories.html）
 *
 * 带工具栏（搜索 / 状态筛选 / 新增按钮）的分类管理列表：
 * 缩略图 + 名称、Slug、商品数、订单数、销售额、更新时间、状态徽标与行内操作。
 *
 * XfAdmin::productCategories([
 *     'title'      => '商品分类',
 *     'categories' => [
 *         [
 *             'name'     => '家具家居',
 *             'image'    => 'products/1.png',    // 相对包内 images/ 路径或完整 URL
 *             'slug'     => 'furniture',
 *             'products' => 5248,
 *             'orders'   => '95.6k',
 *             'earnings' => '¥4,050 万',
 *             'modified' => '2026-04-18 12:24',
 *             'status'   => ['text' => '启用', 'variant' => 'success'],
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class ProductCategories extends Component
{
    /** 默认配置 */
    protected function defaults(): array
    {
        return [
            'title'      => '商品分类',
            'searchable' => true,
            'add_text'   => '新增分类',
            'categories' => [],
        ];
    }

    /** 渲染分类管理表 */
    protected function html(): string
    {
        $items = (array) $this->get('categories', []);
        if (empty($items)) {
            return '';
        }
        $html = '<div' . $this->attrs(['class' => 'card xf-product-categories']) . '>';

        // 工具栏
        $html .= '<div class="card-header border-light justify-content-between">';
        $html .= '<h5 class="card-title mb-0">' . $this->e($this->get('title')) . '</h5>';
        $html .= '<div class="d-flex gap-2">';
        if ($this->get('searchable')) {
            $html .= '<div class="search-box"><input type="text" class="form-control form-control-sm" placeholder="搜索分类…"></div>';
        }
        $html .= '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>' . $this->e($this->get('add_text')) . '</button>';
        $html .= '</div></div>';

        // 表格
        $html .= '<div class="card-body p-0"><div class="table-responsive">'
            . '<table class="table table-custom table-centered table-hover w-100 mb-0">'
            . '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">'
            . '<th class="ps-3" style="width:1%;"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"></th>'
            . '<th>分类名称</th><th>Slug</th><th>商品数</th><th>订单数</th><th>销售额</th><th>更新时间</th><th>状态</th>'
            . '<th class="text-center" style="width:1%;">操作</th>'
            . '</tr></thead><tbody>';

        foreach ($items as $c) {
            $c = (array) $c;
            $html .= '<tr>';
            $html .= '<td class="ps-3"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"></td>';

            // 缩略图 + 名称
            $html .= '<td><div class="d-flex align-items-center">';
            if (! empty($c['image'])) {
                $html .= '<div class="avatar-md me-3"><img src="' . $this->e($this->img($c['image'])) . '" alt="" class="img-fluid rounded"></div>';
            }
            $html .= '<div><h5 class="mb-0"><a href="' . $this->e($c['url'] ?? '#') . '" class="link-reset">' . $this->e($c['name'] ?? '') . '</a></h5></div>';
            $html .= '</div></td>';

            $html .= '<td>' . $this->e($c['slug'] ?? '') . '</td>';
            $html .= '<td><h5 class="fs-base mb-0 fw-medium">' . $this->e((string) ($c['products'] ?? '')) . '</h5></td>';
            $html .= '<td>' . $this->e((string) ($c['orders'] ?? '')) . '</td>';
            $html .= '<td>' . $this->e((string) ($c['earnings'] ?? '')) . '</td>';
            $html .= '<td>' . $this->e((string) ($c['modified'] ?? '')) . '</td>';

            // 状态徽标
            $status  = $c['status'] ?? null;
            $text    = is_array($status) ? ($status['text'] ?? '') : (string) $status;
            $variant = is_array($status) ? ($status['variant'] ?? 'secondary') : 'secondary';
            $html   .= '<td>' . ($text !== ''
                ? '<span class="badge bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' fs-xxs">' . $this->e($text) . '</span>'
                : '') . '</td>';

            // 行内操作
            $html .= '<td><div class="d-flex justify-content-center gap-1">'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>'
                . '</div></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';

        // 页脚统计
        $html .= '<div class="card-footer border-0"><span class="text-muted">共 ' . count($items) . ' 个分类</span></div>';

        return $html . '</div>';
    }
}

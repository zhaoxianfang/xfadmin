<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 商品浏览统计（apps-ecommerce-product-views.html）
 *
 * XfAdmin::productViews([
 *     'products' => [
 *         ['name' => '商品A', 'image' => 'products/1.png', 'views' => 12560, 'uniqueViews' => 8450, 'avgTime' => '3:24', 'ctr' => 4.8, 'sales' => 230],
 *         ...
 *     ],
 *     'totalViews' => '128,430',
 *     'totalUnique' => '45,210',
 * ])
 */
class ProductViews extends Component
{
    protected function defaults(): array
    {
        return [
            'products' => [],
            'totalViews' => '0',
            'totalUnique' => '0',
            'title' => '商品浏览量',
        ];
    }

    protected function html(): string
    {
        $products = (array) $this->get('products', []);
        $totalViews = (string) $this->get('totalViews', '0');
        $totalUnique = (string) $this->get('totalUnique', '0');
        $title = (string) $this->get('title', '商品浏览量');

        $html = '';

        // 概览卡
        $html .= '<div class="row mb-4"><div class="col-md-4"><div class="card text-bg-primary"><div class="card-body text-center">';
        $html .= '<div class="text-white-50">总浏览量</div><h2 class="text-white">' . $this->e($totalViews) . '</h2></div></div></div>';
        $html .= '<div class="col-md-4"><div class="card text-bg-success"><div class="card-body text-center">';
        $html .= '<div class="text-white-50">独立访客</div><h2 class="text-white">' . $this->e($totalUnique) . '</h2></div></div></div>';
        $html .= '<div class="col-md-4"><div class="card text-bg-warning"><div class="card-body text-center">';
        $html .= '<div class="text-white-50">平均转化率</div><h2 class="text-white">4.8%</h2></div></div></div></div>';

        // 商品视图表
        $html .= '<div class="card"><div class="card-header"><h5 class="mb-0">' . $this->e($title) . '</h5></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">商品</th><th>浏览量</th><th>独立访客</th><th>平均停留</th><th>点击率</th><th>销量</th></tr></thead><tbody>';

        foreach ($products as $i => $p) {
            $p = (array) $p;
            $rank = $i + 1;
            $name = (string) ($p['name'] ?? '');
            $views = (int) ($p['views'] ?? 0);
            $uniqueViews = (int) ($p['uniqueViews'] ?? 0);
            $avgTime = (string) ($p['avgTime'] ?? '0:00');
            $ctr = (float) ($p['ctr'] ?? 0);
            $sales = (int) ($p['sales'] ?? 0);

            $ctrColor = $ctr > 3 ? 'success' : ($ctr > 1.5 ? 'warning' : 'danger');

            $html .= '<tr><td class="ps-3"><span class="text-muted me-2">#' . $rank . '</span>' . $this->e($name) . '</td>';
            $html .= '<td>' . number_format($views) . '</td>';
            $html .= '<td>' . number_format($uniqueViews) . '</td>';
            $html .= '<td>' . $this->e($avgTime) . '</td>';
            $html .= '<td><span class="text-' . $ctrColor . ' fw-semibold">' . number_format($ctr, 1) . '%</span></td>';
            $html .= '<td>' . number_format($sales) . '</td></tr>';
        }

        if (empty($products)) {
            $html .= '<tr><td colspan="6" class="text-center text-muted py-4">暂无浏览数据</td></tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

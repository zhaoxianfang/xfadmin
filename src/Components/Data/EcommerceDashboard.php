<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Concerns\HasPriceFormat;
use zxf\XfAdmin\XfAdmin;

/**
 * 电商仪表盘（dashboard-ecommerce.html）
 *
 * XfAdmin::ecommerceDashboard([
 *     'stats' => [
 *         ['label' => '今日订单', 'value' => 284, 'trend' => '+12.5%', 'icon' => 'ti-shopping-cart', 'color' => 'primary'],
 *         ['label' => '销售额', 'value' => 89420.50, 'trend' => '+8.2%', 'icon' => 'ti-currency-dollar', 'color' => 'success', 'currency' => true],
 *         ['label' => '访客数', 'value' => 12450, 'trend' => '+5.3%', 'icon' => 'ti-users', 'color' => 'info'],
 *         ['label' => '转化率', 'value' => 2.28, 'trend' => '-0.1%', 'icon' => 'ti-trending-up', 'color' => 'warning', 'suffix' => '%'],
 *     ],
 *     'recentOrders' => [
 *         ['id' => '#ORD-8921', 'customer' => '张三', 'amount' => 359.00, 'status' => '已完成', 'date' => '2025-01-15'],
 *         ...
 *     ],
 *     'topProducts' => [
 *         ['name' => '无线耳机 Pro', 'image' => 'products/1.png', 'sales' => 1250, 'revenue' => 89750.00],
 *         ...
 *     ],
 *     'chart' => true,  // 是否渲染图表区域
 *     'currency' => '¥',
 * ])
 */
class EcommerceDashboard extends Component
{
    use HasPriceFormat;

    protected function defaults(): array
    {
        return [
            'stats' => [],
            'recentOrders' => [],
            'topProducts' => [],
            'chart' => true,
            'chartData' => null,  // 图表自定义数据数组；为 null 时生成占位演示数据
            'currency' => '¥',
        ];
    }

    protected function html(): string
    {
        $stats = (array) $this->get('stats', []);
        $recentOrders = (array) $this->get('recentOrders', []);
        $topProducts = (array) $this->get('topProducts', []);
        $showChart = (bool) $this->get('chart', true);
        $currency = (string) $this->get('currency', '¥');

        $html = '';

        // 统计卡片行
        if (! empty($stats)) {
            $html .= '<div class="row g-3 mb-4">';
            foreach ($stats as $stat) {
                $stat = (array) $stat;
                $html .= $this->renderStatCard($stat, $currency);
            }
            $html .= '</div>';
        }

        // 图表行
        if ($showChart) {
            $html .= $this->renderChartSection($currency);
        }

        // 底部双列：最近订单 + 热销商品
        $html .= '<div class="row g-3">';
        $html .= '<div class="col-lg-8">' . $this->renderRecentOrders($recentOrders, $currency) . '</div>';
        $html .= '<div class="col-lg-4">' . $this->renderTopProducts($topProducts, $currency) . '</div>';
        $html .= '</div>';

        return $html;
    }

    private function renderStatCard(array $stat, string $currency): string
    {
        $label = $this->e($stat['label'] ?? '');
        $value = $stat['value'] ?? 0;
        $trend = (string) ($stat['trend'] ?? '');
        $icon = $this->e($stat['icon'] ?? 'ti-chart-bar');
        $color = $this->e($stat['color'] ?? 'primary');
        $isCurrency = ! empty($stat['currency']);
        $suffix = $this->e($stat['suffix'] ?? '');

        if ($isCurrency) {
            $displayValue = $this->formatPrice((float) $value, $currency);
        } else {
            $displayValue = is_float($value) ? number_format($value, 2) : number_format((int) $value);
        }

        $trendClass = str_starts_with($trend, '+') ? 'text-success' : 'text-danger';
        $trendIcon = str_starts_with($trend, '+') ? 'ti-arrow-up' : 'ti-arrow-down';

        return '<div class="col-xxl-3 col-md-6"><div class="card"><div class="card-body"><div class="d-flex align-items-center">'
            . '<div class="flex-shrink-0"><div class="rounded-3 bg-' . $color . '-subtle p-3">'
            . '<i class="ti ' . $icon . ' fs-24 text-' . $color . '"></i></div></div>'
            . '<div class="flex-grow-1 ms-3"><h6 class="text-muted mb-1 small">' . $label . '</h6>'
            . '<h4 class="mb-0">' . $displayValue . $suffix . '</h4>'
            . ($trend ? '<small class="' . $trendClass . '"><i class="ti ' . $trendIcon . ' me-1"></i>' . $this->e($trend) . '</small>' : '')
            . '</div></div></div></div>';
    }

    /**
     * 渲染营收趋势图表区域。
     * 优先使用外部传入的 chartData（{labels:[], values:[]}），
     * 未提供时生成固定的演示数据，杜绝 random_int 导致的不可复现问题。
     */
    private function renderChartSection(string $currency): string
    {
        // 图表数据：优先自定义数据，回退到固定演示数据（可复现、可测试）
        $chartData = $this->get('chartData');
        if (is_array($chartData) && ! empty($chartData['labels']) && ! empty($chartData['values'])) {
            $chartLabels = (array) $chartData['labels'];
            $chartValues = (array) $chartData['values'];
        } else {
            $chartLabels = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
            $chartValues = [18420, 21350, 16980, 28730, 32410, 25160, 30280];
        }

        $chartId = 'ec_' . $this->uid();
        $safeCurrency = $this->e($currency);

        // 声明式 ECharts：由 XFAdmin.scan() 统一初始化（支持自定义 chartData）
        $cfg = json_encode([
            'options' => [
                'grid' => ['left' => 56, 'right' => 16, 'top' => 24, 'bottom' => 30],
                'tooltip' => [
                    'trigger' => 'axis',
                    'valueFormatter' => null,
                ],
                'xAxis' => ['type' => 'category', 'boundaryGap' => false, 'data' => array_values($chartLabels)],
                'yAxis' => ['type' => 'value', 'axisLabel' => ['formatter' => '{value}']],
                'series' => [[
                    'name' => '营收 (' . $safeCurrency . ')',
                    'type' => 'line',
                    'smooth' => true,
                    'showSymbol' => true,
                    'symbolSize' => 6,
                    'data' => array_values($chartValues),
                    'itemStyle' => ['color' => '#0d6efd'],
                    'lineStyle' => ['width' => 2, 'color' => '#0d6efd'],
                    'areaStyle' => [
                        'color' => [
                            'type' => 'linear', 'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                            'colorStops' => [
                                ['offset' => 0, 'color' => 'rgba(13,110,253,0.30)'],
                                ['offset' => 1, 'color' => 'rgba(13,110,253,0.02)'],
                            ],
                        ],
                    ],
                ]],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP);

        $html = '<div class="row g-3 mb-4"><div class="col-12"><div class="card"><div class="card-header"><h5 class="card-title mb-0">营收趋势</h5></div>'
            . '<div class="card-body"><div class="chart-container" style="height:300px">'
            . '<canvas id="' . $chartId . '" data-xf="echart" data-xf-config="' . $cfg . '"></canvas></div></div></div></div></div>';

        return $html;
    }

    private function renderRecentOrders(array $orders, string $currency): string
    {
        $statusMap = [
            '已完成' => ['class' => 'text-bg-success', 'label' => '已完成'],
            '处理中' => ['class' => 'text-bg-warning', 'label' => '处理中'],
            '已发货' => ['class' => 'text-bg-info', 'label' => '已发货'],
            '已取消' => ['class' => 'text-bg-secondary', 'label' => '已取消'],
            '退款中' => ['class' => 'text-bg-danger', 'label' => '退款中'],
        ];

        $html = '<div class="card"><div class="card-header d-flex justify-content-between align-items-center">'
            . '<h5 class="card-title mb-0">最近订单</h5>'
            . '<a href="javascript:void(0)" class="btn btn-sm btn-outline-primary">查看全部</a></div>'
            . '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr>'
            . '<th>订单号</th><th>客户</th><th>金额</th><th>状态</th><th class="text-end">日期</th>'
            . '</tr></thead><tbody>';

        if (empty($orders)) {
            $html .= '<tr><td colspan="5" class="text-center text-muted py-4">暂无订单</td></tr>';
        } else {
            foreach ($orders as $o) {
                $o = (array) $o;
                $id = $this->e($o['id'] ?? '');
                $customer = $this->e($o['customer'] ?? '');
                $amount = $this->formatPrice((float) ($o['amount'] ?? 0), $currency);
                $status = (string) ($o['status'] ?? '');
                $date = $this->e($o['date'] ?? '');
                $statusInfo = $statusMap[$status] ?? ['class' => 'text-bg-light', 'label' => $status];

                $html .= '<tr><td><a href="javascript:void(0)" class="fw-medium">' . $id . '</a></td>'
                    . '<td>' . $customer . '</td>'
                    . '<td class="fw-medium">' . $amount . '</td>'
                    . '<td><span class="badge ' . $statusInfo['class'] . '">' . $this->e($statusInfo['label']) . '</span></td>'
                    . '<td class="text-end text-muted small">' . $date . '</td></tr>';
            }
        }

        $html .= '</tbody></table></div></div>';

        return $html;
    }

    private function renderTopProducts(array $products, string $currency): string
    {
        $html = '<div class="card"><div class="card-header"><h5 class="card-title mb-0">热销商品</h5></div>'
            . '<div class="list-group list-group-flush">';

        if (empty($products)) {
            $html .= '<div class="list-group-item text-center text-muted py-4">暂无数据</div>';
        } else {
            $maxSales = 0;
            foreach ($products as $p) {
                $p = (array) $p;
                $maxSales = max($maxSales, (int) ($p['sales'] ?? 0));
            }

            foreach ($products as $p) {
                $p = (array) $p;
                $name = $this->e($p['name'] ?? '');
                $sales = (int) ($p['sales'] ?? 0);
                $revenue = $this->formatPrice((float) ($p['revenue'] ?? 0), $currency);
                $image = ! empty($p['image']) ? XfAdmin::img((string) $p['image']) : '';
                $percent = $maxSales > 0 ? round($sales / $maxSales * 100) : 0;

                $html .= '<div class="list-group-item"><div class="d-flex align-items-center">';
                if ($image) {
                    $html .= '<img src="' . $this->e($image) . '" class="rounded-2 me-3" width="40" height="40" style="object-fit:cover" alt="">';
                } else {
                    $html .= '<div class="rounded-2 bg-light me-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px">'
                        . '<i class="ti ti-package text-muted"></i></div>';
                }
                $html .= '<div class="flex-grow-1 min-w-0"><h6 class="mb-0 text-truncate">' . $name . '</h6>'
                    . '<div class="d-flex align-items-center mt-1"><small class="text-muted">已售 ' . number_format($sales) . ' 件</small>'
                    . '<div class="progress flex-grow-1 ms-2" style="height:4px"><div class="progress-bar bg-primary" style="width:' . $percent . '%"></div></div></div></div>'
                    . '<div class="text-end ms-3"><span class="fw-medium">' . $revenue . '</span></div></div></div>';
            }
        }

        $html .= '</div></div>';

        return $html;
    }
}

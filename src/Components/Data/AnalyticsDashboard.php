<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 分析仪表盘（dashboards-analytics.html）
 *
 * XfAdmin::analyticsDashboard([
 *     'stats' => [
 *         ['title' => 'Today\'s Sales', 'value' => '$8,852', 'icon' => 'ti-currency-dollar', 'color' => 'primary', 'change' => 20],
 *         ['title' => 'Visitors', 'value' => '8,549', 'icon' => 'ti-users', 'color' => 'success', 'change' => -2],
 *         ['title' => 'Conversion', 'value' => '4.48%', 'icon' => 'ti-trending-up', 'color' => 'warning', 'change' => 8],
 *         ['title' => 'Bounce Rate', 'value' => '42.2%', 'icon' => 'ti-activity', 'color' => 'danger', 'change' => -5],
 *     ],
 *     'recentActivity' => [
 *         ['user' => 'John D.', 'action' => 'completed purchase', 'target' => '#ORD-5001', 'time' => '2 min ago', 'icon' => 'ti-shopping-cart', 'color' => 'primary'],
 *         ...
 *     ],
 * ])
 */
class AnalyticsDashboard extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'stats' => [],
            'recentActivity' => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $stats = (array) $this->get('stats', []);
        $recentActivity = (array) $this->get('recentActivity', []);

        $html = '';

        // 统计卡片
        if (!empty($stats)) {
            $html .= $this->renderStats($stats);
        }
        // 最近活动和图表占位
        $html .= '<div class="row g-4">';
        $html .= '<div class="col-lg-8">';
        $html .= '<div class="card"><div class="card-header"><h5 class="mb-0">概览</h5></div>';
        $html .= '<div class="card-body text-center"><div class="py-5 text-muted">';
        $html .= '<i class="ti ti-chart-area-line fs-48 d-block mb-3"></i>';
        $html .= '<p>图表区域 — 使用 XfAdmin::apexChart() 或 XfAdmin::eChart() 嵌入可视化</p>';
        $html .= '</div></div></div></div>';

        // 最近活动
        $html .= '<div class="col-lg-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">最近活动</h5></div>';
        $html .= '<div class="card-body"><div class="xf-activity-list">';
        if (!empty($recentActivity)) {
            foreach ($recentActivity as $activity) {
                $activity = (array) $activity;
                $color = $this->enum((string) ($activity['color'] ?? 'primary'), ['primary','secondary','success','danger','warning','info','dark'], 'primary');
                $icon = (string) ($activity['icon'] ?? 'ti-activity');
                $html .= '<div class="d-flex mb-3">';
                $html .= '<div class="bg-' . $this->e($color) . ' bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px;height:36px">'
                    . '<i class="ti ' . $this->e($icon) . ' text-' . $this->e($color) . '"></i></div>';
                $html .= '<div><strong>' . $this->e($activity['user'] ?? '') . '</strong> '
                    . $this->e($activity['action'] ?? '') . ' ' . $this->e($activity['target'] ?? '');
                $html .= '<br><small class="text-muted">' . $this->e($activity['time'] ?? '') . '</small></div></div>';
            }
        } else {
            $html .= '<div class="text-center text-muted py-3">暂无活动</div>';
        }
        $html .= '</div></div></div></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * render Stats（private实例方法）
     *
     * @param array $stats stats
     *
     * @return string result
     */
    private function renderStats(array $stats): string
    {
        $html = '<div class="row mb-4">';
        foreach ($stats as $stat) {
            $stat = (array) $stat;
            $color = (string) ($stat['color'] ?? 'primary');
            $icon = (string) ($stat['icon'] ?? 'ti-trending-up');
            $change = $stat['change'] ?? 0;
            $changeSign = $change >= 0 ? '+' : '';
            $changeClass = $change >= 0 ? 'text-success' : 'text-danger';
            $changeArrow = $change >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right';

            $html .= '<div class="col-md-3 col-sm-6"><div class="card"><div class="card-body"><div class="d-flex justify-content-between">';
            $html .= '<div><span class="text-muted small">' . $this->e($stat['title'] ?? '') . '</span>';
            $html .= '<h3 class="mt-1 mb-0">' . $this->e($stat['value'] ?? '') . '</h3>';
            $html .= '<div class="mt-2"><span class="' . $changeClass . ' small"><i class="ti ' . $changeArrow . ' me-1"></i>'
                . $changeSign . abs($change) . '%</span></div>';
            $html .= '</div>';
            $html .= '<i class="ti ' . $this->e($icon) . ' fs-24 text-' . $this->e($color) . ' bg-' . $this->e($color) . ' bg-opacity-10 rounded p-2 align-self-start"></i>';
            $html .= '</div></div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }
}

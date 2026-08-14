<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 通用仪表盘网格（dashboards-* 系列页面抽象）
 *
 * 将「统计卡片行 + 图表区（左右两列）+ 底部标签页内容」封装为单组件，
 * 适用于所有仪表盘类页面（运营 / 分析 / 电商 / 项目 …）。
 *
 * XfAdmin::dashboardGrid([
 *     'stats' => [
 *         ['title' => '今日销售额', 'value' => '¥8,852', 'icon' => 'ti-currency-dollar', 'variant' => 'primary', 'trend' => 20],
 *         ...
 *     ],
 *     'charts' => [
 *         ['title' => '运营趋势', 'width' => 8, 'body' => XfAdmin::apexChart([...])],
 *         ['title' => '用户构成', 'width' => 4, 'body' => XfAdmin::apexChart([...])],
 *     ],
 *     'bottom' => XfAdmin::tabs([...]),   // 底部标签页（可空）
 * ])
 */
class DashboardGrid extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'stats'  => [],
            'charts' => [],
            'bottom' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $stats  = (array) $this->get('stats', []);
        $charts = (array) $this->get('charts', []);
        $bottom = $this->get('bottom', '');

        $html = '';

        if (! empty($stats)) {
            $html .= '<div class="row g-3 mb-3">';
            foreach ($stats as $s) {
                $s = (array) $s;
                $html .= $this->statCard($s);
            }
            $html .= '</div>';
        }
        if (! empty($charts)) {
            $html .= '<div class="row g-3">';
            foreach ($charts as $c) {
                $c = (array) $c;
                $width = (int) ($c['width'] ?? 6);
                $body  = $this->raw($c['body'] ?? '');
                $title = $this->e($c['title'] ?? '');
                $html .= '<div class="col-xl-' . $width . '">'
                    . '<div class="card h-100"><div class="card-header"><h5 class="mb-0">' . $title . '</h5></div>'
                    . '<div class="card-body">' . $body . '</div></div></div>';
            }
            $html .= '</div>';
        }
        if ($bottom !== '' && $bottom !== null) {
            $html .= '<div class="mt-3">'
                . '<div class="card"><div class="card-header"><h5 class="mb-0">' . $this->e($this->get('bottom_title', '综合看板')) . '</h5></div>'
                . '<div class="card-body">' . $this->raw($bottom) . '</div></div></div>';
        }
        return $html;
    }

    /**
     * stat Card（private实例方法）
     *
     * @param array $s s
     *
     * @return string result
     */
    private function statCard(array $s): string
    {
        $variant = $this->enum((string) ($s['variant'] ?? 'primary'),
            self::ENUM_VARIANT, 'primary');
        $icon    = (string) ($s['icon'] ?? 'ti-chart-line');
        $trend   = (float) ($s['trend'] ?? 0);
        $trendClass = $trend >= 0 ? 'text-success' : 'text-danger';
        $trendArrow = $trend >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right';
        $trendText   = ($trend >= 0 ? '+' : '') . $trend . '%';

        return '<div class="col-sm-6 col-xl-3">'
            . '<div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start">'
            . '<div><span class="text-muted small">' . $this->e($s['title'] ?? '') . '</span>'
            . '<h3 class="mt-1 mb-1">' . $this->e($s['value'] ?? '') . '</h3>'
            . '<span class="' . $trendClass . ' small"><i class="ti ' . $trendArrow . ' me-1"></i>' . $trendText . '</span></div>'
            . '<i class="ti ' . $this->e($icon) . ' fs-24 text-' . $this->e($variant) . ' bg-' . $this->e($variant) . ' bg-opacity-10 rounded p-2"></i>'
            . '</div></div></div></div>';
    }
}

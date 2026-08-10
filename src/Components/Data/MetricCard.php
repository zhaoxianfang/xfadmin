<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 指标卡片（metrics.html）
 *
 * 「数值 + 变化趋势 + 迷你图表」三合一的经营指标卡，常用于仪表盘顶部的核心指标区。
 * 数值支持滚动计数动画（count-up），迷你图表基于 ECharts 渲染（donut/pie/bar/area/line）。
 *
 * XfAdmin::metricCard([
 *     'title'    => '总收入',                 // 指标名称
 *     'value'    => 368425,                  // 指标数值（数字时自动滚动计数）
 *     'prefix'   => '¥',                     // 数值前缀（货币符号等）
 *     'suffix'   => '',                      // 数值后缀（%、单 等）
 *     'decimals' => 0,                       // 计数动画保留小数位
 *     'trend'    => 12.5,                    // 变化率（正数=上升绿色，负数=下降红色；null 不显示）
 *     'trend_text' => '较上周',               // 变化率说明文字
 *     'chart'    => 'donut',                 // 迷你图类型：donut|pie|bar|area|line|null
 *     'data'     => [40, 68, 52, 80, 63],    // 迷你图数据（donut/pie 为各扇区值，其余为序列值）
 *     'labels'   => [],                      // donut/pie 扇区名称（可选）
 *     'color'    => '#3e60d5',               // 迷你图主色
 *     'icon'     => 'ti ti-coin',            // 左上角图标（无 chart 时展示更醒目）
 *     'footer'   => null,                    // 底部附加 HTML（如链接）
 *     'url'      => null,                    // 整卡跳转链接
 * ])
 */
class MetricCard extends Component
{
    protected function defaults(): array
    {
        return [
            'title'      => '',
            'value'      => 0,
            'prefix'     => '',
            'suffix'     => '',
            'decimals'   => 0,
            'trend'      => null,
            'trend_text' => '较上周',
            'chart'      => 'donut',
            'data'       => [],
            'labels'     => [],
            'color'      => '#3e60d5',
            'icon'       => null,
            'footer'     => null,
            'url'        => null,
        ];
    }

    protected function assets(): array
    {
        // 迷你图基于 ECharts（包内已内置，离线可用）
        return $this->get('chart') ? ['echarts'] : [];
    }

    protected function html(): string
    {
        $id    = $this->resolveId('xf-metric');
        $value = $this->get('value');
        $trend = $this->get('trend');

        // ---------- 数值区：数字自动启用滚动计数动画 ----------
        $valueHtml = is_numeric($value)
            ? '<span class="xf-count" data-xf-count="' . $this->e($value) . '" data-xf-decimals="' . (int) $this->get('decimals') . '">0</span>'
            : $this->e((string) $value);
        $valueHtml = '<h3 class="mb-1 fw-bold">' . $this->e($this->get('prefix', '')) . $valueHtml . $this->e($this->get('suffix', '')) . '</h3>';

        // ---------- 趋势徽标：正=绿升 负=红降 ----------
        $trendHtml = '';
        if ($trend !== null && $trend !== '') {
            $up        = (float) $trend >= 0;
            $trendHtml = '<p class="mb-0 text-muted small">'
                . '<span class="' . ($up ? 'text-success' : 'text-danger') . ' me-1">'
                . '<i class="ti ti-arrow-' . ($up ? 'up' : 'down') . '-right"></i> ' . $this->e(abs((float) $trend)) . '%</span>'
                . $this->e($this->get('trend_text', '')) . '</p>';
        }

        // ---------- 迷你图 ----------
        $chartHtml = '';
        if ($this->get('chart')) {
            $cfg = [
                'type'   => (string) $this->get('chart'),
                'data'   => array_values((array) $this->get('data', [])),
                'labels' => array_values((array) $this->get('labels', [])),
                'color'  => (string) $this->get('color'),
            ];
            $chartHtml = '<div class="xf-metric-chart" data-xf="metric-chart" data-xf-config="'
                . $this->e(json_encode($cfg, JSON_HEX_TAG | JSON_HEX_AMP)) . '"></div>';
        } elseif ($this->get('icon')) {
            $chartHtml = '<div class="avatar-lg d-flex align-items-center justify-content-center rounded bg-primary-subtle">'
                . '<i class="' . $this->e($this->get('icon')) . ' fs-2 text-primary"></i></div>';
        }

        $inner = '<div class="d-flex align-items-center justify-content-between gap-2">'
            . '<div class="flex-grow-1">'
            . '<h5 class="text-muted fs-13 text-uppercase mb-2">'
            . ($this->get('icon') && $this->get('chart') ? '<i class="' . $this->e($this->get('icon')) . ' me-1"></i>' : '')
            . $this->e($this->get('title')) . '</h5>'
            . $valueHtml . $trendHtml
            . '</div>' . $chartHtml . '</div>';

        if ($this->get('footer') !== null) {
            $inner .= '<div class="mt-2 pt-2 border-top border-dashed">' . $this->raw($this->get('footer')) . '</div>';
        }

        $body = '<div class="card-body">' . $inner . '</div>';
        $card = $this->get('url')
            ? '<a href="' . $this->e($this->get('url')) . '" class="card xf-metric-card text-reset" id="' . $id . '">' . $body . '</a>'
            : '<div' . $this->attrs(['class' => 'card xf-metric-card', 'id' => $id]) . '>' . $body . '</div>';

        return $card;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 迷你 sparkline 统计卡
 *
 * 大数字 + 趋势 sparkline（内联 SVG）+ 同比涨跌幅小标，
 * 复刻 inspinia dashboard-analytics 的迷你统计卡。
 *
 * XfAdmin::statMiniSparkline([
 *     'label' => '月活跃用户',
 *     'value' => '12,480',
 *     'delta' => 12.5,          // 正涨 / 负跌
 *     'series' => [10, 14, 12, 18, 16, 22, 20, 26],
 *     'variant' => 'primary',
 *     'icon' => 'ti ti-users',
 * ])
 */
class StatMiniSparkline extends Component
{
    protected function defaults(): array
    {
        return [
            'label'   => '',
            'value'   => '',
            'delta'   => null,
            'series'  => [],
            'variant' => 'primary',
            'icon'    => null,
        ];
    }

    protected function html(): string
    {
        $label   = $this->get('label');
        $value   = $this->get('value');
        $delta   = $this->get('delta');
        $series  = (array) $this->get('series', []);
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');
        $icon    = $this->get('icon');
        $id      = $this->resolveId('xf-spark');

        $spark = $this->sparklineSvg($series, $id, $variant);

        $deltaHtml = '';
        if ($delta !== null) {
            $up   = (float) $delta >= 0;
            $cls  = $up ? 'text-success' : 'text-danger';
            $icon = $up ? 'ti ti-arrow-up-right' : 'ti ti-arrow-down-right';
            $deltaHtml = '<span class="small ' . $cls . '"><i class="' . $icon . '"></i> '
                . $this->e(abs((float) $delta)) . '%</span>';
        }

        $iconHtml = $icon ? '<span class="avatar-sm rounded-circle bg-' . $variant . '-subtle text-' . $variant
            . ' d-inline-flex align-items-center justify-content-center mb-2"><i class="' . $this->e($icon) . '"></i></span>' : '';

        return '<div class="card border-0 shadow-sm h-100">'
            . '<div class="card-body">'
            . $iconHtml
            . '<div class="d-flex align-items-end justify-content-between">'
            . '<div>'
            . '<p class="text-muted mb-1 small">' . $this->e($label) . '</p>'
            . '<h3 class="mb-0 fw-bold">' . $this->e($value) . ' ' . $deltaHtml . '</h3>'
            . '</div>'
            . $spark
            . '</div>'
            . '</div></div>';
    }

    private function sparklineSvg(array $series, string $id, string $variant): string
    {
        if (count($series) < 2) {
            return '';
        }
        $w = 80;
        $h = 32;
        $min = min($series);
        $max = max($series);
        $range = $max - $min ?: 1;
        $n = count($series);
        $pts = [];
        foreach ($series as $i => $v) {
            $x = ($n === 1) ? 0 : ($i / ($n - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 4) - 2;
            $pts[] = round($x, 1) . ',' . round($y, 1);
        }
        $poly = implode(' ', $pts);
        $color = 'var(--bs-' . $variant . ')';

        return '<svg class="xf-spark" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '"'
            . ' preserveAspectRatio="none" aria-hidden="true">'
            . '<polyline points="' . $poly . '" fill="none" stroke="' . $color . '" stroke-width="2"'
            . ' stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
}

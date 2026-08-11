<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * ApexCharts 图表（折线/面积/柱状/条形/饼图/环形/雷达/热力图/K线/迷你走势 sparkline 等全部类型）
 *
 * XfAdmin::apexChart([
 *     'type'   => 'line',
 *     'height' => 350,
 *     'series' => [['name' => '销量', 'data' => [10, 41, 35, 51]]],
 *     'labels' => ['一月', '二月', '三月', '四月'],       // 便捷 xaxis.categories / 饼图 labels
 *     'colors' => ['#3e60d5'],
 *     'options'=> [ ... 透传 ApexCharts 原生配置，最高优先级 ... ],
 * ])
 */
class ApexChart extends Component
{
    protected function defaults(): array
    {
        return [
            'type'      => 'line',
            'height'    => 350,
            'width'     => null,
            'series'    => [],
            'labels'    => null,
            'colors'    => null,
            'sparkline' => false,
            'options'   => [],
        ];
    }

    protected function assets(): array
    {
        return ['apexcharts'];
    }

    protected function html(): string
    {
        $id   = $this->resolveId('xf-apex');
        $type = (string) $this->get('type');

        $options = [
            'chart' => array_filter([
                'type'      => $type,
                'height'    => $this->get('height'),
                'width'     => $this->get('width') ?? '100%', // 默认 100% 确保响应式
                'sparkline' => $this->get('sparkline') ? ['enabled' => true] : null,
            ], fn ($v) => $v !== null),
            'series' => $this->get('series'),
        ];

        if ($this->get('labels') !== null) {
            if (in_array($type, ['pie', 'donut', 'radialBar', 'polarArea'], true)) {
                $options['labels'] = $this->get('labels');
            } else {
                $options['xaxis'] = ['categories' => $this->get('labels')];
            }
        }
        if ($this->get('colors') !== null) {
            $options['colors'] = $this->get('colors');
        }

        $options = array_replace_recursive($options, (array) $this->get('options', []));

        return '<div' . $this->attrs([
            'id'             => $id,
            'data-xf'        => 'apexchart',
            'data-xf-config' => json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

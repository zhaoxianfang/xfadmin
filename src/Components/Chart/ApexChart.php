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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'type' => 'line',
            'height' => 350,
            'width' => null,
            'series' => [],
            'labels' => null,
            'colors' => null,
            'sparkline' => false,
            'options' => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['apexcharts'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-apex');
        $type = (string) $this->get('type');

        $options = [
            'chart' => array_filter([
                'type' => $type,
                'height' => $this->get('height'),
                'width' => $this->get('width') ?? '100%', // 默认 100% 确保响应式
                'sparkline' => $this->get('sparkline') ? ['enabled' => true] : null,
            ], fn ($v) => $v !== null),
            'series' => $this->get('series') ?? [],
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

        // 与其它图表组件统一走 initAttrs 生成 data-xf / data-xf-config（输出等价）
        return '<div'.$this->attrs(['id' => $id] + $this->initAttrs('apexchart', $options)).'></div>';
    }
}

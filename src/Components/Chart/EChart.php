<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * Apache ECharts 图表（支持全部 ECharts 图表类型与配置）
 *
 * XfAdmin::echart([
 *     'height'  => 350,
 *     'options' => [
 *         'xAxis'  => ['type' => 'category', 'data' => ['Mon', 'Tue']],
 *         'yAxis'  => ['type' => 'value'],
 *         'series' => [['type' => 'bar', 'data' => [120, 200]]],
 *     ],
 * ])
 */
class EChart extends Component
{
    protected function defaults(): array
    {
        return [
            'height'  => 350,
            'theme'   => null,
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return ['echarts'];
    }

    protected function html(): string
    {
        $id = $this->resolveId('xf-echart');
        // 键名容错：options / option 等价（ECharts 官方叫 option，历史配置两种写法都有）
        $options = (array) $this->get('options', []);
        if ($options === []) {
            $options = (array) $this->get('option', []);
        }
        $config = [
            'theme'   => $this->get('theme'),
            'options' => (object) $options,
        ];

        return '<div' . $this->attrs([
            'id'             => $id,
            'style'          => 'height:' . (int) $this->get('height') . 'px;',
            'data-xf'        => 'echart',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'height' => 350,
            'theme' => null,
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
        return ['echarts'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-echart');
        // 键名容错：options / option 等价（ECharts 官方叫 option，历史配置两种写法都有）
        $options = (array) $this->get('options', []);
        if ($options === []) {
            $options = (array) $this->get('option', []);
        }
        $config = [
            'theme' => $this->get('theme'),
            'options' => (object) $options,
        ];

        // 与其它图表组件统一走 initAttrs 生成 data-xf / data-xf-config（输出等价）
        // height 显式传 null 时回退文档默认 350，避免渲染出 height:0px
        return '<div'.$this->attrs([
            'id' => $id,
            'style' => 'height:'.(int) ($this->get('height') ?? 350).'px;',
        ] + $this->initAttrs('echart', $config)).'></div>';
    }
}

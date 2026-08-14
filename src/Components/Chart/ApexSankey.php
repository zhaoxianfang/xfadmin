<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * 桑基图（charts-apexsankey.html，基于 apexsankey 插件 + svg.js，离线可用）
 *
 * 用于展示流量 / 能量 / 转化漏斗等「来源 → 去向」的流向分布，
 * 节点与连线宽度按 value 自动分配，内置缩放工具栏与连线悬浮高亮。
 *
 * XfAdmin::apexSankey([
 *     'height' => 400,
 *     'nodes'  => [                          // 节点：id 必填，title 缺省取 id
 *         ['id' => 'oil',  'title' => '石油'],
 *         ['id' => 'coal', 'title' => '煤炭', 'color' => '#fa5c7c'],
 *         ['id' => 'energy', 'title' => '能源'],
 *     ],
 *     'edges'  => [                          // 连线：source/target/value 必填，color 可选
 *         ['source' => 'oil',  'target' => 'energy', 'value' => 15],
 *         ['source' => 'coal', 'target' => 'energy', 'value' => 25, 'color' => '#ffe5eb'],
 *     ],
 *     'node_width' => 20,                    // 节点条宽度（px）
 *     'toolbar'    => true,                  // 是否显示缩放工具栏
 *     'order'      => null,                  // 可选：各列节点排序（apexsankey options.order 原生结构）
 *     'options'    => [ ... 透传 apexsankey 原生图形配置，最高优先级 ... ],
 * ])
 */
class ApexSankey extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'height'     => 400,
            'nodes'      => [],
            'edges'      => [],
            'node_width' => 20,
            'toolbar'    => true,
            'order'      => null,
            'options'    => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        // apexsankey 声明了 svgdotjs 依赖，Assets 会自动按序注入 svg.min.js
        return ['apexsankey'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-apexsankey');

        // 规范化节点：允许简写字符串（'oil' 等价 ['id'=>'oil','title'=>'oil']）
        $nodes = [];
        foreach ((array) $this->get('nodes', []) as $n) {
            if (is_string($n)) {
                $nodes[] = ['id' => $n, 'title' => $n];
                continue;
            }
            $n          = (array) $n;
            $n['title'] = (string) ($n['title'] ?? $n['id'] ?? '');
            $nodes[]    = $n;
        }
        $cfg = array_filter([
            'nodes'     => $nodes,
            'edges'     => array_values((array) $this->get('edges', [])),
            'height'    => (int) $this->get('height', 400),
            'nodeWidth' => (int) $this->get('node_width', 20),
            'toolbar'   => (bool) $this->get('toolbar', true),
            'order'     => $this->get('order'),
            'options'   => (array) $this->get('options', []) ?: null,
        ], fn ($v) => $v !== null);

        return '<div' . $this->attrs([
            'class'          => 'xf-apexsankey',
            'id'             => $id,
            'data-xf'        => 'apexsankey',
            'data-xf-config' => json_encode($cfg, JSON_HEX_TAG | JSON_HEX_AMP),
        ]) . '></div>';
    }
}

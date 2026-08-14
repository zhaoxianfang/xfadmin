<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * 矢量地图（jsVectorMap）
 *
 * XfAdmin::vectorMap([
 *     'map'     => 'world',      // world | world_merc
 *     'height'  => 360,
 *     'markers' => [['name' => 'Beijing', 'coords' => [39.9, 116.4]]],
 *     'options' => [ ...透传 jsVectorMap 配置... ],
 * ])
 */
class VectorMap extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'map'     => 'world',
            'height'  => 360,
            'markers' => [],
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
        return ['jsvectormap-world'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id     = $this->resolveId('xf-map');
        $config = array_replace_recursive([
            'map'     => $this->get('map'),
            'markers' => $this->get('markers'),
        ], (array) $this->get('options', []));

        return '<div' . $this->attrs([
            'id'             => $id,
            'style'          => 'height:' . (int) $this->get('height') . 'px;',
            'data-xf'        => 'vectormap',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

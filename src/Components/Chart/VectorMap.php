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
    protected function defaults(): array
    {
        return [
            'map'     => 'world',
            'height'  => 360,
            'markers' => [],
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return ['jsvectormap-world'];
    }

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
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

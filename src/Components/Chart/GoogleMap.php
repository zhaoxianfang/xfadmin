<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * 谷歌地图（maps-google.html）
 *
 * 采用免费 iframe 嵌入方式（无需 API Key）。支持按地点名称搜索或经纬度定位，
 * 可切换普通 / 卫星视图。注意：底图数据来自 Google 在线服务，离线环境不可用
 * （离线场景请改用 XfAdmin::leafletMap 并设置 tiles=null）。
 *
 * XfAdmin::googleMap([
 *     'height'  => 400,
 *     'place'   => '北京市朝阳区',            // 地点搜索（优先于 center）
 *     'center'  => [39.9042, 116.4074],      // 经纬度定位 [lat, lng]
 *     'zoom'    => 12,                       // 缩放级别 1~21
 *     'maptype' => 'roadmap',                // roadmap 普通 | satellite 卫星
 *     'language'=> 'zh-CN',                  // 界面语言
 *     'rounded' => true,                     // 圆角容器
 * ])
 */
class GoogleMap extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'height'   => 400,
            'place'    => null,
            'center'   => [39.9042, 116.4074],
            'zoom'     => 12,
            'maptype'  => 'roadmap',
            'language' => 'zh-CN',
            'rounded'  => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id   = $this->resolveId('xf-gmap');
        $zoom = max(1, min(21, (int) $this->get('zoom', 12)));

        // 组装免 Key 嵌入地址：place 优先按名称搜索，否则按经纬度
        $query = $this->get('place');
        if ($query === null || $query === '') {
            $center = (array) $this->get('center', [39.9042, 116.4074]);
            $query  = ($center[0] ?? 39.9042) . ',' . ($center[1] ?? 116.4074);
        }
        $params = [
            'q'      => (string) $query,
            'z'      => (string) $zoom,
            'hl'     => (string) $this->get('language', 'zh-CN'),
            'output' => 'embed',
        ];
        if (($this->get('maptype') ?? 'roadmap') === 'satellite') {
            $params['t'] = 'k'; // k = 卫星图层
        }
        $src = 'https://maps.google.com/maps?' . http_build_query($params);

        return '<div' . $this->attrs([
            'class' => 'xf-gmap overflow-hidden' . ($this->get('rounded') ? ' rounded' : ''),
            'id'    => $id,
            'style' => 'height:' . (int) $this->get('height', 400) . 'px',
        ]) . '><iframe src="' . $this->e($src) . '" style="border:0;width:100%;height:100%;"'
            . ' loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
    }
}

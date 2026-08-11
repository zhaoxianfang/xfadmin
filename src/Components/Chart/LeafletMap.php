<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * Leaflet 交互地图（maps-leaflet.html）
 *
 * 说明：Leaflet 的 JS/CSS 已本地内置（离线可用），但地图底图瓦片(tiles)通常来自在线
 * 瓦片服务（如 OpenStreetMap）。设置 `tiles=null` 可完全离线渲染（仅显示标记/图形，无底图）。
 *
 * XfAdmin::leafletMap([
 *     'height'  => 400,
 *     'center'  => [39.9, 116.4],
 *     'zoom'    => 11,
 *     'tiles'   => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', // null=离线无底图
 *     'markers' => [['lat'=>39.9,'lng'=>116.4,'title'=>'总部','popup'=>'说明']],
 *     'circles' => [['lat'=>39.9,'lng'=>116.4,'radius'=>1200,'color'=>'#3e60d5']],
 *     'polygons'=> [['points'=>[[39.9,116.4],[39.91,116.41]],'color'=>'#198754']],
 * ])
 */
class LeafletMap extends Component
{
    protected function defaults(): array
    {
        return [
            'height'  => 400,
            'center'  => [39.9042, 116.4074],
            'zoom'    => 11,
            'tiles'   => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'markers' => [],
            'circles' => [],
            'polygons'=> [],
            'lines'   => [],
        ];
    }

    protected function assets(): array
    {
        return ['leaflet'];
    }

    protected function html(): string
    {
        $id = $this->resolveId('leaflet');
        $cfg = [
            'center'  => $this->get('center'),
            'zoom'    => (int) $this->get('zoom'),
            'tiles'   => $this->get('tiles') ?: null,
            'markers' => array_values((array) $this->get('markers')),
            'circles' => array_values((array) $this->get('circles')),
            'polygons'=> array_values((array) $this->get('polygons')),
            'lines'   => array_values((array) $this->get('lines')),
        ];

        $html = '<div' . $this->attrs(['class' => 'xf-leaflet rounded overflow-hidden', 'id' => $id])
            . ' style="height:' . (int) $this->get('height') . 'px" data-xf="leaflet-map" data-xf-config="'
            . $this->e(json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}') . '"></div>';

        $js = 'XFAdmin.register("leaflet-map",function(el){'
            . 'if(!window.L||!el)return;'
            . 'var cfg=JSON.parse(el.getAttribute("data-xf-config")||"{}");'
            . 'if(!cfg.center)return;'
            . 'if(el._xfLeaflet)return el._xfLeaflet;'
            . 'var map=L.map(el,{scrollWheelZoom:false});'           // 先建地图，尺寸确定后再 setView（避免 0 尺寸下投影错位）
            . 'if(cfg.tiles){L.tileLayer(cfg.tiles,{maxZoom:19,attribution:"&copy; OpenStreetMap"}).addTo(map);}'
            . 'else{el.style.background = "#eef0f3";}'
            . '(cfg.markers||[]).forEach(function(m){var mk=L.marker([m.lat,m.lng]).addTo(map);if(m.popup)mk.bindPopup(m.popup);if(m.title)mk.bindTooltip(m.title);});'
            . '(cfg.circles||[]).forEach(function(c){L.circle([c.lat,c.lng],{radius:c.radius,color:c.color}).addTo(map);});'
            . '(cfg.polygons||[]).forEach(function(p){L.polygon(p.points,{color:p.color}).addTo(map);});'
            . '(cfg.lines||[]).forEach(function(l){L.polyline(l.points,{color:l.color}).addTo(map);});'
            . 'el._xfLeaflet=map;'
            . 'function fit(){'
            . '  map.invalidateSize();'                             // 刷新尺寸
            . '  try{'
            . '    map.setView(cfg.center,cfg.zoom,{animate:false});' // 先建立中心/缩放（触发正确 _resetView）
            . '    if(typeof map._resetView==="function"){map._resetView(map.getCenter(),map.getZoom());}' // 再强制重排瓦片/标记位置
            . '  }catch(e){ try{map._resetView(L.latLng(cfg.center[0],cfg.center[1]),cfg.zoom);}catch(_){} }' // 极端情况下回退
            . '}'
            . 'fit();'                                              // 立即校正一次
            . 'if(window.requestAnimationFrame){requestAnimationFrame(fit);}'
            . 'setTimeout(fit,200);'                                // 布局稳定后再次校正
            . 'if(window.ResizeObserver){var ro=new ResizeObserver(fit);ro.observe(el);}' // 容器尺寸变化（含由隐藏变可见）持续校正
            . 'return map;});';
        XfAdmin::assets()->inlineJs($js, 'xf-leaflet');

        return $html;
    }
}

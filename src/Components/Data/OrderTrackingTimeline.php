<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 订单物流进度时间线
 *
 * 节点（已下单/已发货/运输中/已签收）+ 当前节点高亮，
 * 复刻 inspinia apps-ecommerce-order-details.html 的物流进度。
 *
 * XfAdmin::orderTrackingTimeline([
 *     'steps' => [
 *         ['title'=>'已下单','desc'=>'2026-08-01 09:00','done'=>true],
 *         ['title'=>'已发货','desc'=>'2026-08-02 14:00','done'=>true],
 *         ['title'=>'运输中','desc'=>'2026-08-03 10:00','current'=>true],
 *         ['title'=>'已签收','desc'=>'','done'=>false],
 *     ],
 * ])
 */
class OrderTrackingTimeline extends Component
{
    protected function defaults(): array
    {
        return [
            'steps' => [],
        ];
    }

    protected function html(): string
    {
        $steps = (array) $this->get('steps', []);
        $html  = '<div class="xf-order-track d-flex justify-content-between position-relative">';
        $n = count($steps);
        $html .= '<div class="xf-order-track-line position-absolute top-0 start-0 end-0 mx-4" style="height:2px;background:var(--bs-border-color);top:18px;"></div>';

        foreach ($steps as $i => $s) {
            $s = (array) $s;
            $done    = ! empty($s['done']);
            $current = ! empty($s['current']);
            $state   = $current ? 'current' : ($done ? 'done' : 'todo');
            $dotCls  = $done ? 'bg-success' : ($current ? 'bg-primary' : 'bg-light border');
            $titleCls = $done || $current ? 'fw-semibold' : 'text-muted';

            $html .= '<div class="xf-order-track-step text-center position-relative" style="flex:1;">';
            $html .= '<div class="rounded-circle ' . $dotCls . ' mx-auto d-flex align-items-center justify-content-center" style="width:36px;height:36px;z-index:1;">'
                . '<i class="ti ' . ($done ? 'ti-check' : ($current ? 'ti-truck' : 'ti-circle')) . ' ' . ($done || $current ? 'text-white' : 'text-muted') . '"></i></div>';
            $html .= '<div class="mt-2 ' . $titleCls . '">' . $this->e($s['title'] ?? '') . '</div>';
            if (! empty($s['desc'])) {
                $html .= '<small class="text-muted d-block">' . $this->e($s['desc']) . '</small>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 时间线
 *
 * XfAdmin::timeline([
 *     'items' => [
 *         ['time' => '09:30', 'title' => '创建订单', 'text' => '订单 #1234 已创建', 'icon' => 'ti ti-plus', 'variant' => 'primary'],
 *         ['time' => '10:00', 'title' => '已付款', 'variant' => 'success'],
 *     ],
 * ])
 */
class Timeline extends Component
{
    protected function defaults(): array
    {
        return [
            'items' => [],
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'timeline']) . '>';

        foreach ((array) $this->get('items', []) as $item) {
            $variant = $item['variant'] ?? 'primary';
            $icon    = $item['icon'] ?? 'ti ti-point-filled';

            $html .= '<div class="timeline-item d-flex align-items-stretch">';
            $html .= '<div class="timeline-time pe-3 text-muted">' . $this->e($item['time'] ?? '') . '</div>';
            $html .= '<div class="timeline-dot flex-shrink-0"><span class="avatar-xs"><span class="avatar-title bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' rounded-circle"><i class="' . $this->e($icon) . '"></i></span></span></div>';
            $html .= '<div class="timeline-content ps-3 pb-4">';
            if (isset($item['title'])) {
                $html .= '<h5 class="mb-1">' . $this->e($item['title']) . '</h5>';
            }
            if (isset($item['text'])) {
                $html .= '<p class="text-muted mb-0">' . $this->raw($item['text']) . '</p>';
            }
            if (isset($item['content'])) {
                $html .= $this->raw($item['content']);
            }
            $html .= '</div></div>';
        }

        return $html . '</div>';
    }
}

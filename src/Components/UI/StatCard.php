<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 数据统计卡片（仪表盘小部件）
 *
 * XfAdmin::statCard([
 *     'title'   => '总用户',
 *     'value'   => '12,480',
 *     'icon'    => 'ti ti-users',
 *     'variant' => 'primary',
 *     'trend'   => ['text' => '+12.5%', 'direction' => 'up', 'label' => '较上周'],
 *     'url'     => '/users',
 *     'counter' => 12480,   // 传数字启用数字滚动动画
 * ])
 */
class StatCard extends Component
{
    protected function defaults(): array
    {
        return [
            'title'   => '',
            'value'   => '',
            'icon'    => null,
            'variant' => 'primary',
            'trend'   => null,
            'url'     => null,
            'counter' => null,
            'prefix'  => '',
            'suffix'  => '',
        ];
    }

    protected function html(): string
    {
        $variant = $this->e($this->get('variant'));

        $value = $this->get('counter') !== null
            ? '<span data-xf="counter" data-xf-config="' . $this->e(json_encode([
                'target' => $this->get('counter'),
                'prefix' => $this->get('prefix'),
                'suffix' => $this->get('suffix'),
            ])) . '">0</span>'
            : $this->e($this->get('prefix')) . $this->e($this->get('value')) . $this->e($this->get('suffix'));

        $html  = '<div' . $this->attrs(['class' => 'card']) . '><div class="card-body">';
        $html .= '<div class="d-flex align-items-center justify-content-between">';
        $html .= '<div><h5 class="text-muted fs-13 text-uppercase" title="' . $this->e($this->get('title')) . '">' . $this->e($this->get('title')) . '</h5>';
        $html .= '<div class="d-flex align-items-center gap-2 my-2 py-1">';
        if ($this->get('icon')) {
            $html .= '<div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-' . $variant . '-subtle text-' . $variant . ' rounded fs-22"><i class="' . $this->e($this->get('icon')) . '"></i></span></div>';
        }
        $html .= '<h3 class="mb-0 fw-bold">' . $value . '</h3>';
        $html .= '</div>';

        $trend = $this->get('trend');
        if ($trend) {
            $trend = is_array($trend) ? $trend : ['text' => $trend];
            $up    = ($trend['direction'] ?? 'up') === 'up';
            $html .= '<p class="mb-0 text-muted"><span class="text-' . ($up ? 'success' : 'danger') . ' me-1">'
                . '<i class="ti ti-arrow-' . ($up ? 'up' : 'down') . '"></i> ' . $this->e($trend['text'] ?? '') . '</span>'
                . '<span class="text-nowrap">' . $this->e($trend['label'] ?? '') . '</span></p>';
        }
        $html .= '</div>';

        if ($this->get('url')) {
            $html .= '<a href="' . $this->e($this->get('url')) . '" class="link-secondary fs-24"><i class="ti ti-chevron-right"></i></a>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

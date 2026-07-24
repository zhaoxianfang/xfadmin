<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 仪表盘小部件（widgets.html / index.html）—— 多种预设样式
 *
 * XfAdmin::widget([
 *     'style'   => 'icon',          // icon|progress|chart|minimal
 *     'title'   => '总营收',
 *     'value'   => '¥52,000',
 *     'icon'    => 'ti ti-currency-yen',
 *     'variant' => 'primary',
 *     'trend'   => ['value' => '8.2%', 'up' => true, 'text' => '较上周'],
 *     'progress'=> 72,             // style=progress 时
 *     'footer'  => null,
 * ])
 */
class Widget extends Component
{
    protected function defaults(): array
    {
        return [
            'style'    => 'icon',
            'title'    => '',
            'value'    => '',
            'icon'     => null,
            'variant'  => 'primary',
            'trend'    => null,
            'progress' => null,
            'footer'   => null,
        ];
    }

    protected function html(): string
    {
        $variant = $this->e($this->get('variant'));

        $html = '<div' . $this->attrs(['class' => 'card']) . '><div class="card-body">';
        $html .= '<div class="d-flex align-items-center justify-content-between">';

        $html .= '<div>';
        $html .= '<h4 class="mb-1">' . $this->raw($this->get('value')) . '</h4>';
        $html .= '<p class="text-muted mb-0">' . $this->e($this->get('title')) . '</p>';
        $html .= '</div>';

        if ($this->get('icon')) {
            $html .= '<div class="avatar-md bg-' . $variant . '-subtle rounded flex-centered">'
                . '<i class="' . $this->e($this->get('icon')) . ' fs-24 text-' . $variant . '"></i></div>';
        }
        $html .= '</div>';

        // 趋势
        if ($this->get('trend')) {
            $t = (array) $this->get('trend');
            $up = ! empty($t['up']);
            $cls = $up ? 'text-success' : 'text-danger';
            $arrow = $up ? 'ti-arrow-up-right' : 'ti-arrow-down-right';
            $html .= '<p class="mb-0 mt-2"><span class="' . $cls . '"><i class="ti ' . $arrow . '"></i> ' . $this->e($t['value'] ?? '') . '</span> <span class="text-muted">' . $this->e($t['text'] ?? '') . '</span></p>';
        }

        // 进度条
        if ($this->get('style') === 'progress' && $this->get('progress') !== null) {
            $p = max(0, min(100, (int) $this->get('progress')));
            $html .= '<div class="progress mt-3" style="height:6px;"><div class="progress-bar bg-' . $variant . '" style="width:' . $p . '%"></div></div>';
        }

        if ($this->get('footer')) {
            $html .= '<div class="mt-2">' . $this->raw($this->get('footer')) . '</div>';
        }

        return $html . '</div></div>';
    }
}

<?php

declare(strict_types=1);

namespace XfAdmin\Components\Misc;

use XfAdmin\Components\Component;

/**
 * 日历（FullCalendar）
 *
 * XfAdmin::calendar([
 *     'events' => [['title' => '会议', 'start' => '2026-07-23', 'className' => 'bg-primary']],
 *     'editable' => true,
 *     'options'  => [ ...透传 FullCalendar 配置... ],
 * ])
 */
class Calendar extends Component
{
    protected function defaults(): array
    {
        return [
            'events'   => [],
            'editable' => false,
            'locale'   => 'zh-cn',
            'options'  => [],
        ];
    }

    protected function assets(): array
    {
        return ['fullcalendar'];
    }

    protected function html(): string
    {
        $config = array_replace_recursive([
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
            ],
            'locale'   => $this->get('locale'),
            'editable' => (bool) $this->get('editable'),
            'events'   => array_values((array) $this->get('events', [])),
        ], (array) $this->get('options', []));

        return '<div' . $this->attrs([
            'id'             => $this->resolveId('xf-calendar'),
            'data-xf'        => 'calendar',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

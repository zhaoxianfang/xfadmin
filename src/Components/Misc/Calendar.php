<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 日历（FullCalendar，对齐 INSPINIA calendar.html）
 *
 * - 包在 .card 内；提供 externalEvents 时渲染左侧「可拖拽事件」栏（两栏布局）。
 * - 配置向 FullCalendar 透传；默认补齐 INSPINIA 同款选项（bootstrap 主题 / 视图切换 / 可编辑 / 可拖入）。
 *
 * XfAdmin::calendar([
 *     'events' => [['title' => '会议', 'start' => '2026-07-23', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary']],
 *     'editable' => true,
 *     'externalEvents' => [
 *         ['label' => '工作', 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary'],
 *         ['label' => '私人', 'className' => 'bg-success-subtle text-success border-start border-3 border-success'],
 *     ],
 *     'options' => [ ... ],
 * ])
 */
class Calendar extends Component
{
    protected function defaults(): array
    {
        return [
            'events'        => [],
            'editable'      => false,
            'locale'        => 'zh-cn',
            'externalEvents' => [],
            'addText'       => '新建事件',
            'options'       => [],
        ];
    }

    protected function assets(): array
    {
        return ['fullcalendar'];
    }

    protected function html(): string
    {
        $editable = (bool) $this->get('editable');
        $external = (array) $this->get('externalEvents', []);
        $addText  = $this->get('addText');

        $config = array_replace_recursive([
            'initialView'   => 'dayGridMonth',
            'themeSystem'   => 'bootstrap',
            'locale'        => $this->get('locale'),
            'editable'      => $editable,
            'droppable'     => $editable,
            'selectable'    => true,
            'nowIndicator'  => true,
            'dayMaxEvents'  => true,
            'buttonText'    => ['today' => '今天', 'month' => '月', 'week' => '周', 'day' => '日', 'list' => '列表', 'prev' => '上一页', 'next' => '下一页'],
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
            ],
            'events' => array_values((array) $this->get('events', [])),
        ], (array) $this->get('options', []));

        // 外部事件由前端 Draggable 处理，不进 FullCalendar 初始事件
        if ($external) {
            $config['externalEvents'] = true;
        }

        $calendarDiv = '<div' . $this->attrs([
            'id'             => $this->resolveId('xf-calendar'),
            'data-xf'        => 'calendar',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';

        if (!$external) {
            return '<div class="card"><div class="card-body">' . $calendarDiv . '</div></div>';
        }

        // 两栏：左 外部事件 + 右 日历
        $side = '<div class="col-xl-3 col-lg-4 border-end p-3">';
        $side .= '<button class="btn btn-primary w-100 mb-3 btn-new-event"><i class="ti ti-plus me-1"></i>' . $this->e($addText) . '</button>';
        $side .= '<div id="external-events">';
        $side .= '<p class="text-muted small mb-2">拖动事件到日历</p>';
        foreach ($external as $ev) {
            $ev = is_array($ev) ? $ev : ['label' => (string) $ev, 'className' => 'bg-primary-subtle text-primary border-start border-3 border-primary'];
            $cls = $ev['className'] ?? 'bg-primary-subtle text-primary border-start border-3 border-primary';
            $side .= '<div class="external-event fc-event ' . $this->e($cls) . ' fw-semibold mb-2 p-2 rounded" data-class="' . $this->e($cls) . '">' . $this->e($ev['label'] ?? '') . '</div>';
        }
        $side .= '</div></div>';

        return '<div class="card" data-calendar-root><div class="row g-0"><' . $side
            . '<div class="col-xl-9 col-lg-8"><div class="card-body p-3">' . $calendarDiv . '</div></div>'
            . '</div></div>';
    }
}

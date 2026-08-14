<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 任务清单面板（Tasks）—— 严格对齐 INSPINIA v4 `tasks.html` 的看板式任务列表结构。
 *
 * 输出蓝本：
 *   <div class="card xf-tasklist">
 *     <div class="card-header"> 标题 + 过滤（全部/进行中/已完成）+ 新建按钮
 *     <div class="list-group xf-task-group">
 *       每个任务：
 *         <label class="list-group-item xf-task-item">  ← 含自定义勾选框 + 标题 + 元信息（优先级徽标/指派人/截止日）
 *
 * 前端交互（勾选完成、过滤）由 xfadmin.js 的 initTaskList 驱动。
 *
 * XfAdmin::taskList([
 *     'title'  => '我的任务',
 *     'tasks'  => [
 *         [
 *             'id'        => 1,
 *             'title'     => '完成季度报表',
 *             'done'      => false,
 *             'priority'  => 'high',        // high|medium|low
 *             'assignee'  => '张三',
 *             'avatar'    => 'users/user-1.jpg',
 *             'due'       => '2026-08-20',
 *             'tag'       => '报表',
 *         ],
 *     ],
 *     'filterable' => true,
 *     'addable'    => true,
 *     'add_url'    => '#',
 * ])
 */
class TaskList extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'      => '',
            'tasks'      => [],
            'filterable' => true,
            'addable'    => true,
            'add_url'    => 'javascript:void(0);',
        ];
    }
    private const PRIORITY = [
        'high'   => ['danger', '高'],
        'medium' => ['warning', '中'],
        'low'    => ['info', '低'],
    ];

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $tasks = (array) $this->get('tasks');
        $title = $this->e($this->get('title'));

        $head = '';
        if ($title !== '' || $this->get('filterable') || $this->get('addable')) {
            $head .= '<div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">';
            $head .= '<h5 class="card-title mb-0">' . $title . '</h5>';
            $head .= '<div class="d-flex align-items-center gap-2">';
            if ($this->get('filterable')) {
                $head .= '<div class="btn-group btn-group-sm xf-task-filter" role="group">'
                    . '<button type="button" class="btn btn-soft-primary active" data-filter="all">全部</button>'
                    . '<button type="button" class="btn btn-light" data-filter="active">进行中</button>'
                    . '<button type="button" class="btn btn-light" data-filter="done">已完成</button>'
                    . '</div>';
            }
            if ($this->get('addable')) {
                $head .= '<a href="' . $this->e($this->get('add_url')) . '" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>新建</a>';
            }
            $head .= '</div></div>';
        }
        $list = '<div class="list-group list-group-flush xf-task-group">';
        if ($tasks === []) {
            $list .= '<div class="list-group-item text-muted text-center py-4">暂无任务</div>';
        }
        foreach ($tasks as $t) {
            $t        = (array) $t;
            $id       = $t['id'] ?? '';
            $done     = ! empty($t['done']);
            $prio     = $t['priority'] ?? 'medium';
            [$pv, $pl] = self::PRIORITY[$prio] ?? self::PRIORITY['medium'];
            $avatar   = $t['avatar'] ?? '';
            $assignee = $t['assignee'] ?? '';
            $due      = $t['due'] ?? '';
            $tag      = $t['tag'] ?? '';
            $meta     = [];
            if ($tag !== '') {
                $meta[] = '<span class="badge bg-secondary-subtle text-secondary">' . $this->e($tag) . '</span>';
            }
            $meta[] = '<span class="badge bg-' . $pv . '-subtle text-' . $pv . '">优先级·' . $pl . '</span>';
            if ($assignee !== '') {
                $av = $avatar !== ''
                    ? '<img src="' . $this->e($avatar) . '" class="avatar-xs rounded-circle me-1" alt="">'
                    : '<span class="avatar-xs rounded-circle bg-' . $pv . '-subtle text-' . $pv . ' me-1 d-inline-flex align-items-center justify-content-center"><i class="ti ti-user fs-xs"></i></span>';
                $meta[] = '<span class="d-inline-flex align-items-center">' . $av . $this->e($assignee) . '</span>';
            }
            if ($due !== '') {
                $meta[] = '<span class="text-muted"><i class="ti ti-calendar-event me-1"></i>' . $this->e($due) . '</span>';
            }
            $list .= '<label class="list-group-item xf-task-item d-flex align-items-start gap-2 py-2' . ($done ? ' xf-task-done' : '') . '" data-done="' . ($done ? '1' : '0') . '">'
                . '<input class="form-check-input mt-1 xf-task-check" type="checkbox"' . ($done ? ' checked' : '') . ' data-id="' . $this->e((string) $id) . '">'
                . '<div class="flex-grow-1">'
                . '<div class="xf-task-title fw-medium">' . $this->e($t['title'] ?? '') . '</div>'
                . '<div class="d-flex flex-wrap align-items-center gap-2 mt-1 small">' . implode('', $meta) . '</div>'
                . '</div></label>';
        }
        $list .= '</div>';

        return '<div class="card xf-tasklist">' . $head . $list . '</div>';
    }
}

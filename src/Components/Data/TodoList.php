<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 待办清单（dashboard widget / apps-tasks.html）
 *
 * XfAdmin::todoList([
 *     'title'  => '今日任务',
 *     'items'  => [
 *         ['text' => '回顾周报', 'done' => true,  'priority' => 'high'],
 *         ['text' => '发布版本', 'done' => false, 'priority' => 'medium'],
 *     ],
 *     'addable' => true,        // 允许新增
 * ])
 */
class TodoList extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'    => '',
            'items'    => [],
            'addable'  => false,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        $html = '<div' . $this->attrs(['class' => 'card xf-todo', 'data-xf' => 'todo']) . '>';
        if ($this->get('title')) {
            $html .= '<div class="card-header bg-transparent border-bottom d-flex align-items-center">' .
                '<h6 class="mb-0 fw-semibold">' . $this->e($this->get('title')) . '</h6>' .
                '<span class="badge bg-primary-subtle text-primary ms-auto xf-todo-count"></span></div>';
        }
        $html .= '<div class="card-body p-2">';
        $html .= '<ul class="list-group list-group-flush xf-todo-list">';
        foreach ($items as $it) {
            $text = is_array($it) ? ($it['text'] ?? '') : $it;
            $done = is_array($it) ? ! empty($it['done']) : false;
            $prio = is_array($it) ? ($it['priority'] ?? '') : '';
            $bar = $prio ? ' border-start border-3 border-' . Html::e($prio === 'high' ? 'danger' : ($prio === 'medium' ? 'warning' : 'success')) : '';
            $html .= '<li class="list-group-item d-flex align-items-center gap-2 px-2 py-2' . ($done ? ' xf-todo-done' : '') . $bar . '">';
            $html .= '<div class="form-check mb-0"><input class="form-check-input xf-todo-check" type="checkbox"' . ($done ? ' checked' : '') . '></div>';
            $html .= '<span class="flex-grow-1">' . $this->e($text) . '</span>';
            $html .= '<button type="button" class="btn btn-sm btn-link text-muted xf-todo-del" aria-label="删除"><i class="ti ti-x"></i></button>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        if ($this->get('addable')) {
            $html .= '<form class="d-flex gap-2 p-2 xf-todo-add">' .
                '<input type="text" class="form-control form-control-sm" placeholder="添加新任务…" aria-label="新任务">' .
                '<button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i></button></form>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

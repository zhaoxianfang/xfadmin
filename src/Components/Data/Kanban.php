<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 看板（kanban）组件 —— 对齐 INSPINIA project-kanban.html
 *
 * 结构（与模板完全一致）：
 *   .kanban-app > .card(.card-header 搜索/新增 + .card-body.p-0 > .kanban-content)
 *     > .kanban-board(列) > .kanban-item(列头) + .kanban-board-group(data-simplebar, data-column)
 *       > ul[data-kanban-list][data-plugins=sortable] > li.kanban-item > .card.shadow.border-light
 *
 * XfAdmin::kanban([
 *     'columns' => [
 *         ['id'=>'todo','title'=>'待办','variant'=>'danger',
 *          'cards'=>[
 *             ['title'=>'设计首页','label'=>'设计','variant'=>'info','text'=>'…','members'=>['users/1.jpg'],
 *              'due'=>'今天','progress'=>60,'comments'=>3,'attachments'=>1],
 *          ]],
 *     ],
 *     'search' => true,            // 顶栏搜索框
 *     'addText' => '新建卡片',      // 列头 / 顶栏新增按钮文案
 * ])
 */
class Kanban extends Component
{
    protected function defaults(): array
    {
        return [
            'columns'  => [],
            'search'   => true,
            'addText'  => '新建卡片',
            'class'    => '',
        ];
    }

    protected function html(): string
    {
        $columns  = (array) $this->get('columns', []);
        $search   = $this->get('search');
        $addText  = $this->get('addText');
        $id       = $this->uid('kanban');

        // 顶栏：搜索 + 新增（对齐模板 .kanban-app > .card > .card-header）
        $header = '<div class="card-header border-light align-items-center gap-2">';
        if ($search) {
            $header .= '<div class="app-search flex-grow-1" style="max-width:320px">'
                . '<input type="text" class="form-control" placeholder="搜索任务…" data-kanban-search>'
                . '<span class="app-search-icon"><i class="ti ti-search"></i></span></div>';
        }
        $header .= '<div class="ms-auto d-flex align-items-center gap-2">';
        $header .= '<button type="button" class="btn btn-primary btn-sm" data-kanban-add><i class="ti ti-plus me-1"></i>' . $this->e($addText) . '</button>';
        $header .= '</div></div>';

        $boards = '';
        foreach ($columns as $col) {
            $col    = (array) $col;
            $colId  = (string) ($col['id'] ?? 'col');
            $cards  = (array) ($col['cards'] ?? []);
            $variant = $col['variant'] ?? 'primary';

            $boards .= '<div class="kanban-board" data-column="' . $this->e($colId) . '">';

            // 列头：标题(计数) + 新增按钮（对齐模板 .kanban-item.py-2.px-3）
            $boards .= '<div class="kanban-item py-2 px-3 d-flex align-items-center">';
            $boards .= '<h5 class="m-0"><i class="ti ti-point-filled text-' . $this->e($variant) . ' me-2"></i>'
                . $this->e($col['title'] ?? '') . ' <span class="text-muted fw-normal">(' . count($cards) . ')</span></h5>';
            $boards .= '<a href="#" class="ms-auto btn btn-sm btn-icon rounded-circle btn-soft-' . $this->e($variant) . '" data-kanban-add title="' . $this->e($addText) . '"><i class="ti ti-plus"></i></a>';
            $boards .= '</div>';

            // 卡片组：可滚动 + 拖拽容器（对齐模板 .kanban-board-group[+data-simplebar]）
            $boards .= '<div class="kanban-board-group px-3" data-column="' . $this->e($colId) . '">';
            $boards .= '<ul class="list-unstyled mb-0" data-kanban-list data-plugins="sortable">';
            foreach ($cards as $c) {
                $boards .= $this->renderCard((array) $c);
            }
            $boards .= '</ul></div>'; // /.kanban-board-group / ul

            $boards .= '</div>'; // /.kanban-board
        }

        $html = '<div class="kanban-app ' . $this->e($this->get('class')) . '" id="' . $id . '" data-xf="kanban">';
        $html .= '<div class="card h-100 mb-0 flex-grow-1">';
        $html .= $header;
        $html .= '<div class="card-body p-0"><div class="kanban-content bg-light bg-opacity-40">' . $boards . '</div></div>';
        $html .= '</div></div>';

        return $html;
    }

    // 单张卡片（对齐 project-kanban.html 的 .card.shadow.border-light.mb-2 > .card-body）
    protected function renderCard(array $c): string
    {
        $variant = $c['variant'] ?? 'primary';

        $html = '<li class="kanban-item"><div class="card shadow border-light mb-2"><div class="card-body">';

        // 顶部：标签徽标（badge-soft + ti-point-filled）+ 操作下拉
        $html .= '<div class="d-flex align-items-center mb-2">';
        if (! empty($c['label'])) {
            $html .= '<span class="badge p-1 badge-soft-' . $this->e($variant) . '"><i class="ti ti-point-filled"></i> ' . $this->e($c['label']) . '</span>';
        }
        $html .= '<div class="ms-auto"><div class="dropdown">'
            . '<a href="#" class="btn btn-icon btn-sm btn-ghost-light text-muted" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical fs-xl"></i></a>'
            . '<ul class="dropdown-menu dropdown-menu-end">'
            . '<li><a class="dropdown-item" href="#"><i class="ti ti-edit me-2"></i>编辑</a></li>'
            . '<li><a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i>删除</a></li>'
            . '</ul></div></div>';
        $html .= '</div>';

        // 标题（link-reset）
        if (! empty($c['title'])) {
            $html .= '<h5 class="mb-3"><a href="#!" class="link-reset">' . $this->e($c['title']) . '</a></h5>';
        }
        if (! empty($c['text'])) {
            $html .= '<p class="text-muted mb-2">' . $this->e($c['text']) . '</p>';
        }

        // 底部：成员头像组 + 截止日期 + 可选 评论/附件
        $meta = '';
        if (! empty($c['comments']) || ! empty($c['attachments'])) {
            $meta .= '<div class="d-flex align-items-center gap-3 text-muted small mt-2">';
            if (! empty($c['comments'])) {
                $meta .= '<span><i class="ti ti-message-2 me-1"></i>' . (int) $c['comments'] . '</span>';
            }
            if (! empty($c['attachments'])) {
                $meta .= '<span><i class="ti ti-paperclip me-1"></i>' . (int) $c['attachments'] . '</span>';
            }
            $meta .= '</div>';
        }

        $members = (array) ($c['members'] ?? []);
        $due     = (string) ($c['due'] ?? '');
        if ($members || $due || $meta) {
            $html .= '<div class="d-flex justify-content-between align-items-center">';
            if ($members) {
                $html .= '<div class="avatar-group avatar-group-xs">';
                foreach ($members as $m) {
                    $html .= '<div class="avatar"><img src="' . $this->e(XfAdmin::img((string) $m)) . '" class="rounded-circle avatar-xs" alt=""></div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<span></span>';
            }
            if ($due) {
                $html .= '<div class="d-flex align-items-center gap-1 text-muted small"><i class="ti ti-calendar-time fs-lg"></i><span>' . $this->e($due) . '</span></div>';
            }
            $html .= '</div>';
            $html .= $meta;
        }

        // 进度条
        if (isset($c['progress']) && $c['progress'] !== '' && $c['progress'] !== null) {
            $p = (int) $c['progress'];
            $html .= '<div class="mt-3"><div class="d-flex justify-content-between mb-1"><span class="text-muted small">进度</span><span class="small fw-medium">' . $p . '%</span></div>'
                . '<div class="progress" style="height:5px"><div class="progress-bar bg-' . $this->e($variant) . '" style="width:' . $p . '%"></div></div></div>';
        }

        $html .= '</div></div></li>';

        return $html;
    }

    public function assets(): array
    {
        return ['sortablejs', 'simplebar'];
    }
}

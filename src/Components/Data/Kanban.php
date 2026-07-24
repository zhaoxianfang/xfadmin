<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 看板（project-kanban.html）—— 支持拖拽排序（SortableJS）
 *
 * XfAdmin::kanban([
 *     'columns' => [
 *         [
 *             'id' => 'todo', 'title' => '待办', 'variant' => 'secondary',
 *             'cards' => [
 *                 ['title' => '设计首页', 'text' => '...', 'badge' => ['text'=>'高','variant'=>'danger'],
 *                  'members' => ['users/avatar-1.jpg'], 'meta' => ['comments'=>3,'attachments'=>1]],
 *             ],
 *         ],
 *     ],
 *     'sortable' => true,
 * ])
 */
class Kanban extends Component
{
    protected function defaults(): array
    {
        return [
            'columns'  => [],
            'sortable' => true,
        ];
    }

    protected function assets(): array
    {
        return $this->get('sortable') ? ['sortablejs'] : [];
    }

    protected function html(): string
    {
        $id = $this->resolveId('kanban');
        $sortable = $this->get('sortable') ? ' data-xf="kanban"' : '';

        $html = '<div' . $this->attrs(['class' => 'kanban-board d-flex gap-3 overflow-auto pb-2', 'id' => $id]) . $sortable . '>';

        foreach ((array) $this->get('columns', []) as $col) {
            $variant = $col['variant'] ?? 'secondary';
            $cards   = (array) ($col['cards'] ?? []);
            $html .= '<div class="kanban-board-group flex-shrink-0" style="width:300px;" data-column="' . $this->e($col['id'] ?? '') . '">';
            $html .= '<div class="d-flex align-items-center justify-content-between mb-2">';
            $html .= '<h5 class="mb-0"><span class="badge bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' me-1">' . count($cards) . '</span>' . $this->e($col['title'] ?? '') . '</h5>';
            $html .= '<button class="btn btn-sm btn-icon btn-light" type="button"><i class="ti ti-plus"></i></button>';
            $html .= '</div>';
            $html .= '<div class="kanban-content bg-light bg-opacity-50 rounded p-2" data-kanban-list style="min-height:60px;">';

            foreach ($cards as $card) {
                $html .= $this->card($card);
            }

            $html .= '</div></div>';
        }

        return $html . '</div>';
    }

    private function card(array $card): string
    {
        $html = '<div class="card kanban-item mb-2" draggable="true">';
        $html .= '<div class="card-body p-3">';

        if (! empty($card['badge'])) {
            $b = (array) $card['badge'];
            $html .= '<span class="badge bg-' . $this->e($b['variant'] ?? 'primary') . '-subtle text-' . $this->e($b['variant'] ?? 'primary') . ' mb-2">' . $this->e($b['text'] ?? '') . '</span>';
        }

        $html .= '<h5 class="mb-1">' . $this->e($card['title'] ?? '') . '</h5>';
        if (! empty($card['text'])) {
            $html .= '<p class="text-muted mb-2 fs-xs">' . $this->e($card['text']) . '</p>';
        }
        if (! empty($card['progress'])) {
            $html .= '<div class="progress mb-2" style="height:5px;"><div class="progress-bar" style="width:' . (int) $card['progress'] . '%"></div></div>';
        }

        $html .= '<div class="d-flex align-items-center justify-content-between">';
        // 成员头像
        $members = (array) ($card['members'] ?? []);
        if ($members) {
            $html .= '<div class="avatar-group">';
            foreach ($members as $m) {
                $html .= '<div class="avatar avatar-xs"><img src="' . $this->e(\XfAdmin\XfAdmin::asset('images/' . ltrim((string) $m, '/'))) . '" class="rounded-circle" alt=""></div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<span></span>';
        }
        // meta
        $meta = (array) ($card['meta'] ?? []);
        if ($meta) {
            $html .= '<div class="d-flex gap-2 text-muted fs-xs">';
            if (! empty($meta['comments'])) {
                $html .= '<span><i class="ti ti-message-2"></i> ' . (int) $meta['comments'] . '</span>';
            }
            if (! empty($meta['attachments'])) {
                $html .= '<span><i class="ti ti-paperclip"></i> ' . (int) $meta['attachments'] . '</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html . '</div></div>';
    }
}

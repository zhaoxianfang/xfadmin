<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 项目列表（projects.html / projects-list.html）
 *
 * XfAdmin::projects([
 *     'title' => '项目管理',
 *     'projects' => [
 *         [
 *             'name' => '官网改版', 'client' => 'XX 集团', 'description' => '…',
 *             'progress' => 75, 'status' => 'active', // active|pending|completed|onhold
 *             'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k',
 *             'tasks_done' => 20, 'tasks_total' => 30,
 *             'members' => ['users/user-1.jpg','users/user-2.jpg'],
 *             'color' => 'primary',
 *         ],
 *     ],
 * ])
 */
class Projects extends Component
{
    protected function defaults(): array
    {
        return ['title' => '', 'projects' => [], 'view' => 'grid'];
    }

    private const STATUS = [
        'active'   => ['success', '进行中'],
        'pending'  => ['warning', '待启动'],
        'completed'=> ['primary', '已完成'],
        'onhold'   => ['secondary', '已暂停'],
    ];

    protected function html(): string
    {
        $projects = (array) $this->get('projects', []);
        if (empty($projects)) {
            return '';
        }

        $title = $this->get('title') ? '<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">' . $this->e($this->get('title')) . '</h5><button class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>新建项目</button></div>' : '';
        $html  = $title . '<div class="row g-3">';

        foreach ($projects as $p) {
            $p      = (array) $p;
            $status = $p['status'] ?? 'active';
            [$sCls, $sTxt] = self::STATUS[$status] ?? self::STATUS['active'];
            $color  = $p['color'] ?? 'primary';
            $prog   = (int) ($p['progress'] ?? 0);

            $html .= '<div class="col-xl-4 col-md-6">';
            $html .= '<div class="card h-100">';
            $html .= '<div class="card-body">';
            $html .= '<div class="d-flex justify-content-between align-items-start mb-2"><div><h6 class="mb-0">' . $this->e($p['name'] ?? '') . '</h6><small class="text-muted">' . $this->e($p['client'] ?? '') . '</small></div>'
                . '<span class="badge bg-' . $sCls . '-subtle text-' . $sCls . '">' . $sTxt . '</span></div>';
            $html .= '<p class="text-muted small">' . $this->e($p['description'] ?? '') . '</p>';

            $html .= '<div class="d-flex justify-content-between small mb-1"><span>进度</span><span class="fw-semibold">' . $prog . '%</span></div>';
            $html .= '<div class="progress mb-3" style="height:6px"><div class="progress-bar bg-' . $this->e($color) . '" style="width:' . $prog . '%"></div></div>';

            $html .= '<div class="row g-2 text-center small mb-3">';
            $html .= '<div class="col"><div class="fw-semibold">' . (int) ($p['tasks_done'] ?? 0) . '/' . (int) ($p['tasks_total'] ?? 0) . '</div><div class="text-muted">任务</div></div>';
            $html .= '<div class="col"><div class="fw-semibold">' . $this->e($p['budget'] ?? '-') . '</div><div class="text-muted">预算</div></div>';
            $html .= '<div class="col"><div class="fw-semibold">' . $this->e($p['deadline'] ?? '-') . '</div><div class="text-muted">截止</div></div>';
            $html .= '</div>';

            $avatarGroup = '';
            foreach ((array) ($p['members'] ?? []) as $m) {
                $avatarGroup .= '<div class="avatar avatar-xs"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $m)) . '" class="rounded-circle" alt=""></div>';
            }
            $html .= '<div class="d-flex align-items-center justify-content-between border-top pt-3"><div class="avatar-group">' . $avatarGroup . '</div>'
                . '<a href="#" class="btn btn-sm btn-light">查看 <i class="ti ti-arrow-right"></i></a></div>';

            $html .= '</div></div></div>';
        }

        return $html . '</div>';
    }
}

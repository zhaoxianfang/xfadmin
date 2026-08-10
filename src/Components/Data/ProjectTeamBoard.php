<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 项目团队看板（INSPINIA project-team-board.html）
 *
 * XfAdmin::projectTeamBoard([
 *     'title' => '项目团队',
 *     'cols'  => 3,
 *     'teams' => [
 *         [
 *             'code'     => 'IT-01',
 *             'name'     => 'Design Team',
 *             'badge'    => ['text' => 'New', 'variant' => 'primary'],
 *             'members'  => ['users/user-7.jpg', 'users/user-8.jpg', 'users/user-9.jpg', 'users/user-10.jpg'],
 *             'about'    => '负责 UI/UX 设计与品牌一致性。',
 *             'projects' => 25,
 *             'ranking'  => '#5',
 *             'budgets'  => '$20.3M',
 *             'progress' => 65,
 *             'updated'  => '1 hour ago',
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class ProjectTeamBoard extends Component
{
    protected function defaults(): array
    {
        return [
            'title' => '项目团队',
            'cols'  => 3,
            'teams' => [],
        ];
    }

    protected function html(): string
    {
        $teams = (array) $this->get('teams', []);
        if (empty($teams)) {
            return '';
        }
        $n = max(1, min(4, (int) $this->get('cols', 3)));
        $colCls = $n === 1 ? 'col-12' : 'col-12 col-md-6 col-xl-' . (int) (12 / $n);

        $html = '<div' . $this->attrs(['class' => 'xf-project-board']) . '>';
        $html .= '<div class="d-flex align-items-sm-center flex-sm-row flex-column mb-3">'
            . '<h5 class="mb-0 me-3">' . $this->e($this->get('title')) . '</h5>'
            . '<button class="btn btn-sm btn-success ms-sm-auto"><i class="ti ti-plus me-1"></i>新建团队</button></div>';

        $html .= '<div class="row">';
        foreach ($teams as $t) {
            $html .= '<div class="' . $colCls . '"><div class="card card-h-100"><div class="card-header">';
            $html .= '<h4 class="card-title">' . $this->e($t['code'] ?? '') . ' - ' . $this->e($t['name'] ?? '');
            if (! empty($t['badge'])) {
                $b = $t['badge'];
                $html .= ' <span class="ms-2 badge badge-label text-bg-' . $this->e($b['variant'] ?? 'primary') . '">' . $this->e($b['text'] ?? '') . '</span>';
            }
            $html .= '</h4>';
            $html .= '<div class="dropdown ms-auto"><a href="#" class="text-muted fs-xl" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>'
                . '<ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#"><i class="ti ti-eye me-2"></i>View</a></li>'
                . '<li><a class="dropdown-item" href="#"><i class="ti ti-edit me-2"></i>Edit</a></li>'
                . '<li><a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i>Remove</a></li></ul></div>';
            $html .= '</div><div class="card-body d-flex flex-column justify-content-between">';

            $members = (array) ($t['members'] ?? []);
            $html .= '<p class="mb-2 text-muted">Total ' . count($members) . ' members</p>';
            $html .= '<div class="avatar-group avatar-group-sm mb-3">';
            $shown = 0;
            foreach ($members as $m) {
                if ($shown >= 4) {
                    $html .= '<div class="avatar"><span class="avatar-title rounded-circle bg-light text-muted">+' . (count($members) - 4) . '</span></div>';
                    break;
                }
                $html .= '<div class="avatar">';
                if (! empty($m)) {
                    $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $m)) . '" alt="" class="rounded-circle avatar-sm">';
                }
                $html .= '</div>';
                $shown++;
            }
            $html .= '</div>';

            if (! empty($t['about'])) {
                $html .= '<div class="mb-3"><h6 class="fs-base mb-2">About Team:</h6><p class="text-muted mb-0">' . $this->e($t['about']) . '</p></div>';
            }

            $html .= '<div class="row">';
            foreach ([['ti ti-layout-kanban', 'Projects', $t['projects'] ?? '0'], ['ti ti-award', 'Ranking', $t['ranking'] ?? '-'], ['ti ti-wallet', 'Budgets', $t['budgets'] ?? '-']] as [$icon, $label, $value]) {
                $html .= '<div class="col-4"><div class="d-flex gap-2 mb-3 mb-xl-0"><div class="avatar-sm flex-shrink-0"><span class="avatar-title text-bg-light rounded-circle"><i class="' . $icon . ' fs-lg text-primary"></i></span></div>'
                    . '<div><h6 class="mb-1 text-muted text-uppercase fs-xs">' . $this->e($label) . '</h6><p class="fw-medium mb-0">' . $this->e($value) . '</p></div></div></div>';
            }
            $html .= '</div>';

            $progress = (int) ($t['progress'] ?? 0);
            $html .= '<div class="my-3"><div class="d-flex align-items-center justify-content-between mb-2">'
                . '<p class="mb-0 text-muted fw-semibold fs-xxs">Status of current project</p>'
                . '<p class="fw-semibold mb-0">' . $progress . '%</p></div>'
                . '<div class="progress progress-md"><div class="progress-bar" style="width:' . $progress . '%;"></div></div></div>';

            $html .= '<div class="d-flex justify-content-between align-items-center">'
                . '<span class="text-muted fs-xs"><i class="ti ti-clock me-1"></i>Updated ' . $this->e($t['updated'] ?? '') . '</span>'
                . '<a href="' . $this->e($t['url'] ?? '#') . '" class="btn btn-sm btn-primary rounded-pill">Details</a></div>';

            $html .= '</div></div></div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

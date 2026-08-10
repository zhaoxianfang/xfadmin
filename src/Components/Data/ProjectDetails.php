<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 项目详情（project-details.html）
 *
 * XfAdmin::projectDetails([
 *     'project' => [
 *         'name' => '官网改版', 'client' => 'XX 集团', 'description' => '…',
 *         'progress' => 75, 'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k',
 *         'members' => [['name'=>'张三','avatar'=>'users/user-1.jpg','role'=>'设计']],
 *         'tasks' => [['title'=>'首页设计','done'=>true],['title'=>'前端开发','done'=>false]],
 *         'activity' => [['user'=>'李四','avatar'=>'users/user-2.jpg','text'=>'更新了设计稿','time'=>'2 小时前']],
 *         'files' => [['name'=>'需求文档.pdf','size'=>'1.2MB']],
 *     ],
 * ])
 */
class ProjectDetails extends Component
{
    protected function defaults(): array
    {
        return ['project' => []];
    }

    protected function html(): string
    {
        $p = (array) $this->get('project', []);
        if (empty($p)) {
            return '';
        }

        $prog = (int) ($p['progress'] ?? 0);
        $html = '<div class="row g-3">';

        // 主区
        $html .= '<div class="col-lg-8">';
        $html .= '<div class="card mb-3"><div class="card-body">';
        $html .= '<div class="d-flex justify-content-between align-items-start mb-2"><div><h4 class="mb-1">' . $this->e($p['name'] ?? '') . '</h4><span class="text-muted">' . $this->e($p['client'] ?? '') . '</span></div>'
            . '<div><button class="btn btn-sm btn-primary me-1"><i class="ti ti-edit me-1"></i>编辑</button><button class="btn btn-sm btn-light"><i class="ti ti-dots-vertical"></i></button></div></div>';
        $html .= '<p class="text-muted">' . $this->e($p['description'] ?? '') . '</p>';
        $html .= '<div class="d-flex justify-content-between small mb-1"><span>总体进度</span><span class="fw-semibold">' . $prog . '%</span></div>';
        $html .= '<div class="progress" style="height:8px"><div class="progress-bar" style="width:' . $prog . '%"></div></div></div></div>';

        // 任务清单
        if (! empty($p['tasks'])) {
            $html .= '<div class="card mb-3"><div class="card-header d-flex justify-content-between"><h6 class="mb-0">任务清单</h6><span class="badge bg-light">' . count((array) $p['tasks']) . '</span></div><div class="card-body">';
            $html .= '<div class="list-group list-group-flush">';
            foreach ((array) $p['tasks'] as $t) {
                $t    = (array) $t;
                $done = ! empty($t['done']);
                $html .= '<div class="list-group-item px-0 d-flex align-items-center">';
                $html .= '<i class="ti ti-' . ($done ? 'circle-check text-success' : 'circle text-muted') . ' me-2"></i>';
                $html .= '<span class="' . ($done ? 'text-muted text-decoration-line-through' : '') . '">' . $this->e($t['title'] ?? '') . '</span>';
                if (! empty($t['assignee'])) {
                    $html .= '<span class="ms-auto small text-muted">' . $this->e($t['assignee']) . '</span>';
                }
                $html .= '</div>';
            }
            $html .= '</div></div></div>';
        }

        // 动态
        if (! empty($p['activity'])) {
            $html .= '<div class="card"><div class="card-header"><h6 class="mb-0">项目动态</h6></div><div class="card-body"><div class="timeline-x">';
            foreach ((array) $p['activity'] as $a) {
                $a     = (array) $a;
                $av    = ! empty($a['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $a['avatar']) : '';
                // 时间线节点头像：INSPINIA 规范 .avatar 包裹（avatar-xs=24px）
                $html .= '<div class="timeline-item is-done"><div class="timeline-marker"><span class="avatar avatar-xs"><img src="' . $this->e($av) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span></div>';
                $html .= '<div class="timeline-content"><div><span class="fw-semibold">' . $this->e($a['user'] ?? '') . '</span> ' . $this->e($a['text'] ?? '') . '</div><small class="text-muted">' . $this->e($a['time'] ?? '') . '</small></div></div>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        // 侧栏
        $html .= '<div class="col-lg-4">';
        $html .= '<div class="card mb-3"><div class="card-header"><h6 class="mb-0">概览</h6></div><div class="card-body">';
        $html .= $this->kv('客户', $p['client'] ?? '-');
        $html .= $this->kv('截止日期', $p['deadline'] ?? '-');
        $html .= $this->kv('预算', $p['budget'] ?? '-');
        $html .= $this->kv('已支出', $p['spent'] ?? '-');
        $html .= '</div></div>';

        if (! empty($p['members'])) {
            $html .= '<div class="card mb-3"><div class="card-header"><h6 class="mb-0">团队成员</h6></div><div class="card-body"><div class="list-group list-group-flush">';
            foreach ((array) $p['members'] as $m) {
                $m    = (array) $m;
                $av   = ! empty($m['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $m['avatar']) : '';
                $html .= '<div class="list-group-item px-0 d-flex align-items-center"><div class="avatar avatar-sm me-2"><img src="' . $this->e($av) . '" class="rounded-circle" alt=""></div><div><div class="fw-semibold small">' . $this->e($m['name'] ?? '') . '</div><small class="text-muted">' . $this->e($m['role'] ?? '') . '</small></div></div>';
            }
            $html .= '</div></div></div>';
        }

        if (! empty($p['files'])) {
            $html .= '<div class="card"><div class="card-header"><h6 class="mb-0">附件</h6></div><div class="card-body"><div class="list-group list-group-flush">';
            foreach ((array) $p['files'] as $f) {
                $f = (array) $f;
                $html .= '<a href="#" class="list-group-item px-0 d-flex align-items-center text-decoration-none"><i class="ti ti-file-text text-primary me-2"></i><div class="flex-grow-1"><div class="small fw-semibold">' . $this->e($f['name'] ?? '') . '</div></div><small class="text-muted">' . $this->e($f['size'] ?? '') . '</small></a>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        return $html . '</div>';
    }

    private function kv(string $k, $v): string
    {
        if ($v === '' || $v === null) {
            return '';
        }

        return '<div class="d-flex justify-content-between mb-2"><span class="text-muted">' . $this->e($k) . '</span><span class="fw-semibold">' . $this->e($v) . '</span></div>';
    }
}

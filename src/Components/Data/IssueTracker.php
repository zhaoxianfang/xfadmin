<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 问题跟踪列表（INSPINIA issue-tracker.html）
 *
 * XfAdmin::issueTracker([
 *     'title'      => '问题列表',
 *     'searchable' => true,               // 是否显示前端过滤搜索框
 *     'add_text'   => '新建问题',          // 空字符串则不显示按钮
 *     'issues'     => [
 *         [
 *             'id'       => 'ISSUE-104',
 *             'title'    => '移动端用户资料无法保存',
 *             'status'   => '进行中',                      // 状态文字
 *             'variant'  => 'warning',                     // 状态色 primary/success/...
 *             'assignee' => ['avatar' => 'users/user-3.jpg', 'name' => '李雷'],
 *             'created'  => '2026-02-10',
 *             'due'      => '2026-02-18',
 *             'labels'   => ['Bug', 'Mobile'],
 *             'progress' => 60,                            // 0-100
 *             'comments' => 8,
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class IssueTracker extends Component
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
            'searchable' => true,
            'add_text'   => '',
            'issues'     => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $issues = (array) $this->get('issues', []);
        $title = (string) $this->get('title', '');
        $addText = (string) $this->get('add_text', '');
        $searchable = (bool) $this->get('searchable', true);

        $html = '<div' . $this->attrs(['class' => 'card xf-issue-tracker', 'data-xf' => 'issues']) . '>';

        if ($title !== '' || $addText !== '' || $searchable) {
            $html .= '<div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-light">';
            if ($title !== '') {
                $html .= '<h5 class="card-title mb-0">' . $this->e($title) . '</h5>';
            }
            if ($searchable) {
                $html .= '<div class="flex-grow-1" style="max-width:280px;">'
                    . '<input type="search" class="form-control form-control-sm xf-issue-search" placeholder="搜索问题...">'
                    . '</div>';
            }
            if ($addText !== '') {
                $html .= '<button type="button" class="btn btn-success btn-sm">' . $this->e($addText) . '</button>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="card-body"><div class="table-responsive">'
            . '<table class="table table-custom table-centered table-hover w-100 mb-0"><tbody>';

        foreach ($issues as $it) {
            $variant = $this->e($it['variant'] ?? 'secondary');
            $html .= '<tr class="xf-issue-row">';

            // 状态
            $html .= '<td><span class="badge text-bg-' . $variant . ' fs-xxs badge-label">' . $this->e($it['status'] ?? '') . '</span></td>';

            // 编号 + 标题
            $html .= '<td><a href="' . $this->e($it['url'] ?? '#') . '" class="link-reset text-uppercase fw-semibold">' . $this->e($it['id'] ?? '') . '</a>'
                . '<p class="mb-0 text-muted xf-issue-title">' . $this->e($it['title'] ?? '') . '</p></td>';

            // 负责人
            $html .= '<td>';
            $assignee = $it['assignee'] ?? null;
            if (is_array($assignee)) {
                $html .= '<div class="d-flex align-items-center gap-2">';
                if (! empty($assignee['avatar'])) {
                    $html .= '<div class="avatar avatar-xs"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $assignee['avatar'])) . '" class="img-fluid rounded-circle" alt=""></div>';
                } else {
                    $ini = mb_substr((string) ($assignee['name'] ?? '?'), 0, 1);
                    $html .= '<div class="avatar avatar-xs"><span class="avatar-title bg-primary-subtle text-primary rounded-circle">' . $this->e($ini) . '</span></div>';
                }
                $html .= '<h5 class="text-nowrap mb-0 lh-base fs-sm">' . $this->e($assignee['name'] ?? '') . '</h5></div>';
            }
            $html .= '</td>';

            // 日期
            $html .= '<td class="text-nowrap">';
            if (! empty($it['created'])) {
                $html .= '<p class="mb-0 small"><i class="ti ti-calendar"></i> <span class="fw-semibold">创建:</span> ' . $this->e($it['created']) . '</p>';
            }
            if (! empty($it['due'])) {
                $html .= '<p class="mb-0 small"><i class="ti ti-clock"></i> <span class="fw-semibold">截止:</span> ' . $this->e($it['due']) . '</p>';
            }
            $html .= '</td>';

            // 标签
            $html .= '<td>';
            foreach ((array) ($it['labels'] ?? []) as $label) {
                $html .= '<a href="#" class="badge badge-label badge-default me-1">' . $this->e($label) . '</a>';
            }
            $html .= '</td>';

            // 进度
            $progress = max(0, min(100, (int) ($it['progress'] ?? 0)));
            $html .= '<td style="min-width:120px;">'
                . '<div class="progress progress-sm mb-1"><div class="progress-bar bg-' . $variant . '" role="progressbar" style="width:' . $progress . '%" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100"></div></div>'
                . '<span class="text-muted small">' . $progress . '%</span></td>';

            // 评论数
            $html .= '<td class="text-center text-nowrap"><i class="ti ti-message-circle text-muted"></i> ' . (int) ($it['comments'] ?? 0) . '</td>';

            // 操作
            $html .= '<td><div class="d-flex gap-1 justify-content-center">'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle" aria-label="编辑"><i class="ti ti-edit fs-lg"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle" aria-label="删除"><i class="ti ti-trash fs-lg"></i></a>'
                . '</div></td>';

            $html .= '</tr>';
        }
        return $html . '</tbody></table></div></div></div>';
    }
}

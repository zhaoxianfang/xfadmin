<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * CRM 客户列表（INSPINIA clients.html）
 *
 * XfAdmin::clients([
 *     'title'       => '客户列表',
 *     'searchable'  => true,
 *     'type_filter' => ['全部', 'VIP', '潜在', '常规'],
 *     'clients'     => [
 *         [
 *             'name'     => 'Emily Parker',
 *             'email'    => 'emily@startupwave.io',
 *             'avatar'   => 'users/user-7.jpg',
 *             'phone'    => '+1 202-555-0147',
 *             'country'  => '美国',
 *             'enrolled' => '2026-03-12',
 *             'type'     => 'VIP',
 *             'job_title'=> '采购总监',
 *             'status'   => ['text' => '活跃', 'variant' => 'success'],
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class Clients extends Component
{
    protected function defaults(): array
    {
        return [
            'title'       => '客户列表',
            'searchable'  => true,
            'type_filter' => [],
            'add_text'    => '新增客户',
            'clients'     => [],
        ];
    }

    protected function html(): string
    {
        $clients = (array) $this->get('clients', []);
        if (empty($clients)) {
            return '';
        }

        $html = '<div' . $this->attrs(['class' => 'card xf-clients']) . '>';

        // 头部工具栏
        $html .= '<div class="card-header border-light justify-content-between">';
        $html .= '<h5 class="card-title mb-0">' . $this->e($this->get('title')) . '</h5>';
        $html .= '<div class="d-flex gap-2">';
        if ($this->get('searchable')) {
            $html .= '<div class="search-box"><input type="text" class="form-control form-control-sm" placeholder="搜索客户…"></div>';
        }
        $filters = (array) $this->get('type_filter', []);
        if (! empty($filters)) {
            $html .= '<select class="form-select form-select-sm my-1 my-md-0" style="width:auto;">';
            foreach ($filters as $f) {
                $html .= '<option>' . $this->e($f) . '</option>';
            }
            $html .= '</select>';
        }
        $html .= '<button class="btn btn-sm btn-primary"><i class="ti ti-user-plus me-1"></i>' . $this->e($this->get('add_text')) . '</button>';
        $html .= '</div></div>';

        // 表格
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-custom table-centered table-select table-hover w-100 mb-0">';
        $html .= '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">'
            . '<th style="width:1%;"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></th>'
            . '<th>Clients Name</th><th>Phone</th><th>Country</th><th>Enrolled</th>'
            . '<th>Type</th><th>Job Title</th><th>Status</th><th class="text-center">Actions</th>'
            . '</tr></thead><tbody>';

        foreach ($clients as $c) {
            $html .= '<tr>';
            $html .= '<td><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" value="option"></td>';
            $html .= '<td><div class="d-flex justify-content-start align-items-center gap-2">';
            $html .= '<div class="avatar avatar-sm">';
            if (! empty($c['avatar'])) {
                $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $c['avatar'])) . '" alt="" class="img-fluid rounded-circle">';
            } else {
                $html .= '<span class="avatar-title rounded-circle bg-primary-subtle text-primary">' . $this->e(mb_substr((string) ($c['name'] ?? '?'), 0, 1)) . '</span>';
            }
            $html .= '</div><div><h6 class="text-nowrap mb-0 lh-base fs-base"><a href="' . $this->e($c['url'] ?? '#') . '" class="link-reset">' . $this->e($c['name'] ?? '') . '</a></h6>'
                . '<p class="text-muted fs-xs mb-0">' . $this->e($c['email'] ?? '') . '</p></div></div></td>';
            $html .= '<td class="text-nowrap">' . $this->e($c['phone'] ?? '') . '</td>';
            $html .= '<td>' . $this->e($c['country'] ?? '') . '</td>';
            $html .= '<td>' . $this->e($c['enrolled'] ?? '') . '</td>';
            $html .= '<td><span class="badge text-bg-light fs-sm">' . $this->e($c['type'] ?? '') . '</span></td>';
            $html .= '<td>' . $this->e($c['job_title'] ?? '') . '</td>';
            $html .= '<td>' . $this->badge($c['status'] ?? null) . '</td>';
            $html .= '<td class="text-center"><div class="d-flex justify-content-center align-items-center gap-1">'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-pencil"></i></a></div></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div></div>';

        // 页脚
        $html .= '<div class="card-footer border-0"><div class="d-flex justify-content-between align-items-center">'
            . '<span class="text-muted">' . count($clients) . ' 位客户</span>'
            . '<nav><ul class="pagination pagination-sm mb-0 justify-content-center"><li class="page-item disabled"><a class="page-link" href="#">上一页</a></li><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item"><a class="page-link" href="#">2</a></li><li class="page-item"><a class="page-link" href="#">下一页</a></li></ul></nav>'
            . '</div></div>';

        return $html . '</div>';
    }

    protected function badge(mixed $status): string
    {
        if (empty($status)) {
            return '';
        }
        $text    = $status['text'] ?? (is_string($status) ? $status : '');
        $variant = $status['variant'] ?? 'secondary';
        return '<span class="badge bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' badge-label">' . $this->e($text) . '</span>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 客户 / 会员管理（ecommerce-customers.html / crm-clients.html）
 *
 * XfAdmin::customers([
 *     'title' => '客户管理',
 *     'view'  => 'grid',            // grid | list
 *     'items' => [
 *         [
 *             'name' => '张三', 'email' => 'z@x.com', 'phone' => '138…',
 *             'avatar' => 'users/user-1.jpg', 'company' => 'XX 科技',
 *             'location' => '北京', 'status' => 'active', // active|vip|sleep
 *             'tags' => ['企业','复购'], 'orders' => 12, 'spent' => '¥1,200',
 *         ],
 *     ],
 * ])
 */
class Customers extends Component
{
    protected function defaults(): array
    {
        return [
            'title'      => '',
            'view'       => 'grid',
            'searchable' => true,
            'items'      => [],
        ];
    }

    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        if (empty($items)) {
            return '';
        }

        $toolbar = '';
        if ($this->get('searchable')) {
            $toolbar = '<div class="row g-2 align-items-center mb-3">'
                . '<div class="col-md-6"><div class="position-relative">'
                . '<i class="ti ti-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>'
                . '<input type="text" class="form-control ps-5" placeholder="搜索姓名 / 邮箱 / 公司…"></div></div>'
                . '<div class="col-md-6 text-md-end"><button class="btn btn-primary"><i class="ti ti-plus me-1"></i>新增客户</button></div>'
                . '</div>';
        }

        $title = $this->get('title') ? '<h5 class="mb-3">' . $this->e($this->get('title')) . '</h5>' : '';
        $body  = $this->get('view') === 'list' ? $this->renderList($items) : $this->renderGrid($items);

        return $toolbar . $title . $body;
    }

    private function renderGrid(array $items): string
    {
        $html = '<div class="row g-3">';
        foreach ($items as $it) {
            $it       = (array) $it;
            $status   = $it['status'] ?? 'active';
            $statuses = [
                'active' => ['success', '活跃'],
                'vip'    => ['warning', 'VIP'],
                'sleep'  => ['secondary', '休眠'],
            ];
            [$sCls, $sTxt] = $statuses[$status] ?? $statuses['active'];
            $avatar = ! empty($it['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $it['avatar']) : '';

            $tags = '';
            foreach ((array) ($it['tags'] ?? []) as $t) {
                $tags .= '<span class="badge bg-light text-dark me-1">' . $this->e($t) . '</span>';
            }

            $html .= '<div class="col-lg-4 col-md-6">';
            $html .= '<div class="card h-100">';
            $html .= '<div class="card-body">';
            $html .= '<div class="d-flex align-items-center mb-3">';
            $html .= '<div class="avatar avatar-md me-3"><img src="' . $this->e($avatar) . '" class="rounded-circle" alt=""></div>';
            $html .= '<div class="flex-grow-1 min-w-0">';
            $html .= '<h6 class="mb-0 text-truncate">' . $this->e($it['name'] ?? '') . '</h6>';
            $html .= '<small class="text-muted">' . $this->e($it['company'] ?? '') . '</small>';
            $html .= '</div>';
            $html .= '<span class="badge bg-' . $sCls . '-subtle text-' . $sCls . '">' . $sTxt . '</span>';
            $html .= '</div>';

            $html .= '<ul class="list-unstyled mb-3 small text-muted">';
            $html .= '<li class="mb-1"><i class="ti ti-mail me-2"></i>' . $this->e($it['email'] ?? '') . '</li>';
            if (! empty($it['phone'])) {
                $html .= '<li class="mb-1"><i class="ti ti-phone me-2"></i>' . $this->e($it['phone']) . '</li>';
            }
            if (! empty($it['location'])) {
                $html .= '<li><i class="ti ti-map-pin me-2"></i>' . $this->e($it['location']) . '</li>';
            }
            $html .= '</ul>';

            if ($tags) {
                $html .= '<div class="mb-3">' . $tags . '</div>';
            }

            $html .= '<div class="d-flex justify-content-between align-items-center border-top pt-3">';
            $html .= '<div class="small"><span class="fw-semibold">' . $this->e($it['orders'] ?? 0) . '</span> 订单 · <span class="fw-semibold">' . $this->e($it['spent'] ?? '¥0') . '</span></div>';
            $html .= $this->rowActions();
            $html .= '</div></div></div></div>';
        }

        return $html . '</div>';
    }

    private function renderList(array $items): string
    {
        $html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr>'
            . '<th>客户</th><th>公司</th><th>所在地</th><th>状态</th><th>订单</th><th>消费</th><th></th>'
            . '</tr></thead><tbody>';
        foreach ($items as $it) {
            $it     = (array) $it;
            $status = $it['status'] ?? 'active';
            $sCls   = $status === 'vip' ? 'warning' : ($status === 'sleep' ? 'secondary' : 'success');
            $sTxt   = $status === 'vip' ? 'VIP' : ($status === 'sleep' ? '休眠' : '活跃');
            $avatar = ! empty($it['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $it['avatar']) : '';

            $html .= '<tr>';
            $html .= '<td><div class="d-flex align-items-center"><div class="avatar avatar-sm me-2"><img src="' . $this->e($avatar) . '" class="rounded-circle" alt=""></div><div><div class="fw-semibold">' . $this->e($it['name'] ?? '') . '</div><small class="text-muted">' . $this->e($it['email'] ?? '') . '</small></div></div></td>';
            $html .= '<td>' . $this->e($it['company'] ?? '-') . '</td>';
            $html .= '<td>' . $this->e($it['location'] ?? '-') . '</td>';
            $html .= '<td><span class="badge bg-' . $sCls . '-subtle text-' . $sCls . '">' . $sTxt . '</span></td>';
            $html .= '<td>' . $this->e($it['orders'] ?? 0) . '</td>';
            $html .= '<td class="fw-semibold">' . $this->e($it['spent'] ?? '¥0') . '</td>';
            $html .= '<td class="text-end">' . $this->rowActions() . '</td>';
            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    }

    private function rowActions(): string
    {
        return '<div class="dropdown"><button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>'
            . '<ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#"><i class="ti ti-eye me-2"></i>查看</a></li>'
            . '<li><a class="dropdown-item" href="#"><i class="ti ti-edit me-2"></i>编辑</a></li>'
            . '<li><a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i>删除</a></li></ul></div>';
    }
}

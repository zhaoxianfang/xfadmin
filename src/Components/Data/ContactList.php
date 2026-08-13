<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 联系人列表（apps-users-contacts.html）
 *
 * 头像 + 名称 + 角色/职位 + 状态点 + 操作按钮。
 *
 * XfAdmin::contactList([
 *     'contacts' => [
 *         ['avatar' => 'users/user-1.jpg', 'name' => '王伟', 'role' => '产品经理', 'online' => true],
 *         ['avatar' => 'users/user-2.jpg', 'name' => '赵敏', 'role' => '设计师', 'online' => false],
 *     ],
 *     'title' => '团队成员',
 * ])
 */
class ContactList extends Component
{
    protected function defaults(): array
    {
        return [
            'contacts' => [],
            'title'    => '团队成员',
        ];
    }

    protected function html(): string
    {
        $contacts = (array) $this->get('contacts', []);
        $title    = (string) $this->get('title', '团队成员');

        $html = '<div class="card"><div class="card-header"><h5 class="mb-0">' . $this->e($title) . '</h5></div><div class="card-body p-0"><ul class="list-group list-group-flush">';

        if (empty($contacts)) {
            $html .= '<li class="list-group-item text-center text-muted py-4">暂无联系人</li></ul></div></div>';

            return $html;
        }

        foreach ($contacts as $c) {
            $c       = (array) $c;
            $avatar  = $this->img((string) ($c['avatar'] ?? ''));
            $name    = (string) ($c['name'] ?? '');
            $role    = (string) ($c['role'] ?? '');
            $online  = (bool) ($c['online'] ?? false);

            $html .= '<li class="list-group-item d-flex align-items-center gap-3">';
            $html .= '<div class="position-relative">';
            $html .= '<img src="' . $this->e($avatar) . '" class="rounded-circle avatar-sm" alt="' . $this->e($name) . '">';
            $html .= '<span class="position-absolute bottom-0 end-0 translate-middle p-1 rounded-circle ' . ($online ? 'bg-success' : 'bg-secondary') . ' border border-2 border-white"></span>';
            $html .= '</div>';
            $html .= '<div class="flex-fill"><div class="fw-semibold">' . $this->e($name) . '</div><small class="text-muted">' . $this->e($role) . '</small></div>';
            $html .= '<button class="btn btn-sm btn-outline-primary" type="button"><i class="ti ti-message-circle"></i></button>';
            $html .= '</li>';
        }

        $html .= '</ul></div></div>';

        return $html;
    }
}

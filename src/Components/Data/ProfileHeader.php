<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 个人资料头部（pages-profile.html / ecommerce-seller-details.html）
 *
 * XfAdmin::profileHeader([
 *     'cover'   => 'small/img-10.jpg',
 *     'avatar'  => 'users/avatar-1.jpg',
 *     'name'    => '张三',
 *     'role'    => '前端工程师',
 *     'location'=> '深圳',
 *     'stats'   => [
 *         ['label' => '关注', 'value' => '2.5k'],
 *         ['label' => '粉丝', 'value' => '13k'],
 *     ],
 *     'actions' => '按钮HTML',
 *     'tabs'    => [['label' => '概览', 'active' => true, 'href' => '#o'], ...],
 * ])
 */
class ProfileHeader extends Component
{
    protected function defaults(): array
    {
        return [
            'cover'    => null,
            'avatar'   => 'users/avatar-1.jpg',
            'name'     => '',
            'role'     => null,
            'location' => null,
            'stats'    => [],
            'actions'  => null,
            'tabs'     => [],
            'verified' => false,
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'card overflow-hidden']) . '>';

        // 封面
        if ($this->get('cover')) {
            $html .= '<div class="profile-cover" style="height:180px;background:url(' . $this->e(XfAdmin::asset('images/' . ltrim((string) $this->get('cover'), '/'))) . ') center/cover no-repeat;"></div>';
        } else {
            $html .= '<div class="profile-cover bg-primary" style="height:120px;"></div>';
        }

        $html .= '<div class="card-body">';
        $html .= '<div class="d-flex flex-wrap align-items-center gap-3" style="margin-top:-56px;">';
        $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $this->get('avatar'), '/'))) . '" class="rounded-circle border border-3 border-white bg-white" width="96" height="96" alt="avatar">';
        $html .= '<div class="flex-grow-1" style="margin-top:56px;">';
        $html .= '<h4 class="mb-1">' . $this->e($this->get('name'));
        if ($this->get('verified')) {
            $html .= ' <i class="ti ti-rosette-discount-check-filled text-primary"></i>';
        }
        $html .= '</h4>';
        $meta = array_filter([$this->get('role'), $this->get('location')]);
        if ($meta) {
            $html .= '<p class="text-muted mb-0">' . $this->e(implode(' · ', $meta)) . '</p>';
        }
        $html .= '</div>';

        if ($this->get('actions')) {
            $html .= '<div style="margin-top:56px;">' . $this->raw($this->get('actions')) . '</div>';
        }
        $html .= '</div>';

        // 统计
        $stats = (array) $this->get('stats', []);
        if ($stats) {
            $html .= '<div class="d-flex flex-wrap gap-4 mt-3">';
            foreach ($stats as $s) {
                $html .= '<div class="text-center"><h5 class="mb-0">' . $this->e($s['value'] ?? '') . '</h5><small class="text-muted">' . $this->e($s['label'] ?? '') . '</small></div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // Tabs
        $tabs = (array) $this->get('tabs', []);
        if ($tabs) {
            $html .= '<ul class="nav nav-tabs card-tabs px-3">';
            foreach ($tabs as $t) {
                $active = ! empty($t['active']) ? ' active' : '';
                $html .= '<li class="nav-item"><a class="nav-link' . $active . '" href="' . $this->e($t['href'] ?? '#') . '"';
                if (! empty($t['toggle'])) {
                    $html .= ' data-bs-toggle="tab"';
                }
                $html .= '>' . $this->e($t['label'] ?? '') . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html . '</div>';
    }
}

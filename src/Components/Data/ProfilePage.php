<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 个人主页（封面 + 头像 + 统计 + 操作 + 标签页）—— INSPINIA pages-profile.html 整页抽取
 *
 * XfAdmin::profilePage([
 *     'cover'    => 'stock/small-1.jpg',                       // 或 'gradient:linear-gradient(...)'
 *     'avatar'   => 'users/user-1.jpg',
 *     'name'     => '张伟',
 *     'verified' => true,
 *     'role'     => '高级产品经理',
 *     'meta'     => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
 *     'stats'    => [['value' => '128', 'label' => '项目']],
 *     'actions'  => [['text' => '关注', 'class' => 'btn-primary', 'icon' => 'ti ti-user-plus']],
 *     'tabs'     => [['title' => '动态', 'content' => ..., 'active' => true]],  // XfAdmin::tabs 的 items
 * ])
 */
class ProfilePage extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'cover'    => '',
            'avatar'   => '',
            'name'     => '',
            'verified' => false,
            'role'     => '',
            'meta'     => [],
            'stats'    => [],
            'actions'  => [],
            'tabs'     => [],
            'content'  => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $cover = (string) $this->get('cover', '');
        $coverCss = str_starts_with($cover, 'gradient:')
            ? 'background:' . $this->e(substr($cover, 9)) . ';'
            : ($cover !== ''
                ? 'background:url(' . $this->e($this->img($cover)) . ') center/cover no-repeat;'
                : 'background:linear-gradient(135deg,var(--bs-primary),#6f42c1);');

        $html = '<div' . $this->attrs(['class' => 'xf-profile-page']) . '>';
        $html .= '<div class="card border-0 shadow-sm overflow-hidden mb-3">';
        $html .= '<div class="profile-cover" style="height:200px;' . $coverCss . '"></div>';
        $html .= '<div class="card-body position-relative">';

        // 头像 + 基本信息
        $html .= '<div class="d-flex flex-wrap align-items-end gap-3" style="margin-top:-64px;">';
        $avatar = (string) $this->get('avatar');
        if ($avatar !== '') {
            $html .= '<span class="avatar avatar-xxl flex-shrink-0"><img src="' . $this->e($this->img($avatar))
                . '" class="rounded-circle border border-3 border-white bg-white img-fluid" alt="avatar"></span>';
        } else {
            $html .= '<span class="avatar avatar-xxl flex-shrink-0 bg-light d-flex align-items-center justify-content-center text-muted"><i class="ti ti-user fs-1"></i></span>';
        }
        $html .= '<div class="flex-grow-1 pt-4"><h4 class="mb-1">' . $this->e($this->get('name'));
        if ($this->get('verified')) {
            $html .= ' <i class="ti ti-rosette-discount-check-filled text-primary" title="已认证"></i>';
        }
        $html .= '</h4><div class="text-muted">' . $this->e($this->get('role')) . '</div>';
        $meta = (array) $this->get('meta', []);
        if ($meta) {
            $html .= '<div class="d-flex flex-wrap gap-3 mt-1 small text-muted">';
            foreach ($meta as $m) {
                $html .= '<span>' . (! empty($m['icon']) ? '<i class="' . $this->e($m['icon']) . ' me-1"></i>' : '')
                    . $this->e($m['text'] ?? '') . '</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // 操作按钮
        $actions = (array) $this->get('actions', []);
        if ($actions) {
            $html .= '<div class="d-flex gap-2 pt-4">';
            foreach ($actions as $a) {
                $html .= '<a href="' . $this->e($a['url'] ?? '#') . '" class="btn ' . $this->e($a['class'] ?? 'btn-soft-secondary') . '">'
                    . (! empty($a['icon']) ? '<i class="' . $this->e($a['icon']) . ' me-1"></i>' : '')
                    . $this->e($a['text'] ?? '') . '</a>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // 统计条
        $stats = (array) $this->get('stats', []);
        if ($stats) {
            $html .= '<div class="d-flex flex-wrap border-top mt-3 pt-3 text-center">';
            foreach ($stats as $s) {
                $html .= '<div class="flex-fill px-2"><h5 class="mb-0">' . $this->e($s['value'] ?? '') . '</h5>'
                    . '<small class="text-muted">' . $this->e($s['label'] ?? '') . '</small></div>';
            }
            $html .= '</div>';
        }
        $html .= '</div></div>';

        // 标签页 / 自定义内容
        $tabs = (array) $this->get('tabs', []);
        if ($tabs) {
            $html .= $this->raw(\zxf\XfAdmin\XfAdmin::component('tabs', ['items' => $tabs]));
        } elseif ($this->get('content') !== null) {
            $html .= $this->raw($this->get('content'));
        }
        return $html . '</div>';
    }
}

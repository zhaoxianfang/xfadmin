<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 团队成员卡片网格（apps-team.html / INSPINIA team 区块）
 *
 * XfAdmin::teamMember([
 *     'members' => [
 *         ['avatar' => 'users/avatar-1.jpg', 'name' => '张三', 'role' => '产品经理',
 *          'bio' => '负责产品规划与迭代',
 *          'social' => ['ti ti-twitter' => '#', 'ti ti-facebook' => '#', 'ti ti-linkedin' => '#']],
 *     ],
 *     'cols' => 4,            // 每行列数（1-6）
 * ])
 */
class TeamMember extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'members' => [],
            'cols'    => 4,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $members = (array) $this->get('members', []);
        if (empty($members)) {
            return '';
        }
        $n = max(1, min(6, (int) $this->get('cols', 4)));
        $colCls = $n === 1 ? 'col-12' : 'col-12 col-sm-6 col-lg-' . (int) (12 / $n);

        $html = '<div' . $this->attrs(['class' => 'row g-3 xf-team']) . '>';
        foreach ($members as $m) {
            $html .= '<div class="' . $colCls . '">';
            $html .= '<div class="card card-hover h-100 text-center">';
            $html .= '<div class="card-body p-4">';

            $avatar = $m['avatar'] ?? '';
            if ($avatar) {
                // 成员头像：INSPINIA 规范 .avatar 包裹结构（avatar-xxl = 5rem/80px），替代裸 img + 表现属性写法
                $html .= '<span class="avatar avatar-xxl mx-auto d-block mb-3"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $avatar)) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span>';
            } else {
                $ini = mb_substr((string) ($m['name'] ?? '?'), 0, 1);
                // 首字母占位：与图片头像同尺寸（avatar-xxl），避免有图/无图两种卡片高度不一致
                $html .= '<span class="avatar avatar-xxl mx-auto d-block mb-3"><span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">' . $this->e($ini) . '</span></span>';
            }
            $html .= '<h5 class="mb-1 fw-semibold">' . $this->e($m['name'] ?? '') . '</h5>';
            if (! empty($m['role'])) {
                $html .= '<p class="text-primary mb-2 small fw-medium">' . $this->e($m['role']) . '</p>';
            }
            if (! empty($m['bio'])) {
                $html .= '<p class="text-muted small mb-3">' . $this->e($m['bio']) . '</p>';
            }
            if (! empty($m['social']) && is_array($m['social'])) {
                $html .= '<div class="d-flex justify-content-center gap-2">';
                foreach ($m['social'] as $icon => $href) {
                    $html .= '<a href="' . $this->e($href) . '" class="btn btn-soft-secondary btn-sm rounded-circle px-2" aria-label="social"><i class="' . $this->e($icon) . '"></i></a>';
                }
                $html .= '</div>';
            }
            $html .= '</div></div></div>';
        }
        return $html . '</div>';
    }
}

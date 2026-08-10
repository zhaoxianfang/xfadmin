<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 联系人卡片网格（INSPINIA contacts.html）
 *
 * XfAdmin::contactCard([
 *     'cols'     => 3,
 *     'contacts' => [
 *         [
 *             'avatar'   => 'users/user-5.jpg',
 *             'name'     => '苏菲',
 *             'role'     => '首席 UI/UX 设计师',
 *             'tag'      => '管理员',                 // 身份徽标（可空）
 *             'rating'   => 4.8,                      // 头像角标评分（可空）
 *             'email'    => 'sophia@example.com',
 *             'phone'    => '138-0000-0000',
 *             'location' => '上海',
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class ContactCard extends Component
{
    protected function defaults(): array
    {
        return [
            'contacts' => [],
            'cols'     => 3,
        ];
    }

    protected function html(): string
    {
        $contacts = (array) $this->get('contacts', []);
        if (empty($contacts)) {
            return '';
        }
        $n = max(1, min(4, (int) $this->get('cols', 3)));
        $colCls = $n === 1 ? 'col-12' : 'col-12 col-md-6 col-xl-' . (int) (12 / $n);

        $html = '<div' . $this->attrs(['class' => 'row g-3 xf-contacts']) . '>';
        foreach ($contacts as $c) {
            $html .= '<div class="' . $colCls . '"><div class="card card-h-100"><div class="card-body">';

            // 头部：头像 + 姓名/角色/徽标
            $html .= '<div class="d-flex align-items-center mb-3">';
            $html .= '<div class="me-3 position-relative">';
            if (! empty($c['avatar'])) {
                // 联系人头像：包裹进 .avatar 结构（固定尺寸 + 圆形裁切），替代原先依赖 width/height 表现属性的写法
                $html .= '<span class="avatar avatar-lg"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $c['avatar'])) . '" alt="" class="img-fluid rounded-circle" style="object-fit:cover;"></span>';
            } else {
                $ini = mb_substr((string) ($c['name'] ?? '?'), 0, 1);
                // 首字母占位：尺寸类挂在 .avatar 包裹元素上
                $html .= '<span class="avatar avatar-lg d-inline-block"><span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">' . $this->e($ini) . '</span></span>';
            }
            if (isset($c['rating'])) {
                $html .= '<span class="position-absolute bottom-0 end-0 badge bg-warning rounded-circle p-1 shadow-sm" title="评分 ' . $this->e((string) $c['rating']) . '"><i class="ti ti-star text-white"></i></span>';
            }
            $html .= '</div>';

            $html .= '<div><h5 class="mb-1"><a href="' . $this->e($c['url'] ?? '#') . '" class="link-reset">' . $this->e($c['name'] ?? '') . '</a></h5>';
            if (! empty($c['role'])) {
                $html .= '<p class="text-muted mb-1 small">' . $this->e($c['role']) . '</p>';
            }
            if (! empty($c['tag'])) {
                $html .= '<span class="badge text-bg-light badge-label">' . $this->e($c['tag']) . '</span>';
            }
            $html .= '</div></div>';

            // 联系信息
            $html .= '<ul class="list-unstyled text-muted mb-3 small">';
            foreach ([['email', 'ti ti-mail'], ['phone', 'ti ti-phone'], ['location', 'ti ti-map-pin']] as [$key, $icon]) {
                if (! empty($c[$key])) {
                    $html .= '<li class="mb-2 d-flex align-items-center gap-2"><i class="' . $icon . ' text-muted"></i><span>' . $this->e($c[$key]) . '</span></li>';
                }
            }
            $html .= '</ul>';

            // 操作
            $html .= '<div class="d-flex gap-2">'
                . '<a href="#" class="btn btn-sm btn-soft-primary flex-fill"><i class="ti ti-message-circle me-1"></i>发消息</a>'
                . '<a href="' . $this->e($c['url'] ?? '#') . '" class="btn btn-sm btn-soft-secondary flex-fill"><i class="ti ti-user me-1"></i>查看资料</a>'
                . '</div>';

            $html .= '</div></div></div>';
        }

        return $html . '</div>';
    }
}

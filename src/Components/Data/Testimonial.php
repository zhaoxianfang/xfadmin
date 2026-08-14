<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 用户证言 / 评价卡片（landing / testimonials 区块）
 *
 * XfAdmin::testimonial([
 *     'items' => [
 *         ['avatar' => 'users/avatar-2.jpg', 'name' => '李四', 'role' => 'CTO',
 *          'text' => '这套后台极大提升了我们的运营效率。', 'rating' => 5],
 *     ],
 *     'cols'     => 3,           // 网格列数（非 carousel 模式）
 *     'carousel' => false,       // true 时使用 Bootstrap 走马灯
 * ])
 */
class Testimonial extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'cols'     => 3,
            'carousel' => false,
        ];
    }

    /**
     * card（protected实例方法）
     *
     * @param array $t t
     *
     * @return string result
     */
    protected function card(array $t): string
    {
        $html = '<div class="card h-100 border-0 shadow-none bg-transparent">';
        $html .= '<div class="card-body p-3">';
        $rating = (int) ($t['rating'] ?? 0);
        if ($rating > 0) {
            $html .= '<div class="mb-2 text-warning">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= '<i class="ti ti-star' . ($i <= $rating ? '-filled' : '') . '"></i>';
            }
            $html .= '</div>';
        }
        $html .= '<p class="mb-3 fst-italic">“' . $this->e($t['text'] ?? '') . '”</p>';
        $html .= '<div class="d-flex align-items-center gap-2">';
        $avatar = $t['avatar'] ?? '';
        if ($avatar) {
            $html .= '<span class="avatar avatar-sm flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $avatar)) . '" class="img-fluid rounded-circle" alt=""></span>';
        } else {
            $ini = mb_substr((string) ($t['name'] ?? '?'), 0, 1);
            $html .= '<span class="avatar-sm"><span class="avatar-title bg-primary-subtle text-primary rounded-circle">' . $this->e($ini) . '</span></span>';
        }
        $html .= '<div><h6 class="mb-0 fw-semibold">' . $this->e($t['name'] ?? '') . '</h6>';
        if (! empty($t['role'])) {
            $html .= '<small class="text-muted">' . $this->e($t['role']) . '</small>';
        }
        $html .= '</div></div></div></div>';

        return $html;
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        if (empty($items)) {
            return '';
        }
        if ($this->get('carousel')) {
            $id = $this->uid('xf-tes');
            $html = '<div id="' . $id . '" class="carousel slide" data-bs-ride="carousel"' . $this->attrs([]) . '>';
            $html .= '<div class="carousel-inner">';
            foreach ($items as $i => $t) {
                $html .= '<div class="carousel-item' . ($i === 0 ? ' active' : '') . '"><div class="mx-auto" style="max-width:640px">' . $this->card($t) . '</div></div>';
            }
            $html .= '</div>';
            $html .= '<button class="carousel-control-prev" type="button" data-bs-target="#' . $id . '" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>';
            $html .= '<button class="carousel-control-next" type="button" data-bs-target="#' . $id . '" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>';
            $html .= '</div>';

            return $html;
        }
        $n = max(1, min(4, (int) $this->get('cols', 3)));
        $colCls = $n === 1 ? 'col-12' : 'col-12 col-md-' . (int) (12 / $n);
        $html = '<div' . $this->attrs(['class' => 'row g-3 xf-testimonial']) . '>';
        foreach ($items as $t) {
            $html .= '<div class="' . $colCls . '">' . $this->card($t) . '</div>';
        }
        return $html . '</div>';
    }
}

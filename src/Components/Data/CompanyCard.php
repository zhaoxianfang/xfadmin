<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 公司卡片列表（INSPINIA companies.html）
 *
 * XfAdmin::companyCard([
 *     'cols'      => 2,
 *     'companies' => [
 *         [
 *             'logo'      => 'logos/amazon.svg',       // 相对 images/ 路径
 *             'name'      => '云杉科技',
 *             'website'   => 'www.example.com',
 *             'tags'      => [
 *                 ['text' => '上海', 'icon' => 'ti ti-map-pin', 'variant' => 'primary'],
 *                 ['text' => '电商', 'icon' => 'ti ti-shopping-cart', 'variant' => 'success'],
 *             ],
 *             'desc'      => '专注于企业级 SaaS 与云基础设施。',
 *             'stats'     => ['员工' => '1200+', '年营收' => '¥5.1亿'],
 *             'rating'    => 4,                        // 0-5 星
 *             'follow'    => true,                     // 是否显示关注按钮
 *         ],
 *     ],
 * ])
 */
class CompanyCard extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'companies' => [],
            'cols'      => 2,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $companies = (array) $this->get('companies', []);
        if (empty($companies)) {
            return '';
        }
        $n = max(1, min(3, (int) $this->get('cols', 2)));
        $colCls = $n === 1 ? 'col-12' : 'col-12 col-xl-' . (int) (12 / $n);

        $html = '<div' . $this->attrs(['class' => 'row g-3 xf-companies']) . '>';
        foreach ($companies as $c) {
            $html .= '<div class="' . $colCls . '"><div class="card d-flex flex-row p-3 h-100">';

            if (! empty($c['logo'])) {
                // 公司 Logo：包裹进 .avatar 结构，保持与全局头像一致的固定尺寸；Logo 不裁切故用 object-fit:contain
                $html .= '<span class="avatar avatar-lg me-3 flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $c['logo'])) . '" alt="" class="img-fluid rounded" style="object-fit:contain;"></span>';
            } else {
                $ini = mb_substr((string) ($c['name'] ?? '?'), 0, 1);
                // 首字母占位：尺寸类挂在 .avatar 包裹元素上（对齐 INSPINIA 规范）
                $html .= '<span class="avatar avatar-lg me-3 flex-shrink-0"><span class="avatar-title bg-primary-subtle text-primary rounded fs-3">' . $this->e($ini) . '</span></span>';
            }
            $html .= '<div class="flex-grow-1">';

            // 名称 + 官网 + 关注
            $html .= '<div class="d-flex justify-content-between align-items-start"><div>';
            $html .= '<h4 class="mb-1 fw-bold fs-lg">' . $this->e($c['name'] ?? '') . '</h4>';
            if (! empty($c['website'])) {
                $html .= '<a href="#" class="text-muted text-decoration-none small">' . $this->e($c['website']) . '</a>';
            }
            $html .= '</div>';
            if (! empty($c['follow'])) {
                $html .= '<a href="#" class="btn btn-sm btn-outline-danger rounded-pill"><i class="ti ti-heart me-1"></i>关注</a>';
            }
            $html .= '</div>';

            // 标签
            $tags = (array) ($c['tags'] ?? []);
            if ($tags) {
                $html .= '<div class="mt-2 mb-2 d-flex flex-wrap gap-2">';
                foreach ($tags as $tag) {
                    $variant = $this->e($tag['variant'] ?? 'primary');
                    $icon = ! empty($tag['icon']) ? '<i class="' . $this->e($tag['icon']) . ' me-1"></i>' : '';
                    $html .= '<span class="badge bg-light text-' . $variant . ' p-1 fs-xxs">' . $icon . $this->e($tag['text'] ?? '') . '</span>';
                }
                $html .= '</div>';
            }
            if (! empty($c['desc'])) {
                $html .= '<p class="text-muted mb-3 small">' . $this->e($c['desc']) . '</p>';
            }
            // 统计 + 评分
            $stats = (array) ($c['stats'] ?? []);
            $rating = $c['rating'] ?? null;
            if ($stats || $rating !== null) {
                $html .= '<div class="d-flex justify-content-between flex-wrap gap-3 mt-auto">';
                foreach ($stats as $label => $value) {
                    $html .= '<div><h6 class="text-muted mb-1 small">' . $this->e((string) $label) . '</h6><span class="fw-semibold fs-lg">' . $this->e((string) $value) . '</span></div>';
                }
                if ($rating !== null) {
                    $r = max(0, min(5, (int) $rating));
                    $html .= '<div class="text-warning align-self-center fs-lg">';
                    for ($i = 1; $i <= 5; $i++) {
                        $html .= '<i class="ti ' . ($i <= $r ? 'ti-star-filled' : 'ti-star') . '"></i>';
                    }
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div></div></div>';
        }
        return $html . '</div>';
    }
}

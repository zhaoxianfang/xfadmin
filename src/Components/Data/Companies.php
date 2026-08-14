<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 公司列表卡片网格（对齐 INSPINIA companies.html）
 *
 * 每张卡片包含：Logo、公司名、官网、关注按钮、位置/行业徽标、简介、
 * 员工数 / 营收 / 星级评分。
 *
 * XfAdmin::companies([
 *     'cols'      => 3,                     // 每行列数（1/2/3/4）
 *     'companies' => [
 *         [
 *             'name'      => '亚马逊',
 *             'logo'      => 'sellers/1.png',   // 相对包内 images/ 路径或完整 URL
 *             'website'   => 'www.amazon.com',
 *             'url'       => '#',
 *             'badges'    => [
 *                 ['icon' => 'ti ti-map-pin', 'text' => '西雅图', 'color' => 'primary'],
 *                 ['icon' => 'ti ti-shopping-cart', 'text' => '电商', 'color' => 'success'],
 *             ],
 *             'desc'      => '全球领先的电子商务与云计算公司。',
 *             'employees' => '150 万+',
 *             'revenue'   => '$514B',
 *             'rating'    => 4,                 // 0~5 星
 *             'followed'  => false,
 *         ],
 *     ],
 * ])
 */
class Companies extends Component
{
    /** 默认配置 */
    protected function defaults(): array
    {
        return [
            'cols'      => 3,
            'companies' => [],
        ];
    }

    /** 渲染公司卡片网格 */
    protected function html(): string
    {
        $items = (array) $this->get('companies', []);
        if (empty($items)) {
            return '';
        }
        $cols   = max(1, min(4, (int) $this->get('cols', 3)));
        $colCls = 'col-xl-' . intdiv(12, $cols) . ' col-md-6';

        $html = '<div' . $this->attrs(['class' => 'row xf-companies']) . '>';
        foreach ($items as $c) {
            $html .= '<div class="' . $colCls . '">' . $this->companyCard((array) $c) . '</div>';
        }
        return $html . '</div>';
    }

    /** 单张公司卡片 */
    protected function companyCard(array $c): string
    {
        $html = '<div class="card d-flex flex-row p-3">';

        // 左侧 Logo
        if (! empty($c['logo'])) {
            $html .= '<div class="avatar avatar-xl flex-shrink-0 me-3">'
                . '<img src="' . $this->e($this->img($c['logo'])) . '" alt="" class="img-fluid rounded">'
                . '</div>';
        }
        $html .= '<div class="flex-grow-1">';

        // 名称 / 官网 / 关注按钮
        $followed = ! empty($c['followed']);
        $html .= '<div class="d-flex justify-content-between align-items-start">';
        $html .= '<div><h4 class="mb-1 fw-bold">'
            . '<a href="' . $this->e($c['url'] ?? '#') . '" class="link-reset">' . $this->e($c['name'] ?? '') . '</a></h4>';
        if (! empty($c['website'])) {
            $html .= '<a href="#" class="text-muted text-decoration-none">' . $this->e($c['website']) . '</a>';
        }
        $html .= '</div>';
        $html .= '<a href="#" class="btn btn-sm rounded-pill ' . ($followed ? 'btn-danger' : 'btn-outline-danger') . '">'
            . '<i class="ti ti-heart' . ($followed ? '-filled' : '') . ' me-1"></i>' . ($followed ? '已关注' : '关注') . '</a>';
        $html .= '</div>';

        // 位置 / 行业徽标
        $badges = (array) ($c['badges'] ?? []);
        if (! empty($badges)) {
            $html .= '<div class="mt-2 mb-3 d-flex flex-wrap gap-2">';
            foreach ($badges as $b) {
                $color = $this->e($b['color'] ?? 'primary');
                $html .= '<span class="badge bg-light text-' . $color . ' p-1 fs-xxs">'
                    . (! empty($b['icon']) ? '<i class="' . $this->e($b['icon']) . ' me-1"></i>' : '')
                    . $this->e($b['text'] ?? '') . '</span>';
            }
            $html .= '</div>';
        }
        // 简介
        if (! empty($c['desc'])) {
            $html .= '<p class="text-muted mb-3">' . $this->e($c['desc']) . '</p>';
        }
        // 员工 / 营收 / 评分
        $html .= '<div class="d-flex justify-content-between flex-wrap mt-2 gap-3">';
        if (isset($c['employees'])) {
            $html .= '<div><h6 class="text-muted">员工数</h6><span class="fw-semibold fs-lg">' . $this->e($c['employees']) . '</span></div>';
        }
        if (isset($c['revenue'])) {
            $html .= '<div><h6 class="text-muted">年营收</h6><span class="fw-semibold fs-lg">' . $this->e($c['revenue']) . '</span></div>';
        }
        if (isset($c['rating'])) {
            $rate = max(0, min(5, (int) $c['rating']));
            $html .= '<div class="text-warning align-self-center fs-lg">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= '<i class="ti ti-star' . ($i <= $rate ? '-filled' : '') . '"></i>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html . '</div></div>';
    }
}

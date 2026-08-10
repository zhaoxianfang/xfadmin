<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 定价卡片（pages-pricing.html）
 *
 * XfAdmin::pricingCard([
 *     'name'     => '专业版',
 *     'price'    => '¥199',
 *     'period'   => '/ 月',
 *     'desc'     => '适合成长中的团队',
 *     'features' => [
 *         ['text' => '10 个项目', 'enabled' => true],
 *         ['text' => 'API 访问', 'enabled' => false],
 *     ],
 *     'featured' => true,          // 高亮推荐
 *     'badge'    => '最受欢迎',
 *     'button'   => ['label' => '立即选购', 'href' => '#', 'variant' => 'primary'],
 *     'icon'     => 'ti ti-rocket',
 * ])
 */
class PricingCard extends Component
{
    protected function defaults(): array
    {
        return [
            'name'     => '',
            'price'    => '',
            'period'   => '/ 月',
            'desc'     => null,
            'features' => [],
            'featured' => false,
            'badge'    => null,
            'icon'     => null,
            'button'   => ['label' => '选择方案', 'href' => '#', 'variant' => 'primary'],
        ];
    }

    protected function html(): string
    {
        $cls  = Html::cls('card h-100', ['border-primary' => (bool) $this->get('featured')]);
        $html = '<div' . $this->attrs(['class' => $cls]) . '>';
        $html .= '<div class="card-body p-4 text-center">';

        if ($this->get('badge')) {
            $html .= '<span class="badge bg-primary-subtle text-primary mb-2">' . $this->e($this->get('badge')) . '</span>';
        }
        if ($this->get('icon')) {
            $html .= '<div class="mb-3"><i class="' . $this->e($this->get('icon')) . ' fs-1 text-primary"></i></div>';
        }

        $html .= '<h4 class="fw-bold">' . $this->e($this->get('name')) . '</h4>';
        if ($this->get('desc')) {
            $html .= '<p class="text-muted">' . $this->e($this->get('desc')) . '</p>';
        }
        $html .= '<h2 class="my-3 fw-bold">' . $this->e($this->get('price'))
            . '<span class="fs-6 text-muted fw-normal">' . $this->e($this->get('period')) . '</span></h2>';

        $html .= '<ul class="list-unstyled text-start my-4">';
        foreach ((array) $this->get('features', []) as $f) {
            $enabled = $f['enabled'] ?? true;
            $icon = $enabled ? 'ti ti-circle-check text-success' : 'ti ti-circle-x text-muted';
            $muted = $enabled ? '' : ' text-muted text-decoration-line-through';
            $html .= '<li class="mb-2"><i class="' . $icon . ' me-2"></i><span class="' . $muted . '">' . $this->e($f['text'] ?? $f) . '</span></li>';
        }
        $html .= '</ul>';

        $btn = (array) $this->get('button');
        $variant = $btn['variant'] ?? ($this->get('featured') ? 'primary' : 'outline-primary');
        $html .= '<a href="' . $this->e($btn['href'] ?? '#') . '" class="btn btn-' . $this->e($variant) . ' w-100">' . $this->e($btn['label'] ?? '选择方案') . '</a>';

        $html .= '</div></div>';

        return $html;
    }
}

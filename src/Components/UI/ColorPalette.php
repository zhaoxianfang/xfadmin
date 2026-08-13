<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 配色方案展示（ui-colors.html）
 *
 * XfAdmin::colorPalette([
 *     'groups' => [
 *         ['title' => 'Primary Colors', 'colors' => ['primary' => '#4e5bf2', 'secondary' => '#6c757d', 'success' => '#1ac168', 'info' => '#2ab5d2', 'warning' => '#f6c343', 'danger' => '#ef4866', 'dark' => '#212529', 'light' => '#f8f9fa']],
 *         ['title' => 'Extended Colors', 'colors' => ['indigo' => '#6610f2', 'purple' => '#6f42c1', 'pink' => '#d63384', 'red' => '#dc3545', 'orange' => '#fd7e14', 'yellow' => '#ffc107', 'green' => '#198754', 'teal' => '#20c997', 'cyan' => '#0dcaf0']],
 *     ],
 * ])
 */
class ColorPalette extends Component
{
    protected function defaults(): array
    {
        return [
            'groups' => [],
        ];
    }

    protected function html(): string
    {
        $groups = (array) $this->get('groups', []);

        if (empty($groups)) {
            $groups = $this->getDefaultGroups();
        }

        $html = '';
        foreach ($groups as $group) {
            $group = (array) $group;
            $title = (string) ($group['title'] ?? '');
            $colors = (array) ($group['colors'] ?? []);

            $html .= '<h5 class="mb-3 mt-4">' . $this->e($title) . '</h5>';
            $html .= '<div class="row g-3">';
            foreach ($colors as $name => $hex) {
                $hex = (string) $hex;
                // 校验 hex 颜色格式（#RGB / #RRGGBB），不合法则回退到 #ccc
                $safeHex = preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex) ? $hex : '#cccccc';
                $html .= '<div class="col-md-3 col-sm-6"><div class="card"><div class="card-body p-0">';
                $html .= '<div style="height:80px;background:' . $this->e($safeHex) . ';border-radius:8px 8px 0 0"></div>';
                $html .= '<div class="p-2"><div class="fw-semibold">' . $this->e($name) . '</div>';
                $html .= '<small class="text-muted">' . $this->e(strtoupper($safeHex)) . '</small></div>';
                $html .= '</div></div></div>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    private function getDefaultGroups(): array
    {
        return [
            ['title' => '基础色', 'colors' => [
                'primary' => '#4e5bf2', 'secondary' => '#6c757d', 'success' => '#1ac168',
                'info' => '#2ab5d2', 'warning' => '#f6c343', 'danger' => '#ef4866',
            ]],
            ['title' => '中性色', 'colors' => [
                'dark' => '#212529', 'gray-dark' => '#343a40', 'gray' => '#6c757d',
                'gray-light' => '#ced4da', 'light' => '#f8f9fa', 'white' => '#ffffff',
            ]],
        ];
    }
}

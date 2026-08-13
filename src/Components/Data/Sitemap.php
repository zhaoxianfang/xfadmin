<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 站点地图（pages-sitemap.html）
 *
 * XfAdmin::sitemap([
 *     'columns' => [
 *         ['title' => 'Dashboard & Pages', 'links' => [
 *             ['label' => 'Dashboards', 'children' => ['Analytics', 'CRM', 'Sales', 'Minimal', 'eCommerce']],
 *             ['label' => 'Profile', 'children' => ['Overview', 'Edit', 'Security']],
 *             ['label' => 'Help Center'],
 *             ['label' => 'Login'],
 *             ['label' => 'Register'],
 *         ]],
 *         ['title' => 'Applications', 'links' => [
 *             ['label' => 'Calendar', 'icon' => 'ti-calendar'],
 *             ['label' => 'Email', 'icon' => 'ti-mail', 'children' => ['Inbox', 'Read', 'Compose']],
 *             ...
 *         ]],
 *         ...
 *     ],
 *     'colClass' => 'col-md-4',
 * ])
 */
class Sitemap extends Component
{
    protected function defaults(): array
    {
        return [
            'columns' => [],
            'colClass' => 'col-md-4',
        ];
    }

    protected function html(): string
    {
        $columns = (array) $this->get('columns', []);
        $colClass = (string) $this->get('colClass', 'col-md-4');

        if (empty($columns)) {
            return '';
        }

        $html = '<div class="row">';
        foreach ($columns as $col) {
            $col = (array) $col;
            $title = (string) ($col['title'] ?? '');
            $links = (array) ($col['links'] ?? []);

            $html .= '<div class="' . $this->e($colClass) . '"><div class="card"><div class="card-body">';
            if ($title) {
                $html .= '<h5 class="fw-bold text-uppercase">' . $this->e($title) . '</h5>';
            }
            if (!empty($links)) {
                $html .= '<ul class="list-unstyled sitemap-list mt-3">';
                foreach ($links as $link) {
                    $html .= $this->renderLink($link);
                }
                $html .= '</ul>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderLink(array $link): string
    {
        $link = (array) $link;
        $label = (string) ($link['label'] ?? '');
        $icon = (string) ($link['icon'] ?? '');
        $color = (string) ($link['color'] ?? 'link-reset');
        $children = (array) ($link['children'] ?? []);
        $url = (string) ($link['url'] ?? 'javascript:void(0)');

        $html = '<li>';
        $html .= '<a href="' . $this->e($url) . '" class="' . $this->e($color) . ' fw-semibold">';
        if ($icon) {
            $html .= '<i class="ti ' . $this->e($icon) . ' me-1 text-muted"></i>';
        }
        $html .= $this->e($label) . '</a>';

        if (!empty($children)) {
            $html .= '<ul>';
            foreach ($children as $child) {
                if (is_array($child)) {
                    $html .= $this->renderLink($child);
                } else {
                    $html .= '<li><a href="' . $this->e($url) . '" class="link-reset">' . $this->e((string) $child) . '</a></li>';
                }
            }
            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }
}

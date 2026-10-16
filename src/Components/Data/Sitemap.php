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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'columns' => [],
            'colClass' => 'col-md-4',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
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

            $html .= '<div class="'.$this->escape($colClass).'"><div class="card"><div class="card-body">';
            if ($title) {
                $html .= '<h5 class="fw-bold text-uppercase">'.$this->escape($title).'</h5>';
            }
            if (! empty($links)) {
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

    /**
     * render Link（private实例方法）
     *
     * @param  array  $link  link
     * @return string result
     */
    private function renderLink(array $link): string
    {
        $link = (array) $link;
        $label = (string) ($link['label'] ?? '');
        $icon = (string) ($link['icon'] ?? '');
        // color 走白名单校验，防止任意值拼入 class 属性
        $color = $this->enum((string) ($link['color'] ?? 'link-reset'), ['link-reset', 'link-primary', 'link-secondary', 'link-success', 'link-danger', 'link-warning', 'link-info', 'link-dark', 'link-body'], 'link-reset');
        $children = (array) ($link['children'] ?? []);
        // url 经 safeUrl 校验（默认保留 javascript:void(0) 无操作协议），children 分支复用同一安全值
        $url = $this->safeUrl($link['url'] ?? 'javascript:void(0)', 'javascript:void(0)');

        $html = '<li>';
        $html .= '<a href="'.$this->escape($url).'" class="'.$this->escape($color).' fw-semibold">';
        if ($icon) {
            $html .= '<i class="ti '.$this->escape($icon).' me-1 text-muted"></i>';
        }
        $html .= $this->escape($label).'</a>';

        if (! empty($children)) {
            $html .= '<ul>';
            foreach ($children as $child) {
                if (is_array($child)) {
                    $html .= $this->renderLink($child);
                } else {
                    $html .= '<li><a href="'.$this->escape($url).'" class="link-reset">'.$this->escape((string) $child).'</a></li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</li>';

        return $html;
    }
}

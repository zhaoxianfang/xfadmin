<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Navigation;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 无限极菜单导航
 *
 * 支持两种渲染模式：
 *  - side：侧边栏菜单（collapse 折叠，层级不限）
 *  - top ：水平导航菜单（dropdown 下拉，层级不限）
 *
 * XfAdmin::menu([
 *     'mode'  => 'side',
 *     'current_url' => '/users',      // 可选：自动高亮
 *     'items' => [
 *         ['title' => '导航'],                                    // 分组标题
 *         ['text' => '仪表盘', 'icon' => 'ti ti-layout-dashboard', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
 *         ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
 *             ['text' => '用户管理', 'url' => '/users'],
 *             ['text' => '更多', 'children' => [ ... 无限层级 ... ]],
 *         ]],
 *     ],
 * ])
 */
class Menu extends Component
{
    protected function defaults(): array
    {
        return [
            'mode'        => 'side',   // side | top
            'items'       => [],
            'current_url' => null,
        ];
    }

    protected function html(): string
    {
        $items = (array) $this->get('items', []);

        if ($this->get('mode') === 'top') {
            return '<ul class="navbar-nav"' . $this->attrs() . '>' . $this->renderTopItems($items, 0) . '</ul>';
        }

        return '<ul' . $this->attrs(['class' => 'side-nav']) . '>' . $this->renderSideItems($items, 0) . '</ul>';
    }

    /** 判断项本身或其后代是否处于激活状态 */
    protected function isActive(array $item): bool
    {
        if (! empty($item['active'])) {
            return true;
        }
        $current = $this->get('current_url');
        if ($current !== null && isset($item['url']) && $item['url'] !== '' && $item['url'] !== '#'
            && rtrim((string) $item['url'], '/') === rtrim((string) $current, '/')) {
            return true;
        }
        foreach ($item['children'] ?? [] as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }

        return false;
    }

    protected function badge(array $item): string
    {
        if (empty($item['badge'])) {
            return '';
        }
        $badge = is_array($item['badge']) ? $item['badge'] : ['text' => $item['badge']];
        $class = $badge['class'] ?? 'bg-danger';
        // INSPINIA 侧栏徽标约定：rounded-pill 外观（可通过传入 pill => false 关闭）
        if (($badge['pill'] ?? true) && ! str_contains($class, 'rounded')) {
            $class .= ' rounded-pill';
        }

        return '<span class="badge ' . $this->e($class) . '">' . $this->e($badge['text'] ?? '') . '</span>';
    }

    // ------------------------------------------------------------------
    // side 模式
    // ------------------------------------------------------------------

    protected function renderSideItems(array $items, int $level): string
    {
        $html = '';
        foreach ($items as $item) {
            // 分组标题
            if (isset($item['title']) && ! isset($item['text'])) {
                $html .= '<li class="side-nav-title">' . $this->e($item['title']) . '</li>';
                continue;
            }

            $active   = $this->isActive($item);
            $disabled = ! empty($item['disabled']);
            $children = $item['children'] ?? [];
            $icon     = isset($item['icon']) ? '<span class="menu-icon"><i class="' . $this->e($item['icon']) . '"></i></span>' : '';
            $text     = '<span class="menu-text">' . $this->e($item['text'] ?? '') . '</span>';

            if ($children !== []) {
                $cid  = $item['id'] ?? $this->uid('xf-menu');
                $html .= '<li class="' . Html::cls('side-nav-item', ['active' => $active]) . '">';
                $html .= '<a data-bs-toggle="collapse" href="#' . $this->e($cid) . '" aria-expanded="' . ($active ? 'true' : 'false')
                    . '" aria-controls="' . $this->e($cid) . '" class="' . Html::cls('side-nav-link', ['disabled' => $disabled]) . '">'
                    . $icon . $text . $this->badge($item) . '<span class="menu-arrow"></span></a>';
                $html .= '<div class="' . Html::cls('collapse', ['show' => $active]) . '" id="' . $this->e($cid) . '">';
                $html .= '<ul class="sub-menu">' . $this->renderSideItems($children, $level + 1) . '</ul>';
                $html .= '</div></li>';
                continue;
            }

            $url    = $item['url'] ?? '#!';
            $target = isset($item['target']) ? ' target="' . $this->e($item['target']) . '"' : '';
            $html  .= '<li class="' . Html::cls('side-nav-item', ['active' => $active]) . '">'
                . '<a href="' . $this->e($url) . '"' . $target
                . ' class="' . Html::cls('side-nav-link', ['disabled' => $disabled, 'active' => $active]) . '">'
                . $icon . $text . $this->badge($item) . '</a></li>';
        }

        return $html;
    }

    // ------------------------------------------------------------------
    // top（horizontal）模式
    // ------------------------------------------------------------------

    protected function renderTopItems(array $items, int $level): string
    {
        $html = '';
        foreach ($items as $item) {
            if (isset($item['title']) && ! isset($item['text'])) {
                if ($level > 0) {
                    $html .= '<h6 class="dropdown-header">' . $this->e($item['title']) . '</h6>';
                }
                continue;
            }

            $active   = $this->isActive($item);
            $children = $item['children'] ?? [];
            $icon     = isset($item['icon']) ? '<i class="' . $this->e($item['icon']) . ' me-1"></i>' : '';
            $label    = $icon . '<span>' . $this->e($item['text'] ?? '') . '</span>';
            $badge    = $this->badge($item);

            if ($children !== []) {
                $cid = $item['id'] ?? $this->uid('xf-topnav');
                if ($level === 0) {
                    $html .= '<li class="' . Html::cls('nav-item dropdown', ['active' => $active]) . '">'
                        . '<a class="nav-link dropdown-toggle drop-arrow-none" href="#" id="' . $this->e($cid)
                        . '" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'
                        . $label . $badge . ' <i class="ti ti-chevron-down ms-1"></i></a>'
                        . '<div class="dropdown-menu" aria-labelledby="' . $this->e($cid) . '">'
                        . $this->renderTopItems($children, $level + 1)
                        . '</div></li>';
                } else {
                    $html .= '<div class="dropdown">'
                        . '<a class="dropdown-item dropdown-toggle drop-arrow-none" href="#" id="' . $this->e($cid)
                        . '" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                        . $label . $badge . '</a>'
                        . '<div class="dropdown-menu" aria-labelledby="' . $this->e($cid) . '">'
                        . $this->renderTopItems($children, $level + 1)
                        . '</div></div>';
                }
                continue;
            }

            $url    = $item['url'] ?? '#!';
            $target = isset($item['target']) ? ' target="' . $this->e($item['target']) . '"' : '';
            if ($level === 0) {
                $html .= '<li class="nav-item"><a class="' . Html::cls('nav-link', ['active' => $active])
                    . '" href="' . $this->e($url) . '"' . $target . '>' . $label . $badge . '</a></li>';
            } else {
                $html .= '<a class="' . Html::cls('dropdown-item', ['active' => $active])
                    . '" href="' . $this->e($url) . '"' . $target . '>' . $label . $badge . '</a>';
            }
        }

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Navigation\Menu;
use zxf\XfAdmin\XfAdmin;

/**
 * 侧边栏（Logo + 可选用户卡片 + 无限极菜单）
 *
 * XfAdmin::sidenav([
 *     'brand' => ['name' => 'XfAdmin', 'logo' => '/logo.png', 'logo_sm' => '/logo-sm.png', 'url' => '/'],
 *     'user'  => ['name' => '张三', 'role' => '管理员', 'avatar' => '/a.jpg', 'items' => [['text'=>'退出','url'=>'/logout','icon'=>'ti ti-logout-2']]],
 *     'menu'  => [ ...同 Menu items... ] | Menu 实例 | 原始 HTML,
 * ])
 */
class Sidenav extends Component
{
    protected function defaults(): array
    {
        return [
            'brand'       => [],
            'user'        => false,
            'menu'        => [],
            'current_url' => null,
            'append'      => null, // 菜单下方附加内容
        ];
    }

    protected function html(): string
    {
        $html  = '<div' . $this->attrs(['class' => 'sidenav-menu']) . '>';
        $html .= $this->renderBrand();
        $html .= '<button class="button-on-hover"><i class="ti ti-menu-4 fs-22 align-middle"></i></button>';
        $html .= '<button class="button-close-offcanvas"><i class="ti ti-x align-middle"></i></button>';
        $html .= '<div class="scrollbar" data-simplebar>';
        $html .= $this->renderUser();
        $html .= $this->renderMenu();
        $html .= $this->raw($this->get('append'));
        $html .= '</div></div>';

        return $html;
    }

    protected function renderBrand(): string
    {
        $brand = (array) $this->get('brand', []);
        $brand += (array) XfAdmin::setting('brand', []);

        $url     = $brand['url'] ?? $brand['home_url'] ?? '/';
        $name    = $brand['name'] ?? 'XfAdmin';
        $assets  = XfAdmin::assets();
        $logo    = $brand['logo'] ?? $assets->url('images/logo.png');
        $logoDk  = $brand['logo_dark'] ?? $assets->url('images/logo-black.png');
        $logoSm  = $brand['logo_sm'] ?? $assets->url('images/logo-sm.png');

        return '<a href="' . $this->e($url) . '" class="logo">'
            . '<span class="logo logo-light">'
            . '<span class="logo-lg"><img src="' . $this->e($logo) . '" alt="' . $this->e($name) . '"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="' . $this->e($name) . '"></span>'
            . '</span>'
            . '<span class="logo logo-dark">'
            . '<span class="logo-lg"><img src="' . $this->e($logoDk) . '" alt="' . $this->e($name) . '"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="' . $this->e($name) . '"></span>'
            . '</span></a>';
    }

    protected function renderUser(): string
    {
        $user = $this->get('user');
        if (! $user) {
            return '';
        }
        $user   = (array) $user;
        $avatar = $user['avatar'] ?? XfAdmin::assets()->url('images/users/user-2.jpg');
        $items  = $user['items'] ?? [];

        $dropdown = '';
        if ($items !== []) {
            $dropdown .= '<div><a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="#!" aria-haspopup="false" aria-expanded="false">'
                . '<i class="ti ti-settings fs-24 align-middle ms-1"></i></a><div class="dropdown-menu">';
            foreach ($items as $item) {
                if (($item['divider'] ?? false) === true) {
                    $dropdown .= '<div class="dropdown-divider"></div>';
                    continue;
                }
                $icon = isset($item['icon']) ? '<i class="' . $this->e($item['icon']) . ' me-2 fs-17 align-middle"></i>' : '';
                $dropdown .= '<a href="' . $this->e($item['url'] ?? '#!') . '" class="dropdown-item ' . $this->e($item['class'] ?? '') . '">'
                    . $icon . '<span class="align-middle">' . $this->e($item['text'] ?? '') . '</span></a>';
            }
            $dropdown .= '</div></div>';
        }

        return '<div class="sidenav-user"><div class="d-flex justify-content-between align-items-center"><div>'
            . '<a href="' . $this->e($user['url'] ?? '#!') . '" class="link-reset">'
            . '<img src="' . $this->e($avatar) . '" alt="user-image" class="rounded-circle mb-2 avatar-md">'
            . '<span class="sidenav-user-name fw-bold">' . $this->e($user['name'] ?? '') . '</span>'
            . '<span class="fs-12 fw-semibold">' . $this->e($user['role'] ?? '') . '</span>'
            . '</a></div>' . $dropdown . '</div></div>';
    }

    protected function renderMenu(): string
    {
        $menu = $this->get('menu');
        if ($menu instanceof Menu) {
            return $menu->render();
        }
        if (is_array($menu)) {
            return Menu::make([
                'mode'        => 'side',
                'items'       => $menu,
                'current_url' => $this->get('current_url'),
            ])->render();
        }

        return $this->raw($menu);
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 主题定制面板（offcanvas）——皮肤 / 明暗 / 顶栏色 / 菜单色 / 侧栏尺寸 / 布局位置
 * 与模板 app.js 联动（元素 name 与 id 必须保持模板约定）
 */
class Customizer extends Component
{
    protected function defaults(): array
    {
        return [
            'title'    => 'Admin Customizer',
            'subtitle' => '快速配置后台界面的布局、皮肤与偏好',
        ];
    }

    protected function html(): string
    {
        $a = fn (string $p): string => XfAdmin::assets()->url($p);

        $skins = [
            'classic' => ['Classic', 'images/layouts/themes/theme-classic.png'],
            'material' => ['Material', 'images/layouts/themes/theme-material.png'],
            'modern' => ['Modern', 'images/layouts/themes/theme-modern.png'],
            'saas' => ['SaaS', 'images/layouts/themes/theme-saas.png'],
            'flat' => ['Flat', 'images/layouts/themes/theme-flat.png'],
            'minimal' => ['Minimal', 'images/layouts/themes/theme-minimal.png'],
        ];
        $modes = [
            'light' => ['Light', 'images/layouts/light.svg'],
            'dark' => ['Dark', 'images/layouts/dark.svg'],
            'system' => ['System', 'images/layouts/system.svg'],
        ];
        $topbarColors = [
            'light' => ['Light', 'images/layouts/topbar-light.svg'],
            'dark' => ['Dark', 'images/layouts/topbar-dark.svg'],
            'gray' => ['Gray', 'images/layouts/topbar-gray.svg'],
            'gradient' => ['Gradient', 'images/layouts/topbar-gradient.svg'],
        ];
        $menuColors = [
            'light' => ['Light', 'images/layouts/light.svg'],
            'dark' => ['Dark', 'images/layouts/side-dark.svg'],
            'gray' => ['Gray', 'images/layouts/side-gray.svg'],
            'gradient' => ['Gradient', 'images/layouts/side-gradient.svg'],
            'image' => ['Image', 'images/layouts/side-image.svg'],
        ];
        $sizes = [
            'default' => ['Default', 'images/layouts/light.svg'],
            'compact' => ['Compact', 'images/layouts/sidebar-compact.svg'],
            'condensed' => ['Condensed', 'images/layouts/sidebar-sm.svg'],
            'on-hover' => ['On Hover', 'images/layouts/sidebar-sm.svg'],
            'on-hover-active' => ['On Hover - Show', 'images/layouts/light.svg'],
            'offcanvas' => ['Offcanvas', 'images/layouts/sidebar-full.svg'],
        ];

        $radioGroup = function (string $name, array $items, string $col = 'col-4') use ($a): string {
            $html = '<div class="row g-3">';
            foreach ($items as $value => [$label, $img]) {
                $id    = 'xf-' . $name . '-' . $value;
                $html .= '<div class="' . $col . '"><div class="form-check card-radio">'
                    . '<input class="form-check-input" type="radio" name="' . $this->e($name) . '" id="' . $this->e($id) . '" value="' . $this->e($value) . '">'
                    . '<label class="form-check-label p-0 w-100" for="' . $this->e($id) . '">'
                    . '<img src="' . $this->e($a($img)) . '" alt="layout-img" class="img-fluid"></label></div>'
                    . '<h5 class="fs-sm text-center text-muted mt-2 mb-0">' . $this->e($label) . '</h5></div>';
            }

            return $html . '</div>';
        };

        $section = fn (string $title, string $body): string => '<div class="p-3 border-bottom"><h5 class="mb-3 fw-bold">' . $this->e($title) . '</h5>' . $body . '</div>';

        $html  = '<div class="offcanvas offcanvas-end overflow-hidden" tabindex="-1" id="theme-settings-offcanvas">';
        $html .= '<div class="d-flex justify-content-between text-bg-primary gap-2 p-3" style="background-image: url(' . $this->e($a('images/user-bg-pattern.png')) . ');">'
            . '<div><h5 class="mb-1 fw-bold text-white text-uppercase">' . $this->e($this->get('title')) . '</h5>'
            . '<p class="text-white text-opacity-75 fst-italic fw-medium mb-0">' . $this->e($this->get('subtitle')) . '</p></div>'
            . '<div class="flex-grow-0"><button type="button" class="d-block btn btn-sm bg-white bg-opacity-25 text-white rounded-circle btn-icon" data-bs-dismiss="offcanvas"><i class="ti ti-x fs-lg"></i></button></div></div>';

        $html .= '<div class="offcanvas-body p-0 h-100" data-simplebar>';
        $html .= $section('选择皮肤', $radioGroup('data-skin', $skins, 'col-6'));
        $html .= $section('明暗模式', $radioGroup('data-bs-theme', $modes));
        $html .= $section('顶栏颜色', $radioGroup('data-topbar-color', $topbarColors));
        $html .= $section('菜单颜色', $radioGroup('data-menu-color', $menuColors));
        $html .= $section('侧栏尺寸', $radioGroup('data-sidenav-size', $sizes));
        $html .= '<div class="p-3 border-bottom"><div class="d-flex justify-content-between align-items-center"><h5 class="fw-bold mb-0">布局位置</h5>'
            . '<div class="btn-group radio" role="group">'
            . '<input type="radio" class="btn-check" name="data-layout-position" id="layout-position-fixed" value="fixed">'
            . '<label class="btn btn-sm btn-soft-warning w-sm" for="layout-position-fixed">Fixed</label>'
            . '<input type="radio" class="btn-check" name="data-layout-position" id="layout-position-scrollable" value="scrollable">'
            . '<label class="btn btn-sm btn-soft-warning w-sm ms-0" for="layout-position-scrollable">Scrollable</label>'
            . '</div></div></div>';
        $html .= '<div class="p-3"><div class="d-flex justify-content-between align-items-center">'
            . '<h5 class="mb-0"><label class="fw-bold m-0" for="sidebaruser-check">侧栏用户信息</label></h5>'
            . '<div class="form-check form-switch fs-lg"><input type="checkbox" class="form-check-input" name="sidebar-user" id="sidebaruser-check"></div>'
            . '</div></div>';
        $html .= '</div>';

        $html .= '<div class="offcanvas-footer border-top p-3 text-center"><div class="row"><div class="col-12">'
            . '<button type="button" class="btn btn-light fw-semibold py-2 w-100" id="reset-layout">重置</button>'
            . '</div></div></div>';
        $html .= '</div>';

        return $html;
    }
}

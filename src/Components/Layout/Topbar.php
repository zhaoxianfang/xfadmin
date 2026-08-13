<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 顶部导航栏
 *
 * XfAdmin::topbar([
 *     'brand'         => true,            // 顶栏品牌 logo
 *     'search'        => true,            // 搜索框
 *     'left'          => '自定义HTML',     // 左侧附加插槽
 *     'theme_toggle'  => true,            // 明暗切换按钮
 *     'fullscreen'    => true,            // 全屏按钮
 *     'customizer'    => true,            // 主题定制按钮
 *     'languages'     => [['flag'=>..,'name'=>'中文','code'=>'zh'], ...],
 *     'notifications' => ['count' => 3, 'items' => [['title'=>..,'text'=>..,'time'=>..,'avatar'=>..,'icon'=>..,'url'=>..]], 'all_url' => '#'],
 *     'messages'      => ['count' => 7, 'title'=>'消息', 'items' => [['from'=>..,'avatar'=>..,'text'=>..,'time'=>..,'url'=>..,'unread'=>true]], 'all_url'=>'#'],
 *     'apps'          => ['items' => [['icon'=>'ti ti-mail','text'=>'邮件','url'=>'#','variant'=>'primary'], ...]],
 *     'search_modal'  => true,              // 点击搜索图标弹出全屏搜索模态（需 search=true）
 *     'user'          => ['name'=>'张三','role'=>'管理员','avatar'=>'/a.jpg','items'=>[['text'=>'退出','icon'=>'ti ti-logout-2','url'=>'/logout']]],
 *     'right'         => '自定义HTML',     // 右侧附加插槽
 * ])
 */
class Topbar extends Component
{
    protected function defaults(): array
    {
        return [
            'brand'         => true,
            'search'        => true,
            'search_placeholder' => 'Search...',
            'search_modal'  => false,           // 点击搜索图标弹出全屏模态（替代内联搜索框）
            'left'          => null,
            'theme_toggle'  => true,
            'fullscreen'    => true,
            'customizer'    => true,
            'languages'     => [],
            'notifications' => false,
            'messages'      => false,
            'apps'          => false,
            'user'          => false,
            'right'         => null,
        ];
    }

    protected function html(): string
    {
        $html  = '<header' . $this->attrs(['class' => 'app-topbar']) . '>';
        $html .= '<div class="container-fluid topbar-menu">';

        // 左侧
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= $this->renderBrand();
        $html .= '<button class="sidenav-toggle-button btn btn-primary btn-icon"><i class="ti ti-menu-4 fs-22"></i></button>';
        if ($this->get('search')) {
            if ($this->get('search_modal')) {
                // 点击弹出全屏搜索模态
                $html .= '<div class="topbar-item d-none d-xl-flex"><button class="topbar-link" type="button" data-bs-toggle="modal" data-bs-target="#xfTopbarSearchModal"><i class="ti ti-search fs-xxl"></i></button></div>';
                $html .= $this->renderSearchModal();
            } else {
                $html .= '<div class="app-search d-none d-xl-flex">'
                    . '<input type="search" class="form-control topbar-search" name="search" placeholder="' . $this->e($this->get('search_placeholder')) . '">'
                    . '<i class="ti ti-search app-search-icon text-muted"></i></div>';
            }
        }
        $html .= $this->raw($this->get('left'));
        $html .= '</div>';

        // 右侧
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= $this->raw($this->get('right'));
        $html .= $this->renderLanguages();
        $html .= $this->renderMessages();
        $html .= $this->renderApps();
        $html .= $this->renderNotifications();
        if ($this->get('customizer')) {
            $html .= '<div class="topbar-item d-none d-sm-flex"><button class="topbar-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button"><i class="ti ti-settings fs-xxl"></i></button></div>';
        }
        if ($this->get('fullscreen')) {
            $html .= '<div class="topbar-item d-none d-sm-flex"><button class="topbar-link" data-toggle="fullscreen" type="button"><i class="ti ti-maximize fs-xxl"></i></button></div>';
        }
        if ($this->get('theme_toggle')) {
            $html .= '<div class="topbar-item"><button class="topbar-link" id="light-dark-mode" type="button"><i class="ti ti-moon fs-xxl mode-icon icon-moon"></i><i class="ti ti-sun fs-xxl mode-icon icon-sun"></i></button></div>';
        }
        $html .= $this->renderUser();
        $html .= '</div>';

        $html .= '</div></header>';

        return $html;
    }

    protected function renderBrand(): string
    {
        if (! $this->get('brand')) {
            return '';
        }
        $brand  = is_array($this->get('brand')) ? $this->get('brand') : [];
        $brand += (array) XfAdmin::setting('brand', []);
        $assets = XfAdmin::assets();
        $url    = $brand['url'] ?? $brand['home_url'] ?? '/';
        $logo   = $brand['logo'] ?? $assets->url('images/logo.png');
        $logoDk = $brand['logo_dark'] ?? $assets->url('images/logo-black.png');
        $logoSm = $brand['logo_sm'] ?? $assets->url('images/logo-sm.png');

        return '<div class="logo-topbar">'
            . '<a href="' . $this->e($url) . '" class="logo-light">'
            . '<span class="logo-lg"><img src="' . $this->e($logo) . '" alt="logo"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="small logo"></span></a>'
            . '<a href="' . $this->e($url) . '" class="logo-dark">'
            . '<span class="logo-lg"><img src="' . $this->e($logoDk) . '" alt="dark logo"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="small logo"></span></a>'
            . '</div>';
    }

    protected function renderLanguages(): string
    {
        $languages = (array) $this->get('languages', []);
        if ($languages === []) {
            return '';
        }
        $current = null;
        foreach ($languages as $lang) {
            if (! empty($lang['active'])) {
                $current = $lang;
                break;
            }
        }
        $current ??= $languages[0];

        $html = '<div class="topbar-item"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" aria-haspopup="false" aria-expanded="false">';
        $html .= isset($current['flag'])
            ? '<img src="' . $this->e($current['flag']) . '" alt="language" class="w-100 rounded" height="18" style="height:18px;">'
            : '<span class="fw-semibold">' . $this->e($current['name'] ?? '') . '</span>';
        $html .= '</button><div class="dropdown-menu dropdown-menu-end">';
        foreach ($languages as $lang) {
            $flag  = isset($lang['flag']) ? '<img src="' . $this->e($lang['flag']) . '" alt="lang" class="me-1 rounded" height="18" style="height:18px;"> ' : '';
            $html .= '<a href="' . $this->e($lang['url'] ?? 'javascript:void(0);') . '" class="dropdown-item"'
                . (isset($lang['code']) ? ' data-lang-code="' . $this->e($lang['code']) . '"' : '') . '>'
                . $flag . '<span class="align-middle">' . $this->e($lang['name'] ?? '') . '</span></a>';
        }
        $html .= '</div></div></div>';

        return $html;
    }

    protected function renderNotifications(): string
    {
        $conf = $this->get('notifications');
        if (! $conf) {
            return '';
        }
        $conf  = (array) $conf;
        $items = $conf['items'] ?? [];
        $count = $conf['count'] ?? count($items);

        $html = '<div class="topbar-item"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none position-relative" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">'
            . '<i class="ti ti-bell fs-xxl"></i>';
        if ($count > 0) {
            $html .= '<span class="position-absolute topbar-badge fs-xxs translate-middle badge bg-danger rounded-pill">' . $this->e($count) . '</span>';
        }
        $html .= '</button><div class="dropdown-menu p-0 dropdown-menu-start dropdown-menu-lg" style="min-height: 300px;">';
        $html .= '<div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border"><div class="row align-items-center"><div class="col">'
            . '<h6 class="m-0 fs-md fw-semibold">' . $this->e($conf['title'] ?? '通知') . '</h6></div></div></div>';
        $html .= '<div style="max-height: 300px;" data-simplebar>';
        foreach ($items as $item) {
            $media = '';
            if (isset($item['avatar'])) {
                $media = '<img src="' . $this->e($item['avatar']) . '" class="avatar-md rounded-circle" alt="">';
            } elseif (isset($item['icon'])) {
                $media = '<div class="avatar-md"><span class="avatar-title bg-' . $this->e($item['variant'] ?? 'primary') . '-subtle text-' . $this->e($item['variant'] ?? 'primary') . ' rounded-circle"><i class="' . $this->e($item['icon']) . ' fs-22"></i></span></div>';
            }
            $html .= '<div class="dropdown-item notification-item py-2 text-wrap">'
                . '<a href="' . $this->e($item['url'] ?? 'javascript:void(0);') . '" class="d-flex align-items-center gap-2 text-reset">'
                . '<span class="flex-shrink-0">' . $media . '</span>'
                . '<span class="flex-grow-1 text-muted">'
                . (isset($item['title']) ? '<span class="fw-medium text-body">' . $this->e($item['title']) . '</span> ' : '')
                . $this->e($item['text'] ?? '')
                . (isset($item['time']) ? '<br><span class="fs-xs">' . $this->e($item['time']) . '</span>' : '')
                . '</span></a></div>';
        }
        $html .= '</div>';
        if (! empty($conf['all_url'])) {
            $html .= '<a href="' . $this->e($conf['all_url']) . '" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">'
                . $this->e($conf['all_text'] ?? '查看全部') . '</a>';
        }
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * 消息中心入口（与 TopNav 风格一致）。
     * 配置：messages => ['count'=>int,'title'=>str,'items'=>[['from'=>,'avatar'=>,'text'=>,'time'=>,'url'=>,'unread'=>bool]],'all_url'=>,'all_text'=>]
     */
    protected function renderMessages(): string
    {
        $conf = $this->get('messages');
        if (! $conf) {
            return '';
        }
        $conf  = (array) $conf;
        $items = $conf['items'] ?? [];
        $count = $conf['count'] ?? count($items);

        $html = '<div class="topbar-item"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none position-relative" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">'
            . '<i class="ti ti-message-2 fs-xxl"></i>';
        if ($count > 0) {
            $html .= '<span class="position-absolute topbar-badge fs-xxs translate-middle badge bg-danger rounded-pill">' . $this->e($count) . '</span>';
        }
        $html .= '</button><div class="dropdown-menu p-0 dropdown-menu-start dropdown-menu-lg" style="min-height: 300px;">';
        $html .= '<div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border"><div class="row align-items-center"><div class="col">'
            . '<h6 class="m-0 fs-md fw-semibold">' . $this->e($conf['title'] ?? '消息') . '</h6></div></div></div>';
        $html .= '<div style="max-height: 300px;" data-simplebar>';
        foreach ($items as $item) {
            $item   = (array) $item;
            $avatar = isset($item['avatar']) ? '<img src="' . $this->e($item['avatar']) . '" class="avatar-md rounded-circle" alt="">'
                : '<div class="avatar-md"><span class="avatar-title bg-' . $this->e($item['variant'] ?? 'info') . '-subtle text-' . $this->e($item['variant'] ?? 'info') . ' rounded-circle"><i class="ti ti-user fs-22"></i></span></div>';
            $html .= '<div class="dropdown-item py-2 text-wrap' . (! empty($item['unread']) ? ' unread' : '') . '">'
                . '<a href="' . $this->e($item['url'] ?? 'javascript:void(0);') . '" class="d-flex align-items-center gap-2 text-reset">'
                . '<span class="flex-shrink-0">' . $avatar . '</span>'
                . '<span class="flex-grow-1">'
                . '<span class="fw-medium text-body">' . $this->e($item['from'] ?? '') . '</span>'
                . '<span class="d-block text-muted">' . $this->e($item['text'] ?? '') . '</span>'
                . (isset($item['time']) ? '<small class="text-muted">' . $this->e($item['time']) . '</small>' : '')
                . '</span></a></div>';
        }
        $html .= '</div>';
        if (! empty($conf['all_url'])) {
            $html .= '<a href="' . $this->e($conf['all_url']) . '" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold border-top border-light py-2">'
                . $this->e($conf['all_text'] ?? '查看全部') . '</a>';
        }
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * 应用启动器入口（九宫格图标下拉）。
     * 配置：apps => [
     *     'title'   => str,
     *     'variant' => 'grid'|'rounded',     // grid=方角九宫格（默认）；rounded=圆形图标（对齐后台模板 #apps-dropdown-rounded）
     *     'all_url' => str, 'all_text' => str,
     *     'add_url' => str, 'add_text' => str,
     *     'items'   => [['icon'=>,'text'=>,'url'=>,'variant'=>], ...],
     * ]
     * 圆形变体输出容器 id="apps-dropdown-rounded"，与后台模板（INSPINIA v4）顶栏 App Launcher 标记一致，
     * 便于样式挂钩与 JS 定位（xfadmin.js 的 initAppsDropdown）。
     */
    protected function renderApps(): string
    {
        $conf = $this->get('apps');
        if (! $conf) {
            return '';
        }
        $conf  = (array) $conf;
        $items = $conf['items'] ?? [];
        if ($items === []) {
            return '';
        }
        $variant = strtolower((string) ($conf['variant'] ?? 'grid'));
        $rounded = $variant === 'rounded';

        $html = '<div class="topbar-item"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" aria-haspopup="false" aria-expanded="false">'
            . '<i class="ti ti-layout-grid fs-xxl"></i></button>';
        $menuCls = 'dropdown-menu dropdown-menu-start p-2' . ($rounded ? ' apps-dropdown-rounded-menu' : '');
        $html .= '<div class="' . $menuCls . '"' . ($rounded ? ' id="apps-dropdown-rounded"' : '') . ' style="min-width: 280px;">';
        if (! empty($conf['title'])) {
            $html .= '<h6 class="dropdown-header">' . $this->e($conf['title']) . '</h6>';
        }
        $html .= '<div class="row g-1 app-launcher-grid' . ($rounded ? ' app-launcher-rounded' : '') . '">';
        foreach ($items as $item) {
            $item    = (array) $item;
            $iv      = $item['variant'] ?? 'primary';
            $shape   = $rounded ? 'rounded-circle' : 'rounded';
            $html .= '<div class="col-4">'
                . '<a href="' . $this->e($item['url'] ?? 'javascript:void(0);') . '" class="d-flex flex-column align-items-center text-reset text-decoration-none rounded p-2 app-launcher-item">'
                . '<span class="avatar-md mb-1"><span class="avatar-title bg-' . $this->e($iv) . '-subtle text-' . $this->e($iv) . ' ' . $shape . '"><i class="' . $this->e($item['icon'] ?? 'ti ti-apps') . ' fs-22"></i></span></span>'
                . '<small class="text-truncate">' . $this->e($item['text'] ?? '') . '</small>'
                . '</a></div>';
        }
        $html .= '</div>';
        // 底部操作：查看全部 / 添加应用
        $foot = '';
        if (! empty($conf['all_url']) || ! empty($conf['add_url'])) {
            $foot .= '<div class="d-flex gap-2 mt-2 pt-2 border-top app-launcher-foot">';
            if (! empty($conf['all_url'])) {
                $foot .= '<a href="' . $this->e($conf['all_url']) . '" class="btn btn-sm btn-soft-primary flex-fill">' . $this->e($conf['all_text'] ?? '查看全部应用') . '</a>';
            }
            if (! empty($conf['add_url'])) {
                $foot .= '<a href="' . $this->e($conf['add_url']) . '" class="btn btn-sm btn-soft-secondary flex-fill"><i class="ti ti-plus me-1"></i>' . $this->e($conf['add_text'] ?? '添加应用') . '</a>';
            }
            $foot .= '</div>';
        }
        $html .= $foot;
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * 全屏搜索模态（点击搜索图标弹出）。
     * 配置：search_modal => true（需同时 search=true）
     */
    protected function renderSearchModal(): string
    {
        $ph = $this->e($this->get('search_placeholder'));
        return '<div class="modal fade" id="xfTopbarSearchModal" tabindex="-1" aria-hidden="true">'
            . '<div class="modal-dialog modal-dialog-centered modal-lg">'
            . '<div class="modal-content border-0 shadow">'
            . '<div class="modal-body p-3">'
            . '<div class="position-relative">'
            . '<input type="search" class="form-control form-control-lg ps-5" placeholder="' . $ph . '" autocomplete="off">'
            . '<i class="ti ti-search position-absolute top-50 translate-middle-y ms-3 text-muted fs-18"></i>'
            . '</div>'
            . '<div class="mt-3 text-muted small"><i class="ti ti-info-circle me-1"></i>' . $this->e($this->get('search_hint') ?? '输入关键字后回车进行搜索') . '</div>'
            . '</div></div></div></div>';
    }

    protected function renderUser(): string
    {
        $user = $this->get('user');
        if (! $user) {
            return '';
        }
        $user   = (array) $user;
        $avatar = $user['avatar'] ?? XfAdmin::assets()->url('images/users/user-2.jpg');

        $html = '<div class="topbar-item nav-user"><div class="dropdown">'
            . '<a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,16" href="#!" aria-haspopup="false" aria-expanded="false">'
            . '<img src="' . $this->e($avatar) . '" class="rounded-circle avatar-md me-lg-2" alt="user-image">'
            . '<div class="d-lg-flex align-items-center gap-1 d-none">'
            . '<h5 class="my-0">' . $this->e($user['name'] ?? '') . '</h5>'
            . '<i class="ti ti-chevron-down align-middle"></i></div></a>'
            . '<div class="dropdown-menu dropdown-menu-end">';
        if (! empty($user['header'])) {
            $html .= '<div class="dropdown-header noti-title"><h6 class="text-overflow m-0">' . $this->e($user['header']) . '</h6></div>';
        }
        foreach ($user['items'] ?? [] as $item) {
            if (($item['divider'] ?? false) === true) {
                $html .= '<div class="dropdown-divider"></div>';
                continue;
            }
            $icon  = isset($item['icon']) ? '<i class="' . $this->e($item['icon']) . ' me-2 fs-17 align-middle"></i>' : '';
            $html .= '<a href="' . $this->e($item['url'] ?? '#!') . '" class="dropdown-item ' . $this->e($item['class'] ?? '') . '">'
                . $icon . '<span class="align-middle">' . $this->e($item['text'] ?? '') . '</span></a>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

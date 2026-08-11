<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 顶部水平导航（TopNav）
 *
 * 来源：INSPINIA v4.0 layouts-horizontal.html 的 header.app-topbar 与 header.topnav
 * 两个 DOM 的拆分 / 重构 / 合并 —— 原模板中「横向菜单」独立成第二个 header.topnav，
 * 本组件将其合并进 header.app-topbar：菜单渲染在品牌 Logo 之后、右侧工具区（语言 /
 * 消息 / 通知 / 主题 / 用户头像）之前，即「系统头像右侧」的水平菜单条。
 *
 * 布局要点：
 *  - 单一 <header class="app-topbar"> 容器，内部 topbar-menu 使用 flex 三段式；
 *  - 菜单沿用模板 .topnav .navbar-nav 的 class 契约，直接复用 app.min.css 中
 *    已实现的 hover 级联下拉（含无限级嵌套 .dropdown > .dropdown-menu）；
 *  - 小屏（<lg）由 .topnav-toggle-button 触发 Bootstrap collapse 折叠展开。
 *
 * XfAdmin::topNav([
 *     'brand'         => true,
 *     'menu'          => [ ... ],          // 见 renderMenu()，支持无限级 children 与 mega
 *     'current_url'   => '/admin/x',
 *     'search'        => true,
 *     'mega'          => ['text'=>'快捷入口','title'=>'...','columns'=>[...]],
 *     'languages'     => [['flag'=>..,'name'=>'简体中文','code'=>'cn','active'=>true]],
 *     'messages'      => ['count'=>7,'items'=>[...]],
 *     'notifications' => ['count'=>3,'items'=>[...]],
 *     'theme_toggle'  => true,
 *     'fullscreen'    => true,
 *     'customizer'    => true,
 *     'user'          => ['name'=>'张三','avatar'=>..,'items'=>[...]],
 * ])
 */
class TopNav extends Component
{
    protected function defaults(): array
    {
        return [
            'brand'              => true,
            'sidenav_toggle'     => false,  // 纯水平布局默认不显示侧栏切换按钮
            'menu'               => [],
            'current_url'        => null,
            'search'             => false,
            'search_placeholder' => 'Search for something...',
            'mega'               => false,
            'left'               => null,
            'languages'          => [],
            'messages'           => false,
            'notifications'      => false,
            'theme_toggle'       => true,
            'fullscreen'         => true,
            'customizer'         => true,
            'user'               => false,
            'right'              => null,
        ];
    }

    protected function html(): string
    {
        $html  = '<header' . $this->attrs(['class' => 'app-topbar']) . '>';
        $html .= '<div class="container-fluid topbar-menu">';

        // ---------- 左：品牌 + 折叠按钮 + 搜索 + Mega ----------
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= $this->renderBrand();
        if ($this->get('sidenav_toggle')) {
            $html .= '<button class="sidenav-toggle-button btn btn-primary btn-icon" type="button">'
                . '<i class="ti ti-menu-4 fs-22"></i></button>';
        }
        // 横向菜单折叠按钮（小屏显示，模板原生 class）
        $html .= '<button class="topnav-toggle-button px-2" type="button" data-bs-toggle="collapse"'
            . ' data-bs-target="#topnav-menu-content" aria-controls="topnav-menu-content"'
            . ' aria-expanded="false" aria-label="Toggle navigation">'
            . '<i class="ti ti-menu-4 fs-22"></i></button>';
        $html .= $this->renderMega();
        $html .= $this->raw($this->get('left'));
        $html .= '</div>';

        // ---------- 中：水平菜单（合并自 header.topnav） ----------
        $html .= $this->renderMenu();

        // ---------- 右：搜索 / 语言 / 消息 / 通知 / 定制 / 全屏 / 明暗 / 用户 ----------
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= $this->raw($this->get('right'));
        if ($this->get('search')) {
            $html .= '<div class="app-search topnav-search d-none d-lg-flex">'
                . '<input type="search" class="form-control topbar-search" name="search" placeholder="'
                . $this->e($this->get('search_placeholder')) . '">'
                . '<i class="ti ti-search app-search-icon text-muted"></i></div>';
        }
        $html .= $this->renderLanguages();
        $html .= $this->renderMessages();
        $html .= $this->renderNotifications();
        if ($this->get('customizer')) {
            $html .= '<div class="topbar-item d-none d-sm-flex"><button class="topbar-link"'
                . ' data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">'
                . '<i class="ti ti-settings fs-xxl"></i></button></div>';
        }
        if ($this->get('fullscreen')) {
            $html .= '<div class="topbar-item d-none d-sm-flex"><button class="topbar-link"'
                . ' data-toggle="fullscreen" type="button"><i class="ti ti-maximize fs-xxl"></i></button></div>';
        }
        if ($this->get('theme_toggle')) {
            $html .= '<div class="topbar-item"><button class="topbar-link" id="light-dark-mode" type="button">'
                . '<i class="ti ti-moon fs-xxl mode-icon icon-moon"></i>'
                . '<i class="ti ti-sun fs-xxl mode-icon icon-sun"></i></button></div>';
        }
        $html .= $this->renderUser();
        $html .= '</div>';

        $html .= '</div></header>';

        return $html;
    }

    /* ------------------------------------------------------------------ */
    /* 品牌                                                                */
    /* ------------------------------------------------------------------ */

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
        $name   = $brand['name'] ?? $brand['title'] ?? '';

        $nameHtml = $name !== ''
            ? '<span class="logo-text d-none d-sm-inline-block text-truncate ms-2">'
              . $this->e($name) . '</span>'
            : '';

        return '<div class="logo-topbar d-flex align-items-center">'
            . '<a href="' . $this->e($url) . '" class="logo-light">'
            . '<span class="logo-lg"><img src="' . $this->e($logo) . '" alt="logo"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="small logo"></span></a>'
            . '<a href="' . $this->e($url) . '" class="logo-dark">'
            . '<span class="logo-lg"><img src="' . $this->e($logoDk) . '" alt="dark logo"></span>'
            . '<span class="logo-sm"><img src="' . $this->e($logoSm) . '" alt="small logo"></span></a>'
            . $nameHtml
            . '</div>';
    }

    /* ------------------------------------------------------------------ */
    /* 水平菜单（核心）                                                     */
    /* ------------------------------------------------------------------ */

    /**
     * 菜单项 schema：
     *   ['text'=>'仪表盘','icon'=>'ti ti-x','url'=>'/x','key'=>'dash','badge'=>['text'=>'New','class'=>'text-bg-danger'],
     *    'disabled'=>false,'children'=>[ ...递归... ],
     *    'mega'=>['cols'=>3,'title'=>'...','columns'=>[['title'=>'分组','items'=>[['text'=>..,'url'=>..]]]]]]
     */
    protected function renderMenu(): string
    {
        $items = (array) $this->get('menu', []);
        if ($items === []) {
            return '';
        }

        // 外层保留模板 .topnav 类：app.min.css 中水平菜单的全部样式
        // （hover 级联下拉、菜单项排版、移动端手风琴）均以 .topnav 为作用域，
        // 合并进 app-topbar 后仍需该 class 才能命中框架样式。
        $html = '<div class="topnav topnav-inline">'
            . '<nav class="navbar navbar-expand-lg">'
            . '<div class="collapse navbar-collapse" id="topnav-menu-content">';
        // 移动端折叠面板顶部显示搜索框（小屏优先）
        if ($this->get('search')) {
            $html .= '<div class="app-search topnav-search d-lg-none p-2 border-bottom">'
                . '<input type="search" class="form-control topbar-search" name="search" placeholder="'
                . $this->e($this->get('search_placeholder')) . '">'
                . '<i class="ti ti-search app-search-icon text-muted"></i></div>';
        }
        $html .= '<ul class="navbar-nav">';
        foreach ($items as $item) {
            $html .= $this->renderTopItem((array) $item);
        }
        $html .= '</ul></div></nav></div>';

        return $html;
    }

    /** 一级菜单项 */
    protected function renderTopItem(array $item): string
    {
        $children = (array) ($item['children'] ?? []);
        $mega     = $item['mega'] ?? null;
        $hasDrop  = $children !== [] || ! empty($mega);
        $active   = $this->isActive($item);

        $liClass = 'nav-item' . ($hasDrop ? ' dropdown' : '') . ($active ? ' active' : '');
        $html    = '<li class="' . $liClass . '">';

        $icon = ! empty($item['icon'])
            ? '<span class="menu-icon"><i class="' . $this->e($item['icon']) . '"></i></span>'
            : '';
        $text  = '<span class="menu-text"> ' . $this->e($item['text'] ?? '') . ' </span>';
        $badge = $this->renderBadge($item);

        if ($hasDrop) {
            // 注意：此处【不】使用 data-bs-toggle="dropdown"。
            // 桌面端级联展开完全由框架 CSS 的 .topnav .dropdown:hover 负责；
            // 移动端手风琴由 xfadmin.js 自行切换 .show。若交给 Bootstrap 接管，
            // 其 dropdown 实例会在点击后立即移除我们添加的 .show（并施加浮层定位），
            // 导致移动端菜单无法展开。
            $id    = $this->uid('topnav');
            $html .= '<a class="nav-link dropdown-toggle drop-arrow-none" href="#" id="' . $id . '"'
                . ' role="button" aria-haspopup="true" aria-expanded="false">'
                . $icon . $text . $badge . '<div class="menu-arrow"></div></a>';
            $html .= ! empty($mega)
                ? $this->renderMegaPanel((array) $mega, $id)
                : $this->renderDropdown($children, $id);
        } else {
            $html .= '<a class="nav-link" href="' . $this->e($item['url'] ?? '#') . '">'
                . $icon . $text . $badge . '</a>';
        }

        return $html . '</li>';
    }

    /** 下拉面板（无限级递归） */
    protected function renderDropdown(array $items, string $labelledBy): string
    {
        $html = '<div class="dropdown-menu" aria-labelledby="' . $this->e($labelledBy) . '">';
        foreach ($items as $child) {
            $child = (array) $child;

            // 分隔线与分组标题
            if (($child['divider'] ?? false) === true) {
                $html .= '<div class="dropdown-divider"></div>';
                continue;
            }
            if (isset($child['title']) && ! isset($child['text'])) {
                $html .= '<div class="dropdown-header fs-xs text-uppercase fw-semibold">'
                    . $this->e($child['title']) . '</div>';
                continue;
            }

            $children = (array) ($child['children'] ?? []);
            $icon     = ! empty($child['icon']) ? '<i class="' . $this->e($child['icon']) . '"></i> ' : '';
            $badge    = $this->renderBadge($child);

            if ($children !== []) {
                // 子级菜单：模板用嵌套 .dropdown 承载，CSS 已实现 hover 级联右侧展开
                $id    = $this->uid('topnav');
                $html .= '<div class="dropdown">'
                    . '<a class="dropdown-item dropdown-toggle drop-arrow-none" href="#" id="' . $id . '"'
                    . ' role="button" aria-haspopup="true" aria-expanded="false">'
                    . $icon . $this->e($child['text'] ?? '') . $badge . ' <div class="menu-arrow"></div></a>'
                    . $this->renderDropdown($children, $id)
                    . '</div>';
                continue;
            }

            $cls   = 'dropdown-item' . (! empty($child['disabled']) ? ' disabled' : '')
                . ($this->isActive($child) ? ' active' : '');
            $html .= '<a href="' . $this->e($child['url'] ?? '#!') . '" class="' . $cls . '">'
                . $icon . $this->e($child['text'] ?? '') . $badge . '</a>';
        }

        return $html . '</div>';
    }

    /** 菜单项内的 Mega 大面板 */
    protected function renderMegaPanel(array $mega, string $labelledBy): string
    {
        $cols     = (int) ($mega['cols'] ?? count((array) ($mega['columns'] ?? [])) ?: 3);
        $cols     = max(1, min(4, $cols));
        $colClass = 'col-md-' . (int) (12 / $cols);

        $html = '<div class="dropdown-menu dropdown-menu-xxl p-0" aria-labelledby="' . $this->e($labelledBy) . '">'
            . '<div class="h-100" style="max-height: 380px;" data-simplebar>';

        if (! empty($mega['title'])) {
            $html .= '<div class="row g-0"><div class="col-12">'
                . '<div class="p-3 text-center bg-light bg-opacity-50">'
                . '<h4 class="mb-0 fs-lg fw-semibold">' . $this->e($mega['title']) . '</h4>'
                . '</div></div></div>';
        }

        $html .= '<div class="row g-0">';
        foreach ((array) ($mega['columns'] ?? []) as $column) {
            $column = (array) $column;
            $html  .= '<div class="' . $colClass . '"><div class="p-3">';
            if (! empty($column['title'])) {
                $html .= '<h5 class="mb-2 fw-semibold fs-sm dropdown-header">' . $this->e($column['title']) . '</h5>';
            }
            $html .= '<ul class="list-unstyled megamenu-list">';
            foreach ((array) ($column['items'] ?? []) as $link) {
                $link  = (array) $link;
                $icon  = ! empty($link['icon']) ? '<i class="' . $this->e($link['icon']) . '"></i> ' : '';
                $html .= '<li><a href="' . $this->e($link['url'] ?? 'javascript:void(0);') . '" class="dropdown-item">'
                    . $icon . $this->e($link['text'] ?? '') . $this->renderBadge($link) . '</a></li>';
            }
            $html .= '</ul></div></div>';
        }
        $html .= '</div>';

        if (! empty($mega['footer'])) {
            $html .= '<div class="row g-0"><div class="col-12"><div class="p-2 border-top text-center">'
                . $this->raw($mega['footer']) . '</div></div></div>';
        }

        return $html . '</div></div>';
    }

    /** 顶栏左侧独立的 Mega 下拉（模板 "Boom Boom!" 入口） */
    protected function renderMega(): string
    {
        $mega = $this->get('mega');
        if (! $mega) {
            return '';
        }
        $mega = (array) $mega;
        $id   = $this->uid('topnav-mega');

        return '<div class="topbar-item d-none d-md-flex"><div class="dropdown">'
            . '<button class="topbar-link btn fw-medium btn-link dropdown-toggle drop-arrow-none" id="' . $id . '"'
            . ' data-bs-toggle="dropdown" data-bs-offset="0,16" type="button" aria-haspopup="false" aria-expanded="false">'
            . $this->e($mega['text'] ?? 'Mega Menu') . '<i class="ti ti-chevron-down ms-1"></i></button>'
            . $this->renderMegaPanel($mega, $id)
            . '</div></div>';
    }

    protected function renderBadge(array $item): string
    {
        if (empty($item['badge'])) {
            return '';
        }
        $badge = is_array($item['badge']) ? $item['badge'] : ['text' => $item['badge']];

        return ' <span class="badge ' . $this->e($badge['class'] ?? 'text-bg-danger') . ' ms-1">'
            . $this->e($badge['text'] ?? '') . '</span>';
    }

    /** 当前项是否激活：显式 active，或 url 命中 current_url */
    protected function isActive(array $item): bool
    {
        if (! empty($item['active'])) {
            return true;
        }
        $current = (string) $this->get('current_url', '');
        $url     = (string) ($item['url'] ?? '');
        if ($current === '' || $url === '' || $url === '#' || $url === '#!') {
            return false;
        }

        return trim($url, '/') === trim($current, '/');
    }

    /* ------------------------------------------------------------------ */
    /* 右侧工具区                                                          */
    /* ------------------------------------------------------------------ */

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

        $html = '<div class="topbar-item d-none d-sm-flex"><div class="dropdown">'
            . '<button class="topbar-link fw-bold" data-bs-toggle="dropdown" data-bs-offset="0,21" type="button"'
            . ' aria-haspopup="false" aria-expanded="false">';
        if (! empty($current['flag'])) {
            $html .= '<img src="' . $this->e($this->img($current['flag'])) . '" alt="language"'
                . ' class="rounded me-2" height="18" style="height:18px;" id="selected-language-image">';
        }
        $html .= '<span id="selected-language-code">' . $this->e($current['name'] ?? '') . '</span>';
        $html .= '</button><div class="dropdown-menu dropdown-menu-end">';
        foreach ($languages as $lang) {
            $lang = (array) $lang;
            $flag = ! empty($lang['flag'])
                ? '<img src="' . $this->e($this->img($lang['flag'])) . '" alt="' . $this->e($lang['name'] ?? '')
                  . '" class="me-1 rounded" height="18" style="height:18px;">'
                : '';
            $html .= '<a href="' . $this->e($lang['url'] ?? 'javascript:void(0);') . '" class="dropdown-item"'
                . (isset($lang['code']) ? ' data-lang-code="' . $this->e($lang['code']) . '"' : '')
                . ' title="' . $this->e($lang['name'] ?? '') . '">'
                . $flag . '<span class="align-middle">' . $this->e($lang['name'] ?? '') . '</span></a>';
        }

        return $html . '</div></div></div>';
    }

    protected function renderMessages(): string
    {
        $conf = $this->get('messages');
        if (! $conf) {
            return '';
        }
        $conf  = (array) $conf;
        $items = (array) ($conf['items'] ?? []);
        $count = $conf['count'] ?? count($items);

        $html = '<div class="topbar-item d-none d-sm-flex"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none position-relative" data-bs-toggle="dropdown"'
            . ' data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">'
            . '<i class="ti ti-mail fs-xxl"></i>';
        if ($count > 0) {
            $html .= '<span class="badge text-bg-success badge-circle topbar-badge position-absolute">'
                . $this->e($count) . '</span>';
        }
        $html .= '</button><div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">'
            . '<div class="px-3 py-2 border-bottom"><div class="row align-items-center"><div class="col">'
            . '<h6 class="m-0 fs-md fw-semibold">' . $this->e($conf['title'] ?? '消息') . '</h6></div>';
        if (! empty($conf['label'])) {
            $html .= '<div class="col text-end"><span class="badge badge-soft-success badge-label py-1">'
                . $this->e($conf['label']) . '</span></div>';
        }
        $html .= '</div></div><div style="max-height: 300px;" data-simplebar>';
        $html .= $this->renderFeedItems($items);
        $html .= '</div>';
        if (! empty($conf['all_url'])) {
            $html .= '<a href="' . $this->e($conf['all_url']) . '" class="dropdown-item text-center text-reset'
                . ' text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">'
                . $this->e($conf['all_text'] ?? '查看全部') . '</a>';
        }

        return $html . '</div></div></div>';
    }

    protected function renderNotifications(): string
    {
        $conf = $this->get('notifications');
        if (! $conf) {
            return '';
        }
        $conf  = (array) $conf;
        $items = (array) ($conf['items'] ?? []);
        $count = $conf['count'] ?? count($items);

        $html = '<div class="topbar-item"><div class="dropdown">'
            . '<button class="topbar-link dropdown-toggle drop-arrow-none position-relative" data-bs-toggle="dropdown"'
            . ' data-bs-offset="0,25" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">'
            . '<i class="ti ti-bell fs-xxl"></i>';
        if ($count > 0) {
            $html .= '<span class="position-absolute topbar-badge fs-xxs translate-middle badge bg-danger rounded-pill">'
                . $this->e($count) . '</span>';
        }
        $html .= '</button><div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-height: 300px;">'
            . '<div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">'
            . '<div class="row align-items-center"><div class="col">'
            . '<h6 class="m-0 fs-md fw-semibold">' . $this->e($conf['title'] ?? '通知') . '</h6>'
            . '</div></div></div><div style="max-height: 300px;" data-simplebar>';
        $html .= $this->renderFeedItems($items);
        $html .= '</div>';
        if (! empty($conf['all_url'])) {
            $html .= '<a href="' . $this->e($conf['all_url']) . '" class="dropdown-item text-center text-reset'
                . ' text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">'
                . $this->e($conf['all_text'] ?? '查看全部') . '</a>';
        }

        return $html . '</div></div></div>';
    }

    /** 消息 / 通知共用的条目渲染 */
    protected function renderFeedItems(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $item  = (array) $item;
            $media = '';
            if (! empty($item['avatar'])) {
                $media = '<img src="' . $this->e($this->img($item['avatar']))
                    . '" class="avatar-md rounded-circle" alt="avatar">';
            } elseif (! empty($item['icon'])) {
                $variant = $this->e($item['variant'] ?? 'primary');
                $media   = '<span class="avatar-md"><span class="avatar-title bg-' . $variant
                    . '-subtle text-' . $variant . ' rounded-circle"><i class="' . $this->e($item['icon'])
                    . ' fs-22"></i></span></span>';
            }
            $html .= '<div class="dropdown-item notification-item py-2 text-wrap'
                . (! empty($item['unread']) ? ' active' : '') . '">'
                . '<a href="' . $this->e($item['url'] ?? 'javascript:void(0);') . '" class="d-flex gap-2 text-reset">'
                . '<span class="flex-shrink-0">' . $media . '</span>'
                . '<span class="flex-grow-1 text-muted">'
                . (isset($item['title']) || isset($item['from'])
                    ? '<span class="fw-medium text-body">' . $this->e($item['title'] ?? $item['from']) . '</span> '
                    : '')
                . $this->e($item['text'] ?? '')
                . (isset($item['time']) ? '<br><span class="fs-xs">' . $this->e($item['time']) . '</span>' : '')
                . '</span></a></div>';
        }

        return $html;
    }

    protected function renderUser(): string
    {
        $user = $this->get('user');
        if (! $user) {
            return '';
        }
        $user   = (array) $user;
        $avatar = $this->img($user['avatar'] ?? 'users/user-2.jpg');

        $html = '<div class="topbar-item nav-user"><div class="dropdown">'
            . '<a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown"'
            . ' data-bs-offset="0,16" href="#!" aria-haspopup="false" aria-expanded="false">'
            . '<img src="' . $this->e($avatar) . '" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">'
            . '<div class="d-lg-flex align-items-center gap-1 d-none">'
            . '<h5 class="my-0">' . $this->e($user['name'] ?? '') . '</h5>'
            . '<i class="ti ti-chevron-down align-middle"></i></div></a>'
            . '<div class="dropdown-menu dropdown-menu-end">';
        // 用户信息卡：头像 / 昵称 / 角色 / 签名
        $html .= '<div class="dropdown-header noti-title bg-primary-subtle rounded-top">'
            . '<div class="d-flex align-items-center">'
            . '<img src="' . $this->e($avatar) . '" class="rounded-circle avatar-md me-2" alt="user-image">'
            . '<div class="flex-grow-1 overflow-hidden">'
            . '<h6 class="mb-0 text-truncate">' . $this->e($user['name'] ?? '') . '</h6>'
            . '<span class="fs-xs text-muted text-truncate d-block">'
            . $this->e($user['role'] ?? ($user['subtitle'] ?? '')) . '</span>'
            . '</div></div>';
        if (! empty($user['signature'])) {
            $html .= '<p class="mt-2 mb-0 fs-xs text-muted text-truncate-2">'
                . $this->e($user['signature']) . '</p>';
        }
        $html .= '</div>';
        if (! empty($user['header'])) {
            $html .= '<div class="dropdown-header noti-title">'
                . '<h6 class="text-overflow m-0">' . $this->e($user['header']) . '</h6></div>';
        }
        foreach ((array) ($user['items'] ?? []) as $item) {
            $item = (array) $item;
            if (($item['divider'] ?? false) === true) {
                $html .= '<div class="dropdown-divider"></div>';
                continue;
            }
            $icon  = ! empty($item['icon'])
                ? '<i class="' . $this->e($item['icon']) . ' me-2 fs-17 align-middle"></i>' : '';
            $html .= '<a href="' . $this->e($item['url'] ?? '#!') . '" class="dropdown-item '
                . $this->e($item['class'] ?? '') . '">'
                . $icon . '<span class="align-middle">' . $this->e($item['text'] ?? '') . '</span></a>';
        }

        return $html . '</div></div></div>';
    }
}

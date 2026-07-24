<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Navigation\Menu;

/**
 * 水平导航（horizontal 布局，配合 <html data-layout="topnav">）
 *
 * XfAdmin::topnav(['menu' => [ ...同 Menu items... ]])
 */
class TopNav extends Component
{
    protected function defaults(): array
    {
        return [
            'menu'        => [],
            'current_url' => null,
        ];
    }

    protected function html(): string
    {
        $menu = $this->get('menu');
        if ($menu instanceof Menu) {
            $inner = $menu->render();
        } elseif (is_array($menu)) {
            $inner = Menu::make([
                'mode'        => 'top',
                'items'       => $menu,
                'current_url' => $this->get('current_url'),
            ])->render();
        } else {
            $inner = $this->raw($menu);
        }

        return '<header' . $this->attrs(['class' => 'topnav']) . '>'
            . '<nav class="navbar navbar-expand-lg"><div class="container-fluid">'
            . '<div class="collapse navbar-collapse" id="topnav-menu-content">'
            . $inner
            . '</div></div></nav></header>';
    }
}

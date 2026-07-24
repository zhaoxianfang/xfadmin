<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 整页骨架（完整 HTML 文档）
 *
 * 一行代码渲染完整后台页面，自动组装：主题属性 + 侧边栏 + 顶栏 + 水平导航 +
 * 页面标题 + 内容 + 页脚 + 主题定制面板 + 全部按需资源（去重加载）。
 *
 * echo XfAdmin::page([
 *     'title'   => '仪表盘',
 *     'layout'  => 'vertical',            // vertical | horizontal | dual
 *     'theme'   => ['mode' => 'light', 'menu_color' => 'dark', 'sidenav_size' => 'default', ...],
 *     'menu'    => [ ...Menu items... ],  // 侧栏与水平导航共用，或分别传 sidenav.menu / topnav.menu
 *     'sidenav' => [...] | false,
 *     'topbar'  => [...] | false,
 *     'page_title' => ['title' => '仪表盘', 'breadcrumb' => [...]],
 *     'content' => $components,           // 字符串 / 组件 / 数组（可混排任意组件）
 *     'footer'  => [...] | false,
 *     'customizer' => true,
 * ]);
 */
class Page extends Component
{
    protected function defaults(): array
    {
        return [
            'lang'        => 'zh-CN',
            'title'       => '',
            'description' => null,
            'keywords'    => null,
            'author'      => null,
            'favicon'     => null,
            'layout'      => 'vertical',
            'theme'       => [],
            'menu'        => [],
            'current_url' => null,
            'sidenav'     => [],
            'topbar'      => [],
            'topnav'      => [],
            'page_title'  => null,
            'content'     => '',
            'container'   => 'container-fluid',
            'footer'      => [],
            'customizer'  => true,
            'head'        => null,   // <head> 附加内容
            'scripts'     => null,   // </body> 前附加内容
            'body_class'  => null,
        ];
    }

    protected function html(): string
    {
        $assets = Assets::instance();
        $layout = $this->get('layout', 'vertical');
        $theme  = array_replace((array) XfAdmin::setting('theme', []), (array) $this->get('theme', []));

        $htmlAttrs = ['lang' => $this->get('lang')];
        if (! empty($theme['skin']) && $theme['skin'] !== 'classic') {
            $htmlAttrs['data-skin'] = $theme['skin'];
        }
        if (! empty($theme['mode']) && $theme['mode'] !== 'light') {
            $htmlAttrs['data-bs-theme'] = $theme['mode'];
        }
        if (! empty($theme['layout_position']) && $theme['layout_position'] !== 'fixed') {
            $htmlAttrs['data-layout-position'] = $theme['layout_position'];
        }
        if (! empty($theme['layout_width']) && $theme['layout_width'] !== 'fluid') {
            $htmlAttrs['data-layout-width'] = $theme['layout_width'];
        }
        if (! empty($theme['topbar_color']) && $theme['topbar_color'] !== 'light') {
            $htmlAttrs['data-topbar-color'] = $theme['topbar_color'];
        }
        if (! empty($theme['menu_color']) && $theme['menu_color'] !== 'dark') {
            $htmlAttrs['data-menu-color'] = $theme['menu_color'];
        }
        if (! empty($theme['sidenav_size']) && $theme['sidenav_size'] !== 'default') {
            $htmlAttrs['data-sidenav-size'] = $theme['sidenav_size'];
        }
        if (! empty($theme['sidenav_user'])) {
            $htmlAttrs['data-sidenav-user'] = 'true';
        }
        if ($layout === 'horizontal') {
            $htmlAttrs['data-layout'] = 'topnav';
        }

        // ---------- body 部件（先渲染，确保其资源注册先于 head 输出） ----------
        $body = '<div class="wrapper">';

        // 侧边栏（horizontal 布局不渲染）
        if ($layout !== 'horizontal' && $this->get('sidenav') !== false) {
            $sidenavOpts = (array) $this->get('sidenav', []);
            if (! isset($sidenavOpts['menu']) || $sidenavOpts['menu'] === []) {
                $sidenavOpts['menu'] = $this->get('menu', []);
            }
            $sidenavOpts['current_url'] ??= $this->get('current_url');
            $body .= Sidenav::make($sidenavOpts)->render();
        }

        // 顶栏
        if ($this->get('topbar') !== false) {
            $topbarOpts = (array) $this->get('topbar', []);
            $body .= Topbar::make($topbarOpts)->render();
        }

        // 水平导航
        if ($layout === 'horizontal' || $layout === 'dual' || $this->get('topnav') === true || (is_array($this->get('topnav')) && $this->get('topnav') !== [])) {
            $topnavOpts = is_array($this->get('topnav')) ? $this->get('topnav') : [];
            if (! isset($topnavOpts['menu']) || $topnavOpts['menu'] === []) {
                $topnavOpts['menu'] = $this->get('menu', []);
            }
            $topnavOpts['current_url'] ??= $this->get('current_url');
            $body .= TopNav::make($topnavOpts)->render();
        }

        // 内容区
        $body .= '<div class="content-page"><div class="' . $this->e($this->get('container')) . '">';
        $pageTitle = $this->get('page_title');
        if ($pageTitle) {
            $body .= is_array($pageTitle) ? PageTitle::make($pageTitle)->render() : $this->raw($pageTitle);
        }
        $body .= $this->raw($this->get('content'));
        $body .= '</div>';

        // 页脚
        if ($this->get('footer') !== false) {
            $footerOpts = is_array($this->get('footer')) ? $this->get('footer') : [];
            $body .= Footer::make($footerOpts)->render();
        }
        $body .= '</div>'; // content-page
        $body .= '</div>'; // wrapper

        // 主题定制面板
        if ($this->get('customizer')) {
            $body .= Customizer::make()->render();
        }

        // ---------- 组装文档 ----------
        $favicon = $this->get('favicon') ?? XfAdmin::setting('brand.favicon') ?? $assets->url('images/favicon.ico');

        $doc  = "<!DOCTYPE html>\n<html" . Html::attrs($htmlAttrs) . ">\n<head>\n";
        $doc .= '<meta charset="utf-8">' . "\n";
        $doc .= '<title>' . $this->e($this->get('title')) . '</title>' . "\n";
        $doc .= '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        if ($this->get('description')) {
            $doc .= '<meta name="description" content="' . $this->e($this->get('description')) . '">' . "\n";
        }
        if ($this->get('keywords')) {
            $doc .= '<meta name="keywords" content="' . $this->e($this->get('keywords')) . '">' . "\n";
        }
        if ($this->get('author')) {
            $doc .= '<meta name="author" content="' . $this->e($this->get('author')) . '">' . "\n";
        }
        $doc .= '<link rel="shortcut icon" href="' . $this->e($favicon) . '">' . "\n";
        $doc .= $assets->head();
        $doc .= $this->raw($this->get('head'));
        $doc .= "</head>\n<body" . ($this->get('body_class') ? ' class="' . $this->e($this->get('body_class')) . '"' : '') . ">\n";
        $doc .= $body . "\n";
        $doc .= $assets->scripts();
        $doc .= $this->raw($this->get('scripts'));
        $doc .= "\n</body>\n</html>";

        return $doc;
    }
}

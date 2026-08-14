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
 * 一行代码渲染完整后台页面，自动组装：主题属性 + 侧边栏 + 顶栏 +
 * 页面标题 + 内容 + 页脚 + 主题定制面板 + 全部按需资源（去重加载）。
 *
 * echo XfAdmin::page([
 *     'title'   => '仪表盘',
 *     'layout'  => 'vertical',            // vertical | dual
 *     'theme'   => ['mode' => 'light', 'menu_color' => 'dark', 'sidenav_size' => 'default', ...],
 *     'menu'    => [ ...Menu items... ],  // 侧栏菜单
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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
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
            'topnav'      => null,   // 水平布局顶部导航（layout=horizontal 时启用）
            'page_title'  => null,
            'content'     => '',
            'container'   => 'container-fluid',
            'footer'      => [],
            'customizer'  => true,
            'preloader'   => false,  // 页面加载动画（true = 启用）
            'head'        => null,   // <head> 附加内容
            'scripts'     => null,   // </body> 前附加内容
            'body_class'  => null,
            'csrf'        => null,   // CSRF Token（Laravel 下自动注入 csrf_token()）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $assets = Assets::instance();
        $layout = $this->get('layout', 'vertical');

        // 主题默认值与 INSPINIA v4.1.0 模板 assets/js/config.js 的 defaultConfig 对齐。
        // 注意：模板 config.js 把「属性缺失」视为 skin=modern / menu.color=gradient，
        // 因此这些属性不能按「等于某值就省略」处理 —— 省略会被模板脚本回落成 modern/gradient，
        // 与服务端渲染的 CSS 变量不一致，导致首屏观感与配置不符。这里统一显式输出。
        $themeDefaults = [
            'skin'            => 'modern',
            'mode'            => 'light',
            'layout_position' => 'fixed',
            'layout_width'    => 'fluid',
            'topbar_color'    => 'light',
            'menu_color'      => 'gradient',
            'sidenav_size'    => 'default',
        ];
        $theme = array_replace(
            $themeDefaults,
            (array) XfAdmin::setting('theme', []),
            (array) $this->get('theme', [])
        );

        $htmlAttrs = ['lang' => $this->get('lang')];
        if (! empty($theme['skin'])) {
            $htmlAttrs['data-skin'] = $theme['skin'];
        }
        if (! empty($theme['mode'])) {
            $htmlAttrs['data-bs-theme'] = $theme['mode'];
        }
        if (! empty($theme['layout_position'])) {
            $htmlAttrs['data-layout-position'] = $theme['layout_position'];
        }
        if (! empty($theme['layout_width']) && $theme['layout_width'] !== 'fluid') {
            $htmlAttrs['data-layout-width'] = $theme['layout_width'];
        }
        if (! empty($theme['topbar_color'])) {
            $htmlAttrs['data-topbar-color'] = $theme['topbar_color'];
        }
        if (! empty($theme['menu_color'])) {
            $htmlAttrs['data-menu-color'] = $theme['menu_color'];
        }
        if (! empty($theme['sidenav_size'])) {
            $htmlAttrs['data-sidenav-size'] = $theme['sidenav_size'];
        }
        if (! empty($theme['sidenav_user'])) {
            $htmlAttrs['data-sidenav-user'] = 'true';
        }
        // 水平布局：对齐模板 layouts-horizontal.html 的 <html data-layout="topnav">
        $isHorizontal = in_array($layout, ['horizontal', 'topnav'], true)
            || ($this->get('topnav') !== null && $this->get('topnav') !== false);
        if ($isHorizontal) {
            $htmlAttrs['data-layout'] = 'topnav';
            // 水平布局不存在侧栏，移除仅对垂直布局有意义的尺寸属性
            unset($htmlAttrs['data-sidenav-size'], $htmlAttrs['data-sidenav-user']);
        }
        // ---------- body 部件（先渲染，确保其资源注册先于 head 输出） ----------
        $body = '<div class="wrapper">';

        if ($isHorizontal) {
            // TopNav 已合并「顶栏 + 水平菜单」，不再渲染 Sidenav / Topbar
            $topnavOpts = $this->get('topnav');
            $topnavOpts = is_array($topnavOpts) ? $topnavOpts : [];
            if (! isset($topnavOpts['menu']) || $topnavOpts['menu'] === []) {
                $topnavOpts['menu'] = $this->get('menu', []);
            }
            $topnavOpts['current_url'] ??= $this->get('current_url');
            $body .= TopNav::make($topnavOpts)->render();
        } else {
            // 侧边栏
            if ($this->get('sidenav') !== false) {
                $sidenavOpts = (array) $this->get('sidenav', []);
                if (! isset($sidenavOpts['menu']) || $sidenavOpts['menu'] === []) {
                    $sidenavOpts['menu'] = $this->get('menu', []);
                }
                $sidenavOpts['current_url'] ??= $this->get('current_url');
                $body .= Sidenav::make($sidenavOpts)->render();
            }
            // 顶栏（可选）
            if ($this->get('topbar') !== false) {
                $topbarOpts = (array) $this->get('topbar', []);
                $body .= Topbar::make($topbarOpts)->render();
            }
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
        // CSRF meta：显式传入 csrf 或 Laravel 环境自动注入（前端 AJAX 自动携带）
        $csrf = $this->get('csrf');
        if ($csrf === null && function_exists('csrf_token')) {
            try {
                $csrf = csrf_token();
            } catch (\Throwable) {
                $csrf = null;
            }
        }
        if (is_string($csrf) && $csrf !== '') {
            $doc .= '<meta name="csrf-token" content="' . $this->e($csrf) . '">' . "\n";
        }
        $doc .= '<link rel="shortcut icon" href="' . $this->e($favicon) . '">' . "\n";
        $doc .= $assets->head();
        $doc .= $this->raw($this->get('head'));
        $doc .= "</head>\n<body" . ($this->get('body_class') ? ' class="' . $this->e($this->get('body_class')) . '"' : '') . ">\n";

        // 页面加载动画（preloader）
        if ($this->get('preloader')) {
            $doc .= '<div id="preloader"><div id="status"><div class="spinner">'
                . '<div class="double-bounce1"></div><div class="double-bounce2"></div>'
                . '</div></div></div>';
        }
        $doc .= $body . "\n";
        $doc .= $assets->scripts();
        $doc .= $this->raw($this->get('scripts'));
        $doc .= "\n</body>\n</html>";

        // 完整文档已生成：清空资源收集状态（保留 baseUrl/version），
        // 保证同一请求内渲染多个完整页面时互不污染、不重复引用
        $assets->resetCollected();

        return $doc;
    }
}

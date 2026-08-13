<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 认证页骨架（登录 / 注册 / 找回密码 / 锁屏等）
 *
 * echo XfAdmin::authPage([
 *     'title'    => '登录',
 *     'heading'  => '欢迎回来',
 *     'subheading' => '请输入账号密码继续',
 *     'content'  => XfAdmin::form([...]),   // 卡片内内容（任意组件/HTML）
 *     'below'    => '<p>还没有账号？<a href="/register">注册</a></p>',
 *     'copyright'=> '© 2026 XX公司',
 * ]);
 */
class AuthPage extends Component
{
    protected function defaults(): array
    {
        return [
            'lang'       => 'zh-CN',
            'title'      => '',
            'theme'      => [],
            'brand'      => [],
            'layout'     => 'card',    // card | split | basic
            'heading'    => null,
            'subheading' => null,
            'content'    => '',
            'card'       => true,     // 内容是否包裹在卡片中（layout=split 时固定为卡片）
            'below'      => null,
            'copyright'  => null,
            'width'      => 'col-xxl-4 col-md-6 col-sm-8',
            'favicon'    => null,
            'head'       => null,
            'scripts'    => null,
        ];
    }

    protected function html(): string
    {
        $assets = Assets::instance();
        $theme  = array_replace((array) XfAdmin::setting('theme', []), (array) $this->get('theme', []));

        $htmlAttrs = ['lang' => $this->get('lang')];
        if (! empty($theme['skin']) && $theme['skin'] !== 'classic') {
            $htmlAttrs['data-skin'] = $theme['skin'];
        }
        if (! empty($theme['mode']) && $theme['mode'] !== 'light') {
            $htmlAttrs['data-bs-theme'] = $theme['mode'];
        }

        $brand  = (array) $this->get('brand', []) + (array) XfAdmin::setting('brand', []);
        $logo   = $brand['logo'] ?? $assets->url('images/logo.png');
        $logoDk = $brand['logo_dark'] ?? $assets->url('images/logo-black.png');
        $url    = $brand['url'] ?? $brand['home_url'] ?? '/';

        $layout = in_array($this->get('layout'), ['card', 'split', 'basic'], true) ? $this->get('layout') : 'card';

        if ($layout === 'split') {
            return $this->renderSplit($assets, $theme, $brand, $logo, $logoDk, $url, $htmlAttrs);
        }

        return $this->renderCard($assets, $logo, $logoDk, $url, $htmlAttrs);
    }

    /**
     * 居中卡片布局（默认）。
     */
    private function renderCard(Assets $assets, string $logo, string $logoDk, string $url, array $htmlAttrs): string
    {
        $body  = '<div class="auth-page-wrapper">';
        $body .= '<div class="auth-one-bg"></div>';
        $body .= '<div class="auth-one-bg-position">';
        $body .= '<div class="container">';
        $body .= '<div class="row justify-content-center">';
        $body .= '<div class="' . $this->e($this->get('width')) . '">';
        $body .= '<div class="auth-box overflow-hidden align-items-center d-flex flex-column">';

        $body .= '<div class="auth-brand text-center mb-4">'
            . '<a href="' . $this->e($url) . '" class="logo-dark"><img src="' . $this->e($logoDk) . '" alt="dark logo" height="32" style="height:32px;"></a>'
            . '<a href="' . $this->e($url) . '" class="logo-light"><img src="' . $this->e($logo) . '" alt="logo" height="32" style="height:32px;"></a>';
        if ($this->get('heading')) {
            $body .= '<h4 class="fw-bold mt-3">' . $this->e($this->get('heading')) . '</h4>';
        }
        if ($this->get('subheading')) {
            $body .= '<p class="text-muted w-lg-75 mx-auto">' . $this->e($this->get('subheading')) . '</p>';
        }
        $body .= '</div>';

        $content = $this->raw($this->get('content'));
        $body   .= $this->get('card') ? '<div class="card p-4 rounded-4 w-100">' . $content . '</div>' : $content;

        if ($this->get('below') !== null) {
            $body .= '<div class="text-center mt-3">' . $this->raw($this->get('below')) . '</div>';
        }

        $copyright = $this->get('copyright') ?? ('© ' . date('Y') . ' ' . XfAdmin::setting('brand.name', 'XfAdmin'));
        $body     .= '<p class="text-center text-muted mt-4 mb-0">' . $this->raw($copyright) . '</p>';
        $body     .= '</div></div></div></div></div></div>';

        return $this->wrap($assets, $body, $htmlAttrs);
    }

    /**
     * 左右分栏布局（左品牌大图 + 右卡片表单），复刻 inspinia auth-split-* 页面。
     */
    private function renderSplit(Assets $assets, array $theme, array $brand, string $logo, string $logoDk, string $url, array $htmlAttrs): string
    {
        $slogan   = $brand['slogan'] ?? $this->get('subheading') ?? '极速搭建专业后台';
        $features = $brand['features'] ?? [
            ['icon' => 'ti ti-bolt', 'text' => '声明式组件，几行代码完成复杂界面'],
            ['icon' => 'ti ti-device-desktop', 'text' => '离线优先，原生 JS 无构建依赖'],
            ['icon' => 'ti ti-palette', 'text' => '内置明暗主题与多种配色'],
        ];

        $body = '<div class="auth-split-wrapper">';
        $body .= '<div class="container-fluid px-0">';
        $body .= '<div class="row g-0 min-vh-100">';

        // 左侧品牌区
        $body .= '<div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center auth-split-aside p-5 text-white">';
        $body .= '<a href="' . $this->e($url) . '" class="mb-4"><img src="' . $this->e($logo) . '" alt="logo" height="34" style="height:34px;filter:brightness(0) invert(1);"></a>';
        if ($this->get('heading')) {
            $body .= '<h2 class="fw-bold mb-3">' . $this->e($this->get('heading')) . '</h2>';
        }
        $body .= '<p class="mb-4 opacity-75">' . $this->e($slogan) . '</p>';
        $body .= '<ul class="list-unstyled mb-0">';
        foreach ($features as $f) {
            $body .= '<li class="mb-3 d-flex align-items-center"><i class="' . $this->e($f['icon'] ?? 'ti ti-point-filled') . ' me-2"></i><span>' . $this->e($f['text'] ?? '') . '</span></li>';
        }
        $body .= '</ul>';
        $body .= '<p class="mt-auto mb-0 small opacity-50">© ' . date('Y') . ' ' . $this->e(XfAdmin::setting('brand.name', 'XfAdmin')) . '</p>';
        $body .= '</div>';

        // 右侧表单区
        $body .= '<div class="col-lg-6 d-flex flex-column justify-content-center auth-split-form p-4 p-md-5">';
        $body .= '<div class="auth-box w-100" style="max-width:420px;margin:auto;">';
        $body .= '<div class="auth-brand text-center mb-4 d-lg-none">'
            . '<a href="' . $this->e($url) . '" class="logo-dark"><img src="' . $this->e($logoDk) . '" alt="dark logo" height="32" style="height:32px;"></a></div>';
        if ($this->get('heading') && $this->get('layout') === 'split') {
            // split 左侧已有大标题，右侧用较小的标题
        }
        $content = $this->raw($this->get('content'));
        $body   .= '<div class="card p-4 rounded-4 w-100 border-0 shadow-sm">' . $content . '</div>';
        if ($this->get('below') !== null) {
            $body .= '<div class="text-center mt-3">' . $this->raw($this->get('below')) . '</div>';
        }
        $body .= '</div></div>';

        $body .= '</div></div></div>';

        return $this->wrap($assets, $body, $htmlAttrs);
    }

    /**
     * 包裹为完整 HTML 文档并重置资源收集状态。
     */
    private function wrap(Assets $assets, string $body, array $htmlAttrs): string
    {
        $favicon = $this->get('favicon') ?? XfAdmin::setting('brand.favicon') ?? $assets->url('images/favicon.ico');

        $doc  = "<!DOCTYPE html>\n<html" . Html::attrs($htmlAttrs) . ">\n<head>\n"
            . '<meta charset="utf-8"><title>' . $this->e($this->get('title')) . '</title>'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<link rel="shortcut icon" href="' . $this->e($favicon) . '">' . "\n"
            . $assets->head()
            . $this->raw($this->get('head'))
            . "</head>\n<body>\n" . $body . "\n"
            . $assets->scripts()
            . $this->raw($this->get('scripts'))
            . "\n</body>\n</html>";

        // 完整文档已生成：清空资源收集状态，避免同请求多文档渲染互相污染
        $assets->resetCollected();

        return $doc;
    }
}

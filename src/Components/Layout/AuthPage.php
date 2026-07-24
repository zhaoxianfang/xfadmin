<?php

declare(strict_types=1);

namespace XfAdmin\Components\Layout;

use XfAdmin\Assets\Assets;
use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

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
            'heading'    => null,
            'subheading' => null,
            'content'    => '',
            'card'       => true,     // 内容是否包裹在卡片中
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

        $body  = '<div class="auth-box overflow-hidden align-items-center d-flex">';
        $body .= '<div class="container"><div class="row justify-content-center">';
        $body .= '<div class="' . $this->e($this->get('width')) . '">';

        $body .= '<div class="auth-brand text-center mb-4">'
            . '<a href="' . $this->e($url) . '" class="logo-dark"><img src="' . $this->e($logoDk) . '" alt="dark logo" height="32"></a>'
            . '<a href="' . $this->e($url) . '" class="logo-light"><img src="' . $this->e($logo) . '" alt="logo" height="32"></a>';
        if ($this->get('heading')) {
            $body .= '<h4 class="fw-bold mt-3">' . $this->e($this->get('heading')) . '</h4>';
        }
        if ($this->get('subheading')) {
            $body .= '<p class="text-muted w-lg-75 mx-auto">' . $this->e($this->get('subheading')) . '</p>';
        }
        $body .= '</div>';

        $content = $this->raw($this->get('content'));
        $body   .= $this->get('card') ? '<div class="card p-4 rounded-4">' . $content . '</div>' : $content;

        if ($this->get('below') !== null) {
            $body .= '<div class="text-center mt-3">' . $this->raw($this->get('below')) . '</div>';
        }

        $copyright = $this->get('copyright') ?? ('© ' . date('Y') . ' ' . XfAdmin::setting('brand.name', 'XfAdmin'));
        $body     .= '<p class="text-center text-muted mt-4 mb-0">' . $this->raw($copyright) . '</p>';
        $body     .= '</div></div></div></div>';

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

        return $doc;
    }
}

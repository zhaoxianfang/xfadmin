<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 锁屏页（auth-lock-screen.html）—— 全屏锁定，输入密码解锁
 *
 * XfAdmin::lockScreen([
 *     'user'    => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg'],
 *     'action'  => '/unlock',
 *     'heading' => '已锁定',
 * ])
 */
class LockScreen extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'lang'    => 'zh-CN',
            'title'   => null,      // <title>，默认取 heading
            'theme'   => [],
            'user'    => ['name' => 'User', 'avatar' => ''],
            'action'  => '#',
            'heading' => '屏幕已锁定',
            'text'    => '请输入密码以继续',
            'brand'   => 'XfAdmin',
            'below'   => null,      // 卡片下方补充内容（如「切换账号」链接）
            'copyright' => null,
            'favicon' => null,
            'head'    => null,
            'scripts' => null,
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
        $theme  = array_replace((array) XfAdmin::setting('theme', []), (array) $this->get('theme', []));

        $htmlAttrs = ['lang' => $this->get('lang')];
        if (! empty($theme['skin']) && $theme['skin'] !== 'classic') {
            $htmlAttrs['data-skin'] = $theme['skin'];
        }
        if (! empty($theme['mode']) && $theme['mode'] !== 'light') {
            $htmlAttrs['data-bs-theme'] = $theme['mode'];
        }
        $user = (array) $this->get('user');
        $name = $this->e($user['name'] ?? 'User');
        $avatar = $user['avatar'] ?? '';
        $avatarUrl = $avatar ? XfAdmin::img($avatar) : '';

        $body = '<div class="auth-box overflow-hidden align-items-center d-flex">'
            . '<div class="container"><div class="row justify-content-center">'
            . '<div class="col-xxl-4 col-md-6 col-sm-8">'
            . '<div class="auth-brand text-center mb-4">'
            . '<a href="#" class="fs-3 fw-bold text-decoration-none">' . $this->e($this->get('brand')) . '</a>'
            . '</div>'
            . '<div class="card p-4 rounded-4 text-center">'
            . ($avatarUrl !== ''
                ? '<div class="avatar-xl mx-auto mb-3"><img src="' . $this->e($avatarUrl) . '" class="rounded-circle img-thumbnail w-100" alt="' . $name . '"></div>'
                : '')
            . '<h5 class="mb-1">' . $name . '</h5>'
            . '<p class="text-muted mb-1">' . $this->e($this->get('heading')) . '</p>'
            . '<p class="text-muted small">' . $this->e($this->get('text')) . '</p>'
            . '<form method="post" action="' . $this->e($this->get('action')) . '" class="mt-2 text-start">'
            . '<input type="hidden" name="username" value="' . $name . '">'
            . '<div class="input-group auth-pass-inputgroup">'
            . '<input type="password" name="password" class="form-control" placeholder="密码" required autofocus>'
            . '<button class="btn btn-light" type="button" data-xf="pw-toggle" tabindex="-1"><i class="ti ti-eye-off"></i></button>'
            . '<button class="btn btn-primary" type="submit"><i class="ti ti-login me-1"></i>解锁</button>'
            . '</div>'
            . '</form>'
            . '</div>';

        if ($this->get('below') !== null) {
            $body .= '<div class="text-center mt-3">' . $this->raw($this->get('below')) . '</div>';
        }
        $copyright = $this->get('copyright') ?? ('© ' . date('Y') . ' ' . XfAdmin::setting('brand.name', 'XfAdmin'));
        $body .= '<p class="text-center text-muted mt-4 mb-0">' . $this->raw($copyright) . '</p>';
        $body .= '</div></div></div></div>';

        $js = 'XFAdmin.register("pw-toggle",function(btn){btn.addEventListener("click",function(){'
            . 'var i=btn.parentElement.querySelector("input");var t=i.type==="password"?"text":"password";i.type=t;'
            . 'btn.querySelector("i").className=t==="text"?"ti ti-eye":"ti ti-eye-off";});});';
        $assets->inlineJs($js, 'xf-pw-toggle');

        $favicon = $this->get('favicon') ?? XfAdmin::setting('brand.favicon') ?? $assets->url('images/favicon.ico');
        $title   = $this->get('title') ?? $this->get('heading');

        $doc = "<!DOCTYPE html>\n<html" . Html::attrs($htmlAttrs) . ">\n<head>\n"
            . '<meta charset="utf-8"><title>' . $this->e($title) . '</title>'
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

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
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
    protected function defaults(): array
    {
        return [
            'user'    => ['name' => 'User', 'avatar' => ''],
            'action'  => '#',
            'heading' => '屏幕已锁定',
            'text'    => '请输入密码以继续',
            'brand'   => 'XfAdmin',
        ];
    }

    protected function html(): string
    {
        $id = $this->resolveId('lock');
        $user = (array) $this->get('user');
        $name = $this->e($user['name'] ?? 'User');
        $avatar = $user['avatar'] ?? '';
        $avatarUrl = $avatar ? XfAdmin::asset('images/' . ltrim($avatar, '/')) : '';

        $html = '<div class="auth-page-wrapper pt-5">'
            . '<div class="auth-one-bg-position auth-one-bg" style="background-image:url(' . $this->e(XfAdmin::asset('images/profile-bg.jpg')) . ')">'
            . '<div class="bg-overlay"></div>'
            . '<div class="container">'
            . '<div class="d-flex justify-content-center">'
            . '<a href="#" class="logo"><span>' . $this->e($this->get('brand')) . '</span></a>'
            . '</div>'
            . '<div class="row justify-content-center mt-5">'
            . '<div class="col-xl-5 col-lg-6 col-md-8">'
            . '<div class="card mt-4">'
            . '<div class="card-body p-4 text-center">'
            . '<div class="avatar-md mx-auto mb-3"><img src="' . $this->e($avatarUrl) . '" class="rounded-circle img-thumbnail" alt=""></div>'
            . '<h5 class="mb-1">' . $name . '</h5>'
            . '<p class="text-muted">' . $this->e($this->get('heading')) . '</p>'
            . '<p class="text-muted small">' . $this->e($this->get('text')) . '</p>'
            . '<form method="post" action="' . $this->e($this->get('action')) . '" class="mt-3">'
            . '<input type="hidden" name="username" value="' . $name . '">'
            . '<div class="input-group auth-pass-inputgroup">'
            . '<input type="password" name="password" class="form-control" placeholder="密码" required>'
            . '<button class="btn btn-light" type="button" data-xf="pw-toggle" tabindex="-1"><i class="ti ti-eye-off"></i></button>'
            . '<button class="btn btn-primary" type="submit"><i class="ti ti-login me-1"></i>解锁</button>'
            . '</div>'
            . '</form>'
            . '</div></div>'
            . '</div></div></div></div></div></div>';

        $js = 'XFAdmin.register("pw-toggle",function(btn){btn.addEventListener("click",function(){'
            . 'var i=btn.parentElement.querySelector("input");var t=i.type==="password"?"text":"password";i.type=t;'
            . 'btn.querySelector("i").className=t==="text"?"ti ti-eye":"ti ti-eye-off";});});';
        XfAdmin::assets()->inlineJs($js, 'xf-pw-toggle');

        return $html;
    }
}

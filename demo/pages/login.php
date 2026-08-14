<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

// 登录页（card 布局）+ 扩展插槽演示：协议、验证码、社交、自定义内容
echo XfAdmin::signIn([
    'title'      => '登录 - XfAdmin Demo',
    'heading'    => '欢迎回来',
    'subheading' => '请输入账号密码登录后台',
    'layout'     => 'card',
    'redirects'  => ['forgot' => '/forgot', 'sign_up' => '/register'],
    'captcha'    => XfAdmin::captcha(['mode' => 'image', 'src' => 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="42"><rect width="120" height="42" fill="#eef2f7"/><text x="60" y="28" font-size="22" font-family="monospace" text-anchor="middle" fill="#3b5bdb">X7K9</text></svg>')]),
    'social'     => [
        ['icon' => 'ti ti-brand-google', 'label' => 'Google', 'href' => '#', 'variant' => 'soft-danger'],
        ['icon' => 'ti ti-brand-github', 'label' => 'GitHub', 'href' => '#', 'variant' => 'soft-dark'],
    ],
    'extra' => null,
]);

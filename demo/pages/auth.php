<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

// 认证页综合演示：覆盖 9 种语义（type）× 3 种布局（layout），对应 inspinia 全部 27 个 auth-* 模板页。
// 通过 ?type=sign-up&layout=split 切换；默认展示登录 card 布局及其全部扩展插槽。

$type   = $_GET['type']   ?? 'sign-in';
$layout = $_GET['layout'] ?? 'card';

$map = [
    'sign-in'      => ['heading' => '欢迎回来', 'subheading' => '请输入账号密码登录后台'],
    'sign-up'      => ['heading' => '创建账号', 'subheading' => '填写信息注册新账号'],
    'reset-pass'   => ['heading' => '找回密码', 'subheading' => '输入注册邮箱，发送重置链接'],
    'new-pass'     => ['heading' => '设置新密码', 'subheading' => '设置您的新登录密码'],
    'two-factor'   => ['heading' => '两步验证', 'subheading' => '输入设备上的 6 位验证码'],
    'lock-screen'  => ['heading' => '锁屏', 'subheading' => '输入密码以解锁'],
    'delete-account' => ['heading' => '删除账号', 'subheading' => '此操作不可撤销'],
    'success-mail' => ['heading' => '验证成功', 'subheading' => '邮箱已验证通过'],
    'login-pin'    => ['heading' => 'PIN 登录', 'subheading' => '输入 PIN 码登录'],
];

$cfg = $map[$type] ?? $map['sign-in'];

$opts = [
    'title'      => ($cfg['heading'] ?? '') . ' - XfAdmin Demo',
    'type'       => $type,
    'layout'     => $layout,
    'heading'    => $cfg['heading'] ?? null,
    'subheading' => $cfg['subheading'] ?? null,
    'redirects'  => ['forgot' => '/auth?type=reset-pass&layout=' . $layout, 'sign_up' => '/auth?type=sign-up&layout=' . $layout, 'sign_in' => '/auth?type=sign-in&layout=' . $layout],
    'two_factor_target' => '138****6789',
    'lock_name'  => '管理员',
];

// 扩展插槽演示（仅 sign-in / sign-up 默认带，其它语义可自由加）
if ($type === 'sign-in') {
    $opts['captcha'] = XfAdmin::captcha(['mode' => 'image', 'src' => 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="42"><rect width="120" height="42" fill="#eef2f7"/><text x="60" y="28" font-size="22" font-family="monospace" text-anchor="middle" fill="#3b5bdb">X7K9</text></svg>')]);
    $opts['social']  = [
        ['icon' => 'ti ti-brand-google', 'label' => 'Google', 'href' => '#', 'variant' => 'soft-danger'],
        ['icon' => 'ti ti-brand-github', 'label' => 'GitHub', 'href' => '#', 'variant' => 'soft-dark'],
    ];
    // 演示「在表单任意位置插入自定义内容/组件」
    $opts['append'] = XfAdmin::alert([
        'variant' => 'info', 'dismissible' => false,
        'content' => '这是通过 <code>append</code> 插槽插入的自定义提示组件（任意组件均可注入）。',
    ]);
}
if ($type === 'sign-up') {
    $opts['agreements'] = [
        ['id' => 'agree_terms', 'label' => '《用户注册协议》', 'href' => '/terms', 'required' => true],
        ['id' => 'agree_privacy', 'label' => '《隐私政策》', 'href' => '/privacy', 'required' => true],
    ];
    $opts['actions'] = '<a href="/auth?type=sign-in&layout=' . $layout . '" class="btn btn-soft-light w-100">改用已有账号登录</a>';
}

echo XfAdmin::authPage($opts);

// 底部导航：快速切换所有语义与布局（演示用）
echo '<div style="position:fixed;bottom:12px;left:50%;transform:translateX(-50%);z-index:9999;background:rgba(0,0,0,.8);color:#fff;padding:8px 12px;border-radius:8px;font-size:12px;">'
    . '语义: ' . implode(' ', array_map(fn($t) => '<a style="color:#fff;margin:0 3px" href="?type=' . $t . '&layout=' . $layout . '">' . $t . '</a>', array_keys($map)))
    . ' | 布局: '
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=card">card</a>'
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=split">split</a>'
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=basic">basic</a>'
    . '</div>';

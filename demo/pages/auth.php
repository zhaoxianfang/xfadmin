<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

// 认证页综合演示：覆盖 7 种核心语义（type）× 3 种布局（layout），
// 对应 INSPINIA 后台模板中 auth-* / auth-card-* / auth-split-* 全部页面。
// 通过 ?type=sign-up&layout=split 切换；默认展示登录 split 布局及其全部扩展插槽。

$type   = $_GET['type']   ?? 'sign-in';
$layout = $_GET['layout'] ?? 'split';

$map = [
    'sign-in'      => ['heading' => '欢迎回来', 'subheading' => '请输入账号密码登录后台'],
    'sign-up'      => ['heading' => '创建账号', 'subheading' => '填写信息注册新账号'],
    'reset-pass'   => ['heading' => '找回密码', 'subheading' => '输入注册邮箱，发送重置链接'],
    'new-pass'     => ['heading' => '设置新密码', 'subheading' => '设置您的新登录密码'],
    'lock-screen'  => ['heading' => '屏幕已锁定', 'subheading' => '输入密码以解锁'],
    'login-pin'    => ['heading' => 'PIN 登录', 'subheading' => '输入 PIN 码登录'],
    'two-factor'   => ['heading' => '两步验证', 'subheading' => '输入设备上的 6 位验证码'],
    'delete-account'=> ['heading' => '注销账户', 'subheading' => '此操作不可撤销，请输入密码确认'],
    'success-mail' => ['heading' => '邮件已发送', 'subheading' => '请查收邮件以继续'],
];

$cfg = $map[$type] ?? $map['sign-in'];

$opts = [
    'title'      => ($cfg['heading'] ?? '') . ' - XfAdmin Demo',
    'type'       => $type,
    'layout'     => $layout,
    'heading'    => $cfg['heading'] ?? null,
    'subheading' => $cfg['subheading'] ?? null,
    'action'     => '/auth?type=' . $type . '&layout=' . $layout,
    'brand'      => ['name' => 'XfAdmin', 'url' => '/'],
    'sideTitle'  => '企业级后台管理平台',
    'sideText'   => 'XfAdmin 面向现代企业管理场景，提供组件化、标准化的一站式后台管理解决方案，助力企业实现数字化转型升级与规范化运营。',
    'sideList'   => [
        ['icon' => 'ti ti-check', 'text' => '150+ 标准化业务组件，覆盖主流企业管理场景'],
        ['icon' => 'ti ti-check', 'text' => '数据可视化与权限治理体系，保障业务安全合规'],
        ['icon' => 'ti ti-check', 'text' => '多框架适配、开箱即用，支持企业级快速交付'],
    ],
    'user'       => ['name' => '管理员', 'avatar' => null, 'email' => 'admin@xfadmin.cn'],
    'copyright'  => '© 2026 XfAdmin · 演示环境',
    'footerLinks'=> [
        ['url' => '/', 'text' => '返回首页'],
        ['url' => '/auth?type=sign-in&layout=' . $layout, 'text' => '登录'],
        ['url' => '/auth?type=sign-up&layout=' . $layout, 'text' => '注册'],
    ],
    'socialButtons' => [
        ['icon' => 'ti ti-brand-google', 'url' => '#', 'label' => 'Google'],
        ['icon' => 'ti ti-brand-github', 'url' => '#', 'label' => 'GitHub'],
    ],
];

// 扩展插槽演示（展示「在任意表单前后插入开发者自定义内容」的能力）
if ($type === 'sign-in') {
    // captcha: bool 渲染占位；这里用字符串原样输出 SVG 验证码组件（演示可插入任意组件）
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="42"><rect width="120" height="42" fill="#eef2f7"/><text x="60" y="28" font-size="22" font-family="monospace" text-anchor="middle" fill="#3b5bdb">X7K9</text></svg>';
    $opts['captcha'] = '<div class="d-flex align-items-center gap-2">'
        . '<input type="text" class="form-control" name="captcha" placeholder="验证码" autocomplete="off">'
        . '<img src="data:image/svg+xml;utf8,' . rawurlencode($svg) . '" alt="captcha" style="height:42px;border-radius:6px;"></div>';
    // append：表单底部插入自定义提示组件
    $opts['append'] = XfAdmin::alert([
        'variant' => 'info', 'dismissible' => false,
        'content' => '这是通过 <code>append</code> 插槽插入的自定义提示组件（任意组件均可注入）。',
    ]);
}
if ($type === 'sign-up') {
    // 演示单字段 before/after 插槽 + 表单前/后插槽
    $opts['beforeForm'] = XfAdmin::alert([
        'variant' => 'warning', 'dismissible' => false,
        'content' => '这是通过 <code>beforeForm</code> 插槽插入的说明（注册前提示）。',
    ]);
    $opts['fields'] = [
        'name' => [
            'label' => '姓名', 'name' => 'name', 'placeholder' => '请输入姓名', 'required' => true,
            'before' => '<!-- 自定义：姓名前缀 -->',
        ],
        'email' => [
            'label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入邮箱', 'required' => true,
            'after' => '<div class="form-text">我们不会公开您的邮箱</div>',
        ],
    ];
    $opts['append'] = XfAdmin::alert([
        'variant' => 'light', 'dismissible' => false,
        'content' => '注册即代表同意 <a href="#">《用户协议》</a> 与 <a href="#">《隐私政策》</a>',
    ]);
}
if ($type === 'new-pass') {
    $opts['message'] = '密码长度至少 8 位，需包含字母与数字。';
}

echo XfAdmin::authPage($opts);

// 底部导航：快速切换所有语义与布局（演示用）
echo '<div style="position:fixed;bottom:12px;left:50%;transform:translateX(-50%);z-index:9999;background:rgba(0,0,0,.8);color:#fff;padding:8px 12px;border-radius:8px;font-size:12px;">'
    . '语义: ' . implode(' ', array_map(fn($t) => '<a style="color:#fff;margin:0 3px" href="?type=' . $t . '&layout=' . $layout . '">' . $t . '</a>', array_keys($map)))
    . ' | 布局: '
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=base">base</a>'
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=card">card</a>'
    . '<a style="color:#fff;margin:0 3px" href="?type=' . $type . '&layout=split">split</a>'
    . '</div>';

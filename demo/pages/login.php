<?php

declare(strict_types=1);

use XfAdmin\XfAdmin;

echo XfAdmin::authPage([
    'title'      => '登录 - XfAdmin Demo',
    'heading'    => '欢迎回来',
    'subheading' => '请输入账号密码登录后台',
    'content'    => XfAdmin::form([
        'action'     => '/login',
        'validation' => true,
        'fields'     => [
            XfAdmin::input(['name' => 'username', 'label' => '账号', 'required' => true, 'placeholder' => '请输入账号']),
            XfAdmin::input(['name' => 'password', 'type' => 'password', 'label' => '密码', 'required' => true, 'placeholder' => '请输入密码']),
            XfAdmin::check(['name' => 'remember', 'label' => '记住我', 'checked' => true]),
        ],
        'buttons' => '<button type="submit" class="btn btn-primary w-100">登 录</button>',
    ]),
    'below' => '还没有账号？<a href="#" class="fw-semibold">立即注册</a> · <a href="/">返回首页</a>',
]);

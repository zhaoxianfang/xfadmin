<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 认证页骨架（登录 / 注册 / 找回密码 / 重置密码 / 两步验证 / 锁屏 / 删除账号 / 成功邮件 / PIN 登录）
 *
 * 支持 9 种语义类型（type）与 3 种布局（layout=card|split|basic），复刻 INSPINIA 全部 27 个 auth-* 模板页。
 *
 * 每种类型内置对应表单字段与文案，且全部字段/文案/链接/按钮均可通过 options 覆盖或扩展。
 *
 * 可扩展插槽（满足「在任意表单下新增按钮/协议/链接/验证码/自定义组件」需求）：
 *  - prepend   : 表单最顶部插入的任意内容（组件/HTML/闭包）
 *  - append    : 表单最底部（提交按钮之前）插入的任意内容
 *  - agreements: 协议勾选区（数组，每项 ['id','label','href','required']）
 *  - links     : 表单下方附加链接区（数组，每项 ['text','href']）
 *  - actions   : 提交按钮下方的额外操作按钮/链接（任意内容）
 *  - extra     : 完全自定义的整块内容（字符串/组件/闭包），若设置将覆盖默认表单
 *  - captcha   : 验证码组件（字符串或组件实例），自动插入到 append 区
 *  - social    : 社交登录按钮区（数组，每项 ['icon','label','href','variant']）
 *
 * echo XfAdmin::authPage([
 *     'type'    => 'sign-in',
 *     'layout'  => 'split',
 *     'agreements' => [['id'=>'agree','label'=>'我已阅读并同意','href'=>'/terms','required'=>true]],
 *     'captcha' => XfAdmin::captcha([...]),   // 任意验证码组件
 *     'extra'   => XfAdmin::someComponent([...]), // 插入任意组件
 * ]);
 *
 * 便捷方法：XfAdmin::signIn() / signUp() / resetPass() / newPass() / twoFactor()
 *          / lockScreen() / deleteAccount() / successMail() / loginPin()
 */
class AuthPage extends Component
{
    /** 支持的语义类型 */
    private const TYPES = [
        'sign-in', 'sign-up', 'reset-pass', 'new-pass',
        'two-factor', 'lock-screen', 'delete-account', 'success-mail', 'login-pin',
    ];

    /** 支持的布局 */
    private const LAYOUTS = ['card', 'split', 'basic'];

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
            'theme'       => [],
            'brand'       => [],
            'type'        => 'sign-in',
            'layout'      => 'card',     // card | split | basic
            'heading'     => null,
            'subheading'  => null,
            'content'     => '',         // 自定义卡片内容（与 type 表单二选一，若设置则覆盖默认表单）
            'card'        => true,
            'width'       => 'col-xxl-4 col-md-6 col-sm-8',
            'favicon'     => null,
            'head'        => null,
            'scripts'     => null,
            'copyright'   => null,

            // 可扩展插槽
            'prepend'     => null,       // 表单顶部插入
            'append'      => null,       // 提交按钮之前插入
            'agreements'  => [],         // 协议勾选
            'links'       => [],         // 附加链接
            'actions'     => null,       // 额外操作按钮/链接
            'extra'       => null,       // 完全自定义整块
            'captcha'     => null,       // 验证码组件
            'social'      => [],         // 社交登录

            // 字段级覆盖（可选）
            'fields'      => [],         // 覆盖/追加默认表单字段
            'submit'      => null,       // 提交按钮文案
            'submitVariant' => null,     // 提交按钮样式
            'formAttrs'   => [],         // 表单额外属性（action/method/id 等）
            'redirects'   => [],         // 链接覆盖（sign_in/sign_up/forgot/resend 等）
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
        $brand  = (array) $this->get('brand', []) + (array) XfAdmin::setting('brand', []);
        $logo   = $brand['logo'] ?? $assets->url('images/logo.png');
        $logoDk = $brand['logo_dark'] ?? $assets->url('images/logo-black.png');
        $url    = $brand['url'] ?? $brand['home_url'] ?? '/';

        $type   = $this->enum($this->get('type'), self::TYPES, 'sign-in');
        $layout = $this->enum($this->get('layout'), self::LAYOUTS, 'card');

        // 渲染内容（默认表单 或 自定义）
        $content = $this->resolveContent($type, $assets);
        // 兼容旧版 below 参数（表单外附加链接/HTML）
        if ($this->get('below') !== null && $this->get('below') !== '') {
            $content .= '<div class="mt-3 text-center">' . $this->raw($this->get('below')) . '</div>';
        }
        if ($layout === 'split') {
            return $this->wrap(
                $assets,
                $this->renderSplit($brand, $logo, $logoDk, $url, $htmlAttrs, $content, $type),
                $htmlAttrs
            );
        }
        if ($layout === 'basic') {
            return $this->wrap(
                $assets,
                $this->renderBasic($logo, $logoDk, $url, $htmlAttrs, $content, $type),
                $htmlAttrs
            );
        }
        return $this->wrap(
            $assets,
            $this->renderCard($logo, $logoDk, $url, $htmlAttrs, $content, $type),
            $htmlAttrs
        );
    }

    /**
     * 解析卡片内容：优先 extra（完全自定义），其次 content（自定义块），否则按 type 生成默认表单。
     */
    private function resolveContent(string $type, Assets $assets): string
    {
        if ($this->get('extra') !== null) {
            return $this->raw($this->get('extra'));
        }
        if ($this->get('content') !== null && $this->get('content') !== '') {
            return $this->raw($this->get('content'));
        }
        return $this->renderForm($type, $assets);
    }

    // ------------------------------------------------------------------
    // 默认表单（按语义类型）
    // ------------------------------------------------------------------

    /**
     * render Form（private实例方法）
     *
     * @param string $type type
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function renderForm(string $type, Assets $assets): string
    {
        $method = 'form' . str_replace('-', '', ucwords($type, '-'));
        if (method_exists($this, $method)) {
            return $this->{$method}($assets);
        }
        return $this->formSignIn($assets);
    }

    /**
     * form Sign In（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formSignIn(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $f = (array) $this->get('fields', []);

        $email = $f['email'] ?? [
            'label' => '邮箱', 'type' => 'email', 'id' => 'userEmail',
            'placeholder' => 'you@example.com', 'icon' => 'ti ti-mail', 'required' => true,
        ];
        $pass = $f['password'] ?? [
            'label' => '密码', 'type' => 'password', 'id' => 'userPassword',
            'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true,
        ];

        $html  = $this->field($email);
        $html .= $this->field($pass);

        // 记住我 + 忘记密码
        $html .= '<div class="d-flex justify-content-between align-items-center mb-3">'
            . '<div class="form-check">'
            . '<input class="form-check-input form-check-input-light fs-14" type="checkbox" id="rememberMe">'
            . '<label class="form-check-label" for="rememberMe">保持登录</label>'
            . '</div>'
            . '<a href="' . $this->e($r['forgot'] ?? 'auth-reset-pass.html') . '" class="text-decoration-underline link-offset-3 text-muted">忘记密码？</a>'
            . '</div>';

        $html .= $this->slotAgreements();
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();

        $html .= $this->submitBtn($this->get('submit') ?? '登录', $this->get('submitVariant') ?? 'primary');

        $html .= $this->slotSocial();
        $html .= $this->slotLinks([
            ['text' => '还没有账号？', 'href' => $r['sign_up'] ?? 'auth-sign-up.html', 'label' => '立即注册'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Sign Up（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formSignUp(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $f = (array) $this->get('fields', []);

        $html  = $this->field($f['name'] ?? ['label' => '姓名', 'type' => 'text', 'id' => 'userName', 'placeholder' => '张三', 'required' => true]);
        $html .= $this->field($f['email'] ?? ['label' => '邮箱', 'type' => 'email', 'id' => 'userEmail', 'placeholder' => 'you@example.com', 'icon' => 'ti ti-mail', 'required' => true]);
        $html .= $this->field(array_replace(['label' => '密码', 'type' => 'password', 'id' => 'userPassword', 'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true, 'strength' => true], $f['password'] ?? []));

        // 协议勾选（注册默认可带同意条款）
        $html .= $this->slotAgreements([
            ['id' => 'termAndPolicy', 'label' => '我已阅读并同意', 'href' => $r['terms'] ?? 'terms.html', 'required' => true],
        ]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();

        $html .= $this->submitBtn($this->get('submit') ?? '创建账号', $this->get('submitVariant') ?? 'primary');

        $html .= $this->slotSocial();
        $html .= $this->slotLinks([
            ['text' => '已有账号？', 'href' => $r['sign_in'] ?? 'auth-sign-in.html', 'label' => '去登录'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Reset Pass（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formResetPass(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $f = (array) $this->get('fields', []);

        $html  = $this->field($f['email'] ?? ['label' => '邮箱', 'type' => 'email', 'id' => 'userEmail', 'placeholder' => 'you@example.com', 'icon' => 'ti ti-mail', 'required' => true]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '发送重置链接', $this->get('submitVariant') ?? 'primary');
        $html .= $this->slotLinks([
            ['text' => '返回', 'href' => $r['sign_in'] ?? 'auth-sign-in.html', 'label' => '去登录'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form New Pass（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formNewPass(Assets $assets): string
    {
        $f = (array) $this->get('fields', []);

        $html  = $this->field(array_replace(['label' => '新密码', 'type' => 'password', 'id' => 'userPassword', 'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true, 'strength' => true], $f['password'] ?? []));
        $html .= $this->field($f['confirm'] ?? ['label' => '确认密码', 'type' => 'password', 'id' => 'userPasswordConfirm', 'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '设置新密码', $this->get('submitVariant') ?? 'primary');
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Two Factor（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formTwoFactor(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $target = $this->get('two_factor_target') ?? '******6789';

        $html  = '<div class="text-center mb-4">'
            . '<h5 class="text-muted fs-base mb-3">我们已向以下账号发送 6 位验证码</h5>'
            . '<div class="fw-bold fs-3">' . $this->e($target) . '</div></div>';

        // 6 位验证码输入（复用 TwoFactorInput 组件）
        $html .= (string) \zxf\XfAdmin\Components\Form\TwoFactorInput::make([
            'length' => 6, 'name' => 'code', 'id' => 'twoFactorCode', 'required' => true,
        ]);

        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '确认', $this->get('submitVariant') ?? 'primary');

        $html .= $this->slotLinks([
            ['text' => '没有收到验证码？', 'href' => $r['resend'] ?? '#', 'label' => '重新发送'],
            ['text' => '或 ', 'href' => $r['call'] ?? '#', 'label' => '电话联系'],
        ]);
        $html .= $this->slotLinks([
            ['text' => '返回', 'href' => $r['sign_in'] ?? 'auth-sign-in.html', 'label' => '去登录'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Lock Screen（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formLockScreen(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $avatar = $this->get('lock_avatar') ?? $this->img('users/user-2.jpg');
        $name   = $this->get('lock_name') ?? '管理员';

        $html  = '<div class="text-center mb-4">'
            . '<div class="avatar-xl mx-auto mb-3"><img src="' . $this->e($avatar) . '" class="rounded-circle img-thumbnail" alt="avatar"></div>'
            . '<h5 class="mb-1">' . $this->e($name) . '</h5>'
            . '<p class="text-muted">请输入密码以解锁</p></div>';

        $html .= $this->field(['label' => '密码', 'type' => 'password', 'id' => 'userPassword', 'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '解锁', $this->get('submitVariant') ?? 'primary');
        $html .= $this->slotLinks([
            ['text' => '不是', 'href' => $r['sign_in'] ?? 'auth-sign-in.html', 'label' => $name . '？切换账号'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Delete Account（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formDeleteAccount(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);

        $html  = '<div class="alert alert-danger border-0 text-center mb-4" role="alert">'
            . '<i class="ti ti-alert-triangle fs-2x d-block mb-2"></i>'
            . '此操作不可撤销，将永久删除您的账号及所有数据。</div>';

        $html .= $this->field(['label' => '请输入密码以确认', 'type' => 'password', 'id' => 'userPassword', 'placeholder' => '••••••••', 'icon' => 'ti ti-lock-password', 'required' => true]);
        $html .= $this->slotAgreements([
            ['id' => 'confirmDelete', 'label' => '我已了解删除后果', 'required' => true],
        ]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '删除账号', $this->get('submitVariant') ?? 'danger');
        $html .= $this->slotLinks([
            ['text' => '返回', 'href' => $r['cancel'] ?? 'auth-sign-in.html', 'label' => '取消'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Success Mail（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formSuccessMail(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);

        $html  = '<div class="text-center mb-4">'
            . '<div class="avatar-xxl mx-auto mt-2"><div class="avatar-title bg-light-subtle border border-light border-dashed rounded-circle">'
            . '<i class="ti ti-circle-check fs-2x text-success"></i></div></div></div>';
        $html .= '<h4 class="fw-bold text-center mb-4">' . $this->e($this->get('success_title') ?? '验证成功！') . '</h4>';
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '返回控制台', $this->get('submitVariant') ?? 'primary');
        $html .= $this->slotActions();
        $html .= $this->slotLinks([
            ['text' => '', 'href' => $r['home'] ?? '/', 'label' => '返回首页'],
        ]);

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    /**
     * form Login Pin（private实例方法）
     *
     * @param Assets $assets assets
     *
     * @return string result
     */
    private function formLoginPin(Assets $assets): string
    {
        $r = (array) $this->get('redirects', []);
        $avatar = $this->get('lock_avatar') ?? $this->img('users/user-2.jpg');
        $name   = $this->get('lock_name') ?? '管理员';

        $html  = '<div class="text-center mb-4">'
            . '<div class="avatar-xl mx-auto mb-3"><img src="' . $this->e($avatar) . '" class="rounded-circle img-thumbnail" alt="avatar"></div>'
            . '<h5 class="mb-1">' . $this->e($name) . '</h5>'
            . '<p class="text-muted">请输入 PIN 码登录</p></div>';

        $html .= $this->field(['label' => 'PIN 码', 'type' => 'password', 'id' => 'userPin', 'placeholder' => '••••', 'icon' => 'ti ti-key', 'required' => true]);
        $html .= $this->slotCaptcha();
        $html .= $this->slotAppend();
        $html .= $this->submitBtn($this->get('submit') ?? '登录', $this->get('submitVariant') ?? 'primary');
        $html .= $this->slotLinks([
            ['text' => '使用', 'href' => $r['sign_in'] ?? 'auth-sign-in.html', 'label' => '密码登录'],
        ]);
        $html .= $this->slotActions();

        return '<form' . $this->formAttrs() . '>' . $html . '</form>';
    }

    // ------------------------------------------------------------------
    // 字段渲染
    // ------------------------------------------------------------------

    /**
     * 渲染单个表单字段。
     * 支持：label/type/id/name/placeholder/icon/required/value/help/strength/autocomplete
     */
    private function field(array $cfg): string
    {
        $label   = $cfg['label'] ?? '';
        $type    = $cfg['type'] ?? 'text';
        $id      = $cfg['id'] ?? ('f_' . $this->uid('f'));
        $name    = $cfg['name'] ?? $id;
        $icon    = $cfg['icon'] ?? null;
        $ph      = $cfg['placeholder'] ?? '';
        $req     = ! empty($cfg['required']);
        $value   = $cfg['value'] ?? '';
        $help    = $cfg['help'] ?? null;
        $strength = ! empty($cfg['strength']);

        $html = '<div class="mb-3">';
        if ($label !== '') {
            $html .= '<label for="' . $this->e($id) . '" class="form-label">' . $this->e($label);
            if ($req) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }
        $inputClass = 'form-control' . ($strength ? ' password-input' : '');
        if ($icon) {
            $html .= '<div class="input-group">'
                . '<span class="input-group-text bg-light"><i class="' . $this->e($icon) . ' fs-xl text-muted"></i></span>'
                . '<input type="' . $this->e($type) . '" class="' . $inputClass . '" id="' . $this->e($id) . '" name="' . $this->e($name) . '" placeholder="' . $this->e($ph) . '"' . ($req ? ' required' : '') . ' value="' . $this->e($value) . '">'
                . '</div>';
        } else {
            $html .= '<input type="' . $this->e($type) . '" class="' . $inputClass . '" id="' . $this->e($id) . '" name="' . $this->e($name) . '" placeholder="' . $this->e($ph) . '"' . ($req ? ' required' : '') . ' value="' . $this->e($value) . '">';
        }
        if ($strength) {
            $html .= '<div class="password-bar my-2"></div>'
                . '<p class="text-muted fs-xs mb-0">请使用 8 位以上字母、数字与符号组合。</p>';
        }
        if ($help) {
            $html .= '<div class="form-text">' . $this->e($help) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    // ------------------------------------------------------------------
    // 可扩展插槽
    // ------------------------------------------------------------------

    /** 协议勾选区；若调用方未提供 agreements 且 $default 非空则使用默认 */
    private function slotAgreements(array $default = []): string
    {
        $items = (array) $this->get('agreements', []);
        if ($items === [] && $default !== []) {
            $items = $default;
        }
        if ($items === []) {
            return '';
        }
        $html = '';
        foreach ($items as $a) {
            $id   = $a['id'] ?? ('agree_' . $this->uid('ag'));
            $href = $a['href'] ?? null;
            $req  = ! empty($a['required']);
            $label = $a['label'] ?? '我已阅读并同意';
            $html .= '<div class="mb-3"><div class="form-check">'
                . '<input class="form-check-input form-check-input-light fs-14" type="checkbox" id="' . $this->e($id) . '"' . ($req ? ' required' : '') . '>'
                . '<label class="form-check-label" for="' . $this->e($id) . '">';
            if ($href) {
                $html .= '<a href="' . $this->e($href) . '" class="text-decoration-underline link-offset-3">' . $this->e($label) . '</a>';
            } else {
                $html .= $this->e($label);
            }
            $html .= '</label></div></div>';
        }
        return $html;
    }

    /** 验证码组件插槽（任何组件/HTML/闭包） */
    private function slotCaptcha(): string
    {
        $cap = $this->get('captcha');
        if ($cap === null) {
            return '';
        }
        return '<div class="mb-3">' . $this->raw($cap) . '</div>';
    }

    /** 表单底部（提交前）插入 */
    private function slotAppend(): string
    {
        $pre = $this->get('prepend');
        $ap  = $this->get('append');
        $out = '';
        if ($pre !== null) {
            $out .= '<div class="xf-auth-prepend mb-3">' . $this->raw($pre) . '</div>';
        }
        if ($ap !== null) {
            $out .= '<div class="xf-auth-append mb-3">' . $this->raw($ap) . '</div>';
        }
        return $out;
    }

    /** 提交按钮 */
    private function submitBtn(string $text, string $variant): string
    {
        return '<div class="d-grid"><button type="submit" class="btn btn-' . $this->e($variant) . ' fw-semibold py-2">' . $this->e($text) . '</button></div>';
    }

    /** 社交登录按钮 */
    private function slotSocial(): string
    {
        $items = (array) $this->get('social', []);
        if ($items === []) {
            return '';
        }
        $html = '<div class="text-center mt-4"><div class="row g-2 justify-content-center">';
        foreach ($items as $s) {
            $icon    = $s['icon'] ?? 'ti ti-brand-google';
            $label   = $s['label'] ?? '';
            $href    = $s['href'] ?? '#';
            $variant = $s['variant'] ?? 'soft-primary';
            $html   .= '<div class="col-auto"><a href="' . $this->e($href) . '" class="btn btn-' . $this->e($variant) . '"><i class="' . $this->e($icon) . ' me-1"></i>' . $this->e($label) . '</a></div>';
        }
        $html .= '</div></div>';

        return $html;
    }

    /** 附加链接区（每行可含多个链接） */
    private function slotLinks(array $default = []): string
    {
        $items = (array) $this->get('links', []);
        if ($items === []) {
            $items = $default;
        }
        if ($items === []) {
            return '';
        }
        $html = '';
        foreach ($items as $link) {
            $text = $link['text'] ?? '';
            $href = $link['href'] ?? '#';
            $label = $link['label'] ?? '';
            if ($text === '' && $label === '') {
                continue;
            }
            $html .= '<p class="text-muted text-center mt-3 mb-0">' . $this->e($text)
                . '<a href="' . $this->e($href) . '" class="text-decoration-underline link-offset-3 fw-semibold">' . $this->e($label) . '</a></p>';
        }
        return $html;
    }

    /** 额外操作按钮/链接区 */
    private function slotActions(): string
    {
        $a = $this->get('actions');
        if ($a === null) {
            return '';
        }
        return '<div class="xf-auth-actions text-center mt-3">' . $this->raw($a) . '</div>';
    }

    /** 表单属性（action / method / id / novalidate 等） */
    private function formAttrs(): string
    {
        $base = ['method' => 'post'];
        $attrs = array_replace($base, (array) $this->get('formAttrs', []));
        $pairs = [];
        foreach ($attrs as $k => $v) {
            $pairs[] = $this->e($k) . '="' . $this->e($v) . '"';
        }
        return ' ' . implode(' ', $pairs);
    }

    // ------------------------------------------------------------------
    // 布局渲染
    // ------------------------------------------------------------------

    /**
     * 居中卡片布局（默认）。
     */
    private function renderCard(string $logo, string $logoDk, string $url, array $htmlAttrs, string $content, string $type): string
    {
        $body  = '<div class="auth-page-wrapper">';
        $body .= '<div class="auth-one-bg"></div>';
        $body .= '<div class="auth-one-bg-position">';
        $body .= '<div class="container">';
        $body .= '<div class="row justify-content-center">';
        $body .= '<div class="' . $this->e($this->get('width')) . '">';
        $body .= '<div class="auth-box overflow-hidden align-items-center d-flex flex-column">';

        $body .= $this->brandBlock($logo, $logoDk, $url, $type);

        $body .= $this->get('card') ? '<div class="card p-4 rounded-4 w-100">' . $content . '</div>' : $content;

        $body .= $this->copyrightBlock();
        $body .= '</div></div></div></div></div></div>';

        return $body;
    }

    /**
     * 基础布局（无品牌大图，纯居中卡片）。
     */
    private function renderBasic(string $logo, string $logoDk, string $url, array $htmlAttrs, string $content, string $type): string
    {
        $body  = '<div class="auth-page-wrapper">';
        $body .= '<div class="container">';
        $body .= '<div class="row justify-content-center">';
        $body .= '<div class="' . $this->e($this->get('width')) . '">';
        $body .= '<div class="auth-box d-flex flex-column align-items-center py-4">';

        $body .= $this->brandBlock($logo, $logoDk, $url, $type, false);

        $body .= $this->get('card') ? '<div class="card p-4 rounded-4 w-100 mt-3">' . $content . '</div>' : $content;

        $body .= $this->copyrightBlock();
        $body .= '</div></div></div></div></div>';

        return $body;
    }

    /**
     * 左右分栏布局（左品牌大图 + 右卡片表单），复刻 inspinia auth-split-* 页面。
     */
    private function renderSplit(array $brand, string $logo, string $logoDk, string $url, array $htmlAttrs, string $content, string $type): string
    {
        $slogan   = $brand['slogan'] ?? $this->get('subheading') ?? '极速搭建专业后台';
        $features = $brand['features'] ?? [
            ['icon' => 'ti ti-bolt', 'text' => '声明式组件，几行代码完成复杂界面'],
            ['icon' => 'ti ti-device-desktop', 'text' => '离线优先，原生 JS 无构建依赖'],
            ['icon' => 'ti ti-palette', 'text' => '内置明暗主题与多种配色'],
        ];

        $body  = '<div class="auth-split-wrapper">';
        $body .= '<div class="container-fluid px-0">';
        $body .= '<div class="row g-0 min-vh-100">';

        // 左侧品牌区
        $body .= '<div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center auth-split-aside p-5 text-white">';
        $body .= '<a href="' . $this->e($url) . '" class="mb-4"><img src="' . $this->e($logo) . '" alt="logo" height="34" style="height:34px;filter:brightness(0) invert(1);"></a>';
        $heading = $this->get('heading') ?? ($type === 'sign-in' ? '欢迎回来' : '创建您的账号');
        $body .= '<h2 class="fw-bold mb-3">' . $this->e($heading) . '</h2>';
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

        $topHeading = $type === 'sign-in' ? '登录' : ($type === 'sign-up' ? '注册' : $this->get('heading'));
        if ($topHeading && $type !== 'sign-in') {
            $body .= '<h4 class="fw-bold mb-1">' . $this->e($topHeading) . '</h4>';
            if ($this->get('subheading')) {
                $body .= '<p class="text-muted mb-3">' . $this->e($this->get('subheading')) . '</p>';
            }
        }
        $body .= '<div class="card p-4 rounded-4 w-100 border-0 shadow-sm">' . $content . '</div>';
        $body .= '</div></div>';

        $body .= '</div></div></div>';

        return $body;
    }

    // ------------------------------------------------------------------
    // 公共块
    // ------------------------------------------------------------------

    /**
     * brand Block（private实例方法）
     *
     * @param string $logo logo
     * @param string $logoDk logo Dk
     * @param string $url url
     * @param string $type type
     * @param bool $withHeading with Heading
     *
     * @return string result
     */
    private function brandBlock(string $logo, string $logoDk, string $url, string $type, bool $withHeading = true): string
    {
        $html = '<div class="auth-brand text-center mb-4">'
            . '<a href="' . $this->e($url) . '" class="logo-dark"><img src="' . $this->e($logoDk) . '" alt="dark logo" height="32" style="height:32px;"></a>'
            . '<a href="' . $this->e($url) . '" class="logo-light"><img src="' . $this->e($logo) . '" alt="logo" height="32" style="height:32px;"></a>';
        if ($withHeading && $this->get('heading')) {
            $html .= '<h4 class="fw-bold mt-3">' . $this->e($this->get('heading')) . '</h4>';
        }
        if ($withHeading && $this->get('subheading')) {
            $html .= '<p class="text-muted w-lg-75 mx-auto">' . $this->e($this->get('subheading')) . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * copyright Block（private实例方法）
     *
     * @return string result
     */
    private function copyrightBlock(): string
    {
        $copyright = $this->get('copyright') ?? ('© ' . date('Y') . ' ' . XfAdmin::setting('brand.name', 'XfAdmin'));
        $brand = (array) $this->get('brand', []) + (array) XfAdmin::setting('brand', []);
        $author = $brand['author'] ?? 'XfAdmin';

        return '<p class="text-center text-muted mt-4 mb-0">' . $this->raw($copyright)
            . ' — by <span class="fw-bold">' . $this->e($author) . '</span></p>';
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

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

/**
 * 两步验证 / OTP 验证码输入框
 *
 * 6 格独立输入，自动跳格、退格回退、粘贴自动填充，
 * 复刻 inspinia auth-two-factor.html 的验证码交互。
 *
 * XfAdmin::twoFactorInput([
 *     'length'    => 6,
 *     'name'      => 'code',
 *     'value'     => '',
 *     'mask'      => 'name@example.com',  // 展示邮箱掩码提示
 *     'autofocus' => true,
 * ])
 */
class TwoFactorInput extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'length'    => 6,
            'name'      => 'code',
            'value'     => '',
            'mask'      => null,
            'autofocus' => true,
            'disabled'  => false,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $length   = max(4, min(8, (int) $this->get('length')));
        $name     = $this->get('name');
        $value    = (string) $this->get('value');
        $chars    = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $mask     = $this->get('mask');
        $autofocus = $this->get('autofocus') ? ' data-xf-autofocus' : '';
        $disabled = $this->get('disabled') ? ' disabled' : '';

        $html = '<div class="xf-2fa d-flex gap-2 justify-content-center"' . $autofocus . ' data-xf="twoFactor"' . $disabled . '>';
        for ($i = 0; $i < $length; $i++) {
            $ch   = $chars[$i] ?? '';
            $html .= '<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"'
                . ' class="xf-2fa-cell form-control text-center fs-3 fw-bold"'
                . ' aria-label="' . $this->e($name) . ' 第 ' . ($i + 1) . ' 位"'
                . ' data-xf-index="' . $i . '"'
                . ($i === 0 && $autofocus ? ' autofocus' : '')
                . $disabled
                . ' value="' . $this->e($ch) . '">';
        }
        $html .= '<input type="hidden" name="' . $this->e($name) . '" class="xf-2fa-value" value="' . $this->e($value) . '">';
        $html .= '</div>';

        if ($mask !== null) {
            $html .= '<p class="text-center text-muted small mt-2 mb-0">验证码已发送至 <span class="fw-semibold">' . $this->e($mask) . '</span></p>';
        }
        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 验证码组件（可插入任意表单的扩展插槽）。
 *
 * 支持多种模式：
 *  - image  : 图片验证码（服务端需提供 /captcha 生成接口，src 可覆盖）
 *  - math   : 算术题（如 3 + 7 = ?），纯前端展示，后端校验答案
 *  - slide  : 滑块占位（仅 UI，需接入第三方或自定义校验逻辑）
 *
 * echo XfAdmin::captcha([
 *     'mode' => 'image',
 *     'src'  => '/captcha?t=' . time(),   // 点击刷新
 *     'label'=> '验证码',
 *     'name' => 'captcha',
 * ]);
 *
 * 作为 AuthPage 的 captcha 插槽使用时，会被自动包裹在 .mb-3 容器中。
 */
class Captcha extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'mode'     => 'image',        // image | math | slide
            'label'    => '验证码',
            'name'     => 'captcha',
            'id'       => null,
            'src'      => null,           // image 模式图片地址（默认走 XfAdmin 通用 /captcha 约定）
            'question' => null,           // math 模式题目，默认随机生成
            'placeholder' => '请输入计算结果',
            'refreshable' => true,         // image 模式点击刷新
            'help'     => null,
            'required' => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $mode = $this->enum($this->get('mode'), ['image', 'math', 'slide'], 'image');
        $id   = $this->get('id') ?? ('captcha_' . $this->uid('cap'));
        $name = $this->get('name');
        $label = $this->get('label');
        $req  = ! empty($this->get('required'));

        $html = '<div class="mb-3">';
        if ($label !== '') {
            $html .= '<label for="' . $this->e($id) . '" class="form-label">' . $this->e($label);
            if ($req) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }
        if ($mode === 'image') {
            $src = $this->get('src') ?? (XfAdmin::setting('captcha_url') ?? $this->defaultCaptchaSvg());
            $html .= '<div class="input-group">'
                . '<input type="text" class="form-control" id="' . $this->e($id) . '" name="' . $this->e($name) . '" placeholder="请输入右侧字符" autocomplete="off"' . ($req ? ' required' : '') . '>'
                . '<button type="button" class="btn btn-light border" data-xf-captcha-refresh="' . $this->e($id) . '" title="刷新验证码">'
                . '<i class="ti ti-refresh fs-lg"></i></button></div>';
            $html .= '<div class="mt-2"><img src="' . $this->e($src) . '" class="xf-captcha-img rounded border" alt="captcha" height="42" style="cursor:pointer;height:42px;" data-xf-captcha="' . $this->e($id) . '"></div>';
            if ($this->get('refreshable')) {
                $html .= '<div class="form-text"><a href="javascript:void(0)" class="text-decoration-underline" data-xf-captcha-refresh="' . $this->e($id) . '">看不清？换一张</a></div>';
            }
        } elseif ($mode === 'math') {
            $question = $this->get('question') ?? $this->randomMath();
            $html .= '<div class="alert alert-light border mb-2 py-2"><code>' . $this->e($question) . '</code></div>';
            $html .= '<input type="text" class="form-control" id="' . $this->e($id) . '" name="' . $this->e($name) . '" placeholder="' . $this->e($this->get('placeholder')) . '" autocomplete="off"' . ($req ? ' required' : '') . '>';
        } else { // slide
            $html .= '<div class="xf-captcha-slide border rounded d-flex align-items-center px-2 py-2" data-xf-captcha-slide="' . $this->e($id) . '">'
                . '<span class="badge bg-light text-dark me-2"><i class="ti ti-arrow-right"></i></span>'
                . '<span class="text-muted small flex-grow-1">向右滑动完成验证</span></div>';
            $html .= '<input type="hidden" name="' . $this->e($name) . '" value="">';
        }
        if ($this->get('help')) {
            $html .= '<div class="form-text">' . $this->e($this->get('help')) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * random Math（private实例方法）
     *
     * @return string result
     */
    private function randomMath(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        return "{$a} + {$b} = ?";
    }

    /** 内置占位验证码图片（data URI SVG），真实项目应通过 src 覆盖为服务端生成的 /captcha */
    private function defaultCaptchaSvg(): string
    {
        $code = (string) random_int(1000, 9999);
        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="42">'
            . '<rect width="120" height="42" fill="#eef2f7"/>'
            . '<text x="60" y="28" font-size="22" font-family="monospace" font-weight="bold" text-anchor="middle" fill="#3b5bdb">' . $code . '</text></svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}

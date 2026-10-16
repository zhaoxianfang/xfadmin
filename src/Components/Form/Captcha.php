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
 *  - math   : 算术题（如 3 + 7 = ?），题目/答案写入 session（xf_captcha），后端可校验
 *  - slide  : 滑块占位（仅 UI，需接入第三方或自定义校验逻辑）
 *
 * echo XfAdmin::captcha([
 *     'mode' => 'image',
 *     'src'  => '/captcha?t=' . time(),   // 点击刷新
 *     'label'=> '验证码',
 *     'name' => 'captcha',
 * ]);
 *
 * math 模式：随机生成的题目与答案会存入 session（xf_captcha），同一题目在会话内复用；
 * 无 session 环境时退化为纯展示，校验由调用方接管。image 模式可通过 captcha_answer
 * 登记预期答案（留空表示由服务端 /captcha 接口自行校验）。
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
            'mode' => 'image',        // image | math | slide
            'label' => '验证码',
            'name' => 'captcha',
            'id' => null,
            'src' => null,           // image 模式图片地址（默认走 XfAdmin 通用 /captcha 约定）
            'question' => null,           // math 模式题目，默认随机生成
            'placeholder' => '请输入计算结果',
            'refreshable' => true,         // image 模式点击刷新
            'help' => null,
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
        // id 解析与其它字段组件保持一致：配置 > attr() > 自动生成
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-captcha');
        $name = $this->get('name');
        $label = $this->get('label');
        $req = ! empty($this->get('required'));

        $html = '<div class="mb-3">';
        if (! empty($label)) {
            $html .= '<label for="'.$this->escape($id).'" class="form-label">'.$this->escape($label);
            if ($req) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }
        if ($mode === 'image') {
            $src = $this->safeUrl($this->get('src') ?? (XfAdmin::setting('captcha_url') ?? $this->defaultCaptchaSvg()), '');
            if ($src === '') {
                // 地址为空或被 safeUrl 拦截时走内置占位图，避免输出 src="" 触发破图请求
                $src = $this->defaultCaptchaSvg();
            }
            // image 模式答案由服务端 /captcha 生成并校验；此处同步登记 session，
            // 调用方可通过 captcha_answer 传入预期答案（留空表示完全由服务端校验）
            $this->storeCaptcha($src, $this->get('captcha_answer'));
            $html .= '<div class="input-group">'
                .'<input type="text" class="form-control" id="'.$this->escape($id).'" name="'.$this->escape($name).'" placeholder="'.$this->escape($this->get('placeholder')).'" autocomplete="off"'.($req ? ' required' : '').'>'
                .'<button type="button" class="btn btn-light border" data-xf-captcha-refresh="'.$this->escape($id).'" title="刷新验证码">'
                .'<i class="ti ti-refresh fs-lg"></i></button></div>';
            $html .= '<div class="mt-2"><img src="'.$this->escape($src).'" class="xf-captcha-img rounded border" alt="captcha" height="42" style="cursor:pointer;height:42px;" data-xf-captcha="'.$this->escape($id).'"></div>';
            if ($this->get('refreshable')) {
                $html .= '<div class="form-text"><a href="javascript:void(0)" class="text-decoration-underline" data-xf-captcha-refresh="'.$this->escape($id).'">看不清？换一张</a></div>';
            }
        } elseif ($mode === 'math') {
            // 题目/答案落 session：刷新或多次渲染时复用「同一题目」，后端才能校验用户输入
            $stored = $this->captchaSession();
            $question = (string) ($this->get('question') ?: ($stored['q'] ?? ''));
            $answer = (string) ($this->get('answer') ?? '');
            if ($question === '') {
                // 无显式题目：随机生成新题
                [$question, $answer] = $this->randomMath();
            } else {
                if ($answer === '' && $question === ($stored['q'] ?? null)) {
                    // 复用的正是会话中的题目：答案取会话存储值
                    $answer = (string) ($stored['a'] ?? '');
                }
                if ($answer === '') {
                    // 显式题目：尝试从 "a + b = ?" 算式文本解析
                    $answer = $this->mathAnswer($question);
                }
            }
            $this->storeCaptcha($question, $answer);
            $html .= '<div class="alert alert-light border mb-2 py-2"><code>'.$this->escape($question).'</code></div>';
            $html .= '<input type="text" class="form-control" id="'.$this->escape($id).'" name="'.$this->escape($name).'" placeholder="'.$this->escape($this->get('placeholder')).'" autocomplete="off"'.($req ? ' required' : '').'>';
        } else { // slide
            $html .= '<div class="xf-captcha-slide border rounded d-flex align-items-center px-2 py-2" data-xf-captcha-slide="'.$this->escape($id).'">'
                .'<span class="badge bg-light text-dark me-2"><i class="ti ti-arrow-right"></i></span>'
                .'<span class="text-muted small flex-grow-1">向右滑动完成验证</span></div>';
            $html .= '<input type="hidden" name="'.$this->escape($name).'" value="">';
        }
        if ($this->get('help')) {
            $html .= '<div class="form-text">'.$this->escape($this->get('help')).'</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * 随机算术题：返回 [题目, 答案]
     *
     * @return array{0:string,1:string} result
     */
    private function randomMath(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        return ["{$a} + {$b} = ?", (string) ($a + $b)];
    }

    /**
     * 从 "3 + 7 = ?" 算式文本解析答案（非加法算式或解析失败返回空串）
     */
    private function mathAnswer(string $question): string
    {
        if (preg_match('/^\s*(\d+)\s*\+\s*(\d+)\s*=\s*\?\s*$/', $question, $m) === 1) {
            return (string) ((int) $m[1] + (int) $m[2]);
        }

        return '';
    }

    /**
     * 读取 session 中的验证码题目（无 session 环境或未生成过时返回 []）
     */
    private function captchaSession(): array
    {
        if (! function_exists('session')) {
            return [];
        }
        $data = session('xf_captcha');

        return is_array($data) ? $data : [];
    }

    /**
     * 把题目/答案写入 session，供后端校验用户输入。
     *
     * 无 session 环境（CLI / 未加载会话中间件）时静默跳过：
     * 此时退化为「纯展示验证码」，校验需由调用方自行接管。
     */
    private function storeCaptcha(mixed $question, mixed $answer): void
    {
        if (! function_exists('session')) {
            return;
        }
        session(['xf_captcha' => ['q' => (string) $question, 'a' => (string) $answer]]);
    }

    /** 内置占位验证码图片（data URI SVG），真实项目应通过 src 覆盖为服务端生成的 /captcha */
    private function defaultCaptchaSvg(): string
    {
        $code = (string) random_int(1000, 9999);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="42">'
            .'<rect width="120" height="42" fill="#eef2f7"/>'
            .'<text x="60" y="28" font-size="22" font-family="monospace" font-weight="bold" text-anchor="middle" fill="#3b5bdb">'.$code.'</text></svg>';

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }
}

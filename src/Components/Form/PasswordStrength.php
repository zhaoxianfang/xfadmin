<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 密码强度计（misc-pass-meter）
 *
 * XfAdmin::passwordStrength([
 *     'name'     => 'password',
 *     'label'    => '密码',
 *     'value'    => '',
 *     'showRules'=> true,   // 显示规则清单（长度/小写/大写/数字/符号）
 *     'minScore' => 3,      // 0-4，弱密码时禁用提交（配合表单）
 * ])
 */
class PasswordStrength extends Component
{
    use FieldWrapper;

    protected function defaults(): array
    {
        return [
            'name'      => 'password',
            'id'        => null,
            'label'     => '密码',
            'value'     => '',
            'showRules' => true,
            'minScore'  => 0,
            'hint'      => '',
        ];
    }

    protected function assets(): array
    {
        return [];
    }

    protected function html(): string
    {
        $id = $this->resolveId('pw');
        $attrs = [
            'type'        => 'password',
            'name'        => $this->get('name'),
            'id'          => $id,
            'class'       => Html::cls('form-control', $this->get('class')),
            'value'       => $this->get('value'),
            'autocomplete'=> 'new-password',
            'data-xf'     => 'pw-strength',
            'data-min'    => (int) $this->get('minScore'),
        ];
        foreach ($this->attributes as $k => $v) {
            if (! in_array($k, ['name', 'id', 'class', 'value', 'type'], true)) {
                $attrs[$k] = $v;
            }
        }

        $html = '<input' . Html::attrs($attrs) . '>';
        if ($hint = $this->get('hint')) {
            $html .= '<div class="form-text">' . $this->e($hint) . '</div>';
        }

        $html .= '<div class="mt-2">'
            . '<div class="progress" style="height:6px;"><div class="progress-bar" role="progressbar" style="width:0%"></div></div>'
            . '<div class="small mt-1" data-role="label"></div>'
            . '</div>';

        if ($this->get('showRules')) {
            $html .= '<ul class="list-unstyled small text-muted mb-0 mt-2" data-role="rules">'
                . $this->rule('length', '至少 8 个字符')
                . $this->rule('lower', '包含小写字母')
                . $this->rule('upper', '包含大写字母')
                . $this->rule('number', '包含数字')
                . $this->rule('special', '包含特殊符号')
                . '</ul>';
        }

        $js = 'XFAdmin.register("pw-strength",function(el){'
            . 'var bar=el.parentElement.querySelector(".progress-bar");'
            . 'var label=el.parentElement.querySelector("[data-role=label]");'
            . 'var min=parseInt(el.getAttribute("data-min")||"0",10);'
            . 'function score(v){var s=0,ok={length:false,lower:false,upper:false,number:false,special:false};'
            . 'ok.length=v.length>=8;ok.lower=/[a-z]/.test(v);ok.upper=/[A-Z]/.test(v);ok.number=/[0-9]/.test(v);ok.special=/[^A-Za-z0-9]/.test(v);'
            . 'var cnt=0;for(var k in ok){if(ok[k])cnt++;var li=el.parentElement.querySelector("[data-rule="+k+"]");if(li)li.classList.toggle("text-success",ok[k]);}'
            . 'var extra=0;if(v.length>=12)extra=1;if(/[0-9].*[0-9]/.test(v)||/[^A-Za-z0-9].*[^A-Za-z0-9]/.test(v))extra=1;'
            . 's=Math.min(4,cnt-1+extra);if(v==="")s=0;return {s:s,ok:ok};}'
            . 'function upd(){var r=score(el.value);var pct=[0,20,40,70,100][r.s];'
            . 'bar.style.width=pct+"%";var colors=["","bg-danger","bg-warning","bg-warning","bg-info","bg-success"];'
            . 'bar.className="progress-bar "+(colors[r.s]||"");'
            . 'var txt=["","非常弱","弱","中等","强","非常强"];label.textContent=txt[r.s]||"";'
            . 'el.dispatchEvent(new CustomEvent("xf.pw.score",{detail:{score:r.s,ok:r.ok},bubbles:true}));'
            . 'if(min>0&&el.form){var btn=el.form.querySelector("button[type=submit]");if(btn)btn.disabled=r.s<min;}}'
            . 'el.addEventListener("input",upd);upd();});';
        \zxf\XfAdmin\XfAdmin::assets()->inlineJs($js, 'xf-pw-strength');

        return $this->wrapField($html, $id);
    }

    private function rule(string $key, string $text): string
    {
        return '<li data-rule="' . $key . '"><i class="ti ti-circle-x me-1"></i>' . $this->e($text) . '</li>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 其他表单插件展示（form-other-plugin.html）
 *
 * XfAdmin::formOtherPlugin([
 *     'plugins' => ['mask', 'autosize', 'maxlength', 'touchspin'],
 * ])
 *
 * 支持输入掩码、自适应高度、最大字符数、数字微调等插件。
 */
class FormOtherPlugin extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'plugins' => ['mask', 'autosize', 'maxlength', 'touchspin'],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['inputmask'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $plugins = (array) $this->get('plugins', []);

        if (empty($plugins)) {
            return '<div class="text-center text-muted py-4">未启用任何插件</div>';
        }
        $html = '';

        // 输入掩码：统一走 data-xf="inputmask" + data-xf-config 契约（与 MaskedInput 一致）。
        // 此前在此输出 XFAdmin.register("fmMask", <字符串id>, <掩码>)——register 第二参须为
        // 初始化函数而非字符串，导致掩码完全不生效。
        if (in_array('mask', $plugins, true)) {
            $uid = 'mask_'.$this->uid();
            $masks = [
                ['id' => $uid.'_date',     'label' => '日期 (9999-99-99)', 'ph' => 'YYYY-MM-DD',      'mask' => '9999-99-99'],
                ['id' => $uid.'_phone',    'label' => '电话号码',           'ph' => '(999) 999-9999', 'mask' => '(999) 999-9999'],
                ['id' => $uid.'_currency', 'label' => '货币',               'ph' => '$ 9,999.00',     'mask' => '$ 9,999.99'],
                ['id' => $uid.'_ip',       'label' => 'IP 地址',           'ph' => '999.999.999.999', 'mask' => '999.999.999.999'],
            ];
            $cards = '';
            foreach ($masks as $m) {
                $attrs = $this->initAttrs('inputmask', ['mask' => $m['mask']]);
                $attrs['type'] = 'text';
                $attrs['class'] = 'form-control';
                $attrs['id'] = $m['id'];
                $attrs['placeholder'] = $m['ph'];
                $cards .= '<div class="col-md-6"><label class="form-label">'.$this->e($m['label']).'</label>'
                    .'<input'.Html::attrs($attrs).'></div>';
            }
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">输入掩码 Mask</h5></div><div class="card-body"><div class="row g-3">'
                .$cards.'</div></div></div>';
        }
        // 自适应高度
        if (in_array('autosize', $plugins, true)) {
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">自适应高度 Autosize</h5></div><div class="card-body">'
                .'<div class="mb-3"><label class="form-label">自动调整高度的文本域</label>'
                .'<textarea class="form-control autosize" rows="2" placeholder="输入内容，文本框自动变高..."></textarea></div></div></div>';
        }
        // 最大字符数：原生 maxlength 兜底 + data-xf="fmMaxlength" 计数器徽标
        // （此前 register 第二参传字符串 id，widget 从未被正确注册，计数器不显示）
        if (in_array('maxlength', $plugins, true)) {
            $uid2 = 'maxl_'.$this->uid();
            $ml = [
                ['id' => $uid2.'_1', 'label' => '限制 50 字符',  'max' => 50,  'tag' => 'textarea', 'always' => true,  'warn' => 'badge text-bg-warning', 'reach' => 'badge text-bg-danger'],
                ['id' => $uid2.'_2', 'label' => '限制 100 字符', 'max' => 100, 'tag' => 'input',    'always' => true,  'warn' => null,                  'reach' => null],
            ];
            $cards = '';
            foreach ($ml as $m) {
                $attrs = $this->initAttrs('fmMaxlength', array_filter([
                    'alwaysShow' => $m['always'],
                    'warningClass' => $m['warn'],
                    'limitReachedClass' => $m['reach'],
                ], fn ($v) => $v !== null));
                $attrs['class'] = 'form-control';
                $attrs['id'] = $m['id'];
                $attrs['maxlength'] = (string) $m['max'];
                if ($m['tag'] === 'textarea') {
                    $attrs['rows'] = '3';
                    $attrs['placeholder'] = '最多输入 '.$m['max'].' 个字符';
                    $cards .= '<div class="col-md-6"><label class="form-label">'.$this->e($m['label']).'</label>'
                        .'<textarea'.Html::attrs($attrs).'></textarea></div>';
                } else {
                    $attrs['type'] = 'text';
                    $attrs['placeholder'] = '最多输入 '.$m['max'].' 个字符';
                    $cards .= '<div class="col-md-6"><label class="form-label">'.$this->e($m['label']).'</label>'
                        .'<input'.Html::attrs($attrs).'></div>';
                }
            }
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">最大字符数 Maxlength</h5></div><div class="card-body"><div class="row g-3">'
                .$cards.'</div></div></div>';
        }
        // 数字微调
        if (in_array('touchspin', $plugins, true)) {
            $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">数字微调 TouchSpin</h5></div><div class="card-body"><div class="row g-3">'
                .'<div class="col-md-4"><label class="form-label">数量</label><input type="number" class="form-control touchspin" value="1" min="1" max="100"></div>'
                .'<div class="col-md-4"><label class="form-label">价格</label><input type="number" class="form-control touchspin" value="99.00" min="0" max="9999" step="0.01"></div>'
                .'<div class="col-md-4"><label class="form-label">百分比</label><input type="number" class="form-control touchspin" value="50" min="0" max="100" step="5"></div>'
                .'</div></div></div>';
        }

        return $html;
    }
}

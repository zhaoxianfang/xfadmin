<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

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

        // 输入掩码
        if (in_array('mask', $plugins, true)) {
            $uid = 'mask_' . $this->uid();
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">输入掩码 Mask</h5></div><div class="card-body"><div class="row g-3">'
                . '<div class="col-md-6"><label class="form-label">日期 (9999-99-99)</label><input type="text" class="form-control" id="' . $uid . '_date" placeholder="YYYY-MM-DD"></div>'
                . '<div class="col-md-6"><label class="form-label">电话号码</label><input type="text" class="form-control" id="' . $uid . '_phone" placeholder="(999) 999-9999"></div>'
                . '<div class="col-md-6"><label class="form-label">货币</label><input type="text" class="form-control" id="' . $uid . '_currency" placeholder="$ 9,999.00"></div>'
                . '<div class="col-md-6"><label class="form-label">IP 地址</label><input type="text" class="form-control" id="' . $uid . '_ip" placeholder="999.999.999.999"></div>'
                . '</div></div></div>';
            $html .= '<script>document.addEventListener("DOMContentLoaded",function(){'
                . 'XFAdmin.register("fmMask","' . $uid . '_date","9999-99-99");'
                . 'XFAdmin.register("fmMask","' . $uid . '_phone","(999) 999-9999");'
                . 'XFAdmin.register("fmMask","' . $uid . '_currency","$ 9,999.99");'
                . 'XFAdmin.register("fmMask","' . $uid . '_ip","999.999.999.999")});</script>';
        }
        // 自适应高度
        if (in_array('autosize', $plugins, true)) {
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">自适应高度 Autosize</h5></div><div class="card-body">'
                . '<div class="mb-3"><label class="form-label">自动调整高度的文本域</label>'
                . '<textarea class="form-control autosize" rows="2" placeholder="输入内容，文本框自动变高..."></textarea></div></div></div>';
        }
        // 最大字符数
        if (in_array('maxlength', $plugins, true)) {
            $uid2 = 'maxl_' . $this->uid();
            $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">最大字符数 Maxlength</h5></div><div class="card-body"><div class="row g-3">'
                . '<div class="col-md-6"><label class="form-label">限制 50 字符</label>'
                . '<textarea class="form-control" id="' . $uid2 . '_1" rows="3" maxlength="50" placeholder="最多输入 50 个字符"></textarea></div>'
                . '<div class="col-md-6"><label class="form-label">限制 100 字符</label>'
                . '<input type="text" class="form-control" id="' . $uid2 . '_2" maxlength="100" placeholder="最多输入 100 个字符"></div>'
                . '</div></div></div>';
            $html .= '<script>document.addEventListener("DOMContentLoaded",function(){'
                . 'XFAdmin.register("fmMaxlength","' . $uid2 . '_1",{alwaysShow:true,warningClass:"badge text-bg-warning",limitReachedClass:"badge text-bg-danger"});'
                . 'XFAdmin.register("fmMaxlength","' . $uid2 . '_2",{alwaysShow:true})});</script>';
        }
        // 数字微调
        if (in_array('touchspin', $plugins, true)) {
            $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">数字微调 TouchSpin</h5></div><div class="card-body"><div class="row g-3">'
                . '<div class="col-md-4"><label class="form-label">数量</label><input type="number" class="form-control touchspin" value="1" min="1" max="100"></div>'
                . '<div class="col-md-4"><label class="form-label">价格</label><input type="number" class="form-control touchspin" value="99.00" min="0" max="9999" step="0.01"></div>'
                . '<div class="col-md-4"><label class="form-label">百分比</label><input type="number" class="form-control touchspin" value="50" min="0" max="100" step="5"></div>'
                . '</div></div></div>';
        }
        return $html;
    }
}

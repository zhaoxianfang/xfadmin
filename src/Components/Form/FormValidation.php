<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

/**
 * 表单验证展示（form-validation.html）
 *
 * XfAdmin::formValidation([
 *     'showBuiltin' => true, // 是否展示 HTML5 原生验证
 *     'formId' => 'myForm',
 *     'fields' => [
 *         ['label' => '用户名', 'name' => 'username', 'rules' => 'required|min:2', 'type' => 'text'],
 *         ['label' => '邮箱', 'name' => 'email', 'rules' => 'required|email', 'type' => 'email'],
 *     ],
 * ])
 */
class FormValidation extends Component
{
    protected function defaults(): array
    {
        return [
            'showBuiltin' => true,
            'formId' => 'xf_form_val',
            'fields' => null,
        ];
    }

    protected function html(): string
    {
        $fields = $this->get('fields');

        return $fields !== null
            ? $this->renderCustomForm((string) $this->get('formId'), (array) $fields)
            : $this->renderBuiltin();
    }

    private function renderBuiltin(): string
    {
        $html = '';

        // HTML5 原生验证
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">HTML5 原生验证</h5></div><div class="card-body">'
            . '<form novalidate><div class="row g-3">'
            . '<div class="col-md-6"><label class="form-label">必填 *</label><input type="text" class="form-control" required><div class="invalid-feedback">此字段不能为空</div></div>'
            . '<div class="col-md-6"><label class="form-label">邮箱 *</label><input type="email" class="form-control" required placeholder="name@example.com"><div class="invalid-feedback">请输入有效的邮箱地址</div></div>'
            . '<div class="col-md-6"><label class="form-label">最小长度 (≥3)</label><input type="text" class="form-control" minlength="3"><div class="invalid-feedback">至少 3 个字符</div></div>'
            . '<div class="col-md-6"><label class="form-label">数字范围 (1-100)</label><input type="number" class="form-control" min="1" max="100"><div class="invalid-feedback">必须在 1-100 之间</div></div>'
            . '<div class="col-md-6"><label class="form-label">URL</label><input type="url" class="form-control" placeholder="https://"><div class="invalid-feedback">请输入有效的 URL</div></div>'
            . '<div class="col-md-6"><label class="form-label">正则 (仅字母数字)</label><input type="text" class="form-control" pattern="[A-Za-z0-9]+"><div class="invalid-feedback">仅允许字母和数字</div></div>'
            . '<div class="col-12"><button type="submit" class="btn btn-primary">提交验证</button></div></div></form></div></div>';

        // 校验状态样式
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">校验状态样式</h5></div><div class="card-body"><div class="row g-3">'
            . '<div class="col-md-6"><label class="form-label">成功</label><input type="text" class="form-control is-valid" value="有效值"><div class="valid-feedback">验证通过!</div></div>'
            . '<div class="col-md-6"><label class="form-label">失败</label><input type="text" class="form-control is-invalid" value="无效值"><div class="invalid-feedback">请修正此字段</div></div>'
            . '<div class="col-md-6"><label class="form-label">警告</label><div class="input-group has-validation"><input type="text" class="form-control is-invalid" value="待确认">'
            . '<div class="invalid-feedback">需要进一步确认</div></div></div>'
            . '<div class="col-md-6"><label class="form-label">选择框校验</label><select class="form-select is-invalid"><option selected>请选择...</option><option>选项 1</option></select>'
            . '<div class="invalid-feedback">请选择一个选项</div></div></div></div></div>';

        // JS 自定义验证示例表单
        $uid = 'fv_' . $this->uid();
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">自定义 JS 验证</h5></div><div class="card-body">'
            . '<form id="' . $uid . '" class="needs-validation" novalidate><div class="row g-3">'
            . '<div class="col-md-4"><label class="form-label">姓名 *</label><input type="text" class="form-control" name="name" required><div class="invalid-feedback">请输入姓名</div></div>'
            . '<div class="col-md-4"><label class="form-label">邮箱 *</label><input type="email" class="form-control" name="email" required><div class="invalid-feedback">请输入有效邮箱</div></div>'
            . '<div class="col-md-4"><label class="form-label">手机号</label><input type="text" class="form-control" name="phone" pattern="[0-9]{11}"><div class="invalid-feedback">请输入 11 位手机号</div></div>'
            . '<div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="agree" required>'
            . '<label class="form-check-label">我同意 <a href="#">服务条款</a></label><div class="invalid-feedback">请先同意条款</div></div></div>'
            . '<div class="col-12"><button type="submit" class="btn btn-primary">提交</button></div></div></form></div></div>';
        $html .= '<script>document.addEventListener("DOMContentLoaded",function(){XFAdmin.register("formValidation","' . $uid . '")});</script>';

        return $html;
    }

    private function renderCustomForm(string $formId, array $fields): string
    {
        $html = '<div class="card"><div class="card-body"><form id="' . $this->e($formId) . '" class="needs-validation" novalidate><div class="row g-3">';

        foreach ($fields as $f) {
            $f = (array) $f;
            $label = $this->e($f['label'] ?? '');
            $name = $this->e($f['name'] ?? '');
            $type = $this->e($f['type'] ?? 'text');
            $rules = (string) ($f['rules'] ?? '');
            $placeholder = $this->e($f['placeholder'] ?? '');
            $col = 'col-md-' . (int) ($f['col'] ?? 6);

            $required = str_contains($rules, 'required') ? ' required' : '';
            $email = stripos($type, 'email') !== false ? ' type="email"' : '';

            $html .= '<div class="' . $col . '"><label class="form-label">' . $label . ($required ? ' *' : '') . '</label>'
                . '<input' . $email . ' class="form-control" name="' . $name . '" placeholder="' . $placeholder . '"' . $required . '>'
                . '<div class="invalid-feedback">请填写有效的' . $label . '</div></div>';
        }

        $html .= '<div class="col-12"><button type="submit" class="btn btn-primary">提交</button></div></div></form></div></div>';
        $html .= '<script>document.addEventListener("DOMContentLoaded",function(){XFAdmin.register("formValidation","' . $this->e($formId) . '")});</script>';

        return $html;
    }
}

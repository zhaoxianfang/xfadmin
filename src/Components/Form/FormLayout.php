<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

/**
 * 表单布局变体（form-layout.html）
 *
 * XfAdmin::formLayout([
 *     'layout' => 'horizontal', // vertical|horizontal|inline|floating
 *     'columns' => 2,
 *     'fields' => [['label' => '用户名', 'type' => 'text', 'name' => 'username', 'placeholder' => '请输入']],
 * ])
 */
class FormLayout extends Component
{
    protected function defaults(): array
    {
        return [
            'layout' => 'vertical',
            'columns' => 2,
            'fields' => null,
        ];
    }

    protected function html(): string
    {
        $fields = $this->get('fields');

        return $fields !== null
            ? $this->renderCustomForm((string) $this->get('layout'), (int) $this->get('columns'), (array) $fields)
            : $this->renderBuiltin();
    }

    private function renderCustomForm(string $layout, int $columns, array $fields): string
    {
        $html = '<div class="card"><div class="card-body">';

        switch ($layout) {
            case 'inline':
                $html .= '<div class="row g-3 align-items-end">';
                foreach ($fields as $f) {
                    $f = (array) $f;
                    $html .= '<div class="col-auto"><label class="form-label">' . $this->e($f['label'] ?? '') . '</label>'
                        . $this->renderInput($f) . '</div>';
                }
                $html .= '<div class="col-auto"><button class="btn btn-primary">提交</button></div></div>';
                break;

            case 'horizontal':
                foreach ($fields as $f) {
                    $f = (array) $f;
                    $html .= '<div class="row mb-3"><label class="col-sm-3 col-form-label">' . $this->e($f['label'] ?? '') . '</label>'
                        . '<div class="col-sm-9">' . $this->renderInput($f)
                        . (! empty($f['help']) ? '<div class="form-text">' . $this->e($f['help']) . '</div>' : '')
                        . '</div></div>';
                }
                $html .= '<div class="row"><div class="col-sm-9 offset-sm-3"><button class="btn btn-primary">保存</button></div></div>';
                break;

            case 'floating':
                foreach ($fields as $f) {
                    $f = (array) $f;
                    $html .= '<div class="form-floating mb-3">' . $this->renderInput($f, true) . '<label>' . $this->e($f['label'] ?? '') . '</label></div>';
                }
                $html .= '<button class="btn btn-primary">提交</button>';
                break;

            default: // vertical
                $colClass = 'col-md-' . (12 / max(min($columns, 4), 1));
                $html .= '<div class="row g-3">';
                foreach ($fields as $f) {
                    $f = (array) $f;
                    $html .= '<div class="' . $colClass . '"><label class="form-label">' . $this->e($f['label'] ?? '') . '</label>'
                        . $this->renderInput($f)
                        . (! empty($f['help']) ? '<div class="form-text">' . $this->e($f['help']) . '</div>' : '')
                        . '</div>';
                }
                $html .= '<div class="col-12"><button class="btn btn-primary">提交</button></div></div>';
                break;
        }

        $html .= '</div></div>';

        return $html;
    }

    private function renderBuiltin(): string
    {
        $html = '';

        // 垂直表单
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">垂直表单（默认）</h5></div><div class="card-body">'
            . '<div class="row g-3"><div class="col-md-6"><label class="form-label">用户名</label><input type="text" class="form-control"></div>'
            . '<div class="col-md-6"><label class="form-label">邮箱</label><input type="email" class="form-control"></div>'
            . '<div class="col-12"><label class="form-label">备注</label><textarea class="form-control" rows="3"></textarea></div>'
            . '<div class="col-12"><button class="btn btn-primary">提交</button> <button class="btn btn-outline-secondary">取消</button></div></div></div></div>';

        // 水平表单
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">水平表单</h5></div><div class="card-body">'
            . '<div class="row mb-3"><label class="col-sm-3 col-form-label">用户名</label><div class="col-sm-9"><input type="text" class="form-control"></div></div>'
            . '<div class="row mb-3"><label class="col-sm-3 col-form-label">邮箱</label><div class="col-sm-9"><input type="email" class="form-control"></div></div>'
            . '<div class="row mb-3"><label class="col-sm-3 col-form-label">角色</label><div class="col-sm-9"><select class="form-select"><option>管理员</option><option>编辑</option></select></div></div>'
            . '<div class="row"><div class="col-sm-9 offset-sm-3"><div class="form-check mb-2"><input class="form-check-input" type="checkbox"><label class="form-check-label">我同意条款</label></div>'
            . '<button class="btn btn-primary">保存</button></div></div></div></div>';

        // 浮动标签
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">浮动标签</h5></div><div class="card-body"><div class="row g-3">'
            . '<div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" placeholder="用户名"><label>用户名</label></div></div>'
            . '<div class="col-md-6"><div class="form-floating"><input type="email" class="form-control" placeholder="name@example.com"><label>邮箱</label></div></div>'
            . '<div class="col-md-6"><div class="form-floating"><select class="form-select"><option>请选择</option><option>选项 1</option></select><label>角色</label></div></div>'
            . '<div class="col-md-6"><div class="form-floating"><textarea class="form-control" placeholder="备注" style="height:100px"></textarea><label>备注</label></div></div>'
            . '<div class="col-12"><button class="btn btn-primary">提交</button></div></div></div></div>';

        // 内联表单
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">内联表单</h5></div><div class="card-body">'
            . '<div class="row g-2 align-items-end"><div class="col-auto"><label class="form-label">用户名</label><input type="text" class="form-control" placeholder="用户名"></div>'
            . '<div class="col-auto"><label class="form-label">密码</label><input type="password" class="form-control" placeholder="密码"></div>'
            . '<div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox"><label class="form-check-label">记住我</label></div></div>'
            . '<div class="col-auto"><button class="btn btn-primary">登录</button></div></div></div></div>';

        return $html;
    }

    private function renderInput(array $field, bool $floating = false): string
    {
        $type = (string) ($field['type'] ?? 'text');
        $name = $this->e($field['name'] ?? '');
        $placeholder = $this->e($field['placeholder'] ?? '');
        $value = $this->e((string) ($field['value'] ?? ''));
        $required = ! empty($field['required']) ? ' required' : '';
        $disabled = ! empty($field['disabled']) ? ' disabled' : '';

        if ($type === 'select') {
            $options = (array) ($field['options'] ?? []);
            $html = '<select class="form-select" name="' . $name . '"' . $required . $disabled . '>';
            if (! $floating) {
                $html .= '<option value="">请选择...</option>';
            }
            foreach ($options as $k => $v) {
                $selected = ((string) $k === $value) ? ' selected' : '';
                $html .= '<option value="' . $this->e((string) $k) . '"' . $selected . '>' . $this->e((string) $v) . '</option>';
            }
            $html .= '</select>';

            return $html;
        }

        if ($type === 'textarea') {
            return '<textarea class="form-control" name="' . $name . '" rows="3" placeholder="' . $placeholder . '"' . $required . $disabled . '>' . $value . '</textarea>';
        }

        $cls = 'form-control';
        // 浮动标签模式：预留扩展点，当前由父级 .form-floating 容器处理
        if ($floating) {
            $cls .= ' form-floating-input';
        }

        return '<input type="' . $this->e($type) . '" class="' . $cls . '" name="' . $name . '" value="' . $value . '" placeholder="' . $placeholder . '"' . $required . $disabled . '>';
    }
}

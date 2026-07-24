<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 表单容器（支持浏览器原生校验样式 / AJAX 提交 / 行内布局）
 *
 * XfAdmin::form([
 *     'action'  => '/users',
 *     'method'  => 'POST',
 *     'validation' => true,      // Bootstrap 客户端校验（needs-validation）
 *     'ajax'    => true,         // xfadmin.js 接管提交，触发 xf.form.success/error 事件
 *     'fields'  => [ Input、Select、Check ... 组件或 HTML 的数组 ],
 *     'buttons' => '<button class="btn btn-primary" type="submit">提交</button>',
 *     'csrf'    => ['_token' => 'xxx'],   // 附加隐藏域
 * ])
 */
class Form extends Component
{
    protected function defaults(): array
    {
        return [
            'action'     => '',
            'method'     => 'POST',
            'enctype'    => null,
            'validation' => false,
            'ajax'       => false,
            'inline'     => false,
            'fields'     => [],
            'content'    => null,
            'buttons'    => null,
            'csrf'       => [],
        ];
    }

    protected function html(): string
    {
        $method     = strtoupper((string) $this->get('method', 'POST'));
        $formMethod = in_array($method, ['GET', 'POST'], true) ? $method : 'POST';

        $attrs = [
            'action'  => $this->get('action'),
            'method'  => $formMethod,
            'class'   => Html::cls([
                'needs-validation'   => $this->get('validation'),
                'row row-cols-lg-auto g-3 align-items-center' => $this->get('inline'),
            ]),
            'novalidate' => (bool) $this->get('validation'),
            'enctype' => $this->get('enctype'),
        ];
        if ($this->get('ajax')) {
            $attrs['data-xf'] = 'form';
        }

        $html = '<form' . $this->attrs($attrs) . '>';

        // 隐藏域（CSRF / 方法伪装）
        foreach ((array) $this->get('csrf', []) as $name => $value) {
            $html .= '<input type="hidden" name="' . $this->e($name) . '" value="' . $this->e($value) . '">';
        }
        if (! in_array($method, ['GET', 'POST'], true)) {
            $html .= '<input type="hidden" name="_method" value="' . $this->e($method) . '">';
        }

        foreach ((array) $this->get('fields', []) as $field) {
            $html .= $this->raw($field);
        }
        $html .= $this->raw($this->get('content'));

        if ($this->get('buttons') !== null) {
            $html .= '<div class="d-flex gap-2">' . $this->raw($this->get('buttons')) . '</div>';
        }

        return $html . '</form>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 表单容器（支持浏览器原生校验样式 / AJAX 提交 / 行内布局）
 *
 * XfAdmin::form([
 *     'action'  => '/users',
 *     'method'  => 'POST',
 *     'validation' => true,      // Bootstrap 客户端校验（needs-validation）
 *     'ajax'    => true,         // xfadmin.js 接管提交，触发 xf.form.success/error 事件
 *     'layout'  => 'vertical',   // 布局：vertical 纵向（默认）| horizontal 标签左置 | inline 行内
 *     'label_width' => 180,      // horizontal 布局的标签列宽（px）
 *     'fields'  => [ Input、Select、Check ... 组件或 HTML 的数组 ],
 *     'buttons' => '<button class="btn btn-primary" type="submit">提交</button>',
 *     'csrf'    => true,   // true（默认，自动注入 _token）/ false（不注入）/ [name=>value]（自定义隐藏域）
 * ])
 */
class Form extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'action'      => '',
            'method'      => 'POST',
            'enctype'     => null,
            'validation'  => false,
            'ajax'        => false,
            'remote'      => false,    // true：表单带 data-xf-remote，由前端全局托管 AJAX 提交 + 接收处理（与登录页一致）
            'inline'      => false,     // 兼容旧写法（等价 layout=inline）
            'layout'      => null,      // vertical | horizontal | inline（form-layouts.html）
            'label_width' => 180,       // horizontal 布局标签列宽（px）
            'fields'      => [],
            'content'     => null,
            'buttons'     => null,
            'csrf'        => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $method     = strtoupper((string) $this->get('method', 'POST'));
        $formMethod = in_array($method, ['GET', 'POST'], true) ? $method : 'POST';

        // 布局解析：layout 优先，兼容旧的 inline=true 写法
        $layout = (string) ($this->get('layout') ?? ($this->get('inline') ? 'inline' : 'vertical'));

        $attrs = [
            'action'  => $this->get('action'),
            'method'  => $formMethod,
            'class'   => Html::cls([
                'needs-validation'   => $this->get('validation'),
                'row row-cols-lg-auto g-3 align-items-center' => $layout === 'inline',
                // horizontal：标签左置两栏排版（CSS Grid 实现，见 xfadmin.css .xf-form-horizontal）
                'xf-form-horizontal' => $layout === 'horizontal',
            ]),
            'novalidate' => (bool) $this->get('validation'),
            'enctype' => $this->get('enctype'),
        ];
        if ($layout === 'horizontal') {
            $attrs['style'] = '--xf-label-width:' . (int) $this->get('label_width', 180) . 'px';
        }
        if ($this->get('ajax') || $this->get('remote')) {
            // 远程表单：交由前端 XFAdmin.bindRemoteForms 全局托管（拦截提交 -> AJAX -> 成功刷新/关闭、失败回填）
            $attrs['data-xf-remote'] = '';
        }
        $html = '<form' . $this->attrs($attrs) . '>';

        // 隐藏域（CSRF / 方法伪装）
        $csrf = $this->get('csrf', true);
        if ($csrf === true) {
            // 自动注入框架 CSRF 令牌（宿主框架通过 XfAdmin::setCsrfResolver 注册）
            $html .= '<input type="hidden" name="_token" value="' . $this->e(XfAdmin::csrfToken()) . '">';
        } elseif (is_array($csrf) && $csrf !== []) {
            foreach ($csrf as $name => $value) {
                $html .= '<input type="hidden" name="' . $this->e($name) . '" value="' . $this->e($value) . '">';
            }
        }
        // csrf === false 时不输出任何令牌隐藏域
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

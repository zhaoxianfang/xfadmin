<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 轻提示（Toast）
 *
 * XfAdmin::toast(['title' => '通知', 'body' => '保存成功', 'variant' => 'success', 'autohide' => true, 'show' => true])
 * 前端也可调用 XFAdmin.toast({title, body, variant}) 动态弹出
 */
class Toast extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title' => null,
            'body' => '',
            'time' => null,
            'variant' => null,
            'autohide' => true,
            'delay' => 5000,
            'show' => true,
            'placement' => null,   // 如 'top-0 end-0'，非空时包裹固定容器
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $variant = $this->get('variant') !== null ? $this->escape($this->get('variant')) : null;

        $attrs = [
            'class' => Html::cls('toast', ['show' => $this->get('show')], $variant ? 'text-bg-'.$this->escape($variant).' border-0' : ''),
            'role' => 'alert',
            'aria-live' => 'assertive',
            'aria-atomic' => 'true',
            'data-bs-autohide' => $this->get('autohide') ? 'true' : 'false',
            'data-bs-delay' => (string) $this->get('delay'),
            // 注册扫描入口：xfadmin.js 的 toast widget 据此初始化静态服务端 toast
            // （Bootstrap 5 toast 不自动初始化，关闭按钮与自动消失均依赖 JS 接管）
            'data-xf' => 'toast',
        ];

        $html = '<div'.$this->attrs($attrs).'>';
        if ($this->get('title') !== null) {
            $html .= '<div class="toast-header">'
                .'<strong class="me-auto">'.$this->escape($this->get('title')).'</strong>'
                .($this->get('time') !== null ? '<small class="text-muted">'.$this->escape($this->get('time')).'</small>' : '')
                .'<button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            $html .= '<div class="toast-body">'.$this->raw($this->get('body')).'</div>';
        } else {
            $html .= '<div class="d-flex"><div class="toast-body">'.$this->raw($this->get('body')).'</div>'
                .'<button type="button" class="btn-close '.($variant ? 'btn-close-white ' : '').'me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        }
        $html .= '</div>';

        if ($this->get('placement')) {
            $html = '<div class="toast-container position-fixed '.$this->escape($this->get('placement')).' p-3">'.$html.'</div>';
        }

        return $html;
    }
}

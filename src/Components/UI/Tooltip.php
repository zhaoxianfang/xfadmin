<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 文字提示（Bootstrap Tooltip）
 *
 * XfAdmin::tooltip([
 *     'text'      => '按钮文字',           // 触发元素内容（HTML）
 *     'title'     => '这是提示内容',
 *     'placement' => 'top',               // top|bottom|left|right
 *     'tag'       => 'button',            // 触发元素标签
 *     'class'     => 'btn btn-primary',
 *     'html'      => false,               // title 是否允许 HTML
 *     'trigger'   => null,               // hover focus click
 * ])
 *
 * 前端由 xfadmin.js 统一初始化 [data-bs-toggle="tooltip"]，无需重复实例化。
 */
class Tooltip extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text'      => '',
            'title'     => '',
            'placement' => 'top',
            'tag'       => 'button',
            'class'     => 'btn btn-secondary',
            'html'      => false,
            'trigger'   => null,
            'custom_class' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $tag  = preg_replace('/[^a-z0-9]/i', '', (string) $this->get('tag')) ?: 'button';
        $attrs = [
            'class'            => Html::cls($this->get('class')),
            'data-bs-toggle'   => 'tooltip',
            'data-bs-placement'=> $this->e($this->get('placement')),
            'title'            => $this->e($this->get('title')),
        ];
        if ($this->get('html')) {
            $attrs['data-bs-html'] = 'true';
        }
        if ($this->get('trigger')) {
            $attrs['data-bs-trigger'] = $this->e($this->get('trigger'));
        }
        if ($this->get('custom_class')) {
            $attrs['data-bs-custom-class'] = $this->e($this->get('custom_class'));
        }
        if ($tag === 'button') {
            $attrs['type'] = 'button';
        }

        return '<' . $tag . $this->attrs($attrs) . '>' . $this->raw($this->get('text')) . '</' . $tag . '>';
    }
}

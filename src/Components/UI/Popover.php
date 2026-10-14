<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 弹出框（Bootstrap Popover）
 *
 * XfAdmin::popover([
 *     'text'      => '点击我',
 *     'title'     => '弹框标题',
 *     'content'   => '弹框正文内容',
 *     'placement' => 'right',
 *     'trigger'   => 'click',   // click|hover|focus|manual
 *     'tag'       => 'button',
 *     'class'     => 'btn btn-primary',
 *     'html'      => false,
 *     'dismiss'   => false,     // true 时点击外部关闭
 * ])
 */
class Popover extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text' => '',
            'title' => null,
            'content' => '',
            'placement' => 'top',
            'trigger' => 'click',
            'tag' => 'button',
            'class' => 'btn btn-secondary',
            'html' => false,
            'dismiss' => false,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $tag = preg_replace('/[^a-z0-9]/i', '', (string) $this->get('tag')) ?: 'button';
        $attrs = [
            'class' => Html::cls($this->get('class')),
            'data-bs-toggle' => 'popover',
            // placement / trigger 白名单：非法值回退默认，防止任意属性值注入
            'data-bs-placement' => $this->enum($this->get('placement'), self::ENUM_PLACEMENT, 'top'),
            'data-bs-trigger' => $this->get('dismiss') ? 'focus' : $this->enum($this->get('trigger'), ['click', 'hover', 'focus', 'manual'], 'click'),
            'data-bs-content' => $this->e($this->get('content')),
        ];
        if ($this->get('title') !== null) {
            $attrs['title'] = $this->e($this->get('title'));
        }
        if ($this->get('html')) {
            $attrs['data-bs-html'] = 'true';
        }
        if ($tag === 'button') {
            $attrs['type'] = 'button';
        } elseif ($tag === 'a') {
            $attrs['tabindex'] = '0';
            $attrs['role'] = 'button';
        }

        // 触发文案走 text()：字符串转义，同时保留组件实例透传
        return '<'.$tag.$this->attrs($attrs).'>'.$this->text($this->get('text')).'</'.$tag.'>';
    }
}

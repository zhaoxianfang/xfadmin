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
 *     'trigger'   => 'click',   // click|hover|focus
 *     'tag'       => 'button',
 *     'class'     => 'btn btn-primary',
 *     'html'      => false,
 *     'dismiss'   => false,     // true 时点击外部关闭
 * ])
 */
class Popover extends Component
{
    protected function defaults(): array
    {
        return [
            'text'      => '',
            'title'     => null,
            'content'   => '',
            'placement' => 'top',
            'trigger'   => 'click',
            'tag'       => 'button',
            'class'     => 'btn btn-secondary',
            'html'      => false,
            'dismiss'   => false,
        ];
    }

    protected function html(): string
    {
        $tag  = preg_replace('/[^a-z0-9]/i', '', (string) $this->get('tag')) ?: 'button';
        $attrs = [
            'class'             => Html::cls($this->get('class')),
            'data-bs-toggle'    => 'popover',
            'data-bs-placement' => $this->e($this->get('placement')),
            'data-bs-trigger'   => $this->get('dismiss') ? 'focus' : $this->e($this->get('trigger')),
            'data-bs-content'   => $this->e($this->get('content')),
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
            $attrs['role']     = 'button';
        }

        return '<' . $tag . $this->attrs($attrs) . '>' . $this->raw($this->get('text')) . '</' . $tag . '>';
    }
}

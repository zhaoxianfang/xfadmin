<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 标签/胶囊（Chip / Tag）—— 可带头像、图标、关闭按钮
 *
 * XfAdmin::chip([
 *     'label'    => '张三',
 *     'avatar'   => 'users/avatar-1.jpg',
 *     'icon'     => null,
 *     'variant'  => 'light',
 *     'dismissible' => true,
 *     'href'     => null,
 * ])
 */
class Chip extends Component
{
    protected function defaults(): array
    {
        return [
            'label'       => '',
            'avatar'      => null,
            'icon'        => null,
            'variant'     => 'light',
            'dismissible' => false,
            'href'        => null,
        ];
    }

    protected function html(): string
    {
        $tag   = $this->get('href') ? 'a' : 'span';
        $attrs = ['class' => Html::cls('badge bg-' . $this->e($this->get('variant')) . ' text-body d-inline-flex align-items-center gap-1 p-1 pe-2 rounded-pill')];
        if ($this->get('href')) {
            $attrs['href'] = $this->get('href');
        }

        $html = '<' . $tag . $this->attrs($attrs) . '>';
        if ($this->get('avatar')) {
            $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::asset('images/' . ltrim((string) $this->get('avatar'), '/'))) . '" class="rounded-circle" width="20" height="20" alt="">';
        } elseif ($this->get('icon')) {
            $html .= '<i class="' . $this->e($this->get('icon')) . '"></i>';
        }
        $html .= '<span>' . $this->e($this->get('label')) . '</span>';
        if ($this->get('dismissible')) {
            $html .= '<button type="button" class="btn-close btn-close-sm ms-1" data-xf="chip-close" aria-label="关闭"></button>';
        }

        return $html . '</' . $tag . '>';
    }
}

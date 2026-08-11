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
        // 键名容错：label / text 等价；href / url 等价
        if (($this->get('label') === '' || $this->get('label') === null) && $this->get('text') !== null) {
            $this->options['label'] = $this->get('text');
        }
        if ($this->get('href') === null && $this->get('url') !== null) {
            $this->options['href'] = $this->get('url');
        }

        $tag   = $this->get('href') ? 'a' : 'span';
        $attrs = ['class' => Html::cls('badge bg-' . $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary') . ' text-body d-inline-flex align-items-center gap-1 p-1 pe-2 rounded-pill')];
        if ($this->get('href')) {
            $attrs['href'] = $this->get('href');
        }

        $html = '<' . $tag . $this->attrs($attrs) . '>';
        if ($this->get('avatar')) {
            $html .= '<span class="avatar avatar-xs flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $this->get('avatar'))) . '" class="img-fluid rounded-circle" alt=""></span>';
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

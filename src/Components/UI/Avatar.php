<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 头像（图片 / 文字缩写 / 图标 / 分组堆叠）
 *
 * XfAdmin::avatar(['src' => '/a.jpg', 'size' => 'md', 'rounded' => 'circle'])
 * XfAdmin::avatar(['text' => 'ZS', 'variant' => 'primary', 'size' => 'lg'])
 * XfAdmin::avatar(['group' => [['src' => '/a.jpg'], ['text' => '+5', 'variant' => 'info']]])
 */
class Avatar extends Component
{
    protected function defaults(): array
    {
        return [
            'src'     => null,
            'text'    => null,
            'icon'    => null,
            'alt'     => '',
            'size'    => 'md',       // xs | sm | md | lg | xl | xxl
            'rounded' => 'circle',   // circle | 0 | 1 | 2 | 3
            'variant' => 'primary',
            'soft'    => true,
            'group'   => [],
        ];
    }

    protected function one(array $opts): string
    {
        $size    = 'avatar-' . ($opts['size'] ?? 'md');
        $rounded = ($opts['rounded'] ?? 'circle') === 'circle' ? 'rounded-circle' : 'rounded-' . ($opts['rounded'] ?? '2');

        if (! empty($opts['src'])) {
            return '<img src="' . $this->e($opts['src']) . '" alt="' . $this->e($opts['alt'] ?? '') . '" class="' . Html::cls('img-fluid', $size, $rounded) . '">';
        }

        $variant = $opts['variant'] ?? 'primary';
        $bg      = ($opts['soft'] ?? true) ? "bg-{$variant}-subtle text-{$variant}" : "bg-{$variant} text-white";
        $inner   = ! empty($opts['icon']) ? '<i class="' . $this->e($opts['icon']) . '"></i>' : $this->e($opts['text'] ?? '');

        return '<div class="' . $size . '"><span class="' . Html::cls('avatar-title', $bg, $rounded, 'fw-bold') . '">' . $inner . '</span></div>';
    }

    protected function html(): string
    {
        $group = (array) $this->get('group', []);
        if ($group !== []) {
            $html = '<div' . $this->attrs(['class' => 'avatar-group']) . '>';
            foreach ($group as $item) {
                $item = (array) $item + ['size' => $this->get('size')];
                $html .= '<div class="avatar-group-item">' . $this->one($item) . '</div>';
            }

            return $html . '</div>';
        }

        return '<div' . $this->attrs(['class' => 'd-inline-block']) . '>' . $this->one($this->options) . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 媒体对象（Media Object）：图片 / 头像 + 标题 + 文本，左右布局
 *
 * XfAdmin::media([
 *     'image'  => 'users/user-1.jpg',
 *     'avatar' => false,         // true 时图片作为圆形头像
 *     'title'  => '标题',
 *     'text'   => '正文内容',
 *     'meta'   => '副信息',
 *     'href'   => '#',           // 提供时整体可点击
 * ])
 */
class Media extends Component
{
    protected function defaults(): array
    {
        return [
            'image'  => '',
            'avatar' => false,
            'title'  => '',
            'text'   => '',
            'meta'   => '',
            'href'   => '',
        ];
    }

    protected function html(): string
    {
        $img   = (string) $this->get('image');
        $title = (string) $this->get('title');
        $text  = (string) $this->get('text');
        $meta  = (string) $this->get('meta');
        $href  = (string) $this->get('href');

        $inner = '<div class="xf-media d-flex gap-3">';
        if ($img !== '') {
            $src = $this->img($img);
            if ($this->get('avatar')) {
                $inner .= '<span class="avatar avatar-sm flex-shrink-0"><img src="' . $this->e($src) . '" class="rounded-circle img-fluid" alt=""></span>';
            } else {
                $inner .= '<img src="' . $this->e($src) . '" class="xf-media-img rounded flex-shrink-0" alt="">';
            }
        }
        $inner .= '<div class="xf-media-body flex-grow-1">';
        if ($title !== '') {
            $inner .= '<div class="xf-media-title fw-semibold">' . $this->e($title) . '</div>';
        }
        if ($text !== '') {
            $inner .= '<div class="xf-media-text text-muted small">' . $this->e($text) . '</div>';
        }
        if ($meta !== '') {
            $inner .= '<div class="xf-media-meta small text-muted mt-1">' . $this->e($meta) . '</div>';
        }
        $inner .= '</div></div>';

        if ($href !== '') {
            return '<a' . $this->attrs(['href' => $href, 'class' => 'text-decoration-none text-reset']) . '>' . $inner . '</a>';
        }

        return $inner;
    }
}

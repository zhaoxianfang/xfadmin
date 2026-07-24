<?php

declare(strict_types=1);

namespace XfAdmin\Components\UI;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 轮播
 *
 * XfAdmin::carousel([
 *     'items' => [
 *         ['image' => '/1.jpg', 'caption' => '<h5>标题</h5><p>描述</p>'],
 *         ['content' => '<div>自定义任意HTML</div>'],
 *     ],
 *     'indicators' => true,
 *     'controls'   => true,
 *     'fade'       => false,
 *     'interval'   => 5000,
 *     'dark'       => false,
 * ])
 */
class Carousel extends Component
{
    protected function defaults(): array
    {
        return [
            'items'      => [],
            'indicators' => false,
            'controls'   => true,
            'fade'       => false,
            'interval'   => 5000,
            'dark'       => false,
            'ride'       => 'carousel',
        ];
    }

    protected function html(): string
    {
        $id    = $this->resolveId('xf-carousel');
        $items = array_values((array) $this->get('items', []));

        $html = '<div' . $this->attrs([
            'class'        => Html::cls('carousel slide', ['carousel-fade' => $this->get('fade'), 'carousel-dark' => $this->get('dark')]),
            'id'           => $id,
            'data-bs-ride' => $this->get('ride'),
        ]) . '>';

        if ($this->get('indicators')) {
            $html .= '<div class="carousel-indicators">';
            foreach ($items as $i => $item) {
                $html .= '<button type="button" data-bs-target="#' . $this->e($id) . '" data-bs-slide-to="' . $i . '"' . ($i === 0 ? ' class="active" aria-current="true"' : '') . '></button>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="carousel-inner">';
        foreach ($items as $i => $item) {
            $html .= '<div class="' . Html::cls('carousel-item', ['active' => $i === 0]) . '" data-bs-interval="' . $this->e($item['interval'] ?? $this->get('interval')) . '">';
            if (isset($item['image'])) {
                $html .= '<img src="' . $this->e($item['image']) . '" class="d-block w-100" alt="' . $this->e($item['alt'] ?? '') . '">';
            }
            if (isset($item['caption'])) {
                $html .= '<div class="carousel-caption d-none d-md-block">' . $this->raw($item['caption']) . '</div>';
            }
            if (isset($item['content'])) {
                $html .= $this->raw($item['content']);
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        if ($this->get('controls')) {
            $html .= '<button class="carousel-control-prev" type="button" data-bs-target="#' . $this->e($id) . '" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">上一页</span></button>'
                . '<button class="carousel-control-next" type="button" data-bs-target="#' . $this->e($id) . '" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">下一页</span></button>';
        }

        return $html . '</div>';
    }
}

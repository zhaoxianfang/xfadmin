<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 图片画廊 / 灯箱（GLightbox，可选 Masonry 瀑布流布局）
 *
 * XfAdmin::lightbox([
 *     'images' => [
 *         ['src' => '/big1.jpg', 'thumb' => '/small1.jpg', 'title' => '图一'],
 *         '/big2.jpg',
 *     ],
 *     'columns' => 3,
 *     'masonry' => false,
 *     'gallery' => 'gallery-a',   // 同名画廊内左右切换
 * ])
 */
class Lightbox extends Component
{
    protected function defaults(): array
    {
        return [
            'images'  => [],
            'columns' => 3,
            'masonry' => false,
            'gallery' => 'xf-gallery',
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return $this->get('masonry') ? ['glightbox', 'masonry'] : ['glightbox'];
    }

    protected function html(): string
    {
        $cols   = max(1, (int) $this->get('columns'));
        $config = array_replace_recursive([
            'selector' => '[data-gallery="' . $this->get('gallery') . '"]',
            'masonry'  => (bool) $this->get('masonry'),
        ], (array) $this->get('options', []));

        $html = '<div' . $this->attrs([
            'class'          => 'row g-2',
            'data-xf'        => 'lightbox',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '>';

        foreach ((array) $this->get('images', []) as $image) {
            $image = is_array($image) ? $image : ['src' => $image];
            $thumb = $image['thumb'] ?? $image['src'] ?? '';
            $html .= '<div class="col-' . (int) (12 / $cols) . '">'
                . '<a href="' . $this->e($image['src'] ?? '') . '"' . Html::attrs([
                    'class'        => 'glightbox d-block',
                    'data-gallery' => $this->get('gallery'),
                    'data-glightbox' => isset($image['title']) ? 'title: ' . $image['title'] : null,
                ]) . '>'
                . '<img src="' . $this->e($thumb) . '" alt="' . $this->e($image['title'] ?? '') . '" class="img-fluid rounded">'
                . '</a></div>';
        }

        return $html . '</div>';
    }
}

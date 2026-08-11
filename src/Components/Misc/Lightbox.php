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
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '>';

        // 栅格列类：整除用 col-{n}；非整除回退 col-xf-{cols}（与 FileManager 一致，避免一行挤入过量项）
        $colClass = (12 % $cols === 0) ? 'col-' . (int) (12 / $cols) : 'col-xf-' . $cols;

        foreach ((array) $this->get('images', []) as $image) {
            $image = is_array($image) ? $image : ['src' => $image];
            $thumb = $image['thumb'] ?? $image['src'] ?? '';
            // GLightbox 的 data-glightbox 是自有 DSL（key: value; ...），title 中的 ; 会注入额外配置项，需剔除
            $gTitle = isset($image['title']) ? 'title: ' . str_replace(';', '', (string) $image['title']) : null;
            $html .= '<div class="' . $colClass . '">'
                . '<a href="' . $this->e($image['src'] ?? '') . '"' . Html::attrs([
                    'class'        => 'glightbox d-block',
                    'data-gallery' => $this->get('gallery'),
                    'data-glightbox' => $gTitle,
                ]) . '>'
                . '<img src="' . $this->e($thumb) . '" alt="' . $this->e($image['title'] ?? '') . '" class="img-fluid rounded">'
                . '</a></div>';
        }

        return $html . '</div>';
    }
}

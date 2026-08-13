<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 视频播放器（plugins-video-player.html）
 *
 * XfAdmin::videoPlayer([
 *     'src' => 'videos/sample.mp4',
 *     'poster' => 'images/bg-pattern.png',
 *     'type' => 'video/mp4',
 *     'width' => '100%',
 *     'autoplay' => false,
 *     'controls' => true,
 *     'loop' => false,
 *     'muted' => false,
 *     'title' => '视频标题',
 * ])
 */
class VideoPlayer extends Component
{
    protected function defaults(): array
    {
        return [
            'src' => '',
            'poster' => '',
            'type' => 'video/mp4',
            'width' => '100%',
            'autoplay' => false,
            'controls' => true,
            'loop' => false,
            'muted' => false,
            'title' => '',
        ];
    }

    protected function assets(): array
    {
        return [];
    }

    protected function html(): string
    {
        $src = (string) $this->get('src', '');
        $poster = (string) $this->get('poster', '');
        $type = (string) $this->get('type', 'video/mp4');
        $width = (string) $this->get('width', '100%');
        $autoplay = (bool) $this->get('autoplay', false);
        $controls = (bool) $this->get('controls', true);
        $loop = (bool) $this->get('loop', false);
        $muted = (bool) $this->get('muted', false);
        $title = (string) $this->get('title', '');

        $id = $this->uid('xf-video');

        $attrs = '';
        if ($controls) $attrs .= ' controls';
        if ($autoplay) $attrs .= ' autoplay';
        if ($loop) $attrs .= ' loop';
        if ($muted) $attrs .= ' muted';
        if ($poster) $attrs .= ' poster="' . $this->e($poster) . '"';

        $style = $width === '100%' ? 'width:100%' : '';
        if ($style) $style = ' style="' . $style . '"';

        $html = '<div class="xf-video-player"' . $style . '>';
        if ($title) {
            $html .= '<h5 class="mb-3">' . $this->e($title) . '</h5>';
        }

        if ($src) {
            $html .= '<div class="ratio ratio-16x9"><video id="' . $this->e($id) . '"' . $attrs . ' class="w-100 rounded">';
            $html .= '<source src="' . $this->e($src) . '" type="' . $this->e($type) . '">';
            $html .= '您的浏览器不支持视频播放。</video></div>';
        } else {
            $html .= '<div class="ratio ratio-16x9 bg-dark rounded d-flex align-items-center justify-content-center">';
            $html .= '<div class="text-center text-white"><i class="ti ti-video fs-48 mb-3 d-block"></i>';
            $html .= '<p>未提供视频源</p></div></div>';
        }

        $html .= '</div>';

        return $html;
    }
}

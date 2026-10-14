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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
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

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $src = (string) $this->get('src', '');
        $poster = (string) $this->get('poster', '');
        $type = (string) $this->get('type', 'video/mp4');
        $autoplay = (bool) $this->get('autoplay', false);
        $controls = (bool) $this->get('controls', true);
        $loop = (bool) $this->get('loop', false);
        $muted = (bool) $this->get('muted', false);
        $title = (string) $this->get('title', '');

        $id = $this->uid('xf-video');

        $attrs = '';
        if ($controls) {
            $attrs .= ' controls';
        }
        if ($autoplay) {
            $attrs .= ' autoplay';
        }
        if ($loop) {
            $attrs .= ' loop';
        }
        if ($muted) {
            $attrs .= ' muted';
        }
        // poster 为图片路径，走 img() 解析（外链原样 / 相对路径经 asset）；src 走 safeUrl 拦截伪协议
        if ($poster) {
            $attrs .= ' poster="'.$this->e($this->img($poster)).'"';
        }

        // width 支持任意合法 CSS 长度（此前仅 '100%' 生效）；cssLen 拦截分号注入等非法值并回退 100%
        $w = $this->cssLen($this->get('width'), '100%');
        $style = $w !== '' ? ' style="width:'.$w.'"' : '';

        $html = '<div class="xf-video-player"'.$style.'>';
        if ($title) {
            $html .= '<h5 class="mb-3">'.$this->e($title).'</h5>';
        }
        if ($src) {
            $html .= '<div class="ratio ratio-16x9"><video id="'.$this->e($id).'"'.$attrs.' class="w-100 rounded">';
            $html .= '<source src="'.$this->e($this->safeUrl($src)).'" type="'.$this->e($type).'">';
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

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 视频嵌入（ui-videos.html）
 *
 * XfAdmin::videoEmbed([
 *     'src' => 'https://www.youtube.com/embed/abc123',
 *     'provider' => 'youtube', // youtube | vimeo | html5 | bilibili
 *     'title' => '视频标题',
 *     'description' => '视频描述文字',
 * ])
 */
class VideoEmbed extends Component
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
            'provider' => 'youtube',
            'title' => '',
            'description' => '',
            'ratio' => '16x9',
            'autoplay' => false,
            'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $src = (string) $this->get('src', '');
        $provider = (string) $this->get('provider', 'youtube');
        $title = (string) $this->get('title', '');
        $description = (string) $this->get('description', '');
        $ratio = (string) $this->get('ratio', '16x9');
        $autoplay = (bool) $this->get('autoplay', false);
        $allow = (string) $this->get('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');

        $html = '';

        if ($title || $description) {
            $html .= '<div class="mb-3">';
            if ($title) {
                $html .= '<h5>' . $this->e($title) . '</h5>';
            }
            if ($description) {
                $html .= '<p class="text-muted">' . $this->e($description) . '</p>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="ratio ratio-' . $this->e($ratio) . '">';

        if ($src) {
            if ($provider === 'html5') {
                $html .= '<video controls playsinline class="w-100">';
                $html .= '<source src="' . $this->e($src) . '" type="video/mp4">';
                $html .= '您的浏览器不支持视频播放。';
                $html .= '</video>';
            } else {
                $embedUrl = $src;
                if ($provider === 'youtube') {
                    $embedUrl .= (str_contains($src, '?') ? '&' : '?') . 'rel=0' . ($autoplay ? '&autoplay=1' : '');
                }
                if ($provider === 'bilibili') {
                    $embedUrl .= (str_contains($src, '?') ? '&' : '?') . 'danmaku=0';
                }

                $html .= '<iframe src="' . $this->e($embedUrl) . '" ';
                if ($allow) {
                    $html .= 'allow="' . $this->e($allow) . '" ';
                }
                $html .= 'allowfullscreen></iframe>';
            }
        } else {
            $html .= '<div class="bg-dark d-flex align-items-center justify-content-center text-white">';
            $html .= '<div class="text-center"><i class="ti ti-video fs-48 mb-2 d-block"></i><p>未提供视频源</p></div></div>';
        }

        $html .= '</div>';

        return $html;
    }
}

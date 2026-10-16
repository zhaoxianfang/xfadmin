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
        // ratio 白名单（Bootstrap ratio-* 预设类），非法值回退默认 16x9
        $ratio = $this->enum($this->get('ratio', '16x9'), ['1x1', '4x3', '16x9', '21x9'], '16x9');
        $autoplay = (bool) $this->get('autoplay', false);
        $allow = (string) $this->get('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');

        $html = '';

        if ($title || $description) {
            $html .= '<div class="mb-3">';
            if ($title) {
                $html .= '<h5>'.$this->escape($title).'</h5>';
            }
            if ($description) {
                $html .= '<p class="text-muted">'.$this->escape($description).'</p>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="ratio ratio-'.$ratio.'">';

        if ($src) {
            if ($provider === 'html5') {
                $html .= '<video controls playsinline class="w-100">';
                // src 走 safeUrl 白名单，拦截 javascript: 等伪协议
                $html .= '<source src="'.$this->escape($this->safeUrl($src)).'" type="video/mp4">';
                $html .= '您的浏览器不支持视频播放。';
                $html .= '</video>';
            } else {
                $embedUrl = $src;
                if ($provider === 'youtube') {
                    $embedUrl .= (str_contains($src, '?') ? '&' : '?').'rel=0'.($autoplay ? '&autoplay=1' : '');
                }
                if ($provider === 'bilibili') {
                    $embedUrl .= (str_contains($src, '?') ? '&' : '?').'danmaku=0';
                }

                // embedUrl 同样走 safeUrl 白名单后再转义
                $html .= '<iframe src="'.$this->escape($this->safeUrl($embedUrl)).'" ';
                if ($allow) {
                    $html .= 'allow="'.$this->escape($allow).'" ';
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

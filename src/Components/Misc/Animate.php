<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * CSS 动画包装器（misc-animation.html，基于 animate.css，离线可用）
 *
 * 给任意内容套上入场 / 强调动画，支持四种触发方式：
 *   load   页面加载即播放（默认）
 *   hover  鼠标悬浮播放
 *   click  点击播放
 *   scroll 元素滚动进入视口时播放（IntersectionObserver，逐个触发，适合长页面）
 *
 * XfAdmin::animate([
 *     'animation' => 'bounce',        // 动画名（不带 animate__ 前缀）：bounce/flash/pulse/shakeX/
 *                                     // fadeIn/fadeInUp/zoomIn/slideInLeft/flip/heartBeat...（animate.css 全集）
 *     'trigger'   => 'load',          // load | hover | click | scroll
 *     'infinite'  => false,           // 无限循环
 *     'delay'     => null,            // 延迟：'1s'/'2s'... 或 animate.css 档位 1|2|3|4|5
 *     'speed'     => null,            // 速度：'slow' | 'slower' | 'fast' | 'faster'
 *     'repeat'    => null,            // 重复次数：1|2|3
 *     'content'   => '<h4>内容</h4>',  // 被包裹的任意 HTML / 组件
 *     'tag'       => 'div',           // 外层标签
 * ])
 */
class Animate extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'animation' => 'bounce',
            'trigger'   => 'load',
            'infinite'  => false,
            'delay'     => null,
            'speed'     => null,
            'repeat'    => null,
            'content'   => '',
            'tag'       => 'div',
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['animate'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $anim    = (string) $this->get('animation', 'bounce');
        $trigger = (string) $this->get('trigger', 'load');
        $allowedTags = ['div', 'span', 'section', 'article', 'p', 'header', 'footer', 'main', 'aside', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'figure'];
        $tag     = strtolower((string) $this->get('tag', 'div'));
        if (! in_array($tag, $allowedTags, true)) {
            $tag = 'div';
        }
        // 组装 animate.css 修饰类（无限循环 / 速度 / 延迟 / 重复次数）
        $mods = [];
        if ($this->get('infinite')) {
            $mods[] = 'animate__infinite';
        }
        if ($this->get('speed')) {
            $mods[] = 'animate__' . $this->get('speed');
        }
        if ($this->get('delay') !== null && $this->get('delay') !== '') {
            $mods[] = 'animate__delay-' . rtrim((string) $this->get('delay'), 's') . 's';
        }
        if ($this->get('repeat')) {
            $mods[] = 'animate__repeat-' . (int) $this->get('repeat');
        }
        $attrs = [
            'class'             => 'xf-animate' . ($mods !== [] ? ' ' . implode(' ', $mods) : ''),
            'data-xf'           => 'animate',
            'data-xf-animation' => $anim,
            'data-xf-trigger'   => $trigger,
        ];

        // load 触发直接输出动画类（无需等 JS），其余触发交由 xfadmin.js 绑定
        if ($trigger === 'load') {
            $attrs['class'] .= ' animate__animated animate__' . $anim;
        }
        return '<' . $tag . $this->attrs($attrs) . '>' . $this->raw($this->get('content')) . '</' . $tag . '>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 强调提示框（Callout），比 Alert 更轻量、常用于说明性文字
 *
 * XfAdmin::callout([
 *     'title'   => '提示',
 *     'body'    => '这是一段说明文字。',
 *     'variant' => 'info',   // info | success | warning | danger | primary
 *     'icon'    => 'ti ti-info-circle',
 * ])
 */
class Callout extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'   => '',
            'body'    => '',
            'variant' => 'info',
            'icon'    => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $v     = (string) $this->get('variant');
        $icon  = (string) $this->get('icon');
        $title = (string) $this->get('title');
        $body  = (string) $this->get('body');

        $html = '<div' . $this->attrs(['class' => 'xf-callout xf-callout-' . $this->e($v)]) . '>';
        if ($icon !== '') {
            $html .= '<i class="' . $this->e($icon) . ' xf-callout-icon"></i>';
        }
        $html .= '<div class="xf-callout-body">';
        if ($title !== '') {
            $html .= '<div class="xf-callout-title">' . $this->e($title) . '</div>';
        }
        if ($body !== '') {
            $html .= '<div class="xf-callout-text">' . $this->e($body) . '</div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

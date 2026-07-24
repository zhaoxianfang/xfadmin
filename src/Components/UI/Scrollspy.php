<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 滚动监听（Bootstrap Scrollspy）
 *
 * XfAdmin::scrollspy([
 *     'items'  => [
 *         ['id' => 'sec1', 'label' => '第一节', 'content' => '...'],
 *         ['id' => 'sec2', 'label' => '第二节', 'content' => '...'],
 *     ],
 *     'height' => '250px',
 *     'nav_width' => 3,   // 左侧导航栅格
 * ])
 */
class Scrollspy extends Component
{
    protected function defaults(): array
    {
        return [
            'items'     => [],
            'height'    => '250px',
            'nav_width' => 3,
            'offset'    => 0,
            'smooth'    => true,
        ];
    }

    protected function html(): string
    {
        $navId  = $this->uid('ss-nav');
        $bodyId = $this->uid('ss-body');
        $items  = (array) $this->get('items', []);
        $navW   = max(1, min(6, (int) $this->get('nav_width')));

        // 左侧导航
        $nav = '<nav id="' . $navId . '" class="nav nav-pills flex-column">';
        foreach ($items as $item) {
            $nav .= '<a class="nav-link" href="#' . $this->e($item['id']) . '">' . $this->e($item['label'] ?? $item['id']) . '</a>';
        }
        $nav .= '</nav>';

        // 右侧内容
        $spyAttrs = [
            'data-bs-spy'      => 'scroll',
            'data-bs-target'   => '#' . $navId,
            'data-bs-offset'   => (int) $this->get('offset'),
            'data-bs-smooth-scroll' => $this->get('smooth') ? 'true' : 'false',
            'tabindex'         => '0',
            'class'            => 'overflow-auto border rounded p-3',
            'style'            => 'height:' . $this->e($this->get('height')) . ';',
        ];
        $body = '<div id="' . $bodyId . '"' . Html::attrs($spyAttrs) . '>';
        foreach ($items as $item) {
            $body .= '<h5 id="' . $this->e($item['id']) . '">' . $this->e($item['label'] ?? $item['id']) . '</h5>';
            $body .= '<div class="mb-3">' . $this->raw($item['content'] ?? '') . '</div>';
        }
        $body .= '</div>';

        return '<div' . $this->attrs(['class' => 'row']) . '>'
            . '<div class="col-' . $navW . '">' . $nav . '</div>'
            . '<div class="col-' . (12 - $navW) . '">' . $body . '</div>'
            . '</div>';
    }
}

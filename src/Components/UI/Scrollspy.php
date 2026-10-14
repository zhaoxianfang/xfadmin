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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items' => [],
            'height' => '250px',
            'nav_width' => 3,
            'offset' => 0,
            'smooth' => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $navId = $this->uid('ss-nav');
        $bodyId = $this->uid('ss-body');
        // 渲染前一次性预处理：标量项转数组，并为缺 id 的项生成唯一锚点 id。
        // nav 与 body 两个循环共用同一份 $items，保证 href="#id" 与 body 的 id 一致（锚点生效）。
        $items = array_map(fn ($it) => (array) $it, (array) $this->get('items', []));
        foreach ($items as $i => $it) {
            $items[$i]['id'] = $it['id'] ?? $this->uid('ss-sec-'.$i);
        }
        $navW = max(1, min(6, (int) $this->get('nav_width')));

        // 左侧导航
        $nav = '<nav id="'.$navId.'" class="nav nav-pills flex-column">';
        foreach ($items as $item) {
            $itemId = $item['id'];
            $nav .= '<a class="nav-link" href="#'.$this->e($itemId).'">'.$this->e($item['label'] ?? $itemId).'</a>';
        }
        $nav .= '</nav>';

        // 右侧内容
        $spyAttrs = [
            'data-bs-spy' => 'scroll',
            'data-bs-target' => '#'.$navId,
            'data-bs-offset' => (int) $this->get('offset'),
            'data-bs-smooth-scroll' => $this->get('smooth') ? 'true' : 'false',
            'tabindex' => '0',
            'class' => 'overflow-auto border rounded p-3',
            'style' => 'height:'.$this->cssLen($this->get('height'), '250px').';',
        ];
        $body = '<div id="'.$bodyId.'"'.Html::attrs($spyAttrs).'>';
        foreach ($items as $item) {
            // 与 nav 循环共用同一份 $items：此处 id 即 nav 中 href 指向的锚点
            $itemId = $item['id'];
            $body .= '<h5 id="'.$this->e($itemId).'">'.$this->e($item['label'] ?? $itemId).'</h5>';
            $body .= '<div class="mb-3">'.$this->raw($item['content'] ?? '').'</div>';
        }
        $body .= '</div>';

        return '<div'.$this->attrs(['class' => 'row']).'>'
            .'<div class="col-'.$navW.'">'.$nav.'</div>'
            .'<div class="col-'.(12 - $navW).'">'.$body.'</div>'
            .'</div>';
    }
}

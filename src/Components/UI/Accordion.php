<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 手风琴 / 折叠面板
 *
 * XfAdmin::accordion([
 *     'items' => [
 *         ['title' => '第一项', 'content' => '...', 'open' => true],
 *         ['title' => '第二项', 'content' => '...'],
 *     ],
 *     'flush'       => false,
 *     'always_open' => false,
 * ])
 */
class Accordion extends Component
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
            'flush' => false,
            'always_open' => false,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-accordion');
        $html = '<div'.$this->attrs(['class' => Html::cls('accordion', ['accordion-flush' => $this->get('flush')])]).'>';

        foreach ((array) $this->get('items', []) as $item) {
            // 标量（字符串）容错：非数组项按 title 处理，避免 PHP 8 下标访问致命错误
            if (! is_array($item)) {
                $item = ['title' => (string) $item];
            }
            $itemId = $item['id'] ?? $this->uid('xf-acc-item');
            $open = ! empty($item['open']);

            $html .= '<div class="accordion-item">';
            // 无障碍：header 按钮声明 aria-controls 关联折叠面板；折叠面板用 aria-labelledby 反向关联 header，
            // 供屏幕阅读器正确朗读「哪个按钮控制哪块内容」。
            $html .= '<h2 class="accordion-header" id="'.$this->escape($itemId).'-header"><button class="'.Html::cls('accordion-button', ['collapsed' => ! $open])
                .'" type="button" data-bs-toggle="collapse" data-bs-target="#'.$this->escape($itemId).'" aria-expanded="'.($open ? 'true' : 'false').'" aria-controls="'.$this->escape($itemId).'">'
                .$this->escape($item['title'] ?? '').'</button></h2>';
            $html .= '<div id="'.$this->escape($itemId).'" class="'.Html::cls('accordion-collapse collapse', ['show' => $open]).'"'
                .' aria-labelledby="'.$this->escape($itemId).'-header"'
                .($this->get('always_open') ? '' : ' data-bs-parent="#'.$this->escape($id).'"').'>'
                .'<div class="accordion-body">'.$this->raw($item['content'] ?? '').'</div></div>';
            $html .= '</div>';
        }

        return $html.'</div>';
    }
}

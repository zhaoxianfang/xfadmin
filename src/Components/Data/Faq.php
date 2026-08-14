<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 常见问题（pages-faq.html）—— 手风琴样式
 *
 * XfAdmin::faq([
 *     'items' => [
 *         ['q' => '如何注册？', 'a' => '点击右上角注册按钮...'],
 *         ['q' => '如何退款？', 'a' => '联系客服...'],
 *     ],
 *     'flush'      => false,
 *     'bordered'   => true,
 *     'open'       => 0,          // 默认展开第几个，null 表示全部收起
 * ])
 */
class Faq extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'flush'    => false,
            'bordered' => true,
            'open'     => 0,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id  = $this->resolveId('faq');
        $cls = Html::cls('accordion', [
            'accordion-flush'    => (bool) $this->get('flush'),
            'accordion-bordered' => (bool) $this->get('bordered'),
        ]);

        $html = '<div' . $this->attrs(['class' => $cls, 'id' => $id]) . '>';

        foreach (array_values((array) $this->get('items', [])) as $i => $item) {
            // 标量（字符串）容错：非数组项按 q 处理，避免 PHP 8 下标访问致命错误
            if (! is_array($item)) {
                $item = ['q' => (string) $item];
            }
            $open   = $this->get('open') === $i;
            $itemId = $this->e($id . '-item-' . $i);
            $html .= '<div class="accordion-item">';
            $html .= '<h2 class="accordion-header">';
            $html .= '<button class="accordion-button' . ($open ? '' : ' collapsed') . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $itemId . '" aria-expanded="' . ($open ? 'true' : 'false') . '">'
                . $this->e($item['q'] ?? '') . '</button>';
            $html .= '</h2>';
            $html .= '<div id="' . $itemId . '" class="accordion-collapse collapse' . ($open ? ' show' : '') . '" data-bs-parent="#' . $id . '">';
            $html .= '<div class="accordion-body">' . $this->raw($item['a'] ?? '') . '</div>';
            $html .= '</div></div>';
        }
        return $html . '</div>';
    }
}

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
    protected function defaults(): array
    {
        return [
            'items'       => [],
            'flush'       => false,
            'always_open' => false,
        ];
    }

    protected function html(): string
    {
        $id   = $this->resolveId('xf-accordion');
        $html = '<div' . $this->attrs(['class' => Html::cls('accordion', ['accordion-flush' => $this->get('flush')])]) . '>';

        foreach ((array) $this->get('items', []) as $item) {
            $itemId = $item['id'] ?? $this->uid('xf-acc-item');
            $open   = ! empty($item['open']);

            $html .= '<div class="accordion-item">';
            $html .= '<h2 class="accordion-header"><button class="' . Html::cls('accordion-button', ['collapsed' => ! $open])
                . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $this->e($itemId) . '" aria-expanded="' . ($open ? 'true' : 'false') . '">'
                . $this->raw($item['title'] ?? '') . '</button></h2>';
            $html .= '<div id="' . $this->e($itemId) . '" class="' . Html::cls('accordion-collapse collapse', ['show' => $open]) . '"'
                . ($this->get('always_open') ? '' : ' data-bs-parent="#' . $this->e($id) . '"') . '>'
                . '<div class="accordion-body">' . $this->raw($item['content'] ?? '') . '</div></div>';
            $html .= '</div>';
        }

        return $html . '</div>';
    }
}

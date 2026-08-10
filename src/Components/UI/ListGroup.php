<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 列表组
 *
 * XfAdmin::listGroup([
 *     'items' => [
 *         ['text' => '项目一', 'active' => true],
 *         ['text' => '项目二', 'url' => '/x', 'badge' => ['text' => '3', 'class' => 'bg-danger']],
 *         ['content' => '<b>自定义HTML</b>'],
 *     ],
 *     'flush'     => false,
 *     'numbered'  => false,
 *     'horizontal'=> false,
 * ])
 */
class ListGroup extends Component
{
    protected function defaults(): array
    {
        return [
            'items'      => [],
            'flush'      => false,
            'numbered'   => false,
            'horizontal' => false,
        ];
    }

    protected function html(): string
    {
        $class = Html::cls('list-group', [
            'list-group-flush'      => $this->get('flush'),
            'list-group-numbered'   => $this->get('numbered'),
            'list-group-horizontal' => $this->get('horizontal'),
        ]);

        $hasLinks = false;
        foreach ((array) $this->get('items', []) as $item) {
            if (is_array($item) && isset($item['url'])) {
                $hasLinks = true;
                break;
            }
        }

        $tag  = $hasLinks ? 'div' : 'ul';
        $html = '<' . $tag . $this->attrs(['class' => $class]) . '>';

        foreach ((array) $this->get('items', []) as $item) {
            $item    = is_array($item) ? $item : ['text' => $item];
            $variant = isset($item['variant']) ? ' list-group-item-' . $this->e($item['variant']) : '';
            $cls     = Html::cls('list-group-item' . $variant, [
                'active'                => ! empty($item['active']),
                'disabled'              => ! empty($item['disabled']),
                'list-group-item-action' => isset($item['url']),
                'd-flex justify-content-between align-items-center' => isset($item['badge']),
            ]);

            $content = isset($item['content']) ? $this->raw($item['content']) : $this->e($item['text'] ?? '');
            if (isset($item['badge'])) {
                $badge    = is_array($item['badge']) ? $item['badge'] : ['text' => $item['badge']];
                $content .= '<span class="badge ' . $this->e($badge['class'] ?? 'bg-primary') . ' rounded-pill">' . $this->e($badge['text'] ?? '') . '</span>';
            }

            $html .= isset($item['url'])
                ? '<a href="' . $this->e($item['url']) . '" class="' . $cls . '">' . $content . '</a>'
                : '<li class="' . $cls . '">' . $content . '</li>';
        }

        return $html . '</' . $tag . '>';
    }
}

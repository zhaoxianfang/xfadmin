<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 可拖拽排序列表（SortableJS，支持跨列表拖拽、把手、嵌套）
 *
 * XfAdmin::nestable([
 *     'items'  => ['项目一', '项目二', ['content' => '<b>自定义</b>', 'id' => 5]],
 *     'group'  => 'shared',       // 同组列表可互相拖拽
 *     'handle' => false,          // true 时渲染拖拽把手
 *     'input'  => 'sort_order',   // 排序结果同步到隐藏 input（逗号分隔 id）
 * ])
 */
class Nestable extends Component
{
    protected function defaults(): array
    {
        return [
            'items'   => [],
            'group'   => null,
            'handle'  => false,
            'input'   => null,
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return ['sortablejs'];
    }

    protected function html(): string
    {
        $id     = $this->resolveId('xf-sortable');
        $config = array_replace_recursive(array_filter([
            'group'  => $this->get('group'),
            'handle' => $this->get('handle') ? '.xf-drag-handle' : null,
            'input'  => $this->get('input'),
        ], fn ($v) => $v !== null), (array) $this->get('options', []));

        $html = '<div' . $this->attrs([
            'id'             => $id,
            'class'          => 'list-group',
            'data-xf'        => 'sortable',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '>';

        foreach ((array) $this->get('items', []) as $i => $item) {
            $item    = is_array($item) ? $item : ['content' => $this->e($item)];
            $handle  = $this->get('handle') ? '<i class="ti ti-grip-vertical xf-drag-handle me-2" style="cursor:grab"></i>' : '';
            $html   .= '<div class="list-group-item d-flex align-items-center"' . Html::attrs(['data-id' => $item['id'] ?? $i]) . '>'
                . $handle . $this->raw($item['content'] ?? '') . '</div>';
        }

        $html .= '</div>';
        if ($this->get('input')) {
            $html .= '<input type="hidden" name="' . $this->e($this->get('input')) . '" id="' . $this->e($id) . '-input">';
        }

        return $html;
    }
}

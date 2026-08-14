<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 可嵌套拖拽排序列表（对齐 INSPINIA misc-nestable.html 的 .nested-sortable）
 *
 * 结构：list-group.nested-sortable（可多层嵌套 list-group.nested-sortable），
 * 由 xfadmin.js 的 nestable 模块基于 SortableJS（group:'nested'）初始化。
 *
 * XfAdmin::nestable([
 *     'items'  => [
 *         ['content' => '设计阶段', 'id' => 1, 'children' => [
 *             ['content' => 'UI 设计', 'id' => 11],
 *             ['content' => '前端', 'id' => 12, 'children' => [['content' => '组件', 'id' => 121]]],
 *         ]],
 *         '开发阶段',
 *     ],
 *     'handle' => true,   // 渲染拖拽把手（.sort-handle）
 *     'input'  => 'order', // 隐藏 input 名，拖拽后写入逗号分隔的 id 序列
 * ])
 */
class Nestable extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['items' => [], 'handle' => false, 'input' => null, 'options' => []];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['sortablejs'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $handle = (bool) $this->get('handle');
        $class  = 'list-group nested-sortable' . ($handle ? ' nested-sortable-handle' : '');

        $html = '<div' . $this->attrs(['class' => $class, 'data-xf' => 'nestable']) . '>';
        foreach ((array) $this->get('items', []) as $item) {
            $html .= $this->renderItem($item, $handle);
        }
        $html .= '</div>';

        if ($this->get('input')) {
            $html .= '<input type="hidden" name="' . $this->e($this->get('input')) . '" data-nestable-input>';
        }
        return $html;
    }

    // 递归渲染：list-group-item 内可嵌一个 list-group.nested-sortable（子级）
    /**
     * render Item（protected实例方法）
     *
     * @param mixed $item item
     * @param bool $handle handle
     *
     * @return string result
     */
    protected function renderItem($item, bool $handle): string
    {
        $item   = is_array($item) ? $item : ['content' => (string) $item];
        $id     = $item['id'] ?? null;
        $handleIcon = $handle ? '<i class="ti ti-grip-horizontal align-middle sort-handle me-2" style="cursor:grab"></i>' : '';
        $content = $handleIcon . $this->raw($item['content'] ?? '');

        $children = '';
        if (!empty($item['children'])) {
            $children = '<div class="list-group nested-sortable' . ($handle ? ' nested-sortable-handle' : '') . '">';
            foreach ((array) $item['children'] as $c) {
                $children .= $this->renderItem($c, $handle);
            }
            $children .= '</div>';
        }
        return '<div class="list-group-item"' . ($id !== null ? ' data-id="' . $this->e((string) $id) . '"' : '') . '>'
            . $content . $children . '</div>';
    }
}

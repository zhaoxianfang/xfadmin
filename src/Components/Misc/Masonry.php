<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 瀑布流布局容器（对齐 INSPINIA misc-masonry.html）
 *
 * 结构：.row + .col-xl-N.col-md-6.masonry-cell，由 xfadmin.js 的 masonry 模块基于 Masonry.js 初始化。
 * 若 Masonry.js 未加载，自动降级为 Bootstrap 网格（不破版）。
 *
 * XfAdmin::masonry([
 *     'columns' => 3,
 *     'gap'     => 4,
 *     'items'   => [
 *         '<div class="card">…</div>',
 *         ['html' => '<div class="card">…</div>'],
 *     ],
 * ])
 */
class Masonry extends Component
{
    protected function defaults(): array
    {
        return ['columns' => 3, 'gap' => 4, 'items' => []];
    }

    protected function assets(): array
    {
        return ['masonry'];
    }

    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        $cols  = max(1, min(4, (int) $this->get('columns', 3)));
        $gap   = (int) $this->get('gap', 4);

        if (empty($items)) {
            return '';
        }

        // 列数 → Bootstrap xl 跨度（2→6, 3→4, 4→3）
        $xl = [1 => 12, 2 => 6, 3 => 4, 4 => 3][$cols] ?? 4;

        $html = '<div class="row g-' . $gap . '" data-xf="masonry" data-masonry=\'{"percentPosition": true}\'>';
        foreach ($items as $item) {
            $inner = is_array($item) ? (string) ($item['html'] ?? '') : (string) $item;
            $html .= '<div class="col-xl-' . $xl . ' col-md-6 masonry-cell">' . $inner . '</div>';
        }

        return $html . '</div>';
    }
}

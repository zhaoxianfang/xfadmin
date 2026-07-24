<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 搜索结果列表（pages-search-results.html）
 *
 * XfAdmin::searchResults([
 *     'query'  => 'laravel',
 *     'count'  => 12,
 *     'items'  => [
 *         [
 *             'icon'    => 'ti ti-folder',        // 或 thumb 图片
 *             'title'   => '文档中心',
 *             'url'     => '/docs',
 *             'excerpt' => '关于 Laravel 的文档……',
 *             'meta'    => '更新于 2026-07-01',
 *             'tags'    => ['Laravel', '文档'],
 *         ],
 *     ],
 *     'filters' => ['全部' => 12, '文档' => 8, '用户' => 4],
 * ])
 */
class SearchResults extends Component
{
    protected function defaults(): array
    {
        return [
            'query'   => '',
            'count'   => 0,
            'items'   => [],
            'filters' => [],
            'pagination' => '',
        ];
    }

    protected function html(): string
    {
        $id = $this->resolveId('search');
        $items = array_values((array) $this->get('items', []));

        $html = '<div' . $this->attrs(['class' => 'xf-search-results', 'id' => $id]) . '>';

        // 结果统计
        $html .= '<div class="d-flex align-items-center justify-content-between mb-3">';
        $html .= '<p class="text-muted mb-0">找到 <span class="fw-semibold text-body">' . (int) $this->get('count') . '</span> 条与 “'
            . $this->e($this->get('query')) . '” 相关的结果</p>';
        $html .= '</div>';

        // 过滤
        if (! empty($this->get('filters'))) {
            $html .= '<div class="mb-3 d-flex flex-wrap gap-2">';
            foreach ((array) $this->get('filters') as $label => $n) {
                $html .= '<a href="#" class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 text-decoration-none">' . $this->e((string) $label) . ' <span class="ms-1">'
                    . $this->e((string) $n) . '</span></a>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="list-group list-group-flush border rounded">';
        foreach ($items as $it) {
            $it = (array) $it;
            $thumb = $it['thumb'] ?? '';
            $icon = $it['icon'] ?? 'ti ti-file-text';
            $html .= '<a href="' . $this->e($it['url'] ?? '#') . '" class="list-group-item list-group-item-action d-flex gap-3 py-3">';
            if ($thumb) {
                $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim($thumb, '/'))) . '" class="avatar-xs rounded-circle" alt="">';
            } else {
                $html .= '<span class="avatar-title bg-primary-subtle text-primary rounded fs-22 d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="' . $this->e($icon) . '"></i></span>';
            }
            $html .= '<div class="flex-grow-1">';
            $html .= '<div class="d-flex justify-content-between"><h6 class="mb-1">' . $this->e($it['title'] ?? '') . '</h6>';
            if (! empty($it['meta'])) {
                $html .= '<small class="text-muted">' . $this->e($it['meta']) . '</small>';
            }
            $html .= '</div>';
            if (! empty($it['excerpt'])) {
                $html .= '<p class="text-muted small mb-1">' . $this->e($it['excerpt']) . '</p>';
            }
            if (! empty($it['tags'])) {
                $html .= '<div class="d-flex flex-wrap gap-1">' . $this->tags($it['tags']) . '</div>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';

        if ($pg = $this->get('pagination')) {
            $html .= '<div class="mt-3">' . $this->raw($pg) . '</div>';
        }

        return $html . '</div>';
    }

    private function tags(array $tags): string
    {
        $out = '';
        foreach ($tags as $t) {
            $out .= '<span class="badge text-bg-light">' . $this->e($t) . '</span>';
        }

        return $out;
    }
}

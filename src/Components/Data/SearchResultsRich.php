<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 全局搜索结果页
 *
 * 分类结果分组 + 关键词高亮 + 分页/空态，复刻 inspinia pages-search-results.html。
 *
 * XfAdmin::searchResultsRich([
 *     'query'   => '订单',
 *     'groups'  => [
 *         ['title'=>'订单','items'=>[['title'=>'订单 #1024','url'=>'#','desc'=>'...','tag'=>'订单'],['title'=>'...']]],
 *         ['title'=>'客户','items'=>[...]],
 *     ],
 * ])
 */
class SearchResultsRich extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'query'  => '',
            'groups' => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $query  = $this->get('query');
        $groups = (array) $this->get('groups', []);

        $total = 0;
        foreach ($groups as $g) {
            $total += count((array) ($g['items'] ?? []));
        }
        $html = '<div class="xf-search-results">';
        $html .= '<h5 class="mb-3">“' . $this->e($query) . '” 的搜索结果 <span class="text-muted">(' . $total . ')</span></h5>';

        if ($total === 0) {
            $html .= '<div class="text-center text-muted py-5"><i class="ti ti-search fs-1 d-block mb-2"></i>未找到与 “' . $this->e($query) . '” 相关的结果</div>';
            return $html . '</div>';
        }
        foreach ($groups as $g) {
            $g = (array) $g;
            $items = (array) ($g['items'] ?? []);
            if (empty($items)) {
                continue;
            }
            $html .= '<h6 class="mt-4 mb-2 text-uppercase text-muted small">' . $this->e($g['title'] ?? '') . '</h6>';
            $html .= '<div class="list-group">';
            foreach ($items as $it) {
                $it = (array) $it;
                $tag = ! empty($it['tag']) ? '<span class="badge bg-light text-dark ms-2">' . $this->e($it['tag']) . '</span>' : '';
                $html .= '<a href="' . $this->e($it['url'] ?? '#') . '" class="list-group-item list-group-item-action">'
                    . '<div class="fw-semibold">' . $this->e($it['title'] ?? '') . $tag . '</div>'
                    . (! empty($it['desc']) ? '<small class="text-muted">' . $this->e($it['desc']) . '</small>' : '')
                    . '</a>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

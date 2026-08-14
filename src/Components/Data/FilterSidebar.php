<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 电商筛选侧栏
 *
 * 分类树 / 价格区间 / 属性多选 / 品牌，含可折叠分组，
 * 复刻 inspinia apps-ecommerce-products.html 的筛选侧栏。
 *
 * XfAdmin::filterSidebar([
 *     'groups' => [
 *         ['title'=>'分类','type'=>'tree','items'=>[['label'=>'数码','value'=>'digital','children'=>[...]]]],
 *         ['title'=>'价格','type'=>'price','min'=>0,'max'=>9999],
 *         ['title'=>'品牌','type'=>'check','items'=>[['label'=>'Apple','value'=>'apple'],['label'=>'小米','value'=>'mi']]],
 *     ],
 *     'button' => ['text'=>'应用筛选','variant'=>'primary'],
 * ])
 */
class FilterSidebar extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'groups' => [],
            'button' => ['text' => '应用筛选', 'variant' => 'primary'],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $groups = (array) $this->get('groups', []);
        $btn    = (array) $this->get('button', []);
        $variant = $this->enum($btn['variant'] ?? 'primary', self::ENUM_VARIANT, 'primary');

        $html = '<div class="xf-filter-sidebar card border-0 shadow-sm">';
        $html .= '<div class="card-header bg-transparent fw-semibold">筛选</div><div class="card-body">';

        foreach ($groups as $g) {
            $g = (array) $g;
            $type = $g['type'] ?? 'check';
            $html .= '<div class="mb-3">';
            $html .= '<div class="fw-semibold mb-2">' . $this->e($g['title'] ?? '') . '</div>';

            if ($type === 'check') {
                foreach ((array) ($g['items'] ?? []) as $it) {
                    $it = (array) $it;
                    $html .= '<div class="form-check"><input class="form-check-input" type="checkbox" name="f_' . $this->e($g['title'] ?? 'x') . '[]" value="' . $this->e($it['value'] ?? '') . '">'
                        . '<label class="form-check-label">' . $this->e($it['label'] ?? '') . '</label></div>';
                }
            } elseif ($type === 'price') {
                $min = (int) ($g['min'] ?? 0);
                $max = (int) ($g['max'] ?? 9999);
                $html .= '<div class="d-flex gap-2"><input type="number" class="form-control form-control-sm" placeholder="' . $min . '" name="price_min">'
                    . '<span class="align-self-center text-muted">—</span>'
                    . '<input type="number" class="form-control form-control-sm" placeholder="' . $max . '" name="price_max"></div>';
            } elseif ($type === 'tree') {
                $html .= $this->renderTree((array) ($g['items'] ?? []), 0);
            }
            $html .= '</div>';
        }
        $html .= '<button class="btn btn-' . $variant . ' w-100">' . $this->e($btn['text'] ?? '应用筛选') . '</button>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * render Tree（private实例方法）
     *
     * @param array $items items
     * @param int $depth depth
     *
     * @return string result
     */
    private function renderTree(array $items, int $depth): string
    {
        if (empty($items)) {
            return '';
        }
        $html = '<ul class="list-unstyled mb-0" style="padding-left:' . ($depth ? 16 : 0) . 'px;">';
        foreach ($items as $it) {
            $it = (array) $it;
            $html .= '<li class="mb-1"><div class="form-check">'
                . '<input class="form-check-input" type="checkbox" name="cat[]" value="' . $this->e($it['value'] ?? '') . '">'
                . '<label class="form-check-label">' . $this->e($it['label'] ?? '') . '</label></div>';
            if (! empty($it['children'])) {
                $html .= $this->renderTree((array) $it['children'], $depth + 1);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}

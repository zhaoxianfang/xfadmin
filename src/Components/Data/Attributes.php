<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 产品属性管理（apps-ecommerce-attributes.html）
 *
 * XfAdmin::attributes([
 *     'attributes' => [
 *         ['name' => '颜色', 'slug' => 'color', 'type' => 'select', 'values' => ['红色', '蓝色', '黑色', '白色'], 'products' => 128],
 *         ['name' => '尺寸', 'slug' => 'size', 'type' => 'select', 'values' => ['S', 'M', 'L', 'XL'], 'products' => 256],
 *         ...
 *     ],
 * ])
 */
class Attributes extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'attributes' => [],
            'title' => '产品属性',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $attributes = (array) $this->get('attributes', []);
        $title = (string) $this->get('title', '产品属性');

        $html = '<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">' . $this->e($title) . '</h5>'
            . '<button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>添加属性</button></div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">';
        $html .= '<thead><tr><th class="ps-3">属性名称</th><th>标识符</th><th>类型</th><th>可选值</th><th>关联产品数</th><th class="text-end pe-3">操作</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($attributes as $attr) {
            $attr = (array) $attr;
            $name = (string) ($attr['name'] ?? '');
            $slug = (string) ($attr['slug'] ?? '');
            $type = (string) ($attr['type'] ?? 'text');
            $values = (array) ($attr['values'] ?? []);
            $products = (int) ($attr['products'] ?? 0);

            $typeLabel = match ($type) {
                'select' => '下拉选择',
                'color' => '颜色选择',
                'text' => '文本输入',
                'number' => '数字输入',
                default => $this->e($type),
            };

            $html .= '<tr><td class="ps-3"><a href="javascript:void(0)" class="fw-semibold">' . $this->e($name) . '</a></td>';
            $html .= '<td><code>' . $this->e($slug) . '</code></td>';
            $html .= '<td><span class="badge bg-secondary-subtle text-secondary">' . $typeLabel . '</span></td>';
            $html .= '<td><div class="d-flex flex-wrap gap-1">';
            foreach (array_slice($values, 0, 4) as $v) {
                $html .= '<span class="badge bg-light text-dark border">' . $this->e((string) $v) . '</span>';
            }
            if (count($values) > 4) {
                $html .= '<span class="badge bg-light text-muted">+' . (count($values) - 4) . '</span>';
            }
            $html .= '</div></td>';
            $html .= '<td>' . $products . '</td>';
            $html .= '<td class="text-end pe-3"><div class="btn-group btn-group-sm">'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-pencil"></i></button>'
                . '<button class="btn btn-outline-secondary"><i class="ti ti-trash"></i></button></div></td></tr>';
        }
        if (empty($attributes)) {
            $html .= '<tr><td colspan="6" class="text-center text-muted py-4">暂无属性数据</td></tr>';
        }
        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 套餐功能对比矩阵表
 *
 * 行 × 列（计划）交叉 ✓ / ✗ / 文本，复刻价格页的"功能对比表"。
 *
 * XfAdmin::featureComparisonTable([
 *     'plans' => ['基础版','专业版','企业版'],
 *     'featured' => 1,           // 高亮列索引
 *     'rows'  => [
 *         ['name'=>'项目数', 'values'=>['1','10','∞']],
 *         ['name'=>'数据导出', 'values'=>[false,true,true]],
 *         ['name'=>'SSO', 'values'=>[false,false,true]],
 *     ],
 * ])
 */
class FeatureComparisonTable extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'plans'    => [],
            'featured' => null,
            'rows'     => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $plans    = (array) $this->get('plans', []);
        $featured = $this->get('featured');
        $rows     = (array) $this->get('rows', []);

        $html = '<div class="table-responsive"><table class="table table-bordered align-middle text-center">';
        $html .= '<thead><tr><th class="text-start">功能</th>';
        foreach ($plans as $i => $p) {
            $cls = ($i === $featured) ? ' table-primary' : '';
            $html .= '<th class="' . $cls . '">' . $this->e($p) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $r) {
            $r = (array) $r;
            $html .= '<tr><td class="text-start fw-medium">' . $this->e($r['name'] ?? '') . '</td>';
            foreach ((array) ($r['values'] ?? []) as $v) {
                if (is_bool($v)) {
                    $html .= $v
                        ? '<td><i class="ti ti-check text-success fs-5"></i></td>'
                        : '<td><i class="ti ti-x text-danger"></i></td>';
                } else {
                    $html .= '<td>' . $this->e($v) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        return $html;
    }
}

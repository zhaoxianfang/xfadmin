<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 销售漏斗 / 商机管线（Deals / Pipeline）—— 对齐 INSPINIA v4 `deals.html` 的阶段看板结构。
 *
 * 输出蓝本：
 *   <div class="xf-deals">
 *     <div class="row">
 *       每个阶段一列：
 *         <div class="col-xl-3 xf-deal-stage">
 *           <div class="card"> 标题 + 合计金额 + 数量
 *             <div class="xf-deal-card"> 每个商机卡片：标题/客户/金额/负责人/概率
 *
 * 阶段通过 stages 配置定义（key=阶段标识，name=显示名，color=语义色，total 自动汇总）。
 *
 * XfAdmin::deals([
 *     'stages' => [
 *         'lead'     => ['name' => '线索', 'color' => 'info'],
 *         'contact'  => ['name' => '接洽', 'color' => 'primary'],
 *         'proposal' => ['name' => '方案', 'color' => 'warning'],
 *         'won'      => ['name' => '成交', 'color' => 'success'],
 *     ],
 *     'deals' => [
 *         ['id'=>1,'stage'=>'lead','title'=>'XX 采购','customer'=>'A 公司','amount'=>120000,'owner'=>'张三','prob'=>40],
 *     ],
 * ])
 */
class Deals extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'stages' => [
                'lead'     => ['name' => '线索', 'color' => 'info'],
                'contact'  => ['name' => '接洽', 'color' => 'primary'],
                'proposal' => ['name' => '方案', 'color' => 'warning'],
                'won'      => ['name' => '成交', 'color' => 'success'],
            ],
            'deals'  => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $stages = (array) $this->get('stages');
        $deals  = (array) $this->get('deals');

        // 按阶段分组
        $grouped = [];
        foreach ($stages as $k => $s) {
            $grouped[$k] = [];
        }
        foreach ($deals as $d) {
            $d = (array) $d;
            $sk = $d['stage'] ?? '';
            if (isset($grouped[$sk])) {
                $grouped[$sk][] = $d;
            }
        }
        $html = '<div class="xf-deals"><div class="row g-3">';
        foreach ($stages as $key => $s) {
            $s    = (array) $s;
            $name = $s['name'] ?? $key;
            // 结构性枚举字段（拼 class）：白名单校验，防属性逃逸注入
            $color = $s['color'] ?? 'primary';
            if (! in_array($color, ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'], true)) {
                $color = 'primary';
            }
            $items = $grouped[$key] ?? [];
            $total = 0;
            foreach ($items as $it) {
                $total += (float) ($it['amount'] ?? 0);
            }
            $cards = '';
            if ($items === []) {
                $cards = '<div class="text-muted text-center small py-3 border border-dashed rounded">暂无商机</div>';
            }
            foreach ($items as $it) {
                $it     = (array) $it;
                $amount = is_numeric($it['amount'] ?? null) ? '¥' . number_format((float) $it['amount'], 0) : ($it['amount'] ?? '');
                $prob   = $it['prob'] ?? null;
                $probBar = $prob !== null
                    ? '<div class="progress mt-2" style="height:4px;"><div class="progress-bar bg-' . $color . '" style="width:' . ((int) $prob) . '%"></div></div>'
                    : '';
                $owner = $it['owner'] ?? '';
                $cards .= '<div class="card xf-deal-card mb-2 border shadow-none">'
                    . '<div class="card-body p-2">'
                    . '<div class="d-flex justify-content-between align-items-start">'
                    . '<span class="fw-medium">' . $this->e($it['title'] ?? '') . '</span>'
                    . '<span class="text-' . $color . ' fw-semibold">' . $this->e($amount) . '</span></div>'
                    . '<div class="small text-muted mt-1">' . $this->e($it['customer'] ?? '') . '</div>'
                    . $probBar
                    . ($owner !== '' ? '<div class="small mt-1"><i class="ti ti-user me-1"></i>' . $this->e($owner) . ($prob !== null ? ' · 赢单率 ' . ((int) $prob) . '%' : '') . '</div>' : '')
                    . '</div></div>';
            }
            $html .= '<div class="col-xl-3 col-lg-4 col-sm-6">'
                . '<div class="card h-100">'
                . '<div class="card-header bg-' . $color . '-subtle d-flex justify-content-between align-items-center py-2">'
                . '<span class="fw-semibold text-' . $color . '"><i class="ti ti-flag-3 me-1"></i>' . $this->e($name) . '</span>'
                . '<span class="badge bg-' . $color . ' rounded-pill">' . count($items) . '</span></div>'
                . '<div class="card-body"><div class="small text-muted mb-2">合计 ¥' . number_format($total, 0) . '</div>' . $cards . '</div>'
                . '</div></div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

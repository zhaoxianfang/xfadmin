<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 指标卡横排（响应式栅格包裹一组 StatCard）
 *
 * 将多个指标卡按「每行 N 个」自动排成响应式栅格，取代手写
 * <div class="row">…<div class="col-md-3">…</div>…</div> 的重复结构。
 * 单卡配置与 StatCard 组件完全一致（title/value/icon/trend...）。
 *
 * XfAdmin::statRow([
 *     'items' => [
 *         ['title' => '总用户', 'value' => '12,480', 'icon' => 'ti ti-users', 'variant' => 'primary'],
 *         ['title' => '今日新增', 'value' => '128', 'icon' => 'ti ti-user-plus', 'variant' => 'success'],
 *         ['title' => '待审核', 'value' => '9', 'icon' => 'ti ti-clock', 'variant' => 'warning'],
 *         ['title' => '封禁', 'value' => '3', 'icon' => 'ti ti-ban', 'variant' => 'danger'],
 *     ],
 *     'cols'  => 4,          // 每行卡片数：2/3/4，自动换算响应式列宽
 *     'gap'   => 'g-3',      // 栅格间距工具类
 * ])
 */
class StatRow extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items' => [],             // 指标卡配置数组（每项同 StatCard 组件参数）
            'cols'  => 4,              // 每行卡片数（2~4，自动换算 col-12 col-md-{12/cols}）
            'gap'   => 'g-3',          // 栅格间距工具类（Bootstrap row gap）
            'class' => null,           // 附加到外层 row 的 class
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        if ($items === []) {
            return '';
        }
        $cols = max(2, min(4, (int) $this->get('cols', 4)));
        $span = (int) (12 / $cols);    // 中等屏以上每卡列宽，手机恒占满行

        $html = '<div'.$this->attrs(['class' => Html::cls('row', $this->get('gap'), $this->get('class'))]).'>';
        foreach ($items as $item) {
            $item = (array) $item;
            // 尊重单卡自带 width；否则按 cols 自动换算响应式列宽
            if (empty($item['width'])) {
                $item['width'] = ['md' => $span];
            }
            $html .= (string) StatCard::make($item);
        }
        $html .= '</div>';

        return $html;
    }
}

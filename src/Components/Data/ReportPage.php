<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 报表页（报表类页面抽象）
 *
 * 顶部筛选栏 + 图表区（可多列）+ 底部数据表，典型的后台「数据分析 / 报表」页面。
 *
 * XfAdmin::reportPage([
 *     'title'    => '销售报表',
 *     'filters'  => XfAdmin::form([...]),                 // 筛选栏表单
 *     'charts'   => [
 *         ['title' => '趋势', 'width' => 12, 'body' => XfAdmin::apexChart([...])],
 *         ['title' => '构成', 'width' => 6,  'body' => XfAdmin::apexChart([...])],
 *         ['title' => '排行', 'width' => 6,  'body' => XfAdmin::apexChart([...])],
 *     ],
 *     'table'    => XfAdmin::dataTable([...]),            // 底部明细表
 * ])
 */
class ReportPage extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'   => '报表',
            'filters' => '',
            'charts'  => [],
            'table'   => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $title   = $this->e($this->get('title', '报表'));
        $filters = $this->raw($this->get('filters', ''));
        $charts  = (array) $this->get('charts', []);
        $table   = $this->raw($this->get('table', ''));

        $html = '<div class="d-flex justify-content-between align-items-center mb-3">'
            . '<h4 class="mb-0">' . $title . '</h4></div>';

        if ($filters !== '') {
            $html .= '<div class="card mb-3"><div class="card-body">' . $filters . '</div></div>';
        }
        if (! empty($charts)) {
            $html .= '<div class="row g-3">';
            foreach ($charts as $c) {
                $c = (array) $c;
                $width = (int) ($c['width'] ?? 12);
                $body  = $this->raw($c['body'] ?? '');
                $ctitle = $this->e($c['title'] ?? '');
                $html .= '<div class="col-xl-' . $width . '">'
                    . '<div class="card h-100"><div class="card-header"><h6 class="mb-0">' . $ctitle . '</h6></div>'
                    . '<div class="card-body">' . $body . '</div></div></div>';
            }
            $html .= '</div>';
        }
        if ($table !== '') {
            $html .= '<div class="mt-3">' . $table . '</div>';
        }
        return $html;
    }
}

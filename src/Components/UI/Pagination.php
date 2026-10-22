<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 分页
 *
 * XfAdmin::pagination([
 *     'total'    => 200,        // 总条数
 *     'per_page' => 10,
 *     'current'  => 3,
 *     'url'      => '/users?page={page}',   // {page} 占位
 *     'size'     => null,       // sm | lg
 *     'align'    => 'center',   // start | center | end
 *     'rounded'  => false,
 * ])
 */
class Pagination extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'total' => 0,
            'per_page' => 15,
            'current' => 1,
            'url' => '?page={page}',
            'window' => 2,
            'size' => null,
            'align' => 'center',
            'rounded' => false,
            'arrows' => true,
            'aria_label' => null,   // 分页导航无障碍标签（默认「分页导航」，可自定义如「用户列表分页」）
        ];
    }

    /**
     * link（protected实例方法）
     *
     * @param  int  $page  page
     * @return string result
     */
    protected function link(int $page): string
    {
        return str_replace('{page}', (string) $page, (string) $this->safeUrl($this->get('url')));
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $total = (int) $this->get('total');
        $perPage = max(1, (int) $this->get('per_page'));
        $pages = max(1, (int) ceil($total / $perPage));
        $current = min(max(1, (int) $this->get('current')), $pages);
        // window 夹紧下界 0：负数窗口会导致 start>end，出现双省略号且当前页从窗口消失
        $window = max(0, (int) $this->get('window'));

        $ulClass = Html::cls('pagination', [
            'pagination-rounded' => $this->get('rounded'),
            'justify-content-center' => $this->get('align') === 'center',
            'justify-content-end' => $this->get('align') === 'end',
        ], ($this->get('size') && ($pz = $this->enum($this->get('size'), self::ENUM_SIZE, '')) !== '') ? 'pagination-'.$pz : '');

        $item = function (string $label, ?int $page, bool $active = false, bool $disabled = false): string {
            $li = '<li class="'.Html::cls('page-item', ['active' => $active, 'disabled' => $disabled]).'">';
            if ($disabled || $page === null) {
                $li .= '<span class="page-link">'.$label.'</span>';
            } else {
                // 当前页链接声明 aria-current=page，供屏幕阅读器识别「你在此页」
                $aria = $active ? ' aria-current="page"' : '';
                $li .= '<a class="page-link" href="'.$this->escape($this->link($page)).'"'.$aria.'>'.$label.'</a>';
            }

            return $li.'</li>';
        };

        // 无障碍：为分页导航容器声明 aria-label，使屏幕阅读器能区分页面上多个 nav 区域
        $ariaLabel = $this->get('aria_label') ?? '分页导航';
        $html = '<nav'.$this->attrs(['aria-label' => $ariaLabel]).'><ul class="'.$ulClass.'">';
        if ($this->get('arrows')) {
            $html .= $item('<i class="ti ti-chevron-left"></i>', $current - 1, false, $current <= 1);
        }
        $start = max(1, $current - $window);
        $end = min($pages, $current + $window);
        if ($start > $end) {
            // 兜底：窗口异常（start>end）时退化为仅渲染当前页，不再输出省略号
            $start = $end = $current;
        }
        if ($start > 1) {
            $html .= $item('1', 1);
            if ($start > 2) {
                $html .= $item('…', null, false, true);
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            $html .= $item((string) $i, $i, $i === $current);
        }
        if ($end < $pages) {
            if ($end < $pages - 1) {
                $html .= $item('…', null, false, true);
            }
            $html .= $item((string) $pages, $pages);
        }
        if ($this->get('arrows')) {
            $html .= $item('<i class="ti ti-chevron-right"></i>', $current + 1, false, $current >= $pages);
        }

        return $html.'</ul></nav>';
    }
}

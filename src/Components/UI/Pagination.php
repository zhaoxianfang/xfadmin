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
    protected function defaults(): array
    {
        return [
            'total'    => 0,
            'per_page' => 15,
            'current'  => 1,
            'url'      => '?page={page}',
            'window'   => 2,
            'size'     => null,
            'align'    => 'center',
            'rounded'  => false,
            'arrows'   => true,
        ];
    }

    protected function link(int $page): string
    {
        return str_replace('{page}', (string) $page, (string) $this->get('url'));
    }

    protected function html(): string
    {
        $total   = (int) $this->get('total');
        $perPage = max(1, (int) $this->get('per_page'));
        $pages   = max(1, (int) ceil($total / $perPage));
        $current = min(max(1, (int) $this->get('current')), $pages);
        $window  = (int) $this->get('window');

        $ulClass = Html::cls('pagination', [
            'pagination-rounded' => $this->get('rounded'),
            'justify-content-center' => $this->get('align') === 'center',
            'justify-content-end'    => $this->get('align') === 'end',
        ], $this->get('size') ? 'pagination-' . $this->get('size') : '');

        $item = function (string $label, ?int $page, bool $active = false, bool $disabled = false): string {
            $li = '<li class="' . Html::cls('page-item', ['active' => $active, 'disabled' => $disabled]) . '">';
            $li .= $disabled || $page === null
                ? '<span class="page-link">' . $label . '</span>'
                : '<a class="page-link" href="' . $this->e($this->link($page)) . '">' . $label . '</a>';

            return $li . '</li>';
        };

        $html = '<nav' . $this->attrs() . '><ul class="' . $ulClass . '">';
        if ($this->get('arrows')) {
            $html .= $item('<i class="ti ti-chevron-left"></i>', $current - 1, false, $current <= 1);
        }

        $start = max(1, $current - $window);
        $end   = min($pages, $current + $window);
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

        return $html . '</ul></nav>';
    }
}

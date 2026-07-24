<?php

declare(strict_types=1);

namespace XfAdmin\Components\UI;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 抽屉（Offcanvas）
 *
 * XfAdmin::offcanvas([
 *     'id'        => 'filter-panel',
 *     'title'     => '筛选',
 *     'body'      => $form,
 *     'placement' => 'end',      // start | end | top | bottom
 *     'backdrop'  => true,
 *     'trigger'   => '打开筛选',
 * ])
 */
class Offcanvas extends Component
{
    protected function defaults(): array
    {
        return [
            'title'     => null,
            'body'      => '',
            'placement' => 'end',
            'backdrop'  => true,
            'scroll'    => false,
            'trigger'   => null,
            'trigger_variant' => 'primary',
        ];
    }

    protected function html(): string
    {
        $id   = $this->resolveId('xf-offcanvas');
        $html = '';

        if ($this->get('trigger') !== null) {
            $html .= '<button class="btn btn-' . $this->e($this->get('trigger_variant')) . '" type="button" data-bs-toggle="offcanvas" data-bs-target="#' . $this->e($id) . '">'
                . $this->e($this->get('trigger')) . '</button>';
        }

        $attrs = [
            'class'    => 'offcanvas offcanvas-' . $this->get('placement'),
            'tabindex' => '-1',
            'id'       => $id,
        ];
        if (! $this->get('backdrop')) {
            $attrs['data-bs-backdrop'] = 'false';
        }
        if ($this->get('scroll')) {
            $attrs['data-bs-scroll'] = 'true';
        }

        $html .= '<div' . $this->attrs($attrs) . '>';
        $html .= '<div class="offcanvas-header"><h5 class="offcanvas-title">' . $this->raw($this->get('title')) . '</h5>'
            . '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>';
        $html .= '<div class="offcanvas-body">' . $this->raw($this->get('body')) . '</div>';
        $html .= '</div>';

        return $html;
    }
}

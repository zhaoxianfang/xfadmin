<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 图标（Tabler webfont / Lucide SVG，模板内置两套图标库）
 *
 * XfAdmin::icon(['name' => 'home'])                              // Tabler: ti ti-home
 * XfAdmin::icon(['name' => 'settings', 'lib' => 'lucide'])       // Lucide SVG（data-lucide）
 * XfAdmin::icon(['name' => 'bell', 'size' => 'fs-24', 'color' => 'text-primary'])
 */
class Icon extends Component
{
    protected function defaults(): array
    {
        return [
            'name'  => '',
            'lib'   => 'tabler',   // tabler | lucide
            'size'  => null,       // fs-* 类
            'color' => null,       // text-* 类
        ];
    }

    protected function assets(): array
    {
        // Lucide SVG 图标需要 lucide.min.js（xfadmin.js 会调用 lucide.createIcons() 增量渲染）
        return $this->get('lib') === 'lucide' ? ['lucide'] : [];
    }

    protected function html(): string
    {
        $class = Html::cls($this->get('size'), $this->get('color'));

        if ($this->get('lib') === 'lucide') {
            return '<i' . $this->attrs(['data-lucide' => $this->get('name'), 'class' => $class]) . '></i>';
        }

        return '<i' . $this->attrs(['class' => Html::cls('ti ti-' . $this->get('name'), $class)]) . '></i>';
    }
}

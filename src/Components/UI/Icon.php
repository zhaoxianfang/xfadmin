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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'name' => '',
            'lib' => 'tabler',   // tabler | lucide
            'size' => null,       // fs-* 类
            'color' => null,       // text-* 类
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        // Lucide SVG 图标需要 lucide.min.js（xfadmin.js 会调用 lucide.createIcons() 增量渲染）
        return $this->get('lib') === 'lucide' ? ['lucide'] : [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $class = Html::cls($this->get('size'), $this->get('color'));

        if ($this->get('lib') === 'lucide') {
            return '<i'.$this->attrs(['data-lucide' => $this->get('name'), 'class' => $class]).'></i>';
        }
        // name 限 ti/图标类名格式（字母数字连字符/下划线），非法值置空，防止拼接进 class 的注入
        $name = preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/', (string) $this->get('name')) === 1
            ? (string) $this->get('name')
            : '';

        return '<i'.$this->attrs(['class' => Html::cls('ti ti-'.$name, $class)]).'></i>';
    }
}

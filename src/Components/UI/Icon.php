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
            'title' => null,       // 无障碍名：提供后图标视为「有含义图像」(role=img + aria-label)，否则视为装饰图标(aria-hidden)
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
            $iconAttrs = ['data-lucide' => $this->get('name'), 'class' => $class];
        } else {
            // name 限 ti/图标类名格式（字母数字连字符/下划线），非法值置空，防止拼接进 class 的注入
            $name = preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/', (string) $this->get('name')) === 1
                ? (string) $this->get('name')
                : '';
            $iconAttrs = ['class' => Html::cls('ti ti-'.$name, $class)];
        }
        // 无障碍：提供 title 时图标成为有含义图像（role=img + aria-label），否则视为装饰图标（aria-hidden）
        if (($title = $this->get('title')) !== null && $title !== '') {
            $iconAttrs['role'] = 'img';
            $iconAttrs['aria-label'] = (string) $title;
        } else {
            $iconAttrs['aria-hidden'] = 'true';
        }

        return '<i'.$this->attrs($iconAttrs).'></i>';
    }
}

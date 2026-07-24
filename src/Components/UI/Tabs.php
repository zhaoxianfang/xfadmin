<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 选项卡（tabs / pills / 垂直 / 图标 / 淡入动画）
 *
 * XfAdmin::tabs([
 *     'style' => 'tabs',                 // tabs | pills | underline
 *     'items' => [
 *         ['title' => '基本信息', 'icon' => 'ti ti-home', 'content' => '...', 'active' => true],
 *         ['title' => '安全设置', 'content' => $form],
 *     ],
 *     'justified' => false,
 *     'vertical'  => false,
 *     'fade'      => true,
 * ])
 */
class Tabs extends Component
{
    protected function defaults(): array
    {
        return [
            'style'     => 'tabs',
            'items'     => [],
            'justified' => false,
            'vertical'  => false,
            'fade'      => true,
        ];
    }

    protected function html(): string
    {
        $items = array_values((array) $this->get('items', []));
        if ($items !== [] && ! array_filter($items, fn ($i) => ! empty($i['active']))) {
            $items[0]['active'] = true;
        }

        $navClass = Html::cls('nav', 'nav-' . $this->get('style'), [
            'nav-justified' => $this->get('justified'),
            'flex-column'   => $this->get('vertical'),
        ]);

        $nav  = '<ul class="' . $navClass . '" role="tablist">';
        $pane = '';
        foreach ($items as $item) {
            $paneId = $item['id'] ?? $this->uid('xf-tab');
            $active = ! empty($item['active']);
            $icon   = isset($item['icon']) ? '<i class="' . $this->e($item['icon']) . ' me-1"></i>' : '';

            $nav .= '<li class="nav-item" role="presentation">'
                . '<a href="#' . $this->e($paneId) . '" data-bs-toggle="tab" role="tab" class="' . Html::cls('nav-link', ['active' => $active, 'disabled' => ! empty($item['disabled'])]) . '">'
                . $icon . $this->e($item['title'] ?? '') . '</a></li>';

            $pane .= '<div class="' . Html::cls('tab-pane', ['fade' => $this->get('fade'), 'show' => $active && $this->get('fade'), 'active' => $active]) . '" id="' . $this->e($paneId) . '" role="tabpanel">'
                . $this->raw($item['content'] ?? '') . '</div>';
        }
        $nav .= '</ul>';

        $content = '<div class="tab-content pt-3">' . $pane . '</div>';

        if ($this->get('vertical')) {
            return '<div' . $this->attrs(['class' => 'd-flex gap-3']) . '><div>' . $nav . '</div><div class="flex-grow-1">' . $content . '</div></div>';
        }

        return '<div' . $this->attrs() . '>' . $nav . $content . '</div>';
    }
}

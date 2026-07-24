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
 *     'badge'     => 每个 item 可带 'badge' => '新' 或 ['text' => '谨慎', 'class' => 'bg-warning'],
 *     'footer'    => '<button ...>提交</button>',   // 整个 tab-content 下方的公共区域（如统一提交按钮）
 *     'form'      => ['action' => '/save', 'method' => 'POST'],  // 用表单包裹全部面板 + footer（多标签一次提交）
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
            'footer'    => null,
            'form'      => null,
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
            $badge  = '';
            if (! empty($item['badge'])) {
                $b     = is_array($item['badge']) ? $item['badge'] : ['text' => $item['badge']];
                $badge = ' <span class="badge ' . $this->e($b['class'] ?? 'bg-danger-subtle text-danger') . '">' . $this->e($b['text'] ?? '') . '</span>';
            }

            $nav .= '<li class="nav-item" role="presentation">'
                . '<a href="#' . $this->e($paneId) . '" data-bs-toggle="tab" role="tab" class="' . Html::cls('nav-link', ['active' => $active, 'disabled' => ! empty($item['disabled'])]) . '">'
                . $icon . $this->e($item['title'] ?? '') . $badge . '</a></li>';

            $pane .= '<div class="' . Html::cls('tab-pane', ['fade' => $this->get('fade'), 'show' => $active && $this->get('fade'), 'active' => $active]) . '" id="' . $this->e($paneId) . '" role="tabpanel">'
                . $this->raw($item['content'] ?? '') . '</div>';
        }
        $nav .= '</ul>';

        $content = '<div class="tab-content pt-3">' . $pane . '</div>';

        // 公共底部区域（如统一提交按钮）
        $footer = $this->get('footer');
        $footerHtml = $footer !== null && $footer !== ''
            ? '<div class="xf-tabs-footer border-top pt-3 mt-2">' . $this->raw($footer) . '</div>'
            : '';

        // 表单包裹：全部面板字段 + footer 一次提交
        $form = $this->get('form');
        if (is_array($form)) {
            $body = '<form' . Html::attrs(array_filter([
                'action'  => $form['action'] ?? '',
                'method'  => strtoupper($form['method'] ?? 'POST') === 'GET' ? 'GET' : 'POST',
                'id'      => $form['id'] ?? null,
                'data-xf' => ! empty($form['ajax']) ? 'form' : null,
            ], fn ($v) => $v !== null)) . '>' . ($form['hidden'] ?? '') . $content . $footerHtml . '</form>';
        } else {
            $body = $content . $footerHtml;
        }

        if ($this->get('vertical')) {
            return '<div' . $this->attrs(['class' => 'd-flex gap-3']) . '><div>' . $nav . '</div><div class="flex-grow-1">' . $body . '</div></div>';
        }

        return '<div' . $this->attrs() . '>' . $nav . $body . '</div>';
    }
}

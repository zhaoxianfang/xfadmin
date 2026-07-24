<?php

declare(strict_types=1);

namespace XfAdmin\Components\Layout;

use XfAdmin\Components\Component;

/**
 * 页面标题 + 面包屑
 *
 * XfAdmin::pageTitle([
 *     'title'      => '用户管理',
 *     'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '系统'], ['text' => '用户管理', 'active' => true]],
 *     'actions'    => '右侧自定义HTML（可选，替代面包屑）',
 * ])
 */
class PageTitle extends Component
{
    protected function defaults(): array
    {
        return [
            'title'      => '',
            'breadcrumb' => [],
            'actions'    => null,
        ];
    }

    protected function html(): string
    {
        $html  = '<div' . $this->attrs(['class' => 'page-title-head d-flex align-items-center']) . '>';
        $html .= '<div class="flex-grow-1"><h4 class="fs-sm text-uppercase fw-bold m-0">' . $this->e($this->get('title')) . '</h4></div>';
        $html .= '<div class="text-end">';

        if ($this->get('actions') !== null) {
            $html .= $this->raw($this->get('actions'));
        } else {
            $items = (array) $this->get('breadcrumb', []);
            if ($items !== []) {
                $html .= '<ol class="breadcrumb m-0 py-0">';
                $last = array_key_last($items);
                foreach ($items as $i => $item) {
                    $item   = is_array($item) ? $item : ['text' => $item];
                    $active = ($item['active'] ?? false) || $i === $last;
                    if ($active || empty($item['url'])) {
                        $html .= '<li class="breadcrumb-item' . ($active ? ' active' : '') . '">' . $this->e($item['text'] ?? '') . '</li>';
                    } else {
                        $html .= '<li class="breadcrumb-item"><a href="' . $this->e($item['url']) . '">' . $this->e($item['text'] ?? '') . '</a></li>';
                    }
                }
                $html .= '</ol>';
            }
        }

        $html .= '</div></div>';

        return $html;
    }
}

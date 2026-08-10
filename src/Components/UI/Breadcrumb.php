<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 面包屑（独立使用；页面标题栏内置面包屑见 PageTitle）
 *
 * XfAdmin::breadcrumb(['items' => [['text' => '首页', 'url' => '/'], ['text' => '列表']]])
 */
class Breadcrumb extends Component
{
    protected function defaults(): array
    {
        return [
            'items'   => [],
            'divider' => null,   // 自定义分隔符字符
        ];
    }

    protected function html(): string
    {
        $navAttrs = ['aria-label' => 'breadcrumb'];
        if ($this->get('divider') !== null) {
            // CSS 字符串值：单引号应转义为 \27（非 SQL 的 addslashes），并剔除 ; 防止注入其他声明
            $divider = str_replace([';', "'"], ['', '\\27'], (string) $this->get('divider'));
            $navAttrs['style'] = "--bs-breadcrumb-divider: '" . $divider . "';";
        }

        $items = (array) $this->get('items', []);
        $last  = array_key_last($items);

        $html = '<nav' . $this->attrs($navAttrs) . '><ol class="breadcrumb mb-0">';
        foreach ($items as $i => $item) {
            $item   = is_array($item) ? $item : ['text' => $item];
            $active = ($item['active'] ?? false) || $i === $last;
            $html  .= $active || empty($item['url'])
                ? '<li class="breadcrumb-item active" aria-current="page">' . $this->e($item['text'] ?? '') . '</li>'
                : '<li class="breadcrumb-item"><a href="' . $this->e($item['url']) . '">' . $this->e($item['text'] ?? '') . '</a></li>';
        }

        return $html . '</ol></nav>';
    }
}

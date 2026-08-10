<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 空状态占位（pages-empty.html / pages-search-results.html 无结果）
 *
 * XfAdmin::emptyState([
 *     'icon'   => 'ti ti-inbox',
 *     'image'  => null,               // 或用图片替代图标
 *     'title'  => '暂无数据',
 *     'text'   => '当前还没有任何记录',
 *     'action' => '<a href="#" class="btn btn-primary">新建</a>',
 * ])
 */
class EmptyState extends Component
{
    protected function defaults(): array
    {
        return [
            'icon'   => 'ti ti-inbox',
            'image'  => null,
            'title'  => '暂无数据',
            'text'   => null,
            'action' => null,
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'text-center py-5']) . '>';
        if ($this->get('image')) {
            $html .= '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $this->get('image'))) . '" alt="" style="max-height:180px;" class="mb-3">';
        } elseif ($this->get('icon')) {
            $html .= '<i class="' . $this->e($this->get('icon')) . '" style="font-size:64px;line-height:1;" ></i>';
        }
        $html .= '<h4 class="mt-3">' . $this->e($this->get('title')) . '</h4>';
        if ($this->get('text')) {
            $html .= '<p class="text-muted">' . $this->raw($this->get('text')) . '</p>';
        }
        if ($this->get('action')) {
            $html .= '<div class="mt-3">' . $this->raw($this->get('action')) . '</div>';
        }

        return $html . '</div>';
    }
}

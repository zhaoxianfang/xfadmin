<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 空状态（无数据占位卡片，支持图标/标题/描述/操作区）
 *
 * 注意：类名用 EmptyState（empty 是 PHP 保留字）；对外仍通过别名 empty 调用：
 *   XfAdmin::empty([
 *     'icon'  => 'ti ti-package',                 // Tabler 图标
 *     'title' => '暂无数据',
 *     'text'  => '还没有任何记录',
 *     'action'=> XfAdmin::button([...]),          // 操作区（原样输出 HTML）
 *     'size'  => '',                              // '' | 'sm' | 'lg'
 *   ])
 */
class EmptyState extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'icon'  => 'ti ti-package',
            'title' => '暂无数据',
            'text'  => '',
            'action' => '',
            'size'  => '',
            'id'    => null,
            'class' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-empty');

        $iconSize = $this->get('size') !== '' ? ' xf-empty-icon-' . $this->get('size') : '';

        $action = $this->get('action') !== '' && $this->get('action') !== null ? '<div class="xf-empty-action mt-3">' . $this->raw($this->get('action')) . '</div>' : '';

        return '<div' . $this->attrs([
            'id'    => $id,
            'class' => 'xf-empty text-center py-5'
                . ($this->get('class') !== '' ? ' ' . $this->get('class') : ''),
        ]) . '>'
            . '<div class="xf-empty-icon' . $iconSize . '"><i class="' . $this->e($this->get('icon')) . '"></i></div>'
            . '<h6 class="xf-empty-title mt-3 mb-1">' . $this->e($this->get('title')) . '</h6>'
            . ($this->get('text') !== '' ? '<p class="xf-empty-text text-muted small">' . $this->e($this->get('text')) . '</p>' : '')
            . $action
            . '</div>';
    }
}

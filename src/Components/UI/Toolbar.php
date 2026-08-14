<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 工具栏（页面操作区容器：左右分栏、自动换行、间距统一）
 *
 * XfAdmin::toolbar([
 *     'left'  => XfAdmin::button([...]) . XfAdmin::searchBox([...]),  // 左区（原样 HTML）
 *     'right' => XfAdmin::button([...]),                              // 右区（原样 HTML）
 *     'class' => '',
 * ])
 */
class Toolbar extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'left'   => '',
            'right'  => '',
            'class'  => '',
            'id'     => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-toolbar');

        $left = $this->get('left') !== '' && $this->get('left') !== null ? '<div class="xf-toolbar-left d-flex align-items-center gap-2 flex-wrap">' . $this->raw($this->get('left')) . '</div>' : '';
        $right = $this->get('right') !== '' && $this->get('right') !== null ? '<div class="xf-toolbar-right d-flex align-items-center gap-2 flex-wrap">' . $this->raw($this->get('right')) . '</div>' : '';

        if ($left === '' && $right === '') {
            return '';
        }
        return '<div' . $this->attrs([
            'id'    => $id,
            'class' => 'xf-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2'
                . ($this->get('class') !== '' ? ' ' . $this->get('class') : ''),
        ]) . '>' . $left . $right . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 模块子导航 / 分段标签导航
 *
 * 用于后台「企业应用」等模块的页面内切换导航，把原先散落在各控制器里
 * 重复拼接的 <ul class="nav nav-pills"> 循环提炼为可复用组件。
 * 支持两种形态：
 *   1) 扁平列表：items = [['label'=>.., 'url'=>.., 'active'=>true, 'icon'=>..], ...]
 *   2) 分组（带分区标题）：sections = [['title'=>.., 'items'=>[...]], ...]
 *
 * XfAdmin::moduleNav([
 *     'items'   => [
 *         ['label' => '概览', 'url' => '/admin/app/crm', 'active' => true],
 *         ['label' => '线索', 'url' => '/admin/app/crm/leads'],
 *     ],
 *     'type'    => 'pills',          // pills(默认) | tabs | underline
 *     'align'   => 'start',          // start(默认) | center | end
 *     'class'   => 'xf-module-subnav',
 * ])
 */
class ModuleNav extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'sections' => [],
            'type'     => 'pills',   // pills | tabs | underline
            'align'    => 'start',   // start | center | end
            'class'    => 'xf-module-subnav',
            'id'       => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $type  = $this->enum($this->get('type'), ['pills', 'tabs', 'underline'], 'pills');
        $align = $this->enum($this->get('align'), ['start', 'center', 'end'], 'start');

        $items = $this->get('items');
        if (! is_array($items)) {
            $items = [];
        }
        $sections = $this->get('sections');
        if (! is_array($sections)) {
            $sections = [];
        }
        // 若只给了一维 items，自动包成单个 section（无标题）
        if ($sections === [] && $items !== []) {
            $sections = [['title' => '', 'items' => $items]];
        }
        $navClass = 'nav nav-' . $type;
        if ($align === 'center') {
            $navClass .= ' justify-content-center';
        } elseif ($align === 'end') {
            $navClass .= ' justify-content-end';
        }
        $out = '';
        foreach ($sections as $sec) {
            $secItems = $sec['items'] ?? [];
            $links    = '';
            foreach ($secItems as $it) {
                $active = ! empty($it['active']) ? ' active' : '';
                $icon   = ! empty($it['icon']) ? '<i class="' . $this->e($it['icon']) . ' me-1"></i>' : '';
                $links .= '<a class="nav-link' . $active . '" href="' . $this->e($it['url'] ?? '#') . '">'
                    . $icon . $this->e($it['label'] ?? '') . '</a>';
            }
            if (($sec['title'] ?? '') !== '') {
                $out .= '<div class="text-muted small fw-semibold mb-1">' . $this->e($sec['title']) . '</div>';
            }
            $out .= '<ul class="' . $navClass . ' mb-3 ' . $this->e($this->get('class')) . '">' . $links . '</ul>';
        }
        return $out;
    }
}

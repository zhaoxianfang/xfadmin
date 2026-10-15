<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 页面标题 + 面包屑
 *
 * XfAdmin::pageTitle([
 *     'title'      => '用户管理',
 *     'icon'       => 'ti ti-user',                       // 标题前导图标
 *     'subtitle'   => '用户列表与权限管理',                // 标题下方描述
 *     'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '系统'], ['text' => '用户管理', 'active' => true]],
 *     'actions'    => '右侧自定义HTML（可选，替代面包屑）',
 *     'container'  => 'fluid',                            // fluid | boxed | false（不包裹）
 *     'align'      => 'start',                            // start | center | end
 * ])
 */
class PageTitle extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title' => '',
            'breadcrumb' => [],
            'actions' => null,
            'icon' => '',          // 标题前导图标（ti 图标 class，如 'ti ti-user'）
            'subtitle' => '',      // 标题下方描述文案（small muted）
            'container' => false, // 外层包裹容器：fluid=container-fluid / boxed=container / false=不包裹（默认不包裹，避免与父级容器重复嵌套）
            'align' => 'start',    // 水平对齐：start | center | end
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $align = $this->enum($this->get('align'), ['start', 'center', 'end'], 'start');
        $alignCls = $align === 'center' ? 'justify-content-center text-center'
            : ($align === 'end' ? 'justify-content-end text-end' : '');

        $inner = '<div class="flex-grow-1">';
        if ($icon = $this->iconClass($this->get('icon'))) {
            $inner .= '<i class="'.$icon.' me-2 fs-20 align-middle"></i>';
        }
        $inner .= '<h4 class="fs-sm text-uppercase fw-bold m-0">'.$this->e($this->get('title')).'</h4>';
        if (($sub = (string) $this->get('subtitle')) !== '') {
            $inner .= '<p class="text-muted small mb-0 mt-1">'.$this->e($sub).'</p>';
        }
        $inner .= '</div>';

        $inner .= '<div class="text-end">';
        if ($this->get('actions') !== null) {
            $inner .= $this->raw($this->get('actions'));
        } else {
            $items = (array) $this->get('breadcrumb', []);
            if ($items !== []) {
                $inner .= '<ol class="breadcrumb m-0 py-0">';
                $last = array_key_last($items);
                foreach ($items as $i => $item) {
                    $item = is_array($item) ? $item : ['text' => $item];
                    $active = ($item['active'] ?? false) || $i === $last;
                    if ($active || empty($item['url'])) {
                        $inner .= '<li class="breadcrumb-item'.($active ? ' active' : '').'">'.$this->e($item['text'] ?? '').'</li>';
                    } else {
                        $inner .= '<li class="breadcrumb-item"><a href="'.$this->e($this->safeUrl($item['url'])).'">'.$this->e($item['text'] ?? '').'</a></li>';
                    }
                }
                $inner .= '</ol>';
            }
        }
        $inner .= '</div>';

        $head = '<div'.$this->attrs(['class' => 'page-title-head d-flex align-items-center '.$alignCls]).'>'.$inner.'</div>';

        // container=false 时不包裹（用于已在外层 container 内复用的场景）
        $container = $this->get('container');
        if ($container === false) {
            return $head;
        }
        $wrap = $this->enum($container, ['fluid', 'boxed'], 'fluid');

        return '<div class="'.($wrap === 'boxed' ? 'container' : 'container-fluid').'">'.$head.'</div>';
    }
}

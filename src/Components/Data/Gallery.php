<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 图片画廊 / 作品集（pages-gallery.html）—— 支持 masonry 瀑布流与 lightbox 灯箱
 *
 * XfAdmin::gallery([
 *     'items' => [
 *         ['src' => 'images/01.jpg', 'thumb' => 'images/01.jpg', 'title' => '项目A', 'caption' => '说明', 'group' => 'design'],
 *         ['src' => 'images/02.jpg', 'title' => '项目B', 'group' => 'photo'],
 *     ],
 *     'masonry'  => true,        // 瀑布流布局（需 masonry 插件）
 *     'lightbox' => true,        // 点击放大（需 glightbox）
 *     'cols'     => 3,           // 非 masonry 时的栅格列数
 *     'ratio'    => '4x3',       // 非 masonry 时缩略图比例
 * ])
 */
class Gallery extends Component
{
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'masonry'  => true,
            'lightbox' => true,
            'cols'     => 3,
            'ratio'    => '4x3',
            'gap'      => '12px',
            'filter'   => [],       // ['all' => '全部', 'design' => '设计', ...] 过滤分组
        ];
    }

    protected function assets(): array
    {
        $assets = [];
        if ($this->get('masonry')) {
            $assets[] = 'masonry';
        }
        if ($this->get('lightbox')) {
            $assets[] = 'glightbox';
        }

        return $assets;
    }

    protected function html(): string
    {
        $id     = $this->resolveId('gallery');
        $items  = array_values((array) $this->get('items', []));
        $masonry = (bool) $this->get('masonry');
        $lightbox = (bool) $this->get('lightbox');
        $gap    = $this->e($this->get('gap'));
        $filter = (array) $this->get('filter', []);

        $html = '<div' . $this->attrs(['class' => 'xf-gallery', 'id' => $id])
            . ($lightbox ? ' data-xf="lightbox" data-xf-config="' . $this->e(json_encode(['selector' => '[data-gallery="' . $id . '"]'])) . '"' : '') . '>';

        // 过滤栏
        if (! empty($filter)) {
            $html .= '<div class="mb-3 d-flex flex-wrap gap-2 xf-gallery-filter">';
            foreach ($filter as $key => $label) {
                $active = $key === array_key_first($filter) ? ' active' : '';
                $html .= '<button type="button" class="btn btn-sm btn-soft-primary' . $active . '" data-filter="' . $this->e($key) . '">' . $this->e($label) . '</button>';
            }
            $html .= '</div>';
        }

        $wallCls = $masonry ? 'xf-gallery-masonry row' : 'row g-3 xf-gallery-grid';
        $html .= '<div class="' . $wallCls . '" style="--xf-gap:' . $gap . ';">';
        foreach ($items as $it) {
            $it = (array) $it;
            $src   = $it['src'] ?? '';
            $thumb = $it['thumb'] ?? $src;
            $title = $it['title'] ?? '';
            $caption = $it['caption'] ?? '';
            $group = $it['group'] ?? 'all';
            $ratio = $this->e($this->get('ratio'));

            $colCls = $masonry ? '' : $this->colClass((int) $this->get('cols'));
            $html .= '<div class="xf-gallery-item' . $colCls . '" data-group="' . $this->e($group) . '">';
            $html .= '<div class="card overflow-hidden border-0 shadow-sm">';
            $inner = '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim($src, '/'))) . '" class="card-img-top" loading="lazy" alt="' . $this->e($title) . '">'
                . '<div class="card-img-overlay d-flex align-items-end p-2 xf-gallery-overlay">'
                . '<div class="text-white"><div class="fw-semibold">' . $this->e($title) . '</div>'
                . ($caption ? '<small>' . $this->e($caption) . '</small>' : '') . '</div></div>';
            if ($lightbox && $src) {
                $html .= '<a href="' . $this->e(XfAdmin::asset('images/' . ltrim($src, '/'))) . '" class="glightbox" data-gallery="' . $id . '" data-title="' . $this->e($title) . '">' . $inner . '</a>';
            } else {
                $html .= $inner;
            }
            $html .= '</div></div>';
        }
        $html .= '</div></div>';

        // 初始化脚本（masonry + 过滤）
        if ($masonry || ! empty($filter)) {
            $js = 'XFAdmin.onReady(function(){'
                . 'var root=document.getElementById(' . Html::scriptJson($id) . ');'
                . 'if(!root)return;'
                . 'var grid=root.querySelector(".xf-gallery-masonry,.xf-gallery-grid");'
                . 'var ms=null;';
            if ($masonry) {
                $js .= 'if(window.Masonry){ms=new window.Masonry(grid,{itemSelector:".xf-gallery-item",percentPosition:true,columnWidth:grid.querySelector(".xf-gallery-item")});}';
            }
            if (! empty($filter)) {
                $js .= 'root.querySelectorAll(".xf-gallery-filter [data-filter]").forEach(function(btn){btn.addEventListener("click",function(){'
                    . 'root.querySelectorAll(".xf-gallery-filter [data-filter]").forEach(function(b){b.classList.remove("active");});btn.classList.add("active");'
                    . 'var f=btn.getAttribute("data-filter");'
                    . 'root.querySelectorAll(".xf-gallery-item").forEach(function(it){it.style.display=(f==="all"||it.getAttribute("data-group")===f)?"":"none";});'
                    . 'if(ms&&ms.layout)ms.layout();'
                    . '});});';
            }
            $js .= '});';
            XfAdmin::assets()->inlineJs($js, 'gallery-' . $id);
        }

        return $html;
    }

    private function colClass(int $cols): string
    {
        $map = [1 => 'col-12', 2 => 'col-md-6', 3 => 'col-md-4', 4 => 'col-md-3', 6 => 'col-md-2'];
        $cls = $map[$cols] ?? 'col-md-4';

        return ' ' . $cls;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 图片画廊 / 作品集 —— 严格对齐 INSPINIA v4.1.0 `misc-gallery.html` 的结构与观感
 *
 * INSPINIA 规范结构（本组件的输出蓝本）：
 *   <div class="card">
 *     <div class="card-header">                          ← 搜索框（app-search）+ 分类筛选（filter-buttons，btn-ghost-primary）
 *     <div class="card-body">
 *       <div class="row row-cols-... g-2">               ← 响应式等列栅格
 *         <div class="col" data-category="...">          ← 每张图一个 col
 *           <div class="card border-0 mb-0">
 *             <div class="badge text-bg-dark badge-label ...">分类</div>   ← 左上角分类角标
 *             <a class="image-popup"><img class="card-img rounded-2"></a> ← 灯箱 + 圆角图
 *
 * XfAdmin::gallery([
 *     'items' => [
 *         ['src' => 'gallery/1.jpg', 'title' => '项目A', 'caption' => '说明', 'group' => 'design'],
 *         ['src' => 'gallery/2.jpg', 'title' => '项目B', 'group' => 'photo'],
 *     ],
 *     'filter'   => ['all' => '全部', 'design' => '设计', 'photo' => '摄影'], // 分类筛选按钮（键 all 表示全部）
 *     'search'   => true,        // 卡片头部搜索框（按标题/说明/分类实时过滤，联动筛选按钮）
 *     'masonry'  => true,        // 瀑布流布局（保留图片原始比例；需 masonry 插件）
 *     'lightbox' => true,        // 点击放大灯箱（glightbox）
 *     'cols'     => 4,           // 桌面端列数（映射为 INSPINIA 同款 row-cols-* 响应式组合）
 *     'ratio'    => '4x3',       // 非 masonry 模式下缩略图裁切比例（如 1x1 / 4x3 / 16x9）
 *     'card'     => true,        // 是否输出外层卡片容器（嵌入已有卡片时可关闭）
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
            'search'   => true,
            'cols'     => 4,
            'ratio'    => '4x3',
            'gap'      => '',       // 兼容旧参数；留空时用 INSPINIA 的 g-2 间距
            'filter'   => [],       // ['all' => '全部', 'design' => '设计', ...]
            'card'     => true,
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
        $id       = $this->resolveId('gallery');
        $items    = array_values((array) $this->get('items', []));
        $masonry  = (bool) $this->get('masonry');
        $lightbox = (bool) $this->get('lightbox');
        $search   = (bool) $this->get('search');
        $filter   = (array) $this->get('filter', []);
        $useCard  = (bool) $this->get('card');

        // 根节点：保留 xf-gallery 标识类；灯箱通过 data-xf 委托给 xfadmin.js 统一初始化
        $rootCls = 'xf-gallery' . ($useCard ? ' card' : '');
        $html    = '<div' . $this->attrs(['class' => $rootCls, 'id' => $id])
            . ($lightbox ? ' data-xf="lightbox" data-xf-config="' . $this->e(json_encode(['selector' => '[data-gallery="' . $id . '"]'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) . '"' : '') . '>';

        // 卡片头部：搜索框 + 分类筛选按钮（对齐 misc-gallery.html 的 card-header 布局）
        if ($search || ! empty($filter)) {
            $html .= '<div class="' . ($useCard ? 'card-header ' : 'mb-3 ') . 'd-flex flex-wrap justify-content-between align-items-center w-100 gap-3">';
            if ($search) {
                // INSPINIA 的 app-search：输入框 + 内嵌搜索图标（图标用 Tabler 替代模板的 lucide）
                $html .= '<div class="flex-grow-1"><div class="app-search">'
                    . '<input type="search" class="form-control" placeholder="搜索..." data-xf="gallery-search" autocomplete="off">'
                    . '<i class="ti ti-search app-search-icon text-muted"></i>'
                    . '</div></div>';
            }
            if (! empty($filter)) {
                // 分类筛选：btn-ghost-primary 幽灵按钮组（对齐模板 filter-buttons）；键 all → data-filter=""（显示全部）
                $html .= '<div class="d-flex flex-wrap gap-1 filter-buttons xf-gallery-filter">';
                $first = true;
                foreach ($filter as $key => $label) {
                    $val  = ($key === 'all' || $key === '') ? '' : (string) $key;
                    $html .= '<button type="button" class="btn btn-sm btn-ghost-primary' . ($first ? ' active' : '') . '" data-filter="' . $this->e($val) . '">' . $this->e($label) . '</button>';
                    $first = false;
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        if ($useCard) {
            $html .= '<div class="card-body">';
        }

        // 栅格：INSPINIA 同款 row-cols 响应式组合；masonry 模式由 JS 接管定位（保留原始图片比例）
        $wallCls = 'row g-2 ' . $this->rowColsClass((int) $this->get('cols'))
            . ($masonry ? ' xf-gallery-masonry' : ' xf-gallery-grid');
        // 非 masonry 时通过 CSS 变量控制缩略图裁切比例（如 4x3 → 4/3）
        $ratioCss = $masonry ? '' : ' style="--xf-ratio:' . $this->e(str_replace('x', '/', (string) $this->get('ratio'))) . ';"';
        $html .= '<div class="' . $wallCls . '"' . $ratioCss . '>';

        foreach ($items as $it) {
            $it      = (array) $it;
            $src     = (string) ($it['src'] ?? '');
            $thumb   = (string) ($it['thumb'] ?? $src);
            $title   = (string) ($it['title'] ?? '');
            $caption = (string) ($it['caption'] ?? '');
            $group   = (string) ($it['group'] ?? '');

            $html .= '<div class="col xf-gallery-item" data-group="' . $this->e($group !== '' ? $group : 'all') . '">';
            // 单项卡片：border-0 + mb-0（间距交给 g-2），对齐模板
            $html .= '<div class="card border-0 mb-0 overflow-hidden position-relative">';

            // 左上角分类角标（badge-label 带小圆点，模板同款）：优先用 filter 映射的中文标签
            if ($group !== '' && $group !== 'all') {
                $badgeText = (string) ($filter[$group] ?? $group);
                $html .= '<div class="badge text-bg-dark badge-label position-absolute top-0 start-0 m-2 z-1">' . $this->e($badgeText) . '</div>';
            }

            // 图片：card-img + rounded-2（模板同款）；标题/说明用悬停渐变浮层展示（增强，不破坏模板观感）
            $inner = '<img src="' . $this->e(XfAdmin::img($thumb)) . '" class="card-img rounded-2" loading="lazy" alt="' . $this->e($title) . '">';
            if ($title !== '' || $caption !== '') {
                $inner .= '<div class="xf-gallery-overlay rounded-2"><div class="text-white">'
                    . ($title !== '' ? '<div class="fw-semibold">' . $this->e($title) . '</div>' : '')
                    . ($caption !== '' ? '<small>' . $this->e($caption) . '</small>' : '')
                    . '</div></div>';
            }

            if ($lightbox && $src !== '') {
                // 灯箱链接：image-popup 为模板类名（便于样式/行为对齐），glightbox 为 xfadmin 实际接线
                $html .= '<a href="' . $this->e(XfAdmin::img($src)) . '" class="image-popup glightbox d-block position-relative" data-gallery="' . $id . '" data-title="' . $this->e($title) . '">' . $inner . '</a>';
            } else {
                $html .= '<div class="position-relative">' . $inner . '</div>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';

        if ($useCard) {
            $html .= '</div>';
        }
        $html .= '</div>';

        // 初始化脚本：masonry 布局 + 分类筛选 + 搜索过滤（三者联动，任一变化后重排）
        if ($masonry || ! empty($filter) || $search) {
            $js = 'XFAdmin.onReady(function(){'
                . 'var root=document.getElementById(' . Html::scriptJson($id) . ');'
                . 'if(!root)return;'
                . 'var grid=root.querySelector(".xf-gallery-masonry,.xf-gallery-grid");'
                . 'var ms=null,q="",f="";';
            if ($masonry) {
                $js .= 'if(window.Masonry&&grid){ms=new window.Masonry(grid,{itemSelector:".xf-gallery-item",percentPosition:true});'
                    . 'grid.querySelectorAll("img").forEach(function(im){im.addEventListener("load",function(){if(ms&&ms.layout)ms.layout();});});}';
            }
            // 统一过滤器：分类（data-group 精确匹配）与关键词（标题/说明/分类模糊匹配）同时满足才显示
            $js .= 'function apply(){root.querySelectorAll(".xf-gallery-item").forEach(function(it){'
                . 'var g=it.getAttribute("data-group")||"";'
                . 'var okF=!f||g===f;'
                . 'var okQ=!q||((it.textContent||"")+" "+g).toLowerCase().indexOf(q)>-1;'
                . 'it.style.display=(okF&&okQ)?"":"none";});'
                . 'if(ms&&ms.layout)ms.layout();}';
            if (! empty($filter)) {
                $js .= 'root.querySelectorAll(".filter-buttons [data-filter]").forEach(function(btn){btn.addEventListener("click",function(){'
                    . 'root.querySelectorAll(".filter-buttons [data-filter]").forEach(function(b){b.classList.remove("active");});btn.classList.add("active");'
                    . 'f=btn.getAttribute("data-filter")||"";apply();'
                    . '});});';
            }
            if ($search) {
                $js .= 'var si=root.querySelector(\'[data-xf="gallery-search"]\');'
                    . 'if(si){si.addEventListener("input",function(){q=si.value.trim().toLowerCase();apply();});}';
            }
            $js .= '});';
            XfAdmin::assets()->inlineJs($js, 'gallery-' . $id);
        }

        return $html;
    }

    /**
     * 列数 → INSPINIA 同款响应式 row-cols 组合。
     * 模板默认为 row-cols-xxl-5 row-cols-lg-4 row-cols-md-3 row-cols-1（即 cols=4 档）。
     */
    private function rowColsClass(int $cols): string
    {
        return match (true) {
            $cols <= 1  => 'row-cols-1',
            $cols === 2 => 'row-cols-1 row-cols-md-2',
            $cols === 3 => 'row-cols-1 row-cols-md-2 row-cols-lg-3',
            $cols === 4 => 'row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5',
            $cols === 5 => 'row-cols-2 row-cols-md-3 row-cols-lg-5',
            default     => 'row-cols-2 row-cols-md-4 row-cols-lg-6',
        };
    }
}

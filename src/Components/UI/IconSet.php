<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 图标集合展示（icons-flags.html / icons-lucide.html / icons-tabler.html）
 *
 * XfAdmin::iconSet([
 *     'set' => 'tabler',  // tabler | flag | lucide
 *     'icons' => ['ti-home', 'ti-user', 'ti-settings', 'ti-search', ...],
 *     'columns' => 6,
 *     'searchable' => true,
 * ])
 */
class IconSet extends Component
{
    protected function defaults(): array
    {
        return [
            'set' => 'tabler',
            'icons' => [],
            'columns' => 6,
            'searchable' => true,
        ];
    }

    protected function html(): string
    {
        $set = (string) $this->get('set', 'tabler');
        $icons = (array) $this->get('icons', []);
        $columns = (int) $this->get('columns', 6);
        $searchable = (bool) $this->get('searchable', true);

        $prefixId = $this->uid('xf-icons');

        $html = '';

        if ($searchable) {
            $html .= '<div class="mb-4"><div class="input-group" style="max-width:400px"><span class="input-group-text"><i class="ti ti-search"></i></span>';
            $html .= '<input type="text" class="form-control" id="' . $this->e($prefixId . '-search') . '" placeholder="搜索图标..." oninput="';
            $html .= 'var q=this.value.toLowerCase(),cards=document.querySelectorAll(\'#' . $this->e($prefixId) . ' .xf-icon-card\');';
            $html .= 'cards.forEach(function(c){c.style.display=q===\'\'||c.querySelector(\'.xf-icon-name\').textContent.toLowerCase().indexOf(q)>-1?\'\':\'none\'})';
            $html .= '"></div></div>';
        }

        $colClass = 'row-cols-sm-' . min(12, max(2, (int) (12 / max(1, min(6, $columns)))));
        $html .= '<div id="' . $this->e($prefixId) . '" class="row row-cols-2 ' . $colClass . ' row-cols-xl-' . (12 / max(1, min(4, (int)($columns / 2)))) . ' g-2">';

        if (empty($icons)) {
            $icons = $this->getDefaultTablerIcons();
        }

        foreach ($icons as $icon) {
            if (is_string($icon)) {
                $name = $icon;
                $cls = match ($set) {
                    'flag' => 'fi fi-' . $name,
                    'lucide' => 'lucide-' . $name,
                    default => 'ti ti-' . $name,
                };
            } else {
                $icon = (array) $icon;
                $name = (string) ($icon['name'] ?? '');
                $cls = (string) ($icon['class'] ?? 'ti ti-' . $name);
            }

            $html .= '<div class="col"><div class="card xf-icon-card border"><div class="card-body text-center py-3">';
            $html .= '<i class="' . $this->e($cls) . ' fs-24 mb-2 d-block"></i>';
            $html .= '<small class="text-muted xf-icon-name d-block text-truncate" title="' . $this->e($name) . '">' . $this->e($name) . '</small>';
            if ($searchable) {
                $html .= '<small class="text-muted xf-icon-name" style="display:none">' . $this->e($cls) . '</small>';
            }
            $html .= '</div></div></div>';
        }

        if (empty($icons)) {
            $html .= '<div class="col-12 text-center text-muted py-4">未指定图标列表</div>';
        }

        $html .= '</div>';

        return $html;
    }

    private function getDefaultTablerIcons(): array
    {
        return [
            'home', 'user', 'settings', 'search', 'bell', 'mail', 'calendar', 'file', 'folder',
            'heart', 'star', 'check', 'x', 'plus', 'minus', 'trash', 'edit', 'eye',
            'lock', 'unlock', 'download', 'upload', 'refresh', 'share', 'bookmark', 'flag',
            'phone', 'device-mobile', 'laptop', 'server', 'database', 'chart-bar', 'chart-line',
            'map-pin', 'truck', 'shopping-cart', 'credit-card', 'building', 'globe', 'camera',
            'video', 'music', 'mic', 'volume', 'sun', 'moon', 'cloud', 'wifi', 'bluetooth',
        ];
    }
}

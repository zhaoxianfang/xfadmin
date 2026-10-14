<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Navigation;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 无限极菜单导航（侧边栏模式）
 *
 * XfAdmin::menu([
 *     'mode'  => 'side',
 *     'current_url' => '/users',      // 可选：自动高亮
 *     'items' => [
 *         ['title' => '导航'],                                    // 分组标题
 *         ['text' => '仪表盘', 'icon' => 'ti ti-layout-dashboard', 'url' => '/', 'badge' => ['text' => '5', 'class' => 'bg-success']],
 *         ['text' => '系统', 'icon' => 'ti ti-settings', 'children' => [
 *             ['text' => '用户管理', 'url' => '/users'],
 *             ['text' => '更多', 'children' => [ ... 无限层级 ... ]],
 *         ]],
 *     ],
 * ])
 */
class Menu extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'mode' => 'side',   // side
            'items' => [],
            'current_url' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = $this->normalizeItems((array) $this->get('items', []));

        return '<ul'.$this->attrs(['class' => 'side-nav']).'>'.$this->renderSideItems($items, 0).'</ul>';
    }

    /**
     * 键名容错归一化：
     *  - text / label 等价；带 url/children/icon 的 'title' 也视作 text（否则 title 是分组标题）
     *  - url / href 等价
     */
    protected function normalizeItems(array $items): array
    {
        foreach ($items as &$item) {
            if (! is_array($item)) {
                continue;
            }
            if (! isset($item['text'])) {
                if (isset($item['label'])) {
                    $item['text'] = $item['label'];
                } elseif (isset($item['title']) && (isset($item['url']) || isset($item['href']) || isset($item['children']) || isset($item['icon']))) {
                    $item['text'] = $item['title'];
                    unset($item['title']);
                }
            }
            if (! isset($item['url']) && isset($item['href'])) {
                $item['url'] = $item['href'];
            }
            if (! empty($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->normalizeItems($item['children']);
            }
        }
        unset($item);

        return $items;
    }

    /** 判断项本身或其后代是否处于激活状态 */
    protected function isActive(array $item): bool
    {
        if (! empty($item['active'])) {
            return true;
        }
        $current = $this->get('current_url');
        if ($current !== null && isset($item['url']) && $item['url'] !== '' && $item['url'] !== '#'
            && rtrim((string) $item['url'], '/') === rtrim((string) $current, '/')) {
            return true;
        }
        foreach ($item['children'] ?? [] as $child) {
            if (! is_array($child)) {
                continue;
            }
            if ($this->isActive($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * badge（protected实例方法）
     *
     * @param  array  $item  item
     * @return string result
     */
    protected function badge(array $item): string
    {
        if (empty($item['badge'])) {
            return '';
        }
        $badge = is_array($item['badge']) ? $item['badge'] : ['text' => $item['badge']];
        $class = $this->badgeClass($badge['class'] ?? 'bg-danger');
        // INSPINIA 侧栏徽标约定：rounded-pill 外观（可通过传入 pill => false 关闭）
        if (($badge['pill'] ?? true) && ! str_contains($class, 'rounded')) {
            $class .= ' rounded-pill';
        }

        return '<span class="badge '.$this->e($class).'">'.$this->e($badge['text'] ?? '').'</span>';
    }

    /**
     * badge class 白名单净化：仅放行 Bootstrap 上下文色（bg-{variant} / text-{variant}）
     * 与形状类，其余 token 一律丢弃（全部被丢弃时回落 bg-danger）。
     *
     * badge 的 class 来自配置且直接进入 class 属性，需防任意类 / 样式注入
     * （如 position-fixed、自定义 utility 类改变布局）。
     */
    private function badgeClass(mixed $class): string
    {
        $variants = array_merge(self::ENUM_VARIANT, ['body', 'body-secondary', 'body-tertiary', 'body-emphasis', 'reset', 'opacity', 'muted', 'white', 'black', 'black-50', 'white-50']);
        $shapes = ['rounded', 'rounded-pill', 'rounded-circle', 'rounded-0'];
        $keep = [];
        foreach (preg_split('/\s+/', trim((string) $class)) ?: [] as $t) {
            if ($t === '') {
                continue;
            }
            if (in_array($t, $shapes, true) || preg_match('/^(?:bg|text)-(?:'.implode('|', $variants).')$/i', $t) === 1) {
                $keep[] = $t;
            }
        }

        return $keep !== [] ? implode(' ', $keep) : 'bg-danger';
    }

    // ------------------------------------------------------------------
    // side 模式
    // ------------------------------------------------------------------

    /**
     * render Side Items（protected实例方法）
     *
     * @param  array  $items  items
     * @param  int  $level  level
     * @return string result
     */
    protected function renderSideItems(array $items, int $level): string
    {
        $html = '';
        foreach ($items as $item) {
            // 防御：items 元素若为非数组（调用方误传关联数组/标量），跳过避免 TypeError / 偏移访问告警
            if (! is_array($item)) {
                continue;
            }
            // 分组标题
            if (isset($item['title']) && ! isset($item['text'])) {
                $html .= '<li class="side-nav-title">'.$this->e($item['title']).'</li>';

                continue;
            }
            $active = $this->isActive($item);
            $disabled = ! empty($item['disabled']);
            $children = $item['children'] ?? [];
            $icon = isset($item['icon']) ? '<span class="menu-icon"><i class="'.$this->e($item['icon']).'"></i></span>' : '';
            $text = '<span class="menu-text">'.$this->e($item['text'] ?? '').'</span>';

            if ($children !== []) {
                $cid = $item['id'] ?? $this->uid('xf-menu');
                $html .= '<li class="'.Html::cls('side-nav-item', ['active' => $active]).'">';
                $html .= '<a data-bs-toggle="collapse" href="#'.$this->e($cid).'" aria-expanded="'.($active ? 'true' : 'false')
                    .'" aria-controls="'.$this->e($cid).'" class="'.Html::cls('side-nav-link', ['disabled' => $disabled]).'">'
                    .$icon.$text.$this->badge($item).'<span class="menu-arrow"></span></a>';
                $html .= '<div class="'.Html::cls('collapse', ['show' => $active]).'" id="'.$this->e($cid).'">';
                // data-level 供 CSS 按层级递增缩进（支持 6 级及以上子菜单）
                $html .= '<ul class="sub-menu" data-level="'.($level + 1).'">'.$this->renderSideItems($children, $level + 1).'</ul>';
                $html .= '</div></li>';

                continue;
            }
            // url 过 safeUrl 白名单：拦截 javascript: 等伪协议，非法值回落 '#'
            $url = $this->safeUrl($item['url'] ?? '#!', '#');
            $target = isset($item['target']) ? ' target="'.$this->e($item['target']).'"' : '';
            $html .= '<li class="'.Html::cls('side-nav-item', ['active' => $active]).'">'
                .'<a href="'.$this->e($url).'"'.$target
                .' class="'.Html::cls('side-nav-link', ['disabled' => $disabled, 'active' => $active]).'">'
                .$icon.$text.$this->badge($item).'</a></li>';
        }

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 下拉菜单
 *
 * XfAdmin::dropdown([
 *     'text'    => '更多操作',
 *     'variant' => 'light',
 *     'items'   => [
 *         ['text' => '编辑', 'url' => '/edit', 'icon' => 'ti ti-pencil'],
 *         ['divider' => true],
 *         ['header' => '危险操作'],
 *         ['text' => '删除', 'url' => '/del', 'class' => 'text-danger'],
 *     ],
 *     'direction' => 'down',    // down | up | start | end
 *     'align'     => null,      // end => dropdown-menu-end
 *     'split'     => false,
 * ])
 */
class Dropdown extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text'      => '',
            'variant'   => 'light',
            'size'      => null,
            'items'     => [],
            'direction' => 'down',
            'align'     => null,
            'split'     => false,
            'toggle'    => null,   // 自定义触发器 HTML（完全接管）
            'menu'      => null,   // 自定义菜单内容 HTML
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // 键名容错：text / label 等价（按钮与菜单项同理），url / href 等价
        if (($this->get('text') === '' || $this->get('text') === null) && $this->get('label') !== null) {
            $this->options['text'] = $this->get('label');
        }
        $items = (array) $this->get('items', []);
        foreach ($items as &$it) {
            // 标量（字符串）容错：非数组项按 text 处理，避免 PHP 8 下标访问致命错误
            if (! is_array($it)) {
                $it = ['text' => (string) $it];
                continue;
            }
            if (! isset($it['text']) && isset($it['label'])) {
                $it['text'] = $it['label'];
            }
            if (! isset($it['url']) && isset($it['href'])) {
                $it['url'] = $it['href'];
            }
        }
        unset($it);
        $this->options['items'] = $items;

        $dirClass = match ($this->get('direction')) {
            'up'    => 'btn-group dropup',
            'start' => 'btn-group dropstart',
            'end'   => 'btn-group dropend',
            default => $this->get('split') ? 'btn-group' : 'dropdown',
        };

        // variant 白名单，防止任意类注入
        $variant = in_array($this->get('variant'), ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link', 'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger', 'outline-warning', 'outline-info', 'outline-light', 'outline-dark'], true)
            ? $this->get('variant')
            : 'light';

        $html = '<div' . $this->attrs(['class' => $dirClass]) . '>';

        if ($this->get('toggle') !== null) {
            $html .= $this->raw($this->get('toggle'));
        } else {
            $btnClass = Html::cls('btn', 'btn-' . $variant, $this->get('size') ? 'btn-' . $this->enum($this->get('size'), self::ENUM_SIZE, 'lg') : '');
            if ($this->get('split')) {
                $html .= '<button type="button" class="' . $btnClass . '">' . $this->e($this->get('text')) . '</button>';
                $html .= '<button type="button" class="' . $btnClass . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"><span class="visually-hidden">Toggle</span></button>';
            } else {
                $html .= '<button type="button" class="' . $btnClass . ' dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . $this->e($this->get('text')) . '</button>';
            }
        }
        $menuClass = Html::cls('dropdown-menu', $this->get('align') === 'end' ? 'dropdown-menu-end' : '');
        $html     .= '<div class="' . $menuClass . '">';
        if ($this->get('menu') !== null) {
            $html .= $this->raw($this->get('menu'));
        } else {
            foreach ((array) $this->get('items', []) as $item) {
                if (! empty($item['divider'])) {
                    $html .= '<div class="dropdown-divider"></div>';
                    continue;
                }
                if (isset($item['header'])) {
                    $html .= '<h6 class="dropdown-header">' . $this->e($item['header']) . '</h6>';
                    continue;
                }
                $icon  = isset($item['icon']) ? '<i class="' . $this->e($item['icon']) . ' me-2"></i>' : '';
                $attrs = Html::attrs(array_filter([
                    'class'   => Html::cls('dropdown-item', $item['class'] ?? '', ['active' => ! empty($item['active']), 'disabled' => ! empty($item['disabled'])]),
                    'href'    => $item['url'] ?? '#!',
                    'onclick' => $item['onclick'] ?? null,
                    'target'  => $item['target'] ?? null,
                ], fn ($v) => $v !== null));
                $html .= '<a' . $attrs . '>' . $icon . $this->e($item['text'] ?? '') . '</a>';
            }
        }
        $html .= '</div></div>';

        return $html;
    }
}

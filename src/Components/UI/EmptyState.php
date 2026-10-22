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
 *     'escape'=> true,                            // text 默认转义（安全）；传 false 可原样输出 HTML（如 <code>/<br>）
 *   ])
 *
 * 安全说明：text 默认经 escape() 转义，避免富文本场景的 XSS；
 * 仅当 text 确为可信的静态 HTML（如迁移提示中的 <code>php artisan migrate</code>）时，才显式传 'escape' => false。
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
            'icon' => 'ti ti-package',
            'title' => '暂无数据',
            'text' => '',
            'action' => '',
            'size' => '',
            'title_tag' => 'h6', // 标题标签：h1~h6 / div / p（语义化）
            'escape' => true, // text 是否转义：true=转义（默认，安全）；false=原样输出 HTML
            'id' => null,
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

        // size 直接拼进 class，走白名单（非法值忽略，不产生 xf-empty-icon-<任意串>）
        $sz = $this->enum($this->get('size'), self::ENUM_SIZE, '');
        $iconSize = $sz !== '' ? ' xf-empty-icon-'.$sz : '';
        $titleTag = $this->enum($this->get('title_tag'), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p'], 'h6');

        $action = $this->get('action') !== '' && $this->get('action') !== null ? '<div class="xf-empty-action mt-3">'.$this->raw($this->get('action')).'</div>' : '';

        // text 是否转义：默认转义（安全）；显式 'escape' => false 时原样输出 HTML（如 <code>/<br>）
        $textHtml = $this->get('escape') ? $this->escape($this->get('text')) : $this->raw($this->get('text'));

        return '<div'.$this->attrs([
            'id' => $id,
            'class' => 'xf-empty text-center py-5'
                .($this->get('class') !== '' ? ' '.$this->get('class') : ''),
        ]).'>'
            .'<div class="xf-empty-icon'.$iconSize.'"><i class="'.$this->iconClass($this->get('icon')).'"></i></div>'
            .'<'.$titleTag.' class="xf-empty-title mt-3 mb-1">'.$this->escape($this->get('title')).'</'.$titleTag.'>'
            .($this->get('text') !== '' ? '<p class="xf-empty-text text-muted small">'.$textHtml.'</p>' : '')
            .$action
            .'</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 操作按钮工具条（列表页 / 表单页右侧或顶部的一组操作按钮）
 *
 * 取代各业务模块手写的 `.sp-actions` / `.btn-group` 拼接，统一按钮间距、
 * 对齐方式与换行策略。支持数组按钮配置（经 button 组件渲染）、
 * 原生 HTML 字符串或组件实例混排。
 *
 * XfAdmin::actionBar(['items' => [['text' => '规则测试', 'variant' => 'ghost', 'href' => '/test'], ['text' => '新建任务', 'variant' => 'primary', 'href' => '/create']]])
 * XfAdmin::actionBar(['items' => $buttons, 'align' => 'between'])
 */
class ActionBar extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items' => [],            // 按钮列表：按钮配置数组 / HTML 字符串 / 组件实例
            'align' => 'end',         // 对齐：'start' | 'end' | 'between'
            'wrap' => true,           // 空间不足时是否允许换行
            'class' => null,          // 追加到根元素的 class
            'id' => null,             // 根元素 id（留空自动生成）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $align = $this->enum($this->get('align'), ['start', 'end', 'between'], 'end');
        $root = Html::cls(
            'xf-action-bar',
            'xf-action-bar--'.$align,
            ! (bool) $this->get('wrap') ? 'xf-action-bar--nowrap' : '',
            (string) ($this->get('class') ?? '')
        );
        $attrs = ['class' => $root];
        if ($this->get('id') !== null) {
            $attrs['id'] = (string) $this->get('id');
        }

        return '<div'.$this->attrs($attrs).'>'.$this->renderItems((array) $this->get('items')).'</div>';
    }

    /**
     * 渲染按钮列表（数组按钮配置 / HTML 字符串 / 组件实例混排）
     *
     * @param array $items 按钮列表
     * @return string result
     */
    private function renderItems(array $items): string
    {
        $out = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $out[] = XfAdmin::button(array_merge(['size' => 'sm'], $item));
            } elseif (is_string($item) || $item instanceof \Stringable) {
                $out[] = (string) $item;
            }
        }

        return implode('', $out);
    }
}

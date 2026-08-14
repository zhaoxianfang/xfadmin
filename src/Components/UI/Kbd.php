<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 键盘按键（Kbd），用于展示快捷键
 *
 * XfAdmin::kbd([
 *     'keys' => ['Ctrl', 'K'],   // 多个按键以 + 连接
 *     'text' => '',              // 或直接使用 text（原样输出）
 * ])
 */
class Kbd extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'keys' => [],
            'text' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $text = (string) $this->get('text');
        if ($text !== '') {
            $inner = '<kbd>' . $this->e($text) . '</kbd>';
        } else {
            $parts = [];
            foreach ((array) $this->get('keys', []) as $k) {
                $parts[] = '<kbd>' . $this->e($k) . '</kbd>';
            }
            $inner = implode('<span class="xf-kbd-plus">+</span>', $parts);
        }
        return '<span' . $this->attrs(['class' => 'xf-kbd']) . '>' . $inner . '</span>';
    }
}

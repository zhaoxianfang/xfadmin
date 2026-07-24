<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 复制按钮（clipboard.js）
 *
 * XfAdmin::clipboard(['text' => '要复制的内容', 'label' => '复制'])
 * XfAdmin::clipboard(['target' => '#code-block', 'label' => '复制代码'])
 */
class ClipboardButton extends Component
{
    protected function defaults(): array
    {
        return [
            'text'    => null,
            'target'  => null,
            'label'   => '复制',
            'variant' => 'light',
            'success' => '已复制！',
        ];
    }

    protected function assets(): array
    {
        return ['clipboard'];
    }

    protected function html(): string
    {
        $attrs = [
            'type'     => 'button',
            'class'    => 'btn btn-sm btn-' . $this->get('variant'),
            'data-xf'  => 'clipboard',
            'data-xf-config' => json_encode(['success' => $this->get('success')], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT),
        ];
        if ($this->get('text') !== null) {
            $attrs['data-clipboard-text'] = $this->get('text');
        }
        if ($this->get('target') !== null) {
            $attrs['data-clipboard-target'] = $this->get('target');
        }

        return '<button' . $this->attrs($attrs) . '><i class="ti ti-copy me-1"></i>' . $this->e($this->get('label')) . '</button>';
    }
}

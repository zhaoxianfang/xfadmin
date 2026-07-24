<?php

declare(strict_types=1);

namespace XfAdmin\Components\UI;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 徽章
 *
 * XfAdmin::badge(['text' => '新', 'variant' => 'danger', 'pill' => true])
 * XfAdmin::badge(['text' => '5', 'variant' => 'primary', 'soft' => true, 'icon' => 'ti ti-bell'])
 */
class Badge extends Component
{
    protected function defaults(): array
    {
        return [
            'text'    => '',
            'variant' => 'primary',
            'pill'    => false,
            'soft'    => false,     // bg-*-subtle
            'outline' => false,
            'icon'    => null,
        ];
    }

    protected function html(): string
    {
        $variant = $this->e($this->get('variant'));
        $style   = $this->get('outline')
            ? "border border-{$variant} text-{$variant}"
            : ($this->get('soft') ? "bg-{$variant}-subtle text-{$variant}" : "text-bg-{$variant}");

        $icon = $this->get('icon') ? '<i class="' . $this->e($this->get('icon')) . ' me-1"></i>' : '';

        return '<span' . $this->attrs(['class' => Html::cls('badge', $style, ['rounded-pill' => $this->get('pill')])]) . '>'
            . $icon . $this->e($this->get('text')) . '</span>';
    }
}

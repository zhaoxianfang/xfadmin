<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 警告提示框
 *
 * XfAdmin::alert(['variant' => 'success', 'text' => '操作成功', 'dismissible' => true, 'icon' => 'ti ti-check'])
 */
class Alert extends Component
{
    protected function defaults(): array
    {
        return [
            'variant'     => 'primary',
            'text'        => '',
            'heading'     => null,
            'icon'        => null,
            'dismissible' => false,
            'soft'        => false,   // bg-*-subtle 柔和风格
        ];
    }

    protected function html(): string
    {
        $variant = $this->e($this->get('variant'));
        $class   = Html::cls('alert', [
            'alert-dismissible fade show' => $this->get('dismissible'),
            'd-flex align-items-center'   => (bool) $this->get('icon'),
        ], $this->get('soft') ? "bg-{$variant}-subtle text-{$variant} border-0" : "alert-{$variant}");

        $html = '<div' . $this->attrs(['class' => $class, 'role' => 'alert']) . '>';
        if ($this->get('icon')) {
            $html .= '<i class="' . $this->e($this->get('icon')) . ' fs-20 me-2"></i>';
        }
        $html .= '<div>';
        if ($this->get('heading')) {
            $html .= '<h5 class="alert-heading">' . $this->e($this->get('heading')) . '</h5>';
        }
        $html .= $this->raw($this->get('text')) . '</div>';
        if ($this->get('dismissible')) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

        return $html . '</div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 按钮（支持 soft/outline/ghost 风格、图标、加载态 Ladda、链接按钮、模态/抽屉触发）
 *
 * XfAdmin::button(['text' => '保存', 'variant' => 'primary', 'type' => 'submit'])
 * XfAdmin::button(['text' => '删除', 'variant' => 'danger', 'soft' => true, 'icon' => 'ti ti-trash'])
 * XfAdmin::button(['text' => '打开', 'toggle' => 'modal', 'target' => '#my-modal'])
 * XfAdmin::button(['text' => '提交', 'ladda' => true])   // 点击自动转圈
 */
class Button extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text'     => '',
            'variant'  => 'primary',
            'type'     => 'button',
            'href'     => null,
            'size'     => null,       // sm | lg
            'soft'     => false,
            'outline'  => false,
            'ghost'    => false,
            'rounded'  => false,      // rounded-pill
            'icon'     => null,
            'icon_only' => false,
            'disabled' => false,
            'ladda'    => false,
            'toggle'   => null,       // modal | offcanvas | collapse | dropdown
            'target'   => null,
            'onclick'  => null,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return $this->get('ladda') ? ['ladda'] : [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $variant = $this->get('variant');
        $style   = 'btn-' . $variant;
        if ($this->get('soft')) {
            $style = 'btn-soft-' . $variant;
        } elseif ($this->get('outline')) {
            $style = 'btn-outline-' . $variant;
        } elseif ($this->get('ghost')) {
            $style = 'btn-ghost-' . $variant;
        }
        $class = Html::cls('btn', $style, [
            'btn-' . $this->enum($this->get('size'), self::ENUM_SIZE, 'lg') => (bool) $this->get('size'),
            'rounded-pill'              => $this->get('rounded'),
            'btn-icon'                  => $this->get('icon_only'),
            'ladda-button'              => $this->get('ladda'),
            'disabled'                  => $this->get('disabled') && $this->get('href'),
        ]);

        $attrs = ['class' => $class];
        if ($this->get('ladda')) {
            $attrs['data-xf']    = 'ladda';
            $attrs['data-style'] = 'zoom-out';
        }
        if ($this->get('toggle')) {
            $attrs['data-bs-toggle'] = $this->get('toggle');
            if ($this->get('target')) {
                $attrs['data-bs-target'] = $this->get('target');
            }
        }
        if ($this->get('onclick')) {
            $attrs['onclick'] = $this->get('onclick');
        }
        $icon  = $this->get('icon') ? '<i class="' . $this->e($this->get('icon')) . ($this->get('icon_only') || $this->get('text') === '' ? '"' : ' me-1"') . '></i>' : '';
        $label = $this->get('ladda') ? '<span class="ladda-label">' . $icon . $this->e($this->get('text')) . '</span>' : $icon . $this->e($this->get('text'));

        if ($this->get('href') !== null) {
            $attrs['href'] = $this->get('href');
            $attrs['role'] = 'button';

            return '<a' . $this->attrs($attrs) . '>' . $label . '</a>';
        }
        $attrs['type']     = $this->get('type');
        $attrs['disabled'] = (bool) $this->get('disabled');

        return '<button' . $this->attrs($attrs) . '>' . $label . '</button>';
    }
}

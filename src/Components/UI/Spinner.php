<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 加载指示器（Bootstrap spinner / SpinKit 高级动画）
 *
 * XfAdmin::spinner(['type' => 'border', 'variant' => 'primary', 'size' => 'sm'])
 * XfAdmin::spinner(['spinkit' => 'wave'])   // plane|chase|bounce|wave|pulse|flow|swing|circle|circle-fade|grid|fold|wander
 */
class Spinner extends Component
{
    protected function defaults(): array
    {
        return [
            'type'    => 'border',   // border | grow
            'variant' => 'primary',
            'size'    => null,       // sm
            'spinkit' => null,
        ];
    }

    protected function assets(): array
    {
        return $this->get('spinkit') ? ['spinkit'] : [];
    }

    protected function html(): string
    {
        if ($this->get('spinkit')) {
            $name = $this->e($this->get('spinkit'));
            $dots = ['plane' => 0, 'chase' => 6, 'bounce' => 2, 'wave' => 5, 'pulse' => 0, 'flow' => 3, 'swing' => 2, 'circle' => 12, 'circle-fade' => 12, 'grid' => 9, 'fold' => 4, 'wander' => 3][$this->get('spinkit')] ?? 0;
            if ($this->get('spinkit') === 'fold') {
                // fold 是立方体结构，独立渲染（不走 -dot 模板）
                $inner = str_repeat('<div class="sk-fold-cube"></div>', 4);
            } else {
                $dotClass = $this->get('spinkit') === 'wave' ? 'sk-' . $name . '-rect' : 'sk-' . $name . '-dot';
                $inner = str_repeat('<div class="' . $dotClass . '"></div>', $dots);
            }

            return '<div' . $this->attrs(['class' => 'sk-' . $name]) . '>' . $inner . '</div>';
        }

        $class = Html::cls(
            'spinner-' . $this->get('type'),
            'text-' . $this->get('variant'),
            $this->get('size') ? 'spinner-' . $this->get('type') . '-' . $this->get('size') : ''
        );

        return '<div' . $this->attrs(['class' => $class, 'role' => 'status']) . '><span class="visually-hidden">Loading...</span></div>';
    }
}

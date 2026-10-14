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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'type' => 'border',   // border | grow
            'variant' => 'primary',
            'size' => null,       // sm
            'spinkit' => null,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return $this->get('spinkit') ? ['spinkit'] : [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        if ($this->get('spinkit')) {
            // spinkit 动画名白名单：取 $dots 映射表的键，非法值回退 plane（dots=0，与历史未知名行为一致）
            $dotsMap = ['plane' => 0, 'chase' => 6, 'bounce' => 2, 'wave' => 5, 'pulse' => 0, 'flow' => 3, 'swing' => 2, 'circle' => 12, 'circle-fade' => 12, 'grid' => 9, 'fold' => 4, 'wander' => 3];
            $name = $this->enum($this->get('spinkit'), array_keys($dotsMap), 'plane');
            $dots = $dotsMap[$name];
            if ($name === 'fold') {
                // fold 是立方体结构，独立渲染（不走 -dot 模板）
                $inner = str_repeat('<div class="sk-fold-cube"></div>', 4);
            } else {
                $dotClass = $name === 'wave' ? 'sk-'.$name.'-rect' : 'sk-'.$name.'-dot';
                $inner = str_repeat('<div class="'.$dotClass.'"></div>', $dots);
            }

            return '<div'.$this->attrs(['class' => 'sk-'.$name]).'>'.$inner.'</div>';
        }
        $class = Html::cls(
            'spinner-'.$this->enum($this->get('type'), ['border', 'grow'], 'border'),
            'text-'.$this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary'),
            $this->get('size') ? 'spinner-'.$this->enum($this->get('type'), ['border', 'grow'], 'border').'-'.$this->enum($this->get('size'), ['sm'], 'sm') : ''
        );

        return '<div'.$this->attrs(['class' => $class, 'role' => 'status']).'><span class="visually-hidden">Loading...</span></div>';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 进度条（支持多段叠加）
 *
 * XfAdmin::progress(['value' => 60, 'variant' => 'success', 'striped' => true, 'animated' => true, 'label' => '60%'])
 * XfAdmin::progress(['bars' => [['value' => 30, 'variant' => 'success'], ['value' => 20, 'variant' => 'warning']]])
 */
class Progress extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'value'    => 0,
            'variant'  => 'primary',
            'height'   => null,     // px
            'striped'  => false,
            'animated' => false,
            'label'    => null,
            'bars'     => [],
        ];
    }

    /**
     * bar（protected实例方法）
     *
     * @param array $bar bar
     *
     * @return string result
     */
    protected function bar(array $bar): string
    {
        $value = (float) ($bar['value'] ?? 0);
        $class = Html::cls('progress-bar', 'bg-' . $this->enum($bar['variant'] ?? 'primary', self::ENUM_VARIANT, 'primary'), [
            'progress-bar-striped'  => $bar['striped'] ?? $this->get('striped'),
            'progress-bar-animated' => $bar['animated'] ?? $this->get('animated'),
        ]);

        return '<div class="' . $class . '" role="progressbar" style="width: ' . $value . '%" aria-valuenow="' . $value . '" aria-valuemin="0" aria-valuemax="100">'
            . $this->e($bar['label'] ?? '') . '</div>';
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $bars = (array) $this->get('bars', []);
        if ($bars === []) {
            $bars = [[
                'value'   => $this->get('value'),
                'variant' => $this->get('variant'),
                'label'   => $this->get('label'),
            ]];
        }
        $style = $this->get('height') !== null ? 'height: ' . (int) $this->get('height') . 'px;' : null;
        $html  = '<div' . $this->attrs(array_filter(['class' => 'progress', 'style' => $style])) . '>';
        foreach ($bars as $bar) {
            $html .= $this->bar((array) $bar);
        }
        return $html . '</div>';
    }
}

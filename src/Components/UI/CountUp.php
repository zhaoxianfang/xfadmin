<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 数字滚动动画（CountUp），进入视口时从 0 递增到目标值
 *
 * XfAdmin::countUp([
 *     'value'    => 1280,
 *     'prefix'   => '',
 *     'suffix'   => '',
 *     'decimals' => 0,
 *     'duration' => 1500,        // 动画时长（毫秒）
 * ])
 */
class CountUp extends Component
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
            'prefix'   => '',
            'suffix'   => '',
            'decimals' => 0,
            'duration' => 1500,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $cfg = [
            'value'    => (float) $this->get('value'),
            'prefix'   => (string) $this->get('prefix'),
            'suffix'   => (string) $this->get('suffix'),
            'decimals' => (int) $this->get('decimals'),
            'duration' => (int) $this->get('duration'),
        ];

        return '<span' . $this->attrs(['class' => 'xf-countup'])
            . ' data-xf="countup" data-xf-config="' . $this->e(json_encode(
                $cfg,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            )) . '">' . $this->e($cfg['prefix'] . '0' . $cfg['suffix']) . '</span>';
    }
}

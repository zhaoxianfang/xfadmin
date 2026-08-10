<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 倒计时（Countdown），到达目标时间前逐秒刷新
 *
 * XfAdmin::countdown([
 *     'target'  => '+1 day',          // 目标时间：时间字符串 / 时间戳(秒) / DateTime 对象
 *     'title'   => '距活动结束',
 *     'labels'  => ['天', '时', '分', '秒'],
 *     'expired' => '已结束',
 * ])
 */
class Countdown extends Component
{
    protected function defaults(): array
    {
        return [
            'target'  => '',
            'title'   => '',
            'labels'  => ['天', '时', '分', '秒'],
            'expired' => '已结束',
        ];
    }

    protected function html(): string
    {
        $target = $this->get('target');
        $labels = (array) $this->get('labels', ['天', '时', '分', '秒']);
        $title  = (string) $this->get('title');

        // 统一为可 JSON 序列化的目标
        if ($target instanceof \DateTimeInterface) {
            $target = $target->getTimestamp();
        } elseif (is_numeric($target)) {
            $target = (int) $target;
        } else {
            $target = (string) $target;
        }

        $cfg = ['target' => $target, 'labels' => $labels, 'expired' => (string) $this->get('expired')];

        $html = '<div' . $this->attrs(['class' => 'xf-countdown']) . '>';
        if ($title !== '') {
            $html .= '<div class="xf-countdown-title small text-muted mb-1">' . $this->e($title) . '</div>';
        }
        $html .= '<div class="xf-countdown-blocks d-inline-flex align-items-center gap-2" data-xf="countdown"'
            . ' data-xf-config="' . $this->e(json_encode(
                $cfg,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            )) . '">';
        $units = ['d', 'h', 'm', 's'];
        foreach ($units as $i => $u) {
            $html .= '<span class="xf-countdown-unit"><b class="xf-cd-num" data-u="' . $u . '">00</b>'
                . '<small class="xf-cd-label">' . $this->e($labels[$i] ?? '') . '</small></span>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

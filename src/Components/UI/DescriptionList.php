<?php

declare(strict_types=1);

namespace XfAdmin\Components\UI;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;

/**
 * 描述列表（键值对，详情页常用：invoice / product / order 详情）
 *
 * XfAdmin::descriptionList([
 *     'items' => [
 *         '订单号' => '#12345',
 *         '状态'   => XfAdmin::badge(['text' => '已支付', 'variant' => 'success']),
 *         ['label' => '备注', 'value' => '尽快发货', 'raw' => true],
 *     ],
 *     'horizontal' => true,
 *     'label_width'=> 4,          // 水平布局左侧栅格
 *     'striped'    => false,
 * ])
 */
class DescriptionList extends Component
{
    protected function defaults(): array
    {
        return [
            'items'       => [],
            'horizontal'  => true,
            'label_width' => 4,
            'striped'     => false,
        ];
    }

    protected function html(): string
    {
        $horizontal = (bool) $this->get('horizontal');
        $lw = max(1, min(6, (int) $this->get('label_width')));

        $cls = Html::cls('mb-0', ['row' => $horizontal]);
        $html = '<dl' . $this->attrs(['class' => $cls]) . '>';

        foreach ((array) $this->get('items', []) as $key => $val) {
            if (is_array($val) && isset($val['label'])) {
                $label = $val['label'];
                $value = $val['value'] ?? '';
            } else {
                $label = $key;
                $value = $val;
            }
            $valHtml = $this->raw($value);

            if ($horizontal) {
                $html .= '<dt class="col-sm-' . $lw . ' text-muted fw-normal py-1">' . $this->e($label) . '</dt>';
                $html .= '<dd class="col-sm-' . (12 - $lw) . ' mb-0 py-1">' . $valHtml . '</dd>';
            } else {
                $html .= '<dt class="text-muted fw-normal">' . $this->e($label) . '</dt><dd class="mb-2">' . $valHtml . '</dd>';
            }
        }

        return $html . '</dl>';
    }
}

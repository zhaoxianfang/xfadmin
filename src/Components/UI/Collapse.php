<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 折叠（Bootstrap Collapse）
 *
 * XfAdmin::collapse([
 *     'trigger'   => '切换显示',      // 触发按钮内容
 *     'body'      => '被折叠的内容',
 *     'open'      => false,
 *     'trigger_class' => 'btn btn-primary',
 *     'horizontal'=> false,
 *     'card'      => true,           // 内容是否包裹卡片
 * ])
 */
class Collapse extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'trigger'       => '展开/收起',
            'body'          => '',
            'open'          => false,
            'trigger_tag'   => 'button',
            'trigger_class' => 'btn btn-primary',
            'horizontal'    => false,
            'card'          => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id  = $this->resolveId('collapse');
        $open = (bool) $this->get('open');

        $triggerTag = $this->get('trigger_tag') === 'a' ? 'a' : 'button';
        $triggerAttrs = [
            'class'          => Html::cls($this->get('trigger_class')),
            'data-bs-toggle' => 'collapse',
            'aria-expanded'  => $open ? 'true' : 'false',
            'aria-controls'  => $id,
        ];
        if ($triggerTag === 'a') {
            $triggerAttrs['href'] = '#' . $id;
            $triggerAttrs['role'] = 'button';
        } else {
            $triggerAttrs['type']            = 'button';
            $triggerAttrs['data-bs-target']  = '#' . $id;
        }
        $html  = '<' . $triggerTag . Html::attrs($triggerAttrs) . '>' . $this->raw($this->get('trigger')) . '</' . $triggerTag . '>';

        $collapseClass = Html::cls('collapse', [
            'show'                => $open,
            'collapse-horizontal' => (bool) $this->get('horizontal'),
        ]);

        $inner = $this->get('card')
            ? '<div class="card card-body mt-2 mb-0">' . $this->raw($this->get('body')) . '</div>'
            : $this->raw($this->get('body'));

        $html .= '<div class="' . $collapseClass . '" id="' . $this->e($id) . '">' . $inner . '</div>';

        return $html;
    }
}

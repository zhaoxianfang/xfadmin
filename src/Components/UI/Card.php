<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 卡片
 *
 * XfAdmin::card([
 *     'title'    => '卡片标题',
 *     'subtitle' => '副标题',
 *     'tools'    => ['collapse', 'refresh', 'close'],   // 或自定义 HTML
 *     'actions'  => '头部右侧自定义HTML',
 *     'body'     => '内容（任意组件/HTML）',
 *     'footer'   => '页脚',
 *     'padding'  => true,    // false 时 body 无内边距（适合放表格）
 * ])
 */
class Card extends Component
{
    protected function defaults(): array
    {
        return [
            'title'    => null,
            'subtitle' => null,
            'tools'    => [],
            'actions'  => null,
            'body'     => '',
            'footer'   => null,
            'padding'  => true,
            'class'    => null,
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => Html::cls('card', $this->get('class'))]) . '>';

        if ($this->get('title') !== null || $this->get('actions') !== null || $this->get('tools') !== []) {
            $html .= '<div class="card-header justify-content-between align-items-center d-flex">';
            $html .= '<div>';
            if ($this->get('title') !== null) {
                $html .= '<h4 class="card-title mb-0">' . $this->raw($this->get('title')) . '</h4>';
            }
            if ($this->get('subtitle') !== null) {
                $html .= '<p class="card-subtitle text-muted mt-1 mb-0">' . $this->raw($this->get('subtitle')) . '</p>';
            }
            $html .= '</div>';

            $tools = $this->get('tools');
            if ($this->get('actions') !== null) {
                $html .= '<div>' . $this->raw($this->get('actions')) . '</div>';
            } elseif ($tools !== []) {
                $html .= '<div class="card-action">';
                if (is_array($tools)) {
                    foreach ($tools as $tool) {
                        $html .= match ($tool) {
                            'collapse' => '<span class="card-action-item" data-toggle="collapse"><i class="ti ti-chevron-up"></i></span>',
                            'refresh'  => '<span class="card-action-item" data-toggle="reload"><i class="ti ti-refresh"></i></span>',
                            'close'    => '<span class="card-action-item" data-toggle="remove"><i class="ti ti-x"></i></span>',
                            default    => $this->raw($tool),
                        };
                    }
                } else {
                    $html .= $this->raw($tools);
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="' . Html::cls('card-body', ['p-0' => ! $this->get('padding')]) . '">' . $this->raw($this->get('body')) . '</div>';

        if ($this->get('footer') !== null) {
            $html .= '<div class="card-footer">' . $this->raw($this->get('footer')) . '</div>';
        }

        return $html . '</div>';
    }
}

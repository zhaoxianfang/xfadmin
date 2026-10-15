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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title' => null,
            'subtitle' => null,
            'tools' => [],
            'actions' => null,
            'body' => '',
            'footer' => null,
            'padding' => true,
            'hoverable' => false,   // 鼠标悬停微交互（依赖 xfadmin.css 的 .xf-hoverable）
            'header_class' => null, // 追加到 card-header 的额外 class
            'body_class' => null,   // 追加到 card-body 的额外 class
            'title_tag' => 'h4',    // 标题标签：h1~h6 / div（语义化）
            'class' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $cardClass = Html::cls('card', $this->get('class'));
        if ($this->get('hoverable')) {
            $cardClass .= ' xf-hoverable';
        }
        $html = '<div'.$this->attrs(['class' => $cardClass]).'>';

        // 标题标签白名单（非法值回落 h4），避免注入任意标签
        $titleTag = $this->enum($this->get('title_tag'), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p'], 'h4');
        $headerCls = 'card-header justify-content-between align-items-center d-flex'.($this->get('header_class') ? ' '.$this->e($this->get('header_class')) : '');

        if ($this->get('title') !== null || $this->get('actions') !== null || $this->get('tools') !== []) {
            $html .= '<div class="'.$headerCls.'">';
            $html .= '<div>';
            if ($this->get('title') !== null) {
                $html .= '<'.$titleTag.' class="card-title mb-0">'.$this->e($this->get('title')).'</'.$titleTag.'>';
            }
            if ($this->get('subtitle') !== null) {
                $html .= '<p class="card-subtitle text-muted mt-1 mb-0">'.$this->e($this->get('subtitle')).'</p>';
            }
            $html .= '</div>';

            $tools = $this->get('tools');
            if ($this->get('actions') !== null) {
                $html .= '<div>'.$this->raw($this->get('actions')).'</div>';
            } elseif ($tools !== []) {
                $html .= '<div class="card-action">';
                if (is_array($tools)) {
                    foreach ($tools as $tool) {
                        $html .= match ($tool) {
                            // 与 INSPINIA 模板 app.js initPortletCard() 对齐：使用 data-action 属性
                            'collapse' => '<span class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></span>',
                            'refresh' => '<span class="card-action-item" data-action="card-refresh"><i class="ti ti-refresh"></i></span>',
                            'close' => '<span class="card-action-item" data-action="card-close"><i class="ti ti-x"></i></span>',
                            default => $this->raw($tool),
                        };
                    }
                } else {
                    $html .= $this->raw($tools);
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="'.Html::cls('card-body', ['p-0' => ! $this->get('padding')]).($this->get('body_class') ? ' '.$this->e($this->get('body_class')) : '').'">'.$this->raw($this->get('body')).'</div>';

        if ($this->get('footer') !== null) {
            $html .= '<div class="card-footer">'.$this->raw($this->get('footer')).'</div>';
        }

        return $html.'</div>';
    }
}

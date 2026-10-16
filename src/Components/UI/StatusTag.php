<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 状态标签（彩色胶囊，对标后台列表中的状态 / 类型标记）
 *
 * 取代散落在各处的手写 <span class="tag tag-success"> 片段，统一语义色与无障碍。
 * 常用于表格状态列、卡片角标、流程节点标记。soft 模式走 bg-*-subtle text-*，
 * 与包内 Badge / statusBadge 视觉一致；muted 回落 secondary 色板。
 *
 * XfAdmin::statusTag([
 *     'text'  => '运行中',
 *     'type'  => 'success',   // success/warning/danger/info/primary/secondary/muted
 *     'dot'   => true,         // 前置状态圆点
 *     'soft'  => true,         // 浅底深字；false=实底白字(text-bg-*)
 * ])
 */
class StatusTag extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text'  => '',               // 标签文案（纯文本语义，输出转义）
            'type'  => 'muted',          // 语义色：success/warning/danger/info/primary/secondary/muted
            'dot'   => false,            // 是否前置状态圆点（currentColor 取文字色）
            'soft'  => true,             // true=浅底深字(bg-*-subtle text-*)；false=实底白字(text-bg-*)
            'class' => null,             // 附加 class（挂到标签本身）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // muted 非 Bootstrap 标准 variant，回落 secondary 色板（与 statusBadge 保持一致）
        $type = $this->enum($this->get('type'), ['success', 'warning', 'danger', 'info', 'primary', 'secondary', 'muted'], 'muted');
        $color = $type === 'muted' ? 'secondary' : $type;
        $style = $this->get('soft')
            ? "bg-{$color}-subtle text-{$color}"
            : "text-bg-{$color}";

        $dot = $this->get('dot')
            ? '<span class="me-1" style="display:inline-block;width:.5rem;height:.5rem;border-radius:50%;background:currentColor"></span>'
            : '';

        return '<span'.$this->attrs(['class' => Html::cls('badge rounded-pill', $style, $this->get('class'))]).'>'
            .$dot.$this->escape($this->get('text')).'</span>';
    }
}

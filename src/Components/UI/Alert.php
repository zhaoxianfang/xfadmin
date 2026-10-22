<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 警告提示框
 *
 * XfAdmin::alert(['variant' => 'success', 'text' => '操作成功', 'dismissible' => true, 'icon' => 'ti ti-check'])
 * XfAdmin::alert(['variant' => 'warning', 'text' => '请执行 <code>php artisan migrate</code>', 'escape' => false])
 *
 * 安全说明：text 默认经 escape() 转义，避免富文本场景的 XSS；
 * 仅当 text 确为可信的静态 HTML 时，才显式传 'escape' => false。
 */
class Alert extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'variant' => 'primary',
            'text' => '',
            'heading' => null,
            'icon' => null,
            'dismissible' => false,
            'soft' => false,   // bg-*-subtle 柔和风格
            'escape' => true,  // text 是否转义：true=转义（默认，安全）；false=原样输出 HTML
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // 键名容错：text / message / content 等价；heading / title 等价；variant / type 等价
        if ($this->get('text') === '' || $this->get('text') === null) {
            $alias = $this->get('message') ?? $this->get('content');
            if ($alias !== null) {
                $this->options['text'] = $alias;
            }
        }
        if ($this->get('heading') === null && $this->get('title') !== null) {
            $this->options['heading'] = $this->get('title');
        }
        if ($this->get('type') !== null && $this->get('variant') === 'primary') {
            $this->options['variant'] = $this->get('type');
        }
        // variant 白名单（拼 alert-* / bg-*-subtle 类），非法值回退默认 primary
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');
        $class = Html::cls('alert', [
            'alert-dismissible fade show' => $this->get('dismissible'),
            'd-flex align-items-center' => (bool) $this->get('icon'),
        ], $this->get('soft') ? "bg-{$variant}-subtle text-{$variant} border-0" : "alert-{$variant}");

        $html = '<div'.$this->attrs(['class' => $class, 'role' => 'alert']).'>';
        if ($this->get('icon')) {
            $html .= '<i class="'.$this->iconClass($this->get('icon')).' fs-20 me-2"></i>';
        }
        $html .= '<div>';
        if ($this->get('heading')) {
            $html .= '<h5 class="alert-heading">'.$this->escape($this->get('heading')).'</h5>';
        }
        // text 是否转义：默认转义（安全）；显式 'escape' => false 时原样输出 HTML（如 <code>/<br>）
        $html .= ($this->get('escape') ? $this->escape($this->get('text')) : $this->raw($this->get('text'))).'</div>';
        if ($this->get('dismissible')) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }

        return $html.'</div>';
    }
}

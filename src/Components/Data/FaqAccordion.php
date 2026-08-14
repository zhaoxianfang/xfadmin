<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * FAQ 折叠列表（pages-faq.html）
 *
 * 基于 Bootstrap 原生 accordion 的问答列表。
 *
 * XfAdmin::faqAccordion([
 *     'items' => [
 *         ['q' => '如何重置密码？', 'a' => '在登录页点击「忘记密码」，按邮件指引操作即可。'],
 *         ['q' => '支持哪些支付方式？', 'a' => '支持微信、支付宝及对公转账。'],
 *     ],
 *     'title' => '常见问题',
 * ])
 */
class FaqAccordion extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'items' => [],
            'title' => '常见问题',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        $title = (string) $this->get('title', '常见问题');

        $id = $this->uid('xf-faq');

        $html = '<div class="card"><div class="card-header"><h5 class="mb-0">' . $this->e($title) . '</h5></div><div class="card-body">';

        if (empty($items)) {
            $html .= '<div class="text-center text-muted py-4">暂无常见问题</div></div></div>';

            return $html;
        }
        $html .= '<div class="accordion" id="' . $this->e($id) . '">';

        foreach ($items as $i => $it) {
            $it     = (array) $it;
            $q      = (string) ($it['q'] ?? '');
            $a      = (string) ($it['a'] ?? '');
            $itemId = $id . '-' . $i;
            $expanded = $i === 0;

            $html .= '<div class="accordion-item">';
            $html .= '<h2 class="accordion-header"><button class="accordion-button' . ($expanded ? '' : ' collapsed') . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $this->e($itemId) . '" aria-expanded="' . ($expanded ? 'true' : 'false') . '">' . $this->e($q) . '</button></h2>';
            $html .= '<div id="' . $this->e($itemId) . '" class="accordion-collapse collapse' . ($expanded ? ' show' : '') . '" data-bs-parent="#' . $this->e($id) . '"><div class="accordion-body">' . $this->e($a) . '</div></div>';
            $html .= '</div>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

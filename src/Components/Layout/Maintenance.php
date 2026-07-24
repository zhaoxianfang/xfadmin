<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\XfAdmin;

/**
 * 维护中页（maintenance.html）
 *
 * echo XfAdmin::maintenance([
 *     'heading' => '网站维护中',
 *     'message' => '我们正在进行系统升级，稍后回来。',
 *     'image'   => null,
 *     'contact' => 'support@example.com',
 * ]);
 */
class Maintenance extends AuthPage
{
    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            'heading' => '网站维护中',
            'message' => '我们正在进行例行维护，请稍后再访问。',
            'image'   => null,
            'contact' => null,
            'card'    => false,
        ]);
    }

    protected function html(): string
    {
        $content = '<div class="text-center">';
        if ($this->get('image')) {
            $content .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $this->get('image'), '/'))) . '" class="img-fluid mb-3" alt="">';
        } else {
            $content .= '<i class="ti ti-settings" style="font-size:72px;"></i>';
        }
        $content .= '<h2 class="fw-bold mt-3">' . $this->e($this->get('heading')) . '</h2>';
        $content .= '<p class="text-muted">' . $this->raw($this->get('message')) . '</p>';
        if ($this->get('contact')) {
            $content .= '<p class="mb-0">如需帮助请联系 <a href="mailto:' . $this->e($this->get('contact')) . '">' . $this->e($this->get('contact')) . '</a></p>';
        }
        $content .= '</div>';

        $this->set('content', $content);
        if (! $this->get('title')) {
            $this->set('title', $this->get('heading'));
        }

        return parent::html();
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\XfAdmin;

/**
 * 即将上线页（pages-coming-soon.html）—— 含倒计时
 *
 * echo XfAdmin::comingSoon([
 *     'heading'  => '即将上线',
 *     'message'  => '我们正在努力，敬请期待！',
 *     'deadline' => '2026-12-31 00:00:00',
 *     'image'    => null,
 *     'subscribe'=> true,          // 显示订阅表单
 * ]);
 */
class ComingSoon extends AuthPage
{
    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            'heading'  => '即将上线',
            'message'  => '我们正在努力打造精彩内容，敬请期待。',
            'deadline' => null,
            'image'    => null,
            'subscribe'=> true,
            'card'     => false,
        ]);
    }

    protected function html(): string
    {
        $id = $this->uid('countdown');

        $content = '<div class="text-center">';
        if ($this->get('image')) {
            $content .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $this->get('image'), '/'))) . '" class="img-fluid mb-3" alt="">';
        }
        $content .= '<h2 class="fw-bold">' . $this->e($this->get('heading')) . '</h2>';
        $content .= '<p class="text-muted">' . $this->raw($this->get('message')) . '</p>';

        if ($this->get('deadline')) {
            $ts = strtotime((string) $this->get('deadline')) * 1000;
            $content .= '<div class="d-flex justify-content-center gap-3 my-4" data-xf="countdown" data-deadline="' . $ts . '" id="' . $id . '">';
            foreach (['days' => '天', 'hours' => '时', 'minutes' => '分', 'seconds' => '秒'] as $unit => $label) {
                $content .= '<div class="card mb-0"><div class="card-body px-3 py-2 text-center">'
                    . '<h2 class="mb-0" data-unit="' . $unit . '">00</h2><small class="text-muted">' . $label . '</small>'
                    . '</div></div>';
            }
            $content .= '</div>';
        }

        if ($this->get('subscribe')) {
            $content .= '<form class="d-flex gap-2 justify-content-center mt-3" style="max-width:420px;margin:0 auto;">'
                . '<input type="email" class="form-control" placeholder="输入邮箱订阅上线通知">'
                . '<button class="btn btn-primary" type="submit">订阅</button></form>';
        }
        $content .= '</div>';

        $this->set('content', $content);
        if (! $this->get('title')) {
            $this->set('title', $this->get('heading'));
        }

        return parent::html();
    }
}

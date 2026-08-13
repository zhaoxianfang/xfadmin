<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 聊天气泡
 *
 * 左/右对齐、已读状态、时间、头像、附件预览，
 * 复刻 inspinia apps-chat.html 的会话气泡。
 *
 * XfAdmin::chatMessageBubble([
 *     'side'     => 'in',   // in（对方/左） | out（自己/右）
 *     'avatar'   => 'users/user-1.jpg',
 *     'text'     => '您好，请问订单进度？',
 *     'time'     => '10:24',
 *     'read'     => true,
 *     'attach'   => null,   // 可选：['url'=>..., 'name'=>...]
 * ])
 */
class ChatMessageBubble extends Component
{
    protected function defaults(): array
    {
        return [
            'side'   => 'in',
            'avatar' => null,
            'text'   => '',
            'time'   => '',
            'read'   => false,
            'attach' => null,
        ];
    }

    protected function html(): string
    {
        $out    = $this->get('side') === 'out';
        $avatar = $this->get('avatar');
        $text   = $this->get('text');
        $time   = $this->get('time');
        $read   = $this->get('read');
        $attach = $this->get('attach');

        $align = $out ? 'justify-content-end' : 'justify-content-start';
        $bubbleCls = $out ? 'bg-primary text-white' : 'bg-light';

        $attachHtml = '';
        if ($attach) {
            $a = (array) $attach;
            $attachHtml = '<a href="' . $this->e($this->img($a['url'] ?? '')) . '" class="d-block mt-1">'
                . '<i class="ti ti-paperclip"></i> ' . $this->e($a['name'] ?? '附件') . '</a>';
        }

        $avatarHtml = $avatar ? '<img src="' . $this->e($this->img($avatar)) . '" class="rounded-circle me-2" width="36" height="36" alt="">' : '';

        $html = '<div class="d-flex ' . $align . ' mb-3">';
        if (! $out && $avatarHtml) {
            $html .= '<div class="flex-shrink-0">' . $avatarHtml . '</div>';
        }
        $html .= '<div class="col-8">';
        $html .= '<div class="rounded-3 p-2 ' . $bubbleCls . '">' . $this->e($text) . $attachHtml . '</div>';
        $html .= '<div class="small text-muted mt-1 ' . ($out ? 'text-end' : '') . '">' . $this->e($time)
            . ($out ? ($read ? ' · 已读' : ' · 已发送') : '') . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

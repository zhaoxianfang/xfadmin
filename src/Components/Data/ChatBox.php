<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 聊天窗口（chat.html）
 *
 * XfAdmin::chatBox([
 *     'title'    => '张三',
 *     'status'   => '在线',
 *     'avatar'   => 'users/avatar-2.jpg',
 *     'height'   => '460px',
 *     'messages' => [
 *         ['from' => 'them', 'text' => '你好', 'time' => '10:00', 'avatar' => 'users/avatar-2.jpg'],
 *         ['from' => 'me',   'text' => '在的', 'time' => '10:01'],
 *     ],
 *     'input'    => true,
 * ])
 */
class ChatBox extends Component
{
    protected function defaults(): array
    {
        return [
            'title'    => '',
            'status'   => null,
            'avatar'   => null,
            'height'   => '460px',
            'messages' => [],
            'input'    => true,
            'header'   => true,
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'card mb-0 chat-box']) . '>';

        if ($this->get('header')) {
            $html .= '<div class="card-header d-flex align-items-center gap-2">';
            if ($this->get('avatar')) {
                $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $this->get('avatar'), '/'))) . '" class="rounded-circle" width="36" height="36" alt="">';
            }
            $html .= '<div><h5 class="mb-0">' . $this->e($this->get('title')) . '</h5>';
            if ($this->get('status')) {
                $html .= '<small class="text-success">' . $this->e($this->get('status')) . '</small>';
            }
            $html .= '</div></div>';
        }

        $html .= '<div class="card-body overflow-auto" data-xf="chat-scroll" style="height:' . $this->e($this->get('height')) . ';">';
        foreach ((array) $this->get('messages', []) as $m) {
            $html .= $this->message((array) $m);
        }
        $html .= '</div>';

        if ($this->get('input')) {
            $html .= '<div class="card-footer">';
            $html .= '<form class="d-flex gap-2" data-xf="chat-form">';
            $html .= '<input type="text" class="form-control" placeholder="输入消息..." name="message" autocomplete="off">';
            $html .= '<button type="submit" class="btn btn-primary btn-icon"><i class="ti ti-send"></i></button>';
            $html .= '</form></div>';
        }

        return $html . '</div>';
    }

    private function message(array $m): string
    {
        $me   = ($m['from'] ?? 'them') === 'me';
        $align = $me ? 'justify-content-end' : '';
        $bg    = $me ? 'bg-primary text-white' : 'bg-light';

        $html = '<div class="d-flex mb-3 ' . $align . '">';
        if (! $me && ! empty($m['avatar'])) {
            $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $m['avatar'], '/'))) . '" class="rounded-circle me-2 align-self-end" width="32" height="32" alt="">';
        }
        $html .= '<div class="' . ($me ? 'text-end' : '') . '" style="max-width:75%;">';
        $html .= '<div class="p-2 px-3 rounded ' . $bg . '">' . $this->raw($m['text'] ?? '') . '</div>';
        if (! empty($m['time'])) {
            $html .= '<small class="text-muted">' . $this->e($m['time']) . '</small>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

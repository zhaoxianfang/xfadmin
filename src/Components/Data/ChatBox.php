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
 *         // text 默认转义（防 XSS）；富文本消息请使用 'html' 字段（调用方自行保证安全）
 *         ['from' => 'them', 'html' => '<b>加粗</b>', 'time' => '10:02'],
 *     ],
 *     'input'    => true,
 * ])
 */
class ChatBox extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
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

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'card mb-0 chat-box']) . '>';

        if ($this->get('header')) {
            $html .= '<div class="card-header d-flex align-items-center gap-2">';
            if ($this->get('avatar')) {
                // 会话对象头像：INSPINIA 规范 .avatar 包裹（avatar-md=36px）
                $html .= '<span class="avatar avatar-md flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $this->get('avatar'))) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span>';
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

    /**
     * message（private实例方法）
     *
     * @param array $m m
     *
     * @return string result
     */
    private function message(array $m): string
    {
        $me   = ($m['from'] ?? 'them') === 'me';
        $align = $me ? 'justify-content-end' : '';
        $bg    = $me ? 'bg-primary text-white' : 'bg-light';

        $html = '<div class="d-flex mb-3 ' . $align . '">';
        if (! $me && ! empty($m['avatar'])) {
            // 消息气泡旁头像：INSPINIA 规范 .avatar 包裹（avatar-sm=32px），底部对齐气泡
            $html .= '<span class="avatar avatar-sm me-2 align-self-end flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $m['avatar'])) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span>';
        }
        $html .= '<div class="' . ($me ? 'text-end' : '') . '" style="max-width:75%;">';
        // 聊天内容属于用户数据，默认转义；如需富文本请显式使用 'html' 字段
        $body = isset($m['html']) ? $this->raw($m['html']) : nl2br($this->e($m['text'] ?? ''));
        $html .= '<div class="p-2 px-3 rounded ' . $bg . '">' . $body . '</div>';
        if (! empty($m['time'])) {
            $html .= '<small class="text-muted">' . $this->e($m['time']) . '</small>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

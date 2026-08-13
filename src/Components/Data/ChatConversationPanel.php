<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 会话面板框架
 *
 * 左侧联系人列表 + 右侧消息区 + 底部输入工具栏，
 * 复刻 inspinia apps-chat.html 的会话面板。
 *
 * XfAdmin::chatConversationPanel([
 *     'contacts' => [
 *         ['id'=>1, 'name'=>'张三', 'avatar'=>'users/user-1.jpg', 'last'=>'在吗？', 'time'=>'10:20', 'unread'=>2, 'online'=>true],
 *     ],
 *     'messages' => [ ChatMessageBubble 实例 或 ['side'=>..., 'text'=>...] ],
 *     'title'    => '在线客服',
 * ])
 */
class ChatConversationPanel extends Component
{
    protected function defaults(): array
    {
        return [
            'contacts' => [],
            'messages' => [],
            'title'    => '会话',
            'me'       => null,
        ];
    }

    protected function html(): string
    {
        $contacts = (array) $this->get('contacts', []);
        $messages = (array) $this->get('messages', []);
        $title    = $this->get('title');

        // 联系人列表
        $list = '<div class="list-group list-group-flush xf-chat-contacts border-end" style="min-width:240px;max-width:240px;">';
        foreach ($contacts as $c) {
            $c = (array) $c;
            $online = ! empty($c['online']) ? 'online' : '';
            $unread = ! empty($c['unread']) ? '<span class="badge bg-danger rounded-pill float-end">' . (int) $c['unread'] . '</span>' : '';
            $list .= '<a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2 ' . $online . '">'
                . ($c['avatar'] ? '<img src="' . $this->e($this->img($c['avatar'])) . '" class="rounded-circle" width="40" height="40" alt="">' : '')
                . '<div class="flex-grow-1 overflow-hidden"><div class="d-flex justify-content-between"><span class="fw-semibold text-truncate">' . $this->e($c['name'] ?? '') . '</span><small class="text-muted">' . $this->e($c['time'] ?? '') . '</small></div>'
                . '<small class="text-muted text-truncate d-block">' . $this->e($c['last'] ?? '') . '</small></div>' . $unread
                . '</a>';
        }
        $list .= '</div>';

        // 消息区
        $msgHtml = '';
        foreach ($messages as $m) {
            if ($m instanceof Component) {
                $msgHtml .= $m->render();
            } else {
                $msgHtml .= ChatMessageBubble::make((array) $m)->render();
            }
        }

        $panel = '<div class="d-flex flex-column h-100">';
        $panel .= '<div class="px-3 py-2 border-bottom fw-semibold d-flex align-items-center gap-2"><i class="ti ti-message-circle"></i>' . $this->e($title) . '</div>';
        $panel .= '<div class="flex-grow-1 overflow-auto p-3 xf-chat-messages">' . $msgHtml . '</div>';
        $panel .= '<div class="p-2 border-top d-flex gap-2">'
            . '<input type="text" class="form-control" placeholder="输入消息...">'
            . '<button class="btn btn-primary" type="button"><i class="ti ti-send"></i></button></div>';
        $panel .= '</div>';

        return '<div class="card border-0 shadow-sm" style="height:560px;">'
            . '<div class="row g-0 h-100">'
            . '<div class="col-auto">' . $list . '</div>'
            . '<div class="col">' . $panel . '</div>'
            . '</div></div>';
    }
}

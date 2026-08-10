<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 聊天应用（会话列表 + 聊天窗口）—— INSPINIA apps-chat.html 整页抽取
 *
 * XfAdmin::chatApp([
 *     'conversations' => [
 *         ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'last' => '好的，明天见',
 *          'time' => '10:02', 'unread' => 2, 'active' => true, 'online' => true, 'url' => '#'],
 *     ],
 *     'peer'     => ['name' => '李娜', 'avatar' => 'users/user-2.jpg', 'online' => true, 'status' => '在线'],
 *     'messages' => [
 *         ['from' => 'other', 'text' => '你好！', 'time' => '09:58'],
 *         ['from' => 'me',    'text' => '在的，请讲', 'time' => '09:59'],
 *     ],
 *     'placeholder' => '输入消息…',
 * ])
 */
class ChatApp extends Component
{
    protected function defaults(): array
    {
        return [
            'conversations' => [],
            'peer'          => [],
            'messages'      => [],
            'search'        => '搜索联系人…',
            'placeholder'   => '输入消息…',
            'height'        => '60vh',
        ];
    }

    protected function html(): string
    {
        $h = $this->e($this->get('height', '60vh'));

        // ---- 左栏：会话列表 ----
        $list = '';
        foreach ((array) $this->get('conversations', []) as $c) {
            $dot = isset($c['online'])
                ? '<span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white bg-'
                    . (! empty($c['online']) ? 'success' : 'secondary') . '" style="width:10px;height:10px;"></span>'
                : '';
            $badge = ! empty($c['unread'])
                ? '<span class="badge bg-danger rounded-pill ms-1">' . $this->e($c['unread']) . '</span>' : '';
            $list .= '<a href="' . $this->e($c['url'] ?? '#') . '" class="list-group-item list-group-item-action d-flex align-items-center py-3'
                . (! empty($c['active']) ? ' active' : '') . '">'
                . '<span class="avatar avatar-sm position-relative me-2 flex-shrink-0">'
                . '<img class="rounded-circle img-fluid" src="' . $this->e($this->img($c['avatar'] ?? '')) . '" alt="">' . $dot . '</span>'
                . '<span class="flex-grow-1 overflow-hidden"><span class="d-block fw-semibold text-truncate">' . $this->e($c['name'] ?? '') . '</span>'
                . '<small class="d-block text-truncate' . (! empty($c['active']) ? '' : ' text-muted') . '">' . $this->e($c['last'] ?? '') . '</small></span>'
                . '<span class="text-end ms-2 flex-shrink-0"><small class="d-block' . (! empty($c['active']) ? '' : ' text-muted') . '">'
                . $this->e($c['time'] ?? '') . '</small>' . $badge . '</span></a>';
        }
        $left = '<div class="p-2 border-bottom"><input class="form-control" placeholder="' . $this->e($this->get('search')) . '"></div>'
            . '<div class="list-group list-group-flush xf-scroll-y" style="max-height:calc(' . $h . ' + 56px)">' . $list . '</div>';

        // ---- 右栏：聊天窗 ----
        $peer = (array) $this->get('peer', []);
        $head = '<div class="d-flex align-items-center p-3 border-bottom">'
            . '<span class="avatar avatar-sm position-relative me-2">'
            . '<img class="rounded-circle img-fluid" src="' . $this->e($this->img($peer['avatar'] ?? '')) . '" alt="">'
            . (isset($peer['online'])
                ? '<span class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white bg-'
                    . (! empty($peer['online']) ? 'success' : 'secondary') . '" style="width:10px;height:10px;"></span>'
                : '')
            . '</span><div><div class="fw-semibold">' . $this->e($peer['name'] ?? '') . '</div>'
            . '<small class="text-' . (! empty($peer['online']) ? 'success' : 'muted') . '">' . $this->e($peer['status'] ?? '') . '</small></div>'
            . '<div class="ms-auto">'
            . '<button type="button" class="btn btn-sm btn-soft-secondary" title="语音"><i class="ti ti-phone"></i></button> '
            . '<button type="button" class="btn btn-sm btn-soft-secondary" title="视频"><i class="ti ti-video"></i></button> '
            . '<button type="button" class="btn btn-sm btn-soft-secondary" title="更多"><i class="ti ti-dots-vertical"></i></button></div></div>';

        $body = '<div class="p-3 xf-scroll-y d-flex flex-column gap-3" style="height:' . $h . '">';
        foreach ((array) $this->get('messages', []) as $m) {
            $mine = ($m['from'] ?? '') === 'me';
            $av   = $m['avatar'] ?? ($mine ? null : ($peer['avatar'] ?? null));
            $avatarHtml = $av
                ? '<span class="avatar avatar-xs flex-shrink-0"><img class="rounded-circle img-fluid" src="' . $this->e($this->img($av)) . '" alt=""></span>'
                : '';
            $bubble = '<div><div class="p-2 px-3 rounded-3 ' . ($mine ? 'bg-primary text-white' : 'bg-light') . '">'
                . $this->e($m['text'] ?? '') . '</div>'
                . (! empty($m['time']) ? '<small class="text-muted d-block mt-1' . ($mine ? ' text-end' : '') . '">' . $this->e($m['time']) . '</small>' : '')
                . '</div>';
            $body .= $mine
                ? '<div class="d-flex justify-content-end gap-2">' . $bubble . $avatarHtml . '</div>'
                : '<div class="d-flex gap-2">' . $avatarHtml . $bubble . '</div>';
        }
        $body .= '</div>';

        $foot = '<div class="p-3 border-top"><div class="input-group">'
            . '<button type="button" class="btn btn-soft-secondary" title="表情"><i class="ti ti-mood-smile"></i></button>'
            . '<button type="button" class="btn btn-soft-secondary" title="附件"><i class="ti ti-paperclip"></i></button>'
            . '<input class="form-control" placeholder="' . $this->e($this->get('placeholder')) . '">'
            . '<button type="button" class="btn btn-primary"><i class="ti ti-send"></i></button></div></div>';

        return '<div' . $this->attrs(['class' => 'card border-0 shadow-sm xf-chat-app']) . '><div class="row g-0">'
            . '<div class="col-lg-4 col-xl-3 border-end">' . $left . '</div>'
            . '<div class="col-lg-8 col-xl-9 d-flex flex-column">' . $head . $body . $foot . '</div>'
            . '</div></div>';
    }
}

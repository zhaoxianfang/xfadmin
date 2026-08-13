<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 通知中心（Notification Center）—— 对标 INSPINIA 缺失的右侧通知抽屉
 *
 * XfAdmin::notificationCenter([
 *     'id'       => 'xf-notify-center',
 *     'title'    => '通知中心',
 *     'badge'    => 3,                 // 未读角标数
 *     'empty'    => '暂无新通知',
 *     'items'    => [                  // 通知项
 *         ['avatar' => '', 'icon' => 'ti ti-user-plus', 'variant' => 'primary',
 *          'title' => '新用户注册', 'text' => '张三 申请加入', 'time' => '2 分钟前', 'unread' => true,
 *          'url' => '/admin/users'],
 *     ],
 *     'footer'   => ['all' => '/admin/notifications', 'clear' => true],
 * ])
 * 前端：点击项触发 XFAdmin.request(url,...) 或跳转；清空触发 XFAdmin.onNotify('clear')。
 */
class NotificationCenter extends Component
{
    protected function defaults(): array
    {
        return [
            'id'     => 'xf-notify-center',
            'title'  => '通知中心',
            'badge'  => 0,
            'empty'  => '暂无新通知',
            'items'  => [],
            'footer' => ['all' => null, 'clear' => true],
        ];
    }

    protected function html(): string
    {
        $id   = $this->e($this->get('id'));
        $items = (array) $this->get('items');
        $badge = (int) $this->get('badge');
        $list = '';
        foreach ($items as $it) {
            $avatar = $this->e($it['avatar'] ?? '');
            $icon   = $this->e($it['icon'] ?? 'ti ti-bell');
            $variant = $this->e($it['variant'] ?? 'primary');
            $title  = $this->e($it['title'] ?? '');
            $text   = $this->e($it['text'] ?? '');
            $time   = $this->e($it['time'] ?? '');
            $unread = ! empty($it['unread']);
            $url    = $this->e($it['url'] ?? '');
            $media  = $avatar
                ? '<img src="' . $this->img($avatar) . '" class="rounded-circle" width="38" height="38" alt="">'
                : '<span class="avatar-sm d-inline-flex align-items-center justify-content-center rounded-circle bg-soft-' . $variant . ' text-' . $variant . '"><i class="' . $icon . '"></i></span>';
            $list .= '<a href="' . ($url ?: '#') . '" class="list-group-item list-group-item-action xf-notify-item' . ($unread ? ' unread' : '') . '"'
                . ' data-xf-notify' . ($url ? ' data-url="' . $url . '"' : '') . '>'
                . '<div class="d-flex gap-2">'
                . '<div class="flex-shrink-0">' . $media . '</div>'
                . '<div class="flex-grow-1"><div class="d-flex justify-content-between"><span class="fw-semibold">' . $title . '</span>'
                . '<small class="text-muted">' . $time . '</small></div><div class="small text-muted">' . $text . '</div></div>'
                . ($unread ? '<span class="xf-notify-dot"></span>' : '')
                . '</div></a>';
        }

        $footer = (array) $this->get('footer');
        $footHtml = '';
        if (! empty($footer['all']) || ! empty($footer['clear'])) {
            $footHtml = '<div class="d-flex justify-content-between p-2 border-top">';
            $footHtml .= ! empty($footer['all'])
                ? '<a href="' . $this->e($footer['all']) . '" class="small">查看全部</a>'
                : '<span></span>';
            $footHtml .= ! empty($footer['clear'])
                ? '<button type="button" class="btn btn-sm btn-link p-0 xf-notify-clear">标记已读</button>'
                : '';
            $footHtml .= '</div>';
        }

        $html = '<div class="offcanvas offcanvas-end xf-notify-center" tabindex="-1" id="' . $id . '" aria-label="' . $this->e($this->get('title')) . '">'
            . '<div class="offcanvas-header"><h6 class="offcanvas-title mb-0">' . $this->e($this->get('title')) . '</h6>'
            . '<button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>'
            . '<div class="offcanvas-body p-0">'
            . '<div class="list-group list-group-flush xf-notify-list">' . $list . '</div>'
            . '<div class="text-muted small text-center py-4 xf-notify-empty' . ($items ? ' d-none' : '') . '">' . $this->e($this->get('empty')) . '</div>'
            . '</div>' . $footHtml . '</div>';

        return $html;
    }
}

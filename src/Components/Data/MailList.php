<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\XfAdmin;

/**
 * 邮件列表（email.html）
 *
 * XfAdmin::mailList([
 *     'items' => [
 *         ['from' => '张三', 'avatar' => 'users/avatar-1.jpg', 'subject' => '会议通知',
 *          'excerpt' => '明天下午三点...', 'time' => '10:30', 'unread' => true,
 *          'starred' => false, 'label' => ['text'=>'工作','variant'=>'primary'], 'href' => '#'],
 *     ],
 *     'checkbox' => true,
 * ])
 */
class MailList extends Component
{
    protected function defaults(): array
    {
        return [
            'items'    => [],
            'checkbox' => true,
        ];
    }

    protected function html(): string
    {
        $cb = (bool) $this->get('checkbox');

        $html = '<div' . $this->attrs(['class' => 'list-group list-group-flush']) . '>';
        foreach ((array) $this->get('items', []) as $m) {
            $unread = ! empty($m['unread']);
            $bg = $unread ? ' fw-semibold' : '';
            $html .= '<div class="list-group-item d-flex align-items-center gap-2 py-2' . ($unread ? ' bg-light bg-opacity-50' : '') . '">';
            if ($cb) {
                $html .= '<input type="checkbox" class="form-check-input mt-0">';
            }
            $star = ! empty($m['starred']) ? 'ti-star-filled text-warning' : 'ti-star text-muted';
            $html .= '<i class="ti ' . $star . '" role="button"></i>';
            if (! empty($m['avatar'])) {
                $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $m['avatar'], '/'))) . '" class="rounded-circle" width="36" height="36" alt="">';
            }
            $html .= '<a href="' . $this->e($m['href'] ?? '#') . '" class="flex-grow-1 text-body text-truncate' . $bg . '">';
            $html .= '<span class="d-inline-block" style="width:140px;">' . $this->e($m['from'] ?? '') . '</span>';
            $html .= '<span class="text-truncate">' . $this->e($m['subject'] ?? '');
            if (! empty($m['excerpt'])) {
                $html .= ' <span class="text-muted fw-normal">— ' . $this->e($m['excerpt']) . '</span>';
            }
            $html .= '</span></a>';
            if (! empty($m['label'])) {
                $l = (array) $m['label'];
                $html .= '<span class="badge bg-' . $this->e($l['variant'] ?? 'primary') . '-subtle text-' . $this->e($l['variant'] ?? 'primary') . '">' . $this->e($l['text'] ?? '') . '</span>';
            }
            $html .= '<small class="text-muted" style="width:60px;text-align:right;">' . $this->e($m['time'] ?? '') . '</small>';
            $html .= '</div>';
        }

        return $html . '</div>';
    }
}

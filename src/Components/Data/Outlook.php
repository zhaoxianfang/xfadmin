<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 三栏邮件应用（outlook.html）
 *
 * XfAdmin::outlook([
 *     'folders' => [['icon'=>'ti ti-inbox','name'=>'收件箱','count'=>12,'active'=>true], ...],
 *     'messages' => [
 *         ['id'=>1,'from'=>'张三','avatar'=>'users/user-1.jpg','from_email'=>'z@x.com',
 *          'subject'=>'周末计划','preview'=>'…','time'=>'10:30','unread'=>true,'starred'=>false,'attachments'=>1],
 *     ],
 *     'selected' => ['from'=>'李四','from_email'=>'l@x.com','subject'=>'Re:…','time'=>'昨天','body'=>'…','attachments'=>[['name'=>'a.pdf','size'=>'1MB']]],
 * ])
 */
class Outlook extends Component
{
    protected function defaults(): array
    {
        return ['folders' => [], 'messages' => [], 'selected' => []];
    }

    protected function html(): string
    {
        $folders  = (array) $this->get('folders', []);
        $messages = (array) $this->get('messages', []);
        $sel      = (array) $this->get('selected', []);

        $html = '<div class="card"><div class="row g-0 outlook-app" style="min-height:560px">';

        // 文件夹
        $html .= '<div class="col-md-3 col-lg-2 border-end p-3 outlook-folders">';
        $html .= '<button class="btn btn-primary w-100 mb-3"><i class="ti ti-pencil me-1"></i>写邮件</button>';
        $html .= '<div class="list-group list-group-flush">';
        foreach ($folders as $f) {
            $f      = (array) $f;
            $active = ! empty($f['active']) ? ' active' : '';
            $count  = ! empty($f['count']) ? '<span class="badge bg-light ms-auto">' . (int) $f['count'] . '</span>' : '';
            $html .= '<a href="#" class="list-group-item list-group-item-action d-flex align-items-center' . $active . '"><i class="' . $this->e($f['icon'] ?? 'ti ti-folder') . ' me-2"></i>' . $this->e($f['name'] ?? '') . $count . '</a>';
        }
        $html .= '</div></div>';

        // 邮件列表
        $html .= '<div class="col-md-5 col-lg-4 border-end p-0 outlook-list">';
        $html .= '<div class="p-3 border-bottom"><div class="position-relative"><i class="ti ti-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i><input type="text" class="form-control ps-5" placeholder="搜索邮件…"></div></div>';
        $html .= '<div class="outlook-messages">';
        foreach ($messages as $m) {
            $m      = (array) $m;
            $unread = ! empty($m['unread']) ? ' unread' : '';
            $star   = ! empty($m['starred']) ? 'ti ti-star-filled text-warning' : 'ti ti-star text-muted';
            $av     = ! empty($m['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $m['avatar']) : '';
            $att    = ! empty($m['attachments']) ? '<i class="ti ti-paperclip ms-1"></i>' : '';
            $html .= '<a href="#" class="d-flex gap-2 p-3 border-bottom outlook-msg' . $unread . '">';
            $html .= '<div class="avatar avatar-md flex-shrink-0"><img src="' . $this->e($av) . '" class="rounded-circle" alt=""></div>';
            $html .= '<div class="flex-grow-1 min-w-0"><div class="d-flex justify-content-between"><span class="fw-semibold text-truncate">' . $this->e($m['from'] ?? '') . '</span><small class="text-muted ms-2">' . $this->e($m['time'] ?? '') . '</small></div>';
            $html .= '<div class="text-truncate small">' . $this->e($m['subject'] ?? '') . '</div>';
            $html .= '<div class="text-muted text-truncate small">' . $this->e($m['preview'] ?? '') . $att . '</div></div>';
            $html .= '<i class="ti ' . $star . ' align-self-start"></i>';
            $html .= '</a>';
        }
        $html .= '</div></div>';

        // 阅读窗格
        $html .= '<div class="col-md-4 col-lg-6 p-0 outlook-read">';
        if ($sel) {
            $selAvHtml = ! empty($sel['avatar'])
                ? '<img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $sel['avatar'])) . '" class="rounded-circle" alt="">'
                : '<span class="avatar-title bg-primary-subtle text-primary rounded-circle">' . $this->e(mb_substr((string) ($sel['from'] ?? '?'), 0, 1)) . '</span>';
            $html .= '<div class="p-3 border-bottom d-flex justify-content-between align-items-center">';
            $html .= '<div><h6 class="mb-0">' . $this->e($sel['subject'] ?? '') . '</h6><small class="text-muted">' . $this->e($sel['time'] ?? '') . '</small></div>';
            $html .= '<div><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-archive"></i></button><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-trash"></i></button><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-dots-vertical"></i></button></div>';
            $html .= '</div>';
            $html .= '<div class="p-3">';
            $html .= '<div class="d-flex align-items-center mb-3"><div class="avatar avatar-md me-2">' . $selAvHtml . '</div><div><div class="fw-semibold">' . $this->e($sel['from'] ?? '') . '</div><small class="text-muted">' . $this->e($sel['from_email'] ?? '') . '</small></div><span class="ms-auto text-muted small">收件人：我</span></div>';
            $html .= '<div class="email-body">' . nl2br($this->e($sel['body'] ?? '')) . '</div>';
            if (! empty($sel['attachments'])) {
                $html .= '<div class="mt-3 d-flex gap-2 flex-wrap">';
                foreach ((array) $sel['attachments'] as $a) {
                    $a = (array) $a;
                    $html .= '<a href="#" class="btn btn-sm btn-light"><i class="ti ti-file-text text-primary me-1"></i>' . $this->e($a['name'] ?? '') . ' <small class="text-muted">' . $this->e($a['size'] ?? '') . '</small></a>';
                }
                $html .= '</div>';
            }
            $html .= '<hr><div class="d-flex gap-2"><button class="btn btn-primary btn-sm"><i class="ti ti-reply me-1"></i>回复</button><button class="btn btn-light btn-sm"><i class="ti ti-share me-1"></i>转发</button></div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="d-flex h-100 align-items-center justify-content-center text-muted">请选择一封邮件查看</div>';
        }
        $html .= '</div>';

        return $html . '</div></div>';
    }
}

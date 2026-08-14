<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 三栏邮件应用（email.html 风格）
 *
 * 对齐 INSPINIA email.html：左侧文件夹（list-custom）+ 中间邮件列表（table table-hover table-select，
 * 含 勾选/星标/头像/发件人/主题+预览/时间/附件）+ 右侧阅读窗格。
 *
 * XfAdmin::emailApp([
 *     'folders' => [['icon'=>'ti ti-inbox','name'=>'收件箱','count'=>12,'active'=>true], ...],
 *     'messages' => [
 *         ['id'=>1,'from'=>'张三','avatar'=>'users/user-1.jpg','from_email'=>'z@x.com',
 *          'subject'=>'周末计划','preview'=>'…','time'=>'10:30','unread'=>true,'starred'=>false,'attachments'=>1],
 *     ],
 *     'selected' => ['from'=>'李四','avatar'=>'...','from_email'=>'l@x.com','subject'=>'Re:…','time'=>'昨天','body'=>'…','attachments'=>[['name'=>'a.pdf','size'=>'1MB']]],
 *     'view' => 'split',          // split | preview（仅列表，点击外部打开）
 * ])
 */
class EmailApp extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['folders' => [], 'messages' => [], 'selected' => [], 'view' => 'split', 'composeText' => '写邮件'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $folders = (array) $this->get('folders', []);
        $messages = (array) $this->get('messages', []);
        $sel = (array) $this->get('selected', []);
        $view = $this->get('view');
        $composeText = $this->get('composeText');

        $html = '<div class="card" data-xf="email"><div class="row g-0 email-app" style="min-height:560px">';

        // 左侧文件夹
        $html .= '<div class="col-xl-2 col-lg-3 border-end p-3 email-sidebar">';
        $html .= '<button class="btn btn-primary w-100 mb-3"><i class="ti ti-pencil me-1"></i>' . $this->e($composeText) . '</button>';
        $html .= '<div class="list-group list-group-flush list-custom">';
        foreach ($folders as $f) {
            $f = (array) $f;
            $active = !empty($f['active']) ? ' active' : '';
            $count = !empty($f['count']) ? '<span class="badge bg-light ms-auto">' . (int) $f['count'] . '</span>' : '';
            $html .= '<a href="#" class="list-group-item list-group-item-action d-flex align-items-center' . $active . '"><i class="' . $this->e($f['icon'] ?? 'ti ti-folder') . ' me-2"></i><span>' . $this->e($f['name'] ?? '') . '</span>' . $count . '</a>';
        }
        $html .= '</div></div>';

        // 中间邮件列表（table 式，对齐 email.html）
        $html .= '<div class="col-xl-4 col-lg-5 border-end p-0 email-list d-flex flex-column">';
        $html .= '<div class="p-3 border-bottom d-flex align-items-center gap-2">';
        $html .= '<div class="app-search flex-grow-1"><input type="text" class="form-control" placeholder="搜索邮件…" data-email-search><span class="app-search-icon"><i class="ti ti-search"></i></span></div>';
        $html .= '<button class="btn btn-icon btn-light rounded-circle"><i class="ti ti-adjustments-horizontal"></i></button>';
        $html .= '</div>';
        $html .= '<div class="table-responsive flex-grow-1" style="overflow-y:auto">';
        $html .= '<table class="table table-hover table-select align-middle mb-0 email-table"><tbody>';
        foreach ($messages as $m) {
            $m = (array) $m;
            $unread = !empty($m['unread']) ? ' unread' : '';
            $star = !empty($m['starred']) ? 'ti ti-star-filled text-warning' : 'ti ti-star text-muted';
            $av = !empty($m['avatar']) ? XfAdmin::img((string) $m['avatar']) : '';
            $att = !empty($m['attachments']) ? '<td class="text-center" style="width:40px"><i class="ti ti-paperclip text-muted"></i></td>' : '';
            $html .= '<tr class="' . ltrim($unread) . '">';
            $html .= '<td style="width:40px"><div class="form-check email-item-check"><input class="form-check-input" type="checkbox"></div></td>';
            $html .= '<td style="width:40px"><button class="btn btn-icon btn-sm btn-ghost-light rounded-circle email-action-btn' . (!empty($m['starred']) ? ' active' : '') . '"><i class="' . $star . '"></i></button></td>';
            $html .= '<td style="width:45px"><div class="avatar"><img src="' . $this->e($av) . '" class="rounded-circle avatar-xs" alt=""></div></td>';
            $html .= '<td class="text-nowrap"><h5 class="fs-base fw-medium mb-0">' . $this->e($m['from'] ?? '') . '</h5></td>';
            $html .= '<td><a href="#" class="link-reset fs-base' . (!empty($m['unread']) ? ' fw-semibold' : '') . ' stretched-link">' . $this->e($m['subject'] ?? '') . '</a><div class="text-muted text-truncate">' . $this->e($m['preview'] ?? '') . '</div></td>';
            $html .= '<td class="text-nowrap" style="width:90px"><span class="fs-sm text-muted">' . $this->e($m['time'] ?? '') . '</span></td>';
            $html .= $att;
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';

        // 右侧阅读窗格
        if ($view === 'split') {
            $html .= $this->renderReadPane($sel);
        }
        return $html . '</div></div>';
    }

    /**
     * render Read Pane（protected实例方法）
     *
     * @param array $sel sel
     *
     * @return string result
     */
    protected function renderReadPane(array $sel): string
    {
        $html = '<div class="col-xl-6 col-lg-4 p-0 email-read">';
        if ($sel) {
            $selAvHtml = ! empty($sel['avatar'])
                ? '<img src="' . $this->e(XfAdmin::img((string) $sel['avatar'])) . '" class="rounded-circle" alt="">'
                : '<span class="avatar-title bg-primary-subtle text-primary rounded-circle">' . $this->e(mb_substr((string) ($sel['from'] ?? '?'), 0, 1)) . '</span>';
            $html .= '<div class="p-3 border-bottom d-flex justify-content-between align-items-center">';
            $html .= '<div><h6 class="mb-0">' . $this->e($sel['subject'] ?? '') . '</h6><small class="text-muted">' . $this->e($sel['time'] ?? '') . '</small></div>';
            $html .= '<div><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-archive"></i></button><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-trash"></i></button><button class="btn btn-sm btn-icon btn-light"><i class="ti ti-dots-vertical"></i></button></div>';
            $html .= '</div>';
            $html .= '<div class="p-3" style="overflow-y:auto;max-height:520px">';
            $html .= '<div class="d-flex align-items-center mb-3"><div class="avatar avatar-md me-2">' . $selAvHtml . '</div><div><div class="fw-semibold">' . $this->e($sel['from'] ?? '') . '</div><small class="text-muted">' . $this->e($sel['from_email'] ?? '') . '</small></div><span class="ms-auto text-muted small">收件人：我</span></div>';
            $html .= '<div class="email-body">' . nl2br($this->e($sel['body'] ?? '')) . '</div>';
            if (!empty($sel['attachments'])) {
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
        return $html . '</div>';
    }
}

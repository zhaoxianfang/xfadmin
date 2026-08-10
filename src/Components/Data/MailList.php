<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 邮件列表（紧凑卡片版，复用于仪表盘等）
 *
 * 与 EmailApp 中间列表保持一致的表格式邮件行（对齐 INSPINIA email.html）：
 * 勾选 / 星标 / 头像(avatar-xs) / 发件人 / 主题+预览 / 时间 / 附件。
 *
 * XfAdmin::mailList([
 *     'title' => '收件箱',
 *     'action' => ['text'=>'查看全部','url'=>'#'],
 *     'items' => [
 *         ['from'=>'张三','avatar'=>'users/user-1.jpg','subject'=>'周末计划','preview'=>'…','time'=>'10:30',
 *          'unread'=>true,'starred'=>false,'attachments'=>1],
 *     ],
 * ])
 */
class MailList extends Component
{
    protected function defaults(): array
    {
        return ['title' => '', 'action' => [], 'items' => []];
    }

    protected function html(): string
    {
        $title = (string) $this->get('title', '');
        $action = (array) $this->get('action', []);
        $items = (array) $this->get('items', []);

        $html = '<div class="card"><div class="card-header bg-light bg-opacity-25">';
        $html .= '<h5 class="mb-0">' . $this->e($title) . '</h5>';
        if (!empty($action)) {
            $html .= '<a href="' . $this->e($action['url'] ?? '#') . '" class="link-reset fs-sm fw-medium">' . $this->e($action['text'] ?? '查看全部') . ' <i class="ti ti-chevron-right"></i></a>';
        }
        $html .= '</div>';
        $html .= '<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-select align-middle mb-0 email-table"><tbody>';
        foreach ($items as $m) {
            $m = (array) $m;
            $unread = !empty($m['unread']) ? ' unread' : '';
            $star = !empty($m['starred']) ? 'ti ti-star-filled text-warning' : 'ti ti-star text-muted';
            $av = !empty($m['avatar']) ? XfAdmin::img((string) $m['avatar']) : '';
            $badge = !empty($m['badge']) ? ' <span class="badge bg-' . $this->e($m['badgeVariant'] ?? 'primary') . '-subtle text-' . $this->e($m['badgeVariant'] ?? 'primary') . ' ms-1">' . $this->e($m['badge']) . '</span>' : '';
            $att = !empty($m['attachments']) ? '<td class="text-center" style="width:40px"><i class="ti ti-paperclip text-muted"></i></td>' : '';
            $html .= '<tr class="' . ltrim($unread) . '">';
            $html .= '<td style="width:40px"><div class="form-check email-item-check"><input class="form-check-input" type="checkbox"></div></td>';
            $html .= '<td style="width:40px"><button class="btn btn-icon btn-sm btn-ghost-light rounded-circle email-action-btn' . (!empty($m['starred']) ? ' active' : '') . '"><i class="' . $star . '"></i></button></td>';
            $html .= '<td style="width:45px"><div class="avatar"><img src="' . $this->e($av) . '" class="rounded-circle avatar-xs" alt=""></div></td>';
            $html .= '<td class="text-nowrap"><h5 class="fs-base fw-medium mb-0">' . $this->e($m['from'] ?? '') . $badge . '</h5></td>';
            $html .= '<td><a href="#" class="link-reset fs-base' . (!empty($m['unread']) ? ' fw-semibold' : '') . ' stretched-link">' . $this->e($m['subject'] ?? '') . '</a><div class="text-muted text-truncate">' . $this->e($m['preview'] ?? '') . '</div></td>';
            $html .= '<td class="text-nowrap" style="width:90px"><span class="fs-sm text-muted">' . $this->e($m['time'] ?? '') . '</span></td>';
            $html .= $att;
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div></div>';

        return $html;
    }
}

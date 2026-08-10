<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 动态/活动流（project-activity.html）
 *
 * XfAdmin::activityFeed([
 *     'items' => [
 *         ['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'action' => '评论了任务',
 *          'target' => '首页改版', 'time' => '2 小时前', 'text' => '看起来不错'],
 *         ['icon' => 'ti ti-check', 'variant' => 'success', 'user' => '系统',
 *          'action' => '完成部署', 'time' => '昨天'],
 *     ],
 * ])
 */
class ActivityFeed extends Component
{
    protected function defaults(): array
    {
        return [
            'items' => [],
        ];
    }

    protected function html(): string
    {
        $html = '<div' . $this->attrs(['class' => 'activity-feed']) . '>';
        foreach ((array) $this->get('items', []) as $it) {
            $html .= '<div class="d-flex gap-3 pb-3 mb-3 border-bottom">';
            if (! empty($it['avatar'])) {
                // 动态头像：INSPINIA 规范 .avatar 包裹（avatar-md=36px，与 .activity-feed .avatar 的 36px 约束一致）
                $html .= '<span class="avatar avatar-md flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $it['avatar'])) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span>';
            } else {
                $variant = $it['variant'] ?? 'primary';
                $icon = $it['icon'] ?? 'ti ti-point';
                // 图标占位：与图片头像同尺寸（avatar-md），保证时间线左侧对齐
                $html .= '<span class="avatar avatar-md flex-shrink-0"><span class="avatar-title bg-' . $this->e($variant) . '-subtle text-' . $this->e($variant) . ' rounded-circle"><i class="' . $this->e($icon) . '"></i></span></span>';
            }
            $html .= '<div class="flex-grow-1">';
            $html .= '<p class="mb-0"><strong>' . $this->e($it['user'] ?? '') . '</strong> ' . $this->e($it['action'] ?? '');
            if (! empty($it['target'])) {
                $html .= ' <a href="' . $this->e($it['href'] ?? '#') . '">' . $this->e($it['target']) . '</a>';
            }
            $html .= '</p>';
            if (! empty($it['text'])) {
                // text 为动态正文（多为用户生成内容），按纯文本转义输出，避免 XSS
                $html .= '<p class="text-muted mb-1">' . $this->e($it['text'] ?? '') . '</p>';
            }
            $html .= '<small class="text-muted">' . $this->e($it['time'] ?? '') . '</small>';
            $html .= '</div></div>';
        }

        return $html . '</div>';
    }
}

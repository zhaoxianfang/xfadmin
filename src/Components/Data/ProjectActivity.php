<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 项目动态时间线（对应 INSPINIA project-activity.html）
 *
 * 以纵向时间线展示某个项目的最近动态：每条记录含操作者头像、动作标题、
 * 说明文字与发生时间，按时间倒序排列。适合项目详情页的「动态 / 进展」区块。
 * 与通用 Timeline 组件的区别：本组件聚焦「谁 + 做了什么」，并内嵌操作者头像。
 *
 * 配置项：
 *  - title 区块标题（默认「项目动态」）
 *  - items 动态数组，每条：
 *      user  操作者名称
 *      avatar 操作者头像相对路径
 *      title 动作标题
 *      desc  说明文字（可选，支持内联 HTML）
 *      time  发生时间（如「10 分钟前」，可选）
 *      color 主题色（默认 primary，可选 success/info/warning/danger 等）
 *      icon  图标类名（可选）
 *
 * XfAdmin::projectActivity([
 *     'title' => '项目动态',
 *     'items' => [
 *         ['user' => '张三', 'avatar' => 'users/user-1.jpg', 'title' => '创建了任务「登录页重构」', 'desc' => '从设计稿拆分为 5 个子任务', 'time' => '10 分钟前', 'color' => 'primary'],
 *         ['user' => '李四', 'avatar' => 'users/user-2.jpg', 'title' => '提交了代码', 'time' => '1 小时前', 'color' => 'success'],
 *     ],
 * ])
 */
class ProjectActivity extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['title' => '项目动态', 'items' => []];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        if (empty($items)) {
            return '';
        }
        $html = '<div class="card"><div class="card-body">';
        $html .= '<h4 class="card-title mb-3">' . $this->e($this->get('title', '项目动态')) . '</h4>';
        $html .= '<div class="timeline">';

        foreach ($items as $it) {
            $it     = (array) $it;
            $avatar = ! empty($it['avatar'])
                ? \zxf\XfAdmin\XfAdmin::img((string) $it['avatar'])
                : '';
            $color = $it['color'] ?? 'primary';
            $icon  = $it['icon'] ?? 'ti ti-activity';

            $html .= '<div class="timeline-item d-flex align-items-stretch">';
            $html .= '<div class="timeline-dot flex-shrink-0"><span class="avatar-xs"><span class="avatar-title bg-' . $this->e($color) . '-subtle text-' . $this->e($color) . ' rounded-circle"><i class="' . $this->e($icon) . '"></i></span></span></div>';
            $html .= '<div class="timeline-content ps-3 pb-4">';
            $html .= '<div class="d-flex align-items-center mb-1">';
            if ($avatar) {
                $html .= '<img src="' . $this->e($avatar) . '" class="avatar avatar-sm rounded-circle me-2" alt="' . $this->e($it['user'] ?? '') . '">';
            }
            $html .= '<span class="fw-semibold">' . $this->e($it['user'] ?? '') . '</span>';
            if (! empty($it['time'])) {
                $html .= '<span class="text-muted ms-auto small">' . $this->e($it['time']) . '</span>';
            }
            $html .= '</div>';
            if (! empty($it['title'])) {
                $html .= '<div class="mb-1">' . $this->e($it['title']) . '</div>';
            }
            if (! empty($it['desc'])) {
                $html .= '<div class="text-muted small">' . $this->raw($it['desc']) . '</div>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

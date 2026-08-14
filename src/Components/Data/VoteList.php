<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 投票列表（INSPINIA vote-list.html）
 *
 * XfAdmin::voteList([
 *     'title' => '社区投票',
 *     'items' => [
 *         [
 *             'votes'    => 35,
 *             'title'    => '远程办公是否应成为长期选项？',
 *             'desc'     => '本投票探讨远程办公是否应作为长期弹性选项保留。',
 *             'author'   => ['avatar' => 'users/user-7.jpg', 'name' => '陈晓'],
 *             'date'     => '2026-01-12',
 *             'tag'      => '职场',
 *             'comments' => 89,
 *             'ends'     => '5 天后',
 *             'total'    => 1284,
 *             'status'   => '进行中',       // 状态文字（可空）
 *             'variant'  => 'success',      // 状态色
 *             'url'      => '#',
 *         ],
 *     ],
 * ])
 */
class VoteList extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title' => '',
            'items' => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $items = (array) $this->get('items', []);
        $title = (string) $this->get('title', '');

        $html = '<div' . $this->attrs(['class' => 'card xf-vote-list', 'data-xf' => 'votelist']) . '>';
        if ($title !== '') {
            $html .= '<div class="card-header border-light"><h5 class="card-title mb-0">' . $this->e($title) . '</h5></div>';
        }
        $html .= '<div class="card-body p-0">';

        foreach ($items as $it) {
            $html .= '<div class="border-bottom border-dashed px-4 py-3"><div class="d-flex gap-4 align-items-center">';

            // 投票区
            $html .= '<div><div class="vstack gap-1 text-center xf-vote-box">'
                . '<div><button type="button" class="btn p-0 btn-link xf-vote-up" aria-label="赞成"><i class="ti ti-chevron-up fs-xxl"></i></button></div>'
                . '<h5 class="fw-bold m-0 fs-lg xf-vote-count">' . (int) ($it['votes'] ?? 0) . '</h5>'
                . '<div><button type="button" class="btn p-0 btn-link xf-vote-down" aria-label="反对"><i class="ti ti-chevron-down fs-xxl"></i></button></div>'
                . '</div></div>';

            // 内容区
            $html .= '<div class="flex-grow-1">';
            $html .= '<h4 class="fs-md mb-1"><a href="' . $this->e($it['url'] ?? '#') . '" class="link-reset">' . $this->e($it['title'] ?? '') . '</a></h4>';
            if (! empty($it['desc'])) {
                $html .= '<p class="text-muted mb-2">' . $this->e($it['desc']) . '</p>';
            }
            $html .= '<p class="d-flex flex-wrap gap-3 text-muted mb-1 align-items-center fs-base">';
            $author = $it['author'] ?? null;
            if (is_array($author)) {
                $html .= '<span class="d-flex align-items-center gap-1">';
                if (! empty($author['avatar'])) {
                    // 作者头像：统一用 .avatar 包裹（avatar-xs = 1.5rem/24px），替代裸 img + 尺寸类写法
                    $html .= '<span class="avatar avatar-xs flex-shrink-0"><img src="' . $this->e(\zxf\XfAdmin\XfAdmin::img((string) $author['avatar'])) . '" class="img-fluid rounded-circle" alt=""></span>';
                }
                $html .= '<a href="#" class="link-reset fw-semibold">' . $this->e($author['name'] ?? '') . '</a></span>';
            }
            if (! empty($it['date'])) {
                $html .= '<span><i class="ti ti-calendar"></i> 发布于 ' . $this->e($it['date']) . '</span>';
            }
            if (! empty($it['tag'])) {
                $html .= '<span class="d-flex align-items-center gap-1"><i class="ti ti-tag"></i> <span class="badge bg-light text-primary">' . $this->e($it['tag']) . '</span></span>';
            }
            if (isset($it['comments'])) {
                $html .= '<span><i class="ti ti-message-circle"></i> 评论 ' . (int) $it['comments'] . '</span>';
            }
            if (! empty($it['ends'])) {
                $html .= '<span><i class="ti ti-clock"></i> 截止：' . $this->e($it['ends']) . '</span>';
            }
            if (isset($it['total'])) {
                $html .= '<span><i class="ti ti-users"></i> 投票数 ' . number_format((int) $it['total']) . '</span>';
            }
            if (! empty($it['status'])) {
                $variant = $this->e($it['variant'] ?? 'secondary');
                $html .= '<span class="d-flex align-items-center gap-1"><i class="ti ti-lock"></i> <span class="badge text-bg-' . $variant . '">' . $this->e($it['status']) . '</span></span>';
            }
            $html .= '</p></div></div></div>';
        }
        return $html . '</div></div>';
    }
}

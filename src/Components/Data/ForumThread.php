<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 论坛主题（forum-view.html / forum-post.html）
 *
 * XfAdmin::forumThread([
 *     'thread' => ['title'=>'如何优化 Laravel 性能？','category'=>'技术问答','author'=>['name'=>'张三','avatar'=>'users/user-1.jpg'],
 *                  'views'=>320,'replies'=>3,'created_at'=>'2026-07-20','body'=>'…','tags'=>['PHP','Laravel']],
 *     'posts' => [
 *         ['author'=>['name'=>'李四','avatar'=>'users/user-2.jpg','role'=>'版主'],'created_at'=>'2026-07-21',
 *          'body'=>'…','likes'=>12,'is_solution'=>true,'attachments'=>[['name'=>'code.php']]],
 *     ],
 * ])
 */
class ForumThread extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['thread' => [], 'posts' => []];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $thread = (array) $this->get('thread', []);
        if (empty($thread)) {
            return '';
        }
        $html = '<div class="card mb-3"><div class="card-body">';
        $html .= '<div class="mb-2"><a href="#" class="badge bg-primary-subtle text-primary">' . $this->e($thread['category'] ?? '') . '</a></div>';
        $html .= '<h3 class="mb-2">' . $this->e($thread['title'] ?? '') . '</h3>';

        $tags = '';
        foreach ((array) ($thread['tags'] ?? []) as $t) {
            $tags .= '<span class="badge bg-light text-dark me-1">#' . $this->e($t) . '</span>';
        }
        $au   = ! empty($thread['author']['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $thread['author']['avatar']) : '';
        $html .= '<div class="d-flex align-items-center gap-2 text-muted small">';
        $html .= '<div class="avatar avatar-xs"><img src="' . $this->e($au) . '" class="rounded-circle" alt=""></div>';
        $html .= '<span>' . $this->e($thread['author']['name'] ?? '') . '</span>';
        $html .= '<span>·</span><span>' . $this->e($thread['created_at'] ?? '') . '</span>';
        $html .= '<span>·</span><i class="ti ti-eye"></i> ' . (int) ($thread['views'] ?? 0);
        $html .= '<span>·</span><i class="ti ti-message-2"></i> ' . (int) ($thread['replies'] ?? 0);
        if ($tags) {
            $html .= '<span class="ms-2">' . $tags . '</span>';
        }
        $html .= '</div></div>';

        // 主帖
        $html .= '<div class="card mt-3"><div class="card-body"><div class="d-flex gap-3">';
        $html .= '<div class="flex-shrink-0 text-center"><div class="avatar avatar-md"><img src="' . $this->e($au) . '" class="rounded-circle" alt=""></div><div class="small fw-semibold mt-1">' . $this->e($thread['author']['name'] ?? '') . '</div></div>';
        $html .= '<div class="flex-grow-1">' . nl2br($this->e($thread['body'] ?? '')) . '</div>';
        $html .= '</div></div></div>';

        // 回复
        $posts = (array) $this->get('posts', []);
        if ($posts) {
            $html .= '<h5 class="mt-4 mb-3">' . count($posts) . ' 条回复</h5>';
            foreach ($posts as $post) {
                $post  = (array) $post;
                $pa    = ! empty($post['author']['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $post['author']['avatar']) : '';
                $sol   = ! empty($post['is_solution']);
                $html .= '<div class="card mb-3' . ($sol ? ' border-success' : '') . '"><div class="card-body"><div class="d-flex gap-3">';
                $html .= '<div class="flex-shrink-0 text-center"><div class="avatar avatar-md"><img src="' . $this->e($pa) . '" class="rounded-circle" alt=""></div><div class="small fw-semibold mt-1">' . $this->e($post['author']['name'] ?? '') . '</div>';
                if (! empty($post['author']['role'])) {
                    $html .= '<span class="badge bg-primary-subtle text-primary">' . $this->e($post['author']['role']) . '</span>';
                }
                $html .= '</div>';
                $html .= '<div class="flex-grow-1"><div class="d-flex justify-content-between"><small class="text-muted">' . $this->e($post['created_at'] ?? '') . '</small>';
                if ($sol) {
                    $html .= '<span class="badge bg-success-subtle text-success"><i class="ti ti-circle-check me-1"></i>最佳答案</span>';
                }
                $html .= '</div><div class="mt-1">' . nl2br($this->e($post['body'] ?? '')) . '</div>';
                if (! empty($post['attachments'])) {
                    $html .= '<div class="mt-2 d-flex gap-2 flex-wrap">';
                    foreach ((array) $post['attachments'] as $a) {
                        $a = (array) $a;
                        $html .= '<a href="#" class="btn btn-sm btn-light"><i class="ti ti-paperclip me-1"></i>' . $this->e($a['name'] ?? '') . '</a>';
                    }
                    $html .= '</div>';
                }
                $html .= '<div class="mt-2"><button class="btn btn-sm btn-light"><i class="ti ti-thumb-up me-1"></i>' . (int) ($post['likes'] ?? 0) . '</button> <button class="btn btn-sm btn-light">回复</button></div>';
                $html .= '</div></div></div></div>';
            }
        }
        // 回复框
        $html .= '<div class="card"><div class="card-body"><h6 class="mb-2">发表回复</h6>'
            . '<textarea class="form-control mb-2" rows="3" placeholder="写下你的回答…"></textarea>'
            . '<button class="btn btn-primary">提交回复</button></div></div>';

        return $html;
    }
}

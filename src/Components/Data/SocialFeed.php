<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 社交动态流（apps-social-feed.html）
 *
 * 头像 + 用户名 + 时间 + 文本内容 + 配图 + 点赞/评论/转发 操作条。
 *
 * XfAdmin::socialFeed([
 *     'posts' => [
 *         [
 *             'avatar'   => 'users/user-1.jpg',
 *             'name'     => '林晓',
 *             'handle'   => '@linxiao',
 *             'time'     => '2 分钟前',
 *             'text'     => '刚刚上线了全新的数据看板，体验丝滑！',
 *             'image'    => 'products/1.png',
 *             'likes'    => 128,
 *             'comments' => 24,
 *             'shares'   => 8,
 *         ],
 *         [
 *             'avatar' => 'users/user-2.jpg',
 *             'name'   => '陈昊',
 *             'time'   => '15 分钟前',
 *             'text'   => '分享一篇关于数据驱动运营的干货文章。',
 *         ],
 *     ],
 *     'title' => '团队动态',
 * ])
 */
class SocialFeed extends Component
{
    protected function defaults(): array
    {
        return [
            'posts' => [],
            'title' => '团队动态',
        ];
    }

    protected function html(): string
    {
        $posts = (array) $this->get('posts', []);
        $title = (string) $this->get('title', '团队动态');

        $html = '<div class="card"><div class="card-header"><h5 class="mb-0">' . $this->e($title) . '</h5></div><div class="card-body">';

        if (empty($posts)) {
            $html .= '<div class="text-center text-muted py-4">暂无动态</div></div></div>';

            return $html;
        }

        foreach ($posts as $p) {
            $p       = (array) $p;
            $avatar  = $this->img((string) ($p['avatar'] ?? ''));
            $name    = (string) ($p['name'] ?? '匿名用户');
            $handle  = (string) ($p['handle'] ?? '');
            $time    = (string) ($p['time'] ?? '');
            $text    = (string) ($p['text'] ?? '');
            $image   = (string) ($p['image'] ?? '');
            $likes   = (int) ($p['likes'] ?? 0);
            $comment = (int) ($p['comments'] ?? 0);
            $shares  = (int) ($p['shares'] ?? 0);

            $html .= '<div class="d-flex gap-3 mb-4 pb-4 border-bottom">';
            $html .= '<img src="' . $this->e($avatar) . '" class="rounded-circle avatar-sm" alt="' . $this->e($name) . '">';
            $html .= '<div class="flex-fill">';
            $html .= '<div class="d-flex justify-content-between align-items-center mb-1">';
            $html .= '<div><span class="fw-semibold">' . $this->e($name) . '</span>';
            if ($handle !== '') {
                $html .= ' <span class="text-muted">' . $this->e($handle) . '</span>';
            }
            $html .= '</div><small class="text-muted">' . $this->e($time) . '</small></div>';
            $html .= '<p class="mb-2">' . $this->e($text) . '</p>';
            if ($image !== '') {
                $html .= '<img src="' . $this->e($this->img($image)) . '" class="img-fluid rounded mb-2" alt="">';
            }
            $html .= '<div class="d-flex gap-4 text-muted small">';
            $html .= '<span><i class="ti ti-heart me-1"></i>' . $likes . '</span>';
            $html .= '<span><i class="ti ti-message-circle me-1"></i>' . $comment . '</span>';
            $html .= '<span><i class="ti ti-share me-1"></i>' . $shares . '</span>';
            $html .= '</div></div></div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}

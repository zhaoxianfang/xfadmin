<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 博客文章详情（blog/article.html）
 *
 * XfAdmin::blogArticle([
 *     'article' => [
 *         'title' => '…', 'category' => '技术', 'date' => '2026-07-20', 'read_time' => '8 分钟',
 *         'author' => ['name'=>'张三','avatar'=>'users/user-1.jpg','bio'=>'…'],
 *         'cover' => 'gallery/12.jpg',
 *         'body' => ['段落一…','段落二…'],
 *         'tags' => ['PHP','Laravel'],
 *         'related' => [['title'=>..,'excerpt'=>..,'image'=>..,'date'=>..]],
 *     ],
 * ])
 */
class BlogArticle extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['article' => []];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $a = (array) $this->get('article', []);
        if (empty($a)) {
            return '';
        }
        $av   = ! empty($a['author']['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $a['author']['avatar']) : '';
        $cover = ! empty($a['cover']) ? \zxf\XfAdmin\XfAdmin::img((string) $a['cover']) : '';

        $html = '<article>';
        $html .= '<div class="mb-3"><a href="#" class="badge bg-primary-subtle text-primary">' . $this->e($a['category'] ?? '') . '</a></div>';
        $html .= '<h1 class="mb-3">' . $this->e($a['title'] ?? '') . '</h1>';
        $html .= '<div class="d-flex align-items-center gap-2 mb-4">';
        $html .= '<div class="avatar avatar-sm"><img src="' . $this->e($av) . '" class="rounded-circle" alt=""></div>';
        $html .= '<span class="fw-semibold">' . $this->e($a['author']['name'] ?? '') . '</span>';
        $html .= '<span class="text-muted">· ' . $this->e($a['date'] ?? '') . ' · ' . $this->e($a['read_time'] ?? '') . '</span>';
        $html .= '</div>';

        if ($cover) {
            $html .= '<img src="' . $this->e($cover) . '" class="img-fluid rounded mb-4 w-100 object-fit-cover" style="max-height:420px" alt="">';
        }
        foreach ((array) ($a['body'] ?? []) as $para) {
            $html .= '<p class="text-muted" style="line-height:1.9">' . nl2br($this->e($para)) . '</p>';
        }
        if (! empty($a['tags'])) {
            $html .= '<div class="mt-4">';
            foreach ((array) $a['tags'] as $t) {
                $html .= '<a href="#" class="badge bg-light text-dark me-1">#' . $this->e($t) . '</a>';
            }
            $html .= '</div>';
        }
        // 作者卡
        if (! empty($a['author'])) {
            $html .= '<div class="card mt-4 bg-light"><div class="card-body d-flex align-items-center gap-3">';
            $html .= '<div class="avatar avatar-lg"><img src="' . $this->e($av) . '" class="rounded-circle" alt=""></div>';
            $html .= '<div><h6 class="mb-1">' . $this->e($a['author']['name'] ?? '') . '</h6><p class="text-muted small mb-0">' . $this->e($a['author']['bio'] ?? '') . '</p></div>';
            $html .= '</div></div>';
        }
        $html .= '</article>';

        if (! empty($a['related'])) {
            $html .= '<h4 class="mt-5 mb-3">相关阅读</h4><div class="row g-3">';
            foreach ((array) $a['related'] as $r) {
                $r    = (array) $r;
                $rImg = ! empty($r['image']) ? \zxf\XfAdmin\XfAdmin::img((string) $r['image']) : '';
                $html .= '<div class="col-md-4"><div class="card h-100"><img src="' . $this->e($rImg) . '" class="card-img-top object-fit-cover" height="160" alt="" style="height:160px;"><div class="card-body"><h6 class="mb-1">' . $this->e($r['title'] ?? '') . '</h6><p class="text-muted small">' . $this->e($r['excerpt'] ?? '') . '</p><small class="text-muted">' . $this->e($r['date'] ?? '') . '</small></div></div></div>';
            }
            $html .= '</div>';
        }
        return $html;
    }
}

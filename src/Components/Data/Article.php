<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 文章阅读页（对应 INSPINIA article.html）
 *
 * 渲染一篇完整文章的阅读视图：标题与元信息（分类 / 日期 / 阅读时长）、
 * 封面大图、作者信息块（头像 + 简介）、正文段落、引用块、标签与「相关文章」网格。
 * 适合博客、资讯、帮助文档等详情页。
 *
 * 配置项（article）：
 *  - title    文章标题
 *  - category 分类标签（可选）
 *  - date     发布日期（可选）
 *  - read_time 阅读时长（可选）
 *  - author   ['name'=>作者名, 'avatar'=>头像相对路径, 'bio'=>简介]（可选）
 *  - cover    封面图相对路径（可选，固定 360px 高并裁切）
 *  - body     正文段落数组（支持内联 HTML）
 *  - quote    引用语（可选）
 *  - tags     标签数组（可选）
 *  - related  相关文章数组（可选）：[['title','excerpt','image','date']]
 *
 * XfAdmin::article([
 *     'article' => [
 *         'title'    => '如何构建高可用后台系统',
 *         'category' => '架构', 'date' => '2026-07-20', 'read_time' => '8 分钟',
 *         'author'   => ['name' => '张三', 'avatar' => 'users/user-1.jpg', 'bio' => '资深后端工程师'],
 *         'cover'    => 'blog/blog-post.jpg',
 *         'body'     => ['第一段正文……', '第二段正文……'],
 *         'quote'    => '架构的优雅在于取舍。',
 *         'tags'     => ['PHP', 'Laravel', '架构'],
 *         'related'  => [['title' => '相关文章一', 'excerpt' => '摘要……', 'image' => 'blog/blog-1.jpg', 'date' => '2026-07-10']],
 *     ],
 * ])
 */
class Article extends Component
{
    protected function defaults(): array
    {
        return ['article' => []];
    }

    protected function html(): string
    {
        $a = (array) $this->get('article', []);
        if (empty($a)) {
            return '';
        }

        $avatar = ! empty($a['author']['avatar'])
            ? \zxf\XfAdmin\XfAdmin::img((string) $a['author']['avatar'])
            : '';
        $cover = ! empty($a['cover'])
            ? \zxf\XfAdmin\XfAdmin::img((string) $a['cover'])
            : '';
        $author  = (array) ($a['author'] ?? []);
        $aname   = $author['name'] ?? '';
        $abio    = $author['bio'] ?? '';
        $body    = (array) ($a['body'] ?? []);
        $tags    = (array) ($a['tags'] ?? []);
        $related = (array) ($a['related'] ?? []);

        $html = '<article class="xf-article">';

        // 标题与元信息
        $html .= '<h1 class="h3 mb-1">' . $this->e($a['title'] ?? '') . '</h1>';
        $html .= '<div class="text-muted small mb-3">';
        if (! empty($a['category'])) {
            $html .= '<span class="badge bg-primary-subtle text-primary me-2">' . $this->e($a['category']) . '</span>';
        }
        if (! empty($a['date'])) {
            $html .= '<i class="ti ti-calendar me-1"></i>' . $this->e($a['date']);
        }
        if (! empty($a['read_time'])) {
            $html .= ' <i class="ti ti-clock ms-2 me-1"></i>' . $this->e($a['read_time']);
        }
        $html .= '</div>';

        // 封面（固定高度裁切，避免被 img{height:auto} 拉变形）
        if ($cover) {
            $html .= '<img src="' . $this->e($cover) . '" class="rounded w-100 xf-article-cover mb-4" alt="' . $this->e($a['title'] ?? '') . '">';
        }

        // 作者信息块
        $html .= '<div class="d-flex align-items-center mb-4">';
        if ($avatar) {
            $html .= '<img src="' . $this->e($avatar) . '" class="avatar avatar-xl rounded-circle me-3" alt="' . $this->e($aname) . '">';
        }
        $html .= '<div><div class="fw-semibold">' . $this->e($aname) . '</div>';
        if ($abio) {
            $html .= '<div class="text-muted small">' . $this->e($abio) . '</div>';
        }
        $html .= '</div></div>';

        // 正文段落
        foreach ($body as $p) {
            $html .= '<p class="text-muted">' . $this->raw($p) . '</p>';
        }

        // 引用块
        if (! empty($a['quote'])) {
            $html .= '<blockquote class="border-start border-3 border-primary ps-3 my-4 fst-italic">' . $this->e($a['quote']) . '</blockquote>';
        }

        // 标签
        if ($tags) {
            $html .= '<div class="mt-3">';
            foreach ($tags as $t) {
                $html .= '<span class="badge bg-light text-dark me-1">' . $this->e($t) . '</span>';
            }
            $html .= '</div>';
        }

        // 相关文章网格
        if ($related) {
            $html .= '<hr class="my-4"><h5 class="mb-3">相关文章</h5><div class="row g-3">';
            foreach ($related as $r) {
                $r    = (array) $r;
                $rimg = ! empty($r['image']) ? \zxf\XfAdmin\XfAdmin::img((string) $r['image']) : '';
                $html .= '<div class="col-md-4"><div class="card h-100">';
                if ($rimg) {
                    $html .= '<img src="' . $this->e($rimg) . '" class="card-img-top object-fit-cover" height="140" alt="" style="height:140px;">';
                }
                $html .= '<div class="card-body"><h6 class="mb-1">' . $this->e($r['title'] ?? '') . '</h6>';
                if (! empty($r['excerpt'])) {
                    $html .= '<p class="text-muted small mb-1">' . $this->e($r['excerpt']) . '</p>';
                }
                if (! empty($r['date'])) {
                    $html .= '<small class="text-muted">' . $this->e($r['date']) . '</small>';
                }
                $html .= '</div></div></div>';
            }
            $html .= '</div>';
        }

        $html .= '</article>';

        return $html;
    }
}

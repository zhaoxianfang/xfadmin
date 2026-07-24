<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 博客文章列表（blog.html / blog-details.html）—— 卡片网格或列表视图
 *
 * XfAdmin::blogList([
 *     'items' => [
 *         [
 *             'image'   => 'images/blog/1.jpg',
 *             'category'=> '技术',
 *             'title'   => '如何构建后台系统',
 *             'excerpt' => '本文介绍……',
 *             'author'  => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg'],
 *             'date'    => '2026-07-01',
 *             'comments'=> 12,
 *             'views'   => 320,
 *             'url'     => '/blog/1',
 *             'tags'    => ['Laravel', '后台'],
 *         ],
 *     ],
 *     'layout' => 'grid',   // grid | list
 *     'cols'   => 3,
 * ])
 */
class BlogList extends Component
{
    protected function defaults(): array
    {
        return [
            'items'  => [],
            'layout' => 'grid',
            'cols'   => 3,
            'gap'    => '24px',
        ];
    }

    protected function html(): string
    {
        $id    = $this->resolveId('blog');
        $items = array_values((array) $this->get('items', []));
        $layout = $this->get('layout') === 'list' ? 'list' : 'grid';

        if ($layout === 'list') {
            $html = '<div' . $this->attrs(['class' => 'xf-blog-list d-flex flex-column gap-3', 'id' => $id]) . '>';
            foreach ($items as $p) {
                $html .= $this->row((array) $p);
            }

            return $html . '</div>';
        }

        $colCls = $this->colClass((int) $this->get('cols'));
        $html = '<div' . $this->attrs(['class' => 'xf-blog-grid row g-4', 'id' => $id]) . '>';
        foreach ($items as $p) {
            $html .= '<div class="' . $colCls . '">' . $this->card((array) $p) . '</div>';
        }

        return $html . '</div>';
    }

    private function card(array $p): string
    {
        $image = $p['image'] ?? '';
        $url   = $p['url'] ?? '#';
        $html = '<div class="card h-100 border-0 shadow-sm overflow-hidden">';
        if ($image) {
            $html .= '<a href="' . $this->e($url) . '"><img src="' . $this->e(XfAdmin::asset('images/' . ltrim($image, '/'))) . '" class="card-img-top" style="height:200px;object-fit:cover;" loading="lazy" alt=""></a>';
        }
        $html .= '<div class="card-body d-flex flex-column">';
        if (! empty($p['category'])) {
            $html .= '<span class="badge bg-primary-subtle text-primary mb-2 align-self-start">' . $this->e($p['category']) . '</span>';
        }
        $html .= '<h5 class="card-title mb-2"><a href="' . $this->e($url) . '" class="text-decoration-none">' . $this->e($p['title'] ?? '') . '</a></h5>';
        if (! empty($p['excerpt'])) {
            $html .= '<p class="text-muted small flex-grow-1">' . $this->e($p['excerpt']) . '</p>';
        }
        $html .= $this->meta($p);
        $html .= '</div></div>';

        return $html;
    }

    private function row(array $p): string
    {
        $image = $p['image'] ?? '';
        $url   = $p['url'] ?? '#';
        $html = '<div class="card border-0 shadow-sm overflow-hidden">';
        $html .= '<div class="row g-0">';
        if ($image) {
            $html .= '<div class="col-md-4"><a href="' . $this->e($url) . '"><img src="' . $this->e(XfAdmin::asset('images/' . ltrim($image, '/'))) . '" class="img-fluid h-100" style="object-fit:cover;min-height:180px;" loading="lazy" alt=""></a></div>';
        }
        $html .= '<div class="col-md-8"><div class="card-body">';
        if (! empty($p['category'])) {
            $html .= '<span class="badge bg-primary-subtle text-primary mb-2">' . $this->e($p['category']) . '</span>';
        }
        $html .= '<h5 class="card-title"><a href="' . $this->e($url) . '" class="text-decoration-none">' . $this->e($p['title'] ?? '') . '</a></h5>';
        if (! empty($p['excerpt'])) {
            $html .= '<p class="text-muted">' . $this->e($p['excerpt']) . '</p>';
        }
        $html .= $this->meta($p);
        $html .= '</div></div></div></div>';

        return $html;
    }

    private function meta(array $p): string
    {
        $html = '<div class="d-flex align-items-center gap-2 mt-2 small text-muted">';
        if (! empty($p['author']['avatar'])) {
            $html .= '<img src="' . $this->e(XfAdmin::asset('images/' . ltrim((string) $p['author']['avatar'], '/'))) . '" class="rounded-circle" width="28" height="28" alt="">';
        }
        if (! empty($p['author']['name'])) {
            $html .= '<span class="fw-medium text-body">' . $this->e($p['author']['name']) . '</span>';
        }
        if (! empty($p['date'])) {
            $html .= '<span class="ms-auto">' . $this->e($p['date']) . '</span>';
        }
        if (isset($p['comments'])) {
            $html .= '<span class="ti ti-message-circle ms-2">' . $this->e($p['comments']) . '</span>';
        }
        if (isset($p['views'])) {
            $html .= '<span class="ti ti-eye ms-2">' . $this->e($p['views']) . '</span>';
        }
        $html .= '</div>';
        if (! empty($p['tags'])) {
            $html .= '<div class="mt-2 d-flex flex-wrap gap-1">' . $this->tags($p['tags']) . '</div>';
        }

        return $html;
    }

    private function tags(array $tags): string
    {
        $out = '';
        foreach ($tags as $t) {
            $out .= '<a href="#" class="badge text-bg-light text-decoration-none">' . $this->e($t) . '</a>';
        }

        return $out;
    }

    private function colClass(int $cols): string
    {
        $map = [1 => 'col-12', 2 => 'col-md-6', 3 => 'col-md-4', 4 => 'col-md-3'];
        $cls = $map[$cols] ?? 'col-md-4';

        return ' ' . $cls;
    }
}

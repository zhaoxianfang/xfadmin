<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 条款 / 协议页（pages-terms-conditions.html）
 *
 * 「侧栏目录 + 分节正文」的法律条款版式，目录随滚动高亮（Bootstrap Scrollspy），
 * 适用于服务条款、隐私政策、用户协议等长文档页面。
 *
 * XfAdmin::terms([
 *     'title'      => '服务条款',
 *     'updated_at' => '2026-07-01',              // 最近更新时间（可选）
 *     'intro'      => '<p>欢迎使用本服务…</p>',    // 前言 HTML（可选）
 *     'sections'   => [                          // 分节内容
 *         ['id' => 'usage', 'title' => '1. 使用规范', 'content' => '<p>…</p>'],
 *         ['id' => 'privacy', 'title' => '2. 隐私保护', 'content' => '<p>…</p>'],
 *     ],
 *     'toc'        => true,                      // 是否显示侧栏目录
 *     'accept'     => null,                      // 底部「同意」按钮：['label'=>'我已阅读并同意','url'=>..] 或 HTML
 * ])
 */
class Terms extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'      => '服务条款',
            'updated_at' => null,
            'intro'      => null,
            'sections'   => [],
            'toc'        => true,
            'accept'     => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id       = $this->resolveId('xf-terms');
        $sections = array_values((array) $this->get('sections', []));
        $showToc  = (bool) $this->get('toc', true) && $sections !== [];

        // ---------- 侧栏目录（scrollspy 跟随高亮） ----------
        $tocHtml = '';
        if ($showToc) {
            $tocHtml = '<div class="col-lg-3 d-none d-lg-block"><div class="xf-terms-toc sticky-top" style="top:90px">'
                . '<h6 class="text-muted text-uppercase fs-12 mb-2">目录</h6>'
                . '<nav class="nav flex-column nav-pills xf-terms-nav" id="' . $id . '-toc">';
            foreach ($sections as $i => $sec) {
                $sid = (string) ($sec['id'] ?? 'sec-' . $i);
                $tocHtml .= '<a class="nav-link py-1 px-2" href="#' . $this->e($id . '-' . $sid) . '">' . $this->e($sec['title'] ?? '') . '</a>';
            }
            $tocHtml .= '</nav></div></div>';
        }
        // ---------- 正文 ----------
        $body = '<div class="' . ($showToc ? 'col-lg-9' : 'col-12') . '"><div class="card"><div class="card-body p-4 xf-terms-body">';
        $body .= '<h3 class="mb-1">' . $this->e($this->get('title')) . '</h3>';
        if ($this->get('updated_at')) {
            $body .= '<p class="text-muted mb-3"><i class="ti ti-clock me-1"></i>最近更新：' . $this->e($this->get('updated_at')) . '</p>';
        }
        if ($this->get('intro') !== null) {
            $body .= '<div class="xf-terms-intro text-muted mb-4">' . $this->raw($this->get('intro')) . '</div>';
        }
        foreach ($sections as $i => $sec) {
            $sid = (string) ($sec['id'] ?? 'sec-' . $i);
            $body .= '<section class="xf-terms-section mb-4" id="' . $this->e($id . '-' . $sid) . '">'
                . '<h5 class="mb-2">' . $this->e($sec['title'] ?? '') . '</h5>'
                . '<div class="text-muted">' . $this->raw($sec['content'] ?? '') . '</div></section>';
        }
        // ---------- 底部「同意」按钮（可选） ----------
        $accept = $this->get('accept');
        if ($accept !== null) {
            if (is_array($accept)) {
                $body .= '<div class="border-top pt-3 mt-4 text-end">'
                    . '<a href="' . $this->e($accept['url'] ?? 'javascript:void(0);') . '" class="btn btn-primary">'
                    . '<i class="ti ti-check me-1"></i>' . $this->e($accept['label'] ?? '我已阅读并同意') . '</a></div>';
            } else {
                $body .= '<div class="border-top pt-3 mt-4">' . $this->raw($accept) . '</div>';
            }
        }
        $body .= '</div></div></div>';

        // 仅在启用侧栏目录时开启 Bootstrap Scrollspy（目录不存在时开启会导致
        // ScrollSpy 回退扫描全页锚点、遇到 href="#!" 抛非法选择器异常）
        $spy = $showToc
            ? ' data-bs-spy="scroll" data-bs-target="#' . $id . '-toc" data-bs-smooth-scroll="true" tabindex="0"'
            : '';

        return '<div class="row xf-terms" id="' . $id . '"' . $spy . '>' . $tocHtml . $body . '</div>';
    }
}

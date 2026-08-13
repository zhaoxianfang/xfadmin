<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 隐私政策页面（pages-privacy-policy.html）
 *
 * XfAdmin::privacyPolicy([
 *     'title' => 'Privacy Policy',
 *     'effectiveDate' => 'April 19, 2025',
 *     'intro' => 'This Privacy Policy explains how we collect...',
 *     'sections' => [
 *         ['title' => '1. Information We Collect', 'body' => '<p>We may collect personal details such as...</p>'],
 *         ['title' => '2. How We Use Your Information', 'body' => '<p>Your information is used to provide...</p>'],
 *         ...
 *     ],
 *     'contactEmail' => 'privacy@example.com',
 * ])
 */
class PrivacyPolicy extends Component
{
    protected function defaults(): array
    {
        return [
            'title' => 'Privacy Policy',
            'effectiveDate' => '',
            'intro' => '',
            'sections' => [],
            'contactEmail' => '',
        ];
    }

    protected function html(): string
    {
        $title = (string) $this->get('title', 'Privacy Policy');
        $effectiveDate = (string) $this->get('effectiveDate', '');
        $intro = (string) $this->get('intro', '');
        $sections = (array) $this->get('sections', []);
        $contactEmail = (string) $this->get('contactEmail', '');

        $html = '<div class="row justify-content-center"><div class="col-xxl-9">';

        // 标题区
        $html .= '<div class="text-center my-4">';
        $html .= '<h1 class="fw-bold">' . $this->e($title) . '</h1>';
        if ($effectiveDate) {
            $html .= '<p class="text-muted">Effective Date: ' . $this->e($effectiveDate) . '</p>';
        }
        $html .= '</div>';

        // 内容区
        $html .= '<div class="card"><div class="card-body">';

        if ($intro) {
            $html .= '<p class="fst-italic fs-sm">' . $this->e($intro) . '</p>';
        }

        foreach ($sections as $i => $section) {
            $section = (array) $section;
            $sTitle = (string) ($section['title'] ?? '');
            $sBody = (string) ($section['body'] ?? '');
            $sList = (array) ($section['list'] ?? []);

            if ($sTitle) {
                $mtClass = $i === 0 ? 'mt-3' : 'mt-4';
                $html .= '<h4 class="fw-bold ' . $mtClass . '">' . $this->e($sTitle) . '</h4>';
            }
            if ($sBody) {
                // 允许 body 包含一些安全的 HTML（如 <p>, <a>, <strong> 等）
                $html .= $this->raw($sBody);
            }
            if (!empty($sList)) {
                $html .= '<ul>';
                foreach ($sList as $item) {
                    $html .= '<li>' . $this->e((string) $item) . '</li>';
                }
                $html .= '</ul>';
            }
        }

        if ($contactEmail) {
            $html .= '<h4 class="fw-bold mt-4">Contact Us</h4>';
            $html .= '<p>If you have any questions or concerns about this ' . $this->e($title)
                . ', please contact us at <a href="mailto:' . $this->e($contactEmail) . '">'
                . $this->e($contactEmail) . '</a>.</p>';
        }

        $html .= '</div></div>';

        // 最后更新
        if ($effectiveDate) {
            $html .= '<div class="text-center mt-4 mb-2"><small class="text-muted">Last updated: '
                . $this->e($effectiveDate) . '</small></div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}

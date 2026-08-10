<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 营销落地页（landing.html）—— 返回完整独立页面（Page）
 *
 * return XfAdmin::landing([
 *     'brand'   => 'XfAdmin',
 *     'nav'     => [['text'=>'功能','url'=>'#features'],['text'=>'价格','url'=>'#pricing']],
 *     'hero'    => ['title'=>'…','subtitle'=>'…','primary'=>'立即体验','secondary'=>'查看文档','image'=>'gallery/1.jpg'],
 *     'stats'   => [['value'=>'100+','label'=>'组件'}, ...],
 *     'features'=> [['icon'=>'ti ti-bolt','title'=>'…','text'=>'…']],
 *     'pricing' => [['title'=>'专业版','price'=>'$99','features'=>[...],'highlight'=>true,'button'=>'选择']],
 *     'testimonials' => [['name'=>'张三','role'=>'CTO','avatar'=>'users/user-1.jpg','text'=>'…']],
 *     'footer'  => ['text'=>'© 2026 …','links'=>[['text'=>'关于','url'=>'#']]],
 * ])
 */
class Landing extends Component
{
    protected function defaults(): array
    {
        return [
            'brand'        => 'XfAdmin',
            'nav'          => [],
            'hero'         => [],
            'stats'        => [],
            'features'     => [],
            'pricing'      => [],
            'testimonials' => [],
            'footer'       => [],
        ];
    }

    protected function html(): string
    {
        $brand = $this->get('brand');
        $nav   = (array) $this->get('nav', []);

        $navHtml = '';
        foreach ($nav as $n) {
            $n = (array) $n;
            $navHtml .= '<a href="' . $this->e($n['url'] ?? '#') . '" class="nav-link">' . $this->e($n['text'] ?? '') . '</a>';
        }

        $header = '<header class="landing-header"><nav class="container d-flex align-items-center py-3">'
            . '<a href="#" class="navbar-brand fw-bold fs-4 text-primary"><i class="ti ti-layout-dashboard me-1"></i>' . $this->e($brand) . '</a>'
            . '<div class="d-none d-md-flex gap-3 ms-4">' . $navHtml . '</div>'
            . '<div class="ms-auto"><a href="#" class="btn btn-primary btn-sm">免费开始</a></div>'
            . '</nav></header>';

        $html = $header;
        $html .= $this->hero();
        $html .= $this->stats();
        $html .= $this->features();
        $html .= $this->pricing();
        $html .= $this->testimonials();
        $html .= $this->cta();
        $html .= $this->footer();

        return (string) XfAdmin::page([
            'title'      => $this->get('hero')['title'] ?? $brand,
            'menu'       => [],
            'sidenav'    => false,
            'topbar'     => false,
            'footer'     => false,
            'container'  => '',
            'customizer' => false,
            'body_class' => 'landing-page',
            'content'    => $html,
        ]);
    }

    private function hero(): string
    {
        $h = (array) $this->get('hero', []);
        if (empty($h)) {
            return '';
        }
        $img = ! empty($h['image']) ? \zxf\XfAdmin\XfAdmin::img((string) $h['image']) : '';

        return '<section class="landing-hero"><div class="container"><div class="row align-items-center g-4">'
            . '<div class="col-lg-6"><h1 class="display-4 fw-bold mb-3">' . $this->e($h['title'] ?? '') . '</h1>'
            . '<p class="lead text-muted mb-4">' . $this->e($h['subtitle'] ?? '') . '</p>'
            . '<div class="d-flex gap-2"><a href="#" class="btn btn-primary btn-lg">' . $this->e($h['primary'] ?? '立即体验') . '</a>'
            . '<a href="#" class="btn btn-outline-secondary btn-lg">' . $this->e($h['secondary'] ?? '了解更多') . '</a></div></div>'
            . '<div class="col-lg-6">' . ($img ? '<img src="' . $this->e($img) . '" class="img-fluid rounded shadow-lg" alt="">' : '') . '</div>'
            . '</div></div></section>';
    }

    private function stats(): string
    {
        $stats = (array) $this->get('stats', []);
        if (empty($stats)) {
            return '';
        }
        $html = '<section class="py-5 bg-light"><div class="container"><div class="row text-center g-4">';
        foreach ($stats as $s) {
            $s = (array) $s;
            $html .= '<div class="col-6 col-md-3"><div class="display-6 fw-bold text-primary">' . $this->e($s['value'] ?? '') . '</div><div class="text-muted">' . $this->e($s['label'] ?? '') . '</div></div>';
        }

        return $html . '</div></div></section>';
    }

    private function features(): string
    {
        $feats = (array) $this->get('features', []);
        if (empty($feats)) {
            return '';
        }
        $html = '<section id="features" class="py-5"><div class="container"><div class="text-center mb-5"><h2 class="fw-bold">核心功能</h2></div><div class="row g-4">';
        foreach ($feats as $f) {
            $f = (array) $f;
            $html .= '<div class="col-md-4"><div class="card h-100 border-0 shadow-sm"><div class="card-body text-center p-4">'
                . '<div class="avatar avatar-lg bg-primary-subtle text-primary mx-auto mb-3"><i class="' . $this->e($f['icon'] ?? 'ti ti-bolt') . ' fs-3"></i></div>'
                . '<h5>' . $this->e($f['title'] ?? '') . '</h5><p class="text-muted mb-0">' . $this->e($f['text'] ?? '') . '</p></div></div></div>';
        }

        return $html . '</div></div></section>';
    }

    private function pricing(): string
    {
        $plans = (array) $this->get('pricing', []);
        if (empty($plans)) {
            return '';
        }
        $html = '<section id="pricing" class="py-5 bg-light"><div class="container"><div class="text-center mb-5"><h2 class="fw-bold">价格方案</h2></div><div class="row g-4 justify-content-center">';
        foreach ($plans as $p) {
            $p        = (array) $p;
            $highlight = ! empty($p['highlight']);
            $html .= '<div class="col-lg-4"><div class="card h-100 border-0 shadow-sm' . ($highlight ? ' border-primary border-2' : '') . '">';
            if ($highlight) {
                $html .= '<div class="ribbon"><span>推荐</span></div>';
            }
            $html .= '<div class="card-body p-4 text-center">';
            $html .= '<h5 class="mb-1">' . $this->e($p['title'] ?? '') . '</h5>';
            $html .= '<div class="display-6 fw-bold my-3">' . $this->e($p['price'] ?? '') . '<small class="text-muted fs-6">/年</small></div>';
            $html .= '<ul class="list-unstyled text-start mb-4">';
            foreach ((array) ($p['features'] ?? []) as $ft) {
                $html .= '<li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>' . $this->e($ft) . '</li>';
            }
            $html .= '</ul>';
            $html .= '<a href="#" class="btn ' . ($highlight ? 'btn-primary' : 'btn-outline-primary') . ' w-100">' . $this->e($p['button'] ?? '选择') . '</a>';
            $html .= '</div></div></div>';
        }

        return $html . '</div></div></section>';
    }

    private function testimonials(): string
    {
        $ts = (array) $this->get('testimonials', []);
        if (empty($ts)) {
            return '';
        }
        $html = '<section class="py-5"><div class="container"><div class="text-center mb-5"><h2 class="fw-bold">用户评价</h2></div><div class="row g-4">';
        foreach ($ts as $t) {
            $t   = (array) $t;
            $av  = ! empty($t['avatar']) ? \zxf\XfAdmin\XfAdmin::img((string) $t['avatar']) : '';
            $html .= '<div class="col-md-4"><div class="card h-100"><div class="card-body p-4">'
                . '<div class="text-warning mb-2"><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i></div>'
                . '<p class="text-muted">“' . $this->e($t['text'] ?? '') . '”</p>'
                . '<div class="d-flex align-items-center mt-3"><div class="avatar avatar-sm me-2"><img src="' . $this->e($av) . '" class="rounded-circle" alt=""></div><div><div class="fw-semibold small">' . $this->e($t['name'] ?? '') . '</div><small class="text-muted">' . $this->e($t['role'] ?? '') . '</small></div></div>'
                . '</div></div></div>';
        }

        return $html . '</div></div></section>';
    }

    private function cta(): string
    {
        return '<section class="py-5 bg-primary text-white"><div class="container text-center"><h2 class="fw-bold mb-3">准备好开始了吗？</h2><p class="mb-4 opacity-75">立即创建你的后台，几分钟内上线。</p><a href="#" class="btn btn-light btn-lg">免费开始</a></div></section>';
    }

    private function footer(): string
    {
        $f = (array) $this->get('footer', []);
        $links = '';
        foreach ((array) ($f['links'] ?? []) as $l) {
            $l = (array) $l;
            $links .= '<a href="' . $this->e($l['url'] ?? '#') . '" class="text-white-50 me-3">' . $this->e($l['text'] ?? '') . '</a>';
        }

        return '<footer class="landing-footer py-4"><div class="container d-flex flex-wrap justify-content-between align-items-center">'
            . '<span class="text-white-50">' . $this->e($f['text'] ?? '© 2026 ' . $this->get('brand')) . '</span>'
            . '<div>' . $links . '</div></div></footer>';
    }
}

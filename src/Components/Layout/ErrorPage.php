<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\XfAdmin;

/**
 * 错误页（404 / 500 / 503 …）
 *
 * echo XfAdmin::errorPage([
 *     'code'    => 404,
 *     'heading' => '页面不存在',
 *     'message' => '您访问的页面不存在或已被移动。',
 *     'home_url'=> '/',
 * ]);
 */
class ErrorPage extends AuthPage
{
    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            'code'      => 404,
            'image'     => null,      // 默认取包内 images/svg/{code}.svg
            'heading'   => 'Page Not Found',
            'message'   => '',
            'home_url'  => '/',
            'home_text' => '返回首页',
            'card'      => false,
        ]);
    }

    protected function html(): string
    {
        $code  = (string) $this->get('code');
        $image = $this->get('image') ?? XfAdmin::assets()->url('images/svg/' . $code . '.svg');

        $content = '<div class="p-2 text-center">'
            . '<img src="' . $this->e($image) . '" alt="' . $this->e($code) . '" class="img-fluid">'
            . '<h3 class="fw-bold text-uppercase">' . $this->e($this->get('heading')) . '</h3>'
            . '<p class="text-muted">' . $this->e($this->get('message')) . '</p>'
            . '<a class="btn btn-primary mt-3 rounded-pill" href="' . $this->e($this->get('home_url')) . '">' . $this->e($this->get('home_text')) . '</a>'
            . '</div>';

        $this->set('content', $content);
        if (! $this->get('title')) {
            $this->set('title', $code . ' - ' . $this->get('heading'));
        }

        return parent::html();
    }
}

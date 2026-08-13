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
        $image = $this->get('image');
        if ($image === null) {
            // 仅当包内确实提供对应插画时才引用，避免破图（支持任意错误码全量演示）
            $svgFile = dirname(__DIR__, 3) . '/resources/assets/images/svg/' . $code . '.svg';
            $image = @is_file($svgFile)
                ? XfAdmin::assets()->url('images/svg/' . $code . '.svg')
                : null;
        }

        if ($image) {
            $visual = '<img src="' . $this->e($image) . '" alt="' . $this->e($code) . '" class="img-fluid">';
        } else {
            // 无插画资源时降级为「大号状态码 + 图标」，避免破图
            $visual = '<div class="xf-error-code display-1 fw-bold text-primary">' . $this->e($code) . '</div>';
        }

        $content = '<div class="p-2 text-center">'
            . $visual
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

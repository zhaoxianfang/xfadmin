<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 响应式媒体容器（视频 / iframe / 图片，Bootstrap Ratio）
 *
 * XfAdmin::ratio([
 *     'ratio'  => '16x9',              // 1x1|4x3|16x9|21x9 或自定义百分比数字
 *     'src'    => 'https://...',       // iframe/video 源
 *     'type'   => 'iframe',            // iframe|video|content
 *     'body'   => '自定义内容',
 *     'allowfullscreen' => true,
 * ])
 */
class Ratio extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'ratio'  => '16x9',
            'src'    => null,
            'type'   => 'iframe',
            'body'   => null,
            'allowfullscreen' => true,
            'controls' => true,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // 键名容错：embed / content / html 视作自定义内容（type=content）
        if ($this->get('body') === null) {
            $alias = $this->get('embed') ?? $this->get('content') ?? $this->get('html');
            if ($alias !== null) {
                $this->options['body'] = $alias;
                $this->options['type'] = 'content';
            }
        }
        // src 为空且无内容时给出占位，避免渲染空 iframe（离线环境外链被拦时也有兜底展示）
        if (! $this->get('src') && $this->get('type') !== 'content') {
            $this->options['type'] = 'content';
            $this->options['body'] = $this->options['body']
                ?? '<div class="d-flex align-items-center justify-content-center w-100 h-100 bg-body-tertiary text-muted"><i class="ti ti-photo fs-1 me-2"></i>媒体占位</div>';
        }
        $ratio = (string) $this->get('ratio');
        $isPreset = in_array($ratio, ['1x1', '4x3', '16x9', '21x9'], true);

        $attrs = ['class' => Html::cls('ratio', $isPreset ? 'ratio-' . $ratio : null)];
        if (! $isPreset && is_numeric($ratio)) {
            $attrs['style'] = '--bs-aspect-ratio: ' . (float) $ratio . '%;';
        }
        $inner = '';
        switch ($this->get('type')) {
            case 'video':
                $ctrl = $this->get('controls') ? ' controls' : '';
                $inner = '<video' . $ctrl . '><source src="' . $this->e($this->get('src')) . '"></video>';
                break;
            case 'content':
                $inner = $this->raw($this->get('body'));
                break;
            case 'iframe':
            default:
                $fs = $this->get('allowfullscreen') ? ' allowfullscreen' : '';
                $inner = '<iframe src="' . $this->e($this->get('src')) . '" title="embed"' . $fs . '></iframe>';
        }
        return '<div' . $this->attrs($attrs) . '>' . $inner . '</div>';
    }
}

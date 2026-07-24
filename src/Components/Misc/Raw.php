<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 原样输出（用于把任意 HTML 混入组件树，同时可声明所需插件资源）
 *
 * XfAdmin::raw(['html' => '<div id="custom"></div>', 'plugins' => ['apexcharts'], 'js' => 'console.log("init")'])
 */
class Raw extends Component
{
    protected function defaults(): array
    {
        return [
            'html'    => '',
            'plugins' => [],
            'js'      => null,   // 追加内联 JS（自动去重可传 js_key）
            'js_key'  => null,
            'css'     => null,
            'css_key' => null,
        ];
    }

    protected function assets(): array
    {
        return (array) $this->get('plugins', []);
    }

    protected function html(): string
    {
        $assets = \zxf\XfAdmin\Assets\Assets::instance();
        if ($this->get('js') !== null) {
            $assets->inlineJs((string) $this->get('js'), $this->get('js_key'));
        }
        if ($this->get('css') !== null) {
            $assets->inlineCss((string) $this->get('css'), $this->get('css_key'));
        }

        return $this->raw($this->get('html'));
    }
}

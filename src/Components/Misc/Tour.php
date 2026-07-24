<?php

declare(strict_types=1);

namespace XfAdmin\Components\Misc;

use XfAdmin\Components\Component;

/**
 * 新手引导（TourGuide JS）
 *
 * XfAdmin::tour([
 *     'steps' => [
 *         ['target' => '#menu', 'title' => '导航菜单', 'content' => '在这里切换功能模块'],
 *         ['target' => '#search', 'title' => '搜索', 'content' => '全局搜索'],
 *     ],
 *     'auto'  => true,     // 页面加载后自动开始
 * ])
 */
class Tour extends Component
{
    protected function defaults(): array
    {
        return [
            'steps'   => [],
            'auto'    => false,
            'options' => [],
        ];
    }

    protected function assets(): array
    {
        return ['tourguide'];
    }

    protected function html(): string
    {
        $config = [
            'steps'   => array_values((array) $this->get('steps', [])),
            'auto'    => (bool) $this->get('auto'),
            'options' => (object) $this->get('options', []),
        ];

        return '<span' . $this->attrs([
            'data-xf'        => 'tour',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT),
            'hidden'         => true,
        ]) . '></span>';
    }
}

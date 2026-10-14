<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

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
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'steps' => [],
            'auto' => false,
            'options' => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['tourguide'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // options 传入 list 数组时 (object) 强转会得到 {"0":...}，JS 端按名取不到值；
        // 因此 list 数组按空对象处理，仅关联数组转为对象
        $o = (array) $this->get('options', []);
        $config = [
            'steps' => array_values((array) $this->get('steps', [])),
            'auto' => (bool) $this->get('auto'),
            'options' => array_is_list($o) ? (object) [] : (object) $o,
        ];

        return '<span'.$this->attrs([
            'data-xf' => 'tour',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'hidden' => true,
        ]).'></span>';
    }
}

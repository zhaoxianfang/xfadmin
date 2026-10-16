<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 数据源切换器（DataSourceSwitcher）
 *
 * 在同一区块内切换多个数据源面板（如「实时数据 / 历史数据 / 缓存数据」），
 * 前端 xfadmin.js 自动绑定切换逻辑，无需项目内联 <script>。
 *
 * 用法：
 *   XfAdmin::dataSourceSwitcher([
 *       'sources' => [
 *           ['key' => 'live', 'label' => '实时', 'active' => true],
 *           ['key' => 'history', 'label' => '历史'],
 *       ],
 *       'panes' => [
 *           'live' => '...实时数据面板 HTML...',
 *           'history' => '...历史数据面板 HTML...',
 *       ],
 *   ])
 *
 * 说明：
 *  - 每个面板生成唯一 id（paneId(key, prefix) 静态方法可被外部复用）；
 *  - 切换为纯 CSS/JS 显隐，不重新加载内容；
 *  - 默认激活项由 sources[*]['active'] 或 'active' 选项指定。
 */
class DataSourceSwitcher extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'sources' => [],        // [{key, label, icon?, active?}]
            'panes' => [],          // [key => html|Component|Closure]
            'active' => null,       // 默认激活的 key（优先于 sources[*]['active']）
            'prefix' => 'ds',       // 面板 id 前缀
            'variant' => 'tabs',    // tabs | pills（切换按钮样式）
            'class' => '',          // 根元素额外 class
        ];
    }

    /**
     * 生成面板唯一 id（供外部复用，例如 DataTable ajax.url 动态切换时定位面板）
     *
     * @param  string  $key  面板 key
     * @param  string  $prefix  前缀
     * @return string result
     */
    public static function paneId(string $key, string $prefix = 'ds'): string
    {
        return $prefix.'-pane-'.preg_replace('/[^a-zA-Z0-9_-]/', '-', $key);
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $sources = (array) $this->get('sources');
        $panes = (array) $this->get('panes');
        $prefix = (string) $this->get('prefix');
        $variant = $this->enum($this->get('variant'), ['tabs' => 'tabs', 'pills' => 'pills'], 'tabs');
        $active = $this->get('active');

        if ($sources === [] || $panes === []) {
            return '';
        }

        // 解析默认激活 key
        if ($active === null) {
            foreach ($sources as $s) {
                if (! empty($s['active'])) {
                    $active = $s['key'];
                    break;
                }
            }
        }
        if ($active === null) {
            $active = $sources[0]['key'] ?? null;
        }

        $rootId = $this->uid('xfdsw');

        $html = '<div'.$this->attrs([
            'class' => Html::cls('xf-datasource-switcher', $this->get('class')),
            'id' => $rootId,
            'data-xf' => 'dataSourceSwitcher',
            'data-xf-config' => Html::scriptJson([
                'active' => $active,
            ]),
        ]).'>';

        // 切换按钮组
        $html .= '<ul class="nav nav-'.$variant.' mb-3" role="tablist">';
        foreach ($sources as $s) {
            $key = (string) ($s['key'] ?? '');
            $label = (string) ($s['label'] ?? $key);
            $isActive = ($key === $active);
            $paneId = self::paneId($key, $prefix);
            $icon = $s['icon'] ?? null;
            $html .= '<li class="nav-item" role="presentation">';
            $html .= '<button type="button" class="nav-link'.($isActive ? ' active' : '').'" role="tab" data-xf-dsw-key="'.$this->escape($key).'" data-xf-dsw-target="#'.$this->escape($paneId).'"'.($isActive ? ' aria-selected="true"' : ' aria-selected="false"').'>';
            if ($icon !== null) {
                $html .= '<i class="'.$this->escape($this->iconClass($icon)).' me-1"></i>';
            }
            $html .= $this->escape($label);
            $html .= '</button>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        // 面板容器
        $html .= '<div class="tab-content">';
        foreach ($panes as $key => $content) {
            $key = (string) $key;
            $isActive = ($key === $active);
            $paneId = self::paneId($key, $prefix);
            $html .= '<div class="tab-pane'.($isActive ? ' show active' : '').'" id="'.$this->escape($paneId).'" role="tabpanel">';
            $html .= $this->raw($content);
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}

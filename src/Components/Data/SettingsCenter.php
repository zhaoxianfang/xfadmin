<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 设置中心（settings.html 抽象）
 *
 * 左侧分组导航 + 右侧表单面板，典型的后台「设置」页面。
 *
 * XfAdmin::settingsCenter([
 *     'title'  => '系统设置',
 *     'groups' => [
 *         [
 *             'id'     => 'general',                      // 锚点 id
 *             'icon'   => 'ti-settings',                 // 左侧图标
 *             'label'  => '常规',
 *             'title'  => '常规设置',                    // 右侧面板标题
 *             'desc'   => '站点基础信息',
 *             'body'   => XfAdmin::form([...]),          // 右侧内容（组件/HTML）
 *         ],
 *         ...
 *     ],
 * ])
 */
class SettingsCenter extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'  => '设置',
            'groups' => [],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $groups = (array) $this->get('groups', []);
        if (empty($groups)) {
            return '';
        }
        $title = $this->e($this->get('title', '设置'));

        $nav = '';
        $panels = '';
        $first = true;
        foreach ($groups as $g) {
            $g = (array) $g;
            $id    = $this->e($g['id'] ?? ('grp-' . md5((string) ($g['label'] ?? count($groups)))));
            $icon  = $this->e($g['icon'] ?? 'ti-circle');
            $label = $this->e($g['label'] ?? '');
            $ptitle = $this->e($g['title'] ?? $label);
            $desc  = $this->e($g['desc'] ?? '');
            $body  = $this->raw($g['body'] ?? '');

            $nav .= '<a href="#' . $id . '" class="list-group-item list-group-item-action d-flex align-items-center gap-2 ' . ($first ? 'active' : '') . '" data-bs-toggle="list">'
                . '<i class="ti ' . $icon . '"></i>' . $label . '</a>';

            $panels .= '<div class="tab-pane fade ' . ($first ? 'show active' : '') . '" id="' . $id . '">'
                . '<div class="d-flex justify-content-between align-items-center mb-3">'
                . '<div><h5 class="mb-1">' . $ptitle . '</h5>' . ($desc ? '<p class="text-muted mb-0 small">' . $desc . '</p>' : '') . '</div></div>'
                . $body
                . '</div>';

            $first = false;
        }
        return '<div class="card"><div class="card-header"><h4 class="mb-0">' . $title . '</h4></div>'
            . '<div class="card-body"><div class="row"><div class="col-md-3">'
            . '<div class="list-group list-group-flush ' . $this->e($this->get('nav_class', 'settings-nav')) . '" role="tablist">' . $nav . '</div>'
            . '</div><div class="col-md-9"><div class="tab-content">' . $panels . '</div></div></div></div></div>';
    }
}

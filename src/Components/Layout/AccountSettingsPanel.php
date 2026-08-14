<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 账户设置双栏面板
 *
 * 左侧 sidebar 导航（tab）+ 右侧内容区，点击切换，
 * 复刻 inspinia pages-account-settings.html 的设置布局。
 *
 * XfAdmin::accountSettingsPanel([
 *     'title' => '账户设置',
 *     'tabs'  => [
 *         ['id'=>'profile','label'=>'个人资料','icon'=>'ti ti-user','content'=>'...'],
 *         ['id'=>'security','label'=>'安全','icon'=>'ti ti-lock','content'=>'...'],
 *     ],
 *     'active' => 'profile',
 * ])
 */
class AccountSettingsPanel extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'  => '账户设置',
            'tabs'   => [],
            'active' => null,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $tabs   = (array) $this->get('tabs', []);
        $title  = $this->get('title');
        $active = $this->get('active') ?? ($tabs[0]['id'] ?? '');

        $id = $this->resolveId('xf-asp');

        $nav = '<div class="list-group list-group-flush xf-asp-nav">';
        foreach ($tabs as $t) {
            $t = (array) $t;
            $isActive = ($t['id'] ?? '') === $active;
            $nav .= '<a href="#' . $this->e($id . '-' . ($t['id'] ?? '')) . '" class="list-group-item list-group-item-action d-flex align-items-center gap-2 ' . ($isActive ? 'active' : '') . '" data-bs-toggle="list" role="tab">'
                . '<i class="' . $this->e($t['icon'] ?? 'ti ti-point') . '"></i>' . $this->e($t['label'] ?? '') . '</a>';
        }
        $nav .= '</div>';

        $panes = '<div class="tab-content xf-asp-content">';
        foreach ($tabs as $t) {
            $t = (array) $t;
            $isActive = ($t['id'] ?? '') === $active;
            $panes .= '<div class="tab-pane fade ' . ($isActive ? 'show active' : '') . '" id="' . $this->e($id . '-' . ($t['id'] ?? '')) . '" role="tabpanel">'
                . $this->raw($t['content'] ?? '') . '</div>';
        }
        $panes .= '</div>';

        return '<div class="card border-0 shadow-sm"><div class="card-body">'
            . '<h5 class="mb-3">' . $this->e($title) . '</h5>'
            . '<div class="row g-0"><div class="col-md-3 border-end">' . $nav . '</div>'
            . '<div class="col-md-9 ps-4">' . $panes . '</div></div>'
            . '</div></div>';
    }
}

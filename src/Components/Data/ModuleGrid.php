<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 模块网格 / 应用中心卡片墙
 *
 * 用于后台「企业应用中心」等场景：按分区展示一组模块入口卡片，
 * 把原先散落在各控制器里重复拼接的「分区标题 + 卡片行」提炼为可复用组件。
 *
 * XfAdmin::moduleGrid([
 *     'sections' => [
 *         '内容与创作' => [
 *             ['name' => '博客', 'desc' => '...', 'icon' => 'ti ti-article', 'url' => '/admin/app/blog'],
 *             ...
 *         ],
 *         '营销与客户' => [ ... ],
 *     ],
 *     'columns'  => 3,   // 每分区每行卡片数（手机 1 / 平板 2 / 桌面 columns）
 * ])
 */
class ModuleGrid extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'sections' => [],
            'columns'  => 4,     // 桌面列数
            'title'    => '',
            'subtitle' => '',
            'class'    => '',
            'id'       => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $sections = $this->get('sections');
        if (! is_array($sections)) {
            $sections = [];
        }
        $cols   = max(1, (int) $this->get('columns'));
        $colCls = 'col-md-6 col-xl-' . (int) (12 / $cols);

        $body = '';
        foreach ($sections as $section => $mods) {
            if (! is_array($mods)) {
                continue;
            }
            $items = '';
            foreach ($mods as $mod) {
                $icon = $this->e($mod['icon'] ?? 'ti ti-box');
                $name = $this->e($mod['name'] ?? '');
                $desc = $this->e($mod['desc'] ?? '');
                $url  = $this->e($mod['url'] ?? '#');
                $items .= '<a href="' . $url . '" class="col-12 ' . $colCls . ' text-decoration-none mb-3">'
                    . '<div class="card border h-100 hover-shadow"><div class="card-body">'
                    . '<div class="d-flex align-items-center mb-2">'
                    . '<span class="avatar avatar-sm bg-primary-subtle text-primary me-2"><i class="' . $icon . '"></i></span>'
                    . '<h6 class="mb-0">' . $name . '</h6></div>'
                    . '<div class="small text-muted">' . $desc . '</div>'
                    . '</div></div></a>';
            }
            $head = is_int($section) ? '' : '<h5 class="mt-4 mb-0">' . $this->e($section) . '</h5>'
                . '<div class="text-muted small mb-2">' . count($mods) . ' 个应用</div>';
            $body .= $head . '<div class="row g-3">' . $items . '</div>';
        }
        $header = '';
        if ($this->get('title') !== '') {
            $header .= '<div class="text-center py-4"><h2>' . $this->e($this->get('title')) . '</h2>';
            if ($this->get('subtitle') !== '') {
                $header .= '<p class="text-muted">' . $this->e($this->get('subtitle')) . '</p>';
            }
            $header .= '</div>';
        }
        return '<div class="' . $this->e($this->get('class')) . '">' . $header . '<div class="row g-3">' . $body . '</div></div>';
    }
}

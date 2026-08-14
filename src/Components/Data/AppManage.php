<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 应用管理中心（apps-manage.html）
 *
 * XfAdmin::appManage([
 *     'stats' => [
 *         ['title' => 'Total Apps', 'value' => '128', 'icon' => 'ti-apps', 'color' => 'primary', 'change' => '+12%'],
 *         ['title' => 'Active Users', 'value' => '8,549', 'icon' => 'ti-users', 'color' => 'success', 'change' => '+5.3%'],
 *         ['title' => 'Revenue', 'value' => '$24.8k', 'icon' => 'ti-chart-bar', 'color' => 'warning', 'change' => '+18.2%'],
 *         ['title' => 'Downtime', 'value' => '0.02%', 'icon' => 'ti-activity', 'color' => 'danger', 'change' => '-0.3%'],
 *     ],
 *     'apps' => [
 *         ['name' => 'App Name', 'icon' => 'ti-brand-slack', 'color' => 'primary', 'status' => 'active', 'description' => 'An amazing application for your daily needs.', 'users' => 1250, 'rating' => 4.8],
 *         // ...
 *     ],
 *     'maxApps' => 10,
 * ])
 */
class AppManage extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'stats' => [],
            'apps' => [],
            'maxApps' => 10,
            'search' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $stats = (array) $this->get('stats', []);
        $apps = (array) $this->get('apps', []);

        $html = '';

        // 统计卡片
        if (!empty($stats)) {
            $html .= $this->renderStats($stats);
        }
        // 工具栏
        $html .= $this->renderToolbar();

        // 应用列表
        if (!empty($apps)) {
            $html .= $this->renderAppGrid($apps);
        }
        return $html;
    }

    /**
     * render Stats（private实例方法）
     *
     * @param array $stats stats
     *
     * @return string result
     */
    private function renderStats(array $stats): string
    {
        $colClass = 'col-md-' . (12 / max(1, min(4, count($stats)))) . ' mb-3';

        $html = '<div class="row">';
        foreach ($stats as $stat) {
            $stat = (array) $stat;
            $color = $this->enum((string) ($stat['color'] ?? 'primary'), ['primary','secondary','success','danger','warning','info','dark'], 'primary');
            $icon = (string) ($stat['icon'] ?? 'ti-apps');
            $title = (string) ($stat['title'] ?? '');
            $value = (string) ($stat['value'] ?? '');
            $change = (string) ($stat['change'] ?? '');
            $changeColor = $this->getChangeColor($change);

            $html .= '<div class="' . $colClass . '"><div class="card"><div class="card-body"><div class="d-flex align-items-center justify-content-between">';
            $html .= '<div><span class="text-muted">' . $this->e($title) . '</span>';
            $html .= '<h3 class="mt-2 mb-0 fw-semibold">' . $this->e($value) . '</h3></div>';
            $html .= '<div class="bg-' . $this->e($color) . ' bg-opacity-10 rounded p-2">'
                . '<i class="ti ' . $this->e($icon) . ' fs-24 text-' . $this->e($color) . '"></i></div>';
            $html .= '</div>';
            if ($change) {
                $html .= '<div class="mt-2"><span class="text-' . $changeColor . ' small fw-semibold">'
                    . $this->e($change) . '</span></div>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * render Toolbar（private实例方法）
     *
     * @return string result
     */
    private function renderToolbar(): string
    {
        $html = '<div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '<div class="input-group" style="max-width:300px"><input type="text" class="form-control" placeholder="搜索应用...">';
        $html .= '<button class="btn btn-outline-secondary"><i class="ti ti-search"></i></button></div>';
        $html .= '<div class="d-flex align-items-center"><div class="dropdown"><button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">'
            . '排序: 默认 <i class="ti ti-chevron-down ms-1"></i></button>';
        $html .= '<ul class="dropdown-menu"><li><a class="dropdown-item" href="javascript:void(0)">名称 A-Z</a></li>'
            . '<li><a class="dropdown-item" href="javascript:void(0)">名称 Z-A</a></li><li><a class="dropdown-item" href="javascript:void(0)">最近更新</a></li>';
        $html .= '<li><a class="dropdown-item" href="javascript:void(0)">用户数</a></li></ul></div>';
        $html .= '<button class="btn btn-primary ms-2"><i class="ti ti-plus me-1"></i>新建应用</button></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * render App Grid（private实例方法）
     *
     * @param array $apps apps
     *
     * @return string result
     */
    private function renderAppGrid(array $apps): string
    {
        $html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3">';
        foreach ($apps as $app) {
            $app = (array) $app;
            $html .= $this->renderAppCard($app);
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * render App Card（private实例方法）
     *
     * @param array $app app
     *
     * @return string result
     */
    private function renderAppCard(array $app): string
    {
        $name = (string) ($app['name'] ?? '');
        $icon = (string) ($app['icon'] ?? 'ti-brand-slack');
        $color = (string) ($app['color'] ?? 'primary');
        $status = (string) ($app['status'] ?? 'active');
        $desc = (string) ($app['description'] ?? '');
        $users = (int) ($app['users'] ?? 0);
        $rating = (float) ($app['rating'] ?? 0);
        $image = !empty($app['image']) ? XfAdmin::img((string) $app['image']) : '';

        $statusBadge = match ($status) {
            'active' => '<span class="badge text-bg-success">运行中</span>',
            'inactive' => '<span class="badge text-bg-warning">已暂停</span>',
            'error' => '<span class="badge text-bg-danger">异常</span>',
            default => '<span class="badge text-bg-secondary">' . $this->e($status) . '</span>',
        };

        $html = '<div class="col"><div class="card"><div class="card-body"><div class="d-flex align-items-center mb-3">';
        if ($image) {
            $html .= '<img src="' . $this->e($image) . '" width="48" height="48" class="rounded me-3" alt="" style="width:48px;height:48px">';
        } else {
            $html .= '<div class="bg-' . $this->e($color) . ' bg-opacity-10 rounded p-2 me-3" style="width:48px;height:48px">'
                . '<i class="ti ' . $this->e($icon) . ' fs-24 text-' . $this->e($color) . ' d-flex align-items-center justify-content-center" style="height:32px"></i></div>';
        }
        $html .= '<div><h6 class="mb-0">' . $this->e($name) . '</h6>' . $statusBadge . '</div>';
        $html .= '<div class="ms-auto"><div class="dropdown"><a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown">'
            . '<i class="ti ti-dots-vertical"></i></a><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="javascript:void(0)">编辑</a></li>'
            . '<li><a class="dropdown-item" href="javascript:void(0)">设置</a></li><li><hr class="dropdown-divider"></li>'
            . '<li><a class="dropdown-item text-danger" href="javascript:void(0)">删除</a></li></ul></div></div></div>';

        if ($desc) {
            $html .= '<p class="text-muted small mb-3">' . $this->e($desc) . '</p>';
        }
        $html .= '<div class="d-flex align-items-center justify-content-between">';
        if ($users > 0) {
            $html .= '<span class="small text-muted"><i class="ti ti-users me-1"></i>' . number_format($users) . ' 用户</span>';
        }
        if ($rating > 0) {
            $html .= '<span class="small"><span class="text-warning me-1">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= $i <= round($rating) ? '<i class="ti ti-star-filled"></i>' : '<i class="ti ti-star"></i>';
            }
            $html .= '</span>' . number_format($rating, 1) . '</span>';
        }
        $html .= '</div>';

        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * get Change Color（private实例方法）
     *
     * @param string $change change
     *
     * @return string result
     */
    private function getChangeColor(string $change): string
    {
        if (str_starts_with($change, '+')) {
            return 'success';
        }
        if (str_starts_with($change, '-')) {
            return 'danger';
        }
        return 'muted';
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Concerns\HasPriceFormat;

/**
 * 小部件仪表盘（widgets.html）
 *
 * XfAdmin::widgetsDashboard([
 *     'widgets' => ['stats', 'charts', 'messages', 'activity', 'tasks', 'calendar'],
 *     'currency' => '¥',
 * ])
 */
class WidgetsDashboard extends Component
{
    use HasPriceFormat;

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'widgets' => ['stats', 'charts', 'messages', 'activity', 'tasks'],
            'currency' => '¥',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $widgets = (array) $this->get('widgets', []);
        $currency = (string) $this->get('currency', '¥');

        $html = '<div class="row g-3">';

        // 侧边栏小部件
        $html .= '<div class="col-lg-4">';
        if (in_array('messages', $widgets, true)) {
            $html .= $this->renderMessages();
        }
        if (in_array('tasks', $widgets, true)) {
            $html .= $this->renderTasks();
        }
        if (in_array('activity', $widgets, true)) {
            $html .= $this->renderActivity();
        }
        $html .= '</div>';

        // 主区域
        $html .= '<div class="col-lg-8">';
        if (in_array('stats', $widgets, true)) {
            $html .= $this->renderStats($currency);
        }
        if (in_array('charts', $widgets, true)) {
            $html .= $this->renderCharts();
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * render Stats（private实例方法）
     *
     * @param string $currency currency
     *
     * @return string result
     */
    private function renderStats(string $currency): string
    {
        $stats = [
            ['label' => '总收入', 'value' => 58240.00, 'icon' => 'ti-currency-dollar', 'color' => 'primary', 'trend' => '+12.5%', 'currency' => true],
            ['label' => '订单数', 'value' => 1284, 'icon' => 'ti-shopping-cart', 'color' => 'success', 'trend' => '+8.2%'],
            ['label' => '新用户', 'value' => 456, 'icon' => 'ti-user-plus', 'color' => 'info', 'trend' => '+15.3%'],
            ['label' => '转化率', 'value' => 3.45, 'icon' => 'ti-trending-up', 'color' => 'warning', 'trend' => '-0.8%', 'suffix' => '%'],
        ];

        $uid = 'ws_' . $this->uid();

        $html = '<div class="row g-2 mb-3">';
        foreach ($stats as $i => $stat) {
            $label = $this->e($stat['label']);
            $value = $stat['value'];
            $icon = $this->e($stat['icon']);
            $color = $this->e($stat['color']);
            $trend = $this->e($stat['trend']);
            $suffix = $this->e($stat['suffix'] ?? '');
            $trendClass = str_starts_with($trend, '+') ? 'text-success' : 'text-danger';
            $displayValue = ! empty($stat['currency']) ? $this->formatPrice((float) $value, $currency) : number_format((int) $value);

            $html .= '<div class="col-sm-6 col-xxl-3"><div class="card"><div class="card-body p-3"><div class="d-flex align-items-center">'
                . '<div class="flex-shrink-0"><span class="badge bg-' . $color . '-subtle text-' . $color . ' rounded-3 p-2">'
                . '<i class="ti ' . $icon . ' fs-20"></i></span></div>'
                . '<div class="flex-grow-1 ms-3"><h6 class="text-muted mb-0 small">' . $label . '</h6>'
                . '<h5 class="mb-0 mt-1">' . $displayValue . $suffix . '</h5>'
                . '<small class="' . $trendClass . '">' . $trend . '</small></div></div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * render Charts（private实例方法）
     *
     * @return string result
     */
    private function renderCharts(): string
    {
        // 声明式 ECharts：canvas 带 data-xf="echart" + data-xf-config，
        // 由 XFAdmin.scan() 在 DOM 就绪后统一初始化（避免内联脚本因 XFAdmin 未加载而失效）
        $uid = 'wsc_' . $this->uid();
        $cfg = json_encode([
            'options' => [
                'grid' => ['left' => 48, 'right' => 16, 'top' => 40, 'bottom' => 30],
                'tooltip' => ['trigger' => 'axis'],
                'legend' => ['data' => ['收入', '支出']],
                'xAxis' => ['type' => 'category', 'data' => ['1月', '2月', '3月', '4月', '5月', '6月']],
                'yAxis' => ['type' => 'value', 'beginAtZero' => true],
                'series' => [
                    [
                        'name' => '收入', 'type' => 'bar', 'data' => [8500, 9200, 10100, 8800, 11500, 12800],
                        'itemStyle' => ['color' => 'rgba(13,110,253,0.7)', 'borderRadius' => [6, 6, 0, 0]],
                    ],
                    [
                        'name' => '支出', 'type' => 'bar', 'data' => [4200, 4800, 5100, 4500, 5500, 6200],
                        'itemStyle' => ['color' => 'rgba(220,53,69,0.5)', 'borderRadius' => [6, 6, 0, 0]],
                    ],
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP);

        $html = '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">月度趋势</h5></div>'
            . '<div class="card-body"><div style="height:250px">'
            . '<canvas id="' . $uid . '" data-xf="echart" data-xf-config="' . $cfg . '"></canvas>'
            . '</div></div></div>';

        return $html;
    }

    /**
     * render Messages（private实例方法）
     *
     * @return string result
     */
    private function renderMessages(): string
    {
        $msgs = [
            ['name' => '张三', 'avatar' => 'users/user-1.jpg', 'text' => '新订单已提交，请审核', 'time' => '5 分钟前', 'badge' => '新'],
            ['name' => '李四', 'avatar' => 'users/user-2.jpg', 'text' => '项目进度更新完成', 'time' => '1 小时前'],
            ['name' => '王五', 'avatar' => 'users/user-3.jpg', 'text' => '请检查最新的设计稿', 'time' => '3 小时前', 'badge' => '3'],
            ['name' => '赵六', 'avatar' => 'users/user-4.jpg', 'text' => '服务器维护通知', 'time' => '昨天'],
        ];

        $html = '<div class="card mb-3"><div class="card-header d-flex justify-content-between">'
            . '<h5 class="card-title mb-0">消息</h5><span class="badge text-bg-primary rounded-pill">4</span></div>'
            . '<div class="list-group list-group-flush">';

        foreach ($msgs as $m) {
            $avatar = $this->e($m['avatar']) ?: '';
            $name = $this->e($m['name']);
            $text = $this->e($m['text']);
            $time = $this->e($m['time']);
            $badge = $m['badge'] ?? '';

            $html .= '<div class="list-group-item"><div class="d-flex align-items-center">'
                . ($avatar ? '<img src="' . $this->img($avatar) . '" class="rounded-circle me-3" width="36" height="36" alt="">'
                : '<div class="rounded-circle bg-light me-3 d-flex align-items-center justify-content-center" style="width:36px;height:36px"><i class="ti ti-user text-muted"></i></div>')
                . '<div class="flex-grow-1 min-w-0"><h6 class="mb-0 text-truncate">' . $name . '</h6>'
                . '<small class="text-muted text-truncate d-block">' . $text . '</small></div>'
                . ($badge ? '<span class="badge text-bg-danger rounded-pill ms-2">' . $this->e($badge) . '</span>' : '')
                . '<small class="text-muted ms-2">' . $time . '</small></div></div>';
        }
        $html .= '</div><div class="card-footer text-center"><a href="#">查看所有消息</a></div></div>';

        return $html;
    }

    /**
     * render Tasks（private实例方法）
     *
     * @return string result
     */
    private function renderTasks(): string
    {
        $tasks = [
            ['text' => '完成月度报告', 'done' => true, 'priority' => 'high'],
            ['text' => '审核新用户申请', 'done' => false, 'priority' => 'normal'],
            ['text' => '更新系统文档', 'done' => false, 'priority' => 'low'],
            ['text' => '备份数据库', 'done' => true, 'priority' => 'high'],
            ['text' => '参加周会', 'done' => false, 'priority' => 'normal'],
        ];

        $html = '<div class="card mb-3"><div class="card-header d-flex justify-content-between">'
            . '<h5 class="card-title mb-0">待办事项</h5><small class="text-muted">' . count(array_filter($tasks, fn($t) => ! $t['done'])) . ' 项未完成</small></div>'
            . '<div class="list-group list-group-flush">';

        foreach ($tasks as $t) {
            $checked = $t['done'] ? ' checked' : '';
            $textClass = $t['done'] ? ' text-decoration-line-through text-muted' : '';
            $priorityBadge = match ($t['priority']) {
                'high' => '<span class="badge text-bg-danger ms-2">高</span>',
                'low' => '<span class="badge text-bg-secondary ms-2">低</span>',
                default => '',
            };

            $html .= '<div class="list-group-item"><div class="d-flex align-items-center">'
                . '<input class="form-check-input me-3" type="checkbox"' . $checked . '>'
                . '<span class="flex-grow-1' . $textClass . '">' . $this->e($t['text']) . '</span>'
                . $priorityBadge . '</div></div>';
        }
        $html .= '</div><div class="card-footer"><div class="input-group input-group-sm">'
            . '<input type="text" class="form-control" placeholder="添加新任务...">'
            . '<button class="btn btn-primary"><i class="ti ti-plus"></i></button></div></div></div>';

        return $html;
    }

    /**
     * render Activity（private实例方法）
     *
     * @return string result
     */
    private function renderActivity(): string
    {
        $activities = [
            ['text' => '张三 创建了新项目', 'time' => '刚刚', 'icon' => 'ti-folder-plus', 'color' => 'primary'],
            ['text' => '李四 更新了订单 #ORD-8921', 'time' => '10 分钟前', 'icon' => 'ti-edit', 'color' => 'success'],
            ['text' => '系统 完成了自动备份', 'time' => '1 小时前', 'icon' => 'ti-cloud-download', 'color' => 'info'],
            ['text' => '王五 上传了设计稿', 'time' => '2 小时前', 'icon' => 'ti-upload', 'color' => 'warning'],
            ['text' => '赵六 提交了反馈', 'time' => '5 小时前', 'icon' => 'ti-message', 'color' => 'secondary'],
        ];

        $html = '<div class="card"><div class="card-header"><h5 class="card-title mb-0">最近动态</h5></div>'
            . '<div class="list-group list-group-flush">';

        foreach ($activities as $a) {
            $html .= '<div class="list-group-item"><div class="d-flex align-items-center">'
                . '<span class="badge bg-' . $this->e($a['color']) . '-subtle text-' . $this->e($a['color']) . ' rounded-3 p-2 me-3">'
                . '<i class="ti ' . $this->e($a['icon']) . ' fs-16"></i></span>'
                . '<div class="flex-grow-1"><small>' . $this->e($a['text']) . '</small></div>'
                . '<small class="text-muted">' . $this->e($a['time']) . '</small></div></div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 账号设置页面（pages-account-settings.html）
 *
 * XfAdmin::accountSettings([
 *     'tabs' => [
 *         ['title' => 'General', 'icon' => 'ti-settings', 'content' => '', 'active' => true],
 *         ['title' => 'Change Password', 'icon' => 'ti-lock', 'content' => ''],
 *         ['title' => 'Email Notifications', 'icon' => 'ti-bell', 'content' => ''],
 *         ['title' => 'Export Data', 'icon' => 'ti-download', 'content' => ''],
 *     ],
 *     'user' => [
 *         'avatar' => 'avatars/1.png',
 *         'name' => 'John Doe',
 *         'email' => 'john@example.com',
 *         'role' => 'Administrator',
 *         'bio' => '...',
 *         'timezone' => 'Asia/Shanghai',
 *     ],
 * ])
 */
class AccountSettings extends Component
{
    protected function defaults(): array
    {
        return [
            'tabs' => [],
            'user' => [],
            'activeTab' => 0,
        ];
    }

    protected function html(): string
    {
        $tabs = (array) $this->get('tabs', []);
        $user = (array) $this->get('user', []);
        $activeTab = (int) $this->get('activeTab', 0);

        if (empty($tabs)) {
            return '';
        }

        $prefix = $this->uid('xf-acc');

        $html = '<div class="row g-4"><div class="col-lg-3">';

        // 左侧头像&信息
        $html .= '<div class="card"><div class="card-body text-center">';
        $avatar = !empty($user['avatar']) ? XfAdmin::img((string) $user['avatar']) : '';
        if ($avatar) {
            $html .= '<img src="' . $this->e($avatar) . '" class="rounded-circle mb-3" width="80" height="80" alt="" style="width:80px;height:80px;object-fit:cover">';
        } else {
            $html .= '<div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">'
                . '<i class="ti ti-user fs-24 text-primary"></i></div>';
        }
        $html .= '<h5 class="mb-1">' . $this->e($user['name'] ?? '') . '</h5>';
        $html .= '<small class="text-muted">' . $this->e($user['role'] ?? '') . '</small>';
        $html .= '</div></div>';

        // 选项卡导航
        $html .= '<div class="card mt-3"><div class="list-group list-group-flush">';
        foreach ($tabs as $i => $tab) {
            $tab = (array) $tab;
            $active = $i === $activeTab ? ' active' : '';
            $icon = (string) ($tab['icon'] ?? 'ti-settings');
            $title = (string) ($tab['title'] ?? '');
            $tabId = $prefix . '-tab-' . $i;

            $html .= '<a href="javascript:void(0)" class="list-group-item list-group-item-action' . $active . '"'
                . ' data-bs-toggle="tab" data-bs-target="#' . $this->e($tabId) . '" role="tab">'
                . '<i class="ti ' . $this->e($icon) . ' me-2"></i>' . $this->e($title) . '</a>';
        }
        $html .= '</div></div>';

        $html .= '</div>';

        // 右侧内容
        $html .= '<div class="col-lg-9"><div class="card"><div class="card-body"><div class="tab-content">';
        foreach ($tabs as $i => $tab) {
            $tab = (array) $tab;
            $show = $i === $activeTab ? ' show active' : '';
            $tabId = $prefix . '-tab-' . $i;
            $content = (string) ($tab['content'] ?? '');

            $html .= '<div class="tab-pane fade' . $show . '" id="' . $this->e($tabId) . '" role="tabpanel">';
            if ($content) {
                $html .= $this->raw($content);
            } else {
                $html .= $this->renderDefaultTab($i, $tab, $user);
            }
            $html .= '</div>';
        }
        $html .= '</div></div></div></div>';

        $html .= '</div>';

        return $html;
    }

    private function renderDefaultTab(int $idx, array $tab, array $user): string
    {
        $title = (string) ($tab['title'] ?? '');

        switch ($title) {
            case 'Change Password':
            case '修改密码':
                return $this->renderPasswordTab($user);

            case 'Email Notifications':
            case '通知设置':
                return $this->renderNotificationsTab($user);

            case 'Export Data':
            case '导出数据':
                return $this->renderExportTab($user);

            default:
                return $this->renderGeneralTab($user);
        }
    }

    private function renderGeneralTab(array $user): string
    {
        $name = (string) ($user['name'] ?? '');
        $email = (string) ($user['email'] ?? '');
        $bio = (string) ($user['bio'] ?? '');
        $timezone = (string) ($user['timezone'] ?? '');
        $phone = (string) ($user['phone'] ?? '');
        $company = (string) ($user['company'] ?? '');

        $html = '<h5 class="mb-3">基本信息</h5>';
        $html .= '<div class="row g-3">';
        $html .= '<div class="col-md-6"><label class="form-label">姓名</label><input type="text" class="form-control" value="' . $this->e($name) . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">邮箱</label><input type="email" class="form-control" value="' . $this->e($email) . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">手机号</label><input type="text" class="form-control" value="' . $this->e($phone) . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">公司</label><input type="text" class="form-control" value="' . $this->e($company) . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">时区</label><select class="form-select"><option>' . ($timezone ? $this->e($timezone) : 'Asia/Shanghai') . '</option></select></div>';
        $html .= '<div class="col-12"><label class="form-label">个人简介</label><textarea class="form-control" rows="3">' . $this->e($bio) . '</textarea></div>';
        $html .= '<div class="col-12"><button class="btn btn-primary">保存修改</button></div>';
        $html .= '</div>';

        return $html;
    }

    private function renderPasswordTab(array $user): string
    {
        $html = '<h5 class="mb-3">修改密码</h5>';
        $html .= '<div class="row g-3">';
        $html .= '<div class="col-12"><label class="form-label">当前密码</label><input type="password" class="form-control"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">新密码</label><input type="password" class="form-control"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">确认新密码</label><input type="password" class="form-control"></div>';
        $html .= '<div class="col-12"><button class="btn btn-primary">更新密码</button></div>';
        $html .= '</div>';

        return $html;
    }

    private function renderNotificationsTab(array $user): string
    {
        $items = [
            ['label' => '邮件通知', 'desc' => '接收重要更新和提醒'],
            ['label' => '推送通知', 'desc' => '在浏览器中接收推送消息'],
            ['label' => '短信通知', 'desc' => '接收紧急通知短信'],
            ['label' => '营销邮件', 'desc' => '接收产品更新和优惠信息'],
        ];

        $html = '<h5 class="mb-3">通知偏好</h5>';
        foreach ($items as $item) {
            $html .= '<div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">';
            $html .= '<div><div class="fw-semibold">' . $this->e($item['label']) . '</div>';
            $html .= '<small class="text-muted">' . $this->e($item['desc']) . '</small></div>';
            $html .= '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked></div>';
            $html .= '</div>';
        }
        $html .= '<button class="btn btn-primary mt-2">保存偏好</button>';

        return $html;
    }

    private function renderExportTab(array $user): string
    {
        $html = '<h5 class="mb-3">导出数据</h5>';
        $html .= '<p class="text-muted">您可以导出您的账户数据，包括个人信息、活动记录和设置。导出可能需要几分钟时间。</p>';

        $items = [
            ['label' => '个人资料', 'icon' => 'ti-user', 'desc' => '姓名、邮箱、头像等'],
            ['label' => '活动记录', 'icon' => 'ti-activity', 'desc' => '登录记录、操作日志等'],
            ['label' => '设置数据', 'icon' => 'ti-settings', 'desc' => '偏好设置和配置'],
            ['label' => '完整数据包', 'icon' => 'ti-archive', 'desc' => '包含以上所有数据的完整导出'],
        ];

        $html .= '<div class="list-group mb-3">';
        foreach ($items as $item) {
            $html .= '<div class="list-group-item d-flex justify-content-between align-items-center">';
            $html .= '<div><i class="ti ' . $this->e($item['icon']) . ' me-2 text-muted"></i>';
            $html .= '<span class="fw-semibold">' . $this->e($item['label']) . '</span>';
            $html .= '<small class="text-muted d-block ms-4">' . $this->e($item['desc']) . '</small></div>';
            $html .= '<button class="btn btn-sm btn-outline-primary">导出</button>';
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 用户资料页（pages-profile.html）
 *
 * 封面 + 头像 + 基本信息 + 统计 + 操作按钮。
 *
 * XfAdmin::userProfile([
 *     'avatar'   => 'users/user-1.jpg',
 *     'name'     => '陈一鸣',
 *     'title'    => '高级前端工程师',
 *     'bio'      => '热爱开源，专注中后台体验优化。',
 *     'stats'    => [['label' => '项目', 'value' => 24], ['label' => '关注', 'value' => 1.2 . 'k'], ['label' => '粉丝', 'value' => 3.4 . 'k']],
 *     'actions'  => ['message' => true, 'follow' => true],
 * ])
 */
class UserProfile extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'avatar'  => '',
            'cover'   => '',
            'name'    => '匿名用户',
            'title'   => '',
            'bio'     => '',
            'stats'   => [],
            'actions' => ['message' => true, 'follow' => true],
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $avatar  = $this->img((string) $this->get('avatar', ''));
        $cover   = $this->img((string) $this->get('cover', ''));
        $name    = (string) $this->get('name', '匿名用户');
        $title   = (string) $this->get('title', '');
        $bio     = (string) $this->get('bio', '');
        $text    = (string) $this->get('text', '');
        $stats   = (array) $this->get('stats', []);
        $actions = (array) $this->get('actions', ['message' => true, 'follow' => true]);

        $html = '<div class="card overflow-hidden">';

        // 封面
        $html .= '<div class="profile-cover" style="height:140px;background:' . ($cover !== '' ? 'url(\'' . $this->e($cover) . '\') center/cover' : '#eef2f7') . '"></div>';

        // 头像 + 操作
        $html .= '<div class="card-body text-center">';
        // avatar 为空时用 text 首字母渲染文字头像（避免 src="" 破图）
        if ($avatar !== '') {
            $html .= '<img src="' . $this->e($avatar) . '" class="rounded-circle avatar-lg shadow mt-n5 border border-3 border-white" alt="' . $this->e($name) . '">';
        } elseif ($text !== '') {
            $html .= '<div class="rounded-circle avatar-lg shadow mt-n5 border border-3 border-white bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width:72px;height:72px;font-size:28px;">' . $this->e($text) . '</div>';
        } else {
            $html .= '<img src="" class="rounded-circle avatar-lg shadow mt-n5 border border-3 border-white" alt="' . $this->e($name) . '">';
        }
        $html .= '<h5 class="mt-2 mb-0">' . $this->e($name) . '</h5>';
        if ($title !== '') {
            $html .= '<small class="text-muted">' . $this->e($title) . '</small>';
        }
        if ($bio !== '') {
            $html .= '<p class="mt-2 text-muted">' . $this->e($bio) . '</p>';
        }
        // 统计
        if (! empty($stats)) {
            $html .= '<div class="d-flex justify-content-center gap-4 my-3">';
            foreach ($stats as $s) {
                $s = (array) $s;
                $html .= '<div><div class="h4 mb-0">' . $this->e((string) ($s['value'] ?? 0)) . '</div><small class="text-muted">' . $this->e((string) ($s['label'] ?? '')) . '</small></div>';
            }
            $html .= '</div>';
        }
        // 操作
        $html .= '<div class="d-flex justify-content-center gap-2">';
        if (! empty($actions['message'])) {
            $html .= '<button class="btn btn-outline-primary" type="button"><i class="ti ti-message-circle me-1"></i>私信</button>';
        }
        if (! empty($actions['follow'])) {
            $html .= '<button class="btn btn-primary" type="button"><i class="ti ti-user-plus me-1"></i>关注</button>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

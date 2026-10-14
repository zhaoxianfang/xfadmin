<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 头像组（重叠堆叠），用于展示多人
 *
 * XfAdmin::avatarGroup([
 *     'users' => [
 *         ['src' => 'users/user-1.jpg', 'name' => '张三', 'status' => 'online'],
 *         ['name' => '李四', 'status' => 'busy'],     // 无 src 显示首字母
 *     ],
 *     'max'   => 5,          // 最多显示数量，超出显示 +N
 *     'size'  => 'sm',       // sm | '' | lg
 * ])
 */
class AvatarGroup extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'users' => [],
            'max' => 5,
            'size' => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $users = (array) $this->get('users', []);
        $max = max(1, (int) $this->get('max'));
        $size = (string) $this->get('size');
        $shown = array_slice($users, 0, $max);
        $extra = count($users) - count($shown);

        $html = '<div'.$this->attrs(['class' => 'xf-avatar-group d-inline-flex align-items-center']).'>';
        foreach ($shown as $u) {
            $u = (array) $u;
            // 依赖 ext-mbstring（composer.json 已声明）：按字符而非字节截取，
            // 避免中文名首字被 UTF-8 多字节截断出现乱码
            $html .= (string) XfAdmin::component('avatar', [
                'src' => $u['src'] ?? '',
                'text' => isset($u['name']) ? mb_substr((string) $u['name'], 0, 1) : '',
                'size' => $size,
            ]);
        }
        if ($extra > 0) {
            // +N 徽标的 size 走白名单（sm|lg），非法值回退 sm，防止任意 class 注入
            $moreSize = $this->enum($size ?: 'sm', self::ENUM_SIZE, 'sm');
            $html .= '<span class="avatar avatar-'.$moreSize
                .' xf-avatar-more bg-light text-muted border border-2 border-white">+'.$extra.'</span>';
        }
        $html .= '</div>';

        return $html;
    }
}

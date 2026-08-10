<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support;

/**
 * 演示菜单数据
 *
 * 供扩展包 demo 与宿主项目（如 wsf Admin 演示模块）共用，避免演示数据两处维护。
 * 包含深级子菜单（deepMenu）与顶栏工具区（topbarTools，对应 Topbar 组件）。
 */
final class DemoMenu
{
    /**
     * 顶栏右侧工具区演示数据（语言 / 邮件 / 通知 / 用户卡片）
     *
     * 与 Topbar 组件的 languages / messages / notifications / user 选项对应，
     * 供 demo 与宿主项目共用，保证「一处维护、两处一致」。
     *
     * @return array<string, mixed>
     */
    public static function topbarTools(string $url = '#!'): array
    {
        $img = static fn (string $p): string => \zxf\XfAdmin\XfAdmin::img($p);

        return [
            'languages' => [
                ['name' => '简体中文', 'code' => 'zh-CN', 'url' => $url, 'active' => true],
                ['name' => 'English', 'code' => 'en', 'url' => $url],
                ['name' => '日本語', 'code' => 'ja', 'url' => $url],
                ['name' => 'Deutsch', 'code' => 'de', 'url' => $url],
            ],
            'messages' => [
                'title'   => '邮件通知',
                'count'   => 4,
                'all_url' => $url,
                'items'   => [
                    ['from' => '李文静', 'text' => '关于下周产品评审会的议程安排…', 'time' => '5 分钟前',
                     'avatar' => $img('users/user-1.jpg'), 'url' => $url],
                    ['from' => '王建国', 'text' => '合同附件已上传，请查收并确认。', 'time' => '1 小时前',
                     'avatar' => $img('users/user-3.jpg'), 'url' => $url],
                    ['from' => '系统邮件', 'text' => '您的月度账单已生成。', 'time' => '昨天',
                     'icon' => 'ti ti-mail-opened', 'variant' => 'info', 'url' => $url],
                    ['from' => '张晓芳', 'text' => '设计稿第二版已同步至云文档。', 'time' => '2 天前',
                     'avatar' => $img('users/user-5.jpg'), 'url' => $url],
                ],
            ],
            'notifications' => [
                'title'   => '消息通知',
                'count'   => 3,
                'all_url' => $url,
                'items'   => [
                    ['title' => '订单支付成功', 'text' => '订单 #20260808 已完成支付。', 'time' => '刚刚',
                     'icon' => 'ti ti-shopping-cart', 'variant' => 'success', 'url' => $url],
                    ['title' => '服务器告警', 'text' => 'node-02 CPU 使用率超过 90%。', 'time' => '20 分钟前',
                     'icon' => 'ti ti-alert-triangle', 'variant' => 'danger', 'url' => $url],
                    ['title' => '新用户注册', 'text' => '今日新增注册用户 128 人。', 'time' => '3 小时前',
                     'icon' => 'ti ti-user-plus', 'variant' => 'primary', 'url' => $url],
                ],
            ],
            'user' => [
                'name'   => '陈管理员',
                'role'   => '超级管理员',
                'email'  => 'admin@example.com',
                'avatar' => $img('users/user-2.jpg'),
                'items'  => [
                    ['text' => '个人资料', 'icon' => 'ti ti-user-circle', 'url' => $url],
                    ['text' => '账号设置', 'icon' => 'ti ti-settings', 'url' => $url],
                    ['text' => '消息中心', 'icon' => 'ti ti-bell', 'url' => $url],
                    ['divider' => true],
                    ['text' => '退出登录', 'icon' => 'ti ti-logout', 'url' => $url, 'class' => 'text-danger'],
                ],
            ],
        ];
    }

    /**
     * 顶部水平导航（TopNav）演示菜单
     *
     * 覆盖：普通项、带徽标项、多级下拉（含 5 级深度）、Mega 大面板、
     * 以及靠右的深层菜单（用于验证子面板向左翻转的边界避让）。
     *
     * @return array<int, array<string, mixed>>
     */
    public static function topNavMenu(string $url = '#!'): array
    {
        return [
            ['text' => '仪表盘', 'icon' => 'ti ti-dashboard', 'url' => $url, 'active' => true],
            ['text' => '应用', 'icon' => 'ti ti-apps', 'children' => [
                ['text' => '日历', 'icon' => 'ti ti-calendar', 'url' => $url],
                ['text' => '聊天', 'icon' => 'ti ti-message', 'url' => $url,
                 'badge' => ['text' => '5', 'class' => 'text-bg-danger']],
                ['text' => '邮箱', 'icon' => 'ti ti-mail', 'children' => [
                    ['text' => '收件箱', 'url' => $url],
                    ['text' => '已发送', 'url' => $url],
                    ['text' => '草稿箱', 'url' => $url],
                ]],
                ['divider' => true],
                ['text' => '文件管理', 'icon' => 'ti ti-folder', 'url' => $url],
            ]],
            ['text' => '电商', 'icon' => 'ti ti-shopping-cart', 'mega' => [
                'cols'    => 3,
                'title'   => '电商中心',
                'columns' => [
                    ['title' => '商品', 'items' => [
                        ['text' => '商品列表', 'url' => $url],
                        ['text' => '新增商品', 'url' => $url],
                        ['text' => '商品分类', 'url' => $url],
                        ['text' => '库存管理', 'url' => $url],
                    ]],
                    ['title' => '订单', 'items' => [
                        ['text' => '订单列表', 'url' => $url],
                        ['text' => '退款单', 'url' => $url],
                        ['text' => '发货管理', 'url' => $url],
                        ['text' => '物流跟踪', 'url' => $url],
                    ]],
                    ['title' => '客户', 'items' => [
                        ['text' => '客户列表', 'url' => $url],
                        ['text' => '会员等级', 'url' => $url],
                        ['text' => '优惠券', 'url' => $url,
                         'badge' => ['text' => 'NEW', 'class' => 'text-bg-success']],
                        ['text' => '评价管理', 'url' => $url],
                    ]],
                ],
            ]],
            self::deepMenu($url),
            ['text' => '报表', 'icon' => 'ti ti-chart-bar', 'children' => [
                ['title' => '数据分析'],
                ['text' => '销售报表', 'icon' => 'ti ti-report-money', 'url' => $url],
                ['text' => '流量分析', 'icon' => 'ti ti-chart-line', 'url' => $url],
                ['text' => '用户画像', 'icon' => 'ti ti-users', 'url' => $url],
            ]],
            ['text' => '设置', 'icon' => 'ti ti-settings', 'children' => [
                ['text' => '基础设置', 'url' => $url],
                ['text' => '权限管理', 'children' => [
                    ['text' => '角色列表', 'url' => $url],
                    ['text' => '权限节点', 'url' => $url],
                ]],
                ['text' => '操作日志', 'url' => $url],
            ]],
        ];
    }

    /**
     * 5 级子菜单演示项
     *
     * 层级：一级「多级菜单」→ 二级 → 三级 → 四级 → 五级
     * 桌面端逐级向右浮出，右侧空间不足时自动翻转到左侧；
     * 小屏下整体退化为垂直手风琴，点击展开、再次点击收起。
     *
     * @return array<string, mixed>
     */
    public static function deepMenu(string $url = '#!'): array
    {
        return [
            'text'     => '多级菜单',
            'icon'     => 'ti ti-stack-2',
            'children' => [
                ['title' => '五级深度演示'],
                ['text' => '第一级 · 直达页面', 'icon' => 'ti ti-file', 'url' => $url],
                ['text' => '第二级 · 展开', 'icon' => 'ti ti-folder', 'children' => [
                    ['text' => '二级条目 A', 'url' => $url],
                    ['text' => '二级条目 B', 'url' => $url],
                    ['text' => '第三级 · 展开', 'icon' => 'ti ti-folder', 'children' => [
                        ['text' => '三级条目 A', 'url' => $url],
                        ['text' => '三级条目 B', 'url' => $url],
                        ['text' => '第四级 · 展开', 'icon' => 'ti ti-folder', 'children' => [
                            ['text' => '四级条目 A', 'url' => $url],
                            ['text' => '四级条目 B', 'url' => $url],
                            ['text' => '第五级 · 展开', 'icon' => 'ti ti-folder', 'children' => [
                                ['text' => '五级条目 A', 'url' => $url, 'icon' => 'ti ti-point'],
                                ['text' => '五级条目 B', 'url' => $url, 'icon' => 'ti ti-point'],
                                ['text' => '五级条目 C', 'url' => $url, 'icon' => 'ti ti-point',
                                 'badge' => ['text' => 'NEW', 'class' => 'bg-success']],
                            ]],
                        ]],
                    ]],
                ]],
                ['divider' => true],
                ['text' => '另一分支（同级互斥）', 'icon' => 'ti ti-git-branch', 'children' => [
                    ['text' => '分支条目 1', 'url' => $url],
                    ['text' => '分支条目 2', 'url' => $url],
                    ['text' => '再下一级', 'children' => [
                        ['text' => '深层条目 X', 'url' => $url],
                        ['text' => '深层条目 Y', 'url' => $url],
                    ]],
                ]],
            ],
        ];
    }
}

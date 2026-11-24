<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 后台通用行为声明组件
 *
 * 把后台页面常见的四类交互——退出登录（POST+CSRF）、刷新系统缓存（AJAX）、
 * 可视化图标选择器、主题与语言持久化——收敛为一个「声明式配置节点」。
 * 本组件自身不输出任何 <script> 逻辑代码，只输出一个带 data-xf-config 的容器；
 * 真正的交互实现位于包内 xfadmin.js 的 adminBehaviors widget（XFAdmin.register）。
 *
 * 这样业务控制器 / 布局服务只需传一组 URL 与 token 即可获得完整交互，
 * 彻底消除「控制器 / 服务层内联大段 JS」的违规写法。
 *
 * 用法：
 *   XfAdmin::adminBehaviors([
 *       'logout' => ['url' => route('logout'), 'token' => csrf_token()],
 *       'refresh' => ['url' => route('cache.clear'), 'token' => csrf_token()],
 *       'iconPicker' => ['url' => route('icons'), 'inputTarget' => '#icon', 'previewSelector' => '#preview'],
 *   ]);
 *
 * 主题提前应用脚本（防首屏闪烁）请用 {@see AdminThemeHead}，置于 <head>。
 */
class AdminBehaviors extends Component
{
    /**
     * 默认配置
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            // 退出登录：selector 触发元素选择器；url 提交地址；token CSRF；tokenName 字段名；method
            'logout' => null,
            // 刷新缓存：selector；url；token；tokenName
            'refresh' => null,
            // 图标选择器：trigger 触发按钮；url 图标数据源；inputTarget 回填输入框；previewSelector 预览元素；batch 分批数
            'iconPicker' => null,
            // 主题/语言持久化：false 关闭；或 ['storageKey'=>..,'langKey'=>..]
            'theme' => true,
        ];
    }

    /**
     * 组件依赖：jquery（图标选择器历史兼容 jQuery 选择器，存在时使用）
     *
     * @return array<int, string>
     */
    protected function assets(): array
    {
        return ['jquery'];
    }

    /**
     * 渲染声明式配置节点
     *
     * data-xf-config 使用Html::scriptJson 的同款 HEX 转义（JSON_HEX_*），
     * 保证内联 JSON 中的 </script>、引号等不会破坏属性或造成 XSS。
     */
    protected function html(): string
    {
        $cfg = [];
        foreach (['logout', 'refresh', 'iconPicker', 'theme'] as $key) {
            $value = $this->get($key);
            if ($value !== null) {
                $cfg[$key] = $value;
            }
        }

        // 属性上下文 JSON：HEX 转义标签/引号/尖括号，适配 data-xf-config='...'（单引号包裹）
        $json = json_encode(
            $cfg,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
        );

        return '<div data-xf="adminBehaviors" data-xf-config=\''.$json.'\' '
            .'style="display:none" aria-hidden="true"></div>';
    }
}

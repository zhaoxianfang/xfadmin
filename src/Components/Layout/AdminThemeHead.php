<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 后台主题提前应用组件（置于 <head>）
 *
 * 在 app.js 的 LayoutCustomizer 读取 window.config 之前，先用 localStorage 中保存的偏好
 * 覆盖 window.config.theme / window.defaultConfig.theme，并直接设置 <html data-bs-theme>，
 * 既消除首屏闪烁，也保证明暗切换按钮的基准状态与实际一致。
 *
 * 该脚本必须同步执行且置于 <head>，故由 page() 的 head 插槽渲染；
 * 这是「组件输出自身所需的引导脚本」，不违反「控制器 / 服务层禁止内联 JS」的约束。
 *
 * 用法：
 *   XfAdmin::page(['head' => (string) XfAdmin::adminThemeHead()]);
 */
class AdminThemeHead extends Component
{
    /**
     * 默认配置
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'storageKey' => 'xf_admin_theme', // 主题偏好 localStorage 键
            'langKey' => 'xf_admin_lang',     // 语言偏好 localStorage 键
        ];
    }

    /**
     * 渲染 <head> 引导脚本（nowdoc：内部无 PHP 变量插值，JS 变量在运行时读 localStorage）
     *
     * @return string
     */
    protected function html(): string
    {
        $storageKey = (string) $this->get('storageKey');
        $langKey = (string) $this->get('langKey');

        // 键名仅允许字母数字下划线，杜绝注入（虽然写入的是 JS 字符串字面量）
        $storageKey = preg_replace('/[^A-Za-z0-9_]/', '', $storageKey) ?: 'xf_admin_theme';
        $langKey = preg_replace('/[^A-Za-z0-9_]/', '', $langKey) ?: 'xf_admin_lang';

        return <<<HTML
<script>
(function () {
    try {
        var t = localStorage.getItem('{$storageKey}');
        if (t === 'dark' || t === 'light') {
            document.documentElement.setAttribute('data-bs-theme', t);
            if (window.config && typeof window.config === 'object') { window.config.theme = t; }
            if (window.defaultConfig && typeof window.defaultConfig === 'object') { window.defaultConfig.theme = t; }
            try {
                var sc = JSON.parse(sessionStorage.getItem('__INSPINIA_CONFIG__') || '{}');
                sc.theme = t;
                sessionStorage.setItem('__INSPINIA_CONFIG__', JSON.stringify(sc));
            } catch (e) {}
        }
        var l = localStorage.getItem('{$langKey}');
        if (l) { document.documentElement.setAttribute('lang', l); }
    } catch (e) {}
})();
</script>
HTML;
    }
}

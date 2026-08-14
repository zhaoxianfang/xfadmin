<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 国际化 i18n 展示（plugins-i18.html）
 *
 * XfAdmin::i18n([
 *     'currentLocale' => 'zh-CN',
 *     'locales' => [
 *         'en' => ['name' => 'English', 'flag' => 'flags/us.svg'],
 *         'zh-CN' => ['name' => '简体中文', 'flag' => 'flags/cn.svg'],
 *         'ja' => ['name' => '日本語', 'flag' => 'flags/jp.svg'],
 *     ],
 *     'demoKeys' => [
 *         'greeting' => '你好，世界！',
 *         'welcome' => '欢迎来到管理后台',
 *         'save' => '保存',
 *         'cancel' => '取消',
 *     ],
 * ])
 */
class I18n extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'currentLocale' => 'zh-CN',
            'locales' => [],
            'demoKeys' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $currentLocale = (string) $this->get('currentLocale', 'zh-CN');
        $locales = (array) $this->get('locales', []);
        $demoKeys = $this->get('demoKeys');

        return $demoKeys !== null
            ? $this->renderCustom($currentLocale, $locales, (array) $demoKeys)
            : $this->renderBuiltin($currentLocale, $locales);
    }

    /**
     * render Builtin（private实例方法）
     *
     * @param string $currentLocale current Locale
     * @param array $locales locales
     *
     * @return string result
     */
    private function renderBuiltin(string $currentLocale, array $locales): string
    {
        $defaultLocales = $locales ?: [
            'en' => ['name' => 'English', 'flag' => 'flags/us.svg'],
            'zh-CN' => ['name' => '简体中文', 'flag' => 'flags/cn.svg'],
            'zh-TW' => ['name' => '繁體中文', 'flag' => 'flags/tw.svg'],
            'ja' => ['name' => '日本語', 'flag' => 'flags/jp.svg'],
            'ko' => ['name' => '한국어', 'flag' => 'flags/kr.svg'],
            'fr' => ['name' => 'Français', 'flag' => 'flags/fr.svg'],
            'de' => ['name' => 'Deutsch', 'flag' => 'flags/de.svg'],
            'es' => ['name' => 'Español', 'flag' => 'flags/es.svg'],
        ];

        $defaultKeys = [
            'greeting' => '你好，世界！',
            'welcome' => '欢迎来到管理后台',
            'dashboard' => '仪表盘',
            'users' => '用户管理',
            'settings' => '系统设置',
            'save' => '保存',
            'cancel' => '取消',
            'delete' => '删除',
            'confirm' => '确认',
            'search' => '搜索',
            'export' => '导出',
            'profile' => '个人资料',
            'logout' => '退出登录',
            'notifications' => '通知',
            'messages' => '消息',
        ];

        $html = '<div class="row g-3">';

        // 左侧：语言列表
        $html .= '<div class="col-lg-4"><div class="card"><div class="card-header"><h5 class="card-title mb-0">支持语言</h5></div>'
            . '<div class="list-group list-group-flush">';

        foreach ($defaultLocales as $code => $info) {
            $info = (array) $info;
            $name = $this->e($info['name'] ?? $code);
            $flag = $this->e($info['flag'] ?? '');
            $active = $code === $currentLocale ? ' active' : '';
            $check = $code === $currentLocale ? ' <i class="ti ti-check text-success ms-auto"></i>' : '';

            $html .= '<div class="list-group-item list-group-item-action' . $active . ' d-flex align-items-center">';
            if ($flag) {
                $html .= '<img src="' . $this->img($flag) . '" class="me-2" width="20" height="14" alt="">';
            }
            $html .= $name . $check . '</div>';
        }
        $html .= '</div></div></div>';

        // 右侧：翻译键值对
        $html .= '<div class="col-lg-8"><div class="card"><div class="card-header d-flex justify-content-between">'
            . '<h5 class="card-title mb-0">翻译键值 · ' . $this->e($currentLocale) . '</h5>'
            . '<button class="btn btn-sm btn-outline-primary"><i class="ti ti-plus me-1"></i>添加</button></div>'
            . '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr>'
            . '<th style="width:35%">键 (Key)</th><th>翻译值 (Value)</th><th style="width:80px"></th>'
            . '</tr></thead><tbody>';

        foreach ($defaultKeys as $key => $value) {
            $html .= '<tr><td><code>' . $this->e($key) . '</code></td>'
                . '<td><input type="text" class="form-control form-control-sm" value="' . $this->e($value) . '"></td>'
                . '<td><button class="btn btn-sm btn-ghost-danger"><i class="ti ti-trash"></i></button></td></tr>';
        }
        $html .= '</tbody></table></div><div class="card-footer text-end">'
            . '<button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>保存翻译</button></div></div></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * render Custom（private实例方法）
     *
     * @param string $currentLocale current Locale
     * @param array $locales locales
     * @param array $demoKeys demo Keys
     *
     * @return string result
     */
    private function renderCustom(string $currentLocale, array $locales, array $demoKeys): string
    {
        $html = '<div class="card"><div class="card-header d-flex justify-content-between align-items-center">'
            . '<h5 class="card-title mb-0">多语言管理</h5><div class="btn-group btn-group-sm">';

        foreach ($locales as $code => $info) {
            $info = (array) $info;
            $name = $this->e($info['name'] ?? $code);
            $active = $code === $currentLocale ? ' btn-primary' : ' btn-outline-secondary';
            $html .= '<button class="btn' . $active . '">' . $name . '</button>';
        }
        $html .= '</div></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr>'
            . '<th>键</th><th>翻译值</th><th>状态</th></tr></thead><tbody>';

        foreach ($demoKeys as $key => $value) {
            $html .= '<tr><td><code>' . $this->e($key) . '</code></td>'
                . '<td><input type="text" class="form-control form-control-sm" value="' . $this->e((string) $value) . '"></td>'
                . '<td><span class="badge text-bg-success">已翻译</span></td></tr>';
        }
        $html .= '</tbody></table></div><div class="card-footer text-end">'
            . '<button class="btn btn-primary">保存</button></div></div>';

        return $html;
    }
}

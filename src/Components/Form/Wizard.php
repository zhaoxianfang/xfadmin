<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 分步向导（form-wizard.html）—— 纯原生 JS 驱动
 *
 * XfAdmin::wizard([
 *     'steps' => [
 *         ['title' => '账户', 'icon' => 'ti ti-user', 'content' => '第一步内容 HTML'],
 *         ['title' => '资料', 'icon' => 'ti ti-file', 'content' => '第二步内容'],
 *         ['title' => '完成', 'icon' => 'ti ti-check', 'content' => '完成'],
 *     ],
 *     'variant'  => 'primary',
 *     'vertical' => false,
 *     'progress' => true,          // 顶部进度条
 * ])
 */
class Wizard extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'steps'    => [],
            'variant'  => 'primary',
            'vertical' => false,
            'progress' => true,
            'labels'   => ['prev' => '上一步', 'next' => '下一步', 'finish' => '提交'],
            'action'   => '',          // 提交地址（配置后整体以 <form> 包裹，由 JS 在最后一步 requestSubmit）
            'method'   => 'post',      // 提交方法
            'remote'   => false,       // 是否走 AJAX 托管（data-xf-remote，由全局 bindRemoteForms 接管）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id     = $this->resolveId('wizard');
        $steps  = array_values((array) $this->get('steps', []));
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'primary');
        $labels = (array) $this->get('labels');
        $vertical = (bool) $this->get('vertical');
        $action = $this->get('action');
        $remote = $this->get('remote');

        $wrapCls = Html::cls('xf-wizard', ['xf-wizard-vertical row' => $vertical]);
        $open = '';
        // 配置了 action 时整体以 <form> 包裹，使最后一步「提交」能真正提交并被后端接收处理。
        if ($action) {
            $attrs = [
                'action'  => $action,
                'method'  => $this->e($this->get('method')),
                'class'   => 'xf-wizard-form',
            ];
            if ($remote) {
                $attrs['data-xf-remote'] = '';
            }
            $open = '<form' . $this->attrs($attrs) . '>';
        }
        $html = $open . '<div' . $this->attrs(['class' => $wrapCls, 'id' => $id]) . ' data-xf="wizard">';

        // 步骤导航
        $navCls = $vertical ? 'nav flex-column nav-pills col-md-3' : 'nav nav-pills nav-justified mb-3';
        $html .= '<ul class="' . $navCls . ' xf-wizard-nav">';
        foreach ($steps as $i => $s) {
            // 标量（字符串）容错：非数组项按 title 处理，避免 PHP 8 下标访问致命错误
            if (! is_array($s)) {
                $s = ['title' => (string) $s];
            }
            $active = $i === 0 ? ' active' : '';
            $html .= '<li class="nav-item"><span class="nav-link' . $active . '" data-step="' . $i . '">';
            if (! empty($s['icon'])) {
                $html .= '<i class="' . $this->e($s['icon']) . ' me-1"></i>';
            }
            $html .= '<span class="badge rounded-pill bg-' . $variant . '-subtle text-' . $variant . ' me-1">' . ($i + 1) . '</span>';
            $html .= $this->e($s['title'] ?? '') . '</span></li>';
        }
        $html .= '</ul>';

        // 进度条
        if ($this->get('progress') && ! $vertical) {
            $html .= '<div class="progress mb-3" style="height:4px;"><div class="progress-bar bg-' . $variant . ' xf-wizard-progress" style="width:' . (count($steps) ? round(100 / count($steps)) : 0) . '%"></div></div>';
        }
        // 内容面板
        $paneWrap = $vertical ? '<div class="col-md-9">' : '<div>';
        $html .= $paneWrap;
        foreach ($steps as $i => $s) {
            if (! is_array($s)) {
                $s = ['content' => (string) $s];
            }
            $show = $i === 0 ? '' : ' d-none';
            $html .= '<div class="xf-wizard-pane' . $show . '" data-pane="' . $i . '">' . $this->raw($s['content'] ?? '') . '</div>';
        }
        // 按钮
        $html .= '<div class="d-flex justify-content-between mt-3">';
        $html .= '<button type="button" class="btn btn-light xf-wizard-prev" disabled>' . $this->e($labels['prev'] ?? '上一步') . '</button>';
        $html .= '<button type="button" class="btn btn-' . $variant . ' xf-wizard-next" data-finish-label="' . $this->e($labels['finish'] ?? '提交') . '">' . $this->e($labels['next'] ?? '下一步') . '</button>';
        $html .= '</div>';
        $html .= '</div>';

        return $open ? $html . '</div></form>' : $html . '</div>';
    }
}

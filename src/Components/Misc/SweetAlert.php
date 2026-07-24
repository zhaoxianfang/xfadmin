<?php

declare(strict_types=1);

namespace XfAdmin\Components\Misc;

use XfAdmin\Components\Component;

/**
 * SweetAlert2 弹窗（确认框 / 成功提示等）
 *
 * // 按钮触发确认框（confirm_url 确认后跳转 / confirm_js 执行自定义 JS）
 * XfAdmin::sweetAlert([
 *     'trigger' => '删除',
 *     'trigger_variant' => 'danger',
 *     'title'   => '确定删除？',
 *     'text'    => '删除后不可恢复',
 *     'icon'    => 'warning',
 *     'confirm_text' => '删除',
 *     'cancel_text'  => '取消',
 *     'confirm_url'  => '/users/1/delete',
 * ])
 * // 页面加载即弹出：'auto' => true
 */
class SweetAlert extends Component
{
    protected function defaults(): array
    {
        return [
            'trigger'         => null,
            'trigger_variant' => 'primary',
            'title'           => '',
            'text'            => null,
            'icon'            => null,     // success | error | warning | info | question
            'confirm_text'    => '确定',
            'cancel_text'     => null,     // 非空则显示取消按钮
            'confirm_url'     => null,
            'confirm_js'      => null,
            'auto'            => false,
            'options'         => [],
        ];
    }

    protected function assets(): array
    {
        return ['sweetalert2'];
    }

    protected function html(): string
    {
        $config = array_replace_recursive(array_filter([
            'title'             => $this->get('title'),
            'text'              => $this->get('text'),
            'icon'              => $this->get('icon'),
            'confirmButtonText' => $this->get('confirm_text'),
            'showCancelButton'  => $this->get('cancel_text') !== null,
            'cancelButtonText'  => $this->get('cancel_text'),
            'customClass'       => [
                'confirmButton' => 'btn btn-primary me-2 mt-2',
                'cancelButton'  => 'btn btn-light mt-2',
            ],
            'buttonsStyling' => false,
        ], fn ($v) => $v !== null), (array) $this->get('options', []));

        $xfConfig = [
            'swal' => $config,
            'auto' => (bool) $this->get('auto'),
            'confirmUrl' => $this->get('confirm_url'),
            'confirmJs'  => $this->get('confirm_js'),
        ];

        $json = json_encode($xfConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT);

        if ($this->get('trigger') !== null) {
            return '<button type="button"' . $this->attrs([
                'class'          => 'btn btn-' . $this->get('trigger_variant'),
                'data-xf'        => 'sweetalert',
                'data-xf-config' => $json,
            ]) . '>' . $this->e($this->get('trigger')) . '</button>';
        }

        return '<span' . $this->attrs(['data-xf' => 'sweetalert', 'data-xf-config' => $json, 'hidden' => true]) . '></span>';
    }
}

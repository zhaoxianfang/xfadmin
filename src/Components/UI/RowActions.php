<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 行操作 AJAX 声明组件
 *
 * 把后台列表「确认 → POST(CSRF) → toast → 重载表格」这一高频交互从控制器内联 JS
 * 收敛为声明式配置：本组件只输出一个带 data-xf="rowActions" 的空节点，
 * 真正的交互实现在 xfadmin.js 的 rowActions widget 中。
 *
 * 按钮本身由 dataTable 的 actions 列渲染（带 data-url/data-confirm/data-label），
 * 本组件负责统一接管这些按钮的点击行为。
 *
 * 用法：
 *   XfAdmin::rowActions([
 *       'selector' => '.btn-op',        // 需接管的按钮选择器（默认 [data-xf-row-action]）
 *       'table' => '#article-table',    // 成功后重载的表格选择器
 *       'confirm_delete' => '确认删除？',
 *       'delete_class' => 'btn-delete',
 *   ]);
 */
class RowActions extends Component
{
    /**
     * 默认配置
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'selector' => '[data-xf-row-action]', // 按钮选择器
            'table' => null,    // 成功后重载表格的选择器（如 '#dt-users'）
            'confirm_delete' => null, // 删除确认文案
            'delete_class' => null,   // 删除按钮 class（用于区分确认文案）
            'status_tpl' => null,     // 状态按钮确认文案模板（含 %s）
            'ok_text' => '操作成功',
            'fail_text' => '操作失败',
            'token' => null,    // 可选 CSRF token（留空则自动读页面 _token 隐藏域）
        ];
    }

    /**
     * 渲染声明节点
     */
    protected function html(): string
    {
        $cfg = [];
        $map = [
            'selector' => 'selector',
            'table' => 'table',
            'confirm_delete' => 'confirmDelete',
            'delete_class' => 'deleteClass',
            'status_tpl' => 'statusTpl',
            'ok_text' => 'okText',
            'fail_text' => 'failText',
            'token' => 'token',
        ];
        foreach ($map as $option => $key) {
            $value = $this->get($option);
            if ($value !== null) {
                $cfg[$key] = $value;
            }
        }
        $json = json_encode(
            $cfg,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
        );

        return '<div data-xf="rowActions" data-xf-config=\''.$json.'\' style="display:none" aria-hidden="true"></div>';
    }
}

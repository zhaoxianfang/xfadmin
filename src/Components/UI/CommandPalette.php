<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 命令面板（Command Palette）—— 对标 INSPINIA 缺失的全局快捷键命令中心
 *
 * XfAdmin::commandPalette([
 *     'id'      => 'xf-cmd-palette',
 *     'title'   => '快捷命令',
 *     'placeholder' => '输入命令或搜索…',
 *     'hotkey'  => 'meta+k',          // 唤起快捷键：meta+k / ctrl+k
 *     'commands'=> [                  // 命令列表
 *         ['label' => '新建用户', 'icon' => 'ti ti-user-plus', 'hint' => 'U', 'url' => '/admin/users/create'],
 *         ['label' => '系统设置', 'icon' => 'ti ti-settings', 'action' => 'openSettings'], // action 由 XFAdmin.onCommand 订阅
 *     ],
 * ])
 * 前端：XFAdmin.onCommand('openSettings', fn => ...) 订阅 action 类型命令。
 */
class CommandPalette extends Component
{
    protected function defaults(): array
    {
        return [
            'id'        => 'xf-cmd-palette',
            'title'     => '快捷命令',
            'placeholder' => '输入命令或搜索…',
            'hotkey'    => 'meta+k',
            'commands'  => [],
            'empty'     => '未找到匹配的命令',
        ];
    }

    protected function html(): string
    {
        $id   = $this->e($this->get('id'));
        $cmds = (array) $this->get('commands');
        $list = '';
        foreach ($cmds as $c) {
            $label = $this->e($c['label'] ?? '');
            $icon  = $this->e($c['icon'] ?? 'ti ti-command');
            $hint  = $this->e($c['hint'] ?? '');
            $url   = $this->e($c['url'] ?? '');
            $action = $this->e($c['action'] ?? '');
            $attrs = 'data-cmd ' . ($url ? 'data-url="' . $url . '"' : '') . ($action ? 'data-action="' . $action . '"' : '');
            $list .= '<button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 xf-cmd-item" ' . $attrs . '>'
                . '<i class="' . $icon . '"></i><span class="flex-grow-1 text-start">' . $label . '</span>'
                . ($hint ? '<kbd class="small">' . $hint . '</kbd>' : '') . '</button>';
        }

        $html = '<div class="modal fade xf-cmd-palette" id="' . $id . '" tabindex="-1" aria-hidden="true">'
            . '<div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:520px">'
            . '<div class="modal-content border-0 shadow-lg">'
            . '<div class="modal-body p-2">'
            . '<div class="input-group input-group-sm">'
            . '<span class="input-group-text bg-transparent border-0"><i class="ti ti-search"></i></span>'
            . '<input type="text" class="form-control border-0 shadow-none xf-cmd-input" placeholder="' . $this->e($this->get('placeholder')) . '" autocomplete="off">'
            . '</div>'
            . '<div class="list-group list-group-flush xf-cmd-list mt-1" style="max-height:340px;overflow:auto">' . $list . '</div>'
            . '<div class="text-muted small text-center py-3 xf-cmd-empty d-none">' . $this->e($this->get('empty')) . '</div>'
            . '</div></div></div></div>';

        // 快捷键 + 交互脚本
        $html .= '<script>document.addEventListener("DOMContentLoaded",function(){XFAdmin.initCommandPalette&&XFAdmin.initCommandPalette('
            . json_encode(['id' => $id, 'hotkey' => $this->get('hotkey')], JSON_HEX_TAG | JSON_HEX_AMP) . ');});</script>';

        return $html;
    }
}

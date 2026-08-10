<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 模态框
 *
 * XfAdmin::modal([
 *     'id'      => 'user-modal',
 *     'title'   => '编辑用户',
 *     'body'    => $form,
 *     'footer'  => '<button class="btn btn-light" data-bs-dismiss="modal">取消</button><button class="btn btn-primary">保存</button>',
 *     'size'    => 'lg',          // sm | lg | xl | fullscreen
 *     'centered'=> true,
 *     'scrollable' => false,
 *     'static'  => false,          // 点击遮罩不关闭
 *     'trigger' => '打开弹窗',      // 可选：自动生成触发按钮
 * ])
 */
class Modal extends Component
{
    protected function defaults(): array
    {
        return [
            'title'      => null,
            'body'       => '',
            'footer'     => null,
            'size'       => null,
            'centered'   => false,
            'scrollable' => false,
            'fade'       => true,
            'static'     => false,
            'close'      => true,
            'trigger'    => null,
            'trigger_variant' => 'primary',
        ];
    }

    protected function html(): string
    {
        $id   = $this->resolveId('xf-modal');
        $html = '';

        if ($this->get('trigger') !== null) {
            $html .= '<button type="button" class="btn btn-' . $this->e($this->get('trigger_variant')) . '" data-bs-toggle="modal" data-bs-target="#' . $this->e($id) . '">'
                . $this->e($this->get('trigger')) . '</button>';
        }

        $dialogClass = Html::cls('modal-dialog', [
            'modal-dialog-centered'   => $this->get('centered'),
            'modal-dialog-scrollable' => $this->get('scrollable'),
        ], $this->get('size') ? 'modal-' . $this->e($this->get('size')) : '');

        $attrs = [
            'class'    => Html::cls('modal', ['fade' => $this->get('fade')]),
            'id'       => $id,
            'tabindex' => '-1',
            'aria-hidden' => 'true',
        ];
        if ($this->get('static')) {
            $attrs['data-bs-backdrop'] = 'static';
            $attrs['data-bs-keyboard'] = 'false';
        }

        $html .= '<div' . $this->attrs($attrs) . '><div class="' . $dialogClass . '"><div class="modal-content">';
        if ($this->get('title') !== null || $this->get('close')) {
            $html .= '<div class="modal-header">';
            if ($this->get('title') !== null) {
                $html .= '<h5 class="modal-title">' . $this->raw($this->get('title')) . '</h5>';
            }
            if ($this->get('close')) {
                $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="modal-body">' . $this->raw($this->get('body')) . '</div>';
        if ($this->get('footer') !== null) {
            $html .= '<div class="modal-footer">' . $this->raw($this->get('footer')) . '</div>';
        }
        $html .= '</div></div></div>';

        return $html;
    }
}

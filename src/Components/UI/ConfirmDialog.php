<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 确认对话框（删除 / 危险操作二次确认）
 *
 * 将后台高频的「弹窗确认后再执行操作」封装为标准组件，直接取代各处手写的
 * if (!confirm('确认删除？')) return; 这类原生 JS 确认。
 * 触发按钮 + Bootstrap 模态框，确认后按 method 跳转（GET）或提交隐藏表单（POST，自动带 CSRF）。
 *
 * XfAdmin::confirmDialog([
 *     'message'       => '确认删除该任务？此操作不可恢复。',
 *     'title'         => '删除确认',
 *     'confirmText'   => '确认删除',
 *     'cancelText'    => '取消',
 *     'variant'       => 'danger',
 *     'icon'          => 'ti ti-alert-triangle',
 *     'url'           => '/admin/spider/delete?id=1',
 *     'method'        => 'POST',                 // GET=链接跳转；POST=提交隐藏表单（带 CSRF）
 *     'trigger'       => '删除',
 *     'triggerVariant'=> 'outline-danger',
 * ])
 */
class ConfirmDialog extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'id'             => null,              // 模态框 id（留空自动生成，多实例不冲突）
            'title'          => '操作确认',         // 弹窗标题
            'message'        => '',                // 确认提示正文（纯文本语义，输出转义）
            'icon'           => 'ti ti-alert-triangle', // 左侧警示图标 class
            'confirmText'    => '确定',             // 确认按钮文案
            'cancelText'     => '取消',             // 取消按钮文案
            'variant'        => 'danger',          // 确认按钮语义色：primary/success/danger/warning/info
            'method'         => 'POST',            // 提交方式：GET=链接跳转；POST=隐藏表单提交
            'url'            => null,              // 操作地址（必填）；GET 时作跳转目标，POST 时作表单 action
            'csrf'           => null,              // CSRF 令牌；留空自动取 XfAdmin::csrfToken()
            'size'           => null,              // 弹窗尺寸：sm|lg|xl|fullscreen（留空默认）
            'centered'       => true,              // 垂直居中
            'trigger'        => '删除',             // 触发按钮文案（留空则不渲染触发按钮，需自行 data-bs-toggle）
            'triggerVariant' => 'outline-danger',   // 触发按钮语义色（含 outline- 写法）
            'class'          => null,              // 附加到外层包裹的 class
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $url = $this->get('url');
        if ($url === null || $url === '') {
            // url 为必需项，缺失则不出内容，避免产出无效交互
            return '';
        }
        $safeUrl = $this->safeUrl($url);
        $variant = $this->enum($this->get('variant'), self::ENUM_VARIANT, 'danger');
        $triggerVariant = $this->enum($this->get('triggerVariant'), array_merge(self::ENUM_VARIANT, self::ENUM_VARIANT_OUTLINE), 'outline-danger');
        $method = strtoupper((string) $this->get('method')) === 'GET' ? 'GET' : 'POST';
        $id = $this->resolveId('xf-confirm');

        $html = '';
        if ($this->get('trigger') !== null) {
            $html .= '<button type="button" class="btn btn-'.$this->escape($triggerVariant).'" data-bs-toggle="modal" data-bs-target="#'.$this->escape($id).'">'
                .$this->escape($this->get('trigger')).'</button>';
        }

        $dialogClass = Html::cls('modal-dialog', [
            'modal-dialog-centered' => $this->get('centered'),
        ], $this->get('size') ? 'modal-'.$this->enum($this->get('size'), ['sm', 'lg', 'xl', 'fullscreen'], 'lg') : '');

        $titleId = $id.'-label';
        $dialogAttrs = [
            'class' => Html::cls('modal', ['fade' => true]),
            'id' => $id,
            'tabindex' => '-1',
            'aria-labelledby' => $titleId,
            'aria-hidden' => 'true',
        ];
        $html .= '<div'.$this->attrs($dialogAttrs).'><div class="'.$dialogClass.'"><div class="modal-content">';
        $html .= '<div class="modal-header"><h5 class="modal-title" id="'.$this->escape($titleId).'">'.$this->escape($this->get('title')).'</h5>'
            .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>';
        $html .= '<div class="modal-body"><div class="d-flex gap-3">'
            .'<div class="flex-shrink-0"><span class="avatar avatar-sm bg-'.$variant.'-subtle text-'.$variant.' rounded"><i class="'.$this->iconClass($this->get('icon')).'"></i></span></div>'
            .'<div class="flex-grow-1 pt-1">'.$this->escape($this->get('message')).'</div></div></div>';

        // 确认动作：GET 直接跳转；POST 提交隐藏表单（自动带 CSRF）
        if ($method === 'GET') {
            $confirm = '<a href="'.$this->escape($safeUrl).'" class="btn btn-'.$this->escape($variant).'">'.$this->escape($this->get('confirmText')).'</a>';
        } else {
            $csrf = $this->get('csrf') ?? XfAdmin::csrfToken();
            $formId = $id.'-form';
            $html .= '<form id="'.$this->escape($formId).'" method="POST" action="'.$this->escape($safeUrl).'" style="display:none">'
                .($csrf !== '' ? '<input type="hidden" name="_token" value="'.$this->escape($csrf).'">' : '')
                .'</form>';
            $confirm = '<button type="submit" form="'.$this->escape($formId).'" class="btn btn-'.$this->escape($variant).'">'.$this->escape($this->get('confirmText')).'</button>';
        }
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">'.$this->escape($this->get('cancelText')).'</button>'.$confirm.'</div>';
        $html .= '</div></div></div>';

        if ($this->get('class') !== null) {
            $html = '<span class="'.$this->escape($this->get('class')).'">'.$html.'</span>';
        }

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 邮件撰写（email-compose.html）—— 收件人/主题 + 富文本正文
 *
 * XfAdmin::emailCompose([
 *     'to'       => '', 'subject' => '', 'body' => '',
 *     'action'   => '/mail/send',
 *     'editor'   => 'quill',   // quill | textarea
 * ])
 */
class EmailCompose extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'to'      => '',
            'subject' => '',
            'body'    => '',
            'action'  => '#',
            'editor'  => 'quill',
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return $this->get('editor') === 'quill' ? ['quill'] : [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('mail-compose');
        $editor = $this->get('editor') === 'quill' ? 'quill' : 'textarea';

        $html = '<form' . $this->attrs(['class' => 'xf-mail-compose card', 'id' => $id, 'method' => 'post', 'action' => $this->get('action')]) . '>';
        $html .= '<div class="card-body">';
        $html .= '<div class="mb-3 row"><label class="col-sm-2 col-form-label">收件人</label><div class="col-sm-10">'
            . '<input type="text" name="to" class="form-control" value="' . $this->e($this->get('to')) . '" placeholder="name@example.com"></div></div>';
        $html .= '<div class="mb-3 row"><label class="col-sm-2 col-form-label">主题</label><div class="col-sm-10">'
            . '<input type="text" name="subject" class="form-control" value="' . $this->e($this->get('subject')) . '"></div></div>';
        $html .= '<div class="row"><label class="col-sm-2 col-form-label">正文</label><div class="col-sm-10">';

        if ($editor === 'quill') {
            $editorId = $this->resolveId('mail-body');
            $html .= '<div class="xf-quill"><div id="' . $editorId . '">' . $this->raw($this->get('body')) . '</div></div>'
                . '<input type="hidden" name="body" value="' . $this->e($this->get('body')) . '">';
        } else {
            $html .= '<textarea name="body" class="form-control" rows="10">' . $this->e($this->get('body')) . '</textarea>';
        }
        $html .= '</div></div></div>';
        $html .= '<div class="card-footer text-end d-flex gap-2 justify-content-end">'
            . '<button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>发送</button>'
            . '<button type="button" class="btn btn-light">存草稿</button></div>';
        $html .= '</form>';

        if ($editor === 'quill') {
            $js = 'XFAdmin.onReady(function(){var root=document.getElementById(' . Html::scriptJson($id) . ');if(!root)return;'
                . 'var ed=document.getElementById(' . Html::scriptJson($editorId) . ');if(!ed)return;var q=new Quill(ed,{theme:"snow"});'
                . 'q.on("text-change",function(){root.querySelector("input[name=body]").value=ed.querySelector(".ql-editor").innerHTML;});});';
            XfAdmin::assets()->inlineJs($js, 'xf-mail-quill-' . $id);
        }
        return $html;
    }
}

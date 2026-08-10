<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 评论/讨论线程（article.html / forum-post.html）—— 支持嵌套回复
 *
 * XfAdmin::commentThread([
 *     'items' => [
 *         [
 *             'avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'time' => '2小时前',
 *             'text' => '写得很好', 'likes' => 3,
 *             'replies' => [ ['avatar'=>'users/avatar-2.jpg','user'=>'李四','time'=>'1小时前','text'=>'同意'] ],
 *         ],
 *     ],
 *     'form' => true,   // 顶部发表框
 * ])
 */
class CommentThread extends Component
{
    protected function defaults(): array
    {
        return ['items' => [], 'form' => true, 'maxDepth' => 4];
    }

    protected function html(): string
    {
        $id = $this->resolveId('comments');
        $html = '<div' . $this->attrs(['class' => 'xf-comments', 'id' => $id]) . '>';

        if ($this->get('form')) {
            $html .= '<form class="mb-4" data-xf="comment-form">'
                . '<textarea name="comment" class="form-control" rows="3" placeholder="写下你的评论…"></textarea>'
                . '<div class="text-end mt-2"><button class="btn btn-primary btn-sm" type="submit">发表</button></div>'
                . '</form>';
        }

        $html .= '<div class="list-unstyled mb-0">';
        foreach (array_values((array) $this->get('items')) as $c) {
            $html .= $this->renderItem((array) $c, 1);
        }
        $html .= '</div></div>';

        $js = 'XFAdmin.register("comment-form",function(form){'
            . 'form.addEventListener("submit",function(e){'
            . 'e.preventDefault();var ta=form.querySelector("textarea");var v=(ta.value||"").trim();if(!v)return;'
            . 'form.dispatchEvent(new CustomEvent("xf.comment.post",{detail:{text:v},bubbles:true}));'
            . 'var box=form.parentElement.querySelector(".xf-comments .list-unstyled");'
            . 'var d=document.createElement("div");d.className="d-flex mb-3";'
            . 'd.innerHTML=\'<div class="flex-grow-1"><div class="d-flex"><h6 class="mb-1">我</h6><small class="text-muted ms-2">刚刚</small></div><p class="mb-1"></p></div>\';'
            . 'd.querySelector("p").textContent=v;if(box)box.prepend(d);ta.value="";'
            . '});'
            . '});'
            . 'if(!window.__xfReplyInit){window.__xfReplyInit=1;'
            . 'document.addEventListener("click",function(e){'
            . 'var btn=e.target.closest(\'[data-xf="reply"]\');if(!btn)return;'
            . 'var item=btn.closest(".d-flex.mb-3");if(!item||item.querySelector(":scope > .xf-reply-box"))return;'
            . 'var box=document.createElement("div");box.className="xf-reply-box mt-2";'
            . 'box.innerHTML=\'<textarea class="form-control form-control-sm mb-2" rows="2" placeholder="回复…"></textarea><div class="text-end"><button type="button" class="btn btn-sm btn-primary">回复</button></div>\';'
            . 'item.appendChild(box);var ta=box.querySelector("textarea");ta.focus();'
            . 'box.querySelector("button").addEventListener("click",function(){'
            . 'var v=(ta.value||"").trim();if(!v)return;'
            . 'var rep=document.createElement("div");rep.className="d-flex mb-3 ms-4 ps-3 border-start";'
            . 'rep.innerHTML=\'<div class="flex-grow-1"><div class="d-flex"><h6 class="mb-1">我</h6><small class="text-muted ms-2">刚刚</small></div><p class="mb-1"></p></div>\';'
            . 'rep.querySelector("p").textContent=v;item.appendChild(rep);box.remove();'
            . 'item.dispatchEvent(new CustomEvent("xf.comment.reply",{detail:{text:v},bubbles:true}));'
            . '});'
            . '});'
            . '}';
        XfAdmin::assets()->inlineJs($js, 'xf-comment-form');

        return $html;
    }

    private function renderItem(array $c, int $depth): string
    {
        $avatar = $c['avatar'] ?? '';
        // 评论者头像：统一 INSPINIA 规范 .avatar 包裹结构（avatar-md=36px）；占位缩写与图片同尺寸
        $avatarHtml = $avatar !== ''
            ? '<span class="avatar avatar-md me-2 flex-shrink-0"><img src="' . $this->e($this->img($avatar)) . '" class="img-fluid rounded-circle" alt="" style="object-fit:cover;"></span>'
            : '<span class="avatar avatar-md me-2 flex-shrink-0"><span class="avatar-title bg-light text-secondary rounded-circle fw-semibold">' . $this->e(mb_substr((string) ($c['user'] ?? '?'), 0, 1)) . '</span></span>';
        $html = '<div class="d-flex mb-3 ' . ($depth > 1 ? 'ms-4 ps-3 border-start' : '') . '">';
        $html .= $avatarHtml;
        $html .= '<div class="flex-grow-1">';
        $html .= '<div class="d-flex align-items-center"><h6 class="mb-0">' . $this->e($c['user'] ?? '') . '</h6>'
            . '<small class="text-muted ms-2">' . $this->e($c['time'] ?? '') . '</small></div>';
        $html .= '<p class="mb-1">' . $this->e($c['text'] ?? '') . '</p>';
        $html .= '<div class="small text-muted d-flex gap-3">';
        if (isset($c['likes'])) {
            $html .= '<span><i class="ti ti-heart"></i> ' . $this->e($c['likes']) . '</span>';
        }
        if ($depth < (int) $this->get('maxDepth')) {
            $html .= '<a href="#" class="text-decoration-none" data-xf="reply">回复</a>';
        }
        $html .= '</div>';
        if (! empty($c['replies'])) {
            $html .= '<div class="mt-3">';
            foreach ((array) $c['replies'] as $r) {
                $html .= $this->renderItem((array) $r, $depth + 1);
            }
            $html .= '</div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace XfAdmin\Components\Misc;

use XfAdmin\Components\Component;
use XfAdmin\Support\Html;
use XfAdmin\XfAdmin;

/**
 * 文本对比（misc-text-diff）—— 基于本地 jsdiff 渲染行内/并排差异
 *
 * XfAdmin::textDiff([
 *     'old' => $v1,
 *     'new' => $v2,
 *     'mode'=> 'inline',   // inline | split
 * ])
 */
class TextDiff extends Component
{
    protected function defaults(): array
    {
        return ['old' => '', 'new' => '', 'mode' => 'inline'];
    }

    protected function assets(): array
    {
        return ['diff'];
    }

    protected function html(): string
    {
        $id = $this->resolveId('diff');
        $mode = $this->get('mode') === 'split' ? 'split' : 'inline';
        $html = '<div' . $this->attrs(['class' => 'xf-diff ' . ($mode === 'split' ? 'row' : ''), 'id' => $id])
            . ' data-xf="diff" data-xf-config="' . $this->e(json_encode([
                'old'  => $this->get('old'),
                'new'  => $this->get('new'),
                'mode' => $mode,
            ])) . '"></div>';

        $js = 'XFAdmin.register("diff",function(el){if(!window.Diff)return;'
            . 'var c=JSON.parse(el.getAttribute("data-xf-config")||"{}");'
            . 'var cls=el.className.indexOf("row")>=0?"col-md-6":"",out="";'
            . 'if(c.mode==="split"){'
            . 'var p1=Diff.diffLines(c.old,c.new);var h1="";p1.forEach(function(p){if(!p.added)h1+=p.value;});'
            . 'var p2=Diff.diffLines(c.old,c.new);var h2="";p2.forEach(function(p){if(!p.removed)h2+=p.value;});'
            . 'out=`<pre class="${cls} border p-2">${esc(h1)}</pre><pre class="${cls} border p-2">${esc(h2)}</pre>`;'
            . '}else{'
            . 'var parts=Diff.diffWordsWithSpace(c.old,c.new);var html="";'
            . 'parts.forEach(function(p){var t=esc(p.value);if(p.added)html+=`<span class="bg-success-subtle text-success">${t}</span>`;else if(p.removed)html+=`<span class="bg-danger-subtle text-danger text-decoration-line-through">${t}</span>`;else html+=t;});'
            . 'out=`<pre class="border p-2">${html}</pre>`;}'
            . 'function esc(s){return (s||"").replace(/[&<>]/g,function(m){return {"&":"&amp;","<":"&lt;",">":"&gt;"}[m];});}'
            . 'el.innerHTML=out;});';
        XfAdmin::assets()->inlineJs($js, 'xf-diff');

        return $html;
    }
}

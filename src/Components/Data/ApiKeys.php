<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * API 密钥管理（api-keys.html）—— 列表展示、复制、显示/隐藏、重新生成
 *
 * XfAdmin::apiKeys([
 *     'items' => [
 *         ['name' => '生产环境', 'key' => 'sk-live-xxxx', 'created' => '2026-01-01', 'last_used' => '2天前'],
 *     ],
 * ])
 */
class ApiKeys extends Component
{
    protected function defaults(): array
    {
        return ['items' => [], 'reveal' => true, 'regenerate' => true];
    }

    protected function assets(): array
    {
        return [];
    }

    protected function html(): string
    {
        $id = $this->resolveId('apikeys');
        $items = array_values((array) $this->get('items'));
        $reveal = $this->get('reveal');
        $regen = $this->get('regenerate');

        $html = '<div' . $this->attrs(['class' => 'xf-apikeys', 'id' => $id]) . '>';
        $html .= '<div class="table-responsive"><table class="table table-nowrap align-middle mb-0"><thead><tr>'
            . '<th>名称</th><th>密钥</th><th>创建时间</th><th>最近使用</th><th class="text-end">操作</th></tr></thead><tbody>';

        foreach ($items as $idx => $it) {
            $it = (array) $it;
            $key = $it['key'] ?? '';
            $masked = $this->mask($key);
            $html .= '<tr>'
                . '<td class="fw-medium">' . $this->e($it['name'] ?? '') . '</td>'
                . '<td><code class="xf-key" data-real="' . $this->e($key) . '">' . $this->e($masked) . '</code></td>'
                . '<td>' . $this->e($it['created'] ?? '') . '</td>'
                . '<td>' . $this->e($it['last_used'] ?? '') . '</td>'
                . '<td class="text-end d-flex gap-1 justify-content-end">'
                . '<button type="button" class="btn btn-sm btn-soft-light" data-xf="copy" data-copy="' . $this->e($key) . '" title="复制"><i class="ti ti-copy"></i></button>';
            if ($reveal) {
                $html .= '<button type="button" class="btn btn-sm btn-soft-light" data-xf="key-toggle" data-idx="' . $idx . '" title="显示/隐藏"><i class="ti ti-eye"></i></button>';
            }
            if ($regen) {
                $html .= '<button type="button" class="btn btn-sm btn-soft-danger" data-xf="key-regen" title="重新生成"><i class="ti ti-refresh"></i></button>';
            }
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table></div></div>';

        $js = 'if(!window.__xfApiKeysInit){window.__xfApiKeysInit=1;'
            . 'document.addEventListener("click",function(e){'
            . 'var copy=e.target.closest(\'[data-xf="copy"]\');'
            . 'if(copy){var txt=copy.getAttribute("data-copy")||"";'
            . 'if(navigator.clipboard){navigator.clipboard.writeText(txt).then(function(){var i=copy.querySelector("i");if(i){var o=i.className;i.className="ti ti-check text-success";setTimeout(function(){i.className=o;},1200);}});}}'
            . 'var regen=e.target.closest(\'[data-xf="key-regen"]\');'
            . 'if(regen){regen.dispatchEvent(new CustomEvent("xf.apikey.regen",{bubbles:true}));}'
            . '});}'
            . 'XFAdmin.register("key-toggle",function(btn){btn.addEventListener("click",function(){'
            . 'var row=btn.closest("tr");var code=row.querySelector(".xf-key");'
            . 'var real=code.getAttribute("data-real");'
            . 'var shown=code.getAttribute("data-shown")==="1";'
            . 'if(shown){code.textContent=mask(real);code.setAttribute("data-shown","0");btn.querySelector("i").className="ti ti-eye";}'
            . 'else{code.textContent=real;code.setAttribute("data-shown","1");btn.querySelector("i").className="ti ti-eye-off";}'
            . 'function mask(k){return k.slice(0,4)+"\\u2022\\u2022\\u2022\\u2022"+(k.slice(-4));}'
            . '});});';
        XfAdmin::assets()->inlineJs($js, 'xf-apikeys');

        return $html;
    }

    private function mask(string $key): string
    {
        if ($key === '') {
            return '';
        }

        return substr($key, 0, 4) . str_repeat('•', max(4, strlen($key) - 8)) . substr($key, -4);
    }
}

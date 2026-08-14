<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * PDF 查看器（misc-pdf-viewer）—— 使用本地 pdf.js 渲染，完全离线
 *
 * XfAdmin::pdfViewer([
 *     'url'    => '/files/doc.pdf',
 *     'height' => 600,
 *     'toolbar'=> true,    // 页码/缩放/下载工具栏
 * ])
 */
class PdfViewer extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'url'     => '',
            'height'  => 600,
            'toolbar' => true,
            'download'=> true,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['pdfjs'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('pdf');
        // 外链 / data URI 原样使用，本地路径走资源解析；空地址保持为空，避免请求资源基址
        $raw = (string) $this->get('url');
        $url = $raw === ''
            ? ''
            : $this->e(preg_match('#^(https?:)?//|^data:#i', $raw) ? $raw : XfAdmin::asset(ltrim($raw, '/')));

        $html = '<div' . $this->attrs(['class' => 'xf-pdf card border-0 shadow-sm', 'id' => $id]) . '>';
        if ($this->get('toolbar')) {
            $html .= '<div class="card-header d-flex align-items-center gap-2 py-2">'
                . '<button class="btn btn-sm btn-soft-primary" data-role="prev"><i class="ti ti-chevron-left"></i></button>'
                . '<span class="small" data-role="page">1 / 1</span>'
                . '<button class="btn btn-sm btn-soft-primary" data-role="next"><i class="ti ti-chevron-right"></i></button>'
                . '<span class="vr mx-1"></span>'
                . '<select class="form-select form-select-sm w-auto" data-role="zoom">'
                . '<option value="0.8">80%</option><option value="1" selected>100%</option><option value="1.25">125%</option><option value="1.5">150%</option></select>';
            if ($this->get('download') && $raw !== '') {
                $html .= '<a class="btn btn-sm btn-soft-success ms-auto" href="' . $url . '" download><i class="ti ti-download"></i></a>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="card-body p-2 overflow-auto" data-role="viewport" style="height:' . (int) $this->get('height') . 'px"></div>';
        $html .= '</div>';

        $worker = $this->e(XfAdmin::asset('plugins/pdfjs/pdf.worker.min.js'));
        $js = 'XFAdmin.onReady(function(){'
            . 'var root=document.getElementById(' . Html::scriptJson($id) . ');if(!root)return;'
            . 'var vp=root.querySelector("[data-role=viewport]");var url=' . Html::scriptJson($url) . ';if(!url){fail("未提供 PDF 文件地址");return;}'
            . 'function fail(msg){vp.innerHTML=\'<div class="text-center text-muted py-5"><i class="ti ti-file-off fs-1 d-block mb-2"></i>\'+msg+\'</div>\';}'
            . 'if(!window.pdfjsLib){fail("PDF 渲染引擎未加载");return;}'
            . 'pdfjsLib.GlobalWorkerOptions.workerSrc=' . Html::scriptJson($worker) . ';'
            . 'var pdf=null,pageNum=1,zoom=1,rendering=false,pending=null;'
            . 'function render(p){if(!pdf)return;'
            . 'if(rendering){pending=p;return;}rendering=true;'
            . 'pdf.getPage(p).then(function(page){var vport=page.getViewport({scale:zoom});'
            . 'var c=document.createElement("canvas");c.width=vport.width;c.height=vport.height;c.className="d-block mx-auto mb-2 shadow-sm";'
            . 'vp.innerHTML="";vp.appendChild(c);'
            . 'page.render({canvasContext:c.getContext("2d"),viewport:vport}).promise.then(function(){'
            . 'rendering=false;if(pending!=null){var n=pending;pending=null;render(n);}});'
            . 'var lbl=root.querySelector("[data-role=page]");if(lbl)lbl.textContent=p+" / "+pdf.numPages;'
            . '}).catch(function(){rendering=false;fail("页面渲染失败");});}'
            . 'vp.innerHTML=\'<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-1"></span>正在加载 PDF…</div>\';'
            . 'pdfjsLib.getDocument(url).promise.then(function(d){pdf=d;render(1);})'
            . '.catch(function(e){fail("PDF 加载失败："+((e&&e.message)||"文件不可用"));});'
            . 'var prev=root.querySelector("[data-role=prev]"),next=root.querySelector("[data-role=next]"),zoomSel=root.querySelector("[data-role=zoom]");'
            . 'if(prev)prev.addEventListener("click",function(){if(pdf&&pageNum>1){pageNum--;render(pageNum);}});'
            . 'if(next)next.addEventListener("click",function(){if(pdf&&pageNum<pdf.numPages){pageNum++;render(pageNum);}});'
            . 'if(zoomSel)zoomSel.addEventListener("change",function(){zoom=parseFloat(zoomSel.value)||1;render(pageNum);});'
            . '});';
        XfAdmin::assets()->inlineJs($js, 'xf-pdf-' . $id);

        return $html;
    }
}

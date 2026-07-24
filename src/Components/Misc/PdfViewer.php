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
    protected function defaults(): array
    {
        return [
            'url'     => '',
            'height'  => 600,
            'toolbar' => true,
            'download'=> true,
        ];
    }

    protected function assets(): array
    {
        return ['pdfjs'];
    }

    protected function html(): string
    {
        $id = $this->resolveId('pdf');
        $url = $this->e(XfAdmin::asset(ltrim($this->get('url'), '/')));

        $html = '<div' . $this->attrs(['class' => 'xf-pdf card border-0 shadow-sm', 'id' => $id]) . '>';
        if ($this->get('toolbar')) {
            $html .= '<div class="card-header d-flex align-items-center gap-2 py-2">'
                . '<button class="btn btn-sm btn-soft-primary" data-role="prev"><i class="ti ti-chevron-left"></i></button>'
                . '<span class="small" data-role="page">1 / 1</span>'
                . '<button class="btn btn-sm btn-soft-primary" data-role="next"><i class="ti ti-chevron-right"></i></button>'
                . '<span class="vr mx-1"></span>'
                . '<select class="form-select form-select-sm w-auto" data-role="zoom">'
                . '<option value="0.8">80%</option><option value="1" selected>100%</option><option value="1.25">125%</option><option value="1.5">150%</option></select>';
            if ($this->get('download')) {
                $html .= '<a class="btn btn-sm btn-soft-success ms-auto" href="' . $url . '" download><i class="ti ti-download"></i></a>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="card-body p-2 overflow-auto" data-role="viewport" style="height:' . (int) $this->get('height') . 'px"></div>';
        $html .= '</div>';

        $worker = $this->e(XfAdmin::asset('plugins/pdfjs/pdf.worker.min.js'));
        $js = 'XFAdmin.onReady(function(){'
            . 'var root=document.getElementById(' . Html::scriptJson($id) . ');if(!root||!window.pdfjsLib)return;'
            . 'pdfjsLib.GlobalWorkerOptions.workerSrc=' . Html::scriptJson($worker) . ';'
            . 'var vp=root.querySelector("[data-role=viewport]");var url=' . Html::scriptJson($url) . ';'
            . 'var pdf=null,pageNum=1,zoom=1;'
            . 'function render(p){pdf.getPage(p).then(function(page){var vport=page.getViewport({scale:zoom});'
            . 'var c=document.createElement("canvas");c.width=vport.width;c.height=vport.height;c.className="d-block mx-auto mb-2 shadow-sm";'
            . 'vp.innerHTML="";vp.appendChild(c);page.render({canvasContext:c.getContext("2d"),viewport:vport});'
            . 'var lbl=root.querySelector("[data-role=page]");if(lbl)lbl.textContent=p+" / "+pdf.numPages;});}'
            . 'pdfjsLib.getDocument(url).promise.then(function(d){pdf=d;render(1);});'
            . 'var prev=root.querySelector("[data-role=prev]"),next=root.querySelector("[data-role=next]"),zoomSel=root.querySelector("[data-role=zoom]");'
            . 'if(prev)prev.addEventListener("click",function(){if(pageNum>1){pageNum--;render(pageNum);}});'
            . 'if(next)next.addEventListener("click",function(){if(pdf&&pageNum<pdf.numPages){pageNum++;render(pageNum);}});'
            . 'if(zoomSel)zoomSel.addEventListener("change",function(){zoom=parseFloat(zoomSel.value)||1;render(pageNum);});'
            . '});';
        XfAdmin::assets()->inlineJs($js, 'xf-pdf-' . $id);

        return $html;
    }
}

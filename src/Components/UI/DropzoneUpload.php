<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 拖拽上传区（Dropzone Upload）—— 对标 INSPINIA 缺失的拖拽上传组件
 *
 * XfAdmin::dropzoneUpload([
 *     'id'        => 'xf-dropzone',
 *     'url'       => '/admin/api/upload',   // 上传端点（XFAdmin.request POST）
 *     'multiple'  => true,
 *     'accept'    => 'image/*,.pdf',
 *     'maxSize'   => 10,                     // MB
 *     'hint'      => '将文件拖到此处，或点击选择',
 *     'value'     => [],                     // 已上传文件 [{name,url,size}]
 * ])
 * 前端：拖拽/选择后自动 XFAdmin.request(url) 上传；成功回调 XFAdmin.onUpload('id', res)。
 */
class DropzoneUpload extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'id'       => 'xf-dropzone',
            'url'      => '',
            'multiple' => true,
            'accept'   => '*',
            'maxSize'  => 10,         // MB
            'hint'     => '将文件拖到此处，或点击浏览',
            'value'    => [],
            'name'     => 'file',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id   = $this->e($this->get('id'));
        $url  = $this->e($this->get('url'));
        $initial = json_encode((array) $this->get('value'), JSON_HEX_TAG | JSON_HEX_AMP);

        $html = '<div class="xf-dropzone border rounded-3 p-4 text-center" id="' . $id . '"'
            . ' data-xf-dropzone data-url="' . $url . '"'
            . ' data-multiple="' . ($this->get('multiple') ? '1' : '0') . '"'
            . ' data-accept="' . $this->e($this->get('accept')) . '"'
            . ' data-max-size="' . (int) $this->get('maxSize') . '"'
            . ' data-name="' . $this->e($this->get('name')) . '">'
            . '<i class="ti ti-cloud-upload fs-2 text-primary"></i>'
            . '<p class="mb-1">' . $this->e($this->get('hint')) . '</p>'
            . '<small class="text-muted">支持 ' . $this->e($this->get('accept')) . '，单文件 ≤ ' . (int) $this->get('maxSize') . 'MB</small>'
            . '<input type="file" class="xf-dropzone-input d-none" ' . ($this->get('multiple') ? 'multiple' : '') . ' accept="' . $this->e($this->get('accept')) . '">'
            . '<div class="xf-dropzone-list d-flex flex-wrap gap-2 justify-content-center mt-3">' . $this->renderInitial($initial) . '</div>'
            . '</div>';

        $html .= '<script>document.addEventListener("DOMContentLoaded",function(){XFAdmin.initDropzone&&XFAdmin.initDropzone('
            . json_encode(['id' => $id, 'initial' => (array) $this->get('value')], JSON_HEX_TAG | JSON_HEX_AMP) . ');});</script>';

        return $html;
    }

    /**
     * render Initial（protected实例方法）
     *
     * @param string $json json
     *
     * @return string result
     */
    protected function renderInitial(string $json): string
    {
        // initial 由 JS 渲染，PHP 仅占位（避免重复逻辑）
        return '';
    }
}

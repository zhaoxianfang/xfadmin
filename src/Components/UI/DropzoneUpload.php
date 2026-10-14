<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Assets\Assets;
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
            // id 缺省时由 resolveId('xf-dropzone') 生成唯一 id，避免多实例 id 冲突
            'id' => null,
            'url' => '',
            'multiple' => true,
            'accept' => '*',
            'maxSize' => 10,         // MB
            'hint' => '将文件拖到此处，或点击浏览',
            'value' => [],
            'name' => 'file',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        // 取或生成根元素 id（原始值）：HTML 属性交给 attrs() 转义，script 内 JSON 用原始值，
        // 避免先 e() 再 json_encode 的双重编码导致 JS 拿到 &quot; 等实体
        $id = $this->resolveId('xf-dropzone');

        $html = '<div'.$this->attrs([
            'class' => 'xf-dropzone border rounded-3 p-4 text-center',
            'id' => $id,
            'data-xf-dropzone' => true,
            'data-url' => $this->safeUrl($this->get('url')),
            'data-multiple' => $this->get('multiple') ? '1' : '0',
            'data-accept' => $this->get('accept'),
            'data-max-size' => (int) $this->get('maxSize'),
            'data-name' => $this->get('name'),
        ]).'>'
            .'<i class="ti ti-cloud-upload fs-2 text-primary"></i>'
            .'<p class="mb-1">'.$this->e($this->get('hint')).'</p>'
            .'<small class="text-muted">支持 '.$this->e($this->get('accept')).'，单文件 ≤ '.(int) $this->get('maxSize').'MB</small>'
            .'<input type="file" class="xf-dropzone-input d-none" '.($this->get('multiple') ? 'multiple' : '').' accept="'.$this->e($this->get('accept')).'">'
            .'<div class="xf-dropzone-list d-flex flex-wrap gap-2 justify-content-center mt-3"></div>'
            .'</div>';

        // 初始化脚本：注册为内联 JS（同 key 去重，重复 render 不重复输出）；
        // initial 由 JS 消费渲染，PHP 不再传递 $initial（renderInitial 恒返回 ''，整条传递链无消费方）
        $js = 'document.addEventListener("DOMContentLoaded",function(){XFAdmin.initDropzone&&XFAdmin.initDropzone('
            .json_encode(['id' => $id, 'initial' => (array) $this->get('value')], JSON_HEX_TAG | JSON_HEX_AMP).');});';
        Assets::instance()->inlineJs($js, 'xf-dropzone-init');

        return $html;
    }
}

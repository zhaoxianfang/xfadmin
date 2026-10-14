<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 搜索框（带图标与可选尺寸的输入框，常用于 Toolbar 左区）
 *
 * XfAdmin::searchBox([
 *     'name'        => 'q',
 *     'value'       => '',
 *     'placeholder' => '搜索...',
 *     'size'        => '',          // '' | 'sm' | 'lg'
 *     'class'       => '',
 * ])
 */
class SearchBox extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'name' => 'q',
            'value' => '',
            'placeholder' => '搜索...',
            'size' => '',
            'class' => '',
            'id' => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-search');

        $igClass = 'input-group xf-search';
        // size 白名单（sm|lg）：非法/空值都不加尺寸类，防止任意 class 注入
        $size = $this->get('size') !== '' ? $this->enum($this->get('size'), self::ENUM_SIZE, '') : '';
        if ($size !== '') {
            $igClass .= ' input-group-'.$size;
        }
        if ($this->get('class') !== '') {
            $igClass .= ' '.$this->get('class');
        }

        return '<div'.$this->attrs(['class' => $igClass]).'>'
            .'<span class="input-group-text"><i class="ti ti-search"></i></span>'
            .'<input type="search" class="form-control" id="'.$this->e($id).'"'
            .' name="'.$this->e($this->get('name')).'"'
            .' value="'.$this->e((string) $this->get('value')).'"'
            .' placeholder="'.$this->e($this->get('placeholder')).'">'
            .'</div>';
    }
}

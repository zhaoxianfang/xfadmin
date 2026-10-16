<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 列表筛选条（关键词输入 + 下拉筛选 + 重置 / 查询按钮的横向工具条）
 *
 * 取代各业务模块手写的「搜索框 + 多个 select + 重置按钮」拼接，统一列表页
 * 筛选区结构。支持 text / select 两种字段、自动查询（onchange）与显式提交按钮。
 *
 * XfAdmin::filterBar([
 *     'fields' => [
 *         ['name' => 'name', 'type' => 'text', 'placeholder' => '按名称搜索', 'width' => 'col-md-4'],
 *         ['name' => 'type', 'type' => 'select', 'options' => ['' => '全部类型', 1 => '列表', 2 => '详情']],
 *     ],
 *     'action' => '/spider/list',
 * ])
 * XfAdmin::filterBar(['fields' => $fields, 'reset' => '重置', 'submit' => '查询', 'onchange' => true])
 */
class FilterBar extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'fields' => [],           // 筛选字段数组（结构见各字段说明）
            'action' => null,         // 表单提交地址（留空不包裹 form，仅输出字段）
            'method' => 'GET',        // 表单提交方法：GET | POST
            'reset' => true,          // 重置按钮：true 显示默认文案「重置」/ 字符串自定义文案 / false 不显示
            'submit' => false,        // 提交按钮：true 显示默认文案「查询」/ 字符串自定义文案 / false 不显示
            'onchange' => false,       // 字段变更即自动提交（需配合 action）
            'class' => null,          // 追加到根元素的 class
            'id' => null,             // 根元素 id（留空自动生成）
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $root = Html::cls('xf-filter-bar', (string) ($this->get('class') ?? ''));
        $attrs = ['class' => $root];
        if ($this->get('id') !== null) {
            $attrs['id'] = (string) $this->get('id');
        }

        $body = $this->renderFields((array) $this->get('fields'));
        $body .= $this->renderButtons();

        if ($this->get('action') !== null) {
            $formAttrs = [
                'class' => 'xf-filter-bar__form',
                'method' => $this->enum($this->get('method'), ['GET', 'POST'], 'GET'),
                'action' => $this->safeUrl($this->get('action')),
            ];
            if ($this->get('onchange')) {
                $formAttrs['data-xf-auto-submit'] = '1';
            }

            return '<div'.$this->attrs($attrs).'>'.'<form'.$this->attrs($formAttrs).'>'.$body.'</form>'.'</div>';
        }

        return '<div'.$this->attrs($attrs).'>'.$body.'</div>';
    }

    /**
     * 渲染筛选字段
     *
     * @param array $fields 字段配置数组
     * @return string result
     */
    private function renderFields(array $fields): string
    {
        $out = '';
        foreach ($fields as $field) {
            $field = (array) $field;
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $label = $this->escape($field['label'] ?? '');
            $width = (string) ($field['width'] ?? 'col-md-3');
            $controlId = 'xf-filter-'.$name;
            $control = $field['type'] === 'select'
                ? $this->renderSelect($name, $field)
                : $this->renderText($name, $field);

            $out .= '<div class="xf-filter-bar__field '.$width.'">'
                .($label !== '' ? '<label class="xf-filter-bar__label" for="'.$controlId.'">'.$label.'</label>' : '')
                .$control
                .'</div>';
        }

        return $out;
    }

    /**
     * 渲染文本输入
     *
     * @param string $name 字段名
     * @param array $field 字段配置
     * @return string result
     */
    private function renderText(string $name, array $field): string
    {
        $attrs = [
            'id' => 'xf-filter-'.$name,
            'name' => $name,
            'class' => 'form-control xf-filter-bar__input',
            'placeholder' => $this->escape($field['placeholder'] ?? ''),
        ];
        if (! empty($field['value'])) {
            $attrs['value'] = $this->escape($field['value']);
        }
        if (! empty($field['required'])) {
            $attrs['required'] = true;
        }

        return '<input'.$this->attrs($attrs).'>';
    }

    /**
     * 渲染下拉选择
     *
     * @param string $name 字段名
     * @param array $field 字段配置
     * @return string result
     */
    private function renderSelect(string $name, array $field): string
    {
        $attrs = [
            'id' => 'xf-filter-'.$name,
            'name' => $name,
            'class' => 'form-select xf-filter-bar__input',
        ];
        $current = (string) ($field['value'] ?? '');
        $options = (array) ($field['options'] ?? []);
        $optHtml = '';
        foreach ($options as $val => $text) {
            $selected = (string) $val === $current ? ' selected' : '';
            $optHtml .= '<option value="'.$this->escape((string) $val).'"'.$selected.'>'.$this->escape((string) $text).'</option>';
        }

        return '<select'.$this->attrs($attrs).'>'.$optHtml.'</select>';
    }

    /**
     * 渲染重置 / 提交按钮
     *
     * @return string result
     */
    private function renderButtons(): string
    {
        $out = '';
        if ($this->get('reset') !== false) {
            $label = is_string($this->get('reset')) ? $this->get('reset') : '重置';
            $out .= '<button type="reset" class="btn btn-ghost btn-sm xf-filter-bar__btn">'.$this->escape($label).'</button>';
        }
        if ($this->get('submit') !== false) {
            $label = is_string($this->get('submit')) ? $this->get('submit') : '查询';
            $out .= '<button type="submit" class="btn btn-primary btn-sm xf-filter-bar__btn">'.$this->escape($label).'</button>';
        }

        return $out;
    }
}

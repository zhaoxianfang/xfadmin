<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form\Concerns;

use zxf\XfAdmin\Support\Html;

/**
 * 表单字段通用包装：label / 帮助文本 / 校验反馈 / 外层间距
 */
trait FieldWrapper
{
    /**
     * field Defaults（protected实例方法）
     *
     * @return array result
     */
    protected function fieldDefaults(): array
    {
        return [
            'name' => null,
            'id' => null,
            'label' => null,
            'help' => null,
            'required' => false,
            'disabled' => false,
            'readonly' => false,
            'value' => null,
            'placeholder' => null,
            'wrapper' => 'mb-3',   // 外层 class；false 则不渲染外层
            'feedback' => null,     // ['valid' => '', 'invalid' => '']
            'state' => null,     // 'valid' | 'invalid' | null（显式校验着色，配合服务端回显）
        ];
    }

    /**
     * 控件校验状态 class（is-valid / is-invalid）
     * 用于把校验状态着色追加到 form-control / form-select。
     *
     * @return string result
     */
    protected function controlStateClass(): string
    {
        return match ($this->get('state')) {
            'valid' => 'is-valid',
            'invalid' => 'is-invalid',
            default => '',
        };
    }

    /**
     * 无障碍接线：为控件生成 aria-describedby（指向 help / 反馈节点 id）
     * 屏幕阅读器在聚焦控件时可自动播报说明与错误。
     *
     * @param  string  $forId  控件 id
     * @return array result
     */
    protected function describeAttrs(string $forId): array
    {
        $ids = [];
        if ($this->get('help') !== null) {
            $ids[] = $forId.'-help';
        }
        $feedback = (array) ($this->get('feedback') ?? []);
        if (isset($feedback['invalid'])) {
            $ids[] = $forId.'-invalid';
        }
        if (isset($feedback['valid'])) {
            $ids[] = $forId.'-valid';
        }

        return $ids === [] ? [] : ['aria-describedby' => implode(' ', $ids)];
    }

    /**
     * 校验失败时输出 aria-invalid（无障碍语义）
     *
     * @return array result
     */
    protected function stateAria(): array
    {
        return $this->get('state') === 'invalid' ? ['aria-invalid' => 'true'] : [];
    }

    /**
     * 生成控件完整属性数组：合并校验状态 class + 无障碍接线（aria-describedby / aria-invalid）
     * 各字段组件在构建完基础属性（含自定义属性）后调用，统一接入 state/help 机制，
     * 避免每个组件重复拼接，保证校验着色与无障碍语义一致可用。
     *
     * @param  array  $attrs  已含 class 的属性数组（自定义属性已合并）
     * @param  string  $forId  控件 id（help / 反馈节点 id 前缀）
     * @return array result
     */
    protected function controlAttrs(array $attrs, string $forId): array
    {
        $attrs['class'] = Html::cls($attrs['class'] ?? '', $this->controlStateClass());

        return array_merge($attrs, $this->describeAttrs($forId), $this->stateAria());
    }

    /** 包装控件：label + control + help + feedback */
    protected function wrapField(string $control, string $forId): string
    {
        $label = '';
        if ($this->get('label') !== null) {
            $label = '<label for="'.$this->e($forId).'" class="form-label">'.$this->e($this->get('label'))
                .($this->get('required') ? ' <span class="text-danger">*</span>' : '')
                .'</label>';
        }
        $extra = '';
        $feedback = (array) ($this->get('feedback') ?? []);
        if (isset($feedback['valid'])) {
            $extra .= '<div class="valid-feedback" id="'.$this->e($forId).'-valid">'.$this->e($feedback['valid']).'</div>';
        }
        if (isset($feedback['invalid'])) {
            $extra .= '<div class="invalid-feedback" id="'.$this->e($forId).'-invalid">'.$this->e($feedback['invalid']).'</div>';
        }
        if ($this->get('help') !== null) {
            // help 是「说明文本」槽位（与 feedback 同为纯文本语义），必须转义：
            // 帮助文案常来自数据库/语言包，raw() 会形成存储型 XSS 汇点
            $extra .= '<div class="form-text" id="'.$this->e($forId).'-help">'.$this->e($this->get('help')).'</div>';
        }
        $inner = $label.$control.$extra;

        $wrapper = $this->get('wrapper');
        if ($wrapper === false || $wrapper === null) {
            return $inner;
        }

        return '<div class="'.$this->e($wrapper).'">'.$inner.'</div>';
    }
}

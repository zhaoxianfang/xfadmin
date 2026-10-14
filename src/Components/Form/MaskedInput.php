<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Components\Form\Concerns\FieldWrapper;
use zxf\XfAdmin\Support\Html;

/**
 * 输入掩码（Inputmask，form-pickers.html / form-other-plugins.html）
 *
 * XfAdmin::maskedInput([
 *     'name'  => 'phone',
 *     'label' => '手机号',
 *     'mask'  => '999-9999-9999',   // 或 alias: 'email' / 'currency' / 'datetime'
 *     'value' => '',
 *     'placeholder' => '___-____-____',
 * ])
 */
class MaskedInput extends Component
{
    use FieldWrapper;

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['inputmask'];
    }

    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        // 复用 fieldDefaults 补齐通用键（mb-3 外层包装 / required / feedback 等）；
        // 注意 + 运算符左侧优先：组件自身取值必须写在左边，fieldDefaults 只兜底
        return [
            'name' => '',
            'mask' => null,
            'alias' => null,
            'value' => '',
            'placeholder' => null,
            'help' => null,
            'state' => null,     // 'valid' | 'invalid' | null（显式校验着色，配合服务端回显）
            'col' => null,
        ] + $this->fieldDefaults();
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $config = array_filter([
            'mask' => $this->get('mask'),
            'alias' => $this->get('alias'),
        ], fn ($v) => $v !== null);

        $attrs = $this->initAttrs('inputmask', $config);
        $attrs['type'] = 'text';
        $attrs['class'] = Html::cls('form-control', $this->controlStateClass());
        $attrs['name'] = $this->get('name');
        $attrs['value'] = $this->get('value');
        if ($this->get('placeholder')) {
            $attrs['placeholder'] = $this->get('placeholder');
        }
        // id 解析与其它字段组件一致：配置 > attr() > 自动生成
        $id = $this->get('id') ?? $this->attributes['id'] ?? $this->uid('xf-mask');
        $attrs['id'] = $id;
        // 合并自定义属性（id 已被上面消费并写入 attrs，此处剔除避免重复覆盖）
        $custom = $this->attributes;
        unset($custom['id']);
        foreach ($custom as $k => $v) {
            $attrs[$k] = $k === 'class' ? Html::cls($attrs['class'] ?? '', $v) : $v;
        }
        // 无障碍接线：说明文本 / 校验反馈 / 校验失败语义（置于自定义属性之后，确保不被覆盖）
        $attrs = array_merge($attrs, $this->describeAttrs($id), $this->stateAria());

        return $this->wrapField('<input'.Html::attrs($attrs).'>', $id);
    }
}

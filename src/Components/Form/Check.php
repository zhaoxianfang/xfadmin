<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 复选框 / 单选框 / 开关（单个或一组）
 *
 * XfAdmin::check(['type' => 'switch', 'name' => 'enabled', 'label' => '启用', 'checked' => true])
 * XfAdmin::check([
 *     'type'    => 'radio',
 *     'name'    => 'gender',
 *     'inline'  => true,
 *     'value'   => 'f',
 *     'options' => ['m' => '男', 'f' => '女'],
 * ])
 */
class Check extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'type' => 'checkbox',  // checkbox | radio | switch
            'name' => null,
            'label' => null,
            'options' => [],          // 一组：value => label
            'value' => null,        // 选中值（组模式）；单个模式用 checked
            'checked' => false,
            'inline' => false,
            'reverse' => false,
            'disabled' => false,
            'required' => false,
            'wrapper' => 'mb-3',
            'help' => null,    // 说明文本（组模式渲染在组下方，单模式渲染在控件下方，转义）
            'state' => null,    // 'valid' | 'invalid' | null（显式校验着色）
        ];
    }

    /**
     * 控件校验状态 class（is-valid / is-invalid）
     *
     * @return string result
     */
    protected function stateClass(): string
    {
        return match ($this->get('state')) {
            'valid' => ' is-valid',
            'invalid' => ' is-invalid',
            default => '',
        };
    }

    /**
     * render One（protected实例方法）
     *
     * @param  string|int  $value  value
     * @param  string  $label  label
     * @param  bool  $checked  checked
     * @param  ?string  $id  id
     * @param  ?string  $helpId  help Id（用于 aria-describedby 无障碍接线）
     * @param  ?string  $name  字段名（组模式传补 [] 后的名字，缺省用配置 name）
     * @return string result
     */
    protected function renderOne(string|int $value, string $label, bool $checked, ?string $id = null, ?string $helpId = null, ?string $name = null): string
    {
        $type = $this->get('type');
        $isSwitch = $type === 'switch';
        $id ??= $this->uid('xf-check');
        $state = $this->stateClass();
        $describe = $helpId !== null ? ' aria-describedby="'.$this->escape($helpId).'"' : '';
        $invalid = $this->get('state') === 'invalid' ? ' aria-invalid="true"' : '';

        return '<div class="'.Html::cls('form-check', [
            'form-switch' => $isSwitch,
            'form-check-inline' => $this->get('inline'),
            'form-check-reverse' => $this->get('reverse'),
        ]).'">'
            .'<input class="form-check-input'.$state.'" type="'.($isSwitch ? 'checkbox' : $this->escape($type)).'"'
            .($isSwitch ? ' role="switch"' : '')
            .Html::attrs([
                'name' => $name ?? $this->get('name'),
                'id' => $id,
                'value' => $value,
                'checked' => $checked,
                'disabled' => (bool) $this->get('disabled'),
                'required' => (bool) $this->get('required'),
            ]).$describe.$invalid.'>'
            .'<label class="form-check-label" for="'.$this->escape($id).'">'.$this->escape($label).'</label>'
            .'</div>';
    }

    /**
     * 组模式字段名：复选框/开关组多个同名 input 必须以 name[] 提交，
     * 后端才能按数组接收多选值（单选 radio 组保持原名）
     */
    protected function groupFieldName(): string
    {
        $name = (string) $this->get('name');
        if ($name !== '' && in_array($this->get('type'), ['checkbox', 'switch'], true) && ! str_ends_with($name, '[]')) {
            return $name.'[]';
        }

        return $name;
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $raw = (array) $this->get('options', []);
        // 兼容两种候选项格式：
        //   - 关联数组 value => label（如 ['m'=>'男','f'=>'女']）
        //   - 对象数组 [{text,value}]（属性面板 JSON 编辑器与 diy_defaults 采用此格式）
        $options = [];
        foreach ($raw as $k => $v) {
            if (is_array($v) && (array_key_exists('value', $v) || array_key_exists('text', $v) || array_key_exists('label', $v))) {
                $val = (string) ($v['value'] ?? $k);
                $options[$val] = (string) ($v['text'] ?? $v['label'] ?? '');
            } else {
                // $v 为非预期结构（如嵌套数组）时安全降级为空字符串，
                // 避免 (string) 数组触发 "Array to string conversion" 告警
                $options[(string) $k] = is_scalar($v) ? (string) $v : '';
            }
        }

        // 说明文本（转义，防存储型 XSS）；组模式多个控件共享同一个 help 节点与 id
        $helpHtml = '';
        $helpId = null;
        if ($this->get('help') !== null) {
            $helpId = $this->uid('xf-check').'-help';
            $helpHtml = '<div class="form-text" id="'.$this->escape($helpId).'">'.$this->escape($this->get('help')).'</div>';
        }

        if ($options === []) {
            $html = $this->renderOne(
                (string) ($this->get('value') ?? '1'),
                (string) $this->get('label'),
                (bool) $this->get('checked'),
                $this->get('id'),
                $helpId
            );
            $html .= $helpHtml;
        } else {
            $selected = array_map('strval', (array) ($this->get('value') ?? []));
            $html = $this->get('label') !== null ? '<label class="form-label d-block">'.$this->escape($this->get('label')).'</label>' : '';
            // 组模式：复选框/开关自动补 [] 后缀，保证多选值可被后端按数组接收
            $groupName = $this->groupFieldName();
            foreach ($options as $value => $label) {
                $html .= $this->renderOne($value, (string) $label, in_array((string) $value, $selected, true), null, $helpId, $groupName);
            }
            $html .= $helpHtml;
        }
        $wrapper = $this->get('wrapper');

        return $wrapper ? '<div class="'.$this->escape($wrapper).'">'.$html.'</div>' : $html;
    }
}

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
    protected function defaults(): array
    {
        return [
            'type'     => 'checkbox',  // checkbox | radio | switch
            'name'     => null,
            'label'    => null,
            'options'  => [],          // 一组：value => label
            'value'    => null,        // 选中值（组模式）；单个模式用 checked
            'checked'  => false,
            'inline'   => false,
            'reverse'  => false,
            'disabled' => false,
            'required' => false,
            'wrapper'  => 'mb-3',
        ];
    }

    protected function renderOne(string|int $value, string $label, bool $checked, ?string $id = null): string
    {
        $type     = $this->get('type');
        $isSwitch = $type === 'switch';
        $id     ??= $this->uid('xf-check');

        return '<div class="' . Html::cls('form-check', [
            'form-switch'        => $isSwitch,
            'form-check-inline'  => $this->get('inline'),
            'form-check-reverse' => $this->get('reverse'),
        ]) . '">'
            . '<input class="form-check-input" type="' . ($isSwitch ? 'checkbox' : $this->e($type)) . '"'
            . ($isSwitch ? ' role="switch"' : '')
            . Html::attrs([
                'name'     => $this->get('name'),
                'id'       => $id,
                'value'    => $value,
                'checked'  => $checked,
                'disabled' => (bool) $this->get('disabled'),
                'required' => (bool) $this->get('required'),
            ]) . '>'
            . '<label class="form-check-label" for="' . $this->e($id) . '">' . $this->e($label) . '</label>'
            . '</div>';
    }

    protected function html(): string
    {
        $options = (array) $this->get('options', []);

        if ($options === []) {
            $html = $this->renderOne(
                (string) ($this->get('value') ?? '1'),
                (string) $this->get('label'),
                (bool) $this->get('checked'),
                $this->get('id')
            );
        } else {
            $selected = array_map('strval', (array) ($this->get('value') ?? []));
            $html     = $this->get('label') !== null ? '<label class="form-label d-block">' . $this->e($this->get('label')) . '</label>' : '';
            foreach ($options as $value => $label) {
                $html .= $this->renderOne($value, (string) $label, in_array((string) $value, $selected, true));
            }
        }

        $wrapper = $this->get('wrapper');

        return $wrapper ? '<div class="' . $this->e($wrapper) . '">' . $html . '</div>' : $html;
    }
}

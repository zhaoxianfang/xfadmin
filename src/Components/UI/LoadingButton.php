<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 加载/忙碌按钮（misc-loading-buttons）—— 点击后显示 spinner，避免重复提交
 *
 * XfAdmin::loadingButton([
 *     'text'    => '保存',
 *     'variant' => 'primary',
 *     'driver'  => 'spinner',   // spinner | ladda
 *     'type'    => 'submit',
 *     'size'    => '',          // lg | sm
 * ])
 */
class LoadingButton extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text' => '提交',
            'variant' => 'primary',
            'driver' => 'spinner',
            'type' => 'button',
            'size' => '',
            'name' => null,
            'value' => null,
            'icon' => '',
            // 自动恢复毫秒数：>0 时点击后 N 毫秒自动解除 loading（演示/无后端场景；真实提交请用 XFAdmin.get(el).stop()）
            'restore_ms' => 0,
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return $this->get('driver') === 'ladda' ? ['ladda'] : [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('lbtn');
        $driver = $this->get('driver') === 'ladda' ? 'ladda' : 'spinner';
        $size = $this->get('size') ? ' btn-'.$this->enum($this->get('size'), self::ENUM_SIZE, 'lg') : '';
        $icon = $this->get('icon') ? '<i class="'.$this->iconClass($this->get('icon')).' me-1"></i>' : '';

        $attrs = [
            'type' => $this->get('type'),
            'id' => $id,
            'class' => Html::cls('btn btn-'.$this->enum($this->get('variant'), array_merge(self::ENUM_VARIANT, self::ENUM_VARIANT_OUTLINE), 'primary').$size.' xf-lbtn', $this->get('class')),
            'data-xf' => 'loading-btn',
            'data-driver' => $driver,
        ];
        if ($this->get('name')) {
            $attrs['name'] = $this->get('name');
        }
        if ($this->get('value') !== null) {
            $attrs['value'] = $this->get('value');
        }
        // restoreMs 经 data-xf-config 透传给前端 widget（与 JS 的 config.restoreMs 对齐）
        $restoreMs = max(0, (int) $this->get('restore_ms'));
        if ($restoreMs > 0) {
            $attrs['data-xf-config'] = json_encode(['restoreMs' => $restoreMs], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        }
        // 自定义属性透传（data-*/aria-* 等），固定键 name/id/class/data-xf/data-driver/data-xf-config 除外
        foreach ($this->attributes as $k => $v) {
            if (in_array($k, ['name', 'id', 'class', 'data-xf', 'data-driver', 'data-xf-config', 'value', 'type'], true)) {
                continue;
            }
            $attrs[$k] = $k === 'class' ? Html::cls($attrs['class'] ?? '', $v) : $v;
        }
        $html = '<button'.$this->attrs($attrs).'>';
        $html .= '<span class="xf-lbtn-label">'.$icon.$this->e($this->get('text')).'</span>';
        $html .= '<span class="xf-lbtn-spinner spinner-border spinner-border-sm d-none" role="status"></span>';
        $html .= '</button>';

        return $html;
    }
}

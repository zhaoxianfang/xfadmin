<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Grid;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 栅格列
 *
 * XfAdmin::col(['width' => 6, 'content' => ...])
 * XfAdmin::col(['width' => ['md' => 6, 'xl' => 4], 'offset' => ['md' => 3], 'content' => ...])
 */
class Col extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'width' => null,  // int | 'auto' | [breakpoint => width]
            'offset' => null,
            'order' => null,
            'content' => '',
        ];
    }

    /**
     * 列宽值白名单：'auto' 或 1-12 的整数（返回 null 表示非法值，跳过该 class）。
     *
     * 注意：此处不直接用基类 gridCol()——Col 的宽度语义（col-auto、无断点 col-{n}、
     * 纯数字单输出 col-{n}）与 gridCol 的响应式组合语义不同，保持各组件现有契约。
     */
    private function colWidthValue(mixed $w): ?string
    {
        if (is_string($w) && strtolower($w) === 'auto') {
            return 'auto';
        }
        if (is_numeric($w)) {
            $n = (int) $w;

            return ($n >= 1 && $n <= 12) ? (string) $n : null;
        }

        return null;
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $classes = [];
        $width = $this->get('width');

        if ($width === null) {
            $classes[] = 'col';
        } elseif (is_array($width)) {
            foreach ($width as $bp => $w) {
                $v = $this->colWidthValue($w);
                if ($v === null) {
                    continue;
                }
                if (is_int($bp)) {
                    $classes[] = 'col-'.$v;
                } else {
                    // 断点键白名单化：非法键（可逃逸 class 属性）跳过
                    $bpCls = $this->enum($bp, self::ENUM_BREAKPOINT, '');
                    if ($bpCls !== '') {
                        $classes[] = 'col-'.$bpCls.'-'.$v;
                    }
                }
            }
        } else {
            $v = $this->colWidthValue($width);
            // 非法宽度回落裸 col（与原 null 行为一致），不再输出 col-{任意值}
            $classes[] = $v !== null ? 'col-'.$v : 'col';
        }
        // offset：值夹紧 0-11、断点键白名单
        foreach ((array) ($this->get('offset') ?? []) as $bp => $o) {
            if (! is_numeric($o)) {
                continue;
            }
            $n = max(0, min(11, (int) $o));
            if (is_int($bp)) {
                $classes[] = 'offset-'.$n;
            } else {
                $bpCls = $this->enum($bp, self::ENUM_BREAKPOINT, '');
                if ($bpCls !== '') {
                    $classes[] = 'offset-'.$bpCls.'-'.$n;
                }
            }
        }
        // order：值夹紧 0-5、断点键白名单
        foreach ((array) ($this->get('order') ?? []) as $bp => $o) {
            if (! is_numeric($o)) {
                continue;
            }
            $n = max(0, min(5, (int) $o));
            if (is_int($bp)) {
                $classes[] = 'order-'.$n;
            } else {
                $bpCls = $this->enum($bp, self::ENUM_BREAKPOINT, '');
                if ($bpCls !== '') {
                    $classes[] = 'order-'.$bpCls.'-'.$n;
                }
            }
        }

        return '<div'.$this->attrs(['class' => Html::cls($classes)]).'>'.$this->raw($this->get('content')).'</div>';
    }
}

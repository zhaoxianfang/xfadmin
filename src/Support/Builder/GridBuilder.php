<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support\Builder;

use Closure;
use zxf\XfAdmin\Support\Html;

/**
 * 栅格布局构建器（Bootstrap Grid 链式封装）
 *
 * 用链式 API 在 PHP 控制器里声明式地拼装响应式栅格布局，替代手写
 * `<div class="row"><div class="col-md-6">...</div></div>`。
 *
 * 链式调用示例：
 *
 *   echo XfAdmin::grid()
 *       ->newRow()                          // 开启一行 <div class="row">
 *           ->mdCol(6)                      // 中等屏占 6 列
 *               ->card('统计一')
 *                   ->cardBody('内容 A')
 *               ->endCard()
 *           ->endCol()
 *           ->mdCol(6)
 *               ->card('统计二')
 *                   ->cardBody('内容 B')
 *               ->endCard()
 *           ->endCol()
 *       ->endRow();                          // 闭合行
 *
 * 说明：
 *  - 每个方法返回 $this，可任意链式；
 *  - 传入闭包可做「作用域式」书写，闭包内调用的方法均作用于当前节点；
 *  - 支持 col()/smCol()/mdCol()/lgCol()/xlCol()/xxlCol() 全断点；
 *  - card()/cardBody()/cardHeader()/cardFooter() 封装卡片三段结构。
 */
class GridBuilder extends Builder
{
    /** Bootstrap 栅格断点白名单 */
    private const BREAKPOINTS = ['sm', 'md', 'lg', 'xl', 'xxl'];

    /**
     * 开启一行 <div class="row">
     *
     * @param  string|array  $class  额外 class
     * @param  Closure|null  $cb  可选闭包（接收本实例）
     * @return static
     */
    public function newRow(string|array $class = '', ?Closure $cb = null): static
    {
        $attrs = ['class' => Html::cls('row', $class)];
        $this->push('div', $attrs);
        if ($cb !== null) {
            $this->resolve($cb($this));
        }

        return $this;
    }

    /**
     * row 别名
     */
    public function row(string|array $class = '', ?Closure $cb = null): static
    {
        return $this->newRow($class, $cb);
    }

    /**
     * 闭合当前行
     *
     * @return static
     */
    public function endRow(): static
    {
        return $this->end();
    }

    /**
     * 开启一列（基础 col，可指定列宽）
     *
     * @param  int|string|array|null  $width  列宽（数字 1~12 / 'auto' / 空 / 数组断点映射）
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function col(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        $attrs = ['class' => $this->colClass('', $width)];
        $this->push('div', $attrs);
        if ($cb !== null) {
            $this->resolve($cb($this));
        }

        return $this;
    }

    /**
     * 开启一列并指定断点列宽（如 mdCol(6) → col-md-6）
     *
     * @param  int|string|array|null  $width  列宽
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function smCol(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        return $this->breakpointCol('sm', $width, $cb);
    }

    public function mdCol(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        return $this->breakpointCol('md', $width, $cb);
    }

    public function lgCol(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        return $this->breakpointCol('lg', $width, $cb);
    }

    public function xlCol(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        return $this->breakpointCol('xl', $width, $cb);
    }

    public function xxlCol(int|string|array|null $width = null, ?Closure $cb = null): static
    {
        return $this->breakpointCol('xxl', $width, $cb);
    }

    /**
     * 闭合当前列
     *
     * @return static
     */
    public function endCol(): static
    {
        return $this->end();
    }

    /**
     * 开启一张卡片 <div class="card">
     *
     * @param  string|array|null  $class  额外 class（传 null 用默认，传空串无额外）
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function card(string|array|null $class = null, ?Closure $cb = null): static
    {
        $base = 'card';
        if ($class !== null && $class !== '') {
            $base = Html::cls('card', $class);
        }
        $this->push('div', ['class' => $base]);
        if ($cb !== null) {
            $this->resolve($cb($this));
        }

        return $this;
    }

    /**
     * 卡片头部 <div class="card-header">
     *
     * 纯字符串标题自动用 <h5 class="card-title mb-0"> 包裹（INSPINIA 后台卡片标准样式）；
     * 传组件 / 闭包 / 已含标签的 HTML 字符串时原样输出。
     *
     * @param  mixed  $title  标题（字符串转义 / 组件 / 闭包）
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function cardHeader(mixed $title = null, ?Closure $cb = null): static
    {
        $this->push('div', ['class' => 'card-header']);
        if ($title !== null) {
            if (is_string($title) && ! str_contains($title, '<')) {
                // 纯文本标题 → h5.card-title 包裹（转义）
                $this->push('h5', ['class' => 'card-title mb-0'])->text($title)->end();
            } else {
                $this->add($this->resolve($title));
            }
        }
        if ($cb !== null) {
            $this->resolve($cb($this));
        }
        $this->end();

        return $this;
    }

    /**
     * 卡片主体 <div class="card-body">
     *
     * @param  mixed  $content  内容（字符串 / 组件 / 闭包 / 数组）
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function cardBody(mixed $content = null, ?Closure $cb = null): static
    {
        $this->push('div', ['class' => 'card-body']);
        if ($content !== null) {
            $this->add($this->resolve($content));
        }
        if ($cb !== null) {
            $this->resolve($cb($this));
        }
        $this->end();

        return $this;
    }

    /**
     * 卡片底部 <div class="card-footer">
     *
     * @param  mixed  $content  内容
     * @param  Closure|null  $cb  可选闭包
     * @return static
     */
    public function cardFooter(mixed $content = null, ?Closure $cb = null): static
    {
        $this->push('div', ['class' => 'card-footer']);
        if ($content !== null) {
            $this->add($this->resolve($content));
        }
        if ($cb !== null) {
            $this->resolve($cb($this));
        }
        $this->end();

        return $this;
    }

    /**
     * 闭合当前卡片
     *
     * @return static
     */
    public function endCard(): static
    {
        return $this->end();
    }

    /**
     * 卡片标题（card-header 内纯文本标题的便捷封装）
     *
     * @param  string  $title  标题文本
     * @return static
     */
    public function cardTitle(string $title): static
    {
        $this->push('div', ['class' => 'card-header'])->text($title)->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 内部辅助
    // ------------------------------------------------------------------

    /**
     * 按断点开列
     *
     * @param  string  $bp  断点（sm/md/lg/xl/xxl）
     * @param  int|string|array|null  $width  列宽
     * @param  Closure|null  $cb  闭包
     * @return static
     */
    private function breakpointCol(string $bp, int|string|array|null $width = null, ?Closure $cb = null): static
    {
        $attrs = ['class' => $this->colClass($bp, $width)];
        $this->push('div', $attrs);
        if ($cb !== null) {
            $this->resolve($cb($this));
        }

        return $this;
    }

    /**
     * 生成列 class：'col'（无断点）/ 'col-md-6'（指定断点）+ 自动 col-12 兜底
     *
     * @param  string  $bp  断点（空则纯 col）
     * @param  int|string|array|null  $width  列宽
     */
    private function colClass(string $bp, int|string|array|null $width): string
    {
        // 数组：显式多断点映射 ['md' => 6, 'xl' => 4]
        if (is_array($width)) {
            $cls = 'col-12';
            foreach ($width as $key => $val) {
                $key = (string) $key;
                if (! in_array($key, self::BREAKPOINTS, true)) {
                    continue; // 非法断点跳过
                }
                $columnCount = (int) $val;
                if ($columnCount < 1 || $columnCount > 12) {
                    continue;
                }
                $cls .= ' col-'.$key.'-'.$columnCount;
            }

            return $cls;
        }

        // 无宽度：纯 col 或 col-{bp}
        if ($width === null || $width === '' || $width === 'auto') {
            return $bp === '' ? 'col' : 'col-'.$bp;
        }

        // 数字宽度
        if (is_numeric($width)) {
            $columnCount = (int) $width;
            if ($columnCount < 1 || $columnCount > 12) {
                $columnCount = 12;
            }
            if ($bp === '') {
                return 'col-'.$columnCount;
            }

            return 'col-12 col-'.$bp.'-'.$columnCount;
        }

        // 其他字符串（如 '6' 之外的 'auto'/'content' 等）按纯 col 处理
        return $bp === '' ? 'col' : 'col-'.$bp;
    }
}

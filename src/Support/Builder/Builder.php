<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support\Builder;

use Closure;
use Stringable;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * Builder 基类：HTML 链式构建器（底层）
 *
 * 提供「压栈 → 追加 → 弹栈」的流式 HTML 拼装能力，用于在 PHP 控制器中
 * 以链式调用替代手写 `<div class="...">...</div>`。派生类（GridBuilder /
 * AdminBuilder）在其上扩展业务级方法（栅格 / 卡片 / 后台字段等）。
 *
 * 核心机制：
 *  - push(tag, attrs)：开一个标签并压入栈；
 *  - end()：弹出栈顶并输出闭合标签；
 *  - add($content)：向当前节点追加内容（支持字符串 / Stringable / 闭包 / 数组）；
 *  - text($content)：追加转义后的纯文本；
 *  - when($cond, $cb)：条件分支，减少 if 包裹；
 *  - resolve($cb)：执行闭包并把返回值写入当前节点（闭包返回 $this 时安全跳过）。
 *
 * 安全：
 *  - 标签名白名单（Node::TAGS）；
 *  - 属性经 Html::attrs() 转义与白名单校验；
 *  - 深度上限（Node::MAX_DEPTH）防无限嵌套。
 *
 * 用法：
 *   $b = (new Builder())->push('div', ['class' => 'box'])
 *       ->add('<strong>标题</strong>')
 *       ->push('ul')
 *       ->add('<li>项目一</li>')
 *       ->end()
 *       ->end();
 *   echo $b; // <div class="box"><strong>标题</strong><ul><li>项目一</li></ul></div>
 */
class Builder implements Stringable
{
    /** @var Node[] 已打开的标签栈 */
    protected array $stack = [];

    /** @var string[] 最终输出的内容缓冲 */
    protected array $buffer = [];

    /** 当前嵌套深度 */
    protected int $depth = 0;

    /**
     * 开一个标签并压栈
     *
     * @param  string  $tag  标签名（白名单内）
     * @param  array  $attrs  属性数组
     * @return static
     */
    public function push(string $tag, array $attrs = []): static
    {
        if ($this->depth >= Node::MAX_DEPTH) {
            return $this; // 深度上限，防无限嵌套（静默丢弃）
        }
        $node = new Node($tag, $attrs);
        $this->stack[] = $node;
        $this->depth++;
        if ($node->isVoid()) {
            // 自闭合标签直接输出，不入栈
            array_pop($this->stack);
            $this->depth--;
            $this->buffer[] = '<'.$node->tag().Html::attrs($node->attrs()).'>';

            return $this;
        }
        $this->buffer[] = '<'.$node->tag().Html::attrs($node->attrs()).'>';

        return $this;
    }

    /**
     * 闭合栈顶标签
     *
     * @return static
     */
    public function end(): static
    {
        if ($this->stack === []) {
            return $this; // 无标签可闭合时静默忽略
        }
        $node = array_pop($this->stack);
        $this->depth--;
        $this->buffer[] = '</'.$node->tag().'>';

        return $this;
    }

    /**
     * 一次性闭合所有未闭合标签（保证输出结构完整）
     *
     * @return static
     */
    public function endAll(): static
    {
        while ($this->stack !== []) {
            $this->end();
        }

        return $this;
    }

    /**
     * 向当前节点追加内容（不转义，用于已处理的 HTML / 组件输出）
     *
     * @param  mixed  $content  字符串 / Stringable / 闭包 / 数组
     * @return static
     */
    public function add(mixed $content): static
    {
        $this->buffer[] = $this->resolve($content);

        return $this;
    }

    /**
     * 追加转义后的纯文本
     *
     * @param  mixed  $text  纯文本
     * @return static
     */
    public function text(mixed $text): static
    {
        $this->buffer[] = Html::escape($text);

        return $this;
    }

    /**
     * 原始追加（跳过 resolve，直接拼字符串；等同于 add 但不展开闭包/数组）
     *
     * @param  string  $html  已处理 HTML
     * @return static
     */
    public function raw(string $html): static
    {
        $this->buffer[] = $html;

        return $this;
    }

    /**
     * 条件分支：$cond 为真时执行闭包，返回本实例以继续链式
     *
     * @param  bool  $cond  条件
     * @param  Closure  $cb  回调（接收本实例）
     * @return static
     */
    public function when(bool $cond, Closure $cb): static
    {
        if ($cond) {
            $cb($this);
        }

        return $this;
    }

    /**
     * 条件分支（带 else）：$cond 为真执行 $then，否则执行 $else
     *
     * @param  bool  $cond  条件
     * @param  Closure  $then  真分支回调
     * @param  Closure|null  $else  假分支回调
     * @return static
     */
    public function whenElse(bool $cond, Closure $then, ?Closure $else = null): static
    {
        if ($cond) {
            $then($this);
        } elseif ($else !== null) {
            $else($this);
        }

        return $this;
    }

    /**
     * resolve 的公开入口（供工厂方法渲染闭包返回值时复用，避免重复实现）
     *
     * @param  mixed  $content  待解析内容
     */
    public function resolvePublic(mixed $content): string
    {
        return $this->resolve($content);
    }

    /**
     * 解析内容：字符串原样、Stringable 渲染、闭包惰性求值、数组递归拼接
     *
     * 关键防护：闭包若返回 $this（构建器自身），会导致整棵树重复渲染，
     * 此处显式拦截并返回空串。
     *
     * @param  mixed  $content  待解析内容
     */
    protected function resolve(mixed $content): string
    {
        if ($content === null) {
            return '';
        }
        if ($content === $this) {
            return ''; // 防闭包误返回构建器自身导致重复渲染
        }
        if ($content instanceof Closure) {
            return $this->resolve($content($this));
        }
        if ($content instanceof Stringable || $content instanceof Component) {
            return (string) $content;
        }
        if (is_array($content)) {
            $out = '';
            foreach ($content as $item) {
                $out .= $this->resolve($item);
            }

            return $out;
        }
        if (is_scalar($content)) {
            return (string) $content;
        }

        return '';
    }

    /**
     * 渲染完整 HTML（自动闭合所有未闭合标签）
     */
    public function render(): string
    {
        $out = implode('', $this->buffer);
        // 补上剩余未闭合标签，保证结构完整
        while ($this->stack !== []) {
            $node = array_pop($this->stack);
            $out .= '</'.$node->tag().'>';
        }

        return $out;
    }

    /**
     * to String（public实例方法）
     *
     * @return string result
     */
    public function __toString(): string
    {
        return $this->render();
    }
}

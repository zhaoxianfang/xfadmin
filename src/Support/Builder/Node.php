<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support\Builder;

use Stringable;

/**
 * HTML 节点（Builder 体系的底层构建单元）
 *
 * 代表一次「开标签」或「自闭合标签」操作。与 Builder 基类配合，用
 * 「压栈 → 追加内容 → 弹栈闭合」的方式串接出任意嵌套的 HTML 结构，
 * 从根本上替代项目控制器里手写 `<div class="col-md-6">...</div>` 的做法。
 *
 * 安全性设计：
 *  - 标签名白名单（$TAGS），杜绝调用方拼入 `<script>` / `<style>` 之外的任意标签；
 *  - 深度上限（MAX_DEPTH = 256）防呆，避免误用递归闭包导致无限嵌套；
 *  - 自闭合标签（VOID_TAGS）不产生闭合标签，避免生成非法 HTML。
 *
 * @internal 由 Builder 基类内部维护，外部一般无需直接实例化。
 */
final class Node
{
    /** 允许的标签白名单：覆盖结构 / 布局 / 表单 / 表格 / 语义化标签 */
    public const TAGS = [
        'div', 'span', 'p', 'a', 'button', 'ul', 'ol', 'li', 'nav', 'header', 'footer',
        'main', 'section', 'article', 'aside', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'form', 'input', 'select', 'textarea', 'label', 'fieldset', 'legend', 'option', 'optgroup',
        'strong', 'em', 'b', 'i', 'u', 's', 'small', 'mark', 'code', 'pre', 'kbd', 'samp',
        'blockquote', 'cite', 'abbr', 'sub', 'sup', 'dl', 'dt', 'dd', 'figure', 'figcaption',
        'details', 'summary', 'dialog', 'img', 'br', 'hr', 'iframe', 'audio', 'video', 'source',
        'progress', 'template', 'slot', 'picture', 'wbr',
    ];

    /** 自闭合（空）标签：不产生 </tag> 闭合 */
    public const VOID_TAGS = [
        'img', 'br', 'hr', 'input', 'source', 'wbr', 'col', 'meta', 'link', 'area', 'base', 'embed', 'track',
    ];

    /** 最大嵌套深度：防止误用递归闭包造成无限递归 / 栈溢出 */
    public const MAX_DEPTH = 256;

    private string $tag;

    private array $attrs;

    private bool $void;

    /**
     * construct（public实例方法）
     *
     * @param  string  $tag  标签名（必须在白名单内，否则回退为 div）
     * @param  array  $attrs  属性数组
     */
    public function __construct(string $tag, array $attrs = [])
    {
        $this->tag = in_array($tag, self::TAGS, true) ? $tag : 'div';
        $this->attrs = $attrs;
        $this->void = in_array($this->tag, self::VOID_TAGS, true);
    }

    /** 标签名 */
    public function tag(): string
    {
        return $this->tag;
    }

    /** 是否为自闭合标签 */
    public function isVoid(): bool
    {
        return $this->void;
    }

    /** 属性数组 */
    public function attrs(): array
    {
        return $this->attrs;
    }
}

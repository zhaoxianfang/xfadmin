<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 排版展示组件（ui-typography.html）
 *
 * 封装 Bootstrap 排版示例区块，支持自定义扩展区块。常用于后台「排版 / 组件库」展示页。
 *
 * XfAdmin::typography()                                                  // 渲染全部默认区块（display / headings / 文本工具类 / 引用 / 列表 / 缩写）
 * XfAdmin::typography(['blocks' => ['display', 'headings']])             // 仅渲染指定区块
 * XfAdmin::typography([
 *     'blocks' => [
 *         ['title' => '自定义区块', 'body' => '<p>任意 HTML</p>'],        // 自定义区块
 *     ],
 * ])
 */
class Typography extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'blocks' => [],          // 区块列表：字符串（built-in 键）或 ['title'=>..,'body'=>..]
            'grid'   => true,        // 是否用 row/col 网格包裹
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $blocks = (array) $this->get('blocks', []);
        $grid   = (bool) $this->get('grid', true);

        if (empty($blocks)) {
            $blocks = ['display', 'headings', 'text', 'blockquote', 'lists', 'abbr'];
        }
        $out = '';
        foreach ($blocks as $block) {
            if (is_string($block)) {
                $out .= $this->builtin($block);
            } elseif (is_array($block)) {
                $title = (string) ($block['title'] ?? '');
                $body  = (string) ($block['body'] ?? '');
                $out  .= $this->card($title, $body);
            }
        }
        if (!$grid) {
            return $out;
        }
        // 将卡片按列重排：每个 card 包一层 col
        return $this->wrapGrid($out);
    }

    /**
     * wrap Grid（private实例方法）
     *
     * @param string $html html
     *
     * @return string result
     */
    private function wrapGrid(string $html): string
    {
        // 将连续的 <div class="card">...</div> 切分并以 col 包裹
        $cards = [];
        $offset = 0;
        while (preg_match('/<div class="card">(.*?)<\/div>\s*<!-- end card-->/s', $html, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $cards[] = $m[0][0];
            $offset  = $m[0][1] + strlen($m[0][0]);
        }
        if (empty($cards)) {
            return '<div class="container-xxl"><div class="row">' . $html . '</div></div>';
        }
        $cols = '';
        $wideFirst = true;
        foreach ($cards as $i => $c) {
            $colClass = ($i === 0 && $wideFirst) ? 'col-12' : 'col-lg-4';
            $cols .= '<div class="' . $colClass . '">' . $c . '</div>';
        }
        return '<div class="container-xxl"><div class="row">' . $cols . '</div></div>';
    }

    /**
     * card（private实例方法）
     *
     * @param string $title title
     * @param string $body body
     *
     * @return string result
     */
    private function card(string $title, string $body): string
    {
        return '<div class="card">'
            . '<div class="card-header"><h4 class="card-title">' . $this->e($title) . '</h4></div>'
            . '<div class="card-body">' . $body . '</div>'
            . '</div><!-- end card-->';
    }

    /**
     * builtin（private实例方法）
     *
     * @param string $key key
     *
     * @return string result
     */
    private function builtin(string $key): string
    {
        return match ($key) {
            'display' => $this->card('Display Headings', implode("\n", array_map(
                fn($n) => '<h1 class="display-' . $n . '">Display ' . $n . '</h1>',
                range(1, 6)
            ))),
            'headings' => $this->card('Headings', implode("\n", array_map(
                function ($n) {
                    $h = '<h' . $n . '>Heading ' . $n . ' <small>Sub Heading</small></h' . $n . '>';
                    return $h;
                },
                range(1, 6)
            ))),
            'text' => $this->card('Text Utilities', '
                <p class="fw-bold">Bold text</p>
                <p class="fw-semibold">Semibold text</p>
                <p class="fw-normal">Normal weight</p>
                <p class="fw-light">Light weight</p>
                <p class="fst-italic">Italic text</p>
                <p class="text-decoration-underline">Underlined text</p>
                <p class="text-decoration-line-through">Line-through text</p>
                <p class="text-muted">Muted text</p>
                <p class="text-primary">Primary text</p>
                <p class="text-success">Success text</p>
                <p class="text-danger">Danger text</p>
            '),
            'blockquote' => $this->card('Blockquote', '
                <figure class="text-center">
                    <blockquote class="blockquote">
                        <p>"Design is not just what it looks like and feels like. Design is how it works."</p>
                    </blockquote>
                    <figcaption class="blockquote-footer">Steve Jobs</figcaption>
                </figure>
            '),
            'lists' => $this->card('Lists', '
                <ul class="list-unstyled">
                    <li>Lorem ipsum dolor sit amet</li>
                    <li>Consectetur adipiscing elit</li>
                    <li>Integer molestie lorem at massa</li>
                </ul>
                <ol class="mt-3">
                    <li>First item</li>
                    <li>Second item</li>
                    <li>Third item</li>
                </ol>
            '),
            'abbr' => $this->card('Inline Text Elements', '
                <p><abbr title="attribute">attr</abbr> abbreviation example.</p>
                <p>You can use the mark tag to <mark>highlight</mark> text.</p>
                <p><code>&lt;code&gt;</code> inline code sample.</p>
                <p><kbd>Ctrl</kbd> + <kbd>K</kbd> keyboard shortcut.</p>
            '),
            default => '',
        };
    }
}

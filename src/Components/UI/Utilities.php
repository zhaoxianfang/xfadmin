<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 工具类展示组件（ui-utilities.html）
 *
 * 封装 Bootstrap 工具类（间距 / 弹性 / 文本 / 边框 / 阴影 / 圆角 / 尺寸 / 显示）示例区块。
 * 用于后台「样式 / 组件库」展示页，支持自定义扩展区块。
 *
 * XfAdmin::utilities()                                       // 渲染全部默认区块
 * XfAdmin::utilities(['blocks' => ['spacing', 'flex']])      // 仅指定区块
 * XfAdmin::utilities([
 *     'blocks' => [['title' => '自定义', 'body' => '<div class="p-3 bg-light">demo</div>']],
 * ])
 */
class Utilities extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'blocks' => [],
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
        if (empty($blocks)) {
            $blocks = ['spacing', 'flex', 'text', 'border', 'shadow', 'rounded', 'sizing', 'display'];
        }
        $cards = '';
        foreach ($blocks as $block) {
            if (is_string($block)) {
                $cards .= $this->builtin($block);
            } elseif (is_array($block)) {
                $title = (string) ($block['title'] ?? '');
                $body  = (string) ($block['body'] ?? '');
                $cards .= $this->card($title, $body);
            }
        }
        $cols = '';
        foreach (explode('<!--SPLIT-->', $cards) as $c) {
            if (trim($c) === '') {
                continue;
            }
            $cols .= '<div class="col-lg-6">' . $c . '</div>';
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
            . '</div><!-- end card--><!--SPLIT-->';
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
            'spacing' => $this->card('Spacing', '
                <div class="d-flex flex-wrap gap-2">
                    <span class="p-3 bg-light border">p-3</span>
                    <span class="px-4 py-2 bg-light border">px-4 py-2</span>
                    <span class="m-3 bg-light border">m-3</span>
                    <span class="mt-4 bg-light border">mt-4</span>
                    <span class="gap-3 d-inline-flex bg-light border">gap-3</span>
                </div>
            '),
            'flex' => $this->card('Flex', '
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="bg-light border p-2">start</span><span class="bg-light border p-2">end</span>
                </div>
                <div class="d-flex flex-column gap-1">
                    <span class="bg-light border p-2">col 1</span><span class="bg-light border p-2">col 2</span>
                </div>
            '),
            'text' => $this->card('Text', '
                <p class="text-start">Left aligned</p>
                <p class="text-center">Center aligned</p>
                <p class="text-end">Right aligned</p>
                <p class="text-lowercase">LOWERCASE</p>
                <p class="text-uppercase">uppercase</p>
                <p class="text-truncate" style="max-width:160px">Truncated long text example that overflows the container width.</p>
            '),
            'border' => $this->card('Border', '
                <div class="d-flex gap-2">
                    <span class="border p-2">border</span>
                    <span class="border border-primary p-2">border-primary</span>
                    <span class="border border-2 border-danger p-2">border-2 danger</span>
                    <span class="border-top border-success p-2">border-top</span>
                </div>
            '),
            'shadow' => $this->card('Shadow', '
                <div class="d-flex gap-3">
                    <span class="shadow-none p-3 bg-white border">shadow-none</span>
                    <span class="shadow-sm p-3 bg-white border">shadow-sm</span>
                    <span class="shadow p-3 bg-white border">shadow</span>
                    <span class="shadow-lg p-3 bg-white border">shadow-lg</span>
                </div>
            '),
            'rounded' => $this->card('Rounded', '
                <div class="d-flex gap-2">
                    <span class="rounded bg-light border p-3">rounded</span>
                    <span class="rounded-pill bg-light border p-3">pill</span>
                    <span class="rounded-circle bg-light border p-3">circle</span>
                    <span class="rounded-3 bg-light border p-3">rounded-3</span>
                </div>
            '),
            'sizing' => $this->card('Sizing', '
                <div class="w-25 p-2 bg-light border mb-2">w-25</div>
                <div class="w-50 p-2 bg-light border mb-2">w-50</div>
                <div class="w-75 p-2 bg-light border mb-2">w-75</div>
                <div class="w-100 p-2 bg-light border">w-100</div>
            '),
            'display' => $this->card('Display', '
                <div class="d-none bg-light border p-2 mb-1">d-none（不可见）</div>
                <div class="d-inline bg-light border p-2 mb-1">d-inline</div>
                <div class="d-block bg-light border p-2 mb-1">d-block</div>
                <div class="d-flex bg-light border p-2">d-flex</div>
            '),
            default => '',
        };
    }
}

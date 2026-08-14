<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;

/**
 * 代码块（转义输出用户代码，防止 XSS；可选复制按钮与标题栏）
 *
 * XfAdmin::codeBlock([
 *     'code'     => '<?php echo "hi";',  // 任意代码，自动 HTML 转义
 *     'language' => 'php',               // 语言标签（仅展示用）
 *     'title'    => '示例',              // 标题栏（可空）
 *     'copyable' => true,                // 显示复制按钮
 *     'theme'    => 'dark',              // dark | light
 * ])
 */
class CodeBlock extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'code'     => '',
            'language' => '',
            'title'    => '',
            'copyable' => true,
            'theme'    => 'dark',
            'id'       => null,
            'class'    => '',
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-code');

        $copyBtn = $this->get('copyable')
            ? '<button type="button" class="xf-code-copy btn btn-sm btn-light" data-xf="codeCopy">复制</button>'
            : '';

        if ($this->get('title') !== '') {
            $header = '<div class="xf-code-header d-flex align-items-center justify-content-between">'
                . '<span class="xf-code-lang">' . $this->e($this->get('language') !== '' ? $this->get('language') : $this->get('title')) . '</span>'
                . $copyBtn
                . '</div>';
        } elseif ($copyBtn !== '') {
            $header = '<div class="xf-code-header d-flex justify-content-end">' . $copyBtn . '</div>';
        } else {
            $header = '';
        }
        return '<div' . $this->attrs([
            'id'    => $id,
            'class' => 'xf-code-block xf-code-' . $this->e($this->get('theme'))
                . ($this->get('class') !== '' ? ' ' . $this->get('class') : ''),
            'data-xf' => 'codeblock',
        ]) . '>'
            . $header
            . '<pre class="xf-code-pre"><code'
            . ($this->get('language') !== '' ? ' class="language-' . $this->e($this->get('language')) . '"' : '')
            . '>' . $this->e((string) $this->get('code')) . '</code></pre>'
            . '</div>';
    }
}

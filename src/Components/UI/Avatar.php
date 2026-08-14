<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\UI;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 头像组件（图片 / 文字缩写 / 图标 / 分组堆叠）
 * ------------------------------------------------------------------
 * 设计契约（严格对齐后台模板 INSPINIA v4.1.0 的 .avatar 规范）：
 *   - 头像恒以 <span class="avatar avatar-{size}"> 包裹，尺寸类挂在「包裹元素」而非 <img> 上；
 *   - 图片头像：<span class="avatar avatar-sm"><img class="img-fluid rounded-circle"></span>
 *   - 文字/图标头像：<span class="avatar avatar-sm"><span class="avatar-title {bg} rounded-circle fw-bold">XX</span></span>
 *   - 分组堆叠：<div class="avatar-group">…<span class="avatar avatar-sm">…</span>…</div>
 *
 * 调用示例：
 *   XfAdmin::avatar(['src' => '/a.jpg', 'size' => 'md', 'rounded' => 'circle'])
 *   XfAdmin::avatar(['text' => 'ZS', 'variant' => 'primary', 'size' => 'lg'])
 *   XfAdmin::avatar(['group' => [['src' => '/a.jpg'], ['text' => '+5', 'variant' => 'info']]])
 */
class Avatar extends Component
{
    /**
     * 选项默认值（数据契约）
     *   src     图片地址（外链/data URI 原样；相对路径走 XfAdmin::img 解析）
     *   text    文字缩写（如「张三」取首字、「ZS」原样），与 src 互斥，优先 src
     *   icon    Tabler 图标类（如 'ti ti-user'），与 text 互斥
     *   alt     图片 alt 文本（留空输出空 alt，符合装饰图可访问性）
     *   size    尺寸：xxs(1rem) | xs(1.5) | sm(2) | md(2.25) | lg(2.75) | xl(3) | xxl(5)
     *   rounded 圆角：circle(圆形) | 0/1/2/3(Bootstrap 圆角档)
     *   variant 首字母/图标底色主题：primary/success/info/warning/danger/…
     *   soft    true=浅底深字(bg-*-subtle text-*)；false=实底白字(bg-* text-white)
     *   group   头像组数组，非空时渲染为堆叠头像
     */
    protected function defaults(): array
    {
        return [
            'src'     => null,
            'text'    => null,
            'icon'    => null,
            'alt'     => '',
            'size'    => 'md',       // xs | sm | md | lg | xl | xxl
            'rounded' => 'circle',   // circle | 0 | 1 | 2 | 3
            'variant' => 'primary',
            'soft'    => true,
            'group'   => [],
        ];
    }

    /**
     * 渲染单个头像（图片或文字/图标），统一包 .avatar 结构。
     * @param array  $opts   单项配置
     * @param string $extra  仅分组/根级需要附加到 .avatar 的属性串（本组件统一用外层包裹承载根属性，故通常为空）
     */
    protected function one(array $opts): string
    {
        // 尺寸类恒挂包裹元素 .avatar（INSPINIA 规范：.avatar-xxs…xxl 设 width/height，内部 img/title 占满 100%）
        $size    = 'avatar-' . $this->enum($opts['size'] ?? 'md', ['xxs', 'xs', 'sm', 'md', 'lg', 'xl', 'xxl'], 'md');
        $rounded = $this->enum($opts['rounded'] ?? 'circle', ['circle', '0', '1', '2', '3', '4', '5', 'pill'], 'circle');
        $rounded = $rounded === 'circle' ? 'rounded-circle' : 'rounded-' . $rounded;

        // —— 图片头像 ——
        if (! empty($opts['src'])) {
            return '<span class="' . Html::cls('avatar', $size) . '">'
                . '<img src="' . $this->e($this->img($opts['src'])) . '"'
                . ' alt="' . $this->e($opts['alt'] ?? '') . '"'
                . ' class="' . Html::cls('img-fluid', $rounded) . '">'
                . '</span>';
        }
        // —— 文字缩写 / 图标头像 ——
        $variant = $this->enum($opts['variant'] ?? 'primary', self::ENUM_VARIANT, 'primary');
        // soft：浅底深字（与后台模板 card 上的状态徽标观感一致）；否则实底白字
        $bg      = ($opts['soft'] ?? true)
            ? "bg-{$variant}-subtle text-{$variant}"
            : "bg-{$variant} text-white";
        $inner   = ! empty($opts['icon'])
            ? '<i class="' . $this->e($opts['icon']) . '"></i>'
            : $this->e($opts['text'] ?? '');

        return '<span class="' . Html::cls('avatar', $size) . '">'
            . '<span class="' . Html::cls('avatar-title', $bg, $rounded, 'fw-bold') . '">' . $inner . '</span>'
            . '</span>';
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $group = (array) $this->get('group', []);

        // 分组堆叠：外层 .avatar-group（xfadmin.css 负责负边距+描边+hover 上移，对齐 INSPINIA）
        if ($group !== []) {
            $html = '<div' . $this->attrs(['class' => 'avatar-group']) . '>';
            foreach ($group as $item) {
                // 子项继承根 size，未单独指定时使用
                $item = (array) $item + ['size' => $this->get('size')];
                $html .= $this->one($item);
            }
            return $html . '</div>';
        }
        // 单头像：根级 class/id/style/data 挂到外层内联块容器，.avatar 在内部保持纯净结构
        return '<div' . $this->attrs(['class' => 'd-inline-block']) . '>' . $this->one($this->options) . '</div>';
    }
}

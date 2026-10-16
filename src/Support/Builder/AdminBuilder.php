<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support\Builder;

use Closure;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;

/**
 * 后台专用构建器（在 GridBuilder 基础上扩展后台业务级构建方法）
 *
 * 覆盖后台常见的展示与操作元素，全部为链式、惰性、可嵌套：
 *  - 指标卡 statCards / 键值对 kv / infoCards
 *  - 告警 alert / cardAlert / notice / errorText / emptyText
 *  - 徽章 badge / levelBadge / codeBadge / pushBadge / pushSpan
 *  - 表单 field / fieldGrid / formActions / permissionForm
 *  - 详情 detailButton / summaryBar / buttonGroup / quickLinks / toolbar
 *  - 结构 panel / panelRow / miniTable / kvTable / listGroup / dialogListItem / treeWidget
 *  - 内容 pre / jsonBlock / tablePane / dialogContent / dialogEnd / accordion
 *
 * 所有方法均返回 $this，可与栅格方法（newRow/col/mdCol/card/cardBody）无缝链式混用。
 */
class AdminBuilder extends GridBuilder
{
    // ------------------------------------------------------------------
    // 指标 / 统计卡
    // ------------------------------------------------------------------

    /**
     * 一行多张指标卡（statCards）
     *
     * @param  array  $cards  卡片数组，每项含 title/value/icon/variant/delta 等
     * @return static
     *
     * 示例：
     *   ->statCards([
     *       ['title' => '今日订单', 'value' => 128, 'icon' => 'ti ti-shopping-cart', 'variant' => 'primary'],
     *       ['title' => '营收', 'value' => '¥12,800', 'icon' => 'ti ti-currency-yen', 'variant' => 'success'],
     *   ])
     */
    public function statCards(array $cards): static
    {
        $this->push('div', ['class' => 'row']);
        foreach ($cards as $card) {
            $variant = $this->enumVariant($card['variant'] ?? 'primary');
            $icon = $this->iconClass($card['icon'] ?? 'ti ti-chart-bar');
            $title = (string) ($card['title'] ?? '');
            $value = (string) ($card['value'] ?? '');
            $delta = $card['delta'] ?? null;

            // 单卡：col > card > (card-body > row > ...)
            $this->push('div', ['class' => 'col-12 col-md-6 col-xl-3']);
            $this->push('div', ['class' => 'card card-sm']);
            $this->push('div', ['class' => 'card-body']);
            $this->push('div', ['class' => 'row align-items-center']);

            // 图标列
            $this->push('div', ['class' => 'col-auto']);
            $this->push('span', ['class' => 'avatar avatar-md', 'style' => 'background:'.Html::escape($variant)]);
            $this->push('i', ['class' => Html::escape($icon)]);
            $this->end(); // i
            $this->end(); // span.avatar
            $this->end(); // col-auto

            // 数值列
            $this->push('div', ['class' => 'col']);
            $this->push('div', ['class' => 'font-weight-medium']);
            $this->text($value);
            $this->end(); // .font-weight-medium
            $this->push('div', ['class' => 'text-secondary']);
            $this->text($title);
            $this->end(); // .text-secondary
            $this->end(); // col

            // 趋势列（可选）
            if ($delta !== null) {
                $dir = ($delta['direction'] ?? 'up') === 'down' ? 'down' : 'up';
                $this->push('div', ['class' => 'col-auto']);
                $this->push('span', ['class' => 'badge '.($dir === 'up' ? 'bg-green-lt' : 'bg-red-lt')]);
                $this->text(($dir === 'up' ? '+' : '-').(string) ($delta['value'] ?? ''));
                $this->end(); // span.badge
                $this->end(); // col-auto
            }

            $this->end(); // row
            $this->end(); // card-body
            $this->end(); // card
            $this->end(); // col
        }
        $this->end(); // row

        return $this;
    }

    /**
     * 键值对展示行（label : value）
     *
     * @param  string  $label  标签
     * @param  mixed  $value  值（转义）
     * @param  string|null  $class  额外 class
     * @return static
     */
    public function kv(string $label, mixed $value, ?string $class = null): static
    {
        $this->push('div', ['class' => Html::cls('d-flex justify-content-between py-2 border-bottom', $class)])
            ->push('span', ['class' => 'text-secondary'])->text($label)->end()
            ->push('span', ['class' => 'fw-semibold'])->text($value)->end()
            ->end();

        return $this;
    }

    /**
     * 信息卡列表（infoCards）：一组「图标 + 标题 + 描述」卡片
     *
     * @param  array  $cards  每项 ['icon'=>, 'title'=>, 'desc'=>, 'variant'=>]
     * @return static
     */
    public function infoCards(array $cards): static
    {
        $this->push('div', ['class' => 'row']);
        foreach ($cards as $cardData) {
            $icon = $this->iconClass($cardData['icon'] ?? 'ti ti-info-circle');
            $variant = $this->enumVariant($cardData['variant'] ?? 'primary');
            $this->push('div', ['class' => 'col-12 col-md-6 col-lg-4'])
                ->push('div', ['class' => 'card'])
                ->push('div', ['class' => 'card-body d-flex align-items-center gap-3'])
                ->push('span', ['class' => 'avatar avatar-md bg-'.Html::escape($variant).'-lt'])
                ->push('i', ['class' => Html::escape($icon)])->end()
                ->end()
                ->push('div')
                ->push('div', ['class' => 'fw-semibold'])->text($cardData['title'] ?? '')->end()
                ->push('div', ['class' => 'text-secondary small'])->text($cardData['desc'] ?? '')->end()
                ->end()
                ->end()
                ->end()
                ->end(); // col
        }
        $this->end(); // row

        return $this;
    }

    // ------------------------------------------------------------------
    // 告警 / 提示
    // ------------------------------------------------------------------

    /**
     * 告警框（alert）
     *
     * @param  string  $message  内容
     * @param  string  $variant  primary/secondary/success/danger/warning/info/light/dark
     * @param  string|null  $icon  图标 class
     * @param  bool  $dismissible  是否可关闭
     * @return static
     */
    public function alert(string $message, string $variant = 'info', ?string $icon = null, bool $dismissible = false): static
    {
        $value = $this->enumVariant($variant);
        $this->push('div', ['class' => 'alert alert-'.Html::escape($value), 'role' => 'alert']);
        if ($dismissible) {
            $this->push('button', ['type' => 'button', 'class' => 'btn-close', 'data-bs-dismiss' => 'alert'])->end();
        }
        if ($icon !== null) {
            $this->push('i', ['class' => Html::escape($this->iconClass($icon))])->end();
        }
        $this->text($message);
        $this->end();

        return $this;
    }

    /**
     * 卡片内告警（cardAlert）：card-body 内带 alert 样式的提示条
     *
     * @param  string  $message  内容
     * @param  string  $variant  语义
     * @param  string|null  $icon  图标
     * @return static
     */
    public function cardAlert(string $message, string $variant = 'info', ?string $icon = null): static
    {
        $value = $this->enumVariant($variant);
        $this->push('div', ['class' => 'card-body'])
            ->push('div', ['class' => 'alert alert-'.Html::escape($value).' mb-0', 'role' => 'alert']);
        if ($icon !== null) {
            $this->push('i', ['class' => Html::escape($this->iconClass($icon))])->end();
        }
        $this->text($message);
        $this->end()->end();

        return $this;
    }

    /**
     * 提示条（notice）：轻量信息提示
     *
     * @param  string  $message  内容
     * @param  string  $type  info/success/warning/danger
     * @return static
     */
    public function notice(string $message, string $type = 'info'): static
    {
        $type = $this->enumVariant($type);
        $this->push('div', ['class' => 'text-'.Html::escape($type).' small'])->text($message)->end();

        return $this;
    }

    /**
     * 错误提示文本
     *
     * @param  string  $message  错误信息
     * @return static
     */
    public function errorText(string $message): static
    {
        $this->push('div', ['class' => 'text-danger small'])->text($message)->end();

        return $this;
    }

    /**
     * 空状态提示文本
     *
     * @param  string  $message  提示内容
     * @return static
     */
    public function emptyText(string $message = '暂无数据'): static
    {
        $this->push('div', ['class' => 'text-center text-secondary py-5'])->text($message)->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 徽章 / 标签
    // ------------------------------------------------------------------

    /**
     * 徽章（badge）
     *
     * @param  string  $text  文本
     * @param  string  $variant  语义
     * @return static
     */
    public function badge(string $text, string $variant = 'primary'): static
    {
        $value = $this->enumVariant($variant);
        $this->push('span', ['class' => 'badge bg-'.Html::escape($value)])->text($text)->end();

        return $this;
    }

    /**
     * 等级徽章（levelBadge）：带渐变/特定样式的等级标记
     *
     * @param  string  $text  文本
     * @param  string  $level  level 语义（如 success/warning/danger）
     * @return static
     */
    public function levelBadge(string $text, string $level = 'success'): static
    {
        $link = $this->enumVariant($level);
        $this->push('span', ['class' => 'badge bg-'.Html::escape($link).'-lt text-'.Html::escape($link)])
            ->text($text)->end();

        return $this;
    }

    /**
     * 代码徽章（codeBadge）：等宽字体小标签
     *
     * @param  string  $code  代码文本
     * @return static
     */
    public function codeBadge(string $code): static
    {
        $this->push('span', ['class' => 'badge bg-light text-dark font-monospace'])
            ->text($code)->end();

        return $this;
    }

    /**
     * 追加徽章（pushBadge，push 语义别名，便于链式上下文语义清晰）
     *
     * @param  string  $text  文本
     * @param  string  $variant  语义
     * @return static
     */
    public function pushBadge(string $text, string $variant = 'primary'): static
    {
        return $this->badge($text, $variant);
    }

    /**
     * 追加纯文本 span（pushSpan）
     *
     * @param  mixed  $text  文本（转义）
     * @param  string|null  $class  额外 class
     * @return static
     */
    public function pushSpan(mixed $text, ?string $class = null): static
    {
        $this->push('span', ['class' => $class])->text($text)->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 表单
    // ------------------------------------------------------------------

    /**
     * 表单字段包装（field）：label + 控件 + 帮助文本
     *
     * @param  string  $label  标签
     * @param  mixed  $control  控件（字符串 HTML / Component / 闭包）
     * @param  string|null  $help  帮助文本
     * @param  bool  $required  是否必填
     * @return static
     */
    public function field(string $label, mixed $control, ?string $help = null, bool $required = false): static
    {
        $this->push('div', ['class' => 'mb-3']);
        if ($label !== '') {
            $this->push('label', ['class' => 'form-label'])
                ->text($label);
            if ($required) {
                $this->push('span', ['class' => 'text-danger'])->text(' *')->end();
            }
            $this->end();
        }
        $this->add($this->resolve($control));
        if ($help !== null && $help !== '') {
            $this->push('div', ['class' => 'form-text'])->text($help)->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 表单字段栅格（fieldGrid）：将多个 field 放进响应式栅格
     *
     * @param  array  $fields  字段数组，每项 ['label'=>, 'control'=>, 'help'=>, 'required'=>, 'width'=>]
     * @return static
     */
    public function fieldGrid(array $fields): static
    {
        $this->push('div', ['class' => 'row']);
        foreach ($fields as $field) {
            $width = $field['width'] ?? 12;
            $cls = $this->colClass('md', $width);
            $this->push('div', ['class' => $cls])
                ->field(
                    (string) ($field['label'] ?? ''),
                    $field['control'] ?? '',
                    $field['help'] ?? null,
                    (bool) ($field['required'] ?? false)
                )
                ->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 表单操作按钮区（formActions）：提交 + 取消
     *
     * @param  string  $submit  提交文案
     * @param  string|null  $cancelUrl  取消跳转链接
     * @param  string  $submitVariant  提交按钮语义
     * @return static
     */
    public function formActions(string $submit = '保存', ?string $cancelUrl = null, string $submitVariant = 'primary'): static
    {
        $value = $this->enumVariant($submitVariant);
        $this->push('div', ['class' => 'd-flex justify-content-end gap-2'])
            ->push('button', ['type' => 'submit', 'class' => 'btn btn-'.Html::escape($value)])
            ->text($submit)->end();
        if ($cancelUrl !== null) {
            $this->push('a', ['href' => $this->url($cancelUrl), 'class' => 'btn btn-link'])
                ->text('取消')->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 权限表单（permissionForm）：复选框列表项（用于角色权限编辑）
     *
     * @param  array  $items  权限项 [['name'=>, 'label'=>, 'checked'=>], ...]
     * @param  string  $name  表单字段名
     * @return static
     */
    public function permissionForm(array $items, string $name = 'permissions[]'): static
    {
        foreach ($items as $item) {
            $checked = ! empty($item['checked']);
            $this->push('div', ['class' => 'form-check'])
                ->push('input', [
                    'class' => 'form-check-input',
                    'type' => 'checkbox',
                    'name' => $name,
                    'value' => (string) ($item['name'] ?? ''),
                    'checked' => $checked ? true : null,
                ])->end()
                ->push('label', ['class' => 'form-check-label'])
                ->text($item['label'] ?? $item['name'] ?? '')->end()
                ->end();
        }

        return $this;
    }

    // ------------------------------------------------------------------
    // 详情 / 操作
    // ------------------------------------------------------------------

    /**
     * 详情按钮（detailButton）
     *
     * @param  string  $url  跳转链接
     * @param  string  $label  文案
     * @param  string  $variant  语义
     * @return static
     */
    public function detailButton(string $url, string $label = '查看', string $variant = 'primary'): static
    {
        $value = $this->enumVariant($variant);
        $this->push('a', ['href' => $this->url($url), 'class' => 'btn btn-sm btn-'.Html::escape($value)])
            ->text($label)->end();

        return $this;
    }

    /**
     * 摘要栏（summaryBar）：一排「标签 + 值」
     *
     * @param  array  $items  [['label'=>, 'value'=>], ...]
     * @return static
     */
    public function summaryBar(array $items): static
    {
        $this->push('div', ['class' => 'd-flex flex-wrap gap-4 mb-3']);
        foreach ($items as $item) {
            $this->push('div')
                ->push('div', ['class' => 'text-secondary small'])->text($item['label'] ?? '')->end()
                ->push('div', ['class' => 'fw-semibold'])->text($item['value'] ?? '')->end()
                ->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 按钮组（buttonGroup）
     *
     * @param  array  $buttons  [['label'=>, 'url'=>, 'variant'=>, 'icon'=>], ...]
     * @return static
     */
    public function buttonGroup(array $buttons): static
    {
        $this->push('div', ['class' => 'btn-group']);
        foreach ($buttons as $button) {
            $value = $this->enumVariant($button['variant'] ?? 'primary');
            $icon = $button['icon'] ?? null;
            $this->push('a', ['href' => $this->url($button['url'] ?? '#'), 'class' => 'btn btn-'.Html::escape($value)]);
            if ($icon !== null) {
                $this->push('i', ['class' => Html::escape($this->iconClass($icon))])->end();
            }
            $this->text($button['label'] ?? '');
            $this->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 快捷链接（quickLinks）
     *
     * @param  array  $links  [['label'=>, 'url'=>, 'icon'=>], ...]
     * @return static
     */
    public function quickLinks(array $links): static
    {
        $this->push('div', ['class' => 'd-flex flex-wrap gap-3']);
        foreach ($links as $link) {
            $icon = $link['icon'] ?? null;
            $this->push('a', ['href' => $this->url($link['url'] ?? '#'), 'class' => 'd-inline-flex align-items-center gap-1 text-decoration-none']);
            if ($icon !== null) {
                $this->push('i', ['class' => Html::escape($this->iconClass($icon))])->end();
            }
            $this->text($link['label'] ?? '');
            $this->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 工具栏（toolbar）：标题 + 右侧操作区
     *
     * @param  string  $title  标题
     * @param  mixed  $actions  右侧操作（字符串 / 组件 / 闭包）
     * @return static
     */
    public function toolbar(string $title, mixed $actions = null): static
    {
        $this->push('div', ['class' => 'd-flex justify-content-between align-items-center mb-3'])
            ->push('h5', ['class' => 'mb-0'])->text($title)->end();
        if ($actions !== null) {
            $this->push('div', ['class' => 'd-flex gap-2'])
                ->add($this->resolve($actions))
                ->end();
        }
        $this->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 结构面板 / 表格
    // ------------------------------------------------------------------

    /**
     * 面板（panel）：带标题与正文的结构容器
     *
     * @param  string  $title  标题
     * @param  mixed  $body  正文
     * @return static
     */
    public function panel(string $title, mixed $body): static
    {
        $this->push('div', ['class' => 'card'])
            ->push('div', ['class' => 'card-header'])->text($title)->end()
            ->push('div', ['class' => 'card-body'])
            ->add($this->resolve($body))
            ->end()
            ->end();

        return $this;
    }

    /**
     * 面板行（panelRow）：无卡片边框的内容行
     *
     * @param  mixed  $content  内容
     * @return static
     */
    public function panelRow(mixed $content): static
    {
        $this->push('div', ['class' => 'row'])->add($this->resolve($content))->end();

        return $this;
    }

    /**
     * 迷你表格（miniTable）：键值对两列表格
     *
     * @param  array  $rows  行数组 [['label'=>, 'value'=>], ...]
     * @return static
     */
    public function miniTable(array $rows): static
    {
        $this->push('table', ['class' => 'table table-sm table-borderless'])
            ->push('tbody');
        foreach ($rows as $row) {
            $this->push('tr')
                ->push('td', ['class' => 'text-secondary'])->text($row['label'] ?? '')->end()
                ->push('td')->text($row['value'] ?? '')->end()
                ->end();
        }
        $this->end()->end();

        return $this;
    }

    /**
     * 键值表（kvTable）：带边框的键值表格
     *
     * @param  array  $rows  [['label'=>, 'value'=>], ...]
     * @return static
     */
    public function kvTable(array $rows): static
    {
        $this->push('table', ['class' => 'table table-bordered'])
            ->push('tbody');
        foreach ($rows as $row) {
            $this->push('tr')
                ->push('th', ['class' => 'w-25 bg-light'])->text($row['label'] ?? '')->end()
                ->push('td')->text($row['value'] ?? '')->end()
                ->end();
        }
        $this->end()->end();

        return $this;
    }

    /**
     * 纵向键值列表（kvList）：label 在上、value 在下，逐条堆叠
     *
     * 后台详情页/弹窗的通用「字段详情」模式（区别于 kv() 的横向键值行）。
     * 空值（null / ''）自动归一为「-」。
     *
     * @param  array<string, mixed>  $rows  键值对，如 ['模块' => 'Admin', '路径' => '/admin']
     * @param  bool  $wrap  是否用 <div class="row g-2"> 包裹（false 时仅输出条目，供嵌入列内）
     * @return static
     */
    public function kvList(array $rows, bool $wrap = true): static
    {
        if ($wrap) {
            $this->push('div', ['class' => 'row g-2']);
        }
        foreach ($rows as $label => $value) {
            if ($value === null || $value === '') {
                $value = '-';
            }
            $this->push('div', ['class' => 'col-12'])
                ->push('div', ['class' => 'text-muted small'])->text((string) $label)->end()
                ->push('div', ['style' => 'word-break:break-all'])->text((string) $value)->end()
                ->end();
        }
        if ($wrap) {
            $this->end();
        }

        return $this;
    }

    /**
     * 信息卡（infoCard）：标题 + 纵向键值列表，置于响应式列中
     *
     * 后台详情页「请求上下文 / 响应结果」等分栏信息卡模式。
     *
     * @param  string  $title  卡片标题
     * @param  array<string, mixed>  $rows  键值对
     * @param  string  $colClass  列宽 class（默认 col-12 col-lg-6）
     * @return static
     */
    public function infoCard(string $title, array $rows, string $colClass = 'col-12 col-lg-6'): static
    {
        $this->push('div', ['class' => Html::escape($colClass)])
            ->push('div', ['class' => 'card h-100'])
            ->push('div', ['class' => 'card-header py-2'])
            ->push('h6', ['class' => 'mb-0'])->text($title)->end()
            ->end()
            ->push('div', ['class' => 'card-body'])
            ->kvList($rows)
            ->end()
            ->end()
            ->end();

        return $this;
    }

    /**
     * 列表组（listGroup）
     *
     * @param  array  $items  列表项 [['label'=>, 'value'=>, 'badge'=>, 'href'=>], ...]
     * @return static
     */
    public function listGroup(array $items): static
    {
        $this->push('ul', ['class' => 'list-group']);
        foreach ($items as $item) {
            $href = $item['href'] ?? null;
            $tag = $href !== null ? 'a' : 'li';
            $attrs = ['class' => 'list-group-item d-flex justify-content-between align-items-center'];
            if ($href !== null) {
                $attrs['href'] = $this->url($href);
            }
            $this->push($tag, $attrs)
                ->text($item['label'] ?? '');
            if (isset($item['badge'])) {
                $this->push('span', ['class' => 'badge bg-secondary'])->text($item['badge'])->end();
            }
            $this->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 对话框列表项（dialogListItem）：带图标与说明的列表项
     *
     * @param  string  $icon  图标
     * @param  string  $label  标签
     * @param  string|null  $desc  描述
     * @return static
     */
    public function dialogListItem(string $icon, string $label, ?string $desc = null): static
    {
        $this->push('div', ['class' => 'd-flex align-items-center gap-3 py-2'])
            ->push('i', ['class' => Html::escape($this->iconClass($icon))])->end()
            ->push('div')
            ->push('div', ['class' => 'fw-semibold'])->text($label)->end();
        if ($desc !== null) {
            $this->push('div', ['class' => 'text-secondary small'])->text($desc)->end();
        }
        $this->end()->end();

        return $this;
    }

    /**
     * 树形组件（treeWidget）：简单两级树
     *
     * @param  array  $tree  [['label'=>, 'children'=>[['label'=>,...]], 'icon'=>], ...]
     * @return static
     */
    public function treeWidget(array $tree): static
    {
        $this->push('ul', ['class' => 'list-unstyled']);
        foreach ($tree as $node) {
            $icon = $node['icon'] ?? 'ti ti-folder';
            $this->push('li', ['class' => 'mb-1'])
                ->push('div', ['class' => 'd-flex align-items-center gap-2'])
                ->push('i', ['class' => Html::escape($this->iconClass($icon))])->end()
                ->text($node['label'] ?? '')
                ->end();
            if (! empty($node['children'])) {
                $this->push('ul', ['class' => 'list-unstyled ps-4']);
                foreach ($node['children'] as $child) {
                    $this->push('li', ['class' => 'mb-1'])
                        ->push('div', ['class' => 'd-flex align-items-center gap-2'])
                        ->push('i', ['class' => Html::escape($this->iconClass($child['icon'] ?? 'ti ti-file'))])->end()
                        ->text($child['label'] ?? '')
                        ->end()
                        ->end();
                }
                $this->end();
            }
            $this->end();
        }
        $this->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 内容 / 结构容器
    // ------------------------------------------------------------------

    /**
     * 预格式化代码块（pre）
     *
     * @param  string  $content  代码内容
     * @return static
     */
    public function pre(string $content): static
    {
        $this->push('pre', ['class' => 'bg-light p-3 rounded'])->text($content)->end();

        return $this;
    }

    /**
     * JSON 代码块（jsonBlock）
     *
     * @param  mixed  $data  数据（自动 json_encode）
     * @return static
     */
    public function jsonBlock(mixed $data): static
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $this->push('pre', ['class' => 'bg-light p-3 rounded font-monospace'])->text($json ?: '')->end();

        return $this;
    }

    /**
     * 可滚动预格式化块（scrollBlock）：max-height 内滚动的代码/文本块
     *
     * 通用封装，用于日志详情、API 响应体、异常堆栈等「可能很长」的内容：
     * 内容按纯文本转义；默认横向滚动（适合已排版的 JSON/代码），
     * $wrap=true 时保留换行并自动断词（适合纯文本）；可指定最大高度与字号。
     *
     * @param  string  $content  内容（按纯文本转义）
     * @param  int|null  $maxHeight  最大高度（px），null 不限制
     * @param  bool  $wrap  是否保留换行并断词（默认 false 横向滚动）
     * @param  int|null  $fontSize  字号（px）
     * @return static
     */
    public function scrollBlock(string $content, ?int $maxHeight = null, bool $wrap = false, ?int $fontSize = null): static
    {
        $style = 'overflow:auto;';
        if ($maxHeight !== null && $maxHeight > 0) {
            $style .= 'max-height:'.Html::escape((string) $maxHeight).'px;';
        }
        $style .= 'white-space:'.($wrap ? 'pre-wrap;word-break:break-all;' : 'pre;');
        if ($fontSize !== null && $fontSize > 0) {
            $style .= 'font-size:'.Html::escape((string) $fontSize).'px;';
        }
        $this->push('pre', ['class' => 'mb-0 p-3', 'style' => $style])
            ->text($content)->end();

        return $this;
    }

    /**
     * JSON 可滚动块（jsonScrollBlock）：自动 json_encode + max-height 滚动
     *
     * 空值（null/''/[]）不输出任何内容（返回 $this，render 后为空串）；
     * 字符串参数直接作为「已编码 JSON」透传（避免二次编码）。
     *
     * @param  mixed  $data  数据（数组/对象/已编码字符串）
     * @param  int|null  $maxHeight  最大高度（px）
     * @param  int|null  $fontSize  字号（px）
     * @return static
     */
    public function jsonScrollBlock(mixed $data, ?int $maxHeight = null, ?int $fontSize = null): static
    {
        if ($data === null || $data === [] || $data === '') {
            return $this;
        }
        $json = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->scrollBlock((string) $json, $maxHeight, false, $fontSize);
    }

    /**
     * 表格面板（tablePane）：标题 + 表格内容
     *
     * @param  string  $title  标题
     * @param  mixed  $table  表格内容
     * @return static
     */
    public function tablePane(string $title, mixed $table): static
    {
        $this->push('div', ['class' => 'card'])
            ->push('div', ['class' => 'card-header'])->text($title)->end()
            ->push('div', ['class' => 'card-body p-0'])
            ->add($this->resolve($table))
            ->end()
            ->end();

        return $this;
    }

    /**
     * 对话框内容容器（dialogContent）
     *
     * @param  mixed  $content  内容
     * @return static
     */
    public function dialogContent(mixed $content): static
    {
        $this->push('div', ['class' => 'modal-body'])->add($this->resolve($content))->end();

        return $this;
    }

    /**
     * 对话框结束（dialogEnd）：模态框页脚（关闭/确认）
     *
     * @param  string  $confirm  确认文案
     * @param  string  $cancel  取消文案
     * @return static
     */
    public function dialogEnd(string $confirm = '确定', string $cancel = '取消'): static
    {
        $this->push('div', ['class' => 'modal-footer'])
            ->push('button', ['type' => 'button', 'class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal'])
            ->text($cancel)->end()
            ->push('button', ['type' => 'button', 'class' => 'btn btn-primary'])
            ->text($confirm)->end()
            ->end();

        return $this;
    }

    /**
     * 手风琴（accordion）
     *
     * @param  array  $items  [['title'=>, 'content'=>, 'open'=>bool], ...]
     * @return static
     */
    public function accordion(array $items): static
    {
        $this->push('div', ['class' => 'accordion', 'id' => $this->uid()]);
        foreach ($items as $index => $item) {
            $hid = $this->uid();
            $open = ! empty($item['open']);
            $this->push('div', ['class' => 'accordion-item'])
                ->push('h2', ['class' => 'accordion-header'])
                ->push('button', [
                    'class' => 'accordion-button'.($open ? '' : ' collapsed'),
                    'type' => 'button',
                    'data-bs-toggle' => 'collapse',
                    'data-bs-target' => '#'.$hid,
                ])->text($item['title'] ?? '')->end()
                ->end()
                ->push('div', [
                    'id' => $hid,
                    'class' => 'accordion-collapse collapse'.($open ? ' show' : ''),
                ])
                ->push('div', ['class' => 'accordion-body'])
                ->add($this->resolve($item['content'] ?? ''))
                ->end()
                ->end()
                ->end();
        }
        $this->end();

        return $this;
    }

    /**
     * 状态徽章（statusBadge）：将业务状态映射为语义徽章
     *
     * 内置常见状态 → Bootstrap 语义色映射（不区分大小写）：
     *  - success：active / enabled / published / normal / ok / online / success
     *  - secondary：inactive / disabled / draft / offline / closed / deleted（软删除灰）
     *  - warning：pending / waiting / review / audit
     *  - danger：banned / blocked / failed / error / rejected / deleted（硬删除红）
     *  - info：processing / running / info
     * 未命中映射回退 secondary（浅灰），避免外部传入任意状态值逃逸出 class 属性。
     *
     * @param  string  $status  状态标识
     * @param  string|null  $label  显示文案（默认用状态原值）
     * @return static
     */
    public function statusBadge(string $status, ?string $label = null): static
    {
        $map = [
            'success' => 'success', 'active' => 'success', 'enabled' => 'success', 'published' => 'success',
            'normal' => 'success', 'ok' => 'success', 'online' => 'success',
            'secondary' => 'secondary', 'inactive' => 'secondary', 'disabled' => 'secondary', 'draft' => 'secondary',
            'offline' => 'secondary', 'closed' => 'secondary',
            'warning' => 'warning', 'pending' => 'warning', 'waiting' => 'warning', 'review' => 'warning', 'audit' => 'warning',
            'danger' => 'danger', 'banned' => 'danger', 'blocked' => 'danger', 'failed' => 'danger',
            'error' => 'danger', 'rejected' => 'danger',
            'info' => 'info', 'processing' => 'info', 'running' => 'info',
        ];
        $variant = $map[strtolower($status)] ?? 'secondary';
        $text = $label ?? $status;
        $this->push('span', ['class' => 'badge bg-'.Html::escape($variant).'-lt text-'.Html::escape($variant)])
            ->text($text)->end();

        return $this;
    }

    /**
     * 页面区块（section）：带可选标题的结构化内容容器
     *
     * 用于在详情/列表页把相关内容分组为「区块」，统一外边距与卡片样式。
     * 传 $title 时渲染带标题的卡片（card-header + card-body）；不传时渲染带底外边距的裸卡片。
     *
     * @param  string|null  $title  区块标题（null/空时不渲染标题）
     * @param  mixed  $body  区块内容（字符串 / 组件 / 闭包 / 数组）
     * @param  array  $attrs  额外根元素属性（class/id 等，class 会与默认 class 合并）
     * @return static
     */
    public function section(?string $title, mixed $body, array $attrs = []): static
    {
        $class = Html::cls('card mb-3', $attrs['class'] ?? null);
        unset($attrs['class']);
        $attrs['class'] = $class;
        $this->push('div', $attrs);
        if ($title !== null && $title !== '') {
            $this->push('div', ['class' => 'card-header'])->text($title)->end();
        }
        $this->push('div', ['class' => 'card-body'])
            ->add($this->resolve($body))
            ->end();
        $this->end();

        return $this;
    }

    // ------------------------------------------------------------------
    // 内部辅助
    // ------------------------------------------------------------------

    /**
     * 语义枚举白名单校验
     *
     * @param  string  $variant  语义值
     */
    private function enumVariant(string $variant): string
    {
        $allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
        $variant = str_replace('outline-', '', $variant);

        return in_array($variant, $allowed, true) ? $variant : 'primary';
    }

    /** 图标 class 净化 */
    private function iconClass(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return (string) preg_replace('/[^a-zA-Z0-9_\- ]/', '', $value);
    }

    /** 安全 URL */
    private function url(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '#') {
            return $value;
        }
        if (preg_match('/^javascript:\s*(?:void\s*\(?\s*0*\s*\)?\s*;?|;?)$/i', $value)) {
            return $value;
        }
        if (preg_match('#^(?:https?:)?//#i', $value)
            || str_starts_with($value, '/')
            || str_starts_with($value, '#')
            || preg_match('#^(?:mailto:|tel:)#i', $value)
            || preg_match('#^data:image/#i', $value)
            || ! preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $value)) {
            return $value;
        }

        return '#';
    }

    /** 生成唯一 id */
    private function uid(): string
    {
        static $counter = 0;

        return 'xfa-'.(++$counter);
    }
}

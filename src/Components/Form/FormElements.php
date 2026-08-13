<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Form;

use zxf\XfAdmin\Components\Component;

/**
 * 表单元素展示（form-elements.html）
 *
 * XfAdmin::formElements([
 *     'sections' => [
 *         ['title' => '基础输入', 'items' => [...], 'cols' => 2],
 *         ['title' => '选择器', 'items' => [...], 'cols' => 3],
 *     ],
 * ])
 *
 * 也可不传 sections，使用内置完整的表单元素展示页。
 */
class FormElements extends Component
{
    protected function defaults(): array
    {
        return [
            'sections' => null,
        ];
    }

    protected function html(): string
    {
        $sections = $this->get('sections');

        if ($sections !== null) {
            return $this->renderSections((array) $sections);
        }

        return $this->renderBuiltin();
    }

    /**
     * 用户自定义 sections 模式
     */
    private function renderSections(array $sections): string
    {
        $html = '<div class="row g-4">';
        foreach ($sections as $section) {
            $section = (array) $section;
            $title = $this->e($section['title'] ?? '');
            $items = (array) ($section['items'] ?? []);
            $cols = (int) ($section['cols'] ?? 2);

            $html .= '<div class="col-12"><div class="card"><div class="card-header"><h5 class="card-title mb-0">' . $title . '</h5></div>'
                . '<div class="card-body"><div class="row g-3">';

            foreach ($items as $item) {
                $item = (array) $item;
                $colClass = 'col-md-' . (12 / min($cols, 4));
                $html .= '<div class="' . $colClass . '">' . $this->renderFormItem($item) . '</div>';
            }

            $html .= '</div></div></div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * 内置完整表单元素展示
     */
    private function renderBuiltin(): string
    {
        $html = '';

        // Section 1: 基础输入
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">基础输入框</h5></div><div class="card-body"><div class="row g-3">';
        $html .= $this->formGroup('文本输入', '<input type="text" class="form-control" placeholder="请输入文本">', 6);
        $html .= $this->formGroup('密码', '<input type="password" class="form-control" value="password123">', 6);
        $html .= $this->formGroup('邮箱', '<input type="email" class="form-control" placeholder="name@example.com">', 6);
        $html .= $this->formGroup('数字', '<input type="number" class="form-control" value="42">', 6);
        $html .= $this->formGroup('网址', '<input type="url" class="form-control" placeholder="https://">', 6);
        $html .= $this->formGroup('电话号码', '<input type="tel" class="form-control" placeholder="138xxxx">', 6);
        $html .= $this->formGroup('日期', '<input type="date" class="form-control">', 6);
        $html .= $this->formGroup('时间', '<input type="time" class="form-control">', 6);
        $html .= $this->formGroup('日期时间', '<input type="datetime-local" class="form-control">', 6);
        $html .= $this->formGroup('颜色选择', '<input type="color" class="form-control form-control-color" value="#0d6efd">', 6);
        $html .= $this->formGroup('范围滑块', '<input type="range" class="form-range" min="0" max="100" value="50">', 6);
        $html .= $this->formGroup('文件上传', '<input type="file" class="form-control">', 6);
        $html .= '</div></div></div>';

        // Section 2: 选择器
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">选择器 & 开关</h5></div><div class="card-body"><div class="row g-3">';
        $html .= $this->formGroup('下拉选择', '<select class="form-select"><option selected>请选择...</option><option>选项 1</option><option>选项 2</option><option>选项 3</option></select>', 6);
        $html .= $this->formGroup('多选', '<select class="form-select" multiple size="4"><option>选项 1</option><option selected>选项 2</option><option selected>选项 3</option><option>选项 4</option></select>', 6);
        $html .= $this->formGroup('复选框', '<div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label">默认选中</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="checkbox"><label class="form-check-label">未选中</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="checkbox" disabled><label class="form-check-label">禁用</label></div>', 6);
        $html .= $this->formGroup('单选按钮', '<div class="form-check"><input class="form-check-input" type="radio" name="demoRadio" checked><label class="form-check-label">选项 A</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="radio" name="demoRadio"><label class="form-check-label">选项 B</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="radio" name="demoRadio" disabled><label class="form-check-label">选项 C (禁用)</label></div>', 6);
        $html .= $this->formGroup('开关', '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked><label class="form-check-label">启用通知</label></div>'
            . '<div class="form-check form-switch"><input class="form-check-input" type="checkbox"><label class="form-check-label">暗色模式</label></div>', 6);
        $html .= '</div></div></div>';

        // Section 3: 文本区域 & 富文本控件
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">文本区域</h5></div><div class="card-body"><div class="row g-3">';
        $html .= $this->formGroup('多行文本', '<textarea class="form-control" rows="4" placeholder="请输入内容..."></textarea>', 6);
        $html .= $this->formGroup('只读输入', '<input type="text" class="form-control" value="只读内容" readonly>', 6);
        $html .= $this->formGroup('禁用输入', '<input type="text" class="form-control" value="禁用内容" disabled>', 6);
        $html .= $this->formGroup('无边框输入', '<input type="text" class="form-control border-0 bg-transparent" value="无边框样式">', 6);
        $html .= '</div></div></div>';

        // Section 4: 输入组 & 图标
        $html .= '<div class="card mb-3"><div class="card-header"><h5 class="card-title mb-0">输入组</h5></div><div class="card-body"><div class="row g-3">';
        $html .= $this->formGroup('前缀图标', '<div class="input-group"><span class="input-group-text"><i class="ti ti-user"></i></span><input type="text" class="form-control" placeholder="用户名"></div>', 6);
        $html .= $this->formGroup('后缀图标', '<div class="input-group"><input type="text" class="form-control" placeholder="搜索..."><span class="input-group-text"><i class="ti ti-search"></i></span></div>', 6);
        $html .= $this->formGroup('前缀文本', '<div class="input-group"><span class="input-group-text">¥</span><input type="number" class="form-control" value="99.00"></div>', 6);
        $html .= $this->formGroup('后缀文本', '<div class="input-group"><input type="number" class="form-control" value="100"><span class="input-group-text">件</span></div>', 6);
        $html .= $this->formGroup('前后组合', '<div class="input-group"><span class="input-group-text"><i class="ti ti-mail"></i></span><input type="email" class="form-control"><button class="btn btn-primary">发送</button></div>', 6);
        $html .= $this->formGroup('按钮下拉', '<div class="input-group"><input type="text" class="form-control"><button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">操作</button>'
            . '<ul class="dropdown-menu"><li><a class="dropdown-item" href="#">导出</a></li><li><a class="dropdown-item" href="#">导入</a></li></ul></div>', 6);
        $html .= '</div></div></div>';

        // Section 5: 尺寸 & 布局
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">尺寸变化</h5></div><div class="card-body"><div class="row g-3">';
        $html .= $this->formGroup('大号', '<input type="text" class="form-control form-control-lg" placeholder="大号输入框">', 4);
        $html .= $this->formGroup('默认', '<input type="text" class="form-control" placeholder="默认输入框">', 4);
        $html .= $this->formGroup('小号', '<input type="text" class="form-control form-control-sm" placeholder="小号输入框">', 4);
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * 渲染单个表单项
     */
    private function renderFormItem(array $item): string
    {
        $label = $this->e($item['label'] ?? '');
        $content = $item['content'] ?? '';
        $help = $this->e($item['help'] ?? '');

        $html = '<label class="form-label">' . $label . '</label>';
        $html .= is_string($content) ? $content : '';
        if ($help) {
            $html .= '<div class="form-text">' . $help . '</div>';
        }

        return $html;
    }

    /**
     * 快捷渲染带 label 的表单组
     */
    private function formGroup(string $label, string $content, int $colMd = 6): string
    {
        return '<div class="col-md-' . $colMd . '"><label class="form-label">' . $this->e($label) . '</label>' . $content . '</div>';
    }
}

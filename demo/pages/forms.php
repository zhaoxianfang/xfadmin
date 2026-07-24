<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

echo XfAdmin::page([
    'title'       => '表单 - XfAdmin Demo',
    'menu'        => $menu,
    'current_url' => '/forms',
    'topbar'      => ['user' => $user],
    'page_title'  => ['title' => '表单组件', 'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '表单']]],
    'content'     => [
        XfAdmin::row(['gutter' => 3, 'cols' => [
            ['width' => ['lg' => 6], 'content' => XfAdmin::card([
                'title' => '基础字段 + 客户端校验',
                'body'  => XfAdmin::form([
                    'action'     => '/save',
                    'validation' => true,
                    'fields'     => [
                        XfAdmin::input(['name' => 'name', 'label' => '姓名', 'required' => true, 'placeholder' => '请输入姓名', 'feedback' => ['invalid' => '姓名不能为空']]),
                        XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true, 'prepend' => '@']),
                        XfAdmin::input(['name' => 'phone', 'label' => '手机号（输入掩码）', 'mask' => '999-9999-9999']),
                        XfAdmin::select(['name' => 'city', 'label' => '城市（Choices 增强/可搜索）', 'enhance' => 'choices', 'placeholder' => '请选择', 'groups' => [
                            '直辖市' => ['bj' => '北京', 'sh' => '上海', 'tj' => '天津', 'cq' => '重庆'],
                            '省会'   => ['gz' => '广州', 'hz' => '杭州', 'cd' => '成都'],
                        ]]),
                        XfAdmin::select(['name' => 'tags', 'label' => '标签（多选）', 'enhance' => 'choices', 'multiple' => true, 'options' => ['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go'], 'value' => ['php']]),
                        XfAdmin::dateRange(['name' => 'period', 'label' => '统计时段（快捷区间）', 'ranges' => true]),
                        XfAdmin::check(['type' => 'radio', 'name' => 'gender', 'label' => '性别', 'inline' => true, 'options' => ['m' => '男', 'f' => '女'], 'value' => 'm']),
                        XfAdmin::check(['type' => 'switch', 'name' => 'enabled', 'label' => '立即启用', 'checked' => true]),
                    ],
                    'buttons' => '<button type="submit" class="btn btn-primary">提交</button><button type="reset" class="btn btn-light">重置</button>',
                ]),
            ])],
            ['width' => ['lg' => 6], 'content' => XfAdmin::card([
                'title' => '高级控件',
                'body'  => XfAdmin::form([
                    'fields' => [
                        XfAdmin::slider(['name' => 'price', 'label' => '价格区间（noUiSlider）', 'min' => 0, 'max' => 1000, 'value' => [200, 600], 'tooltips' => true]),
                        XfAdmin::colorPicker(['name' => 'color', 'label' => '主题色（Pickr）', 'value' => '#3e60d5']),
                        XfAdmin::editor(['name' => 'content', 'label' => '富文本（Quill）', 'height' => 200, 'value' => '<p>初始<strong>内容</strong></p>']),
                        XfAdmin::upload(['driver' => 'dropzone', 'label' => '拖拽上传（Dropzone）', 'url' => '/upload', 'text' => '点击或拖拽文件到此处']),
                    ],
                ]),
            ]) . XfAdmin::card([
                'title' => '交互组件',
                'body'  => '<div class="d-flex flex-wrap gap-2">'
                    . XfAdmin::button(['text' => '打开模态框', 'toggle' => 'modal', 'target' => '#demo-modal'])
                    . XfAdmin::button(['text' => '打开抽屉', 'variant' => 'info', 'toggle' => 'offcanvas', 'target' => '#demo-offcanvas'])
                    . XfAdmin::sweetAlert(['trigger' => 'SweetAlert 确认', 'trigger_variant' => 'danger', 'title' => '确定删除？', 'text' => '此操作不可恢复', 'icon' => 'warning', 'cancel_text' => '取消', 'confirm_text' => '删除'])
                    . XfAdmin::clipboard(['text' => 'XfAdmin 复制内容示例', 'label' => '复制文本'])
                    . XfAdmin::button(['text' => 'Ladda 加载按钮', 'variant' => 'success', 'ladda' => true])
                    . '</div>'
                    . XfAdmin::modal(['id' => 'demo-modal', 'title' => '模态框', 'centered' => true, 'body' => XfAdmin::alert(['variant' => 'info', 'text' => '模态框里可以嵌套任何组件', 'soft' => true]), 'footer' => '<button class="btn btn-light" data-bs-dismiss="modal">关闭</button>'])
                    . XfAdmin::offcanvas(['id' => 'demo-offcanvas', 'title' => '抽屉面板', 'body' => XfAdmin::listGroup(['items' => ['抽屉内容一', '抽屉内容二']])]),
            ])],
        ]]),
        XfAdmin::card([
            'title' => 'Tabs / 手风琴 / 进度条 / 头像 / 徽章 混排',
            'body'  => XfAdmin::tabs(['items' => [
                ['title' => '手风琴', 'icon' => 'ti ti-layout-rows', 'content' => XfAdmin::accordion(['items' => [
                    ['title' => '面板一', 'content' => '内容一', 'open' => true],
                    ['title' => '面板二', 'content' => XfAdmin::progress(['bars' => [['value' => 40, 'variant' => 'success'], ['value' => 25, 'variant' => 'warning']]])],
                ]])],
                ['title' => '头像组', 'icon' => 'ti ti-users', 'content' => XfAdmin::avatar(['group' => [
                    ['src' => xf_asset('images/users/user-1.jpg')],
                    ['src' => xf_asset('images/users/user-3.jpg')],
                    ['text' => '+8', 'variant' => 'primary'],
                ]])],
                ['title' => '徽章', 'icon' => 'ti ti-tag', 'content' => '<div class="d-flex gap-2">'
                    . XfAdmin::badge(['text' => '默认'])
                    . XfAdmin::badge(['text' => '柔和', 'variant' => 'success', 'soft' => true])
                    . XfAdmin::badge(['text' => '圆角', 'variant' => 'danger', 'pill' => true])
                    . XfAdmin::badge(['text' => '描边', 'variant' => 'info', 'outline' => true])
                    . '</div>'],
            ]]),
        ]),
    ],
]);

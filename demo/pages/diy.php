<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

/**
 * DIY 可视化布局器演示页
 *
 * 渲染三栏式页面构建器；组件实时预览走同站点 /diy/render 端点。
 */
echo XfAdmin::diyLayoutPage([
    'render_url' => '/diy/render',
]);

<?php
/**
 * 离线渲染验证：逐个实例化 Data 组件并调用 html()，
 * 捕获任何渲染期异常 / 致命错误（不依赖 HTTP 服务器与登录中间件）。
 *
 * 运行：php tools/selftest/render_new_components.php
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use zxf\XfAdmin\XfAdmin;

XfAdmin::config(require __DIR__ . '/../../config/xfadmin.php');

// 取所有 Data 命名空间下已注册组件
$all = XfAdmin::componentList();

$dataAliases = [];
foreach ($all as $alias => $class) {
    if (str_starts_with($class, 'zxf\\XfAdmin\\Components\\Data\\')) {
        $dataAliases[$alias] = $class;
    }
}

$fail   = 0;
$ok     = 0;
$empty  = 0;
foreach ($dataAliases as $alias => $class) {
    try {
        // 用最小示例 options 触发 defaults + 渲染（经 __toString 内部调用 html()）
        $instance = XfAdmin::$alias([]);
        $html     = (string) $instance;
        if ($html === '' || $html === '0') {
            // 空数组下无数据可渲染属正常（页面级组件需示例数据），仅计数不报错
            $empty++;
        } else {
            $ok++;
        }
    } catch (\Throwable $e) {
        echo "ERROR  : $alias ($class) => " . get_class($e) . ': ' . $e->getMessage() . "\n";
        $fail++;
    }
}

echo "\n=== Data 组件渲染验证：渲染成功=$ok  空数据跳过=$empty  致命错误=$fail  TOTAL=" . count($dataAliases) . " ===\n";
exit($fail === 0 ? 0 : 1);

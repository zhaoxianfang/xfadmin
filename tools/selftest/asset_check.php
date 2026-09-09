<?php
declare(strict_types=1);

/**
 * 资源依赖完整性校验：
 *   1) 组件 assets() 声明的插件必须已注册在 Assets::PLUGINS
 *   2) PLUGINS 声明的 css/js 文件必须真实存在于 resources/assets
 *
 * 运行：php tools/selftest/asset_check.php
 * 退出码：0 = 通过，1 = 存在缺陷
 */

$root = dirname(__DIR__, 2);

if (is_file($root . '/vendor/autoload.php')) {
    require $root . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class) use ($root): void {
        if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
            $path = $root . '/src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    });
    require $root . '/src/helpers.php';
}

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\XfAdmin;

XfAdmin::config(array_replace(require $root . '/config/xfadmin.php', [
    'assets_url' => '/zxf/xfadmin',
    'version'    => '1.0.0',
]));

$components = XfAdmin::componentList();

$missing     = [];
$missingFile = [];

foreach ($components as $alias => $class) {
    if (! class_exists($class)) {
        continue;
    }
    try {
        $ref = new ReflectionMethod($class, 'assets');
        $obj  = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $deps = (array) $ref->invoke($obj);
    } catch (Throwable $e) {
        continue;
    }
    foreach ($deps as $dep) {
        if (! array_key_exists($dep, Assets::PLUGINS)) {
            $missing[] = "{$alias} → {$dep}";
        }
    }
}

foreach (Assets::PLUGINS as $name => $def) {
    foreach (['css', 'js'] as $type) {
        foreach ($def[$type] ?? [] as $file) {
            $path = $root . '/resources/assets/' . ltrim($file, '/');
            if (! is_file($path)) {
                $missingFile[] = "{$name}:{$file}";
            }
        }
    }
}

$fail = 0;

echo '组件 assets() 插件均已注册: ';
if ($missing === []) {
    echo 'OK（' . count($components) . ' 组件）' . PHP_EOL;
} else {
    $fail++;
    echo 'FAIL' . PHP_EOL . '  ' . implode("\n  ", array_slice($missing, 0, 12)) . PHP_EOL;
}

echo 'PLUGINS 声明的资源文件均存在: ';
if ($missingFile === []) {
    echo 'OK' . PHP_EOL;
} else {
    $fail++;
    echo 'FAIL' . PHP_EOL . '  ' . implode("\n  ", array_slice($missingFile, 0, 12)) . PHP_EOL;
}

exit($fail ? 1 : 0);

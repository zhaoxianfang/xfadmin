<?php

/**
 * XfAdmin 组件矩阵分析器（文档生成用，仅供开发维护使用）
 *
 * 作用：用反射读取组件注册表，实例化每个组件并导出：
 *   - 别名 / 类名 / 文件路径 / 分类（按命名空间目录）
 *   - 中文用途说明（优先取 src/XfAdmin.php 的 @method 注释，回退类 docblock）
 *   - defaults() 全量参数与默认值（经 Component 合并后的 options）
 *   - assets() 依赖的插件
 *   - 类的公开方法（扩展点）
 *
 * 用法：php tools/gen_component_matrix.php [输出文件路径]
 *      默认输出 storage/docs/components.json（不存在则写 /tmp/xfadmin_components.json）
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

// PHP 8.5 起 ReflectionMethod::setAccessible() 已废弃（反射默认可访问非公共成员）
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

/** 从 XfAdmin.php 的 @method 注释中解析「别名 => 中文说明」 */
function parseMethodDocs(string $file): array
{
    $docs = [];
    $src = (string) file_get_contents($file);
    if (preg_match_all('/@method\s+static\s+[\\\\\w]+\s+(\w+)\s*\([^)]*\)\s*(?:\/\/|::)(.+)/', $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $docs[$x[1]] = trim($x[2]);
        }
    }
    return $docs;
}

/** 取类 docblock 的摘要（去掉 @ 开头的标签行） */
function classSummary(string $class): string
{
    try {
        $rc = new ReflectionClass($class);
    } catch (Throwable) {
        return '';
    }
    $doc = (string) $rc->getDocComment();
    if ($doc === '') {
        return '';
    }
    $lines = [];
    foreach (explode("\n", $doc) as $line) {
        $l = trim($line);
        $l = preg_replace('#^[/*]+\s?#', '', $l) ?? '';
        $l = trim($l);
        if ($l === '' || str_starts_with($l, '@')) {
            continue;
        }
        $lines[] = $l;
    }
    return implode(' ', $lines);
}

/** 把默认值转成可读字符串 */
function dumpValue(mixed $v, int $depth = 0): string
{
    if ($v === null) {
        return 'null';
    }
    if (is_bool($v)) {
        return $v ? 'true' : 'false';
    }
    if (is_string($v)) {
        if ($v === '') {
            return "''";
        }
        return mb_strlen($v) > 40 ? "'" . mb_substr($v, 0, 40) . "…'" : "'" . $v . "'";
    }
    if (is_int($v) || is_float($v)) {
        return (string) $v;
    }
    if (is_array($v)) {
        if ($v === []) {
            return '[]';
        }
        if ($depth >= 1) {
            $keys = array_slice(array_keys($v), 0, 6);
            return '[' . implode(', ', array_map(fn ($k) => (is_int($k) ? '' : $k . ':'), $keys)) . '…]';
        }
        $parts = [];
        $i = 0;
        foreach ($v as $k => $vv) {
            if ($i++ >= 8) {
                $parts[] = '…';
                break;
            }
            $parts[] = (is_int($k) ? '' : $k . ': ') . dumpValue($vv, $depth + 1);
        }
        return '[' . implode(', ', $parts) . ']';
    }
    if ($v instanceof Closure) {
        return 'Closure';
    }
    if (is_object($v)) {
        return '\\' . $v::class;
    }
    return gettype($v);
}

/** 推断参数语义类型 */
function guessType(mixed $v): string
{
    if (is_bool($v)) {
        return 'bool';
    }
    if (is_int($v)) {
        return 'int';
    }
    if (is_float($v)) {
        return 'float';
    }
    if (is_string($v)) {
        return 'string';
    }
    if (is_array($v)) {
        if ($v === []) {
            return 'array';
        }
        return array_is_list($v) ? 'array(索引)' : 'array(关联)';
    }
    return is_object($v) ? 'object' : 'mixed';
}

$methodDocs = parseMethodDocs(__DIR__ . '/../src/XfAdmin.php');
$list = XfAdmin::componentList();

$out = [
    'generated_at' => date('Y-m-d H:i:s'),
    'version'      => XfAdmin::VERSION,
    'total'        => count($list),
    'components'   => [],
];

foreach ($list as $alias => $class) {
    $row = [
        'alias'   => $alias,
        'class'   => ltrim($class, '\\'),
        'summary' => $methodDocs[$alias] ?? classSummary($class),
        'assets'  => [],
        'params'  => [],
        'methods' => [],
    ];

    // 文件路径
    try {
        $rc       = new ReflectionClass($class);
        $row['file'] = str_replace(dirname(__DIR__) . '/', '', (string) $rc->getFileName());
        $row['lines'] = $rc->getEndLine() - $rc->getStartLine() + 1;
        // 分类：Components 下的第一段目录
        if (preg_match('#Components\\\\(\w+)\\\\#', (string) $class, $mm)) {
            $row['group'] = $mm[1];
        } else {
            $row['group'] = 'Other';
        }
        foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->getDeclaringClass()->getName() === $rc->getName() && ! str_starts_with($m->getName(), '__')) {
                $row['methods'][] = $m->getName();
            }
        }
        // assets()
        $am = $rc->getMethod('assets');
        $row['assets'] = array_values(array_filter((array) $am->invoke($rc->newInstanceWithoutConstructor())));
    } catch (Throwable $e) {
        $row['file']   = '';
        $row['lines']  = 0;
        $row['group']  = 'Other';
    }

    // defaults（通过实例化取 options，即 defaults() 合并结果）
    try {
        $inst = XfAdmin::component($alias, []);
        $opts = $inst->options();
        foreach ($opts as $k => $v) {
            $row['params'][$k] = [
                'default' => dumpValue($v),
                'type'    => guessType($v),
            ];
        }
    } catch (Throwable $e) {
        $row['error'] = $e->getMessage();
    }

    $out['components'][$alias] = $row;
}

$target = $argv[1] ?? '/tmp/xfadmin_components.json';
file_put_contents($target, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

echo "组件总数: {$out['total']}\n";
$byGroup = [];
foreach ($out['components'] as $r) {
    $byGroup[$r['group'] ?? 'Other'] = ($byGroup[$r['group'] ?? 'Other'] ?? 0) + 1;
}
foreach ($byGroup as $g => $n) {
    echo "  {$g}: {$n}\n";
}
echo "已导出: {$target}\n";
echo '大小: ' . round(filesize($target) / 1024, 1) . " KB\n";

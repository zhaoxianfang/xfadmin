<?php
/**
 * xfadmin 文档生成器：扫描全部已注册组件，提取
 *  - 类注释（描述）
 *  - defaults() 选项键 + 默认值 + 行内注释（说明）
 *  - assets() 依赖插件
 *  - 当前类声明的公开链式 setter 方法
 * 生成 components-reference.md 的「组件详细参考」主体。
 */

declare(strict_types=1);

require '/Users/aha/www/xfadmin/src/XfAdmin.php';

use zxf\XfAdmin\XfAdmin;

$base = '/Users/aha/www/xfadmin/src/';

$list = XfAdmin::componentList();
// 去重：同一类可能被多个别名指向（如 dateRange / dateRangePicker）
$seen = [];
$rows = [];
foreach ($list as $alias => $class) {
    if (isset($seen[$class])) { $rows[$seen[$class]]['aliases'][] = $alias; continue; }
    $seen[$class] = $alias;
    $rows[$alias] = ['class' => $class, 'aliases' => [$alias]];
}
$totalClasses = count($rows);

// 分类：从命名空间推断
function categoryOf(string $class): string {
    if (preg_match('/\\\(Layout|Navigation|Grid|UI|Form|Chart|Table|Data|Misc)\\\/', $class, $m)) {
        return $m[1];
    }
    return 'Other';
}
$catLabel = [
    'Layout' => '布局 / 页面', 'Navigation' => '导航', 'Grid' => '栅格',
    'UI' => 'UI 基础', 'Form' => '表单', 'Chart' => '图表 / 地图',
    'Table' => '表格', 'Data' => '数据 / 业务', 'Misc' => '杂项',
];

// ---- 解析类文件源码 ----
function parseSource(string $class, string $base): array {
    $rel = str_replace('zxf\\XfAdmin\\', '', $class);
    $rel = str_replace('\\', '/', $rel) . '.php';
    $file = $base . $rel;
    if (! is_file($file)) return ['desc' => '', 'opts' => [], 'assets' => []];
    $src = file_get_contents($file);
    $out = ['desc' => '', 'opts' => [], 'assets' => []];
    // 类注释
    if (preg_match('#/\*\*(.*?)\*/\s*(?:final\s+)?(?:abstract\s+)?class\s+\w+#s', $src, $m)) {
        $doc = trim($m[1]);
        $lines = array_filter(array_map(fn($l)=>preg_replace('/^\s*\*\s?/', '', $l), explode("\n", $doc)));
        $desc = [];
        foreach ($lines as $l) {
            if (str_starts_with($l, '@')) break;
            if (str_starts_with($l, '```')) break;            // 遇到代码块停止
            if (trim($l) === '') break;                         // 遇到空行（段落结束）停止
            if (str_starts_with($l, 'XfAdmin::') || str_starts_with($l, 'echo XfAdmin')) break;
            $desc[] = $l;
        }
        $out['desc'] = implode(' ', $desc);
    }
    // defaults() 选项 + 行内注释（捕获源码数组字面量，避免运行时依赖 Laravel）
    if (preg_match('/protected\s+function\s+defaults\s*\(\)\s*(?::\s*\w+)?\s*\{/s', $src, $mm, PREG_OFFSET_CAPTURE)) {
        $start = $mm[0][1];
        $body = extractBrace($src, $start);
        if (preg_match('/return\s*(array\s*)?\[/s', $body, $rm, PREG_OFFSET_CAPTURE)) {
            $base = $rm[0][1];
            $arrStart = $base + strpos(substr($body, $base), '[');
            $arr = extractArr($body, $arrStart);
            // 整段源码字面量（用于「默认值（源码）」）
            $out['defaultsSrc'] = trim($arr);
            // 逐行扫描键值对 + 行内注释 + 尽量抓取字面默认值
            $lines = explode("\n", $arr);
            foreach ($lines as $ln) {
                if (preg_match("/^\s*['\"]?(\w+)['\"]?\s*=>/u", $ln, $km)) {
                    $key = $km[1];
                    $comment = '';
                    if (preg_match('/(\/\/|#)\s?(.*)$/', $ln, $cm)) $comment = trim($cm[2]);
                    // 默认值字面：截取 => 之后、注释之前的 token
                    $def = '';
                    if (preg_match("/=>\s*(.+?)(\/\/|#|$)/u", $ln, $dm)) {
                        $tok = trim($dm[1]);
                        $tok = rtrim($tok, ',');
                        if (preg_match("/^(['\"][^'\"]*['\"]|\d+|true|false|null|\[\])$/u", $tok)) {
                            $def = $tok;
                        } elseif (preg_match("/^\[.*\]$/us", $tok) && mb_strlen($tok) <= 120) {
                            $def = $tok;
                        }
                    }
                    $out['opts'][$key] = ['comment' => $comment, 'default' => $def];
                }
            }
        }
    }
    // assets() 依赖
    if (preg_match('/protected\s+function\s+assets\s*\(\)\s*(?::\s*\w+)?\s*\{/s', $src, $am, PREG_OFFSET_CAPTURE)) {
        $start = $am[0][1];
        $body = extractBrace($src, $start);
        if (preg_match('/return\s*(array\s*)?\[/s', $body, $rm, PREG_OFFSET_CAPTURE)) {
            $arrStart = $rm[0][1] + strpos(substr($body, $rm[0][1]), '[');
            $arr = extractArr($body, $arrStart);
            if (preg_match_all("/['\"]([^'\"]+)['\"]/", $arr, $asm)) {
                $out['assets'] = array_values(array_unique($asm[1]));
            }
        }
    }
    return $out;
}

function extractBrace(string $s, int $openPos): string {
    // openPos 指向 '{'
    $depth = 0; $len = strlen($s);
    for ($i = $openPos; $i < $len; $i++) {
        $c = $s[$i];
        if ($c === '{') $depth++;
        elseif ($c === '}') { $depth--; if ($depth === 0) return substr($s, $openPos, $i - $openPos + 1); }
    }
    return substr($s, $openPos);
}

/** 平衡提取 [...] 数组字面量（openPos 指向 '['） */
function extractArr(string $s, int $openPos): string {
    $depth = 0; $len = strlen($s);
    for ($i = $openPos; $i < $len; $i++) {
        $c = $s[$i];
        if ($c === '[') $depth++;
        elseif ($c === ']') { $depth--; if ($depth === 0) return substr($s, $openPos, $i - $openPos + 1); }
    }
    return substr($s, $openPos);
}

// 执行 defaults() 拿到真实默认值
function realDefaults(string $class): array {
    try {
        $inst = $class::make([]);
        $r = new ReflectionMethod($class, 'defaults');
        $r->setAccessible(true);
        $vals = $r->invoke($inst);
        return is_array($vals) ? $vals : [];
    } catch (\Throwable $e) { return []; }
}

function isAssoc(array $a): bool {
    if ($a === []) return false;
    $i = 0;
    foreach ($a as $k => $v) { if ($k !== $i++) return true; }
    return false;
}

function typeOf($v): string {
    if (is_bool($v)) return 'bool';
    if (is_int($v)) return 'int';
    if (is_float($v)) return 'float';
    if (is_string($v)) return 'string';
    if (is_array($v)) return isAssoc($v) ? 'array<assoc>' : 'array<list>';
    if ($v === null) return 'null';
    if ($v instanceof Closure) return 'Closure';
    if (is_object($v)) return 'object<' . get_class($v) . '>';
    return 'mixed';
}

function shortVal($v, int $depth = 0): string {
    if ($v instanceof Closure) return 'Closure';
    if (is_object($v)) return 'object<' . get_class($v) . '>';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_null($v)) return 'null';
    if (is_int($v) || is_float($v)) return (string)$v;
    if (is_string($v)) {
        $s = $v;
        if (mb_strlen($s) > 60) $s = mb_substr($s, 0, 57) . '...';
        return '"' . str_replace(["\r","\n"], ['\r','\n'], $s) . '"';
    }
    if (is_array($v)) {
        $n = count($v);
        if ($n === 0) return '[]';
        if ($depth >= 1) return isAssoc($v) ? "array{…$n keys}" : "array[$n]";
        if (isAssoc($v)) {
            $parts = [];
            $i = 0;
            foreach ($v as $k => $vv) {
                if ($i++ >= 6) { $parts[] = "…+$n"; break; }
                $parts[] = "$k: " . shortVal($vv, $depth + 1);
            }
            return '{ ' . implode(', ', $parts) . ' }';
        }
        $parts = [];
        $i = 0;
        foreach ($v as $vv) {
            if ($i++ >= 6) { $parts[] = "…+$n"; break; }
            $parts[] = shortVal($vv, $depth + 1);
        }
        return '[ ' . implode(', ', $parts) . ' ]';
    }
    return (string)$v;
}

// 公共 setter 方法（当前类声明、>=1 参数、非魔术、非构造）
function settersOf(string $class): array {
    try {
        $rc = new ReflectionClass($class);
    } catch (\Throwable $e) { return []; }
    $out = [];
    foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
        if ($m->getDeclaringClass()->getName() !== $class) continue;
        $name = $m->getName();
        if ($m->isStatic()) continue;
        if ($m->isConstructor()) continue;
        if (str_starts_with($name, '__')) continue;
        if ($m->getNumberOfParameters() < 1) continue;
        $out[] = $name;
    }
    sort($out);
    return $out;
}

// ---- 生成 ----
$parts = [];
$idx = 1;
$cats = [];
foreach ($rows as $alias => $info) {
    $class = $info['class'];
    $cat = categoryOf($class);
    $cats[$cat][] = $alias;
    $meta = parseSource($class, $base);
    $defaults = realDefaults($class);
    $setters = settersOf($class);
    $aliases = array_values(array_diff($info['aliases'], [$alias]));

    $b = [];
    $b[] = "## {$alias}";
    if ($aliases) $b[] = "_别名：_ `" . implode('`, `', $aliases) . "`";
    $b[] = "";
    $b[] = "> 分类：**" . ($catLabel[$cat] ?? $cat) . "** · 类：`{$class}`";
    $b[] = "";
    $desc = trim($meta['desc']);
    if ($desc !== '') $b[] = $desc . "\n";
    $b[] = "**依赖资源**：" . ($meta['assets'] ? '`' . implode('`, `', $meta['assets']) . '`' : '无') . "\n";

    // 选项表：仅取真实标识符键（过滤 '' / 中文占位键）
    $keysParsed = $meta['opts'];
    $wordKeys = array_keys($keysParsed);
    if (count($wordKeys) >= 3) {
        $b[] = "### 选项（defaults）\n";
        $b[] = "| 键 | 默认值 | 说明 |";
        $b[] = "|----|--------|------|";
        foreach ($wordKeys as $k) {
            $def = $keysParsed[$k]['default'] ?: '…';
            $note = $keysParsed[$k]['comment'] ?: '—';
            $b[] = "| `{$k}` | `{$def}` | " . $note . " |";
        }
        $b[] = "";
        // 源码字面量（可选，短则展示）
        $srcLines = explode("\n", $meta['defaultsSrc'] ?? '');
        if (count($srcLines) <= 40 && mb_strlen($meta['defaultsSrc'] ?? '') <= 900) {
            $src2 = rtrim($meta['defaultsSrc'], " \t\n\r;");
            $b[] = "<details><summary>查看 defaults() 源码字面量</summary>\n";
            $b[] = "```php\n" . $src2 . "\n```\n</details>\n";
        }
    } else {
        $b[] = "_本组件的 `defaults()` 以示例数据 / 内容槽位为主，未暴露离散配置键；可用选项与结构见下方「默认值（源码）」，或直接传入通用 `id` / `class` / `attributes`。_\n";
        // 展示源码字面量（截断过长）
        $src = rtrim($meta['defaultsSrc'] ?? '', " \t\n\r;");
        $lines = explode("\n", $src);
        if (count($lines) > 60) {
            $src = implode("\n", array_slice($lines, 0, 60)) . "\n// … 省略 " . (count($lines) - 60) . " 行 …";
        }
        $b[] = "### 默认值（源码）\n";
        $b[] = "```php\n" . $src . "\n```\n";
    }

    // setter
    if ($setters) {
        $b[] = "### 链式方法\n";
        $b[] = "```php\n->" . implode("()\n->", $setters) . "()\n```\n";
    }

    // 示例
    $b[] = "### 示例\n";
    $b[] = "```php\necho XfAdmin::{$alias}([\n";
    if ($wordKeys) {
        $i = 0;
        foreach ($wordKeys as $k) {
            if ($i++ >= 10) { $b[] = "    // … 其余选项见上表 / 默认值（源码）"; break; }
            $def = $keysParsed[$k]['default'];
            if ($def === '' || $def === '…' || $def === null) {
                $b[] = "    '{$k}' => …, // " . ($keysParsed[$k]['comment'] ?: '见说明');
            } else {
                $b[] = "    '{$k}' => {$def},\n";
            }
        }
    } else {
        $b[] = "    // 内容 / 数据由描述与默认值（源码）决定，例如传入 'data' / 'content' / 'items' 等槽位\n";
    }
    $b[] = "]);\n```\n";

    $parts[$alias] = implode("\n", $b);
}

/**
 * 把真实 defaults 渲染为缩进 PHP 数组字面量，深/长嵌套截断
 */
function ppDump($v, int $indent, int $depth): string {
    $pad = str_repeat('    ', $indent);
    $pad2 = str_repeat('    ', $indent + 1);
    if (is_array($v)) {
        if ($v === []) return '[]';
        if ($depth >= 3) {
            return isAssoc($v) ? "'… (对象型数组, " . count($v) . " 项)'" : "'… (列表, " . count($v) . " 项)'";
        }
        $assoc = isAssoc($v);
        $lines = [];
        $i = 0;
        foreach ($v as $k => $vv) {
            if (++$i > 12) { $lines[] = $pad2 . "// … 共 " . count($v) . " 项"; break; }
            $key = $assoc ? (is_int($k) ? $k : "'" . addcslashes((string)$k, "'\\") . "'") . ' => ' : '';
            $lines[] = $pad2 . $key . ppDump($vv, $indent + 1, $depth + 1);
        }
        return "[\n" . implode(",\n", $lines) . "\n" . $pad . "]";
    }
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_null($v)) return 'null';
    if (is_int($v) || is_float($v)) return (string)$v;
    if (is_string($v)) {
        $s = $v;
        if (mb_strlen($s) > 80) $s = mb_substr($s, 0, 77) . '…';
        return "'" . addcslashes($s, "'\\") . "'";
    }
    if ($v instanceof Closure) return 'function () { /* … */ }';
    if (is_object($v)) return '/* ' . get_class($v) . ' 实例 */';
    return var_export($v, true);
}

// 分类目录
$toc = [];
$toc[] = "## 分类索引\n";
foreach (['Layout','Navigation','Grid','UI','Form','Chart','Table','Data','Misc'] as $c) {
    if (empty($cats[$c])) continue;
    $toc[] = "- **" . ($catLabel[$c] ?? $c) . "**（" . count($cats[$c]) . "）";
    foreach ($cats[$c] as $a) {
        $toc[] = "  - [`{$a}`](#" . anchor($a) . ")";
    }
}
$toc[] = "";

$totalAliases = count($list);
$header = "# 组件详细参考（自动生成 · 全量 {$totalClasses} 个组件 / {$totalAliases} 个别名）\n\n";
$header .= "> 本文档由 `tools/gen_docs.php` 扫描全部已注册组件自动生成，列出每个组件的别名、分类、类、描述、依赖资源、全部 `defaults()` 选项（含类型 / 默认值 / 行内说明）、链式方法与实际调用示例。\n";
$header .= "> 调用统一形式：`XfAdmin::<alias>(array \$options)`。所有组件均支持通用键 `id` / `class` / `attributes`。\n";
$header .= "> 资源前缀统一为 `zxf/xfadmin`，无需发布即可在 `demo/` 中直接加载。返回 → [组件总览](components.md)\n\n";

file_put_contents('/Users/aha/www/xfadmin/docs/components-reference.md', $header . implode("\n", $toc) . "\n" . implode("\n", $parts));

echo "TOTAL_CLASSES=$totalClasses\n";
echo "ALIASES=" . count($list) . "\n";
foreach (['Layout','Navigation','Grid','UI','Form','Chart','Table','Data','Misc'] as $c) {
    if (!empty($cats[$c])) echo "$c=" . count($cats[$c]) . "\n";
}

function anchor(string $alias): string {
    $a = strtolower($alias);
    $a = preg_replace('/[^a-z0-9]+/', '-', $a);
    $a = trim($a, '-');
    return $a;
}

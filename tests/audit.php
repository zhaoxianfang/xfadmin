<?php

declare(strict_types=1);
/**
 * XfAdmin 扩展包静态审计
 *
 * 三道防线（均可离线运行，`php tests/audit.php`）：
 *   1. 资源完整性   —— 组件 assets() 声明的插件必须已注册；PLUGINS 声明的文件必须真实存在
 *   2. 注入模糊审计 —— 对每个组件的「文本展示字段」注入 payload，检测输出是否未转义
 *   3. 健壮性审计   —— 极端/非法输入不得触发 PHP 致命错误
 *   4. 前端契约审计 —— 校验 xfadmin.js / xfadmin.css 中已修复的关键点仍然存在
 *
 * 退出码：0 = 全部通过，1 = 存在缺陷。
 */

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'zxf\\XfAdmin\\')) {
        $f = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen('zxf\\XfAdmin\\'))) . '.php';
        if (is_file($f)) {
            require $f;
        }
    }
});

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

$root = dirname(__DIR__);
$fail = 0;
$pass = 0;

function ok(string $name): void
{
    global $pass;
    $pass++;
    echo "  ✔ {$name}" . PHP_EOL;
}

function bad(string $name, string $detail = ''): void
{
    global $fail;
    $fail++;
    echo "  ✘ {$name}" . ($detail !== '' ? "  → {$detail}" : '') . PHP_EOL;
}

function check(string $name, bool $cond, string $detail = ''): void
{
    $cond ? ok($name) : bad($name, $detail);
}

/* ------------------------------------------------------------------ */
/* 1. 资源完整性                                                        */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== 资源完整性（组件声明 ↔ PLUGINS 注册 ↔ 文件存在） ==' . PHP_EOL;

$components = XfAdmin::componentList();
check('组件注册表非空', count($components) > 100, '实际 ' . count($components) . ' 个');

$missingPlugin = [];
$assetErrors   = [];
foreach ($components as $alias => $class) {
    if (! class_exists($class)) {
        $assetErrors[] = "{$alias}: 类 {$class} 不存在";
        continue;
    }
    // assets() 是 protected，用反射取出声明依赖
    try {
        $ref = new ReflectionMethod($class, 'assets');
        $ref->setAccessible(true);
        $obj = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $deps = (array) $ref->invoke($obj);
    } catch (Throwable $e) {
        continue;
    }
    foreach ($deps as $dep) {
        if (! array_key_exists($dep, Assets::PLUGINS)) {
            $missingPlugin[] = "{$alias} → {$dep}";
        }
    }
}
check('组件 assets() 声明的插件均已注册', $missingPlugin === [], implode('; ', array_slice($missingPlugin, 0, 8)));

$missingFile = [];
foreach (Assets::PLUGINS as $name => $def) {
    foreach (['css', 'js'] as $type) {
        foreach ($def[$type] ?? [] as $file) {
            // PLUGINS 中的路径均相对于 resources/assets（js/ 前缀已在路径内）
            $path = $root . '/resources/assets/' . ltrim($file, '/');
            if (! is_file($path)) {
                $missingFile[] = "{$name}:{$file}";
            }
        }
    }
}
check('PLUGINS 声明的资源文件均存在', $missingFile === [], implode('; ', array_slice($missingFile, 0, 8)));
check('组件类文件均可加载', $assetErrors === [], implode('; ', array_slice($assetErrors, 0, 5)));

/* ------------------------------------------------------------------ */
/* 2. 注入模糊审计                                                      */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== 注入模糊审计（文本字段 ↔ 输出转义） ==' . PHP_EOL;

const PAYLOAD = '"><xfxss-payload>';

/** 明确允许原样输出 HTML 的内容槽位（调用方需自行保证可信） */
const RAW_KEYS = [
    'body', 'content', 'slot', 'head', 'scripts', 'html', 'raw', 'action', 'actions',
    'append', 'prepend', 'before_form', 'after_form', 'beforeForm', 'afterForm',
    'left', 'right', 'toggle', 'menu', 'below', 'above', 'footer', 'caption',
    'prefix_html', 'suffix_html', 'custom_html', 'extra_html', 'item_html',
    'before', 'after', 'buttons', 'links', 'toolbar', 'dropdown',
];

/** 组件级豁免：Popover/Tooltip 的 text 是触发元素内容；editor.value 是富文本正文，二者设计上原样输出 */
const RAW_EXEMPT = [
    'popover' => ['text'],
    'tooltip' => ['text'],
    'editor'  => ['value'],
    // Terms.intro / accept 是明确声明的 HTML 槽位（见类注释：「前言 HTML」「或 HTML」）
    'terms'   => ['intro', 'accept'],
    // AuthPage 的 captcha 为字符串时按「web component HTML」原样输出（见类注释）
    'authPage' => ['captcha'],
    'signIn'   => ['captcha'],
    'signUp'   => ['captcha'],
    'resetPass' => ['captcha'],
    'newPass'  => ['captcha'],
    'twoFactor' => ['captcha'],
    'lockScreen' => ['captcha'],
    'deleteAccount' => ['captcha'],
    'loginPin' => ['captcha'],
    // DashboardGrid.bottom 是 HTML 内容槽位（传入 Tabs 等组件渲染出的字符串 / 开发者自定义 HTML），按 raw 原样输出
    'dashboardGrid' => ['bottom'],
    // ReportPage.filters / table 与 SearchResults.pagination 同为 HTML 内容槽位（Form/DataTable/Pagination 组件实例或自定义 HTML）
    'reportPage'    => ['filters', 'table'],
    'searchResults' => ['pagination'],
];

$violations = [];
$crashes    = [];

foreach ($components as $alias => $class) {
    if (! class_exists($class) || ! is_subclass_of($class, Component::class)) {
        continue;
    }
    $defaults = [];
    try {
        $ref = new ReflectionMethod($class, 'defaults');
        $ref->setAccessible(true);
        $obj = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $defaults = (array) $ref->invoke($obj);
    } catch (Throwable $e) {
        continue;
    }
    $exempt = RAW_EXEMPT[$alias] ?? [];

    foreach (array_keys($defaults) as $key) {
        if (in_array($key, RAW_KEYS, true) || in_array($key, $exempt, true)) {
            continue;
        }
        // 只注入「标量或 null」默认值的键（结构化键由专项用例覆盖）
        if ($defaults[$key] !== null && ! is_scalar($defaults[$key])) {
            continue;
        }
        Assets::reset();
        try {
            $html = (string) XfAdmin::{$alias}([$key => PAYLOAD]);
        } catch (Throwable $e) {
            // 渲染本身抛异常说明健壮性有问题（第 3 节单独统计），此处不计为 XSS
            $crashes[] = "{$alias}.{$key}: " . get_class($e) . ' ' . $e->getMessage();
            continue;
        }
        if (str_contains($html, '<xfxss-payload')) {
            $violations[] = "{$alias}.{$key}";
        }
    }
}

check('文本字段注入 payload 均未裸输出', $violations === [], implode('; ', array_slice($violations, 0, 12)));
check('注入未引发渲染异常', $crashes === [], implode('; ', array_slice($crashes, 0, 6)));

/* ------------------------------------------------------------------ */
/* 3. 健壮性审计（历史缺陷回归）                                        */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== 健壮性审计（非法输入不崩溃 / 历史缺陷回归） ==' . PHP_EOL;

function noCrash(string $name, callable $fn): void
{
    try {
        $fn();
        ok($name);
    } catch (Throwable $e) {
        bad($name, get_class($e) . ': ' . $e->getMessage());
    }
}

noCrash('FormElements cols=0 不触发除零', function () {
    XfAdmin::formElements(['sections' => [['title' => 't', 'cols' => 0, 'items' => [['name' => 'a']]]]]);
});
noCrash('CommandPalette 标量命令项不触发 TypeError', function () {
    (string) XfAdmin::commandPalette(['commands' => ['新建用户']]);
});
noCrash('StatCard 非法断点键被过滤', function () {
    $h = (string) XfAdmin::statCard(['title' => 't', 'value' => '1', 'width' => ['x" onload="alert(1)' => 3]]);
    if (str_contains($h, 'onload=')) {
        throw new RuntimeException('非法断点键未被过滤');
    }
});
noCrash('FileManager 非法断点键被过滤', function () {
    $h = (string) XfAdmin::fileManager(['files' => [['name' => 'a']], 'cols' => ['x" onload="alert(1)' => 3]]);
    if (str_contains($h, 'onload=')) {
        throw new RuntimeException('非法断点键未被过滤');
    }
});
noCrash('ProductsGrid 非法列数被夹紧', function () {
    $h = (string) XfAdmin::productsGrid(['products' => [], 'columns' => ['x" onload="a', 99]]);
    if (str_contains($h, 'onload=') || str_contains($h, 'row-cols-xxl-99')) {
        throw new RuntimeException('列数未夹紧');
    }
});
noCrash('Marketplace 非法列数被夹紧', function () {
    $h = (string) XfAdmin::marketplace(['products' => [], 'columns' => ['x" onload="a', 99]]);
    if (str_contains($h, 'onload=')) {
        throw new RuntimeException('列数未夹紧');
    }
});

// style 注入：height/ratio/cover 等被拼进 style 属性的值必须走白名单
noCrash('ChatBox 高度拒绝分号注入', function () {
    $h = (string) XfAdmin::chatBox(['height' => '10px;position:fixed;inset:0']);
    if (str_contains($h, 'position:fixed')) {
        throw new RuntimeException('style 注入未被拦截');
    }
});
noCrash('Scrollspy 高度拒绝分号注入', function () {
    $h = (string) XfAdmin::scrollspy(['items' => [['label' => 'a', 'content' => 'b']], 'height' => '10px;position:fixed']);
    if (str_contains($h, 'position:fixed')) {
        throw new RuntimeException('style 注入未被拦截');
    }
});
noCrash('Carousel 高度拒绝分号注入', function () {
    $h = (string) XfAdmin::carousel(['items' => ['a.jpg'], 'height' => '10px;position:fixed']);
    if (str_contains($h, 'position:fixed')) {
        throw new RuntimeException('style 注入未被拦截');
    }
});
noCrash('Gallery 比例拒绝 CSS 变量注入', function () {
    $h = (string) XfAdmin::gallery(['items' => [['src' => 'a.jpg']], 'ratio' => '1;background:url(//evil)']);
    if (str_contains($h, '//evil')) {
        throw new RuntimeException('ratio 注入未被拦截');
    }
});
noCrash('ProfilePage cover 渐变拒绝分号注入', function () {
    $h = (string) XfAdmin::profilePage(['cover' => 'gradient:red;position:fixed']);
    if (str_contains($h, 'position:fixed')) {
        throw new RuntimeException('cover 注入未被拦截');
    }
});
noCrash('AuthPage 按钮 type/variant 受限', function () {
    $h = (string) XfAdmin::authPage(['type' => 'sign-in', 'buttons' => [['type' => 'button" onmouseover="a', 'variant' => 'x" onload="b']]]);
    if (str_contains($h, 'onmouseover=') || str_contains($h, 'onload=')) {
        throw new RuntimeException('按钮属性未受限');
    }
});
noCrash('AuthPage class 选项被转义', function () {
    $h = (string) XfAdmin::authPage(['type' => 'sign-in', 'class' => 'x" onload="alert(1)']);
    if (str_contains($h, 'onload="alert')) {
        throw new RuntimeException('class 未转义');
    }
});
noCrash('StatMiniSparkline delta 不覆盖自定义图标', function () {
    $h = (string) XfAdmin::statMiniSparkline(['label' => 'l', 'value' => 1, 'delta' => 5, 'icon' => 'ti ti-coin']);
    if (! str_contains($h, 'ti ti-coin')) {
        throw new RuntimeException('自定义图标被涨跌箭头覆盖');
    }
});
noCrash('FieldWrapper help 文本被转义', function () {
    $h = (string) XfAdmin::input(['name' => 'a', 'label' => 'L', 'help' => '<img src=x onerror=alert(1)>']);
    if (str_contains($h, '<img src=x')) {
        throw new RuntimeException('help 未转义');
    }
});

/* ------------------------------------------------------------------ */
/* 4. 前端契约审计                                                      */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== 前端契约审计（JS/CSS 关键修复点仍在位） ==' . PHP_EOL;

$js  = (string) file_get_contents($root . '/resources/assets/js/xfadmin.js');
$css = (string) file_get_contents($root . '/resources/assets/css/xfadmin.css');

check('xfadmin.js 通过 node --check 语法校验', (static function () use ($root): bool {
    $node = trim((string) shell_exec('command -v node 2>/dev/null'));
    if ($node === '') {
        return true; // 环境无 node 时跳过，不误报
    }
    $cmd = escapeshellarg($node) . ' --check ' . escapeshellarg($root . '/resources/assets/js/xfadmin.js') . ' 2>&1';
    $out = (string) shell_exec($cmd);

    return trim($out) === '';
})());

check('countdown 过期文案经 escapeHtml 输出', str_contains($js, "escapeHtml(expired)"));
check('countdown 非法目标时间不再常驻定时器', str_contains($js, "if (end == null) return { destroy: function () {} };"));
check('时间线节点文本经 escapeHtml 输出', str_contains($js, "escapeHtml(btn.textContent.trim())"));
check('kanban 持久化使用正确的 request 签名', str_contains($js, "XFAdmin.request(url, { method: 'PATCH', data: payload })"));
check('kanban 持久化接收卡片节点', str_contains($js, 'persistMove(detail, card)'));
check('弹窗移除统一走 disposeModal', str_contains($js, "XFAdmin.disposeModal('xf-dialog-modal')")
    && str_contains($js, "XFAdmin.disposeModal('xf-form-modal')")
    && str_contains($js, "XFAdmin.disposeModal('xf-edit-modal')"));
check('disposeModal 清理残留 backdrop', str_contains($js, "document.querySelectorAll('.modal-backdrop')"));
check('scan() 增量初始化 Bootstrap 增强', str_contains($js, 'if (XFAdmin.initBootstrapExtras) XFAdmin.initBootstrapExtras(root);'));
check('初始化失败允许重试（失败不置位）', str_contains($js, 'el.__xfInited = false;'));
check('preloader 兜底在文件最前面注册', strpos($js, "getElementById('preloader')") < strpos($js, "XFAdmin.register('datatable'"));
check('popoverConfirm 监听在 clean() 中解绑', str_contains($js, "document.removeEventListener('click', onDoc, true); onDoc = null;"));
check('行分组不再强依赖 jQuery', str_contains($js, 'rowNode.insertAdjacentHTML'));
check('apexchart 深拷贝有 try/catch', str_contains($js, 'options = JSON.parse(JSON.stringify(options));')
    && str_contains($js, 'options = Object.assign({}, options);'));
check('captcha 选择器属性已转义', str_contains($js, 'CSS.escape(id)'));
check('promptFields 字段文案经 escapeHtml', str_contains($js, "escapeHtml(f.label || f.name || '')"));
check('图表主题切换时清理已移除实例', str_contains($js, 'window.__xfApex = window.__xfApex.filter(')
    && str_contains($js, 'window.__xfEchartsMeta = window.__xfEchartsMeta.filter('));
check('jQuery 依赖缺失有明确告警', str_contains($js, "xfWarnMissingDep('select2')")
    && str_contains($js, "xfWarnMissingDep('jstree')"));
check('提供组件销毁钩子 destroy/destroyWithin', str_contains($js, 'XFAdmin.destroy = function')
    && str_contains($js, 'XFAdmin.destroyWithin = function'));
check('register 支持第三参销毁函数', str_contains($js, 'XFAdmin.register = function (name, initFn, destroyFn)'));
check('countdown/countup/backtotop 返回 destroy 钩子', str_contains($js, "return { destroy: function () { clearInterval(timer); } };")
    && str_contains($js, "return { destroy: function () { global.removeEventListener('scroll', onScroll); } };")
    && str_contains($js, 'if (rafId) cancelAnimationFrame(rafId);'));
check('DataTable 销毁时反注册 ResizeObserver', str_contains($js, "el.addEventListener('destroy.dt'")
    && str_contains($js, 'XFAdmin._dtResizeObserver.unobserve(container);'));
check('工具条四类交互已接线', str_contains($js, "XFAdmin.register('dt-search'")
    && str_contains($js, "XFAdmin.register('dt-filter'")
    && str_contains($js, "XFAdmin.register('dt-pagesize'")
    && str_contains($js, "XFAdmin.register('dt-views'"));
check('打印作用域限定为 body.xf-printing', str_contains($css, 'body.xf-printing * { visibility: hidden; }')
    && ! str_contains($css, "\n    body * { visibility: hidden; }"));
check('preloader z-index 不高于 toast', ! preg_match('/#preloader\s*\{[^}]*z-index:\s*99999/', $css));
check('select2 下拉层级低于模态框', ! preg_match('/\.select2-dropdown\s*\{[^}]*z-index:\s*1060/', $css));
check('auth-split 无重复定义', substr_count($css, '.auth-split-wrapper { min-height: 100vh; }') === 1);

/* ------------------------------------------------------------------ */
/* 5. HTML 结构完整性                                                  */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== HTML 结构完整性（标签配平 / 无未闭合） ==' . PHP_EOL;

/**
 * 标签配平检查：忽略 void 元素与自闭合标签，返回问题描述数组。
 */
function htmlUnbalanced(string $html): array
{
    $void = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    // 剔除脚本/样式内容与注释（其中的字符串可能含合法 < >）
    $html = (string) preg_replace('#<script\b.*?</script>#si', '', $html);
    $html = (string) preg_replace('#<style\b.*?</style>#si', '', $html);
    $html = (string) preg_replace('#<!--.*?-->#s', '', $html);
    preg_match_all('#<(/?)([a-zA-Z][a-zA-Z0-9]*)[^>]*?(/?)>#s', $html, $m, PREG_SET_ORDER);
    $stack = [];
    foreach ($m as $t) {
        $name = strtolower($t[2]);
        if (in_array($name, $void, true) || ($t[3] ?? '') === '/') {
            continue;
        }
        if ($t[1] !== '/') {
            $stack[] = $name;
            continue;
        }
        $top = array_pop($stack);
        if ($top !== $name) {
            return ["期望 </{$top}> 但遇到 </{$name}>"];
        }
    }

    return $stack === [] ? [] : ['未闭合: ' . implode(' > ', array_slice($stack, 0, 5))];
}

/** 结构性豁免：片段型组件（只输出开标签或不完整结构，由调用方拼接） */
const HTML_EXEMPT = ['col', 'raw'];

$htmlIssues = [];
foreach ($components as $alias => $class) {
    if (in_array($alias, HTML_EXEMPT, true) || ! is_subclass_of($class, Component::class)) {
        continue;
    }
    Assets::reset();
    try {
        $html = (string) XfAdmin::{$alias}([]);
    } catch (Throwable $e) {
        continue;
    }
    $errs = htmlUnbalanced($html);
    if ($errs !== []) {
        $htmlIssues[] = "{$alias}: " . implode('; ', $errs);
    }
}
check('组件输出 HTML 标签均配平', $htmlIssues === [], implode(' | ', array_slice($htmlIssues, 0, 10)));

/* ------------------------------------------------------------------ */
/* 6. 可访问性 / HTML 有效性基线                                        */
/* ------------------------------------------------------------------ */
echo PHP_EOL . '== 可访问性基线（img 必带 alt / 装饰性图标不参与统计） ==' . PHP_EOL;

$altIssues = [];
foreach ($components as $alias => $class) {
    if (! is_subclass_of($class, Component::class)) {
        continue;
    }
    Assets::reset();
    try {
        $html = (string) XfAdmin::{$alias}([]);
    } catch (Throwable $e) {
        continue;
    }
    preg_match_all('#<img\b[^>]*>#si', $html, $im);
    foreach ($im[0] as $tag) {
        if (! preg_match('#\balt\s*=#i', $tag)) {
            $altIssues[] = $alias;
            break;
        }
    }
}
$altIssues = array_values(array_unique($altIssues));
check('所有 <img> 均带 alt 属性', $altIssues === [], implode(', ', array_slice($altIssues, 0, 12)));

/* ------------------------------------------------------------------ */
echo PHP_EOL . ($fail === 0
    ? "ALL AUDIT PASSED（{$pass} 项）" . PHP_EOL
    : "AUDIT FAILED：{$fail} 项未通过 / {$pass} 项通过" . PHP_EOL);

exit($fail === 0 ? 0 : 1);

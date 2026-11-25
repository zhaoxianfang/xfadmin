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
        $f = __DIR__.'/../src/'.str_replace('\\', '/', substr($class, strlen('zxf\\XfAdmin\\'))).'.php';
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
    echo "  ✔ {$name}".PHP_EOL;
}

function bad(string $name, string $detail = ''): void
{
    global $fail;
    $fail++;
    echo "  ✘ {$name}".($detail !== '' ? "  → {$detail}" : '').PHP_EOL;
}

function check(string $name, bool $cond, string $detail = ''): void
{
    $cond ? ok($name) : bad($name, $detail);
}

/* ------------------------------------------------------------------ */
/* 1. 资源完整性 */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== 资源完整性（组件声明 ↔ PLUGINS 注册 ↔ 文件存在） =='.PHP_EOL;

$components = XfAdmin::componentList();
check('组件注册表非空', count($components) > 100, '实际 '.count($components).' 个');

$missingPlugin = [];
$assetErrors = [];
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
            $path = $root.'/resources/assets/'.ltrim($file, '/');
            if (! is_file($path)) {
                $missingFile[] = "{$name}:{$file}";
            }
        }
    }
}
check('PLUGINS 声明的资源文件均存在', $missingFile === [], implode('; ', array_slice($missingFile, 0, 8)));
check('组件类文件均可加载', $assetErrors === [], implode('; ', array_slice($assetErrors, 0, 5)));

/* ------------------------------------------------------------------ */
/* 2. 注入模糊审计 */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== 注入模糊审计（文本字段 ↔ 输出转义） =='.PHP_EOL;

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
    'editor' => ['value'],
    // Terms.intro / accept 是明确声明的 HTML 槽位（见类注释：「前言 HTML」「或 HTML」）
    'terms' => ['intro', 'accept'],
    // AuthPage 的 captcha 为字符串时按「web component HTML」原样输出（见类注释）
    'authPage' => ['captcha'],
    'signIn' => ['captcha'],
    'signUp' => ['captcha'],
    'resetPass' => ['captcha'],
    'newPass' => ['captcha'],
    'twoFactor' => ['captcha'],
    'lockScreen' => ['captcha'],
    'deleteAccount' => ['captcha'],
    'loginPin' => ['captcha'],
    // DashboardGrid.bottom 是 HTML 内容槽位（传入 Tabs 等组件渲染出的字符串 / 开发者自定义 HTML），按 raw 原样输出
    'dashboardGrid' => ['bottom'],
    // ReportPage.filters / table 与 SearchResults.pagination 同为 HTML 内容槽位（Form/DataTable/Pagination 组件实例或自定义 HTML）
    'reportPage' => ['filters', 'table'],
    'searchResults' => ['pagination'],
];

$violations = [];
$crashes = [];

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
            $crashes[] = "{$alias}.{$key}: ".get_class($e).' '.$e->getMessage();

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
/* 3. 健壮性审计（历史缺陷回归） */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== 健壮性审计（非法输入不崩溃 / 历史缺陷回归） =='.PHP_EOL;

function noCrash(string $name, callable $fn): void
{
    try {
        $fn();
        ok($name);
    } catch (Throwable $e) {
        bad($name, get_class($e).': '.$e->getMessage());
    }
}

/** 把 PHP Warning/Notice 提升为可断言结果：数组型配置传标量时 foreach 不得告警 */
function noPhpWarning(string $name, callable $fn): void
{
    $warnings = [];
    set_error_handler(static function (int $no, string $str) use (&$warnings): bool {
        $warnings[] = $str;

        return true; // 抑制输出，统一在断言中报告
    }, E_WARNING | E_NOTICE);
    try {
        $fn();
    } finally {
        restore_error_handler();
    }
    check($name, $warnings === [], implode('; ', array_slice($warnings, 0, 3)));
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

// 数组型默认键传标量：取值处已归一化，foreach 不得触发 Warning/Notice
noPhpWarning('Footer links 传标量不触发 PHP Warning', function () {
    $h = (string) XfAdmin::footer(['links' => 'not-an-array']);
    if (! str_contains($h, '<footer')) {
        throw new RuntimeException('Footer 未正常渲染');
    }
});
noPhpWarning('Footer groups 传标量不触发 PHP Warning', function () {
    $h = (string) XfAdmin::footer(['groups' => 'not-an-array']);
    if (! str_contains($h, '<footer')) {
        throw new RuntimeException('Footer 未正常渲染');
    }
});

// 数组元素内的 url 属性：safeUrl 只做协议白名单，必须再经 escape() 转义 HTML 属性
$urlPayload = '" onmouseover="alert(1)';
$urlClean = static function (string $html): bool {
    // 属性逃逸后必然出现裸 onmouseover="alert；正确转义后只应留下 &quot; 实体
    return ! str_contains($html, 'onmouseover="alert') && str_contains($html, '&quot;');
};
check('Footer groups.items[].url 注入已转义', $urlClean((string) XfAdmin::footer([
    'groups' => [['title' => 'G', 'items' => [['label' => 'L', 'url' => $urlPayload]]]],
])));
check('Footer links[].url 注入已转义', $urlClean((string) XfAdmin::footer([
    'links' => [['label' => 'L', 'url' => $urlPayload]],
])));
check('Footer icp + icp_url 注入已转义', $urlClean((string) XfAdmin::footer([
    'icp' => 'ICP', 'icp_url' => $urlPayload,
])));
check('Footer police + police_url 注入已转义', $urlClean((string) XfAdmin::footer([
    'police' => 'POL', 'police_url' => $urlPayload,
])));

// 其它中危修复点的运行时断言
check('ErrorPage 非法 code 回落 404（路径/文案无注入）', (static function (): bool {
    $h = (string) XfAdmin::errorPage(['code' => '404" onmouseover="alert(1)']);

    return str_contains($h, '404 - ') && ! str_contains($h, 'onmouseover=');
})());
check('EcommerceDashboard 货币符号不被双重转义', (static function (): bool {
    $h = (string) XfAdmin::ecommerceDashboard(['currency' => 'A&B']);

    return str_contains($h, 'A\\u0026B') && ! str_contains($h, 'A\\u0026amp\\u0026B');
})());
$cfg = require $root.'/config/xfadmin.php';
check('config 提供 captcha_url 默认键', array_key_exists('captcha_url', $cfg) && $cfg['captcha_url'] === null);

/* ------------------------------------------------------------------ */
/* 4. 前端契约审计 */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== 前端契约审计（JS/CSS 关键修复点仍在位） =='.PHP_EOL;

$js = (string) file_get_contents($root.'/resources/assets/js/xfadmin.js');
$css = (string) file_get_contents($root.'/resources/assets/css/xfadmin.css');

check('xfadmin.js 通过 node --check 语法校验', (static function () use ($root): bool {
    $node = trim((string) shell_exec('command -v node 2>/dev/null'));
    if ($node === '') {
        return true; // 环境无 node 时跳过，不误报
    }
    $cmd = escapeshellarg($node).' --check '.escapeshellarg($root.'/resources/assets/js/xfadmin.js').' 2>&1';
    $out = (string) shell_exec($cmd);

    return trim($out) === '';
})());

check('countdown 过期文案经 escapeHtml 输出', str_contains($js, 'escapeHtml(expired)'));
check('countdown 非法目标时间不再常驻定时器', str_contains($js, 'if (end == null) return { destroy: function () {} };'));
check('时间线节点文本经 escapeHtml 输出', str_contains($js, 'escapeHtml(btn.textContent.trim())'));
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
check('countdown/countup/backtotop 返回 destroy 钩子', str_contains($js, 'return { destroy: function () { clearInterval(timer); } };')
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

// 弹窗 size 契约：data-xf-size 经浏览器解码后拼入 class，必须走白名单
check('dialog size 使用白名单枚举且非法值回落 lg', str_contains($js, "var XF_DIALOG_SIZES = ['sm', 'lg', 'xl', 'fullscreen']")
    && str_contains($js, "return XF_DIALOG_SIZES.indexOf(value) >= 0 ? value : 'lg';"));
check('formDialog/pageDialog/dialog 三处尺寸拼接均调用 dialogSize', str_contains($js, "dialogSize(opts.size || 'lg')")
    && substr_count($js, "dialogSize(opts.size)") >= 2);
check('data-xf-size 触发点文档标注全量枚举与回落', str_contains($js, 'data-xf-size(sm|lg|xl|fullscreen，非法值回落 lg)'));

// JSDoc / hover / CSS 契约
$p1 = strpos($js, '确认弹窗（全包唯一实现');
$p2 = $p1 !== false ? strpos($js, '气泡卡片（Popover）确认', $p1) : false;
$confirmDoc = $p1 !== false && $p2 !== false ? substr($js, $p1, $p2 - $p1) : '';
check('confirm JSDoc 注释块已闭合', $confirmDoc !== '' && substr_count($confirmDoc, '*/') === 1);
check('单元格 hover 改用事件委托（无实例标记残留）', ! str_contains($js, '__xfHoverBound')
    && str_contains($js, "document.addEventListener('mouseout'")
    && str_contains($js, 'xfHoverEl.contains'));
check('暗色模式非固定列 hover/条纹高亮不被基础背景覆盖', str_contains($css, 'td:not(.xf-dt-sticky):not(:hover)')
    && str_contains($css, 'tr:hover > td:not(.xf-dt-sticky)')
    && str_contains($css, 'tr:nth-of-type(odd) > td:not(.xf-dt-sticky)'));
check('xfadmin.css 大括号配平', substr_count($css, '{') === substr_count($css, '}'));

/* 本轮缺陷整改的前端契约（固定列 / ECharts / destroy / 搜索 / Toast） */
check('固定列总列数取自 DataTables API 并回退 thead', str_contains($js, 'total = api.columns().count();')
    && str_contains($js, 'total = headRow ? headRow.children.length : 0;')
    && ! str_contains($js, "var bodyRow = el.querySelector('tbody tr');"));
check('固定列跳过 colspan 分组/详情行的 sticky 赋值', str_contains($js, 'var isSpanRow = trCells.some(')
    && str_contains($js, 'if (isSpanRow) return;'));
check('ECharts 主题重建后新实例回写并同步镜像', str_contains($js, 'm.inst = inst;')
    && str_contains($js, 'window.__xfEcharts = (window.__xfEchartsMeta || []).map('));
check('destroy.dt 监听 once 化且 sticky 清理移出 RO 分支',
    preg_match("/addEventListener\('destroy\.dt'[\s\S]{0,600}\{ once: true \}\)/", $js) === 1
    && strpos($js, 'XFAdmin.destroyTableSticky(el);') < strpos($js, "if ('ResizeObserver' in window)"));
check('自定义搜索仅 submit 接管，无 keyup Enter 双发', substr_count($js, 'applyCustomSearch();') === 2
    && ! str_contains($js, "e.key === 'Enter') applyCustomSearch()"));
check('Toast 关闭按钮按 variant 白名单选择深浅', str_contains($js, "var lightToastVariants = ['light', 'warning'];"));

// 死配置 / 注释一致性契约
check('ErrorPage/Maintenance/ComingSoon/FormValidation 死配置已移除', (static function () use ($root): bool {
    foreach ([
        'src/Components/Layout/ErrorPage.php',
        'src/Components/Layout/Maintenance.php',
        'src/Components/Layout/ComingSoon.php',
        'src/Components/Form/FormValidation.php',
    ] as $rel) {
        $src = (string) file_get_contents($root.'/'.$rel);
        if (str_contains($src, "'card' => false") || str_contains($src, "'showBuiltin' => true")) {
            return false;
        }
    }

    return true;
})());
check('config/Page 主题注释与 config.js 实际默认值(classic/dark)一致', (static function () use ($root): bool {
    $cfgSrc = (string) file_get_contents($root.'/config/xfadmin.php');
    $pageSrc = (string) file_get_contents($root.'/src/Components/Layout/Page.php');

    return str_contains($cfgSrc, 'skin=classic') && str_contains($cfgSrc, 'menu.color=dark')
        && str_contains($pageSrc, 'skin=classic') && str_contains($pageSrc, 'menu.color=dark');
})());

/* ------------------------------------------------------------------ */
/* 5. HTML 结构完整性 */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== HTML 结构完整性（标签配平 / 无未闭合） =='.PHP_EOL;

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

    return $stack === [] ? [] : ['未闭合: '.implode(' > ', array_slice($stack, 0, 5))];
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
        $htmlIssues[] = "{$alias}: ".implode('; ', $errs);
    }
}
check('组件输出 HTML 标签均配平', $htmlIssues === [], implode(' | ', array_slice($htmlIssues, 0, 10)));

/* ------------------------------------------------------------------ */
/* 6. 可访问性 / HTML 有效性基线 */
/* ------------------------------------------------------------------ */
echo PHP_EOL.'== 可访问性基线（img 必带 alt / 装饰性图标不参与统计） =='.PHP_EOL;

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

/* 声明式组件：data-xf-config 必须 HEX 转义，杜绝配置 JSON 破裂/属性注入 */
$declarativeClasses = [
    'src/Components/UI/RowActions.php',
    'src/Components/Layout/AdminBehaviors.php',
];
$hexOk = true;
foreach ($declarativeClasses as $rel) {
    $src = @file_get_contents(__DIR__.'/../'.$rel);
    if ($src === false
        || ! str_contains($src, 'JSON_HEX_TAG')
        || ! str_contains($src, 'JSON_HEX_AMP')
        || ! str_contains($src, 'JSON_HEX_APOS')
        || ! str_contains($src, 'JSON_HEX_QUOT')) {
        $hexOk = false;
    }
}
check('声明式组件 data-xf-config 统一 HEX 转义', $hexOk);

/* 站点图标：三套完整文档组件均使用共享解析入口，默认项目 /favicon.ico */
$faviconSources = [
    'src/Components/Component.php' => 'faviconUrl',
    'src/Components/Layout/Page.php' => 'faviconUrl',
    'src/Components/Layout/AuthPage.php' => 'faviconUrl',
    'src/Components/Layout/LockScreen.php' => 'faviconUrl',
];
$faviconOk = true;
foreach ($faviconSources as $rel => $needle) {
    $src = @file_get_contents(__DIR__.'/../'.$rel);
    if ($src === false || ! str_contains($src, $needle)) {
        $faviconOk = false;
    }
}
$configSrc = (string) @file_get_contents(__DIR__.'/../config/xfadmin.php');
check('站点图标统一走 faviconUrl() 且配置默认 /favicon.ico', $faviconOk
    && str_contains($configSrc, "'favicon' => '/favicon.ico'")
    && XfAdmin::VERSION === '1.0.4');

/* ------------------------------------------------------------------ */
echo PHP_EOL.($fail === 0
    ? "ALL AUDIT PASSED（{$pass} 项）".PHP_EOL
    : "AUDIT FAILED：{$fail} 项未通过 / {$pass} 项通过".PHP_EOL);

exit($fail === 0 ? 0 : 1);

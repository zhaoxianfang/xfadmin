<?php
// 校验 PLUGINS 声明的所有 js/css 文件是否真实存在；并校验组件 assets() 引用的 key 是否注册。
$assetsFile = __DIR__ . '/../../src/Assets/Assets.php';
$code = file_get_contents($assetsFile);

// 提取 PLUGINS 数组块
if (!preg_match('/public const PLUGINS = \[(.*?)\n    \];/s', $code, $m)) {
    die("cannot find PLUGINS\n");
}
$block = $m[1];
$plugins = [];
// 解析 'key' => [ ... ]
if (!preg_match_all("/'([a-z0-9_-]+)'\s*=>\s*\[([^\[\]]*(?:\[[^\[\]]*\][^\[\]]*)*)\]/s", $block, $km, PREG_SET_ORDER)) {
    die("cannot parse plugins\n");
}
foreach ($km as $mm) {
    $key = $mm[1];
    $cfg = $mm[2];
    $files = [];
    if (preg_match_all("/'(plugins\/[^']+|js\/plugins\/[^']+)'/", $cfg, $fm)) {
        $files = array_merge($files, $fm[1]);
    }
    $plugins[$key] = $files;
}

$base = realpath(__DIR__ . '/../../resources/assets');
$missingFiles = [];
foreach ($plugins as $key => $files) {
    foreach ($files as $file) {
        $path = $base . '/' . ltrim($file, '/');
        if (!file_exists($path)) {
            $missingFiles[] = "$key -> $file";
        }
    }
}

// 注册表 key 集合
$registered = array_keys($plugins);

// 扫描组件 assets() 键
$compDir = __DIR__ . '/../../src/Components';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($compDir));
$referenced = [];
foreach ($rii as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $c = file_get_contents($f->getPathname());
        // 找 return ['key1','key2',...] 形式的 assets
        if (preg_match_all("/return\s*\[\s*'([a-z0-9_-]+)'(?:\s*,\s*'([a-z0-9_-]+)')*\s*\];/s", $c, $am)) {
            foreach ($am[1] as $k) if ($k) $referenced[] = $k;
            foreach ($am[2] as $k) if ($k) $referenced[] = $k;
        }
    }
}
$referenced = array_unique($referenced);
$common = ['class','id','style','data','options','type','title','text','name','value','src','href','url','icon','variant','size','items','cols','rows','head','body','footer','label','placeholder','method','action','target','rel','role','tabindex','disabled','checked','selected','multiple','required','readonly','min','max','step','default','config','settings','attr','attrs','html','content','slot','children','fields','columns','html','menu','left','right','toggle','tag','placement','trigger','heading','subtitle','message','user','group','page','modal','table'];
$unknown = [];
foreach ($referenced as $k) {
    if (in_array($k, $common, true)) continue;
    if (!in_array($k, $registered, true) && preg_match('/^[a-z0-9_-]+$/', $k)) {
        $unknown[] = $k;
    }
}

echo "=== MISSING RESOURCE FILES (" . count($missingFiles) . ") ===\n";
echo implode("\n", array_unique($missingFiles)) ?: "(none)\n";
echo "\n=== COMPONENT ASSET KEYS NOT REGISTERED (" . count($unknown) . ") ===\n";
echo implode("\n", array_unique($unknown)) ?: "(none)\n";
echo "\n=== PLUGINS TOTAL: " . count($registered) . " ===\n";

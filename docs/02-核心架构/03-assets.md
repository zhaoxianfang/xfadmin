# 资源与插件系统

XfAdmin 的静态资源由 `zxf\XfAdmin\Assets\Assets`（单例）统一管理：组件声明依赖插件，
管理器解析依赖、去重、按序输出。

> 源码：`src/Assets/Assets.php`

## 1. 核心 API

```php
$assets = XfAdmin::assets();       // 等价 Assets::instance()

// 声明插件（自动解析 deps、去重）
$assets->plugin('datatables', 'select2');

// 直接追加文件（去重）
$assets->css('plugins/foo/foo.css');
$assets->js('plugins/foo/foo.js');

// 内联代码（按 key 或内容去重）
$assets->inlineJs('console.log(1)', 'my-key');
$assets->inlineCss('.x{color:red}', 'my-key');

// 输出
echo XfAdmin::head();       // <head> 内
echo XfAdmin::scripts();    // </body> 前

// 查询
$assets->hasPlugin('datatables');   // true
$assets->cssFiles();                // 已收集的 CSS 相对路径列表
$assets->jsFiles();                 // 已收集的 JS 相对路径列表
$assets->baseUrl();                 // /zxf/xfadmin

// 配置
$assets->setBaseUrl('/static/xfadmin')->setVersion('2.1.0');

// 重置（同请求渲染多个完整页面 / 测试）
$assets->resetCollected();          // 清空收集状态，保留 baseUrl/version
Assets::reset();                    // 全新实例，保留 baseUrl/version
```

## 2. URL 生成规则

```php
public function url(string $path): string
{
    if (preg_match('#^(https?:)?//#', $path)) return $path;   // 绝对 URL 原样返回
    $rel = ltrim($path, '/');

    // 幂等：若路径中已含资源基址，截取其后部分（防拼接两遍）
    $base = ltrim($this->baseUrl, '/');
    if ($base !== '') {
        $marker = $base . '/';
        $pos = strrpos($rel, $marker);
        if ($pos !== false) $rel = substr($rel, $pos + strlen($marker));
        if (str_contains($rel, '?')) $rel = preg_replace('/\?.*$/', '', $rel);
    }

    $url = $this->baseUrl . '/' . $rel;
    return $this->version ? $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . rawurlencode($this->version) : $url;
}
```

| 输入 | 输出（baseUrl=/zxf/xfadmin，version=2.1.0） |
|---|---|
| `css/app.min.css` | `/zxf/xfadmin/css/app.min.css?v=2.1.0` |
| `/zxf/xfadmin/js/xfadmin.js?v=1.0.0` | `/zxf/xfadmin/js/xfadmin.js?v=2.1.0`（幂等） |
| `https://cdn.x.com/a.css` | `https://cdn.x.com/a.css`（原样） |

## 3. 插件注册表 `Assets::PLUGINS`

结构：`name => ['css' => [...], 'js' => [...], 'deps' => [...]]`（路径相对 `resources/assets/`）。

### 3.1 基础与表格

| 插件名 | 内容 | 依赖 |
|---|---|---|
| `jquery` | `plugins/jquery/jquery.min.js` | — |
| `moment` | `plugins/moment/moment.min.js` | — |
| `lucide` | `plugins/lucide/lucide.min.js` | — |
| `datatables` | DataTables 2 + Bootstrap5 主题 + Buttons（jszip/html5/print）+ Responsive + FixedHeader + Select（4 CSS + 13 JS） | — |
| `datatables-pdf` | `pdfmake.min.js`、`vfs_fonts.js` | `datatables` |
| `qrcode` | `js/plugins/qrcode/qrcode.min.js`（表格 `qr` 渲染器按需） | — |

### 3.2 图表与地图

| 插件名 | 内容 | 依赖 |
|---|---|---|
| `apexcharts` | `plugins/apexcharts/apexcharts.min.js` | — |
| `apextree` | `plugins/apextree/apextree.min.js` | — |
| `apexsankey` | 前后 svg 护栏 + `svg.js v3` + `apexsankey.min.js` | 特殊顺序（见 §5） |
| `echarts` | `plugins/echarts/echarts.min.js` | — |
| `svgdotjs` | `plugins/svgdotjs/svg.min.js` | — |
| `jsvectormap` | CSS + JS | — |
| `jsvectormap-world` | `world.js`、`world-merc.js` | `jsvectormap` |
| `leaflet` | `leaflet.css` + `leaflet.js` | — |

### 3.3 表单

| 插件名 | 内容 | 依赖 |
|---|---|---|
| `choices` | CSS + JS | — |
| `select2` | CSS + JS | `jquery` |
| `daterangepicker` | CSS + JS | `jquery`、`moment` |
| `nouislider` | CSS + JS + `wNumb` | — |
| `pickr` | classic/monolith/nano 三种主题 CSS + JS | — |
| `inputmask` | `inputmask.min.js` | — |
| `typeahead` | `typeahead.bundle.min.js` | `jquery`、`handlebars` |
| `handlebars` | `handlebars.min.js` | — |
| `tagify` | CSS + JS | — |

### 3.4 编辑器与上传

| 插件名 | 内容 | 依赖 |
|---|---|---|
| `quill` | core/snow/bubble CSS + JS | — |
| `summernote` | CSS + JS | `jquery` |
| `dropzone` | CSS + JS | — |
| `filepond` | 2 CSS + 5 JS（含 file-encode / validate-size / exif / image-preview） | — |

### 3.5 交互与杂项

| 插件名 | 内容 | 依赖 |
|---|---|---|
| `sweetalert2` | CSS + JS | — |
| `sortablejs` | `Sortable.min.js` | — |
| `simplebar` | CSS + JS | — |
| `masonry` | `masonry.pkgd.min.js` | — |
| `dragsort` | `js/plugins/dragsort/dragsort.js` | — |
| `jstree` | CSS + JS | `jquery` |
| `muuri` | `web-animations` + `muuri` | — |
| `glightbox` | CSS + JS | — |
| `clipboard` | `clipboard.min.js` | — |
| `tourguide` | CSS + JS | — |
| `ladda` | CSS + `spin.min.js` + `ladda.min.js` | — |
| `fullcalendar` | `index.global.min.js` | — |
| `animate` | `animate.min.css` | — |
| `spinkit` | `spinkit.min.css` | — |
| `pdfjs` | `pdf.min.js` | — |
| `tinycon` | `tinycon.min.js` | — |
| `diff` | `diff.min.js` | — |

## 4. 去重算法

```php
public function plugin(string ...$names): self
{
    foreach ($names as $name) {
        if (isset($this->plugins[$name])) continue;      // ① 插件级去重
        $def = self::PLUGINS[$name] ?? null;
        if ($def === null) continue;                     // 未知插件静默忽略
        $this->plugins[$name] = true;
        foreach ($def['deps'] ?? [] as $dep) $this->plugin($dep);   // ② 递归依赖
        foreach ($def['css'] ?? [] as $css) $this->css($css);       // ③ 文件级去重
        foreach ($def['js']  ?? [] as $js)  $this->js($js);
    }
    return $this;
}
```

三级去重：

1. **插件级**：同名插件只处理一次（`$this->plugins[$name]`）；
2. **文件级**：`$css[$path] = true` / `$js[$path] = true`（有序、天然去重）；
3. **内联级**：`$inlineJs[$key ?? '__'.md5($code)] = $code`。

因此：

```php
echo XfAdmin::dataTable([...]);   // 注册 datatables（13 个 JS、4 个 CSS）
echo XfAdmin::dataTable([...]);   // 无重复
echo XfAdmin::table([...]);       // 静态表不依赖插件
// 最终 datatables 资源只输出一次
```

## 5. 输出顺序

### `Assets::head()`

```
1. <script src="js/config.js">                 主题配置（同步，防首屏闪烁）
2. <link css/vendors.min.css>
3. 插件 CSS（按注册顺序）
4. <link css/app.min.css>                      INSPINIA 主题
5. <link css/xfadmin.css>                      包内自定义（最后加载，可覆盖框架）
6. 内联 <style>（按 key/内容去重）
```

### `Assets::scripts()`

```
1. <script src="js/vendors.min.js">
2. 插件 JS（按注册顺序）
3. <script src="js/app.js">                    框架运行时
4. <script src="js/xfadmin.js">                包核心（含 scan 引导）
5. 兜底：head() 之后新注册的 CSS / 内联 CSS
6. 内联 <script>
```

⚠️ **兜底机制**：`head()` 会记录当时已输出的 CSS 列表（`headCssEmitted`），
`scripts()` 对比后补发差异 —— 解决"组件在 head 之后才渲染"导致的样式丢失。
但补发的 CSS 出现在 `</body>` 前，可能引起首屏闪烁，
**推荐先渲染内容再输出 head**（见 [安装文档](../01-快速开始/01-installation.md#34-自定义布局手动接管-head--scripts)）。

## 6. 特殊加载顺序：apexsankey

```php
'apexsankey' => ['js' => [
    'js/plugins/apexsankey/svg-guard-pre.js',    // 前置护栏：备份 window.SVG
    'plugins/svgdotjs/svg.min.js',               // 加载 svg.js v3
    'js/plugins/apexsankey/apexsankey.min.js',   // UMD 加载时闭包捕获 v3
    'js/plugins/apexsankey/svg-guard-post.js',   // 后置护栏：恢复
]],
```

⚠️ **为什么不走 `deps`**：apexcharts 内置旧版 svg.js，两者都占用 `window.SVG`，
同页共存会互相破坏（draggable 缺失 / parser Error）。
必须按「护栏 → v3 → sankey → 恢复」顺序加载。

## 7. 组件如何声明依赖

```php
protected function assets(): array
{
    return ['datatables'];                 // 固定依赖
}

// 条件依赖（Input 组件）
protected function assets(): array
{
    $assets = [];
    if ($this->get('mask')) $assets[] = 'inputmask';
    if ($this->get('tags')) $assets[] = 'tagify';
    return $assets;
}

// DataTable 的条件依赖
// 恒有 datatables；buttons/export 含 pdf → datatables-pdf；
// filter_bar 含 select2/multiple → select2；列渲染器含 qr → qrcode
```

## 8. Laravel 资源自托管

`XfAdminServiceProvider::boot()` 注册（当 `assets_url` 不含 `http(s)://` 时）：

```php
Route::get('{prefix}/{path}', [AssetController::class, 'serve'])
    ->where('path', '(?i).*\.(css|js|mjs|map|json|svg|png|jpe?g|gif|ico|webp|avif|woff2?|ttf|eot|otf)$')
    ->name('xfadmin.assets');
```

| 特性 | 说明 |
|---|---|
| 前缀 | `ltrim(config('xfadmin.assets_url'), '/')`，默认 `zxf/xfadmin` |
| 安全 | 扩展名白名单（20 种）；`realpath` 必须在 `resources/assets` 内 + `is_file` + `is_readable`，否则 404 |
| 缓存 | `ETag = md5(path . ':' . filemtime)`；`If-None-Match` 命中 → **304 空响应**；`Cache-Control: public, max-age=31536000`；`Expires` +1 年 |
| 设计 | 用**控制器方法**而非闭包路由 —— 闭包会隐式绑定容器，导致 `route:cache` 序列化失败 |

## 9. 自定义资源

### 9.1 追加自己的 CSS/JS（临时）

```php
XfAdmin::assets()
    ->css('css/my-custom.css')
    ->js('js/my-custom.js')
    ->inlineJs('console.log("ready")', 'my-init');
```

### 9.2 注册新插件（需改源码常量）

在 `Assets::PLUGINS` 中追加：

```php
'myplugin' => [
    'css' => ['plugins/myplugin/myplugin.css'],
    'js'  => ['plugins/myplugin/myplugin.js'],
    'deps' => ['jquery'],
],
```

然后把文件放到 `resources/assets/plugins/myplugin/`。

> 包内自检脚本 `tools/selftest/asset_check.php` 会校验：
> ① 每个组件 `assets()` 的 key 都已注册在 `PLUGINS`；
> ② `PLUGINS` 声明的每个文件（含 deps 递归）都真实存在。

## 10. 常见资源问题排查

| 现象 | 原因 | 处理 |
|---|---|---|
| 页面无样式、无交互 | 未输出 `head()`/`scripts()`，或资源 404 | 检查 `assets_url` 与实际发布路径是否一致 |
| 资源 404 | 未发布且未注册自托管路由；或 Web 服务器未放行 `/zxf/xfadmin` | 执行 `vendor:publish --tag=xfadmin-assets` |
| 升级后样式没变 | 浏览器缓存 | 修改 `version` 配置（追加 `?v=`） |
| 组件没样式但其它正常 | 该组件的 CSS 在 `head()` 之后注册，被兜底到页脚 | 先渲染内容再输出 head |
| 两个图表库冲突（svg.js） | apexsankey 与 apexcharts 争抢 `window.SVG` | 已内置护栏，确认资源按 `PLUGINS` 顺序加载 |
| 打包后资源路径错 | `assets_url` 与实际部署目录不一致 | 改为 CDN 或正确子路径 |

下一步：[04-安全与转义](04-security.md)

<?php

declare(strict_types=1);

namespace XfAdmin\Assets;

use XfAdmin\Support\Html;

/**
 * 资源管理器（单例）
 *
 * - 组件在渲染时按需声明所依赖的插件（plugin）
 * - 同一插件 / 同一文件无论被多少个组件引用，最终只输出一次
 * - 自动处理插件间依赖（如 select2 依赖 jquery）
 * - head() 输出 CSS 与主题配置脚本；scripts() 输出 JS 与内联初始化脚本
 */
final class Assets
{
    private static ?self $instance = null;

    private string $baseUrl = '/vendor/xfadmin';
    private ?string $version = null;

    /** @var array<string,bool> 已加载插件 */
    private array $plugins = [];
    /** @var array<string,bool> css 文件（有序去重） */
    private array $css = [];
    /** @var array<string,bool> js 文件（有序去重） */
    private array $js = [];
    /** @var array<string,string> 内联 JS（key 去重） */
    private array $inlineJs = [];
    /** @var array<string,string> 内联 CSS（key 去重） */
    private array $inlineCss = [];

    private bool $headRendered = false;
    private bool $scriptsRendered = false;

    /**
     * 插件注册表：name => [css[], js[], deps[]]
     * 路径均相对于资源根目录
     */
    public const PLUGINS = [
        'jquery' => ['js' => ['plugins/jquery/jquery.min.js']],
        'moment' => ['js' => ['plugins/moment/moment.min.js']],

        // 表格
        'datatables' => [
            'css' => [
                'plugins/datatables/buttons.bootstrap5.min.css',
                'plugins/datatables/responsive.bootstrap5.min.css',
                'plugins/datatables/fixedHeader.bootstrap5.min.css',
                'plugins/datatables/select.bootstrap5.min.css',
            ],
            'js' => [
                'plugins/datatables/dataTables.min.js',
                'plugins/datatables/dataTables.bootstrap5.min.js',
                'plugins/datatables/dataTables.buttons.min.js',
                'plugins/datatables/buttons.bootstrap5.min.js',
                'plugins/datatables/jszip.min.js',
                'plugins/datatables/buttons.html5.min.js',
                'plugins/datatables/buttons.print.min.js',
                'plugins/datatables/dataTables.responsive.min.js',
                'plugins/datatables/responsive.bootstrap5.min.js',
                'plugins/datatables/dataTables.fixedHeader.min.js',
                'plugins/datatables/fixedHeader.bootstrap5.min.js',
                'plugins/datatables/dataTables.select.min.js',
                'plugins/datatables/select.bootstrap5.min.js',
            ],
        ],
        'datatables-pdf' => [
            'js'   => ['plugins/datatables/pdfmake.min.js', 'plugins/datatables/vfs_fonts.js'],
            'deps' => ['datatables'],
        ],

        // 图表
        'apexcharts' => ['js' => ['plugins/apexcharts/apexcharts.min.js']],
        'apextree'   => ['js' => ['plugins/apextree/apextree.min.js']],
        'apexsankey' => ['js' => ['js/plugins/apexsankey/apexsankey.min.js'], 'deps' => ['svgdotjs']],
        'echarts'    => ['js' => ['plugins/echarts/echarts.min.js']],
        'svgdotjs'   => ['js' => ['plugins/svgdotjs/svg.min.js']],

        // 地图
        'jsvectormap' => [
            'css' => ['plugins/jsvectormap/jsvectormap.min.css'],
            'js'  => ['plugins/jsvectormap/jsvectormap.min.js'],
        ],
        'jsvectormap-world' => ['js' => ['plugins/jsvectormap/world.js', 'plugins/jsvectormap/world-merc.js'], 'deps' => ['jsvectormap']],
        'leaflet' => ['css' => ['plugins/leaflet/leaflet.css'], 'js' => ['plugins/leaflet/leaflet.js']],

        // 表单
        'choices'  => ['css' => ['plugins/choices/choices.min.css'], 'js' => ['plugins/choices/choices.min.js']],
        'select2'  => ['css' => ['plugins/select2/select2.min.css'], 'js' => ['plugins/select2/select2.min.js'], 'deps' => ['jquery']],
        'daterangepicker' => [
            'css'  => ['plugins/daterangepicker/daterangepicker.css'],
            'js'   => ['plugins/daterangepicker/daterangepicker.js'],
            'deps' => ['jquery', 'moment'],
        ],
        'nouislider' => [
            'css' => ['plugins/nouislider/nouislider.min.css'],
            'js'  => ['plugins/nouislider/nouislider.min.js', 'plugins/wnumb/wNumb.min.js'],
        ],
        'pickr'     => ['css' => ['plugins/pickr/classic.min.css', 'plugins/pickr/monolith.min.css', 'plugins/pickr/nano.min.css'], 'js' => ['plugins/pickr/pickr.min.js']],
        'inputmask' => ['js' => ['plugins/inputmask/inputmask.min.js']],
        'typeahead' => ['js' => ['plugins/typeahead/typeahead.bundle.min.js'], 'deps' => ['jquery', 'handlebars']],
        'handlebars' => ['js' => ['plugins/handlebars/handlebars.min.js']],
        'tagify'    => ['js' => ['js/plugins/tagify/tagify.js']],

        // 编辑器
        'quill' => [
            'css' => ['plugins/quill/quill.core.css', 'plugins/quill/quill.snow.css', 'plugins/quill/quill.bubble.css'],
            'js'  => ['plugins/quill/quill.js'],
        ],
        'summernote' => [
            'css'  => ['plugins/summernote/summernote-bs5.min.css'],
            'js'   => ['plugins/summernote/summernote-bs5.min.js'],
            'deps' => ['jquery'],
        ],

        // 上传
        'dropzone' => ['css' => ['plugins/dropzone/dropzone.css'], 'js' => ['plugins/dropzone/dropzone-min.js']],
        'filepond' => [
            'css' => ['plugins/filepond/filepond.min.css', 'plugins/filepond/filepond-plugin-image-preview.min.css'],
            'js'  => [
                'plugins/filepond/filepond-plugin-file-encode.min.js',
                'plugins/filepond/filepond-plugin-file-validate-size.min.js',
                'plugins/filepond/filepond-plugin-image-exif-orientation.min.js',
                'plugins/filepond/filepond-plugin-image-preview.min.js',
                'plugins/filepond/filepond.min.js',
            ],
        ],

        // 交互 / 杂项
        'sweetalert2'  => ['css' => ['plugins/sweetalert2/sweetalert2.min.css'], 'js' => ['plugins/sweetalert2/sweetalert2.min.js']],
        'sortablejs'   => ['js' => ['plugins/sortablejs/Sortable.min.js']],
        'dragsort'     => ['js' => ['js/plugins/dragsort/dragsort.js']],
        'jstree'       => ['css' => ['plugins/jstree/style.min.css'], 'js' => ['plugins/jstree/jstree.min.js'], 'deps' => ['jquery']],
        'masonry'      => ['js' => ['plugins/masonry/masonry.pkgd.min.js']],
        'muuri'        => ['js' => ['plugins/web-animations/web-animations.min.js', 'plugins/muuri/muuri.min.js']],
        'glightbox'    => ['css' => ['plugins/glightbox/glightbox.min.css'], 'js' => ['plugins/glightbox/glightbox.min.js']],
        'clipboard'    => ['js' => ['plugins/clipboard/clipboard.min.js']],
        'tourguide'    => ['css' => ['plugins/tourguidejs/tour.min.css'], 'js' => ['plugins/tourguidejs/tour.js']],
        'ladda'        => ['css' => ['plugins/ladda/ladda.min.css'], 'js' => ['plugins/ladda/spin.min.js', 'plugins/ladda/ladda.min.js']],
        'fullcalendar' => ['js' => ['plugins/fullcalendar/index.global.min.js']],
        'animate'      => ['css' => ['plugins/animate/animate.min.css']],
        'spinkit'      => ['css' => ['plugins/spinkit/spinkit.min.css']],
        'pdfjs'        => ['js' => ['plugins/pdfjs/pdf.min.js']],
        'tinycon'      => ['js' => ['plugins/tinycon/tinycon.min.js']],
        'diff'         => ['js' => ['plugins/diff/diff.min.js']],
    ];

    private function __construct()
    {
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /** 重置（多次完整页面渲染 / 测试场景） */
    public static function reset(): self
    {
        return self::$instance = new self();
    }

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');

        return $this;
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /** 生成资源完整 URL */
    public function url(string $path): string
    {
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        return $this->version ? $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . rawurlencode($this->version) : $url;
    }

    /** 声明需要某插件（自动解析依赖、去重） */
    public function plugin(string ...$names): self
    {
        foreach ($names as $name) {
            if (isset($this->plugins[$name])) {
                continue;
            }
            $def = self::PLUGINS[$name] ?? null;
            if ($def === null) {
                continue;
            }
            $this->plugins[$name] = true;
            foreach ($def['deps'] ?? [] as $dep) {
                $this->plugin($dep);
            }
            foreach ($def['css'] ?? [] as $css) {
                $this->css($css);
            }
            foreach ($def['js'] ?? [] as $js) {
                $this->js($js);
            }
        }

        return $this;
    }

    public function hasPlugin(string $name): bool
    {
        return isset($this->plugins[$name]);
    }

    /** 追加 css 文件（去重） */
    public function css(string $path): self
    {
        $this->css[$path] = true;

        return $this;
    }

    /** 追加 js 文件（去重） */
    public function js(string $path): self
    {
        $this->js[$path] = true;

        return $this;
    }

    /** 追加内联 JS；指定 $key 可实现"同 key 只输出一次" */
    public function inlineJs(string $code, ?string $key = null): self
    {
        $this->inlineJs[$key ?? ('__' . count($this->inlineJs) . '_' . md5($code))] = $code;

        return $this;
    }

    /** 追加内联 CSS；指定 $key 可实现"同 key 只输出一次" */
    public function inlineCss(string $code, ?string $key = null): self
    {
        $this->inlineCss[$key ?? ('__' . count($this->inlineCss) . '_' . md5($code))] = $code;

        return $this;
    }

    /**
     * 渲染 <head> 资源：主题配置脚本 + vendor/app CSS + 插件 CSS + 内联 CSS
     */
    public function head(): string
    {
        $this->headRendered = true;

        $html = '<script src="' . Html::e($this->url('js/config.js')) . '"></script>' . "\n";
        $html .= '<link href="' . Html::e($this->url('css/vendors.min.css')) . '" rel="stylesheet" type="text/css">' . "\n";

        foreach (array_keys($this->css) as $css) {
            $html .= '<link href="' . Html::e($this->url($css)) . '" rel="stylesheet" type="text/css">' . "\n";
        }

        $html .= '<link href="' . Html::e($this->url('css/app.min.css')) . '" rel="stylesheet" type="text/css">' . "\n";
        $html .= '<link href="' . Html::e($this->url('css/xfadmin.css')) . '" rel="stylesheet" type="text/css">' . "\n";

        foreach ($this->inlineCss as $css) {
            $html .= "<style>\n{$css}\n</style>\n";
        }

        return $html;
    }

    /**
     * 渲染 </body> 前脚本：vendors + 插件 JS + app.js + xfadmin.js + 内联 JS
     * 若渲染 head 之后又有新的 CSS 注册（组件在 head 之后渲染），此处兜底补输出
     */
    public function scripts(): string
    {
        $this->scriptsRendered = true;
        $html = '';

        $html .= '<script src="' . Html::e($this->url('js/vendors.min.js')) . '"></script>' . "\n";

        foreach (array_keys($this->js) as $js) {
            $html .= '<script src="' . Html::e($this->url($js)) . '"></script>' . "\n";
        }

        $html .= '<script src="' . Html::e($this->url('js/app.js')) . '"></script>' . "\n";
        $html .= '<script src="' . Html::e($this->url('js/xfadmin.js')) . '"></script>' . "\n";

        foreach ($this->inlineJs as $js) {
            $html .= "<script>\n{$js}\n</script>\n";
        }

        return $html;
    }

    /** 收集到的 css 列表（相对路径） */
    public function cssFiles(): array
    {
        return array_keys($this->css);
    }

    /** 收集到的 js 列表（相对路径） */
    public function jsFiles(): array
    {
        return array_keys($this->js);
    }
}

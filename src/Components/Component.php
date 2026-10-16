<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components;

use Stringable;
use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 组件基类
 *
 * 所有组件均支持：
 *  - Component::make([...]) 传入配置数据
 *  - ->set('a.b', v) / ->get('a.b') 点式配置
 *  - ->attr() / ->addClass() / ->id() 自定义 HTML 属性（链式）
 *  - 渲染时自动向 Assets 注册所需插件资源（去重，只加载一次）
 *  - echo / (string) / ->render() 输出 HTML
 *
 * 所有组件（含全部子类）零改动即可获得的【通用控制参数】：
 *  - id        string     根元素 id（等价 ->id()）
 *  - class     string     根元素附加 class（等价 ->addClass()，自动去重）
 *  - style     string|array 根元素内联样式（安全净化，拦截 url(javascript:)/expression() 等注入）
 *  - attrs     array      批量自定义属性（等价 ->attr([...])）
 *  - hidden    bool       true 时不渲染任何内容
 *  - when      bool|Closure 条件渲染；闭包惰性求值，入参为组件自身，false 不渲染
 *  - visible   bool       false 时不渲染（与 hidden 语义相反）
 *  - col       int|array  响应式栅格列宽（int=col-md-N col-xl-N；array=['md'=>6,'xl'=>3]），非空时用 <div class="col-*"> 包裹
 *
 * 优先级：hidden > when > visible；三者任一判定「不渲染」即返回空串。
 * 注意：width 不作为统一栅格保留字（ApexChart/Skeleton/VideoPlayer 用其表渲染尺寸、
 * Grid/Col 用其表栅格原语、StatCard 已在自身内部用其包裹），栅格列宽统一用 col。
 */
abstract class Component implements Stringable
{
    protected array $options = [];

    /** 附加到根元素的自定义属性 */
    protected array $attributes = [];

    private static int $uidCounter = 0;

    /**
     * construct（public实例方法）
     *
     * @param  array  $options  options
     * @return mixed 渲染结果 / 组件实例或配置
     */
    public function __construct(array $options = [])
    {
        $this->options = array_replace_recursive($this->defaults(), $options);
        $this->bootCommonOptions();
    }

    /**
     * 归一化通用控制参数（构造期执行，一次接线让全部组件零改动获得）
     *
     * 将第一公民参数 class/style/id/attrs 统一转入 $attributes，使根元素经
     * attrs() 自动合并；hidden/when/visible/col/mt/mb 等条件与布局参数由
     * render() 统一后处理，故此处无需处理。
     */
    protected function bootCommonOptions(): void
    {
        // class：既支持第一公民 options['class']，也兼容已存在的链式 addClass()
        if (isset($this->options['class']) && is_string($this->options['class']) && $this->options['class'] !== '') {
            $this->addClass(...preg_split('/\s+/', trim($this->options['class'])));
        }
        // id：第一公民 id（与 ->id() 链式等价）
        if (isset($this->options['id']) && is_string($this->options['id']) && $this->options['id'] !== '') {
            $this->attr('id', $this->options['id']);
        }
        // style：安全 CSS 声明串（仅允许键值对，拦截 ;/url 注入）
        if (isset($this->options['style'])) {
            $style = $this->safeStyle($this->options['style']);
            if ($style !== '') {
                $this->attr('style', $style);
            }
        }
        // attrs：批量自定义属性（数组），与 ->attr() 链式等价
        if (isset($this->options['attrs']) && is_array($this->options['attrs'])) {
            $this->attr($this->options['attrs']);
        }
    }

    /**
     * make（public静态方法）
     *
     * @param  array  $options  options
     * @return static result
     */
    public static function make(array $options = []): static
    {
        return new static($options);
    }

    /** 组件默认配置 */
    protected function defaults(): array
    {
        return [];
    }

    /** 组件依赖的插件名（见 Assets::PLUGINS） */
    protected function assets(): array
    {
        return [];
    }

    /** 生成组件 HTML */
    abstract protected function html(): string;

    // ------------------------------------------------------------------
    // 配置
    // ------------------------------------------------------------------

    /**
     * set（public静态方法）
     *
     * @param  string|array  $key  key
     * @param  mixed  $value  value
     * @return static result
     */
    public function set(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->options = array_replace_recursive($this->options, $key);
        } else {
            Html::set($this->options, $key, $value);
        }

        return $this;
    }

    /**
     * get（public实例方法）
     *
     * @param  string  $key  key
     * @param  mixed  $default  default
     * @return mixed result
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Html::get($this->options, $key, $default);
    }

    /**
     * options（public实例方法）
     *
     * @return array result
     */
    public function options(): array
    {
        return $this->options;
    }

    // ------------------------------------------------------------------
    // 属性 / class
    // ------------------------------------------------------------------

    /**
     * attr（public静态方法）
     *
     * @param  string|array  $name  name
     * @param  mixed  $value  value
     * @return static result
     */
    public function attr(string|array $name, mixed $value = true): static
    {
        if (is_array($name)) {
            $this->attributes = array_merge($this->attributes, $name);
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    /**
     * add Class（public静态方法）
     *
     * @param string ... $classes classes
     * @return static result
     */
    public function addClass(string ...$classes): static
    {
        $this->attributes['class'] = Html::cls($this->attributes['class'] ?? '', $classes);

        return $this;
    }

    /**
     * id（public静态方法）
     *
     * @param  string  $id  id
     * @return static result
     */
    public function id(string $id): static
    {
        return $this->attr('id', $id);
    }

    /** 合并渲染根元素属性 */
    protected function attrs(array $base = []): string
    {
        $merged = $base;
        foreach ($this->attributes as $name => $value) {
            if ($name === 'class') {
                $merged['class'] = Html::cls($base['class'] ?? '', $value);
            } else {
                $merged[$name] = $value;
            }
        }

        return Html::attrs($merged);
    }

    // ------------------------------------------------------------------
    // 工具
    // ------------------------------------------------------------------

    /**
     * escape（protected实例方法）：HTML 转义
     *
     * @param  mixed  $value  value
     * @return string result
     */
    protected function escape(mixed $value): string
    {
        return Html::escape($value);
    }

    /**
     * @deprecated 自 13.x 起废弃，请改用 escape()。保留仅为向后兼容。
     *
     * @param  mixed  $value  value
     * @return string result
     */
    protected function e(mixed $value): string
    {
        return $this->escape($value);
    }

    /** 枚举白名单：非法值回退默认，防止任意 CSS 类/属性注入（variant/size/type/placement 等） */
    protected function enum(mixed $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? (string) $value : $default;
    }

    /** 常见枚举值白名单集合 */
    protected const ENUM_VARIANT = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'];

    protected const ENUM_VARIANT_OUTLINE = ['outline-primary', 'outline-secondary', 'outline-success', 'outline-danger', 'outline-warning', 'outline-info', 'outline-light', 'outline-dark'];

    protected const ENUM_SIZE = ['sm', 'lg'];

    protected const ENUM_PLACEMENT = ['top', 'bottom', 'left', 'right', 'start', 'end'];

    /** Bootstrap 栅格断点白名单（响应式列宽的「键」必须受限，否则可逃逸出 class 属性） */
    protected const ENUM_BREAKPOINT = ['sm', 'md', 'lg', 'xl', 'xxl'];

    // ------------------------------------------------------------------
    // CSS 值安全助手（style="..." 场景：e() 不转义 ; : ( ) / 等，无法阻止追加声明）
    // ------------------------------------------------------------------

    /**
     * 安全 CSS 长度：仅接受 数字+单位（px/%/rem/em/vh/vw/pt/ch/fr），其余回退默认。
     *
     * 用于 height/width/max-height 等被拼进 style 属性的选项，杜绝
     * `10px;position:fixed;inset:0` 这类「分号注入」造成的点击劫持/覆盖层。
     */
    protected function cssLen(mixed $value, string $default = ''): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return $default;
        }

        return preg_match('/^-?\d+(?:\.\d+)?(?:px|%|rem|em|vh|vw|pt|ch|fr)$/', $candidate) === 1 ? $candidate : $default;
    }

    /**
     * 安全 CSS 宽高比（aspect-ratio）：仅接受 N/M 或纯数字，其余回退默认。
     */
    protected function cssRatio(mixed $value, string $default = '4/3'): string
    {
        $candidate = trim((string) str_replace(['x', 'X', ':', ' '], '/', (string) $value));
        if (preg_match('/^\d+(?:\.\d+)?(?:\/\d+(?:\.\d+)?)?$/', $candidate) === 1) {
            return $candidate;
        }

        return $default;
    }

    /**
     * 安全 CSS 颜色：接受 #hex / rgb() / rgba() / hsl() / hsla() / 具名色 / var(--x) / transparent。
     * 拒绝 `;`、圆括号嵌套以外的自由文本，避免注入额外声明。
     */
    protected function cssColor(mixed $value, string $default = ''): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return $default;
        }
        $ok = preg_match('/^(?:#[0-9a-fA-F]{3,8}|[a-zA-Z]{3,20}|var\(\s*--[A-Za-z0-9_-]+\s*(?:,[^()]+)?\))$/', $candidate) === 1
            || preg_match('/^(?:rgb|rgba|hsl|hsla)\([^()]*\)$/', $candidate) === 1;

        return $ok ? $candidate : $default;
    }

    /**
     * 安全 CSS 背景：仅允许纯色、线性/径向渐变与 url()（协议限定 http/https/data:image），
     * 其余回退默认。用于 cover/background 这类「半自由 CSS」选项。
     */
    protected function cssBackground(mixed $value, string $default = ''): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return $default;
        }
        if (preg_match('/^(?:linear|radial|conic)-gradient\([^()]*(?:\([^()]*\)[^()]*)*\)$/', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^url\((?:"|\')?(https?:\/\/|\/|data:image\/)[^"\')]*(?:"|\')?\)$/', $candidate) === 1) {
            return $candidate;
        }

        return $this->cssColor($candidate, $default);
    }

    /**
     * 安全 CSS 声明串（style="..." 第一公民参数）：仅接受 `prop:value;prop:value` 形式，
     * 键限定 [a-zA-Z-]+、值限定不含 `;`/`}`/`url(`/`expression`/`<` 的普通文本，
     * 拦截 CSS 注入（如 `position:fixed` 配合 `;` 追加、`url(javascript:)`、IE expression()）。
     *
     * @param  mixed  $value  样式声明串或 key=>value 数组
     * @return string 净化后的声明串，非法则返回 ''
     */
    protected function safeStyle(mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '';
        }
        // 数组形式：['color' => 'red', 'font-size' => '12px'] 逐个校验后拼接
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $property => $candidate) {
                $prop = trim((string) $property);
                if (! preg_match('/^[a-zA-Z][a-zA-Z0-9-]*$/', $prop)) {
                    continue;
                }
                $val = $this->safeCssValue($candidate);
                if ($val === '') {
                    continue;
                }
                $parts[] = $prop.':'.$val;
            }

            return implode(';', $parts);
        }
        // 字符串形式：切分声明后逐个校验
        $decls = explode(';', trim((string) $value));
        $parts = [];
        foreach ($decls as $decl) {
            $decl = trim($decl);
            if ($decl === '') {
                continue;
            }
            $pos = strpos($decl, ':');
            if ($pos === false) {
                continue;
            }
            $prop = trim(substr($decl, 0, $pos));
            $val = trim(substr($decl, $pos + 1));
            if (! preg_match('/^[a-zA-Z][a-zA-Z0-9-]*$/', $prop)) {
                continue;
            }
            $val = $this->safeCssValue($val);
            if ($val === '') {
                continue;
            }
            $parts[] = $prop.':'.$val;
        }

        return implode(';', $parts);
    }

    /**
     * 校验单个 CSS 值：拒绝含 `;`/`}`/`{`/`url(`/`expression(`/`<` 的注入载体。
     */
    protected function safeCssValue(mixed $value): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return '';
        }
        // 显式拒绝可逃逸 style 属性的危险载体
        if (preg_match('/[;{}]|url\s*\(|expression\s*\(|<\s*\/?script|javascript:/i', $candidate)) {
            return '';
        }

        return $candidate;
    }

    /**
     * 生成响应式栅格列 class（断点走白名单、列数夹紧 1~12），返回 '' 表示不需要包裹列。
     *
     * - 数组：['md' => 6, 'xl' => 3] → 'col-12 col-md-6 col-xl-3'（键非法则跳过该项）
     * - 数字：N → 'col-12 col-md-{N} col-xl-{N}'，非数字/超范围回退 col-12
     */
    protected function gridCol(mixed $width): string
    {
        if ($width === null || $width === false || $width === '' || $width === true) {
            return '';
        }
        if (is_array($width)) {
            $cls = 'col-12';
            foreach ($width as $bp => $cols) {
                $bp = $this->enum($bp, self::ENUM_BREAKPOINT, '');
                if ($bp === '') {
                    continue;
                }
                $columns = (int) $cols;
                if ($columns < 1 || $columns > 12) {
                    continue;
                }
                $cls .= ' col-'.$bp.'-'.$columns;
            }

            return $cls;
        }
        if (is_numeric($width)) {
            $columns = (int) $width;
            if ($columns < 1 || $columns > 12) {
                return 'col-12';
            }

            return 'col-12 col-md-'.$columns.' col-xl-'.$columns;
        }

        return 'col-12';
    }

    /** 允许 HTML 的槽位：Component/Stringable 会被渲染，闭包会被调用（惰性内容），字符串原样输出 */
    protected function raw(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \Closure) {
            return $this->raw($value());
        }
        if (is_array($value)) {
            return implode('', array_map(fn ($candidate) => $this->raw($candidate), $value));
        }

        return (string) $value;
    }

    /**
     * 「可传组件、但字符串必须转义」的槽位渲染。
     *
     * 与 raw() 的区别：raw() 对字符串原样输出（用于 body/content 等明确的内容槽位）；
     * text() 用于标题、版权、按钮文案这类「语义上是纯文本、但允许传组件实例」的字段，
     * 传字符串时一律 e() 转义，传 Component/Stringable/闭包/数组时按 raw() 渲染。
     */
    protected function text(mixed $value): string
    {
        if ($value instanceof Stringable || $value instanceof \Closure) {
            return $this->raw($value);
        }
        if (is_array($value)) {
            return implode('', array_map(fn ($candidate) => $this->text($candidate), $value));
        }

        return $this->escape($value);
    }

    /**
     * 解析图片地址：http(s)://、// 与 data: 开头的原样返回，
     * 其余按包内 images/ 相对路径经 XfAdmin::asset() 解析
     */
    protected function img(mixed $path): string
    {
        $normalizedPath = trim((string) $path);
        if ($normalizedPath === '') {
            // 空路径返回透明 1x1 GIF，避免组件输出 src="" 触发破图请求
            return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }
        // 放行：协议相对 // 与 http(s):// 原样返回（外链图片）
        if (preg_match('#^(?:https?:)?//#i', $normalizedPath)) {
            return $normalizedPath;
        }
        // 放行：仅 data:image/*（拒绝 data:text/html 等可承载脚本的 XSS 载体）
        if (preg_match('#^data:image/#i', $normalizedPath)) {
            return $normalizedPath;
        }
        // 拦截：javascript:/vbscript:/data: 非图片 等显式伪协议。
        // 必须在 asset() 拼接基址之前拦截，否则 javascript:alert(1) 经 asset 变为
        // /base/javascript:alert(1) 后会因「以 / 开头」绕过 safeUrl 检测。
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $normalizedPath)) {
            return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }

        return XfAdmin::asset('images/'.ltrim($normalizedPath, '/'));
    }

    /**
     * 图标 class 净化
     *
     * 图标字段本质是 CSS 类名（如 ti ti-user），仅允许 [a-zA-Z0-9_\- ]，
     * 剔除可用于属性注入的字符（: ( ) " ' < > 等）。比 e() 更彻底：e() 不转义
     * 冒号与括号，故 javascript:alert(1) 经 e() 后仍会以类名义留在 class 中。
     */
    protected function iconClass(mixed $value, string $default = ''): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '') {
            return $default;
        }
        // 去除非法字符后压缩多余空白（保留合法类名与分隔空格）
        $candidate = (string) preg_replace('/[^a-zA-Z0-9_\- ]/', '', $candidate);

        return (string) preg_replace('/\s+/', ' ', trim($candidate));
    }

    /** 生成组件级唯一 id */
    protected function uid(string $prefix = 'xf'): string
    {
        return $prefix.'-'.(++self::$uidCounter);
    }

    /** 取或生成根元素 id */
    protected function resolveId(string $prefix): string
    {
        if (! empty($this->attributes['id'])) {
            return (string) $this->attributes['id'];
        }
        if ($this->get('id')) {
            return (string) $this->get('id');
        }

        return $this->attributes['id'] = $this->uid($prefix);
    }

    /**
     * 生成 data-xf-init 属性对：前端 xfadmin.js 会自动扫描并初始化，
     * 且同一资源只会被加载一次
     */
    protected function initAttrs(string $widget, array $config = []): array
    {
        $attrs = ['data-xf' => $widget];
        if ($config !== []) {
            // JSON_HEX_TAG 将 < > & 转义，纵深防御 data-xf-config 中的 </script> 等断标签注入；
            // HEX_APOS/HEX_QUOT 防止属性引号逃逸，HEX_AMP 防止 & 误解析
            $attrs['data-xf-config'] = json_encode(
                $config,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        }

        return $attrs;
    }

    // ------------------------------------------------------------------
    // 渲染
    // ------------------------------------------------------------------

    /**
     * 安全 URL：拦截 javascript:/vbscript:/data:(非图片) 等可触发 XSS 的伪协议。
     *
     * 用于所有链接类选项（href/url/src），避免调用方传入 javascript:alert(1) 点击即 XSS。
     * 放行：协议相对 //、http(s)://、mailto:/tel:、data:image/、锚点 #、根相对 /、无 scheme 的相对路径。
     * javascript:void(0) 等「无操作」伪协议作为常见 no-op 白名单放行（非用户可控）。
     */
    protected function safeUrl(mixed $value, string $default = '#'): string
    {
        $candidate = trim((string) $value);
        if ($candidate === '' || $candidate === '#') {
            return $candidate;
        }
        // 常见无操作伪协议（设计性 no-op，非用户可控），放行：
        // javascript:void(0) / javascript:void() / javascript:; / 裸 javascript:
        if (preg_match('/^javascript:\s*(?:void\s*\(?\s*0*\s*\)?\s*;?|;?)$/i', $candidate)) {
            return $candidate;
        }
        // 放行：协议相对 / 绝对 http(s) / 锚点 / 根相对 / mailto / tel / data:image / 无 scheme 的相对路径
        if (preg_match('#^(?:https?:)?//#i', $candidate)
            || str_starts_with($candidate, '/')
            || str_starts_with($candidate, '#')
            || preg_match('#^(?:mailto:|tel:)#i', $candidate)
            || preg_match('#^data:image/#i', $candidate)
            || ! preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $candidate)) {
            return $candidate;
        }

        // 含非常规协议（javascript:/vbscript:/data: 非图片 等）→ 拦截
        return $default;
    }

    /**
     * render（public实例方法）
     *
     * @return string result
     */
    public function render(): string
    {
        // 注册资源依赖（注册幂等、自动去重：同一插件被多个组件依赖或同一组件
        // 多次渲染都只会输出一次资源引用）
        $plugins = $this->assets();
        if ($plugins !== []) {
            Assets::instance()->plugin(...$plugins);
        }

        // 说明：render 不做结果缓存——内联初始化 JS 按 key/内容去重已保证不重复输出；
        // 同一实例被多次渲染时各自生成独立 uid，互不干扰；且跨多个完整页面复用实例时
        // 能在资源状态重置后重新注册初始化脚本。
        return $this->renderWithWrapper();
    }

    /**
     * 统一渲染后处理（条件渲染 + 栅格列宽包裹）
     *
     * 所有组件零改动即自动获得以下通用控制参数：
     *  - hidden：true 时不输出任何内容
     *  - when：布尔值或闭包（惰性求值，参数为组件自身），false 时不输出
     *  - visible：布尔值（true 输出 / false 不输出），与 hidden 语义相反
     *  - col / width：响应式栅格列宽（数组 ['md'=>6,'xl'=>3] 或数字），非空时用 <div class="col-*"> 包裹
     *
     * 优先级：hidden > when > visible（任一判定「不渲染」即返回 ''）。
     *
     * @return string 最终 HTML
     */
    protected function renderWithWrapper(): string
    {
        // ① 条件渲染：hidden 优先
        if ($this->boolOpt('hidden')) {
            return '';
        }
        // when：闭包惰性求值或直接布尔
        if (array_key_exists('when', $this->options) && $this->options['when'] !== null) {
            $when = $this->options['when'];
            $ok = $when instanceof \Closure ? (bool) $when($this) : (bool) $when;
            if (! $ok) {
                return '';
            }
        }
        // visible：false 时不渲染（默认未设置时视为可见）
        if (array_key_exists('visible', $this->options) && $this->options['visible'] !== null && ! (bool) $this->options['visible']) {
            return '';
        }

        $html = $this->html();

        // ② 栅格列宽包裹：col 为保留字（栅格语义）。
        // 注意：width 不作为统一保留字——ApexChart/Skeleton/VideoPlayer 用 width 表渲染尺寸、
        // Grid/Col 用 width 表栅格原语、StatCard 已在自身 html() 内用 width 包裹，
        // 基类若再对 width 包裹会造成语义冲突或双重包裹。故统一栅格列宽仅认 col。
        $width = $this->options['col'] ?? null;
        if ($width !== null && $width !== false && $width !== '' && $width !== true) {
            $colClass = $this->gridCol($width);
            if ($colClass !== '') {
                $html = '<div class="'.$colClass.'">'.$html.'</div>';
            }
        }

        return $html;
    }

    /**
     * 布尔型配置读取（兼容 0/1/'0'/'1'/false/true/'' 等常见写法）
     */
    protected function boolOpt(string $key): bool
    {
        $candidate = $this->options[$key] ?? null;

        return $candidate === true || $candidate === 1 || $candidate === '1' || $candidate === 'true' || $candidate === 'on' || $candidate === 'yes';
    }

    /**
     * to String（public实例方法）
     *
     * @return string result
     */
    public function __toString(): string
    {
        return $this->render();
    }
}

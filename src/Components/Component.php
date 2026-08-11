<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components;

use Stringable;
use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Support\Html;

/**
 * 组件基类
 *
 * 所有组件均支持：
 *  - Component::make([...]) 传入配置数据
 *  - ->set('a.b', v) / ->get('a.b') 点式配置
 *  - ->attr() / ->addClass() 自定义 HTML 属性
 *  - 渲染时自动向 Assets 注册所需插件资源（去重，只加载一次）
 *  - echo / (string) / ->render() 输出 HTML
 */
abstract class Component implements Stringable
{
    protected array $options = [];

    /** 附加到根元素的自定义属性 */
    protected array $attributes = [];

    private static int $uidCounter = 0;

    public function __construct(array $options = [])
    {
        $this->options = array_replace_recursive($this->defaults(), $options);
    }

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

    public function set(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->options = array_replace_recursive($this->options, $key);
        } else {
            Html::set($this->options, $key, $value);
        }

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Html::get($this->options, $key, $default);
    }

    public function options(): array
    {
        return $this->options;
    }

    // ------------------------------------------------------------------
    // 属性 / class
    // ------------------------------------------------------------------

    public function attr(string|array $name, mixed $value = true): static
    {
        if (is_array($name)) {
            $this->attributes = array_merge($this->attributes, $name);
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    public function addClass(string ...$classes): static
    {
        $this->attributes['class'] = Html::cls($this->attributes['class'] ?? '', $classes);

        return $this;
    }

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

    protected function e(mixed $value): string
    {
        return Html::e($value);
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
            return implode('', array_map(fn ($v) => $this->raw($v), $value));
        }

        return (string) $value;
    }

    /**
     * 解析图片地址：http(s)://、// 与 data: 开头的原样返回，
     * 其余按包内 images/ 相对路径经 XfAdmin::asset() 解析
     */
    protected function img(mixed $path): string
    {
        $p = trim((string) $path);
        if ($p === '') {
            // 空路径返回透明 1x1 GIF，避免组件输出 src="" 触发破图请求
            return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }
        if (preg_match('#^(?:https?:)?//|^data:#i', $p)) {
            return $p;
        }

        return \zxf\XfAdmin\XfAdmin::asset('images/' . ltrim($p, '/'));
    }

    /** 生成组件级唯一 id */
    protected function uid(string $prefix = 'xf'): string
    {
        return $prefix . '-' . (++self::$uidCounter);
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
        return $this->html();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

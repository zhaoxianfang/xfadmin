<?php

declare(strict_types=1);

namespace XfAdmin\Components;

use Stringable;
use XfAdmin\Assets\Assets;
use XfAdmin\Support\Html;

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

    /** 允许 HTML 的槽位：Component/Stringable 会被渲染，字符串原样输出 */
    protected function raw(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value)) {
            return implode('', array_map(fn ($v) => $this->raw($v), $value));
        }

        return (string) $value;
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
            $attrs['data-xf-config'] = json_encode(
                $config,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        }

        return $attrs;
    }

    // ------------------------------------------------------------------
    // 渲染
    // ------------------------------------------------------------------

    public function render(): string
    {
        // 注册资源依赖（重复注册自动去重）
        $plugins = $this->assets();
        if ($plugins !== []) {
            Assets::instance()->plugin(...$plugins);
        }

        return $this->html();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

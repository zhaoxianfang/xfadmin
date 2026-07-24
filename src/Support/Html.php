<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support;

/**
 * HTML 构建工具：转义 / 属性拼装 / class 合并 / JSON 属性
 */
final class Html
{
    /** HTML 转义 */
    public static function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * 属性数组转字符串
     * ['class' => 'a b', 'disabled' => true, 'data-x' => 1, 'hidden' => false]
     * => ' class="a b" disabled data-x="1"'
     */
    public static function attrs(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            if ($value === true) {
                $html .= ' ' . $name;
                continue;
            }
            if (is_array($value)) {
                $value = $name === 'class'
                    ? self::cls($value)
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $html .= ' ' . $name . '="' . self::e($value) . '"';
        }

        return $html;
    }

    /** class 合并：支持字符串 / 数组 / [class => bool] */
    public static function cls(mixed ...$groups): string
    {
        $classes = [];
        foreach ($groups as $group) {
            if ($group === null || $group === '' || $group === false) {
                continue;
            }
            if (is_string($group)) {
                $classes = array_merge($classes, preg_split('/\s+/', trim($group)) ?: []);
                continue;
            }
            if (is_array($group)) {
                foreach ($group as $key => $val) {
                    if (is_int($key)) {
                        $classes[] = self::cls($val);
                    } elseif ($val) {
                        $classes[] = $key;
                    }
                }
            }
        }

        return implode(' ', array_values(array_unique(array_filter($classes, fn ($c) => $c !== ''))));
    }

    /** 供 data-* 属性安全携带 JSON 配置 */
    public static function json(mixed $data): string
    {
        return self::e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    /** 内联 <script> 场景下的安全 JSON（防止 </script> 注入） */
    public static function scriptJson(mixed $data): string
    {
        return (string) json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }

    /** 数组点式读取 */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /** 数组点式写入 */
    public static function set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $ref  = &$array;
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $ref[$segment] = $value;
                return;
            }
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
    }
}

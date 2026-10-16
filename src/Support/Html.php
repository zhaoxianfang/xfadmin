<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support;

/**
 * HTML 构建工具：转义 / 属性拼装 / class 合并 / JSON 属性 / 单标签 / 内联样式
 */
final class Html
{
    /** 自闭合标签白名单（用于 tag() 判定是否输出闭合标签） */
    private const VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    /**
     * HTML 转义（可读命名主方法）
     *
     * null 返回空串；数组/对象尝试序列化为 JSON（对象若实现 __toString 则按字符串处理），
     * 避免 "Array to string conversion" 与对象直接拼接警告。
     *
     * @param  mixed  $value  待转义值
     * @return string 转义后字符串
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            // 数组（如组件把数组配置误当文本传入）序列化为 JSON，避免 "Array to string conversion"
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * @deprecated 自 13.x 起废弃，请改用 escape()。保留仅为向后兼容。
     *
     * @param  mixed  $value  待转义值
     * @return string 转义后字符串
     */
    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    /**
     * 属性数组转字符串
     * ['class' => 'a b', 'disabled' => true, 'data-x' => 1, 'hidden' => false]
     * => ' class="a b" disabled data-x="1"'
     *
     * 属性名做白名单校验（字母开头，允许数字/下划线/连字符/点/冒号，覆盖
     * data-*、aria-*、xlink:href、@click、:prop 等常见形态）：非法名直接跳过，
     * 防止调用方把用户可控字符串当作属性名注入时破坏属性结构。
     */
    public static function attrs(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            if (! is_string($name) || preg_match('/^[a-zA-Z_][a-zA-Z0-9_:.-]*$/', $name) !== 1) {
                continue;
            }
            if ($value === true) {
                $html .= ' '.$name;

                continue;
            }
            if (is_array($value)) {
                $value = $name === 'class'
                    ? self::cls($value)
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            }
            $html .= ' '.$name.'="'.self::escape($value).'"';
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

        return implode(' ', array_values(array_unique(array_filter($classes, fn ($class) => $class !== ''))));
    }

    /** 供 data-* 属性安全携带 JSON 配置 */
    public static function json(mixed $data): string
    {
        return self::escape(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    /** 内联 <script> 场景下的安全 JSON（防止 </script> 注入） */
    public static function scriptJson(mixed $data): string
    {
        return (string) json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }

    /**
     * 生成单个 HTML 标签
     *
     * 自动对属性做白名单与转义（复用 attrs()），内容默认按纯文本转义；
     * 传 $escape=false 时内容按原始 HTML 输出（调用方需自行保证安全）。
     * 自闭合标签（img/input/br/...）不输出闭合标签。
     *
     * @param  string  $name  标签名（字母开头，非法名回退为 span）
     * @param  array  $attrs  属性数组
     * @param  string  $content  标签内容
     * @param  bool  $escape  内容是否转义（默认 true）
     * @return string 完整标签 HTML
     */
    public static function tag(string $name, array $attrs = [], string $content = '', bool $escape = true): string
    {
        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9-]*$/', $name)) {
            $name = 'span';
        }
        $attrString = self::attrs($attrs);
        $open = '<'.$name.$attrString.'>';
        if (in_array($name, self::VOID_TAGS, true)) {
            // 自闭合标签：以 /> 结尾，无内容、无闭合标签
            return rtrim($open, '>').' />';
        }
        $body = $escape ? self::escape($content) : $content;

        return $open.$body.'</'.$name.'>';
    }

    /**
     * 安全内联样式数组 → 声明串
     *
     * 输入 ['color' => 'red', 'font-size' => '12px']，输出 'color:red;font-size:12px'。
     * 逐项校验属性名（字母/连字符）与值（拒绝含 ;{} url( expression( < 的注入载体），
     * 非法项跳过，避免 style 属性注入。空数组返回 ''。
     *
     * @param  array  $styles  键值样式
     * @return string 净化后的 style 声明串
     */
    public static function style(array $styles): string
    {
        $parts = [];
        foreach ($styles as $property => $value) {
            $property = trim((string) $property);
            if (! preg_match('/^[a-zA-Z][a-zA-Z0-9-]*$/', $property)) {
                continue;
            }
            $val = trim((string) $value);
            if ($val === '' || preg_match('/[;{}]|url\s*\(|expression\s*\(|<\s*\/?script|javascript:/i', $val)) {
                continue;
            }
            $parts[] = $property.':'.$val;
        }

        return implode(';', $parts);
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
        $ref = &$array;
        foreach ($keys as $index => $segment) {
            if ($index === count($keys) - 1) {
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

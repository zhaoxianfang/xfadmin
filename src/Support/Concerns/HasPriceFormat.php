<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support\Concerns;

/**
 * 统一定价格式化：消除各电商组件中重复的 formatPrice 方法
 *
 * 使用：
 *   use HasPriceFormat;
 *   $this->formatPrice(89.00, '¥');  // "¥89.00"
 *   $this->formatPrice(12500, '$');  // "$12,500.00"
 */
trait HasPriceFormat
{
    /**
     * 格式化价格展示。
     * 货币符号会经 htmlspecialchars 转义，防止 XSS。
     *
     * @param float|int $amount  金额
     * @param string    $currency 货币符号（¥ / $ / € / £ 等），传入 '' 则不输出前缀
     * @param int       $decimals 小数位数
     */
    protected function formatPrice(float|int $amount, string $currency = '¥', int $decimals = 2): string
    {
        if ($currency !== '') {
            return htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') . number_format($amount, $decimals);
        }

        return number_format($amount, $decimals);
    }

    /**
     * 格式化价格 + 原价对比（划线旧价格）
     *
     * @return string HTML 片段
     */
    protected function formatPriceWithOld(float|int $price, float|int $oldPrice, string $currency = '¥', int $decimals = 2): string
    {
        $html = '';
        if ($oldPrice > 0) {
            $html .= '<span class="text-muted text-decoration-line-through small">' . $this->formatPrice($oldPrice, $currency, $decimals) . '</span> ';
        }
        $html .= '<span class="fw-bold text-danger">' . $this->formatPrice($price, $currency, $decimals) . '</span>';

        return $html;
    }
}

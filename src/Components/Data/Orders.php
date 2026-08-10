<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 订单列表 —— 严格对齐 INSPINIA v4.1.0 `ecommerce-orders.html` 的结构与观感
 *
 * 模板规范结构（本组件的输出蓝本）：
 *   <div class="card">
 *     <div class="card-header">      ← 搜索框 + 状态/支付筛选下拉 + 每页条数 + 批量删除
 *     <div class="table-responsive">
 *       <table class="table table-custom table-select table-hover align-middle mb-0">
 *         <thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">
 *         <tbody>：勾选框 / #订单号(link-reset) / 日期+时间 / 客户(.avatar avatar-sm)
 *                 / 金额 / 付款状态(ti-point-filled) / 订单状态(badge-soft) / 支付方式 / 圆形图标操作钮
 *     <div class="card-footer border-0"> ← 统计信息 + 分页
 *
 * 前端交互（搜索/筛选/分页/全选/批量删除）由 xfadmin.js 的 `xftable` 模块驱动
 * （对标模板的 custom-table.js data-table-* 体系）。服务端大数据量请改用 DataTable 组件。
 *
 * XfAdmin::orders([
 *     'title'  => '订单管理',
 *     'orders' => [
 *         [
 *             'id' => '#ORD-1001', 'customer' => '张三', 'avatar' => 'users/user-1.jpg',
 *             'email' => 'z@x.com', 'date' => '2026-07-20', 'time' => '10:10',
 *             'items' => 3, 'total' => '¥320.00',
 *             'status' => 'completed',        // pending|processing|completed|refunded|cancelled
 *             'paid'   => true,               // 付款状态（true=已付款 / false=未付款 / 'refund'=退款中）
 *             'payment' => '支付宝',
 *         ],
 *     ],
 *     'searchable' => true,   // 头部搜索框
 *     'filterable' => true,   // 状态/付款筛选下拉
 *     'selectable' => true,   // 勾选框 + 全选 + 批量删除
 *     'page_size'  => 10,     // 前端分页每页条数（0 = 不分页）
 * ])
 */
class Orders extends Component
{
    protected function defaults(): array
    {
        return [
            'title'      => '',
            'orders'     => [],
            'searchable' => true,
            'filterable' => true,
            'selectable' => true,
            'page_size'  => 10,
        ];
    }

    /** 订单状态 → [badge-soft 变体, 中文文案] */
    private const STATUS = [
        'pending'    => ['warning', '待付款'],
        'processing' => ['info', '处理中'],
        'completed'  => ['success', '已完成'],
        'refunded'   => ['purple', '已退款'],
        'cancelled'  => ['danger', '已取消'],
    ];

    protected function html(): string
    {
        $orders = (array) $this->get('orders', []);
        if (empty($orders)) {
            return '';
        }

        $id         = $this->resolveId('orders');
        $searchable = (bool) $this->get('searchable');
        $filterable = (bool) $this->get('filterable');
        $selectable = (bool) $this->get('selectable');
        $pageSize   = (int) $this->get('page_size');
        $interactive = $searchable || $filterable || $selectable || $pageSize > 0;

        // 根节点：card 容器；交互能力交给 xftable 模块（前端搜索/筛选/分页/全选/批删）
        $html = '<div' . $this->attrs(['class' => 'card', 'id' => $id])
            . ($interactive ? ' data-xf="xftable" data-xf-config="' . $this->e(json_encode(['pageSize' => $pageSize > 0 ? $pageSize : 100000], JSON_UNESCAPED_UNICODE)) . '"' : '') . '>';

        /* ---------- 卡片头：标题 + 搜索 + 筛选 + 批量操作（对齐模板 card-header 布局） ---------- */
        if ($this->get('title') || $interactive) {
            $html .= '<div class="card-header border-light d-flex flex-wrap align-items-center gap-2">';
            if ($this->get('title')) {
                $html .= '<h5 class="card-title mb-0 me-2">' . $this->e($this->get('title')) . '</h5>';
            }
            if ($searchable) {
                // 模板同款 app-search 搜索框（图标覆盖样式复用画廊段的定位规则）
                $html .= '<div class="app-search xf-card-search"><input type="search" class="form-control" placeholder="搜索订单号 / 客户…" data-xftable-search autocomplete="off"><i class="ti ti-search app-search-icon text-muted"></i></div>';
            }
            $html .= '<div class="d-flex flex-wrap align-items-center gap-2 ms-auto">';
            if ($filterable) {
                // 付款状态筛选（匹配行 data-paid）
                $html .= '<select class="form-select form-select-sm w-auto" data-xftable-filter="paid">'
                    . '<option value="">付款状态</option><option value="1">已付款</option><option value="0">未付款</option><option value="refund">退款中</option></select>';
                // 订单状态筛选（匹配行 data-status）
                $html .= '<select class="form-select form-select-sm w-auto" data-xftable-filter="status"><option value="">订单状态</option>';
                foreach (self::STATUS as $key => [, $label]) {
                    $html .= '<option value="' . $key . '">' . $label . '</option>';
                }
                $html .= '</select>';
            }
            if ($pageSize > 0) {
                $html .= '<select class="form-select form-select-sm w-auto" data-xftable-pagesize>'
                    . '<option value="10"' . ($pageSize === 10 ? ' selected' : '') . '>10 条/页</option>'
                    . '<option value="20"' . ($pageSize === 20 ? ' selected' : '') . '>20 条/页</option>'
                    . '<option value="50"' . ($pageSize === 50 ? ' selected' : '') . '>50 条/页</option></select>';
            }
            if ($selectable) {
                $html .= '<button type="button" class="btn btn-sm btn-danger" data-xftable-delete><i class="ti ti-trash me-1"></i>删除所选</button>';
            }
            $html .= '</div></div>';
        }

        /* ---------- 表格（模板同款类组合与表头样式） ---------- */
        $html .= '<div class="table-responsive"><table class="table table-custom table-select table-hover align-middle mb-0">'
            . '<thead class="bg-light bg-opacity-25 thead-sm"><tr class="text-uppercase fs-xxs">';
        if ($selectable) {
            $html .= '<th class="ps-3" style="width:1%;"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" data-xftable-check-all></th>';
        }
        $html .= '<th>订单号</th><th>日期</th><th>客户</th><th>金额</th><th>付款状态</th><th>订单状态</th><th>支付方式</th><th class="text-center" style="width:1%;">操作</th>'
            . '</tr></thead><tbody>';

        foreach ($orders as $o) {
            $o      = (array) $o;
            $status = (string) ($o['status'] ?? 'pending');
            [$sCls, $sTxt] = self::STATUS[$status] ?? self::STATUS['pending'];
            // 付款状态归一化：true/1=已付款、'refund'=退款中、其余=未付款
            $paidRaw = $o['paid'] ?? in_array($status, ['completed', 'processing'], true);
            $paidKey = $paidRaw === 'refund' ? 'refund' : ($paidRaw ? '1' : '0');

            // 行 data-* 供 xftable 下拉筛选精确匹配
            $html .= '<tr data-status="' . $this->e($status) . '" data-paid="' . $paidKey . '">';

            if ($selectable) {
                $html .= '<td class="ps-3"><input class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"></td>';
            }

            // 订单号：模板同款 h5.fs-sm + link-reset
            $html .= '<td><h5 class="fs-sm mb-0 fw-medium"><a href="' . $this->e($o['url'] ?? '#') . '" class="link-reset">' . $this->e($o['id'] ?? '') . '</a></h5></td>';

            // 日期 + 弱化时间
            $html .= '<td>' . $this->e($o['date'] ?? '')
                . (! empty($o['time']) ? ' <small class="text-muted">' . $this->e($o['time']) . '</small>' : '') . '</td>';

            // 客户：.avatar avatar-sm 包裹头像 + 姓名/邮箱（模板同款结构）
            $html .= '<td><div class="d-flex justify-content-start align-items-center gap-2">';
            if (! empty($o['avatar'])) {
                $html .= '<div class="avatar avatar-sm"><img src="' . $this->e(XfAdmin::img((string) $o['avatar'])) . '" alt="" class="img-fluid rounded-circle"></div>';
            } else {
                $ini = mb_substr((string) ($o['customer'] ?? '?'), 0, 1);
                $html .= '<div class="avatar avatar-sm"><span class="avatar-title bg-primary-subtle text-primary rounded-circle">' . $this->e($ini) . '</span></div>';
            }
            $html .= '<div><h5 class="text-nowrap fs-base mb-0 lh-base">' . $this->e($o['customer'] ?? '') . '</h5>'
                . (! empty($o['email']) ? '<p class="text-muted fs-xs mb-0">' . $this->e($o['email']) . '</p>' : '')
                . '</div></div></td>';

            // 金额
            $html .= '<td class="fw-semibold">' . $this->e($o['total'] ?? '') . '</td>';

            // 付款状态：模板同款 ti-point-filled 圆点 + 语义色文字
            $paidMap = ['1' => ['success', '已付款'], '0' => ['danger', '未付款'], 'refund' => ['warning', '退款中']];
            [$pCls, $pTxt] = $paidMap[$paidKey];
            $html .= '<td class="text-' . $pCls . ' fw-semibold"><i class="ti ti-point-filled fs-sm"></i> ' . $pTxt . '</td>';

            // 订单状态：模板同款 badge-soft
            $html .= '<td><span class="badge badge-soft-' . $sCls . ' fs-xxs">' . $sTxt . '</span></td>';

            // 支付方式
            $html .= '<td>' . $this->e($o['payment'] ?? '-') . '</td>';

            // 操作：模板同款圆形浅色图标钮（查看/编辑/删除）
            $html .= '<td><div class="d-flex justify-content-center gap-1">'
                . '<a href="' . $this->e($o['url'] ?? '#') . '" class="btn btn-light btn-icon btn-sm rounded-circle" title="查看"><i class="ti ti-eye fs-lg"></i></a>'
                . '<a href="#" class="btn btn-light btn-icon btn-sm rounded-circle" title="编辑"><i class="ti ti-edit fs-lg"></i></a>'
                . '<a href="#" data-xftable-delete-row class="btn btn-light btn-icon btn-sm rounded-circle" title="删除"><i class="ti ti-trash fs-lg"></i></a>'
                . '</div></td>';

            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        /* ---------- 卡片脚：统计信息 + 分页（对齐模板 card-footer） ---------- */
        if ($pageSize > 0) {
            $html .= '<div class="card-footer border-0"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2">'
                . '<div class="text-muted fs-sm" data-xftable-info></div>'
                . '<div data-xftable-pagination></div>'
                . '</div></div>';
        }

        return $html . '</div>';
    }
}

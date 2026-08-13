<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Table;

use zxf\XfAdmin\Support\Html;

/**
 * 全功能数据表格（基于 DataTables，前端由 xfadmin.js 自动初始化）
 *
 * 支持：搜索、排序、分页、多选、导出（Excel/CSV/打印/PDF）、固定表头、
 * 响应式折叠、列筛选、AJAX / 服务端模式、超丰富单元格渲染器、行操作栏等
 *
 * XfAdmin::dataTable([
 *     'columns' => [
 *         'id'     => ['label' => 'ID', 'sortable' => true],
 *         'name'   => ['label' => '姓名', 'render' => 'input'],                    // 单元格输入框
 *         'token'  => ['label' => '令牌', 'render' => 'copy'],                     // 带复制按钮
 *         'ip'     => ['label' => 'IP', 'render' => 'ip'],                        // IP 展示（等宽字体 + 复制）
 *         'status' => ['label' => '状态', 'render' => ['type' => 'switch', 'url' => '/api/toggle/{id}']],  // 状态切换
 *         'roles'  => ['label' => '角色', 'render' => 'tags'],                     // 标签组
 *         'color'  => ['label' => '颜色', 'render' => 'color'],                    // 颜色块
 *         'level'  => ['label' => '级别', 'badges' => ['ERROR' => 'danger']],      // 徽章映射
 *         'op'     => ['label' => '操作', 'actions' => [                          // 行操作栏
 *             ['label' => '编辑', 'icon' => 'ti ti-edit', 'action' => 'edit',     // 模态表单行编辑
 *              'ajax' => '/api/users/{id}', 'method' => 'PUT', 'fields' => [      // 省略 fields 时按行数据自动生成
 *                  ['name' => 'nickname', 'label' => '昵称', 'required' => true],
 *                  ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => [...]],
 *                  ['name' => 'vip', 'label' => 'VIP', 'type' => 'switch'],
 *              ]],
 *             ['label' => '删除', 'icon' => 'ti ti-trash', 'class' => 'btn-soft-danger',
 *              'ajax' => '/del/{id}', 'method' => 'DELETE', 'confirm' => '确认删除？', 'reload' => true],
 *             ['label' => '详情', 'action' => 'view'],
 *             ['label' => '复制行', 'action' => 'copy-row'],
 *         ]],
 *     ],
 *     'data'        => [...],                 // 本地数据；或使用 ajax
 *     'ajax'        => '/api/users',          // 远程数据（返回 {data: [...]}）
 *     'server_side' => false,                 // 服务端分页/排序/搜索（配合 Support\DataSet）
 *     'method'      => 'GET',                 // 服务端数据请求方式；POST 规避超长 URL 被 WAF/服务器拒绝
 *     'buttons'     => ['copy', 'csv', 'excel', 'print', 'colvis', 'refresh'],
 *     'select'      => true | 'multi' | 'single',
 *     'fixed_header'   => true,
 *     'responsive'     => true,
 *     'column_filters' => true,               // 每列底部筛选输入框
 *     'row_id'      => 'id',                  // 行 id 字段（行操作定位用）
 *     'options'     => [],                    // 透传 DataTables 原生配置（最高优先级）
 * ])
 *
 * 内置单元格渲染器（render 可为字符串或 ['type' => ..., ...配置]）：
 *   input | copy | ip | switch | toggle | tags | color | image | images | avatar | user |
 *   progress | percent | bool | link | code | datetime | money | number | filesize |
 *   truncate | rating | icon | enum | email | phone | url | select | status | trend |
 *   sparkline | timeline | tooltip | popover | dropdown | buttons | actions
 *
 * 交互/展示渲染器补充说明：
 *   - tooltip   悬浮提示：['type' => 'tooltip', 'text' => '注册于 {created_at}', 'length' => 20]
 *   - popover   气泡提示（点击弹出）：['type' => 'popover', 'title' => '备注', 'content' => '{remark}']
 *   - toggle    按钮式状态切换（点击提交后端）：['type' => 'toggle', 'url' => '/api/x/{id}/toggle', 'on_label' => '已启用', 'off_label' => '已停用']
 *   - status    彩色状态点：['type' => 'status', 'map' => ['active' => ['label' => '运行中', 'color' => 'success']]]
 *   - trend     涨跌趋势（红降绿升）：['type' => 'trend', 'suffix' => '%']
 *   - sparkline 迷你趋势图（内联 SVG）：['type' => 'sparkline', 'type' => 'line|bar']，值为数字数组
 *   - timeline  单元格时间线（超出弹窗查看全部）：值为 [{time,title,color}]
 *   - dropdown  点击按钮展开下拉操作组：['type' => 'dropdown', 'label' => '操作', 'items' => [...同 actions 子项]]
 *
 * 行操作（actions 子项）闭环能力：
 *   - action=view    详情弹窗；'view' => [...] 可个性化布局（layout: kv|profile|tabs|sections|template、
 *                    header、sections（kv/table/timeline/stats/tags/progress/images/html）、ajax 详情接口）
 *   - action=edit    模态表单编辑；'ajax' + 'fields' 保存后 PUT 提交并自动刷新表格
 *   - action=delete  删除闭环；'ajax' 指定后端 DELETE 接口，确认后请求并刷新；本地数据源省略 ajax 时直接移除行
 */
class DataTable extends Table
{
    /** 快捷渲染器列键（列定义中出现即转为 render 配置） */
    protected const RENDER_SHORTCUTS = [
        'switch', 'copy', 'tags', 'color', 'progress', 'link', 'image', 'enum', 'user',
        'toggle', 'tooltip', 'popover', 'status', 'trend', 'sparkline', 'timeline', 'dropdown',
        // page：单元格文本变为可点击链接，点击弹窗加载后端页面（URL 支持 {字段} 占位），
        // 关闭弹窗后可选刷新表格（['url'=>..,'title'=>..,'size'=>..,'frame'=>..,'reload'=>true]）
        'page',
        // 丰富多彩的单元格渲染器（前端 XFAdmin.cellRenderers 对应实现）
        'badge', 'statusPill', 'priority', 'rate', 'duration', 'currency', 'json', 'copyBtn',
        'linkBtn', 'miniBar', 'progressBar', 'sparkbar', 'heatmap', 'ranking', 'progressSteps',
        'gradient', 'tagInput', 'avatarStack', 'rich',
    ];

    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            // 与 INSPINIA 后台模板保持一致的表格外观：
            // 模板中所有 DataTable 均为 `table table-striped dt-responsive align-middle mb-0`
            // 且表头为 `<thead class="thead-sm text-uppercase fs-xxs">`。
            // 此前本组件默认输出裸 `table`，导致演示系统的用户列表等表格
            // 无斑马纹、无垂直居中、表头字号/字重与模板明显不同。
            'striped'        => true,
            'align_middle'   => true,
            'head_variant'   => null,     // 保留 table-light/dark 语义；模板风格表头见 head_class
            'head_class'     => 'thead-sm text-uppercase fs-xxs',
            'ajax'           => null,
            'server_side'    => false,
            'method'         => 'GET',    // 服务端数据请求方式（POST 可规避 URL 长度限制/WAF）
            'searching'         => false,   // 默认关闭内置搜索，改用自定义搜索表单
            'ordering'          => true,
            'paging'            => true,
            'info'              => true,
            'processing'        => true,    // 默认显示加载处理提示
            'page_length'       => 10,
            'length_menu'       => [10, 15, 20, 25, 50],  // 标准每页条数菜单
            'show_custom_search' => true,   // 启用自定义搜索表单
            'row_detail'        => null,    // 行详情展开 callback / 列名（null=不启用）
            'show_header_btn'   => null,    // 表头按钮区域 ['create'=>'/url','refresh'=>true,'search'=>true]
            'created_row'       => null,    // createdRow 回调（JS 函数名/全局函数引用）
            'draw_callback'     => null,    // drawCallback 全局函数名
            'buttons'           => [],
            'select'            => false,
            'fixed_header'   => false,
            'responsive'     => false,  // 默认关闭响应式折叠（避免列被收进 child 子行），依赖 scrollX 水平滚动处理溢出列；row_detail 启用时仍互斥
            'column_filters' => false,
            'order'          => [],       // [[0, 'asc']]
            'language'       => null,     // DataTables 语言包配置数组（缺省内置中文）
            'row_id'         => null,     // 行 id 字段
            'row_url'        => null,     // 整行点击跳转 URL（支持 {id} 占位符，如 '/admin/app/{module}/{page}/detail/{id}'）
            'auto_width'     => null,
            'scroll_x'       => true,     // 默认启用横向滚动，溢出列通过滚动条展示（scroll_x=false 可关闭）
            'fixed_columns'  => null,     // 固定列：true=左1列；['left'=>2,'right'=>1]（自动开启横向滚动）
            'filter_bar'     => [],       // 过滤工具栏（服务端模式自动拼接查询参数并重载）
            'filter_auto'    => false,    // 过滤工具栏是否即改即查（默认点击“搜索”按钮才发起请求）
            'create'         => null,     // 「新增」弹窗按钮：'/xx/create' 或 ['page'=>..,'label'=>..,'title'=>..,'size'=>..,'frame'=>..,'icon'=>..,'class'=>..]
            'density'        => null,     // 'compact' | 'comfortable'（行间距密度；默认舒适）
            'options'        => [],
        ]);
    }

    protected function assets(): array
    {
        $assets = ['datatables'];
        // buttons 与 export 中任一处包含 pdf 即加载 pdfmake 资源（excel 依赖的 jszip 已在基础组）
        $export  = $this->get('export');
        $allBtns = array_merge(
            (array) $this->get('buttons', []),
            $export === true ? ['pdf'] : (is_array($export) ? $export : [])
        );
        foreach ($allBtns as $btn) {
            if ((is_string($btn) ? $btn : ($btn['extend'] ?? '')) === 'pdf') {
                $assets[] = 'datatables-pdf';
            }
        }
        // 过滤工具栏含多选/select2 控件时加载 select2（多选交互体验更好）
        foreach ((array) $this->get('filter_bar', []) as $c) {
            if (is_array($c) && (! empty($c['multiple']) || ($c['type'] ?? '') === 'select2')) {
                $assets[] = 'select2';
                break;
            }
        }

        // 二维码渲染器按需依赖 qrcode 库
        foreach ((array) $this->get('columns', []) as $col) {
            if (! is_array($col)) continue;
            $r = $this->columnRender($col);
            if ($r && ($r['type'] ?? null) === 'qr') { $assets[] = 'qrcode'; break; }
        }

        return $assets;
    }

    /** 解析单列的 xfRender 配置（render / actions / 快捷键） */
    protected function columnRender(array $col): ?array
    {
        // 行操作栏
        if (isset($col['actions']) && is_array($col['actions'])) {
            return ['type' => 'actions', 'items' => array_values($col['actions'])];
        }
        // 显式 render
        if (isset($col['render'])) {
            return is_array($col['render']) ? $col['render'] + ['type' => 'text'] : ['type' => (string) $col['render']];
        }
        // 快捷键：'switch' => '/url'、'copy' => true、'tags' => true ...
        foreach (self::RENDER_SHORTCUTS as $key) {
            if (isset($col[$key]) && $col[$key] !== false) {
                $cfg = ['type' => $key];
                if (is_string($col[$key])) {
                    $cfg['url'] = $col[$key];
                } elseif (is_array($col[$key])) {
                    $cfg += $col[$key];
                }

                return $cfg;
            }
        }

        return null;
    }

    protected function html(): string
    {
        $id      = $this->resolveId('xf-datatable');
        $columns = $this->columns();

        // 交互增强：按需在最前插入辅助列
        // ① row_detail 行明细列
        if ($this->get('row_detail')) {
            array_unshift($columns, [
                'key'        => '',
                'label'      => '',
                'class'      => 'xf-dt-detail-col',
                'sortable'   => false,
                'searchable' => false,
            ]);
        }
        // ② show_detail dt-control 箭头独立列（不占用业务列——原列数据保持不变）
        foreach ($columns as $idx => $col) {
            if (! empty($col['show_detail'])) {
                $detailCfg = $col['show_detail'];
                array_splice($columns, $idx, 0, [[
                    'key'             => '',
                    'label'           => '',
                    'class'           => 'dt-control',
                    'sortable'        => false,
                    'searchable'      => false,
                    '_xf_dt_control'  => $detailCfg,
                ]]);
                break;
            }
        }
        // ③ bulk 批量选择列（最后 unshift，保持最左）
        if ($this->get('bulk')) {
            array_unshift($columns, [
                'key'        => '',
                'label'      => '',
                'class'      => 'xf-dt-select-col',
                'sortable'   => false,
                'searchable' => false,
            ]);
        }

        // ---------- 组装 DataTables 配置 ----------
        $dtColumns = [];
        foreach ($columns as $col) {
            $c = ['data' => $col['key'] !== '' ? $col['key'] : null];
            if (isset($col['sortable'])) {
                $c['orderable'] = (bool) $col['sortable'];
            }
            if (isset($col['searchable'])) {
                $c['searchable'] = (bool) $col['searchable'];
            }
            if (isset($col['visible'])) {
                $c['visible'] = (bool) $col['visible'];
            }
            if (isset($col['width'])) {
                $c['width'] = $col['width'];
            }
            if (isset($col['minWidth'])) {
                $c['minWidth'] = $col['minWidth'];
            }
            if (isset($col['class'])) {
                $c['className'] = $col['class'];
            }
            // dt-control 箭头独立辅助列（_xf_dt_control 为内部标记，传递 showDetail 配置）
            if (! empty($col['_xf_dt_control'])) {
                $val = $col['_xf_dt_control'];
                if (is_bool($val) || strtolower((string) $val) === 'true') {
                    $c['showDetail'] = true;
                } elseif (is_callable($val)) {
                    $c['showDetail'] = $val;
                } elseif (is_string($val)) {
                    $c['showDetail'] = $val;
                }
            }
            // 业务列的 show_detail：仅保留 showDetail 标记（dt-control 箭头已由独立辅助列承载）
            if (! empty($col['show_detail'])) {
                $c['showDetail'] = true;
            }
            // 模板渲染：{field} 占位符（由 xfadmin.js 解析）
            if (isset($col['template'])) {
                $c['xfTemplate'] = $col['template'];
            }
            // 徽章映射：值 => bootstrap 颜色
            if (isset($col['badges'])) {
                $c['xfBadges'] = $col['badges'];
            }
            // 富单元格渲染器
            $render = $this->columnRender($col);
            if ($render !== null) {
                $c['xfRender'] = $render;
                // 操作/交互列默认不排序不搜索
                if (in_array($render['type'], ['actions', 'buttons', 'dropdown', 'switch', 'toggle', 'input', 'select', 'sparkline', 'timeline'], true)) {
                    $c['orderable']  ??= isset($col['sortable']) ? (bool) $col['sortable'] : false;
                    $c['searchable'] ??= isset($col['searchable']) ? (bool) $col['searchable'] : false;
                }
                // data 为空的操作列使用整行数据
                if ($col['key'] === '' || ! isset($col['key'])) {
                    $c['data'] = null;
                }
            }
            // 任何 data 为空的列（操作列 / 明细列 / 选择列）都必须提供 defaultContent，
            // 否则 DataTables 会对每行抛出 "Requested unknown parameter" 警告
            if ($c['data'] === null) {
                $c['defaultContent'] ??= '';
            }
            $dtColumns[] = $c;
        }

        $config = [
            'columns'    => $dtColumns,
            'searching'  => (bool) $this->get('searching'),
            'ordering'   => (bool) $this->get('ordering'),
            'paging'     => (bool) $this->get('paging'),
            'info'       => (bool) $this->get('info'),
            'processing' => (bool) $this->get('processing'),
            'pageLength' => (int) $this->get('page_length'),
            'lengthMenu' => $this->get('length_menu'),
            // 搜索延迟 1200ms + 首尾分页按钮（最佳实践）
            'searchDelay'       => 1200,
            'pagingType'        => 'first_last_numbers',
        ];

        if ($this->get('order') !== []) {
            $config['order'] = $this->get('order');
        }
        if ($this->get('ajax')) {
            $config['ajax'] = $this->get('ajax');
            if ($this->get('server_side')) {
                $config['serverSide'] = true;
                // method=POST：数据请求改用 POST（前端自动附带 CSRF 头），
                // 彻底规避 GET 查询串长度限制 / WAF 拦截
                if (strtoupper((string) $this->get('method', 'GET')) === 'POST' && is_string($config['ajax'])) {
                    $config['ajax'] = ['url' => $config['ajax'], 'type' => 'POST'];
                }
            }
        } else {
            $config['data'] = array_values((array) $this->get('data', []));
        }
        if ($this->get('fixed_header')) {
            $config['fixedHeader'] = true;
        }
        // 行明细展开与响应式展开都占用首列，二者互斥；启用明细时关闭响应式
        if ($this->get('responsive') && ! $this->get('row_detail')) {
            $config['responsive'] = true;
        }
        // 横向滚动：默认始终启用 scrollX + scrollY 联合模式。
        // 关键：DataTables 2.x 在 scrollX+scrollY 联合模式下创建协调的 .dt-scroll-head/.dt-scroll-body
        // 双表结构，列宽在固定高度容器内统一计算→表头表体完美对齐。
        // 仅 scrollX（无 scrollY）时双表独立计算列宽→严重错位。
        // - autoWidth: true + columns.adjust() 同步表头/表体列宽
        // - scrollCollapse: true 让表格高度随内容收缩，不产生多余空白
        // - scrollY: "100%" = 父容器高度，配合 scrollCollapse 多余空间缩至内容高度
        // 用户可通过 scroll_x=false / auto_width=false / scroll_y=false 显式关闭
        $disableScrollX = $this->get('scroll_x') === false || $this->get('auto_width') === false;
        if (! $disableScrollX) {
            $config['scrollX']      = true;
            $config['autoWidth']    = true;
            $config['scrollCollapse'] = true;
            // scrollX + scrollY 联合模式，保证表头表体列宽一致
            if ($this->get('scroll_y') !== false) {
                $config['scrollY'] = $this->get('scroll_y') ? (string) $this->get('scroll_y') : '100%';
            }
        } elseif ($this->get('scroll_x') === true || count($this->columns()) > 10) {
            // 显式指定 scroll_x: true 或列数很多时强制启用
            $config['scrollX']      = true;
            $config['scrollCollapse'] = true;
            if ($this->get('scroll_y') !== false) {
                $config['scrollY'] = $this->get('scroll_y') ? (string) $this->get('scroll_y') : '100%';
            }
        }
        // 延迟渲染：客户端大数据集仅渲染可视行，显著降低首屏 DOM 构建耗时。
        // 阈值 100 行：小表无收益且保持原有 select-all 行为；大表自动开启（可被 defer_render=false 关闭）
        $rowCount = is_array($this->get('data')) ? count((array) $this->get('data')) : 0;
        if ($this->get('defer_render') === true
            || (! $this->get('ajax') && $rowCount >= 100 && $this->get('defer_render') !== false)
        ) {
            $config['deferRender'] = true;
        }
        // 固定列（CSS sticky 实现，无需 FixedColumns 扩展；自动开启横向滚动）
        $fixedCols = $this->get('fixed_columns');
        if ($fixedCols) {
            $config['scrollX'] = true;
            if ($fixedCols === true) {
                $fixedCols = ['left' => 1];
            }
        } else {
            // 自动固定操作列：当表格存在「操作列」（actions/dropdown/buttons 类型）时，
            // 默认将最后一列（操作列）右固定，避免横向滚动/冻结列场景下「更多」下拉被裁剪飞出。
            // 同时满足「列较多时操作列固定展示」的产品需求（用户列表 / 管理员列表等统一行为）。
            $opCols = array_filter((array) $this->columns(), function ($c) {
                // 行操作栏（actions 键）或显式 render 类型为操作型
                if (isset($c['actions']) && is_array($c['actions'])) {
                    return true;
                }
                $r = $c['render'] ?? null;
                $t = is_array($r) ? ($r['type'] ?? '') : (is_string($r) ? $r : '');
                return $t === 'actions' || $t === 'dropdown' || $t === 'buttons';
            });
            if ($opCols !== []) {
                $fixedCols = ['right' => 1];
                $config['scrollX'] = true;
            }
        }
        if ($this->get('auto_width') !== null) {
            $config['autoWidth'] = (bool) $this->get('auto_width');
        }
        if ($this->get('row_id')) {
            $config['rowId'] = (string) $this->get('row_id');
        }
        // 整行点击跳转：把 row_url（支持 {id} 占位符）透传给前端，由 initDataTable 绑定行点击
        if ($this->get('row_url')) {
            $xfConfig['rowUrl'] = (string) $this->get('row_url');
        }
        if ($this->get('select')) {
            $config['select'] = $this->get('select') === true ? ['style' => 'multi'] : ['style' => (string) $this->get('select')];
        }

        // 导出按钮：export 快捷开启（true=全格式；数组=指定格式）；亦支持 buttons 自定义
        $buttons = (array) $this->get('buttons', []);
        $export  = $this->get('export');
        if ($export === true) {
            $buttons = array_merge($buttons, ['copy', 'excel', 'csv', 'pdf', 'print']);
        } elseif (is_array($export)) {
            $buttons = array_merge($buttons, $export);
        }
        if ($buttons !== []) {
            $config['buttons'] = array_map([$this, 'mapButton'], $buttons);
            $config['layout']  = [
                'topStart' => ['buttons'],
                'topEnd'   => ['search'],
            ];
        }
        if ($this->get('language')) {
            $config['language'] = $this->get('language');
        }

        // 状态持久化（DataTables 原生选项：排序/分页/列显隐持久化到 localStorage）
        // 注意：必须在 $config 拷入 $xfConfig 之前设置，否则丢失
        if ($this->get('state_save')) {
            $config['stateSave'] = true;
        }

        // 透传原生配置（最高优先级）
        $config = array_replace_recursive($config, (array) $this->get('options', []));

        $xfConfig = [
            'dt'                => $config,
            'columnFilters'     => (bool) $this->get('column_filters'),
            'processing'        => (bool) $this->get('processing'),
            'showCustomSearch'  => (bool) $this->get('show_custom_search'),
            'createdRow'        => $this->get('created_row'),
            'drawCallback'      => $this->get('draw_callback'),
        ];
        if ((array) $this->get('filter_bar', []) !== []) {
            $xfConfig['filterBar']  = true;
            $xfConfig['filterAuto'] = (bool) $this->get('filter_auto');
        }
        if (! empty($fixedCols) && is_array($fixedCols)) {
            $xfConfig['fixedColumns'] = [
                'left'  => max(0, (int) ($fixedCols['left'] ?? 0)),
                'right' => max(0, (int) ($fixedCols['right'] ?? 0)),
            ];
        }

        // 交互增强接线（供前端 initDataTable 消费）
        if ($this->get('bulk')) {
            $bulkCfg = is_array($this->get('bulk')) ? $this->get('bulk') : [];
            $xfConfig['bulk'] = [
                'checkbox' => $bulkCfg['checkbox'] ?? true,
                'actions'  => array_values($bulkCfg['actions'] ?? []),
            ];
        }
        if ($this->get('row_detail')) {
            $detailCfg = $this->get('row_detail');
            $xfConfig['rowDetail'] = is_array($detailCfg) ? $detailCfg : ['columns' => null];
        }
        if ($this->get('row_group')) {
            // 兼容 'row_group' => 'office'（字段名）与 ['data'=>'office','empty'=>'…']（完整配置）两种写法
            $rg = $this->get('row_group');
            $groupCfg = is_array($rg) ? $rg : ['data' => (string) $rg];
            if (! empty($groupCfg['data'])) {
                $xfConfig['rowGroup'] = $groupCfg;
            }
        }
        // ---------- HTML ----------
        $dtDataset = (string) ($xfConfig['dataset'] ?? $this->get('dataset', ''));
        $html = '<table' . $this->attrs([
            'id'             => $id,
            // 移除 w-100：避免 !important 宽度压制 min-width，确保 table-responsive 在内容/列过多时
            // 能出现水平滚动条；Bootstrap .table 自身已带 width:100%，列少时仍会自然撑满。
            'class'          => $this->tableClass(),
            'data-xf'        => 'datatable',
            'data-xf-dataset'=> $dtDataset !== '' ? $dtDataset : null,
            'data-xf-config' => json_encode($xfConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
        ]) . '>';

        // 表头 class：模板风格（thead-sm text-uppercase fs-xxs）+ 可选 table-light/table-dark
        $theadCls = Html::cls(
            (string) $this->get('head_class', ''),
            $this->get('head_variant') ? 'table-' . $this->enum($this->get('head_variant'), ['light', 'dark', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'striped', 'striped-dark'], 'light') : ''
        );
        $html .= '<thead' . ($theadCls !== '' ? ' class="' . $this->e($theadCls) . '"' : '') . '><tr>';
        foreach ($columns as $col) {
            // th 带上列 class（前端依赖 thead th.xf-dt-select-col 定位全选框等）
            $thCls = (string) ($col['class'] ?? '');
            // 列固定宽度 / 最小宽度：直接落到 th 的内联 style，确保 DataTables 渲染后生效
            $thStyle = '';
            if (isset($col['minWidth'])) {
                $thStyle .= 'min-width:' . $this->e($col['minWidth']) . ';';
            }
            if (isset($col['width'])) {
                $thStyle .= 'width:' . $this->e($col['width']) . ';';
            }
            if (isset($col['style'])) {
                $thStyle .= $this->e($col['style']);
            }
            $thAttr = ($thCls !== '' ? ' class="' . $this->e($thCls) . '"' : '')
                . ($thStyle !== '' ? ' style="' . trim($thStyle) . '"' : '');
            $html .= '<th' . $thAttr . '>' . $this->e($col['label']) . '</th>';
        }
        $html .= '</tr></thead>';
        // 始终输出空 tbody：保证 HTML 结构合法（DataTables 会在客户端/服务端填充行），
        // 避免初始化前出现无 tbody 的无效表格与布局抖动
        $html .= '<tbody></tbody>';
        $html .= '</table>';

        // 水平滚动容器：
        // - DataTables 2.x 启用 scrollX 时，会自动创建 .dt-scroll-body/.dt-scroll-head 结构
        // - 此时再套 .table-responsive 会导致双滚动条，故仅在未开启 scrollX 时包裹
        // - scrollX 默认启用（最佳实践），仅 scroll_x=false 时关闭
        $scrollXDisabled = $this->get('scroll_x') === false || $this->get('auto_width') === false;
        if ($scrollXDisabled && ! $this->get('fixed_columns')) {
            $html = '<div class="table-responsive">' . $html . '</div>';
        }

        // 过滤工具栏（前端自动接线：变更 => 拼接查询参数 => 重载表格）
        $filterBar = (array) $this->get('filter_bar', []);
        if ($filterBar !== []) {
            $html = $this->renderFilterBar($id, $filterBar) . $html;
        }

        // 「新增」弹窗按钮（点击后以弹窗加载服务端新建页面，提交成功自动刷新表格）
        $create = $this->get('create');
        if ($create) {
            $html = $this->renderCreateButton($id, is_array($create) ? $create : ['page' => (string) $create]) . $html;
        }

        // 批量操作栏（选中行后显示，提交至各 action 的 url，{ids} 占位自动替换为选中 id 列表）
        if ($this->get('bulk')) {
            $bk = $this->get('bulk');
            $html = $this->renderBulkToolbar($id, is_array($bk) ? ($bk['actions'] ?? []) : []) . $html;
        }

        return $html;
    }

    /**
     * 渲染「新增」弹窗按钮（依赖前端 [data-xf-page-dialog] 声明式接线）
     *
     * 'create' => '/admin/users/create'
     * 'create' => ['page' => '/admin/users/create', 'label' => '新增用户', 'title' => '新增用户',
     *              'size' => 'lg', 'frame' => false, 'icon' => 'ti ti-plus', 'class' => 'btn-primary']
     */
    protected function renderCreateButton(string $tableId, array $cfg): string
    {
        $page = (string) ($cfg['page'] ?? $cfg['url'] ?? '');
        if ($page === '') {
            return '';
        }
        $label = (string) ($cfg['label'] ?? '新增');
        $icon  = (string) ($cfg['icon'] ?? 'ti ti-plus');
        $cls   = Html::cls('btn btn-sm', (string) ($cfg['class'] ?? 'btn-primary'));

        return '<div class="xf-dt-create mb-2 d-flex">'
            . '<button type="button" class="' . $this->e($cls) . '"'
            . ' data-xf-page-dialog="' . $this->e($page) . '"'
            . ' data-xf-title="' . $this->e((string) ($cfg['title'] ?? $label)) . '"'
            . ' data-xf-size="' . $this->e((string) ($cfg['size'] ?? 'lg')) . '"'
            . (! empty($cfg['frame']) ? ' data-xf-frame' : '')
            . ' data-xf-table="#' . $this->e($tableId) . '">'
            . ($icon !== '' ? '<i class="' . $this->e($icon) . ' me-1"></i>' : '')
            . $this->e($label) . '</button></div>';
    }

    /**
     * 渲染「批量操作」工具栏（依赖前端 XFAdmin.bindBulk 接管选择框与提交）。
     *
     * 工具栏默认隐藏，前端在勾选行后自动显示并展示已选数量；每个动作按钮：
     *   - data-xf-bulk-action  标记该按钮为批量动作
     *   - data-url             目标地址，支持 {ids} 占位符（自动替换为逗号分隔的选中行 id）
     *   - data-method          HTTP 方法（默认 POST）
     *   - data-confirm         提交前的确认文案（为空则不确认）
     *   - data-reload          "0" 表示成功后仅本地重绘、不向服务端重载（默认重载）
     *
     * 'bulk' => ['actions' => [
     *     ['label' => '启用', 'icon' => 'ti ti-check', 'class' => 'btn-soft-success',
     *      'url' => '/admin/users/batch-enable', 'method' => 'POST', 'confirm' => '确认启用选中用户？'],
     * ]]
     */
    protected function renderBulkToolbar(string $tableId, array $actions): string
    {
        // 无动作时仍输出容器（便于后续前端动态注入），但隐藏
        if ($actions === []) {
            return '<div class="xf-dt-bulk d-none card card-body py-2 px-3 mb-2 d-flex align-items-center gap-2"'
                . ' data-dt="' . $this->e($tableId) . '">'
                . '<span class="xf-dt-bulk-count text-muted small">已选 <b class="text-primary">0</b> 项</span></div>';
        }

        $btns = '';
        foreach ($actions as $a) {
            $a      = (array) $a;
            $label  = (string) ($a['label'] ?? '操作');
            $icon   = (string) ($a['icon'] ?? '');
            $cls    = Html::cls('btn btn-sm', (string) ($a['class'] ?? 'btn-outline-secondary'));
            $url    = (string) ($a['url'] ?? '');
            $method = strtoupper((string) ($a['method'] ?? 'POST'));
            $confirm = (string) ($a['confirm'] ?? '');
            $reload = ! isset($a['reload']) || $a['reload'];
            // 批量动作名（后端据此分发领域动作 / 状态流转 / 启用停用 / 删除等）
            $actName = (string) ($a['action'] ?? '');

            $btns .= '<button type="button" class="' . $this->e($cls) . '"'
                . ' data-xf-bulk-action data-url="' . $this->e($url) . '"'
                . ' data-method="' . $this->e($method) . '"'
                . (! empty($confirm) ? ' data-confirm="' . $this->e($confirm) . '"' : '')
                . (! empty($actName) ? ' data-action="' . $this->e($actName) . '"' : '')
                . ' data-reload="' . ($reload ? '1' : '0') . '">'
                . ($icon !== '' ? '<i class="' . $this->e($icon) . ' me-1"></i>' : '')
                . $this->e($label) . '</button>';
        }

        return '<div class="xf-dt-bulk d-none card card-body py-2 px-3 mb-2 d-flex flex-wrap align-items-center gap-2"'
            . ' data-dt="' . $this->e($tableId) . '">'
            . '<span class="xf-dt-bulk-count text-muted small me-1">已选 <b class="text-primary">0</b> 项</span>'
            . '<div class="btn-group btn-group-sm">' . $btns . '</div></div>';
    }

    /**
     * 渲染过滤工具栏
     *
     * 'filter_bar' => [
     *     ['name' => 'status', 'label' => '状态', 'options' => ['on' => '启用', 'off' => '停用']],   // select
     *     ['name' => 'vips', 'label' => 'VIP', 'options' => [...], 'multiple' => true],              // 多选（select2 交互，值逗号连接，配 op=in）
     *     ['name' => 'cate', 'type' => 'select2', 'options' => [...]],         // 单选亦可用 select2（可搜索）
     *     ['name' => 'keyword', 'label' => '关键词', 'type' => 'text', 'placeholder' => '姓名/邮箱'],
     *     ['name' => 'score', 'label' => '积分', 'type' => 'range'],           // 数值区间 => score_min / score_max
     *     ['name' => 'reg', 'label' => '注册时间', 'type' => 'daterange'],     // 日期区间 => reg_from / reg_to
     *     ['name' => 'created', 'type' => 'datetimerange'],                    // 日期时间区间 => created_from / created_to
     *     ['name' => 'date_from', 'label' => '开始日期', 'type' => 'date'],
     *     ['name' => 'at', 'type' => 'datetime'],                              // 日期时间
     *     ['name' => 'time', 'type' => 'time'],                                // 时间
     *     ['name' => 'duty', 'type' => 'timerange'],                           // 时间区间 => duty_from / duty_to
     *     ['name' => 'month', 'type' => 'month'],  ['name' => 'week', 'type' => 'week'],  ['name' => 'year', 'type' => 'year'],
     *     ['name' => 'balance', 'type' => 'number', 'min' => 0, 'step' => 100],
     *     ['name' => 'vip', 'type' => 'checkbox', 'label' => '仅VIP', 'value' => '1'],   // 勾选生效
     *     ['name' => 'level', 'type' => 'radio', 'options' => [...]],          // 按钮组单选
     *     ['name' => 'tags', 'type' => 'checkboxes', 'options' => [...]],      // 复选组（值逗号连接）
     *     ['name' => 'color', 'type' => 'color'],                              // 颜色选择
     * ]
     * 默认点击「搜索」按钮才发起过滤请求（filter_auto=true 可改为即改即查）。
     * 区间控件与 Support\DataSet 的 op 过滤规则（>= <= date_from date_to in between）搭配即可零代码完成服务端过滤。
     */
    protected function renderFilterBar(string $tableId, array $controls): string
    {
        $html = '<form class="row g-2 mb-3 xf-filter-bar" data-xf-filter-for="' . $this->e($tableId) . '" onsubmit="return false;">';
        foreach ($controls as $c) {
            // 开发者完全自定义控件：['html' => '<div>...任意表单控件...</div>', 'width' => 'col-3']
            // 控件内部凡带 class="xf-filter" data-filter="参数名" 的元素都会被自动采集进查询参数
            if (is_array($c) && isset($c['html'])) {
                $html .= '<div class="' . $this->e($c['width'] ?? 'col-6 col-md-3 col-xl-2') . '">' . (string) $c['html'] . '</div>';
                continue;
            }
            if (! is_array($c) || empty($c['name'])) {
                continue;
            }
            $name        = (string) $c['name'];
            $type        = (string) ($c['type'] ?? 'select');
            $width       = (string) ($c['width'] ?? 'col-6 col-md-3 col-xl-2');
            $placeholder = (string) ($c['placeholder'] ?? '');
            $numAttrs    = '';
            foreach (['min', 'max', 'step'] as $na) {
                if (isset($c[$na]) && is_numeric($c[$na])) {
                    $numAttrs .= ' ' . $na . '="' . $this->e($c[$na]) . '"';
                }
            }
            $html .= '<div class="' . $this->e($width) . '">';
            if (! empty($c['label'])) {
                $html .= '<label class="form-label mb-1">' . $this->e($c['label']) . '</label>';
            }
            // 原生日期/时间族控件（浏览器随系统语言自动本地化，中文环境即中文界面）
            $nativeDate = ['date' => 'date', 'datetime' => 'datetime-local', 'time' => 'time', 'month' => 'month', 'week' => 'week'];
            if (isset($nativeDate[$type])) {
                $html .= '<input type="' . $nativeDate[$type] . '" lang="zh-CN" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '" title="' . $this->e($c['label'] ?? $name) . '">';
            } elseif ($type === 'daterange' || $type === 'datetimerange' || $type === 'timerange') {
                $it = ['daterange' => 'date', 'datetimerange' => 'datetime-local', 'timerange' => 'time'][$type];
                $html .= '<div class="input-group input-group-sm flex-nowrap">'
                    . '<input type="' . $it . '" lang="zh-CN" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '_from" title="起始">'
                    . '<span class="input-group-text px-1">~</span>'
                    . '<input type="' . $it . '" lang="zh-CN" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '_to" title="结束">'
                    . '</div>';
            } elseif ($type === 'year') {
                $y    = (int) date('Y');
                $from = (int) ($c['from'] ?? $y - 10);
                $html .= '<select class="form-select form-select-sm xf-filter" data-filter="' . $this->e($name) . '"><option value="">' . $this->e($placeholder !== '' ? $placeholder : '全部年份') . '</option>';
                for ($i = $y; $i >= $from; $i--) {
                    $html .= '<option value="' . $i . '">' . $i . ' 年</option>';
                }
                $html .= '</select>';
            } elseif ($type === 'color') {
                $html .= '<input type="color" class="form-control form-control-sm form-control-color w-100 xf-filter" data-filter="' . $this->e($name) . '" value="' . $this->e($c['value'] ?? '#3e60d5') . '">';
            } elseif ($type === 'checkboxes') {
                $html .= '<div class="xf-filter-checks d-flex flex-wrap gap-2 pt-1" data-filter="' . $this->e($name) . '">';
                foreach ((array) ($c['options'] ?? []) as $v => $t) {
                    $cid = 'xf-fbc-' . $this->e($tableId . '-' . $name . '-' . $v);
                    $html .= '<div class="form-check form-check-inline m-0">'
                        . '<input type="checkbox" class="form-check-input" value="' . $this->e($v) . '" id="' . $cid . '">'
                        . '<label class="form-check-label small" for="' . $cid . '">' . $this->e($t) . '</label></div>';
                }
                $html .= '</div>';
            } elseif ($type === 'number') {
                $html .= '<input type="number" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '" placeholder="' . $this->e($placeholder) . '"' . $numAttrs . '>';
            } elseif ($type === 'range') {
                $html .= '<div class="input-group input-group-sm flex-nowrap">'
                    . '<input type="number" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '_min" placeholder="' . $this->e($c['min_placeholder'] ?? '最小') . '"' . $numAttrs . '>'
                    . '<span class="input-group-text px-1">~</span>'
                    . '<input type="number" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '_max" placeholder="' . $this->e($c['max_placeholder'] ?? '最大') . '"' . $numAttrs . '>'
                    . '</div>';
            } elseif ($type === 'text' || $type === 'search') {
                $html .= '<input type="' . ($type === 'search' ? 'search' : 'text') . '" class="form-control form-control-sm xf-filter" data-filter="' . $this->e($name) . '" placeholder="' . $this->e($placeholder) . '">';
            } elseif ($type === 'checkbox' || $type === 'switch') {
                $html .= '<div class="form-check' . ($type === 'switch' ? ' form-switch' : '') . ' mt-1">'
                    . '<input type="checkbox" class="form-check-input xf-filter" data-filter="' . $this->e($name) . '" value="' . $this->e($c['value'] ?? '1') . '" id="xf-fb-' . $this->e($tableId . '-' . $name) . '">'
                    . '<label class="form-check-label small" for="xf-fb-' . $this->e($tableId . '-' . $name) . '">' . $this->e($c['text'] ?? $placeholder ?: '启用') . '</label>'
                    . '</div>';
            } elseif ($type === 'radio') {
                $html .= '<div class="btn-group btn-group-sm w-100 xf-filter-radio" data-filter="' . $this->e($name) . '" role="group">';
                $html .= '<button type="button" class="btn btn-outline-secondary active" data-value="">全部</button>';
                foreach ((array) ($c['options'] ?? []) as $v => $t) {
                    $html .= '<button type="button" class="btn btn-outline-secondary" data-value="' . $this->e($v) . '">' . $this->e($t) . '</button>';
                }
                $html .= '</div>';
            } elseif ($type === 'slider' || $type === 'range-slider') {
                // 双滑块范围过滤：data-type=slider，值形如 lo-hi（* 表示不限）
                $smin   = $c['min'] ?? 0;
                $smax   = $c['max'] ?? 100;
                $sstep  = $c['step'] ?? 1;
                $suffix = $c['suffix'] ?? '';
                $html .= '<div class="xf-filter xf-slider-filter" data-type="slider" data-filter="' . $this->e($name) . '">'
                    . '<div class="d-flex justify-content-between small text-muted mb-1"><span class="xf-slider-lo-label"></span><span class="xf-slider-hi-label"></span></div>'
                    . '<input type="range" class="xf-slider-lo form-range" min="' . $this->e($smin) . '" max="' . $this->e($smax) . '" step="' . $this->e($sstep) . '" value="' . $this->e($smin) . '" data-suffix="' . $this->e($suffix) . '">'
                    . '<input type="range" class="xf-slider-hi form-range" min="' . $this->e($smin) . '" max="' . $this->e($smax) . '" step="' . $this->e($sstep) . '" value="' . $this->e($smax) . '" data-suffix="' . $this->e($suffix) . '">'
                    . '<input type="hidden" data-xf-min value="' . $this->e($smin) . '">'
                    . '<input type="hidden" data-xf-max value="' . $this->e($smax) . '">'
                    . '</div>';
            } elseif ($type === 'tree') {
                // 树形选择过滤：options 为 [['value'=>..,'label'=>..,'children'=>[...]]] 层级结构
                $html .= '<div class="xf-filter xf-tree-filter" data-type="tree" data-filter="' . $this->e($name) . '">';
                $html .= $this->renderTreeFilter($c['options'] ?? [], $tableId, $name);
                $html .= '</div>';
            } elseif ($type === 'autocomplete') {
                // 自动完成过滤：datalist 提供候选（或 data-url 走远程搜索）
                $remote = $c['url'] ?? '';
                $html .= '<input type="text" class="form-control form-control-sm xf-filter" data-type="autocomplete" data-filter="' . $this->e($name) . '"'
                    . ' placeholder="' . $this->e($placeholder ?: '输入并选择') . '"' . ($remote ? ' data-url="' . $this->e($remote) . '"' : '') . ' list="xf-ac-' . $this->e($tableId . '-' . $name) . '">';
                if (! $remote && ! empty($c['options'])) {
                    $html .= '<datalist id="xf-ac-' . $this->e($tableId . '-' . $name) . '">';
                    foreach ((array) $c['options'] as $v => $t) {
                        $html .= '<option value="' . $this->e($v) . '">' . $this->e($t) . '</option>';
                    }
                    $html .= '</datalist>';
                }
            } elseif ($type === 'custom') {
                // 完全自定义过滤控件：开发者传入 'control' => '<任意带 class="xf-filter" data-filter="x" 的 HTML>'
                // 前端通过 data-xf-custom-value 或 xf:filter-custom 事件获取值
                $html .= '<div class="xf-filter xf-filter-custom" data-filter="' . $this->e($name) . '">' . (string) ($c['control'] ?? '') . '</div>';
            } else {
                // select / select2（multiple 时自动启用 select2 多选交互）
                $multiple = ! empty($c['multiple']);
                $useS2    = $multiple || $type === 'select2';
                $html .= '<select class="form-select form-select-sm xf-filter' . ($useS2 ? ' xf-filter-s2' : '') . '" data-filter="' . $this->e($name) . '"'
                    . ($multiple ? ' multiple' : '')
                    . ($useS2 ? ' data-placeholder="' . $this->e($placeholder !== '' ? $placeholder : '全部') . '"' : '') . '>'
                    . ($multiple ? '' : '<option value="">' . $this->e($placeholder !== '' ? $placeholder : '全部') . '</option>');
                foreach ((array) ($c['options'] ?? []) as $v => $t) {
                    $html .= '<option value="' . $this->e($v) . '">' . $this->e($t) . '</option>';
                }
                $html .= '</select>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="col-12 col-md-auto d-flex align-items-end gap-2 ms-md-auto">'
            . '<button type="submit" class="btn btn-sm btn-primary px-3 xf-filter-search"><i class="ti ti-search"></i> 搜索</button>'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-filter-reset"><i class="ti ti-rotate"></i> 重置</button></div>';
        $html .= '</form>';

        return $html;
    }

    /** 递归渲染树形过滤控件 */
    protected function renderTreeFilter(array $nodes, string $tableId, string $name, int $depth = 0): string
    {
        $html = '';
        foreach ($nodes as $node) {
            if (! is_array($node)) continue;
            $v    = (string) ($node['value'] ?? '');
            $t    = (string) ($node['label'] ?? $v);
            $cid  = 'xf-tree-' . $this->e($tableId . '-' . $name . '-' . $v);
            $children = $node['children'] ?? [];
            $html .= '<div class="xf-tree-node' . ($depth ? ' ps-3' : '') . '">'
                . '<div class="form-check m-0">'
                . '<input type="checkbox" class="form-check-input xf-tree-leaf" value="' . $this->e($v) . '" id="' . $cid . '">'
                . '<label class="form-check-label small" for="' . $cid . '">' . $this->e($t) . '</label>'
                . ($children ? ' <i class="ti ti-chevron-down xf-tree-toggle small"></i>' : '')
                . '</div>';
            if ($children) {
                $html .= '<div class="xf-tree-children">' . $this->renderTreeFilter($children, $tableId, $name, $depth + 1) . '</div>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    /** 按钮映射：字符串简写 => DataTables 按钮配置（refresh/colvis/fullscreen 为包内扩展按钮） */
    protected function mapButton(mixed $b): array
    {
        if (! is_string($b)) {
            return (array) $b;
        }

        // 包内扩展按钮（由 xfadmin.js 转换为带 action 的真实按钮）
        if (in_array($b, ['refresh', 'fullscreen', 'density'], true)) {
            return ['xfButton' => $b, 'className' => 'btn btn-sm btn-secondary xf-btn-' . $b];
        }

        // DataTables 原生导出/列显隐按钮（需 datatables 资源的 buttons.html5 / print）
        $map = [
            'copy'   => ['extend' => 'copyHtml5',   'text' => '<i class="ti ti-copy"></i> 复制',     'className' => 'btn btn-soft-secondary btn-sm'],
            'csv'    => ['extend' => 'csvHtml5',    'text' => '<i class="ti ti-file-text"></i> CSV', 'className' => 'btn btn-soft-secondary btn-sm'],
            'excel'  => ['extend' => 'excelHtml5',  'text' => '<i class="ti ti-file-spreadsheet"></i> Excel', 'className' => 'btn btn-soft-success btn-sm'],
            'pdf'    => ['extend' => 'pdfHtml5',    'text' => '<i class="ti ti-file-type-pdf"></i> PDF', 'className' => 'btn btn-soft-danger btn-sm'],
            // 注意：DataTables 打印按钮的类型名是 print（不存在 printHtml5，误用会抛
            // “Cannot extend unknown button type: printHtml5” 导致整表初始化失败）
            'print'  => ['extend' => 'print',       'text' => '<i class="ti ti-printer"></i> 打印',  'className' => 'btn btn-soft-info btn-sm'],
            'colvis' => ['extend' => 'colvis',      'text' => '<i class="ti ti-columns"></i> 列显示', 'className' => 'btn btn-soft-secondary btn-sm', 'columns' => ':not(.no-export)'],
        ];
        if (array_key_exists($b, $map)) {
            return $map[$b];
        }

        // 未知 key 作为自定义集合按钮
        return ['extend' => 'collection', 'text' => $b, 'className' => 'btn btn-soft-secondary btn-sm'];
    }

    /** 行间距密度：density='compact' 时追加紧凑类（由 xfadmin.css 收紧单元格内边距） */
    protected function tableClass(): string
    {
        $cls = parent::tableClass();
        // xf-datatable：本组件专属标识类，xfadmin.css 的 DataTable 样式均作用域于此，
        // 避免污染页面内其它 .table 表格。
        $cls .= ' xf-datatable';
        // 与模板一致：模板所有 DataTable 均为 `table table-striped dt-responsive align-middle mb-0`
        $cls .= ' mb-0';
        // 行明细展开与响应式展开都占用首列，二者互斥；启用明细时不输出 dt-responsive 类
        if ($this->get('responsive') && ! $this->get('row_detail')) {
            $cls .= ' dt-responsive';
        }
        if (($this->get('density') ?? 'comfortable') === 'compact') {
            $cls .= ' xf-dt-compact';
        }

        return $cls;
    }
}

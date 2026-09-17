<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Support;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\XfAdmin;

/**
 * DiyLayoutPage 服务端渲染助手
 *
 * 负责把编辑器（前端）维护的「块树」转换为真实 HTML：
 *   - 组件块：调用 XfAdmin::component($alias, $options) 渲染；
 *   - 容器块：row / col 直接输出 Bootstrap 栅格包裹（无需 PHP 组件）；
 *   - 模板：本质也是块树，复用同一渲染逻辑。
 *
 * 渲染模式：
 *   - block：渲染单个组件块，返回 {html, css[], js[]}（css/js 为完整资源 URL，
 *            供前端 XFAdmin.load() 动态补齐依赖后再 XFAdmin.scan() 初始化）；
 *   - page ：渲染整棵块树，返回干净（不含编辑器外壳）的 HTML 片段；
 *   - export：把整棵块树包进 XfAdmin::page() 输出完整可独立运行的页面文档。
 */
final class DiyRenderer
{
    /** 单棵块树允许的最大块数（防滥用） */
    private const MAX_BLOCKS = 600;

    /** 块树最大嵌套深度 */
    private const MAX_DEPTH = 10;

    /** 分类命名空间 → 中文分类名 */
    private const CATEGORIES = [
        'Layout'     => '布局',
        'Navigation' => '导航',
        'Grid'       => '栅格',
        'UI'         => '基础 UI',
        'Form'       => '表单',
        'Chart'      => '图表',
        'Table'      => '表格',
        'Data'       => '业务数据',
        'Misc'       => '杂项',
    ];

    /** 全局候选值（变体 / 尺寸） */
    private const VARIANT_OPTIONS = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'none'];
    private const SIZE_OPTIONS    = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'];

    /** 组件别名 → 中文展示名（未列出的回退到别名自动分词） */
    private const LABELS = [
        'page'        => '整页骨架',
        'sidenav'     => '侧边导航',
        'topbar'      => '顶部导航',
        'topNav'      => '水平导航',
        'pageTitle'   => '页面标题',
        'footer'      => '页脚',
        'customizer'  => '主题定制面板',
        'authPage'    => '认证页',
        'errorPage'   => '错误页',
        'comingSoon'  => '即将上线',
        'maintenance' => '维护页',
        'emptyState'  => '空状态页',
        'landing'     => '落地页',
        'menu'        => '菜单数据',
        'row'         => '栅格行',
        'col'         => '栅格列',
        'card'        => '卡片',
        'statCard'    => '指标卡',
        'table'       => '静态表格',
        'dataTable'   => '数据表格',
        'form'        => '表单',
        'input'       => '输入框',
        'textarea'    => '多行文本',
        'select'      => '下拉框',
        'check'       => '复选/单选',
        'slider'      => '滑块',
        'dateRange'   => '日期范围',
        'datePicker'  => '日期选择',
        'editor'      => '富文本',
        'upload'      => '文件上传',
        'colorPicker' => '颜色选择',
        'tags'        => '标签输入',
        'wizard'      => '向导',
        'alert'       => '警告提示',
        'badge'       => '徽章',
        'button'      => '按钮',
        'dropdown'    => '下拉菜单',
        'modal'       => '模态框',
        'offcanvas'   => '抽屉面板',
        'tabs'        => '选项卡',
        'accordion'   => '手风琴',
        'progress'    => '进度条',
        'spinner'     => '加载指示器',
        'pagination'  => '分页器',
        'listGroup'   => '列表组',
        'avatar'      => '头像',
        'icon'        => '图标',
        'toast'       => '轻提示',
        'timeline'    => '时间线',
        'carousel'    => '轮播图',
        'breadcrumb'  => '面包屑',
        'tooltip'     => '文字提示',
        'popover'     => '弹出框',
        'ribbon'      => '缎带角标',
        'chip'        => '筹码标签',
        'stepper'     => '步骤条',
        'rating'      => '评分',
        'switch'      => '开关',
        'codeBlock'   => '代码块',
        'empty'       => '空状态',
        'toolbar'     => '工具栏',
        'searchBox'   => '搜索框',
        'countdown'   => '倒计时',
        'countUp'     => '数字滚动',
        'backToTop'   => '回到顶部',
        'callout'     => '强调提示',
        'divider'     => '分割线',
        'kbd'         => '键盘按键',
        'media'       => '媒体对象',
        'skeleton'    => '骨架屏',
        'typography'  => '排版展示',
        'utilities'   => '工具类',
        'iconSet'     => '图标集',
        'colorPalette'=> '配色方案',
        'videoEmbed'  => '视频嵌入',
        'apexChart'   => 'Apex 图表',
        'echart'      => 'ECharts 图表',
        'leafletMap'  => 'Leaflet 地图',
        'vectorMap'   => '矢量地图',
        'googleMap'   => 'Google 地图',
        'pricingCard' => '价格方案卡',
        'faq'         => '常见问题',
        'profileHeader'=> '个人资料头',
        'productCard' => '商品卡',
        'kanban'      => '看板',
        'chatBox'     => '聊天框',
        'widget'      => '小部件卡',
        'gallery'     => '图片画廊',
        'blogList'    => '博客列表',
        'commentThread'=> '评论线程',
        'statMiniSparkline' => '迷你指标',
    ];

    /**
     * 生成组件「属性面板字段 schema」（供右侧操作区渲染完整可编辑表单）。
     *
     * 策略：① 优先取手工标注的 curatedFields()（含中文标签 / 候选值 / 类型）；
     *       ② 其余字段由 defaults() 推断类型（bool/number/json/text/select…）。
     * 这样无论组件是否声明 schema，右侧面板都能把「全部配置参数」呈现给用户编辑。
     */
    private static function buildSchema(string $alias, array $defaults): array
    {
        $curated = self::curatedFields()[$alias] ?? [];
        $schema  = [];
        foreach ($curated as $f) {
            if (is_array($f) && isset($f['name'])) {
                $schema[$f['name']] = $f;
            }
        }
        foreach ($defaults as $k => $v) {
            if (isset($schema[$k])) {
                continue;
            }
            $schema[$k] = self::inferField($k, $v);
        }

        return array_values($schema);
    }

    /**
     * 由默认值推断字段类型（与前端 specialType 保持语义一致）
     *
     * 通过字段名语义识别「候选值 / 控件类型 / 分组 / 数据格式提示」，
     * 让即使未手工标注的组件，右侧面板也能呈现完整、友好的可编辑表单。
     */
    private static function inferField(string $key, mixed $value): array
    {
        $k     = strtolower($key);
        $label = self::humanLabel($key);

        if (is_bool($value)) {
            return ['name' => $key, 'type' => 'bool', 'label' => $label, 'group' => '基础', 'hint' => '开关选项'];
        }
        if (is_int($value) || is_float($value)) {
            return ['name' => $key, 'type' => 'number', 'label' => $label, 'group' => '基础'];
        }
        if (is_array($value) || is_object($value)) {
            return ['name' => $key, 'type' => 'json', 'label' => $label, 'group' => '数据', 'hint' => self::jsonHint($k)];
        }
        $str = (string) $value;

        if (preg_match('/^(variant|theme)$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '样式', 'options' => self::VARIANT_OPTIONS, 'hint' => '配色变体'];
        }
        if ($k === 'size') {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '样式', 'options' => self::SIZE_OPTIONS, 'hint' => '尺寸规格'];
        }
        if (preg_match('/^(placement|position)$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '样式', 'options' => ['start', 'end', 'top', 'bottom', 'left', 'right', 'top-start', 'top-end', 'bottom-start', 'bottom-end'], 'hint' => '方位 / 浮层位置'];
        }
        if (preg_match('/^(align|alignment|valign|text_align)$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '样式', 'options' => ['start', 'center', 'end', 'between', 'around'], 'hint' => '对齐方式'];
        }
        if (preg_match('/^(direction|orientation)$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '布局', 'options' => ['horizontal', 'vertical', 'up', 'down', 'left', 'right'], 'hint' => '排列方向'];
        }
        if (preg_match('/^trigger$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '行为', 'options' => ['click', 'hover', 'focus', 'manual'], 'hint' => '触发方式'];
        }
        if (preg_match('/^(type|chart_type)$/i', $k)) {
            return ['name' => $key, 'type' => 'select', 'label' => $label, 'group' => '数据', 'options' => ['bar', 'line', 'area', 'pie', 'donut', 'radar', 'scatter', 'bubble'], 'hint' => '图表类型'];
        }
        if (preg_match('/(icon|ico)$/i', $k)) {
            return ['name' => $key, 'type' => 'icon', 'label' => $label, 'group' => '样式', 'hint' => 'Tabler 图标，如 ti ti-star'];
        }
        if (preg_match('/(image|img|avatar|cover|logo|background|bg|thumbnail|picture|photo|poster|src)$/i', $k)) {
            return ['name' => $key, 'type' => 'image', 'label' => $label, 'group' => '样式', 'hint' => '图片地址（支持 http(s)/data URI）'];
        }
        if (preg_match('/(url|href|link)$/i', $k) || $k === 'link') {
            return ['name' => $key, 'type' => 'url', 'label' => $label, 'group' => '内容', 'hint' => '链接地址'];
        }
        if (preg_match('/(color|colour)$/i', $k)) {
            return ['name' => $key, 'type' => 'color', 'label' => $label, 'group' => '样式', 'hint' => '颜色值'];
        }
        if (preg_match('/^(class|id|style|wrapper_class|container_class|wrapperClass)$/i', $k)) {
            return ['name' => $key, 'type' => 'text', 'label' => $label, 'group' => '样式', 'hint' => '附加类名 / 行内样式'];
        }
        if (mb_strlen($str) > 50 || strpos($str, "\n") !== false) {
            return ['name' => $key, 'type' => 'textarea', 'label' => $label, 'group' => '内容'];
        }

        return ['name' => $key, 'type' => 'text', 'label' => $label, 'group' => '内容'];
    }

    /**
     * 字段名 → 中文友好标签
     */
    private static function humanLabel(string $key): string
    {
        $map = [
            'title' => '标题', 'subtitle' => '副标题', 'text' => '文字', 'body' => '内容',
            'content' => '内容', 'label' => '标签', 'name' => '名称', 'value' => '数值',
            'caption' => '说明', 'description' => '描述', 'heading' => '主标题',
            'items' => '列表项', 'rows' => '数据行', 'columns' => '列配置', 'series' => '数据系列',
            'data' => '数据', 'tabs' => '标签页', 'features' => '特性', 'comments' => '评论',
            'messages' => '消息', 'posts' => '文章', 'plans' => '方案', 'steps' => '步骤',
            'options' => '候选项', 'cards' => '卡片', 'menu' => '菜单', 'href' => '链接',
            'src' => '地址', 'icon' => '图标', 'variant' => '配色', 'class' => '附加 class',
            'period' => '周期', 'price' => '价格', 'badge' => '角标', 'button' => '按钮',
            'total' => '总数', 'current' => '当前页', 'cols' => '列数', 'lines' => '行数',
            'target' => '目标', 'date' => '日期', 'time' => '时间', 'author' => '作者',
            'quote' => '引言', 'image' => '图片', 'avatar' => '头像', 'cover' => '封面',
            'bio' => '简介', 'meta' => '元信息', 'placeholder' => '占位文字',
            'gutters' => '栅格间距', 'width' => '列宽', 'style' => '风格', 'type' => '类型',
            'placement' => '位置', 'size' => '尺寸', 'striped' => '条纹', 'bordered' => '边框',
            'dismissible' => '可关闭', 'outline' => '描边', 'pill' => '圆角', 'rounded' => '圆形',
            'centered' => '居中', 'highlight' => '高亮', 'alwaysOpen' => '允许全部展开',
            'animated' => '动画', 'checked' => '选中', 'block' => '块级',
        ];
        if (isset($map[$key])) {
            return $map[$key];
        }
        $words = preg_split('/[_\-]+/', $key);
        $words = array_map(static fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)) . mb_substr($w, 1), $words);

        return implode(' ', $words);
    }

    /**
     * JSON 数据字段的「数据格式 / 结构」提示
     */
    private static function jsonHint(string $k): string
    {
        $examples = [
            'items'     => "JSON 数组，每项例：{\"title\":\"标题\",\"text\":\"描述\"}",
            'rows'      => "JSON 二维数组，例：[[\"列1\",\"列2\"],[\"值1\",\"值2\"]]",
            'columns'   => "JSON 数组，每项例：{\"title\":\"列名\",\"items\":[{\"text\":\"任务\"}]}",
            'series'    => "JSON 数组，例：[{\"name\":\"访问量\",\"data\":[120,200,150]}]",
            'data'      => "JSON 数组，例：[10,20,30,40]",
            'tabs'      => "JSON 数组，每项例：{\"title\":\"标签\",\"content\":\"内容\"}",
            'features'  => "JSON 数组，例：[\"功能一\",\"功能二\"]",
            'comments'  => "JSON 数组，每项例：{\"author\":\"张三\",\"text\":\"评论\"}",
            'messages'  => "JSON 数组，每项例：{\"from\":\"user\",\"text\":\"消息\"}",
            'posts'     => "JSON 数组，每项例：{\"title\":\"标题\",\"excerpt\":\"摘要\"}",
            'plans'     => "JSON 数组，每项例：{\"title\":\"方案\",\"price\":\"¥99\"}",
            'steps'     => "JSON 数组，每项例：{\"title\":\"步骤\",\"text\":\"说明\"}",
            'options'   => "JSON 数组，每项例：{\"text\":\"选项\",\"value\":\"v\"}",
            'cards'     => "JSON 数组，每项例：{\"title\":\"卡片\",\"body\":\"内容\"}",
            'menu'      => "JSON 数组，每项例：{\"text\":\"菜单\",\"href\":\"#\"}",
            'navigation'=> "JSON 数组，每项例：{\"text\":\"导航\",\"href\":\"#\"}",
            'nodes'     => "JSON 数组 / 树结构",
            'points'    => "JSON 数组，例：[[116.4,39.9]]",
            'trend'     => "JSON 对象，例：{\"value\":\"+12%\",\"direction\":\"up\"}",
            'button'    => "JSON 对象，例：{\"text\":\"按钮\",\"variant\":\"primary\"}",
            'ribbon'    => "JSON 对象，例：{\"text\":\"推荐\",\"variant\":\"danger\"}",
            'badge'     => "JSON 对象，例：{\"text\":\"新\",\"variant\":\"primary\"}",
        ];
        foreach ($examples as $key => $hint) {
            if ($k === $key || str_ends_with($k, $key)) {
                return $hint;
            }
        }

        return 'JSON 结构（对象 / 数组），按组件数据格式填写';
    }

    /**
     * 手工标注的组件字段 schema（别名 => 字段定义数组）。
     * 字段：{name,type,label,options?,placeholder?,hint?}
     * type ∈ text|textarea|number|bool|select|icon|image|url|color|json
     * 未在此标注的组件将完全由 defaults() 推断，仍能完整编辑。
     */
    private static function curatedFields(): array
    {
        $variants = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'none'];
        $sizes    = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'];

        return [
            'pageTitle' => [
                ['name' => 'title', 'type' => 'text', 'label' => '主标题'],
                ['name' => 'subtitle', 'type' => 'text', 'label' => '副标题'],
                ['name' => 'class', 'type' => 'text', 'label' => '附加 class'],
            ],
            'alert' => [
                ['name' => 'variant', 'type' => 'select', 'label' => '样式', 'options' => $variants],
                ['name' => 'text', 'type' => 'textarea', 'label' => '内容'],
                ['name' => 'dismissible', 'type' => 'bool', 'label' => '可关闭'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标'],
            ],
            'card' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容（支持 HTML）'],
                ['name' => 'class', 'type' => 'text', 'label' => '附加 class'],
                ['name' => 'footer', 'type' => 'textarea', 'label' => '底部区'],
            ],
            'statCard' => [
                ['name' => 'title', 'type' => 'text', 'label' => '指标名'],
                ['name' => 'value', 'type' => 'text', 'label' => '数值'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'trend', 'type' => 'json', 'label' => '趋势（JSON）'],
            ],
            'button' => [
                ['name' => 'text', 'type' => 'text', 'label' => '文字'],
                ['name' => 'variant', 'type' => 'select', 'label' => '样式', 'options' => $variants],
                ['name' => 'size', 'type' => 'select', 'label' => '尺寸', 'options' => $sizes],
                ['name' => 'href', 'type' => 'url', 'label' => '链接'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标'],
                ['name' => 'block', 'type' => 'bool', 'label' => '块级'],
                ['name' => 'outline', 'type' => 'bool', 'label' => '描边'],
            ],
            'badge' => [
                ['name' => 'text', 'type' => 'text', 'label' => '文字'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'pill', 'type' => 'bool', 'label' => '圆角'],
            ],
            'listGroup' => [
                ['name' => 'items', 'type' => 'json', 'label' => '列表项（JSON 数组）'],
                ['name' => 'class', 'type' => 'text', 'label' => '附加 class'],
            ],
            'tabs' => [
                ['name' => 'tabs', 'type' => 'json', 'label' => '标签页（JSON 数组）'],
                ['name' => 'style', 'type' => 'select', 'label' => '风格', 'options' => ['tabs', 'pills', 'underline']],
            ],
            'accordion' => [
                ['name' => 'items', 'type' => 'json', 'label' => '折叠项（JSON 数组）'],
                ['name' => 'alwaysOpen', 'type' => 'bool', 'label' => '允许全部展开'],
            ],
            'progress' => [
                ['name' => 'value', 'type' => 'number', 'label' => '进度值（0-100）'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'striped', 'type' => 'bool', 'label' => '条纹'],
                ['name' => 'label', 'type' => 'text', 'label' => '文字标签'],
            ],
            'avatar' => [
                ['name' => 'src', 'type' => 'image', 'label' => '头像图'],
                ['name' => 'name', 'type' => 'text', 'label' => '名称'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'size', 'type' => 'select', 'label' => '尺寸', 'options' => ['sm', 'md', 'lg', 'xl']],
                ['name' => 'rounded', 'type' => 'bool', 'label' => '圆形'],
            ],
            'icon' => [
                ['name' => 'name', 'type' => 'icon', 'label' => '图标'],
                ['name' => 'size', 'type' => 'select', 'label' => '尺寸', 'options' => $sizes],
                ['name' => 'color', 'type' => 'color', 'label' => '颜色'],
            ],
            'toast' => [
                ['name' => 'body', 'type' => 'text', 'label' => '内容'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
            ],
            'spinner' => [
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'type', 'type' => 'select', 'label' => '类型', 'options' => ['border', 'grow']],
            ],
            'pagination' => [
                ['name' => 'total', 'type' => 'number', 'label' => '总页数'],
                ['name' => 'current', 'type' => 'number', 'label' => '当前页'],
            ],
            'pricingCard' => [
                ['name' => 'title', 'type' => 'text', 'label' => '方案名'],
                ['name' => 'price', 'type' => 'text', 'label' => '价格'],
                ['name' => 'period', 'type' => 'text', 'label' => '周期'],
                ['name' => 'features', 'type' => 'json', 'label' => '特性（JSON 数组）'],
                ['name' => 'button', 'type' => 'json', 'label' => '按钮（JSON）'],
                ['name' => 'highlight', 'type' => 'bool', 'label' => '高亮推荐'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
            ],
            'faq' => [
                ['name' => 'items', 'type' => 'json', 'label' => '问答（JSON 数组）'],
            ],
            'profileHeader' => [
                ['name' => 'name', 'type' => 'text', 'label' => '姓名'],
                ['name' => 'title', 'type' => 'text', 'label' => '职位'],
                ['name' => 'avatar', 'type' => 'image', 'label' => '头像'],
                ['name' => 'cover', 'type' => 'image', 'label' => '封面'],
                ['name' => 'bio', 'type' => 'textarea', 'label' => '简介'],
            ],
            'productCard' => [
                ['name' => 'title', 'type' => 'text', 'label' => '商品名'],
                ['name' => 'price', 'type' => 'text', 'label' => '价格'],
                ['name' => 'image', 'type' => 'image', 'label' => '图片'],
                ['name' => 'badge', 'type' => 'text', 'label' => '角标文字'],
                ['name' => 'button', 'type' => 'json', 'label' => '按钮（JSON）'],
            ],
            'media' => [
                ['name' => 'img', 'type' => 'image', 'label' => '图片'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '描述'],
                ['name' => 'meta', 'type' => 'text', 'label' => '元信息'],
            ],
            'callout' => [
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标'],
            ],
            'gallery' => [
                ['name' => 'images', 'type' => 'json', 'label' => '图片（JSON 数组）'],
                ['name' => 'cols', 'type' => 'number', 'label' => '列数'],
            ],
            'kanban' => [
                ['name' => 'columns', 'type' => 'json', 'label' => '看板列（JSON）'],
            ],
            'chatBox' => [
                ['name' => 'messages', 'type' => 'json', 'label' => '消息（JSON）'],
            ],
            'widget' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
            ],
            'commentThread' => [
                ['name' => 'comments', 'type' => 'json', 'label' => '评论（JSON）'],
            ],
            'blogList' => [
                ['name' => 'posts', 'type' => 'json', 'label' => '文章（JSON）'],
            ],
            'table' => [
                ['name' => 'headers', 'type' => 'json', 'label' => '表头（JSON）'],
                ['name' => 'rows', 'type' => 'json', 'label' => '数据行（JSON）'],
                ['name' => 'striped', 'type' => 'bool', 'label' => '斑马纹'],
                ['name' => 'bordered', 'type' => 'bool', 'label' => '边框'],
            ],
            'form' => [
                ['name' => 'fields', 'type' => 'json', 'label' => '字段（JSON）'],
                ['name' => 'buttons', 'type' => 'json', 'label' => '按钮（JSON）'],
            ],
            'modal' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容'],
                ['name' => 'size', 'type' => 'select', 'label' => '尺寸', 'options' => ['sm', 'md', 'lg', 'xl']],
            ],
            'offcanvas' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容'],
                ['name' => 'placement', 'type' => 'select', 'label' => '位置', 'options' => ['start', 'end', 'top', 'bottom']],
            ],
            'dropdown' => [
                ['name' => 'label', 'type' => 'text', 'label' => '按钮文字'],
                ['name' => 'items', 'type' => 'json', 'label' => '菜单项（JSON）'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
            ],
            'divider' => [
                ['name' => 'text', 'type' => 'text', 'label' => '分割文字'],
                ['name' => 'class', 'type' => 'text', 'label' => '附加 class'],
            ],
            'kbd' => [
                ['name' => 'keys', 'type' => 'text', 'label' => '按键'],
            ],
            'typography' => [
                ['name' => 'content', 'type' => 'textarea', 'label' => '内容（HTML）'],
            ],
            'searchBox' => [
                ['name' => 'placeholder', 'type' => 'text', 'label' => '占位'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants],
            ],
            'countdown' => [
                ['name' => 'target', 'type' => 'text', 'label' => '目标时间'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
            ],
            'statMiniSparkline' => [
                ['name' => 'label', 'type' => 'text', 'label' => '指标'],
                ['name' => 'value', 'type' => 'text', 'label' => '数值'],
                ['name' => 'data', 'type' => 'json', 'label' => '折线（JSON）'],
            ],
            'emptyState' => [
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题'],
                ['name' => 'text', 'type' => 'text', 'label' => '说明'],
            ],
            'skeleton' => [
                ['name' => 'lines', 'type' => 'number', 'label' => '行数'],
            ],
            'backToTop' => [
                ['name' => 'text', 'type' => 'text', 'label' => '文字'],
            ],

            // ---------- 指标 / 数字 ----------
            'statMini' => [
                ['name' => 'label', 'type' => 'text', 'label' => '指标名', 'group' => '基础'],
                ['name' => 'value', 'type' => 'text', 'label' => '数值', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
            ],
            'countUp' => [
                ['name' => 'value', 'type' => 'number', 'label' => '目标数值', 'group' => '基础'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
            ],

            // ---------- 评分 / 开关 / 徽章 / 缎带 ----------
            'rating' => [
                ['name' => 'value', 'type' => 'number', 'label' => '评分（0-5）', 'group' => '基础'],
                ['name' => 'max', 'type' => 'number', 'label' => '满分', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'size', 'type' => 'select', 'label' => '尺寸', 'options' => $sizes, 'group' => '样式'],
            ],
            'switch' => [
                ['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'],
                ['name' => 'checked', 'type' => 'bool', 'label' => '默认开启', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'disabled', 'type' => 'bool', 'label' => '禁用', 'group' => '基础'],
            ],
            'chip' => [
                ['name' => 'text', 'type' => 'text', 'label' => '文字', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
                ['name' => 'pill', 'type' => 'bool', 'label' => '圆角', 'group' => '样式'],
            ],
            'ribbon' => [
                ['name' => 'text', 'type' => 'text', 'label' => '文字', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],
            'ribbonCard' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'body', 'type' => 'textarea', 'label' => '内容', 'group' => '内容'],
                ['name' => 'ribbon', 'type' => 'json', 'label' => '缎带（JSON）', 'group' => '样式', 'hint' => 'JSON 对象，例：{"text":"推荐","variant":"danger"}'],
            ],

            // ---------- 区块 / 标题类 ----------
            'sectionTitle' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'subtitle', 'type' => 'text', 'label' => '副标题', 'group' => '基础'],
                ['name' => 'align', 'type' => 'select', 'label' => '对齐', 'options' => ['start', 'center', 'end'], 'group' => '样式'],
                ['name' => 'class', 'type' => 'text', 'label' => '附加 class', 'group' => '样式'],
            ],
            'featureCard' => [
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '描述', 'group' => '内容'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],
            'iconCard' => [
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '描述', 'group' => '内容'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],
            'iconList' => [
                ['name' => 'items', 'type' => 'json', 'label' => '列表（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"icon":"ti ti-check","text":"特性"}'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],
            'steps' => [
                ['name' => 'items', 'type' => 'json', 'label' => '步骤（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"title":"步骤","text":"说明"}'],
                ['name' => 'current', 'type' => 'number', 'label' => '当前步', 'group' => '基础'],
            ],
            'process' => [
                ['name' => 'steps', 'type' => 'json', 'label' => '流程（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"title":"提交","text":"说明"}'],
            ],
            'stepper' => [
                ['name' => 'steps', 'type' => 'json', 'label' => '步骤（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"title":"步骤","text":"说明"}'],
                ['name' => 'current', 'type' => 'number', 'label' => '当前步', 'group' => '基础'],
            ],

            // ---------- 营销区块 ----------
            'banner' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '副文案', 'group' => '内容'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
                ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式'],
            ],
            'hero' => [
                ['name' => 'title', 'type' => 'text', 'label' => '主标题', 'group' => '基础'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '副标题', 'group' => '内容'],
                ['name' => 'button', 'type' => 'json', 'label' => '按钮（JSON）', 'group' => '行为', 'hint' => 'JSON 对象，例：{"text":"体验","variant":"primary","href":"#"}'],
                ['name' => 'image', 'type' => 'image', 'label' => '配图', 'group' => '样式'],
            ],
            'cta' => [
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'text', 'type' => 'textarea', 'label' => '文案', 'group' => '内容'],
                ['name' => 'button', 'type' => 'json', 'label' => '按钮（JSON）', 'group' => '行为', 'hint' => 'JSON 对象，例：{"text":"了解","variant":"primary"}'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],
            'testimonial' => [
                ['name' => 'quote', 'type' => 'textarea', 'label' => '评价', 'group' => '内容'],
                ['name' => 'author', 'type' => 'text', 'label' => '作者', 'group' => '基础'],
                ['name' => 'avatar', 'type' => 'image', 'label' => '头像', 'group' => '样式'],
                ['name' => 'role', 'type' => 'text', 'label' => '身份', 'group' => '基础'],
                ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'],
            ],

            // ---------- 卡片变体 ----------
            'cardInfo' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'body', 'type' => 'textarea', 'label' => '内容', 'group' => '内容'], ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式']],
            'alertCard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'body', 'type' => 'textarea', 'label' => '内容', 'group' => '内容'], ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式']],
            'infoCard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'body', 'type' => 'textarea', 'label' => '内容', 'group' => '内容']],
            'userCard' => [['name' => 'name', 'type' => 'text', 'label' => '姓名', 'group' => '基础'], ['name' => 'title', 'type' => 'text', 'label' => '职位', 'group' => '基础'], ['name' => 'avatar', 'type' => 'image', 'label' => '头像', 'group' => '样式'], ['name' => 'bio', 'type' => 'textarea', 'label' => '简介', 'group' => '内容']],
            'teamCard' => [['name' => 'members', 'type' => 'json', 'label' => '成员（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"name":"成员","avatar":"url"}'], ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础']],
            'imageCard' => [['name' => 'image', 'type' => 'image', 'label' => '图片', 'group' => '样式'], ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'text', 'type' => 'textarea', 'label' => '描述', 'group' => '内容']],
            'videoCard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'cover', 'type' => 'image', 'label' => '封面', 'group' => '样式'], ['name' => 'src', 'type' => 'url', 'label' => '视频地址', 'group' => '内容']],
            'linkCard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'text', 'type' => 'textarea', 'label' => '描述', 'group' => '内容'], ['name' => 'href', 'type' => 'url', 'label' => '链接', 'group' => '内容'], ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式']],
            'tagCard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'tags', 'type' => 'json', 'label' => '标签（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：["设计","前端"]']],
            'htmlBlock' => [['name' => 'html', 'type' => 'textarea', 'label' => 'HTML 代码', 'group' => '内容', 'hint' => '原生 HTML，可内嵌组件或样式']],
            'iframeBlock' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'src', 'type' => 'url', 'label' => '嵌入地址', 'group' => '内容'], ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式']],

            // ---------- 导航 / 菜单 ----------
            'nav' => [['name' => 'items', 'type' => 'json', 'label' => '菜单项（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"text":"首页","href":"#","icon":"ti ti-home"}'], ['name' => 'align', 'type' => 'select', 'label' => '对齐', 'options' => ['start', 'center', 'end', 'between'], 'group' => '样式']],
            'breadcrumb' => [['name' => 'items', 'type' => 'json', 'label' => '路径（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"text":"首页","href":"#"}（末项无 href 为当前页）']],
            'footer' => [['name' => 'text', 'type' => 'textarea', 'label' => '版权 / 文案', 'group' => '内容'], ['name' => 'class', 'type' => 'text', 'label' => '附加 class', 'group' => '样式']],

            // ---------- 浮层 / 提示 ----------
            'tooltip' => [['name' => 'text', 'type' => 'text', 'label' => '提示文字', 'group' => '内容'], ['name' => 'placement', 'type' => 'select', 'label' => '位置', 'options' => ['top', 'bottom', 'left', 'right'], 'group' => '样式'], ['name' => 'trigger', 'type' => 'select', 'label' => '触发', 'options' => ['hover', 'click', 'focus'], 'group' => '行为']],
            'popover' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'content', 'type' => 'textarea', 'label' => '内容', 'group' => '内容'], ['name' => 'placement', 'type' => 'select', 'label' => '位置', 'options' => ['top', 'bottom', 'left', 'right'], 'group' => '样式'], ['name' => 'trigger', 'type' => 'select', 'label' => '触发', 'options' => ['click', 'hover', 'focus'], 'group' => '行为']],
            'notification' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'text', 'type' => 'textarea', 'label' => '内容', 'group' => '内容'], ['name' => 'variant', 'type' => 'select', 'label' => '配色', 'options' => $variants, 'group' => '样式'], ['name' => 'icon', 'type' => 'icon', 'label' => '图标', 'group' => '样式']],

            // ---------- 图表 / 地图 ----------
            'chart' => [
                ['name' => 'type', 'type' => 'select', 'label' => '图表类型', 'options' => ['bar', 'line', 'area', 'pie', 'donut'], 'group' => '数据'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'categories', 'type' => 'json', 'label' => 'X 轴分类（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：["一月","二月","三月"]'],
                ['name' => 'series', 'type' => 'json', 'label' => '数据系列（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[{"name":"访问量","data":[120,200,150]}]'],
                ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式'],
            ],
            'echart' => [
                ['name' => 'type', 'type' => 'select', 'label' => '图表类型', 'options' => ['line', 'bar', 'pie', 'area', 'radar', 'scatter'], 'group' => '数据'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'categories', 'type' => 'json', 'label' => 'X 轴分类（JSON）', 'group' => '数据'],
                ['name' => 'series', 'type' => 'json', 'label' => '数据系列（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[{"name":"访问量","data":[120,200,150]}]'],
                ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式'],
            ],
            'apexChart' => [
                ['name' => 'type', 'type' => 'select', 'label' => '图表类型', 'options' => ['area', 'line', 'bar', 'pie', 'donut', 'radar'], 'group' => '数据'],
                ['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'],
                ['name' => 'categories', 'type' => 'json', 'label' => 'X 轴分类（JSON）', 'group' => '数据'],
                ['name' => 'series', 'type' => 'json', 'label' => '数据系列（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[{"name":"销售额","data":[30,40,35]}]'],
                ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式'],
            ],
            'leafletMap' => [['name' => 'center', 'type' => 'json', 'label' => '中心点（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[39.9,116.4]'], ['name' => 'zoom', 'type' => 'number', 'label' => '缩放级别', 'group' => '基础'], ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式'], ['name' => 'markers', 'type' => 'json', 'label' => '标记（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"lat":39.9,"lng":116.4,"title":"北京"}']],
            'vectorMap' => [['name' => 'region', 'type' => 'text', 'label' => '区域', 'group' => '基础'], ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式']],
            'googleMap' => [['name' => 'center', 'type' => 'json', 'label' => '中心点（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[39.9,116.4]'], ['name' => 'zoom', 'type' => 'number', 'label' => '缩放级别', 'group' => '基础'], ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式']],
            'calendar' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'events', 'type' => 'json', 'label' => '事件（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"date":"2026-01-01","title":"事件"}']],

            // ---------- 表单子组件（独立使用）----------
            'input' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'type', 'type' => 'select', 'label' => '输入类型', 'options' => ['text', 'email', 'password', 'number', 'tel', 'url', 'search'], 'group' => '数据'], ['name' => 'placeholder', 'type' => 'text', 'label' => '占位', 'group' => '内容'], ['name' => 'value', 'type' => 'text', 'label' => '默认值', 'group' => '基础'], ['name' => 'icon', 'type' => 'icon', 'label' => '前缀图标', 'group' => '样式']],
            'textarea' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'placeholder', 'type' => 'text', 'label' => '占位', 'group' => '内容'], ['name' => 'rows', 'type' => 'number', 'label' => '行数', 'group' => '样式'], ['name' => 'value', 'type' => 'textarea', 'label' => '默认值', 'group' => '基础']],
            'select' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'options', 'type' => 'json', 'label' => '候选项（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[{"text":"选项","value":"v"}]'], ['name' => 'placeholder', 'type' => 'text', 'label' => '占位', 'group' => '内容']],
            'check' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'type', 'type' => 'select', 'label' => '类型', 'options' => ['checkbox', 'radio', 'switch'], 'group' => '数据'], ['name' => 'checked', 'type' => 'bool', 'label' => '默认选中', 'group' => '基础'], ['name' => 'options', 'type' => 'json', 'label' => '候选项（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：[{"text":"是","value":"1"}]']],
            'slider' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'min', 'type' => 'number', 'label' => '最小', 'group' => '基础'], ['name' => 'max', 'type' => 'number', 'label' => '最大', 'group' => '基础'], ['name' => 'value', 'type' => 'number', 'label' => '当前值', 'group' => '基础'], ['name' => 'step', 'type' => 'number', 'label' => '步长', 'group' => '基础']],
            'dateRange' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'value', 'type' => 'text', 'label' => '默认值', 'group' => '基础']],
            'datePicker' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'value', 'type' => 'text', 'label' => '默认值', 'group' => '基础']],
            'editor' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'content', 'type' => 'textarea', 'label' => '内容', 'group' => '内容']],
            'upload' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'multiple', 'type' => 'bool', 'label' => '允许多选', 'group' => '基础']],
            'colorPicker' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'value', 'type' => 'color', 'label' => '默认色', 'group' => '样式']],
            'tags' => [['name' => 'label', 'type' => 'text', 'label' => '标签', 'group' => '基础'], ['name' => 'name', 'type' => 'text', 'label' => '字段名', 'group' => '基础'], ['name' => 'value', 'type' => 'text', 'label' => '默认标签（逗号分隔）', 'group' => '基础']],
            'wizard' => [['name' => 'title', 'type' => 'text', 'label' => '标题', 'group' => '基础'], ['name' => 'steps', 'type' => 'json', 'label' => '步骤（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"title":"步骤","content":"内容"}']],

            // ---------- 时间线 / 轮播 / 其它 ----------
            'timeline' => [['name' => 'items', 'type' => 'json', 'label' => '事件（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，每项例：{"title":"事件","time":"2026-01-01","text":"描述"}']],
            'carousel' => [['name' => 'images', 'type' => 'json', 'label' => '图片（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：["url1","url2"]'], ['name' => 'height', 'type' => 'text', 'label' => '高度', 'group' => '样式'], ['name' => 'autoplay', 'type' => 'bool', 'label' => '自动播放', 'group' => '行为']],
            'dataTable' => [['name' => 'headers', 'type' => 'json', 'label' => '表头（JSON）', 'group' => '数据', 'hint' => 'JSON 数组，例：["名称","状态"]'], ['name' => 'rows', 'type' => 'json', 'label' => '数据行（JSON）', 'group' => '数据', 'hint' => 'JSON 二维数组，例：[["记录","正常"]]'], ['name' => 'striped', 'type' => 'bool', 'label' => '斑马纹', 'group' => '样式'], ['name' => 'bordered', 'type' => 'bool', 'label' => '边框', 'group' => '样式']],
            'textEditor' => [['name' => 'content', 'type' => 'textarea', 'label' => '内容（HTML）', 'group' => '内容']],
            'kbd' => [['name' => 'keys', 'type' => 'text', 'label' => '按键组合', 'group' => '基础', 'hint' => '例：Ctrl + S']],
        ];
    }

    /**
     * 生成组件目录（左侧操作栏数据）
     *
     * 遍历 XfAdmin::componentList() 全部已注册组件，按命名空间归类，
     * 并附带各组件的默认配置（供右侧属性面板生成表单）。
     *
     * @return array{categories:array, components:array}
     */
    /**
     * DIY 拖拽默认配置：把组件拖到舞台时使用的「有内容」初始参数。
     * 目的是让组件一加入即可见、可编辑，避免默认空配置导致空白占位。
     */
    private static function diyDefaults(): array
    {
        $img = static fn (string $p): string => XfAdmin::img($p);
        return [
            'pageTitle' => ['title' => '页面标题', 'subtitle' => '副标题说明文字'],
            'alert' => ['variant' => 'info', 'text' => '这是一条提示信息，可在右侧编辑内容与样式。', 'icon' => 'ti ti-info-circle'],
            'card' => ['title' => '卡片标题', 'body' => '卡片内容示例文本，可放置任意结构。', 'footer' => '卡片底部说明'],
            'statCard' => ['title' => '访问量', 'value' => '12,840', 'icon' => 'ti ti-chart-bar', 'variant' => 'primary', 'trend' => ['value' => '+12.5%', 'direction' => 'up']],
            'statMini' => ['label' => '转化率', 'value' => '3.2%', 'variant' => 'success'],
            'statMiniSparkline' => ['label' => '活跃用户', 'value' => '1,280', 'data' => [10, 20, 15, 30, 25, 40, 35, 50]],
            'button' => ['text' => '按钮', 'variant' => 'primary', 'size' => 'md'],
            'badge' => ['text' => '徽章', 'variant' => 'primary'],
            'listGroup' => ['items' => [['title' => '列表项一', 'text' => '描述文字', 'badge' => ['text' => '新', 'variant' => 'primary']], ['title' => '列表项二', 'text' => '描述文字', 'badge' => ['text' => '热', 'variant' => 'danger']]]],
            'tabs' => ['tabs' => [['title' => '标签一', 'content' => '第一个标签页的内容。'], ['title' => '标签二', 'content' => '第二个标签页的内容。']]],
            'accordion' => ['items' => [['title' => '折叠项一', 'content' => '这是第一个折叠项的内容。'], ['title' => '折叠项二', 'content' => '这是第二个折叠项的内容。']]],
            'progress' => ['value' => 65, 'variant' => 'primary', 'label' => '完成度 65%'],
            'avatar' => ['src' => $img('users/user-1.jpg'), 'name' => '用户名', 'variant' => 'primary'],
            'icon' => ['name' => 'ti ti-heart', 'size' => 'md', 'color' => '#ef4444'],
            'rating' => ['value' => 4],
            'switch' => ['label' => '启用开关', 'checked' => true],
            'spinner' => ['variant' => 'primary', 'type' => 'border'],
            'pagination' => ['total' => 10, 'current' => 1],
            'kbd' => ['keys' => 'Ctrl + S'],
            'divider' => ['text' => '分隔文字'],
            'typography' => ['content' => '<h2>标题示例</h2><p>正文文本示例，用于展示排版与字号效果。</p>'],
            'callout' => ['variant' => 'primary', 'title' => '提示', 'body' => '这是一条强调提示，适合放置重点说明。'],
            'toast' => ['body' => '轻提示内容示例', 'variant' => 'success'],
            'skeleton' => ['lines' => 3],
            'breadcrumb' => ['items' => [['text' => '首页', 'href' => '#'], ['text' => '分类', 'href' => '#'], ['text' => '当前页']]],
            'searchBox' => ['placeholder' => '搜索内容…', 'variant' => 'primary'],
            'countdown' => ['target' => date('Y-m-d H:i:s', strtotime('+3 days')), 'title' => '距活动开始'],
            'backToTop' => ['text' => '回到顶部'],
            'codeBlock' => ['code' => "<?php\n echo 'Hello, XfAdmin';\n", 'lang' => 'php'],
            'emptyState' => ['icon' => 'ti ti-inbox', 'title' => '暂无数据', 'text' => '这里还没有内容，点击右侧属性填充。'],
            'empty' => ['icon' => 'ti ti-inbox', 'title' => '空状态', 'text' => '暂无内容'],
            'widget' => ['title' => '小部件', 'body' => '小部件内容示例。', 'variant' => 'primary'],
            'media' => ['img' => $img('gallery/1.jpg'), 'title' => '媒体标题', 'text' => '媒体对象描述内容示例。'],
            'gallery' => ['images' => [$img('gallery/1.jpg'), $img('gallery/2.jpg'), $img('gallery/3.jpg')], 'cols' => 3],
            'carousel' => ['images' => [$img('gallery/1.jpg'), $img('gallery/2.jpg'), $img('gallery/3.jpg')]],
            'productCard' => ['title' => '商品名称', 'price' => '¥199', 'image' => $img('gallery/1.jpg'), 'badge' => '热卖', 'button' => ['text' => '加入购物车', 'variant' => 'primary']],
            'profileHeader' => ['name' => '张明', 'title' => '产品总监', 'avatar' => $img('users/user-1.jpg'), 'bio' => '负责产品规划与体验设计。'],
            'commentThread' => ['comments' => [['author' => '张三', 'text' => '评论内容示例。', 'avatar' => $img('users/user-1.jpg')]]],
            'blogList' => ['posts' => [['title' => '文章标题一', 'excerpt' => '摘要文字示例。', 'image' => $img('gallery/1.jpg')]]],
            'faq' => ['items' => [['q' => '问题一？', 'a' => '回答一的内容。'], ['q' => '问题二？', 'a' => '回答二的内容。']]],
            'pricingCard' => ['title' => '基础版', 'price' => '¥0', 'period' => '/月', 'features' => ['核心功能', '社区支持', '基础模板'], 'button' => ['text' => '开始使用', 'variant' => 'primary']],
            'pricingTable' => ['plans' => [['title' => '基础', 'price' => '¥0', 'features' => ['功能一']], ['title' => '专业', 'price' => '¥99', 'features' => ['功能一', '功能二']]]],
            'timeline' => ['items' => [['title' => '事件一', 'time' => '2026-01-01', 'text' => '描述一'], ['title' => '事件二', 'time' => '2026-02-01', 'text' => '描述二']]],
            'form' => ['fields' => [['label' => '姓名', 'name' => 'name', 'type' => 'text', 'placeholder' => '请输入姓名'], ['label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入邮箱']], 'buttons' => [['text' => '提交', 'variant' => 'primary', 'type' => 'submit']]],
            'table' => ['headers' => ['名称', '描述', '状态'], 'rows' => [['示例一', '描述一', '正常'], ['示例二', '描述二', '异常']], 'striped' => true, 'bordered' => true],
            'modal' => ['title' => '弹窗标题', 'body' => '弹窗内容示例文本。'],
            'offcanvas' => ['title' => '抽屉标题', 'body' => '抽屉内容示例文本。'],
            'dropdown' => ['label' => '下拉菜单', 'items' => [['text' => '操作一'], ['text' => '操作二'], ['text' => '操作三']], 'variant' => 'primary'],
            'toolbar' => ['items' => [['text' => '左操作', 'variant' => 'soft'], ['text' => '右操作', 'variant' => 'primary']]],
            'cardInfo' => ['title' => '信息卡片', 'body' => '信息卡片内容。'],
            'ribbon' => ['text' => '推荐', 'variant' => 'danger'],
            'ribbonCard' => ['title' => '卡片标题', 'body' => '卡片内容。', 'ribbon' => ['text' => '推荐', 'variant' => 'danger']],
            'comingSoon' => ['title' => '敬请期待', 'message' => '功能正在开发中。', 'date' => date('Y-m-d', strtotime('+10 days'))],
            'maintenance' => ['title' => '系统维护中', 'message' => '系统正在升级维护，请稍后再试。'],
            'sectionTitle' => ['title' => '区块标题', 'subtitle' => '区块副标题'],
            'featureCard' => ['icon' => 'ti ti-rocket', 'title' => '特性标题', 'text' => '特性描述文字示例。', 'variant' => 'primary'],
            'iconCard' => ['icon' => 'ti ti-bolt', 'title' => '图标卡片', 'text' => '图标卡片描述。'],
            'iconList' => ['items' => [['icon' => 'ti ti-check', 'text' => '支持特性一'], ['icon' => 'ti ti-check', 'text' => '支持特性二']]],
            'steps' => ['items' => [['title' => '步骤一', 'text' => '说明一'], ['title' => '步骤二', 'text' => '说明二'], ['title' => '步骤三', 'text' => '说明三']]],
            'process' => ['steps' => [['title' => '提交', 'text' => '提交申请'], ['title' => '审核', 'text' => '等待审核'], ['title' => '完成', 'text' => '流程完成']]],
            'countUp' => ['value' => 1280, 'title' => '累计访问'],
            'textEditor' => ['content' => '<p>富文本编辑内容示例。</p>'],
            'chart' => ['type' => 'bar', 'title' => '示例图表', 'categories' => ['一月', '二月', '三月', '四月', '五月'], 'series' => [['name' => '访问量', 'data' => [120, 200, 150, 80, 170]]]],
            'echart' => ['type' => 'line', 'title' => '访问趋势', 'categories' => ['周一', '周二', '周三', '周四', '周五'], 'series' => [['name' => '访问量', 'data' => [120, 200, 150, 80, 170]]]],
            'apexChart' => ['type' => 'area', 'title' => '销售趋势', 'categories' => ['一月', '二月', '三月', '四月'], 'series' => [['name' => '销售额', 'data' => [30, 40, 35, 50]]]],
            'dataTable' => ['headers' => ['名称', '状态'], 'rows' => [['记录一', '正常'], ['记录二', '异常']]],
            'calendar' => ['title' => '日程日历'],
            'chat' => ['messages' => [['from' => 'user', 'text' => '你好'], ['from' => 'bot', 'text' => '有什么可以帮你？']]],
            'kanban' => ['columns' => [['title' => '待办', 'items' => [['text' => '任务一']]], ['title' => '进行中', 'items' => [['text' => '任务二']]], ['title' => '完成', 'items' => [['text' => '任务三']]]]],
            'testimonial' => ['quote' => '这是一段客户评价示例文字。', 'author' => '李四', 'avatar' => $img('users/user-2.jpg')],
            'notification' => ['title' => '通知标题', 'text' => '通知内容示例。', 'variant' => 'primary'],
            'alertCard' => ['title' => '提示卡片', 'body' => '提示卡片内容。', 'variant' => 'warning'],
            'infoCard' => ['title' => '信息', 'body' => '信息内容。'],
            'userCard' => ['name' => '王五', 'title' => '工程师', 'avatar' => $img('users/user-3.jpg')],
            'teamCard' => ['members' => [['name' => '成员一', 'avatar' => $img('users/user-1.jpg')], ['name' => '成员二', 'avatar' => $img('users/user-2.jpg')]]],
            'imageCard' => ['image' => $img('gallery/2.jpg'), 'title' => '图片卡片', 'text' => '图片卡片描述。'],
            'videoCard' => ['title' => '视频卡片', 'cover' => $img('gallery/3.jpg')],
            'htmlBlock' => ['html' => '<div class="p-3">自定义 HTML 内容区块。</div>'],
            'iframeBlock' => ['title' => '嵌入区块'],
            'linkCard' => ['title' => '链接卡片', 'text' => '点击跳转示例。', 'href' => '#'],
            'tagCard' => ['title' => '标签', 'tags' => ['设计', '前端', '体验']],
            'banner' => ['title' => '欢迎使用', 'text' => '这是一个横幅示例。', 'variant' => 'primary'],
            'hero' => ['title' => '主视觉标题', 'text' => '主视觉副标题说明。', 'button' => ['text' => '立即体验', 'variant' => 'primary']],
            'cta' => ['title' => '立即行动', 'text' => '呼吁性文字示例。', 'button' => ['text' => '了解更多', 'variant' => 'primary']],

            // ---------- 评分 / 开关 / 缎带 ----------
            'rating' => ['value' => 4, 'max' => 5, 'variant' => 'warning'],
            'ribbon' => ['text' => '推荐', 'variant' => 'danger'],
            'ribbonCard' => ['title' => '卡片标题', 'body' => '卡片内容。', 'ribbon' => ['text' => '推荐', 'variant' => 'danger']],

            // ---------- 区块 / 标题类 ----------
            'sectionTitle' => ['title' => '区块标题', 'subtitle' => '区块副标题', 'align' => 'start'],
            'featureCard' => ['icon' => 'ti ti-rocket', 'title' => '特性标题', 'text' => '特性描述文字示例。', 'variant' => 'primary'],
            'iconCard' => ['icon' => 'ti ti-bolt', 'title' => '图标卡片', 'text' => '图标卡片描述。'],
            'iconList' => ['items' => [['icon' => 'ti ti-check', 'text' => '支持特性一'], ['icon' => 'ti ti-check', 'text' => '支持特性二']], 'variant' => 'primary'],
            'steps' => ['items' => [['title' => '步骤一', 'text' => '说明一'], ['title' => '步骤二', 'text' => '说明二'], ['title' => '步骤三', 'text' => '说明三']], 'current' => 1],
            'process' => ['steps' => [['title' => '提交', 'text' => '提交申请'], ['title' => '审核', 'text' => '等待审核'], ['title' => '完成', 'text' => '流程完成']]],
            'stepper' => ['steps' => [['title' => '第一步', 'text' => '说明'], ['title' => '第二步', 'text' => '说明']], 'current' => 0],

            // ---------- 营销区块 ----------
            'banner' => ['title' => '欢迎使用', 'text' => '这是一个横幅示例。', 'variant' => 'primary', 'icon' => 'ti ti-flag'],
            'hero' => ['title' => '主视觉标题', 'text' => '主视觉副标题说明。', 'button' => ['text' => '立即体验', 'variant' => 'primary', 'href' => '#']],

            // ---------- 卡片变体 ----------
            'cardInfo' => ['title' => '信息卡片', 'body' => '信息卡片内容。', 'variant' => 'info'],
            'alertCard' => ['title' => '提示卡片', 'body' => '提示卡片内容。', 'variant' => 'warning'],
            'infoCard' => ['title' => '信息', 'body' => '信息内容。'],
            'userCard' => ['name' => '王五', 'title' => '工程师', 'avatar' => $img('users/user-3.jpg')],
            'teamCard' => ['title' => '团队', 'members' => [['name' => '成员一', 'avatar' => $img('users/user-1.jpg')], ['name' => '成员二', 'avatar' => $img('users/user-2.jpg')]]],
            'imageCard' => ['image' => $img('gallery/2.jpg'), 'title' => '图片卡片', 'text' => '图片卡片描述。'],
            'videoCard' => ['title' => '视频卡片', 'cover' => $img('gallery/3.jpg'), 'src' => 'https://www.example.com/video.mp4'],
            'linkCard' => ['title' => '链接卡片', 'text' => '点击跳转示例。', 'href' => '#', 'icon' => 'ti ti-link'],
            'tagCard' => ['title' => '标签', 'tags' => ['设计', '前端', '体验']],
            'htmlBlock' => ['html' => '<div class="p-3 border rounded">自定义 HTML 内容区块。</div>'],
            'iframeBlock' => ['title' => '嵌入区块', 'src' => 'https://www.example.com', 'height' => '320px'],

            // ---------- 导航 / 菜单 ----------
            'nav' => ['items' => [['text' => '首页', 'href' => '#', 'icon' => 'ti ti-home'], ['text' => '产品', 'href' => '#', 'icon' => 'ti ti-box'], ['text' => '关于', 'href' => '#', 'icon' => 'ti ti-info-circle']], 'align' => 'start'],
            'footer' => ['text' => '© 2026 示例公司. 保留所有权利。'],

            // ---------- 浮层 / 提示 ----------
            'tooltip' => ['text' => '提示文字示例', 'placement' => 'top', 'trigger' => 'hover'],
            'popover' => ['title' => '弹窗标题', 'content' => '弹出框内容示例。', 'placement' => 'top', 'trigger' => 'click'],

            // ---------- 图表 / 地图 ----------
            'calendar' => ['title' => '日程日历', 'events' => [['date' => date('Y-m-d'), 'title' => '今日事件']]],
            'leafletMap' => ['center' => [39.9042, 116.4074], 'zoom' => 10, 'height' => '360px', 'markers' => [['lat' => 39.9042, 'lng' => 116.4074, 'title' => '北京']]],
            'googleMap' => ['center' => [39.9042, 116.4074], 'zoom' => 10, 'height' => '360px'],
            'vectorMap' => ['region' => 'china', 'height' => '360px'],

            // ---------- 表单子组件（独立拖入使用）----------
            'input' => ['label' => '姓名', 'name' => 'name', 'type' => 'text', 'placeholder' => '请输入姓名', 'icon' => 'ti ti-user'],
            'textarea' => ['label' => '留言', 'name' => 'message', 'placeholder' => '请输入留言', 'rows' => 3],
            'select' => ['label' => '城市', 'name' => 'city', 'options' => [['text' => '北京', 'value' => 'bj'], ['text' => '上海', 'value' => 'sh']], 'placeholder' => '请选择'],
            'check' => ['label' => '是否启用', 'name' => 'enable', 'type' => 'checkbox', 'checked' => true, 'options' => [['text' => '启用', 'value' => '1']]],
            'slider' => ['label' => '音量', 'min' => 0, 'max' => 100, 'value' => 60, 'step' => 1],
            'dateRange' => ['label' => '日期范围', 'name' => 'range'],
            'datePicker' => ['label' => '日期', 'name' => 'date'],
            'editor' => ['label' => '富文本', 'name' => 'content', 'content' => '<p>富文本示例。</p>'],
            'upload' => ['label' => '上传文件', 'name' => 'file', 'multiple' => false],
            'colorPicker' => ['label' => '主题色', 'name' => 'color', 'value' => '#3b82f6'],
            'tags' => ['label' => '标签', 'name' => 'tags', 'value' => '设计,前端'],
            'wizard' => ['title' => '向导', 'steps' => [['title' => '第一步', 'content' => '内容一'], ['title' => '第二步', 'content' => '内容二']]],

            // ---------- 时间线 / 轮播 ----------
            'timeline' => ['items' => [['title' => '事件一', 'time' => '2026-01-01', 'text' => '描述一'], ['title' => '事件二', 'time' => '2026-02-01', 'text' => '描述二']]],
            'carousel' => ['images' => [$img('gallery/1.jpg'), $img('gallery/2.jpg'), $img('gallery/3.jpg')], 'height' => '320px', 'autoplay' => true],
            'dataTable' => ['headers' => ['名称', '状态'], 'rows' => [['记录一', '正常'], ['记录二', '异常']], 'striped' => true, 'bordered' => true],
        ];
    }

    private static function iconFor(string $alias): string
    {
        $map = [
            'pageTitle' => 'ti ti-heading', 'alert' => 'ti ti-alert-circle', 'card' => 'ti ti-layout-card',
            'statCard' => 'ti ti-chart-bar', 'statMini' => 'ti ti-chart-pie', 'statMiniSparkline' => 'ti ti-chart-line',
            'button' => 'ti ti-click', 'badge' => 'ti ti-badge', 'listGroup' => 'ti ti-list', 'tabs' => 'ti ti-tabs',
            'accordion' => 'ti ti-chevrons-down', 'progress' => 'ti ti-percentage', 'avatar' => 'ti ti-user',
            'icon' => 'ti ti-heart', 'rating' => 'ti ti-star', 'switch' => 'ti ti-toggle-right', 'spinner' => 'ti ti-loader',
            'pagination' => 'ti ti-chevron-right', 'kbd' => 'ti ti-keyboard', 'divider' => 'ti ti-minus',
            'typography' => 'ti ti-typography', 'callout' => 'ti ti-speakerphone', 'toast' => 'ti ti-bell',
            'skeleton' => 'ti ti-layout', 'breadcrumb' => 'ti ti-arrow-guide', 'searchBox' => 'ti ti-search',
            'countdown' => 'ti ti-clock', 'backToTop' => 'ti ti-arrow-up', 'codeBlock' => 'ti ti-code',
            'emptyState' => 'ti ti-inbox', 'empty' => 'ti ti-inbox', 'widget' => 'ti ti-widget', 'media' => 'ti ti-photo',
            'gallery' => 'ti ti-layout-grid', 'carousel' => 'ti ti-slideshow', 'productCard' => 'ti ti-shopping-cart',
            'profileHeader' => 'ti ti-id', 'commentThread' => 'ti ti-messages', 'blogList' => 'ti ti-news',
            'faq' => 'ti ti-help', 'pricingCard' => 'ti ti-tag', 'pricingTable' => 'ti ti-table', 'timeline' => 'ti ti-timeline',
            'form' => 'ti ti-forms', 'table' => 'ti ti-table', 'modal' => 'ti ti-square', 'offcanvas' => 'ti ti-layout-sidebar',
            'dropdown' => 'ti ti-chevron-down', 'toolbar' => 'ti ti-toolbar', 'ribbon' => 'ti ti-award',
            'comingSoon' => 'ti ti-hourglass', 'maintenance' => 'ti ti-tools', 'sectionTitle' => 'ti ti-heading',
            'featureCard' => 'ti ti-bulb', 'iconCard' => 'ti ti-bolt', 'iconList' => 'ti ti-checklist',
            'steps' => 'ti ti-list-numbers', 'process' => 'ti ti-git-branch', 'countUp' => 'ti ti-trending-up',
            'textEditor' => 'ti ti-writing', 'chart' => 'ti ti-chart-bar', 'echart' => 'ti ti-chart-area',
            'apexChart' => 'ti ti-chart-areaspline', 'dataTable' => 'ti ti-database', 'calendar' => 'ti ti-calendar',
            'chat' => 'ti ti-message-circle', 'kanban' => 'ti ti-kanban', 'testimonial' => 'ti ti-quote',
            'notification' => 'ti ti-bell', 'alertCard' => 'ti ti-alert-triangle', 'infoCard' => 'ti ti-info-circle',
            'userCard' => 'ti ti-user', 'teamCard' => 'ti ti-users', 'imageCard' => 'ti ti-image', 'videoCard' => 'ti ti-video',
            'htmlBlock' => 'ti ti-code', 'iframeBlock' => 'ti ti-frame', 'linkCard' => 'ti ti-link', 'tagCard' => 'ti ti-tag',
            'banner' => 'ti ti-flag', 'hero' => 'ti ti-layout', 'cta' => 'ti ti-speakerphone', 'nav' => 'ti ti-menu',
            'footer' => 'ti ti-layout',
        ];
        return $map[$alias] ?? 'ti ti-box';
    }

    public static function catalog(): array
    {
        $list = XfAdmin::componentList();
        $groups = [];
        $components = [];
        $diyDefaults = self::diyDefaults();

        foreach ($list as $alias => $class) {
            // 编辑器自身不进入组件面板（唯一需排除的组件）
            if ($class === \zxf\XfAdmin\Components\Layout\DiyLayoutPage::class || $alias === 'diy' || $alias === 'diyLayoutPage') {
                continue;
            }
            // 整页 / 导航类布局组件（渲染完整 HTML 文档或页面级 chrome，而非可嵌入的内容片段）
            // 拖入舞台会输出 <head>/<script> 或嵌套破损结构，故不进入组件面板。
            static $diyExcludeClasses = [
                \zxf\XfAdmin\Components\Layout\Page::class,
                \zxf\XfAdmin\Components\Layout\Sidenav::class,
                \zxf\XfAdmin\Components\Layout\Topbar::class,
                \zxf\XfAdmin\Components\Layout\TopNav::class,
                \zxf\XfAdmin\Components\Layout\Footer::class,
                \zxf\XfAdmin\Components\Layout\Customizer::class,
                \zxf\XfAdmin\Components\Layout\AuthPage::class,
                \zxf\XfAdmin\Components\Layout\ErrorPage::class,
                \zxf\XfAdmin\Components\Layout\ComingSoon::class,
                \zxf\XfAdmin\Components\Layout\Maintenance::class,
                \zxf\XfAdmin\Components\Layout\Landing::class,
                \zxf\XfAdmin\Components\Layout\LockScreen::class,
                \zxf\XfAdmin\Components\Layout\AccountSettingsPanel::class,
            ];
            if (in_array($class, $diyExcludeClasses, true)) {
                continue;
            }
            $cat = self::categoryOf($class);
            $catKey = $cat['key'];
            $groups[$catKey] = $cat['label'];

            $defaults = self::defaultsOf($class);
            $dd = $diyDefaults[$alias] ?? null;
            // 用「diy 示例默认值优先于真实默认值」构建 schema，
            // 使拖入即有内容的组件，其右侧面板也能呈现完整可编辑字段。
            $schemaSrc = $dd ?? $defaults;
            $schema = self::buildSchema($alias, $schemaSrc);
            $components[] = [
                'alias'      => $alias,
                'label'      => self::LABELS[$alias] ?? self::labelOf($alias),
                'category'   => $catKey,
                'icon'       => self::iconFor($alias),
                'desc'       => $cat['label'],
                'defaults'   => $defaults,
                'diy_defaults' => $dd,
                'schema'     => $schema,
                'param_count' => count($schema),
            ];
        }

        // 分类按固定顺序输出
        $ordered = [];
        foreach (self::CATEGORIES as $key => $label) {
            if (isset($groups[$key])) {
                $ordered[$key] = $label;
            }
        }
        foreach ($groups as $key => $label) {
            if (! isset($ordered[$key])) {
                $ordered[$key] = $label;
            }
        }

        return ['categories' => $ordered, 'components' => $components];
    }

    /**
     * 内置模板（左侧「模板」分组，点击即插入整段布局）
     *
     * @return array<int, array{name:string, icon:string, tree:array}>
     */
    public static function templates(): array
    {
        return [
            [
                'name' => '统计卡片行',
                'icon' => 'ti ti-chart-bar',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'pageTitle', 'options' => ['title' => '数据概览', 'subtitle' => '今日关键指标']],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-3'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '访问量', 'value' => '12,846', 'icon' => 'ti ti-eye', 'variant' => 'primary', 'trend' => ['value' => '+12.5%', 'direction' => 'up']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '销售额', 'value' => '¥86,240', 'icon' => 'ti ti-coin', 'variant' => 'success', 'trend' => ['value' => '+8.1%', 'direction' => 'up']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '新用户', 'value' => '328', 'icon' => 'ti ti-user-plus', 'variant' => 'info', 'trend' => ['value' => '-2.4%', 'direction' => 'down']]],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '双栏卡片',
                'icon' => 'ti ti-layout-columns',
                'tree' => [
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 6]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '左栏卡片', 'body' => '在左侧拖入更多组件，或在此卡片上点击编辑内容。']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 6]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '右栏卡片', 'body' => '右侧属性区会随选中组件自动切换。']],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '英雄区',
                'icon' => 'ti ti-star',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'card', 'options' => [
                        'class' => 'text-center py-5',
                        'body'  => '<h1 class="display-5 fw-bold mb-3">欢迎使用 DIY 布局器</h1>'
                            . '<p class="lead text-muted">从左侧拖入任意组件，右侧编辑属性，像搭积木一样构建页面。</p>'
                            . '<a class="btn btn-primary btn-lg" href="#">立即开始</a>',
                    ]],
                ],
            ],
            [
                'name' => '图文混排',
                'icon' => 'ti ti-layout-grid',
                'tree' => [
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4 align-items-center'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 5]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['body' => '<img src="' . XfAdmin::img('gallery/1.jpg') . '" class="img-fluid rounded" alt="示例">']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 7]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '图文标题', 'body' => '在右侧属性区修改图片地址与文案。']],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '表单卡片',
                'icon' => 'ti ti-forms',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '快速表单', 'body' => (string) XfAdmin::form([
                        'fields' => [
                            ['label' => '姓名', 'name' => 'name', 'type' => 'text', 'placeholder' => '请输入姓名'],
                            ['label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入邮箱'],
                        ],
                        'buttons' => [['text' => '提交', 'variant' => 'primary', 'type' => 'submit']],
                    ])]],
                ],
            ],
            [
                'name' => '列表卡片',
                'icon' => 'ti ti-list-check',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'listGroup', 'options' => ['items' => [
                        ['title' => '项目一', 'text' => '描述内容', 'badge' => ['text' => '进行中', 'variant' => 'primary']],
                        ['title' => '项目二', 'text' => '描述内容', 'badge' => ['text' => '已完成', 'variant' => 'success']],
                        ['title' => '项目三', 'text' => '描述内容', 'badge' => ['text' => '待处理', 'variant' => 'warning']],
                    ]]],
                ],
            ],
            [
                'name' => '标签页卡',
                'icon' => 'ti ti-tabs',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'tabs', 'options' => ['tabs' => [
                        ['title' => '概览', 'content' => '第一个标签页的内容。'],
                        ['title' => '详情', 'content' => '第二个标签页的内容。'],
                        ['title' => '设置', 'content' => '第三个标签页的内容。'],
                    ]]],
                ],
            ],
            [
                'name' => '空白栅格',
                'icon' => 'ti ti-layout-grid-add',
                'tree' => [
                    ['kind' => 'row', 'options' => ['gutters' => 'g-3'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => []],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => []],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => []],
                    ]],
                ],
            ],
            [
                'name' => '数据看板',
                'icon' => 'ti ti-chart-pie',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'pageTitle', 'options' => ['title' => '运营数据看板', 'subtitle' => '实时监控核心指标']],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-3'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 3]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '今日访问', 'value' => '12,846', 'icon' => 'ti ti-eye', 'variant' => 'primary', 'trend' => ['value' => '+12.5%', 'direction' => 'up']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 3]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '订单数', 'value' => '1,284', 'icon' => 'ti ti-shopping-cart', 'variant' => 'success', 'trend' => ['value' => '+8.1%', 'direction' => 'up']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 3]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '转化率', 'value' => '3.6%', 'icon' => 'ti ti-percentage', 'variant' => 'info', 'trend' => ['value' => '+0.4%', 'direction' => 'up']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 3]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '退款率', 'value' => '0.8%', 'icon' => 'ti ti-receipt-refund', 'variant' => 'warning', 'trend' => ['value' => '-0.2%', 'direction' => 'down']]],
                        ]],
                    ]],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 7]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '最新动态', 'body' => (string) XfAdmin::component('listGroup', ['items' => [
                                ['title' => '系统完成每日备份', 'text' => '凌晨 02:00 自动执行', 'badge' => ['text' => '成功', 'variant' => 'success']],
                                ['title' => '新增 32 位注册用户', 'text' => '来自多渠道引流', 'badge' => ['text' => '新增', 'variant' => 'primary']],
                                ['title' => '支付通道抖动', 'text' => '已自动恢复', 'badge' => ['text' => '已恢复', 'variant' => 'warning']],
                            ]])]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 5]], 'children' => [
                            ['kind' => 'component', 'alias' => 'alert', 'options' => ['variant' => 'info', 'text' => '点击任意卡片即可在右侧属性区编辑内容、颜色与数据。']],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '个人资料',
                'icon' => 'ti ti-id',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'card', 'options' => ['body' => (string) XfAdmin::component('media', [
                        'img'   => XfAdmin::img('users/user-1.jpg'),
                        'title' => '张明 · 产品总监',
                        'text'  => '负责平台整体规划与体验设计，关注数据驱动的增长。',
                        'meta'  => '📧 ming@example.com · 📱 138-0000-0000',
                    ])]],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 6]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '基本资料', 'body' => '姓名：张明<br>部门：产品部<br>入职：2021-03']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 6]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '技能标签', 'body' => '<span class="badge bg-primary me-1">产品规划</span><span class="badge bg-info me-1">数据分析</span><span class="badge bg-success">团队协作</span>']],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '价格方案',
                'icon' => 'ti ti-ticket',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'pageTitle', 'options' => ['title' => '选择适合你的方案', 'subtitle' => '随时升级或降级']],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'pricingCard', 'options' => ['title' => '基础版', 'price' => '¥0', 'period' => '/月', 'features' => ['核心组件库', '社区支持', '单项目'], 'button' => ['text' => '免费开始', 'variant' => 'secondary']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'pricingCard', 'options' => ['title' => '专业版', 'price' => '¥99', 'period' => '/月', 'highlight' => true, 'features' => ['全部组件', '优先支持', '无限项目', '主题定制'], 'button' => ['text' => '立即升级', 'variant' => 'primary']]],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'pricingCard', 'options' => ['title' => '企业版', 'price' => '¥299', 'period' => '/月', 'features' => ['专业版全部', '专属客服', '私有部署'], 'button' => ['text' => '联系销售', 'variant' => 'success']]],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '常见问题',
                'icon' => 'ti ti-message-question',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'faq', 'options' => ['items' => [
                        ['q' => '如何添加新的组件？', 'a' => '从左侧组件栏拖拽或点击组件到舞台即可。'],
                        ['q' => '可以嵌套布局吗？', 'a' => '可以。拖入「栅格行 / 列」作为容器，再向其中放置其它组件。'],
                        ['q' => '如何导出页面？', 'a' => '点击顶部「导出」按钮，可复制或下载完整 HTML。'],
                    ]]],
                ],
            ],
            [
                'name' => '联系我们',
                'icon' => 'ti ti-mail',
                'tree' => [
                    ['kind' => 'row', 'options' => ['gutters' => 'g-4'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 5]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '联系方式', 'body' => '📧 support@example.com<br>☎ 400-000-0000<br>🏢 北京市朝阳区']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 7]], 'children' => [
                            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '在线留言', 'body' => (string) XfAdmin::form([
                                'fields' => [
                                    ['label' => '姓名', 'name' => 'name', 'type' => 'text', 'placeholder' => '请输入姓名'],
                                    ['label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入邮箱'],
                                    ['label' => '留言', 'name' => 'message', 'type' => 'textarea', 'placeholder' => '请输入留言内容'],
                                ],
                                'buttons' => [['text' => '提交', 'variant' => 'primary', 'type' => 'submit']],
                            ])]],
                        ]],
                    ]],
                ],
            ],
            [
                'name' => '营销落地页',
                'icon' => 'ti ti-rocket',
                'tree' => [
                    ['kind' => 'component', 'alias' => 'card', 'options' => [
                        'class' => 'text-center py-5',
                        'body'  => '<h1 class="display-5 fw-bold mb-3">用积木方式构建后台页面</h1>'
                            . '<p class="lead text-muted">200+ 组件、实时预览、一键导出，让页面搭建前所未有的高效。</p>'
                            . '<a class="btn btn-primary btn-lg me-2" href="#">免费试用</a><a class="btn btn-outline-secondary btn-lg" href="#">查看文档</a>',
                    ]],
                    ['kind' => 'row', 'options' => ['gutters' => 'g-3'], 'children' => [
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '组件数量', 'value' => '200+', 'icon' => 'ti ti-components', 'variant' => 'primary']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '渲染速度', 'value' => '实时', 'icon' => 'ti ti-bolt', 'variant' => 'success']],
                        ]],
                        ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                            ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '导出格式', 'value' => 'HTML', 'icon' => 'ti ti-file-export', 'variant' => 'info']],
                        ]],
                    ]],
                ],
            ],
        ];
    }

    /**
     * 渲染入口（供 demo/router.php 与 wsf DemoController 调用）
     *
     * @param  array  $payload  {mode, alias, options, tree}
     * @return mixed  block → array{html,css,js}；page/export → string
     */
    public static function render(array $payload): mixed
    {
        $mode = (string) ($payload['mode'] ?? 'block');

        if ($mode === 'block') {
            $alias   = (string) ($payload['alias'] ?? '');
            $options = (array) ($payload['options'] ?? []);
            $html    = self::renderComponent($alias, $options);

            $assets = Assets::instance();
            $mk     = static fn (array $files): array => array_map(
                static fn (string $f) => XfAdmin::asset($f),
                $files
            );

            return [
                'html' => $html,
                'css'  => $mk($assets->cssFiles()),
                'js'   => $mk($assets->jsFiles()),
            ];
        }

        $tree = (array) ($payload['tree'] ?? []);

        if ($mode === 'export') {
            $inner = self::renderTree($tree, 0);
            return (string) XfAdmin::page([
                'title'      => 'DIY 导出页面',
                'sidenav'    => false,
                'topbar'     => false,
                'footer'     => false,
                'customizer' => false,
                'container'  => 'container-fluid',
                'content'    => $inner,
            ]);
        }

        // page 模式：干净片段
        return self::renderTree($tree, 0);
    }

    /**
     * 递归渲染整棵块树为干净 HTML
     */
    public static function renderTree(array $tree, int $depth): string
    {
        if ($depth > self::MAX_DEPTH) {
            return '';
        }
        $html = '';
        $count = 0;
        foreach ($tree as $block) {
            if (++$count > self::MAX_BLOCKS) {
                break;
            }
            $html .= self::renderBlock((array) $block, $depth);
        }

        return $html;
    }

    /**
     * 渲染单个块（组件 / 行 / 列）
     */
    private static function renderBlock(array $block, int $depth): string
    {
        $kind = (string) ($block['kind'] ?? 'component');

        if ($kind === 'row') {
            $opt   = (array) ($block['options'] ?? []);
            $cls   = 'row ' . ($opt['gutters'] ?? 'g-3');
            $inner = self::renderTree((array) ($block['children'] ?? []), $depth + 1);

            return '<div class="' . Html::e(trim($cls)) . '">' . $inner . '</div>';
        }

        if ($kind === 'col') {
            $opt   = (array) ($block['options'] ?? []);
            $cls   = self::colClass($opt['width'] ?? null);
            $inner = self::renderTree((array) ($block['children'] ?? []), $depth + 1);

            return '<div class="' . Html::e($cls) . '">' . $inner . '</div>';
        }

        return self::renderComponent((string) ($block['alias'] ?? ''), (array) ($block['options'] ?? []));
    }

    /**
     * 渲染单个组件块
     */
    private static function renderComponent(string $alias, array $options): string
    {
        if ($alias === '' || ! XfAdmin::has($alias)) {
            return '<div class="alert alert-warning mb-0">未知组件：' . Html::e($alias) . '</div>';
        }

        return (string) XfAdmin::component($alias, $options);
    }

    /**
     * 列宽 class 生成（断点键走白名单、列数夹紧 1~12）
     */
    private static function colClass(mixed $width): string
    {
        if ($width === null || $width === '' || $width === false) {
            return 'col-12';
        }
        if (is_array($width)) {
            $cls = 'col-12';
            $bps = ['sm', 'md', 'lg', 'xl', 'xxl'];
            foreach ($width as $bp => $cols) {
                if (! in_array($bp, $bps, true)) {
                    continue;
                }
                $n = (int) $cols;
                if ($n < 1 || $n > 12) {
                    continue;
                }
                $cls .= ' col-' . $bp . '-' . $n;
            }

            return $cls;
        }
        if (is_numeric($width)) {
            $n = (int) $width;

            return ($n < 1 || $n > 12) ? 'col-12' : 'col-12 col-md-' . $n . ' col-xl-' . $n;
        }

        return 'col-12';
    }

    /**
     * 取组件默认配置（反射调用受保护 defaults()，避免触发构造函数副作用）
     */
    private static function defaultsOf(string $class): array
    {
        if (! class_exists($class)) {
            return [];
        }
        try {
            $obj = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            $ref = new \ReflectionMethod($class, 'defaults');
            $defaults = $ref->invoke($obj);
        } catch (\Throwable) {
            return [];
        }

        return self::sanitizeForJson($defaults);
    }

    /**
     * 把任意值转为可 JSON 序列化结构（闭包 / 不可序列化对象 → null）
     */
    private static function sanitizeForJson(mixed $value): mixed
    {
        if ($value instanceof \Closure) {
            return null;
        }
        if (is_object($value)) {
            if ($value instanceof \Stringable) {
                return (string) $value;
            }

            return null;
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::sanitizeForJson($v);
            }

            return $out;
        }

        return $value;
    }

    private static function categoryOf(string $class): array
    {
        if (preg_match('#\\\\Components\\\\(\w+)\\\\#', $class, $m)) {
            $key = $m[1];
            if (isset(self::CATEGORIES[$key])) {
                return ['key' => $key, 'label' => self::CATEGORIES[$key]];
            }

            return ['key' => $key, 'label' => $key];
        }

        return ['key' => 'Misc', 'label' => '杂项'];
    }

    private static function labelOf(string $alias): string
    {
        $parts = preg_split('/(?<!^)(?=[A-Z])/', $alias);

        return ucfirst(implode(' ', $parts));
    }
}

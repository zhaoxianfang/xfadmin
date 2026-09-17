<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Assets\Assets;
use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\DiyRenderer;
use zxf\XfAdmin\XfAdmin;

/**
 * DIY 可视化布局器（对标 Bootstrap LayoutIt）
 *
 * 三栏式页面构建器：
 *   - 左侧：本扩展包全部已注册组件 + 内置模板（按分类分组，可拖拽）；
 *   - 中间：布局舞台（拖入组件即实时服务端渲染预览，可继续拖入行/列嵌套布局）；
 *   - 右侧：选中组件/模板的属性、配置、数据编辑面板（字段由组件 defaults() 自动生成）。
 *
 * 用法：
 *   echo XfAdmin::diyLayoutPage([
 *       'render_url' => '/diy/render',   // 服务端实时渲染端点（返回 {html,css,js} 或整页 HTML）
 *       'blocks'     => [...],           // 可选：初始块树（缺省载入内置示例布局）
 *   ]);
 *
 * 端点约定（demo/router.php 与 wsf DemoController 均已实现）：
 *   POST {render_url}  body: {mode:'block'|'page'|'export', alias, options, tree}
 */
class DiyLayoutPage extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'title'      => 'DIY 可视化布局器',
            'render_url' => '/diy/render',
            'blocks'     => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['diy'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $renderUrl = $this->safeUrl($this->get('render_url'), '/diy/render');

        $catalog   = DiyRenderer::catalog();
        $templates = DiyRenderer::templates();
        $blocks    = $this->get('blocks');
        // 初始化时舞台为空：全部由用户自定义编辑；已编辑内容通过 localStorage 草稿或导入恢复，
        // 服务端传入的 blocks 也会优先使用。内置「示例」仅在点击「加载示例」时注入。
        if (! is_array($blocks) || $blocks === []) {
            $blocks = [];
        }

        $config = json_encode([
            'render_url' => $renderUrl,
            'categories' => $catalog['categories'],
            'components' => $catalog['components'],
            'templates'  => $templates,
            'blocks'     => $blocks,
            'sample'     => $this->starterBlocks(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 关键：JSON 中的结构性双引号必须 HTML 转义，否则会提前终止 data-xf-config="..." 属性，
        // 导致浏览器读取到的配置只剩 "{"，JSON.parse 抛错并使整个编辑器初始化中断。
        $shell = '<div class="xf-diy-editor" data-xf="diy" data-xf-config="' . htmlspecialchars($config, ENT_QUOTES, 'UTF-8') . '">'
            . $this->palette($catalog['categories'], $templates)
            . $this->stage()
            . $this->props()
            . '</div>';

        return (string) XfAdmin::page([
            'title'       => $this->get('title'),
            'sidenav'     => false,
            'topbar'      => false,
            'footer'      => false,
            'customizer'  => false,
            'container'   => '',
            'body_class'  => 'xf-diy-page',
            'content'     => $shell,
        ]);
    }

    /**
     * 左侧组件 / 模板操作栏
     */
    private function palette(array $categories, array $templates): string
    {
        $catOpts = '';
        foreach ($categories as $key => $label) {
            $catOpts .= '<option value="' . $this->e($key) . '">' . $this->e($label) . '</option>';
        }

        $tplHtml = '';
        foreach ($templates as $i => $t) {
            $tplHtml .= '<button type="button" class="xf-diy-tpl" data-tpl="' . $i . '" draggable="true" title="拖入或点击添加到舞台">'
                . '<i class="' . $this->e($t['icon']) . ' xf-diy-tpl-ico"></i>'
                . '<span class="xf-diy-tpl-name">' . $this->e($t['name']) . '</span>'
                . '<span class="xf-diy-tpl-cat">模板</span></button>';
        }

        // 布局元素（行 / 列）：拖入或点击即可在舞台/栅格中插入容器
        $layoutHtml = '<div class="xf-diy-layout-list">'
            . '<button type="button" class="xf-diy-comp-item" draggable="true" data-diy-kind="row" title="拖入或点击添加栅格行">'
            . '<i class="xf-diy-comp-ico ti ti-layout-rows"></i><span>栅格行</span><span class="xf-diy-comp-cat">row</span></button>'
            . '<button type="button" class="xf-diy-comp-item" draggable="true" data-diy-kind="col" title="拖入或点击添加栅格列">'
            . '<i class="xf-diy-comp-ico ti ti-layout-columns"></i><span>栅格列</span><span class="xf-diy-comp-cat">col</span></button>'
            . '</div>';

        // 组件项（由 JS 根据 catalog 渲染，避免服务端硬编码 200+ 项）
        return '<aside class="xf-diy-palette">'
            . '<div class="xf-diy-palette-head">'
            . '<div class="input-group input-group-sm">'
            . '<span class="input-group-text"><i class="ti ti-search"></i></span>'
            . '<input type="text" class="form-control xf-diy-search" placeholder="搜索组件…">'
            . '</div>'
            . '<select class="form-select form-select-sm mt-2 xf-diy-cat-filter"><option value="">全部分类</option>' . $catOpts . '</select>'
            . '</div>'
            . '<div class="xf-diy-palette-scroll xf-scroll-soft">'
            . '<div class="xf-diy-palette-section"><div class="xf-diy-palette-title"><i class="ti ti-layout-template"></i> 模板</div>'
            . '<div class="xf-diy-tpl-list">' . $tplHtml . '</div></div>'
            . '<div class="xf-diy-palette-section"><div class="xf-diy-palette-title"><i class="ti ti-layout-grid"></i> 布局元素</div>'
            . $layoutHtml . '</div>'
            . '<div class="xf-diy-palette-section"><div class="xf-diy-palette-title"><i class="ti ti-components"></i> 组件（' . count(DiyRenderer::catalog()['components']) . '）</div>'
            . '<div class="xf-diy-comp-list"></div></div>'
            . '</div>'
            . '</aside>';
    }

    /**
     * 中间舞台
     */
    private function stage(): string
    {
        return '<main class="xf-diy-stage-wrap">'
            . '<div class="xf-diy-toolbar">'
            . '<div class="xf-diy-toolbar-left">'
            . '<span class="xf-diy-brand"><i class="ti ti-layout-dashboard"></i> 可视化布局器</span>'
            . '</div>'
            . '<div class="xf-diy-toolbar-center">'
            . '<div class="xf-diy-devices" role="group" title="设备预览">'
            . '<button type="button" data-dev="desktop" class="active" title="桌面"><i class="ti ti-device-desktop-analytics"></i></button>'
            . '<button type="button" data-dev="tablet" title="平板"><i class="ti ti-device-tablet"></i></button>'
            . '<button type="button" data-dev="mobile" title="手机"><i class="ti ti-device-mobile"></i></button>'
            . '</div>'
            . '<div class="xf-diy-dev-width-wrap" title="设备宽度（px）">'
            . '<i class="ti ti-resize"></i>'
            . '<input type="number" class="xf-diy-dev-width" min="280" max="1600" step="10" value="834">'
            . '</div>'
            . '<div class="xf-diy-sep"></div>'
            . '<div class="xf-diy-history">'
            . '<button type="button" class="xf-diy-btn-undo" title="撤销 (Ctrl+Z)" disabled><i class="ti ti-arrow-back-up"></i></button>'
            . '<button type="button" class="xf-diy-btn-redo" title="重做 (Ctrl+Y)" disabled><i class="ti ti-arrow-forward-up"></i></button>'
            . '</div>'
            . '<div class="xf-diy-sep"></div>'
            . '<button type="button" class="xf-diy-btn-preview" title="预览模式（隐藏编辑栏）"><i class="ti ti-eye"></i></button>'
            . '<div class="xf-diy-sep"></div>'
            . '<div class="xf-diy-zoom" role="group" title="缩放画布">'
            . '<button type="button" data-zoom="out" title="缩小"><i class="ti ti-minus"></i></button>'
            . '<span class="xf-diy-zoom-val">100%</span>'
            . '<button type="button" data-zoom="in" title="放大"><i class="ti ti-plus"></i></button>'
            . '<button type="button" data-zoom="reset" title="重置缩放"><i class="ti ti-refresh"></i></button>'
            . '</div>'
            . '</div>'
            . '<div class="xf-diy-toolbar-right">'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-btn-help" title="使用帮助"><i class="ti ti-help me-1"></i>帮助</button>'
            . '<button type="button" class="btn btn-sm btn-soft-primary xf-diy-btn-code" title="生成 Laravel / ThinkPHP 代码"><i class="ti ti-code me-1"></i>生成代码</button>'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-btn-sample" title="载入内置示例布局"><i class="ti ti-layout-template me-1"></i>示例</button>'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-btn-save" title="保存草稿到本地"><i class="ti ti-device-floppy me-1"></i>保存</button>'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-btn-import" title="导入 JSON"><i class="ti ti-file-import me-1"></i>导入</button>'
            . '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-btn-export" title="导出 HTML"><i class="ti ti-file-export me-1"></i>导出</button>'
            . '<button type="button" class="btn btn-sm btn-soft-danger xf-diy-btn-clear" title="清空舞台"><i class="ti ti-trash me-1"></i>清空</button>'
            . '</div>'
            . '</div>'
            . '<div class="xf-diy-stage xf-scroll-soft" id="xfDiyStage">'
            . '<div class="xf-diy-stage-empty xf-empty-hint">'
            . '<div class="xf-diy-empty-art"><i class="ti ti-layout-dashboard"></i></div>'
            . '<div class="h5 mb-1">空白画布</div>从左侧拖入或点击组件 / 模板开始构建页面'
            . '</div>'
            . '</div>'
            . '</main>';
    }

    /**
     * 右侧属性面板
     */
    private function props(): string
    {
        return '<aside class="xf-diy-props">'
            . '<div class="xf-diy-props-head"><i class="ti ti-sliders"></i> 属性 / 配置</div>'
            . '<div class="xf-diy-props-body xf-scroll-soft" id="xfDiyProps">'
            . '<div class="xf-diy-props-empty xf-empty-hint py-5">'
            . '<i class="ti ti-click fs-2 d-block mb-2"></i>点击舞台中的组件<br>这里将显示其可编辑属性'
            . '</div>'
            . '</div>'
            . '</aside>';
    }

    /**
     * 初始示例布局（块树）
     */
    private function starterBlocks(): array
    {
        return [
            ['kind' => 'component', 'alias' => 'pageTitle', 'options' => ['title' => '我的自定义页面', 'subtitle' => '使用 DIY 布局器拖拽生成']],
            ['kind' => 'component', 'alias' => 'alert', 'options' => ['variant' => 'info', 'body' => '这是示例内容。从左侧拖入更多组件，点击任意组件在右侧编辑属性。']],
            ['kind' => 'row', 'options' => ['gutters' => 'g-3'], 'children' => [
                ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                    ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '组件总数', 'value' => '200+', 'icon' => 'ti ti-components', 'variant' => 'primary']],
                ]],
                ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                    ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '模板数', 'value' => '8', 'icon' => 'ti ti-layout-template', 'variant' => 'success']],
                ]],
                ['kind' => 'col', 'options' => ['width' => ['md' => 4]], 'children' => [
                    ['kind' => 'component', 'alias' => 'statCard', 'options' => ['title' => '渲染模式', 'value' => '实时', 'icon' => 'ti ti-bolt', 'variant' => 'info']],
                ]],
            ]],
            ['kind' => 'component', 'alias' => 'card', 'options' => ['title' => '欢迎卡片', 'body' => '把组件拖进栅格列里即可实现任意嵌套布局。']],
        ];
    }
}

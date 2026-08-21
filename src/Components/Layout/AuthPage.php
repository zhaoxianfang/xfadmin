<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;

/**
 * 认证页（三套布局 / 7 种语义类型）
 *
 * 用途：
 *  - 提供 登录 / 注册 / 忘记密码 / 设置新密码 / 重置密码 / 锁屏 / PIN 登录 等认证页标准化渲染，
 *    外加向后兼容的两步验证 / 注销账户 / 邮件发送成功 3 种类型。
 *  - 支持 base（精简居中）/ card（左右分栏卡片）/ split（左图右表单）3 套布局，
 *    严格对齐 INSPINIA 后台模板中 auth-* / auth-card-* / auth-split-* 的标记结构与排版。
 *
 * 布局说明（与后台模板一一对应）：
 *  - base  ：auth-sign-in.html 等（单卡片居中，无侧栏图）
 *  - card  ：auth-card-sign-in.html 等（左右分栏：左表单卡片 + 右侧宣传图）
 *  - split ：auth-split-sign-in.html 等（左整幅背景图 + 右自动宽表单卡片）
 *
 * 设计要点：
 *  - 这是一个“完整 HTML 文档”组件，由调用方直接 response() 输出（不包裹后台模板）。
 *  - 复用 XfAdmin 共享资产（config.js / vendors.min.css / app.min.css / xfadmin.css / app.js），
 *    因此渲染出的标记与 INSPINIA 后台模板共享同一套 CSS 变量与组件类。
 *  - fields 必须传【关联数组】（key 即字段名），例如：
 *      'fields' => ['email' => [...], 'password' => [...]]
 *    组件内部按 $f['email'] / $f['password'] 读取，与后端验证规则字段名保持一致。
 *
 * 自定义表单 / 模板插槽（支持在任意表单前后插入开发者自定义内容）：
 *  - 表单整体：'beforeForm' / 'afterForm'（raw HTML，插入到 <form> 标签之前 / </form> 之后）
 *  - 卡片主体：'prepend' / 'append'（raw HTML，插入到卡片标题之后 / 卡片底部之前）
 *  - 单字段：每个 fields[*] 内可带 'before' / 'after'（raw HTML，插入到该字段之前 / 之后）
 *  - 全局：'content' 直接接管整张卡片主体（raw），其余表单自动跳过
 *
 * 配置兼容（向后兼容历史调用方）：
 *  - 表单地址：'action' 或 'formAttrs' => ['action' => ...]
 *  - 提交按钮：'submit'（字符串标签，生成默认主按钮）或 'buttons'（自定义按钮数组）
 *  - 验证码：'captcha' 为 bool 时渲染占位；为字符串时原样输出（如 web component 组件 HTML）
 *  - 底部链接：'links' => [['text'=>..., 'href'=>...]] 或 loginRedirect/registerRedirect/footerLinks/backLink
 *
 * 通过 XfAdmin::authPage(['type' => 'sign-in', 'layout' => 'split', ...]) 调用；
 * 也提供 XfAdmin::signIn() / signUp() ... 等语义别名快捷方法（自动注入 type）。
 */
class AuthPage extends Component
{
    /** 7 种核心语义类型 + 3 种兼容类型 */
    protected const TYPES = [
        'sign-in', 'sign-up', 'reset-pass', 'new-pass',
        'lock-screen', 'login-pin',
        'two-factor', 'delete-account', 'success-mail',
    ];

    /** 3 套布局（base 与 basic 等价，basic 为历史别名） */
    protected const LAYOUTS = ['base', 'card', 'split'];

    /** 顶部链接文案的默认映射（按 type 自动生成“去/回”导航） */
    protected const DEFAULT_LINKS = [
        'sign-in'        => ['registerRedirect', 'loginRedirect'],
        'sign-up'        => ['loginRedirect'],
        'reset-pass'     => ['loginRedirect'],
        'new-pass'       => [],
        'lock-screen'    => ['loginRedirect'],
        'login-pin'      => ['loginRedirect'],
        'two-factor'     => ['loginRedirect'],
        'delete-account' => [],
        'success-mail'   => ['loginRedirect'],
    ];

    /** card / split 布局表单输入框的辅助图标（按字段名） */
    protected const FIELD_ICONS = [
        'email'                => 'ti ti-mail',
        'password'             => 'ti ti-lock',
        'password_confirmation'=> 'ti ti-lock',
        'name'                 => 'ti ti-user',
        'pin'                  => 'ti ti-key',
        'code'                 => 'ti ti-shield-lock',
    ];

    /**
     * defaults（protected实例方法）
     * 组件默认配置（调用方未传入时使用）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            // —— 布局与外观 ——
            'layout'        => 'base',   // base | card | split（basic 视为 base）
            'class'         => '',       // 附加到根容器 class
            'id'            => '',       // 根容器 id
            'bodyClass'     => '',       // 卡片 / 主体区域附加 class
            'theme'         => 'light', // light | dark（split/card 侧栏风格）

            // —— 品牌 / 文案 ——
            'brand'         => [
                'name' => 'XfAdmin',
                'url'  => '/',
                'logo' => null,          // 图片地址（http(s):/data:/包内 images/ 相对路径）
            ],
            'title'         => '',       // <title>
            'heading'       => '',       // 主标题
            'subheading'    => '',       // 副标题（历史字段名 subtitle 兼容）
            'copyright'     => '',       // 底部版权
            'status'        => '',       // 顶部状态提示（绿，通常由控制器 with('status') 注入）
            'message'       => '',       // 说明性文本（如 new-pass 的提示语）

            // —— 表单配置 ——
            'action'        => '',       // 表单提交地址（核心字段；formAttrs.action 兼容）
            'method'        => 'POST',
            'fields'        => [],       // 关联数组：['email' => [...], 'password' => [...]]
            'buttons'       => [],       // 按钮数组（label/variant/type/...）
            'submit'        => '提交',    // 默认提交按钮文案（未传 buttons 时使用）
            'content'       => '',       // 自定义主体内容（raw，覆盖默认表单）
            'below'         => '',       // 表单下方补充内容（raw，如提示文案 / 链接）
            // 自定义插槽（raw HTML）
            'beforeForm'    => '',       // 插入到 <form> 之前
            'afterForm'     => '',       // 插入到 </form> 之后
            'prepend'       => '',       // 插入到卡片标题之后（表单之前）
            'append'        => '',       // 插入到卡片底部（links 之前）
            'links'         => [],       // 顶部 / 底部导航链接（[['text'=>..., 'href'=>...], ...]）

            // —— 导航链接（均会经 e() 转义，配置时请确保传入可信地址）——
            'loginRedirect'   => '/login',    // “已有账号？去登录” 链接
            'registerRedirect'=> '/register', // “还没有账号？去注册” 链接
            'backLink'        => null,        // 返回链接（['url'=>..., 'text'=>...] 或 null 隐藏）
            'footerLinks'     => [],          // 底部链接列表（[['url'=>..., 'text'=>...], ...]）

            // —— 社交登录 ——
            'socialButtons'   => [],          // [['icon'=>..., 'url'=>..., 'label'=>...], ...]

            // —— split / card 布局侧栏 ——
            // 背景图地址：默认包内 auth.jpg（与后台模板 auth-split-* / auth-card-* 的
            // assets/images/auth.jpg 一致，未传时也自动有背景图）；传 '' / false / null
            // 可显式关闭，回退为 sideVariant 纯色渐变。
            'sideImage'       => 'auth.jpg',
            'sideImageAlt'    => '',       // 背景图 alt
            'sideImageSize'   => 'cover',  // 背景尺寸（CSS background-size：cover/contain/100% 100%...）
            'sideImagePosition' => 'center', // 背景定位（CSS background-position：center/top left...）
            'sideOverlay'     => true,     // 是否叠加底部渐变遮罩（保证侧栏白字可读；false 时无暗角）
            'sideTitle'       => '企业级后台管理平台', // 侧栏标题（默认官方文案，可按需覆盖）
            'sideText'        => 'XfAdmin 提供组件化、标准化的一站式企业后台管理解决方案，覆盖业务运营、流程审批与数据分析等核心场景，助力企业实现数字化、规范化的高效管理。', // 侧栏文案
            'sideList'        => [
                ['icon' => 'ti ti-check', 'text' => '150+ 标准化业务组件，覆盖主流企业管理场景'],
                ['icon' => 'ti ti-check', 'text' => '数据可视化与权限治理体系，保障业务安全合规'],
                ['icon' => 'ti ti-check', 'text' => '多框架适配、开箱即用，支持企业级快速交付'],
            ], // 侧栏要点列表（[['icon'=>..., 'text'=>...], ...]，card/split 通用）
            'sideVariant'     => 'primary',// 侧栏强调色（primary/info/success/...，无背景图时作为纯色渐变）

            // —— 锁屏 / 用户 ——
            'user'          => [],       // ['name'=>..., 'avatar'=>..., 'email'=>...]

            // —— 杂项 ——
            'showBackToTop' => false,
            'captcha'       => false,    // bool=渲染占位；string=原样输出（如 web component HTML）
        ];
    }

    /**
     * assets（protected实例方法）
     * 认证页依赖的第三方资源（Bootstrap Icons 由 app.min.css 自带，无需额外声明）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return [];
    }

    /**
     * html（protected实例方法）
     * 按 layout + type 拼装完整认证页 HTML 文档
     *
     * @return string result
     */
    protected function html(): string
    {
        $layout = $this->enum($this->get('layout'), self::LAYOUTS, 'base');
        if ($layout === 'basic') {
            $layout = 'base'; // 历史别名
        }
        $type   = $this->enum($this->get('type', 'sign-in'), self::TYPES, 'sign-in');

        $this->set('layout', $layout);
        $this->set('type', $type);

        // 兼容历史字段名 subtitle → subheading
        if (($this->get('subheading') === '') && ($this->get('subtitle') !== '')) {
            $this->set('subheading', $this->get('subtitle'));
        }

        // 根据布局拼装完整文档
        return match ($layout) {
            'card'  => $this->docCard($type),
            'split' => $this->docSplit($type),
            default => $this->docBase($type),
        };
    }

    // ------------------------------------------------------------------
    // 文档骨架（base / card / split 三套）
    // ------------------------------------------------------------------

    /**
     * doc Base（protected实例方法）
     * base 布局（对齐 auth-sign-in.html 等单卡片居中结构）
     *
     * @param string $type 语义类型
     *
     * @return string result
     */
    protected function docBase(string $type): string
    {
        $brand  = $this->brandBlock('text-center');
        $form   = $this->renderForm($type, false);
        $copy   = $this->copyrightBlock();

        // 对齐 auth-sign-in.html：品牌下方追加 Welcome 标题 + 副标题 + 分隔线
        $welcome = '<div class="text-center mt-3 mb-4">';
        if (($h = (string) $this->get('heading')) !== '') {
            $welcome .= '<h4 class="mb-1 fw-semibold">' . $this->e($h) . '</h4>';
        } else {
            $welcome .= '<h4 class="mb-1 fw-semibold">Welcome !</h4>';
        }
        if (($s = (string) $this->get('subheading')) !== '') {
            $welcome .= '<p class="mb-0 text-muted">' . $this->e($s) . '</p>';
        }
        $welcome .= '<div class="auth-line"></div></div>';

        $body = '<div class="auth-box overflow-hidden align-items-center d-flex">' . "\n"
            . '  <div class="container">' . "\n"
            . '    <div class="row justify-content-center">' . "\n"
            . '      <div class="col-xxl-4 col-md-6 col-sm-8">' . "\n"
            . $brand . "\n"
            . $welcome . "\n"
            . $form . "\n"
            . $copy . "\n"
            . '      </div>' . "\n"
            . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</div>';

        return $this->document($body);
    }

    /**
     * doc Card（protected实例方法）
     * card 布局（对齐 auth-card-sign-in.html：左表单卡片 + 右侧宣传图）
     *
     * @param string $type 语义类型
     *
     * @return string result
     */
    protected function docCard(string $type): string
    {
        $brand = $this->brandBlock('d-flex align-items-center mb-4');
        $form  = $this->renderForm($type, true);

        $side = $this->sidePanel(false);

        $body = '<div class="auth-box d-flex align-items-center">' . "\n"
            . '  <div class="container-xxl">' . "\n"
            . '    <div class="row justify-content-center">' . "\n"
            . '      <div class="col-xl-10">' . "\n"
            . '        <div class="card rounded-4 overflow-hidden">' . "\n"
            . '          <div class="row g-0">' . "\n"
            . '            <div class="col-lg-6 card-body p-4 p-md-5">' . "\n"
            . $brand . "\n"
            . $form . "\n"
            . '            </div>' . "\n"
            . $side . "\n"
            . '          </div>' . "\n"
            . '        </div>' . "\n"
            . '      </div>' . "\n"
            . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</div>';

        return $this->document($body);
    }

    /**
     * doc Split（protected实例方法）
     * split 布局（对齐 auth-split-sign-in.html：左整幅背景图 + 右自动宽表单）
     *
     * @param string $type 语义类型
     *
     * @return string result
     */
    protected function docSplit(string $type): string
    {
        $brand = $this->brandBlock('mb-0 text-center');
        $form  = $this->renderForm($type, true);

        $img     = $this->sideImageUrl();
        $imgAlt  = (string) $this->get('sideImageAlt');
        $imgSize = $this->e((string) $this->get('sideImageSize', 'cover'));
        $imgPos  = $this->e((string) $this->get('sideImagePosition', 'center'));
        $overlay = $this->get('sideOverlay') ? 'auth-overlay' : 'auth-overlay-plain';
        $variant = $this->e($this->get('sideVariant', 'primary'));

        // 侧栏（左）：对齐 auth-split-*.html —— 外层 col + 内层整幅背景图
        // （card-side-img + background-image），叠加 card-img-overlay 文案（标题/正文/要点列表），
        // 缺图时回退 sideVariant 纯色渐变。
        $sideCls = 'h-100 position-relative card-side-img rounded-0 overflow-hidden auth-split-media--' . $variant;
        $side = '<div class="col">' . "\n"
            . '  <div class="' . $sideCls . '"'
            . ($img
                ? ' style="background-image: url(\'' . $this->e($img) . '\'); background-size: ' . $imgSize . '; background-position: ' . $imgPos . ';"'
                : ' data-solid="1"')
            . '>' . "\n";
        if ($imgAlt !== '') {
            $side .= '    <span class="visually-hidden">' . $this->e($imgAlt) . '</span>' . "\n";
        }
        $side .= '    <div class="p-4 card-img-overlay ' . $overlay . ' d-flex align-items-center justify-content-center">' . "\n";
        $side .= $this->sideContent();
        $side .= "\n" . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</div>';

        // 右侧表单卡片（对齐 auth-split-sign-in.html）：
        //   card auth-box-form border-0 mb-0 + card-body min-vh-100 d-flex flex-column justify-content-center
        //   品牌居中(mb-0) → 表单落入 mt-auto 区 → 底部“返回登录”链接 → 版权 mt-auto mb-0
        $links = $this->bottomLinksHtml($type);
        $copy  = $this->copyrightInline();

        $main = '<div class="col-md-auto">' . "\n"
            . '  <div class="card auth-box-form border-0 mb-0">' . "\n"
            . '    <div class="card-body min-vh-100 d-flex flex-column justify-content-center">' . "\n"
            . $brand . "\n"
            . '      <div class="mt-auto text-center">' . "\n"
            . $form . "\n"
            . ($links !== '' ? '        <div class="mt-3">' . $links . '</div>' . "\n" : '')
            . '      </div>' . "\n"
            . ($copy !== '' ? '      <p class="mt-auto mb-0 text-muted text-center small">' . $copy . '</p>' . "\n" : '')
            . '    </div>' . "\n"
            . '  </div>' . "\n"
            . '</div>';

        $body = '<div class="auth-box p-0 w-100">' . "\n"
            . '  <div class="row w-100 g-0">' . "\n"
            . $side . "\n"
            . $main . "\n"
            . '  </div>' . "\n"
            . '</div>';

        return $this->document($body);
    }

    /**
     * document（protected实例方法）
     * 包裹完整 HTML 文档骨架（head / meta / csrf-token / body / scripts）
     *
     * @param string $body 主体内容
     *
     * @return string result
     */
    protected function document(string $body): string
    {
        $title = $this->get('title') ?: ($this->get('heading') ?: 'XfAdmin');
        $rootCls = 'auth-page auth-' . $this->e($this->get('type')) . ' auth-layout-' . $this->e($this->get('layout'));
        $extraCls = (string) $this->get('class');
        if ($extraCls !== '') {
            $rootCls .= ' ' . $extraCls;
        }

        // 复用 XfAdmin 共享资产（与 INSPINIA 后台模板共用 app.min.css / xfadmin.css）
        $head = \zxf\XfAdmin\Assets\Assets::instance()->head();
        $scripts = \zxf\XfAdmin\Assets\Assets::instance()->scripts();

        return '<!DOCTYPE html>' . "\n"
            . '<html lang="zh-CN">' . "\n"
            . '<head>' . "\n"
            . '<meta charset="utf-8">' . "\n"
            . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n"
            . '<meta name="csrf-token" content="' . $this->csrfToken() . '">' . "\n"
            . '<title>' . $this->e($title) . '</title>' . "\n"
            . $head . "\n"
            . '</head>' . "\n"
            . '<body class="' . $rootCls . '">' . "\n"
            . $body . "\n"
            . ($this->get('showBackToTop') ? '<a href="#" class="back-to-top"><i class="ti ti-chevron-up"></i></a>' : '')
            . $this->footerLinksBlock()
            . $scripts . "\n"
            . '</body>' . "\n"
            . '</html>';
    }

    // ------------------------------------------------------------------
    // 公共片段
    // ------------------------------------------------------------------

    /**
     * brand Block（protected实例方法）
     * 渲染品牌区（logo + 名称 + 可选副标题）
     *
     * @param string $cls 附加 class
     *
     * @return string result
     */
    protected function brandBlock(string $cls): string
    {
        $brand = (array) $this->get('brand', []);
        $name  = $brand['name'] ?? 'XfAdmin';
        $url   = $brand['url'] ?? '/';
        $logo  = $brand['logo'] ?? null;

        if ($logo) {
            $mark = '<img src="' . $this->img($logo) . '" alt="' . $this->e($name) . '" class="auth-brand-logo">';
        } else {
            $mark = '<span class="auth-brand-text">' . $this->e($name) . '</span>';
        }

        return '<div class="auth-brand ' . $this->e($cls) . '">'
            . '<a href="' . $this->e($url) . '" class="auth-brand-link">' . $mark . '</a>'
            . '</div>';
    }

    /**
     * copyright Block（protected实例方法）
     * 渲染底部版权（base 布局用，置于卡片外）
     *
     * @return string result
     */
    protected function copyrightBlock(): string
    {
        $copyright = (string) $this->get('copyright');
        if ($copyright === '') {
            return '';
        }
        return '<div class="mt-4 text-center auth-copyright">' . $this->e($copyright) . '</div>';
    }

    /**
     * footer Links Block（protected实例方法）
     * 渲染底部链接列表（body 末尾，url/text 均经 e() 转义）
     *
     * @return string result
     */
    protected function footerLinksBlock(): string
    {
        $items = $this->get('footerLinks');
        if (! is_array($items) || $items === []) {
            return '';
        }
        $html = '<footer class="auth-footer"><div class="auth-footer-links">';
        foreach ($items as $item) {
            $item = (array) $item;
            $url  = $item['url'] ?? '#';
            $text = $item['text'] ?? '';
            if ($text === '') {
                continue;
            }
            $html .= '<a href="' . $this->e($url) . '" class="auth-footer-link">' . $this->e($text) . '</a>';
        }
        $html .= '</div></footer>';
        return $html;
    }

    /**
     * bottom Links Html（protected实例方法）
     * split 布局右侧卡片底部“返回登录”等导航链接（对齐 auth-split-* 末尾的 Return to Sign in）
     *
     * @param string $type 语义类型
     *
     * @return string result
     */
    protected function bottomLinksHtml(string $type): string
    {
        $links = $this->get('links');
        if (is_array($links) && $links !== []) {
            $html = '';
            foreach ($links as $item) {
                $item = (array) $item;
                $href = (string) ($item['href'] ?? ($item['url'] ?? ''));
                $text = (string) ($item['text'] ?? '');
                if ($text === '' || $href === '') {
                    continue;
                }
                $html .= '<a href="' . $this->e($href) . '" class="auth-link">' . $this->e($text) . '</a>'
                    . (isset($item['divider']) && $item['divider'] ? ' <span class="text-muted">/</span> ' : ' ');
            }
            return trim($html);
        }

        // 默认：除 sign-in 外均提供“返回登录”链接（对齐模板）
        if ($type === 'sign-in') {
            return '';
        }
        $url = (string) $this->get('loginRedirect', '/login');
        if ($url === '') {
            return '';
        }
        return '<a href="' . $this->e($url) . '" class="auth-link">返回登录</a>';
    }

    /**
     * copyright Inline（protected实例方法）
     * split 布局卡片底部内联版权（mt-auto mb-0）
     *
     * @return string result
     */
    protected function copyrightInline(): string
    {
        $copyright = (string) $this->get('copyright');
        if ($copyright === '') {
            return '';
        }
        // 数据本身已含 © 符号（如 “© 2026 XfAdmin 控制台”），不再额外加 &copy;
        return $this->e($copyright);
    }

    /**
     * side Image Url（protected实例方法）
     * 解析侧栏背景图（兼容 http(s):/包内 images/）
     *
     * @return string|null result
     */
    protected function sideImageUrl(): ?string
    {
        $img = $this->get('sideImage');
        if (! $img) {
            return null;
        }
        return $this->img($img);
    }

    /**
     * side Panel（protected实例方法）
     * card 布局右侧宣传图面板（对齐 auth-card-* 的 col-lg-6 d-none d-lg-block）
     *
     * @param bool $split 是否 split 布局（此处恒 false）
     *
     * @return string result
     */
    protected function sidePanel(bool $split): string
    {
        $img     = $this->sideImageUrl();
        $imgSize = $this->e((string) $this->get('sideImageSize', 'cover'));
        $imgPos  = $this->e((string) $this->get('sideImagePosition', 'center'));
        $overlay = $this->get('sideOverlay') ? 'auth-overlay' : 'auth-overlay-plain';
        $variant = $this->e($this->get('sideVariant', 'primary'));

        $html = '<div class="col-lg-6 d-none d-lg-block card-side-img auth-card-media auth-card-media--' . $variant . '">';
        if ($img) {
            // 与模板 auth-card-*.html 一致：card-side-img 用 background-image 承载背景图，
            // 便于 sideImageSize/sideImagePosition 生效（而非 <img> 标签）。
            $html .= '<div class="h-100 position-relative auth-card-media-bg"'
                . ' style="background-image: url(\'' . $this->e($img) . '\'); background-size: ' . $imgSize . '; background-position: ' . $imgPos . ';"></div>';
        }
        $html .= '<div class="p-4 card-img-overlay rounded-4 rounded-start-0 ' . $overlay . ' auth-overlay-card d-flex align-items-center justify-content-center">';
        $html .= $this->sideContent();
        $html .= '</div></div>';

        return $html;
    }

    /**
     * side Content（protected实例方法）
     * 侧栏叠加的标题 / 文案 / 要点列表（card 与 split 共用）
     *
     * @return string result
     */
    protected function sideContent(): string
    {
        $title = (string) $this->get('sideTitle');
        $text  = (string) $this->get('sideText');
        $list  = $this->get('sideList');

        $html = '';
        if ($title !== '') {
            $html .= '<h2 class="auth-side-title">' . $this->e($title) . '</h2>';
        }
        if ($text !== '') {
            $html .= '<p class="auth-side-text">' . $this->e($text) . '</p>';
        }
        if (is_array($list) && $list !== []) {
            $html .= '<ul class="auth-side-list">';
            foreach ($list as $item) {
                $item = (array) $item;
                $icon = $item['icon'] ?? 'ti ti-circle-check';
                $txt  = $item['text'] ?? '';
                if ($txt === '') {
                    continue;
                }
                $html .= '<li>'
                    . '<span class="auth-side-list-icon"><i class="' . $this->e($icon) . '"></i></span>'
                    . '<span class="auth-side-list-text">' . $this->e($txt) . '</span>'
                    . '</li>';
            }
            $html .= '</ul>';
        }

        return $html !== '' ? '<div class="auth-side-content">' . $html . '</div>' : '';
    }

    // ------------------------------------------------------------------
    // 表单渲染（按类型分发）
    // ------------------------------------------------------------------

    /**
     * render Form（protected实例方法）
     * 生成整张卡片 / 面板表单（含标题、主体、底部链接 + 开发者插槽）
     *
     * @param string $type   语义类型
     * @param bool   $withBg card/split 输入框带浅底样式
     *
     * @return string result
     */
    protected function renderForm(string $type, bool $withBg): string
    {
        // 调用方提供完整自定义主体时直接采用（raw）
        $content = $this->get('content');
        if (is_string($content) && $content !== '') {
            return $this->cardShell($content, $type, $withBg);
        }

        $body = match ($type) {
            'sign-in'     => $this->formSignIn($withBg),
            'sign-up'     => $this->formSignUp($withBg),
            'reset-pass'  => $this->formResetPass($withBg),
            'new-pass'    => $this->formNewPass($withBg),
            'lock-screen' => $this->formLockScreen($withBg),
            'login-pin'   => $this->formPin($withBg),
            'two-factor'  => $this->formTwoFactor($withBg),
            'delete-account'=> $this->formDeleteAccount($withBg),
            'success-mail'=> $this->formSuccessMail(),
            default       => $this->formSignIn($withBg),
        };

        return $this->cardShell($body, $type, $withBg);
    }

    /**
     * card Shell（protected实例方法）
     * 包裹卡片：标题 / 副标题 / 主体 + 开发者插槽（prepend/append）
     *
     * @param string $body 主体
     * @param string $type 类型
     * @param bool   $withBg 是否浅底输入框
     *
     * @return string result
     */
    protected function cardShell(string $body, string $type, bool $withBg): string
    {
        $heading    = (string) ($this->get('heading') ?: $this->defaultHeading($type));
        $subheading = (string) ($this->get('subheading') ?: $this->defaultSubheading($type));

        $html = '';
        if ($heading !== '') {
            $html .= '<h1 class="auth-heading h3 mb-1">' . $this->e($heading) . '</h1>';
        }
        if ($subheading !== '') {
            $html .= '<p class="auth-subheading text-muted mb-4">' . $this->e($subheading) . '</p>';
        }
        // prepend 插槽（raw，标题之后、表单之前）
        $html .= $this->raw($this->get('prepend'));

        $html .= $body;

        // append 插槽（raw，底部链接之前）
        $html .= $this->raw($this->get('append'));

        // 社交登录按钮（socialButtons 配置时渲染）
        $social = $this->socialButtonsHtml();
        if ($social !== '') {
            $html .= '<div class="mt-4 auth-social">' . $social . '</div>';
        }

        // 底部链接（sign-in 等类型默认生成，其余按配置）
        $below = $this->get('below');
        if ($below !== null && $below !== '') {
            $html .= '<div class="mt-3 text-center auth-below">' . $this->raw($below) . '</div>';
        } else {
            $links = $this->defaultLinksHtml($type);
            if ($links !== '') {
                $html .= '<div class="mt-3 text-center auth-below">' . $links . '</div>';
            }
        }

        return $html;
    }

    /**
     * default Heading（protected实例方法）
     * 各类型默认主标题
     *
     * @param string $type type
     *
     * @return string result
     */
    protected function defaultHeading(string $type): string
    {
        return match ($type) {
            'sign-in'     => '欢迎回来',
            'sign-up'     => '创建账号',
            'reset-pass'  => '找回密码',
            'new-pass'    => '设置新密码',
            'lock-screen' => '屏幕已锁定',
            'login-pin'   => 'PIN 登录',
            'two-factor'  => '两步验证',
            'delete-account'=> '注销账户',
            'success-mail'=> '邮件已发送',
            default       => '',
        };
    }

    /**
     * default Subheading（protected实例方法）
     * 各类型默认副标题
     *
     * @param string $type type
     *
     * @return string result
     */
    protected function defaultSubheading(string $type): string
    {
        return match ($type) {
            'sign-in'     => '请输入账号信息登录',
            'sign-up'     => '填写信息以注册新账号',
            'reset-pass'  => '输入邮箱以接收重置链接',
            'new-pass'    => '请输入新的登录密码',
            'lock-screen' => '请输入密码以继续',
            'login-pin'   => '请输入您的 PIN 码',
            'two-factor'  => '请输入您的身份验证代码',
            'delete-account'=> '此操作不可恢复，请输入密码确认',
            'success-mail'=> '请查收邮件以继续',
            default       => '',
        };
    }

    // ------------------------------------------------------------------
    // 表单构造（7 种核心类型 + 3 种兼容类型）
    // ------------------------------------------------------------------

    /**
     * form Sign In（protected实例方法）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formSignIn(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        // 后台登录通常使用账号(username)而非邮箱；username 字段优先，否则回退 email
        $account = $f['username'] ?? $f['email'] ?? ['label' => '账号', 'name' => 'username', 'type' => 'text', 'placeholder' => '请输入管理员账号', 'required' => true, 'autofocus' => true];
        $html = $this->formOpen()
            . $this->field($account, $withBg)
            . $this->field($f['password'] ?? ['label' => '密码', 'name' => 'password', 'type' => 'password', 'placeholder' => '请输入密码', 'required' => true], $withBg)
            . $this->captchaField()
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * form Sign Up（protected实例方法）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formSignUp(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        $html = $this->formOpen()
            . $this->field($f['name'] ?? ['label' => '姓名', 'name' => 'name', 'placeholder' => '请输入姓名', 'required' => true], $withBg)
            . $this->field($f['email'] ?? ['label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入邮箱', 'required' => true], $withBg)
            . $this->field($f['password'] ?? ['label' => '密码', 'name' => 'password', 'type' => 'password', 'placeholder' => '请设置密码', 'required' => true], $withBg)
            . $this->field($f['password_confirmation'] ?? ['label' => '确认密码', 'name' => 'password_confirmation', 'type' => 'password', 'placeholder' => '请再次输入密码', 'required' => true], $withBg)
            . $this->captchaField()
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * form Reset Pass（protected实例方法）
     * 忘记密码（提交邮箱）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formResetPass(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        $html = $this->formOpen()
            . $this->field($f['email'] ?? ['label' => '邮箱', 'name' => 'email', 'type' => 'email', 'placeholder' => '请输入注册邮箱', 'required' => true, 'autofocus' => true], $withBg)
            . $this->captchaField()
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * form New Pass（protected实例方法）
     * 设置新密码 / 重置密码（新密码 + 确认）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formNewPass(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        $msg = (string) $this->get('message');

        // 对齐 INSPINIA auth-*-new-pass.html：邮箱展示(disabled) + 新密码(强度条) + 确认 + 协议。
        // 注意：后台模板的 new-pass 页【不含】6 位验证码分格框（那是 login-pin/two-factor 才有的），
        // 故 newPassShowCode 默认 false；仅当调用方显式传 true 时才展示分格验证码。
        $showEmail = $this->get('newPassShowEmail', true);
        $showCode  = $this->get('newPassShowCode', false);
        $showAgree = $this->get('newPassShowAgree', true);

        $html = $this->formOpen()
            . ($msg !== '' ? '<div class="alert alert-info">' . $this->e($msg) . '</div>' : '');

        // 邮箱展示（disabled，来自 get('email') 或 user.email）
        if ($showEmail) {
            $email = (string) ($this->get('email') ?: (($this->get('user')['email'] ?? '') ?: ''));
            $html .= '<div class="mb-3">' . $this->label('邮箱')
                . '<input type="email" class="form-control py-2 px-3 bg-light bg-opacity-40 border-light" value="' . $this->e($email) . '" disabled></div>';
        }

        // 6 位验证码分格输入
        if ($showCode) {
            $html .= '<div class="mb-3">' . $this->label('输入 6 位验证码')
                . $this->pinCodeGroup(6)
                . '</div>';
        }

        // 新密码（带强度条）
        $html .= '<div class="mb-3" data-password="bar">'
            . $this->label('新密码')
            . $this->passwordInput($f['password'] ?? ['name' => 'password', 'placeholder' => '请设置新密码', 'required' => true, 'autofocus' => true], $withBg)
            . '<div class="password-bar my-2"></div>'
            . '<p class="text-muted fs-xs mb-0">使用 8 位以上，含字母、数字与符号</p>'
            . '</div>';

        // 确认新密码
        $html .= '<div class="mb-3">'
            . $this->label('确认新密码')
            . $this->passwordInput($f['password_confirmation'] ?? ['name' => 'password_confirmation', 'placeholder' => '请再次输入新密码', 'required' => true], $withBg)
            . '</div>';

        // 协议勾选
        if ($showAgree) {
            $html .= '<div class="mb-3"><div class="form-check">'
                . '<input class="form-check-input form-check-input-light fs-14" type="checkbox" id="auth-agree" name="agree">'
                . '<label class="form-check-label" for="auth-agree">我已阅读并同意《用户协议》与《隐私政策》</label>'
                . '</div></div>';
        }

        $html .= $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * label（protected实例方法）
     * 生成标准 form-label（含必填星号）
     *
     * @param string $text     标签文本
     * @param bool   $required 是否必填
     *
     * @return string result
     */
    protected function label(string $text, bool $required = true): string
    {
        return '<label class="form-label">' . $this->e($text)
            . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';
    }

    /**
     * pin Code Group（protected实例方法）
     * 渲染 N 位分格验证码输入（data-xf-pin-input，由 JS 串联）
     *
     * @param int $len 位数
     *
     * @return string result
     */
    protected function pinCodeGroup(int $len = 6, string $name = 'code[]', bool $withBg = false): string
    {
        $html = '<div class="d-flex gap-2 two-factor" data-xf-pin-input>';
        for ($i = 0; $i < $len; $i++) {
            $cls = 'form-control form-control-lg text-center fs-3';
            $cls .= ' bg-light bg-opacity-40 border-light';
            $html .= '<input type="password" class="' . $cls . '" inputmode="numeric" maxlength="1"'
                . ' name="' . $this->e($name) . '" data-xf-pin-cell autocomplete="one-time-code"' . ($i === 0 ? ' autofocus' : '') . '>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * password Input（protected实例方法）
     * 渲染密码输入框（card/split 带图标 input-group，base 普通）
     *
     * @param array $f     字段配置
     * @param bool  $withBg 浅底
     *
     * @return string result
     */
    protected function passwordInput(array $f, bool $withBg): string
    {
        $name  = (string) ($f['name'] ?? 'password');
        $ph    = (string) ($f['placeholder'] ?? '');
        $req   = ! empty($f['required']);
        $focus = ! empty($f['autofocus']);
        $cls   = 'form-control py-2 px-3';
        $cls   .= ' bg-light bg-opacity-40 border-light';
        if ($withBg) {
            return '<div class="input-group">'
                . '<span class="input-group-text bg-transparent border-light">' . $this->icon('ti ti-lock') . '</span>'
                . '<input type="password" class="' . $cls . '" name="' . $this->e($name) . '" placeholder="' . $this->e($ph) . '"'
                . ($req ? ' required' : '') . ($focus ? ' autofocus' : '') . '></div>';
        }
        return '<input type="password" class="' . $cls . '" name="' . $this->e($name) . '" placeholder="' . $this->e($ph) . '"'
            . ($req ? ' required' : '') . ($focus ? ' autofocus' : '') . '>';
    }

    /**
     * form Two Factor（protected实例方法）
     * 两步验证（OTP）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formTwoFactor(bool $withBg): string
    {
        $html = $this->formOpen()
            . '<div class="mb-3">'
            . $this->label('输入 6 位验证码')
            . $this->pinCodeGroup(6)
            . '</div>'
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * form Lock Screen（protected实例方法）
     * 锁屏解锁（用户头像 + 密码）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formLockScreen(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        $user = (array) $this->get('user', []);
        if (($user['name'] ?? '') === '' && $this->get('name') !== '') {
            $user['name'] = $this->get('name');
        }
        if (($user['avatar'] ?? '') === '' && $this->get('avatar') !== '') {
            $user['avatar'] = $this->get('avatar');
        }
        if (($user['email'] ?? '') === '' && $this->get('email') !== '') {
            $user['email'] = $this->get('email');
        }
        $html = $this->formOpen()
            . $this->lockUserBadge($user)
            . $this->field($f['password'] ?? ['label' => '密码', 'name' => 'password', 'type' => 'password', 'placeholder' => '请输入密码解锁', 'required' => true, 'autofocus' => true], $withBg)
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * lock User Badge（protected实例方法）
     * 锁屏页用户头像 + 名称徽标
     *
     * @param array $user 用户信息
     *
     * @return string result
     */
    protected function lockUserBadge(array $user): string
    {
        if (($user['name'] ?? '') === '' && ($user['avatar'] ?? '') === '' && ($user['email'] ?? '') === '') {
            return '';
        }
        $avatar = $user['avatar'] ?? null;
        $img    = $avatar ? $this->img($avatar) : null;
        $mark   = $img
            ? '<img src="' . $this->e($img) . '" alt="' . $this->e($user['name'] ?? '') . '" class="auth-lock-avatar rounded-circle">'
            : '<div class="auth-lock-avatar auth-lock-avatar--text rounded-circle">' . $this->e(mb_substr((string) ($user['name'] ?? '?'), 0, 1)) . '</div>';

        return '<div class="auth-lock-user d-flex align-items-center gap-3 mb-4">'
            . $mark
            . '<div class="auth-lock-meta">'
            . ($user['name'] !== '' ? '<div class="auth-lock-name fw-semibold">' . $this->e($user['name']) . '</div>' : '')
            . ($user['email'] !== '' ? '<div class="auth-lock-email small text-muted">' . $this->e($user['email']) . '</div>' : '')
            . '</div></div>';
    }

    /**
     * form Delete Account（protected实例方法）
     * 注销账户确认（密码）
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formDeleteAccount(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        $html = $this->formOpen()
            . $this->field($f['password'] ?? ['label' => '密码', 'name' => 'password', 'type' => 'password', 'placeholder' => '请输入密码确认', 'required' => true, 'autofocus' => true], $withBg)
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    /**
     * form Success Mail（protected实例方法）
     * 邮件发送成功提示（无表单）
     *
     * @return string result
     */
    protected function formSuccessMail(): string
    {
        $html = '<div class="auth-success-icon text-center mb-3"><i class="ti ti-mail fs-1 text-success"></i></div>'
            . $this->statusAlert(true)
            . $this->defaultLinksHtml('success-mail');

        return $html;
    }

    /**
     * form Pin（protected实例方法）
     * PIN 码登录
     *
     * @param bool $withBg 浅底输入框
     *
     * @return string result
     */
    protected function formPin(bool $withBg): string
    {
        $f = (array) $this->get('fields', []);
        // 对齐 auth-split-login-pin.html：6 个分格密码框（form-control form-control-lg text-center fs-3）
        $pinGroup = $f['pin']['group'] ?? $this->get('pinGroup', 6);
        $html = $this->formOpen()
            . $this->pinCodeGroup($pinGroup, 'pin[]', $withBg)
            . $this->buttonsRow()
            . $this->formClose();

        return $html;
    }

    // ------------------------------------------------------------------
    // 表单片段
    // ------------------------------------------------------------------

    /**
     * form Open（protected实例方法）
     * 生成 <form> 开标签（含 CSRF + 错误/状态提示 + beforeForm 插槽）
     *
     * @return string result
     */
    protected function formOpen(): string
    {
        return $this->raw($this->get('beforeForm'))
            . '<form' . $this->formAttrs() . '>'
            . $this->csrfField()
            . $this->formErrorsAlert()
            . $this->formStatusAlert();
    }

    /**
     * form Close（protected实例方法）
     * </form> + afterForm 插槽
     *
     * @return string result
     */
    protected function formClose(): string
    {
        return '</form>' . $this->raw($this->get('afterForm'));
    }

    /**
     * form Attrs（protected实例方法）
     * 生成 <form> 标签属性（兼容 'action' 与历史 'formAttrs.action'）
     *
     * @return string result
     */
    protected function formAttrs(): string
    {
        $formAttrs = (array) $this->get('formAttrs', []);
        if (($this->get('action') === '') && isset($formAttrs['action'])) {
            $this->set('action', $formAttrs['action']);
        }
        if (($this->get('method') === 'POST') && isset($formAttrs['method'])) {
            $this->set('method', $formAttrs['method']);
        }

        $action = (string) $this->get('action');
        // text-start：表单区标签/输入框强制左对齐（表单外层容器是 text-center，
        // label 会继承居中；Bootstrap .text-start 带 !important，比 CSS 覆盖更可靠）
        $class  = 'auth-form text-start ' . $this->e($this->get('bodyClass'));
        $id     = $this->get('id') ? ' id="' . $this->e($this->get('id')) . '"' : '';

        return ' action="' . $this->e($action) . '"'
            . ' method="POST"'
            . ' class="' . trim($class) . '"'
            . $id
            . ' accept-charset="UTF-8"'
            . ' novalidate';
    }

    /**
     * buttons Row（protected实例方法）
     * 渲染表单按钮（'buttons' 自定义数组优先；否则用 'submit' 生成默认主按钮）
     *
     * @return string result
     */
    protected function buttonsRow(): string
    {
        $buttons = $this->get('buttons');
        if (is_array($buttons) && $buttons !== []) {
            $html = '';
            foreach ($buttons as $b) {
                $b = (array) $b;
                $html .= '<button type="' . ($b['type'] ?? 'submit') . '"'
                    . ' class="btn btn-' . ($b['variant'] ?? 'primary') . ' ' . $this->e($b['class'] ?? '') . '"'
                    . (($b['name'] ?? '') !== '' ? ' name="' . $this->e($b['name']) . '"' : '')
                    . '>' . $this->e($b['label'] ?? '提交') . '</button>';
            }
            return '<div class="auth-buttons d-grid gap-2 mt-3">' . $html . '</div>';
        }

        $submit = $this->get('submit');
        if (is_array($submit)) {
            // 兼容 label / text 两种键名，并支持 icon 前缀图标
            $submitLabel = $submit['text'] ?? ($submit['label'] ?? '提交');
            $submitClass = $submit['class'] ?? '';
            $submitVariant = $submit['variant'] ?? 'primary';
            $submitIcon = (string) ($submit['icon'] ?? '');
        } else {
            $submitLabel = $submit ?: '提交';
            $submitClass = '';
            $submitVariant = 'primary';
            $submitIcon = '';
        }

        $iconHtml = $submitIcon !== '' ? $this->icon($submitIcon) . ' ' : '';

        // d-grid + w-100：提交按钮撑满整行（对齐后台模板 auth-*.html 的
        // <div class="d-grid"><button class="btn btn-primary fw-bold py-2"> 结构）
        return '<div class="auth-buttons d-grid gap-2 mt-3">'
            . '<button type="submit" class="btn btn-' . $this->enum($submitVariant, self::ENUM_VARIANT, 'primary') . ' fw-bold py-2 w-100 '
            . $this->e($submitClass) . '">'
            . $iconHtml . $this->e($submitLabel)
            . '</button></div>';
    }

    /**
     * social Buttons Html（protected实例方法）
     * 渲染社交登录按钮（socialButtons: [['icon','url','label']]）
     *
     * @return string result
     */
    protected function socialButtonsHtml(): string
    {
        $items = $this->get('socialButtons');
        if (! is_array($items) || $items === []) {
            return '';
        }
        $html = '<div class="auth-social-divider text-center text-muted my-3"><span>或使用社交账号</span></div>';
        $html .= '<div class="d-flex gap-2 justify-content-center">';
        foreach ($items as $item) {
            $item = (array) $item;
            $icon = $item['icon'] ?? 'ti ti-world';
            $url  = $item['url'] ?? '#';
            $label= $item['label'] ?? '';
            $html .= '<a href="' . $this->e($url) . '" class="btn btn-outline-secondary" title="' . $this->e($label) . '">'
                . $this->icon($icon) . '</a>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * field（protected实例方法）
     * 渲染单个输入字段（含标签 / 错误 / 旧值回填 / 帮助文本 / 图标 / 前后插槽）
     *
     * @param array $f     字段配置
     * @param bool  $withBg 浅底输入框（card/split）
     *
     * @return string result
     */
    protected function field(array $f, bool $withBg = false): string
    {
        $name     = (string) ($f['name'] ?? '');
        $type     = (string) ($f['type'] ?? 'text');
        $label    = (string) ($f['label'] ?? '');
        $ph       = (string) ($f['placeholder'] ?? '');
        $required = ! empty($f['required']);
        $autofocus= ! empty($f['autofocus']);
        $disabled = ! empty($f['disabled']);

        $old   = $this->oldInput($name);
        $value = ($old !== null && $old !== '') ? $old : ($f['value'] ?? '');
        $value = (string) $value;

        $errMsg = $this->fieldError($name);
        $invalid = $errMsg !== '' ? ' is-invalid' : '';

        $id = ($f['id'] ?? '') ?: ('auth-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $name));

        // 字段前后插槽（raw）
        $before = $this->raw($f['before'] ?? '');
        $after  = $this->raw($f['after'] ?? '');

        $html = '<div class="mb-3 auth-field">';
        if ($label !== '') {
            $html .= '<label for="' . $this->e($id) . '" class="form-label">'
                . $this->e($label)
                . ($required ? ' <span class="text-danger">*</span>' : '')
                . '</label>';
        }

        // 输入框样式：统一浅底背景 + 普通高度（对齐 INSPINIA auth-split-*：form-control py-2 px-3 bg-light bg-opacity-40 border-light）
        $inpCls = 'form-control py-2 px-3' . $invalid;
        $inpCls .= ' bg-light bg-opacity-40 border-light';

        // 图标（card/split 用 input-group 包裹）
        $icon = self::FIELD_ICONS[$name] ?? ($f['icon'] ?? '');
        if ($withBg && $icon !== '') {
            $html .= '<div class="input-group">'
                . '<span class="input-group-text bg-transparent border-light">' . $this->icon($icon) . '</span>'
                . '<input type="' . $this->e($type) . '"'
                . ' class="' . $inpCls . '"'
                . ' id="' . $this->e($id) . '"'
                . ' name="' . $this->e($name) . '"'
                . ' placeholder="' . $this->e($ph) . '"'
                . ' value="' . $this->e($value) . '"'
                . ($required ? ' required' : '')
                . ($autofocus ? ' autofocus' : '')
                . ($disabled ? ' disabled' : '')
                . '></div>';
        } else {
            $html .= '<input type="' . $this->e($type) . '"'
                . ' class="' . $inpCls . '"'
                . ' id="' . $this->e($id) . '"'
                . ' name="' . $this->e($name) . '"'
                . ' placeholder="' . $this->e($ph) . '"'
                . ' value="' . $this->e($value) . '"'
                . ($required ? ' required' : '')
                . ($autofocus ? ' autofocus' : '')
                . ($disabled ? ' disabled' : '')
                . '>';
        }

        if ($errMsg !== '') {
            $html .= '<div class="invalid-feedback d-block">' . $this->e($errMsg) . '</div>';
        } elseif (! empty($f['help'])) {
            $html .= '<div class="form-text">' . $this->e($f['help']) . '</div>';
        }

        $html .= $after . '</div>';

        return $before . $html;
    }

    /**
     * icon（protected实例方法）
     * 渲染图标（自动补全 ti 前缀）
     *
     * @param string $icon 图标类
     *
     * @return string result
     */
    protected function icon(string $icon): string
    {
        if ($icon === '' || str_starts_with($icon, '<')) {
            return $icon;
        }
        return '<i class="' . $this->e($icon) . '"></i>';
    }

    /**
     * captcha Field（protected实例方法）
     * 渲染验证码。'captcha' 为 bool 时输出占位；为字符串时原样输出（如 web component HTML）
     *
     * @return string result
     */
    protected function captchaField(): string
    {
        $captcha = $this->get('captcha');
        if ($captcha === false || $captcha === null || $captcha === '') {
            return '';
        }
        if (is_string($captcha)) {
            return '<div class="mb-3 auth-field">' . $captcha . '</div>';
        }
        return '<div class="mb-3 auth-field">'
            . '<div class="xf-captcha" data-xf="captcha"></div>'
            . '<input type="hidden" name="xf_captcha_token" value="">'
            . '</div>';
    }

    /**
     * default Links Html（protected实例方法）
     * 按 type 生成默认导航链接 + 兼容历史 'links' 配置（[['text','href']]）
     *
     * @param string $type 语义类型
     *
     * @return string result
     */
    protected function defaultLinksHtml(string $type): string
    {
        $links = $this->get('links');
        if (is_array($links) && $links !== []) {
            $html = '';
            foreach ($links as $item) {
                $item = (array) $item;
                $href = (string) ($item['href'] ?? '');
                $text = (string) ($item['text'] ?? '');
                if ($text === '' || $href === '') {
                    continue;
                }
                $html .= '<a href="' . $this->e($href) . '" class="auth-link">' . $this->e($text) . '</a>';
            }
            return $html;
        }

        $map    = self::DEFAULT_LINKS[$type] ?? [];
        $html   = '';
        $labels = [
            'loginRedirect'    => '已有账号？去登录',
            'registerRedirect' => '还没有账号？去注册',
        ];
        foreach ($map as $key) {
            $url = (string) $this->get($key);
            if ($url === '') {
                continue;
            }
            $html .= '<a href="' . $this->e($url) . '" class="auth-link">' . $this->e($labels[$key] ?? '') . '</a>';
        }
        return $html;
    }

    // ------------------------------------------------------------------
    // 错误 / 状态提示 / 旧值回填
    // ------------------------------------------------------------------

    /**
     * form Errors Alert（protected实例方法）
     * 顶部 alert 显示【非字段级】错误（字段级在 field() 内联显示）
     *
     * @return string result
     */
    protected function formErrorsAlert(): string
    {
        $errors = $this->sessionErrors();
        if ($errors === null) {
            return '';
        }
        $fieldNames = $this->errorFieldNames();
        $messages = [];
        foreach ($errors->all() as $msg) {
            if ($this->isFieldError($fieldNames, $errors)) {
                continue;
            }
            $messages[] = $msg;
        }
        if ($messages === []) {
            return '';
        }
        return '<div class="alert alert-danger">' . $this->e(implode(' ', $messages)) . '</div>';
    }

    /**
     * form Status Alert（protected实例方法）
     * 顶部绿色 alert 显示 session('status')（如邮件发送成功提示）
     *
     * @param bool $force 是否强制渲染（success-mail 页用）
     *
     * @return string result
     */
    protected function formStatusAlert(bool $force = false): string
    {
        $status = (string) $this->get('status');
        if ($status === '' && ! $force) {
            if (function_exists('session')) {
                $status = (string) session('status', '');
            }
        }
        if ($status === '') {
            return '';
        }
        return '<div class="alert alert-success">' . $this->e($status) . '</div>';
    }

    /**
     * status Alert（protected实例方法，别名）
     *
     * @param bool $force force
     *
     * @return string result
     */
    protected function statusAlert(bool $force = false): string
    {
        return $this->formStatusAlert($force);
    }

    /**
     * session Errors（protected实例方法）
     *
     * @return mixed result
     */
    protected function sessionErrors(): mixed
    {
        if (! function_exists('session')) {
            return null;
        }
        $errors = session('errors');
        if ($errors !== null && is_callable([$errors, 'all'])) {
            return $errors;
        }
        return null;
    }

    /**
     * error Field Names（protected实例方法）
     *
     * @return array result
     */
    protected function errorFieldNames(): array
    {
        $fields = $this->get('fields');
        if (! is_array($fields)) {
            return [];
        }
        $names = [];
        foreach ($fields as $f) {
            if (is_array($f) && isset($f['name'])) {
                $names[] = (string) $f['name'];
            }
        }
        return $names;
    }

    /**
     * is Field Error（protected实例方法）
     *
     * @param array  $fieldNames 字段名列表
     * @param mixed  $errors     ViewErrorBag
     *
     * @return bool result
     */
    protected function isFieldError(array $fieldNames, mixed $errors): bool
    {
        foreach ($fieldNames as $name) {
            if (is_callable([$errors, 'get']) && $errors->get($name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * field Error（protected实例方法）
     * 取单个字段的首条错误信息（Laravel ViewErrorBag 的 has()/first() 是 __call 魔术方法，
     * 必须用 is_callable 判断）。
     *
     * @param string $name 字段名
     *
     * @return string result
     */
    protected function fieldError(string $name): string
    {
        $errors = $this->sessionErrors();
        if ($errors === null) {
            return '';
        }
        if (! is_callable([$errors, 'has']) || ! $errors->has($name)) {
            return '';
        }
        if (! is_callable([$errors, 'first'])) {
            return '';
        }
        return (string) $errors->first($name);
    }

    /**
     * old Input（protected实例方法）
     * 回填旧输入值
     *
     * @param string $name    字段名
     * @param mixed  $default 默认值
     *
     * @return mixed result
     */
    protected function oldInput(string $name, mixed $default = null): mixed
    {
        if (! function_exists('old')) {
            return $default;
        }
        return old($name, $default);
    }

    // ------------------------------------------------------------------
    // CSRF
    // ------------------------------------------------------------------

    /**
     * csrf Field（protected实例方法）
     * 生成 CSRF 隐藏域（兼容 Laravel csrf_field()；非 Laravel 环境降级为空）
     *
     * @return string result
     */
    protected function csrfField(): string
    {
        if (function_exists('csrf_field')) {
            return (string) csrf_field();
        }
        if (function_exists('csrf_token')) {
            return '<input type="hidden" name="_token" value="' . $this->e(csrf_token()) . '">';
        }
        return '';
    }

    /**
     * csrf Token（protected实例方法）
     *
     * @return string result
     */
    protected function csrfToken(): string
    {
        if (function_exists('csrf_token')) {
            return (string) csrf_token();
        }
        return '';
    }
}

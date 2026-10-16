<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 页脚
 *
 * 支持多区块布局与备案信息展示：
 *
 *   XfAdmin::footer([
 *       'text' => '© 2026 XX公司',          // 版权文案（纯文本，自动转义）
 *       'right' => '<a href="#">帮助</a>',  // 右侧自定义 HTML（调用方保证可信）
 *       'icp'  => '粤ICP备2026000000号',     // ICP 备案号（自动跳转 beian.miit.gov.cn）
 *       'icp_url' => 'https://beian.miit.gov.cn/', // 自定义 ICP 链接
 *       'police' => '粤公网安备 44030000000000号', // 网安备案号（可选 police_icon）
 *       'police_url' => 'https://www.beian.gov.cn/', // 网安备案链接
 *       'links' => [                        // 底部导航链接组
 *           ['label' => '关于我们', 'url' => '/about'],
 *           ['label' => '联系我们', 'url' => '/contact'],
 *       ],
 *       'support' => '技术支持：XX团队',       // 技术支持文案
 *       'groups' => [                       // 多列链接组（适合企业官网）
 *           ['title' => '产品', 'items' => [['label'=>'A','url'=>'/a'], ...]],
 *       ],
 *   ])
 *
 * 所有链接均经 safeUrl() 校验，拦截 javascript: 等伪协议。
 */
class Footer extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'text' => null,
            'right' => null,
            'container' => 'fluid',   // 外层容器：fluid=container-fluid / boxed=container / false=不包裹
            'align' => 'start',       // start | center | end（版权与右侧内容的对齐）
            'icp' => null,            // ICP 备案号
            'icp_url' => null,        // ICP 链接（null 时自动使用默认）
            'police' => null,         // 网安备案号
            'police_url' => null,     // 网安链接（null 时自动使用默认）
            'police_icon' => null,    // 网安备案图标（默认包内盾牌图标）
            'links' => [],            // 底部导航链接组 [{label,url}]
            'support' => null,        // 技术支持文案
            'brand_url' => null,      // 版权品牌跳转链接
            'groups' => [],           // 多列链接组 [{title, items:[{label,url}]}]
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $text = $this->get('text') ?? XfAdmin::setting('footer.text');
        $right = $this->get('right') ?? XfAdmin::setting('footer.right');
        $icp = $this->get('icp') ?? XfAdmin::setting('footer.icp');
        $police = $this->get('police') ?? XfAdmin::setting('footer.police');
        $links = $this->get('links') ?: XfAdmin::setting('footer.links', []);
        $support = $this->get('support') ?? XfAdmin::setting('footer.support');
        $groups = $this->get('groups') ?: XfAdmin::setting('footer.groups', []);
        $brandUrl = $this->get('brand_url') ?? XfAdmin::setting('footer.brand_url');

        if ($text === null) {
            $brand = XfAdmin::setting('brand.name', 'XfAdmin');
            $text = '© '.date('Y').' '.$brand;
        }

        // 容器与对齐（对齐白名单，非法值回落默认）
        $containerCls = $this->enum($this->get('container'), ['fluid' => 'container-fluid', 'boxed' => 'container', '' => ''], 'container-fluid');
        $align = $this->enum($this->get('align'), ['start' => 'start', 'center' => 'center', 'end' => 'end'], 'start');
        $leftAlign = $align === 'center' ? 'text-center' : 'text-center text-md-'.$align;
        $rightAlign = $align === 'center' ? 'text-md-center' : 'text-md-'.$align;

        $html = '<footer'.$this->attrs(['class' => 'footer']).'>'.($containerCls ? '<div class="'.$containerCls.'">' : '');

        // 可选：多列链接组（groups）
        if ($groups !== []) {
            $html .= '<div class="row footer-groups py-4">';
            foreach ($groups as $group) {
                $html .= '<div class="col-6 col-md-3">';
                $html .= '<div class="fw-semibold mb-2">'.$this->text($group['title'] ?? '').'</div>';
                $html .= '<ul class="list-unstyled mb-0">';
                foreach ((array) ($group['items'] ?? []) as $item) {
                    $html .= '<li class="py-1"><a href="'.$this->safeUrl($item['url'] ?? '#').'">'.$this->text($item['label'] ?? '').'</a></li>';
                }
                $html .= '</ul></div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="row align-items-center py-3">';

        // 左：版权 + 备案信息
        $html .= '<div class="col-md-6 '.$leftAlign.'">';
        $copyright = $this->text($text);
        if ($brandUrl !== null) {
            $copyright = '<a href="'.$this->safeUrl($brandUrl).'">'.$copyright.'</a>';
        }
        $html .= $copyright;

        // ICP 备案号
        if ($icp !== null && $icp !== '') {
            $icpUrl = $this->get('icp_url') ?? XfAdmin::setting('footer.icp_url', 'https://beian.miit.gov.cn/');
            $html .= ' &nbsp;<a href="'.$this->safeUrl($icpUrl).'" rel="noopener" target="_blank">'.$this->text($icp).'</a>';
        }

        // 网安备案号
        if ($police !== null && $police !== '') {
            $policeUrl = $this->get('police_url') ?? XfAdmin::setting('footer.police_url', 'https://www.beian.gov.cn/');
            $policeIcon = $this->get('police_icon') ?? XfAdmin::setting('footer.police_icon', 'police.png');
            $iconHtml = '<img src="'.$this->img($policeIcon).'" alt="" class="police-icon me-1" style="height:16px;vertical-align:-2px;">';
            $html .= ' &nbsp;<a href="'.$this->safeUrl($policeUrl).'" rel="noopener" target="_blank">'.$iconHtml.$this->text($police).'</a>';
        }

        // 技术支持文案
        if ($support !== null && $support !== '') {
            $html .= ' &nbsp;<span class="text-muted">'.$this->text($support).'</span>';
        }
        $html .= '</div>';

        // 右：链接组 + 自定义内容
        $html .= '<div class="col-md-6 '.$rightAlign.'">';
        if ($links !== []) {
            $html .= '<nav class="footer-links d-none d-md-inline-block">';
            foreach ($links as $i => $link) {
                if ($i > 0) {
                    $html .= '<span class="mx-2 text-muted">·</span>';
                }
                $html .= '<a href="'.$this->safeUrl($link['url'] ?? '#').'">'.$this->text($link['label'] ?? '').'</a>';
            }
            $html .= '</nav>';
        }
        if ($right !== null) {
            $html .= '<div class="'.$rightAlign.' d-none d-md-block">'.$this->raw($right).'</div>';
        }
        $html .= '</div>';

        $html .= '</div>'.($containerCls ? '</div>' : '').'</footer>';

        return $html;
    }
}

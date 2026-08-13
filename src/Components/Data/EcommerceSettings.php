<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 电商设置（apps-ecommerce-settings.html）
 *
 * XfAdmin::ecommerceSettings([
 *     'store' => [
 *         'name' => 'My Store',
 *         'email' => 'store@example.com',
 *         'phone' => '+86 138-xxxx-xxxx',
 *         'currency' => 'CNY',
 *         'timezone' => 'Asia/Shanghai',
 *     ],
 *     'sections' => [
 *         ['title' => '支付方式', 'content' => '<button class="btn btn-outline-primary">配置支付</button>'],
 *         ['title' => '配送设置', 'content' => '<div class="form-check"><input class="form-check-input" type="checkbox" checked><label>免运费（满 ¥99）</label></div>'],
 *     ],
 * ])
 */
class EcommerceSettings extends Component
{
    protected function defaults(): array
    {
        return [
            'store' => [],
            'sections' => [],
        ];
    }

    protected function html(): string
    {
        $store = (array) $this->get('store', []);
        $sections = (array) $this->get('sections', []);

        $html = '<div class="row g-4">';

        // 左侧导航
        $html .= '<div class="col-lg-3"><div class="card"><div class="list-group list-group-flush">';
        $navItems = ['店铺信息', '支付方式', '配送设置', '税费规则', '通知设置', '高级选项'];
        foreach ($navItems as $i => $item) {
            $active = $i === 0 ? ' active' : '';
            $html .= '<a href="javascript:void(0)" class="list-group-item list-group-item-action' . $active . '">' . $this->e($item) . '</a>';
        }
        $html .= '</div></div></div>';

        // 右侧内容
        $html .= '<div class="col-lg-9"><div class="card"><div class="card-body">';

        // 店铺信息表单
        $html .= '<h5 class="mb-3">店铺信息</h5><div class="row g-3">';
        $html .= '<div class="col-md-6"><label class="form-label">店铺名称</label><input type="text" class="form-control" value="' . $this->e($store['name'] ?? '') . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">邮箱</label><input type="email" class="form-control" value="' . $this->e($store['email'] ?? '') . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">电话</label><input type="text" class="form-control" value="' . $this->e($store['phone'] ?? '') . '"></div>';
        $html .= '<div class="col-md-6"><label class="form-label">货币</label><select class="form-select"><option>' . $this->e($store['currency'] ?? 'CNY') . '</option><option>USD</option><option>EUR</option></select></div>';
        $html .= '<div class="col-md-6"><label class="form-label">时区</label><select class="form-select"><option>' . $this->e($store['timezone'] ?? 'Asia/Shanghai') . '</option></select></div>';
        $html .= '</div>';

        // 自定义章节
        foreach ($sections as $i => $section) {
            $section = (array) $section;
            $html .= '<hr class="my-4"><h5 class="mb-3">' . $this->e($section['title'] ?? '') . '</h5>';
            $html .= $this->raw($section['content'] ?? '');
        }

        $html .= '<hr class="my-4"><button class="btn btn-primary">保存设置</button>';

        $html .= '</div></div></div></div>';

        return $html;
    }
}

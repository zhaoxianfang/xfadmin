<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 新增 / 编辑商品表单页（对齐 INSPINIA ecommerce-add-product.html）
 *
 * 左侧主表单：基本信息（名称 / SKU / 库存 / 描述）、商品图上传占位、
 * 价格（原价 / 折扣类型 / 折扣值）、分类（品牌 / 分类 / 子分类）；
 * 右侧发布栏：状态 / 可见性 / 标签 / 操作按钮。
 *
 * XfAdmin::productAdd([
 *     'action'     => '/admin/products',
 *     'brands'     => ['Apple', 'Samsung'],
 *     'categories' => ['手机数码', '家用电器'],
 *     'tags'       => ['新品', '热卖'],
 *     'values'     => ['name' => 'iPhone 17', 'sku' => 'IP17-001', 'stock' => 100],
 * ])
 */
class ProductAdd extends Component
{
    /** 默认配置 */
    protected function defaults(): array
    {
        return [
            'action'         => '#',
            'method'         => 'POST',
            'brands'         => [],
            'categories'     => [],
            'sub_categories' => [],
            'statuses'       => ['草稿', '上架', '下架'],
            'tags'           => [],
            'values'         => [],
            'submit_text'    => '保存商品',
        ];
    }

    /** 渲染商品表单页 */
    protected function html(): string
    {
        $v = (array) $this->get('values', []);

        $html = '<form' . $this->attrs([
            'class'  => 'xf-product-add',
            'action' => (string) $this->get('action', '#'),
            'method' => (string) $this->get('method', 'POST'),
        ]) . '><div class="row g-3">';

        // ============ 左侧主表单 ============
        $html .= '<div class="col-xl-9">';

        // 基本信息
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">基本信息</h5></div><div class="card-body">';
        $html .= '<div class="row g-3">';
        $html .= '<div class="col-md-6"><label class="form-label" for="xfProductName">商品名称 <span class="text-danger">*</span></label>'
            . '<input type="text" id="xfProductName" name="name" class="form-control" placeholder="请输入商品名称" value="' . $this->e($v['name'] ?? '') . '"></div>';
        $html .= '<div class="col-md-3"><label class="form-label" for="xfProductSku">SKU</label>'
            . '<input type="text" id="xfProductSku" name="sku" class="form-control" placeholder="如 SKU-0001" value="' . $this->e($v['sku'] ?? '') . '"></div>';
        $html .= '<div class="col-md-3"><label class="form-label" for="xfProductStock">库存</label>'
            . '<input type="number" id="xfProductStock" name="stock" class="form-control" placeholder="0" value="' . $this->e((string) ($v['stock'] ?? '')) . '"></div>';
        $html .= '<div class="col-12"><label class="form-label" for="xfProductDesc">商品描述</label>'
            . '<textarea id="xfProductDesc" name="description" class="form-control" rows="4" placeholder="请输入商品描述…">' . $this->e($v['description'] ?? '') . '</textarea></div>';
        $html .= '</div></div></div>';

        // 商品图片（上传占位区）
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">商品图片</h5></div><div class="card-body">'
            . '<div class="border border-dashed rounded-3 text-center p-4 bg-light bg-opacity-25">'
            . '<i class="ti ti-cloud-upload fs-1 text-muted"></i>'
            . '<p class="text-muted mb-1 mt-2">拖拽图片到此处，或点击选择文件</p>'
            . '<small class="text-muted">支持 JPG / PNG，单张不超过 5MB</small>'
            . '<input type="file" name="images[]" class="d-none" multiple accept="image/*">'
            . '</div></div></div>';

        // 价格
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">价格设置</h5></div><div class="card-body"><div class="row g-3">';
        $html .= '<div class="col-md-4"><label class="form-label" for="xfProductPrice">原价 <span class="text-danger">*</span></label>'
            . '<div class="input-group"><span class="input-group-text">¥</span>'
            . '<input type="number" id="xfProductPrice" name="price" class="form-control" step="0.01" placeholder="0.00" value="' . $this->e((string) ($v['price'] ?? '')) . '"></div></div>';
        $html .= '<div class="col-md-4"><label class="form-label" for="xfProductDiscountType">折扣类型</label>'
            . '<select id="xfProductDiscountType" name="discount_type" class="form-select">'
            . '<option value="">无折扣</option><option value="percent">百分比</option><option value="fixed">固定金额</option></select></div>';
        $html .= '<div class="col-md-4"><label class="form-label" for="xfProductDiscount">折扣值</label>'
            . '<input type="number" id="xfProductDiscount" name="discount_value" class="form-control" step="0.01" placeholder="0" value="' . $this->e((string) ($v['discount_value'] ?? '')) . '"></div>';
        $html .= '</div></div></div>';

        // 分类归属
        $html .= '<div class="card mb-0"><div class="card-header"><h5 class="card-title mb-0">分类归属</h5></div><div class="card-body"><div class="row g-3">';
        $html .= '<div class="col-md-4">' . $this->selectField('品牌', 'brand', (array) $this->get('brands', []), (string) ($v['brand'] ?? '')) . '</div>';
        $html .= '<div class="col-md-4">' . $this->selectField('分类', 'category', (array) $this->get('categories', []), (string) ($v['category'] ?? '')) . '</div>';
        $html .= '<div class="col-md-4">' . $this->selectField('子分类', 'sub_category', (array) $this->get('sub_categories', []), (string) ($v['sub_category'] ?? '')) . '</div>';
        $html .= '</div></div></div>';

        $html .= '</div>';

        // ============ 右侧发布栏 ============
        $html .= '<div class="col-xl-3">';
        $html .= '<div class="card"><div class="card-header"><h5 class="card-title mb-0">发布</h5></div><div class="card-body">';
        $html .= $this->selectField('状态', 'status', (array) $this->get('statuses', []), (string) ($v['status'] ?? ''));
        $html .= '<div class="mt-3"><label class="form-label">可见性</label>'
            . '<div class="form-check"><input class="form-check-input" type="radio" name="visibility" id="xfVisPublic" value="public" checked><label class="form-check-label" for="xfVisPublic">公开</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="radio" name="visibility" id="xfVisHidden" value="hidden"><label class="form-check-label" for="xfVisHidden">隐藏</label></div></div>';

        $tags = (array) $this->get('tags', []);
        if (! empty($tags)) {
            $html .= '<div class="mt-3"><label class="form-label">标签</label><div class="d-flex flex-wrap gap-1">';
            foreach ($tags as $t) {
                $html .= '<span class="badge bg-light text-dark border">' . $this->e($t) . '</span>';
            }
            $html .= '</div></div>';
        }

        $html .= '<div class="d-grid gap-2 mt-4">'
            . '<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>' . $this->e($this->get('submit_text')) . '</button>'
            . '<button type="button" class="btn btn-soft-secondary">存为草稿</button>'
            . '</div>';
        $html .= '</div></div>';
        $html .= '</div>';

        return $html . '</div></form>';
    }

    /** 生成带 label 的下拉选择字段 */
    protected function selectField(string $label, string $name, array $options, string $current = ''): string
    {
        $id   = $this->uid('xfsel');
        $html = '<div><label class="form-label" for="' . $id . '">' . $this->e($label) . '</label>'
            . '<select id="' . $id . '" name="' . $this->e($name) . '" class="form-select">'
            . '<option value="">请选择</option>';
        foreach ($options as $opt) {
            $sel   = ((string) $opt === $current) ? ' selected' : '';
            $html .= '<option value="' . $this->e((string) $opt) . '"' . $sel . '>' . $this->e((string) $opt) . '</option>';
        }

        return $html . '</select></div>';
    }
}

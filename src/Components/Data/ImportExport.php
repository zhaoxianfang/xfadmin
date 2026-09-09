<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;

/**
 * 批量导入/导出工具条（DataTable / 管理页通用）
 *
 * 对标后台高频「导出 CSV/Excel/JSON + 导入文件」操作区，自我包含（Bootstrap 下拉 + 模态框），
 * 不依赖额外前端插件。导出项为真链接（指向后端导出端点），导入为带文件域的模态表单
 * （action 指向后端导入端点，CSRF 由宿主通过 csrf 选项注入）。
 *
 * XfAdmin::importExport([
 *     'exports' => [
 *         ['label' => 'CSV',  'format' => 'csv',  'url' => '/admin/users/export?fmt=csv'],
 *         ['label' => 'Excel','format' => 'xlsx', 'url' => '/admin/users/export?fmt=xlsx'],
 *         ['label' => 'JSON', 'format' => 'json', 'url' => '/admin/users/export?fmt=json'],
 *     ],
 *     // 简写：export_url + formats 自动生成上述 exports
 *     // 'export_url' => '/admin/users/export?fmt=', 'formats' => ['csv','xlsx','json'],
 *     'import' => [
 *         'url'    => '/admin/users/import',
 *         'accept' => '.csv,.xlsx',
 *         'title'  => '导入用户',
 *     ],
 *     'csrf' => csrf_field(),   // 宿主注入隐藏域（如 Laravel csrf_field()）
 * ])
 */
class ImportExport extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'exports'      => [],   // [['label','format','url']]
            'export_url'  => '',     // 简写基址
            'formats'     => [],     // 简写格式列表（默认空；使用 export_url 简写时必须显式传 formats）
            'import'      => null,   // null | ['url','accept','title']
            'export_label' => '导出',
            'import_label' => '导入',
            'csrf'        => '',     // 宿主注入的隐藏域 HTML（如 csrf_field()）
            'class'       => '',
            'id'          => null,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('xf-importexport');

        $exports = $this->normalizeExports();

        $exportHtml = '';
        if ($exports !== []) {
            $items = '';
            foreach ($exports as $ex) {
                $items .= '<li><a class="dropdown-item" href="' . $this->e($this->safeUrl($ex['url']))
                    . '" data-format="' . $this->e($ex['format'] ?? '') . '">'
                    . $this->e($ex['label']) . '</a></li>';
            }
            $exportHtml = '<div class="btn-group">'
                . '<button type="button" class="btn btn-soft-primary dropdown-toggle" '
                . 'data-bs-toggle="dropdown" aria-expanded="false">'
                . '<i class="ti ti-download"></i> ' . $this->e($this->get('export_label')) . '</button>'
                . '<ul class="dropdown-menu">' . $items . '</ul></div>';
        }

        $importHtml = '';
        $import = $this->get('import');
        if (is_array($import) && ($import['url'] ?? '') !== '') {
            $modalId = $id . '-import';
            $accept  = $this->e($import['accept'] ?? '.csv,.xlsx');
            $title   = $this->e($import['title'] ?? '导入数据');
            $url     = $this->e($this->safeUrl($import['url']));
            $csrf    = $this->get('csrf');
            $importHtml = '<button type="button" class="btn btn-soft-success" '
                . 'data-bs-toggle="modal" data-bs-target="#' . $modalId . '">'
                . '<i class="ti ti-upload"></i> ' . $this->e($this->get('import_label')) . '</button>'
                . '<div class="modal fade" id="' . $modalId . '" tabindex="-1" aria-hidden="true">'
                . '<div class="modal-dialog"><form class="modal-content" action="' . $url
                . '" method="post" enctype="multipart/form-data">'
                . ($csrf !== '' ? $this->raw($csrf) : '')
                . '<div class="modal-header"><h5 class="modal-title">' . $title . '</h5>'
                . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button></div>'
                . '<div class="modal-body"><div class="mb-3">'
                . '<label class="form-label">选择文件</label>'
                . '<input type="file" class="form-control" name="file" accept="' . $accept . '" required>'
                . '<div class="form-text">支持格式：' . $accept . '</div>'
                . '</div></div>'
                . '<div class="modal-footer">'
                . '<button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>'
                . '<button type="submit" class="btn btn-success">开始导入</button>'
                . '</div></form></div></div>';
        }

        if ($exportHtml === '' && $importHtml === '') {
            return '';
        }

        return '<div' . $this->attrs([
            'id'    => $id,
            'class' => 'xf-importexport d-flex align-items-center gap-2 flex-wrap'
                . ($this->get('class') !== '' ? ' ' . $this->get('class') : ''),
        ]) . '>' . $exportHtml . $importHtml . '</div>';
    }

    /** 归一导出项：显式 exports 优先；否则由 export_url + formats 简写生成 */
    private function normalizeExports(): array
    {
        $exports = $this->get('exports');
        if (is_array($exports) && $exports !== []) {
            $out = [];
            foreach ($exports as $ex) {
                $out[] = is_array($ex)
                    ? $ex
                    : ['label' => (string) $ex, 'format' => (string) $ex, 'url' => ''];
            }
            return $out;
        }

        $base = $this->get('export_url');
        if (! is_string($base) || $base === '') {
            return [];
        }
        $formats = (array) $this->get('formats');
        $out = [];
        foreach ($formats as $f) {
            $f = (string) $f;
            $out[] = [
                'label'  => strtoupper($f),
                'format' => $f,
                'url'    => $base . $f,
            ];
        }
        return $out;
    }
}

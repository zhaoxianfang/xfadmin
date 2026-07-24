<?php

declare(strict_types=1);

namespace XfAdmin\Components\Data;

use XfAdmin\Components\Component;

/**
 * 文件管理器网格（file-manager.html）
 *
 * XfAdmin::fileManager([
 *     'files' => [
 *         ['name' => '文档.pdf', 'type' => 'pdf', 'size' => '2.4 MB', 'meta' => '3 天前', 'href' => '#'],
 *         ['name' => '图片', 'type' => 'folder', 'meta' => '32 个文件'],
 *     ],
 *     'cols' => ['md' => 3, 'sm' => 6],
 * ])
 */
class FileManager extends Component
{
    private const ICONS = [
        'folder' => ['ti ti-folder-filled', 'warning'],
        'pdf'    => ['ti ti-file-type-pdf', 'danger'],
        'doc'    => ['ti ti-file-type-doc', 'primary'],
        'xls'    => ['ti ti-file-type-xls', 'success'],
        'zip'    => ['ti ti-file-zip', 'secondary'],
        'img'    => ['ti ti-photo', 'info'],
        'video'  => ['ti ti-video', 'purple'],
        'file'   => ['ti ti-file', 'secondary'],
    ];

    protected function defaults(): array
    {
        return [
            'files' => [],
            'cols'  => ['md' => 3, 'sm' => 6],
        ];
    }

    protected function html(): string
    {
        $cols = (array) $this->get('cols');
        $colCls = 'col';
        foreach ($cols as $bp => $n) {
            $colCls .= ' col-' . $bp . '-' . (int) $n;
        }

        $html = '<div' . $this->attrs(['class' => 'row g-3']) . '>';
        foreach ((array) $this->get('files', []) as $f) {
            [$icon, $variant] = self::ICONS[$f['type'] ?? 'file'] ?? self::ICONS['file'];
            $html .= '<div class="' . $colCls . '">';
            $html .= '<div class="card mb-0 h-100"><div class="card-body">';
            $html .= '<div class="d-flex align-items-center gap-2">';
            $html .= '<i class="' . $icon . ' fs-1 text-' . $variant . '"></i>';
            $html .= '<div class="flex-grow-1 overflow-hidden">';
            $html .= '<h5 class="mb-0 text-truncate"><a href="' . $this->e($f['href'] ?? '#') . '" class="text-body">' . $this->e($f['name'] ?? '') . '</a></h5>';
            $meta = array_filter([$f['size'] ?? null, $f['meta'] ?? null]);
            if ($meta) {
                $html .= '<small class="text-muted">' . $this->e(implode(' · ', $meta)) . '</small>';
            }
            $html .= '</div>';
            $html .= '<div class="dropdown"><a href="#" class="text-muted" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>';
            $html .= '<div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">下载</a><a class="dropdown-item" href="#">重命名</a><a class="dropdown-item text-danger" href="#">删除</a></div></div>';
            $html .= '</div></div></div></div>';
        }

        return $html . '</div>';
    }
}

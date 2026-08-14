<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;

/**
 * 树形视图（jsTree，支持复选框、拖拽、无限层级）
 *
 * XfAdmin::treeView([
 *     'data' => [
 *         ['text' => '根节点', 'state' => ['opened' => true], 'children' => [
 *             ['text' => '子节点1', 'icon' => 'ti ti-file'],
 *         ]],
 *     ],
 *     'checkbox' => true,
 *     'dnd'      => true,      // 拖拽
 *     'options'  => [],
 * ])
 */
class TreeView extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'data'     => [],
            'checkbox' => false,
            'dnd'      => false,
            'options'  => [],
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return ['jstree'];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $plugins = [];
        if ($this->get('checkbox')) {
            $plugins[] = 'checkbox';
        }
        if ($this->get('dnd')) {
            $plugins[] = 'dnd';
        }
        $config = array_replace_recursive([
            'core'    => ['data' => array_values((array) $this->get('data', [])), 'check_callback' => (bool) $this->get('dnd')],
            'plugins' => $plugins,
        ], (array) $this->get('options', []));

        return '<div' . $this->attrs([
            'id'             => $this->resolveId('xf-tree'),
            'data-xf'        => 'jstree',
            'data-xf-config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]) . '></div>';
    }
}

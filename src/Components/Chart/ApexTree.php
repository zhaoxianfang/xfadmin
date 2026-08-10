<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Chart;

use zxf\XfAdmin\Components\Component;

/**
 * 组织架构树图（charts-apextree.html，基于 apextree 插件，离线可用）
 *
 * 以树状节点图展示组织架构 / 层级关系，支持四个展开方向、节点头像与自定义配色，
 * 内置展开/收起与缩放交互。
 *
 * XfAdmin::apexTree([
 *     'height'    => 500,
 *     'direction' => 'top',              // top | bottom | left | right（树的生长方向）
 *     'data'      => [                   // 嵌套节点：id/name 必填，其余可选
 *         'id'   => '1',
 *         'name' => '董事长',
 *         'role' => 'CEO',               // 副标题（职位）
 *         'avatar' => 'users/1.jpg',     // 头像（相对 images/ 或完整 URL）
 *         'color'  => '#3e60d5',         // 节点边框色
 *         'children' => [ [...], [...] ],
 *     ],
 *     'node_width'  => 150,
 *     'node_height' => 60,
 *     'collapsible' => true,             // 节点可展开收起
 * ])
 */
class ApexTree extends Component
{
    protected function defaults(): array
    {
        return [
            'height'      => 500,
            'direction'   => 'top',
            'data'        => [],
            'node_width'  => 150,
            'node_height' => 60,
            'collapsible' => true,
        ];
    }

    protected function assets(): array
    {
        return ['apextree'];
    }

    /** 递归规范化节点：头像走包内图片解析，保证外链 / data URI / 相对路径均可用 */
    protected function normalizeNode(array $node): array
    {
        if (! empty($node['avatar'])) {
            $node['avatar'] = $this->img((string) $node['avatar']);
        }
        if (! empty($node['children']) && is_array($node['children'])) {
            $node['children'] = array_map(fn ($c) => $this->normalizeNode((array) $c), array_values($node['children']));
        }

        return $node;
    }

    protected function html(): string
    {
        $id  = $this->resolveId('xf-apextree');
        $cfg = [
            'data'        => $this->normalizeNode((array) $this->get('data', [])),
            'direction'   => (string) $this->get('direction', 'top'),
            'height'      => (int) $this->get('height', 500),
            'nodeWidth'   => (int) $this->get('node_width', 150),
            'nodeHeight'  => (int) $this->get('node_height', 60),
            'collapsible' => (bool) $this->get('collapsible', true),
        ];

        return '<div' . $this->attrs([
            'class'          => 'xf-apextree',
            'id'             => $id,
            'data-xf'        => 'apextree',
            'data-xf-config' => json_encode($cfg, JSON_HEX_TAG | JSON_HEX_AMP),
        ]) . '></div>';
    }
}

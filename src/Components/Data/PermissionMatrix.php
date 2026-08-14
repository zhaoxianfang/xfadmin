<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\Support\Html;
use zxf\XfAdmin\XfAdmin;

/**
 * 权限矩阵（roles.html / permissions.html）—— 角色 × 权限 的勾选网格
 *
 * XfAdmin::permissionMatrix([
 *     'roles' => ['admin' => '管理员', 'editor' => '编辑'],
 *     'groups'=> [
 *         '用户管理' => [
 *             'user.view' => '查看', 'user.edit' => '编辑', 'user.delete' => '删除',
 *         ],
 *     ],
 *     'values' => ['admin' => ['user.view','user.edit','user.delete'], 'editor' => ['user.view','user.edit']],
 *     'readOnly'=> false,
 * ])
 */
class PermissionMatrix extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'roles'    => [],
            'groups'   => [],
            'values'   => [],
            'readOnly' => false,
        ];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('perms');
        $roles = (array) $this->get('roles');
        $groups = (array) $this->get('groups');
        $values = (array) $this->get('values');
        $ro = $this->get('readOnly') ? 'disabled' : '';

        $html = '<div' . $this->attrs(['class' => 'xf-perms table-responsive', 'id' => $id]) . '>';
        $html .= '<table class="table table-bordered align-middle mb-0"><thead><tr><th>权限</th>';
        foreach ($roles as $rk => $rl) {
            $html .= '<th class="text-center">' . $this->e($rl) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($groups as $gName => $perms) {
            $html .= '<tr class="table-light"><th colspan="' . (count($roles) + 1) . '">' . $this->e($gName) . '</th></tr>';
            foreach ((array) $perms as $pk => $pl) {
                $html .= '<tr><td>' . $this->e($pl) . ' <code class="text-muted small">' . $this->e($pk) . '</code></td>';
                foreach ($roles as $rk => $rl) {
                    $checked = in_array($pk, (array) ($values[$rk] ?? []), true) ? 'checked' : '';
                    $html .= '<td class="text-center"><input type="checkbox" class="form-check-input" name="perm[' . $this->e($rk) . '][]" value="' . $this->e($pk) . '" ' . $checked . ' ' . $ro . '></td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table></div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Data;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 角色管理（permissions.html / roles 页面）
 *
 * XfAdmin::roles([
 *     'title' => '角色与权限',
 *     'roles' => [
 *         ['name'=>'超级管理员','description'=>'拥有全部权限','users_count'=>1,'permissions_count'=>48,'color'=>'danger','guard'=>'admin'],
 *         ['name'=>'编辑','description'=>'内容管理','users_count'=>8,'permissions_count'=>20,'color'=>'info','guard'=>'web'],
 *     ],
 *     'permissions' => [ ... ],   // 可选：传入则渲染权限矩阵（PermissionMatrix）
 * ])
 */
class Roles extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['title' => '', 'roles' => [], 'permissions' => []];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $roles = (array) $this->get('roles', []);
        if (empty($roles)) {
            return '';
        }
        $title = $this->get('title') ? '<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">'.$this->escape($this->get('title')).'</h5><button class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>新建角色</button></div>' : '';

        $html = $title.'<div class="row g-3">';
        foreach ($roles as $r) {
            $r = (array) $r;
            // color 走白名单校验，防止任意值拼入 class 属性
            $color = $this->enum($r['color'] ?? 'primary', ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'], 'primary');
            $html .= '<div class="col-lg-4 col-md-6">';
            $html .= '<div class="card h-100">';
            $html .= '<div class="card-body">';
            $html .= '<div class="d-flex align-items-center justify-content-between mb-2"><h6 class="mb-0"><i class="ti ti-shield-check text-'.$this->escape($color).' me-1"></i>'.$this->escape($r['name'] ?? '').'</h6>'
                .'<span class="badge bg-'.$this->escape($color).'-subtle text-'.$this->escape($color).'">'.$this->escape($r['guard'] ?? 'web').'</span></div>';
            $html .= '<p class="text-muted small">'.$this->escape($r['description'] ?? '').'</p>';
            $html .= '<div class="d-flex gap-4 mb-3"><div><div class="fw-bold">'.(int) ($r['users_count'] ?? 0).'</div><small class="text-muted">用户</small></div>'
                .'<div><div class="fw-bold">'.(int) ($r['permissions_count'] ?? 0).'</div><small class="text-muted">权限</small></div></div>';
            $html .= '<div class="d-flex gap-2"><button class="btn btn-sm btn-light flex-fill"><i class="ti ti-key me-1"></i>权限</button><button class="btn btn-sm btn-light"><i class="ti ti-edit"></i></button><button class="btn btn-sm btn-light text-danger"><i class="ti ti-trash"></i></button></div>';
            $html .= '</div></div></div>';
        }
        $html .= '</div>';

        $perms = (array) $this->get('permissions', []);
        if ($perms) {
            $html .= (string) XfAdmin::permissionMatrix(['groups' => $perms]);
        }

        return $html;
    }
}

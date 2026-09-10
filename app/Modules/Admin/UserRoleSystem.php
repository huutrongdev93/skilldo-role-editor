<?php
namespace UserRoleEditor\Modules\Admin;

use SkillDo\Cms\Support\Admin;
use SkillDo\Cms\Support\Role;
use SkillDo\Http\Request;
use SkillDo\Support\Auth;
use UserRoleEditor\Services\RoleEditorService;
use UserRoleEditor\Supports\RoleEditorHelper;
/**
 * UserRoleSystem
 * -------------------------------------------------
 * Tạo tab phân quyền trong trang hệ thống
 * Hỗ trợ phân quyền cho các nhóm quyền
 * Tạo thêm nhóm quyền
 */

class UserRoleSystem
{
    static function register($tabs)
    {
        if(Auth::hasCap('role_editor'))
        {
            $tabs['role'] = [
                'label'         => trans('user-role-editor::role.title'),
                'description'   => trans('user-role-editor::role.system.description'),
                'callback'      => [self::class, 'render'],
                'icon'          => '<i class="fa-duotone fa-user-lock"></i>',
                'form'          => false,
            ];
        }
        return $tabs;
    }

    static function render(Request $request): void
    {
        $roles = Role::make()->all();

        $roleNameDefault  = (Admin::isRoot()) ? 'root' : 'administrator';

        $roleCurrentKey   = ($request->input('role') == '') ? $roleNameDefault : $request->input('role');

        $roleCurrentName  = '';

        $roleCurrent 	  = Role::get($roleCurrentKey)->getCapabilities();

        $roleLabel = RoleEditorService::label();

        $roleGroup = RoleEditorService::group();

        if(!Admin::isRoot() && $roles['root'])
        {
            unset($roles['root']);

            $roleOnlyRoot = RoleEditorHelper::onlyRoot();

            foreach ($roleGroup as $groupKey => $groupValue)
            {
                if($groupKey == $roleCurrentKey) $roleCurrentName = $groupValue['label'];

                foreach ($groupValue['capabilities'] as $capabilityKey => $capability)
                {
                    if(in_array($capability, $roleOnlyRoot) !== false)
                    {
                        unset($roleGroup[$groupKey]['capabilities'][$capabilityKey]);
                    }
                }

                if(!hasItems($roleGroup[$groupKey]['capabilities']))
                {
                    unset($roleGroup[$groupKey]);
                }
            }
        }

        foreach ($roles as $groupKey => $groupValue)
        {
            if($groupKey == $roleCurrentKey)
            {
                $roleCurrentName = $groupValue->getName();
                break;
            }
        }

        echo view('user-role-editor::system', [
            'roleCurrentKey' => $roleCurrentKey,
            'roleCurrentName' => $roleCurrentName,
            'roles' => $roles,
            'roleGroup' => $roleGroup,
            'roleLabel' => $roleLabel,
            'roleCurrent' => $roleCurrent,
        ]);
    }
}
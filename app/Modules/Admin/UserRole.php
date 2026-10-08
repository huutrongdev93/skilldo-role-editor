<?php
namespace UserRoleEditor\Modules\Admin;

use SkillDo\Cms\Support\Admin;
use SkillDo\Cms\Support\Role;
use SkillDo\Cms\Table\Columns\ColumnView;
use SkillDo\Support\Auth;
use UserRoleEditor\Services\RoleEditorService;
use SkillDo\Cms\Support\UserRole as UserRoleHelper;
use UserRoleEditor\Supports\RoleEditorHelper;

/**
 * UserRole
 * -------------------------------------------------
 * Tạo tab phân quyền trong trang chi tiết user
 * Hỗ trợ phân quyền riêng cho từng user
 */
class UserRole
{
    static function columnHeader($column) {
        $column['role'] = [
            'label' => trans('user-role-editor::user.role'),
            'column'=> fn($item, $args) => ColumnView::make('role', $item, $args)
                ->value(fn($item) => Role::get($item->role)?->getName() ?? $item->role)
                ->html(function (ColumnView $column) {
                    echo '<span class="js_btn_user_role" data-id="'.$column->item->id.'" data-role="'.$column->item->role.'">'.$column->value.'</span>';
                })
        ];
        return $column;
    }

    static function model(): void
    {
        // v8: route admin.user.index → UserController@index → trang 'user_index' (không phải 'users_index')
        if(app('router')->currentRouteName() === 'admin.user.index' || Admin::isPage('user_index'))
        {
            $roles = Role::make()->all();

            $roleOptions = [];

            foreach ($roles as $role)
            {
                $roleOptions[$role->getKey()] = $role->getName();
            }

            echo view('user-role-editor::user-model', [
                'roleOptions' => $roleOptions
            ]);
        }
    }

    //Thêm tab vào admin > user > detail
    static function registerTab($args) {
        if(Auth::hasCap('role_editor_user') )
        {
            $args['role'] = [
                'label' => trans('user-role-editor::role.title'),
                'callback' => [static::class, 'tab']
            ];
        }
        return $args;
    }

    static function tab($user): void
    {
        $roles = Role::make()->all();

        $roleCurrent = UserRoleHelper::getCap($user->id);

        $roleDefault   = Role::get($user->role)?->getCapabilities() ?? [];

        $roleLabel = RoleEditorService::label();

        $roleGroup = RoleEditorService::group();

        if(!Admin::isRoot() && $roles['root'])
        {
            unset($roles['root']);

            $roleOnlyRoot = RoleEditorHelper::onlyRoot();

            foreach ($roleGroup as $groupKey => $groupValue)
            {
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

        $form = form();

        $roleOption = [];

        foreach ($roles as $roleKey => $role)
        {
            $roleOption[$roleKey] = $role->getName();
        }

        $form->radio('role_name', $roleOption, [], $user->role);

        echo view('user-role-editor::user-tab', [
            'form'      => $form,
            'roles'     => $roles,
            'roleGroup' => $roleGroup,
            'roleLabel' => $roleLabel,
            'roleCurrent' => $roleCurrent,
            'roleDefault' => $roleDefault,
            'user' => $user
        ]);
    }
}
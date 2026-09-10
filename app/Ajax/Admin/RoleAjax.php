<?php
namespace UserRoleEditor\Ajax\Admin;

use SkillDo\Cms\Models\User;
use SkillDo\Cms\Support\Admin;
use SkillDo\Cms\Support\Role;
use SkillDo\Cms\Support\UserRole;
use SkillDo\Http\Request;
use SkillDo\Support\Auth;
use Illuminate\Support\Str;
use SkillDo\Validate\Rule;

class RoleAjax 
{
    static function save(Request $request): void
    {
        if(!Auth::hasCap('role_editor')) 
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $data            = $request->input();

        $roleName        = Str::clear($data['role_name']);

        if($roleName == 'root' && !Admin::isRoot())
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $role  = Role::get($roleName);

        if(!hasItems($role))
        {
            response()->error(trans('user-role-editor::error.role.notFound'));
        }

        $capabilities_old = $role->getCapabilities();

        if(!empty($data['capabilities']))
        {
            $capabilities_up  = $data['capabilities'];

            foreach ($capabilities_up as $key => $value)
            {
                if(!isset($capabilities_old[$key]))
                {
                    $role->add($key);
                }
                else
                {
                    unset($capabilities_old[$key]);
                }
            }
        }

        if( hasItems($capabilities_old))
        {
            foreach ($capabilities_old as $key => $value )
            {
                $role->remove($key);
            }
        }

        response()->success(trans('ajax.save.success'));
    }
    
    static function add(Request $request): void
    {
        if(!Auth::hasCap('role_add'))
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $validate = $request->validate([
            'label' => Rule::make(trans('role.name'))->notEmpty(),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $roleName  = Str::clear($request->input('label'));

        $roleKey = str_replace('-', '', Str::slug($roleName));

        if($roleKey == 'root' && !Admin::isRoot())
        {
            response()->error(trans('user-role-editor::error.role.exists'));
        }

        $role = Role::get($roleKey);

        if(hasItems($role))
        {
            response()->error(trans('user-role-editor::error.role.exists'));
        }

        Role::make()->add($roleKey, $roleName);

        response()->success(trans('ajax.add.success'), $roleKey);
    }
    
    static function edit(Request $request): void
    {
        if(!Auth::hasCap('role_update'))
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $validate = $request->validate([
            'roleName' => Rule::make(trans('role.name'))->notEmpty(),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $roleName = Str::clear($request->input('roleName'));

        $roleKey = $request->input('roleKey');

        if($roleKey == 'root' && !Admin::isRoot())
        {
            response()->error(trans('user-role-editor::error.role.exists'));
        }

        $role    = Role::get($roleKey);

        if(!hasItems($role))
        {
            response()->error(trans('error.role.isset'));
        }

        Role::make()->update($roleKey, $roleName);

        response()->success(trans('ajax.update.success'), $roleName);
    }
    
    static function delete(Request $request): void
    {
        if(!Auth::hasCap('role_delete'))
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        if($request->input())
        {
            $validate = $request->validate([
                'data' => Rule::make(trans('user-role-editor::role.name'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $roleKey = Str::clear($request->input('data'));

			if(in_array($roleKey, ['root', 'administrator', 'customer', config('cms.default_role')]))
            {
                response()->error(trans('user-role-editor::error.role.delete'));
			}

            $role = Role::get($roleKey);

            if(!hasItems($role))
            {
                response()->error(trans('user-role-editor::error.role.isset'));
            }

            Role::make()->remove($roleKey);

			User::where('role', $roleKey)->update(['role', config('cms.default_role')]);

            response()->success(trans('ajax.delete.success'), [
				'location' => 'admin/plugins/role'
            ]);
        }

        response()->error(trans('ajax.delete.error'));
    }
    
    static function userLoadCapabilities(Request $request): void
    {
        $roleKey = $request->input('role_name');

        $userId = (int)$request->input('user_id');

        $user = User::find($userId);

        $roleDisabled = Role::get($roleKey)->getCapabilities();

        $roleChecked = [];

        if($roleKey == $user->role)
        {
            $roleChecked = UserRole::getCap($user->id);
        }

        foreach ($roleDisabled as $roleKey => $roleValue)
        {
            $roleChecked[$roleKey] = $roleValue;
        }

        response()->error(trans('ajax.load.success'), [
            'roleDisabled' => $roleDisabled,
            'roleChecked' => $roleChecked,
        ]);
    }
    
    static function userSave(Request $request): void
    {
        if(!Auth::hasCap('role_editor_user'))
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $roleName = Str::clear($request->input('role_name'));

        if($roleName == 'root' && !Admin::isRoot()) {
            response()->error(trans('user-role-editor::error.role.notFound'));
        }

        $capabilities = $request->input('capabilities');

        $userId = (int)$request->input('user_id');

        $userEdit = User::find($userId);

        if(!hasItems($userEdit))
        {
            response()->error(trans('user-role-editor::error.role.user'));
        }

        $userCurrent = auth();

        if(($userCurrent->id != $userEdit->id && $userEdit->username == 'root') || Auth::hasCap('user_edit'))
        {
            response()->error(trans('user-role-editor::error.role.update'));
        }

        $capabilitiesUp = (!empty($capabilities)) ? $capabilities : [];

        $capabilitiesUp[$roleName] = 1;

        User::updateMeta($userEdit->id, 'capabilities', $capabilitiesUp);

        if($userEdit->role !== $roleName)
        {
            $userEdit->role = $roleName;

            $userEdit->save();
        }

        response()->success(trans('ajax.save.success'));
    }
    
    static function userChangeRole(Request $request): void
    {
        if(!Auth::hasCap('role_editor_user'))
        {
            response()->error(trans('user-role-editor::error.role'));
        }

        $id = (int)$request->input('id');

        $userEdit = User::find($id);

        if(!hasItems($userEdit))
        {
            response()->error(trans('user-role-editor::user.ajax.noExit'));
        }

        if(!Auth::hasCap('edit_users'))
        {
            response()->error(trans('user-role-editor::user.ajax.role'));
        }

        $validate = $request->validate([
            'role' => Rule::make(trans('role.name'))
                ->notEmpty()
                ->in(array_keys(Role::make()->getNames()))
                ->custom(function($value) use ($userEdit) {
                    return !($value == $userEdit->role);
                }, trans('error.role.noChange')),
        ]);

        if ($validate->fails())
        {
            response()->error($validate->errors());
        }

        $role = Str::clear($request->input('role'));

        if($role == 'root' && !Admin::isRoot())
        {
            response()->error(trans('user-role-editor::error.role.notFound'));
        }

        $userEdit->role = $role;

        $userEdit->save();

        response()->success(trans('ajax.update.success'), [
            'id'    => $userEdit->id,
            'key'  => $role,
            'name' => Role::get($role)->getName(),
        ]);
    }
}
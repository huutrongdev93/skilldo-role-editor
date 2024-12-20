<?php
use JetBrains\PhpStorm\NoReturn;
use SkillDo\Validate\Rule;

class AdminRoleAjax {
    #[NoReturn] 
    static function save(SkillDo\Http\Request $request, $model): void
    {
        if(!Auth::hasCap('role_editor')) {
            response()->error(trans('error.role'));
        }

        if ($request->isMethod('post')) {

            $data            = $request->input();

            $roleName        = Str::clear($data['role_name']);

            if($roleName == 'root' && !Admin::isRoot()) {
                response()->error(trans('error.role'));
            }

            $role  = Role::get($roleName);

            if(!have_posts($role)) {
                response()->error(trans('error.role.notFound'));
            }

            $capabilities_old = $role->getCapabilities();

            if(!empty($data['capabilities']) ) {

                $capabilities_up  = $data['capabilities'];

                foreach ($capabilities_up as $key => $value) {

                    if(!isset($capabilities_old[$key])) {
                        $role->add($key);
                    }
                    else {
                        unset($capabilities_old[$key]);
                    }
                }
            }

            if( have_posts($capabilities_old)) {
                foreach ($capabilities_old as $key => $value ) {
                    $role->remove($key);
                }
            }

            response()->success(trans('ajax.save.success'));
        }

        response()->error(trans('ajax.save.error'));
    }
    #[NoReturn]
    static function add(SkillDo\Http\Request $request, $model): void
    {
        if(!Auth::hasCap('role_add')) {
            response()->error(trans('error.role'));
        }

        if($request->input()) {

            $validate = $request->validate([
                'label' => Rule::make(trans('role.name'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $roleName  = Str::clear($request->input('label'));

            $roleKey = str_replace('-', '', Str::slug($roleName));

            if($roleKey == 'root' && !Admin::isRoot())
            {
                response()->error(trans('error.role.exists'));
            }

            $role    = Role::get($roleKey);

            if(have_posts($role)) {
                response()->error(trans('error.role.exists'));
            }

            Role::make()->add($roleKey, $roleName);

            response()->success(trans('ajax.add.success'), $roleKey);
        }

        response()->error(trans('ajax.add.error'));
    }
    #[NoReturn]
    static function edit(SkillDo\Http\Request $request, $model): void
    {
        if(!Auth::hasCap('role_update')) {
            response()->error(trans('error.role'));
        }

        if($request->input()) {

            $validate = $request->validate([
                'roleName' => Rule::make(trans('role.name'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $roleName = Str::clear($request->input('roleName'));

            $roleKey = $request->input('roleKey');

            $role    = Role::get($roleKey);

            if(!have_posts($role)) {
                response()->error(trans('error.role.isset'));
            }

            Role::make()->update($roleKey, $roleName);

            response()->success(trans('ajax.update.success'), $roleName);
        }

        response()->error(trans('ajax.update.error'));
    }
    #[NoReturn]
    static function delete(SkillDo\Http\Request $request, $model): void
    {
        if(!Auth::hasCap('role_delete')) {
            response()->error(trans('error.role'));
        }

        if($request->input()) {

            $validate = $request->validate([
                'data' => Rule::make(trans('role.name'))->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $roleKey = Str::clear($request->input('data'));

			if(in_array($roleKey, ['root', 'administrator', 'customer', Option::get('default_role')])) {
                response()->error(trans('error.role.delete'));
			}

            $role = Role::get($roleKey);

            if(!have_posts($role)) {
                response()->error(trans('error.role.isset'));
            }

            Role::make()->remove($roleKey);

			model('users')::where('role', $roleKey)->update(['role', Option::get('default_role')]);

            response()->success(trans('ajax.delete.success'), [
				'location' => 'admin/plugins/role'
            ]);
        }

        response()->error(trans('ajax.delete.error'));
    }
    #[NoReturn]
    static function userLoadCapabilities(SkillDo\Http\Request $request, $model): void {

        if($request->input() ) {

            $roleKey = $request->input('role_name');

            $userId = (int)$request->input('user_id');

			$user = User::get($userId);

            $roleDisabled = Role::get($roleKey)->getCapabilities();

            $roleChecked = [];

			if($roleKey == $user->role) {

                $roleChecked = User::getCap($user->id);
			}

			foreach ($roleDisabled as $roleKey => $roleValue) {

                $roleChecked[$roleKey] = $roleValue;
			}

            response()->error(trans('ajax.load.success'), [
				'roleDisabled' => $roleDisabled,
				'roleChecked' => $roleChecked,
            ]);
        }

        response()->error(trans('ajax.load.error'));
    }
    #[NoReturn]
    static function userSave(SkillDo\Http\Request $request, $model): void
    {
        if(!Auth::hasCap('role_editor_user')) {
            response()->error(trans('error.role'));
        }

        if($request->input()) {

            $roleName = Str::clear($request->input('role_name'));

            if($roleName == 'root' && !Admin::isRoot()) {
                response()->error(trans('error.role.notFound'));
            }

            $capabilities = $request->input('capabilities');

            $userId = (int)$request->input('user_id');

            $userEdit = User::get(Qr::set($userId)->where('status', '<>', 'trash'));

            if(!have_posts($userEdit) ) {
                response()->error(trans('error.role.user'));
            }

            $userCurrent = Auth::user();

            if(($userCurrent->id != $userEdit->id && $userEdit->username == 'root') || Auth::hasCap('user_edit')) {
                response()->error(trans('error.role.update'));
            }

            $capabilitiesUp = (!empty($capabilities)) ? $capabilities : [];

            $capabilitiesUp[$roleName] = 1;

            User::updateMeta($userEdit->id, 'capabilities', $capabilitiesUp);

            if($userEdit->role !== $roleName) {
                User::insert(['id' => $userEdit->id, 'role' => $roleName], $userEdit);
            }

            response()->success(trans('ajax.save.success'));

        }

        response()->error(trans('ajax.save.error'));
    }
    #[NoReturn]
    static function userChangeRole(SkillDo\Http\Request $request): void
    {
        if(!Auth::hasCap('role_editor_user')) {
            response()->error(trans('error.role'));
        }

        if($request->isMethod('post')) {

            $id = (int)$request->input('id');

            $userEdit = User::get($id);

            if(!have_posts($userEdit)) {
                response()->error(trans('user.ajax.noExit'));
            }

            if(!Auth::hasCap('edit_users')) {
                response()->error(trans('user.ajax.role'));
            }

            $validate = $request->validate([
                'role' => Rule::make(trans('role.name'))
                    ->notEmpty()
                    ->in(array_keys(Role::make()->getNames()))
                    ->custom(function($value) use ($userEdit) {
                        return !($value == $userEdit->role);
                    }, trans('error.role.noChange')),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $role = Str::clear($request->input('role'));

            if($role == 'root' && !Admin::isRoot()) {
                response()->error(trans('error.role.notFound'));
            }

            $error = User::insert(['id' => $id, 'role' => $role], $userEdit);

            if(is_skd_error($error)) {
                response()->error($error);
            }

            response()->success(trans('ajax.update.success'), [
                'id'    => $userEdit->id,
                'key'  => $role,
                'name' => Role::get($role)->getName(),
            ]);
        }

        response()->error(trans('ajax.update.error'));
    }
}

Ajax::admin('AdminRoleAjax::save');
Ajax::admin('AdminRoleAjax::add');
Ajax::admin('AdminRoleAjax::edit');
Ajax::admin('AdminRoleAjax::delete');
Ajax::admin('AdminRoleAjax::userLoadCapabilities');
Ajax::admin('AdminRoleAjax::userSave');
Ajax::admin('AdminRoleAjax::userChangeRole');
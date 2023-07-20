<?php
class AdminRoleAjax {
    static function save($ci, $model) {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if( Request::post() ) {

            $data            = Request::post();

            $roleName        = Str::clear($data['role_name']);

            $role            = Role::get($data['role_name']);

            $capabilities_old = $role->capabilities;

            if(!empty($data['capabilities']) ) {
                $capabilities_up  = $data['capabilities'];
                foreach ( $capabilities_up as $key => $value ) {
                    if( !isset($capabilities_old[$key]) ) {
                        $role->add_cap($key);
                    }
                    else unset( $capabilities_old[$key] );
                }
            }

            if( have_posts($capabilities_old)) {
                foreach ($capabilities_old as $key => $value ) {
                    $role->remove_cap($key);
                }
            }

            $result['status']  = 'success';

            $result['message'] = __('Lưu dữ liệu thành công');

        }

        echo json_encode($result);

    }
    static function add( $ci, $model ): bool
    {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if(Request::post()) {

            $data            = Request::post();

            $roleName        = Str::clear($data['label']);

			if(empty($roleName)) {
                $result['message'] = __('Tên vai trò không được để trống');
                echo json_encode($result);
				return false;
			}

            $roleKey = str_replace('-', '', Str::slug($roleName));

            $role    = Role::get($roleKey);

            if(!have_posts($role)) {
                Role::add($roleKey, $roleName);
                $result['role']     = $roleKey;
                $result['status']   = 'success';
                $result['message']  = __('Lưu dữ liệu thành công');
            }
            else {
                $result['message'] = __('Nhóm quyền này đã tồn tại.');
            }
        }

        echo json_encode($result);

		return false;
    }
    static function edit( $ci, $model ): bool
    {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if(Request::post()) {

            $data            = Request::post();

            $roleName        = Str::clear($data['roleName']);

            if(empty($roleName)) {
                $result['message'] = __('Tên vai trò không được để trống');
                echo json_encode($result);
                return false;
            }

            $roleKey = Request::post('roleKey');

            $role    = Role::get($roleKey);

            if(have_posts($role)) {
                Role::update($roleKey, $roleName);
                $result['name']     = $roleName;
                $result['status']   = 'success';
                $result['message']  = __('Lưu dữ liệu thành công');
            }
            else {
                $result['message'] = __('Không tìm thấy vai trò này.');
            }
        }

        echo json_encode($result);

        return false;
    }
    static function userLoadCapabilities( $ci, $model ): void {

        $result['status']  = 'error';

        $result['message'] = __('Load dữ liệu không thành công');

        if(Request::post() ) {

            $role_name = Request::post('role_name');

            $user_id = Request::post('user_id');

            $role_name_current = user_role( $user_id );

            $role_name_current =  array_pop( $role_name_current );

            if( Admin::isRoot() ) {

                $role_name_default = 'root';
            }
            else $role_name_default = 'administrator';

            $role_all 		= Role::get( $role_name_default )->capabilities;

            $role_default   = Role::get( $role_name )->capabilities;

            $role_current 	= Role::get( $user_id );

            $roleLabel = RoleEditor::label();

            $roleGroup = RoleEditor::group();

            if($role_name_default == 'administrator') {

                foreach ($roleGroup as &$role_group_value) {

                    foreach ($role_group_value['capabilities'] as $key => $cap) {

                        if(empty($role_all[$cap])) unset($role_group_value['capabilities'][$key]);
                    }
                }
            }

            if($role_name_current != $role_name ) {

                $role_current = $role_default;
            }

            ob_start();

            foreach ( $roleGroup as $key => $value): if(!have_posts($value['capabilities'])) continue; ?>
                <div class="col-md-12">
                    <h5><?php echo $value['label'];?></h5>
                    <?php foreach ($value['capabilities'] as $capabilities): ?>
                        <?php if(!isset($role_all[$capabilities])) continue; ?>
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label> <input type="checkbox" class="<?php echo (!empty($role_default[$capabilities]))?'icheckdisable':'icheck';?>" name="capabilities[<?php echo $capabilities;?>]" value="1" <?php echo (!empty($role_current[$capabilities]))?'checked':'';?>> <?php echo $roleLabel[$capabilities];?> </label>
                            </div>
                        </div>
                    <?php endforeach ?>

                    <div class="clearfix"></div>
                    <hr />
                </div>
            <?php endforeach;

            $content = ob_get_contents();

            ob_clean();

            ob_end_flush();

            $result['content']  = $content;

            $result['status']  = 'success';

            $result['message'] = __('Lưu dữ liệu thành công');

        }

        echo json_encode($result);

    }
    static function userSave( $ci, $model ) {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if(Request::post()) {

            $data       = Request::post();

            $roleName = Str::clear($data['role_name']);

            $user_id = (int)$data['user_id'];

            $userEdit = User::get(Qr::set($user_id)->where('status', '<>', 'trash'));

            if(!have_posts($userEdit) ) {
                $result['message'] = __('User không chính xác.');
                echo json_encode($result);
                return false;
            }

            $userCurrent = Auth::user();

            if($userCurrent->id != $userEdit->id && $userEdit->username == 'root') {
                $result['message'] = __('Bạn không có quyền thay đổi quyền hạn của thành viên này.');
                echo json_encode($result);
                return false;
            }

            if(Auth::hasCap('user_edit')) {
                $result['message'] = __('Bạn không có quyền thay đổi quyền hạn của thành viên.');
                echo json_encode($result);
                return false;
            }

            $capabilitiesUp = array();

            if(!empty($data['capabilities'])) $capabilitiesUp  = $data['capabilities'];

            $capabilitiesUp[$roleName] = 1;

            User::updateMeta($userEdit->id, 'capabilities', $capabilitiesUp);

			User::insert(['id' => $userEdit->id, 'role' => $roleName]);

            $result['status']  = 'success';

            $result['message'] = __('Lưu dữ liệu thành công');

        }

        echo json_encode($result);
    }
}

Ajax::admin('AdminRoleAjax::save');
Ajax::admin('AdminRoleAjax::add');
Ajax::admin('AdminRoleAjax::edit');
Ajax::admin('AdminRoleAjax::userLoadCapabilities');
Ajax::admin('AdminRoleAjax::userSave');

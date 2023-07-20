<?php
class AdminRoleUserAdd {
    static function roleBox(): void
    {
        $roles = skd_roles()->get_names();
        if(isset($roles['root'])) {
            unset($roles['root']);
        }
        include 'html/user/role.php';
    }

    static function roleSave($user_array) {

        $role = Request::post('role');

        if(!empty($role)) {
            $user_array['role'] = $role;
        }

        return $user_array;
    }
}
add_action('user_created_sections_secondary', 'AdminRoleUserAdd::roleBox', 10, 1);
add_action('admin_pre_user_register', 'AdminRoleUserAdd::roleSave', 10, 1);
<?php
class AdminRole {
    static function onlyRoot() {
        return apply_filters('role_only_root', [
            'builder',
            'switch_themes',
            'update_themes',
            'delete_themes',
            'install_themes',
            'edit_theme_editor',
            'edit_cms_status',
            'edit_setting_cache',
            'edit_setting_audit',
            'edit_setting_tinymce',
            'edit_plugins',
            'install_plugins',
            'update_plugins',
            'activate_plugins',
            'delete_plugins',
            'delete_users',
            'generate_form_register',
            'customer_list',
            'customer_active',
            'customer_add',
            'customer_edit',
            'customer_reset_password',
            'customer_block',
            'order_setting'
        ]);
    }
    static function registerSystem($tabs) {
        if(Auth::hasCap('role_editor')) {
            $tabs['role'] = [
                'label' => 'Phân quyền',
                'description' => 'Quản lý các cấp bật quyền hạng thành viên',
                'callback' => 'AdminRole::render',
                'icon' => '<i class="fa-duotone fa-user-lock"></i>',
                'form' => false,
            ];
        }
        return $tabs;
    }
    static function render(): void
    {
        $user = Auth::user();

        $roles = skd_roles()->get_names();

        if(Admin::isRoot()) {
            $roleNameDefault = 'root';
        }
        else $roleNameDefault = 'administrator';

        $roleCurrentKey 		= (Request::get('role') == '') ? $roleNameDefault : Request::get('role');

        $roleCurrentName 		= '';

        $roleCurrent 	= Role::get($roleCurrentKey)->capabilities;

        $roleLabel = RoleEditor::label();

        $roleGroup = RoleEditor::group();

        if(!Admin::isRoot() && $roles['root']) {

            unset($roles['root']);

            $roleOnlyRoot = AdminRole::onlyRoot();

            foreach ($roleGroup as $groupKey => $groupValue) {

                if($groupKey == $roleCurrentKey) $roleCurrentName = $groupValue['label'];

                foreach ($groupValue['capabilities'] as $capabilityKey => $capability) {
                    if(in_array($capability, $roleOnlyRoot) !== false) {
                        unset($roleGroup[$groupKey]['capabilities'][$capabilityKey]);
                    }
                }

                if(!have_posts($roleGroup[$groupKey]['capabilities'])) {
                    unset($roleGroup[$groupKey]);
                }
            }
        }

        foreach ($roles as $groupKey => $groupValue) {
            if($groupKey == $roleCurrentKey) {
                $roleCurrentName = $groupValue;
                break;
            }
        }

        include 'html/user_role_editor.php';
    }
    static function registerTab($args) {
        if(Auth::hasCap('role_editor_user') ) {
            $args['role'] = array(
                'label' => __('Phân Quyền'),
                'callback' => 'AdminRole::userTab'
            );
        }
        return $args;
    }
    static function userTab($user) {

        $role 			= skd_roles()->get_names();

        $role_name 		= user_role($user->id);

        $role_name 		=  array_pop($role_name);

        if(is_super_admin()) {

            $role_name_default = 'root';
        }
        else $role_name_default = 'administrator';

        $role_all 		= Role::get( $role_name_default )->capabilities;

        $role_default   = Role::get( $role_name )->capabilities;

        $role_current 	= get_role_caps( $user->id );

        $role_label 	= RoleEditor::label();

        $role_group     = RoleEditor::group();

        if($role_name_default == 'administrator') {

            foreach ($role_group as &$role_group_value) {

                foreach ($role_group_value['capabilities'] as $key => $cap) {

                    if(empty($role_all[$cap])) unset($role_group_value['capabilities'][$key]);
                }
            }
        }

        if( !is_super_admin() && $role['root'] ) unset($role['root']);

        include 'html/user_role_tab.php';
    }
}
add_filter('skd_system_tab', 'AdminRole::registerSystem', 50);
add_filter('admin_my_action_links', 'AdminRole::registerTab');
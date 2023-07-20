<?php
class RoleEditor {
    static function label() {
        return apply_filters('user_role_editor_label', []);
    }

    static function group() {
        return apply_filters('user_role_editor_group', []);
    }

    static function capabilities($label) {
        $label['role_editor']         = 'Phân quyền cho nhóm';
        $label['role_editor_user']    = 'Phân quyền cho user';
        return $label;
    }
}

add_filter('skd_admin_capabilities_user', 'RoleEditor::capabilities');


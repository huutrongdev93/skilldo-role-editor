<?php
class RoleEditor {
    static function label() {
        return apply_filters('user_role_editor_label', RoleEditor::capabilities());
    }

    static function group() {

        $group = apply_filters('user_role_editor_group', []);

        $group['role'] = [
            'label'         => trans('role.title'),
            'capabilities'  => array_keys(RoleEditor::capabilities())
        ];

        return $group;
    }

    static function capabilities(): array
    {
        $label['role_editor']         = 'Phân quyền cho nhóm';
        $label['role_add']            = 'Thêm chức vụ';
        $label['role_update']         = 'Cập nhật chức vụ';
        $label['role_delete']         = 'Xóa chức vụ';
        $label['role_editor_user']    = 'Phân quyền cho user';
        return $label;
    }

    static function highlightKeyword($label): array|string
    {
        $replaces = [
            'gray'  => ['xem', 'Xem', 'view', 'View'],
            'red'   => ['xóa', 'Xóa', 'delete', 'Delete', 'remove', 'Remove'],
            'green' => ['thêm', 'Thêm', 'add', 'Add', 'create', 'Create'],
            'blue'  => ['sửa', 'Sửa', 'cập nhật', 'Cập nhật', 'Cập Nhật', 'edit', 'Edit', 'update', 'Update'],
        ];

        foreach ($replaces as $template => $keywords) {
            foreach ($keywords as $keyword) {
                $label = str_replace($keyword, Admin::badge($template, $keyword), $label);
            }
        }

        return $label;
    }

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
}

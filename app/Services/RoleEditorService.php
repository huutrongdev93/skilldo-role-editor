<?php
namespace UserRoleEditor\Services;

class RoleEditorService
{
    static function label()
    {
        return apply_filters('user_role_editor_label', static::capabilities());
    }

    static function group()
    {
        $group = apply_filters('user_role_editor_group', []);

        $group['role'] = [
            'label'         => trans('user-role-editor::role.title'),
            'capabilities'  => array_keys(static::capabilities())
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
}

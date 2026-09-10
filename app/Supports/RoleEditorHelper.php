<?php
namespace UserRoleEditor\Supports;

use SkillDo\Cms\Support\Admin;

class RoleEditorHelper
{
    static function highlightKeyword($label): array|string
    {
        $replaces = [
            'gray'  => ['xem', 'Xem', 'view', 'View'],
            'red'   => ['xóa', 'Xóa', 'delete', 'Delete', 'remove', 'Remove'],
            'green' => ['thêm', 'Thêm', 'add', 'Add', 'create', 'Create'],
            'blue'  => ['sửa', 'Sửa', 'cập nhật', 'Cập nhật', 'Cập Nhật', 'edit', 'Edit', 'update', 'Update'],
        ];

        foreach ($replaces as $template => $keywords)
        {
            foreach ($keywords as $keyword)
            {
                $label = str_replace($keyword, Admin::badge($template, $keyword), $label);
            }
        }

        return $label;
    }

    static function onlyRoot()
    {
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

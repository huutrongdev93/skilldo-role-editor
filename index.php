<?php
class UserRoleEditor
{
    function __construct()
    {
        $role = Role::get('root');
        $role->add('role_editor');
        $role->add('role_add');
        $role->add('role_update');
        $role->add('role_delete');
        $role->add('role_editor_user');
    }
}

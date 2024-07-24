<?php
const URE_NAME = 'user-role-editor';

class user_role_editor {

    private string $name = 'user_role_editor';

    function __construct() {
        $role = Role::get('root');
        $role->add('role_editor');
        $role->add('role_add');
        $role->add('role_update');
        $role->add('role_delete');
        $role->add('role_editor_user');
    }
}

include 'user-role-function.php';
include 'user-role-ajax.php';
include 'user-role-admin.php';

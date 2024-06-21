<?php
/**
Plugin name     : User Role Editor
Plugin class    : user_role_editor
Plugin uri      : http://sikido.vn
Description     : Trình chỉnh sửa vai trò người dùng SKD plugin cho phép bạn thay đổi vai trò người dùng và khả năng dễ dàng. 
Author          : SKDSoftware Dev Team
Version         : 2.0.0
*/
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

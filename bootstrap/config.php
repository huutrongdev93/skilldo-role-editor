<?php

use UserRoleEditor\Modules\Admin\UserRole;
use UserRoleEditor\Modules\Admin\UserRoleSystem;

add_filter('admin_system_tabs', [UserRoleSystem::class, 'register'], 50);

add_filter('admin_my_action_links', [UserRole::class, 'registerTab']);
add_filter('manage_user_columns', [UserRole::class, 'columnHeader']);
add_action('admin_footer', [UserRole::class, 'model']);
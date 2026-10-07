<?php

use UserRoleEditor\Services\AdminAccessGuard;
use UserRoleEditor\Services\CoreRoleLabels;
use UserRoleEditor\Modules\Admin\UserRole;
use UserRoleEditor\Modules\Admin\UserRoleSystem;

add_filter('admin_system_tabs', [UserRoleSystem::class, 'register'], 50);

// Nhãn quyền của core (v8 không còn tự đăng ký như v7)
add_filter('user_role_editor_group', [CoreRoleLabels::class, 'group'], 1);
add_filter('user_role_editor_label', [CoreRoleLabels::class, 'label'], 1);

// Chặn menu + trang admin theo quyền (core v8 bỏ qua 'role' của AdminMenu — WD-107)
add_filter('admin_navigation_data', [AdminAccessGuard::class, 'navigation'], 99);
add_filter('admin_permission_access', [AdminAccessGuard::class, 'access']);
add_filter('admin_item_quick_access', [AdminAccessGuard::class, 'quickAccess'], 99);

add_filter('admin_my_action_links', [UserRole::class, 'registerTab']);
add_filter('manage_user_columns', [UserRole::class, 'columnHeader']);
add_action('admin_footer', [UserRole::class, 'model']);
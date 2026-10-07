<?php
namespace UserRoleEditor\Services;

use SkillDo\Support\Auth;

/**
 * AdminAccessGuard
 * -------------------------------------------------
 * Chặn menu + trang admin theo quyền của chức vụ.
 *
 * Core v8 có hai lỗ (WD-107):
 *  - AdminMenu::add()/addSub() bỏ qua tham số 'role' → menu Trang, Thư viện, Giao diện,
 *    Hệ thống, Thành viên hiện với mọi tài khoản đăng nhập được admin.
 *  - Layout admin vẫn gọi filter 'admin_permission_access' nhưng không còn ai gắn luật
 *    (v7 có hàm admin_permission_access()) → mở thẳng URL là vào được.
 *
 * Plugin khác bổ sung luật qua filter 'role_editor_admin_menu_caps' / 'role_editor_admin_route_caps'.
 */
class AdminAccessGuard
{
    /**
     * Khoá menu → quyền. Menu con viết 'cha.con'.
     */
    static public function menuCaps(): array
    {
        $themeWidgets = 'edit_theme_widgets';

        return apply_filters('role_editor_admin_menu_caps', [
            'page'                 => 'view_pages',
            'page.page-add'        => 'add_pages',
            'galleries'            => 'edit_gallery',
            'theme'                => 'edit_themes',
            'theme.option'         => 'edit_theme_options',
            'theme.menu'           => 'edit_theme_menus',
            'theme.widgets'        => $themeWidgets,
            'theme.footer'         => $themeWidgets,
            'theme.sidebar-list'   => $themeWidgets,
            'theme.sidebar-detail' => $themeWidgets,
            'theme.builder'        => $themeWidgets,
            'theme.builder-header' => $themeWidgets,
            'theme.builder-footer' => $themeWidgets,
            'theme.builder-home'   => $themeWidgets,
            'theme.theme-layout'   => $themeWidgets,
            'plugins'              => 'edit_plugins',
            'system'               => 'edit_setting',
            'user'                 => 'list_users',
        ]);
    }

    /**
     * Tên route admin → quyền (string) hoặc callable trả về bool.
     */
    static public function routeCaps(): array
    {
        $themeWidgets = 'edit_theme_widgets';

        return apply_filters('role_editor_admin_route_caps', [
            'admin.page.index'                => 'view_pages',
            'admin.page.add'                  => 'add_pages',
            'admin.page.edit'                 => 'edit_pages',
            'admin.galleries.index'           => 'edit_gallery',
            'admin.theme.option'              => 'edit_theme_options',
            'admin.theme.menu'                => 'edit_theme_menus',
            'admin.theme.widget.widget'       => $themeWidgets,
            'admin.theme.widget.sidebar'      => $themeWidgets,
            'admin.theme.widget.footer'       => $themeWidgets,
            'admin.builder.index'             => $themeWidgets,
            'admin.theme.builder.header'      => $themeWidgets,
            'admin.theme.builder.footer'      => $themeWidgets,
            'admin.theme.builder.home'        => $themeWidgets,
            'admin.theme.builder.page'        => $themeWidgets,
            'admin.theme.builder.create'      => $themeWidgets,
            'admin.theme.builder.layout'      => $themeWidgets,
            'admin.plugin.list'               => 'edit_plugins',
            'admin.system.index'              => 'edit_setting',
            'admin.system.detail'             => [self::class, 'canSystemDetail'],
            'admin.user.index'                => 'list_users',
            'admin.user.add'                  => 'create_users',
            'admin.user.edit'                 => [self::class, 'canEditUser'],
            'admin.post.index'                => [self::class, 'canPost'],
            'admin.post.add'                  => [self::class, 'canPost'],
            'admin.post.edit'                 => [self::class, 'canPost'],
            'admin.post_categories.index'     => [self::class, 'canPostCategory'],
        ]);
    }

    /**
     * filter admin_navigation_data
     */
    static public function navigation($nav)
    {
        if (!is_array($nav)) return $nav;

        $caps = static::menuCaps();

        foreach ($nav as $key => $item)
        {
            if (isset($caps[$key]) && !Auth::hasCap($caps[$key]))
            {
                unset($nav[$key]);
                continue;
            }

            if (!empty($item['subs']) && is_array($item['subs']))
            {
                foreach ($item['subs'] as $subKey => $sub)
                {
                    $capKey = $key.'.'.$subKey;

                    if (isset($caps[$capKey]) && !Auth::hasCap($caps[$capKey]))
                    {
                        unset($nav[$key]['subs'][$subKey]);
                    }
                }

                // Menu nhóm (url dạng 'system#marketing') chỉ để chứa menu con → hết con thì bỏ
                if (empty($nav[$key]['subs']) && str_contains((string)($item['url'] ?? ''), '#'))
                {
                    unset($nav[$key]);
                }
            }
            elseif (str_contains((string)($item['url'] ?? ''), '#') && !empty($item['callback']))
            {
                unset($nav[$key]);
            }
        }

        return $nav;
    }

    /**
     * filter admin_item_quick_access — ô "Truy cập nhanh" ở Dashboard
     */
    static public function quickAccess($items)
    {
        if (!is_array($items)) return $items;

        $caps = apply_filters('role_editor_quick_access_caps', [
            'post'     => 'add_posts',
            'option'   => 'edit_theme_options',
            'widget'   => 'edit_theme_widgets',
            'menu'     => 'edit_theme_menus',
            'gallery'  => 'edit_gallery',
            'products' => 'product_edit',
            'order'    => 'order_list',
            'contact'  => 'edit_setting',
        ]);

        foreach ($items as $index => $item)
        {
            $key = $item['key'] ?? '';

            if (isset($caps[$key]) && !Auth::hasCap($caps[$key]))
            {
                unset($items[$index]);
                continue;
            }

            // Link v7 'plugins?page=order' không còn route ở v8 → trang đơn hàng của sicommerce
            if ($key === 'order' && ($item['url'] ?? '') === 'plugins?page=order')
            {
                $items[$index]['url'] = 'order';
            }
        }

        return $items;
    }

    /**
     * filter admin_permission_access
     */
    static public function access($allowed)
    {
        if (!$allowed) return $allowed;

        $router = app('router');

        $routeName = $router->currentRouteName();

        if (empty($routeName)) return $allowed;

        $caps = static::routeCaps();

        if (!isset($caps[$routeName])) return $allowed;

        $rule = $caps[$routeName];

        if (is_callable($rule))
        {
            return (bool)call_user_func($rule, $router->current());
        }

        return Auth::hasCap($rule);
    }

    static public function canSystemDetail($route): bool
    {
        // Tab phân quyền có quyền riêng (role_editor) — không bắt thêm edit_setting
        if ($route && $route->parameter('tabKey') === 'role')
        {
            return Auth::hasCap('role_editor');
        }

        return Auth::hasCap('edit_setting');
    }

    static public function canEditUser($route): bool
    {
        if (Auth::hasCap('edit_users')) return true;

        // Ai cũng sửa được hồ sơ của chính mình
        $user = Auth::user();

        return $route && $user && (string)$route->parameter('id') === (string)$user->id;
    }

    static public function canPost($route): bool
    {
        $postType = (string)request()->input('post_type', 'post');

        $detail = \Taxonomy::getPost($postType ?: 'post');

        $capabilities = $detail['capabilities'] ?? [];

        $name = $route ? $route->getName() : '';

        $action = match ($name) {
            'admin.post.add'  => 'add',
            'admin.post.edit' => 'edit',
            default           => 'view',
        };

        return empty($capabilities[$action]) || Auth::hasCap($capabilities[$action]);
    }

    static public function canPostCategory($route): bool
    {
        $cateType = (string)request()->input('cate_type', 'post_categories');

        $detail = \Taxonomy::getCategory($cateType ?: 'post_categories');

        $cap = $detail['capabilities']['edit'] ?? '';

        return empty($cap) || Auth::hasCap($cap);
    }
}

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

            // Plugin khác (luật mặc định, plugin không bật thì khoá menu không tồn tại nên vô hại)
            'order'                => 'order_list',
            'affiliate'            => 'edit_setting', // plugin affiliate chưa khai quyền riêng
            'locator-store'        => 'store_list',
            'locator-store.locator-store-add' => 'store_edit',
            'reels'                => 'reels_view',
            'reels.reels-add'      => 'reels_add',
            'reels.reels-setting'  => 'reels_setting',
            // Menu con form đăng ký (khoá động theo từng form) nằm trong nhóm Marketing, xem navigation()
            'marketing.form_register_result_*' => 'view_email_register',
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

            // sicommerce
            'admin.products.index'                  => 'product_list',
            'admin.products.add'                    => 'product_edit',
            'admin.products.edit'                   => 'product_edit',
            'admin.products_categories.index'       => 'product_cate_list',
            'admin.products.brands.index'           => 'product_cate_list',
            'admin.products.brands.add'             => 'product_cate_edit',
            'admin.products.brands.edit'            => 'product_cate_edit',
            'admin.products.attributes.index'       => 'attributes_list',
            'admin.products.attributes.add'         => 'attributes_add',
            'admin.products.attributes.edit'        => 'attributes_edit',
            'admin.products.extra-templates.index'  => 'attributes_list',
            'admin.products.extra-templates.add'    => 'attributes_add',
            'admin.products.extra-templates.edit'   => 'attributes_edit',
            'admin.products.collection.index'       => 'products_collections_list',
            'admin.products.collection.add'         => 'products_collections_edit',
            'admin.products.collection.edit'        => 'products_collections_edit',
            'admin.order.index'                     => 'order_list',
            'admin.order.detail'                    => 'order_list',
            'admin.order.add'                       => 'order_add',
            'admin.order.edit'                      => 'order_edit',

            // affiliate (chưa khai quyền riêng nên dùng quyền cấu hình hệ thống)
            'admin.affiliate.index'                 => 'edit_setting',
            'admin.affiliate.commission.categories' => 'edit_setting',
            'admin.affiliate.commission.products'   => 'edit_setting',
            'admin.affiliate.registers'             => 'edit_setting',
            'admin.affiliate.users'                 => 'edit_setting',
            'admin.affiliate.user.detail'           => 'edit_setting',
            'admin.affiliate.orders'                => 'edit_setting',
            'admin.affiliate.histories'             => 'edit_setting',
            'admin.affiliate.payments'              => 'edit_setting',

            // locator-store
            'admin.locator_stores.index'            => 'store_list',
            'admin.locator_stores.add'              => 'store_edit',
            'admin.locator_stores.edit'             => 'store_edit',

            // reels
            'admin.reels.index'                     => 'reels_view',
            'admin.reels.add'                       => 'reels_add',
            'admin.reels.edit'                      => 'reels_edit',
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

                    $subCap = $caps[$capKey] ?? static::wildcardCap($caps, $capKey);

                    if ($subCap !== null && !Auth::hasCap($subCap))
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
     * Luật dạng 'cha.tiền_tố*' cho menu con có khoá động (vd form đăng ký theo từng form).
     */
    static protected function wildcardCap(array $caps, string $capKey): ?string
    {
        foreach ($caps as $pattern => $cap)
        {
            if (str_ends_with((string)$pattern, '*') && str_starts_with($capKey, substr($pattern, 0, -1)))
            {
                return $cap;
            }
        }

        return null;
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

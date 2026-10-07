<?php
namespace UserRoleEditor\Services;

use SkillDo\Cms\Taxonomy\Taxonomy;

/**
 * CoreRoleLabels
 * -------------------------------------------------
 * Nhãn + nhóm cho các quyền của CORE (đăng nhập admin, trang, bài viết, danh mục,
 * thư viện, giao diện, hệ thống, plugin, thành viên).
 * Bản v7 do core tự đăng ký (SKD_Admin_Role); v8 bỏ đi nên trang phân quyền chỉ còn
 * quyền của plugin. Nhóm bài viết / danh mục dựng theo Taxonomy nên post type do
 * theme/plugin đăng ký thêm cũng hiện ra.
 */
class CoreRoleLabels
{
    static public function group($group): array
    {
        $core = [];

        $core['general'] = ['label' => 'Chung', 'capabilities' => array_keys(static::general())];

        $core['page'] = ['label' => 'Trang nội dung', 'capabilities' => array_keys(static::page())];

        foreach (static::postTypes() as $key => $postType)
        {
            $core['post_'.$key] = ['label' => $postType['label'], 'capabilities' => array_keys($postType['capabilities'])];
        }

        foreach (static::categories() as $key => $category)
        {
            $core['category_'.$key] = ['label' => $category['label'], 'capabilities' => array_keys($category['capabilities'])];
        }

        $core['gallery'] = ['label' => 'Thư viện ảnh', 'capabilities' => array_keys(static::gallery())];

        $core['builder'] = ['label' => 'Builder', 'capabilities' => array_keys(static::builder())];

        $core['theme'] = ['label' => 'Theme', 'capabilities' => array_keys(static::theme())];

        $core['themeStyle'] = ['label' => 'Giao diện', 'capabilities' => array_keys(static::themeStyle())];

        $core['setting'] = ['label' => 'Hệ thống', 'capabilities' => array_keys(static::setting())];

        $core['plugin'] = ['label' => 'Plugin', 'capabilities' => array_keys(static::plugin())];

        $core['user'] = ['label' => 'Thành viên', 'capabilities' => array_keys(static::user())];

        // Nhóm core đứng đầu, nhóm plugin theo sau
        return array_merge($core, is_array($group) ? $group : []);
    }

    static public function label($label): array
    {
        $core = array_merge(
            static::general(),
            static::page(),
            static::gallery(),
            static::builder(),
            static::theme(),
            static::themeStyle(),
            static::setting(),
            static::plugin(),
            static::user()
        );

        foreach (static::postTypes() as $postType)
        {
            $core = array_merge($core, $postType['capabilities']);
        }

        foreach (static::categories() as $category)
        {
            $core = array_merge($core, $category['capabilities']);
        }

        return array_merge($core, is_array($label) ? $label : []);
    }

    static public function general(): array
    {
        $label['loggin_admin'] = 'Đăng nhập admin';
        return apply_filters('skd_admin_capabilities_general', $label);
    }

    static public function page(): array
    {
        $label['view_pages']   = 'Xem';
        $label['add_pages']    = 'Thêm';
        $label['edit_pages']   = 'Sửa';
        $label['delete_pages'] = 'Xóa';
        return apply_filters('skd_admin_capabilities_page', $label);
    }

    static public function gallery(): array
    {
        $label['edit_gallery']   = 'Quản lý thư viện';
        $label['delete_gallery'] = 'Xóa';
        return apply_filters('skd_admin_capabilities_gallery', $label);
    }

    static public function builder(): array
    {
        $label['builder'] = 'Builder';
        return apply_filters('skd_admin_capabilities_builder', $label);
    }

    static public function theme(): array
    {
        $label['edit_themes']    = 'Giao diện';
        $label['switch_themes']  = 'Thay đổi theme';
        $label['update_themes']  = 'Cập nhật theme';
        $label['delete_themes']  = 'Xóa theme';
        $label['install_themes'] = 'Cài đặt theme';
        return apply_filters('skd_admin_capabilities_theme', $label);
    }

    static public function themeStyle(): array
    {
        $label['edit_theme_options'] = 'Cấu hình';
        $label['edit_theme_menus']   = 'Menu';
        $label['edit_theme_widgets'] = 'Widget';
        return apply_filters('skd_admin_capabilities_theme_style', $label);
    }

    static public function setting(): array
    {
        $label['edit_setting']         = 'Hệ thống';
        $label['edit_smtp']            = 'Cấu hình SMTP';
        $label['edit_cms_status']      = 'Cấu hình trạng thái hệ thống';
        $label['edit_setting_cache']   = 'Quản lý cache';
        $label['edit_setting_audit']   = 'Quản lý nhật ký hoạt động';
        $label['edit_setting_tinymce'] = 'Cấu hình trình soạn thảo';
        return apply_filters('skd_admin_capabilities_setting', $label);
    }

    static public function plugin(): array
    {
        $label['edit_plugins']     = 'Quản lý';
        $label['install_plugins']  = 'Cài đặt';
        $label['update_plugins']   = 'Cập nhật';
        $label['activate_plugins'] = 'Kích hoạt';
        $label['delete_plugins']   = 'Xóa';
        return apply_filters('skd_admin_capabilities_plugin', $label);
    }

    static public function user(): array
    {
        $label['list_users']   = 'Xem danh sách';
        $label['create_users'] = 'Thêm mới';
        $label['edit_users']   = 'Cập nhật';
        $label['remove_users'] = 'Xóa tạm';
        $label['delete_users'] = 'Xóa vĩnh viễn';
        return apply_filters('skd_admin_capabilities_user', $label);
    }

    /**
     * Post type đã đăng ký → [key => ['label' => ..., 'capabilities' => [cap => nhãn]]]
     */
    static public function postTypes(): array
    {
        $actions = ['view' => 'Xem', 'add' => 'Thêm', 'edit' => 'Sửa', 'delete' => 'Xóa'];

        return static::fromTaxonomy(Taxonomy::getPost(), $actions, '');
    }

    /**
     * Danh mục đã đăng ký → cùng cấu trúc với postTypes()
     */
    static public function categories(): array
    {
        $actions = ['view' => 'Xem', 'add' => 'Thêm', 'edit' => 'Thêm & sửa', 'delete' => 'Xóa'];

        return static::fromTaxonomy(Taxonomy::getCategory(), $actions, 'Danh mục: ');
    }

    static protected function fromTaxonomy($items, array $actions, string $prefix): array
    {
        $result = [];

        if (!is_array($items)) return $result;

        foreach ($items as $key => $item)
        {
            if (empty($item['capabilities']) || !is_array($item['capabilities'])) continue;

            $capabilities = [];

            foreach ($item['capabilities'] as $action => $capability)
            {
                if (empty($capability) || !is_string($capability)) continue;

                $capabilities[$capability] = $actions[$action] ?? ucfirst((string)$action);
            }

            if (empty($capabilities)) continue;

            $name = $item['labels']['name'] ?? $key;

            // Danh mục: ghép tên post type sở hữu ("Danh mục" → "Danh mục: Bài viết")
            if ($prefix !== '' && !empty($item['post_type']) && is_string($item['post_type']))
            {
                $postType = Taxonomy::getPost($item['post_type']);

                if (!empty($postType['labels']['name'])) $name = $postType['labels']['name'];
            }

            $result[$key] = [
                'label'        => $prefix.$name,
                'capabilities' => $capabilities,
            ];
        }

        return $result;
    }
}

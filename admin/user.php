<?php
class AdminRoleUser {
    static function onlyRoot() {
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

    static function columnHeader($column) {
        $column['role'] = [
            'label' => trans('user.role'),
            'column'=> fn($item, $args) => \SkillDo\Table\Columns\ColumnView::make('role', $item, $args)
                ->value(fn($item) => Role::get($item->role)->getName())
                ->html(function (\SkillDo\Table\Columns\ColumnView $column) {
                    echo '<span class="js_btn_user_role" data-id="'.$column->item->id.'" data-role="'.$column->item->role.'">'.$column->value.'</span>';
                })
        ];
        return $column;
    }

    static function modelRole(): void
    {
        if(Template::isPage('users_index')) {

            $roles = Role::make()->all();

            $roleOptions = [];

            foreach ($roles as $role) {
                $roleOptions[$role->getKey()] = $role->getName();
            }

            Plugin::view('user-role-editor', 'user-model', [
                'roleOptions' => $roleOptions
            ]);
        }

    }

    //Thêm tab vào admin > user > detail
    static function registerTab($args) {
        if(Auth::hasCap('role_editor_user') ) {
            $args['role'] = [
                'label' => trans('role.title'),
                'callback' => 'AdminRoleUser::tab'
            ];
        }
        return $args;
    }

    static function tab($user): void
    {
        $roles = Role::make()->all();

        $roleCurrent = User::getCap($user->id);

        $roleDefault   = Role::get($user->role)->getCapabilities();

        $roleLabel = RoleEditor::label();

        $roleGroup = RoleEditor::group();

        if(!Admin::isRoot() && $roles['root']) {

            unset($roles['root']);

            $roleOnlyRoot = RoleEditor::onlyRoot();

            foreach ($roleGroup as $groupKey => $groupValue) {

                foreach ($groupValue['capabilities'] as $capabilityKey => $capability) {
                    if(in_array($capability, $roleOnlyRoot) !== false) {
                        unset($roleGroup[$groupKey]['capabilities'][$capabilityKey]);
                    }
                }

                if(!have_posts($roleGroup[$groupKey]['capabilities'])) {
                    unset($roleGroup[$groupKey]);
                }
            }
        }

        $form = form();

        $roleOption = [];

        foreach ($roles as $roleKey => $role) {
            $roleOption[$roleKey] = $role->getName();
        }

        $form->radio('role_name', $roleOption, [], $user->role);

        Plugin::view('user-role-editor', 'user-tab', [
            'form'      => $form,
            'roles'     => $roles,
            'roleGroup' => $roleGroup,
            'roleLabel' => $roleLabel,
            'roleCurrent' => $roleCurrent,
            'roleDefault' => $roleDefault,
            'user' => $user
        ]);
    }
}

add_filter('admin_my_action_links', 'AdminRoleUser::registerTab');
add_filter('manage_user_columns', 'AdminRoleUser::columnHeader');
add_action('admin_footer', 'AdminRoleUser::modelRole');
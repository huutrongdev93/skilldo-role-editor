<?php
class AdminRole {
    static function registerSystem($tabs) {
        if(Auth::hasCap('role_editor')) {
            $tabs['role'] = [
                'label' => trans('role.title'),
                'description' => trans('role.system.description'),
                'callback' => 'AdminRole::render',
                'icon' => '<i class="fa-duotone fa-user-lock"></i>',
                'form' => false,
            ];
        }
        return $tabs;
    }

    static function render(\SkillDo\Http\Request $request): void
    {
        $roles = Role::make()->all();

        $roleNameDefault = (Admin::isRoot()) ? 'root' : 'administrator';

        $roleCurrentKey  = ($request->input('role') == '') ? $roleNameDefault : $request->input('role');

        $roleCurrentName  = '';

        $roleCurrent 	= Role::get($roleCurrentKey)->getCapabilities();

        $roleLabel = RoleEditor::label();

        $roleGroup = RoleEditor::group();

        if(!Admin::isRoot() && $roles['root']) {

            unset($roles['root']);

            $roleOnlyRoot = RoleEditor::onlyRoot();

            foreach ($roleGroup as $groupKey => $groupValue) {

                if($groupKey == $roleCurrentKey) $roleCurrentName = $groupValue['label'];

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

        foreach ($roles as $groupKey => $groupValue) {
            if($groupKey == $roleCurrentKey) {
                $roleCurrentName = $groupValue->getName();
                break;
            }
        }

        Plugin::view('user-role-editor', 'system', [
            'roleCurrentKey' => $roleCurrentKey,
            'roleCurrentName' => $roleCurrentName,
            'roles' => $roles,
            'roleGroup' => $roleGroup,
            'roleLabel' => $roleLabel,
            'roleCurrent' => $roleCurrent,
        ]);
    }
}
add_filter('skd_system_tab', 'AdminRole::registerSystem', 50);
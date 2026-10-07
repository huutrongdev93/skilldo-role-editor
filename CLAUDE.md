# CLAUDE.md — Plugin user-role-editor

Trang phân quyền `/admin/system/role` (tab `role` trong Cấu hình) + tab phân quyền riêng cho từng user.
Class chính `UserRoleEditor` (`index.php`), namespace `UserRoleEditor\*`. Version 3.0.2.

⚠ Chức vụ THẬT của một user nằm ở `users_metadata.capabilities` (`{"nhanvien":true}`), không phải cột
`users.role` — đổi cột `role` không đổi quyền.

## Chặn menu + trang theo quyền (3.0.2) — `app/Services/AdminAccessGuard.php`

Core v8 (WD-107): `AdminMenu::add()/addSub()` **bỏ qua** tham số `'role'`, và không còn ai gắn luật vào
filter `admin_permission_access` (layout admin vẫn gọi nó, false → hiện trang 404). Hệ quả: tài khoản
chỉ có `loggin_admin` vẫn thấy + mở được Trang, Thư viện, Giao diện, Hệ thống, Thành viên.

- `admin_navigation_data` → bỏ menu theo bảng `menuCaps()` (khoá `cha` hoặc `cha.con`); menu nhóm url
  dạng `system#…` (Marketing) hết menu con thì bỏ luôn.
- `admin_permission_access` → chặn theo **tên route** ở `routeCaps()`; luật là tên quyền hoặc callable
  (bài viết/danh mục đọc `capabilities` của post type/danh mục từ Taxonomy; sửa hồ sơ của chính mình luôn được).
- `admin_item_quick_access` → ẩn ô Truy cập nhanh ở Dashboard theo quyền; đổi link v7 `plugins?page=order` → `order`.
- Plugin khác thêm luật qua `role_editor_admin_menu_caps` / `role_editor_admin_route_caps` /
  `role_editor_quick_access_caps` (vd generate-form-register).
- Chưa soát: các **ajax** admin (lưu trang, lưu cấu hình…) — dispatcher core chỉ kiểm `loggin_admin`
  (`AjaxController`), từng handler có kiểm quyền riêng hay không thì chưa đo; và nút
  cứng trong widget "Hỗ trợ" của Dashboard (F6/F7/F8) vẫn hiện — bấm vào ra trang 404.

## Trang phân quyền hiện quyền nào

Chỉ những quyền có **nhóm** (`user_role_editor_group`) và **nhãn** (`user_role_editor_label`).
Quyền có trong `user_roles` nhưng không ai khai nhóm thì KHÔNG hiện — im lặng, không lỗi.

- Quyền của **core** (đăng nhập admin, trang, bài viết, danh mục, thư viện, theme, giao diện, hệ thống,
  plugin, thành viên): `app/Services/CoreRoleLabels.php`, gắn priority 1. v7 do core tự khai
  (`SKD_Admin_Role`); v8 bỏ đi nên trước 3.0.1 trang này chỉ còn quyền của plugin.
  Nhóm bài viết / danh mục dựng từ `Taxonomy::getPost()` / `getCategory()` → post type mới đăng ký
  kèm `capabilities` tự hiện ra.
- Quyền của **plugin**: mỗi plugin tự `add_filter` hai hook trên (vd `sicommerce`, `generate-form-register`,
  `skilldo/filemanager`). Callback phải là method **static** và class phải nằm trong namespace autoload
  của plugin — sai một trong hai là cả trang phân quyền sập (generate-form-register < 5.0.6).

`RoleEditorHelper::onlyRoot()` (filter `role_only_root`) = quyền chỉ root mới thấy/cấp.

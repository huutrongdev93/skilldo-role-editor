<form id="jsRoleForm" method="post" data-role-key="<?php echo $roleCurrentKey;?>" data-role-name="<?php echo $roleCurrentName;?>">
	<?php echo form_open();?>
	<div class="box">
        <?php echo Admin::loading();?>
		<div class="box-content">
			<div class="row m-2" style="overflow: hidden;">
				<label class="col-md-4">Chọn vai trò và tùy chỉnh khả năng làm việc của vai trò đó</label>
				<div class="col-md-4">
					<select name="role_name" class="form-control" required="required" id="jsRoleList">
						<?php foreach ($roles as $key => $name): ?>
						<option value="<?php echo $key;?>" <?php echo ($roleCurrentKey == $key )?'selected':'';?> ><?php echo $name;?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="col-md-4">
					<button type="button" class="btn btn-blue jsRole_btn_edit"><?php echo Admin::icon('edit');?></button>
				</div>
			</div>
			<hr>
            <div class="row m-2">
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-3 ure_caps_groups">
                            <ul id="ure_caps_groups_list">
                                <li id="ure_caps_group_all" class="active">Tất cả </li>
                                <?php foreach ($roleGroup as $key => $value): ?>
                                    <li id="ure_caps_group_<?php echo $key;?>" class=""><?php echo $value['label'];?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                        <div class="col-md-9 scroll scrollbar">
                            <?php foreach ($roleGroup as $key => $value): ?>
                                <div class="ure_caps_group ure_caps_group_<?php echo $key;?>">
                                    <h4 style="font-size: 18px;margin: 20px 0;"><?php echo $value['label'];?></h4>
                                    <?php foreach ($value['capabilities'] as $capabilities): ?>
                                        <?php if(!isset($roleLabel[$capabilities])) continue; ?>
                                        <div class="checkbox">
                                            <label>
	                                            <input type="checkbox" class="icheck" name="capabilities[<?php echo $capabilities;?>]" value="1" <?php echo (!empty($roleCurrent[$capabilities]))?'checked':'';?>>
	                                            <?php echo $roleLabel[$capabilities];?>
                                            </label>
                                        </div>
                                    <?php endforeach ?>
                                    <hr />
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="button-action">
                        <button type="submit" class="btn btn-green d-block" style="width: 100%;">Cập nhật</button>
                        <hr />
                        <button type="button" class="btn btn-white d-block" style="width: 100%;" data-fancybox data-src="#hidden-content">Thêm nhóm quyền</button>
                    </div>
                </div>
            </div>
		</div>
	</div>
</form>

<!-- popup thêm menu -->
<div style="display: none; padding:10px; min-width: 350px;" id="hidden-content">
    <h4 class="heading">Thêm nhóm quyền</h4>
	<hr />
    <form id="jsRole_form_add" autocomplete="off">
		<div class="form-group">
			<label for="">Tên vai trò</label>
			<input type="text" name="label" class="form-control" value="" required>
		</div>
        <div class="clearfix"></div>
        <div class="text-right">
            <button class="btn btn-green"><?php echo Admin::icon('save');?> Thêm</button>
        </div>
    </form>
</div>

<div class="modal fade" id="jsEditRoleName" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Chỉnh sữa tên vai trò</h4>
			</div>
			<form id="jsRole_form_edit" autocomplete="off">
				<div class="modal-body">
					<div class="form-group">
						<label for="">Tên vai trò</label>
						<input type="text" name="roleName" class="form-control" value="" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">Hủy</button>
					<button class="btn btn-green"><?php echo Admin::icon('save');?> Cập nhật</button>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
	.button-action {
		background-color: #F5F5F5;
		padding:10px;
		border-radius: 5px;
		border:1px solid #999;
	}
	.scroll { height: 400px; overflow: auto; border-left: 1px solid #ccc; }
	.ure_caps_groups ul li{
		padding:5px; cursor: pointer;
	}
	.ure_caps_groups ul li.active {
		background-color: #ccc;
	}
    .action-bar button[type="submit"] { display:none;}
</style>

<script defer>
  	$(function() {

	    let form = $('#jsRoleForm');

	    let formEdit = $('#jsEditRoleName');

	    const modalEdit = new bootstrap.Modal('#jsEditRoleName')

      	$('#ure_caps_groups_list li').click(function() {

      		let id = $(this).attr('id');

      		$('#ure_caps_groups_list li').removeClass('active');

      		$(this).addClass('active');

      		if( id === 'ure_caps_group_all' ) {

      			$('.ure_caps_group').show();
      		}
      		else {

      			$('.ure_caps_group').hide();

      			$('.'+id).show();
      		}
      	});

	    $(document).on('change', '#jsRoleList', function(event) {
		    window.location ="admin/system/role?role="+$(this).val()+"";
		    return true;
	    });

		$(document).on('submit', '#jsRole_form_add', function(event) {

			let data 		= $(this).serializeJSON();

			data.action     =  'AdminRoleAjax::add';

			$.post(ajax, data, function(data) {}, 'json').done(function( data ) {
				show_message(data.message, data.status);
				if(data.status === 'success') {
					window.location ="admin/system/role?role="+data.role+"";
				}
			});

			return false;
		});

	    $(document).on('click', '.jsRole_btn_edit', function(event) {
		    formEdit.find('input').val(form.attr('data-role-name'));
		    modalEdit.show();
		    return false;
	    });

	    $(document).on('submit', '#jsRole_form_edit', function(event) {

		    let data 		= $(this).serializeJSON();

		    data.action     =  'AdminRoleAjax::edit';

		    data.roleKey     =  form.attr('data-role-key');

		    $.post(ajax, data, function(data) {}, 'json').done(function( data ) {
			    show_message(data.message, data.status);
			    if(data.status === 'success') {
				    form.attr('data-role-name', data.name);
					$('#jsRoleList option:selected').html(data.name);
				    modalEdit.hide();
			    }
		    });

		    return false;
	    });

	    $(document).on('submit', '#jsRoleForm', function(event) {

		    let loading = $(this).find('.loading');

		    let data 		= $(this).serializeJSON();

		    data.action     =  'AdminRoleAjax::save';

		    loading.show();

		    $.post(ajax, data, function() {}, 'json').done(function( data ) {
			    loading.hide();
			    show_message(data.message, data.status);
		    });

		    return false;

	    });
  	});
</script>
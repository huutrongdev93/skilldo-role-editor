<form id="jsRoleForm" method="post" data-role-key="{!! $roleCurrentKey !!}" data-role-name="{!! $roleCurrentName !!}">
	<div class="box">
        {!! Admin::loading() !!}
		<div class="box-header">
			<div class="row align-items-center">
				<label class="col-md-3">{!! trans('role.system.detail.description') !!}</label>
				<div class="col-md-3">
					<select name="role_name" class="form-control" required="required" id="jsRoleList">
						@foreach ($roles as $key => $role)
							<option value="{!! $key !!}" {{ ($roleCurrentKey == $key ) ? 'selected' : '' }} >
								{!! $role->getName() !!}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					@if(\Auth::hasCap('role_update'))
						{!! Admin::button('blue', ['icon' => Admin::icon('edit'), 'class' => 'jsRole_btn_edit']) !!}
					@endif
					@if(\Auth::hasCap('role_delete'))
						{!! Admin::btnConfirm([
							'icon' => Admin::icon('delete'),
							'class' => 'jsRole_btn_delete',
							'ajax' => 'AdminRoleAjax::delete',
							'module' => 'role',
							'heading' => 'Xóa chức vụ',
							'trash' => 'disable',
							'id' => $roleCurrentKey,
						]) !!}
					@endif
				</div>
				<div class="col-md-3 button-action">
					@if(\Auth::hasCap('role_add'))
					{!! Admin::button('green', ['icon' => Admin::icon('add'), 'text' => trans('role.btn.add'), 'modal' => 'jsAddRole']) !!}
					@endif
				</div>
			</div>
		</div>
		<div class="box-content">
			<div class="role-search text-center mb-3">
				<input type="text" class="form-control js_input_role_search" placeholder="{!! trans('role.search') !!}">
			</div>
			<div class="scroll">
				@foreach ($roleGroup as $key => $value)
					<div class="box mb-3 box-role-item">
						<div class="box-header"><div class="box-title" data-title="{!! Str::ascii($value['label']) !!}">{!! $value['label'] !!}</div></div>
						<div class="box-content box-capabilities">
							<div class="capabilities row">
								@foreach ($value['capabilities'] as $capabilities)
									@if(!isset($roleLabel[$capabilities]))
										@continue
									@endif
									<div class="form-check col-md-3">
										<label>
											<input type="checkbox" class="form-check-input" name="capabilities[{!! $capabilities !!}]" value="1" {{ (!empty($roleCurrent[$capabilities]))?'checked':'' }}>
											<span class="bg-primary-lt">{!! RoleEditor::highlightKeyword($roleLabel[$capabilities]) !!}</span>
										</label>
									</div>
								@endforeach
							</div>
						</div>
					</div>
				@endforeach
			</div>
		</div>
		<div class="box-footer">
			{!! Admin::button('blue', ['icon' => Admin::icon('edit'), 'text' => trans('button.save'), 'type' => 'submit' ]) !!}
		</div>
	</div>
</form>

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
						<label for="">{{ trans('role.name') }}</label>
						<input type="text" name="roleName" class="form-control" value="" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">{!! trans('button.cancel') !!}</button>
					<button class="btn btn-green">{!! Admin::icon('save') !!} {!! trans('button.update') !!}</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="jsAddRole" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">{{ trans('role.btn.add') }}</h4>
			</div>
			<form id="jsRole_form_add" autocomplete="off">
				<div class="modal-body">
					<div class="form-group">
						<label for="">{{ trans('role.name') }}</label>
						<input type="text" name="label" class="form-control" value="" required>
					</div>
				</div>
				<div class="modal-footer">
					{!! Admin::button('white', ['text' => trans('button.cancel'), 'data-bs-dismiss' => 'modal']) !!}
					{!! Admin::button('blue', ['icon' => Admin::icon('save'), 'text' => trans('button.save'), 'type' => 'submit']) !!}
				</div>
			</form>
		</div>
	</div>
</div>

<style>
	.scroll { height: 600px; overflow: auto; }
	.capabilities {
	}
	.bg-primary-lt {
		--bs-bg-opacity: 1;
		--bs-text-opacity: 1;
		color: rgba(var(--bs-primary-rgb), var(--bs-text-opacity)) !important;
	}
	.box-capabilities {
		background-color:#cfe2ff54;
	}
</style>

<script defer>
  	$(function() {

	    let form = $('#jsRoleForm');

	    let formEdit = $('#jsEditRoleName');

	    const modalEdit = new bootstrap.Modal('#jsEditRoleName')

		$(document).on('keyup', '.js_input_role_search', function(event) {

			let keyword = $(this).val().toLowerCase();

			$('.box-role-item').hide();

			$('.box-role-item .box-header .box-title').each(function(){
				if($(this).text().toLowerCase().indexOf(""+keyword+"") !== -1 ){
					$(this).closest('.box-role-item').show();
				}
				if($(this).data('title').toLowerCase().indexOf(""+keyword+"") !== -1 ){
					$(this).closest('.box-role-item').show();
				}
			});
			return false;
		});

	    $(document).on('change', '#jsRoleList', function(event) {
		    window.location ="admin/system/role?role="+$(this).val()+"";
		    return true;
	    });

		$(document).on('submit', '#jsRole_form_add', function(event) {

			let data 		= $(this).serializeJSON();

			data.action     =  'AdminRoleAjax::add';

			request.post(ajax, data).then(function( response ) {

				SkilldoMessage.response(response);

				if(response.status === 'success') {
					window.location ="admin/system/role?role=" + response.data + "";
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

			request.post(ajax, data).then(function( response ) {

				SkilldoMessage.response(response);

			    if(response.status === 'success') {

				    form.attr('data-role-name', response.data);

					$('#jsRoleList option:selected').html(response.data);

				    modalEdit.hide();
			    }
		    });

		    return false;
	    });

	    $(document).on('submit', '#jsRoleForm', function(event) {

		    let loading = $(this).find('.loading');

		    let data = $(this).serializeJSON();

		    data.action  =  'AdminRoleAjax::save';

		    loading.show();

			request.post(ajax, data).then(function(response) {

			    loading.hide();

				SkilldoMessage.response(response);
		    });

		    return false;
	    });
  	});
</script>
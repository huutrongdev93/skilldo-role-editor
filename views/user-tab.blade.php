<form action="" method="post" id="js_form_user_role">
    <div class="box">
        <div class="box-content">
            {!! Admin::loading() !!}
            <div class="row">
                <div class="col-md-3"><label>Quyền</label></div>
                <div class="col-md-7"><label>Khả năng</label></div>
                <div class="col-md-2"></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    {!! $form->html() !!}
                    <hr />
                    <div class="button-action">
                        <input type="hidden" name="user_id" value="{!! $user->id !!}">
                        {!! Admin::button('blue', ['icon' => Admin::icon('save'), 'text' => trans('button.save'), 'type' => 'submit', 'class' => 'w-full']) !!}
                    </div>
                </div>
                <div class="col-md-9">
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
                                                    <input type="checkbox"
                                                           class="form-check-input capability-input"
                                                           name="capabilities[{!! $capabilities !!}]"
                                                           value="1"
                                                            {{ (!empty($roleCurrent[$capabilities])) ? 'checked' : '' }}
                                                            {{ (!empty($roleDefault[$capabilities])) ? 'disabled' : '' }}
                                                    >
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
            </div>
        </div>
    </div>
</form>

<style>
    .scroll { height: 450px; overflow: auto; }
</style>


<script defer>
  	$(function() {

        let loading = $('.loading');

		$(document).on('change', 'input[name="role_name"]', function(){

			loading.show();

			let data = {
				action 	: 'AdminRoleAjax::userLoadCapabilities',
				role_name	: $(this).val(),
				user_id	: $('input[name="user_id"]').val()
			}

			request.post(ajax, data).then(function(response) {

				loading.hide();

				let capability = $('.box-capabilities .capability-input');

				capability.prop('checked', false);

				capability.attr('disabled', false);

				if(Object.keys(response.data.roleChecked).length !== 0) {

					for (const [capability, value] of Object.entries(response.data.roleChecked)) {

						$('.box-capabilities .capability-input[name="capabilities['+capability+']"]').prop('checked', true);
					}
				}

				if(Object.keys(response.data.roleDisabled).length !== 0) {

					for (const [capability, value] of Object.entries(response.data.roleDisabled)) {

						$('.box-capabilities .capability-input[name="capabilities['+capability+']"]').attr('disabled', true);
					}
				}
			});
		});

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

	    $(document).on('submit', '#js_form_user_role', function(event) {

		    loading.show();

		    let data 		= $(this).serializeJSON();

		    data.action     =  'AdminRoleAjax::userSave';

		    request.post(ajax, data).then(function( response ) {

			    SkilldoMessage.response(response);

			    loading.hide();
		    });

		    return false;
	    });
  	});
</script>
<!-- Modal -->
<div class="modal fade" id="modelUserRole" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thay đổi chức vụ thành viên</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! SkillDo\Form\Form::select2('userRole', $roleOptions, [
                    'label' => 'Chức vụ thành viên'
                ]) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">{{trans('button.close')}}</button>
                <button type="button" class="btn btn-blue js_btn_user_role_save">{{trans('button.save')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
		
	    let userId = 0;

	    let userModelRoleHandler;

	    let userModelRole;

	    function showRoleModal(element) {

		    if(userModelRoleHandler === undefined) {

			    userModelRole          = $('#modelUserRole');

			    userModelRoleHandler   = new bootstrap.Modal('#modelUserRole', {backdrop: "static", keyboard: false})
		    }

		    userId = element.attr('data-id');

		    let status = element.attr('data-role');

		    userModelRole.find('select[name="userRole"]').val(status).trigger('change')

		    userModelRoleHandler.show();
	    }

	    function changeRole(element) {

		    let loading = SkilldoUtil.buttonLoading(element)

		    let data = {
			    action: 'AdminRoleAjax::userChangeRole',
			    role: userModelRole.find('select[name="userRole"]').val(),
			    id: userId
		    }

		    loading.start()

		    request
			    .post(ajax, data)
			    .then(function (response) {

				    SkilldoMessage.response(response);

				    loading.stop();

				    if(response.status === 'success') {

					    $('.tr_' + response.data.id).find('.column-role').html(`<span class="js_btn_user_role" data-id="${response.data.id}" data-role="${response.data.key}">${response.data.name}</span>`);

					    userModelRoleHandler.hide();
				    }
			    })
			    .catch(function (error) {
				    loading.stop();
			    });

		    return false;
	    }

	    $(document)
		    .on('click', '.js_btn_user_role', function () { return showRoleModal($(this)) } )
		    .on('click', '.js_btn_user_role_save', function () { return changeRole($(this)) } )
		
    })
</script>

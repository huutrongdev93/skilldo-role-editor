<div class="clearfix"></div>

<div class="ui-layout">
	<form action="" method="post" id="form-action">
		<div class="box">
			<div class="box-content">
			
				<div id="ajax_item_save_loader" class="ajax-load-qa">&nbsp;</div>
				<?php echo form_open();?>
                <div class="row m-2">
                    <div class="col-md-3 pd-t-10"><label>Quyền</label></div>
                    <div class="col-md-7 border-lt-rg pd-t-10"><label>Khả năng</label></div>
                    <div class="col-md-2 pd-t-10"></div>
                </div>
				
				<div class="clearfix"> </div>
				
				<hr style="margin: 0;">
                <div class="row m-2">
                    <div class="col-md-3 pd-t-10">
                        <?php foreach ($role as $key => $name): ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="role_name" value="<?php echo $key;?>" <?php echo ($role_name == $key )?'checked':'';?> class="icheck">
                                <?php echo $name;?>
                            </label>
                        </div>
                        <br />
                        <?php endforeach ?>

                    </div>
                    <div class="col-md-7 border-lt-rg pd-t-10" style="max-height:500px; overflow:auto;">
                        <div class="row" id="capabilities_content">
                            <?php foreach ( $role_group as $key => $value): if(!have_posts($value['capabilities'])) continue; ?>
                                <div class="col-md-12">
                                    <h5 style="margin: 20px 0"><?php echo $value['label'];?></h5>
                                    <?php foreach ($value['capabilities'] as $capbilitie): ?>
                                        <?php if(!isset($role_all[$capbilitie])) continue; ?>
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <label> <input type="checkbox" class="<?php echo (!empty($role_default[$capbilitie]))?'icheckdisable':'icheck';?>" name="capabilities[<?php echo $capbilitie;?>]" value="1" <?php echo (!empty($role_current[$capbilitie]))?'checked':'';?>> <?php echo $role_label[$capbilitie];?> </label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>

                                    <div class="clearfix"></div>
                                    <hr />
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="col-md-2 pd-t-10">
                        <div class="button-action">
                            <input type="hidden" name="user_id" value="<?php echo $user->id;?>">
                            <button type="submit" class="btn btn-red d-block">Cập nhật</button>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</form>
</div>

<style type="text/css">
	.border-lt-rg {
		border-left: 1px solid #ccc; border-right: 1px solid #ccc;
	}
	.pd-t-10 { padding-top:10px; }
</style>

<script defer>
  	$(function() {

  		$('input.icheckdisable').iCheck({
	        checkboxClass: 'icheckbox_square-blue',
	        radioClass: 'iradio_square-blue',
	        increaseArea: '20%' // optional
	    });

	    $('input.icheckdisable').iCheck('disable');

		$(document).on('ifChecked', 'input[name="role_name"]', function(){

			$.post(ajax, {
				'action' 	: 'AdminRoleAjax::userLoadCapabilities',
				'role_name'	: $(this).val(),
				'user_id'	: $('input[name="user_id"]').val()
			}, function() {}, 'json').done(function( data ) {
				$('#capabilities_content').html(data.content);

				checkbox_style();

				$('input.icheckdisable').iCheck({
					checkboxClass: 'icheckbox_square-blue',
					radioClass: 'iradio_square-blue',
					increaseArea: '20%' // optional
				});

				$('input.icheckdisable').iCheck('disable');
			});
		});

		$('#form-action').submit(function() {

      		let loading = $(this).find('.loading');

			let data 		= $(this).serializeJSON();

			data.action     =  'AdminRoleAjax::userSave';

			loading.show();

			$.post(ajax, data, function() {}, 'json').done(function( data ) {
				loading.hide();
	  			show_message(data.message, data.status);
			});

			return false;

		});
  	});
</script>
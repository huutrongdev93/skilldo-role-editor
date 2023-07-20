<div class="box">
    <div class="box-content">
        <div class="ui-layout__title row m-1"><h2 class="heading">Vai trò</h2></div>
        <hr />
        <div class="row m-1">
            <div class="form-group col-md-12" id="box_city">
                <div class="group">
                    <select name="role" class="form-control " id="city">
                        <option value="0">Chọn vai trò</option>
                        <?php foreach ($roles as $key => $name) { ?>
                            <option value="<?php echo $key;?>"><?php echo $name;?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
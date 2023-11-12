<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
?>
<div id="wrapper" style="background:fff">
    <div class="content">
        <div class="row ">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="customer-profile-group-heading">Branch</h4>

                        <?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'branches-form', 'class' => isset($tab['update_url']) ? 'custom-update-url' : '']); ?>

                        <div class="row ">
                            <div class="col-md-6">


                                <?php $attrs = (isset($branch) ? array() : array('autofocus' => true)); ?>
                                <div class="col-md-12">
                                    <?php $logo = @$branch->logo; ?>

                                    <?php if ($logo != '') { ?>
                                        <div class="row">
                                            <div class="col-md-9">
                                                <img width="100" height="100" src="<?php echo $logo; ?>" class="img img-responsive">
                                            </div>
                                            <?php if (has_permission('branches', '', 'delete')) { ?>
                                                <div class="col-md-3 text-right">
                                                    <a href="<?php echo admin_url('branches/remove_logo/' . @$branch->id . '/logo'); ?>" data-toggle="tooltip" title="<?php echo _l('branches_general_company_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="clearfix"></div>
                                    <?php } else { ?>
                                        <div class="form-group">
                                            <label for="logo" class="control-label"><?php echo _l('branches_general_logo'); ?></label>
                                            <input type="file" name="logo" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('branches_general_logo_tooltip'); ?>">
                                        </div>
                                    <?php } ?>
                                    <br>
                                    <?php echo render_input('branch', 'branches_branch', @$branch->branch, 'text'); ?>

                                    <?php echo render_input('invoice_prefix', 'branches_prefix', @$branch->invoice_prefix); ?>
                                    <?php echo render_input('invoice_postfix', 'branches_postfix', @$branch->invoice_postfix); ?>
                                    <?php echo render_input('estimate_prefix', 'estimate_prefix', @$branch->estimate_prefix); ?>
                                    <?php echo render_input('estimate_postfix', 'estimate_postfix', @$branch->estimate_postfix); ?>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group select-placeholder>">
                                                <label for="user"><?php echo _l('task_single_user'); ?></label>
                                                <select name="user[]" id="user" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple data-live-search="true">
                                                    <?php $assign_staffs = json_decode($branch->user);
                                                    if (!is_array($assign_staffs)) {
                                                        $assign_staffs = [];
                                                    }
                                                    foreach ($members as $member) { ?>
                                                        <option value="<?php echo $member['staffid']; ?>" <?php if (in_array($member['staffid'], $assign_staffs)) {
                                                                                                                echo 'selected';
                                                                                                            } ?>>
                                                            <?php echo $member['firstname'] . ' ' .  $member['lastname']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="col-md-6">

                                <?php $value=( isset($branch->branch_street) ? $branch->branch_street : ''); ?>
                                <?php echo render_textarea( 'branch_street', 'branch_street',$value); ?>
                                <?php $value=( isset($branch->branch_city) ? $branch->branch_city : ''); ?>
                                <?php echo render_input( 'branch_city', 'branch_city',$value); ?>
                                <?php $value=( isset($branch->branch_state) ? $branch->branch_state : ''); ?>
                                <?php echo render_input( 'branch_state', 'branch_state',$value); ?>
                                <?php $value=( isset($branch->branch_zip) ? $branch->branch_zip : ''); ?>
                                <?php echo render_input( 'branch_zip', 'branch_zip',$value); ?>
                                <?php $selected=( isset($branch->branch_country) ? $branch->branch_country : '' ); ?>
                                <?php $countries= get_all_countries(); echo render_select( 'branch_country',$countries,array( 'country_id',array( 'short_name')), 'branch_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>

                            </div>
                        </div>

                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>

</script>
</body>

</html>
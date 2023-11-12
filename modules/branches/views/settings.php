<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" style="background:fff">
    <div class="content">
        <div class="row">
            <?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'branches-form', 'class' => isset($tab['update_url']) ? 'custom-update-url' : '']); ?>

            <?php $attrs = (isset($branch) ? array() : array('autofocus' => true)); ?>
            <div class="col-md-4 ">
            </div>
            <div class="col-md-4 ">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="" class="control-label"><?php echo _l('branch_active_module'); ?></label>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_invoices" <?php if(get_option('is_active_branch_in_invoices') == 1)echo "checked";?> id="dcp_1" value="">
                                <label for="dcp_1">Invoices</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_estimates" <?php if(get_option('is_active_branch_in_estimates'))echo "checked";?> id="dcp_2" value="">
                                <label for="dcp_2">Estimates</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_potencial_customers" <?php if(get_option('is_active_branch_in_potencial_customers'))echo "checked";?> id="dcp_3" value="">
                                <label for="dcp_3">Potencial customers</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_proposals" <?php if(get_option('is_active_branch_in_proposals'))echo "checked";?> id="dcp_4" value="">
                                <label for="dcp_4">Proposals</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_customers" <?php if(get_option('is_active_branch_in_customers'))echo "checked";?> id="dcp_5" value="">
                                <label for="dcp_5">Customers</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_projects" <?php if(get_option('is_active_branch_in_projects'))echo "checked";?> id="dcp_6" value="">
                                <label for="dcp_6">Projects</label>
                            </div>
                            <!-- <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="is_active_branch_in_invoice" <?php if(get_option('is_active_branch_in_projects'))echo "checked";?> id="dcp_7" value="7">
                                <label for="dcp_7">Customers</label>
                            </div> -->
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                    </div>

                </div>

            </div>
            <div class="col-md-4 ">
            </div>

            <?php echo form_close(); ?>


        </div>
    </div>
</div>
<?php init_tail(); ?>

</body>

</html>
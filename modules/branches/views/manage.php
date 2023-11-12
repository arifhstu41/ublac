<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons">
              <a href="<?php echo admin_url('branches/branch'); ?>" class="btn btn-info pull-left"><?php echo _l('new_branches'); ?></a>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <?php hooks()->do_action('forms_table_start'); ?>
            <div class="clearfix"></div>
            <?php render_datatable(array(
              _l('branches_branch'),
              _l('branches_user'),
              _l('branches_logo'),
              _l('options'),
            ), 'branches'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    initDataTable('.table-branches', window.location.href);
  });
</script>
</body>

</html>
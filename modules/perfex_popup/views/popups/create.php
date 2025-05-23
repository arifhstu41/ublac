<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h3 class="text-center mbot30"><?php echo _l('choose_a_template'); ?></h3>
                        <div class="row row_blog_responsive">
                            <?php foreach($templates as $item){ ?>
                            <div class="col-md-4 itembb">
                                <div class="clearfix blog-bottom blog blogitemlarge">
                                    <a href="#" class="image-blog date clearfix">
                                        <img src="<?php echo base_url(PERFEX_POPUP_UPLOAD_PATH.'/popup_thumb_templates/'. $item->thumbnail); ?>" width="200" class="approve_child_top">
                                    </a>
                                    <div class="content_blog clearfixflex flex-column flex-lg-row">
                                        <div class="name-template">
                                            <?php echo $item->name; ?>
                                        </div>
                                        <div class="_buttons">
                                            <a href="#" class="btn btn-xs btn-success mright5 test pull-left display-block btn_builder_template" data-id="<?php echo $item->id; ?>" data-toggle="modal" data-target="#createModal">
                                            <?php echo _l('select'); ?></a>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"><?php echo _l('new_popup'); ?></h5>
    </div>
    <?php echo form_open(admin_url('perfex_popup/popups/save'),array('id'=>'form_save_perfex_popup')); ?>             
        <div class="modal-body">
            <div class="form-group">
              <input type="number" class="form-control" name="template_id" hidden required="" id="template_id_builder">
              <label for="name" class="col-form-label"><?php echo _l('name'); ?></label>
              <input type="text" class="form-control" name="name" required="" id="page-name">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-xs btn-primary" id="saveandbuilder"><?php echo _l('save_builder'); ?></button>
        </div>
    <?php echo form_close(); ?> 
  </div>
</div>

<?php init_tail(); ?>

<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/create.js'); ?>"></script>
</body>
</html>
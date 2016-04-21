<div class="center">
    <div class="col-md-8 cred-lockup">
        <h1 style="text-align: center;">Create a Job</h1>

        <?php if(validation_errors() !='') { ?>
            <div class="alert alert-danger"><strong>Error!</strong> <?php echo validation_errors(); ?> </div>
        <?php }?>
        <?php $message = $this->session->userdata('frontMsg'); ?>
        <?php if($message !='') { 
            $this->session->set_userdata('frontMsg','');
            ?>
          <div class="alert alert-success">
              <?php echo $message; ?>
          </div>
        <?php }?>  
            <form role="form" method="post" action="" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-3 control-label">Topic:</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="ID_text" name="topic" value="<?php echo (isset($_POST['topic']) and $_POST['topic'] !='') ? set_value('topic') : '';?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Template:</label>
                <div class="col-sm-6">
                    <?php echo getStandardBlogPost((isset($_POST['format_id'])) ? $_POST['format_id'] : '');?>
                </div>
            </div>
            <div class="form-group" id="formfieldsplace">
                <?php echo $fields;?>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Guideline:</label>
                <div class="col-sm-6">
                    <?php echo getListGuidelineIds((isset($_POST['guideline_ids'])) ? $_POST['guideline_ids'] : '');?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Industries:</label>
                <div class="col-sm-6">
                    <?php echo getListIndustryIds((isset($_POST['industry_ids'])) ? $_POST['industry_ids'] : '');?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Delivery:</label>
                <div class="col-sm-6">
                    <?php echo delivery((isset($_POST['delivery'])) ? $_POST['delivery'] : '');?>
                </div>
            </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="Save Changes" class="submit-btn">Submit</button>
                    </div>
                </div>
            
        </form>
    </div>
</div>
<script>
    function getFormFields(id) {
            jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo site_url('scripted_template_fields');?>',
                    data: 'form_id='+id+'&action=get_form_fields',
                    success: function(data) {
                        jQuery('#formfieldsplace').html(data);

                    }
                });
       }
</script>
<style>
    ul {
        padding-left: 0px;
        list-style: none;
    }
</style>
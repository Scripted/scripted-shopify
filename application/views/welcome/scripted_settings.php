<div class="center">
    <div class="col-md-6 cred-lockup">
    <h1>Scripted Account Settings</h1>
    <p>Authentication to the Scripted.com API now uses two factors: an API Identifier, and an API Secret Key. To get your identifier and key, please follow these two steps:</p>
    <ol>
	<li>Register as a business on Scripted.</li>
	<li>Log in and go to Account Settings in the top right, and then go to the API tab, or <a href="https://dashboard.scripted.com/business/account/api">click here</a>.</li>
	<li>Click on the "Show" button to get your API Secret Key, and copy them into our Shopify app!</li>
    </ol>
    <?php if(validation_errors() !='') { ?>
        <div class="alert alert-danger"><strong>Error!</strong> <?php echo validation_errors(); ?> </div>
    <?php }?>
    <?php $message = $this->session->userdata('frontMsg'); ?>
    <?php if($message !='') { 
        $this->session->set_userdata('frontMsg','');
        ?>
      <div class="alert alert-success">
          <strong>Success!</strong> <?php echo $message; ?>
      </div>
    <?php }?>  
        <form role="form" method="post" action="">
        <div class="form-group">
            <label for="ID_text">ID:</label>
            <input type="text" class="form-control" id="ID_text" name="ID_text" value="<?php echo (isset($_POST['ID_text']) and $_POST['ID_text'] !='') ? set_value('ID_text') : $ID;?>" />
        </div>
        <div class="form-group">
            <label for="success_tokent_text">Access Token:</label>
            <input type="text" class="form-control" id="success_tokent_text" name="success_tokent_text" value="<?php echo (isset($_POST['ID_text']) and $_POST['success_tokent_text']) ? set_value('success_tokent_text') : $scripted_access_token;?>" />
        </div>

        <button type="Save Changes" class="submit-btn">Submit</button>
    </form>
    </div>
</div>

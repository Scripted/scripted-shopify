<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="http://scripted.com/wp-content/themes/scripted/images/favicon.png">
        <title><?php echo (isset($title)) ? $title : 'AthletaIntl'; ?></title>
        <meta name="description" content="<?php echo (isset($meta_description)) ? $meta_description : ''; ?>">
        <meta name="keywords" content="<?php echo (isset($meta_keywords)) ? $meta_keywords : ''; ?>" />
        <meta name="author" content="<?php echo (isset($meta_author)) ? $meta_author : ''; ?>">
        <!-- Bootstrap -->
        <link href="<?php echo base_url(); ?>assests/css/bootstrap.min.css" rel="stylesheet">    
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bitter%3A400%2C700&#038;ver=screen">
        <link href="<?php echo base_url(); ?>assests/css/styles.css" rel="stylesheet">    
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        
    </head>
    <body>
        <div class="container" <?php echo (isset($container_id)) ? 'id="' . $container_id . '"' : ''; ?>>

            <header class="group">
                <a class="home-link" href="<?php base_url();?>"><span class="icon-logo-scripted"></span></a>
                <div class="utility-nav desktop">
                      <?php 
                        $scripted_api = $this->session->userdata('scripted_api');
                        if($scripted_api != '') {
                        ?>
                      <div class="join-link triggers">
                          <a href="<?php echo base_url();?>">Current Jobs</a>
                      </div>
                      <div class="join-link triggers">
                          <a href="<?php echo base_url();?>scripted-create-job">Create a Job</a>
                      </div>
                    <?php }?>
                    <div class="login-link triggers">
                        <a href="<?php echo base_url();?>scripted-settings">Scripted API Settings</a>
                    </div>
               </div>
           </header>
            
            <div class="clearfix"></div>

            <div id="wrap" class="wrap">
                <?php
                if (isset($contents)) {
                    echo $contents;
                }
                ?>
            </div>
        </div>

        <div class="footer">
            <a class="footer-logo" href="http://scripted.com"><img alt="Scripted" src="<?php echo base_url()?>assests/scripted-logo-temp.png"></a>
            <span class="footer-tagline">The New Way to Create Original Content</span>
            <div class="footer-social-links">
                    <a target="_blank" href="http://www.twitter.com/getscripted"><span class="icon-social-twitter"></span></a>
               <a target="_blank" href="http://www.facebook.com/getscripted"><span class="icon-social-facebook"></span></a>
               <a target="_blank" href="http://www.linkedin.com/company/scripted-com"><span class="icon-social-linkedin"></span></a>
               <a target="_blank" href="https://plus.google.com/+ScriptedSanFrancisco"><span class="icon-social-gplus"></span></a>
               <a target="_blank" href="http://www.youtube.com/user/ScriptedWriting"><span class="icon-social-youtube"></span></a>
            </div>
        </div>
        <div class="copyright group">
            &copy; 2014 Scripted, Inc. All rights reserved.  &nbsp;  <a href="http://scripted.com/privacy">Privacy</a>  |  <a href="http://scripted.com/terms">Terms of Use</a>  |  <a href="http://scripted.com/agreement">Writer Services Agreement</a>  |  <a href="http://scripted.com/sitemap">Sitemap</a>
        </div>
        <script src="<?php echo base_url(); ?>assests/js/bootstrap.min.js"></script>
    </body>
</html>
<?php
if(!function_exists('validateApiKey')) {
    function validateApiKey($ID,$accessToken) {

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Token token='.$accessToken));    
        curl_setopt($ch, CURLOPT_HEADER, 1);    
        curl_setopt($ch, CURLOPT_URL, SCRIPTED_END_POINT.'/'.$ID.'/v1/industries/');     
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $result = curl_exec($ch);     
        curl_close($ch);

        if ($result === false) {        
            return false;
        }    
        list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', $result, 2 );    
        $industries = json_decode($contents);  
       if($contents != '') {
            if(isset($industries->data) and count($industries->data) > 0) {
                return true;
            }
       }
       return false;
    }
}
if(!function_exists('getStandardBlogPost')) {
    
    function getStandardBlogPost($selected ='') {
        $jobTemplates = scriptedCurlRequest('job_templates/');   
        
        $out = '';
        if($jobTemplates) {

            $out .= '<select name="format_id" onchange="getFormFields(this.value);" class="form-control">';
            $out .='<option value="0">Select</option>';
            foreach($jobTemplates as $jobT) { 
                $class = '';
                if($selected !='' and $selected == $jobT->id) 
                    $class = 'selected="selected"';
                $out .='<option value="'.$jobT->id.'" '.$class.'>'.$jobT->name.' for $'.($jobT->pricing->base/100).'</option>';
            }
            $out .='</select>';
            return $out;
        }
    }
    
}
if(!function_exists('getListIndustryIds')) {
    function getListIndustryIds($selected ='') {
        $industuries = scriptedCurlRequest('industries/');
        $out = '';
        if($industuries) {        
            $out .= '<select name="industry_ids" class="form-control">';
            $out .='<option value="">Select one at a time</option>';
            foreach($industuries as $indust) {

                $class = '';
                if($selected !='' and $selected == $indust->id) 
                    $class = 'selected="selected"';

                $out .='<option value="'.$indust->id.'" '.$class.'>'.$indust->name.'</option>';
            }
            $out .='</select>';
            return $out;
        }
    }
}
if(!function_exists('getListGuidelineIds')) {
    function getListGuidelineIds($selected ='') {
        $guideLines = scriptedCurlRequest('guidelines/');
        $out = '';
        if($guideLines) {

            $out .= '<select name="guideline_ids" class="form-control">';
            $out .='<option value="">Select one at a time</option>';
            foreach($guideLines as $guide) {
                $class = '';
                if($selected !='' and $selected == $guide->id) 
                    $class = 'selected="selected"';

                $out .='<option value="'.$guide->id.'" '.$class.'>'.$guide->name.'</option>';
            }
            $out .='</select>';
            return $out;
        }
    }
}
if(!function_exists('delivery')) {
    function delivery($selected ='') {
        $out = '';
        $standard = ($selected != '' and $selected=='standard')?'selected="selected"':'';
        $rush = ($selected != '' and $selected=='rush')?'selected="selected"':'';

        $out ='<select name="delivery" id="delivery" class="form-control">
            <option value="standard" '.$standard.'>Delivered in 5 business days</option>
                <option value="rush" '.$rush.'>Delivered in 3 business days (+$10)</option>
                </select>';

        return $out;
    }
}
if(!function_exists('scriptedCurlRequest')) {
    function scriptedCurlRequest($type,$post = false,$fields = '') {
        
        $CI =& get_instance();
        
        $token      = $CI->session->userdata('token');
        
        $CI->load->model('general_model');
        
        $getScriotedInfo = $CI->general_model->getAppInfoScripted($token);
        if(!$getScriotedInfo)
           return false;
        
        $ID               = $getScriotedInfo->scripted_ID;
        $accessToken      = $getScriotedInfo->scripted_access_token;

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: Token token='.$accessToken));    
        curl_setopt($ch, CURLOPT_HEADER, 1);    
        curl_setopt($ch, CURLOPT_URL, SCRIPTED_END_POINT.'/'.$ID.'/v1/'.$type);     
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if($post) {
             curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $result = curl_exec($ch);   
        curl_close($ch);

        if ($result === false) {        
            return false;
        }

        list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', $result, 2 ); // extracting
        if($contents != '') {
            $contents = json_decode($contents);        
            if(isset($contents->data) and count($contents->data) > 0) {
                return $contents->data;
            }
        }

        return false;
    }
}
if(!function_exists('createScriptedProject')) {
    function createScriptedProject($proId) {
        $CI =& get_instance();
        
        $token      = $CI->session->userdata('token');
        $url        = $CI->session->userdata('shop');
       
        $_projectJob = scriptedCurlRequest('jobs/'.$proId);
        $_projectContent = scriptedCurlRequest('jobs/'.$proId.'/html_contents');
        if($_projectContent->id == $proId and !empty($_projectJob)) {
            $content = $_projectContent->html_contents;
            if(is_array($content)) {
                $content = $content[0];
            }
            
            $blogs = shopify_call($token, $url, "/admin/blogs.json", array(), 'GET');                
            $blogs = json_decode($blogs['response'], TRUE);
            
            
           if(isset($blogs['blogs']) and isset($blogs['blogs'][0])) {
               
               $shop_detail = shopify_call($token, $url, "/admin/shop.json", array(), 'GET');                
               $shop_detail = json_decode($shop_detail['response'], TRUE);
               $author = (isset($shop_detail['shop']['name'])) ? $shop_detail['shop']['name'] : 'Shopify API';
               
               $blog_id = $blogs['blogs'][0]['id'];      
               $content .= '<p style="font-style:italic; font-size: 10px;">Powered by <a href="https://app.scripted.com" alt="Scripted.com content marketing automation">Scripted.com</a></p>';
               $article_add = array('article' => array('title' => strip_tags($_projectJob->topic) , 'body_html' => $content, 'author' => $author));

               $articles = shopify_call($token, $url, '/admin/blogs/'.$blog_id.'/articles.json', $article_add, 'POST');                
               $articles = json_decode($articles['response'], TRUE);
               $articleId = $articles['article']['id'];
               echo 'Draft Created!';
               $track_url = 'http://toofr.com/api/track?url='.urlencode('http://'.$url.'.myshopify.com/blogs/'.$blogs['blogs'][0]['handle'].'/'.$articleId).'&title='.urlencode($_projectJob->topic);
               @file_get_contents($track_url);
           }
            
            
        } else {
            echo 'Failed';
        }


    }
}
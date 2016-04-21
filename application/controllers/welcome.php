<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct() {
            parent::__construct();            
            $base_path = str_replace('system', 'application', BASEPATH);
            require_once ($base_path.'third_party/shopify.php');
            $this->load->model('general_model');
            $this->load->helper('main_helper');
        }
	public function index()
	{
            
            $url   = $this->session->userdata('shop');
            $token = $this->session->userdata('token');
            
            if($url == '' or $token == '')
                redirect('error');
            
            $shop = shopify_call($token, $url, "/admin/shop.json", array(), 'GET');                
            $shop = json_decode($shop['response'], TRUE);
            
            if(isset($shop['shop']) and isset($shop['shop']['id'])) {
                
                $getScriotedInfo = $this->general_model->getAppInfoScripted($token);
                if(!$getScriotedInfo)
                    redirect('scripted-settings');
                
                $scripted_ID               = $getScriotedInfo->scripted_ID;
                $scripted_accessToken      = $getScriotedInfo->scripted_access_token;
                $validate                   = validateApiKey($scripted_ID,$scripted_accessToken);    
                if($validate) {
                    $allJobs = scriptedCurlRequest('jobs/');
                    
                    $template['title']      = 'Current Jobs - Scripted';
                    $template['contents']   = $this->load->view('welcome/current_jobs',array('allJobs' => $allJobs),true);
                    $this->load->view('template/template',$template);                    
                }
                
            } else {                
                $this->install($url.'.myshopify.com');
                //redirect('error?message=wrong-informations');
            }
	}
        function register() {
           
            $shop = (isset($_GET["shop"])) ? $this->input->get('shop') : '';            
            $code = (isset($_GET["code"])) ? $this->input->get('code') : '';
            
            $verify = $this->verifyHmacAppInstall();
            if(!$verify) {
                // Someone is trying to be shady!
                die('This request is NOT from Shopify!');
            }
           
            $shop_prefix = str_replace('.myshopify.com', '', $shop);
            $query = array(
                    "Content-type" => "application/json",
                    "client_id" => API_KEY,
                    "client_secret" => API_SECRET,
                    "code" => $code
            );

            $shopify_response = shopify_call(NULL, $shop_prefix, "/admin/oauth/access_token", $query, 'POST');

            $shopify_response = json_decode($shopify_response['response'], TRUE);

            $token = $shopify_response['access_token'];

            if($token != null) {

                    $shop_prefix = str_replace('.myshopify.com', '', $shop);
                    $checkShop = $this->general_model->getAppInfoWithShop($shop_prefix);

                    $shop = shopify_call($token, $shop_prefix, "/admin/shop.json", array(), 'GET');                
                    $shop = json_decode($shop['response'], TRUE);

                    if(isset($shop['shop']) and isset($shop['shop']['id'])) {
                    $shop = $shop['shop'];

                    $input['shopurl']       = $shop_prefix;
                    $input['owner_name']    = $shop['email'];
                    $input['owner_email']   = $shop['shop_owner'];
                    $input['access_token']  = $token;
                    $input['shop_id']       = $shop['id'];
                    $input['code']          = $code;

                    if($checkShop) {
                        $this->general_model->updateShopInfo($input,$shop_prefix);
                    } else {
                        $input['register_time'] = date('Y-m-d H:i:s');
                        $input['status'] = 1;
                        $this->general_model->insertShopInfo($input);
                    }
                    $this->session->set_userdata('token',$token);
                    $this->session->set_userdata('shop',$shop_prefix);
                }

                redirect('scripted-settings');
            } else {
                die('This request is NOT from Shopify!');
            }
                   

            
        }
        private function verifyHmacAppInstall() {
                $params = array();

                foreach($_GET as $param => $value) {
                    if ($param != 'signature' && $param != 'hmac') {
                        $params[$param] = "{$param}={$value}";
                    }
                }

                asort($params);

                $params = implode('&', $params);
                $hmac = $_GET['hmac'];
                $calculatedHmac = hash_hmac('sha256', $params, API_SECRET);

                return ($hmac == $calculatedHmac);
        }
        public function login() {
            
            $shop = (isset($_GET["shop"]) and $_GET["shop"] !='') ? $this->input->get('shop') : '';            
            $timestamp = (isset($_GET["timestamp"])) ? $this->input->get('timestamp') : '';
            $signature = (isset($_GET["signature"])) ? $this->input->get('signature') : '';
            
            $verify = $this->verifyHmacAppInstall();
            if(!$verify) {
                header("Location: https://".$shop."/admin/auth/login");
                die();
            }
            
            $shop_prefix = str_replace('.myshopify.com', '', $shop);            
            $checkShop = $this->general_model->getShopInfoByShopUrl($shop_prefix);            
            if(!$checkShop) {
                $this->install($shop);
                exit;
            } 
            
            $this->session->set_userdata('token',$checkShop->access_token);
            $this->session->set_userdata('shop',$shop_prefix);

            $getScriotedInfo = $this->general_model->getAppInfoScripted($checkShop->access_token);

            if($getScriotedInfo)
                $this->session->set_userdata('scripted_api',$getScriotedInfo->id);

            redirect();
            die();
            
        }
        private function install($shop) {
           
            $redirect_uri = base_url()."register";
            $install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . API_KEY . "&scope=read_content,write_content,read_products,write_products&redirect_uri=" . urlencode($redirect_uri);

            header("Location: " . $install_url);
            die();
        }
        function scripted_settings() {
            
            $token  = $this->session->userdata('token');
            
            if($token == '')
                redirect('error');
            
            $getShopInfo            = $this->general_model->getShopInfo($token);          
            $getScriptedInfo        = $this->general_model->getScriptedInfo($getShopInfo->id);          
            $ID                     = ($getScriptedInfo) ? $getScriptedInfo->scripted_ID : '';
            $scripted_access_token  = ($getScriptedInfo) ? $getScriptedInfo->scripted_access_token : '';
            
                
            $this->load->library('form_validation');                
            $this->form_validation->set_rules('ID_text', 'ID', 'trim|required');
            $this->form_validation->set_rules('success_tokent_text', 'Access Token', 'trim|required|callback_verifyscriptedapi');

            if($this->form_validation->run()==FALSE) {
                    
                    $data['ID'] = $ID;
                    $data['scripted_access_token'] = $scripted_access_token;
                    $template['title'] = 'Scripted API Settings';
                    $template['contents'] = $this->load->view('welcome/scripted_settings',$data,true);
                    $this->load->view('template/template',$template);
            } else {
                
                    $input['scripted_ID'] = $this->input->post('ID_text');
                    $input['scripted_access_token'] = $this->input->post('success_tokent_text');
                    $scripted_api = 0;
                    
                    if(!$getScriptedInfo) {
                        $input['api_info_id'] = $getShopInfo->id;
                        $this->db->insert('scripted_apis',$input);
                        $scripted_api = $this->db->insert_id();
                    } else {                        
                        $this->db->where('id',$getScriptedInfo->id);
                        $this->db->update('scripted_apis',$input);
                        $scripted_api = $getScriptedInfo->id;
                    }
                    $this->session->set_userdata('scripted_api',$scripted_api);
                    $this->session->set_userdata('frontMsg','Setting saved successfuly');
                    redirect('scripted-settings');
            }    
            
        }
        function verifyscriptedapi($token) {
            $ID_text = $this->input->post('ID_text');
            if ($ID_text == '' or $token == '') {
			$this->form_validation->set_message('verifyscriptedapi', 'Id and Token are required field');
			return FALSE;
		} else {                    
                    $validate = validateApiKey($ID_text,$token);                    
                    if($validate) {
			return TRUE;
                    } else {
                        $this->form_validation->set_message('verifyscriptedapi', 'Scripted Id and token are not valid');
			return FALSE;
                    }
		}
        }
        public  function error() {
            
        }
        function scripted_actions() {
            
            $scripted_api = $this->session->userdata('scripted_api');
            if($scripted_api == '')
                die('error');
            
            $getScriotedInfo = $this->general_model->getScriptedInfoById($scripted_api);
            if(!$getScriotedInfo)
                die('error');

            $do             = (isset($_GET['do']) and $_GET['do'] !='') ? $this->input->get('do') : '';
            $project_id     = (isset($_GET['project_id']) and $_GET['project_id'] !='') ? $this->input->get('project_id') : '';

            $ID               = $getScriotedInfo->scripted_ID;
            $accessToken      = $getScriotedInfo->scripted_access_token;
            $validate         = validateApiKey($ID,$accessToken);

            if(!$validate or $project_id == '' or $do == '') 
                die('Failed');

            if($do == 'View') {
                $_projectContent = scriptedCurlRequest('jobs/'.$project_id.'/html_contents');
                
                if($_projectContent->id == $project_id) {
                    $content = $_projectContent->html_contents;
                    if(is_array($content)) {
                        $content = $content[0];
                    }            
                    echo $content;
                }
            }elseif($do == 'Accept') {
                $_projectAction = scriptedCurlRequest('jobs/'.$project_id.'/accept',true);
                if($_projectAction)
                    echo 'Accepted';
                else
                    echo 'Failed';
            }elseif($do == 'Reject') {
                $_projectAction = scriptedCurlRequest('jobs/'.$project_id.'/reject',true);     
                if($_projectAction)
                    echo 'Accepted';
                else
                    echo 'Failed';
            }elseif($do == 'Create') {
                createScriptedProject($project_id);
            }elseif($do == 'Request') {

                if(empty($_POST))
                    $this->getFormRequestEditProject($project_id);
                else {
                    $chief_complaint = $this->input->post('chief_complaint');
                    $_projectAction = $_projectAction = scriptedCurlRequest('jobs/'.$project_id.'/request_edits',true,'feedback='.$chief_complaint); 

                    if($_projectAction)
                        echo '<strong>Success!<strong> Submitted Successfully.';
                    else
                        echo '<strong>Error!<strong> Scripted Return an error.';
                }
            }
            die();
            
        }
        function getFormRequestEditProject($project_id) {
    
            $out ='<form action="" method="post" name="frmEditRequests" id="frmEditRequests" onsubmit="return sendEditRequest();">';
            $out .= '<div class="form-group"><label for="chief_complaint">Chief Complaint</label>';
            $out .= '<textarea id="chief_complaint" name="chief_complaint" class="form-control"></textarea></div>';
            $out .='<button type="Save Changes" class="btn btn-default">Request Edits</button>';
            $out .='</form>';
            $out .='<script>';
            $out .='function sendEditRequest() {
                        var chief_complaint = document.getElementById("chief_complaint").value;
                        if(chief_complaint == "") {
                            document.getElementById("chief_complaint").style.border="1px solid red";
                            return false;
                        }
                        jQuery.ajax({
                        type: "POST",
                        url: "'.site_url('scripted_actions').'?do=Request&project_id='.$project_id.'",
                        data: $( "#frmEditRequests" ).serialize(),
                        success: function(data) {  
                                $( "#frmEditRequests" ).prepend( "<div class=\'alert alert-success\'>"+data+"</div>");
                            }
                        });
                        return false;
                    }
                ';
            $out .='</script>';
            echo $out;
        }
        function scripted_create_a_job() {
            
            $scripted_api = $this->session->userdata('scripted_api');
            if($scripted_api == '')
                die('error');
            
            $getScriotedInfo = $this->general_model->getScriptedInfoById($scripted_api);
            if(!$getScriotedInfo)
                die('error');
            
            $this->load->library('form_validation');                
            $this->form_validation->set_rules('topic', 'Topic', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('quantity_order', 'Quantity Order', 'trim|required');
            $this->form_validation->set_rules('format_id', 'Template', 'trim|required|callback_minimumquantity');

            if($this->form_validation->run()==FALSE) {
                    $data['fields'] = (!empty($_POST)) ? $this->scripted_template_fields($_POST['format_id']) : '';
                    $template['title'] = 'Scripted Create A Job';
                    $template['contents'] = $this->load->view('welcome/scripted_create_a_job',$data,true);
                    $this->load->view('template/template',$template);
            } else {
                
                $topic           = urlencode($this->input->post('topic'));
                $quantity_order  = $this->input->post('quantity_order');

                $format_id       = $this->input->post('format_id');
                $industry_ids    = $this->input->post('industry_ids');
                $guideline_ids   = $this->input->post('guideline_ids');
                $delivery        = $this->input->post('delivery');
                $formFields      = $this->input->post('form_fields');
                $fields          ='topic='.$topic.'&quantity='.$quantity_order;

                if($format_id!= '')
                    $fields .= '&job_template[id]='.$format_id;

                if(is_array($formFields)) {
                    foreach($formFields as $key => $value) {
                        $value   = $value;
                        
                        if(is_array($value)) {
                            foreach ($value as $sub) {    
                                $fields  .= '&job_template[prompts][][id]='.$key;
                                $fields  .= '&job_template[prompts][][value][]='.urlencode($sub);
                            }                            
                        } else {
                            $fields  .= '&job_template[prompts][][id]='.$key;
                            $fields  .= '&job_template[prompts][][value]='.urlencode($value);
                        }
                    }
                }                
                
                if($industry_ids!= '')
                    $fields .= '&industries[][id]='.$industry_ids;
                
                if($guideline_ids!= '')
                    $fields .= '&guidelines[][id]='.$guideline_ids;

                if($delivery!= '')
                    $fields .= '&delivery='.$delivery;
                
                 $fieldslength = strlen($fields);

                 $ch = curl_init(); 
                 curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token token='.$getScriotedInfo->scripted_access_token));    
                 curl_setopt($ch, CURLOPT_HEADER, false);    
                 curl_setopt($ch, CURLOPT_URL, SCRIPTED_END_POINT.'/'.$getScriotedInfo->scripted_ID.'/v1/jobs');     
                 curl_setopt($ch,CURLOPT_POST,$fieldslength);
                 curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                 $result = curl_exec($ch);   
                 curl_close($ch);
                 
                 $response = json_decode($result);
                 
                 if ($result === false) {   
                     $message = '<strong>Sorry!</strong> Sorry, we found an error and your Scripted job was not created! Please confirm your ID and Access Token are correct and try again.';
                     $this->session->set_userdata('frontMsg',$message);
                 } else {
                        
                        if($response != '' and isset($response->data)) {
                             $success = true;
                             $response   = $response->data;
                             $deadlineAt = strtotime($response->deadline_at);
                             $deadlineAt = '<p>Delivery Time : '.date('M d, Y',$deadlineAt).'</p>';
                             $projectId  = '<p>Project id : '.$response->id.'</p>';
                             $message = '<strong>Success!</strong> Congratulation! Your project has been created.</p>'.$projectId.$deadlineAt;                             
                             $this->session->set_userdata('frontMsg',$message);
                         }elseif($response != '' and isset($response->errors)) {
                             $errors = $response->errors;
                             $message  = '<strong>Sorry!</strong>';
                             foreach ($errors as $error) {
                                 $message .='<p>'.$error.'</p>';
                             }
                             $this->session->set_userdata('frontMsg',$message);
                         }
                 }
                 redirect('scripted-create-job');                
            } 
        }
        function minimumquantity($format_id) {                     
            $dataFields = scriptedCurlRequest('job_templates/'.$format_id);
            
            if(!in_array($this->input->post('quantity_order'), $dataFields->content_format->quantity_options)) {
                $this->form_validation->set_message('minimumquantity', 'Quantity field is not correct');
                return FALSE;
            } else {
                return TRUE;
            }            
        }
        function scripted_template_fields($postformField = '') {
            $scripted_api = $this->session->userdata('scripted_api');
            if($scripted_api == '')
                die('error');
            
            $getScriotedInfo = $this->general_model->getScriptedInfoById($scripted_api);
            if(!$getScriotedInfo)
                die('error');
            $formField =  $this->input->post('form_id');
            
            if($postformField !='')
                $formField =  $postformField;
            
            $dataFields = scriptedCurlRequest('job_templates/'.$formField); 
            
            $out = '<div class="clearfix"></div>';
            
            if($dataFields) {           
                $out .='<div class="form-group"><label class="col-sm-3 control-label">Quantity:</label><div class="col-sm-6"><select name="quantity_order" class="form-control">';
                foreach($dataFields->content_format->quantity_options as $key => $value) {
                    $out .='<option value="'.$value.'">'.$value.'</option>';
                }
                $out .='</select><div class="clearfix"></div></div></div>';
                
                $fields = $dataFields->prompts;
                foreach($fields as $field) {    
                    
                    $required = (isset($field->answer_required) and $field->answer_required == 1) ? '*':'';
                    
                    if($field->kind == 'checkbox') {                        
                        $oldValue = @$_POST['form_fields'][$field->id];                       
                        $out .='<div class="form-group"><label class="col-sm-3 control-label">'.$field->label.$required.'</label>';
                        $out .='<div class="col-sm-6">';
                        foreach ($field->value_options as $optionKey => $optionValue) {
                            $class = '';
                            if(in_array($optionValue, $oldValue))
                                    $class = 'checked';
                            $out .='<div class="checkbox"><input type="checkbox" '.$class.' value="'.$optionValue.'" name="form_fields['.$field->id.'][]">'.$optionValue.'</div>';    
                        }
                        
                        $out .='<p>'.$field->description.'</p></div></div>';      
                        
                    } else if($field->kind == 'radio') {
                        $oldValue = @$_POST['form_fields'][$field->id];   
                        $out .='<div class="form-group"><label class="col-sm-3 control-label">'.$field->label.$required.'</label>';
                        $out .='<div class="col-sm-6">';
                        foreach ($field->value_options as $optionKey => $optionValue) {
                                $class = '';
                                if($optionValue == $oldValue)
                                    $class = 'checked';
                            $out .='<div class="radio"><input type="radio" '.$class.' value="'.$optionValue.'" name="form_fields['.$field->id.']">'.$optionValue.'</div>';    
                        }
                        
                        $out .='<p>'.$field->description.'</p></div></div>'; 
                        
                    } else if(strpos($field->kind, 'string[255]') !== false) {
                        $out .='<div class="form-group"><label class="col-sm-3 control-label">'.$field->label.$required.'</label>';
                        $out .='<div class="col-sm-6"><input type="text" name="form_fields['.$field->id.']" class="form-control" value="'.@$_POST['form_fields'][$field->id].'">';
                        $out .='<p>'.$field->description.'</p></div></div>';                
                    } else if(strpos($field->kind, 'string[1024]') !== false) {
                        $out .='<div class="form-group"><label class="col-sm-3 control-label">'.$field->label.$required.'</label>';
                        $out .='<div class="col-sm-6"><textarea name="form_fields['.$field->id.']" rows="5" class="form-control">'.@$_POST['form_fields'][$field->id].'</textarea>';
                        $out .='<p>'.$field->description.'</p></div></div>';                
                    } else if(strpos($field->kind, 'array') !== false) {
                        $out .='<div class="form-group"><label class="col-sm-3 control-label">'.$field->label.$required.'</label>';
                        $out .='<div class="col-sm-6"><textarea name="form_fields['.$field->id.'][]" rows="5" class="form-control">'.@$_POST['form_fields'][$field->id].'</textarea>';
                        $out .='<p>'.$field->description.'</p></div></div>';                
                    }
                    
                        
                }


            }
            if($postformField !='')
                return $out;
            else
                echo $out;
        }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
<?php

class General_model extends CI_Model {

	function General_model () {
			parent::__construct();
			
	}
	function getAppInfoScripted($token) {
            
                $this->db->select('*,api.id as apiId');
                $this->db->from('api_info as api, scripted_apis as sc');
                $this->db->where('api.id = sc.api_info_id ');
                $this->db->where('api.access_token',$token);
                $this->db->where('status',1);
                $result = $this->db->get();
                
                if($result->num_rows() > 0) {
                    return $result->row();
                }
                return false;
	}
        function getShopInfo($token) {
                $this->db->select('*');
                $this->db->from('api_info');
                $this->db->where('access_token',$token);
                $this->db->where('status',1);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    return $result->row();
                }
                return false;
        }
        function getScriptedInfo($api_info_id) {
                $this->db->select('*');
                $this->db->from('scripted_apis');
                $this->db->where('api_info_id',$api_info_id);
                $result = $this->db->get();
                
                if($result->num_rows() > 0) {
                    return $result->row();
                }
                return false;
        }
        function getScriptedInfoById($id) {
                $this->db->select('*');
                $this->db->from('scripted_apis');
                $this->db->where('id',$id);
                $result = $this->db->get();
                
                if($result->num_rows() > 0) {
                    return $result->row();
                }
                return false;
        }
        function getAppInfoWithShop($url) {
            $this->db->select('*');
            $this->db->from('api_info');
            $this->db->where('shopurl',$url);
            $result = $this->db->get();

            if($result->num_rows() > 0) {
                return $result->row();
            }
            return false;
        }
        function updateShopInfo($input,$shop_url) {
            $this->db->where('shopurl',$shop_url);
            $this->db->update('api_info',$input);
        }
        function insertShopInfo($input) {
            $this->db->insert('api_info',$input);
        }
        function getShopInfoByShopUrl($shop) {
                $this->db->select('*');
                $this->db->from('api_info');
                $this->db->where('shopurl',$shop);
                $this->db->where('status',1);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    return $result->row();
                }
                return false;
        }
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 class Dashboard extends CI_Controller {
 	public function __construct(){
 		parent::__construct();
 		$this->load->model('login_model');
  	}

	public function index(){
 		if($this->session->userdata("type") == 'subadmin' || $this->session->userdata("type") == 'admin'){
 			$data["master_title"] = $this->config->item('sitename')." | Home";
 			$data['sort']         = $this->input->post("sort"); //echo $data['sort']; die;
   			$data["master_body"]  = "dashboard";  
 			$this->load->theme('mainlayout',$data);
 		}
 		else{
 		  redirect(base_url());	
 		}
 	}

  	//for update profile
 	public function update_profile(){ 
 		$data["master_title"] = "Update Profile";   // Please enter the title of page......
 		$data["userdata"]     = $this->session->userdata("tempdata",$data);
 		$data["master_body"]  = "update_profile";  //  Please use view name in this field please do not include '.php' for including view name
 		$this->load->theme('mainlayout',$data);
 	}

    public function update_profile_db(){ 
		$username     = clean($this->input->post("username")); 
		$password     = clean($this->input->post("password"));
		$old_password = clean($this->input->post("old_password"));
		$type         = $this->session->userdata['type']; 
		$arr1['username'] = $username;
		$arr1['password'] = $password;
		$arr1['old_password'] = $old_password;
	    if($type == "admin"){
			$arr1['user_type'] = 1;
	    }
	    else{
 		   $arr1['user_type'] = '';
 	    }
 		$id = $result["id"];
        $data["userdata"]=$this->session->set_userdata("tempdata",$arr1);
 		if($type == "admin"){  
 		 $result = $this->login_model->check_admin_login($arr1);
 		 $id = $result["id"];
 			if($username == ''){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Please enter the Username");
 			}
 			else if($old_password == ''){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Please enter the Current Password");
 			}
 			else if($password == ''){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Please enter the new Password");
 			}
 			else if(!$this->common->validateHash($old_password,$result["password"])){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Wrong Current Password");
 			}
 			else{
 				$arr['username'] = $username;
 				$arr['password'] = $arr1['password'];
 				$arr['id'] = $id;
 				$result=$this->login_model->update_admin_data($arr, 'admin');
 				if($result){
 					$this->session->unset_userdata('tempdata');
 					$this->session->set_flashdata("successmsg","Profile updated successfully");	
 					redirect(base_url()."dashboard/update_profile");
 					$err=0;	
 				}
 				else{
 					 $this->session->set_flashdata("errormsg","There is error updating profile to data base . Please contact database admin");
 				 	 $err=1;
 				}
 				redirect(base_url()."dashboard/update_profile");
 			}
 			redirect(base_url()."dashboard/update_profile");
 		}
 		else{ 
   		   $id = $result["id"];
 			if($old_password == ''){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Please enter the Current Password");
 			}
 			else if($password == ''){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Please enter the New Password");
 			}
 			else if(!$this->common->validateHash($old_password,$result["password"])){
 				$err=1;	
 				$this->session->set_flashdata("errormsg","Wrong Current Password");
 			}
 			else{
 				$arr['password'] = $arr1['password'];
 				$arr['username'] = $username;
 				$arr['id'] = $id;
 				$result=$this->login_model->update_admin_data($arr, 'restaurants');

 				if($result){
 					$this->session->unset_userdata('tempdata');
 					$this->session->set_flashdata("successmsg","Profile updated successfully");	
 					redirect(base_url()."dashboard/update_profile");	
 					$err=0;
 				}
 				else{
 					 $this->session->set_flashdata("errormsg","There is error updating profile to data base . Please contact database admin");
 				 	 $err=1;
 				}
 				redirect(base_url()."dashboard/update_profile");
 			}
 		}
 		redirect(base_url()."dashboard/update_profile");
 	}
 

 }
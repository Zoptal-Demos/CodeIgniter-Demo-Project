<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 class Login extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('login_model'); //for login model
		$this->load->model('user_model'); 
 	}
 	/***********************************************Login function starts **************************************************************/
	
	//for login page   
	public function index(){

	
		if($this->session->userdata("user_type") == ''){
			$data["master_title"] = $this->config->item('sitename')." | Login"; // for page title
			$data['userdata']     = $this->session->userdata('tempdata');
			$this->load->theme('login', $data); 
		}
		else{
			redirect(base_url()."dashboard");
		}
		
	}
	
	//for validate login details  
	public function check_login(){   
		$arr["email"]     = trim($this->input->post("username"));
		$arr["password"]  = trim($this->input->post("password"));
		//$arr["user_type"] = trim($this->input->post("user_type"));
		//$arr["user_type"] = 1; 
		//for validate admin details in database
  		$userData = $this->login_model->check_hotel_login($arr); 
		if($userData["user_type"] != 5){ 
			 if($this->common->validateHash($arr["password"], $userData["password"])){ 
				if($userData["email"] != ""){ 
					$arr2['time']       = time();
					$arr2['id']         = $userData['id'];
					$arr2['last_login'] = time();
					$this->user_model->update_profile_data($arr2);
					
					//for set session
					$err = 0;
					$id  = $userData['id'];
					$hotel_id = $userData['hotel_id'];
					$m_id     = "";
					$lm_id    = "";
					if($userData['name'] == ""){ $name = "User"; } else{$name = $userData['name']; } 
					//$this->session->set_userdata("name",$name);
					//$this->session->set_userdata("user_id", $id);  
					
 				 	//	$this->session->set_userdata("hotel_id", $hotel_id);
 					if($userData["user_type"] == 1){
 						$login_as  = "chain_manager";
						$user_type = "chain_manager";
						$location_manager = "";
						$manager =  "";
 					}
					else if($userData["user_type"] == 2){
						$login_as  = "location_manager";
						$user_type = "location_manager";
						$location_manager = "";
						$manager =  "";
 					}
					else if($userData["user_type"] == 3){
						$login_as  = "manager";
						$user_type = "manager";
						$location_manager = $userData["location_manager"];
						$manager =  "";
 					}
					else if($userData["user_type"] == 4){
						$login_as  = "employee";
						$user_type = "employee";
						$location_manager = $userData["location_manager"];
						$manager =  $userData["manager"];
 					}  
					 
                     $otherUser = $userData['id'];
                    
					
					$this->session->set_userdata(array("name" => $name, "other_user" => $otherUser, "user_id" => $id, "hotel_id" => $hotel_id, "login_as" => $login_as, "user_type" => $user_type, "location_manager" => $location_manager, "manager" => $manager, "last_logged" => $userData["last_login"])); 
					//header("Location : ".base_url()."dashboard"); die;
					
 					//$this->session->set_userdata("last_logged",$userData["last_login"]);
					redirect(base_url()."dashboard"); die;
				}
			}
			else{
				$err=1;	
				$this->session->set_flashdata("errormsg","Wrong email and password");
				redirect(base_url()."login"); 
			}
		}
		else{
			$err=1;	
			$this->session->set_flashdata("errormsg","Wrong email and password");
			redirect(base_url()."login");
		}
 	}
	 
	public function authenticate(){  
 		 $userData = $this->user_model->get_user_data($this->uri->segment(3));
		 $userType = $this->uri->segment(4);
		 $id       = $userData['id'];
		 $hotel_id = $userData['hotel_id'];
		 if(!empty($userData)){
			 $err = 0;
			 if($userData['name'] == ""){ $name = "User"; } else{$name = $userData['name']; }
             if($userData["user_type"] == 1){ //|| $userType == "admin"
                  $login_as  = "chain_manager";
                  $user_type = "chain_manager";
                  $location_manager = "";
                  $manager =  "";
              }
              else if($userData["user_type"] == 2){
                  $login_as  = "location_manager";
                  $user_type = "location_manager";
                  $location_manager = "";
                  $manager =  "";
              }
              else if($userData["user_type"] == 3){
                  $login_as  = "manager";
                  $user_type = "manager";
                  $location_manager = $userData["location_manager"];
                  $manager =  "";
              }
              else if($userData["user_type"] == 4){
                  $login_as  = "employee";
                  $user_type = "employee";
                  $location_manager = $userData["location_manager"];
                  $manager =  $userData["manager"];
              } 
			 if($this->session->userdata("other_user") <> ''){
				 $otherUser = $this->session->userdata("other_user");
			 }
			 else{
				  if($userType == "admin"){
						$otherUser = 6;
				  }
				  else if($userType == "chain_manager"){
						$otherUser = $userData['chain_manager'];
				  }
				  else if($userType == "location_manager"){
						$otherUser = $userData['location_manager'];
				  }
				  else if($userType == "manager"){
						$otherUser = $userData['manager'];
				  }
				  else if($userType == "employee"){
						$otherUser = $userData['employee'];
				  }
			 }

              $this->session->set_userdata(array("name" => $name, "other_user" => $otherUser, "user_id" => $id, "hotel_id" => $hotel_id, "login_as" => $login_as, "user_type" => $user_type, "location_manager" => $location_manager, "manager" => $manager, "last_logged" => $userData["last_login"])); 
 			  redirect(base_url()."dashboard");
		 }
		 else{
			 $err=1;	
			 $this->session->set_flashdata("errormsg","Wrong username and password");
			 redirect(base_url()."login"); 
		 } 	
	}
	 
	public function lm_authenticate(){  
 		 $userData = $this->user_model->get_user_data($this->uri->segment(3));
		 $hotel_id = $this->uri->segment(4);
		 $userType = $this->uri->segment(5);
		 if(!empty($userData) && ($userType == "admin")){
			 $err = 0;
			 if($userData['name'] == ""){ $name = "User"; } else{$name = $userData['name']; }
 			 
			 $this->session->set_userdata("login_as", "location_manager");
  			 $this->session->set_userdata("user_type", $userType);
 			// $this->session->set_userdata("login_by", $userType);
			 $this->session->set_userdata("hotel_id", $hotel_id);
			 $this->session->set_userdata("lm_id", $userData['id']);
			 redirect(base_url()."dashboard");
		 }
		 else{
			 $err=1;	
			 $this->session->set_flashdata("errormsg","Wrong username and password");
			 redirect(base_url()."login"); 
		 } 	
	}
	 
	public function mana_authenticate(){  
 		 $userData    = $this->user_model->get_user_data($this->uri->segment(3));
		 $hotel_id    = $this->uri->segment(4);
		 $loc_manager = $this->uri->segment(5);
		 $userType    = $this->uri->segment(6);
		 if(!empty($userData) && ($userType == "admin")){
			 $err = 0;
			 if($userData['name'] == ""){ $name = "User"; } else{$name = $userData['name']; }
 			 
			 $this->session->set_userdata("login_as", "manager");
  			 $this->session->set_userdata("user_type", $userType);
 			// $this->session->set_userdata("login_by", $userType);
			 $this->session->set_userdata("hotel_id", $hotel_id);
			 $this->session->set_userdata("lm_id", $loc_manager);
			 $this->session->set_userdata("manager_id", $userData['id']);
			 redirect(base_url()."dashboard");
		 }
		 else{
			 $err=1;	
			 $this->session->set_flashdata("errormsg","Wrong username and password");
			 redirect(base_url()."login"); 
		 } 	
	}
	 
	public function emp_authenticate(){  
 		 $userData    = $this->user_model->get_user_data($this->uri->segment(3)); 
		 $hotel_id    = $this->uri->segment(4);
		 $loc_manager = $this->uri->segment(5);
		 $manager     = $this->uri->segment(6);
		 $userType    = $this->uri->segment(7);
		 if(!empty($userData) && ($userType == "admin")){ 
			 $err = 0;
			 if($userData['name'] == ""){ $name = "User"; } else{$name = $userData['name']; }
 			 $sarr = array("login_as" => "employee", "user_type" => $userType, "hotel_id" => $hotel_id, "lm_id" => $loc_manager, "manager_id" => $manager, "emp_id" => $userData['id']);
			   $this->session->set_userdata($sarr);   
			// $this->session->set_userdata("login_as", "employee"); echo 
  			// $this->session->set_userdata("user_type", $userType);
 			// $this->session->set_userdata("login_by", $userType);
			// $this->session->set_userdata("hotel_id", $hotel_id);
			// $this->session->set_userdata("lm_id", $loc_manager);
			// $this->session->set_userdata("manager_id", $manager);
			// $this->session->set_userdata("emp_id", $userData['id']); echo "fsdfsd"; die;
			 redirect(base_url()."dashboard");
		 }
		 else{ 
			 $err=1;	
			 $this->session->set_flashdata("errormsg","Wrong username and password");
			 redirect(base_url()."login"); 
		 } 	
	}
	 
	
	//for forgot password
	public function forgot_password(){
		$data["master_title"] = $this->config->item('sitename')." | Forgot Password";  // for page title
		$data['userdata']     = $this->session->userdata('tempdata'); 
		$this->load->theme('forgot_password',$data); 	 
	}
	
	//for validate/update admin data 
	public function forgot_password_db(){
		$arr["email"] = trim($this->input->post("username"));
		$this->session->set_flashdata("tempdata", $arr);
 		$userData = $this->login_model->getSubAdminData($arr["email"]); 
		if(!empty($userData)){
			//for generate new password
			$arr1['id'] = $userData['id'];
			$password = $this->common->random_generator(5);
			$str = $this->common->random_generator(2);
			$new_password = md5($str.$password);
			$new_password = $new_password.":".$str;
			$arr1['password'] = $new_password;
			$userData['password'] = $password;
			//for send new password to admin email 
            $email   = $userData['email'];
			$subject = "Admin Login Details -->".$this->config->item('sitename');
			$message = $this->common->forgotAdminEmailTemplate($userData);
			$headers = "From: support@zoptal.in\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n";

			$send = mail($email,$subject,$message,$headers);
			if($send){
				//for update new password to database
				$this->login_model->updateSubAdminPassword($arr1);
				//for success message
	 			$this->session->set_flashdata("successmsg","New password has been sent to your email address.");
 				redirect(base_url()."login/forgot_password"); exit;
	 		} 
			else {
				//for error message
	 			$this->session->set_flashdata("errormsg","Something wrong, Please try again.");
 				redirect(base_url()."login/forgot_password"); exit;					
	 		}
	 	} else {
			//for error message
	 		$this->session->set_flashdata("errormsg","Wrong Email.");
 			redirect(base_url()."login/forgot_password"); exit;
	 	}
	}
	
	//logout the Admin
	public function logout(){
		$this->session->sess_destroy();
		$this->session->set_flashdata("successmsg","Log out successfully");	
		redirect(base_url()."login");
	}
	/***********************************************Login function ends **************************************************************/
}
?>
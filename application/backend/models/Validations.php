<?php 

class Validations extends CI_Model {
     function __construct(){
         parent::__construct();
     }

    ////////////////////////////// validate change password data /////////////////////////	

	public function validate_change_password($arr){
 		if($arr["oldpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please enter Current password");	
 			$err=1;	
 		}	
 		else if($arr["newpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please enter new password");	
 			$err=1;	
 		}
 		else if(preg_match('/[#$@%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/',$arr["newpassword"])){
 			$this->session->set_flashdata("errormsg","Spacial characters are not allowed in new password");
 			$err=1;	
 		}
 		else if(strlen($arr["newpassword"]) < 5 || strlen($arr["newpassword"]) > 10){
 			$this->session->set_flashdata("errormsg","Your password must be between 5 and 10 characters long ");
 			$err=1;	
 		}
 		else if($arr["confirmnewpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please confirm your password");	
 			$err=1;	
 		}
 		else if(preg_match('/[#$@%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/',$arr["confirmnewpassword"])){
 			$this->session->set_flashdata("errormsg","Spacial characters are not allowed in confirm password");
 			$err=1;	
 		}
 		else if($arr["confirmnewpassword"]!=$arr["newpassword"]){
 			$this->session->set_flashdata("errormsg","new password and confirm password should match.");	
 			$err=1;	
 		}
 		else{
 			$err=0;	
 		}
 		if($err==0){
 			return true;	
 		}
 		else{
 			return false;	
 		}
 	}

 
	/**************************************Profile validation starts ****************************************/

	public function validatepasswords($arr){
 		if($arr["oldpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please enter old password");	
 			$err=1;	
 		}	
 		else if($this->common->checkpasswordvalidity($arr)){
 			$this->session->set_flashdata("errormsg","Wrong old password");	
 			$err=1;	
 		}
 		else if($arr["newpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please enter new password");	
 			$err=1;	
 		}
 		else if(preg_match('/[#$@%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/',$arr["newpassword"])){
 			$this->session->set_flashdata("errormsg","Spacial characters are not allowed in new password");
 			$err=1;	
 		}
 		else if(strlen($arr["newpassword"]) < 5 || strlen($arr["newpassword"]) > 15){
 			$this->session->set_flashdata("errormsg","Your password must be between 5 and 15 characters long ");
 			$err=1;	
 		}
 		else if($arr["confirmnewpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please confirm your password");	
 			$err=1;	
 		}
 		else if($arr["newpassword"]!=$arr["confirmnewpassword"]){
 			$this->session->set_flashdata("errormsg","Both passwords do not matches");	
 			$err=1;	
 		}
 		else{
 			$err=0;	
 		}
 		if($err==0){
 			return true;	
 		}
 		else{
 			return false;	
 		}
 	}

	
 	public function update_password($arr){
 		$err=0;
 		if($arr["password"]==""){
 			$this->session->set_flashdata("errormsg","Please enter your password");
 			$err=1;	
 		}	
 		else if($arr["confirmpassword"]==""){
 			$this->session->set_flashdata("errormsg","Please confirm your password");
 			$err=1;	
 		}
 		if($err==0){
 			return true;	
 		}
 		else{
 			return false;	
 		}
 	}

 	/****************************** signup  validation ********************************/

 	public function validate_userdata($arr){
 		 if($arr["email"] == "" || $arr["username"] || $arr["password"] == "" || $arr["cpassword"] == "" || $arr["device_type"] == ""){
 			$err=1;
 			$this->session->set_flashdata("errormsg","Email, Username, Password, Confirm Passwod, Device_type should not be empty.");	
 		 }
 		 else if($arr["password"] != $arr["cpassword"]){
 			$err=1;
 			$this->session->set_flashdata("errormsg","Password should not matche");	
 		 }
  		 else if(!$this->common->validate_email($arr["email"])){
 			$err=1;
 			$this->session->set_flashdata("errormsg","Email should be valid");	
 		 }
  		 else if(!$this->common->check_email_availabilty($arr["email"], $arr["id"])){
 			$err=1;
 			$this->session->set_flashdata("errormsg","Email already exists.");	
 		 }
 		 else if(!$this->common->check_username_availabilty($arr["username"],$arr["id"])){
 			$err=1;
 			$this->session->set_flashdata("errormsg","Error : Username already exists.");	
 		 }
   		 else{
 			$err=0;
 		 }
 		 if($err == 1){
 			return false;	
 		 }
 		 else{
 			return true;	
 		 }
 	}

 
//for forget password

	public function validate_forgot_password($arr){
 		$err=0;
 		if($arr == ""){
 			$this->session->set_flashdata("errormsg","Error : Please enter your email id");
 			$err=1;	
 		}
 		else if(!$this->common->validate_email($arr)){
 			$this->session->set_flashdata("errormsg","Error : Email you entered is not valid");
 			$err=1;		
 		}
 		else if($this->common->check_email_availabilty($arr, '')){
 				$this->session->set_flashdata("errormsg","Error : Email id not exist or your account not active, please enter correct email id ");
 			    $err=1;		
 		}
 		else if(!$this->common->getuser_id_new($arr)){
 				$this->session->set_flashdata("errormsg","Error : Your account is currently inactive. Please contact admin.");	
 			    $err=1;		
 		}
  		if($err==0){
 			return true;	
 		}
 		else{
 			return false;	
 		}
 	}
	

 }
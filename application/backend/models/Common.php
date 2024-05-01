<?php 

 class common extends CI_Model {

    function __construct(){
        parent::__construct();
 		$http="";
 		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on"){
 			$http="http://";	
 		} else {
 			$http="https://";
 		}
 		$prevurl=array("prevurl"=>base_url());
 		$this->session->set_userdata($prevurl);
 		$this->session->set_userdata("currenturl",base_url());
    }

	public function get_extension($file_name){
 		$ext = end(explode('.', $file_name));
 		//$ext = array_pop($ext);
 		return strtolower($ext);
 	}
	
	public function get_extensions($file_name = NULL){
 		$ext1 = end(explode('.', $file_name));
 		if(!in_array($ext1, $this->config->item("allowedimages"))){
 			$this->session->set_flashdata("errormsg","Wrong file format only jpg, png, gif allowded");
 		} else {
 			return strtolower($ext1);
 		}
 	}

	public function get_extensions_attachement($file_name = NULL){
 		$ext1 = explode('.', $file_name);
 		$ext = array_pop($ext1);
 		if(!in_array($ext,$this->config->item("alloweddocs"))){
 			$this->session->set_flashdata("errormsg","Wrong file format only doc, docx allowded");
 		} else {
 			return strtolower($ext);
 		}
 	}

	public function generate_transaction_number($digits=10){
 		srand((double) microtime() * 10000000);
 		$input = array("A", "B", "C", "D", "E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a", "b", "c", "d", "e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
 		$random_generator="";
 		for ($i=1; $i<=$digits; $i++){
 			if(rand(1,2) == 1){
 				$rand_index = array_rand($input);
 				$random_generator .=$input[$rand_index];
 			} else {
 				$random_generator .=rand(1,9);
 			}
 		}
		return $random_generator;
 	}

	public function convert_date($arr){
 		$from_data=explode('/',$arr);
 		$from = strtotime($from_data[1]."-".$from_data[0]."-".$from_data[2]." "."00:00:00");
        return $from;
 	}

	public function convert_dateTime($date,$time){
		$from_data=explode('/',$arr);
		$from = strtotime($from_data[1]."-".$from_data[0]."-".$from_data[2]." "."00:00:00");
		return $from;
 	}

	public function convert_dateTimeFormat($date,$time){ 
		$from = strtotime($date." ".$time);
	    return $from;
 	}

	function validate_email($email = NULL){
 		return filter_var($email, FILTER_VALIDATE_EMAIL);	
 	}

 	public function check_authentication() {
  	 	$controllername=$this->router->class;
 	 	$methodname=$this->router->method;
 	 	if(($controllername=="login") && $methodname!="logout"){
 			$username=$this->session->userdata("username");
 			if(isset($username) && $username!=""){
 				redirect(base_url()."dashboard");	
 			}
 		}

		if($controllername !="login"){
 			$this->db->select("username");
 			$this->db->from("admin");
 			$query=$this->db->get();
 			$resultset=$query->row_array();
 			if($resultset["username"] != $this->session->userdata("username")){
 			   $this->session->set_flashdata("errormsg","Please login to access admin panel first");
 			   redirect(base_url()."login/"); 
 			}
  	   }
 	}

	public function checkpasswordvalidity($arr = NULL){
 		$this->db->select("*");
 		$this->db->from("admin");
 		$this->db->where("username",$this->session->userdata("username"));
 		$query = $this->db->get();
 		$result = $query->row_array();
 		if($this->validateHash($arr["oldpassword"],$result["password"])){
 			return false;	
 		} else {
 			return true;
		}
 	}

 	public function sort_array_of_array(&$array, $subfield, $order){
    	$order = (strtolower($order) == "desc") ? SORT_DESC : SORT_ASC;
	    $sortarray = array();
	    foreach ($array as $key => $row)
	    {
	        $sortarray[$key] = $row[$subfield];
	    }

	    array_multisort($sortarray, $order, $array);
	}

 	public function update_profile($arr = NULL){
 		$newarr["password"] = $this->salt_password(array("password" => $arr["newpassword"]));
 		if(isset($arr["username"]) && $arr["username"]!=""){
 			$newarr["username"] = $arr["username"];
 		}
 		return $this->db->update("admin",$newarr);
 	}

 	public function removeUrl($parametername = NULL,$querystring = NULL){
 		$newquerystring="";
 		$querystring=explode("&",$querystring);
 		foreach($querystring as $key=>$val){
 			$newval=explode("=",$val);
 			if($newval[0]!=""){
 				if($newval[0]!=$parametername){
 					$newquerystring.="&".$newval[0]."=".$newval[1];	
 				}
 			}
 		}
 		$newquerystring=substr($newquerystring,1,strlen($newquerystring));
 		return $newquerystring;
	}


	public function addUrl($parametername = NULL,$parametervalue = NULL,$querystring = NULL){
 		$querystring=explode("&",$querystring);
 		$newquerystring="";
 		$i=0;
 		if(count($querystring)!=0 && $querystring[0]!=""){
 			foreach($querystring as $key=>$val){
			  	$valnew=explode("=",$val);
			  	if($valnew[0]!=$parametername){
				  	$newquerystring.="&".$valnew[0]."=".$valnew[1];
			  	} else {
				  	$newquerystring.="&".$parametername."=".$parametervalue;	
				  	$i=1;
			  	}
 			}

 		}
 		if($i==0){
 			$newquerystring.="&".$parametername."=".$parametervalue;
 		}
 		return $newquerystring=substr($newquerystring,1,strlen($newquerystring));
 	} 	

 	public function is_logged_in(){
    	$is_logged_in = $this->session->userdata('is_logged_in');
 		if($is_logged_in != true){
 			$this->session->set_flashdata("errormsg","Authentication failed. You need to login to access this page");
 			redirect(base_url()."users/sign_in");
 			return false;
        } else {
 			$this->db->select("*");
 			$this->db->from("users"); 
 			$this->db->where(array("id"=>$this->session->userdata("userid"),"archive <>"=>"1", "users.status <>"=>"4"));
 			$result=$this->db->get();
 			$row=$result->row_array();
 			$num_row = $result->num_rows();
 		    $err=0;
 			if($num_row == 0){
 			  	$err =1;
 			} else {
 				$data_array= array("userid"=>$row['id']);
 				$this->session->set_userdata($data_array);
 				$err=0;
		  	}
		  	if($err==1){
 			   	redirect(base_url()."users/sign_in");
 			}
 		}
 	}	

	public function check_email_availabilty($email,$id){
 		$this->db->select('email');
 		$this->db->from('users');
 		if($id == ""){
 			$this->db->where(array('email'=>$email, "status <>" => 4));
 	    } else {
 			$this->db->where(array('email'=>$email, 'id <>'=>$id, "status <>" => 4));
 		}
 		$query=$this->db->get();
 		$resultset=$query->num_rows();
 		 return $resultset;
 	}

	public function check_username_availabilty($username,$id){
 		$this->db->select('username');
 		$this->db->from('users');
 		if($id!=""){
 			$this->db->where(array('username'=>$username, 'id <>'=>$id,  "status <>"=>4));
 		} else {
 			$this->db->where(array('username'=>$username,  "status <>" => 4));
 		}
 		$query=$this->db->get();
		//echo $this->db->last_query(); die;
 		$resultset=$query->num_rows();
  		 return $resultset;
  	}	
	

 	public function login_check_user_login($data){
 		 $login["email"]=trim($data['email']);	
 		 $login["password"]=trim($data['password']);
 		 $this->session->set_flashdata("tempdata",$login);
 		 if($this->common->authenticateUserLogin($login)){
 			redirect(base_url()."user/dashboard");
 		} else {
 			redirect(base_url()."home/login");
 		}
 	}

  	public function check_user_login(){ 
 		if(isset($_COOKIE['login']) || $this->session->userdata("user_id") != '' ){
 			$this->db->select("*");
 			$this->db->from("user");
 			$this->db->where(array("email" => $_COOKIE['login']));
 			$query=$this->db->get();
 			$this->db->last_query(); 
 			$result = $query->row_array();
 			if($result['status']==1){ 
 				$this->session->set_userdata("user_id",$result["id"]);
  				return true;
 			} else {
 				return false;
 			}
 		}
 		if($this->session->userdata("status")==1){
 			redirect(base_url()."user/");
 		}
 	}

 	public function salt_password($arr = NULL){
 		 $salt_key = $this->common->random_generator(2);
 		 $pas = md5($salt_key. $arr["password"]);
 		 $column = ':';
 		 return $pas.$column.$salt_key;
 	}

	public function validateHash($password = NULL, $hash = NULL){
 		$hashArr = explode(':', $hash);
 		if(md5($hashArr[1].$password) === $hashArr[0]){
 			return true;
 		} else {
 			return false;
 		}
 	}
	 
	 

 	public function empty_filed(){
 		return  $this->session->unset_userdata('tempdata'); 
 	}

 	public function random_generator($digits = NULL){
 		srand((double) microtime() * 10000000);
 		$input = array("A", "B", "C", "D", "E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a", "b", "c", "d", "e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
 		$random_generator="";
 		for ($i=1; $i<=$digits; $i++) {
 			if (rand(1,2) == 1) {
 				$rand_index = array_rand($input);
 				$random_generator .=$input[$rand_index];
 			} else {
 				$random_generator .=rand(1,9);
 			}
 		}
 		return $random_generator;
 	}	

	public function numeric_random_generator($digits = NULL){
 		srand((double) microtime() * 10000000);
 		$input = array("A", "B", "C", "D", "E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
 		$random_generator="";
 		for ($i=1; $i<=$digits; $i++) {
 			if (rand(1,2) == 1) {
 				$rand_index = array_rand($input);
 				$random_generator .=$input[$rand_index];
 			} else {
 				$random_generator .=rand(1,9);
 			}
 		}
 		return $random_generator;
 	}

  	public function rg($table, $column, $length){
 	  	$uniq = $this->common->random_generator($length);
 	  	$this->db->select('count(*) as count');
 	  	$this->db->from($table);
 	  	$this->db->where(array($column => $uniq));
 	  	$query = $this->db->get();
  	  	$verfied = $query->row_array();
 	  	if($verfied["count"] > 0){
 			return $this->common->rg($table, $column, $length); 
 	  	} else {
 			return $uniq;
 	  	}
	}

	public function rg1($table, $column, $length){
		$uniq = $this->common->numeric_random_generator($length);
		$this->db->select('count(*) as count');
		$this->db->from($table);
		$this->db->where(array($column => $uniq));
		$query = $this->db->get();
		$verfied = $query->row_array();
		if($verfied["count"] > 0){
			return $this->common->rg($table, $column, $length);
		} else {
			return $uniq;
		}
	}

  	public function get_time_stamp($date = NULL){
 		$date=explode("-",$date);
 		$date=$date[2]."-".$date[1]."-".$date[0];
 		$time=strtotime($date);
 		return $time;
 	}

   	public function get_data_passing_id($table, $id){
 		$this->db->select("*");
 		$this->db->from("$table");
 		$this->db->where(array("status"=>1, "archive" => "0", 'id' => $id));
 		$result=$this->db->get();
		$c=$result->row_array();
	 	return $c;
 	}

    public function convert_time_days($time){
   		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   		$lengths = array("60","60","24","7","4.35","12","10");     
	   	$now = time();     
	   	$difference = $now - $time;
	   	$tense = "ago";     
	   	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	    	$difference /= $lengths[$j];
	   	}     
	   	$difference = round($difference);   
	   	if($difference != 1) {
	    	$periods[$j].= "s";
	   	}     
	   	return "$difference $periods[$j] ago "; 
 	} 

  	public function update_forgot_password($arr){ 
 		$email = $arr["email"];
		$username = $arr["username"];
 		unset($arr["email"]);
		unset($arr["username"]);
		if($username <> "" && $email <> ""){
 			$this->db->where(array("username"=>$username, "email" => $email, 'status <>' => "4", 'archive'=>0));
		} else if($username <> "" && $email == ""){
 			$this->db->where(array("username"=>$username, 'status <>' => "4", 'archive'=>0));
		} else if($username == "" && $email <> ""){
 			$this->db->where(array("email"=>$email, 'status <>' => "4", 'archive'=>0));
		}

 		if($this->db->update("users",$arr)){
			$result = true;
		} else {
			$result = false;
		}
 		return  $result;
 	}

	public function validateUserEmailDB($arr){ 
		$this->db->select("*");
		$this->db->from("users");
		if($arr['id'] <> ''){
			$this->db->where(array("id <>" => $arr['id'], "email" => $arr['email'], "status <>" => 4));
		} else {
			$this->db->where(array("email" => $arr['email'], "status <> " => 4));
		}
		$query = $this->db->get();
		return $query->num_rows();	
 	}

	public function email_exist($email, $id){ 
		$this->db->select("*");
		$this->db->from("users");
		if($id <> ''){
			$this->db->where(array("email" => $email, "status <>" => 4, "id <>" => $id));
		}
		else{
			$this->db->where(array("email" => $email, "status <>" => 4));
		}
		$query = $this->db->get();
		return $query->num_rows();	
 	}

	public function username_exist($username, $id){ 
		$this->db->select("*");
		$this->db->from("users");
		if($id <> ''){
			$this->db->where(array("username" => $username, "status <>" => 4, "id <>" => $id));
		}
		else{
			$this->db->where(array("username" => $username, "status <>" => 4));
		}
		$query = $this->db->get();
		return $query->num_rows();	
 	}
	
	public function phone_exist($phone, $id){ 
		$this->db->select("*");
		$this->db->from("users");
		if($id <> ''){
			$this->db->where(array("phone" => $phone, "status <>" => 4, "id <>" => $id));
		}
		else{
			$this->db->where(array("phone" => $phone, "status <>" => 4));
		}
		$query = $this->db->get();
		return $query->num_rows();	
 	}
	
	public function compressImage($ext, $uploadedfile, $path, $actual_image_name, $newwidth){
 		if($ext=="jpg" || $ext=="jpeg" ){
			$src = imagecreatefromjpeg($uploadedfile);
		}
		else if($ext=="png"){
			$src = imagecreatefrompng($uploadedfile);
 		}
		else if($ext=="gif"){
			$src = imagecreatefromgif($uploadedfile);
		}
		else{
			$src = imagecreatefromwbmp($uploadedfile);
		}
 		list($width,$height) = getimagesize($uploadedfile);
		$newheight = ($height/$width)*$newwidth;
		$tmp       = imagecreatetruecolor($newwidth,$newheight);
		if($ext=="png"){
 			imagecolortransparent($tmp, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
			imagealphablending($tmp, false);
			imagesavealpha($tmp, true);
		}
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		$filename  = $path.$newwidth.'_'.$actual_image_name; //PixelSize_TimeStamp.jpg
		imagejpeg($tmp,$filename,100);
		imagedestroy($tmp);
		return $filename;
	}	
	 
	function validateAccessToken() {
		$access_token = "";
	//	$headers = getallheaders();
		$headers = []; //print_r($_SERVER);
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		$token = isset($headers['Vauthtoken']) ? urldecode($headers['Vauthtoken']) : "";

		if(substr($token, 0, 7) === 'Zoptal ') {
			$access_token = substr($token, 7);
		}
		
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('access_token' => $access_token, "status" => 1));
		$query = $this->db->get(); //echo $this->db->last_query(); die;
		return $query->row_array();
 	} 
	 
 
 	 public function totalUsersRegistedAdmin($type){
	   $this->db->select("*");
	   $this->db->from("users");
	   $this->db->where(array("status <>" => 4, "user_type" => $type));
	   $query = $this->db->get();
	   $countrows = $query->num_rows();
	   return $countrows;
	} 
	 
 	public function totalHoltelRegisted($user_id){
	   $this->db->select("*");
	   $this->db->from("hotels");
	   $this->db->where(array("status <>" => 4, "chain_manager" => $user_id));
	   $query = $this->db->get();
	   $countrows = $query->num_rows();
	   return $countrows;
	}
	 
 	public function totalUsersRegisted($user_type, $login_as, $user_id){
	   $this->db->select("*");
	   $this->db->from("users");
	   $this->db->where(array("status <>" => 4, $login_as => $user_id, "user_type" => $user_type));
	   $query = $this->db->get();
	   $countrows = $query->num_rows();
	   return $countrows;
	}
	 
 	public function totalHoltelUsersRegisted($type, $hotel_id){
	   $this->db->select("*");
	   $this->db->from("users");
	   $this->db->where(array("status <>" => 4, "user_type" => $type, "hotel_id" => $hotel_id));
	   $query = $this->db->get();
	   $countrows = $query->num_rows(); //echo $this->db->last_query(); die;
	   return $countrows;
	}
	 
 	public function totalHoltelManagerUsersRegisted($type, $user_id, $user_type){
	   $this->db->select("*");
	   $this->db->from("users");
	   $this->db->where(array("status <>" => 4, "user_type" => $type, $user_type => $user_id));
	   $query = $this->db->get();
	   $countrows = $query->num_rows(); //echo $this->db->last_query(); die;
	   return $countrows;
	}
	 
 	public function forgotAdminEmailTemplate($userData){
		$message   = "<p>Dear ".$userData['username']."</p>";
 		$message  .= "<p>As per your forgot password request, Your Login information as below: </p>";
		$message  .= "<p>Username: ".$userData['username']."</p>";
		$message  .= "<p>Password: ".$userData['password']."</p>";
		$message  .= "<p>Thanks, </p>";
		$message  .= "<p>".$this->config->item('sitename')."</p>";
		return $message;
	}
	 
     public function getIndUserData($user_id){
        $this->db->select("*");
        $this->db->from('users');
        $where = array("id" => $user_id, "status <> " => "4");
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->row_array();
        return $resultset;
    } 
		
	public function check_already_checked($hotel_id, $user_id, $checking){
        $this->db->select("*");
        $this->db->from('booking');
		if($checking == 2){
        	$where = array("user_id" => $user_id, "hotel_id" => $hotel_id,  "user_check_in <> " => 0, "user_check_out" => 0, "status <>" => 4);
		}
		else{
			$where = array("user_id" => $user_id, "hotel_id" => $hotel_id,  "user_check_in <> " => 0, "user_check_out <>" => 0, "status <>" => 4);
		}
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->num_rows();
        return $resultset;
	}
 	 

	/////////////////////////////////////////////////////////////////////////////////////////////

}

if(FRONTPATH !="frontend"){
 	$common= new common;
 	$common->check_authentication();
 	$common->check_user_login();
}
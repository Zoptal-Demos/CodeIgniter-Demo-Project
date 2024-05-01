<?php
class Users_model extends CI_Model{
    function __construct(){
        parent::__construct();
    }
	
 	public function getLocationManagerData($searcharray = array(), $user_type, $user_id){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."' AND chain_manager='".$user_id."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR phone like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
 			$sql .= " AND (chain_manager = '".$searcharray["sort"]."')";
        }
		if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
 			$sql .= " AND (location_manager = '".$searcharray["lsort"]."')";
        }
		if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
 			$sql .= " AND (manager = '".$searcharray["msort"]."')";
        }
		if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
 			$sql .= " AND (employee = '".$searcharray["esort"]."')";
        }
		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
       //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }
	
 	public function getuserData($searcharray = array(), $user_type){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR phone like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
 			$sql .= " AND (chain_manager = '".$searcharray["sort"]."')";
        }
		if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
 			$sql .= " AND (location_manager = '".$searcharray["lsort"]."')";
        }
		if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
 			$sql .= " AND (manager = '".$searcharray["msort"]."')";
        }
		if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
 			$sql .= " AND (employee = '".$searcharray["esort"]."')";
        }
		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
       //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }
	
    public function view_user($id){
        $this->db->select("*");
        $this->db->from('users');
        $where = array("id" => $id,"archive <> " => 1);
        $this->db->where($where);
        $query     = $this->db->get();
        //echo $this->db->last_query();
        $resultset = $query->row_array();
        return $resultset;
    }
    public function enable_disable_user($id, $status){
        $this->db->where("id", $id);
		if($status == 2){
			 $arr = array("status" => $status, "access_token" => "", "device_token" => "");
		}
		else{
        	$arr = array("status" => $status);
		}
        return  $this->db->update("users", $arr);
       // echo  $this->db->last_query(); die;
    }
	
    public function deleteChainManager($id){
 		$this->db->where("id", $id);
		$res = $this->db->delete('users');
 		if($res){
			//get list hotel realted to chain manager
			$this->db->select("id");
			$this->db->from('hotels');
			$where = array("chain_manager" => $id);
			$this->db->where($where);
			$query     = $this->db->get();
			//echo $this->db->last_query();
			$resultset = $query->result_array();
			foreach($resultset as $k => $v){
				//delete booking/check in/out
				$this->db->where(array("hotel_id" => $v['id']));
				$this->db->delete('booking');
				
				//delete staff related to hotel
				$this->db->where(array("hotel_id" => $v['id'], 'user_type <>' => 5));
				$this->db->delete('users');
				
				//delete hotel related to chain manager
				$this->db->where(array("id" => $v['id']));
				$this->db->delete('hotels');
			}
 		}
  		return $res;
     }
	 
	//for set user password
	public function changePassword($arr){
		$id = $arr["id"];
		unset($arr["id"]);
		$this->db->where("id", $id);
		$res = $this->db->update('users', $arr);
		if($res){
			$response = true;
		}
		else{
			$response = false;
		}
		return $response;
	}

	
	//for update user profile data
	 public function add_user($arr){
		 if($arr["id"] <> ''){
		    $arr["time"] = time();
			$id          = $arr["id"];
			unset($arr["id"]);
			$this->db->where(array("id" => $id));
			$result = $this->db->update("users",$arr);
			//echo $this->db->last_query(); die;
		 }
		 else{
			  unset($arr['id']);
			  $arr['status']     = 1;
			  $arr["time"]       = time();
			  $arr["created_on"] = time();
			  $result = $this->db->insert("users",$arr);
			 // echo $this->db->last_query(); die;
 		 }
 		  return $result;
	 }
	
    //for user category	
    public function getIndividualUser($user_id){
        $this->db->select("*");
        $this->db->from('users');
        $where = array("id" => $user_id, "status <> " => "4");
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->row_array();
        return $resultset;
    } 
	
	 
    //for paypal	
    public function getpaypal(){
        $id = $this->session->userdata("id");
        $this->db->select('*');
        $this->db->from('admin');
        $this->db->where(array('id' => $id));
        //$this->db->limit(1);
        //$this->db->order_by("services.id DESC");
        $query     = $this->db->get();
        //echo $this->db->last_query();
        $resultset = $query->row_array();
        return $resultset;
    }
    public function edit_paypal($arr){
        $id = $arr["id"];
        unset($arr["id"]);
        $this->db->where("id", $id);
        $result = $this->db->update("admin", $arr);
        //echo $this->db->last_query(); die;
        return $result;
    }
	
	public function countNoOfPostsCreatedByUser($user_id){
		$this->db->select("*");
 		$this->db->from("challenges");
 		$this->db->where(array("status <>" => 4, "user_id" => $user_id));
 		$result = $this->db->get();
 		$countrows = $result->num_rows();
		return $countrows;
	}
 	
 	
	public function getPostFlagsData($searcharray = array()){
        $recordperpage = "";
        $startlimit    = "";
        if(!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if(!isset($searcharray["countdata"])){
            if(isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if(isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM videos_flags where status <> 4";
        if(isset($searcharray["search"]) && $searcharray["search"] != ""){
         }
		$sql .= " order by id desc";
        if(isset($searcharray["page"]) && $searcharray["page"] != ""){
            if($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " limit  $startlimit,$recordperpage";
            }
        }
		
         $query     = $this->db->query($sql);
         //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        return $resultset;
    }
	
    public function enable_disable_post($id, $status, $user_id){
        $this->db->where("id", $id);
        $arr = array("status" => $status);
        $res = $this->db->update("challenges", $arr);
		if($res){
			if($status == 2){
				$arr2['status'] = 1;
			}
			else if($status == 1){
 				$arr2['status'] = 2;
			}
			$this->db->where(array("id" => $user_id));
			$this->db->update('videos_flags', $arr2);
		}
		 return $res;
    }
	
	
	public function getUserList($user_type){
		$this->db->select("*");
        $this->db->from('users');
        $where = array("user_type" => $user_type, "status <> " => "4");
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;
	}
 	
	public function getLocationManagerList($rel_id, $user_type){
		$this->db->select("*");
        $this->db->from('users');
        $where = array("chain_manager" => $rel_id, "status <> " => "4", 'user_type' => $user_type);
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;
	}
	
	
	
	public function getManagerListAdmin($rel_id, $user_type){
		$this->db->select("*");
        $this->db->from('users');
        $where = array("status <> " => "4", "location_manager" => $rel_id, "user_type" => $user_type);
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get(); //echo $this->db->last_query(); die;
        $resultset = $query->result_array();
        return $resultset;
	}
	
	public function getManagerList($rel_id, $user_type, $uType){
		$this->db->select("*");
        $this->db->from('users');
        $where = array("status <> " => "4", $uType => $rel_id, "user_type" => $user_type);
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get(); //echo $this->db->last_query(); die;
        $resultset = $query->result_array();
        return $resultset;
	}
	
	public function getEmployeesList($rel_id, $user_type){
		$this->db->select("*");
        $this->db->from('users');
        $where = array("manager" => $rel_id, "status <> " => "4", 'user_type' => $user_type);
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;
	}
    /////////////// user Booking  @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    public function getChekedInubookData($searcharray = array(), $user_type){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
         $sql = "SELECT users.id, users.name, hotels.id, hotels.name as hotel, booking.* FROM `booking` LEFT join users on users.id = booking.user_id   LEFT join hotels on hotels.id =booking.hotel_id WHERE booking.status <>4 "; 
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (users.name like '%".$searcharray["search"]."%' OR hotels.name like '%".$searcharray["search"]."%')";
        }
         if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by booking.id desc limit  $startlimit,$recordperpage";
            }
        }
        $sql .= " ";
         $query = $this->db->query($sql);
       // echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }

     public function getIndividualbooking($user_id){
        $this->db->select("*");
        $this->db->from('booking');
        $where = array("id" => $user_id, "status <> " => "4");
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->row_array();
        return $resultset;
    } 

    public function getallUserData(){

        $this->db->select("id, name");
        $this->db->from('users');
        $where = array("user_type"=> "5", "status <> " => "4");
        $this->db->where($where);
		 $this->db->order_by('name ASC');
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;

    }
    public function getallhotelData(){

        $this->db->select("id, name");
        $this->db->from('hotels');
        $where = array( "status <> " => "4");
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;

    }   
    public function add_booking($arr){ 
         if($arr["id"] <> ''){
            $arr["time"] = time();
            $id          = $arr["id"];
            unset($arr["id"]);
            $this->db->where(array("id" => $id));
            $result = $this->db->update("booking",$arr);
            //echo $this->db->last_query(); die;
         }
         else{
              unset($arr['id']); 
              $arr['status']     = 1;
              $arr["time"]       = time();
              $result = $this->db->insert("booking",$arr);
              //echo $this->db->last_query(); die;
         }
          return $result;
     }
      public function enable_disable_booking($id, $status){
        $this->db->where("id", $id);
        $arr = array("status" => $status);
        return $this->db->update("booking", $arr);
        //return $this->db->last_query();
    }
    public function archive_booking($id){
        $this->db->where("id", $id);
        $arr = array("archive" => 1, "status" => 4);
        $res = $this->db->update("booking", $arr); //echo $this->db->last_query(); die;
        return $res;
        //return $this->db->last_query();
    }

    /*********************** Check In/out ****************************/
	
    public function getChekedInuserData($searcharray = array(), $user_type){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        //$sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."' AND checkin != 0 AND checkout = 0";
        $sql = "SELECT * FROM users  JOIN booking on booking.user_id = users.id where users.status <> 4 AND user_type = '".$user_type."' AND booking.user_check_in != 0 AND booking.user_check_out = 0"; 
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (users.email like '%".$searcharray["search"]."%' OR users.name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
            $sql .= " AND (booking.hotel_id = '".$searcharray["sort"]."')";
        }
        if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
            $sql .= " AND (users.location_manager = '".$searcharray["lsort"]."')";
        }
        if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
            $sql .= " AND (users.manager = '".$searcharray["msort"]."')";
        }
        if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
            $sql .= " AND (users.employee = '".$searcharray["esort"]."')";
        }
        
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by users.id desc limit  $startlimit,$recordperpage";
            }
        }
        $sql .= " ";
         $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }

    public function getChekedOutuserData($searcharray = array(), $user_type){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users  JOIN booking on booking.user_id = users.id where users.status <> 4 AND user_type = '".$user_type."' AND booking.user_check_in != 0 AND booking.user_check_out != 0"; 
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (users.email like '%".$searcharray["search"]."%' OR users.name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
            $sql .= " AND (booking.hotel_id = '".$searcharray["sort"]."')";
        }
        if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
            $sql .= " AND (users.location_manager = '".$searcharray["lsort"]."')";
        }
        if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
            $sql .= " AND (users.manager = '".$searcharray["msort"]."')";
        }
        if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
            $sql .= " AND (users.employee = '".$searcharray["esort"]."')";
        }
        
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by users.id desc limit  $startlimit,$recordperpage";
            }
        }
       
        $sql .= " ";
         $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }

    public function getHotelData($searcharray = array(), $user_id){ 
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM hotels where status <> 4 AND chain_manager = '".$user_id."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (location like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }
      
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
        $sql .= " ";
         $query = $this->db->query($sql);
       //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }
    public function getUserList1($user_type){
        $this->db->select("*");
        $this->db->from('hotels');
        $where = array( "status  " => 1);
        $this->db->where($where);
        $this->db->order_by('name asc');
        $query     = $this->db->get();
        $resultset = $query->result_array();
        return $resultset;
    }
     public function add_hotel($arr){
         if($arr["id"] <> ''){
            $arr["time"] = time();
            $id          = $arr["id"];
            unset($arr["id"]);
            $this->db->where(array("id" => $id));
            $result = $this->db->update("hotels",$arr);
            //echo $this->db->last_query(); die;
         }
         else{
              unset($arr['id']);
              $arr['status']     = 1;
              $arr["time"]       = time();
              $arr["created_at"] = time();
              $result = $this->db->insert("hotels",$arr);
              //echo $this->db->last_query(); die;
         }
          return $result;
     }
    public function view_hotel($id){
        $this->db->select("*");
        $this->db->from('hotels');
        $where = array("id" => $id,"archive <> " => 4);
        $this->db->where($where);
        $query     = $this->db->get();
        //echo $this->db->last_query();
        $resultset = $query->row_array(); //print_r( $resultset);
        return $resultset;
    }
	
    public function enable_disable_hotel($id, $status){
        $this->db->where("id", $id);
        $arr = array("status" => $status);
       
        return  $this->db->update("hotels", $arr);
         //echo  $this->db->last_query(); die;
    }
    
    public function archive_hotel($id){
        $this->db->where("id", $id);
		$res = $this->db->delete('hotels');
        //$arr = array("archive" => 1, "status" => 4);
        //$res = $this->db->update("hotels", $arr); //echo $this->db->last_query(); die;
		if($res){
			//delete booking/check in/out
			$this->db->where(array("hotel_id" => $id));
			$this->db->delete('booking');
			
			//delete staff related to hotel
			$this->db->where(array("hotel_id" => $id, 'user_type <>' => 5));
			$this->db->delete('users');

		}
        return $res;
        //return $this->db->last_query();
    }

     public function getIndividualhotel($id){
        $this->db->select("*");
        $this->db->from('hotels');
        $where = array("id" => $id, "status <> " => "4");
        $this->db->where($where);
        $query     = $this->db->get();
        $resultset = $query->row_array();
        return $resultset;
    } 
	
	/* ******************* for frontend panel 30-01-2020 ****************************** */
	 public function getManagerData($searcharray = array(), $user_type, $uType, $user_id){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."' AND ".$uType."='".$user_id."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR phone like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
 			$sql .= " AND (hotel_id = '".$searcharray["sort"]."')";
        }
		if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
 			$sql .= " AND (location_manager = '".$searcharray["lsort"]."')";
        }
		if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
 			$sql .= " AND (manager = '".$searcharray["msort"]."')";
        }
		if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
 			$sql .= " AND (employee = '".$searcharray["esort"]."')";
        }
		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
        // echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }
	
	public function getHotelsList($user_id){
        $this->db->select("id, name, location");
        $this->db->from('hotels');
        $where = array("chain_manager" => $user_id, "status" => 1);
        $this->db->where($where);
        $this->db->order_by('name, location', 'ASC');
        $query     = $this->db->get(); //echo $this->db->last_query(); die;
        $resultset = $query->result_array();
        return $resultset;
	}
	
	public function getAllLocationManagerList($user_id){
        $this->db->select("id, hotel_id, chain_manager, name");
        $this->db->from('users');
        $where = array("chain_manager" => $user_id, "status" => 1, "user_type" => 2);
        $this->db->where($where);
        $this->db->order_by('name', 'ASC');
        $query     = $this->db->get(); //echo $this->db->last_query(); die;
        $resultset = $query->result_array();
        return $resultset;
	}
	
	
 	public function getEmployeesData($searcharray = array(), $user_type, $uType, $user_id){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."' AND $uType = '".$user_id."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR  name like '%".$searcharray["search"]."%')";
        }
 		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
        // echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }

 	public function getUsersDataList($searcharray = array(), $user_type, $uType, $user_id){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
        $sql = "SELECT * FROM users where status <> 4 AND user_type = '".$user_type."' AND $uType='".$user_id."'";
        if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR phone like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }
        if (isset($searcharray["sort"]) && $searcharray["sort"] != ""){
 			$sql .= " AND (hotel_id = '".$searcharray["sort"]."')";
        }
		if (isset($searcharray["lsort"]) && $searcharray["lsort"] != ""){
 			$sql .= " AND (location_manager = '".$searcharray["lsort"]."')";
        }
		if (isset($searcharray["msort"]) && $searcharray["msort"] != ""){
 			$sql .= " AND (manager = '".$searcharray["msort"]."')";
        }
		if (isset($searcharray["esort"]) && $searcharray["esort"] != ""){
 			$sql .= " AND (employee = '".$searcharray["esort"]."')";
        }
		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
    }
	
	public function getCountryCodeList(){
        $this->db->select("phonecode");
        $this->db->from('country');
        $where = array("phonecode <>" => 0);
        $this->db->where($where);
        $this->db->group_by('phonecode');
        $this->db->order_by('phonecode', 'ASC');
        $query     = $this->db->get(); //echo $this->db->last_query(); die;
        $resultset = $query->result_array();
        return $resultset;
	}
	
	public function updateProfileData($arr){
		$id = $arr["id"];
		unset($arr["id"]);
		$this->db->where("id", $id);
		return $this->db->update('users', $arr);
	}
	
	public function saveNotifications($arr){
 		$arr['seen'] = 0;
 		$arr["date"] = time();
		$arr["time"] = time();
		$result = $this->db->insert("notifications", $arr);
		return $this->db->insert_id();
	}
	
	public function getHotelNotificationData($hotel_id){
		$sql = "SELECT * FROM notifications where id in (SELECT max(id) FROM notifications where status <> 4 AND seen = 0 AND receiver = '".$hotel_id."' GROUP BY sender) order by id desc limit 20";
		$query = $this->db->query($sql);		 
		//echo $this->db->last_query(); die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getHotelNotificationsData($searcharray = array(), $hotel_id){
        $recordperpage = "";
        $startlimit    = "";
        if (!isset($searcharray["page"]) || $searcharray["page"] == ""){
            $searcharray["page"] = 0;
        }
        if (!isset($searcharray["countdata"])){
            if (isset($searcharray["per_page"]) && $searcharray["per_page"] != ""){
                $recordperpage = $searcharray["per_page"];
            }
            else{
                $recordperpage = 1;
            }
            if (isset($searcharray["page"]) && $searcharray["page"] != ""){
                $startlimit = $searcharray["page"];
            }
            else{
                $startlimit = 0;
            }
        }
		$sql = "SELECT * FROM notifications where id in (SELECT max(id) FROM notifications where status <> 4 AND receiver = '".$hotel_id."' GROUP BY sender)";
         /*if (isset($searcharray["search"]) && $searcharray["search"] != ""){
            $sql .= " AND (email like '%".$searcharray["search"]."%' OR phone like '%".$searcharray["search"]."%' OR name like '%".$searcharray["search"]."%')";
        }*/
 		
        if (isset($searcharray["page"]) && $searcharray["page"] != ""){
            if ($recordperpage != "" && ($startlimit != "" || $startlimit == 0)){
                $sql .= " order by id desc limit  $startlimit,$recordperpage";
            }
        }
		$sql .= " ";
         $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        $resultset = $query->result_array();
        //print_r($resultset);die;
        return $resultset;
	}
	
	public function archive_notifi($sender, $receiver){
		$where = array("sender" => $sender, "receiver" => $receiver);
 		$this->db->where($where);
		return $this->db->delete("notifications");
	}
	
    public function archive_user($id){
        $this->db->where("id", $id);
        $arr = array("archive" => 1, "status" => 4, "access_token" => "", "device_token" => "");
        $res = $this->db->update("users", $arr); //echo $this->db->last_query(); die;
        return $res;
        //return $this->db->last_query();
    }


    public function checkUserAlreadyCheckout($arr, $type){
        $this->db->select("*");
        $this->db->from('booking');
        $where = "user_id = '".$arr['user_id']."' AND hotel_id = '".$arr['hotel_id']."'";
        if($type == 2){
            $where .= " AND user_check_out <> 0";
        }
        else{
            $where .= " AND user_check_out = 0";
        }
        $this->db->where($where);
        $this->db->order_by("id DESC");
        $query     = $this->db->get();
        $resultset = $query->num_rows();
        return $resultset;

    }
	
 	
     //////////////////////////////////////////////////////////////////////////////	
}
?>
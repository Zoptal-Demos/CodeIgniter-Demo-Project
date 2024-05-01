<?php
class User_model extends CI_Model{

	function __construct(){
		parent::__construct();
	}

	public function ulogin($arr){
		$sql = "select * from users where (email = '".$arr['email']."') AND archive = 0"; //username = '".$arr['email']."' OR 
		$query = $this->db->query($sql);
		$result = $query->row_array();
		if(!empty($result)){
			if($this->common->validateHash($arr["password"], $result["password"])) {
				if ($result['status'] == 1) {
					$response = $result["id"];
				} else if ($result['status'] == 0) {
					$response = "inactive";
				} else if ($result['status'] == 2) {
					$response = "block";
				}
			} else {
				$response = "wrong_pass";
			}
		} else {
			$response = "wrong_user";
		}
		return $response;
	}

	
	public function changePassword($arr){
		$this->db->select("*");
		$this->db->from('users');
		$this->db->where(array("id" => $arr['user_id'], "status" => 1, "archive <> " => 1));
		$query = $this->db->get();
		$result = $query->row_array();
		$response = false; //echo $arr["old_password"].' - ';print_r($result); die;
		if($this->common->validateHash($arr["old_password"], $result["password"])) {
			$id = $arr["user_id"];
			unset($arr["user_id"]);
			unset($arr["old_password"]);
			$this->db->where("id", $id);
			$this->db->update('users', $arr);
			$response = true;
		}
		return $response;
	}

	public function get_user_list($id, $blocked_users){
		$this->db->select("id, username, name, profile_pic");
		$this->db->from('users');
		$where = array("status" => 1, "user_type" => 2, "id <>" => $id);
		$this->db->where($where);
		if(!empty($blocked_users)){
			$this->db->where_not_in('id', $blocked_users);
		}
		$this->db->order_by("username asc");
		$query = $this->db->get();
		return $query->result_array();
	}

	public function user_profile_data($user_id){ 
		$this->db->select("*");
		$this->db->from('users');
		$where = array("id" => $user_id, 'archive <> ' => 1, 'status =' => 1);
		$this->db->where($where);
		$query = $this->db->get();
	//echo $this->db->last_query(); die;
		return $query->row_array();
	}
  
	public function add_edit_user($arr){
		if ($arr["oldimage"] == 1) {
			$this->db->select("*");
			$this->db->from("users");
			$this->db->where("id", $arr["user_id"]);
			$query = $this->db->get();
			$Images = $query->row_array();
			$Images['image'];
			unlink('pics/' . $Images['image']);
		}

		$id = $arr["id"];
		unset($arr["id"]);
		unset($arr["oldimage"]);
		$this->db->where("id", $id);
		$result = $this->db->update("users", $arr);
		return $result;
	}

 	public function add_user($arr) {
		$arr["created_on"] = time();
		$arr["last_login"] = time();
		$arr["time"]       = time();
		$arr["status"]     = 1;
		$res = $this->db->insert("users", $arr);
		 //echo $this->db->last_query(); die;
		return $res;
		
	}
	
	// for signup/login with facebook
	public function add_user_fb($arr){
   		$this->db->select("*");	
		$this->db->from('users');
		$where = "email = '".$arr["email"]."' OR fb_id='".$arr["fb_id"]."'";
   		$this->db->where($where);
  		$query = $this->db->get();
 		$num_row = $query->num_rows();
		if($num_row == 0){
			$arr['status']      = 1;
			$arr["time"]        = time();
			$arr["last_login"]  = time();
			$arr["created_on"] = time();
			$this->db->insert("users", $arr);
			$result = $this->db->insert_id();
			return $result;
		}
		else{
			$resultr = $query->row_array();
			$fb_id = $arr["fb_id"];
			unset($arr["fb_id"]);
			$where = "email = '".$arr["email"]."' OR fb_id='".$fb_id."'";
			$this->db->where($where);
			// print_r($arr); die;
			$result = $this->db->update("users", $arr);
			//echo $this->db->last_query(); die;
			return $resultr['id'];
		}
	}
	
	public function validateFacebookId($fb_id){
   		$this->db->select("*");	
		$this->db->from('users');
   		$this->db->where(array('fb_id' => $fb_id));
  		$query = $this->db->get();
 		$res = $query->row_array();
		return $res;
	}
	
	function get_user_data($user_id){
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('id' => $user_id,"status <>" => 4, 'archive' => 0));
		$query = $this->db->get();
		return $query->row_array();
	}

	function get_user_data_by_email($email){
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('email' => $email, "status" => 1, 'archive' => 0));
		$query = $this->db->get();
		return $query->row_array();
	}

 	public function getUserDataByAccessToken($access_token){
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('access_token' => $access_token, "status" => 1));
		$query = $this->db->get();
		return $query->row_array();
	}

 	public function userLogout($id){
		$data['access_token'] = "";
		$data['device_token'] = "";
		$data['device_type']  = "";
		$this->db->where(array('id' => $id));
		return $this->db->update("users", $data);
	}
	public function update_profile_data($arr){
		$id = $arr["id"];
		unset($arr["id"]);
		$this->db->where("id", $id);
		return $this->db->update('users', $arr);
	}
      
	public function getUserDetails($user_id){
 		$this->db->select('*');
 		$this->db->from('users');
 		$this->db->where(array('id' => $user_id));
 		$query  = $this->db->get();
 		return $query->row_array(); 		
 	}
 	
	public function getAllUserDataByAccessToken($user_id){
 		$this->db->select('*');
 		$this->db->from('users');
 		$this->db->where(array('id <> ' => $user_id, "status" => 1));
 		$query  = $this->db->get();
 		return $query->result_array(); 		
 	}
	
	public function verify_email($id){
		$arr = array("verified" => 1);
		$this->db->where(array('id' => $id));
		$result = $this->db->update("users", $arr);
		//echo $this->db->last_query(); die;
		if ($result) {			
			$data = 1;
		} else {
			$data = 0;
		}
		return $data;
	}
	 
	
	public function getAppUserList($user_id, $blocked_users){
		$this->db->select('*');
 		$this->db->from('users');
 		$this->db->where(array("status" => 1, "id <>" => $user_id));
		if(!empty($blocked_users)){
			$this->db->where_not_in('id', $blocked_users);
		}
 		$this->db->order_by("name ASC");
 		$query  = $this->db->get();
		//echo $this->db->last_query(); die;
 		return $query->result_array(); 
	}
	
	public function getCategoriesList(){
 		$this->db->select('*');
 		$this->db->from('categories');
 		$this->db->where(array("status" => 1, 'type' => 0));
 		$this->db->order_by("title ASC");
 		$query  = $this->db->get();
 		return $query->result_array(); 		
	}
	
	public function getTagsList(){
 		$this->db->select('*');
 		$this->db->from('categories');
 		$this->db->where(array("status" => 1, 'type' => 1));
 		$this->db->order_by("title ASC");
 		$query  = $this->db->get();
 		return $query->result_array(); 		
	}
	
	public function getTagsByCategList($cat){
 		$this->db->select('*');
 		$this->db->from('categories');
 		$this->db->where(array("status" => 1, 'type' => 1, "category" => $cat));
 		$this->db->order_by("title ASC");
 		$query  = $this->db->get();
 		return $query->result_array(); 		
	}
	
	public function getTagsData($id){
 		$this->db->select('*');
 		$this->db->from('categories');
 		$this->db->where(array("status" => 1, 'id' => $id));
  		$query  = $this->db->get();
 		return $query->row_array(); 		
	}
	
	public function createPost($arr){
		$arr['status']     = 1;
		$arr["time"]       = time();
 		$arr["created_on"] = time();
		$this->db->insert("challenges", $arr);
		$result = $this->db->insert_id();
		return $result;
	}
	
	public function getHomeFeedData($uesr_id, $blocked, $limit, $no_of_post){
 		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1));
		if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}
 		$this->db->order_by('id desc');
		$this->db->limit($no_of_post, $limit);
 		$query  = $this->db->get();
 		return $query->result_array();
 	}
	
	public function getFilteredChallengesList($arr, $trr, $limit, $no_of_post){
 		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1));
		if(!empty($trr)){
			$i=0;
			foreach($trr as $k => $v){
				if($i == 0){
					$this->db->where("FIND_IN_SET('$v', tags) != 0");
					$i++;
 				}
				else{
					$this->db->or_where("FIND_IN_SET('$v', tags) != 0");
				}
			}
		}
 		$this->db->order_by('id desc');
		$this->db->limit($no_of_post, $limit);
 		$query  = $this->db->get();
		//echo $this->db->last_query(); die;
 		return $query->result_array();
 	}
	
	public function getUserPosts($user_id, $blocked, $limit, $no_of_post){
 		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1, "user_id" => $user_id));
		if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		} 
		$this->db->where_in('user_id', $user_id);		 
 		$this->db->order_by('id desc');
		$this->db->limit($no_of_post, $limit);
 		$query  = $this->db->get();
 		return $query->result_array();
 	}
	
	public function getPostDetails($post_id){
 		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("id" => $post_id));
  		$query  = $this->db->get();
 		return $query->row_array();
 	}
	
	public function checkPostLiked($arr){
		$this->db->select("*");
		$this->db->from("challenges_likes");
		$where = "(user_id = '".$arr['user_id']."' AND post_id = '".$arr['post_id']."')";
		$this->db->where($where);
		$result = $this->db->get();
		$countrows = $result->num_rows();;
		return $countrows;
	}
	
	public function savePostLiked($arr){
  		$arr["time"] = time();
 		return $this->db->insert("challenges_likes", $arr);
	}
	
	public function deletePostLiked($arr){
		$where = array("user_id" => $arr['user_id'], "post_id" => $arr['post_id']);
 		$this->db->where($where);
		return $this->db->delete("challenges_likes");
	}
	
	public function savePostComment($arr){
  		$arr["time"] = time();
 		return $this->db->insert("challenges_comments", $arr);
	}	
	
	public function share_post($arr){
  		$arr["time"]    = time();
  		$arr["status"]  = 1;
  		return $this->db->insert("challenges_share", $arr); //echo $this->db->last_query(); die;
	}
	
	
	public function getNoOfShared($post_id){
		$this->db->select("*");
		$this->db->from("challenges_share");
 		$this->db->where(array("post_id" => $post_id));
		$result = $this->db->get();
		$countrows = $result->num_rows();
		return $countrows;
	}
	
	
	public function getUserListLikedPost($post_id){
		$this->db->select("*");
		$this->db->from("challenges_likes");
		$where = "post_id = '".$post_id."'";
		$this->db->where($where);
 		$this->db->order_by('id ASC');
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getCommentListPost($post_id){
		$this->db->select("*");
		$this->db->from("challenges_comments");		
		$where = "post_id = '".$post_id."'";
		$this->db->where($where);
 		$this->db->order_by('id ASC');
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getNoOfLikes($post_id){
		$this->db->select("*");
		$this->db->from("challenges_likes");
 		$this->db->where(array("post_id" => $post_id));
		$result = $this->db->get();
		$countrows = $result->num_rows();
		return $countrows;
	}		 
	
	public function getNoOfComments($post_id){
		$this->db->select("*");
		$this->db->from("challenges_comments");
 		$this->db->where(array("post_id" => $post_id));
		$result = $this->db->get();
		$countrows = $result->num_rows();
		return $countrows;
	}
	
	public function getMyPostedChallenges($user_id){
		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1, 'user_id' => $user_id));
  		$this->db->order_by('id', 'DESC');
		$this->db->limit(6);
 		$query  = $this->db->get();
 		return $query->result_array();
	}
	
	public function getMyAllPostedChallengesList($user_id, $limit, $no_of_post){
		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1, 'user_id' => $user_id));
  		$this->db->order_by('id', 'DESC');
		$this->db->limit($no_of_post, $limit);
 		$query  = $this->db->get();
 		return $query->result_array();
	}
	
	public function getChallengesDetails($arr){
		$this->db->select('*');
 		$this->db->from('challenges');
 		$this->db->where(array("status" => 1, 'id' => $arr['id']));
  		$query = $this->db->get();
 		return $query->row_array();
	}
	
	public function getMyLikedChallenges($user_id){
		$this->db->select('challenges_likes.*, challenges.id as challenge_id, challenges.title, challenges.image, challenges.video_image');
 		$this->db->from('challenges_likes');
 		$this->db->join('challenges', "challenges.id = challenges_likes.post_id");
 		$this->db->where(array("challenges.status" => 1, 'challenges_likes.user_id' => $user_id));
  		$this->db->order_by('challenges_likes.id', 'DESC');
		$this->db->limit(3);
 		$query  = $this->db->get();
 		return $query->result_array();
	}
	
	public function getMyLikedAllChallengesList($user_id, $limit, $no_of_post){
		$this->db->select('challenges_likes.*, challenges.id as challenge_id, challenges.title, challenges.image, challenges.video_image');
 		$this->db->from('challenges_likes');
 		$this->db->join('challenges', "challenges.id = challenges_likes.post_id");
 		$this->db->where(array("challenges.status" => 1, 'challenges_likes.user_id' => $user_id));
  		$this->db->order_by('challenges_likes.id', 'DESC');
		$this->db->limit($no_of_post, $limit);
		$query  = $this->db->get();
 		return $query->result_array();
	}
	
		
	public function getMyCommentedChallenges($user_id){
		$this->db->select('challenges_comments.*, challenges.id as challenge_id, challenges.title, challenges.image, challenges.video_image');
 		$this->db->from('challenges_comments');
 		$this->db->join('challenges', "challenges.id = challenges_comments.post_id");
 		$this->db->where(array("challenges.status" => 1, 'challenges_comments.user_id' => $user_id));
  		$this->db->group_by('challenges_comments.post_id');
		$this->db->order_by('challenges_comments.id', 'DESC');
		$this->db->limit(3);
 		$query  = $this->db->get();
 		return $query->result_array();
	}
	
	public function getMyCommentedAllChallengesList($user_id, $limit, $no_of_post){
		$this->db->select('challenges_comments.*, challenges.id as challenge_id, challenges.title, challenges.image, challenges.video_image');
 		$this->db->from('challenges_comments');
 		$this->db->join('challenges', "challenges.id = challenges_comments.post_id");
 		$this->db->where(array("challenges.status" => 1, 'challenges_comments.user_id' => $user_id));
  		$this->db->group_by('challenges_comments.post_id');
		$this->db->order_by('challenges_comments.id', 'DESC');
  		$query  = $this->db->get();
 		return $query->result_array();
	}
	
	
	/********************************* Follow/Unfllow Section *********************************/
	
 	public function getUserForFriendSuggestion($following, $blocked, $user_id){
 		$this->db->select('*');
 		$this->db->from('users');
 		$this->db->where(array("status" => 1, 'id <>' => $user_id));
		if(!empty($following)){
			$this->db->where_not_in('id', $following);
		}
		
		if(!empty($blocked)){
			$this->db->where_not_in('id', $blocked);
		}
 		$this->db->order_by('id', 'RANDOM');
		$this->db->limit(6);
 		$query  = $this->db->get();
		//echo $this->db->last_query(); die;
 		return $query->result_array();
 	}
	
	public function checkAlreadyFriend($other_user, $user_id){
		$sql = "select * from firends where ((other_user = '".$other_user."' AND user_id = '".$user_id."'))"; //OR (other_user = '".$user_id."' AND user_id = '".$other_user."')
 		$query  = $this->db->query($sql);
		//echo $this->db->last_query(); die;
		$result = $query->row_array();
		return $result;
	}
	
	public function unFriend($other_user, $user_id){
		$where = "((other_user = '".$other_user."' AND user_id = '".$user_id."'))"; // OR (other_user = '".$user_id."' AND user_id = '".$other_user."')
		$this->db->where($where);
		$res = $this->db->delete("firends");
		return $res;
	}
	
	public function addFriend($arr){
		$arr["sent_on"] = time();
 		$arr["time"]    = time();
		$arr["status"]  = 1;
		return $this->db->insert("firends", $arr);
	}
	
	public function getListOfFollowing($user_id){
		$this->db->select("other_user");
		$this->db->from("firends");
		$where = "user_id = '".$user_id."' AND status = 1";
		$this->db->where($where);
   		$query  = $this->db->get();
		// echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getListOfFriend($arr, $search, $blocked, $user_id, $limit, $no_of_post){
		$this->db->select("user_id");
		$this->db->from("firends");
		$where = "other_user = '".$user_id."' AND status = 1";
		$this->db->where($where);
		$this->db->where_in('user_id', $arr);
		if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}
		if($search == ""){
			$this->db->limit($no_of_post, $limit);
		}
  		$query  = $this->db->get();
		//echo $this->db->last_query(); die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getFriendSuggestionList($following, $search, $blocked, $user_id, $limit, $no_of_post){
		$this->db->select("id, username, profile_pic");
		$this->db->from("users");
		$where = "id <> '".$user_id."' AND status = 1";
		$this->db->where($where);
		if(!empty($following)){
			$this->db->where_not_in('id', $following);
		}
		
		if(!empty($blocked)){
			$this->db->where_not_in('id', $blocked);
		}
		if($search == ""){
			$this->db->limit($no_of_post, $limit);
		}
  		$query  = $this->db->get();
		//  echo $this->db->last_query(); die;
		$result = $query->result_array();
		return $result;
	}
	
	/******************************* Message/Chat section ********************************/
	public function getConversationId($user_id, $receiver_id){
		$this->db->select("conversation_id");
		$this->db->from("messages"); //type = 'User' AND
		$where = "((sender_id = '".$user_id."' AND receiver_id = '".$receiver_id."') OR (sender_id = '".$receiver_id."' AND receiver_id = '".$user_id."'))";  
		$this->db->where($where);
		$query = $this->db->get();
		$result = $query->row_array();
		if(!empty($result)){
			$conversation_id = $result['conversation_id'];
		}
		else{
			$conversation_id = $receiver_id.time().uniqid(); //$arr['event_id'].
		}
		return $conversation_id;
 	}
	
	public function sendMessage($arr){
  		$arr["time"]    = time();
  		$arr["status"]  = 0;
  		$arr["sent_on"] = time();
 		return $this->db->insert("messages", $arr); //echo $this->db->last_query(); die;
	}
	
	public function getDelMesgConId($user_id){
		$this->db->select("conversation_id");
		$this->db->from("messages_delete");
		$where = "user_id = '".$user_id."'";
		$this->db->where($where);
  		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getInboxMesg($user_id, $dm, $limit, $no_of_post){
		/*$sql = "SELECT t1.* FROM messages t1 JOIN (SELECT conversation_id, MAX(time) time FROM messages where status <> 4 AND (sender_id = '".$user_id."' OR FIND_IN_SET('$user_id', receiver_id) != 0) GROUP BY conversation_id) t2 ON t1.conversation_id = t2.conversation_id ORDER by time DESC limit ".$limit.",".$no_of_post;*/
		$sql = "SELECT * FROM messages where id in (SELECT max(id) FROM messages where status <> 4 AND (sender_id = '".$user_id."' OR receiver_id = '".$user_id."') GROUP BY conversation_id ) order by id desc limit ".$limit.",".$no_of_post;
		$query = $this->db->query($sql);		 
		//echo $this->db->last_query(); die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getconversation($conversation_id, $time, $limit, $no_of_post){
		$this->db->select("*");
		$this->db->from("messages");
		if($time <> ''){
		$where = "conversation_id = '".$conversation_id."' AND status <> 4 AND time > '".$time."'";
		}
		else{
		$where = "conversation_id = '".$conversation_id."' AND status <> 4";
		}
		$this->db->where($where);
 		$this->db->order_by('id asc');
		$this->db->limit($no_of_post, $limit);
		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function updateMessageNotificatonStatus($user_id){
		$arr['seen'] = 1;
		$arr['time'] = time();
 		$this->db->where(array("receiver" => $user_id, "notification" => "message"));
		$this->db->update('notifications', $arr);
		
		$arr2['status'] = 1;
 		$this->db->where(array("receiver_id" => $user_id));
		$this->db->update('messages', $arr2);
 	}
	
	public function getUserDeleteLatestMesssge($arr){
		$this->db->select("*");
		$this->db->from("messages_delete");
 		$this->db->where(array("conversation_id" => $arr['conversation_id'], "user_id" => $arr['user_id']));
 		$query = $this->db->get();
		 //echo $this->db->last_query(); die;
		$res = $query->row_array();
		return $res ;
	}
	
	public function getLatestMessageAfterDelete($conversation_id, $time){
		$this->db->select("*");
		$this->db->from("messages");
		$where = "conversation_id = '".$conversation_id."' AND status <> 4 AND time > '".$time."'";
		$this->db->where($where);
 		$this->db->order_by('id desc');
		$this->db->limit(1);
		$query  = $this->db->get();
		 // echo $this->db->last_query(); 
		$result = $query->row_array();
		return $result;
	}	
	
	public function getLatestMessage($conversation_id){
		$this->db->select("*");
		$this->db->from("messages");
		$where = "conversation_id = '".$conversation_id."' AND status <> 4";
		$this->db->where($where);
 		$this->db->order_by('id desc');
		$this->db->limit(1);
		$query  = $this->db->get();
		// echo $this->db->last_query(); 
		$result = $query->row_array();
		return $result;
	}
	
	public function deleteConversationForUser($arr){
		$this->db->select("*");
		$this->db->from("messages_delete");
 		$this->db->where(array("conversation_id" => $arr['conversation_id'], "user_id" => $arr['user_id']));
 		$query = $this->db->get();
		 //echo $this->db->last_query(); die;
		$res = $query->row_array();
		if(!empty($res)){
			$arr3['time'] = time();
 			$this->db->where(array("conversation_id" => $arr['conversation_id'], "user_id" => $arr['user_id']));
			return $this->db->update('messages_delete', $arr3);
		}
		else{
			$arr["time"]   = time();
			$arr["status"] = 4;
			return $this->db->insert("messages_delete", $arr);
		}
	}
	
	/******************* Notification  section *****************************/	
	
	public function add_notifications($arr){
 		$arr['seen'] = 0;
 		$arr["date"] = time();
		$arr["time"] = time();
		$result = $this->db->insert("notifications", $arr);
		return $this->db->insert_id();
	}
	
	public function getNotificationList($user_id, $limit, $no_of_post){
 		$this->db->select("*");
		$this->db->from("notifications");
		$where = "receiver = '".$user_id."'"; // AND (time >= '".$arr['from']."' AND time <= '".$arr['to']."'
  		$this->db->where($where);
		$this->db->order_by('id desc');
		$this->db->limit($no_of_post, $limit);
 		$query = $this->db->get();
		//   echo $this->db->last_query();die;
 		$resultset = $query->result_array();	
 		return $resultset;	
	}
	
	/******************* Block user section *****************************/
	public function checkUserBlocked($arr){
		$this->db->select("*");
		$this->db->from("block_user");
		$where = "((user_id = '".$arr['user_id']."' AND other_user = '".$arr['other_user']."') OR (user_id = '".$arr['other_user']."' AND other_user = '".$arr['user_id']."'))";
		$this->db->where($where);
		$result = $this->db->get();
		$countrows = $result->num_rows();
		return $countrows;
	}
	
	public function checkUserBlockedBy($arr){
		$this->db->select("*");
		$this->db->from("block_user");
		$where = "((user_id = '".$arr['user_id']."' AND other_user = '".$arr['other_user']."') OR (user_id = '".$arr['other_user']."' AND other_user = '".$arr['user_id']."'))";
		$this->db->where($where);
		$result = $this->db->get();
		$countrows = $result->row_array();
		return $countrows['user_id'];
	}
	
	public function saveUserBlocked($arr){
  		$arr["time"] = time();
 		return $this->db->insert("block_user", $arr);
	}
	
	public function deleteUserBlocked($arr){
		$where = array("user_id" => $arr['user_id'], "other_user" => $arr['other_user']);
 		$this->db->where($where);
		return $this->db->delete("block_user");
	}
  
	public function getBlockedUserList($user_id){
		$this->db->select("*");
		$this->db->from("block_user");
		$where = "user_id = '".$user_id."'";
		$this->db->where($where);
 		$this->db->order_by('id desc');
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	} 
	
	public function getBlockedMeList($user_id){
		$this->db->select("*");
		$this->db->from("block_user");
		$where = "other_user = '".$user_id."'";
		$this->db->where($where);
 		$this->db->order_by('id desc');
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	} 
	
	public function getMyTopStories($user_id, $blocked, $time){
		$this->db->select("*");
		$this->db->from("challenges");
		$where = "created_on >= '".$time."' AND find_in_set($user_id,challenged_friend)<> 0"; //challenged_friend = '".$user_id."' AND 
		$this->db->where($where);
		//$this->db->where_in('challenged_friend', $user_id);
		if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}
		
 		$this->db->order_by('id desc');
 		$query  = $this->db->get();
		// echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function getMyTopTwentyStories($user_id, $blocked){
		$this->db->select("*");
		$this->db->from("challenges");
		//$this->db->where_in('challenged_friend', $user_id);
		$where = "find_in_set($user_id,challenged_friend)<> 0";
		$this->db->where($where);
		if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}
 		$this->db->order_by('id desc');
 		$this->db->limit(20);
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function checkUserAlreadyRateChallenge($user_id, $challenge_id){
		$this->db->select("*");
		$this->db->from("challenges_rating");
		$where = "(user_id = '".$user_id."' AND challenge_id = '".$challenge_id."')";
		$this->db->where($where);
		$result = $this->db->get();
		$res = $result->row_array();
		return $res;
		
	}
	
	public function saveRating($arr){
		$arr['status'] = 1;
		$arr["time"]   = time();
		$result = $this->db->insert("challenges_rating", $arr);
 		return $result;
	}
	
	public function updateRating($arr){
		$user_id      = $arr["user_id"];
		$challenge_id = $arr["challenge_id"];
		unset($arr["user_id"]);
		unset($arr["challenge_id"]);
		$this->db->where(array("user_id" => $user_id, "challenge_id" => $challenge_id));
		$result = $this->db->update('challenges_rating', $arr);
		return $result;
	}
	
	public function getChallengeRatingList($challenge_id, $limit, $no_of_post){
		$this->db->select("*");
		$this->db->from("challenges_rating");
		$where = "(challenge_id = '".$challenge_id."')";
		$this->db->where($where);
		$this->db->order_by('id desc');
		$this->db->limit($no_of_post, $limit);
		$result = $this->db->get();
		$res = $result->result_array();
		return $res;
	}
	
	public function getChallengeAvgRating($challenge_id){
		$this->db->select("AVG(rating) as avg_rating");
		$this->db->from("challenges_rating");
		$where = "(challenge_id = '".$challenge_id."')";
		$this->db->where($where);
		$result = $this->db->get();
		$res = $result->row_array();
		return $res['avg_rating'];
	}
	
	
	public function raiseFlagPostDB($arr){
  		$arr["time"]   = time();
  		$arr["posted"] = time();
 		return $this->db->insert("videos_flags", $arr);
	}
	
	public function getRaiseFlagPostList($user_id){
		$this->db->select("*");
		$this->db->from("videos_flags");
		$where = "user_id = '".$user_id."'";
		$this->db->where($where);
 		$this->db->order_by('id desc');
 		$query  = $this->db->get();
		//echo $this->db->last_query();die;
		$result = $query->result_array();
		return $result;
	}
	
	public function checkUserRaisFlag($user_id, $post_id){
		$this->db->select("*");	
		$this->db->from('videos_flags');
		$where = "user_id = '".$user_id."' AND post_id='".$post_id."'";
   		$this->db->where($where);
  		$query = $this->db->get();
 		$num_row = $query->num_rows();
		return $num_row;
	}
	
	public function insertData($table, $arr) {		
		$arr["time"]       = time();
		$arr["status"]     = 1;
		$res = $this->db->insert($table, $arr);
		 //echo $this->db->last_query(); die;
		return $res;
 	}
	
	public function updateData($table, $arr, $where){
		$arr['time'] = time();
		$this->db->where($where);
		return $this->db->update($table, $arr);
	}
	
	public function getDataSingleRow($table, $where){
 		$this->db->select('*');
 		$this->db->from($table);
 		$this->db->where($where);
 		$query  = $this->db->get();
 		 //echo $this->db->last_query(); die;
 		return $query->row_array(); 		
 	}
	
	public function getDataMultipleRows($table, $where, $order = ""){
 		$this->db->select('*');
 		$this->db->from($table);
 		$this->db->where($where);
		if($order <> ''){
			$this->db->order_by($order);
		}
		//echo $this->db->last_query();
 		$query  = $this->db->get();
 		return $query->result_array(); 		
 	}
 /**************************Add Hotel section ****************************/

 	public function addHotel($arr){
		$arr["status"] = 0;
		$arr["archive"]  = 1;
 		$arr["time"]    = time();
		return $this->db->insert("hotels", $arr);
	}


	public function get_hotel($id){
		$this->db->select("*");
		$this->db->from("hotels");
		$where = "(id = '".$id."' AND status = 1 AND archive = 0)";
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
		
	}
	
	public function getHotelData($id){
		$this->db->select("*");
		$this->db->from("hotels");
		$where = "(id = '".$id."' AND status <> 4 AND archive = 0)";
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
 	}

/**************set user geofence**************/
	public function User_geofence($arr){
		$arr["status"] = 0;
		$arr["archive"]  = 1;
 		$arr["time"]    = time();

		$result =  $this->db->insert("set_user_geofence", $arr);
		//echo $this->db->last_query(); die;
		return $result;
	}
	public function User_geofence_set_already($arr){
		$this->db->select("*");
		$this->db->from("set_user_geofence"); //AND latitude = '".$arr['latitude']."' AND longitude = '".$arr['longitude']."'
		$where = "(user_id = '".$arr['user_id']."'  AND status = 0 AND archive = 1)"; //AND  hotel_id = '".$arr['hotel_id']."'
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
		
	}
	public function User_in_out($arr){
		$arr["status"] = 0;
		$arr["archive"]  = 1;
 		$arr["time"]    = time();
		return $this->db->insert("user_location", $arr);
	}

	public function userInOutHistories($arr){ 
		$this->db->select("*");
		$this->db->from("user_location"); 
		$where = "(user_id = '".$arr['user_id']."' AND hotel_id = '".$arr['hotel_id']."'  AND status = 0 AND archive = 1)";//AND latitude = '".$arr['latitude']."' AND longitude = '".$arr['longitude']."'
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->result_array();
		return $res;
		
	}

	public function get_user_geofence($arr){
		$this->db->select("*");
		$this->db->from("set_user_geofence"); //AND latitude = '".$arr['latitude']."' AND longitude = '".$arr['longitude']."'
		$where = "(user_id = '".$arr['user_id']."'  AND status = 0 AND archive = 1)";
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
		
	}

	public function match_location($arr){
		$this->db->select("*");
		$this->db->from("set_user_geofence");
		$where = "(latitude = '".$arr['latitude']."' AND  longitude = '".$arr['longitude']."' AND status = 0 AND archive = 1)";
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
		
	}
	public function get_user_geo_fence($arr, $lat, $long){ 

			$latlang =array();
			foreach ($arr as $key => $value) {
	 				if($lat == $value['latitude'] && $long == $value['longitude']){
	 					$div['lat'] = $value['latitude'];
	 					$div['long'] = $value['longitude'];
	 					$div['hotel_id'] = $value['hotel_id'];

	 				}else{
	 					$div =array();


	 				}

	 					$latlang =$div;

	 			}
	 			print_r($latlang); 

	}
	public function check_radius($lat, $long, $number){

		$distance = number_format($number * 0.000621371, 2);
		 echo $sql  = "SELECT id, 3956 * 2 * ASIN(SQRT(POWER(SIN(($lat - abs(latitude)) * pi()/180 / 2), 2) + COS($lat * pi()/180 ) * COS(abs(latitude) * pi()/180) * POWER(SIN(($long - longitude) * pi()/180 / 2), 2) )) as  distance FROM set_user_geofence  HAVING distance <= $number ORDER BY distance";
		$result = $this->db->query($sql);
		//echo $this->db->last_query(); die;
		return $result->result_array();
    
	}

	public function add_location_log($arr, $id){
		$arr["status"]  = 0;
		$arr["archive"] = 1;
		$arr["user_id"] = $id;
 		$arr["time"]    = time();
		return $this->db->insert("user_location_logs", $arr); //test_location_update
	}
	

	public function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {  
	  $earth_radius = 6371;

	  $dLat = deg2rad($latitude2 - $latitude1);  
	  $dLon = deg2rad($longitude2 - $longitude1);  

	  $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);  
	  $c = 2 * asin(sqrt($a));  
	  $d = $earth_radius * $c;  

	  return $d;  
	}

	public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "m") {
	      return ($miles * 1609.34);
	  }else if ($unit == "K") {
	      return ($miles * 1.609344);
	  } else if ($unit == "N") {
	      return ($miles * 0.8684);
	  } else {
	      return $miles;
	  }
	}

	public function check_welcome($arr){
		//SELECT * FROM `notifications` where receiver = 18
		$this->db->select("*");
		$this->db->from("notifications");
		$where = "(receiver = '".$arr['user_id']."')";
		$this->db->where($where);
		$result = $this->db->get();
		//echo $this->db->last_query(); die;
		$res = $result->row_array();
		return $res;
		
	}

	public function is_checkIn($user_id, $hotelid){
		//SELECT * FROM `booking` where user_id = 23
		$this->db->select("*");
		$this->db->from('booking');
		if(!empty($hotelid)){
			$this->db->where(array('user_id' => $user_id, 'hotel_id' => $hotelid, 'user_check_out'=>0));
			$query  = $this->db->get(); 
			return $query->row_array();
		}else{
			$this->db->where(array('user_id' => $user_id, 'hotel_id' => $hotelid, 'user_check_out'=>0));
			$query  = $this->db->get(); 
			return $query->num_rows();
		}
		 	
	}
	public function hotels_location_api($lat, $long, $listedHotel, $limit, $no_of_post){
		if($listedHotel <> ''){
			$where  = "status = 1 AND NOT id IN (".$listedHotel.")";
		}
		else{
			$where  = "status = 1";
		}
		$sql = "select * FROM (SELECT *, (3959 * acos (cos(radians('".$lat."'))* cos(radians( latitude))* cos(radians(longitude) - radians('".$long."') )+ sin(radians('".$lat."'))* sin(radians(latitude)))) AS distances FROM (hotels)) AS t  where distances < 50 AND ".$where;
		if($limit <> ''){
			$sql .= " order by distances ASC limit ".$limit.",".$no_of_post;
		}
		else{
			$sql .= " order by distances ASC";
		}
		// echo $sql;
		$result = $this->db->query($sql);
		return $result->result_array();
		
	}
	
	public function getUserCheckInOutHistroy($uesr_id, $limit, $no_of_post){
 		$this->db->select('booking.id as bid, booking.user_id, booking.hotel_id, booking.user_check_in, booking.user_check_out, hotels.name, hotels.location, hotels.latitude, hotels.longitude, hotels.phone, hotels.hotelpic, hotels.weblink, hotels.landmark,');
 		$this->db->from('booking');
 		$this->db->join('hotels', 'hotels.id = booking.hotel_id');
 		$this->db->where(array("booking.user_check_out <>" => 0, "booking.user_id" => $uesr_id));
		/*if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}*/
 		$this->db->order_by('booking.user_check_in desc');
		if($limit <> ''){
			$this->db->limit($no_of_post, $limit);
		}
 		$query  = $this->db->get();
 		return $query->result_array();
 	}
	
	public function getUserCheckInList($uesr_id, $limit, $no_of_post){
 		$this->db->select('booking.id as bid, booking.user_id, booking.hotel_id, booking.user_check_in, booking.user_check_out, hotels.name, hotels.location, hotels.latitude, hotels.longitude, hotels.phone, hotels.hotelpic, hotels.weblink, hotels.landmark,');
 		$this->db->from('booking');
 		$this->db->join('hotels', 'hotels.id = booking.hotel_id');
 		$this->db->where(array("booking.user_check_in <>" => 0, "booking.user_check_out" => 0, "booking.user_id" => $uesr_id));
		/*if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}*/
 		$this->db->order_by('booking.user_check_in desc');
		if($limit <> ''){
			$this->db->limit($no_of_post, $limit);
		}
 		$query  = $this->db->get();
 		return $query->result_array();
 	}
	
	public function getUserCheckInHistoryList($uesr_id, $limit, $no_of_post){
 		$this->db->select('booking.id as bid, booking.user_id, booking.hotel_id, booking.user_check_in, booking.user_check_out, hotels.name, hotels.location, hotels.latitude, hotels.longitude, hotels.phone, hotels.hotelpic, hotels.weblink, hotels.landmark,');
 		$this->db->from('booking');
 		$this->db->join('hotels', 'hotels.id = booking.hotel_id');
 		$this->db->where(array("booking.user_check_in <>" => 0, "booking.user_check_out <>" => 0, "booking.user_id" => $uesr_id));
		/*if(!empty($blocked)){
			$this->db->where_not_in('user_id', $blocked);
		}*/
 		$this->db->order_by('booking.user_check_in desc');
		if($limit <> ''){
			$this->db->limit($no_of_post, $limit);
		}
 		$query  = $this->db->get();
 		return $query->result_array();
 	}
	
	public function getNearByHotel($long, $lat, $listedHotel){
		if($listedHotel <> ''){
			$where  = "status = 1 AND NOT id IN (".$listedHotel.")";
		}
		else{
			$where  = "status = 1";
		}
		$sql  = "select * FROM (SELECT *, (3959 * acos (cos(radians('".$lat."'))* cos(radians( latitude))* cos(radians(longitude) - radians('".$long."') )+ sin(radians('".$lat."'))* sin(radians(latitude)))) AS distances FROM (hotels)) AS t  where distances < 0.5 AND ".$where;
 		$sql .= " order by distances ASC";
 		 //echo $sql;
		$result = $this->db->query($sql);
		return $result->result_array();
 	}

//////////////////////////////////////////////////////////	 
}
?>
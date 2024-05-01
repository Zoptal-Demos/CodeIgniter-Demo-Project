<?php 
class Post_model extends CI_Model {
    function __construct()
    {
        parent::__construct();
    }
	
 	/*************************************Posts function starts*****************************/
	
	public function getPostData($searchdata=array(), $user_id){
		$searcharray=array("status"=>"status");		
		if(!isset($searchdata["page"]) || $searchdata["page"]==""){
			$searchdata["page"]=0;	
		}
	    if(!isset($searchdata["countdata"])){	
			if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
				$recordperpage=$searchdata["per_page"];	
			}
			else{
				$recordperpage=1;
			}
			if(isset($searchdata["page"]) && $searchdata["page"]!=""){
				$startlimit=$searchdata["page"];	
			}
			else{
				$startlimit=0;
			}
		}
		
		$this->db->select("*");
		$this->db->from("challenges");
 		if(isset($searchdata["search"]) && $searchdata["search"]!="" && $searchdata["search"]!="search"){
			$this->db->like("title", $searchdata["search"]);	
		}	
		foreach($searchdata as $key=>$val){
			if(isset($searcharray[$key]) && $searchdata[$key]!=""){
				if(array_key_exists($key,$searcharray)){
					$where=array($searcharray[$key]=>$val);
					$this->db->where($where);
				}
			}
		}		
 		$where=array("status <>"=>"4");
		$this->db->where($where);	
		if($user_id <> 0 && $user_id <> ""){
			$this->db->where(array('user_id' => $user_id));	
		}
		if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
			if(isset($recordperpage) && $recordperpage!="" && ($startlimit!="" || $startlimit==0)){
				$this->db->limit($recordperpage,$startlimit);
			}
		}
		$this->db->order_by("id DESC");
		$query = $this->db->get();
		$resultset=$query->result_array();
		return $resultset; 
	}
	
	
	 
	
	public function getIndividualPost($id){
		$this->db->select("*");	
		$this->db->from('challenges');
		$where=array("id"=>$id, "status <> " => "4");
		$this->db->where($where);
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->row_array();
		return $resultset;		
	}
	
	public function getPostList($id){
		$this->db->select("*");	
		$this->db->from('challenges');
		$where=array("status <> " => "4", 'type' => 0);
		$this->db->where($where);
		$this->db->order_by('title asc');
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->result_array();
		return $resultset;		
	}
	
 	
	public function enable_disable_post($id,$status){
		$this->db->where("id",$id);
		$array=array("status"=>$status);
		$this->db->update("challenges",$array);		
	}
	
	public function archive_post($id){
		$this->db->select("*");	
		$this->db->from('challenges');
		$where2=array("id"=>$id);
		$this->db->where($where2);
		$query2 = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query2->row_array();
		
		$where=array("id" => $id);
		$array=array("status" => 4);
		$this->db->where($where);
		$result = $this->db->delete("challenges");
		if($result){
			unlink('../uploads/posts/'.$resultset['video_image']);
			unlink('../uploads/posts/'.$resultset['image']);
			
			$this->db->where(array("post_id" => $id));
		    $this->db->delete("challenges_comments");
			
			$this->db->where(array("post_id" => $id));
		    $this->db->delete("challenges_likes");
			
			$this->db->where(array("challenge_id" => $id));
		    $this->db->delete("challenges_likes");
			
			$this->db->where(array("post_id" => $id));
		    $this->db->delete("challenges_share");
			
			$this->db->where(array("post_id" => $id));
		    $this->db->delete("videos_flags");
			
			$this->db->where(array("other_id" => $id, "notification" => "rating"));
		    $this->db->delete("notifications");
			
			$this->db->where(array("other_id" => $id, "notification" => "challenge_reply"));
		    $this->db->delete("notifications");
			
			$this->db->where(array("other_id" => $id, "notification" => "challenge_you"));
		    $this->db->delete("notifications");
			
			$this->db->where(array("other_id" => $id, "notification" => "comment_post"));
		    $this->db->delete("notifications");
			
			$this->db->where(array("other_id" => $id, "notification" => "like_post"));
		    $this->db->delete("notifications");
		}
		return $result;
	}
	
	public function getNoOfPostLikes($id){
		$this->db->select("*");	
		$this->db->from('challenges_likes');
		$where=array("post_id"=>$id, "status <> " => "4");
		$this->db->where($where);
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->num_rows();
		return $resultset;
	}
	
	public function getNoOfPostComments($id){
		$this->db->select("*");	
		$this->db->from('challenges_comments');
		$where=array("post_id"=>$id, "status <> " => "4");
		$this->db->where($where);
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->num_rows();
		return $resultset;
	}
	public function getAvgRating($id){
		$this->db->select("AVG(rating) as trating");	
		$this->db->from('challenges_rating');
		$where=array("challenge_id"=>$id, "status <> " => "4");
		$this->db->where($where);
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->row_array();
		return $resultset['trating'];
	}
	
	public function getUsersListThatAddedPosts(){
		$this->db->select("users.username, users.id, challenges.id as challenges_id");	
		$this->db->from('challenges');
		$this->db->join('users', 'challenges.user_id = users.id');
		$where=array("users.status <> " => "4", "challenges.status <> " => "4");
		$this->db->where($where);
		$this->db->group_by('challenges.user_id');
		$this->db->order_by('users.username asc');
		$query = $this->db->get();
		//echo $this->db->last_query(); die;
		$resultset=$query->result_array();
		return $resultset;	
	}
	
	
	public function getPostCommentsData($searchdata=array(), $post_id){
		$searcharray=array("status"=>"status");		
		if(!isset($searchdata["page"]) || $searchdata["page"]==""){
			$searchdata["page"]=0;	
		}
	    if(!isset($searchdata["countdata"])){	
			if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
				$recordperpage=$searchdata["per_page"];	
			}
			else{
				$recordperpage=1;
			}
			if(isset($searchdata["page"]) && $searchdata["page"]!=""){
				$startlimit=$searchdata["page"];	
			}
			else{
				$startlimit=0;
			}
		}
		
		$this->db->select("*");
		$this->db->from("challenges_comments");
 		if(isset($searchdata["search"]) && $searchdata["search"]!="" && $searchdata["search"]!="search"){
			$this->db->like("comment", $searchdata["search"]);	
		}	
		foreach($searchdata as $key=>$val){
			if(isset($searcharray[$key]) && $searchdata[$key]!=""){
				if(array_key_exists($key,$searcharray)){
					$where=array($searcharray[$key]=>$val);
					$this->db->where($where);
				}
			}
		}		
 		$where=array("status <>"=>"4");
		$this->db->where($where);	
		if($post_id <> 0 && $post_id <> ""){
			$this->db->where(array('post_id' => $post_id));	
		}
		if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
			if(isset($recordperpage) && $recordperpage!="" && ($startlimit!="" || $startlimit==0)){
				$this->db->limit($recordperpage,$startlimit);
			}
		}
		$this->db->order_by("id DESC");
		$query = $this->db->get();
		$resultset=$query->result_array();
		return $resultset; 
	}
	
	public function post_comment_delete($id){
		$where=array("id" => $id);
		$array=array("status" => 4);
		$this->db->where($where);
		$this->db->update("challenges",$array);
	}
	
	
	public function getPostLikesData($searchdata=array(), $post_id){
		$searcharray=array("status"=>"status");		
		if(!isset($searchdata["page"]) || $searchdata["page"]==""){
			$searchdata["page"]=0;	
		}
	    if(!isset($searchdata["countdata"])){	
			if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
				$recordperpage=$searchdata["per_page"];	
			}
			else{
				$recordperpage=1;
			}
			if(isset($searchdata["page"]) && $searchdata["page"]!=""){
				$startlimit=$searchdata["page"];	
			}
			else{
				$startlimit=0;
			}
		}
		
		$this->db->select("*");
		$this->db->from("challenges_likes");
 		if(isset($searchdata["search"]) && $searchdata["search"]!="" && $searchdata["search"]!="search"){
			//$this->db->like("comment", $searchdata["search"]);	
		}	
		foreach($searchdata as $key=>$val){
			if(isset($searcharray[$key]) && $searchdata[$key]!=""){
				if(array_key_exists($key,$searcharray)){
					$where=array($searcharray[$key]=>$val);
					$this->db->where($where);
				}
			}
		}		
 		$where=array("status <>"=>"4");
		$this->db->where($where);	
		if($post_id <> 0 && $post_id <> ""){
			$this->db->where(array('post_id' => $post_id));	
		}
		if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
			if(isset($recordperpage) && $recordperpage!="" && ($startlimit!="" || $startlimit==0)){
				$this->db->limit($recordperpage,$startlimit);
			}
		}
		$this->db->order_by("id DESC");
		$query = $this->db->get();
		$resultset=$query->result_array();
		return $resultset; 
	}
	
	public function getPostRatingData($searchdata=array(), $post_id){
		$searcharray=array("status"=>"status");		
		if(!isset($searchdata["page"]) || $searchdata["page"]==""){
			$searchdata["page"]=0;	
		}
	    if(!isset($searchdata["countdata"])){	
			if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
				$recordperpage=$searchdata["per_page"];	
			}
			else{
				$recordperpage=1;
			}
			if(isset($searchdata["page"]) && $searchdata["page"]!=""){
				$startlimit=$searchdata["page"];	
			}
			else{
				$startlimit=0;
			}
		}
		
		$this->db->select("*");
		$this->db->from("challenges_rating");
 		if(isset($searchdata["search"]) && $searchdata["search"]!="" && $searchdata["search"]!="search"){
			//$this->db->like("comment", $searchdata["search"]);	
		}	
		foreach($searchdata as $key=>$val){
			if(isset($searcharray[$key]) && $searchdata[$key]!=""){
				if(array_key_exists($key,$searcharray)){
					$where=array($searcharray[$key]=>$val);
					$this->db->where($where);
				}
			}
		}		
 		$where=array("status <>"=>"4");
		$this->db->where($where);	
		if($post_id <> 0 && $post_id <> ""){
			$this->db->where(array('challenge_id' => $post_id));	
		}
		if(isset($searchdata["per_page"]) && $searchdata["per_page"]!=""){
			if(isset($recordperpage) && $recordperpage!="" && ($startlimit!="" || $startlimit==0)){
				$this->db->limit($recordperpage,$startlimit);
			}
		}
		$this->db->order_by("id DESC");
		$query = $this->db->get();
		$resultset=$query->result_array();
		return $resultset; 
	}
	
	public function post_rating_delete($id){
		$where=array("id" => $id);
		$array=array("status" => 4);
		$this->db->where($where);
		$this->db->update("challenges_rating",$array);
	}
	
	/*************************************Category function starts*****************************/
	
	
}
?>
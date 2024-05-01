<?php 
class Page_model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

	/********************************************************Page function starts***************************************************/

	public function getPageData($pagename=''){
		
		$this->db->select("*");
		$this->db->from("content_pages");
		$this->db->where(array("page_name"=>$pagename,"page_status"=>"1"));
		$query=$this->db->get();		
		$resultset=$query->row_array();
		return $resultset; 
	}
	public function aboutUsPageData(){
		$pagedata='about_us';
		$this->db->select("*");
		$this->db->from("about_us");
		$this->db->where(array("page_name" => 'about_us'));
		$query=$this->db->get();
		//echo $this->db->last_query();
		
		$resultset=$query->row_array();
		return $resultset; 
	}
	
	public function getTestimonialsData(){
		$this->db->select("*");
		$this->db->from("testimonials");
		$this->db->where(array("status" => "1"));
		$this->db->order_by('id desc');
		$query=$this->db->get();
		//echo $this->db->last_query();
		$resultset=$query->result_array();
		return $resultset; 
	}
	
	public function getTeamsData(){
		$this->db->select("*");
		$this->db->from("team");
		$this->db->where(array("status" => "1"));
		$this->db->order_by('id desc');
		$query=$this->db->get();
		//echo $this->db->last_query();
		$resultset=$query->result_array();
		return $resultset; 
	}
 

	public function updatepagedata($arr=array()){
		$this->db->where("id",$arr["id"]);
		return $this->db->update("content_pages",$arr);	
	}
	
	public function updatepageAboutdata($arr=array()){
		$this->db->where("id",$arr["id"]);
		return $this->db->update("about_us",$arr);	
	}

	public function update_contact_information_db($arr){
	  	$this->db->where("id",$arr["id"]);
 		$resultset=$this->db->update("content_pages",$arr);
		// echo $this->db->last_query(); die;
		return $resultset; 
 	}

  
	public function AboutUsPages($pages){		
		$this->db->select("page_title, page_content");	
		$this->db->from('content_pages');
		$where="page_name IN(".$pages.") AND page_status = 1";
		$this->db->where($where);
		$this->db->order_by('id', ASC);
		$query = $this->db->get();
		return $query->result_array();	
	}
	

}
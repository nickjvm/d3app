<?php 

class Locations_model extends CI_Model {


    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }
    
    function get($id)
    {
        if($id){ 
            $query = $this->db->get_where('locations', array("id"=>$id));
        } else {
            $query = $this->db->get("locations");
        }
        
        return $query->result();
    }

}
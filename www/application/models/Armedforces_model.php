<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Armedforces_table extends CI_Model {
    public static $table_name = 'armedforces';
	public static $id = 'id';
	public static $cadet_no = 'cadet_no';
	public static $name = 'name';
	public static $army_airforce_navy = 'army_airforce_navy';
	public static $course_name = 'course_name';
	public static $academic_year = 'academic_year';
	public static $year_of_joining = 'year_of_joining';
	public static $present_rank = 'present_rank';
	public static $special_achievements = 'special_achievements';
	public static $awards = 'awards';
	public static $status = 'status';
	public static $created_on = 'created_on';
	public static $modified_on = 'modified_on';
}

class Armedforces_model extends Armedforces_table {
    
    public function add($params=[])
    {
        $date = new DateTime();
        if(!empty($params['cadet_no']))
        {
            $this->db->set(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['name']))
        {
            $this->db->set(self::$name,$params['name']);
        }
        if(!empty($params['army_airforce_navy']))
        {
            $this->db->set(self::$army_airforce_navy,$params['army_airforce_navy']);
        }
        if(!empty($params['course_name']))
        {
            $this->db->set(self::$course_name,$params['course_name']);
        }
        if(!empty($params['academic_year']))
        {
            $this->db->set(self::$academic_year,$params['academic_year']);
        }
        if(!empty($params['year_of_joining']))
        {
            $this->db->set(self::$year_of_joining,$params['year_of_joining']);
        }
        if(!empty($params['present_rank']))
        {
            $this->db->set(self::$present_rank,$params['present_rank']);
        }
        if(!empty($params['special_achievements']))
        {
            $this->db->set(self::$special_achievements,$params['special_achievements']);
        }
        if(!empty($params['awards']))
        {
            $this->db->set(self::$awards,$params['awards']);
        }
        
        $this->db->set(self::$created_on,$date->format('Y-m-d H:i:s'));
        
        
        $this->db->set(self::$modified_on,$date->format('Y-m-d H:i:s'));

        $query = $this->db->insert(self::$table_name);
        if($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
    }

    public function get($params=[])
    {
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
   
        $this->db->order_by(self::$id,'desc');
        $this->db->limit(1);
        $query = $this->db->get(self::$table_name);
        return $query->row();
    }

    public function edit($params=[])
    {
        $date = new DateTime();
        
        if(!empty($params['name']))
        {
            $this->db->set(self::$name,$params['name']);
        }
        if(!empty($params['army_airforce_navy']))
        {
            $this->db->set(self::$army_airforce_navy,$params['army_airforce_navy']);
        }
        if(!empty($params['course_name']))
        {
            $this->db->set(self::$course_name,$params['course_name']);
        }
        if(!empty($params['academic_year']))
        {
            $this->db->set(self::$academic_year,$params['academic_year']);
        }
        if(!empty($params['year_of_joining']))
        {
            $this->db->set(self::$year_of_joining,$params['year_of_joining']);
        }
        if(!empty($params['present_rank']))
        {
            $this->db->set(self::$present_rank,$params['present_rank']);
        }
        if(!empty($params['special_achievements']))
        {
            $this->db->set(self::$special_achievements,$params['special_achievements']);
        }
        if(!empty($params['awards']))
        {
            $this->db->set(self::$awards,$params['awards']);
        }        
        if(!empty($params['status']))
        {
            $this->db->set(self::$status,$params['status']);
        }  
        $this->db->set(self::$modified_on,$date->format('Y-m-d H:i:s'));
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
            $query = $this->db->update(self::$table_name);
            if($this->db->affected_rows() > 0)
		    {
			    return TRUE;
		    }else{
			    return FALSE;
            }
		}
    }
}
?>
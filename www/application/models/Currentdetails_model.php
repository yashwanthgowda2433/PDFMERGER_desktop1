<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currentdetails_table extends CI_Model {
    public static $table_name = 'currentdetails';
	public static $id = 'id';
	public static $cadet_no = 'cadet_no';
	public static $name = 'name';
	public static $qualification = 'qualification';
	public static $employment = 'employment';
	public static $designation = 'designation';
	public static $permanent_address = 'permanent_address';
	public static $current_address = 'current_address';
	public static $telephone_no = 'telephone_no';
	public static $mobile_no = 'mobile_no';
	public static $email = 'email';
	public static $spouse_name = 'spouse_name';
	public static $semployment = 'semployment';
	public static $dependents_one = 'dependents_one';
	public static $dependents_two = 'dependents_two';
	public static $dependents_three = 'dependents_three';
	public static $dependents_four = 'dependents_four';
	public static $dependents_five = 'dependents_five';
	public static $dependents_six = 'dependents_six';

	public static $achievements = 'achievements';
	public static $status = 'status';
	public static $created_on = 'created_on';
	public static $modified_on = 'modified_on';
}

class Currentdetails_model extends Currentdetails_table {
    
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
        if(!empty($params['qualification']))
        {
            $this->db->set(self::$qualification,$params['qualification']);
        }
        if(!empty($params['employment']))
        {
            $this->db->set(self::$employment,$params['employment']);
        }
        if(!empty($params['designation']))
        {
            $this->db->set(self::$designation,$params['designation']);
        }
        if(!empty($params['permanent_address']))
        {
            $this->db->set(self::$permanent_address,$params['permanent_address']);
        }
        if(!empty($params['current_address']))
        {
            $this->db->set(self::$current_address,$params['current_address']);
        }
        if(!empty($params['telephone_no']))
        {
            $this->db->set(self::$telephone_no,$params['telephone_no']);
        }
        if(!empty($params['mobile_no']))
        {
            $this->db->set(self::$mobile_no,$params['mobile_no']);
        }
        if(!empty($params['email']))
        {
            $this->db->set(self::$email,$params['email']);
        }
        if(!empty($params['spouse_name']))
        {
            $this->db->set(self::$spouse_name,$params['spouse_name']);
        }
        if(!empty($params['semployment']))
        {
            $this->db->set(self::$semployment,$params['semployment']);
        }
        if(!empty($params['dependents_one']))
        {
            $this->db->set(self::$dependents_one,$params['dependents_one']);
        }
        if(!empty($params['dependents_two']))
        {
            $this->db->set(self::$dependents_two,$params['dependents_two']);
        }
        if(!empty($params['dependents_three']))
        {
            $this->db->set(self::$dependents_three,$params['dependents_three']);
        }

        if(!empty($params['dependents_four']))
        {
            $this->db->set(self::$dependents_four,$params['dependents_four']);
        }
        if(!empty($params['dependents_five']))
        {
            $this->db->set(self::$dependents_five,$params['dependents_five']);
        }
        if(!empty($params['dependents_six']))
        {
            $this->db->set(self::$dependents_six,$params['dependents_six']);
        }

        if(!empty($params['achievements']))
        {
            $this->db->set(self::$achievements,$params['achievements']);
        }
        
        $this->db->set(self::$created_on,$date->format('Y-m-d H:i:s'));
        
        
        $this->db->set(self::$modified_on,$date->format('Y-m-d H:i:s'));

        $query = $this->db->insert(self::$table_name);
        // echo $this->db->last_query();exit;
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
        if(!empty($params['qualification']))
        {
            $this->db->set(self::$qualification,$params['qualification']);
        }
        if(!empty($params['employment']))
        {
            $this->db->set(self::$employment,$params['employment']);
        }
        if(!empty($params['designation']))
        {
            $this->db->set(self::$designation,$params['designation']);
        }
        if(!empty($params['permanent_address']))
        {
            $this->db->set(self::$permanent_address,$params['permanent_address']);
        }
        if(!empty($params['current_address']))
        {
            $this->db->set(self::$current_address,$params['current_address']);
        }
        if(!empty($params['telephone_no']))
        {
            $this->db->set(self::$telephone_no,$params['telephone_no']);
        }
        if(!empty($params['mobile_no']))
        {
            $this->db->set(self::$mobile_no,$params['mobile_no']);
        }
        if(!empty($params['email']))
        {
            $this->db->set(self::$email,$params['email']);
        }
        if(!empty($params['spouse_name']))
        {
            $this->db->set(self::$spouse_name,$params['spouse_name']);
        }
        if(!empty($params['semployment']))
        {
            $this->db->set(self::$semployment,$params['semployment']);
        }
        if(!empty($params['dependents_one']))
        {
            $this->db->set(self::$dependents_one,$params['dependents_one']);
        }
        if(!empty($params['dependents_two']))
        {
            $this->db->set(self::$dependents_two,$params['dependents_two']);
        }
        if(!empty($params['dependents_three']))
        {
            $this->db->set(self::$dependents_three,$params['dependents_three']);
        }

        if(!empty($params['dependents_four']))
        {
            $this->db->set(self::$dependents_four,$params['dependents_four']);
        }
        if(!empty($params['dependents_five']))
        {
            $this->db->set(self::$dependents_five,$params['dependents_five']);
        }
        if(!empty($params['dependents_six']))
        {
            $this->db->set(self::$dependents_six,$params['dependents_six']);
        }

        if(!empty($params['achievements']))
        {
            $this->db->set(self::$achievements,$params['achievements']);
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
        // echo $this->db->last_query();exit;

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
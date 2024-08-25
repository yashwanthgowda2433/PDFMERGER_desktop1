<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cadet_table extends CI_Model {
    public static $table_name = 'cadet';
	public static $cadet_no = 'cadet_no';
	public static $cadet_name = 'cadet_name';
	public static $house_name = 'house_name';
	public static $dob = 'dob';
	public static $year_of_admn = 'year_of_admn';
	public static $class_joined = 'class_joined';
	public static $year_of_discharged = 'year_of_discharged';
	public static $class_left = 'class_left';
	public static $nda_course = 'nda_course';
	public static $photo = 'photo';
	public static $pdf = 'pdf';
	public static $created_on = 'created_on';
	public static $modified_on = 'modified_on';
}

class Cadet_model extends Cadet_table {
    public function get($params=[])
    {
        $sql = self::$cadet_no.' as cadet_no,'
               .self::$cadet_name.' as cadet_name,'
               .self::$house_name.' as house_name,'
               .self::$dob.' as dob,'
               .self::$year_of_admn.' as year_of_admn,'
               .self::$class_joined.' as class_joined,'
               .self::$year_of_discharged.' as year_of_discharged,'
               .self::$class_left.' as class_left,'
               .self::$nda_course.' as nda_course,'
               .self::$pdf.' as pdf,'
               .self::$photo.' as photo,'
               .self::$created_on.' as created_on,'
               .self::$modified_on.' as modified_on' ;
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['year_of_admn']))
        {
            $this->db->where(self::$year_of_admn,$params['year_of_admn']);
        }
        if(!empty($params['year_of_discharged']))
        {
            $this->db->where(self::$year_of_discharged,$params['year_of_discharged']);
        }
        if(isset($params['start']) && isset($params['end']))
		{
			$this->db->limit($params['end'],$params['start']);
		}
        $query = $this->db->get(self::$table_name);
        if(!empty($params['cadet_no']))
        {
            return $query->row();
        }else{
            return $query->result();
        }

    }

    public function view($params=[])
    {
        $sql = self::$cadet_no.' as cadet_no,'
               .self::$cadet_name.' as cadet_name,'
               .self::$house_name.' as house_name,'
               .self::$dob.' as dob,'
               .self::$year_of_admn.' as year_of_admn,'
               .self::$class_joined.' as class_joined,'
               .self::$year_of_discharged.' as year_of_discharged,'
               .self::$class_left.' as class_left,'
               .self::$nda_course.' as nda_course,'
               .self::$pdf.' as pdf,'
               .self::$photo.' as photo,'
               .self::$created_on.' as created_on,'
               .self::$modified_on.' as modified_on' ;
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['year_of_admn']))
        {
            $this->db->where(self::$year_of_admn,$params['year_of_admn']);
        }
        if(!empty($params['year_of_discharged']))
        {
            $this->db->where(self::$year_of_discharged,$params['year_of_discharged']);
        }
        if(isset($params['start']) && isset($params['end']))
		{
			$this->db->limit($params['end'],$params['start']);
		}
        $query = $this->db->get(self::$table_name);
        
        return $query->result();
        

    }

    public function count($params=[])
    {
        $sql = '*';
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['year_of_admn']))
        {
            $this->db->where(self::$year_of_admn,$params['year_of_admn']);
        }
        if(!empty($params['year_of_discharged']))
        {
            $this->db->where(self::$year_of_discharged,$params['year_of_discharged']);
        }
        // if(isset($params['start']) && isset($params['end']))
		// {
		// 	$this->db->limit($params['end'],$params['start']);
		// }
        $query = $this->db->get(self::$table_name);
        if(!empty($params['cadet_no']))
        {
            return $query->num_rows();
        }else{
            return $query->num_rows();
        }

    }

    public function add($params=[])
    {
        $date = new DateTime();
        if(!empty($params['cadet_no']))
        {
            $this->db->set(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['cadet_name']))
        {
            $this->db->set(self::$cadet_name,$params['cadet_name']);
        }
        if(!empty($params['house_name']))
        {
            $this->db->set(self::$house_name,$params['house_name']);
        }
        if(!empty($params['dob']))
        {
            $this->db->set(self::$dob,$params['dob']);
        }
        if(!empty($params['year_of_admn']))
        {
            $this->db->set(self::$year_of_admn,$params['year_of_admn']);
        }
        if(!empty($params['class_joined']))
        {
            $this->db->set(self::$class_joined,$params['class_joined']);
        }
        if(!empty($params['year_of_discharged']))
        {
            $this->db->set(self::$year_of_discharged,$params['year_of_discharged']);
        }
        if(!empty($params['class_left']))
        {
            $this->db->set(self::$class_left,$params['class_left']);
        }
        if(!empty($params['nda_course']))
        {
            $this->db->set(self::$nda_course,$params['nda_course']);
        }
        if(!empty($params['photo']))
        {
            $this->db->set(self::$photo,$params['photo']);
        }
        if(!empty($params['pdf']))
        {
            $this->db->set(self::$pdf,$params['pdf']);
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

    public function edit($params=[])
    {
        $date = new DateTime();
        
        if(!empty($params['cadet_name']))
        {
            $this->db->set(self::$cadet_name,$params['cadet_name']);
        }
        if(!empty($params['house_name']))
        {
            $this->db->set(self::$house_name,$params['house_name']);
        }
        if(!empty($params['dob']))
        {
            $this->db->set(self::$dob,$params['dob']);
        }
        if(!empty($params['year_of_admn']))
        {
            $this->db->set(self::$year_of_admn,$params['year_of_admn']);
        }
        if(!empty($params['class_joined']))
        {
            $this->db->set(self::$class_joined,$params['class_joined']);
        }
        if(!empty($params['year_of_discharged']))
        {
            $this->db->set(self::$year_of_discharged,$params['year_of_discharged']);
        }
        if(!empty($params['class_left']))
        {
            $this->db->set(self::$class_left,$params['class_left']);
        }
        if(!empty($params['nda_course']))
        {
            $this->db->set(self::$nda_course,$params['nda_course']);
        }
        if(!empty($params['photo']))
        {
            $this->db->set(self::$photo,$params['photo']);
        }
        if(!empty($params['pdf']))
        {
            $this->db->set(self::$pdf,$params['pdf']);
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
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Query_table extends CI_Model {
    public static $table_name = 'query';
	public static $id = 'id';
	public static $cadet_no = 'cadet_no';
	public static $query = 'query';
	public static $status = 'status';
	public static $remarks = 'remarks';
	public static $viewed = 'viewed';
	public static $created_on = 'created_on';
	public static $modified_on = 'modified_on';
}

class Query_model extends Query_table {
    
    public function add($params=[])
    {
        $date = new DateTime();
        if(!empty($params['cadet_no']))
        {
            $this->db->set(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['query']))
        {
            $this->db->set(self::$query,$params['query']);
        }
        if(!empty($params['status']))
        {
            $this->db->set(self::$status,$params['status']);
        }
        if(!empty($params['remarks']))
        {
            $this->db->set(self::$remarks,$params['remarks']);
        }
        if(!empty($params['viewed']))
        {
            $this->db->set(self::$viewed,$params['viewed']);
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
        if(!empty($params['cadet_no']))
        {
            $this->db->set(self::$cadet_no,$params['cadet_no']);
        }
        if(!empty($params['query']))
        {
            $this->db->set(self::$query,$params['query']);
        }
        
        if(!empty($params['status']))
        {
            $this->db->set(self::$status,$params['status']);
        }
        if(!empty($params['remarks']))
        {
            $this->db->set(self::$remarks,$params['remarks']);
        }
        if(!empty($params['viewed']))
        {
            $this->db->set(self::$viewed,$params['viewed']);
        }
        
        $this->db->set(self::$modified_on,$date->format('Y-m-d H:i:s'));
        if(!empty($params['id']))
        {
            $this->db->where(self::$id,$params['id']);
        }
        $query = $this->db->update(self::$table_name);

        if($query)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
    }

    public function delete($params=[])
    {
        $date = new DateTime();
       
        if(!empty($params['id']))
        {
            $this->db->where(self::$id,$params['id']);
        }
        $query = $this->db->delete(self::$table_name);

        if($query)
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
        $sql = '*';
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        
        if(isset($params['start']) && isset($params['end']))
		{
			$this->db->limit($params['end'],$params['start']);
		}
        $this->db->order_by(self::$created_on,'desc');
        $this->db->order_by(self::$viewed,'asc');

        $query = $this->db->get(self::$table_name);

        
        // echo $this->db->last_query();exit;
        if(!empty($params['cadet_no']))
        {
            return $query->row();
        }else{
            return $query->result();
        }

    }

    public function view_edit($params=[])
    {
        $sql = '*';
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        
        if(isset($params['start']) && isset($params['end']))
		{
			$this->db->limit($params['end'],$params['start']);
		}
        $query = $this->db->get(self::$table_name);
        if(!empty($params['id']))
        {
            $this->db->where(self::$id,$params['id']);

            return $query->row();
        }else{
            return $query->result();
        }

    }

    public function view($params=[])
    {
        $sql = '*';
        $this->db->select($sql);
        if(!empty($params['cadet_no']))
        {
            $this->db->where(self::$cadet_no,$params['cadet_no']);
        }
        
        if(isset($params['start']) && isset($params['end']))
		{
			$this->db->limit($params['end'],$params['start']);
		}
        $this->db->order_by(self::$created_on,'desc');
        $this->db->order_by(self::$viewed,'asc');

        $query = $this->db->get(self::$table_name);
        // echo $this->db->last_query();exit;
        
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

}
?>
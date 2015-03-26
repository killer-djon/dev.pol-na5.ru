<?php

class STRequestClassModel extends DbModel
{
	protected $table = 'st_request_class';
	
	public function add($request_id, $classes = false, $delete_old = false) 
	{
		if ($delete_old){
		  $sql = "DELETE FROM ".$this->table." WHERE request_id = i:request_id";
		  $this->prepare($sql)->query(array('request_id' => $request_id));
		}
		if (!empty($classes)) {
		    if (!is_array($classes)) {
				$classes = array($classes);
			}
			
			$sql = "INSERT IGNORE INTO ".$this->table."
					(request_id, class_id) 
					VALUES";
			$comma = false;
			foreach ($classes as $class_id) {
			    if (!empty($class_id)){
					if ($comma) {
						$sql .= ", ";
					} else {
						$comma = true;
					}
					$sql .= "(".(int)$request_id.", ".(int)$class_id.")";
			    }
			}
			return $this->exec($sql); 
		} else {
		    return false;
		}
	}
    
    public function replaceClass($old_class_id, $new_class_id)
    {
        $sql = "UPDATE ".$this->table." 
                SET class_id = i:new_class_id
                WHERE class_id = i:old_class_id";
        return $this->prepare($sql)->query(array('new_class_id' => $new_class_id, 'old_class_id' => $old_class_id));
    }
    
    public function selectClassesByRequest($request_id)
    {
            $sql = "SELECT st_class.id as class_id, st_class.name as class_name, st_class_type.*
					FROM st_request_class
					LEFT JOIN st_class ON st_request_class.class_id = st_class.id
					LEFT JOIN st_class_type ON st_class_type.id = st_class.class_type_id
					WHERE `request_id` = i:request_id
					ORDER BY st_class_type.sorting, st_class.sorting
                    ";
        return $this->prepare($sql)->query(array('request_id' => $request_id))->fetchAll();
    }
    
	public function countRequests($class_id, $class_type = false)
	{
	    if (!$class_type){
			$sql = "SELECT COUNT(*) 
					FROM ".$this->table." 
					WHERE class_id = i:class_id";
	    } else {
            $sql = "SELECT COUNT(*) FROM st_request_class 
                    LEFT JOIN st_class ON st_request_class.class_id=st_class.id
                    WHERE `class_type_id` = i:class_type";
	    }
		return $this->prepare($sql)->query(array('class_id' => $class_id, 'class_type' => $class_type))->fetchField();
	}
	
}
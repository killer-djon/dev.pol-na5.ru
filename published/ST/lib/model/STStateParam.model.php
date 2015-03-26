<?php

class STStateParamModel extends DbModel
{
	protected $table = 'st_state_param';
	
	public function getParams($state_id)
	{
		$sql = "SELECT name, value FROM ".$this->table." 
				WHERE sts_id = i:state_id";
		return $this->prepare($sql)->query(array('state_id' => $state_id))->fetchAll('name', true);
	}
}

?>
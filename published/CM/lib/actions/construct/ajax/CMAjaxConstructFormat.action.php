<?php

class CMAjaxConstructFormatAction extends UGAjaxAction
{
	protected $type_id;
	protected $formats = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->type_id = Env::Post('type_id', Env::TYPE_INT, 1);
		if ($this->type_id && Env::Post('format', Env::TYPE_INT, 0)) {
			$this->save();
		}
	}
		
	protected function save()
	{
		$first = ContactType::getFieldByDbName('C_FIRSTNAME', User::getLang());
		$middle = ContactType::getFieldByDbName('C_MIDDLENAME', User::getLang());
		$last = ContactType::getFieldByDbName('C_LASTNAME', User::getLang());
		
		$this->formats = array(
			1 => array(
				'format' => "!".$first['id']."! !".$middle['id']."! !".$last['id']."!",
				'name' => $first['name']." ".$middle['name']." ".$last['name'] 
			),
			2 => array(
				'format' => "!".$last['id']."! !".$first['id']."! !".$middle['id']."!",
				'name' => $last['name']." ".$first['name']." ".$middle['name']
			),
			3 => array(
				'format' => "!".$last['id']."!, !".$first['id']."! !".$middle['id']."!",
				'name' => $last['name'].", ".$first['name']." ".$middle['name']
			),
		);
		$this->formats[1]['sql'] = <<<SQL
	IF(C_FIRSTNAME IS NOT NULL AND C_FIRSTNAME != '', CONCAT(C_FIRSTNAME, " "), ""),
	IF(C_MIDDLENAME IS NOT NULL AND C_MIDDLENAME != '', CONCAT(C_MIDDLENAME, " "), ""), 	
 	IF(C_LASTNAME IS NOT NULL AND C_LASTNAME != '', CONCAT(C_LASTNAME, " "), "")		
SQL;

		$this->formats[2]['sql'] = <<<SQL
	IF(C_LASTNAME IS NOT NULL AND C_LASTNAME != '', CONCAT(C_LASTNAME, " "), ""),
	IF(C_FIRSTNAME IS NOT NULL AND C_FIRSTNAME != '', CONCAT(C_FIRSTNAME, " "), ""),
	IF(C_MIDDLENAME IS NOT NULL AND C_MIDDLENAME != '', CONCAT(C_MIDDLENAME, " "), "")
SQL;
		$this->formats[3]['sql'] = <<<SQL
	IF(C_LASTNAME IS NOT NULL AND C_LASTNAME != '', CONCAT(C_LASTNAME, ", "), ""),
	IF(C_FIRSTNAME IS NOT NULL AND C_FIRSTNAME != '', CONCAT(C_FIRSTNAME, " "), ""),
	IF(C_MIDDLENAME IS NOT NULL AND C_MIDDLENAME != '', CONCAT(C_MIDDLENAME, " "), "")
SQL;

		$format = Env::Post('format', Env::TYPE_INT, 0);
		$contact_type_model = new ContactTypeModel();
		$sql = "UPDATE CONTACT SET C_FULLNAME = TRIM(CONCAT(".$this->formats[$format]['sql'].")) WHERE CT_ID = ".(int)$this->type_id;
		$contact_type_model->exec($sql);
		$contact_type_model->saveFormat($this->type_id, $this->formats[$format]['format']);
		$this->response['format'] =str_replace(array(" name", " Name"), "", $this->formats[$format]['name']);
		
	}
	
	
}

?>
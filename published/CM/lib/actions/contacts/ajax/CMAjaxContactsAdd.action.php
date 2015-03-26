<?php

class CMAjaxContactsAddAction extends UGAjaxAction
{    
		protected $info = array();
		protected $type_id = 1;
		protected $contact_id;
		
		protected $type = array();
		protected $fields = array();

		protected $errors = array();
		
		public function __construct() 
		{
			Contact::checkLimits(1);
			$this->info = Env::Post('info', false, array());		
			$this->type_id = Env::Post('typeId', Env::TYPE_INT, 1);

			if ($this->info) {
			    $this->info['C_CREATEMETHOD'] = 'ADD';
				$this->contact_id = Contact::add($this->type_id, $this->info, $this->errors, true);
				if ($this->contact_id) {
				    User::addMetric('ADDCONTACT');
				} 
			}
		}
				
		/**
		 * Returns PHP response
		 *
		 * @return array
		 */
		public function getResponse()
		{	
			$response = array(
				'status' => $this->errors ? 'ERR' : 'OK',
				'error' => $this->errors 
			);

			$response['data'] = base64_encode($this->contact_id);
			if (isset($this->info['CF_ID'])) {
				$response['folder'] = $this->info['CF_ID'];
			}

			return json_encode($response);	
		}
	
}
?>
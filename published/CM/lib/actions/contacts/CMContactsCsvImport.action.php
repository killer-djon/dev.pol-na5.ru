<?php
 
class CMContactsCsvImportAction extends UGViewAction 
{
	
	protected $fields= array();
	protected $file = false;
	protected $delimiter = false;
	protected $first_line;
	
	protected $csv = false;
	
	protected $step = false;
	protected $import_errors = array();
	
	protected $info = array();
	protected $users = array();
	
	const UPLOAD = 'upload';
	const IMPORT = 'import';
	
    public function __construct()
    {
        ini_set('max_execution_time', 3600);

        parent::__construct();
        $this->title = _('Import contacts');
        
        $this->step = Env::Post('step');
        
        if (!$this->step) {
            Contact::checkLimits(1, true);
        }
        
        // First step
        if ($this->step == self::UPLOAD) {
            try {
        	    $this->upload();
            } catch (Exception $e) {
                $this->smarty->assign('import_error', _('Incorrect import data format'));
                $this->step = false;
            }
        }
        
        // Second step
        if ($this->step == self::IMPORT) {
        	$this->import();
        }   
    }

    public function upload()
    {
    	$this->csv = new CSV(false, false, false);
    	if (Env::Post('encode', Env::TYPE_STRING_TRIM, 'utf-8') != 'utf-8') {
    		$this->csv->encode = Env::Post('encode', Env::TYPE_STRING_TRIM); 
    	}
		if (Env::Post('from') == 1) {
			$this->file = $this->csv->saveContent(Env::Post('content'));	
		} elseif (Env::Post('from') == 2) {
			$this->file = $this->csv->upload("csv");
		} else {
			throw new Exception(_("Error"));
		}
		// Get info about file
		$this->info = $this->csv->getInfo();

    }
    
    
    public function import()
    {
    	// After upload
    	if (!$this->csv) {
	    	if (Env::Post('delimiter', Env::TYPE_INT, false) !== false) {
		    	$this->delimiter = CSV::$delimiters[Env::Post('delimiter')][0];
	    	}
	    	$this->first_line = Env::Post('first_line');
	    	$this->format_id = Env::Post('format_id');
	    	$this->csv = new CSV($this->format_id, $this->first_line, $this->delimiter, Env::Post('fields', false , array()), Env::Post('file'));
    	    if (Env::Post('encode', Env::TYPE_STRING_TRIM, 'utf-8') != 'utf-8') {
    			$this->csv->encode = Env::Post('encode', Env::TYPE_STRING_TRIM); 
    		}	    	
    	}
    	
    	$folder_id = Env::Post('folder_id');
    	// Added users
    	$this->users = array();
    	$users_model = new UsersModel();
    	$contacts_model = new ContactsModel();
    	$required_fields = array('C_FIRSTNAME', 'C_MIDDLENAME', "C_LASTNAME", "C_EMAILADDRESS");
    	// Read data from file and save to db
    	$i = 0;
    	
	    $contact_type_model = new ContactTypeModel();
	    $type_ids = $contact_type_model->getTypeIds();
	    $all_fields = ContactType::getAllFields();
	    $limit = Limits::get('CM');    	
	    if ($limit) {
	        $limit = $limit - $contacts_model->getQueryConstructor()->count() + $users_model->getQueryConstructor()->count();
	        if (!$limit) {
	            $limit = -1;
	        } 
	    }
	    $n = 1;
		while (++$i && ($data = $this->csv->import(1)) && (!$limit || $n <= $limit)) {
			$user_info = array_shift($data);
			if (!$user_info) {
			    continue;
			}
			
			if ($folder_id) {
			    $user_info['CF_ID'] = $folder_id;
			}
			// Users
			if (isset($user_info['U_ID']) && $user_info['U_ID']) {
				// if only login, then set first name = login
				$t = false;
				foreach ($required_fields as $f) {
					$t = $t || (isset($user_info[$f]) && $user_info[$f]);
				}
				if (!$t) {
					$user_info['C_FIRSTNAME'] = $user_info['U_ID'];
				}
				$login = mb_strtoupper($user_info['U_ID']);
		        if (!preg_match("/^[A-Z0-9_-]+$/i", $login)) {
					$this->import_errors[] = array(
						User::getName($user_info),
						_("Login ").$login.": "._("Latin letters and numbers only, no spaces")
					);
					continue;
        		}				
				try {
				    $user_info['C_CREATEMETHOD'] = 'ADD';
				    $errors = array();
                    $type = 1;
				    foreach ($type_ids as $type_id) {
				        $contact_type = new ContactType($type_id);
				        $main_fields = $contact_type->getMainFields(false);
				        foreach ($main_fields as $f) {
				            $dbname = $all_fields[$f]['dbname'];
				            if (isset($user_info[$dbname]) && $user_info[$dbname]) {
				                $type = $type_id;
				                break;
				            }
				        }
				    }
				    
				    $contact_id = Contact::add($type, $user_info, $errors);
					if ($contact_id) {
				        if (!$users_model->add($login, false, $contact_id, 0)) {
	            			$errors[] = _("This login name is already in use. Please try another name.");
	            			// Delete contact
	            			$contacts_model->delete($contact_id);
	        			} else {	
	        				$this->users[] = $user_info;
	        			}				
					}
				} catch (Exception $e) {
					$this->import_errors[] = array($login, $e->getMessage());
				}
			} else{
				try {
				    $type = 1;
					$errors = array();
					$user_info['C_CREATEMETHOD'] = 'ADD';
					foreach ($type_ids as $type_id) {
				        $contact_type = new ContactType($type_id);
				        $main_fields = $contact_type->getMainFields(false);
				        foreach ($main_fields as $f) {
				            $dbname = $all_fields[$f]['dbname'];
				            if (isset($user_info[$dbname]) && $user_info[$dbname]) {
				                $type = $type_id;
				                break 2;
				            }
				        }
				    }			
				    if (isset($user_info["C_EMAILADDRESS"]) && $user_info["C_EMAILADDRESS"]) {
				        $user_info["C_EMAILADDRESS"] = explode(", ", $user_info["C_EMAILADDRESS"]);
				    }		
					$contact_id = Contact::add($type, $user_info, $errors);
					if ($contact_id) {
					    $n++;
						$this->users[] = $user_info;
					} else {
						$this->import_errors[] = array(_("Row ").$i, $errors);
					}
				} catch (Exception $e) {
					$this->import_errors[] = array($login, $e->getMessage());
				} 
				
			}
		}
		
		if ($this->users) {
		    User::addMetric('IMPORT', 'ACCOUNT', count($this->users));
		}

		$this->step = self::IMPORT;
    }
    

    public function getDbFields()
    {
    	$contact_type = new ContactType();
    	return $contact_type->getFieldsNames(false, true, true);
    }
    
    public function treeToArray($tree, $right, $offset = 0)
    {
    	$result = array();
    	foreach ($tree as $folder) {
    		if ($folder[3] >= $right) {
    			$result[] = array(
    				'id' => $folder[0],
    				'name' => $folder[2],
    				'offset' => $offset
    			);
    		} 
    		if ($folder[4]) {
    			$result = array_merge($result, $this->treeToArray($folder[4], $right, $offset + 1));
    		}
    	}
    	return $result;
    }
    
    public function checkLimits()
    {
        if (isset($this->info['NUM_ROWS'])) {
            Contact::checkLimits($this->info['NUM_ROWS'] + 1, false, _("Your import may be partial."));
        }
    }
    
    protected function getEncodingList()
    {
    	$encoding = mb_list_encodings();
    	$list = array();
    	foreach ($encoding as $k => $v) {
    		if ($k > 10) {
    			$list[$v] = $v;
    		}
    	}
    	natcasesort($list);
    	return $list;
    }
    
    public function prepareData()
    {
    	$this->smarty->assign('step', $this->step);
    	$this->smarty->assign('file', $this->file);
    	if (!$this->step) {
    		$this->smarty->assign('folders', Contact::getFolders());
    		$this->smarty->assign('encoding', $this->getEncodingList());	
            $this->smarty->assign('current_folder', Contact::getCurrentFolder(Rights::RIGHT_WRITE));
            $this->smarty->assign('referer', Env::Server('HTTP_REFERER'));
    		   	
    	}
    	elseif ($this->step == self::UPLOAD) {
			$this->smarty->assign('delimiter', $this->info['DELIMITER_INDEX']);
			$this->smarty->assign('folder_id', Env::Post('folder_id'));
			$this->smarty->assign('encode', Env::Post('encode'));
			$this->smarty->assign('fields', $this->info['FIELDS']);
			$this->smarty->assign('records', $this->info['RECORDS']);
			$this->smarty->assign('n', $this->info['NUM_ROWS']);
    		$this->smarty->assign('dbfields', $this->getDbFields());
    		$this->smarty->assign('referer', Env::Post('referer'));
    	}
    	elseif ($this->step == self::IMPORT) {
    	    $this->smarty->assign('folder_id', Env::Post('folder_id'));
    		$this->smarty->assign('users', $this->users);
    		$this->smarty->assign('import_errors', $this->import_errors);
    	}
    	 
    }
    
}

?>
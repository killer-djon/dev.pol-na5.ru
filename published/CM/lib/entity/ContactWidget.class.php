<?php

class ContactWidget extends Widget
{
    protected $type = 'SBSC';
	/**
	* Default for the text fields you can find in system gettext file
	*/ 
	public $param_fields = array (
		"FOLDER" => array("default" => false),
	    "LISTS" => array("default" => false),
		"CMFIELDS" => array("default" => false),
		"CMFIELDSLABELS" => array("default" => false),  
	
		"TITLE" => array("default" => "Signup", "gettext" => true),
		"SAVEBTN" => array ("default" => "Submit", "gettext" => true),
		    
		"WIDTH" => array("default" => 180, "min" => 100, "max" => 800),
		"TITLE_bgcolor" => array ("default" => "#999999"),
		"TITLE_color" => array ("default" => "#FFFFFF"),
		"BGCOLOR" => array ("default" => "#F0F0F0"),	
	    
		"DOPTIN" => array ("default" => false),
		"EMAILSEND" => array ("default" => false),	
		"SIGNUPTEXT" => array ("rows" => 3, "default" => "Thank you!", "gettext" => true),
		"REDIRECT" => array ("default" => ""),
	    "NEWWINDOW" => array("default" => false),		
		"EMAILFROMNAME" => array(),
		"EMAILFROM" => array(),
	    "EMAILSUBJECT" => array("default" => "Please confirm your subscription", "gettext" => true),
		"EMAILTEXT" => array ("rows" => 3, "default" => '<p>{COMPANY_NAME} has just received a sign-up request from your address: {EMAILADDRESS}</p><p>Please click this link to confirm your subscription:<br />{CONFIRM_SUBSCRIPTION_URL}</p><p>Thank you.</p>', "gettext" => true),
	    "CAPTCHA" => array ("default" => false),	  
		"CAPTCHA_TITLE" => array("default" => "Type these numbers", "gettext" => true),  
	);
	
	protected function getFields()
	{
	    $type = $this->getParam('CT_ID', 1);
	    $contact_type = new ContactType($type);
	    $subtype = $this->getInfo('WST_ID');
	    switch ($subtype) {
	        case 'PHOTO':
	            $fields = $contact_type->getMainFields(false);
	            $result = array();
	            foreach ($fields as $field_id) {
	                $result[ContactType::getDbName($field_id)] = 1;
	            }
                $result['C_EMAILADDRESS'] = 1;
                $photo_field = $contact_type->getPhotoField();
	            if ($photo_field) {
		            $result[ContactType::getDbName($photo_field)] = 1;
                }
                return $result;
	        case 'SIMPLE':
	            return array('C_EMAILADDRESS' => 1);
	        case 'MAIN':
                return array('C_FULLNAME' => 1, 'C_EMAILADDRESS' => 1);
	        default:
	            return array('C_FULLNAME' => 1, 'C_COMPANY' => 1, 'C_EMAILADDRESS' => 1);                
	    }
	}
	
	
	public function getParam($name = false, $default = false)
	{
	    if ($name == 'CMFIELDS' && !isset($this->params[$name])) {
	        $default = $this->getFields();
	    } elseif ($name == 'EMAILFROM' && !isset($this->params[$name])) {
	        $default = Company::get('COM_EMAIL'); 
	        if (!$default) {
	        	$default = Wbs::getSystemObj()->getEmail();
	        }
	    } elseif ($name == 'EMAILFROMNAME' && !isset($this->params[$name])) {
	        $default = Company::getName();
	    }
	    // For support old widgets
	    if ($name == 'EMAILSEND' && !isset($this->params['EMAILSEND'])) {
	        return $this->getParam('DOPTIN', $default);
	    }
	    
       // is set
        if ($name && isset($this->params[$name])) {
            if ($name == 'CMFIELDS' || $name == 'LISTS') {
                $values = explode(",", $this->params[$name]);
                $result = array();
                foreach ($values as $value) {
                    $result[$value] = 1;
                }
                return $result;
            } elseif ($name == 'CMFIELDSLABELS' && $this->params[$name]) {
                $labels = explode(";", $this->params[$name]);
                $result = array();
                foreach ($labels as $label_info) {
                    $label = explode("=", $label_info, 2);
                    $result[$label[0]] = $label[1];
                }
                return $result;
            }
            return $this->params[$name];
        }
        
        return parent::getParam($name, $default);
	}
	
	public function getTypeId()
	{
	    $type = $this->getParam('CT_ID');
	    return $type ? $type : 1;
	}
	
	public function getHeight()
	{
		$dbfields = $this->getParam('CMFIELDS');
		$fields = array();
		foreach ($dbfields as $dbfield => $use) {
		    $field_info = ContactType::getFieldByDbName($dbfield);
			if ($field_info) {
				$fields[] = $field_info;
			}
		}
        $height = 85;
        foreach ($fields as $field) {
            switch ($field['type']) {
                case 'TEXT': 
                    $height += 75;
                    break;
                case 'IMAGE':
                    $height += 160;
                    break;
                default: 
                    $height += 44.5;
            }
        }
	    return round($height);
	}
	
	public function getEmbedInfo()
	{
	    $info = parent::getEmbedInfo();
	    $lang = $this->getInfo('WG_LANG');
		$dbfields = $this->getParam('CMFIELDS');
		$field_names = $this->getParam('CMFIELDSLABELS');
		$fields = array();
		foreach ($dbfields as $dbfield => $use) {
		    if ($dbfield == 'C_FULLNAME') {
		        $field_info = array(
		            'id' => 0,
		            'dbname' => 'C_FULLNAME',
		            'name' => _s('Full name'),
		            'type' => 'VARCHAR',
		            'options' => 255
		        );
		    } else {
		        $field_info = ContactType::getFieldByDbName($dbfield, $lang);
		    }
		    if (!$field_info) {
		        continue;
		    }
		    if (isset($field_names[$dbfield]) && $field_names[$dbfield]) {
		        $field_info['name'] = $field_names[$dbfield];
		    }
			$fields[] = $field_info;
		}	

		$file_exists = false;
		$code = "\n".'<input type="hidden" value="signup" name="action" />';
		$code .= "\n".'<input class="encoding" type="hidden" value="" name="encoding" />';
        $code .= "\n".'<input class="source" type="hidden" value="" name="source" />';		
		$contact_type = new ContactType($this->getTypeId());
		$width = $this->getParam('WIDTH');
		$main_fields = $contact_type->getMainFields();
		foreach ($fields as $f) {
		    $class = array();
		    $attr = array();
		    if (in_array($f['id'], $main_fields) || $f['dbname'] == 'C_FULLNAME') {
		        $class[] = "primary";
		    }
		    $set_width = true;
			switch ($f['type']) {
			    case 'VARCHAR':
			        if ($f['options']) {
			            $attr[] = 'maxlength="'.$f['options'].'"';
			        }
			        break;
			    case 'URL':
			        $class[] = "url";
			        break;
			    case 'CHECKBOX': 
			    	$set_width = false;
			    	$attr[] = 'value="1"';
			    	break;
			    case 'NUMERIC':
			        $set_width = false;
			        $class[] = "number"; 
			        break;
			    case 'EMAIL':
			        $class[] = "email";
			        break;
			    case 'IMAGE':
				    $file_exists = true;
				    break;
			}
			$class = implode(" ", $class);
			$attr = implode(" ", $attr);
			if ($f['type'] == 'DATE') {
    		    $code .= '<label for="wbs-field'.$f['id'].'">'.$f['name'].'</label>';
    		    $code .= '<select name="'.$f['dbname'].'[d]" style="width: 40px"><option></option>';
    		    for ($i = 1; $i <= 31 ; $i++) {
    		        $code .= '<option value="'.$i.'">'.$i.'</option>';
    		    }
    		    $code .= '</select>';
    		    $code .= '<select name="'.$f['dbname'].'[m]" style="width: 50px"><option></option>';
    		    for ($i = 1; $i <= 12 ; $i++) {
    		        $month = date("M", mktime(0, 0, 0, $i, 1, 1971));
    		        $code .= '<option value="'.$i.'">'.$month.'</option>';
    		    }
    		    $code .= '</select>';    		    
                $code .= '<input style="width:35px" id="wbs-field'.$f['id'].'" name="'.$f['dbname'].'[y]" type="text" class="year" maxlength="4" />';
			} elseif ($f['type'] == 'COUNTRY') {
				$code .= '<label for="wbs-field'.$f['id'].'">'.$f['name'].'</label>';
				$code .= '<select style="width:'.$width.'" name="'.$f['dbname'].'"><option value=""></option>';
				$countries = Wbs::getCountries($lang);
				foreach ($countries as $iso => $name) {
					$code .= '<option value="'.$iso.'">'.trim($name).'</option>';
				} 
				$code .= '</select>';
			} elseif ($f['type'] == 'MENU') {
				$code .= '<label for="wbs-field'.$f['id'].'">'.$f['name'].'</label>';
				$code .= '<select style="width:'.$width.'" name="'.$f['dbname'].'"><option value=""></option>';
				foreach ($f['options'] as $v) {
					$code .= '<option value="'.$v.'">'.$v.'</option>';
				} 
				$code .= '</select>';		
			} elseif ($f['type'] == 'CHECKBOX') {
				$code .= <<<HTML
<input style="margin-left:0; padding-left:0" id="wbs-field{$f['id']}" name="{$f['dbname']}" type="checkbox" class="{$class}" {$attr} />				
<label style="display:inline" for="wbs-field{$f['id']}">{$f['name']}</label>
HTML;
				
			} elseif ($f['type'] == 'TEXT') {
				$code .= '<label for="wbs-field'.$f['id'].'">'.$f['name'].'</label>';
				$code .= <<<HTML
<textarea rows="3" id="wbs-field{$f['id']}" style="width:{$width}" name="{$f['dbname']}"></textarea>
HTML;
				
			} else {
			    if ($set_width) {
			        $set_width = 'style="width:'.$width.'" ';
			        if ($f['type'] == 'IMAGE') {
			            $set_width .= ' size="'.(int)($width * 14 / 180).'" ';
			        }
			    }
			    switch ($f['type']) {
			    	case 'IMAGE': 
			    		$type = 'file';
			    		break;
			    	default: 
			    		$type = 'text';
			    }
    		    $code .= <<<HTML
<label for="wbs-field{$f['id']}">{$f['name']}</label>
<input {$set_width}id="wbs-field{$f['id']}" name="{$f['dbname']}" type="{$type}" class="{$class}" {$attr} />
HTML;
			}
		}	
		$url = Url::get('/WG/', true);
		$info['html_code'] = <<<HTML
<link rel="stylesheet" type="text/css" media="screen" href="{$url}css/contacts.css" />
<script type="text/javascript" src="{$url}js/jquery.js"></script>
<script type="text/javascript" src="{$url}js/jquery.validate.js"></script>
<script type="text/javascript" src="{$url}js/contacts.js"></script>

HTML;
		if ($this->getInfo('WG_LANG') == 'rus') {
			$info['html_code'] .= <<<HTML
<script type="text/javascript" src="{$url}js/contacts-rus.js"></script>

HTML;
		}
        $target = "";
        if ($this->getParam('REDIRECT')) {
            if ($this->getParam("NEWWINDOW")) {
                $target = ' target="_blank"';
            }
            $use_iframe = "";
        } else {
            $use_iframe = " use-iframe";
        }
		$info['html_code'] .= '<form class="wbs-sign-up'.$use_iframe.'" '.($file_exists ? 'enctype="multipart/form-data"' : '').' method="post" action="'.$info['src'].'&from=form" style="width:'.$width.'"'.$target.'>'.$code;
		$button = $this->getParam('SAVEBTN');
		$message = $this->getParam('SIGNUPTEXT');
		if ($this->getParam('CAPTCHA')) {
		    $url = Url::get("/WG/captcha.php", true) ;
		    $info['html_code'] .= <<<HTML
{$this->getParam('CAPTCHA_TITLE')}<br />
<img style="vertical-align:middle" src="{$url}" class="captcha" />&rarr;<input style="width: 35px" id="wbs-field-captcha" name="CAPTCHA" type="text" class="captcha digits" maxlength="4" minlength="4" />
HTML;
		}
		$info['html_code'] .= <<<HTML
<div>
    <div class="error"></div>
    <div class="form-message">{$message}</div>
	<input class="submit" type="submit" value="{$button}" />
</div>
</form>		 
HTML;
	    return $info;
	}
}

?>
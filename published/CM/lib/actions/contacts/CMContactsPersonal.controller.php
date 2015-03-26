<?php 

class CMContactsPersonalController extends UGController
{
	public $public = true;
	
	protected $hash; 
	protected $contact_id;
	
	public function exec()
	{
		$this->layout = false;
		if ($this->ajax) {
		    if (Env::Get('close')) {
		        $this->actions[] = new CMAjaxContactsCloseAction(); 
		    } else {
		        $this->actions[] = new CMAjaxContactsPersonalAction();
		    }
		} elseif (Env::Get('m') == 'requests' && ($key = Env::Get('attachment'))) {
			include WBS_PUBLISHED_DIR."ST/config/autoload.php";
			$_GET['id'] = substr($key, 0, -1);
			$_GET['n'] = substr($key, -1);
			$controller = new STRequestsAttachmentController();
			$controller->exec();
		} else {
			if (Env::Get('t') == 'requests') {
				include WBS_PUBLISHED_DIR."ST/config/autoload.php";
				User::setApp('ST');
				
				// Localization (Gettext)
			    $lang = Env::Get('lang', Env::TYPE_STRING, false);
			    if (!$lang) {
			    	$lang = User::getLang();
			    }
			    $lang = substr($lang, 0, 2);
			    GetText::load($lang, WBS_PUBLISHED_DIR."ST/locale", 'webasystST');			         
			    $smarty = new WbsSmarty(WBS_PUBLISHED_DIR."ST/templates", 'ST', $lang);		
				// Set smarty
				Registry::set("smarty", $smarty);				
				
                if (!Env::Get('a') && !Env::Get('iframe')) {
				    $this->layout = 'Personal';
                }
				$view = Registry::get('UGSmarty');
				$view->assign('company', Company::getName());
				
			    if (!$this->check()) {
					header("HTTP/1.0 404 Not Found");
					echo "<h1>Not Found</h1>";
					exit;        	
        		}		
        				
				$this->contact_info = Contact::getInfo($this->contact_id);
				$view->assign('contact_name', Contact::getName($this->contact_id));
				$view->assign('contact', $this->contact_info);
				
		    	// Check logo exists
		    	$logoFilename = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("AA", "logo.gif");
		    	$logoExists = file_exists($logoFilename);
		    	$logoTime = ($logoExists) ? filemtime($logoFilename) : null;
		    	
		    	$view->assign('contact_url', "?key=".Env::Get('key').(Wbs::isHosted() ? "" : "&DK_KEY=".Wbs::getDbkeyObj()->getDbkey()));
		    	
		    	$view->assign('subscribe_exists', Wbs::getDbkeyObj()->appExists('MM'));
		    	
				$wbs_d = (int)(strtotime('2010-07-16 00:00:00') - time()) / 86400;
				$view->assign('wbs_d', $wbs_d); 		    	
				$view->assign('wbs', Wbs::isHosted() && Wbs::getDbKey() == 'WEBASYST');		    	
		    	
		    	// Load viewsettings
		    	$dbkeyObj = Wbs::getDbkeyObj();
		    	$showLogo = ($dbkeyObj->getAdvancedParam("show_company_top") == "yes") && $logoExists;
		    	$showCompanyName = ($dbkeyObj->getAdvancedParam("show_company_name_top") != "no");	    
		
		    	$view->assign('logo_time', $logoTime);
		    	$view->assign('show_logo', $showLogo);		
				if (Env::Get('a') == 'info') {
                    $this->actions[] = new STPersonalInfoAction($this->contact_id);
				} elseif (Env::Get('a') == 'save') {
                    $this->actions[] = new STPersonalSaveAction($this->contact_id);
                } else {
				    $this->actions[] = new STPersonalListAction($this->contact_id);
				}
			} else {
		    	$this->actions[] = new CMContactsPersonalAction();
			}
		}
	}	

	public function check()
	{
        $this->hash = Env::Get('key');
        
		if (defined('STRONG_AUTH') && STRONG_AUTH) {
			$hash = explode('-', $this->hash);
			$this->contact_id = substr($hash[0], 6, -6);
		} else {
			$this->contact_id = substr($this->hash, 6, -6);
		}
        		
		$contact_info = Contact::getInfo($this->contact_id);
		if (!$contact_info) {
			return false;
		}
		$contacts_model = new ContactsModel();
		$contact_info = $contacts_model->get($this->contact_id);
		if (defined('STRONG_AUTH') && STRONG_AUTH) {
			$md5 = md5($this->contact_id.$contact_info['C_CREATEDATETIME']);
			$hash = explode('-', $this->hash, 2);
			if (!isset($hash[1]) || $hash[1] != (is_array($contact_info['C_EMAILADDRESS']) ? $contact_info['C_EMAILADDRESS'][0] : $contact_info['C_EMAILADDRESS'])) {
				return false;
			}
			return (substr($hash[0], 0, 6) == substr($md5, 0, 6) && substr($hash[0], -6) == substr($md5, -6));
		} else {
			$md5 = md5($contact_info['C_CREATEDATETIME']);
			return (substr($this->hash, 0, 6) == substr($md5, 0, 6) && substr($this->hash, -6) == substr($md5, -6));
		} 
	}		
}

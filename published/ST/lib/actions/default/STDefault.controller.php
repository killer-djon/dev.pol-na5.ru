<?php

/**
 * Default view controller for service Support Tracker
 * 
 * @author Webasyst Team
 */
class STDefaultController extends ViewController
{
	protected $action_name;
	
	public function __construct($action_name = false)
	{
		$this->action_name = $action_name;
		parent::__construct();
	}
	
	public function exec()
	{
		if ($this->action_name) {
			$action = $this->action_name;
			$this->invokeAction(new $action());
		}
	}
}
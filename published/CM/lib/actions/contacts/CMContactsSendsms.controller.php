<?php

class CMContactsSendsmsController extends UGController 
{
    public function exec()
    {
        $this->layout = false;
		$this->title = _('Send SMS');
		try {
			$this->actions[] = new CMContactsSendsmsAction();
		} catch (Exception $e) {
			throw  $e;
		}
    }

}

?>
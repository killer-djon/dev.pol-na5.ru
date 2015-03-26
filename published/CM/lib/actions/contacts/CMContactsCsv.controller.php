<?php
 
class CMContactsCsvController extends UGController 
{
    
    public function exec()
    {
        $this->layout = 'Empty';
        if (Env::Get('import')) {
            $this->title = _('Import contacts');
            try {
        	    $this->actions[] = new CMContactsCsvImportAction();
            } catch (Exception $e) {
                $this->layout = false;
                throw  $e;
            }
        } else {
			$this->layout = false;
        	$this->actions[] = new CMContactsCsvExportAction();
        }
    }

}

?>
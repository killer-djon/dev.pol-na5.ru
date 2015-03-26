<?php

class STRequestsAttachmentController extends Controller
{ 
	public function exec()
	{
		$type = substr(Env::Get('id'), 0, 1);
		$id = (int)substr(Env::Get('id'), 1);
		// request
		if ($type == 'r') {
			$request_model = new STRequestModel();
			$attachments = $request_model->get($id, 'attachments');
			$request_id = $id;
			$log_id = false;
		} else {
			$request_log_model = new STRequestLogModel();
			$log = $request_log_model->getById($id);
			$request_id = $log['request_id'];
			$log_id = $id;
			$attachments = $log['attachments'];
		}
		
		if (!$attachments) {
			header('"HTTP/1.0 404 Not Found"');
			return;
		}
		$attachments = unserialize($attachments);
	
		$file = $attachments[Env::Get('n')];
		if (substr($file['file'], 0, 1) == '/') {
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentsDir('ST');
			$path .= "/attachments".$file['file'];
		} else {		
			$path = STRequest::getAttachmentsPath($request_id, $log_id);
			$path .= "/".$file['file'];
		}
		
		header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: private",false);
	    header("Content-Disposition: attachment; filename=\"".$file['name']."\";");
	    header("Content-Transfer-Encoding: binary");
	    header("Content-Length: ".$file['size']); 
      
		if (isset($file['type'])) {
			header('Content-type: '.$file['type']);
		}
		
		readfile($path);
	}
}
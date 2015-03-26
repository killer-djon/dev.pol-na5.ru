<?php 

class STNotesPlugin extends STPlugin
{
	protected $apps = array(
		'CM'
	);

	protected function register()
	{
		$this->registerMethod('top', 'notes');
	}	
	
	public function notesAction($params)
	{
		$contact_id = $params['contact_id'];
		$notes_model = new ContactNotesModel();
		$notes = $notes_model->getByContactId($contact_id);
	    foreach($notes as &$note) {
            $note['CN_TEXT'] = nl2br($note['CN_TEXT']);
            $note['date'] = WbsDateTime::getTime(strtotime($note['CN_CREATETIME']));
            $note['author'] = Contact::getName($note['CN_CREATECID']);
        }
		$view = View::getInstance();
        $view->assign('contact_id', $contact_id);
        $view->assign('notes', $notes);
		return $this->display($view, 'Notes');
	}
    
    public function saveAction($params)
    {
        $note_id = $params['noteid'];
        $notes_model = new ContactNotesModel();
        $success = $notes_model->save($note_id, $params['text']);
        if ($success) {
            $success = json_encode(array("text" => nl2br($params['text'])));
        }
        $view = View::getInstance();
        $view->assign('success', $success);
        return $this->display($view, 'NotesAjax');
    }
    
    public function deleteAction($params)
    {
        $note_id = $params['noteid'];
        $notes_model = new ContactNotesModel();
        $success = $notes_model->delete($note_id);
        $view = View::getInstance();
        $view->assign('success', $success);
        return $this->display($view, 'NotesAjax');
    }
    
    public function addAction($params)
    {
        $contact_id = $params['contactid'];
        $notes_model = new ContactNotesModel();
        $success = $notes_model->add($contact_id, $params['text'], User::getContactId());
        $view = View::getInstance();
        if ($success) {
            $success = json_encode(array(
	            "id" => $success, 
	            "author" => User::getName(), 
	            "date" => WbsDateTime::getTime(time()),
                "text" => nl2br($params['text'])
            ));
        }
        $view->assign('success', $success);
        return $this->display($view, 'NotesAjax');
    }

}

?>

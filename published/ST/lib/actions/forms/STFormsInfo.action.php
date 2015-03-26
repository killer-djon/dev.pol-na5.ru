<?php

class STFormsInfoAction extends Action
{
	protected $id;
	protected $info = array();
	protected $params = array();
	protected $embed = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->id = Env::Get('id', Env::TYPE_INT, 0);
	}
	
	protected function getInfo()
	{
		$form = new SupportForm($this->id);
		$this->info = $form->getInfo();
		$this->params = $form->getParam();
		$this->params['LABELS'] = $form->getParam('LABELS');
		$this->embed = $form->getEmbedInfo();
	}
	
	public function prepare()
	{
		$this->getInfo();
		
        $this->view->assign('id', $this->id);
		$this->view->assign('info', $this->info);
		$this->view->assign('embed', $this->embed);
		$this->view->assign('params', $this->params);
		$selected_classes_types = array();
		$selected_classes = explode(';',$this->params['CLASSES']);
        array_pop($selected_classes);
		foreach ($selected_classes as &$s_class_type) {
		    $tmp = explode('=',$s_class_type);
		    $s_class_type = $tmp[0];
            $selected_classes_types[$tmp[0]] = $tmp[1];
		}
		
        $source_model = new STSourceModel();
        $data = $source_model->getAllWithEmail();
        $sources = array();
        foreach ($data as $source) {
            $sources[$source['type']][] = $source;
        }
        $this->view->assign('sources', $sources);
        $this->view->assign('default_email', User::getSetting('DEFAULT_EMAIL',  'ST', ''));
        
		$class_type_model = new STClassTypeModel();
        $data = $class_type_model->getAll();
        $classTypes = array();
        foreach ($data as $class_type) {
            $class_type['selected']=0;
            $class_type['type']=0;
            if (in_array($class_type['id'], $selected_classes)){
                $class_type['selected']=1;
                $class_type['type']= $selected_classes_types[$class_type['id']];
            }
            $classTypes[] = $class_type;
        }
        $this->view->assign('classTypes', $classTypes);
		
		$this->view->assign('langs', Wbs::getDbkeyObj()->getLanguages());
	}
}
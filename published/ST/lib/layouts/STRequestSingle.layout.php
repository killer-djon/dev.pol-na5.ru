<?php

class STRequestSingleLayout extends Layout
{
    
    public function getData()
    {
        $data = array();
        
        $state_model = new STStateModel();
        $data['states'] = $state_model->getAll();
        
        $action_model = new STActionModel();
        $data['actions'] = $action_model->getAll();
        
        return $data;
    }
    
    public function display()
    {
        $this->view = View::getInstance();
        foreach ($this->blocks as $name => $content) {
            $this->view->assign($name, $content);
        }    
        $app_id = User::getAppId();
        $url = array(
            'common' => Url::get("/common/"),
            'app' => Url::get("/".$app_id."/"),
            'css' => Url::get('/'.$app_id.'/css/'),
            'img' => Url::get('/'.$app_id.'/img/'),
        );
        if (defined("USE_LOCALIZATION") && USE_LOCALIZATION) {
            $lang = mb_substr(User::getLang(), 0, 2);
            $url['js'] = Url::get('/'.$app_id.'/js/' . $lang . '/');
        } else {      
            $url['js'] = Url::get('/'.$app_id.'/js/source/');
        }
        $this->view->assign('data', $this->getData(), View::TYPE_JSON);
        $this->view->assign('id', Env::Get('id',Env::TYPE_INT));
        $this->view->assign("url", $url);                
        $this->view->display($this->getTemplate());
    }
}

?>
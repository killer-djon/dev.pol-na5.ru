<?php

class STRequestsTabsAction extends Action
{
    public function prepare()
    {
        $request_id = Env::Get('id', Env::TYPE_INT, 0);
        $request_model = new STRequestModel();
        $request_info = $request_model->get($request_id);
        $this->view->assign('contact', $request_info['client_c_id']);
        
        if ($request_info['client_c_id']) {
            $plugins = new STPlugins();
            $panel_items = $plugins->exec('ContactPanelItem', $request_info['client_c_id']);
            $tab_items = $plugins->exec('ContactTabItem', $request_info['client_c_id']);
        } else {
            $tab_items = $panel_items = array();
        }       
                
        $count_requests = $request_model->countByContact($request_info['client_c_id']);
        $this->view->assign('other_requests', $count_requests - 1);
        
        $this->view->assign('request', $request_info);
        $this->view->assign('panel_items', $panel_items);
        $this->view->assign('tab_items', $tab_items);       
    }
}

?>
<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/PM/pm.php" );
	require_once dirname(dirname(__FILE__)).'/classes/class.tasks_exporter.php';
	require_once dirname(dirname(__FILE__)).'/classes/class.lang_message.php';
	
	//
	// Authorization
	//
	$SCR_ID = "WL";
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	$_errors = array();
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];

	if(getButtonIndex( array(BTN_CANCEL), $_POST ) == 0)
	{
	    redirectBrowser( PAGE_PM_WORKLIST, array() );
	};

	$exporter = &new Tasks_Exporter();
	$exporter->loadCurrentStepFromRequest();

	if(!empty($_errors))
	{
	    $_SESSION['export_tasks_errors'] = $_errors;
	    redirectBrowser(PAGE_PM_EXPORT_TASKS, array('export_step' => $exporter->getPrevStep()));
	};
	
	if(!array_key_exists('export_tasks_errors', $_SESSION))
	{
    	$exporter->setDataSource($_POST);
    	$exporter->processCurrentStep();
    	$exporter->saveState();
    	
    	$results = $exporter->getResults();
    	$_errors = $results['errors'];

    	if(!empty($_errors))
    	{
    	    $_SESSION['export_tasks_errors'] = $_errors;
    	    redirectBrowser(PAGE_PM_EXPORT_TASKS, array('export_step' => $exporter->getPrevStep()));
    	};
	}
	else
	{
	    $_errors = $_SESSION['export_tasks_errors'];
	    unset($_SESSION['export_tasks_errors']);
	};

	
	$_errors = array_map(
	    create_function('$a', 'return "export_tasks_err_".$a;')
	   ,$_errors
	);
	
	
	$results['messages'] = array_map(
	    create_function('$a', 'return array("export_tasks_msg_".$a[0], $a[1]);')
	   ,$results['messages']
	);
	$results['messages'] = array_map(array('Lang_Message', 'render'), $results['messages']);

	$exporter_state = $exporter->getState();
	$proj_data = pm_getProjectData($exporter_state['project_id'], $kernelStrings);

	$tabs = array(
	array( PT_NAME => '',
    	   PT_PAGE_ID => 'EXPORT_TSK',
    	   PT_FILE => $exporter->getCurrentStep().'_frm.htm',
    	   PT_CONTROL => ''
		 )
	);
	
	$selected_tasks = $exporter_state['task_ids'];
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );
	$preproc->assign( PAGE_TITLE, $pmStrings['export_tasks_screen_long_name'] );
	$preproc->assign('base_uri', prepareURLStr(PAGE_PM_EXPORT_TASKS, array()));
	
	$preproc->assign('_errors', $_errors);
	$preproc->assign('exporter_state', $exporter_state);

	$preproc->assign('proj_data', $proj_data);
	
	$preproc->assign('tabs', $tabs);
	$preproc->assign('activeTab', 'EXPORT_TSK');
	
	$preproc->assign('current_step_name', $exporter->getCurrentStep());
	$preproc->assign('step_tpl', $exporter->getCurrentStep().'.htm');
	$preproc->assign('next_step_name', $exporter->getNextStep());
	$preproc->assign('back_url', prepareURLStr(PAGE_PM_EXPORT_TASKS, array('export_step' => $exporter->getPrevStep())));
	$preproc->assign('forward_url', prepareURLStr(PAGE_PM_EXPORT_TASKS, array('export_step' => $exporter->getNextStep())));
	$preproc->assign('results', $results);
	
	$preproc->assign('includedFields', $exporter_state['included_fields']);
	$preproc->assign('excludedFields', $exporter_state['excluded_fields']);
	
	$preproc->assign('includedFieldNames', array_map(
	     create_function('$a', 'global $pm_importTaskColumnNames; return Lang_Message::render($pm_importTaskColumnNames[$a]);') 
	    ,$exporter_state['included_fields']));
	
	$preproc->assign('excludedFieldNames', array_map(
	     create_function('$a', 'global $pm_importTaskColumnNames; return Lang_Message::render($pm_importTaskColumnNames[$a]);') 
        ,$exporter_state['excluded_fields']));
	
	$preproc->assign('iconv_encoding_charsets', $iconv_encoding_charsets);
	
	$preproc->assign('selected_msg', Lang_Message::render(array('export_tasks_source_selected', count($selected_tasks))));
	
	$preproc->display("export_tasks/index.htm");
?>
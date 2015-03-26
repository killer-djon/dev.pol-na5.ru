<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/PM/pm.php" );
	require_once dirname(dirname(__FILE__)).'/classes/class.tasks_importer.php';
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
	$metric = metric::getInstance();
	if(getButtonIndex( array(BTN_CANCEL), $_POST ) == 0)
	{
	    redirectBrowser( PAGE_PM_WORKLIST, array() );
	};

	if(!empty($_FILES))
	{
	    foreach($_FILES as $fname => $finfo)
	    {
	        if($finfo['error'] == UPLOAD_ERR_OK)
	        {
	            $dst_path = str_replace("\\","/",realpath(WBS_TEMP_DIR)).'/'.uniqid(TMP_FILES_PREFIX);
	            if(@move_uploaded_file($finfo['tmp_name'], $dst_path) != false)
	            {
	                $file_info = array(
	                    'name' => $finfo['name']
	                   ,'path' => $dst_path
	                );
	                $_POST[$fname] = $file_info;
	            }
	            else
	            {
	                $_errors[] = 'upload_file';
	            };
	        }
	        else
	        {
	            $_errors[] = 'upload_file';
	        };
	    };
	};

	
	$importer = &new Tasks_Importer();
	$importer->loadCurrentStepFromRequest();

	if(!empty($_errors))
	{
	    $_SESSION['import_tasks_errors'] = $_errors;
	    redirectBrowser(PAGE_PM_IMPORT_TASKS, array('import_step' => $importer->getPrevStep()));
	};
	
	if(!array_key_exists('import_tasks_errors', $_SESSION))
	{
    	$importer->setDataSource($_POST);
    	$importer->processCurrentStep();
    	$importer->saveState();
    	
    	$results = $importer->getResults();
    	$_errors = $results['errors'];

    	if(!empty($_errors))
    	{
    	    $_SESSION['import_tasks_errors'] = $_errors;
    	    redirectBrowser(PAGE_PM_IMPORT_TASKS, array('import_step' => $importer->getPrevStep()));
    	};
	}
	else
	{
	    $_errors = $_SESSION['import_tasks_errors'];
	    unset($_SESSION['import_tasks_errors']);
	};

	$next_step_url = prepareURLStr(PAGE_PM_IMPORT_TASKS, array('import_step' => $importer->getNextStep()));
	
	$_errors = array_map(
	    create_function('$a', 'return "import_tasks_err_".$a;')
	   ,$_errors
	  );
	
	$task_column_names = array_keys($pm_importTaskColumnNames);
	$task_column_labels = array_values(array_map(
	    create_function('$a', 'global $pmStrings; return $pmStrings[$a];')
	   ,$pm_importTaskColumnNames
	));
	
	array_unshift($task_column_names, null);
	array_unshift($task_column_labels, $pmStrings['icl_select_item']);

	$results['messages'] = array_map(
	    create_function('$a', 'return array("import_tasks_msg_".$a[0], $a[1]);')
	   ,$results['messages']
	);
	$totalToImport = $results['messages'][0][1];
	$results['messages'] = array_map(array('Lang_Message', 'render'), $results['messages']);
	
	$importer_state = $importer->getState();
	$proj_data = pm_getProjectData($importer_state['project_id'], $kernelStrings);

	$tabs = array(
	array( PT_NAME => '',
    	   PT_PAGE_ID => 'IMPORT_TSK',
    	   PT_FILE => $importer->getCurrentStep().'_frm.htm',
    	   PT_CONTROL => ''
		 )
	);
	
	if ( $importer->getCurrentStep() == 'import_process') {
	    $metric->addAction($DB_KEY, $currentUser, 'PM', 'IMPORT', 'ACCOUNT', $totalToImport);
	}
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );
	$preproc->assign( "pmStrings", $pmStrings );
	$preproc->assign( PAGE_TITLE, $pmStrings['import_tasks_screen_long_name'] );
	$preproc->assign('base_uri', prepareURLStr(PAGE_PM_IMPORT_TASKS, array()));
	$preproc->assign('import_help_file', ($preproc->template_exists('import_tasks/help.'.$language.'.html') ? 'help.'.$language.'.html': 'help.'.DEF_LANG_ID.'.html'));
	
	$preproc->assign('proj_data', $proj_data);
	
	$preproc->assign('tabs', $tabs);
	$preproc->assign('activeTab', 'IMPORT_TSK');
	
	$preproc->assign('_errors', $_errors);
	$preproc->assign('importer_state', $importer_state);
	
	$preproc->assign('current_step_name', $importer->getCurrentStep());
	$preproc->assign('step_tpl', $importer->getCurrentStep().'.htm');
	$preproc->assign('next_step_url', $next_step_url);
	$preproc->assign('next_step_name', $importer->getNextStep());
	$preproc->assign('back_url', prepareURLStr(PAGE_PM_IMPORT_TASKS, array('import_step' => $importer->getPrevStep())));
	$preproc->assign('results', $results);
	
	$preproc->assign('task_column_names', $task_column_names);
	$preproc->assign('task_column_labels', $task_column_labels);
	
	$preproc->assign('iconv_encoding_charsets', $iconv_encoding_charsets);
	$preproc->assign('date_formats', $date_formats);
	
	$preproc->display("import_tasks/index.htm");
?>

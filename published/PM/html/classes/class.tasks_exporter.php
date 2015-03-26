<?php

class Tasks_Exporter
{
    function Tasks_Exporter()
    {
        $this->current_step = $this->export_steps[0];
        $this->loadState();
    }

    function saveState()
    {
        $_SESSION[__CLASS__.'_state'] = $this->state;
    }
    
    function loadState()
    {
        if(array_key_exists(__CLASS__.'_state', $_SESSION))
        {
            $this->state = $_SESSION[__CLASS__.'_state'];
        }
        else
        {
            global $pm_importTaskColumnNames;
            $this->state = array(
                'project_id' => null
               ,'task_ids' => array()
               ,'export_from' => 'project'
               ,'file_encoding' => 'utf-8'
               ,'included_fields' => array_keys($pm_importTaskColumnNames)
               ,'excluded_fields' => array()
               ,'csv_delim' => ';'
            );
        };
    }
    
    function getState()
    {
        return $this->state;
    }
    
    function loadCurrentStepFromRequest()
    {
        if(array_key_exists('export_step', $_GET) and in_array($_GET['export_step'], $this->export_steps))
        {
            $this->current_step = $_GET['export_step'];
            return;
        };
        if(array_key_exists('export_step', $_POST) and in_array($_POST['export_step'], $this->export_steps))
        {
            $this->current_step = $_POST['export_step'];
            return;
        };
        if(array_key_exists('project_id', $_GET))
        {
            $this->state['project_id'] = $_GET['project_id'];
        };
        if(array_key_exists('works_ids', $_GET))
        {
            $ids = $_GET['works_ids'];
            $this->state['task_ids'] = ($ids == '' ? array() : explode('|', $ids));
        };
    }
    
    function setDataSource($ds)
    {
        if(is_array($ds))
        {
            $this->_ds = $ds;
        };
    }
    
    function setCurrentStep($step_name)
    {
        if(in_array($step_name, $this->export_steps))
        {
            $this->current_step = $step_name;
        };
    }
    
    function getCurrentStep()
    {
        return $this->current_step;
    }
    
    function getNextStep()
    {
        $step_key = array_search($this->current_step, $this->export_steps);
        if($step_key < (count($this->export_steps) - 1))
        {
            return $this->export_steps[$step_key + 1];
        }
        else
        {
            return null;
        };
    }
    
    function getPrevStep()
    {
        $step_key = array_search($this->current_step, $this->export_steps);
        if($step_key > 0)
        {
            return $this->export_steps[$step_key - 1];
        }
        else
        {
            return null;
        };
    }
    
    function processCurrentStep()
    {
        $this->results = array(
            'errors'   => array()
           ,'messages' => array()
        );
        
        $method_name = "_process_{$this->current_step}";
        if(method_exists(__CLASS__, $method_name))
        {
            $this->$method_name();
        };
    }
    
    function getResults()
    {
        return $this->results;
    }

    function _process_select_source()
    {
        return;
    }
    
    function _process_export_process()
    {
        global $pm_importTaskColumnNames;
        
        $export_from = $this->_get_from_source('export_from');
        $file_encoding = $this->_get_from_source('file_encoding');
        $included_fields = $this->_get_from_source('included_fields');
        $excluded_fields = $this->_get_from_source('excluded_fields');
        $delim = $this->csv_delims[$this->_get_from_source('csv_delimiter')];
        
        $this->state['export_from'] = $export_from;
        $this->state['file_encoding'] = $file_encoding;
        $this->state['included_fields'] = $included_fields;
        $this->state['excluded_fields'] = $excluded_fields;
        $this->state['csv_delim'] = $delim;
        
        $all_columns = array_keys($pm_importTaskColumnNames);
        $tasks = $this->_get_tasks_list();

        $headers_array = array_map(
            create_function('$a', 'global $pm_importTaskColumnNames; return $pm_importTaskColumnNames[$a];')
           ,$included_fields
        );
        
        $headers_array = array_map(array('Lang_Message','render'), $headers_array);
        $headers_string = self::_prepare_csv_string($headers_array, $delim);

        $this->_open_csv_handler();
        $this->_write_string_to_handler($headers_string);
        
        foreach($tasks as $task_info)
        {
            $to_csv = array();
            foreach($included_fields as $column_name)
            {
                if($column_name == PM_TASK_ASSIGN)
                {
                    $uids = pm_getProjectWorkAssignments($task_info[PM_TASK_COLUMN_PROJECT_ID], $task_info[PM_TASK_COLUMN_TASK_ID]);
                    $to_csv[] = implode(self::CUST_DELIM, $uids);
                }
                else
                {
                    $to_csv[] = $task_info[$column_name];
                };
            };
            $this->_write_string_to_handler(self::_prepare_csv_string($to_csv, $delim));
        };
        
        $this->_close_csv_handler();
    }

    function _process_get_file()
    {
        $file_path = $this->state['csv_tmp_path'];
        
        header('Expires: '.gmdate('D, d M Y H:i:s', time()).' GMT');
        header('Cache-Control: public, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: application/csv');
        header('Content-Length: '.(filesize($file_path)));
        header('Content-Disposition: attachment; filename="tasks.csv"');
        header('Content-Transfer-Encoding: binary');
        
        if($fh = fopen($file_path, 'rb'))
            while(!feof($fh))
            {
                echo fread($fh, 4096);
            };
        die();
    }
    
    function _get_tasks_list()
    {
        $sql_where = PM_TASK_COLUMN_PROJECT_ID.'='.$this->state['project_id'];
        if($this->state['export_from'] == 'selected')
        {
            $sql_where .= ' and '.PM_TASK_COLUMN_TASK_ID.' in ('.implode(', ', $this->state['task_ids']).')';
        };
        
        $sql = "select * from ".self::TASKS_TABLE." where {$sql_where} order by ".PM_TASK_COLUMN_TASK_ID." asc";
        $res = db_query($sql);

        $tasks = array();
        while($row = db_fetch_array($res))
        {
            foreach(array(PM_TASK_COLUMN_STARTDATE, PM_TASK_COLUMN_DUEDATE, PM_TASK_COLUMN_ENDDATE) as $d)
            {
                if($row[$d] == '0000-00-00') $row[$d] = '';
            };
            
            $tasks[] = $row;
        };
        
        return $tasks;
    }
    
    function _prepare_csv_string($data, $delimiter)
    {
        $data = array_map(
            create_function('$a','return \'"\'.str_replace(\'"\', \'""\', $a).\'"\';')
           ,$data
        );
        
        return iconv('utf-8', $this->state['file_encoding'].'//IGNORE', implode($delimiter, $data));
    }
    
    function _open_csv_handler()
    {
        $csv_tmp_path = str_replace("\\","/",realpath(WBS_TEMP_DIR)).'/'.uniqid(TMP_FILES_PREFIX);
        $this->state['csv_tmp_path'] = $csv_tmp_path;
        
        $this->csv_handler = fopen($csv_tmp_path, 'wb');
    }
    
    function _write_string_to_handler($string)
    {
        fwrite($this->csv_handler, $string.self::NEWLINE);
    }
    
    function _close_csv_handler()
    {
        fclose($this->csv_handler);
    }
    
    function _get_from_source($var_name)
    {
        return (array_key_exists($var_name, $this->_ds) ? $this->_ds[$var_name] : null);
    }
    
    var $current_step = null;
    var $export_steps = array(
        'select_source'
       ,'export_process'
       ,'get_file'
    );
    var $results = null;
    var $_ds = null;
    var $state = null;
    var $csv_delims = array(";", ",", "\t");
    const TASKS_TABLE = 'PROJECTWORK';
    const CUST_DELIM = ',';
    const NEWLINE = "\n";
    var $csv_handler = null;
};

?>
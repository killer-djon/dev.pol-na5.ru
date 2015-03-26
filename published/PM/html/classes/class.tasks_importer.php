<?php

class Tasks_Importer
{
    function Tasks_Importer()
    {
        $this->current_step = $this->import_steps[0];
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
            $this->state = array(
                'project_id' => null
               ,'columns' => array()
               ,'import_source' => 'plaintext'
               ,'import_source_data' => ''
               ,'delimiter' => null
               ,'columns_to_import' => array()
               ,'import_first_line' => 'off'
               ,'file_encoding' => 'utf-8'
            );
        };
    }
    
    function getState()
    {
        return $this->state;
    }
    
    function loadCurrentStepFromRequest()
    {
        if(array_key_exists('import_step', $_GET) and in_array($_GET['import_step'], $this->import_steps))
        {
            $this->current_step = $_GET['import_step'];
            return;
        };
        if(array_key_exists('import_step', $_POST) and in_array($_POST['import_step'], $this->import_steps))
        {
            $this->current_step = $_POST['import_step'];
            return;
        };
        if(array_key_exists('project_id', $_GET))
        {
            $this->state['project_id'] = $_GET['project_id'];
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
        if(in_array($step_name, $this->import_steps))
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
        $step_key = array_search($this->current_step, $this->import_steps);
        if($step_key < (count($this->import_steps) - 1))
        {
            return $this->import_steps[$step_key + 1];
        }
        else
        {
            return null;
        };
    }
    
    function getPrevStep()
    {
        $step_key = array_search($this->current_step, $this->import_steps);
        if($step_key > 0)
        {
            return $this->import_steps[$step_key - 1];
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
    
    function _process_import_prepare()
    {
        $import_source = $this->_get_from_source('import_source');

        $this->state['import_source'] = $import_source;
        $this->state['import_source_data'] = $this->_get_from_source('import_source_'.$import_source);
        
        if(is_array($this->state['import_source_data']))
        {
            $this->state['import_source_data'] = array_map('stripslashes', $this->state['import_source_data']);
        }
        else
        {
            $this->state['import_source_data'] = stripslashes($this->state['import_source_data']);
        };
        
        if($this->state['import_source'] == 'file')
        {
            $this->state['file_encoding'] = $this->_get_from_source('file_encoding');
            file_convert_encoding($this->state['file_encoding'], 'utf-8', $this->state['import_source_data']['path']);
        };
        
        if($this->state['import_source'] == 'plaintext')
        {
            $_d = intval($this->_get_from_source('text_csv_delimiter'));
            $this->state['text_csv_delimiter'] = ($_d > 0 ? $this->csv_delims[$_d-1] : 'auto');
        }
        else
        {
            $this->state['text_csv_delimiter'] = 'auto';
        };
        
        $first_line = null;
        $method_name = "_extract_first_line_from_{$import_source}";

        if(method_exists(__CLASS__, $method_name))
        {
            $first_line = $this->$method_name();
        };

        if($first_line == null)
        {
            $this->results['errors'][] = 'invalid_first_line';
            return;
        };
        
        $delimiter = ($this->state['text_csv_delimiter'] == 'auto' ? self::_detect_csv_delimiter($first_line) : $this->state['text_csv_delimiter']);
        $columns = self::_csv_extract($first_line, $delimiter);
        
        $this->state = array_merge($this->state, array(
            'columns' => $columns
           ,'delimiter' => $delimiter
           ,'columns_to_import' => array()
        ));
        
        foreach($columns as $index => $value)
        {
            $this->state['columns_to_import'][$index] = 'on';
        };
        
        return;
    }
    
    function _process_import_process()
    {
        $columns_to_import = $this->_get_from_source('columns_to_import');
        $this->state['import_first_line'] = ($this->_get_from_source('import_first_line') != null ? 'on' : 'off');
        
        foreach($this->state['columns_to_import'] as $index => $val)
        {
            $this->state['columns_to_import'][$index] = (array_key_exists($index, $columns_to_import) ? 'on' : 'off');
        };
        
        $column_map = $this->_get_from_source('column_map');
        $this->state['column_map'] = $column_map;

        foreach($column_map as $index => $val)
        {
            if($val == "" and array_key_exists($index, $columns_to_import))
            {
                $this->results['errors'][] = 'column_not_defined';
                return;
            };
        };
        
        $this->state['format_input'] = $this->_get_from_source('format_input');
        
        $counter = array('success' => 0, 'fail' => 0);

        $statusList = array(RS_ACTIVE, RS_LOCKED);
        global $kernelStrings;
        $systemUsers = listSystemUsers($statusList, $kernelStrings);
        if(PEAR::isError($systemUsers))
        {
             $this->results['errors'] = $systemUsers->getMessage();
             return;
        };
        
        $this->_user_list = array_map('strtolower',array_keys($systemUsers));
        
        $this->_prepare_to_iterate();
        
        while(($data = $this->_get_next_data()) !== false)
        {
            $this->_current_line++;
            
            if($data === null) // empty string
            {
                $this->results['messages'][] = array('err_empty_line', $this->_current_line);
                $counter['fail']++;
                continue;
            };

            if(count($data) != count($this->state['columns'])) // incorrect columns count
            {
                $this->results['messages'][] = array('err_invalid_columns_count', $this->_current_line);
                $counter['fail']++;
                continue;
            };
            
            if($this->_put_to_db($data))
            {
                $counter['success']++;
            }
            else
            {
                $counter['fail']++;
            };
        }

        $this->state['import_source_data'] = '';
        
        if($counter['fail'] > 0)
        {
            array_unshift($this->results['messages'], array('fails_processed', $counter['fail']));
        };
        
        array_unshift($this->results['messages'], array('success_processed', $counter['success']));
    }
    
    function _extract_first_line_from_plaintext()
    {
        $lines = preg_split("/(\n)|(\r\n)/", $this->state['import_source_data']);
        return (count($lines) > 0 ? $lines[0] : null);
    }
    
    function _extract_first_line_from_file()
    {
        $file_info = $this->state['import_source_data'];
        $this->csv_handler = fopen($file_info['path'], 'rb');
        $first_line = fgetcsv($this->csv_handler, 4096, "\x0B");
        return ($first_line !== false ? $first_line[0] : null);
    }
    
    function _extract_all_lines_from_plaintext()
    {
        $lines = preg_split("/(\n)|(\r\n)/", $this->state['import_source_data']);
        return $lines;
    }
    
    function _extract_all_lines_from_file()
    {
        $file_info = $this->state['import_source_data'];
        $lines = file($file_info['path']);
        return $lines;
    }
    
    function _prepare_to_iterate()
    {
        switch($this->state['import_source'])
        {
            case 'plaintext':
                $this->data_lines = $this->_extract_all_lines_from_plaintext();
                if($this->state['import_first_line'] == 'off')
                {
                    array_shift($this->data_lines);
                };
                reset($this->data_lines);
                break;
            case 'file':
                $this->csv_handler = fopen($this->state['import_source_data']['path'], 'rb');
                if($this->state['import_first_line'] == 'off')
                {
                    fgetcsv($this->csv_handler, 4096, $this->state['delimiter']);
                };
                break;
        };
    }
    
    function _get_next_data()
    {
        switch($this->state['import_source'])
        {
            case 'plaintext':
                $line = current($this->data_lines);
                next($this->data_lines);
                if($line === false) return false;
                if($line === '') return null;
                return self::_csv_extract($line, $this->state['delimiter']);
                break;
            case 'file':
                $data = fgetcsv($this->csv_handler, 4096, $this->state['delimiter']);
                if($data === false) return false;
                if($data === array('')) return null;
                return $data;
                break;
        };
    }
    
    function _put_to_db($task_data)
    {
        $data_to_insert = array();
        foreach($this->state['columns_to_import'] as $index => $state)
        {
            if($state == 'on')
            {
                $field_name = $this->state['column_map'][$index];
                $field_value = trim($task_data[$index]);
                $data_to_insert[$field_name] = $field_value;
            };
        };
        
        $data_to_insert[PM_TASK_COLUMN_PROJECT_ID] = $this->state['project_id'];
        $sql = "select max(".PM_TASK_COLUMN_TASK_ID.") as max_id from ".self::TARGET_TABLE." where ".PM_TASK_COLUMN_PROJECT_ID."=".$this->state['project_id'];
        $max_id = db_query_result( $sql, DB_FIRST);
        $data_to_insert[PM_TASK_COLUMN_TASK_ID] = ++$max_id;

        if(!$this->_check_data($data_to_insert))
        {
            return false;
        };

        $uids = array();
        if(array_key_exists(PM_TASK_ASSIGN, $data_to_insert))
        {
            $uids = array_map('strtoupper', $data_to_insert[PM_TASK_ASSIGN]);
            unset($data_to_insert[PM_TASK_ASSIGN]);
        };
        
        $sql = "insert into ".self::TARGET_TABLE." (".implode(', ', array_keys($data_to_insert)).") ".
        	   "values ('".implode("', '", array_map('addslashes', $data_to_insert))."')";

        if(PEAR::isError(db_query($sql)))
        {
            $this->results['messages'][] = array('err_unknown', $this->_current_line);
            return false;
        };
        
        if(!empty($uids))
        {
            $ilines = array();
            foreach($uids as $uid)
            { 
            	$ilines[] = "({$data_to_insert[PM_TASK_COLUMN_PROJECT_ID]}, {$data_to_insert[PM_TASK_COLUMN_TASK_ID]}, '{$uid}')";
            };
            $sql = "insert into ".self::ASSIGN_TABLE." (P_ID, PW_ID, U_ID) values ".implode(', ', $ilines);
            
            if(PEAR::isError(db_query($sql)))
            {
                $this->results['messages'][] = array('err_unknown', $this->_current_line);
                return false;
            };
        };
        
        return true;
    }
    
    function _check_data(&$data)
    {
        $unsetKeys = array ();
        foreach($data as $attr_code => $attr_value)
        {
            switch($attr_code)
            {
                case PM_TASK_COLUMN_BILLABLE:
                    $true_rx = "/^(1|yes|true|y)$/i";
                    $attr_value = (preg_match($true_rx, $attr_value) ? '1' : '0');
                    break;
                case PM_TASK_COLUMN_COSTESTIMATE:
                    $attr_value = sprintf("%.2f",floatval(trim($attr_value)));
                    break;
                case PM_TASK_COLUMN_COSTCUR:
                    $currencies = pm_getCurrencies();
                    if(!in_array($attr_value, $currencies))
                    {
                        $attr_value = $currencies[0];
                    };
                    break;
                case PM_TASK_COLUMN_STARTDATE:
                case PM_TASK_COLUMN_DUEDATE:
                case PM_TASK_COLUMN_ENDDATE:
                    if($attr_value == '' or $attr_value == '0000-00-00')
                    {
                        $unsetKeys[] = $attr_code;
                        $attr_value = '';
                        break;
                    };
                    
                    $column_index = array_search($attr_code, $this->state['column_map']);
                    $format_str = $this->state['format_input'][$column_index];
                    $format_rx = '/'.str_replace(
                        array('DD','MM','YYYY','.','/','-')
                       ,array('\d{2}','\d{2}','\d{4}','\.','\/','\-')
                       ,$format_str
                    ).'/';

                    if(!preg_match($format_rx, $attr_value))
                    {
                        $this->results['messages'][] = array('err_incorrect_date_format', $this->_current_line);
                        return false;
                    };
                    
                    $date_parts = array('DD'=>0, 'MM'=>0, 'YYYY'=>0);
                    foreach($date_parts as $code => $val)
                    {
                        $date_parts[$code] = substr($attr_value, strpos($format_str, $code), strlen($code));
                    };

                    if(!checkdate($date_parts['MM'], $date_parts['DD'], $date_parts['YYYY']))
                    {
                        $this->results['messages'][] = array('err_not_exists_date', $this->_current_line);
                        return false;
                    };
                    $attr_value = $date_parts['YYYY'].'-'.$date_parts['MM'].'-'.$date_parts['DD'];
                    break;
                case PM_TASK_ASSIGN:
                    $uids = array_unique(array_map('strtolower', array_map('trim', explode(self::CUST_DELIM, $attr_value))));
                    $uids = array_intersect($uids, $this->_user_list);
                    $attr_value = $uids;
                    break;
                default: break;
            };
            
            $data[$attr_code] = $attr_value;
        };
        
        foreach ($unsetKeys as $key) {
        	if (isset($data[$key])) 
        		unset($data[$key]);
        }
        
        // check dates
        
        // 1. due after start
        if(array_key_exists(PM_TASK_COLUMN_STARTDATE, $data) 
            and array_key_exists(PM_TASK_COLUMN_DUEDATE, $data)
            and $data[PM_TASK_COLUMN_DUEDATE] < $data[PM_TASK_COLUMN_STARTDATE]
            and count(array_filter(array($data[PM_TASK_COLUMN_DUEDATE], $data[PM_TASK_COLUMN_STARTDATE]))) == 2)
        {
            $this->results['messages'][] = array('err_start_date_later_due_date', $this->_current_line);
            return false;
        };

        // 2. end after start
        if(array_key_exists(PM_TASK_COLUMN_STARTDATE, $data) 
            and array_key_exists(PM_TASK_COLUMN_ENDDATE, $data)
            and $data[PM_TASK_COLUMN_ENDDATE] < $data[PM_TASK_COLUMN_STARTDATE]
            and count(array_filter(array($data[PM_TASK_COLUMN_ENDDATE], $data[PM_TASK_COLUMN_STARTDATE]))) == 2)
        {
            $this->results['messages'][] = array('err_start_date_later_cmpl_date', $this->_current_line);
            return false;
        };
        
        // 3. (end or due) with start
/*        if(!array_key_exists(PM_TASK_COLUMN_STARTDATE, $data)
            and (array_key_exists(PM_TASK_COLUMN_DUEDATE, $data) or array_key_exists(PM_TASK_COLUMN_ENDDATE, $data) ))
        {
            return false;
        };
*/
        
        // complete and today
        if(array_key_exists(PM_TASK_COLUMN_ENDDATE, $data)
            and $data[PM_TASK_COLUMN_ENDDATE] != ''
            and $data[PM_TASK_COLUMN_ENDDATE] > str_replace(
                        array('YYYY', 'MM', 'DD'), array(date('Y'), date('m'), date('d'))
                       ,'YYYY-MM-DD'
                    ))
        {
            $this->results['messages'][] = array('err_complete_date_later_today', $this->_current_line);
            return false;
        };
        
        // check cost
        
        // 1. cost with currency
        if(array_key_exists(PM_TASK_COLUMN_COSTESTIMATE, $data) and !array_key_exists(PM_TASK_COLUMN_COSTCUR, $data))
        {
            $currencies = pm_getCurrencies();
            $data[PM_TASK_COLUMN_COSTCUR] = $currencies[0];
        };
        
        // 2. currency with cost
        if(!array_key_exists(PM_TASK_COLUMN_COSTESTIMATE, $data) and array_key_exists(PM_TASK_COLUMN_COSTCUR, $data))
        {
            $data[PM_TASK_COLUMN_COSTESTIMATE] = 0.00;
        };
        
        return true;
    }
    
    function _detect_csv_delimiter($line)
    {
        $last = array('count' => 0, 'delimiter' => null);
        foreach($this->csv_delims as $d)
        {
            $fields = self::_csv_extract($line, $d);
            if((count($fields) > $last['count']))
            {
                $last['count'] = count($fields);
                $last['delimiter'] = $d;
            };
        };
        
        return $last['delimiter'];
    }
    
    function _csv_extract($line, $delimiter)
    {
        $parts = explode($delimiter, $line);
        $split_flag = false;
        for($k=0; $k < count($parts); $k++)
        {
            $part = $parts[$k];
            if($split_flag)
            {
                $parts[$k-1] .= $delimiter . $part;
                if(substr($part, -1) == '"')
                {
                    $split_flag = false;
                };
                array_splice($parts, $k, 1);
                $k--;
                continue;
            };
            if($part[0] == '"' and substr($part, -1) != '"')
            {
                $split_flag = true;
            };
        };
        foreach($parts as $k => $part)
        {
            if($part[0] == '"')
            {
                $part = str_replace('""', '"', substr($part, 1, -1));
                $parts[$k] = $part;
            };
        };
        
        return $parts;
    }
    
    function _get_from_source($var_name)
    {
        return (array_key_exists($var_name, $this->_ds) ? $this->_ds[$var_name] : null);
    }
    
    var $current_step = null;
    var $import_steps = array(
        'select_source'
       ,'import_prepare'
       ,'import_process'
    );
    var $results = null;
    var $_ds = null;
    var $state = null;
    const TARGET_TABLE = 'PROJECTWORK';
    const ASSIGN_TABLE = 'WORKASSIGNMENT';
    const CUST_DELIM = ',';
    var $csv_delims = array(";", ",", "\t");
    var $csv_handler = null;
    var $data_lines = array();
    var $_user_list = array();
    var $_current_line = 0;
};

?>
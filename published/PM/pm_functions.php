<?php

	//
	// Project Management non-DMBS application functions
	//

	function dateCmp( $date1, $date2 )
	//
	// Compares dates
	//
	//		Parameters:
	//			$date1 - first date in DISPLAY_DATE_FORMAT format
	//			$date2 - second date in DISPLAY_DATE_FORMAT format
	//
	//		Returns 1, if first date is later that the second
	//				0, if they are equal
	//			   -1, otherwise
	//
	{
		validateInputDate( $date1, $timestamp1 );
		validateInputDate( $date2, $timestamp2 );

		if( $timestamp1 > $timestamp2 ) return 1;
		if( $timestamp1 == $timestamp2 ) return 0;
		return -1;
	}
	
	function dateCmpNT( $date1, $date2 )
	//
	// Compares dates
	//
	//		Parameters:
	//			$date1 - first date in DISPLAY_DATE_FORMAT format
	//			$date2 - second date in DISPLAY_DATE_FORMAT format
	//
	//		Returns 1, if first date is later that the second
	//				0, if they are equal
	//			   -1, otherwise
	//
	{
		validateInputDateNT( $date1, $timestamp1 );
		validateInputDateNT( $date2, $timestamp2 );
		
		if( $timestamp1 > $timestamp2 ) return 1;
		if( $timestamp1 == $timestamp2 ) return 0;
		return -1;
	}

	function pm_compareWithCurDate( $str )
	//
	// Compares incoming date with the current
	//
	//		Parameters:
	//			$str - date for compare with the current date
	//
	//		Returns RS_ACTIVE, if transfered date is later then the current date or it does not exist; otherwise returns RS_DELETED
	//
	{
		return ( dateCmp($str, displayDate(time())) > 0 || !strlen($str) ) ?
			RS_ACTIVE : RS_DELETED;
	}

	function pm_initProject()
	//
	// Initializes new project with initial data
	//
	//		Returns an array containing initial data
	//
	{
		$projectData = array();

		$projectData["P_STARTDATE"] = displayDate( convertTimestamp2Local( time() ) );
		$projectData["P_STATUS"] = RS_ACTIVE;

		return $projectData;
	}

	function pm_initWork( $P_ID, $pmStrings )
	//
	// Initializes new work with initial data
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$pmStrings - an array containing strings stored within pm_localization.php in specific language
	//
	//		Returns an array containing initial data, or PEAR_Error
	//
	{
		global $qr_pm_select_project;

		$workData = array();

		$workData["P_ID"] = $P_ID;

		$workData["PW_STARTDATE"] =  displayDate( convertTimestamp2Local( time() ) );
		$workData["PW_STATUS"] = RS_ACTIVE;

		$res = exec_sql( $qr_pm_select_project, $workData, $projectData, true );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD]), PM_ERRCODE_DATAHANDLE );

		$workData["P_DESC"] = strTruncate( $projectData["C_NAME"], PM_CUST_NAME_LEN ) . ". " . $projectData["P_DESC"];

		return $workData;
	}

	function pm_sortProjectWorksArray( $sort_array, &$sorting )
	//
	// Assorts an array containing project work (it is used if the sorting with the means of query can not be implemented)
	//
	//		Parameters:
	//			$sort_array - an array to be sorted
	//			$sorting - contains field, which is used as sorting order
	//
	//		Returns assorted array
	//
	{
		if ( !is_array($sort_array) || !count($sort_array) )
			return $sort_array;

		$position = strpos( $sorting, "asc" );
		if ( !$position )
			$position = strpos( $sorting, "desc" );

		$sort_type = substr( $sorting, $position );
		$sorting = substr( $sorting, 0, $position-1 );

		$keys = array_keys( $sort_array[0] );
		if ( !in_array($sorting, $keys) ) {
			$sorting = null;
			return $sort_array;
		}

		$contain = 0;

		$array = array();

		foreach( $sort_array as $fieldNumber => $fieldValue ) {
			if ( $fieldValue[$sorting] > $contain )
				$contain = $fieldValue[$sorting];
		}

		if ( !strcmp($sort_type, "desc") ) {
			for( $i=$contain; $i>=0; $i-- )
				foreach( $sort_array as $fieldNumber => $fieldValue ) {
					if ( $fieldValue[$sorting] == $i )
						$array[count($array)] = $fieldValue;
				}
		}
		else {
			for ( $i=0; $i<=$contain; $i++ )
				foreach( $sort_array as $fieldNumber => $fieldValue ) {
					if ( $fieldValue[$sorting] == $i )
						$array[count($array)] = $fieldValue;
				}
		}

		$sorting = implode( " ", array($sorting, $sort_type) );

		return $array;
	}

	function pm_makeConfirmationString( $works_count, $language, $pmStrings )
	//
	//	Makes string with the regard for morfology. The string is used during the confirmation of project deleting
	//
	//		Parameters:
	//			$works_count - number of works
	//			$language - user language
	//			$pmStrings - an array containing strings stored within pm_localization.php in specific language
	//
	//		Returns string
	//
	{
		$confirmation_string = sprintf( $pmStrings['amp_projdeltasks_message'], $works_count );

		return $confirmation_string;
	}

	function pm_makeGanttDateDelimiter()
	//
	//	Makes date delimiter
	//
	//		Parameters:
	//			none
	//
	//		Returns delimiter
	//
	{
		global $dateFormats;
		global $dateDelimiters;

		$current_delimiter = explode( $dateDelimiters[DATE_DISPLAY_FORMAT], $dateFormats[DATE_DISPLAY_FORMAT] );

		$prepared_delimiter = array();

		foreach( $current_delimiter as $index => $index_value )
			if ( strtolower($index_value[0]) != "y" )
				$prepared_delimiter[count($prepared_delimiter)] = strtolower($index_value[0]);

		$gantt_delimiter = implode( $dateDelimiters[DATE_DISPLAY_FORMAT], $prepared_delimiter );

		return $gantt_delimiter;
	}

	function pm_calculateDuration( $start_date, $end_date, $convert = true, $month_days )
	//
	//	Calculates duration between the start and the end date in days
	//
	//		Parameters:
	//			$start_date - start date
	//			$end_date - end date
	//
	//		Returns duration
	//
	{
		if ( $convert )
			$start_date = convertToDisplayDate( $start_date );

		if ( strlen( $end_date ) )  {
			if ( $convert )
				$end_date = convertToDisplayDate( $end_date );
			$enddate = pm_makeDateArray( $end_date );
		}
		else if ( dateCmp( $start_date, displayDate( time() ) ) < 0 )
			$enddate = pm_makeDateArray( displayDate( time() ) );
		else
			return 1;

		$startdate = pm_makeDateArray( $start_date );

		$end_days = 0;
		$start_days = 0;

		for ( $start_year = $startdate["y"]; $start_year<= $enddate["y"] - 1; $start_year++ )
			$end_days += array_sum($month_days[$start_year]);

		for ( $month = 1; $month <= $enddate["m"]-1; $month++ ) {
			if ( array_key_exists($enddate["y"], $month_days) )
				$end_days += $month_days[$enddate["y"]][$month];
		}

		for ( $month = 1; $month <= $startdate["m"]-1; $month++ )
			$start_days +=$month_days[$startdate["y"]][$month];

		$end_days += $enddate["d"];
		$start_days += $startdate["d"];

		$duration = $end_days - $start_days + 1;

		return $duration;
	}

	function pm_makeMonthDaysArray( $start_date, $end_date )
	//
	//	Makes an array containing number of months' days for years, starting with year of $start_date and finishing with the year of $end_date
	//
	//		Parameters:
	//			$start_date - start date
	//			$end_date - end date
	//
	//		Returns an array containing number of months' days
	//
	{
		$month_days_array = array();

		for( $year_index = $start_date["y"]; $year_index <= $end_date["y"]; $year_index++ ) {
			$year_months = array();

			for ( $month_index = 1; $month_index <= 12; $month_index++ ) {
				validateInputDate( pm_makeDate(array("d"=>1, "m"=>$month_index, "y"=>$year_index)), $timestamp );

				$year_months[$month_index] = date("t", $timestamp);
			}

			$month_days_array[$year_index] = $year_months;
		}

		return $month_days_array;
	}

	function pm_refineStartDate( $start_date, $min_date, $max_date, $month_days )
	//
	//	Refines start date
	//
	//		Parameters:
	//			$start_date - date made by function pm_findStartDate()
	//			$min_date - minimum date of project works' dates
	//			$max_date - maximum date of project works' dates
	//			$month_days - an array (mady be pm_makeMonthDaysArray()) containing number of months' days
	//
	//		Returns refined start date
	//
	{
		$duration = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($max_date), false, $month_days ) - 1;

		if ( $duration <= 70 )
			return $start_date;
		else {
			while ( $min_date["d"] - 1 > 0 )
				$min_date["d"]--;

			return $min_date;
		}
	}

	function pm_refineEndDate( $end_date, $min_date, $max_date, $month_days )
	//
	//	Refines end date
	//
	//		Parameters:
	//			$start_date - date made by function pm_findEndDate()
	//			$min_date - minimum date of project works' dates
	//			$max_date - maximum date of project works' dates
	//			$month_days - an array (mady be pm_makeMonthDaysArray()) containing number of months' days
	//
	//		Returns refined end date
	//
	{
		$duration = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($max_date), false, $month_days ) - 1;

		if ( $duration <= 70 )
			return $end_date;
		else {
			while ( $max_date["d"] != $month_days[$max_date["y"]][$max_date["m"]] )
				$max_date["d"]++;

			return $max_date;
		}
	}

	function pm_findStartDate( $date )
	//
	//	Finds start date
	//
	//		Parameters:
	//			$date - minimum date of project works' dates
	//
	//		Returns start date
	//
	{
		global $pm_month_days;

		if ( !isset($date) || !strlen($date) )
			return null;

		validateInputDate( $date, $timestamp );

		if  ( date("D", $timestamp) == "Sun" )
			return $date;
		else {
			$date_array = pm_makeDateArray( $date );

			while ( $date_array["d"]-1 > 0 ) {
				$date_array["d"]--;

				$date = pm_makeDate( $date_array );
				validateInputDate( $date, $timestamp );

				if ( date("D", $timestamp) == "Sun" )
					return $date;
			}

			if ( $date_array["m"] == 1 ) {
				$date_array["d"] = 31;
				$date_array["m"] = 12;
				$date_array["y"] -= 1;

				while ( true ) {
					$date_array["d"]--;

					$date = pm_makeDate( $date_array );
					validateInputDate( $date, $timestamp );

					if ( date("D", $timestamp) == "Sun" )
						return $date;
				}
			}
			else {
				$date_array["m"] -= 1;
				$date_array["d"] = $pm_month_days[$date_array["m"]];

				$date = pm_makeDate($date_array);
				validateInputDate($date, $timestamp);

				$date_array["d"] = date( "t", $timestamp );

				while ( true ) {
					$date = pm_makeDate( $date_array );
					validateInputDate( $date, $timestamp );

					if ( date("D", $timestamp) == "Sun" )
						return $date;

					$date_array["d"]--;
				}
			}
		}
	}

	function pm_findEndDate( $date )
	//
	//	Finds end date
	//
	//		Parameters:
	//			$date - maximum date of project works' dates
	//
	//		Returns end date
	//
	{
		global $pm_month_days;

		if ( !isset($date) || !strlen($date) )
			return null;

		validateInputDate( $date, $timestamp );

		if  ( date("D", $timestamp) == "Sun" )
			return $date;
		else {
			$date_array = pm_makeDateArray( $date );

			$month_days = date( "t", $timestamp );

			while ( $date_array["d"] <= $month_days ) {
				$date_array["d"]++;

				$date = pm_makeDate( $date_array );
				validateInputDate( $date, $timestamp );

				if ( date("D", $timestamp) == "Sun" )
					return $date;
			}

			if ( $date_array["m"] == 12 ) {
				$date_array["d"] = 1;
				$date_array["m"] = 1;
				$date_array["y"] += 1;

				while ( true ) {
					$date_array["d"]++;

					$date = pm_makeDate( $date_array );
					validateInputDate( $date, $timestamp );

					if ( date("D", $timestamp) == "Sun" )
						return $date;
				}
			}
			else {
				$date_array["m"] += 1;
				$date_array["d"] = 1;

				while ( true ) {
					$date = pm_makeDate( $date_array );
					validateInputDate( $date, $timestamp );

					if ( date("D", $timestamp) == "Sun" )
						return $date;

					$date_array["d"]++;
				}

			}
		}
	}

	function pm_makeFinalMonthDaysArray( $min_date, $max_date )
	//
	//	Makes an array containing number of months' days for months, starting with year and month of $min_date and finishing with year and month of $max_date
	//
	//		Parameters:
	//			$min_date - minimum date
	//			$max_date - maximum date
	//
	//		Returns an array containing number of months' days
	//
	{
		$final_month_days_array = array();

		if ( $min_date["y"] == $max_date["y"] ) {
			$year_months = array();
			for ( $month_index = $min_date["m"]; $month_index <= $max_date["m"]; $month_index++ ) {
				validateInputDate( pm_makeDate(array("d"=>1, "m"=>$month_index, "y"=>$min_date["y"])), $timestamp );

				$year_months[$month_index] = date( "t", $timestamp );
			}
			$final_month_days_array[$min_date["y"]] = $year_months;
		}
		else {
			for ( $year_index = $min_date["y"]; $year_index <= $max_date["y"]; $year_index++ ) {
				$year_months = array();

				if ( $year_index == $min_date["y"] ) {
					$start_month_index = $min_date["m"];
					$end_month_index = 12;
				}
				else if ( $year_index == $max_date["y"] ) {
					$start_month_index = 1;
					$end_month_index = $max_date["m"];
				}
				else {
					$start_month_index = 1;
					$end_month_index = 12;
				}

				for ( $month_index = $start_month_index; $month_index <= $end_month_index; $month_index++ ) {
					validateInputDate( pm_makeDate(array("d"=>1, "m"=>$month_index, "y"=>$year_index)), $timestamp );

					$year_months[$month_index] = date( "t", $timestamp );
				}
				$final_month_days_array[$year_index] = $year_months;
			}
		}

		return $final_month_days_array;
	}

	function pm_reverseArray( $array )
	//
	//	Reverses an array - array keys become values, and array values become keys
	//
	//		Parameters:
	//			$array - an array to be reversed
	//
	//		Returns reversed array
	//
	{
		$result_array = array();

		foreach( $array as $fieldName => $fieldValue )
			$result_array[$fieldValue] = $fieldName;

		return $result_array;
	}

	function pm_nullArray( $array )
	//
	//	Checks if an array containg only null values
	//
	//		Parameters:
	//			$array - an array to be checked
	//
	//		Returns true, if array contains only null values, otherwise returns false
	//
	{
		if ( !is_array($array) )
			return false;

		for ( $i = 0; $i <= count($array) - 1; $i++ )
			if ( $array[$i] )
				return false;

		return true;
	}

	function pm_shortenArray( $min_date, $max_date, $date_ruler, $month_days )
	//
	//	Deletes secondary data from the array $date_ruler
	//
	//		Parameters:
	//			$min_date - minimum date
	//			$max_date - maximum date
	//			$date_ruler - date ruler
	//			$month_days - an array(made by pm_makeMonthDaysArray()) containing number of months' days
	//
	//		Returns modified date ruler
	//
	{
		$duration = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($max_date), false, $month_days ) - 1;

		if ( $duration <= 70 ) {
			for ( $i=0; $i<=count($date_ruler) - 1; $i++ ) {
				if ( $date_ruler[$i][PM_VALUEFIELD][0] != PM_DAYSURVIVE )
					$date_ruler[$i][PM_VALUEFIELD] = 0;
				else {
					if ( strlen($date_ruler[$i][PM_VALUEFIELD]) == 3 )
						$date_ruler[$i][PM_VALUEFIELD] = 10 * $date_ruler[$i][PM_VALUEFIELD][1] + $date_ruler[$i][PM_VALUEFIELD][2];
					else
						$date_ruler[$i][PM_VALUEFIELD] = $date_ruler[$i][PM_VALUEFIELD][1];
				}
			}
		}

		return $date_ruler;
	}

	function pm_drawCurrentDateLine( $img, $gantt_colors, $min_date, $max_date, $width, $month_days )
	//
	//	Draws information about current date on the image
	//
	//		Parameters:
	//			$img - image identifier
	//			$gantt_colors - colors palette
	//			$min_date - minimum date
	//			$max_date - maximum date
	//			$width - chart width
	//			$month_days - an array (mady be pm_makeMonthDaysArray()) containing number of months' days
	//
	//		Returns null
	//
	{
		global $pm_month_days;

		$currentDate = displayDate( time() );

		$curr_days = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($currentDate), false, $month_days );

		$duration = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($max_date), false, $month_days );

		$coord_x = ( $curr_days - 1 )*$width/($duration-1) - 1;

		if ( $coord_x == $width )
			$coord_x -= 1;

		imageline( $img, $coord_x, 0, $coord_x, 2*PM_TIME_BAR_HEIGHT+2, $gantt_colors["DATECOLOR"] );

		return null;
	}

	function pm_drawCurrentDateInfo( $min_date, $max_date, $width, $month_days )
	//
	//	Draws an image with the information about current date
	//
	//		Parameters:
	//			$min_date - minimum date
	//			$max_date - maximum date
	//			$width - chart width
	//			$month_days - an array (made by pm_makeMonthDaysArray()) containing number of months' days
	//
	//		Returns null
	//
	{
		$currentDate = displayDate( time() );

		$curr_days = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($currentDate), false, $month_days );

		$duration = pm_calculateDuration( pm_makeDate($min_date), pm_makeDate($max_date), false, $month_days );

		if ( abs($width - PM_PRINTERGANTTWIDTH) > abs($width - PM_SMALLGANTTWIDTH) ) {
			$main_width = $width - 60;
			$font = 2;
		}
		else {
			$main_width = $width - 30;
			$font = 1;
		}

		$coord_x = ( $curr_days -1 )*$main_width/($duration-1) - 1;

		if ( $coord_x == $width )
			$coord_x -= 1;

		if ( $coord_x > $main_width - 60 )
			$str_coord = $coord_x - 70;
		else
			$str_coord = $coord_x + 7;

		header( "Content-type: image/jpeg" );

		$img = imagecreate( $width, 15 );

		$gantt_colors = pm_prepareDiagramColors( $img );

		imagefill( $img, 0, 0, $gantt_colors["TRANSPARENT"] );

		if ( dateCmp($currentDate, pm_makeDate($max_date)) <= 0 && dateCmp($currentDate, pm_makeDate($min_date)) >= 0 )
			imagefilledpolygon( $img, array( $coord_x, 0, $coord_x+4, 15, $coord_x-4, 15 ), 3, $gantt_colors["DATECOLOR"] );

		imagestring($img, $font, $str_coord, 4, $currentDate, $gantt_colors["DATACOLOR"]);

		imagejpeg( $img );

		return null;
	}

	function pm_makeNiceDate( $date, $kernelStrings, $supressDay = false )
	//
	// Returns date in "November'01 2004" format
	//
	//		Parameters:
	//			$date - date as timestamp
	//			$kernelStrings - Kernel localization strings
	//			$supressDay - do not include day part in result
	//
	//
	{
		global $monthShortNames;

		$date = convertTimestamp2Local( $date );

		$month = $monthShortNames[date( "n", $date ) - 1];

		if ( !$supressDay )
			return sprintf( "%s'%s", $kernelStrings[$month], date( "d Y", $date ) );
		else
			return sprintf( "%s'%s", $kernelStrings[$month], date( "Y", $date ) );
	}

    function file_convert_encoding($from, $to, $file)
    {
    	$fp_source = fopen($file ,'rb');
    	$fp_target = fopen($file.'_iconv' , 'wb');
    
    	while ($Text = fread($fp_source, 4096))
    	{
    		fwrite($fp_target, iconv($from, $to.'//IGNORE', $Text));
    	}
    	fclose($fp_source);
    	fclose($fp_target);
    	unlink($file);
    	copy($file.'_iconv', $file);
    	unlink($file.'_iconv');
    }
	
	
?>
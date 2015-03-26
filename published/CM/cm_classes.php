<?php

	//
	// Contact Manager common classes
	//

	// Report classes
	//
	class cm_unsubscribersReportDataRangeValidator extends arrayAdaptedClass
	//
	// Validates date range input data for the unsubscribers report
	//
	{
		var $from;
		var $to;
		var $dateMode;
		var $sortMode;

		function cm_unsubscribersReportDataRangeValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'from', t_date, false );
			$this->dataDescrition->addFieldDescription( 'to', t_date, false );
		}

		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			extract($params);

			if ( (($fieldName == 'from' || $fieldName == 'to') && $array['dateMode'] == '1') ) {
				if ( !strlen($fieldValue) )
					return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
												SOAPROBOT_ERR_EMPTYFIELD,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$fieldName );
			}
		}

		function onAfterSet( $array, $params = null )
		//
		// onAfterSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
			extract( $params );

			$timestamp = null;

			if ( strlen($this->from) ) {
				validateInputDate( $this->from, $timestamp );
				$this->from = convertToSQLDate($timestamp);
			}

			if ( strlen($this->to) ) {
				validateInputDate( $this->to, $timestamp );
				$this->to = convertToSQLDate($timestamp);
			}
		}
	}

	class cm_signupsReportDataRangeValidator extends arrayAdaptedClass
	//
	// Validates date range input data for the singup statistics report
	//
	{
		var $days;
		var $from;
		var $to;
		var $type;

		function cm_signupsReportDataRangeValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'days', t_integer, false );
			$this->dataDescrition->addFieldDescription( 'from', t_date, false );
			$this->dataDescrition->addFieldDescription( 'to', t_date, false );
		}

		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			extract($params);

			if ( (($fieldName == 'from' || $fieldName == 'to') && $array['type'] == 'range') ||
					($fieldName == 'days' && $array['type'] == 'days')) {
				if ( !strlen($fieldValue) )
					return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
												SOAPROBOT_ERR_EMPTYFIELD,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$fieldName );
			}
		}

		function onAfterSet( $array, $params = null )
		//
		// onAfterSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
			extract( $params );

			$timestamp = null;

			if ( strlen($this->from) ) {
				validateInputDate( $this->from, $timestamp );
				$this->from = convertToSQLDateTime($timestamp, true);
			}

			if ( strlen($this->to) ) {
				validateInputDate( $this->to, $timestamp );
				$this->to = convertToSQLDateTime($timestamp, true);
			}
		}
	}

?>

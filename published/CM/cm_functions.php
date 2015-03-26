<?php

	//
	// Contact Manager non-DMBS application functions
	//

	function cm_getNameLatinSymbols( $str )
	//
	//	Cuts all non-Latin symbols from string
	//
	//		Parameters:
	//			$str - source string
	//
	//		Returns strings
	//
	{
		$count = strlen($str);
		$arr = array();

		for ( $i = 0; $i < $count; $i++ )
			$arr[] = $str[$i];

		$res = preg_grep( '/[a-zA-Z]/u', $arr );

		return implode( '', $res );
	}

	function cm_createFieldNameElement( &$dom, &$parent, $elementName, $nameValues )
	//
	// Creates name element in XML field description
	//
	//		Parameters:
	//			$dom - DOM object reference
	//			$parent - parent element reference
	//			$elementName - new element name
	//			$nameValues - name values array
	//
	//		Returns null or PEAR_Error
	//
	{
		// Create new XML element
		//
		$nameElement = @create_addElement( $dom, $parent, $elementName );

		// Create name values elements
		//
		if ( is_array($nameValues) )
			foreach ( $nameValues as $key=>$value ) {
				$valueElement = @create_addElement( $dom, $nameElement, $key );
				$valueElement->set_attribute( CONTACT_NAMEVALUE, $value );
			}
	}

	function cm_getSignupScriptAddress( $external = true )
	//
	//	Returns address of the Contact Manager signup form script
	//
	//		Parameters:
	//			$external - indicates that function must return a script for external subscribers
	//
	//		Returns string
	//
	{
		$URL = dirname( getCurrentAddress() );

		$pathData = explodePath( $URL );

		if ( !strlen($pathData[count($pathData)-1]) )
			array_pop($pathData);

		if ( $external )
			return implode("/", $pathData).'/addcont.php';
		else
			return implode("/", $pathData).'/'.PAGE_CM_SIGNUPFORM;
	}

?>